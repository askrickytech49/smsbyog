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
    $l = 0;
    while ($row = $result->fetch_assoc()) {
        $sql = mysqli_query($conn, "SELECT * FROM user_data WHERE id='" . $row['user_id'] . "'");
        $user_data = mysqli_fetch_assoc($sql);
        $sql2 = mysqli_query($conn, "SELECT * FROM user_wallet WHERE user_id='" . $row['user_id'] . "'");
        $user_wallet = mysqli_fetch_assoc($sql2);
        if ($user_data['status'] == 1) {
          $balance = $user_wallet['balance'];
          $total_buy = $row['total_purchases'];
          $total_recharge = $user_wallet['total_recharge'];
          $total_use = $balance + $total_buy;
         
          if($total_recharge < $total_use){
             $minus = $total_use - $total_recharge;
             $cut_balance = $balance - $minus;
             if($balance >= $minus){
   mysqli_query($conn, "UPDATE user_wallet SET balance='" . $cut_balance . "' WHERE user_id='" .$row['user_id']. "'");
             echo $minus." -";
             echo $user_data['email'];
             echo "<br>";
             $i ++;
             $l +=  $minus;
             }else{
                 if($balance > 0){
                 mysqli_query($conn, "UPDATE user_wallet SET balance='0' WHERE user_id='" . $row['user_id'] . "'");
                 }
                  echo "failed - ".$balance;
                  echo "-".$minus." -";
                  echo "<br>";
             }
          }
        }
    }
    echo "<br>";
    echo "Total invalid :" . $i;
    echo "<br>";
    echo "Total invalid 2 :" . $l;
} else {
    echo "0 results";
}
// echo "ok";
// Close the connection
$conn->close();
?>