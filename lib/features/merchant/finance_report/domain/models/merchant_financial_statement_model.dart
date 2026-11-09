/// Model representing a single staff member's sales contribution and performance metrics.
class StaffSalesMetric {
  final String staffId;
  final String staffName;
  final String jobTitle;
  final int ordersCount;
  final double salesTotal;
  final double transportTotal;
  final double platformCommission;
  final double netSales;
  final double contributionPercent;

  const StaffSalesMetric({
    required this.staffId,
    required this.staffName,
    required this.jobTitle,
    required this.ordersCount,
    required this.salesTotal,
    required this.transportTotal,
    required this.platformCommission,
    required this.netSales,
    required this.contributionPercent,
  });
}

/// Comprehensive Financial Statement model representing inflow (الوارد),
/// outflow (المنصرف), and cumulative net balance (التراكمي / الصافي).
class MerchantFinancialStatement {
  final double totalInflow;
  final double goodsTotal;
  final double transportTotal;
  final double offloadingTotal;
  final double totalOutflow;
  final double platformCommissionTotal;
  final double commissionRate;
  final bool hasCommissionEnabled;
  final double payoutsTotal;
  final double netCumulative;
  final double collectedPaid;
  final double unpaidDebts;
  final int totalOrdersCount;
  final List<StaffSalesMetric> staffBreakdown;
  final List<Map<String, dynamic>> orders;

  const MerchantFinancialStatement({
    required this.totalInflow,
    required this.goodsTotal,
    required this.transportTotal,
    required this.offloadingTotal,
    required this.totalOutflow,
    required this.platformCommissionTotal,
    required this.commissionRate,
    required this.hasCommissionEnabled,
    required this.payoutsTotal,
    required this.netCumulative,
    required this.collectedPaid,
    required this.unpaidDebts,
    required this.totalOrdersCount,
    required this.staffBreakdown,
    required this.orders,
  });

  factory MerchantFinancialStatement.empty() {
    return const MerchantFinancialStatement(
      totalInflow: 0.0,
      goodsTotal: 0.0,
      transportTotal: 0.0,
      offloadingTotal: 0.0,
      totalOutflow: 0.0,
      platformCommissionTotal: 0.0,
      commissionRate: 0.0,
      hasCommissionEnabled: false,
      payoutsTotal: 0.0,
      netCumulative: 0.0,
      collectedPaid: 0.0,
      unpaidDebts: 0.0,
      totalOrdersCount: 0,
      staffBreakdown: [],
      orders: [],
    );
  }
}
