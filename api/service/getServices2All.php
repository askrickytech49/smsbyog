<?php
/**
 * getServices2All.php — Server 2 (5SIM)
 * Returns a deduplicated list of ALL services available across ALL countries.
 * Service-First buy flow: user picks service first, then country.
 */
include __DIR__ . '/../../include/config.php';
include __DIR__ . '/../../include/api_active_check.php';
require_api_active($conn, 2);
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

// Fetch 5SIM API details
$api_sql = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='2'");
if (!$api_sql) {
    echo json_encode(['service' => [], 'error' => 'Database error: ' . mysqli_error($conn)]);
    exit;
}
$api_data = mysqli_fetch_assoc($api_sql);
$api_url = rtrim($api_data['api_url'] ?? '', '/');

// Fetch ALL prices from 5SIM — returns {country: {product: {operator: {cost, count}}}}
$url = "https://5sim.net/v1/guest/prices";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$raw = curl_exec($ch);
$curl_error = curl_error($ch);
curl_close($ch);

$allPrices = $raw ? json_decode($raw, true) : [];

if (!$allPrices || !is_array($allPrices)) {
    $debug_msg = 'Failed to fetch services. cURL Error: ' . ($curl_error ?: 'Unknown') . ' | Raw: ' . substr((string)$raw, 0, 100);
    echo json_encode(['service' => [], 'error' => $debug_msg]);
    exit;
}

// Build unique service list with total stock
$serviceMap = []; // product_name => ['total_stock', 'country_count']

foreach ($allPrices as $country => $products) {
    if (!is_array($products)) continue;
    foreach ($products as $product => $operators) {
        if (!is_array($operators)) continue;
        foreach ($operators as $opName => $opDetails) {
            $count = (int)($opDetails['count'] ?? 0);
            if ($count <= 0) continue;
            
            if (!isset($serviceMap[$product])) {
                $serviceMap[$product] = [
                    'total_stock'   => 0,
                    'country_count' => 0,
                    'countries_seen' => [],
                ];
            }
            
            $serviceMap[$product]['total_stock'] += $count;
            if (!in_array($country, $serviceMap[$product]['countries_seen'])) {
                $serviceMap[$product]['countries_seen'][] = $country;
                $serviceMap[$product]['country_count']++;
            }
        }
    }
}

// Build final array
$final = [];
foreach ($serviceMap as $code => $info) {
    $displayName = ucfirst(str_replace('_', ' ', $code));
    $logoUrl = getServiceIcon($displayName, $code);
    
    $final[] = [
        'id'             => $code,
        'service_name'   => $displayName,
        'logo_url'       => $logoUrl,
        'total_stock'    => $info['total_stock'],
        'country_count'  => $info['country_count'],
    ];
}

// Sort alphabetically
usort($final, function($a, $b) {
    $pA = getServicePriority($a['service_name']);
    $pB = getServicePriority($b['service_name']);
    if ($pA !== $pB) {
        return ($pA < $pB) ? -1 : 1;
    }
    return strcasecmp($a['service_name'], $b['service_name']);
});

if (ob_get_length()) ob_clean();
header('Content-Type: application/json');
echo json_encode(['service' => $final]);
exit;
