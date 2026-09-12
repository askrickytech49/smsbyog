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
$api_prices = getfunction($services_url, $api_key);

if (!is_array($api_prices)) {
    echo json_encode(['status' => '500', 'service' => [], 'error' => 'Failed to fetch services']);
    exit;
}

$final = [];

foreach ($api_prices as $details) {
    $service_code = $details['service_code'] ?? '';
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

usort($final, function($a, $b) {
    $pA = getServicePriority($a['service_name']);
    $pB = getServicePriority($b['service_name']);
    if ($pA !== $pB) return $pA <=> $pB;
    return strcasecmp($a['service_name'], $b['service_name']);
});

if (ob_get_length()) ob_clean();
header('Content-Type: application/json');
echo json_encode(['status' => '200', 'service' => $final]);
exit;
