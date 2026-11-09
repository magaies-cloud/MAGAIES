import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/core/network/supabase_safe_query.dart';

class QuoteRequestModel {
  final String id;
  final String? buyerId;
  final String materialType;
  final String quantity;
  final String? deliveryDate;
  final String notes;
  final String status;
  final DateTime? createdAt;
  final String buyerName;
  final String buyerPhone;
  final String ticketNumber;
  final double dealValue;
  final double commissionValue;
  final String? assignedSupplier;
  final List<String> itemsBreakdown;

  QuoteRequestModel({
    required this.id,
    this.buyerId,
    required this.materialType,
    required this.quantity,
    this.deliveryDate,
    required this.notes,
    required this.status,
    this.createdAt,
    required this.buyerName,
    required this.buyerPhone,
    required this.ticketNumber,
    required this.dealValue,
    required this.commissionValue,
    this.assignedSupplier,
    required this.itemsBreakdown,
  });

  factory QuoteRequestModel.fromMap(
    Map<String, dynamic> map,
    Map<String, dynamic>? buyer,
  ) {
    final rawNotes = map['notes']?.toString() ?? '';
    final id = map['id']?.toString() ?? '';
    final shortId = id.length > 6 ? id.substring(0, 6).toUpperCase() : id;

    // Extract ticket number
    final ticketRegex = RegExp(r'رقم الطلب \(مرجع\):\s*([^\n\r]+)');
    final ticketMatch = ticketRegex.firstMatch(rawNotes);
    final ticketNumber = ticketMatch?.group(1)?.trim() ?? 'T26$shortId';

    // Extract deal value
    final dealRegex = RegExp(r'قيمة الصفقة التقديرية:\s*([\d,.]+)');
    final dealMatch = dealRegex.firstMatch(rawNotes);
    final dealValue = dealMatch != null
        ? (double.tryParse(dealMatch.group(1)!.replaceAll(',', '')) ?? 0.0)
        : 0.0;

    // Extract commission
    final commRegex = RegExp(r'عمولة الوسيط الرقمي:\s*([\d,.]+)');
    final commMatch = commRegex.firstMatch(rawNotes);
    final commissionValue = commMatch != null
        ? (double.tryParse(commMatch.group(1)!.replaceAll(',', '')) ?? 0.0)
        : 0.0;

    // Extract assigned supplier / advertiser
    final supplierRegex = RegExp(r'المورد المعلن المعتمد:\s*([^\n\r]+)');
    final supplierMatch = supplierRegex.firstMatch(rawNotes);
    final assignedSupplier = supplierMatch?.group(1)?.trim();

    // Extract items breakdown
    final items = <String>[];
    final lines = rawNotes.split('\n');
    for (var line in lines) {
      final trimmed = line.trim();
      if (trimmed.startsWith('صنف ') || trimmed.startsWith('Item ')) {
        items.add(trimmed);
      }
    }

    final buyerNameAr = buyer?['name_ar']?.toString();
    final buyerNameEn = buyer?['name']?.toString();
    final bName = (buyerNameAr != null && buyerNameAr.isNotEmpty)
        ? buyerNameAr
        : (buyerNameEn != null && buyerNameEn.isNotEmpty ? buyerNameEn : 'عميل مقاييس');

    final bPhone = buyer?['phone']?.toString() ?? 'غير متوفر';

    DateTime? created;
    if (map['created_at'] != null) {
      created = DateTime.tryParse(map['created_at'].toString())?.toLocal();
    }

    return QuoteRequestModel(
      id: id,
      buyerId: map['buyer_id']?.toString(),
      materialType: map['material_type']?.toString() ?? 'مواد وتوريدات عامة',
      quantity: map['quantity']?.toString() ?? '-',
      deliveryDate: map['delivery_date']?.toString(),
      notes: rawNotes,
      status: map['status']?.toString() ?? 'pending',
      createdAt: created,
      buyerName: bName,
      buyerPhone: bPhone,
      ticketNumber: ticketNumber,
      dealValue: dealValue,
      commissionValue: commissionValue,
      assignedSupplier: assignedSupplier,
      itemsBreakdown: items,
    );
  }
}

class DigitalBrokerSupplier {
  final String id;
  final String name;
  final String type; // 'store', 'fleet', 'engineering', 'ad'
  final String phone;
  final String location;
  final double rating;

  DigitalBrokerSupplier({
    required this.id,
    required this.name,
    required this.type,
    required this.phone,
    required this.location,
    required this.rating,
  });
}

class AdminDigitalBrokerInsights {
  final int totalRequests;
  final int pendingRequests;
  final int contactedRequests;
  final int closedRequests;
  final double totalEstimatedDealVolume;
  final double totalBrokerageCommissions;
  final int accreditedSuppliersCount;
  final List<QuoteRequestModel> requests;
  final List<DigitalBrokerSupplier> suppliers;

  AdminDigitalBrokerInsights({
    required this.totalRequests,
    required this.pendingRequests,
    required this.contactedRequests,
    required this.closedRequests,
    required this.totalEstimatedDealVolume,
    required this.totalBrokerageCommissions,
    required this.accreditedSuppliersCount,
    required this.requests,
    required this.suppliers,
  });
}

final adminDigitalBrokerProvider =
    AsyncNotifierProvider<AdminDigitalBrokerNotifier, AdminDigitalBrokerInsights>(() {
  return AdminDigitalBrokerNotifier();
});

class AdminDigitalBrokerNotifier extends AsyncNotifier<AdminDigitalBrokerInsights> {
  @override
  Future<AdminDigitalBrokerInsights> build() async {
    return _fetchData();
  }

  Future<AdminDigitalBrokerInsights> _fetchData() async {
    final supabase = Supabase.instance.client;

    final results = await Future.wait([
      safeSupabaseQuery(
        () => supabase
            .from('quote_requests')
            .select()
            .order('created_at', ascending: false),
        fallback: () => <dynamic>[],
      ),
      safeSupabaseQuery(
        () => supabase.from('users').select('id, name, name_ar, phone'),
        fallback: () => <dynamic>[],
      ),
      safeSupabaseQuery(
        () => supabase.from('stores').select('id, name, name_ar, owner_id'),
        fallback: () => <dynamic>[],
      ),
      safeSupabaseQuery(
        () => supabase
            .from('service_providers')
            .select('id, name, name_ar, trade_ar, category_id, phone, location_ar, rating'),
        fallback: () => <dynamic>[],
      ),
      safeSupabaseQuery(
        () => supabase.from('ads').select('id, title, company, price'),
        fallback: () => <dynamic>[],
      ),
    ]);

    final rawQuotes = List<Map<String, dynamic>>.from(results[0]);
    final rawUsers = List<Map<String, dynamic>>.from(results[1]);
    final rawStores = List<Map<String, dynamic>>.from(results[2]);
    final rawProviders = List<Map<String, dynamic>>.from(results[3]);
    final rawAds = List<Map<String, dynamic>>.from(results[4]);

    final Map<String, Map<String, dynamic>> userMap = {
      for (var u in rawUsers) u['id'].toString(): u
    };

    final List<DigitalBrokerSupplier> suppliersList = [];

    // 1. Stores
    for (var s in rawStores) {
      final nameAr = s['name_ar']?.toString();
      final nameEn = s['name']?.toString();
      final title = (nameAr != null && nameAr.isNotEmpty)
          ? nameAr
          : (nameEn != null && nameEn.isNotEmpty ? nameEn : 'متجر معتمد');
      final ownerId = s['owner_id']?.toString() ?? '';
      final ownerUser = userMap[ownerId];
      suppliersList.add(DigitalBrokerSupplier(
        id: s['id'].toString(),
        name: title,
        type: 'store',
        phone: ownerUser?['phone']?.toString() ?? '+249 91 234 0000',
        location: 'الخرطوم - السجانة',
        rating: 4.9,
      ));
    }

    // 2. Logistics & Engineering Providers
    for (var p in rawProviders) {
      final nameAr = p['name_ar']?.toString();
      final nameEn = p['name']?.toString() ?? 'مزود خدمة';
      final title = (nameAr != null && nameAr.isNotEmpty) ? nameAr : nameEn;
      final cat = p['category_id']?.toString() ?? '';
      suppliersList.add(DigitalBrokerSupplier(
        id: p['id'].toString(),
        name: title,
        type: cat == 'logistics' ? 'fleet' : 'engineering',
        phone: p['phone']?.toString() ?? '+249 91 234 5678',
        location: p['location_ar']?.toString() ?? 'الخرطوم',
        rating: (p['rating'] as num?)?.toDouble() ?? 4.8,
      ));
    }

    // 3. Advertisers
    for (var a in rawAds) {
      final comp = a['company']?.toString() ?? a['title']?.toString() ?? 'شركة معلنة';
      if (!suppliersList.any((s) => s.name == comp)) {
        suppliersList.add(DigitalBrokerSupplier(
          id: a['id'].toString(),
          name: comp,
          type: 'ad',
          phone: '+249 91 000 0000',
          location: 'السودان',
          rating: 4.7,
        ));
      }
    }

    final List<QuoteRequestModel> quoteModels = [];
    int pendingCount = 0;
    int contactedCount = 0;
    int closedCount = 0;
    double totalDeals = 0.0;
    double totalComms = 0.0;

    for (var q in rawQuotes) {
      final buyer = q['buyer_id'] != null ? userMap[q['buyer_id'].toString()] : null;
      final model = QuoteRequestModel.fromMap(q, buyer);
      quoteModels.add(model);

      if (model.status == 'closed') {
        closedCount++;
      } else if (model.status == 'contacted') {
        contactedCount++;
      } else {
        pendingCount++;
      }

      totalDeals += model.dealValue;
      totalComms += model.commissionValue;
    }

    return AdminDigitalBrokerInsights(
      totalRequests: quoteModels.length,
      pendingRequests: pendingCount,
      contactedRequests: contactedCount,
      closedRequests: closedCount,
      totalEstimatedDealVolume: totalDeals,
      totalBrokerageCommissions: totalComms,
      accreditedSuppliersCount: suppliersList.length,
      requests: quoteModels,
      suppliers: suppliersList,
    );
  }

  Future<void> updateRequestStatus(String id, String newStatus) async {
    state = const AsyncValue.loading();
    try {
      await Supabase.instance.client
          .from('quote_requests')
          .update({'status': newStatus})
          .eq('id', id);
      state = AsyncValue.data(await _fetchData());
    } catch (e, st) {
      state = AsyncValue.error(e, st);
    }
  }

  Future<void> saveDealDetails({
    required String id,
    required String status,
    required double dealValue,
    required double commissionPercent,
    required String assignedSupplier,
    String? additionalNotes,
  }) async {
    state = const AsyncValue.loading();
    try {
      final existingRes = await Supabase.instance.client
          .from('quote_requests')
          .select('notes')
          .eq('id', id)
          .maybeSingle();

      String rawNotes = existingRes?['notes']?.toString() ?? '';

      final cleanNotesIndex = rawNotes.indexOf('-- بيانات الوساطة المالية --');
      if (cleanNotesIndex != -1) {
        rawNotes = rawNotes.substring(0, cleanNotesIndex).trim();
      }

      final commAmount = (dealValue * commissionPercent) / 100.0;
      final fDeal = dealValue > 0 ? dealValue.toStringAsFixed(0) : '0';
      final fComm = commAmount > 0 ? commAmount.toStringAsFixed(0) : '0';

      final financialSection = '''

-- بيانات الوساطة المالية --
قيمة الصفقة التقديرية: $fDeal
عمولة الوسيط الرقمي: $fComm ($commissionPercent%)
المورد المعلن المعتمد: $assignedSupplier''';

      final userExtra = (additionalNotes != null && additionalNotes.trim().isNotEmpty)
          ? '\nملاحظات الوسيط: ${additionalNotes.trim()}'
          : '';

      final updatedNotes = '$rawNotes$financialSection$userExtra'.trim();

      await Supabase.instance.client.from('quote_requests').update({
        'status': status,
        'notes': updatedNotes,
      }).eq('id', id);

      state = AsyncValue.data(await _fetchData());
    } catch (e, st) {
      state = AsyncValue.error(e, st);
    }
  }

  Future<void> deleteRequest(String id) async {
    state = const AsyncValue.loading();
    try {
      await Supabase.instance.client
          .from('quote_requests')
          .delete()
          .eq('id', id);
      state = AsyncValue.data(await _fetchData());
    } catch (e, st) {
      state = AsyncValue.error(e, st);
    }
  }
}
