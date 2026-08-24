import 'package:flutter/material.dart';
import 'package:intl/intl.dart' hide TextDirection;

class AdminOrdersScreen extends StatefulWidget {
  const AdminOrdersScreen({super.key});

  @override
  State<AdminOrdersScreen> createState() => _AdminOrdersScreenState();
}

class _AdminOrdersScreenState extends State<AdminOrdersScreen> {
  String _selectedFilter = 'all';
  String _searchQuery = '';
  final TextEditingController _searchCtrl = TextEditingController();

  // Comprehensive interactive orders dataset
  final List<Map<String, dynamic>> _orders = [
    {
      'id': 'ORD-9012',
      'customer': 'أحمد محمد عثمان',
      'phone': '+249 912 345 678',
      'merchant': 'شركة الأسعد للصلب (توريد مقاييس)',
      'items': [
        {'name': 'حديد تسليح الأسعد 16 ملم مشرشر', 'qty': 10, 'unit': 'طن', 'price': 3250000.0},
        {'name': 'حديد تسليح الأسعد 8 ملم كانات', 'qty': 2, 'unit': 'طن', 'price': 3350000.0},
      ],
      'amount': 39200000.0,
      'status': 'قيد المعالجة',
      'statusEn': 'Processing',
      'color': Colors.orange,
      'date': '2026-08-22 14:30',
      'type': 'processing',
      'address': 'الخرطوم - حي الرياض، شارع المشتل',
      'payment': 'تطبيق بنكك (Bankak)',
    },
    {
      'id': 'ORD-9013',
      'customer': 'فاطمة عبدالله التاج',
      'phone': '+249 923 456 789',
      'merchant': 'أسمنت عطبرة (توريد مقاييس)',
      'items': [
        {'name': 'أسمنت عطبرة بورتلاندي 50 كجم', 'qty': 200, 'unit': 'كيس', 'price': 28500.0},
      ],
      'amount': 5700000.0,
      'status': 'مشحون',
      'statusEn': 'Shipped',
      'color': Colors.blue,
      'date': '2026-08-22 11:15',
      'type': 'shipped',
      'address': 'أم درمان - الثورة الحارة العاشرة',
      'payment': 'تطبيق فوري (Fawry)',
    },
    {
      'id': 'ORD-9014',
      'customer': 'يوسف علي الريح',
      'phone': '+249 915 678 901',
      'merchant': 'رواد البناء والتشطيب',
      'items': [
        {'name': 'سيراميك رأس الخيمة 60×60 سم فاخر', 'qty': 80, 'unit': 'م²', 'price': 45000.0},
        {'name': 'غراء سيراميك ممتاز 20 كجم', 'qty': 15, 'unit': 'كيس', 'price': 18000.0},
      ],
      'amount': 3870000.0,
      'status': 'مكتمل',
      'statusEn': 'Delivered',
      'color': Colors.green,
      'date': '2026-08-21 16:40',
      'type': 'delivered',
      'address': 'بحري - كافوري، مربع 6',
      'payment': 'نقداً عند الاستلام',
    },
    {
      'id': 'ORD-9015',
      'customer': 'مريم حسين الدسوقي',
      'phone': '+249 918 765 432',
      'merchant': 'أدوات كهربائية الحديثة',
      'items': [
        {'name': 'أسلاك نحاس 4 ملم لفة كاملة', 'qty': 12, 'unit': 'لفة', 'price': 85000.0},
      ],
      'amount': 1020000.0,
      'status': 'ملغي',
      'statusEn': 'Cancelled',
      'color': Colors.red,
      'date': '2026-08-21 09:20',
      'type': 'cancelled',
      'address': 'الخرطوم بحري - شمبات الأراضي',
      'payment': 'تطبيق بنكك (Bankak)',
    },
    {
      'id': 'ORD-9016',
      'customer': 'عثمان طه الجزولي',
      'phone': '+249 911 223 344',
      'merchant': 'مصنع الألومنيوم الحديث',
      'items': [
        {'name': 'قطاعات ألومنيوم جامبو سرايا', 'qty': 25, 'unit': 'لوح', 'price': 120000.0},
      ],
      'amount': 3000000.0,
      'status': 'قيد المعالجة',
      'statusEn': 'Processing',
      'color': Colors.orange,
      'date': '2026-08-20 18:00',
      'type': 'processing',
      'address': 'أم درمان - المهندسين',
      'payment': 'تحويل بنكي مباشر',
    },
    {
      'id': 'ORD-9017',
      'customer': 'سارة إبراهيم خالد',
      'phone': '+249 919 888 777',
      'merchant': 'شركة مقاييس للتوريد المباشر',
      'items': [
        {'name': 'مواسير مجلفنة 2 بوصة ثقيل', 'qty': 40, 'unit': 'عود', 'price': 65000.0},
        {'name': 'زنك جملون سمك 0.5 ملم', 'qty': 50, 'unit': 'لوح', 'price': 82000.0},
      ],
      'amount': 6700000.0,
      'status': 'مكتمل',
      'statusEn': 'Delivered',
      'color': Colors.green,
      'date': '2026-08-19 13:20',
      'type': 'delivered',
      'address': 'سوبا - المنطقة الصناعية',
      'payment': 'تطبيق بنكك (Bankak)',
    },
  ];

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  List<Map<String, dynamic>> _filteredOrders() {
    return _orders.where((order) {
      // Filter tab
      final matchesFilter = _selectedFilter == 'all' || order['type'] == _selectedFilter;
      // Search query
      if (!matchesFilter) return false;
      if (_searchQuery.isEmpty) return true;
      final q = _searchQuery.toLowerCase();
      final id = (order['id'] as String).toLowerCase();
      final customer = (order['customer'] as String).toLowerCase();
      final merchant = (order['merchant'] as String).toLowerCase();
      final phone = (order['phone'] as String).toLowerCase();
      return id.contains(q) || customer.contains(q) || merchant.contains(q) || phone.contains(q);
    }).toList();
  }

  void _showOrderDetailsModal(Map<String, dynamic> order, bool isRtl) {
    final currencyFmt = NumberFormat.decimalPattern('ar');
    final itemsList = order['items'] as List<dynamic>;

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setDlgState) {
          return Directionality(
            textDirection: isRtl ? TextDirection.rtl : TextDirection.ltr,
            child: AlertDialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              title: Row(
                children: [
                  const Icon(Icons.receipt_long, color: Color(0xFF1E40AF)),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      '${isRtl ? "تفاصيل الطلب والفاتورة" : "Order Invoice"} #${order['id']}',
                      style: const TextStyle(fontWeight: FontWeight.bold),
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: (order['color'] as Color).withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      order['status'],
                      style: TextStyle(color: order['color'], fontWeight: FontWeight.bold, fontSize: 12),
                    ),
                  ),
                ],
              ),
              content: SizedBox(
                width: 600,
                child: SingleChildScrollView(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Customer & Shipping Info
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: Colors.grey.shade50,
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(color: Colors.grey.shade200),
                        ),
                        child: Column(
                          children: [
                            _infoRow(Icons.person, isRtl ? 'العميل' : 'Customer', order['customer']),
                            _infoRow(Icons.phone, isRtl ? 'الهاتف' : 'Phone', order['phone']),
                            _infoRow(Icons.location_on, isRtl ? 'عنوان التوصيل' : 'Address', order['address']),
                            _infoRow(Icons.storefront, isRtl ? 'التاجر / المورد' : 'Supplier', order['merchant']),
                            _infoRow(Icons.payment, isRtl ? 'طريقة الدفع' : 'Payment', order['payment']),
                            _infoRow(Icons.calendar_today, isRtl ? 'تاريخ الطلب' : 'Date', order['date']),
                          ],
                        ),
                      ),
                      const SizedBox(height: 16),

                      // Order Items Table
                      Text(
                        isRtl ? 'الأصناف المطلوبة والكميات:' : 'Order Items:',
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                      ),
                      const SizedBox(height: 8),
                      Container(
                        decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(color: Colors.grey.shade200),
                        ),
                        child: Table(
                          columnWidths: const {
                            0: FlexColumnWidth(4),
                            1: FlexColumnWidth(1.5),
                            2: FlexColumnWidth(2),
                            3: FlexColumnWidth(2),
                          },
                          children: [
                            TableRow(
                              decoration: BoxDecoration(color: Colors.grey.shade100),
                              children: [
                                _tableHeader(isRtl ? 'الصنف' : 'Item'),
                                _tableHeader(isRtl ? 'الكمية' : 'Qty'),
                                _tableHeader(isRtl ? 'السعر' : 'Price'),
                                _tableHeader(isRtl ? 'الإجمالي' : 'Subtotal'),
                              ],
                            ),
                            ...itemsList.map((item) {
                              final subtotal = (item['qty'] as num) * (item['price'] as num);
                              return TableRow(
                                children: [
                                  _tableCell(item['name']),
                                  _tableCell('${item['qty']} ${item['unit']}'),
                                  _tableCell('${currencyFmt.format(item['price'])} ج.س'),
                                  _tableCell('${currencyFmt.format(subtotal)} ج.س', isBold: true),
                                ],
                              );
                            }),
                          ],
                        ),
                      ),
                      const SizedBox(height: 12),

                      // Total Row
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                        decoration: BoxDecoration(
                          color: const Color(0xFF1E40AF).withValues(alpha: 0.08),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              isRtl ? 'إجمالي قيمة الطلب:' : 'Total Amount:',
                              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                            ),
                            Text(
                              '${currencyFmt.format(order['amount'])} ${isRtl ? "ج.س" : "SDG"}',
                              style: const TextStyle(
                                fontWeight: FontWeight.w900,
                                fontSize: 18,
                                color: Color(0xFF1E40AF),
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 18),

                      // Status Update Controls
                      Text(
                        isRtl ? 'تحديث حالة الطلب والشحن:' : 'Update Order Status:',
                        style: const TextStyle(fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 8),
                      Wrap(
                        spacing: 8,
                        children: [
                          _statusChip(order, 'قيد المعالجة', 'Processing', 'processing', Colors.orange, setDlgState),
                          _statusChip(order, 'مشحون', 'Shipped', 'shipped', Colors.blue, setDlgState),
                          _statusChip(order, 'مكتمل', 'Delivered', 'delivered', Colors.green, setDlgState),
                          _statusChip(order, 'ملغي', 'Cancelled', 'cancelled', Colors.red, setDlgState),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
              actions: [
                OutlinedButton.icon(
                  onPressed: () {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('🖨️ جاري إعداد وتجهيز الفاتورة للطباعة...'),
                        backgroundColor: Color(0xFF1E40AF),
                      ),
                    );
                  },
                  icon: const Icon(Icons.print, size: 18),
                  label: Text(isRtl ? 'طباعة الفاتورة' : 'Print Invoice'),
                ),
                FilledButton(
                  onPressed: () => Navigator.pop(ctx),
                  child: Text(isRtl ? 'حفظ وإغلاق' : 'Save & Close'),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _infoRow(IconData icon, String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3),
      child: Row(
        children: [
          Icon(icon, size: 16, color: Colors.grey.shade600),
          const SizedBox(width: 8),
          SizedBox(
            width: 110,
            child: Text('$label:', style: TextStyle(color: Colors.grey.shade600, fontSize: 12)),
          ),
          Expanded(
            child: Text(value, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 12)),
          ),
        ],
      ),
    );
  }

  Widget _tableHeader(String title) {
    return Padding(
      padding: const EdgeInsets.all(8.0),
      child: Text(
        title,
        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
        textAlign: TextAlign.center,
      ),
    );
  }

  Widget _tableCell(String text, {bool isBold = false}) {
    return Padding(
      padding: const EdgeInsets.all(8.0),
      child: Text(
        text,
        style: TextStyle(
          fontSize: 12,
          fontWeight: isBold ? FontWeight.bold : FontWeight.normal,
          color: isBold ? const Color(0xFF1E40AF) : Colors.black87,
        ),
        textAlign: TextAlign.center,
      ),
    );
  }

  Widget _statusChip(
    Map<String, dynamic> order,
    String statusAr,
    String statusEn,
    String type,
    Color color,
    StateSetter setDlgState,
  ) {
    final isSelected = order['status'] == statusAr;
    return ChoiceChip(
      label: Text(statusAr),
      selected: isSelected,
      selectedColor: color.withValues(alpha: 0.2),
      labelStyle: TextStyle(
        color: isSelected ? color : Colors.grey.shade700,
        fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
        fontSize: 12,
      ),
      onSelected: (_) {
        setState(() {
          order['status'] = statusAr;
          order['statusEn'] = statusEn;
          order['type'] = type;
          order['color'] = color;
        });
        setDlgState(() {});
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final currencyFmt = NumberFormat.decimalPattern('ar');
    final filtered = _filteredOrders();

    final processingCount = _orders.where((o) => o['type'] == 'processing').length;
    final shippedCount = _orders.where((o) => o['type'] == 'shipped').length;
    final deliveredCount = _orders.where((o) => o['type'] == 'delivered').length;
    final cancelledCount = _orders.where((o) => o['type'] == 'cancelled').length;

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      body: Padding(
        padding: const EdgeInsets.all(24.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      isRtl ? 'إدارة الطلبات والمبيعات' : 'Orders & Sales Management',
                      style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                            fontWeight: FontWeight.bold,
                            color: const Color(0xFF0F172A),
                          ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      isRtl ? 'متابعة تفاصيل الشحن، الفواتير، وتحديث حالة طلبات مواد البناء' : 'Track orders, invoices & logistics status',
                      style: TextStyle(color: Colors.grey.shade600, fontSize: 13),
                    ),
                  ],
                ),
                FilledButton.icon(
                  style: FilledButton.styleFrom(backgroundColor: const Color(0xFF059669)),
                  onPressed: () {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('📊 تم تصدير تقرير الطلبات بتنسيق Excel بنجاح'),
                        backgroundColor: Color(0xFF059669),
                      ),
                    );
                  },
                  icon: const Icon(Icons.file_download, size: 18),
                  label: Text(isRtl ? 'تصدير الطلبات (Excel)' : 'Export Orders'),
                ),
              ],
            ),
            const SizedBox(height: 20),

            // Stats Bar
            Row(
              children: [
                _statPill(Icons.all_inbox, isRtl ? 'الكل' : 'All', '${_orders.length}', Colors.grey.shade800),
                const SizedBox(width: 10),
                _statPill(Icons.pending_actions, isRtl ? 'قيد المعالجة' : 'Processing', '$processingCount', Colors.orange),
                const SizedBox(width: 10),
                _statPill(Icons.local_shipping, isRtl ? 'مشحون' : 'Shipped', '$shippedCount', Colors.blue),
                const SizedBox(width: 10),
                _statPill(Icons.check_circle, isRtl ? 'مكتمل' : 'Delivered', '$deliveredCount', Colors.green),
                const SizedBox(width: 10),
                _statPill(Icons.cancel, isRtl ? 'ملغي' : 'Cancelled', '$cancelledCount', Colors.red),
              ],
            ),
            const SizedBox(height: 18),

            // Search Bar
            TextField(
              controller: _searchCtrl,
              onChanged: (v) => setState(() => _searchQuery = v.trim()),
              decoration: InputDecoration(
                hintText: isRtl ? 'ابحث برقم الطلب، اسم العميل، التاجر، أو رقم الهاتف...' : 'Search by order #, customer, merchant, or phone...',
                prefixIcon: const Icon(Icons.search),
                suffixIcon: _searchQuery.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear),
                        onPressed: () {
                          _searchCtrl.clear();
                          setState(() => _searchQuery = '');
                        },
                      )
                    : null,
                filled: true,
                fillColor: Colors.white,
                contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: Colors.grey.shade300)),
                enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: Colors.grey.shade300)),
              ),
            ),
            const SizedBox(height: 14),

            // Filter Chips
            Wrap(
              spacing: 8.0,
              children: [
                _buildFilterChip('all', isRtl ? 'الكل (${_orders.length})' : 'All (${_orders.length})'),
                _buildFilterChip('processing', isRtl ? 'قيد المعالجة ($processingCount)' : 'Processing ($processingCount)'),
                _buildFilterChip('shipped', isRtl ? 'مشحون ($shippedCount)' : 'Shipped ($shippedCount)'),
                _buildFilterChip('delivered', isRtl ? 'مكتمل ($deliveredCount)' : 'Delivered ($deliveredCount)'),
                _buildFilterChip('cancelled', isRtl ? 'ملغي ($cancelledCount)' : 'Cancelled ($cancelledCount)'),
              ],
            ),
            const SizedBox(height: 16),

            // Orders Table
            Expanded(
              child: Container(
                width: double.infinity,
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: Colors.grey.shade200),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.03),
                      blurRadius: 10,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: filtered.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(Icons.inbox, size: 48, color: Colors.grey.shade400),
                            const SizedBox(height: 8),
                            Text(isRtl ? 'لا توجد طلبات مطابقة للبحث' : 'No matching orders found', style: TextStyle(color: Colors.grey.shade500)),
                          ],
                        ),
                      )
                    : Scrollbar(
                        child: SingleChildScrollView(
                          scrollDirection: Axis.horizontal,
                          child: ConstrainedBox(
                            constraints: BoxConstraints(minWidth: MediaQuery.of(context).size.width - 140),
                            child: DataTable(
                              headingRowColor: WidgetStatePropertyAll(Colors.grey.shade50),
                              headingTextStyle: TextStyle(fontWeight: FontWeight.bold, color: Colors.grey.shade800, fontSize: 13),
                              columnSpacing: 18,
                              horizontalMargin: 16,
                              dataRowMinHeight: 60,
                              dataRowMaxHeight: 70,
                              columns: [
                                DataColumn(label: Text(isRtl ? 'رقم الطلب' : 'Order #')),
                                DataColumn(label: Text(isRtl ? 'العميل' : 'Customer Name')),
                                DataColumn(label: Text(isRtl ? 'التاجر / المورد' : 'Merchant')),
                                DataColumn(label: Text(isRtl ? 'المبلغ الإجمالي' : 'Total Amount')),
                                DataColumn(label: Text(isRtl ? 'طريقة الدفع' : 'Payment')),
                                DataColumn(label: Text(isRtl ? 'الحالة' : 'Status')),
                                DataColumn(label: Text(isRtl ? 'التاريخ' : 'Date')),
                                DataColumn(label: Text(isRtl ? 'الإجراءات' : 'Actions')),
                              ],
                              rows: filtered.map((order) {
                                return DataRow(
                                  cells: [
                                    DataCell(Text(order['id'], style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF1E40AF)))),
                                    DataCell(Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      children: [
                                        Text(order['customer'], style: const TextStyle(fontWeight: FontWeight.w600)),
                                        Text(order['phone'], style: TextStyle(fontSize: 11, color: Colors.grey.shade500)),
                                      ],
                                    )),
                                    DataCell(Text(order['merchant'], style: TextStyle(color: Colors.grey.shade800, fontSize: 12))),
                                    DataCell(Text('${currencyFmt.format(order['amount'])} ج.س', style: const TextStyle(fontWeight: FontWeight.bold))),
                                    DataCell(Text(order['payment'], style: TextStyle(fontSize: 12, color: Colors.grey.shade700))),
                                    DataCell(
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                        decoration: BoxDecoration(
                                          color: (order['color'] as Color).withValues(alpha: 0.12),
                                          borderRadius: BorderRadius.circular(12),
                                          border: Border.all(color: (order['color'] as Color).withValues(alpha: 0.4)),
                                        ),
                                        child: Text(
                                          isRtl ? order['status'] : order['statusEn'],
                                          style: TextStyle(
                                            color: order['color'],
                                            fontSize: 12,
                                            fontWeight: FontWeight.bold,
                                          ),
                                        ),
                                      ),
                                    ),
                                    DataCell(Text(order['date'], style: TextStyle(fontSize: 12, color: Colors.grey.shade600))),
                                    DataCell(
                                      Row(
                                        mainAxisSize: MainAxisSize.min,
                                        children: [
                                          IconButton(
                                            icon: const Icon(Icons.visibility, color: Color(0xFF1E40AF), size: 20),
                                            onPressed: () => _showOrderDetailsModal(order, isRtl),
                                            tooltip: isRtl ? 'عرض الفاتورة والتفاصيل' : 'View Details',
                                          ),
                                          IconButton(
                                            icon: const Icon(Icons.edit_note, color: Colors.orange, size: 22),
                                            onPressed: () => _showOrderDetailsModal(order, isRtl),
                                            tooltip: isRtl ? 'تحديث الحالة' : 'Update Status',
                                          ),
                                        ],
                                      ),
                                    ),
                                  ],
                                );
                              }).toList(),
                            ),
                          ),
                        ),
                      ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _statPill(IconData icon, String label, String value, Color color) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        decoration: BoxDecoration(
          color: color.withValues(alpha: 0.08),
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: color.withValues(alpha: 0.2)),
        ),
        child: Row(
          children: [
            Icon(icon, color: color, size: 18),
            const SizedBox(width: 8),
            Text(label, style: TextStyle(color: Colors.grey.shade700, fontSize: 12)),
            const Spacer(),
            Text(value, style: TextStyle(fontWeight: FontWeight.bold, color: color, fontSize: 14)),
          ],
        ),
      ),
    );
  }

  Widget _buildFilterChip(String id, String label) {
    return ChoiceChip(
      label: Text(label),
      selected: _selectedFilter == id,
      selectedColor: const Color(0xFF1E40AF).withValues(alpha: 0.15),
      labelStyle: TextStyle(
        color: _selectedFilter == id ? const Color(0xFF1E40AF) : Colors.grey.shade700,
        fontWeight: _selectedFilter == id ? FontWeight.bold : FontWeight.normal,
      ),
      onSelected: (selected) {
        if (selected) {
          setState(() {
            _selectedFilter = id;
          });
        }
      },
    );
  }
}
