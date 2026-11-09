/// Central utility for formatting order numbers uniformly across all modules
/// (Buyer, Merchant, Admin, Employee, Reports, and Notifications).
class OrderNumberFormatter {
  /// Formats an order number deterministically.
  /// If [orderNumber] is present and not empty, it is returned.
  /// Otherwise, returns an 8-digit readable format: YYMM#### (e.g. 26091234)
  /// calculated from the creation date and the order ID hash.
  static String format({
    required String? id,
    String? orderNumber,
    DateTime? createdAt,
    String? createdAtStr,
  }) {
    if (orderNumber != null && orderNumber.trim().isNotEmpty) {
      return orderNumber.trim();
    }

    DateTime date;
    if (createdAt != null) {
      date = createdAt;
    } else if (createdAtStr != null && createdAtStr.trim().isNotEmpty) {
      date = DateTime.tryParse(createdAtStr) ?? DateTime.now();
    } else {
      date = DateTime.now();
    }

    final idStr = id?.toString() ?? '';
    final yy = date.year.toString().length >= 2 ? date.year.toString().substring(2) : '26';
    final mm = date.month.toString().padLeft(2, '0');
    final seq = (idStr.hashCode.abs() % 9000 + 1001).toString();
    return '$yy$mm$seq';
  }

  /// Helper to format directly from an order Map<String, dynamic>.
  static String formatFromMap(Map<String, dynamic>? order) {
    if (order == null) return '';
    return format(
      id: order['id']?.toString(),
      orderNumber: order['order_number']?.toString(),
      createdAtStr: order['created_at']?.toString(),
    );
  }
}
