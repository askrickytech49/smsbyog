<?php
include __DIR__ . '/../../include/config.php';
include __DIR__ . '/../../include/api_active_check.php';
require_api_active($conn, 8);

include __DIR__ . '/../../include/service_icons.php';
function makeCurlRequest($url, $api_key, $action, $country)
{
    $url .= '?api_key=' . urlencode($api_key);
    $url .= '&action=' . urlencode($action);
    $url .= '&country=' . urlencode($country);

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
        
        $country_code = mysqli_real_escape_string($conn, $_GET['server']);
        $server = $country_code; // Keeping $server variable name for compatibility with custom_price
        
        // Fetch API details for TigerSMS (Server 1 is API ID 8)
        $api_sql = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='8'");
        $api_data = mysqli_fetch_assoc($api_sql);
        
        $conversion_rate = $api_data['rate'];
        $fixed_profit = $api_data['profit_amount'];

        $url = $api_data['api_url'] . '/stubs/handler_api.php';
        
        $price_response = makeCurlRequest($url, $api_data['api_key'], 'getPrices', $country_code);
        $api_prices = json_decode($price_response, true);
        
        $stock_response = makeCurlRequest($url, $api_data['api_key'], 'getNumbersStatus', $country_code);
        $stock_data = json_decode($stock_response, true);

        // $sql = "SELECT * FROM service WHERE server_id = '" . $server . "'";
        $sql = "SELECT * FROM service";
        $result = mysqli_query($conn, $sql);

        $final = array();

        if ($result) {
           while ($row = mysqli_fetch_assoc($result)) {
            $service_id = $row['service_id'];
            
            // 1. Fetch RAW API Price
            $raw_api_price = 0;
            if (isset($api_prices[$country_code][$service_id])) {
                $raw_api_price = $api_prices[$country_code][$service_id]['cost'] ?? current($api_prices[$country_code][$service_id]);
            }
        
            // Skip services that don't exist on the upstream API
            // (fixed_profit alone would make them appear valid)
            if ($raw_api_price <= 0) continue;

            // 2. Perform Calculations
            // Convert USD price to Naira
$base_price_naira = $raw_api_price * $conversion_rate;

// Add fixed profit
$base_calculated_price = $base_price_naira + $fixed_profit;
            // $base_calculated_price = $with_markup * $conversion_rate;
        
            // 3. Apply User Custom Discount
            $op_price = custom_price($check_token, $service_id, $server, $base_calculated_price, $conn);
            $final_price = round($op_price, 2);
        
            // --- FILTER: Only proceed if price is greater than 0 ---
            if ($final_price > 0) {
                
                // Use service_icons helper — bypasses DB mismatches entirely
                $logo_url = getServiceIcon($row['service_name'], $service_id);
        
                // Stock Logic
                $short_code = $service_id . '_0';
                $send_stock = $stock_data[$short_code] ?? 0;
        
                // Add to final array only if it passed the price check
                array_push($final, array(
                    'id' => $service_id,
                    'service_name' => $row['service_name'],
                    'service_price' => $final_price,
                    'server_id' => $server,
                    'logo_url' => $logo_url,
                    'stock' => $send_stock
                ));
            } 
            // If price is 0, the code simply skips this service and moves to the next row
        }

          

            // CLEAN THE OUTPUT BUFFER to prevent "Warning" texts from breaking JSON
            if (ob_get_length()) ob_clean(); 
            
            header('Content-Type: application/json');
            echo json_encode(array('service' => $final));
            
            // IMPORTANT: exit here so the "Token Expired" warning at the bottom doesn't run
            exit; 
        } else {
            echo json_encode(array('service' => [], 'error' => 'No result'));
            exit;
        }

    }
    mysqli_close($conn);
}








if (!isset($_GET['server']) || $_GET['server'] == "") {
    echo "Invalid Server";
} elseif (!isset($_GET['token']) || $_GET['token'] == "") {
    echo "Invalid Token";
} else {
    $token = mysqli_real_escape_string($conn, $_GET['token']);
    // $find_token = new radiumsahil();
    $check_token = check_token($token, $conn);
    // $find_token->closeConnection();
    if ($check_token === false) {
        echo 'Token Expired Please Logout And Login Again';
    } else {
        $server = $_GET['server'];
        $sql = "SELECT * FROM service WHERE server_id = '" . $server . "'";
        // $sql = "SELECT * FROM service";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            $service_sql = mysqli_fetch_assoc($result);
            $server_sql = mysqli_query($conn, "SELECT * FROM otp_server WHERE id='" . $service_sql['server_id'] . "'");
            $server_data = mysqli_fetch_assoc($server_sql);
            $api_sql = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='" . $server_data['api_id'] . "'");
            $api_data = mysqli_fetch_assoc($api_sql);

            $url = $api_data['api_url'] . '/stubs/handler_api.php';
            $action = 'getNumbersStatus';
            $country = $server_data['server_code'];

            $stock_datas = makeCurlRequest($url, $api_data['api_key'], $action, $country);
            $stock_data = json_decode($stock_datas, true);
            

            $final = array();

            $sql_logo = mysqli_query($conn, "SELECT * FROM service_icon WHERE short_code='" . $service_sql['service_id'] . "'");
            if (mysqli_num_rows($sql_logo) > 0) {
                $logo_data = mysqli_fetch_assoc($sql_logo);
                $logo_url = $logo_data['img_url'];
            } else {
                $logo_url = "https://www.google.com/s2/favicons?sz=64&domain=" . urlencode(strtolower($service_id)) . ".com";
            }
            $short_code = $service_sql['service_id'] . '_0';
            $send_stock = false;
            if (isset($stock_data[$short_code])) {
                $send_stock = $stock_data[$short_code];
            } else {
                $send_stock = false;
            }
            $op_price = custom_price($check_token, $service_sql['service_id'], $service_sql['server_id'], $service_sql['service_price'], $conn);
            array_push($final, array(
                'id' => $service_sql['service_id'],
                'service_name' => $service_sql['service_name'],
                'service_price' => $op_price,
                'server_id' => $service_sql['server_id'],
                'logo_url' => $logo_url,
                'stock' => $send_stock
            )
            );

            while ($row = mysqli_fetch_assoc($result)) {
                $sql_logo = mysqli_query($conn, "SELECT * FROM service_icon WHERE short_code='" . $row['service_id'] . "'");
                if (mysqli_num_rows($sql_logo) > 0) {
                    $logo_data = mysqli_fetch_assoc($sql_logo);
                    $logo_url = $logo_data['img_url'];
                } else {
                    $logo_url = "https://www.google.com/s2/favicons?sz=64&domain=" . urlencode(strtolower($service_id)) . ".com";
                }
                $short_code = $row['service_id'] . '_0';
                $send_stock = false;
                if (isset($stock_data[$short_code])) {
                    $send_stock = $stock_data[$short_code];
                } else {
                    $send_stock = false;
                }
                $op_price = custom_price($check_token, $row['service_id'], $row['server_id'], $row['service_price'], $conn);

                array_push($final, array(
                    'id' => $row['service_id'],
                    'service_name' => $row['service_name'],
                    'service_price' => $op_price,
                    'server_id' => $row['server_id'],
                    'logo_url' => $logo_url,
                    'stock' => $send_stock
                )
                );
            }

            $data = array('service' => $final);

            echo json_encode($data);
        } else {
            echo "Error executing database";
        }

    }
    mysqli_close($conn);
}