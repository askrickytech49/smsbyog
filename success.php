<?php
require_once __DIR__ . "/include/config.php";

$current_time_in_ist = date('Y-m-d H:i:s');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verifying Payment – AuthPadi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Nunito -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .verify-card {
            background: #fff;
            border-radius: 16px;
            padding: 40px 30px;
            max-width: 420px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0,0,0,.08);
        }

        .logo {
            font-weight: 800;
            font-size: 22px;
            color: #e10700;
            margin-bottom: 20px;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
            color: #e10700;
        }

        .text-muted {
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="verify-card">
    <div class="logo">AuthPadi</div>
    <div class="spinner-border mb-4" role="status"></div>
    <h5 class="fw-bold mb-2">Verifying Payment</h5>
    <p class="text-muted mb-0">
        Please wait while we confirm your transaction.<br>
        Do not close this page.
    </p>
</div>

<?php
/* ================================
   ORIGINAL LOGIC — UNTOUCHED
   ================================ */

if (isset($_GET['status'])) {

    if ($_GET['status'] == 'cancelled') {
        redirect('index');
        exit();
    }

    elseif (
        ($_GET['status'] == 'successful' || $_GET['status'] == 'completed')
        && isset($_GET['transaction_id'])
    ) {

        $txn_id = $_GET['transaction_id'];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.flutterwave.com/v3/transactions/{$txn_id}/verify",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Authorization: Bearer " . SECRET_KEY
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $res = json_decode($response);

        if ($res && $res->status == 'success') {

            $amountPaid = $res->data->charged_amount;
            $email = $res->data->customer->email;

            $amount = (float) str_replace(',', '', number_format($amountPaid, 2));

            $sql = mysqli_query($conn, "SELECT * FROM user_data WHERE email='$email'");
            $user_data = mysqli_fetch_assoc($sql);

            if ($user_data) {

                $sql1 = mysqli_query($conn, "SELECT * FROM upi_recharge WHERE txn_id='$txn_id'");
                if (mysqli_num_rows($sql1) == 0) {

                    $user_id = $user_data['id'];
                    $sql2 = mysqli_query($conn, "SELECT * FROM user_wallet WHERE user_id='$user_id'");
                    $user_wallet = mysqli_fetch_assoc($sql2);

                    if ($user_wallet) {

                        $add_balance = $user_wallet['balance'] + $amount;
                        $add_total_rc = $user_wallet['total_recharge'] + $amount;

                        mysqli_query($conn,
                            "INSERT INTO upi_recharge (user_id, amount, txn_id, recharge_time, status)
                             VALUES ('$user_id', '$amount', '$txn_id', '$current_time_in_ist', '1')"
                        );

                        $stmt = $conn->prepare(
                            "UPDATE user_wallet SET balance=?, total_recharge=? WHERE user_id=?"
                        );
                        $stmt->bind_param("ddi", $add_balance, $add_total_rc, $user_id);
                        $stmt->execute();

                        mysqli_query($conn,
                            "INSERT INTO user_transaction (user_id, amount, date, type, txn_id, status)
                             VALUES ('$user_id', '$amount', '$current_time_in_ist', 'Rave Recharge', '$txn_id', '1')"
                        );

                        redirect('dashboard');
                        exit();
                    }
                }
            }
        }

        redirect('login');
        exit();
    }

} else {
    redirect('login');
    exit();
}
?>

</body>
</html>
