<?php
require_once __DIR__ . '/../../include/config.php';
function sendTelegramMessage($chat_id, $message_text)
{
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

            if ($total_recharge < $total_use) {
                $minus = $total_use - $total_recharge;
                $cut_balance = $balance - $minus;
                if ($balance >= $minus) {
                    // mysqli_query($conn, "UPDATE user_wallet SET balance='" . $cut_balance . "' WHERE user_id='" . $row['user_id'] . "'");
                    echo "Email:" . $user_data['email'] . " - Minus: " . $minus . " - Balance: " . $user_wallet['balance'] . " - Type: Reversed";
                    echo "<br>";
                    $i++;
                    $l += $minus;
                } else {
                    $sql30 = mysqli_query($conn, "SELECT * FROM active_number WHERE status='2' AND active_status='2' AND user_id='".$row['user_id']."'");
                    if ($sql30->num_rows > 0) {
                        $add = 0;
                        $otp = 0;
                        while ($row2 = $sql30->fetch_assoc()) {
                            $otp++;
                            // mysqli_query($conn, "UPDATE active_number SET active_status='1', status='3' WHERE id='" . $row2['id'] . "'");
                            $add += $row2['service_price'];
                        }
                        $bal2 = $balance + $add;
                        $cut_otp = $user_wallet['total_otp'] - $otp;
                        if ($bal2 >= $minus) {
                $cut_balance2 = $bal2 - $minus;
                // mysqli_query($conn, "UPDATE user_wallet SET balance='" . $cut_balance2 . "', total_otp='" . $cut_otp . "' WHERE user_id='" . $row['user_id'] . "'");
                echo "Email:" . $user_data['email'] . " - Minus: " . $minus . " - Balance: " . $bal2 . " - Type: CANCLE NUMBER REFUND";
                echo "<br>";
                        }else{
                // mysqli_query($conn, "UPDATE user_wallet SET balance='0' WHERE user_id='" . $row['user_id'] . "'");
                            echo "Email:" . $user_data['email'] . " - Minus: " . $minus . " - Balance: " . $user_wallet['balance'] . " - Type: FAILED 1";
                            echo "<br>";
                        }
                    } else {
                // mysqli_query($conn, "UPDATE user_wallet SET balance='0' WHERE user_id='" . $row['user_id'] . "'");
                        echo "Email:" . $user_data['email'] . " - Minus: " . $minus . " - Balance: " . $user_wallet['balance'] . " - Type: FAILED 2";
                        echo "<br>";
                    }
                }
            }
        }
    }

} else {
    echo "0 results";
}
// echo "ok";
// Close the connection
$conn->close();
?>