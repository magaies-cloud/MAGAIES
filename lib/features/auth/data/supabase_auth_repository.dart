import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:magaiees_marketplace/core/enums/user_role.dart';
import 'package:magaiees_marketplace/features/auth/domain/models/user_model.dart';
import 'package:magaiees_marketplace/features/auth/domain/repositories/auth_repository.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/core/network/supabase_safe_query.dart';

class SupabaseAuthRepository implements AuthRepository {
  final SupabaseClient _supabase = Supabase.instance.client;
  final SharedPreferences _prefs;

  SupabaseAuthRepository(this._prefs);

  static const _userCacheKey = 'magaiees_user_cache';

  @override
  Future<UserModel> login({
    required String email,
    required String password,
  }) async {
    final cleanInput = email.trim();
    final cleanEmail = cleanInput.toLowerCase();
    final cleanPass = password.trim();

    AuthResponse? response;
    Object? authError;

    // 1. Try standard Supabase Auth login
    try {
      response = await _supabase.auth.signInWithPassword(
        email: cleanEmail,
        password: password,
      );
    } catch (e) {
      authError = e;
      // Retry with trimmed password if different
      if (cleanPass != password) {
        try {
          response = await _supabase.auth.signInWithPassword(
            email: cleanEmail,
            password: cleanPass,
          );
          authError = null;
        } catch (e2) {
          authError = e2;
        }
      }
    }

    // 2. If standard Auth succeeded, fetch profile from public.users (by ID first, then by email fallback)
    if (response?.user != null) {
      final authUser = response!.user!;
      Map<String, dynamic>? userData = await safeSupabaseQuery(
        () => _supabase
            .from('users')
            .select()
            .eq('id', authUser.id)
            .maybeSingle(),
        fallback: () => null,
      );

      userData ??= await safeSupabaseQuery(
        () => _supabase
            .from('users')
            .select()
            .ilike('email', cleanEmail)
            .limit(1)
            .maybeSingle(),
        fallback: () => null,
      );

      // If the user exists in Supabase Auth (old account) but has no row in public.users, reconstruct it
      if (userData == null) {
        final meta = authUser.userMetadata ?? {};
        final fallbackUser = {
          'id': authUser.id,
          'name': meta['name']?.toString() ?? cleanEmail.split('@').first,
          'name_ar': meta['name_ar']?.toString() ?? meta['name']?.toString() ?? cleanEmail.split('@').first,
          'email': authUser.email ?? cleanEmail,
          'phone': meta['phone']?.toString() ?? authUser.phone ?? '',
          'role': meta['role']?.toString() ?? 'buyer',
        };
        try {
          await safeSupabaseQuery(() => _supabase.from('users').upsert(fallbackUser));
        } catch (e) {
          debugPrint('Could not auto-upsert missing public.users row: $e');
        }
        return _mapSupabaseDataToUserModel(fallbackUser);
      }

      return _mapSupabaseDataToUserModel(userData);
    }

    // 3. Fallback: Check if user exists in public.users with a temporary_pin / password
    // (e.g., staff members created from MerchantTeamScreen or accounts migrated in public.users)
    try {
      Map<String, dynamic>? publicUser = await safeSupabaseQuery(
        () => _supabase
            .from('users')
            .select()
            .ilike('email', cleanEmail)
            .limit(1)
            .maybeSingle(),
        fallback: () => null,
      );

      publicUser ??= await safeSupabaseQuery(
        () => _supabase
            .from('users')
            .select()
            .eq('phone', cleanInput)
            .limit(1)
            .maybeSingle(),
        fallback: () => null,
      );

      if (publicUser != null) {
        final permsRaw = publicUser['permissions'];
        Map<String, dynamic> perms = {};
        if (permsRaw is Map) {
          perms = Map<String, dynamic>.from(permsRaw);
        } else if (permsRaw is String) {
          try {
            perms = jsonDecode(permsRaw);
          } catch (_) {}
        }

        final storedPin = perms['temporary_pin']?.toString() ?? perms['password']?.toString();
        if (storedPin != null && storedPin.isNotEmpty && (storedPin == password || storedPin == cleanPass)) {
          return _mapSupabaseDataToUserModel(publicUser);
        }
      }
    } catch (e) {
      debugPrint('Fallback public.users login check warning: $e');
    }

    if (authError != null) {
      throw authError;
    }
    throw Exception('Invalid login credentials');
  }

  @override
  Future<UserModel> loginAsGuest() async {
    const guestUser = UserModel(
      id: 'guest_user',
      name: 'Guest User',
      nameAr: 'زائر',
      email: 'guest@magaiees.com',
      phone: '0000000000',
      role: UserRole.guest,
    );
    return guestUser;
  }

  @override
  Future<UserModel> register({
    required String name,
    required String nameAr,
    required String email,
    required String phone,
    required String password,
  }) async {
    final cleanEmail = email.trim().toLowerCase();
    // 1. Create user in Supabase Auth
    final response = await _supabase.auth.signUp(
      email: cleanEmail,
      password: password,
    );

    if (response.user == null) {
      throw Exception('Registration failed.');
    }

    // 2. Insert into public.users table
    final newUser = {
      'id': response.user!.id,
      'name': name,
      'name_ar': nameAr,
      'email': cleanEmail,
      'phone': phone,
      'role': 'buyer', // Default role for new signups
    };

    await safeSupabaseQuery(() => _supabase.from('users').upsert(newUser));

    // 3. Return mapped UserModel
    final createdUser = await safeSupabaseQuery(() => _supabase
        .from('users')
        .select()
        .eq('id', response.user!.id)
        .maybeSingle());

    return _mapSupabaseDataToUserModel(createdUser ?? newUser);
  }

  @override
  Future<void> logout() async {
    try {
      await _supabase.auth.signOut();
    } catch (_) {}
    await clearCache();
  }

  @override
  Future<UserModel> refreshSession() async {
    final cached = await getCachedUser();
    if (cached != null && (cached.role == UserRole.guest || cached.id.startsWith('staff_'))) {
      return cached;
    }

    try {
      await _supabase.auth.refreshSession();
    } catch (_) {}

    final session = _supabase.auth.currentSession;
    Map<String, dynamic>? userData;

    if (session != null) {
      try {
        userData = await safeSupabaseQuery(() => _supabase
            .from('users')
            .select()
            .eq('id', session.user.id)
            .maybeSingle());
      } catch (_) {}

      if (userData == null && session.user.email != null && session.user.email!.isNotEmpty) {
        try {
          userData = await safeSupabaseQuery(() => _supabase
              .from('users')
              .select()
              .ilike('email', session.user.email!.trim())
              .limit(1)
              .maybeSingle());
        } catch (_) {}
      }
    }

    if (userData == null && cached != null && cached.id.isNotEmpty) {
      try {
        userData = await safeSupabaseQuery(() => _supabase
            .from('users')
            .select()
            .eq('id', cached.id)
            .maybeSingle());
      } catch (_) {}
    }

    if (userData != null) {
      final updated = _mapSupabaseDataToUserModel(userData);
      await cacheUser(updated);
      return updated;
    }

    if (cached != null) {
      return cached;
    }

    throw Exception('Session expired');
  }

  @override
  Future<UserModel?> getCachedUser() async {
    final cachedData = _prefs.getString(_userCacheKey);
    if (cachedData != null) {
      try {
        final Map<String, dynamic> jsonMap = jsonDecode(cachedData);
        return UserModel.fromJson(jsonMap);
      } catch (e) {
        debugPrint('Error parsing cached user: $e');
      }
    }
    return null;
  }

  @override
  Future<void> cacheUser(UserModel user) async {
    await _prefs.setString(_userCacheKey, jsonEncode(user.toJson()));
  }

  @override
  Future<void> clearCache() async {
    await _prefs.remove(_userCacheKey);
  }

  // Helper method to map Supabase JSON to UserModel
  UserModel _mapSupabaseDataToUserModel(Map<String, dynamic> data) {
    UserRole role;
    switch (data['role']) {
      case 'admin':
        role = UserRole.admin;
        break;
      case 'merchant':
        role = UserRole.merchant;
        break;
      case 'employee':
        role = UserRole.employee;
        break;
      case 'guest':
        role = UserRole.guest;
        break;
      default:
        role = UserRole.buyer;
    }

    final permsData = data['permissions'];
    Map<String, dynamic> perms = {};
    if (permsData != null && permsData is Map) {
      perms = Map<String, dynamic>.from(permsData);
    } else if (permsData is String) {
      try {
        perms = jsonDecode(permsData);
      } catch (_) {}
    }

    final storeId = data['store_id']?.toString() ?? perms['store_id']?.toString();
    final storeName = data['store_name']?.toString() ?? perms['store_name']?.toString();
    final storeNameAr = data['store_name_ar']?.toString() ?? perms['store_name_ar']?.toString();

    return UserModel(
      id: data['id']?.toString() ?? '',
      name: data['name']?.toString() ?? 'User',
      nameAr: data['name_ar']?.toString(),
      email: data['email']?.toString() ?? '',
      phone: data['phone']?.toString() ?? '',
      role: role,
      hasStore: data['role'] == 'merchant',
      storeId: storeId,
      storeName: storeName,
      storeNameAr: storeNameAr,
      permissions: perms,
      merchantPoints: (num.tryParse(data['merchant_points']?.toString() ?? '0') ?? 0).toInt(),
      merchantTier: data['merchant_tier']?.toString() ?? 'beginner',
    );
  }
}
