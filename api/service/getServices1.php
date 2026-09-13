<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '256M');
set_time_limit(60);

/**
 * getServices1.php — Server 1 (TigerSMS)
 * Returns a deduplicated list of ALL services available across ALL countries.
 * Used in the Service-First buy flow: user picks service first, then country.
 */
include __DIR__ . '/../../include/config.php';
include __DIR__ . '/../../include/api_active_check.php';
require_api_active($conn, 8);
include __DIR__ . '/../../include/service_icons.php';

if (!isset($_GET['token']) || $_GET['token'] == "") {
    echo json_encode(['service' => [], 'error' => 'Token Blank']);
    exit;
}

$token = mysqli_real_escape_string($conn, $_GET['token']);
$check_token = check_token($token, $conn);

if ($check_token === false) {
    echo json_encode(['service' => [], 'error' => 'Token Expired']);
    exit;
}

// Fetch TigerSMS API details
$api_sql = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='8'");
$api_data = mysqli_fetch_assoc($api_sql);
$api_url = $api_data['api_url'];
$api_key = $api_data['api_key'];

// Fetch ALL prices with caching (2-minute TTL)
include_once __DIR__ . '/../../include/api_cache.php';
$cache_key = 'tigersms_getPrices';
$allPrices = api_cache_get($cache_key, 120);

if (!$allPrices) {
    $url = "{$api_url}/stubs/handler_api.php?api_key={$api_key}&action=getPrices";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $raw = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    $allPrices = $raw ? json_decode($raw, true) : [];
    
    // Fallback to stale cache if failed
    if (!$allPrices || empty($allPrices)) {
        $allPrices = api_cache_get_stale($cache_key);
    }
    
    if ($allPrices && is_array($allPrices) && !empty($allPrices)) {
        api_cache_set($cache_key, $allPrices);
    } else {
        error_log("TigerSMS getPrices failed and no stale cache available. cURL Error: " . $error);
    }
}

if (!$allPrices || !is_array($allPrices)) {
    echo json_encode(['service' => [], 'error' => 'Failed to fetch services from API']);
    exit;
}

// Fetch active services and their manual sort orders for API ID 8 (TigerSMS)
$activeServices = [];
$serviceOrders = [];
$activeRes = mysqli_query($conn, "SELECT service_code FROM api_active_services WHERE api_id='8'");
if ($activeRes) {
    while ($r = mysqli_fetch_assoc($activeRes)) {
        $activeServices[$r['service_code']] = true;
    }
}
$orderRes = mysqli_query($conn, "SELECT service_code, sort_order FROM api_service_order WHERE api_id='8'");
if ($orderRes) {
    while ($r = mysqli_fetch_assoc($orderRes)) {
        $serviceOrders[$r['service_code']] = (int)$r['sort_order'];
    }
}

// Build a unique service list with total stock across all countries
$serviceMap = []; // service_code => ['total_stock' => int, 'min_cost' => float]

foreach ($allPrices as $countryCode => $services) {
    if (!is_array($services)) continue;
    foreach ($services as $serviceCode => $details) {
        if (!isset($activeServices[$serviceCode])) continue; // Only show active services
        
        $count = (int)($details['count'] ?? 0);
        $cost  = (float)($details['cost'] ?? 0);
        
        if ($count <= 0) continue;
        
        if (!isset($serviceMap[$serviceCode])) {
            $serviceMap[$serviceCode] = [
                'total_stock'    => 0,
                'min_cost'       => $cost,
                'country_count'  => 0,
            ];
        }
        
        $serviceMap[$serviceCode]['total_stock']   += $count;
        $serviceMap[$serviceCode]['country_count'] += 1;
        if ($cost > 0 && $cost < $serviceMap[$serviceCode]['min_cost']) {
            $serviceMap[$serviceCode]['min_cost'] = $cost;
        }
    }
}

// Fetch service names from DB
$dbServices = [];
$dbResult = mysqli_query($conn, "SELECT service_id, service_name FROM service");
if ($dbResult) {
    while ($row = mysqli_fetch_assoc($dbResult)) {
        $dbServices[strtolower($row['service_id'])] = $row['service_name'];
    }
}

// Build final array
$final = [];
foreach ($serviceMap as $code => $info) {
    // Get display name: prefer DB name, else capitalize the code
    $displayName = strip_tags($dbServices[strtolower($code)] ?? ucfirst($code));
    $logoUrl = getServiceIcon($displayName, $code);
    
    $final[] = [
        'id'             => $code,
        'service_name'   => $displayName,
        'logo_url'       => $logoUrl,
        'total_stock'    => $info['total_stock'],
        'country_count'  => $info['country_count'],
    ];
}

// Sort manual order first, then popular services priority, then alphabetically
usort($final, function($a, $b) use ($serviceOrders) {
    $orderA = $serviceOrders[$a['id']] ?? 9999;
    $orderB = $serviceOrders[$b['id']] ?? 9999;
    if ($orderA !== $orderB) return $orderA <=> $orderB;
    
    $pA = getServicePriority($a['service_name']);
    $pB = getServicePriority($b['service_name']);
    if ($pA !== $pB) return $pA <=> $pB;
    return strcasecmp($a['service_name'], $b['service_name']);
});

if (ob_get_length()) ob_clean();
header('Content-Type: application/json');
echo json_encode(['service' => $final]);
exit;
