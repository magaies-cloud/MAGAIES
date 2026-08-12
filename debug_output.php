<?php
// Test what the server actually outputs for single-cash-payment.php
// by simulating a request
$ch = curl_init('http://localhost:3000/single-cash-payment.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, ''); // no cookies
$html = curl_exec($ch);
curl_close($ch);

// Check if SERVER_SINGLE_PAYMENTS is in the output
if (strpos($html, 'SERVER_SINGLE_PAYMENTS') !== false) {
    echo "✓ SERVER_SINGLE_PAYMENTS found in HTML output\n";
    // Extract the value
    preg_match('/window\.SERVER_SINGLE_PAYMENTS\s*=\s*(.+?);/', $html, $m);
    if ($m) {
        $data = json_decode($m[1], true);
        if (is_array($data)) {
            echo "✓ Contains " . count($data) . " vouchers\n";
        } else {
            echo "✗ Could not parse JSON. Raw: " . substr($m[1], 0, 200) . "\n";
        }
    }
} else {
    echo "✗ SERVER_SINGLE_PAYMENTS NOT found in HTML output\n";
}

// Check voucher number
preg_match('/id="voucher-number"[^>]*>([^<]+)</', $html, $m2);
if ($m2) {
    echo "Voucher number shown: " . $m2[1] . "\n";
} else {
    echo "Could not find voucher-number element\n";
}

// Check for stray ?>
if (strpos($html, '?>') !== false) {
    echo "⚠ Stray '?>' found in HTML output!\n";
}

// Show first 500 chars
echo "\n--- First 500 chars of output ---\n";
echo substr($html, 0, 500);
