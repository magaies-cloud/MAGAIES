import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'dart:math';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';
import 'package:magaiees_marketplace/core/utils/app_notification.dart';

class _QuoteItemData {
  final TextEditingController materialCtrl = TextEditingController();
  final TextEditingController qtyCtrl = TextEditingController();

  void dispose() {
    materialCtrl.dispose();
    qtyCtrl.dispose();
  }
}

class QuoteRequestScreen extends ConsumerStatefulWidget {
  const QuoteRequestScreen({super.key});

  @override
  ConsumerState<QuoteRequestScreen> createState() => _QuoteRequestScreenState();
}

class _QuoteRequestScreenState extends ConsumerState<QuoteRequestScreen> {
  final _formKey = GlobalKey<FormState>();
  final List<_QuoteItemData> _items = [_QuoteItemData()];
  final _dateController = TextEditingController();
  final _notesController = TextEditingController();
  bool _isLoading = false;

  @override
  void dispose() {
    for (var item in _items) {
      item.dispose();
    }
    _dateController.dispose();
    _notesController.dispose();
    super.dispose();
  }

  void _addItem() {
    setState(() {
      _items.add(_QuoteItemData());
    });
  }

  void _removeItem(int index) {
    setState(() {
      _items[index].dispose();
      _items.removeAt(index);
    });
  }

  Future<void> _submit() async {
    if (_formKey.currentState?.validate() ?? false) {
      setState(() => _isLoading = true);

      try {
        final user = ref.read(authNotifierProvider).valueOrNull;

        // Compile materials and quantities for overview columns
        final materialsList = _items.map((e) => e.materialCtrl.text.trim()).where((e) => e.isNotEmpty).toList();
        final qtyList = _items.map((e) => e.qtyCtrl.text.trim()).where((e) => e.isNotEmpty).toList();

        final materialsOverview = materialsList.join('، ');
        final qtyOverview = qtyList.join('، ');

        // Detailed notes breakdown
        final isRtl = Directionality.of(context) == TextDirection.rtl;
        final detailedItems = _items.asMap().entries.map((e) {
          final m = e.value.materialCtrl.text.trim();
          final q = e.value.qtyCtrl.text.trim();
          return isRtl ? "صنف ${e.key + 1}: $m (الكمية: $q)" : "Item ${e.key + 1}: $m (Qty: $q)";
        }).join('\n');

        // Generate Professional Ticket Number
        final now = DateTime.now();
        final yy = now.year.toString().substring(2);
        final mm = now.month.toString().padLeft(2, '0');
        final randomDigits = (1000 + Random().nextInt(9000)).toString();
        final ticketNumber = 'T$yy$mm$randomDigits';

        final userNotes = _notesController.text.trim();
        final finalNotes = "رقم الطلب (مرجع): $ticketNumber\n\n" + (userNotes.isNotEmpty
            ? "$userNotes\n\n-- ${isRtl ? 'تفاصيل الأصناف' : 'Items Breakdown'} --\n$detailedItems"
            : "-- ${isRtl ? 'تفاصيل الأصناف' : 'Items Breakdown'} --\n$detailedItems");

        await Supabase.instance.client.from('quote_requests').insert({
          'buyer_id': user?.id,
          'material_type': materialsOverview.length > 60 ? "${materialsOverview.substring(0, 57)}..." : materialsOverview,
          'quantity': qtyOverview.length > 40 ? "${qtyOverview.substring(0, 37)}..." : qtyOverview,
          'delivery_date': _dateController.text,
          'notes': finalNotes,
        });

        if (mounted) {
          _showSuccessDialog(isRtl, ticketNumber);
        }
      } catch (e) {
        if (mounted) {
          AppNotification.showError(context, 'Error: $e');
        }
      }

      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  void _showSuccessDialog(bool isRtl, String ticketNumber) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Column(
          children: [
            const Icon(Icons.check_circle_outline, color: Colors.green, size: 64),
            const SizedBox(height: 16),
            Text(
              isRtl ? 'تم إرسال الطلب بنجاح' : 'Request Sent Successfully',
              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 22),
              textAlign: TextAlign.center,
            ),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              isRtl
                  ? 'تم استلام طلبك لأسعار الجملة. سيقوم فريقنا بالتواصل معك قريباً لتوفير عروض تنافسية لمشروعك.'
                  : 'Your bulk quote request has been received. Our team will contact you shortly.',
              textAlign: TextAlign.center,
              style: const TextStyle(fontSize: 16),
            ),
            const SizedBox(height: 24),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              decoration: BoxDecoration(
                color: Colors.blue.shade50,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.blue.shade200),
              ),
              child: Column(
                children: [
                  Text(
                    isRtl ? 'رقم الطلب للمتابعة:' : 'Tracking Number:',
                    style: TextStyle(color: Colors.blue.shade900, fontSize: 14),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    ticketNumber,
                    style: TextStyle(color: Colors.blue.shade900, fontSize: 24, fontWeight: FontWeight.bold, letterSpacing: 2),
                  ),
                ],
              ),
            ),
          ],
        ),
        actionsAlignment: MainAxisAlignment.center,
        actions: [
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.blue.shade800,
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 12),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            onPressed: () {
              Navigator.pop(ctx); // Close dialog
              Navigator.pop(context); // Go back from screen
            },
            child: Text(isRtl ? 'حسناً، العودة' : 'OK, Go Back', style: const TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final primaryColor = Colors.blue.shade900;

    return Scaffold(
      backgroundColor: Colors.grey.shade50,
      appBar: AppBar(
        title: Text(isRtl ? 'الوسيط الرقمي - طلب تسعير' : 'Digital Broker Quote'),
        backgroundColor: Colors.white,
        elevation: 0,
        foregroundColor: Colors.black87,
        centerTitle: true,
      ),
      body: _isLoading
          ? Center(child: CircularProgressIndicator(color: primaryColor))
          : SingleChildScrollView(
              child: Column(
                children: [
                  // Beautiful Header (Compact)
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16),
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        colors: [primaryColor, Colors.blue.shade700],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: const BorderRadius.only(
                        bottomLeft: Radius.circular(20),
                        bottomRight: Radius.circular(20),
                      ),
                    ),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.center,
                      children: [
                        Container(
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.15),
                            shape: BoxShape.circle,
                          ),
                          child: const Icon(Icons.handshake_outlined, size: 32, color: Colors.amber),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                isRtl ? 'طلب تسعير المشاريع الكبيرة' : 'Bulk Quote Request',
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 18,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                isRtl
                                    ? 'أدخل الأصناف المطلوبة والكميات، سنقوم بتوفير أسعار تنافسية لك من شبكة موردينا المعتمدة.'
                                    : 'Enter required items and quantities, we will provide competitive prices from our network.',
                                style: TextStyle(color: Colors.white.withValues(alpha: 0.9), fontSize: 12),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),

                  // Form Area
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 12.0),
                    child: Form(
                      key: _formKey,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          Row(
                            children: [
                              Icon(Icons.inventory_2, color: primaryColor),
                              const SizedBox(width: 8),
                              Text(
                                isRtl ? 'قائمة الأصناف المطلوبة' : 'Required Items List',
                                style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                              ),
                            ],
                          ),
                          const SizedBox(height: 16),
                          
                          // Dynamic Items List
                          ..._items.asMap().entries.map((entry) {
                            int index = entry.key;
                            _QuoteItemData itemData = entry.value;

                            return Container(
                              margin: const EdgeInsets.only(bottom: 12),
                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(16),
                                boxShadow: [
                                  BoxShadow(
                                    color: Colors.grey.withValues(alpha: 0.1),
                                    blurRadius: 10,
                                    spreadRadius: 1,
                                    offset: const Offset(0, 4),
                                  ),
                                ],
                                border: Border.all(color: Colors.grey.shade200),
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      // Quantity (First in RTL so it shows up on the right)
                                      // Wait, in RTL, Row children start from right to left! 
                                      // So the first child is on the RIGHT.
                                      // User wants Quantity FIRST (on the right).
                                      Expanded(
                                        flex: 2,
                                        child: TextFormField(
                                          controller: itemData.qtyCtrl,
                                          keyboardType: TextInputType.number,
                                          decoration: InputDecoration(
                                            labelText: isRtl ? 'الكمية' : 'Qty',
                                            hintText: '0',
                                            filled: true,
                                            fillColor: Colors.grey.shade50,
                                            border: OutlineInputBorder(
                                              borderRadius: BorderRadius.circular(12),
                                              borderSide: BorderSide.none,
                                            ),
                                            contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                                          ),
                                          validator: (value) => value == null || value.trim().isEmpty
                                              ? (isRtl ? 'مطلوب' : 'Required')
                                              : null,
                                        ),
                                      ),
                                      const SizedBox(width: 8),
                                      // Material Name (Second in RTL so it shows up on the left)
                                      Expanded(
                                        flex: 5,
                                        child: TextFormField(
                                          controller: itemData.materialCtrl,
                                          decoration: InputDecoration(
                                            labelText: isRtl ? 'اسم الصنف' : 'Item Name',
                                            hintText: isRtl ? 'مثال: أسمنت' : 'e.g. Cement',
                                            filled: true,
                                            fillColor: Colors.grey.shade50,
                                            border: OutlineInputBorder(
                                              borderRadius: BorderRadius.circular(12),
                                              borderSide: BorderSide.none,
                                            ),
                                            contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                                            suffixIcon: index > 0 
                                              ? IconButton(
                                                  icon: const Icon(Icons.close, color: Colors.red, size: 20),
                                                  onPressed: () => _removeItem(index),
                                                  padding: EdgeInsets.zero,
                                                  constraints: const BoxConstraints(),
                                                )
                                              : null,
                                          ),
                                          validator: (value) => value == null || value.trim().isEmpty
                                              ? (isRtl ? 'مطلوب' : 'Required')
                                              : null,
                                        ),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                            );
                          }),

                          // Add Item Button
                          Align(
                            alignment: Alignment.center,
                            child: ElevatedButton.icon(
                              onPressed: _addItem,
                              icon: const Icon(Icons.add_circle_outline, size: 20),
                              label: Text(isRtl ? 'إضافة صنف جديد' : 'Add another item'),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: Colors.green.shade600,
                                foregroundColor: Colors.white,
                                elevation: 0,
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 10),
                              ),
                            ),
                          ),
                          
                          const SizedBox(height: 16),
                          const Divider(),
                          const SizedBox(height: 16),

                          // Additional Details
                          Row(
                            children: [
                              Icon(Icons.more_time_rounded, color: primaryColor),
                              const SizedBox(width: 8),
                              Text(
                                isRtl ? 'تفاصيل إضافية للطلب' : 'Additional Details',
                                style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                              ),
                            ],
                          ),
                          const SizedBox(height: 16),

                          TextFormField(
                            controller: _dateController,
                            readOnly: true,
                            decoration: InputDecoration(
                              labelText: isRtl ? 'تاريخ التسليم المتوقع' : 'Expected Delivery Date',
                              filled: true,
                              fillColor: Colors.white,
                              prefixIcon: Icon(Icons.calendar_month, color: primaryColor),
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                                borderSide: BorderSide(color: Colors.grey.shade300),
                              ),
                              enabledBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                                borderSide: BorderSide(color: Colors.grey.shade300),
                              ),
                              focusedBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                                borderSide: BorderSide(color: primaryColor, width: 2),
                              ),
                            ),
                            onTap: () async {
                              final date = await showDatePicker(
                                context: context,
                                initialDate: DateTime.now().add(const Duration(days: 1)),
                                firstDate: DateTime.now(),
                                lastDate: DateTime.now().add(const Duration(days: 365)),
                                builder: (context, child) {
                                  return Theme(
                                    data: Theme.of(context).copyWith(
                                      colorScheme: ColorScheme.light(
                                        primary: primaryColor, // header background color
                                        onPrimary: Colors.white, // header text color
                                        onSurface: Colors.black, // body text color
                                      ),
                                    ),
                                    child: child!,
                                  );
                                },
                              );
                              if (date != null) {
                                _dateController.text = "${date.year}-${date.month.toString().padLeft(2, '0')}-${date.day.toString().padLeft(2, '0')}";
                              }
                            },
                            validator: (value) => value == null || value.trim().isEmpty
                                ? (isRtl ? 'الرجاء تحديد تاريخ التسليم' : 'Please select a date')
                                : null,
                          ),
                          const SizedBox(height: 16),

                          TextFormField(
                            controller: _notesController,
                            maxLines: 4,
                            decoration: InputDecoration(
                              labelText: isRtl ? 'ملاحظات إضافية (اختياري)' : 'Additional Notes (Optional)',
                              hintText: isRtl ? 'أدخل أي ملاحظات عن المواصفات، مكان التسليم، إلخ...' : 'Any specs, delivery location, etc...',
                              filled: true,
                              fillColor: Colors.white,
                              alignLabelWithHint: true,
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                                borderSide: BorderSide(color: Colors.grey.shade300),
                              ),
                              enabledBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                                borderSide: BorderSide(color: Colors.grey.shade300),
                              ),
                              focusedBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                                borderSide: BorderSide(color: primaryColor, width: 2),
                              ),
                            ),
                          ),
                          
                          const SizedBox(height: 16),

                          // Submit Button
                          ElevatedButton(
                            onPressed: _submit,
                            style: ElevatedButton.styleFrom(
                              backgroundColor: Colors.amber, // Golden/Amber accent
                              foregroundColor: Colors.black87,
                              padding: const EdgeInsets.symmetric(vertical: 12),
                              elevation: 4,
                              shadowColor: Colors.amber.withValues(alpha: 0.5),
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(16),
                              ),
                            ),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                const Icon(Icons.send_rounded, size: 24),
                                const SizedBox(width: 12),
                                Text(
                                  isRtl ? 'تأكيد وإرسال الطلب' : 'Confirm & Submit Request',
                                  style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(height: 32),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
    );
  }
}
