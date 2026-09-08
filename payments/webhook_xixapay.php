<?php
require_once '../include/config.php';

$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_PAYMENTPOINT_SIGNATURE'] ?? '';

// Verify signature
$expectedSignature = hash_hmac('sha256', $payload, XIXA_SECRET);

if (!hash_equals($expectedSignature, $signature)) {
    http_response_code(400);
    exit("Invalid signature");
}

$data = json_decode($payload, true);

if (!$data) {
    http_response_code(400);
    exit("Invalid JSON");
}

// Only process successful payments
if (
    $data['notification_status'] !== 'payment_successful' ||
    $data['transaction_status'] !== 'success'
) {
    http_response_code(200);
    exit("Ignored");
}

$transactionId = $data['transaction_id'];
$amountPaid = floatval($data['amount_paid']);
$accountNumber = $data['receiver']['account_number'];

/*
|--------------------------------------------------------------------------
| 1️⃣ Prevent double credit
|--------------------------------------------------------------------------
*/
$stmt = $conn->prepare("SELECT id FROM user_transaction WHERE txn_id = ? LIMIT 1");
$stmt->bind_param("s", $transactionId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->fetch_assoc()) {
    http_response_code(200);
    exit("Already processed");
}

/*
|--------------------------------------------------------------------------
| 2️⃣ Find user by virtual account
|--------------------------------------------------------------------------
*/
$stmt = $conn->prepare("SELECT user_id FROM user_dynamic_va WHERE account_number = ? LIMIT 1");
$stmt->bind_param("s", $accountNumber);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    http_response_code(404);
    exit("User not found");
}

$userId = $user['user_id'];

/*
|--------------------------------------------------------------------------
| 3️⃣ Credit wallet + insert transaction (atomic)
|--------------------------------------------------------------------------
*/

$conn->begin_transaction();

try {

    // Update wallet + total_recharge together
$stmt = $conn->prepare("
    UPDATE user_wallet 
    SET 
        balance = balance + ?, 
        total_recharge = total_recharge + ?
    WHERE user_id = ?
");
$stmt->bind_param("ddi", $amountPaid, $amountPaid, $userId);
$stmt->execute();

    // Insert transaction
    $type = "Paymentpoint Recharge";
    $status = 1;

    $stmt = $conn->prepare("
        INSERT INTO user_transaction 
        (user_id, amount, date, type, txn_id, status)
        VALUES (?, ?, NOW(), ?, ?, ?)
    ");
    $stmt->bind_param("idssi", $userId, $amountPaid, $type, $transactionId, $status);
    $stmt->execute();

    $conn->commit();

} catch (Exception $e) {

    $conn->rollback();
    http_response_code(500);
    exit("Database error");
}

http_response_code(200);
echo "Webhook Action Successful";