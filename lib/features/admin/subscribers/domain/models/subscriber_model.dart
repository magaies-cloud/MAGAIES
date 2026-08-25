import 'package:magaiees_marketplace/core/enums/user_role.dart';

enum SubscriberStatus { active, suspended, pendingVerification }

class SubscriberModel {
  final String id;
  final String name;
  final String nameAr;
  final String email;
  final String phone;
  final UserRole role;
  final SubscriberStatus status;
  final bool isVerified;
  final DateTime joinedAt;
  final String? storeName;
  final String? storeNameAr;
  final String? documentType;
  final int totalOrders;
  final double totalSpent;
  final Map<String, dynamic> permissions;
  final int merchantPoints;
  final String merchantTier;
  final bool directPaymentEnabled;
  final String? bankName;
  final String? bankAccountName;
  final String? bankAccountNumber;

  const SubscriberModel({
    required this.id,
    required this.name,
    required this.nameAr,
    required this.email,
    required this.phone,
    required this.role,
    required this.status,
    required this.isVerified,
    required this.joinedAt,
    this.storeName,
    this.storeNameAr,
    this.documentType,
    this.totalOrders = 0,
    this.totalSpent = 0.0,
    this.permissions = const {},
    this.merchantPoints = 0,
    this.merchantTier = 'beginner',
    this.directPaymentEnabled = false,
    this.bankName,
    this.bankAccountName,
    this.bankAccountNumber,
  });

  SubscriberModel copyWith({
    String? id,
    String? name,
    String? nameAr,
    String? email,
    String? phone,
    UserRole? role,
    SubscriberStatus? status,
    bool? isVerified,
    DateTime? joinedAt,
    String? storeName,
    String? storeNameAr,
    String? documentType,
    int? totalOrders,
    double? totalSpent,
    Map<String, dynamic>? permissions,
    int? merchantPoints,
    String? merchantTier,
    bool? directPaymentEnabled,
    String? bankName,
    String? bankAccountName,
    String? bankAccountNumber,
  }) {
    return SubscriberModel(
      id: id ?? this.id,
      name: name ?? this.name,
      nameAr: nameAr ?? this.nameAr,
      email: email ?? this.email,
      phone: phone ?? this.phone,
      role: role ?? this.role,
      status: status ?? this.status,
      isVerified: isVerified ?? this.isVerified,
      joinedAt: joinedAt ?? this.joinedAt,
      storeName: storeName ?? this.storeName,
      storeNameAr: storeNameAr ?? this.storeNameAr,
      documentType: documentType ?? this.documentType,
      totalOrders: totalOrders ?? this.totalOrders,
      totalSpent: totalSpent ?? this.totalSpent,
      permissions: permissions ?? this.permissions,
      merchantPoints: merchantPoints ?? this.merchantPoints,
      merchantTier: merchantTier ?? this.merchantTier,
      directPaymentEnabled: directPaymentEnabled ?? this.directPaymentEnabled,
      bankName: bankName ?? this.bankName,
      bankAccountName: bankAccountName ?? this.bankAccountName,
      bankAccountNumber: bankAccountNumber ?? this.bankAccountNumber,
    );
  }
}
