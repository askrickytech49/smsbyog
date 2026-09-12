<?php
include __DIR__ . '/../../include/config.php';
include __DIR__ . '/../../include/api_active_check.php';
// require_api_active($conn, 2);

include __DIR__ . '/../../include/service_icons.php';
function makeCurlRequest($url, $api_key, $country)
{
    $url .= '?country=' . urlencode($country);
    
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
    $server = mysqli_real_escape_string($conn, $_GET['server']); // Country ID (e.g., 187)
    
    // Fetch rate and profit from DB
    $api_sql = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='2'");
    $api_data = $api_sql ? mysqli_fetch_assoc($api_sql) : null;
    $conversion_rate = $api_data ? $api_data['rate'] : 1500;
    $fixed_profit = $api_data ? $api_data['profit_amount'] : 200;
    
    $api_url = "https://5sim.net";
    $api_key = "eyJhbGciOiJSUzUxMiIsInR5cCI6IkpXVCJ9.eyJleHAiOjE4MjA2ODU5OTksImlhdCI6MTc4OTE0OTk5OSwicmF5IjoiYmMxNTVkYzI1NGNkZjlhZThlYTg3OTFjN2Y1MzYwNGEiLCJzdWIiOjQ0ODMyNjV9.PxCFWUR6bP29BpMt1PKfAdHSmdmXUQriKLq6nPYEkWldyephtuijh4BqnU_EtMTgxXdLXwmX-hNJKKBNEMZBG-p8WK1o6usLPOdTwWu3Lw0yOcS0e-YwPUxTPKu0ocZSdSP5FJtCUZMTKCTIe7nZmWBngLkyUmuPQzbNG12KF5JL0G6_G8_iG3WBQSMg7yeQF-13l6KOzc5aA56V4PdgiVHlTYbibicINth7evneW7I7pT_HLStvbUjLtgA8mEWsSvUFMEknyflUkwZi2Yo2sjSOEZH50Tc5KYz7iKFIq7p5KKmf3J_5pM7PFri1I8yXpSqUeEjUoGtN4QnDRCSRmg";
    
    $url = $api_url . '/v1/guest/prices';
    $country_code = $server;
    
    // Fetch with caching (2-minute TTL)
    include_once __DIR__ . '/../../include/api_cache.php';
    $cache_key = '5sim_country_prices_' . $country_code;
    $api_prices = api_cache_get($cache_key, 120);
    
    if (!$api_prices) {
        $price_response = makeCurlRequest($url, $api_key, $country_code);
        $api_prices = json_decode($price_response, true);
        if ($api_prices && is_array($api_prices)) {
            api_cache_set($cache_key, $api_prices);
        }
    }

    $final = array();

    if (isset($api_prices[$country_code]) && is_array($api_prices[$country_code])) {
        
        foreach ($api_prices[$country_code] as $service_id => $operators) {
    
    // Loop through every operator (vodafone, virtual60, etc.) for this service
    foreach ($operators as $op_name => $op_details) {
        
        $stock_count = (int)$op_details['count'];

        // FILTER: Only proceed if there is stock available
        if ($stock_count > 0) {
            
            $raw_api_price = (float)$op_details['cost'];
            
            // Format a friendly name: e.g., "Facebook (Vodafone)" or "Whatsapp (Virtual60)"
            $display_name = ucfirst($service_id) . ' (' . ucfirst($op_name) . ')';

            // 1. Perform Calculations (Markup and Conversion)
                  // Convert USD price to Naira
$base_price_naira = $raw_api_price * $conversion_rate;

// Add fixed profit
$base_calculated_price = $base_price_naira + $fixed_profit;
            // $base_calculated_price = $with_markup * $conversion_rate;
        
            // 2. Apply User Custom Discount
            // Note: passing $service_id . '_' . $op_name might be better for custom pricing
            $op_price = custom_price($check_token, $service_id, $server, $base_calculated_price, $conn);
            $final_price = round($op_price, 2);
        
            if ($final_price > 0) {
                
                // Use service_icons helper — bypasses DB mismatches entirely
                $logo_url = getServiceIcon($display_name, $service_id);
        
                // 4. Push as a unique entry
                array_push($final, array(
                    'id'            => $service_id,      // The code to buy (e.g. facebook)
                    'operator'      => $op_name,         // The operator to buy (e.g. vodafone)
                    'service_name'  => $display_name,    // Full name for UI
                    'service_price' => $final_price,
                    'server_id'     => $server,
                    'logo_url'      => $logo_url,
                    'stock'         => $stock_count
                ));
            } 
        }
    }
}

        // Clean output and return JSON
        if (ob_get_length()) ob_clean(); 
        header('Content-Type: application/json');
        echo json_encode(array('service' => $final));
        exit; 

    } else {
        header('Content-Type: application/json');
        echo json_encode(array('service' => [], 'error' => 'No services available for this country'));
        exit;
    }
}
mysqli_close($conn);
}


