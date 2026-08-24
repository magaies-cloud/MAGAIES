import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:magaiees_marketplace/core/enums/user_role.dart';
import 'package:magaiees_marketplace/features/auth/domain/models/user_model.dart';
import 'package:magaiees_marketplace/features/auth/domain/repositories/auth_repository.dart';

/// Mock implementation of [AuthRepository] for development.
///
/// Simulates authentication with artificial delays and persists user in local storage.
class MockAuthRepository implements AuthRepository {
  UserModel? _cachedUser;
  static const String _userStorageKey = 'magaiees_authenticated_user_session';

  @override
  Future<UserModel> login({
    required String email,
    required String password,
  }) async {
    // Simulate network delay
    await Future.delayed(const Duration(milliseconds: 400));

    final normalized = email.trim().toLowerCase();

    // 1. Admin Role
    if (normalized.contains('admin') || normalized == '0912300105') {
      final user = UserModel(
        id: 'admin_001',
        name: 'Platform Admin',
        nameAr: 'مدير المنصة',
        email: email,
        phone: '+966500000000',
        role: UserRole.admin,
        hasStore: true,
        token: 'mock_admin_jwt_${DateTime.now().millisecondsSinceEpoch}',
        refreshToken: 'mock_admin_refresh_token',
      );
      await cacheUser(user);
      return user;
    }

    // 2. Merchant Role
    if (normalized.contains('merchant') || normalized.contains('seller') || normalized == 'test@magaiees.com' || normalized == '0912345678') {
      final user = UserModel(
        id: 'usr_merchant_001',
        name: 'Ahmed Al-Rashid',
        nameAr: 'أحمد الراشد',
        email: email,
        phone: '+966501234567',
        role: UserRole.merchant,
        hasStore: true,
        storeId: 'str_001',
        storeName: 'Al-Rashid Building Materials',
        storeNameAr: 'مواد البناء الراشد',
        token: 'mock_merchant_jwt_${DateTime.now().millisecondsSinceEpoch}',
        refreshToken: 'mock_merchant_refresh_token',
      );
      await cacheUser(user);
      return user;
    }

    // 3. Buyer Role (Default Customer)
    final user = UserModel(
      id: 'usr_buyer_001',
      name: 'Mohammed Ali',
      nameAr: 'محمد علي',
      email: email,
      phone: '+966509876543',
      role: UserRole.buyer,
      hasStore: false,
      token: 'mock_buyer_jwt_${DateTime.now().millisecondsSinceEpoch}',
      refreshToken: 'mock_buyer_refresh_token',
    );
    await cacheUser(user);
    return user;
  }

  @override
  Future<UserModel> loginAsGuest() async {
    await Future.delayed(const Duration(milliseconds: 300));
    final user = UserModel(
      id: 'guest_${DateTime.now().millisecondsSinceEpoch}',
      name: 'Guest User',
      nameAr: 'زائر',
      email: 'guest@magaiees.local',
      phone: '',
      role: UserRole.guest,
      hasStore: false,
      token: 'mock_guest_token_${DateTime.now().millisecondsSinceEpoch}',
      refreshToken: '',
    );
    await cacheUser(user);
    return user;
  }

  @override
  Future<UserModel> register({
    required String name,
    required String nameAr,
    required String email,
    required String phone,
    required String password,
  }) async {
    await Future.delayed(const Duration(milliseconds: 500));

    // New users start as buyers without a store
    final user = UserModel(
      id: 'usr_${DateTime.now().millisecondsSinceEpoch}',
      name: name,
      nameAr: nameAr,
      email: email,
      phone: phone,
      role: UserRole.buyer,
      hasStore: false,
      token: 'mock_jwt_token_${DateTime.now().millisecondsSinceEpoch}',
      refreshToken: 'mock_refresh_token',
    );

    await cacheUser(user);
    return user;
  }

  @override
  Future<void> logout() async {
    await clearCache();
  }

  @override
  Future<UserModel> refreshSession() async {
    final cached = await getCachedUser();
    if (cached == null) {
      throw Exception('No active session');
    }
    return cached;
  }

  @override
  Future<UserModel?> getCachedUser() async {
    if (_cachedUser != null) return _cachedUser;
    try {
      final prefs = await SharedPreferences.getInstance();
      final userString = prefs.getString(_userStorageKey);
      if (userString != null && userString.isNotEmpty) {
        final Map<String, dynamic> json = jsonDecode(userString);
        _cachedUser = UserModel.fromJson(json);
        return _cachedUser;
      }
    } catch (e) {
      print('Error restoring cached user: $e');
    }
    return null;
  }

  @override
  Future<void> cacheUser(UserModel user) async {
    _cachedUser = user;
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_userStorageKey, jsonEncode(user.toJson()));
    } catch (_) {}
  }

  @override
  Future<void> clearCache() async {
    _cachedUser = null;
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove(_userStorageKey);
    } catch (_) {}
  }
}
