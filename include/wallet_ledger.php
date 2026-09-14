<?php

/**
 * Credit a refund exactly once. Call inside the caller's DB transaction.
 */
function refund_once(mysqli $conn, int $user_id, string $order_id, $amount, string $source): bool
{
    $amount = number_format((float)$amount, 2, '.', '');
    $entry_type = 'number_refund';

    $stmt = $conn->prepare(
        'INSERT IGNORE INTO wallet_ledger
         (user_id, entry_type, reference_id, amount, source)
         VALUES (?, ?, ?, ?, ?)'
    );
    if (!$stmt) {
        throw new RuntimeException('Refund ledger prepare failed');
    }
    $stmt->bind_param('issss', $user_id, $entry_type, $order_id, $amount, $source);
    if (!$stmt->execute()) {
        throw new RuntimeException('Refund ledger insert failed');
    }

    // A duplicate reference means this refund was already credited.
    if ($stmt->affected_rows === 0) {
        return false;
    }

    $wallet = $conn->prepare(
        'UPDATE user_wallet
         SET balance = balance + ?, total_otp = GREATEST(total_otp - 1, 0)
         WHERE user_id = ?'
    );
    if (!$wallet) {
        throw new RuntimeException('Refund wallet prepare failed');
    }
    $wallet->bind_param('di', $amount, $user_id);
    if (!$wallet->execute() || $wallet->affected_rows !== 1) {
        throw new RuntimeException('Refund wallet update failed');
    }

    return true;
}