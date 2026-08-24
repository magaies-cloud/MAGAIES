import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/features/auth/application/auth_provider.dart';

class QuoteRequestScreen extends ConsumerStatefulWidget {
  const QuoteRequestScreen({super.key});

  @override
  ConsumerState<QuoteRequestScreen> createState() => _QuoteRequestScreenState();
}

class _QuoteRequestScreenState extends ConsumerState<QuoteRequestScreen> {
  final _formKey = GlobalKey<FormState>();
  final _materialTypeController = TextEditingController();
  final _quantityController = TextEditingController();
  final _dateController = TextEditingController();
  final _notesController = TextEditingController();
  bool _isLoading = false;

  @override
  void dispose() {
    _materialTypeController.dispose();
    _quantityController.dispose();
    _dateController.dispose();
    _notesController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (_formKey.currentState?.validate() ?? false) {
      setState(() => _isLoading = true);
      
      try {
        final user = ref.read(authNotifierProvider).valueOrNull;
        await Supabase.instance.client.from('quote_requests').insert({
          'buyer_id': user?.id,
          'material_type': _materialTypeController.text,
          'quantity': _quantityController.text,
          'delivery_date': _dateController.text,
          'notes': _notesController.text,
        });

        if (mounted) {
          final isRtl = Directionality.of(context) == TextDirection.rtl;
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(isRtl ? 'تم إرسال الطلب بنجاح' : 'Request submitted successfully'),
              backgroundColor: Colors.green,
            ),
          );
          _materialTypeController.clear();
          _quantityController.clear();
          _dateController.clear();
          _notesController.clear();
        }
      } catch (e) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text('Error: $e'),
              backgroundColor: Colors.red,
            ),
          );
        }
      }
      
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;

    return Scaffold(
      appBar: AppBar(
        title: Text(isRtl ? 'الوسيط الرقمي' : 'Digital Broker'),
      ),
      body: _isLoading 
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16.0),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Text(
                      isRtl ? 'طلب تسعيرة للكميات الكبيرة' : 'Request a Quote for Bulk Quantities',
                      style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      isRtl ? 'أدخل تفاصيل طلبك وسنقوم بتوفير أفضل عروض الأسعار لك.' : 'Enter the details of your request and we will provide the best quotes for you.',
                      style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: Colors.grey.shade700),
                    ),
                    const SizedBox(height: 24),
                    TextFormField(
                      controller: _materialTypeController,
                      decoration: InputDecoration(
                        labelText: isRtl ? 'نوع المادة' : 'Material Type',
                        border: const OutlineInputBorder(),
                        prefixIcon: const Icon(Icons.category),
                      ),
                      validator: (value) => value == null || value.isEmpty 
                          ? (isRtl ? 'الرجاء إدخال نوع المادة' : 'Please enter material type') 
                          : null,
                    ),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _quantityController,
                      keyboardType: TextInputType.number,
                      decoration: InputDecoration(
                        labelText: isRtl ? 'الكمية المقدرة' : 'Estimated Quantity',
                        border: const OutlineInputBorder(),
                        prefixIcon: const Icon(Icons.format_list_numbered),
                      ),
                      validator: (value) => value == null || value.isEmpty 
                          ? (isRtl ? 'الرجاء إدخال الكمية' : 'Please enter quantity') 
                          : null,
                    ),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _dateController,
                      decoration: InputDecoration(
                        labelText: isRtl ? 'تاريخ التسليم المطلوب' : 'Required Delivery Date',
                        border: const OutlineInputBorder(),
                        prefixIcon: const Icon(Icons.calendar_today),
                      ),
                      readOnly: true,
                      onTap: () async {
                        final date = await showDatePicker(
                          context: context,
                          initialDate: DateTime.now().add(const Duration(days: 1)),
                          firstDate: DateTime.now(),
                          lastDate: DateTime.now().add(const Duration(days: 365)),
                        );
                        if (date != null) {
                          _dateController.text = "${date.year}-${date.month.toString().padLeft(2, '0')}-${date.day.toString().padLeft(2, '0')}";
                        }
                      },
                      validator: (value) => value == null || value.isEmpty 
                          ? (isRtl ? 'الرجاء تحديد التاريخ' : 'Please select a date') 
                          : null,
                    ),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _notesController,
                      decoration: InputDecoration(
                        labelText: isRtl ? 'ملاحظات إضافية (اختياري)' : 'Additional Notes (Optional)',
                        border: const OutlineInputBorder(),
                        prefixIcon: const Icon(Icons.notes),
                      ),
                      maxLines: 4,
                    ),
                    const SizedBox(height: 32),
                    ElevatedButton(
                      onPressed: _submit,
                      style: ElevatedButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 16),
                      ),
                      child: Text(isRtl ? 'إرسال الطلب' : 'Submit Request', style: const TextStyle(fontSize: 16)),
                    ),
                  ],
                ),
              ),
            ),
    );
  }
}
