<?php
include __DIR__ . '/../include/config.php';
$_GET['token'] = 'd4c518e178122dcc9a318a0edbd8cdd9'; // assuming we had one, let's just bypass
$token = 'test';
$user_id = 1;
$service = 'whatsapp';

// Fetch 5SIM API details
$api_sql = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='2'");
$api_data = mysqli_fetch_assoc($api_sql);
$conversion_rate = (float)$api_data['rate'];
$fixed_profit    = (float)$api_data['profit_amount'];

$url = "https://5sim.net/v1/guest/prices?product=" . urlencode($service);
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$raw = curl_exec($ch);
curl_close($ch);

$pricesData = $raw ? json_decode($raw, true) : [];

$final = [];

foreach ($pricesData as $country => $operators) {
    if (!is_array($operators)) continue;
    
    // Find the best operator (cheapest with stock)
    $bestCost = PHP_FLOAT_MAX;
    $bestOperator = 'any';
    $totalStock = 0;
    
    foreach ($operators as $opName => $opDetails) {
        $count = (int)($opDetails['count'] ?? 0);
        $cost  = (float)($opDetails['cost'] ?? 0);
        
        if ($count <= 0) continue;
        $totalStock += $count;
        
        if ($cost > 0 && $cost < $bestCost) {
            $bestCost = $cost;
            $bestOperator = $opName;
        }
    }
    
    if ($totalStock <= 0 || $bestCost == PHP_FLOAT_MAX) continue;
    
    $final[] = [
        'country_code' => $country,
        'operator'     => $bestOperator,
        'price'        => $bestCost,
        'stock'        => $totalStock,
    ];
}

echo "Found " . count($final) . " countries.\n";
print_r(array_slice($final, 0, 2));
