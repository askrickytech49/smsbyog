<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('memory_limit', '256M');
set_time_limit(60);

/**
 * getServices2All.php — Server 2 (5SIM)
 * Returns a deduplicated list of ALL services available across ALL countries.
 * Service-First buy flow: user picks service first, then country.
 */
include __DIR__ . '/../../include/config.php';
include __DIR__ . '/../../include/api_active_check.php';
// require_api_active($conn, 2);
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
$api_url = "https://5sim.net";
$api_key = "eyJhbGciOiJSUzUxMiIsInR5cCI6IkpXVCJ9.eyJleHAiOjE4MjA2ODU5OTksImlhdCI6MTc4OTE0OTk5OSwicmF5IjoiYmMxNTVkYzI1NGNkZjlhZThlYTg3OTFjN2Y1MzYwNGEiLCJzdWIiOjQ0ODMyNjV9.PxCFWUR6bP29BpMt1PKfAdHSmdmXUQriKLq6nPYEkWldyephtuijh4BqnU_EtMTgxXdLXwmX-hNJKKBNEMZBG-p8WK1o6usLPOdTwWu3Lw0yOcS0e-YwPUxTPKu0ocZSdSP5FJtCUZMTKCTIe7nZmWBngLkyUmuPQzbNG12KF5JL0G6_G8_iG3WBQSMg7yeQF-13l6KOzc5aA56V4PdgiVHlTYbibicINth7evneW7I7pT_HLStvbUjLtgA8mEWsSvUFMEknyflUkwZi2Yo2sjSOEZH50Tc5KYz7iKFIq7p5KKmf3J_5pM7PFri1I8yXpSqUeEjUoGtN4QnDRCSRmg";

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
