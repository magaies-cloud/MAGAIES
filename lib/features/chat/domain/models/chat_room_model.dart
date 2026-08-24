class ChatRoomModel {
  final String id;
  final String buyerId;
  final String merchantId;
  final String? storeId;
  final String? lastMessage;
  final DateTime? lastMessageAt;
  final DateTime? createdAt;

  // Display helpers (populated after join or manually)
  final String? otherUserName;
  final String? storeName;
  final int unreadCount;

  const ChatRoomModel({
    required this.id,
    required this.buyerId,
    required this.merchantId,
    this.storeId,
    this.lastMessage,
    this.lastMessageAt,
    this.createdAt,
    this.otherUserName,
    this.storeName,
    this.unreadCount = 0,
  });

  factory ChatRoomModel.fromJson(Map<String, dynamic> json) {
    return ChatRoomModel(
      id: json['id']?.toString() ?? '',
      buyerId: json['buyer_id']?.toString() ?? '',
      merchantId: json['merchant_id']?.toString() ?? '',
      storeId: json['store_id']?.toString(),
      lastMessage: json['last_message']?.toString(),
      lastMessageAt: json['last_message_at'] != null
          ? DateTime.tryParse(json['last_message_at'].toString())
          : null,
      createdAt: json['created_at'] != null
          ? DateTime.tryParse(json['created_at'].toString())
          : null,
    );
  }

  ChatRoomModel copyWith({
    String? otherUserName,
    String? storeName,
    int? unreadCount,
    String? lastMessage,
    DateTime? lastMessageAt,
  }) {
    return ChatRoomModel(
      id: id,
      buyerId: buyerId,
      merchantId: merchantId,
      storeId: storeId,
      lastMessage: lastMessage ?? this.lastMessage,
      lastMessageAt: lastMessageAt ?? this.lastMessageAt,
      createdAt: createdAt,
      otherUserName: otherUserName ?? this.otherUserName,
      storeName: storeName ?? this.storeName,
      unreadCount: unreadCount ?? this.unreadCount,
    );
  }
}
