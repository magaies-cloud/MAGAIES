import 'dart:async';
import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/core/enums/user_role.dart';
import 'package:magaiees_marketplace/core/providers/global_settings_provider.dart';
import 'package:magaiees_marketplace/features/admin/pricing/application/pricing_provider.dart';
import 'package:magaiees_marketplace/core/network/supabase_safe_query.dart';
import '../domain/models/subscriber_model.dart';

final _supabase = Supabase.instance.client;

/// Page size for paginated queries
const int _pageSize = 50;

/// Server-side filter and search state
class SubscriberQueryState {
  final String searchQuery;
  final UiServerFilter filter;

  const SubscriberQueryState({
    this.searchQuery = '',
    this.filter = UiServerFilter.all,
  });

  SubscriberQueryState copyWith({String? searchQuery, UiServerFilter? filter}) {
    return SubscriberQueryState(
      searchQuery: searchQuery ?? this.searchQuery,
      filter: filter ?? this.filter,
    );
  }
}

enum UiServerFilter { all, buyers, merchants, employees, active, suspended, pending, rejected, advertisers }

final subscriberQueryProvider = StateProvider<SubscriberQueryState>((ref) => const SubscriberQueryState());

/// Main paginated subscribers provider
final subscribersProvider = StateNotifierProvider<SubscribersNotifier, AsyncValue<List<SubscriberModel>>>((ref) {
  final notifier = SubscribersNotifier(ref);
  ref.listen<SubscriberQueryState>(subscriberQueryProvider, (prev, next) {
    notifier.loadSubscribers(reset: true);
  });
  notifier.loadSubscribers();
  return notifier;
});

class SubscribersNotifier extends StateNotifier<AsyncValue<List<SubscriberModel>>> {
  final Ref _ref;
  int _currentPage = 0;
  bool _hasMore = true;
  bool _isLoadingMore = false;
  int _queryEpoch = 0; // Prevents stale responses from overwriting fresh data
  List<SubscriberModel> _allUsersSnapshot = [];
  RealtimeChannel? _usersRealtimeChannel;
  Timer? _realtimeDebounce;

  SubscribersNotifier(this._ref) : super(const AsyncValue.loading()) {
    _initRealtimeSubscription();
  }

  void _initRealtimeSubscription() {
    try {
      _usersRealtimeChannel = _supabase
          .channel('public:users:subscribers_live_sync')
          .onPostgresChanges(
            event: PostgresChangeEvent.all,
            schema: 'public',
            table: 'users',
            callback: (payload) {
              _realtimeDebounce?.cancel();
              _realtimeDebounce = Timer(const Duration(milliseconds: 600), () {
                if (mounted) {
                  loadSubscribers(reset: true);
                }
              });
            },
          )
          .subscribe();
    } catch (e) {
      debugPrint('Subscribers realtime subscription warning: $e');
    }
  }

  @override
  void dispose() {
    _realtimeDebounce?.cancel();
    _usersRealtimeChannel?.unsubscribe();
    super.dispose();
  }

  bool get hasMore => _hasMore;
  bool get isLoadingMore => _isLoadingMore;
  List<SubscriberModel> get allUsersSnapshot => _allUsersSnapshot;

  Future<void> loadSubscribers({bool reset = false}) async {
    try {
      if (reset) {
        _currentPage = 0;
        _hasMore = true;
        _isLoadingMore = false; // Cancel any in-flight request logically
        _queryEpoch++;
        state = const AsyncValue.loading();
      }

      if (!_hasMore || _isLoadingMore) return;
      _isLoadingMore = true;

      final thisEpoch = _queryEpoch;
      final queryState = _ref.read(subscriberQueryProvider);
      final from = _currentPage * _pageSize;
      final to = from + _pageSize - 1;

      // Build query with server-side filtering (always exclude soft-deleted guest records)
      var query = _supabase.from('users').select().neq('role', 'guest');

      // Apply role/status filter server-side
      switch (queryState.filter) {
        case UiServerFilter.buyers:
          query = query.eq('role', 'buyer');
          break;
        case UiServerFilter.merchants:
          query = query.eq('role', 'merchant');
          break;
        case UiServerFilter.employees:
          query = query.eq('role', 'employee');
          break;
        case UiServerFilter.active:
          query = query.eq('is_suspended', false);
          break;
        case UiServerFilter.suspended:
          query = query.eq('is_suspended', true);
          break;
        case UiServerFilter.pending:
          query = query.or('is_verified.eq.false,permissions->>merchant_upgrade_pending.eq.true');
          break;
        case UiServerFilter.rejected:
          query = query.not('permissions->rejection_reason', 'is', 'null');
          break;
        case UiServerFilter.advertisers:
        case UiServerFilter.all:
          break;
      }

      // Apply search server-side (sanitize input to prevent PostgREST syntax errors)
      final rawSearch = queryState.searchQuery
          .replaceAll(',', '')
          .replaceAll('(', '')
          .replaceAll(')', '')
          .trim();

      if (rawSearch.isNotEmpty) {
        final q = '%$rawSearch%';
        query = query.or('name.ilike.$q,name_ar.ilike.$q,email.ilike.$q,phone.ilike.$q,store_name.ilike.$q,store_name_ar.ilike.$q');
      }

      final isUnfilteredFirstPage =
          queryState.filter == UiServerFilter.all && rawSearch.isEmpty && _currentPage == 0;

      // Parallel fetch: Filtered Users, All Users (for global KPI accuracy), Stores, Orders, and Ads
      final results = await Future.wait([
        safeSupabaseQuery(
          () => query.order('created_at', ascending: false).range(from, to),
          fallback: () => <dynamic>[],
        ),
        safeSupabaseQuery(
          () => _supabase.from('stores').select('id, owner_id, name, name_ar'),
          fallback: () => <dynamic>[],
        ),
        safeSupabaseQuery(
          () => _supabase.from('orders').select('id, buyer_id, store_id, total_amount, status'),
          fallback: () => <dynamic>[],
        ),
        safeSupabaseQuery(
          () => _supabase.from('ads').select('id, title, company, price, is_active'),
          fallback: () => <dynamic>[],
        ),
        if (!isUnfilteredFirstPage)
          safeSupabaseQuery(
            () => _supabase.from('users').select().neq('role', 'guest').order('created_at', ascending: false).limit(500),
            fallback: () => <dynamic>[],
          ),
      ]);

      // Discard stale responses if a newer reset happened while we were waiting
      if (thisEpoch != _queryEpoch) {
        _isLoadingMore = false;
        return;
      }

      final rawUsers = results[0];
      final rawStores = results[1];
      final rawOrders = results[2];
      final rawAds = results[3];
      final rawAllUsers = isUnfilteredFirstPage ? rawUsers : (results.length > 4 ? results[4] : rawUsers);

      // 1. Build Store maps (separating English and Arabic store names)
      final Map<String, String> ownerToStoreName = {};
      final Map<String, String> ownerToStoreNameAr = {};
      final Map<String, String> storeIdToOwnerId = {};
      for (var s in rawStores) {
        if (s is Map) {
          final ownerId = s['owner_id']?.toString() ?? '';
          final storeId = s['id']?.toString() ?? '';
          final nameAr = s['name_ar']?.toString() ?? '';
          final name = s['name']?.toString() ?? '';
          if (ownerId.isNotEmpty) {
            ownerToStoreName[ownerId] = name.isNotEmpty ? name : nameAr;
            ownerToStoreNameAr[ownerId] = nameAr.isNotEmpty ? nameAr : name;
            storeIdToOwnerId[storeId] = ownerId;
          }
        }
      }

      // 2. Aggregate Live Accounting (Orders & Financial Volume)
      final Map<String, int> buyerOrdersCount = {};
      final Map<String, double> buyerTotalSpent = {};
      final Map<String, int> merchantOrdersCount = {};
      final Map<String, double> merchantTotalSales = {};

      for (var o in rawOrders) {
        if (o is Map) {
          final status = o['status']?.toString().toLowerCase() ?? 'pending';
          final isCancelled = status == 'cancelled' || status == 'ملغي' || status == 'ملغى';
          if (isCancelled) continue; // Exclude cancelled orders from buyer spend and merchant sales

          final buyerId = o['buyer_id']?.toString() ?? '';
          final storeId = o['store_id']?.toString() ?? '';
          final amount = (num.tryParse(o['total_amount']?.toString() ?? '0') ?? 0.0).toDouble();

          if (buyerId.isNotEmpty) {
            buyerOrdersCount[buyerId] = (buyerOrdersCount[buyerId] ?? 0) + 1;
            buyerTotalSpent[buyerId] = (buyerTotalSpent[buyerId] ?? 0.0) + amount;
          }

          final merchantOwnerId = storeIdToOwnerId[storeId] ?? '';
          if (merchantOwnerId.isNotEmpty) {
            merchantOrdersCount[merchantOwnerId] = (merchantOrdersCount[merchantOwnerId] ?? 0) + 1;
            merchantTotalSales[merchantOwnerId] = (merchantTotalSales[merchantOwnerId] ?? 0.0) + amount;
          }
        }
      }

      // 3. Aggregate Advertiser Campaigns & Spend across all users
      final Map<String, int> userAdsCount = {};
      final Map<String, double> userAdSpend = {};

      for (var ad in rawAds) {
        if (ad is Map) {
          final comp = (ad['company']?.toString() ?? '').trim().toLowerCase();
          final price = (num.tryParse(ad['price']?.toString() ?? '0') ?? 0.0).toDouble();
          if (comp.isEmpty) continue;

          // Match company to user across rawAllUsers
          for (var u in rawAllUsers) {
            if (u is Map) {
              final uId = u['id']?.toString() ?? '';
              final uName = (u['name']?.toString() ?? '').trim().toLowerCase();
              final uNameAr = (u['name_ar']?.toString() ?? '').trim().toLowerCase();
              final uEmail = (u['email']?.toString() ?? '').trim().toLowerCase();
              final uStore = (u['store_name']?.toString() ?? '').trim().toLowerCase();
              final directStore = (ownerToStoreName[uId] ?? '').trim().toLowerCase();
              final directStoreAr = (ownerToStoreNameAr[uId] ?? '').trim().toLowerCase();

              final isMatch = (uStore.isNotEmpty && (comp == uStore || comp.contains(uStore) || uStore.contains(comp))) ||
                  (directStore.isNotEmpty && (comp == directStore || comp.contains(directStore) || directStore.contains(comp))) ||
                  (directStoreAr.isNotEmpty && (comp == directStoreAr || comp.contains(directStoreAr) || directStoreAr.contains(comp))) ||
                  (uName.isNotEmpty && (comp == uName || comp.contains(uName) || uName.contains(comp))) ||
                  (uNameAr.isNotEmpty && (comp == uNameAr || comp.contains(uNameAr) || uNameAr.contains(comp))) ||
                  (uEmail.isNotEmpty && comp == uEmail);

              if (isMatch) {
                userAdsCount[uId] = (userAdsCount[uId] ?? 0) + 1;
                userAdSpend[uId] = (userAdSpend[uId] ?? 0.0) + price;
              }
            }
          }
        }
      }

      // Build global snapshot for KPI & Filter Badge accuracy (excluding deleted accounts)
      _allUsersSnapshot = rawAllUsers
          .map((e) {
            return _parseSubscriber(
              e as Map<String, dynamic>,
              ownerToStoreName: ownerToStoreName,
              ownerToStoreNameAr: ownerToStoreNameAr,
              buyerOrdersCount: buyerOrdersCount,
              buyerTotalSpent: buyerTotalSpent,
              merchantOrdersCount: merchantOrdersCount,
              merchantTotalSales: merchantTotalSales,
              userAdsCount: userAdsCount,
              userAdSpend: userAdSpend,
            );
          })
          .where((s) => !_isDeletedSubscriber(s))
          .toList();

      List<SubscriberModel> newSubs = rawUsers
          .map((e) {
            return _parseSubscriber(
              e as Map<String, dynamic>,
              ownerToStoreName: ownerToStoreName,
              ownerToStoreNameAr: ownerToStoreNameAr,
              buyerOrdersCount: buyerOrdersCount,
              buyerTotalSpent: buyerTotalSpent,
              merchantOrdersCount: merchantOrdersCount,
              merchantTotalSales: merchantTotalSales,
              userAdsCount: userAdsCount,
              userAdSpend: userAdSpend,
            );
          })
          .where((s) => !_isDeletedSubscriber(s))
          .toList();

      // Also include users whose store name in `stores` table matched search query even if `users.store_name` was empty
      if (rawSearch.isNotEmpty) {
        final searchLower = rawSearch.toLowerCase();
        final existingIds = newSubs.map((s) => s.id).toSet();
        for (final sub in _allUsersSnapshot) {
          if (!existingIds.contains(sub.id)) {
            final matchStore = (sub.storeName?.toLowerCase().contains(searchLower) ?? false) ||
                (sub.storeNameAr?.toLowerCase().contains(searchLower) ?? false) ||
                sub.membershipNumber.toLowerCase().contains(searchLower);
            if (matchStore) {
              newSubs.add(sub);
            }
          }
        }
      }

      // Apply exact client-side refinement for status/advertiser filters
      if (queryState.filter == UiServerFilter.advertisers) {
        newSubs = newSubs.where((s) => s.isAdvertiser || s.totalAdsCount > 0).toList();
      } else if (queryState.filter == UiServerFilter.pending) {
        newSubs = newSubs.where((s) => s.status == SubscriberStatus.pendingVerification).toList();
      } else if (queryState.filter == UiServerFilter.rejected) {
        newSubs = newSubs.where((s) => s.status == SubscriberStatus.rejectedVerification).toList();
      } else if (queryState.filter == UiServerFilter.active) {
        newSubs = newSubs.where((s) => s.status == SubscriberStatus.active).toList();
      }

      _hasMore = rawUsers.length == _pageSize;
      _currentPage++;
      _isLoadingMore = false;

      if (reset || _currentPage == 1) {
        state = AsyncValue.data(newSubs);
      } else {
        final existingList = state.valueOrNull ?? [];
        state = AsyncValue.data([...existingList, ...newSubs]);
      }
    } catch (e) {
      _isLoadingMore = false;
      if (_currentPage == 0) {
        state = AsyncValue.error(e, StackTrace.current);
      }
    }
  }

  /// Load next page (for infinite scroll)
  Future<void> loadMore() => loadSubscribers();

  SubscriberModel _parseSubscriber(
    Map<String, dynamic> e, {
    Map<String, String> ownerToStoreName = const {},
    Map<String, String> ownerToStoreNameAr = const {},
    Map<String, int> buyerOrdersCount = const {},
    Map<String, double> buyerTotalSpent = const {},
    Map<String, int> merchantOrdersCount = const {},
    Map<String, double> merchantTotalSales = const {},
    Map<String, int> userAdsCount = const {},
    Map<String, double> userAdSpend = const {},
  }) {
    final permsData = e['permissions'];
    Map<String, dynamic> perms = {};
    if (permsData != null && permsData is Map) {
      perms = Map<String, dynamic>.from(permsData);
    } else if (permsData is String) {
      try {
        perms = jsonDecode(permsData);
      } catch (_) {}
    }

    // Determine status: suspended > rejectedVerification > pendingVerification > active
    final hasRejection = perms.containsKey('rejection_reason') &&
        perms['rejection_reason'] != null &&
        perms['rejection_reason'].toString().trim().isNotEmpty &&
        e['role'] != 'merchant';

    final hasUpgradePending =
        (perms['merchant_upgrade_pending'] == true || perms['merchant_upgrade_pending'] == 'true') &&
        e['role'] != 'merchant';

    SubscriberStatus status;
    if (e['is_suspended'] == true) {
      status = SubscriberStatus.suspended;
    } else if (hasRejection) {
      status = SubscriberStatus.rejectedVerification;
    } else if (e['is_verified'] == false || hasUpgradePending) {
      status = SubscriberStatus.pendingVerification;
    } else {
      status = SubscriberStatus.active;
    }

    final userId = e['id'].toString();
    final role = _parseRole(e['role']);

    // Resolved store name (both En and Ar)
    String? storeName = e['store_name']?.toString();
    if (storeName == null || storeName.trim().isEmpty) {
      storeName = ownerToStoreName[userId];
    }
    String? storeNameAr = e['store_name_ar']?.toString();
    if (storeNameAr == null || storeNameAr.trim().isEmpty) {
      storeNameAr = ownerToStoreNameAr[userId] ?? storeName;
    }

    // Resolved live accounting numbers
    int liveOrders = 0;
    double liveSpent = 0.0;
    if (role == UserRole.merchant) {
      liveOrders = merchantOrdersCount[userId] ?? (e['total_orders'] ?? 0);
      liveSpent = merchantTotalSales[userId] ?? (num.tryParse(e['total_spent']?.toString() ?? '0') ?? 0.0).toDouble();
    } else {
      liveOrders = buyerOrdersCount[userId] ?? (e['total_orders'] ?? 0);
      liveSpent = buyerTotalSpent[userId] ?? (num.tryParse(e['total_spent']?.toString() ?? '0') ?? 0.0).toDouble();
    }

    // Resolved advertiser campaigns
    final adsCount = userAdsCount[userId] ?? 0;
    final adSpend = userAdSpend[userId] ?? 0.0;
    final isAdv = adsCount > 0 || perms['is_advertiser'] == true;

    final shortId = userId.length > 6 ? userId.substring(0, 6).toUpperCase() : userId;
    final resolvedMembership = (e['membership_number']?.toString().trim().isNotEmpty == true)
        ? e['membership_number'].toString().trim()
        : (perms['membership_number']?.toString().trim().isNotEmpty == true
            ? perms['membership_number'].toString().trim()
            : 'MAG-$shortId');

    return SubscriberModel(
      id: userId,
      membershipNumber: resolvedMembership,
      name: e['name'] ?? 'User',
      nameAr: e['name_ar'] ?? e['name'] ?? 'User',
      email: e['email'] ?? '',
      phone: e['phone'] ?? '',
      role: role,
      status: status,
      joinedAt: DateTime.tryParse(e['created_at']?.toString() ?? '') ?? DateTime.now(),
      isVerified: e['is_verified'] == true,
      permissions: perms,
      merchantPoints: e['merchant_points'] ?? 0,
      merchantTier: e['merchant_tier'] ?? 'beginner',
      directPaymentEnabled: e['direct_payment_enabled'] ?? false,
      bankName: e['bank_name'],
      bankAccountName: e['bank_account_name'],
      bankAccountNumber: e['bank_account_number'],
      storeName: storeName ?? perms['requested_store_name']?.toString(),
      storeNameAr: storeNameAr ?? perms['requested_store_name']?.toString(),
      documentType: e['document_type']?.toString() ??
          perms['verification_doc_type']?.toString() ??
          perms['document_type']?.toString(),
      totalOrders: liveOrders,
      totalSpent: liveSpent,
      totalAdsCount: adsCount,
      totalAdSpend: adSpend,
      isAdvertiser: isAdv,
    );
  }

  UserRole _parseRole(String? role) {
    if (role == null) return UserRole.buyer;
    switch (role.toLowerCase()) {
      case 'admin': return UserRole.admin;
      case 'employee': return UserRole.employee;
      case 'merchant': return UserRole.merchant;
      case 'guest': return UserRole.guest;
      default: return UserRole.buyer;
    }
  }

  /// All mutation methods rethrow so UI can display error feedback
  Future<void> changeRole(String userId, UserRole newRole) async {
    final roleStr = newRole.toString().split('.').last;
    await _supabase.from('users').update({'role': roleStr}).eq('id', userId);
    _updateLocal(userId, (sub) => sub.copyWith(role: newRole));
  }

  /// Update subscriber's basic profile, contact numbers, store names, verification & tier
  Future<void> updateSubscriberBasicAndStoreInfo({
    required String userId,
    required String name,
    required String nameAr,
    required String email,
    required String phone,
    String? secondaryPhone,
    String? cityOrAddress,
    String? membershipNumber,
    String? storeName,
    String? storeNameAr,
    bool? isVerified,
    String? merchantTier,
    String? documentType,
  }) async {
    final sub = _findById(userId);
    final perms = Map<String, dynamic>.from(sub?.permissions ?? {});

    if (secondaryPhone != null) {
      perms['secondary_phone'] = secondaryPhone.trim();
    }
    if (cityOrAddress != null) {
      perms['address'] = cityOrAddress.trim();
    }
    if (membershipNumber != null && membershipNumber.trim().isNotEmpty) {
      perms['membership_number'] = membershipNumber.trim();
    }
    if (documentType != null && documentType.trim().isNotEmpty) {
      perms['document_type'] = documentType.trim();
    }

    final resolvedStoreEn = (storeName != null && storeName.trim().isNotEmpty)
        ? storeName.trim()
        : (storeNameAr != null && storeNameAr.trim().isNotEmpty ? storeNameAr.trim() : null);
    final resolvedStoreAr = (storeNameAr != null && storeNameAr.trim().isNotEmpty)
        ? storeNameAr.trim()
        : resolvedStoreEn;

    final Map<String, dynamic> userUpdatePayload = {
      'name': name.trim().isNotEmpty ? name.trim() : nameAr.trim(),
      'name_ar': nameAr.trim().isNotEmpty ? nameAr.trim() : name.trim(),
      'email': email.trim(),
      'phone': phone.trim(),
      'permissions': perms,
      'store_name': ?resolvedStoreEn,
      'store_name_ar': ?resolvedStoreAr,
      'is_verified': ?isVerified,
      if (merchantTier != null && merchantTier.isNotEmpty) 'merchant_tier': merchantTier,
    };

    await _supabase.from('users').update(userUpdatePayload).eq('id', userId);

    // Sync with `stores` table if store name is provided or subscriber is a merchant
    if (resolvedStoreEn != null || resolvedStoreAr != null || sub?.role == UserRole.merchant) {
      try {
        final existingStore = await _supabase
            .from('stores')
            .select('id')
            .eq('owner_id', userId)
            .maybeSingle();

        if (existingStore != null) {
          await _supabase.from('stores').update({
            'name': ?resolvedStoreEn,
            'name_ar': ?resolvedStoreAr,
          }).eq('owner_id', userId);
        } else if (resolvedStoreEn != null) {
          await _supabase.from('stores').insert({
            'owner_id': userId,
            'name': resolvedStoreEn,
            'name_ar': resolvedStoreAr ?? resolvedStoreEn,
            'is_open': true,
          });
        }
      } catch (e) {
        debugPrint('Store sync warning: $e');
      }
    }

    _updateLocal(userId, (s) {
      SubscriberStatus updatedStatus = s.status;
      if (isVerified == true && s.status == SubscriberStatus.pendingVerification) {
        updatedStatus = SubscriberStatus.active;
      } else if (isVerified == false && s.status == SubscriberStatus.active) {
        updatedStatus = SubscriberStatus.pendingVerification;
      }
      return s.copyWith(
        name: name.trim().isNotEmpty ? name.trim() : nameAr.trim(),
        nameAr: nameAr.trim().isNotEmpty ? nameAr.trim() : name.trim(),
        email: email.trim(),
        phone: phone.trim(),
        membershipNumber: (membershipNumber != null && membershipNumber.trim().isNotEmpty)
            ? membershipNumber.trim()
            : s.membershipNumber,
        storeName: resolvedStoreEn ?? s.storeName,
        storeNameAr: resolvedStoreAr ?? s.storeNameAr,
        isVerified: isVerified ?? s.isVerified,
        status: updatedStatus,
        merchantTier: merchantTier ?? s.merchantTier,
        documentType: documentType ?? s.documentType,
        permissions: perms,
      );
    });
  }

  Future<void> updatePermissions(String userId, Map<String, dynamic> perms) async {
    await _supabase.from('users').update({'permissions': perms}).eq('id', userId);
    _updateLocal(userId, (sub) => sub.copyWith(permissions: perms));
  }

  Future<void> toggleDirectPayment(String userId, bool isEnabled) async {
    final sub = _findById(userId);
    final perms = Map<String, dynamic>.from(sub?.permissions ?? {});
    perms['direct_payment_enabled'] = isEnabled;

    await _supabase.from('users').update({
      'direct_payment_enabled': isEnabled,
      'permissions': perms,
    }).eq('id', userId);

    _updateLocal(userId, (sub) => sub.copyWith(
      directPaymentEnabled: isEnabled,
      permissions: perms,
    ));
  }

  Future<void> updateMerchantWalletData({
    required String userId,
    required String bankName,
    required String bankAccountName,
    required String bankAccountNumber,
    required String bankakAccount,
    required String bankakName,
    required String ocashPhone,
    required String ocashName,
    required bool directPaymentEnabled,
  }) async {
    final sub = _findById(userId);
    final perms = Map<String, dynamic>.from(sub?.permissions ?? {});
    perms['bankak_account'] = bankakAccount;
    perms['bankak_name'] = bankakName;
    perms['ocash_phone'] = ocashPhone;
    perms['ocash_name'] = ocashName;

    await _supabase.from('users').update({
      'bank_name': bankName,
      'bank_account_name': bankAccountName,
      'bank_account_number': bankAccountNumber,
      'direct_payment_enabled': directPaymentEnabled,
      'permissions': perms,
    }).eq('id', userId);

    try {
      await _supabase.from('stores').update({
        'bank_name': bankName,
        'bank_account_name': bankAccountName,
        'bank_account_number': bankAccountNumber,
      }).eq('owner_id', userId);
    } catch (_) {}

    _updateLocal(userId, (s) => s.copyWith(
      bankName: bankName,
      bankAccountName: bankAccountName,
      bankAccountNumber: bankAccountNumber,
      directPaymentEnabled: directPaymentEnabled,
      permissions: perms,
    ));
  }

  Future<void> updateBuyerB2bCredit({
    required String userId,
    required bool enabled,
    double? creditLimit,
    String? allowedScope, // 'all', 'exclusive_only', 'merchants_only'
    List<String>? specificStoreIds,
  }) async {
    final sub = _findById(userId);
    final perms = Map<String, dynamic>.from(sub?.permissions ?? {});
    perms['b2b_credit_enabled'] = enabled;
    if (creditLimit != null) perms['b2b_credit_limit'] = creditLimit;
    if (allowedScope != null) perms['b2b_allowed_scope'] = allowedScope;
    if (specificStoreIds != null) perms['b2b_allowed_merchants'] = specificStoreIds;

    await _supabase.from('users').update({
      'permissions': perms,
    }).eq('id', userId);

    _updateLocal(userId, (s) => s.copyWith(permissions: perms));
  }

  Future<void> adjustMerchantPoints(String userId, int pointsToAdd) async {
    final sub = _findById(userId);
    if (sub == null) return;
    final newPoints = (sub.merchantPoints + pointsToAdd).clamp(0, 999999);
    await _supabase.from('users').update({'merchant_points': newPoints}).eq('id', userId);
    _updateLocal(userId, (s) => s.copyWith(merchantPoints: newPoints));
  }

  bool _isDeletedSubscriber(SubscriberModel s) {
    if (s.permissions['is_deleted'] == true || s.permissions['deletion_requested'] == true) {
      return true;
    }
    // Accounts previously soft-deleted by old removeSubscriber had role set to guest + suspended
    if (s.role == UserRole.guest && s.status == SubscriberStatus.suspended) {
      return true;
    }
    return false;
  }

  Future<void> removeSubscriber(String id) async {
    final sub = _findById(id);

    // 1. Call server-side SECURITY DEFINER RPC to physically delete from both public.users and auth.users
    bool hardDeleted = false;
    try {
      final rpcRes = await _supabase.rpc(
        'delete_user_completely',
        params: {'target_user_id': id},
      );
      if (rpcRes == true) {
        hardDeleted = true;
      }
    } catch (_) {}

    if (!hardDeleted) {
      // 2. Clean up dependent records if any
      try {
        await _supabase.from('notifications').delete().eq('user_id', id);
      } catch (_) {}
      try {
        await _supabase.from('stores').delete().eq('owner_id', id);
      } catch (_) {}

      // 3. Attempt physical hard-delete from public.users
      try {
        final deletedRows = await _supabase.from('users').delete().eq('id', id).select('id');
        hardDeleted = deletedRows.isNotEmpty;
      } catch (_) {}
    }

    // 4. Fallback if FK constraints or RLS block physical delete: mark as permanently deleted and free email
    if (!hardDeleted) {
      final perms = Map<String, dynamic>.from(sub?.permissions ?? {});
      perms['is_deleted'] = true;
      perms['deletion_requested'] = true;
      perms['deleted_at'] = DateTime.now().toUtc().toIso8601String();
      if (sub != null && sub.email.isNotEmpty) {
        perms['deleted_email'] = sub.email;
      }
      final shortId = id.length > 8 ? id.substring(0, 8) : id;
      await _supabase.from('users').update({
        'is_suspended': true,
        'role': 'guest',
        'email': 'deleted_$shortId@deleted.local',
        'permissions': perms,
      }).eq('id', id);
    }

    // 4. Remove subscriber completely from local lists so it disappears immediately
    _allUsersSnapshot = _allUsersSnapshot.where((s) => s.id != id).toList();
    if (state.value != null) {
      state = AsyncValue.data(state.value!.where((s) => s.id != id).toList());
    }
  }

  /// Sends a targeted bulk notification to the specified list of subscriber IDs.
  Future<int> sendBulkBroadcast({
    required List<String> targetUserIds,
    required String title,
    required String body,
    String type = 'admin_broadcast_info',
  }) async {
    final cleanIds = targetUserIds.where((id) => id.trim().isNotEmpty).toSet().toList();
    if (cleanIds.isEmpty) return 0;

    try {
      final res = await _supabase.rpc(
        'send_admin_broadcast_notifications',
        params: {
          'target_user_ids': cleanIds,
          'msg_title': title.trim(),
          'msg_body': body.trim(),
          'msg_type': type,
        },
      );
      if (res is num) return res.toInt();
      return cleanIds.length;
    } catch (_) {
      // Fallback direct batch insert if RPC is unavailable
      final rows = cleanIds
          .map((uid) => {
                'user_id': uid,
                'title': title.trim(),
                'body': body.trim(),
                'type': type,
                'is_read': false,
              })
          .toList();
      await _supabase.from('notifications').insert(rows);
      return cleanIds.length;
    }
  }

  Future<void> approveMerchant(String id) async {
    final sub = _findById(id);
    final perms = Map<String, dynamic>.from(sub?.permissions ?? {});
    perms.remove('rejection_reason');
    perms.remove('rejected_at');
    perms['merchant_upgrade_pending'] = false;
    if (perms['allowed_categories'] == null && perms['requested_categories'] != null) {
      perms['allowed_categories'] = perms['requested_categories'];
    }

    await _supabase.from('users').update({
      'role': 'merchant',
      'is_verified': true,
      'permissions': perms,
    }).eq('id', id);

    // Ensure merchant has a store entry in `stores`
    try {
      final existingStore = await _supabase
          .from('stores')
          .select('id')
          .eq('owner_id', id)
          .maybeSingle();
      if (existingStore == null && sub != null) {
        final sName = (sub.storeName != null && sub.storeName!.isNotEmpty) ? sub.storeName! : sub.name;
        final sNameAr = (sub.storeNameAr != null && sub.storeNameAr!.isNotEmpty) ? sub.storeNameAr! : sub.nameAr;
        await _supabase.from('stores').insert({
          'owner_id': id,
          'name': sName,
          'name_ar': sNameAr,
          'is_open': true,
          'status': 'approved',
        });
      }
    } catch (_) {}
    
    _updateLocal(id, (sub) => sub.copyWith(
      role: UserRole.merchant,
      isVerified: true,
      status: SubscriberStatus.active,
      permissions: perms,
    ));
  }

  Future<void> rejectMerchant(String id, {String? reason}) async {
    final sub = _findById(id);
    final perms = Map<String, dynamic>.from(sub?.permissions ?? {});
    perms['rejection_reason'] = reason ?? 'الوثائق غير واضحة أو غير مطابقة للشروط';
    perms['rejected_at'] = DateTime.now().toIso8601String();
    perms['merchant_upgrade_pending'] = false;
    
    await _supabase.from('users').update({
      'role': 'buyer',
      'is_verified': false,
      'permissions': perms,
    }).eq('id', id);
    
    _updateLocal(id, (s) => s.copyWith(
      role: UserRole.buyer,
      isVerified: false,
      status: SubscriberStatus.rejectedVerification,
      permissions: perms,
    ));
  }

  Future<void> createSubscriber({
    required String name,
    required String nameAr,
    required String email,
    required String phone,
    required UserRole role,
    String? storeName,
    String? storeNameAr,
  }) async {
    final roleStr = role.toString().split('.').last;
    final resolvedStoreEn = (storeName != null && storeName.trim().isNotEmpty)
        ? storeName.trim()
        : (storeNameAr != null && storeNameAr.trim().isNotEmpty ? storeNameAr.trim() : null);
    final resolvedStoreAr = (storeNameAr != null && storeNameAr.trim().isNotEmpty)
        ? storeNameAr.trim()
        : resolvedStoreEn;

    final payload = <String, dynamic>{
      'name': name.trim().isNotEmpty ? name.trim() : nameAr.trim(),
      'name_ar': nameAr.trim().isNotEmpty ? nameAr.trim() : name.trim(),
      'email': email.trim(),
      'phone': phone.trim(),
      'role': roleStr,
      'is_suspended': false,
      'is_verified': true,
      'store_name': ?resolvedStoreEn,
      'store_name_ar': ?resolvedStoreAr,
    };
    final res = await _supabase.from('users').insert(payload).select().maybeSingle();

    final newSub = _parseSubscriber(res ?? payload);
    if (role == UserRole.merchant && newSub.id.isNotEmpty) {
      try {
        await _supabase.from('stores').insert({
          'owner_id': newSub.id,
          'name': resolvedStoreEn ?? newSub.name,
          'name_ar': resolvedStoreAr ?? newSub.nameAr,
          'is_open': true,
        });
      } catch (_) {}
    }

    _allUsersSnapshot = [newSub, ..._allUsersSnapshot];
    final currentList = state.valueOrNull ?? [];
    state = AsyncValue.data([newSub, ...currentList]);
  }

  Future<void> toggleSuspension(String id) async {
    final sub = _findById(id);
    if (sub == null) return;

    final isSuspended = sub.status == SubscriberStatus.suspended;
    final newStatus = isSuspended ? SubscriberStatus.active : SubscriberStatus.suspended;

    await _supabase.from('users').update({'is_suspended': !isSuspended}).eq('id', id);
    _updateLocal(id, (s) => s.copyWith(status: newStatus));
  }

  // --- Helpers ---

  SubscriberModel? _findById(String id) {
    final list = state.valueOrNull;
    if (list != null) {
      for (final s in list) {
        if (s.id == id) return s;
      }
    }
    for (final s in _allUsersSnapshot) {
      if (s.id == id) return s;
    }
    return null;
  }

  void _updateLocal(String id, SubscriberModel Function(SubscriberModel) updater) {
    _allUsersSnapshot = _allUsersSnapshot.map((sub) {
      if (sub.id == id) return updater(sub);
      return sub;
    }).toList();

    if (state.value != null) {
      final updatedList = state.value!.map((sub) {
        if (sub.id == id) return updater(sub);
        return sub;
      }).toList();
      state = AsyncValue.data(updatedList);
    }
  }
}

// Backward compatibility
final subscriberFilterProvider = StateProvider<SubscriberFilterType>((ref) => SubscriberFilterType.all);
final subscriberSearchProvider = StateProvider<String>((ref) => '');
enum SubscriberFilterType { all, buyers, merchants, pending, suspended }

final filteredSubscribersProvider = Provider<AsyncValue<List<SubscriberModel>>>((ref) {
  return ref.watch(subscribersProvider);
});

final pendingVerificationsCountProvider = Provider<int>((ref) {
  final subs = ref.watch(subscribersProvider).valueOrNull ?? [];
  return subs.where((s) => s.status == SubscriberStatus.pendingVerification).length;
});

final rejectedVerificationsCountProvider = Provider<int>((ref) {
  final subs = ref.watch(subscribersProvider).valueOrNull ?? [];
  return subs.where((s) => s.status == SubscriberStatus.rejectedVerification).length;
});

final advertisersCountProvider = Provider<int>((ref) {
  final subs = ref.watch(subscribersProvider).valueOrNull ?? [];
  return subs.where((s) => s.isAdvertiser || s.totalAdsCount > 0).length;
});

final totalSubscribersFinancialVolumeProvider = Provider<double>((ref) {
  final subs = ref.watch(subscribersProvider).valueOrNull ?? [];
  return subs.fold<double>(0.0, (sum, s) => sum + s.totalSpent);
});

class MerchantUpgradeSettingNotifier extends StateNotifier<bool> {
  final Ref? _ref;
  final SupabaseClient _supabase = Supabase.instance.client;
  RealtimeChannel? _realtimeChannel;

  MerchantUpgradeSettingNotifier([this._ref]) : super(false) {
    _fetchSetting();
    _initRealtime();
  }

  void _initRealtime() {
    try {
      _realtimeChannel = _supabase
          .channel('public:users:admin_upgrade_setting')
          .onPostgresChanges(
            event: PostgresChangeEvent.all,
            schema: 'public',
            table: 'users',
            filter: PostgresChangeFilter(
              type: PostgresChangeFilterType.eq,
              column: 'role',
              value: 'admin',
            ),
            callback: (payload) {
              _fetchSetting();
              _ref?.read(globalSettingsProvider.notifier).refresh();
            },
          )
          .subscribe();
    } catch (e) {
      debugPrint('Realtime channel error for upgrade settings: $e');
    }
  }

  @override
  void dispose() {
    final ch = _realtimeChannel;
    if (ch != null) {
      ch.unsubscribe();
      _supabase.removeChannel(ch);
    }
    super.dispose();
  }

  Future<void> _fetchSetting() async {
    try {
      final res = await _supabase
          .from('users')
          .select('permissions')
          .eq('role', 'admin')
          .limit(1)
          .maybeSingle();
      if (res != null && res['permissions'] is Map) {
        final perms = res['permissions'] as Map<String, dynamic>;
        if (perms.containsKey('enable_merchant_upgrade')) {
          final val = perms['enable_merchant_upgrade'];
          state = val == true || val == 'true';
          return;
        }
        if (perms.containsKey('platform_settings') && perms['platform_settings'] is Map) {
          final ps = perms['platform_settings'] as Map<String, dynamic>;
          if (ps.containsKey('enableMerchantUpgrade')) {
            final val = ps['enableMerchantUpgrade'];
            state = val == true || val == 'true';
            return;
          }
          if (ps.containsKey('enable_merchant_upgrade')) {
            final val = ps['enable_merchant_upgrade'];
            state = val == true || val == 'true';
            return;
          }
        }
        state = false;
      }
    } catch (e) {
      debugPrint('Error fetching merchant upgrade setting: $e');
    }
  }

  Future<void> toggleSetting(bool enabled) async {
    state = enabled;
    _ref?.read(globalSettingsProvider.notifier).refresh();
    try {
      final currentAdmin = await _supabase
          .from('users')
          .select('permissions')
          .eq('role', 'admin')
          .limit(1)
          .maybeSingle();
      final existingPerms = (currentAdmin != null && currentAdmin['permissions'] is Map)
          ? Map<String, dynamic>.from(currentAdmin['permissions'] as Map)
          : <String, dynamic>{};

      existingPerms['enable_merchant_upgrade'] = enabled;
      if (existingPerms['platform_settings'] is Map) {
        final ps = Map<String, dynamic>.from(existingPerms['platform_settings'] as Map);
        ps['enableMerchantUpgrade'] = enabled;
        ps['enable_merchant_upgrade'] = enabled;
        existingPerms['platform_settings'] = ps;
      }

      await _supabase.from('users').update({
        'permissions': existingPerms,
      }).eq('role', 'admin');

      _ref?.read(globalSettingsProvider.notifier).refresh();
    } catch (e) {
      debugPrint('Error saving merchant upgrade toggle: $e');
    }
  }
}

final merchantUpgradeSettingProvider = StateNotifierProvider<MerchantUpgradeSettingNotifier, bool>((ref) {
  return MerchantUpgradeSettingNotifier(ref);
});

class PlatformUsdRateNotifier extends StateNotifier<double> {
  final SupabaseClient _supabase = Supabase.instance.client;
  final Ref? _ref;
  RealtimeChannel? _channel;

  PlatformUsdRateNotifier([this._ref]) : super(2800.0) {
    _fetchSetting();
    _subscribeRealtime();
  }

  void _subscribeRealtime() {
    try {
      _channel = _supabase
          .channel('public:platform_usd_rate_setting')
          .onPostgresChanges(
            event: PostgresChangeEvent.all,
            schema: 'public',
            table: 'users',
            filter: PostgresChangeFilter(
              type: PostgresChangeFilterType.eq,
              column: 'role',
              value: 'admin',
            ),
            callback: (payload) {
              _fetchSetting();
              _ref?.read(globalSettingsProvider.notifier).refresh();
            },
          )
          .subscribe();
    } catch (_) {}
  }

  @override
  void dispose() {
    final ch = _channel;
    if (ch != null) {
      ch.unsubscribe();
      _supabase.removeChannel(ch);
    }
    super.dispose();
  }

  Future<void> _fetchSetting() async {
    try {
      final res = await _supabase
          .from('users')
          .select('permissions')
          .eq('role', 'admin')
          .limit(1)
          .maybeSingle();
      if (res != null && res['permissions'] is Map) {
        final perms = res['permissions'] as Map<String, dynamic>;
        if (perms.containsKey('platform_usd_rate')) {
          final val = double.tryParse(perms['platform_usd_rate']?.toString() ?? '');
          if (val != null && val > 0) {
            state = val;
            return;
          }
        }
        if (perms.containsKey('platform_settings') && perms['platform_settings'] is Map) {
          final ps = perms['platform_settings'] as Map<String, dynamic>;
          if (ps.containsKey('platformUsdRate')) {
            final val = double.tryParse(ps['platformUsdRate']?.toString() ?? '');
            if (val != null && val > 0) {
              state = val;
              return;
            }
          }
        }
      }
    } catch (e) {
      debugPrint('Error fetching platform USD rate: $e');
    }
  }

  Future<void> updateRate(double newRate) async {
    if (newRate <= 0) return;
    double oldRate = state;
    try {
      final currentAdmin = await _supabase
          .from('users')
          .select('permissions')
          .eq('role', 'admin')
          .limit(1)
          .maybeSingle();
      final existingPerms = (currentAdmin != null && currentAdmin['permissions'] is Map)
          ? Map<String, dynamic>.from(currentAdmin['permissions'] as Map)
          : <String, dynamic>{};

      final dbExistingRate = double.tryParse(existingPerms['platform_usd_rate']?.toString() ?? '');
      if (dbExistingRate != null && dbExistingRate > 0) {
        oldRate = dbExistingRate;
      }

      state = newRate;
      _ref?.read(globalSettingsProvider.notifier).refresh();

      existingPerms['previous_platform_usd_rate'] = oldRate;
      existingPerms['platform_usd_rate'] = newRate;
      existingPerms['platform_usd_rate_updated_at'] = DateTime.now().toUtc().toIso8601String();
      if (existingPerms['platform_settings'] is Map) {
        final ps = Map<String, dynamic>.from(existingPerms['platform_settings'] as Map);
        ps['platformUsdRate'] = newRate.toString();
        existingPerms['platform_settings'] = ps;
      } else {
        existingPerms['platform_settings'] = {'platformUsdRate': newRate.toString()};
      }

      await _supabase.from('users').update({
        'permissions': existingPerms,
      }).eq('role', 'admin');

      _ref?.read(pricingProvider.notifier).updateUsdRate(newRate);
      _ref?.read(globalSettingsProvider.notifier).refresh();

      // Notify ONLY Merchants who use the Platform USD Rate (strictly never Buyers)
      if ((newRate - oldRate).abs() >= 0.01) {
        try {
          await _supabase.rpc(
            'notify_merchants_usd_rate_change',
            params: {
              'old_rate': oldRate,
              'new_rate': newRate,
            },
          );
        } catch (rpcErr) {
          debugPrint('Error notifying merchants of USD rate change: $rpcErr');
        }
      }
    } catch (e) {
      debugPrint('Error updating platform USD rate: $e');
    }
  }
}

final platformUsdRateSettingProvider = StateNotifierProvider<PlatformUsdRateNotifier, double>((ref) {
  return PlatformUsdRateNotifier(ref);
});
