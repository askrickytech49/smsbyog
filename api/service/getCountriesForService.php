<?php
/**
 * getCountriesForService.php — Server 1 (TigerSMS)
 * Given a service code, returns all countries that have this service in stock,
 * along with per-country pricing (converted to Naira with markup).
 */
include __DIR__ . '/../../include/config.php';
include __DIR__ . '/../../include/api_active_check.php';
require_api_active($conn, 8);

function custom_price($user_id, $service_id, $server_id, $price, $conn) {
    $sql = mysqli_query($conn, "SELECT * FROM custom_price WHERE user_id='" . $user_id . "' AND service_id='" . $service_id . "' AND server_id='" . $server_id . "'");
    if (mysqli_num_rows($sql) > 0) {
        $data = mysqli_fetch_assoc($sql);
        if ($data['type'] == "flat") {
            return $data['discount'];
        } elseif ($data['type'] == "percent") {
            $percent = $data['discount'];
            $final_percent = ($percent / 100) * $price;
            return $price - $final_percent;
        }
    }
    return $price;
}

if (!isset($_GET['token']) || $_GET['token'] == "") {
    echo json_encode(['countries' => [], 'error' => 'Token Blank']);
    exit;
}
if (!isset($_GET['service']) || $_GET['service'] == "") {
    echo json_encode(['countries' => [], 'error' => 'Service not specified']);
    exit;
}

$token = mysqli_real_escape_string($conn, $_GET['token']);
$check_token = check_token($token, $conn);

if ($check_token === false) {
    echo json_encode(['countries' => [], 'error' => 'Token Expired']);
    exit;
}

$service = mysqli_real_escape_string($conn, $_GET['service']);
$user_id = $check_token;

// Fetch TigerSMS API details
$api_sql = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='8'");
$api_data = mysqli_fetch_assoc($api_sql);
$api_url = $api_data['api_url'];
$api_key = $api_data['api_key'];
$conversion_rate = (float)$api_data['rate'];
$fixed_profit    = (float)$api_data['profit_amount'];

// Fetch ALL prices
$url = "{$api_url}/stubs/handler_api.php?api_key={$api_key}&action=getPrices";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$raw = curl_exec($ch);
curl_close($ch);

$allPrices = $raw ? json_decode($raw, true) : [];

// Fetch countries list for name mapping
$countries_url = "{$api_url}/stubs/handler_api.php?api_key={$api_key}&action=getCountries";
$ch2 = curl_init($countries_url);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_TIMEOUT, 10);
curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
$rawCountries = curl_exec($ch2);
curl_close($ch2);
$countriesList = $rawCountries ? json_decode($rawCountries, true) : [];

// Build countries that have this service
$final = [];

foreach ($allPrices as $countryCode => $services) {
    if (!is_array($services)) continue;
    if (!isset($services[$service])) continue;
    
    $details = $services[$service];
    $count = (int)($details['count'] ?? 0);
    $cost  = (float)($details['cost'] ?? 0);
    
    if ($count <= 0 || $cost <= 0) continue;
    
    // Calculate price in Naira
    $base_price_naira = $cost * $conversion_rate;
    $base_with_profit = $base_price_naira + $fixed_profit;
    
    // Apply custom user pricing
    $final_price = custom_price($user_id, $service, $countryCode, $base_with_profit, $conn);
    $final_price = round($final_price, 2);
    
    if ($final_price <= 0) continue;
    
    // Get country name
    $countryName = 'Country ' . $countryCode;
    if (isset($countriesList[$countryCode])) {
        $countryName = $countriesList[$countryCode]['eng'] ?? $countriesList[$countryCode]['name'] ?? $countryName;
    }
    
    $final[] = [
        'country_code' => $countryCode,
        'country_name' => $countryName,
        'price'        => $final_price,
        'stock'        => $count,
    ];
}

// Sort by country name
usort($final, function($a, $b) { return strcasecmp($a['country_name'], $b['country_name']); });

if (ob_get_length()) ob_clean();
header('Content-Type: application/json');
echo json_encode(['countries' => $final]);
exit;
