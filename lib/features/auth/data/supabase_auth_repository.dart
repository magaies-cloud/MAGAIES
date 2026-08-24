import 'dart:convert';
import 'package:magaiees_marketplace/core/enums/user_role.dart';
import 'package:magaiees_marketplace/features/auth/domain/models/user_model.dart';
import 'package:magaiees_marketplace/features/auth/domain/repositories/auth_repository.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:supabase_flutter/supabase_flutter.dart';

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
    // 1. Authenticate with Supabase Auth
    final response = await _supabase.auth.signInWithPassword(
      email: email,
      password: password,
    );

    if (response.user == null) {
      throw Exception('Login failed: User not found.');
    }

    // 2. Fetch user profile from public.users table
    final userData = await _supabase
        .from('users')
        .select()
        .eq('id', response.user!.id)
        .single();

    return _mapSupabaseDataToUserModel(userData);
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
    // 1. Create user in Supabase Auth
    final response = await _supabase.auth.signUp(
      email: email,
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
      'email': email,
      'phone': phone,
      'role': 'buyer', // Default role for new signups
    };

    await _supabase.from('users').insert(newUser);

    // 3. Return mapped UserModel
    final createdUser = await _supabase
        .from('users')
        .select()
        .eq('id', response.user!.id)
        .single();

    return _mapSupabaseDataToUserModel(createdUser);
  }

  @override
  Future<void> logout() async {
    await _supabase.auth.signOut();
    await clearCache();
  }

  @override
  Future<UserModel> refreshSession() async {
    final session = _supabase.auth.currentSession;
    if (session == null || session.isExpired) {
      throw Exception('Session expired');
    }
    final userData = await _supabase
        .from('users')
        .select()
        .eq('id', session.user.id)
        .single();
    
    return _mapSupabaseDataToUserModel(userData);
  }

  @override
  Future<UserModel?> getCachedUser() async {
    final cachedData = _prefs.getString(_userCacheKey);
    if (cachedData != null) {
      try {
        final Map<String, dynamic> jsonMap = jsonDecode(cachedData);
        // We trust the cached user on startup to prevent aggressive logouts on refresh.
        // API calls will naturally fail if the token is truly expired.
        return UserModel.fromJson(jsonMap);
      } catch (e) {
        print('Error parsing cached user: $e');
        // Ignored, let it return null
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

    return UserModel(
      id: data['id'],
      name: data['name'] ?? '',
      nameAr: data['name_ar'],
      email: data['email'] ?? '',
      phone: data['phone'] ?? '',
      role: role,
      hasStore: data['role'] == 'merchant',
    );
  }
}
