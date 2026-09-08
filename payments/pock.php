<?php
require_once '../include/config.php';

// 1. Capture payload data
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_POCKETFI_SIGNATURE'] ?? '';

$secretKey = defined('POCKETFI_SECRET') ? POCKETFI_SECRET : '';
$expectedSignature = hash_hmac('sha512', $payload, $secretKey);

if (!hash_equals($expectedSignature, $signature)) {
    http_response_code(400);
    exit("Invalid signature");
}

$data = json_decode($payload, true);
if (!$data) {
    http_response_code(400);
    exit("Invalid JSON");
}

$transactionId = $data['transaction']['reference'] ?? null;
$amountPaid    = isset($data['order']['amount']) ? floatval($data['order']['amount']) : 0.00;
$accountNumber = $data['account_number'] ?? null; 

if (empty($transactionId) || empty($accountNumber)) {
    http_response_code(400);
    exit("Missing tracking identifiers");
}


$stmt = $conn->prepare("SELECT id FROM user_transaction WHERE txn_id = ? LIMIT 1");
$stmt->bind_param("s", $transactionId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->fetch_assoc()) {
    http_response_code(200);
    exit("Already processed");
}


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


$conn->begin_transaction();

try {
    // Update wallet balance + tracking metric
    $stmt = $conn->prepare("
        UPDATE user_wallet 
        SET 
            balance = balance + ?, 
            total_recharge = total_recharge + ?
        WHERE user_id = ?
    ");
    $stmt->bind_param("ddi", $amountPaid, $amountPaid, $userId);
    $stmt->execute();

    $type = "PocketFi Recharge";
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
    exit("Database error processing payment");
}

http_response_code(200);
echo "Webhook Action Successful";