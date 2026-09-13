<?php
include 'include/config.php';
include_once 'include/api_cache.php';

$cache_key = '5sim_guest_prices';
$allPrices = api_cache_get($cache_key, 120);

// Admin view logic
$availableCodes = [];
foreach ($allPrices as $country => $products) {
    if (is_array($products)) {
        foreach ($products as $productCode => $operators) {
            if (is_array($operators)) {
                $availableCodes[$productCode] = true;
            }
        }
    }
}
$adminCount = count($availableCodes);
echo "Admin sees $adminCount services.\n";

// User view logic
$serviceMap = [];
foreach ($allPrices as $country => $products) {
    if (!is_array($products)) continue;
    foreach ($products as $product => $operators) {
        if (!is_array($operators)) continue;
        foreach ($operators as $opName => $opDetails) {
            $count = (int)($opDetails['count'] ?? 0);
            if ($count <= 0) continue;
            
            if (!isset($serviceMap[$product])) {
                $serviceMap[$product] = [
                    'total_stock'   => 0
                ];
            }
            
            $serviceMap[$product]['total_stock'] += $count;
        }
    }
}
$userCount = count($serviceMap);
echo "User sees $userCount services.\n";

$diff1 = array_diff(array_keys($availableCodes), array_keys($serviceMap));
$diff2 = array_diff(array_keys($serviceMap), array_keys($availableCodes));

echo "In admin but NOT user (count: " . count($diff1) . ")\n";
echo "In user but NOT admin (count: " . count($diff2) . ")\n";

// Get active services from DB
$activeRes = mysqli_query($conn, "SELECT service_code FROM api_active_services WHERE api_id='2'");
$activeDb = [];
while ($r = mysqli_fetch_assoc($activeRes)) {
    $activeDb[$r['service_code']] = true;
}
echo "Active in DB: " . count($activeDb) . "\n";
