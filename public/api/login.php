<?php
/**
 * Karl Peace Legacy Foundation — Login
 * POST /api/login.php
 * Body: { email, password }
 * Returns: { success, token, user }
 */
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed.'], 405);
}

// Rate limit: 10 attempts per minute per IP
checkRateLimit('login_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 10, 60);

$body     = getRequestBody();
$email    = strtolower(sanitizeString($body['email'] ?? '', 191));
$password = trim($body['password'] ?? '');

if (empty($email) || empty($password)) {
    jsonResponse(['success' => false, 'error' => 'Email and password are required.'], 400);
}

$db = getDB();

// Look up user
$stmt = $db->prepare(
    'SELECT id, uid, email, display_name, password_hash, role, is_super_admin, is_active
     FROM admin_users WHERE email = ? LIMIT 1'
);
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !$user['is_active']) {
    auditLog('login', 'admin_users', null, null, ['email' => $email, 'result' => 'user_not_found']);
    jsonResponse(['success' => false, 'error' => 'Invalid credentials.'], 401);
}

if (!password_verify($password, $user['password_hash'])) {
    auditLog('login', 'admin_users', (string)$user['id'], null, ['result' => 'wrong_password']);
    jsonResponse(['success' => false, 'error' => 'Invalid credentials.'], 401);
}

// Create session token
$token     = generateToken();
$tokenHash = hashToken($token);
$expiresAt = date('Y-m-d H:i:s', time() + SESSION_LIFETIME);

$db->prepare(
    'INSERT INTO admin_sessions (user_id, token_hash, ip_address, user_agent, expires_at)
     VALUES (?, ?, ?, ?, ?)'
)->execute([
    $user['id'],
    $tokenHash,
    $_SERVER['REMOTE_ADDR'] ?? null,
    $_SERVER['HTTP_USER_AGENT'] ?? null,
    $expiresAt,
]);

// Update last login
$db->prepare('UPDATE admin_users SET last_login_at = NOW() WHERE id = ?')
   ->execute([$user['id']]);

auditLog('login', 'admin_users', (string)$user['id'], null, ['result' => 'success'], $user);

jsonResponse([
    'success' => true,
    'message' => 'Authentication successful.',
    'token'   => $token,
    'user'    => [
        'uid'          => $user['uid'],
        'email'        => $user['email'],
        'displayName'  => $user['display_name'],
        'role'         => $user['role'],
        'isSuperAdmin' => (bool) $user['is_super_admin'],
    ],
]);
