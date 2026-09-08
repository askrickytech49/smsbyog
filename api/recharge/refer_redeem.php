<?php
include __DIR__ . '/../../include/config.php';

if (!isset($_POST['amount']) || empty($_POST['amount'])) {
    echo '{"status":"500","msg": "Enter amount"}';
} elseif (!isset($_POST['token']) || empty($_POST['token'])) {
    echo '{"status":"500","msg": "Token not provided"}';
} else {
    $current_time_in_ist = date('Y-m-d H:i:s');
    $token = mysqli_real_escape_string($conn, $_POST['token']);
    // $find_token = new radiumsahil();
    $check_token = check_token($token, $conn);
    // $find_token->closeConnection();

    if ($check_token === false) {
        echo '{"status":"500","msg": "Token Expired Please Logout And Login Again"}';
    } else {
        $amount = (float) $_POST['amount']; // Convert the amount to a float
        if ($amount < 0) {
            echo '{"status": "500", "msg": "Amount cannot be less than 0"}';
        } else {
            $sql = $conn->query("SELECT * FROM refer_data WHERE user_id='$check_token'");
            $refer_data = $sql->fetch_assoc();
            $refer_balance = (float) $refer_data['balance']; // Convert balance to a float
            $refer_transfer = (float) $refer_data['transfer']; // Convert transfer to a float
            if ($site_data['min_redeem'] <= $amount) {
                if ($refer_balance >= $amount) {
                    $sql5 = mysqli_query($conn, "SELECT * FROM user_wallet WHERE user_id='$check_token'");
                    $user_data = mysqli_fetch_assoc($sql5);
                    $user_balance = (float) $user_data['balance']; // Convert balance to a float
                    $user_total_recharge = (float) $user_data['total_recharge']; // Convert total_recharge to a float

                    $add_balance = $amount + $user_balance;
                    $add_rc = $amount + $user_total_recharge;
                    $sql6 = mysqli_query($conn, "UPDATE user_wallet SET balance='$add_balance', total_recharge='$add_rc' WHERE user_id='$check_token'");
                    $sqladd2 = "INSERT INTO user_transaction (user_id, amount, date, type, txn_id, status) VALUES ('$check_token', '$amount', '$current_time_in_ist', 'Refer Reward', 'reward', '1')";
                    mysqli_query($conn, $sqladd2);
                    $sqladd3 = "INSERT INTO refer_history (user_id, type, date, status, amount) VALUES ('$check_token', 'debit', '$current_time_in_ist', '1', '$amount')";
                    mysqli_query($conn, $sqladd3);
                    $cut_balance = $refer_balance - $amount;
                    $add_transfer = $refer_transfer + $amount;
                    mysqli_query($conn, "UPDATE refer_data SET balance='$cut_balance', transfer='$add_transfer' WHERE user_id='$check_token'");
                    echo '{"status": "200","msg": "Transfer Successful"}';
                } else {
                    echo '{"status": "500","msg": "Not Enough Refer Balance"}';
                }
            } else {
                echo '{"status": "500","msg": "Minimum Transfer Amount is ₦' . $site_data['min_redeem'] . '"}';
            }
        }
    }
    mysqli_close($conn);
}
?>