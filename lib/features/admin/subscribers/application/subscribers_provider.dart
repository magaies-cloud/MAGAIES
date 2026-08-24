import 'dart:convert';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/core/enums/user_role.dart';
import '../domain/models/subscriber_model.dart';

final _supabase = Supabase.instance.client;

final subscribersProvider = StateNotifierProvider<SubscribersNotifier, AsyncValue<List<SubscriberModel>>>((ref) {
  return SubscribersNotifier()..loadSubscribers();
});

class SubscribersNotifier extends StateNotifier<AsyncValue<List<SubscriberModel>>> {
  SubscribersNotifier() : super(const AsyncValue.loading());

  Future<void> loadSubscribers() async {
    try {
      state = const AsyncValue.loading();
      final data = await _supabase.from('users').select().order('created_at', ascending: false);
      
      final List<SubscriberModel> subs = (data as List<dynamic>).map((e) {
        final permsData = e['permissions'];
        Map<String, dynamic> perms = {};
        if (permsData != null && permsData is Map) {
          perms = Map<String, dynamic>.from(permsData);
        } else if (permsData is String) {
          // just in case it comes as stringified json
          try {
            perms = jsonDecode(permsData);
          } catch (_) {}
        }

        return SubscriberModel(
          id: e['id'].toString(),
          name: e['name'] ?? 'User',
          nameAr: e['name'] ?? 'User',
          email: e['email'] ?? '',
          phone: e['phone'] ?? '',
          role: _parseRole(e['role']),
          status: e['is_suspended'] == true ? SubscriberStatus.suspended : SubscriberStatus.active,
          joinedAt: e['created_at'] != null ? DateTime.parse(e['created_at']) : DateTime.now(),
          isVerified: true,
          permissions: perms,
        );
      }).toList();
      
      state = AsyncValue.data(subs);
    } catch (e) {
      state = AsyncValue.error(e, StackTrace.current);
    }
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

  Future<void> changeRole(String userId, UserRole newRole) async {
    try {
      final roleStr = newRole.toString().split('.').last;
      await _supabase.from('users').update({'role': roleStr}).eq('id', userId);
      
      if (state.value != null) {
        final updatedList = state.value!.map((sub) {
          if (sub.id == userId) return sub.copyWith(role: newRole);
          return sub;
        }).toList();
        state = AsyncValue.data(updatedList);
      }
    } catch (e) {
      print('Error updating role: ' + e.toString());
    }
  }

  Future<void> updatePermissions(String userId, Map<String, dynamic> perms) async {
    try {
      await _supabase.from('users').update({'permissions': perms}).eq('id', userId);
      
      if (state.value != null) {
        final updatedList = state.value!.map((sub) {
          if (sub.id == userId) return sub.copyWith(permissions: perms);
          return sub;
        }).toList();
        state = AsyncValue.data(updatedList);
      }
    } catch (e) {
      print('Error updating permissions: ' + e.toString());
    }
  }

  Future<void> removeSubscriber(String id) async {
    try {
      await _supabase.from('users').delete().eq('id', id);
      if (state.value != null) {
        state = AsyncValue.data(state.value!.where((sub) => sub.id != id).toList());
      }
    } catch (e) {
      print('Error deleting user: ' + e.toString());
    }
  }

  void approveMerchant(String id) {
    changeRole(id, UserRole.merchant);
  }
  
  Future<void> toggleSuspension(String id) async {
    try {
      final sub = state.value?.firstWhere((s) => s.id == id);
      if (sub == null) return;
      
      final isSuspended = sub.status == SubscriberStatus.suspended;
      final newStatus = isSuspended ? SubscriberStatus.active : SubscriberStatus.suspended;
      
      await _supabase.from('users').update({'is_suspended': !isSuspended}).eq('id', id);
      
      final updatedList = state.value!.map((s) {
        if (s.id == id) return s.copyWith(status: newStatus);
        return s;
      }).toList();
      state = AsyncValue.data(updatedList);
    } catch (e) {
      print('Error toggling suspension: ' + e.toString());
    }
  }
}

final subscriberFilterProvider = StateProvider<SubscriberFilterType>((ref) => SubscriberFilterType.all);
final subscriberSearchProvider = StateProvider<String>((ref) => '');

enum SubscriberFilterType { all, buyers, merchants, pending, suspended }

final filteredSubscribersProvider = Provider<AsyncValue<List<SubscriberModel>>>((ref) {
  final subscribersAsync = ref.watch(subscribersProvider);
  final filter = ref.watch(subscriberFilterProvider);
  final search = ref.watch(subscriberSearchProvider).toLowerCase();

  return subscribersAsync.whenData((subscribers) {
    return subscribers.where((sub) {
      bool matchesFilter = true;
      switch (filter) {
        case SubscriberFilterType.all: matchesFilter = true; break;
        case SubscriberFilterType.buyers: matchesFilter = sub.role == UserRole.buyer; break;
        case SubscriberFilterType.merchants: matchesFilter = sub.role == UserRole.merchant; break;
        case SubscriberFilterType.pending: matchesFilter = sub.status == SubscriberStatus.pendingVerification; break;
        case SubscriberFilterType.suspended: matchesFilter = sub.status == SubscriberStatus.suspended; break;
      }

      bool matchesSearch = true;
      if (search.isNotEmpty) {
        matchesSearch = sub.name.toLowerCase().contains(search) ||
            sub.nameAr.toLowerCase().contains(search) ||
            sub.email.toLowerCase().contains(search) ||
            sub.phone.contains(search);
      }

      return matchesFilter && matchesSearch;
    }).toList();
  });
});

final pendingVerificationsCountProvider = Provider<int>((ref) => 0);