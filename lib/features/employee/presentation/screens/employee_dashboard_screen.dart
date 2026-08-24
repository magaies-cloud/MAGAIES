import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:magaiees_marketplace/features/employee/application/employee_orders_provider.dart';

class EmployeeDashboardScreen extends ConsumerWidget {
  const EmployeeDashboardScreen({super.key});

  void _showNewOrderDialog(BuildContext context, WidgetRef ref, OrderModel order) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => AlertDialog(
        backgroundColor: const Color(0xFF1E293B),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        title: const Column(
          children: [
            Icon(Icons.directions_car, size: 64, color: Colors.blueAccent),
            SizedBox(height: 16),
            Text('طلب جديد متاح!', 
              style: TextStyle(color: Colors.white, fontSize: 24, fontWeight: FontWeight.bold),
              textAlign: TextAlign.center,
            ),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text('قيمة الطلب: ${order.totalSdg} ج.س', style: const TextStyle(color: Colors.amber, fontSize: 20, fontWeight: FontWeight.bold)),
            const SizedBox(height: 12),
            Text('العنوان: ${order.address}', style: const TextStyle(color: Colors.white70, fontSize: 16), textAlign: TextAlign.center),
            const SizedBox(height: 24),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: [
                FilledButton.tonal(
                  style: FilledButton.styleFrom(
                    backgroundColor: Colors.red.withValues(alpha: 0.2),
                    foregroundColor: Colors.redAccent,
                    padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                  ),
                  onPressed: () {
                    ref.read(employeeOrdersProvider.notifier).rejectOrder(order.id);
                    Navigator.of(ctx, rootNavigator: true).pop();
                  },
                  child: const Text('رفض', style: TextStyle(fontSize: 18)),
                ),
                FilledButton(
                  style: FilledButton.styleFrom(
                    backgroundColor: Colors.blueAccent,
                    padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                  ),
                  onPressed: () async {
                    Navigator.of(ctx, rootNavigator: true).pop(); // Close dialog first
                    final success = await ref.read(employeeOrdersProvider.notifier).acceptOrder(order.id);
                    if (!context.mounted) return;
                    
                    if (success) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('مبروك! تم استلام الطلب بنجاح.'), backgroundColor: Colors.green),
                      );
                    } else {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('عذراً، موظف آخر قام بقبول الطلب قبلك!'), backgroundColor: Colors.orange),
                      );
                    }
                  },
                  child: const Text('قبول الطلب', style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(employeeOrdersProvider);
    
    // Listen for new pending orders to show dialog
    ref.listen(employeeOrdersProvider, (prev, next) {
      if (next.pendingOrders.isNotEmpty) {
        final prevIds = prev?.pendingOrders.map((e) => e.id).toSet() ?? {};
        for (var order in next.pendingOrders) {
          if (!prevIds.contains(order.id)) {
            WidgetsBinding.instance.addPostFrameCallback((_) {
              _showNewOrderDialog(context, ref, order);
            });
            break; 
          }
        }
      }
    });

    return Directionality(
      textDirection: TextDirection.rtl,
      child: Scaffold(
        backgroundColor: const Color(0xFF0F172A), // Deep Slate
        appBar: AppBar(
          title: const Text('رادار مقاييس للموظفين', style: TextStyle(fontWeight: FontWeight.bold, color: Colors.white)),
          backgroundColor: const Color(0xFF0F172A),
          elevation: 0,
          actions: [
            Container(
              margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
              decoration: BoxDecoration(
                color: state.isAvailable ? Colors.green.withValues(alpha: 0.2) : Colors.red.withValues(alpha: 0.2),
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: state.isAvailable ? Colors.green : Colors.red),
              ),
              child: Row(
                children: [
                  Icon(state.isAvailable ? Icons.sensors : Icons.sensors_off, size: 16, color: state.isAvailable ? Colors.green : Colors.red),
                  const SizedBox(width: 8),
                  Text(state.isAvailable ? 'مُتصل' : 'غير مُتصل', style: TextStyle(color: state.isAvailable ? Colors.green : Colors.red, fontWeight: FontWeight.bold)),
                ],
              ),
            )
          ],
        ),
        body: Column(
          children: [
            // Quick Stats Bar
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
              child: Row(
                children: [
                  Expanded(child: _StatCard(icon: Icons.checklist, title: 'الطلبات المنجزة', value: state.completedOrders.length.toString(), color: Colors.blueAccent)),
                  const SizedBox(width: 12),
                  Expanded(child: _StatCard(icon: Icons.pending_actions, title: 'قيد التنفيذ', value: state.activeOrders.length.toString(), color: Colors.orangeAccent)),
                ],
              ),
            ),
            
            const SizedBox(height: 16),
            
            // The Professional Radar
            Expanded(
              child: Container(
                margin: const EdgeInsets.only(left: 16, right: 16, bottom: 16),
                decoration: BoxDecoration(
                  color: const Color(0xFF1E293B),
                  borderRadius: BorderRadius.circular(32),
                  border: Border.all(color: Colors.white10),
                  boxShadow: [
                    BoxShadow(color: Colors.black.withValues(alpha: 0.3), blurRadius: 20, offset: const Offset(0, 10)),
                  ],
                ),
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(32),
                  child: Stack(
                    alignment: Alignment.center,
                    children: [
                      // Grid lines background
                      CustomPaint(
                        size: Size.infinite,
                        painter: _RadarGridPainter(),
                      ),
                      
                      if (state.isAvailable)
                        const _AnimatedRadarScan()
                      else
                        Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.power_settings_new, size: 64, color: Colors.grey.shade600),
                            const SizedBox(height: 16),
                            Text('الرادار متوقف', style: TextStyle(fontSize: 22, color: Colors.grey.shade500, fontWeight: FontWeight.bold)),
                            const SizedBox(height: 8),
                            Text('قم بتفعيل حالة الاتصال لاستقبال الطلبات القريبة', style: TextStyle(fontSize: 14, color: Colors.grey.shade600)),
                          ],
                        ),

                      // Availability Toggle at bottom of radar
                      Positioned(
                        bottom: 24,
                        child: AnimatedContainer(
                          duration: const Duration(milliseconds: 300),
                          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                          decoration: BoxDecoration(
                            color: state.isAvailable ? Colors.green.withValues(alpha: 0.1) : Colors.white.withValues(alpha: 0.1),
                            borderRadius: BorderRadius.circular(30),
                            border: Border.all(color: state.isAvailable ? Colors.green.withValues(alpha: 0.5) : Colors.white24),
                            boxShadow: state.isAvailable ? [BoxShadow(color: Colors.green.withValues(alpha: 0.2), blurRadius: 10)] : null,
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Text('استقبال الطلبات', style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold)),
                              const SizedBox(width: 16),
                              Switch(
                                value: state.isAvailable,
                                activeColor: Colors.white,
                                activeTrackColor: Colors.green,
                                onChanged: (val) => ref.read(employeeOrdersProvider.notifier).toggleAvailability(val),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _StatCard extends StatelessWidget {
  final IconData icon;
  final String title;
  final String value;
  final Color color;

  const _StatCard({required this.icon, required this.title, required this.value, required this.color});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFF1E293B),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.white10),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(color: color.withValues(alpha: 0.2), shape: BoxShape.circle),
            child: Icon(icon, color: color, size: 24),
          ),
          const SizedBox(width: 16),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(title, style: TextStyle(color: Colors.grey.shade400, fontSize: 12)),
              const SizedBox(height: 4),
              Text(value, style: const TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.bold)),
            ],
          ),
        ],
      ),
    );
  }
}

class _RadarGridPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = Colors.blueAccent.withValues(alpha: 0.1)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 1.0;

    final center = Offset(size.width / 2, size.height / 2);
    final maxRadius = size.width > size.height ? size.width / 2 : size.height / 2;

    // Draw concentric circles
    for (var i = 1; i <= 4; i++) {
      canvas.drawCircle(center, maxRadius * (i / 4), paint);
    }
    
    // Draw cross lines
    canvas.drawLine(Offset(size.width / 2, 0), Offset(size.width / 2, size.height), paint);
    canvas.drawLine(Offset(0, size.height / 2), Offset(size.width, size.height / 2), paint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

class _AnimatedRadarScan extends StatefulWidget {
  const _AnimatedRadarScan();

  @override
  State<_AnimatedRadarScan> createState() => _AnimatedRadarScanState();
}

class _AnimatedRadarScanState extends State<_AnimatedRadarScan> with SingleTickerProviderStateMixin {
  late AnimationController _controller;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(vsync: this, duration: const Duration(seconds: 4))..repeat();
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _controller,
      builder: (context, child) {
        return Transform.rotate(
          angle: _controller.value * 2 * 3.141592653589793238,
          child: CustomPaint(
            size: Size.infinite,
            painter: _RadarSweepPainter(),
          ),
        );
      },
    );
  }
}

class _RadarSweepPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final center = Offset(size.width / 2, size.height / 2);
    final radius = size.width > size.height ? size.width / 2 : size.height / 2;

    final rect = Rect.fromCircle(center: center, radius: radius);
    
    const startAngle = 0.0;
    const sweepAngle = 3.141592653589793238 / 2; // 90 degrees sweep

    final paint = Paint()
      ..shader = SweepGradient(
        center: Alignment.center,
        startAngle: 0.0,
        endAngle: sweepAngle,
        colors: [
          Colors.blueAccent.withValues(alpha: 0.0),
          Colors.blueAccent.withValues(alpha: 0.6),
        ],
        stops: const [0.0, 1.0],
      ).createShader(rect)
      ..style = PaintingStyle.fill;

    canvas.drawArc(rect, startAngle, sweepAngle, true, paint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => true;
}
