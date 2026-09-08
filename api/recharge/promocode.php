<?php
include __DIR__ . '/../../include/config.php';

if (!isset($_POST['code']) || empty($_POST['code'])) {
    echo '{"status":"500","message": "Enter Code"}';
} elseif (!isset($_POST['token']) || empty($_POST['token'])) {
    echo '{"status":"500","message": "Token not provided"}';
} else {
    $current_time_in_ist = date('Y-m-d H:i:s');
    $token = mysqli_real_escape_string($conn, $_POST['token']);
    // $find_token = new radiumsahil();
    $check_token = check_token($token, $conn);
    // $find_token->closeConnection();

    if ($check_token === false) {
        echo '{"status":"500","message": "Token Expired Please Logout And Login Again"}';
    } else {
        $user_id = $check_token;
        $promocode = mysqli_real_escape_string($conn, $_POST['code']);
        $sql1 = mysqli_query($conn, "SELECT * FROM promocode WHERE promocode='$promocode'");
        if (mysqli_num_rows($sql1) == 1) {
            $promo_data = mysqli_fetch_assoc($sql1);
            $promo_id = $promo_data['id'];
            $for_user = $promo_data['for_user'];
            $promo_amount = $promo_data['amount'];
            $sql20 = mysqli_query($conn, "SELECT * FROM promocode_history WHERE code_id='$promo_id' and user_id='$user_id'");
            if (mysqli_num_rows($sql20) == 0) {
                $sql2 = mysqli_query($conn, "SELECT * FROM promocode_history WHERE code_id='$promo_id'");
                if ($for_user > mysqli_num_rows($sql2)) {
                    $sql5 = mysqli_query($conn, "SELECT * FROM user_wallet WHERE user_id='" . $user_id . "'");
                    $user_data = mysqli_fetch_assoc($sql5);
                    $add_balance = $user_data['balance'] + $promo_amount;
                    $add_total_rc = $user_data['total_recharge'] + $promo_amount;

                    $sqladd = "INSERT INTO promocode_history (user_id, promocode, code_id, amount, date, status) VALUES ('$user_id', '$promocode', '$promo_id', '$promo_amount', '$current_time_in_ist', '1')";
                    mysqli_query($conn, $sqladd);
                    $sql6 = mysqli_query($conn, "UPDATE user_wallet SET balance='$add_balance', total_recharge='$add_total_rc' WHERE user_id='" . $user_id . "'");
                    $sqladd2 = "INSERT INTO user_transaction (user_id, amount, date, type, txn_id, status) VALUES ('$user_id', '$promo_amount', '$current_time_in_ist', 'Promocode Redeem', '$promocode', '1')";
                    mysqli_query($conn, $sqladd2);
                    echo '{"status": "200","message": "₦' . $promo_amount . ' Credit In Your Account"}';
                } else {
                    echo '{"status":"500","message": "Promocode Expired"}';
                }
            } else {
                echo '{"status":"500","message": "You Have Already Redeem Promocode"}';
            }
        } else {
            echo '{"status":"500","message": "Invalid Promocode"}';
        }
    }
    mysqli_close($conn);
}
?>