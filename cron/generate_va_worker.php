<?php
require '../include/config.php';
require '../include/xixapay.php';

$limit = 20;

/*
|--------------------------------------------------------------------------
| STEP 1 — Lock 20 fresh users atomically
|--------------------------------------------------------------------------
*/

$conn->query("
    UPDATE user_data u
    LEFT JOIN user_dynamic_va v ON u.id = v.user_id
    SET u.va_processing = 1
    WHERE v.user_id IS NULL
    AND u.status = '1'
    AND u.va_processing = 0
    LIMIT $limit
");

/*
|--------------------------------------------------------------------------
| STEP 2 — Select ONLY those we just locked
|--------------------------------------------------------------------------
*/

$result = $conn->query("
    SELECT u.id, u.name, u.email
    FROM user_data u
    LEFT JOIN user_dynamic_va v ON u.id = v.user_id
    WHERE u.va_processing = 1
    AND v.user_id IS NULL
    LIMIT $limit
");

if ($result->num_rows === 0) {
    exit("No users left.");
}

/*
|--------------------------------------------------------------------------
| STEP 3 — Process them
|--------------------------------------------------------------------------
*/

while ($user = $result->fetch_assoc()) {

    // Extra safety check
    $check = $conn->prepare("SELECT id FROM user_dynamic_va WHERE user_id=? LIMIT 1");
    $check->bind_param("i", $user['id']);
    $check->execute();
    $res = $check->get_result();

    if ($res->fetch_assoc()) {
        $conn->query("UPDATE user_data SET va_processing = 2 WHERE id='{$user['id']}'");
        continue;
    }

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

        // Mark completed
        $conn->query("UPDATE user_data SET va_processing = 2 WHERE id='{$user['id']}'");

    } else {

        // Unlock so it retries next minute
        $conn->query("UPDATE user_data SET va_processing = 0 WHERE id='{$user['id']}'");
    }

    usleep(300000); // 0.3 sec delay
}

echo "Batch completed.";