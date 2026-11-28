import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart' hide TextDirection;
import 'package:supabase_flutter/supabase_flutter.dart';
import 'package:magaiees_marketplace/core/providers/global_settings_provider.dart';

class UsdRateHistoryEntry {
  final String id;
  final double oldRate;
  final double newRate;
  final String direction;
  final DateTime changedAt;

  const UsdRateHistoryEntry({
    required this.id,
    required this.oldRate,
    required this.newRate,
    required this.direction,
    required this.changedAt,
  });

  double get delta => newRate - oldRate;
  bool get isUp => delta > 0.009;
  bool get isDown => delta < -0.009;
}

class _ChartPoint {
  final DateTime timestamp;
  final double rate;
  const _ChartPoint(this.timestamp, this.rate);
}

class UsdRateHistoryDialog extends ConsumerStatefulWidget {
  const UsdRateHistoryDialog({super.key});

  static Future<void> show(BuildContext context) {
    return showDialog<void>(
      context: context,
      builder: (_) => const UsdRateHistoryDialog(),
    );
  }

  @override
  ConsumerState<UsdRateHistoryDialog> createState() => _UsdRateHistoryDialogState();
}

class _UsdRateHistoryDialogState extends ConsumerState<UsdRateHistoryDialog> {
  static const Color _kBinanceBg = Color(0xFF0B0E11);
  static const Color _kBinanceCard = Color(0xFF181A20);
  static const Color _kBinanceGreen = Color(0xFF0ECB81);
  static const Color _kBinanceRed = Color(0xFFF6465D);
  static const Color _kBinanceGold = Color(0xFFFCD535);

  bool _isLoading = true;
  List<UsdRateHistoryEntry> _entriesDesc = [];
  DateTime _selectedDateTime = DateTime.now();
  String _activePreset = 'now'; // now, yesterday, d7, d10, d30, custom
  String _chartRange = '30d'; // 24h, 7d, 30d, all
  int? _hoveredPointIndex;

  @override
  void initState() {
    super.initState();
    _loadHistory();
  }

  Future<void> _loadHistory() async {
    setState(() => _isLoading = true);
    try {
      final res = await Supabase.instance.client
          .from('usd_rate_history')
          .select('id, old_rate, new_rate, change_direction, changed_at')
          .order('changed_at', ascending: false)
          .limit(500);

      final parsed = <UsdRateHistoryEntry>[];
      for (final row in (res as List)) {
        final oldR = double.tryParse(row['old_rate']?.toString() ?? '') ?? 0;
        final newR = double.tryParse(row['new_rate']?.toString() ?? '') ?? 0;
        final dt = DateTime.tryParse(row['changed_at']?.toString() ?? '')?.toLocal() ?? DateTime.now();
        if (newR > 0) {
          parsed.add(
            UsdRateHistoryEntry(
              id: row['id']?.toString() ?? '',
              oldRate: oldR > 0 ? oldR : newR,
              newRate: newR,
              direction: row['change_direction']?.toString() ?? 'initial',
              changedAt: dt,
            ),
          );
        }
      }

      if (mounted) {
        setState(() {
          _entriesDesc = parsed;
          _isLoading = false;
        });
      }
    } catch (_) {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  bool _isSameDay(DateTime a, DateTime b) =>
      a.year == b.year && a.month == b.month && a.day == b.day;

  /// Resolves the exact USD rate active at [targetMoment] (down to the exact hour & minute).
  double _resolveRateAtExactMoment(DateTime targetMoment, double currentFallback) {
    if (_entriesDesc.isEmpty) return currentFallback;

    // _entriesDesc is sorted newest -> oldest; first entry <= targetMoment was the active rate at that exact clock time
    for (final entry in _entriesDesc) {
      if (!entry.changedAt.isAfter(targetMoment)) {
        return entry.newRate;
      }
    }
    return _entriesDesc.last.oldRate;
  }

  List<UsdRateHistoryEntry> _changesOnDate(DateTime date) {
    return _entriesDesc
        .where((e) => e.direction != 'initial' && _isSameDay(e.changedAt, date))
        .toList();
  }

  List<_ChartPoint> _buildChartSeries(double currentRate) {
    final now = DateTime.now();
    DateTime cutoff;
    switch (_chartRange) {
      case '24h':
        cutoff = now.subtract(const Duration(hours: 24));
        break;
      case '7d':
        cutoff = now.subtract(const Duration(days: 7));
        break;
      case '30d':
        cutoff = now.subtract(const Duration(days: 30));
        break;
      case 'all':
      default:
        cutoff = _entriesDesc.isNotEmpty
            ? _entriesDesc.last.changedAt.subtract(const Duration(days: 1))
            : now.subtract(const Duration(days: 30));
        break;
    }

    final asc = _entriesDesc.reversed.toList();
    final points = <_ChartPoint>[];

    final startRate = _resolveRateAtExactMoment(cutoff, currentRate);
    points.add(_ChartPoint(cutoff, startRate));

    for (final e in asc) {
      if (e.changedAt.isAfter(cutoff) && !e.changedAt.isAfter(now)) {
        if (e.direction != 'initial' && points.isNotEmpty) {
          // Step-before anchor so jumps are crisp like financial terminals
          points.add(_ChartPoint(e.changedAt.subtract(const Duration(seconds: 1)), e.oldRate));
        }
        points.add(_ChartPoint(e.changedAt, e.newRate));
      }
    }

    points.add(_ChartPoint(now, currentRate));
    return points;
  }

  void _selectPreset(String preset, Duration ago) {
    final target = DateTime.now().subtract(ago);
    setState(() {
      _activePreset = preset;
      _selectedDateTime = target;
      _hoveredPointIndex = null;
    });
  }

  Future<void> _pickCalendarDate(bool isRtl) async {
    final now = DateTime.now();
    final picked = await showDatePicker(
      context: context,
      initialDate: _selectedDateTime.isAfter(now) ? now : _selectedDateTime,
      firstDate: DateTime(2024, 1, 1),
      lastDate: now,
      helpText: isRtl ? 'اختر اليوم لمعرفة سعر الدولار' : 'Select Date',
    );
    if (picked != null && mounted) {
      setState(() {
        _activePreset = 'custom';
        _selectedDateTime = DateTime(
          picked.year,
          picked.month,
          picked.day,
          _selectedDateTime.hour,
          _selectedDateTime.minute,
        );
        if (_selectedDateTime.isAfter(now)) {
          _selectedDateTime = now;
        }
      });
    }
  }

  Future<void> _pickClockTime(bool isRtl) async {
    final pickedTime = await showTimePicker(
      context: context,
      initialTime: TimeOfDay.fromDateTime(_selectedDateTime),
      helpText: isRtl ? 'حدد الساعة والدقيقة بدقة' : 'Select Exact Time',
    );
    if (pickedTime != null && mounted) {
      final now = DateTime.now();
      var candidate = DateTime(
        _selectedDateTime.year,
        _selectedDateTime.month,
        _selectedDateTime.day,
        pickedTime.hour,
        pickedTime.minute,
      );
      if (candidate.isAfter(now)) {
        candidate = now;
      }
      setState(() {
        _activePreset = 'custom';
        _selectedDateTime = candidate;
      });
    }
  }

  void _handleChartTouch(Offset localPos, double width, List<_ChartPoint> series) {
    if (series.isEmpty || width <= 0) return;
    final ratio = (localPos.dx / width).clamp(0.0, 1.0);
    final idx = (ratio * (series.length - 1)).round().clamp(0, series.length - 1);
    final pt = series[idx];
    setState(() {
      _hoveredPointIndex = idx;
      _activePreset = 'custom';
      _selectedDateTime = pt.timestamp;
    });
  }

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;
    final currentPlatformRate = ref.watch(globalSettingsProvider).platformUsdRate;
    final chartSeries = _buildChartSeries(currentPlatformRate);

    final firstSeriesRate = chartSeries.isNotEmpty ? chartSeries.first.rate : currentPlatformRate;
    final lastSeriesRate = chartSeries.isNotEmpty ? chartSeries.last.rate : currentPlatformRate;
    final periodDelta = lastSeriesRate - firstSeriesRate;
    final periodPct = firstSeriesRate > 0 ? (periodDelta / firstSeriesRate) * 100 : 0.0;
    final isBullish = periodDelta >= -0.009;
    final trendColor = isBullish ? _kBinanceGreen : _kBinanceRed;

    double highRate = currentPlatformRate;
    double lowRate = currentPlatformRate;
    for (final p in chartSeries) {
      if (p.rate > highRate) highRate = p.rate;
      if (p.rate < lowRate) lowRate = p.rate;
    }

    final hoveredPt = (_hoveredPointIndex != null && _hoveredPointIndex! < chartSeries.length)
        ? chartSeries[_hoveredPointIndex!]
        : null;

    final rateAtMoment = _resolveRateAtExactMoment(_selectedDateTime, currentPlatformRate);
    final dayChanges = _changesOnDate(_selectedDateTime);
    final visibleTimeline = _entriesDesc.where((e) => e.direction != 'initial').toList();

    final fmt = NumberFormat('#,##0.##');
    final dateFmt = DateFormat('yyyy/MM/dd');
    final timeFmt = DateFormat('hh:mm a');
    final dateTimeFmt = DateFormat('yyyy/MM/dd • hh:mm a');

    final presets = <Map<String, dynamic>>[
      {'id': 'now', 'ago': Duration.zero, 'label': isRtl ? 'الآن' : 'Now'},
      {'id': 'yesterday', 'ago': const Duration(days: 1), 'label': isRtl ? 'أمس' : 'Yesterday'},
      {'id': 'd7', 'ago': const Duration(days: 7), 'label': isRtl ? 'قبل 7 أيام' : '7d Ago'},
      {'id': 'd10', 'ago': const Duration(days: 10), 'label': isRtl ? 'قبل 10 أيام' : '10d Ago'},
      {'id': 'd30', 'ago': const Duration(days: 30), 'label': isRtl ? 'قبل شهر' : '1m Ago'},
    ];

    final ranges = <Map<String, String>>[
      {'id': '24h', 'label': isRtl ? '24س' : '24H'},
      {'id': '7d', 'label': isRtl ? '7أيام' : '7D'},
      {'id': '30d', 'label': isRtl ? 'شهر' : '1M'},
      {'id': 'all', 'label': isRtl ? 'الكل' : 'ALL'},
    ];

    return Directionality(
      textDirection: isRtl ? TextDirection.rtl : TextDirection.ltr,
      child: Dialog(
        backgroundColor: Colors.white,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(22)),
        insetPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 18),
        child: Container(
          width: 620,
          constraints: const BoxConstraints(maxHeight: 820),
          padding: const EdgeInsets.all(18),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Top Title Bar
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(9),
                    decoration: BoxDecoration(
                      color: _kBinanceBg,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Icon(Icons.candlestick_chart_rounded, color: _kBinanceGold, size: 22),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          isRtl ? 'مؤشر وسجل سعر الدولار المعتمد (USD / SDG)' : 'USD / SDG Exchange Rate Terminal',
                          style: const TextStyle(fontSize: 16.5, fontWeight: FontWeight.w900, color: Color(0xFF0F172A)),
                        ),
                        Text(
                          isRtl
                              ? 'مخطط بياني تفاعلي وبحث دقيق باليوم والساعة لحركة الطلوع والنزول'
                              : 'Interactive chart & exact date/time historical lookup',
                          style: const TextStyle(fontSize: 11.5, color: Color(0xFF64748B)),
                        ),
                      ],
                    ),
                  ),
                  IconButton(
                    onPressed: () => Navigator.pop(context),
                    icon: const Icon(Icons.close_rounded),
                  ),
                ],
              ),
              const SizedBox(height: 12),

              Flexible(
                child: SingleChildScrollView(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // =========================================================
                      // 1. BINANCE-STYLE INTERACTIVE TERMINAL CHART CARD
                      // =========================================================
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: _kBinanceBg,
                          borderRadius: BorderRadius.circular(18),
                          border: Border.all(color: const Color(0xFF2B3139)),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withValues(alpha: 0.16),
                              blurRadius: 14,
                              offset: const Offset(0, 6),
                            ),
                          ],
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            // Pair Header + High/Low Stats
                            Wrap(
                              alignment: WrapAlignment.spaceBetween,
                              crossAxisAlignment: WrapCrossAlignment.center,
                              spacing: 12,
                              runSpacing: 8,
                              children: [
                                Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      mainAxisSize: MainAxisSize.min,
                                      children: [
                                        const Text(
                                          'USD / SDG',
                                          style: TextStyle(
                                            color: Colors.white,
                                            fontSize: 13,
                                            fontWeight: FontWeight.w900,
                                            letterSpacing: 0.6,
                                          ),
                                        ),
                                        const SizedBox(width: 8),
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                          decoration: BoxDecoration(
                                            color: _kBinanceGold.withValues(alpha: 0.16),
                                            borderRadius: BorderRadius.circular(4),
                                          ),
                                          child: Text(
                                            isRtl ? 'مؤشر مقاييس' : 'Magaiees Index',
                                            style: const TextStyle(
                                              color: _kBinanceGold,
                                              fontSize: 10,
                                              fontWeight: FontWeight.w800,
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),
                                    const SizedBox(height: 4),
                                    Row(
                                      mainAxisSize: MainAxisSize.min,
                                      crossAxisAlignment: CrossAxisAlignment.baseline,
                                      textBaseline: TextBaseline.alphabetic,
                                      children: [
                                        Text(
                                          fmt.format(hoveredPt?.rate ?? currentPlatformRate),
                                          style: TextStyle(
                                            color: trendColor,
                                            fontSize: 27,
                                            fontWeight: FontWeight.w900,
                                          ),
                                        ),
                                        const SizedBox(width: 6),
                                        Text(
                                          isRtl ? 'ج.س' : 'SDG',
                                          style: const TextStyle(
                                            color: Color(0xFF848E9C),
                                            fontSize: 12.5,
                                            fontWeight: FontWeight.w700,
                                          ),
                                        ),
                                        const SizedBox(width: 10),
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                                          decoration: BoxDecoration(
                                            color: trendColor.withValues(alpha: 0.16),
                                            borderRadius: BorderRadius.circular(6),
                                          ),
                                          child: Text(
                                            '${periodDelta >= 0 ? '+' : ''}${periodPct.toStringAsFixed(2)}%',
                                            textDirection: TextDirection.ltr,
                                            style: TextStyle(
                                              color: trendColor,
                                              fontSize: 11.5,
                                              fontWeight: FontWeight.w800,
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),
                                    if (hoveredPt != null)
                                      Padding(
                                        padding: const EdgeInsets.only(top: 3),
                                        child: Text(
                                          '🕒 ${dateTimeFmt.format(hoveredPt.timestamp)}',
                                          style: const TextStyle(color: _kBinanceGold, fontSize: 11, fontWeight: FontWeight.w700),
                                        ),
                                      ),
                                  ],
                                ),

                                // High / Low Pills (Binance Top Right Box)
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                                  decoration: BoxDecoration(
                                    color: _kBinanceCard,
                                    borderRadius: BorderRadius.circular(10),
                                    border: Border.all(color: const Color(0xFF2B3139)),
                                  ),
                                  child: Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Text(
                                            isRtl ? 'أعلى سعر' : 'High',
                                            style: const TextStyle(color: Color(0xFF848E9C), fontSize: 10),
                                          ),
                                          Text(
                                            fmt.format(highRate),
                                            style: const TextStyle(color: _kBinanceGreen, fontSize: 12.5, fontWeight: FontWeight.w800),
                                          ),
                                        ],
                                      ),
                                      Container(
                                        width: 1,
                                        height: 24,
                                        margin: const EdgeInsets.symmetric(horizontal: 12),
                                        color: const Color(0xFF2B3139),
                                      ),
                                      Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Text(
                                            isRtl ? 'أدنى سعر' : 'Low',
                                            style: const TextStyle(color: Color(0xFF848E9C), fontSize: 10),
                                          ),
                                          Text(
                                            fmt.format(lowRate),
                                            style: const TextStyle(color: _kBinanceRed, fontSize: 12.5, fontWeight: FontWeight.w800),
                                          ),
                                        ],
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 12),

                            // Timeframe Selector Bar (24H | 7D | 1M | ALL)
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Wrap(
                                  spacing: 6,
                                  children: ranges.map((r) {
                                    final active = _chartRange == r['id'];
                                    return InkWell(
                                      borderRadius: BorderRadius.circular(6),
                                      onTap: () => setState(() {
                                        _chartRange = r['id']!;
                                        _hoveredPointIndex = null;
                                      }),
                                      child: Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                        decoration: BoxDecoration(
                                          color: active ? const Color(0xFF2B3139) : Colors.transparent,
                                          borderRadius: BorderRadius.circular(6),
                                        ),
                                        child: Text(
                                          r['label']!,
                                          style: TextStyle(
                                            color: active ? _kBinanceGold : const Color(0xFF848E9C),
                                            fontSize: 11.5,
                                            fontWeight: FontWeight.w800,
                                          ),
                                        ),
                                      ),
                                    );
                                  }).toList(),
                                ),
                                Text(
                                  isRtl ? 'اسحب إصبعك على المؤشر للمعاينة' : 'Drag across chart to inspect',
                                  style: const TextStyle(color: Color(0xFF848E9C), fontSize: 10.5),
                                ),
                              ],
                            ),
                            const SizedBox(height: 10),

                            // Interactive Binance Canvas Area
                            SizedBox(
                              height: 155,
                              width: double.infinity,
                              child: LayoutBuilder(
                                builder: (ctx, constraints) {
                                  return GestureDetector(
                                    behavior: HitTestBehavior.opaque,
                                    onTapDown: (d) => _handleChartTouch(d.localPosition, constraints.maxWidth, chartSeries),
                                    onHorizontalDragUpdate: (d) => _handleChartTouch(d.localPosition, constraints.maxWidth, chartSeries),
                                    child: MouseRegion(
                                      onHover: (e) => _handleChartTouch(e.localPosition, constraints.maxWidth, chartSeries),
                                      onExit: (_) => setState(() => _hoveredPointIndex = null),
                                      child: CustomPaint(
                                        painter: _BinanceAreaChartPainter(
                                          points: chartSeries,
                                          lineColor: trendColor,
                                          hoveredIndex: _hoveredPointIndex,
                                        ),
                                      ),
                                    ),
                                  );
                                },
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 16),

                      // =========================================================
                      // 2. EXACT DATE + EXACT CLOCK TIME LOOKUP
                      // =========================================================
                      Text(
                        isRtl
                            ? 'تحديد يوم وساعة معينة لمعرفة سعر الدولار وقتها:'
                            : 'Check Exact USD Rate by Date & Time:',
                        style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 13.5, color: Color(0xFF0F172A)),
                      ),
                      const SizedBox(height: 8),

                      // Date Picker + Time Picker Buttons Side-by-Side
                      Row(
                        children: [
                          Expanded(
                            child: OutlinedButton.icon(
                              style: OutlinedButton.styleFrom(
                                backgroundColor: const Color(0xFFF8FAFC),
                                foregroundColor: const Color(0xFF0F172A),
                                side: const BorderSide(color: Color(0xFFCBD5E1)),
                                padding: const EdgeInsets.symmetric(vertical: 11, horizontal: 10),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(11)),
                              ),
                              onPressed: () => _pickCalendarDate(isRtl),
                              icon: const Icon(Icons.calendar_month_rounded, size: 18, color: Color(0xFF059669)),
                              label: Text(
                                isRtl ? 'اليوم: ${dateFmt.format(_selectedDateTime)}' : 'Date: ${dateFmt.format(_selectedDateTime)}',
                                style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 12.5),
                              ),
                            ),
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: OutlinedButton.icon(
                              style: OutlinedButton.styleFrom(
                                backgroundColor: const Color(0xFFF8FAFC),
                                foregroundColor: const Color(0xFF0F172A),
                                side: const BorderSide(color: Color(0xFFCBD5E1)),
                                padding: const EdgeInsets.symmetric(vertical: 11, horizontal: 10),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(11)),
                              ),
                              onPressed: () => _pickClockTime(isRtl),
                              icon: const Icon(Icons.access_time_filled_rounded, size: 18, color: Color(0xFF1E40AF)),
                              label: Text(
                                isRtl ? 'الساعة: ${timeFmt.format(_selectedDateTime)}' : 'Time: ${timeFmt.format(_selectedDateTime)}',
                                style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 12.5),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),

                      // Quick Presets
                      Wrap(
                        spacing: 6,
                        runSpacing: 6,
                        children: presets.map((p) {
                          final id = p['id'] as String;
                          final selected = _activePreset == id;
                          return ChoiceChip(
                            selected: selected,
                            selectedColor: const Color(0xFF059669),
                            labelStyle: TextStyle(
                              fontSize: 11.5,
                              fontWeight: FontWeight.w700,
                              color: selected ? Colors.white : const Color(0xFF334155),
                            ),
                            checkmarkColor: Colors.white,
                            label: Text(p['label'] as String),
                            onSelected: (_) => _selectPreset(id, p['ago'] as Duration),
                          );
                        }).toList(),
                      ),
                      const SizedBox(height: 12),

                      // Exact Moment Result Card
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          gradient: const LinearGradient(
                            colors: [Color(0xFFF0FDF4), Color(0xFFECFDF5)],
                            begin: Alignment.topRight,
                            end: Alignment.bottomLeft,
                          ),
                          borderRadius: BorderRadius.circular(14),
                          border: Border.all(color: const Color(0xFF6EE7B7), width: 1.3),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Flexible(
                                  child: Text(
                                    isRtl
                                        ? 'السعر المعتمد في: ${dateTimeFmt.format(_selectedDateTime)}'
                                        : 'Active Rate at: ${dateTimeFmt.format(_selectedDateTime)}',
                                    style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w800, color: Color(0xFF065F46)),
                                  ),
                                ),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2.5),
                                  decoration: BoxDecoration(
                                    color: Colors.white,
                                    borderRadius: BorderRadius.circular(20),
                                    border: Border.all(color: const Color(0xFFA7F3D0)),
                                  ),
                                  child: Text(
                                    dayChanges.isEmpty
                                        ? (isRtl ? 'مستقر في هذا اليوم' : 'Stable')
                                        : (isRtl ? '${dayChanges.length} تعديل في هذا اليوم' : '${dayChanges.length} change(s)'),
                                    style: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.w800, color: Color(0xFF047857)),
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 6),
                            Row(
                              crossAxisAlignment: CrossAxisAlignment.baseline,
                              textBaseline: TextBaseline.alphabetic,
                              children: [
                                Text(
                                  '1\$ = ${fmt.format(rateAtMoment)}',
                                  style: const TextStyle(
                                    fontSize: 24,
                                    fontWeight: FontWeight.w900,
                                    color: Color(0xFF064E3B),
                                  ),
                                ),
                                const SizedBox(width: 6),
                                Text(
                                  isRtl ? 'جنيه سوداني (ج.س)' : 'SDG',
                                  style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700, color: Color(0xFF047857)),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 16),

                      // =========================================================
                      // 3. CHRONOLOGICAL UP & DOWN LOG
                      // =========================================================
                      Text(
                        isRtl ? 'سجل التعديلات بالتاريخ والساعة (طلوعاً ونزولاً):' : 'Chronological Adjustments (Up & Down):',
                        style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 13.5, color: Color(0xFF0F172A)),
                      ),
                      const SizedBox(height: 8),

                      if (_isLoading)
                        const Padding(
                          padding: EdgeInsets.all(24),
                          child: Center(child: CircularProgressIndicator()),
                        )
                      else if (visibleTimeline.isEmpty)
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: const Color(0xFFF8FAFC),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: const Color(0xFFE2E8F0)),
                          ),
                          child: Text(
                            isRtl
                                ? 'السعر المعتمد الحالي هو ${fmt.format(currentPlatformRate)} ج.س — سيُحفظ هنا كل تعديل قادم تلقائياً مع سهم الصعود أو الهبوط والتاريخ والساعة الدقيقة.'
                                : 'Current official rate is ${fmt.format(currentPlatformRate)} SDG. All future adjustments will be logged here automatically.',
                            textAlign: TextAlign.center,
                            style: const TextStyle(fontSize: 12.5, color: Color(0xFF64748B), height: 1.5),
                          ),
                        )
                      else
                        ListView.separated(
                          shrinkWrap: true,
                          physics: const NeverScrollableScrollPhysics(),
                          itemCount: visibleTimeline.length,
                          separatorBuilder: (_, _) => const SizedBox(height: 8),
                          itemBuilder: (context, idx) {
                            final item = visibleTimeline[idx];
                            final isUp = item.isUp;
                            final badgeColor = isUp ? const Color(0xFF059669) : const Color(0xFFDC2626);
                            final diffAbs = fmt.format(item.delta.abs());

                            return InkWell(
                              borderRadius: BorderRadius.circular(12),
                              onTap: () {
                                setState(() {
                                  _activePreset = 'custom';
                                  _selectedDateTime = item.changedAt;
                                });
                              },
                              child: Container(
                                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFF8FAFC),
                                  borderRadius: BorderRadius.circular(12),
                                  border: Border.all(color: const Color(0xFFE2E8F0)),
                                ),
                                child: Row(
                                  children: [
                                    Container(
                                      padding: const EdgeInsets.all(8),
                                      decoration: BoxDecoration(
                                        color: badgeColor.withValues(alpha: 0.1),
                                        borderRadius: BorderRadius.circular(10),
                                      ),
                                      child: Icon(
                                        isUp ? Icons.trending_up_rounded : Icons.trending_down_rounded,
                                        color: badgeColor,
                                        size: 20,
                                      ),
                                    ),
                                    const SizedBox(width: 12),
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Text(
                                            isRtl
                                                ? 'من ${fmt.format(item.oldRate)} ج.س ⬅️ إلى ${fmt.format(item.newRate)} ج.س'
                                                : 'From ${fmt.format(item.oldRate)} ➔ ${fmt.format(item.newRate)} SDG',
                                            style: const TextStyle(
                                              fontWeight: FontWeight.w800,
                                              fontSize: 13,
                                              color: Color(0xFF0F172A),
                                            ),
                                          ),
                                          const SizedBox(height: 2),
                                          Text(
                                            dateTimeFmt.format(item.changedAt),
                                            style: const TextStyle(fontSize: 11.5, color: Color(0xFF64748B)),
                                          ),
                                        ],
                                      ),
                                    ),
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
                                      decoration: BoxDecoration(
                                        color: badgeColor.withValues(alpha: 0.1),
                                        borderRadius: BorderRadius.circular(20),
                                      ),
                                      child: Text(
                                        isUp
                                            ? (isRtl ? '▲ طلوع +$diffAbs' : '▲ +$diffAbs')
                                            : (isRtl ? '▼ نزول -$diffAbs' : '▼ -$diffAbs'),
                                        style: TextStyle(
                                          fontSize: 11,
                                          fontWeight: FontWeight.w900,
                                          color: badgeColor,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            );
                          },
                        ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _BinanceAreaChartPainter extends CustomPainter {
  final List<_ChartPoint> points;
  final Color lineColor;
  final int? hoveredIndex;

  _BinanceAreaChartPainter({
    required this.points,
    required this.lineColor,
    required this.hoveredIndex,
  });

  @override
  void paint(Canvas canvas, Size size) {
    if (points.isEmpty || size.width <= 0 || size.height <= 0) return;

    const double padTop = 14.0;
    const double padBottom = 14.0;
    final double usableH = math.max(1.0, size.height - padTop - padBottom);

    // Subtle horizontal grid lines (Binance dark grid)
    final gridPaint = Paint()
      ..color = const Color(0xFF2B3139).withValues(alpha: 0.7)
      ..strokeWidth = 0.8;
    for (int i = 0; i <= 3; i++) {
      final y = padTop + (usableH * i / 3);
      canvas.drawLine(Offset(0, y), Offset(size.width, y), gridPaint);
    }

    double minY = points.first.rate;
    double maxY = points.first.rate;
    for (final p in points) {
      if (p.rate < minY) minY = p.rate;
      if (p.rate > maxY) maxY = p.rate;
    }
    if ((maxY - minY).abs() < 0.01) {
      minY -= 50;
      maxY += 50;
    } else {
      final margin = (maxY - minY) * 0.12;
      minY -= margin;
      maxY += margin;
    }

    Offset pointAt(int i) {
      final x = points.length == 1 ? size.width / 2 : (i / (points.length - 1)) * size.width;
      final normY = (points[i].rate - minY) / (maxY - minY);
      final y = padTop + usableH * (1.0 - normY.clamp(0.0, 1.0));
      return Offset(x, y);
    }

    final linePath = Path();
    final fillPath = Path();
    final firstPt = pointAt(0);
    linePath.moveTo(firstPt.dx, firstPt.dy);
    fillPath.moveTo(firstPt.dx, size.height);
    fillPath.lineTo(firstPt.dx, firstPt.dy);

    for (int i = 1; i < points.length; i++) {
      final pt = pointAt(i);
      linePath.lineTo(pt.dx, pt.dy);
      fillPath.lineTo(pt.dx, pt.dy);
    }

    final lastPt = pointAt(points.length - 1);
    fillPath.lineTo(lastPt.dx, size.height);
    fillPath.close();

    // Glowing area gradient fill under the curve
    final areaPaint = Paint()
      ..shader = LinearGradient(
        begin: Alignment.topCenter,
        end: Alignment.bottomCenter,
        colors: [
          lineColor.withValues(alpha: 0.34),
          lineColor.withValues(alpha: 0.02),
        ],
      ).createShader(Rect.fromLTWH(0, 0, size.width, size.height));
    canvas.drawPath(fillPath, areaPaint);

    // Crisp price line
    final strokePaint = Paint()
      ..color = lineColor
      ..style = PaintingStyle.stroke
      ..strokeWidth = 2.4
      ..strokeJoin = StrokeJoin.round
      ..strokeCap = StrokeCap.round;
    canvas.drawPath(linePath, strokePaint);

    // Live pulsing dot at latest price
    canvas.drawCircle(lastPt, 6.5, Paint()..color = lineColor.withValues(alpha: 0.25));
    canvas.drawCircle(lastPt, 3.5, Paint()..color = lineColor);

    // Crosshair on hover / finger drag
    if (hoveredIndex != null && hoveredIndex! >= 0 && hoveredIndex! < points.length) {
      final hPt = pointAt(hoveredIndex!);
      final crossPaint = Paint()
        ..color = const Color(0xFFFCD535).withValues(alpha: 0.75)
        ..strokeWidth = 1.0;

      canvas.drawLine(Offset(hPt.dx, 0), Offset(hPt.dx, size.height), crossPaint);
      canvas.drawLine(Offset(0, hPt.dy), Offset(size.width, hPt.dy), crossPaint);
      canvas.drawCircle(hPt, 6.0, Paint()..color = const Color(0xFFFCD535).withValues(alpha: 0.28));
      canvas.drawCircle(hPt, 3.8, Paint()..color = const Color(0xFFFCD535));
    }
  }

  @override
  bool shouldRepaint(covariant _BinanceAreaChartPainter oldDelegate) {
    return oldDelegate.points != points ||
        oldDelegate.lineColor != lineColor ||
        oldDelegate.hoveredIndex != hoveredIndex;
  }
}
