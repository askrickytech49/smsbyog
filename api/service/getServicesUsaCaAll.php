<?php
/**
 * getServicesUsaCaAll.php — USA + Canada (Dino)
 * Returns a list of ALL services available.
 * Service-First buy flow: user picks service first, then country.
 */
include __DIR__ . '/../../include/config.php';
include __DIR__ . '/../../include/api_active_check.php';
require_api_active($conn, 3);
include __DIR__ . '/../../include/service_icons.php';

if (!isset($_GET['token']) || $_GET['token'] == "") {
    echo json_encode(['status' => '500', 'service' => [], 'error' => 'Token Blank']);
    exit;
}

$token = mysqli_real_escape_string($conn, $_GET['token']);
$check_token = check_token($token, $conn);

if ($check_token === false) {
    echo json_encode(['status' => '500', 'service' => [], 'error' => 'Token Expired']);
    exit;
}

// Fetch Dino API details
$api_sql = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='3'");
$api_data = mysqli_fetch_assoc($api_sql);
$api_url = rtrim($api_data['api_url'], '/');
$api_key = $api_data['api_key'];

$services_url = "{$api_url}/sms-otp/services";

include_once __DIR__ . '/../../include/api_cache.php';
$cache_key = 'dinommo_services_all_' . md5($api_key);
$api_prices = api_cache_get($cache_key, 120);

if (!$api_prices) {
    $api_prices = getfunction($services_url, $api_key);
    
    // Fallback to stale cache if failed
    if (!is_array($api_prices) || empty($api_prices)) {
        $api_prices = api_cache_get_stale($cache_key);
    }
    
    if (is_array($api_prices) && !empty($api_prices)) {
        api_cache_set($cache_key, $api_prices);
    }
}

if (!is_array($api_prices) || empty($api_prices)) {
    echo json_encode(['status' => '500', 'service' => [], 'error' => 'Failed to fetch services']);
    exit;
}

// Fetch active services and their manual sort orders for API ID 3 (DinoSMS)
$activeServices = [];
$serviceOrders = [];
$activeRes = mysqli_query($conn, "SELECT service_code FROM api_active_services WHERE api_id='3'");
if ($activeRes) {
    while ($r = mysqli_fetch_assoc($activeRes)) {
        $activeServices[$r['service_code']] = true;
    }
}
$orderRes = mysqli_query($conn, "SELECT service_code, sort_order FROM api_service_order WHERE api_id='3'");
if ($orderRes) {
    while ($r = mysqli_fetch_assoc($orderRes)) {
        $serviceOrders[$r['service_code']] = (int)$r['sort_order'];
    }
}

$final = [];

foreach ($api_prices as $details) {
    $service_code = $details['service_code'] ?? '';
    
    if (!isset($activeServices[$service_code])) continue; // Only show active services

    $service_name = $details['service_name'] ?? $service_code;
    $is_active    = $details['is_active'] ?? false;
    
    if (!$is_active) continue;
    
    $logo_url = getServiceIcon($service_name, $service_code);
    
    $final[] = [
        'id'           => $service_code,
        'service_name' => $service_name,
        'logo_url'     => $logo_url,
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
echo json_encode(['status' => '200', 'service' => $final]);
exit;
