<?php
require_once __DIR__ . '/../../include/config.php';
function sendTelegramMessage($chat_id, $message_text) {
    $apiToken = "6609792423:AAFU7uPwAch1Xz7s4jrFQIaNc3NYMTvtEfs"; // Replace with your bot's API token

    $data = [
        'chat_id' => $chat_id,
        'text' => $message_text
    ];

    $url = "https://api.telegram.org/bot$apiToken/sendMessage";

    // Initialize curl session
    $ch = curl_init($url);

    // Configure curl options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    
    // Execute the curl session
    $response = curl_exec($ch);
    
    // Check for errors
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    
    // Close the curl session
    curl_close($ch);
    
    // Return the response
    return $response;
}




$sql = "SELECT user_id, SUM(CAST(service_price AS DECIMAL(10, 2))) AS total_purchases 
        FROM active_number 
        WHERE status = '1' 
        GROUP BY user_id 
        ORDER BY total_purchases DESC";
$result = $conn->query($sql);

// Check if there are results
if ($result->num_rows > 0) {
    $i = 0;
    while ($row = $result->fetch_assoc()) {
        $sql = mysqli_query($conn, "SELECT * FROM user_data WHERE id='" . $row['user_id'] . "'");
        $user_data = mysqli_fetch_assoc($sql);
        $sql2 = mysqli_query($conn, "SELECT * FROM user_wallet WHERE user_id='" . $row['user_id'] . "'");
        $user_wallet = mysqli_fetch_assoc($sql2);
        if ($user_data['status'] == 1) {
            if ($row["total_purchases"]-50 > $user_wallet['total_recharge']) {
                $sql34 = mysqli_query($conn, "UPDATE user_data SET status='2' WHERE id='" . $row['user_id'] . "'");
                echo "Email: " . $user_data["email"] . " - Total Purchases: ₦" . $row["total_purchases"] . " - Total Recharge :" . $user_wallet['total_recharge'] . "- Balance :" . $user_wallet['balance'] . " - Status :" . $user_data['status'] . "- Register Date :" . $user_data['register_date'] . "<br>";
                $message1 = "⭐ NEW USER BAN ⭐\n";
                $message1 .= "Email: ".$user_data['email']."\n";
                $message1 .= "Balance: ₦" . $user_wallet["balance"] . "\n";
                $message1 .= "Total Recharge: ₦" . $user_wallet['total_recharge'] . "\n";
                $message1 .= "Total Purchase: ₦" . $row["total_purchases"]."\n";
                $message1 .= "Total Otp: " . $user_wallet["total_otp"]."\n";
                $message1 .= "Register Date: " . $user_data['register_date']."\n";
                $message1 .= "Reason: Invalid Purchase \n";
                $message1 .= "Website : " . $site_data['web_name']."\n";
                  $response = sendTelegramMessage("-1002195298182", $message1);
                $i++;
            }
        }
    }
    echo "<br>";
    echo "Total invalid :" . $i;
} else {
    echo "0 results";
}
// echo "ok";
// Close the connection
$conn->close();
?>