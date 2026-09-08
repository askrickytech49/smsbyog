<?php
session_start();
require '../include/config.php';
require '../include/xixapay.php';

if (!isset($_SESSION['token'])) {
    if (isset($_COOKIE['remember_me'])) {
        $_SESSION['token'] = $_COOKIE['remember_me'];
    } else {
        header('Location: login.php'); exit;
    }
}

$limit = 10; // 🔥 number of users per run

if (isset($_POST['generate'])) {

    $query = "
        SELECT u.id, u.name, u.email
        FROM user_data u
        LEFT JOIN user_dynamic_va v ON u.id = v.user_id
        WHERE v.user_id IS NULL
        AND u.status = '1'
        LIMIT $limit
    ";

    $result = $conn->query($query);

    if ($result->num_rows === 0) {
        echo "<h3>All users now have virtual accounts 🎉</h3>";
        exit;
    }

    $processed = 0;

    while ($user = $result->fetch_assoc()) {

        $response = xixapayRequest("/api/v1/createVirtualAccount", "POST", [
            "email" => $user['email'],
            "name" => $user['name'],
            "phoneNumber" => "08000000000",
            "bankCode" => ["29007"],
            "businessId" => XIXA_BUSINESS,
            "accountType" => "static"
        ]);

        if (
            isset($response['status']) &&
            $response['status'] === 'success' &&
            isset($response['bankAccounts'][0])
        ) {

            $bank = $response['bankAccounts'][0];

            $stmt = $conn->prepare("
                INSERT INTO user_dynamic_va 
                (user_id, account_number, account_name, bank_name, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");

            $stmt->bind_param(
                "isss",
                $user['id'],
                $bank['accountNumber'],
                $bank['accountName'],
                $bank['bankName']
            );

            $stmt->execute();
            $processed++;

            echo "✔ Created VA for User ID: {$user['id']}<br>";
        } else {
            echo "❌ Failed for User ID: {$user['id']}<br>";
        }

        usleep(300000); // 0.3 second delay to avoid rate limit
    }

    echo "<br><strong>Processed $processed users.</strong>";

    echo '
        <form method="POST">
            <button type="submit" name="generate">
                Continue Generating Next Batch
            </button>
        </form>
    ';

    exit;
}
?>

<form method="POST">
    <button type="submit" name="generate">
        Start Generating Virtual Accounts (Batch Mode)
    </button>
</form>