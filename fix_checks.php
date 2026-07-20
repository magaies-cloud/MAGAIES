<?php
$f = 'checks_detector.php';
$c = file_get_contents($f);

// Fix $tot_query
$new_tot_query = <<<PHP
        // Query to get totals of pending checks
        \$tot_query = "
            SELECT 
                SUM(CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(data, '$.type')) = 'in' THEN CAST(JSON_UNQUOTE(JSON_EXTRACT(data, '$.amount')) AS DECIMAL(15,2)) ELSE 0 END) AS inflow,
                SUM(CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(data, '$.type')) = 'out' THEN CAST(JSON_UNQUOTE(JSON_EXTRACT(data, '$.amount')) AS DECIMAL(15,2)) ELSE 0 END) AS outflow
            FROM `checks`
            WHERE tenant_id = '{\$GLOBAL_TENANT_ID}' 
              AND (is_deleted = 0 OR is_deleted IS NULL)
              AND JSON_UNQUOTE(JSON_EXTRACT(data, '$.status')) = 'pending'
        ";
PHP;

$c = preg_replace('/\/\/ Query to get totals of pending checks.*?\$tot_query = ".*?";/s', $new_tot_query, $c);


// Fix $vel_query
$new_vel_query = <<<PHP
        // Query to get daily checks velocity
        \$vel_query = "
            SELECT 
                JSON_UNQUOTE(JSON_EXTRACT(data, '$.dueDate')) AS due_date,
                SUM(CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(data, '$.type')) = 'in' THEN CAST(JSON_UNQUOTE(JSON_EXTRACT(data, '$.amount')) AS DECIMAL(15,2)) ELSE 0 END) AS expected_inflow,
                SUM(CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(data, '$.type')) = 'out' THEN CAST(JSON_UNQUOTE(JSON_EXTRACT(data, '$.amount')) AS DECIMAL(15,2)) ELSE 0 END) AS expected_outflow,
                (SUM(CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(data, '$.type')) = 'in' THEN CAST(JSON_UNQUOTE(JSON_EXTRACT(data, '$.amount')) AS DECIMAL(15,2)) ELSE 0 END) - 
                 SUM(CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(data, '$.type')) = 'out' THEN CAST(JSON_UNQUOTE(JSON_EXTRACT(data, '$.amount')) AS DECIMAL(15,2)) ELSE 0 END)) AS net_daily_velocity
            FROM `checks`
            WHERE tenant_id = '{\$GLOBAL_TENANT_ID}' 
              AND (is_deleted = 0 OR is_deleted IS NULL)
              AND JSON_UNQUOTE(JSON_EXTRACT(data, '$.status')) = 'pending' 
              AND JSON_UNQUOTE(JSON_EXTRACT(data, '$.dueDate')) >= CURRENT_DATE()
            GROUP BY JSON_UNQUOTE(JSON_EXTRACT(data, '$.dueDate'))
            ORDER BY JSON_UNQUOTE(JSON_EXTRACT(data, '$.dueDate')) ASC
        ";
PHP;

$c = preg_replace('/\/\/ Query to get daily checks velocity.*?\$vel_query = ".*?";/s', $new_vel_query, $c);

file_put_contents($f, $c);
echo "checks_detector.php fixed!\n";
