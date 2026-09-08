<?php
include __DIR__ . '/../../include/config.php';
include __DIR__ . '/../../include/service_icons.php';
function makeCurlRequest($url, $api_key)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);


    curl_close($ch);


    return $response;
}



function custom_price($user_id, $service_id, $server_id, $price, $conn)
{
    $sql = mysqli_query($conn, "SELECT * FROM custom_price WHERE user_id='" . $user_id . "' AND service_id='" . $service_id . "' AND server_id='" . $server_id . "'");
    if (mysqli_num_rows($sql) > 0) {
        $data = mysqli_fetch_assoc($sql);
        if ($data['type'] == "flat") {
            return $data['discount'];
        } elseif ($data['type'] == "percent") {
            $percent = $data['discount'];
            $final_percent = ($percent / 100) * $price;
            $sub = $price - $final_percent;
            return $sub;
        }
    } else {
        return $price;
    }
}

if (!isset($_GET['server']) || $_GET['server'] == "") {
    echo "Invalid Server";
} elseif (!isset($_GET['token']) || $_GET['token'] == "") {
    echo "Invalid Token";
} else {
    $token = mysqli_real_escape_string($conn, $_GET['token']);
    $check_token = check_token($token, $conn); // Assuming this returns user_id

    if ($check_token === false) {
    echo 'Token Expired Please Logout And Login Again';
} else {
    $server = mysqli_real_escape_string($conn, $_GET['server']); // Country ID (if applicable)
    
    // Fetch API Configuration
    $api_sql = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='1'");
    $api_data = mysqli_fetch_assoc($api_sql);
        
    $conversion_rate = $api_data['rate'];
$fixed_profit = $api_data['profit_amount'];
    $url = $api_data['api_url'] . '/api/services?api_key=' . $api_data['api_key']; 
    // Ensure this endpoint matches VerifySMS
        
    // Fetch the list from VerifySMS
    $price_response = makeCurlRequest($url, $api_data['api_key']); 
    $api_prices = json_decode($price_response, true);
    
    $final = array();
    
    if (is_array($api_prices) && !empty($api_prices)) {
            
        foreach ($api_prices as $service_id => $details) {
            
            
            // 1. Fetch data from the flat object
            // 1. Fetch data from the flat object
$raw_api_price = (float)($details['cost'] ?? 0);
$service_name  = $details['name'] ?? 'Unknown Service';

// Force WhatsApp base price if API returns lower
// if (stripos($service_name, 'whatsapp') !== false && $raw_api_price < 1.75) {
//     $raw_api_price = 1.75;
// }
            $raw_api_price = (float)($details['cost'] ?? 0);
            $service_name  = $details['name'] ?? 'Unknown Service';
            
            // Convert boolean true/false to 1/0 for your logic
            $stock_count   = ($details['in_stock'] === true) ? 1 : 0;
    
            // 2. Perform Calculations
           // Convert USD price to Naira
$base_price_naira = $raw_api_price * $conversion_rate;

// Add fixed profit
$base_calculated_price = $base_price_naira + $fixed_profit;
        
            // 3. Apply User Custom Discount
            $op_price = custom_price($check_token, $service_id, $server, $base_calculated_price, $conn);
            $final_price = round($op_price, 2);
        
            // --- FILTER: Only show if price is valid and in stock ---
            if ($final_price > 0 && $stock_count > 0) {
                
                // Use service_icons helper — bypasses DB mismatches entirely
                $logo_url = getServiceIcon($service_name, $service_id);
        
                array_push($final, array(
                    'id'            => $service_id,
                    'service_name'  => $service_name,
                    'service_price' => $final_price,
                    'server_id'     => $server,
                    'logo_url'      => $logo_url,
                    'stock'         => $stock_count 
                ));
            } 
        }
    
        // Clean output and return JSON
        if (ob_get_length()) ob_clean(); 
        header('Content-Type: application/json');
        echo json_encode(array('service' => $final));
        exit; 
    
    } else {
        header('Content-Type: application/json');
        echo json_encode(array('service' => [], 'error' => 'API returned no data or invalid format'));
        exit;
    }
}
mysqli_close($conn);
}


