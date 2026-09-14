<?php
/**
 * Karl Peace Legacy Foundation — Logout
 * POST /api/logout.php
 * Revokes the current session token.
 */
require_once __DIR__ . '/config.php';

$token = getBearerToken();
if ($token) {
    $tokenHash = hashToken($token);
    try {
        $db = getDB();
        $db->prepare('UPDATE admin_sessions SET is_revoked = 1 WHERE token_hash = ?')
           ->execute([$tokenHash]);
    } catch (Throwable) {}
}

jsonResponse(['success' => true, 'message' => 'Logged out successfully.']);
