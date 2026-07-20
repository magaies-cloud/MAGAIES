<?php
$reps = ['general_ledger_v2.php', 'cashflow_modern.php', 'profitability_analysis.php', 'breakeven_analysis.php', 'inventory_turnover.php', 'sales_forecasting.php', 'financial_health.php', 'balances_modern_hub.php', 'credit_limits_dashboard.php', 'checks_detector.php', 'supplier_reliability.php', 'combined_receivables.php', 'next_gen_analytics.php', 'steel_market_forecast.php', 'customer_exposure.php', 'alerts_manager.php', 'customer_churn.php', 'seasonal_trends.php', 'price_variance.php', 'customer_engagement.php'];
$err = 0;
foreach($reps as $r) {
    if(file_exists($r)) {
        $c = file_get_contents($r);
        if(preg_match('/(transactions|checks_velocity|customer_credit_profiles|inventory_items)/i', $c, $m)) {
            echo "$r uses ghost table: {$m[1]}\n";
            $err = 1;
        }
    }
}
if(!$err) echo "No direct ghost table usage found.\n";
