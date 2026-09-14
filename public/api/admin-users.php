<?php
/**
 * Karl Peace Legacy Foundation — Admin Users API
 * ------------------------------------------------
 * GET    /api/admin-users.php          — list users (super-admin)
 * POST   /api/admin-users.php          — create user (super-admin)
 * PATCH  /api/admin-users.php?id=X     — update user (super-admin)
 * DELETE /api/admin-users.php?id=X     — deactivate user (super-admin)
 * POST   /api/admin-users.php?action=change-password — change own password (auth)
 */
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? null;
$db     = getDB();

// ── Change own password (any authenticated user) ──────────────
if ($method === 'POST' && $action === 'change-password') {
    $actor = requireAuth();
    $body  = getRequestBody();

    $current = trim($body['currentPassword'] ?? '');
    $newPw   = trim($body['newPassword']     ?? '');

    if (strlen($newPw) < 8) {
        jsonResponse(['success' => false, 'error' => 'New password must be at least 8 characters.'], 400);
    }

    // Verify current password
    $stmt = $db->prepare('SELECT password_hash FROM admin_users WHERE id = ?');
    $stmt->execute([$actor['id']]);
    $row = $stmt->fetch();

    if (!$row || !password_verify($current, $row['password_hash'])) {
        jsonResponse(['success' => false, 'error' => 'Current password is incorrect.'], 401);
    }

    $db->prepare('UPDATE admin_users SET password_hash = ?, updated_at = NOW() WHERE id = ?')
       ->execute([password_hash($newPw, PASSWORD_BCRYPT), $actor['id']]);

    auditLog('updated', 'admin_users', (string)$actor['id'], null, ['action' => 'password_change'], $actor);
    jsonResponse(['success' => true, 'message' => 'Password changed successfully.']);
}

// ── All other actions require super-admin ─────────────────────
$actor = requireSuperAdmin();

// ── GET — list users ──────────────────────────────────────────
if ($method === 'GET') {
    $rows = $db->query(
        'SELECT id, uid, email, display_name, role, is_super_admin, is_active,
                last_login_at, created_at FROM admin_users ORDER BY created_at DESC'
    )->fetchAll();

    jsonResponse(['success' => true, 'users' => array_map('dbRowToAdminUser', $rows)]);
}

// ── POST — create user ────────────────────────────────────────
if ($method === 'POST') {
    $body     = getRequestBody();
    $email    = strtolower(sanitizeString($body['email'] ?? '', 191));
    $password = trim($body['password'] ?? '');
    $role     = in_array($body['role'] ?? '', ['admin','editor','viewer'])
                ? $body['role'] : 'editor';
    $name     = sanitizeString($body['displayName'] ?? 'Foundation Admin', 191);

    if (!$email || !validateEmail($email)) {
        jsonResponse(['success' => false, 'error' => 'Valid email required.'], 400);
    }
    if (strlen($password) < 8) {
        jsonResponse(['success' => false, 'error' => 'Password must be at least 8 characters.'], 400);
    }

    // Check duplicate
    $dup = $db->prepare('SELECT id FROM admin_users WHERE email = ?');
    $dup->execute([$email]);
    if ($dup->fetch()) {
        jsonResponse(['success' => false, 'error' => 'Email already in use.'], 409);
    }

    $uid  = generateId('admin_');
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $isSuper = ($body['isSuperAdmin'] ?? false) ? 1 : 0;

    $db->prepare(
        'INSERT INTO admin_users (uid, email, display_name, password_hash, role, is_super_admin)
         VALUES (?, ?, ?, ?, ?, ?)'
    )->execute([$uid, $email, $name, $hash, $role, $isSuper]);

    $newId = $db->lastInsertId();
    auditLog('created', 'admin_users', (string)$newId, null, ['email' => $email, 'role' => $role], $actor);

    $stmt = $db->prepare('SELECT * FROM admin_users WHERE id = ?');
    $stmt->execute([$newId]);
    jsonResponse(['success' => true, 'user' => dbRowToAdminUser($stmt->fetch())], 201);
}

// ── PATCH — update user ───────────────────────────────────────
if ($method === 'PATCH') {
    $id   = $_GET['id'] ?? '';
    $body = getRequestBody();
    if (!$id) jsonResponse(['success' => false, 'error' => 'id required.'], 400);

    $sets   = [];
    $params = [];

    if (isset($body['displayName'])) {
        $sets[]   = 'display_name = ?';
        $params[] = sanitizeString($body['displayName'], 191);
    }
    if (isset($body['role']) && in_array($body['role'], ['admin','editor','viewer'], true)) {
        $sets[]   = 'role = ?';
        $params[] = $body['role'];
    }
    if (isset($body['isActive'])) {
        $sets[]   = 'is_active = ?';
        $params[] = $body['isActive'] ? 1 : 0;
    }
    if (isset($body['isSuperAdmin'])) {
        $sets[]   = 'is_super_admin = ?';
        $params[] = $body['isSuperAdmin'] ? 1 : 0;
    }
    if (isset($body['password']) && strlen(trim($body['password'])) >= 8) {
        $sets[]   = 'password_hash = ?';
        $params[] = password_hash(trim($body['password']), PASSWORD_BCRYPT);
    }

    if (empty($sets)) jsonResponse(['success' => false, 'error' => 'Nothing to update.'], 400);

    $sets[]   = 'updated_at = NOW()';
    $params[] = $id;
    $db->prepare('UPDATE admin_users SET ' . implode(', ', $sets) . ' WHERE id = ?')
       ->execute($params);

    auditLog('updated', 'admin_users', $id, null, $body, $actor);
    jsonResponse(['success' => true, 'message' => 'User updated.']);
}

// ── DELETE — deactivate (soft delete) ────────────────────────
if ($method === 'DELETE') {
    $id = $_GET['id'] ?? '';
    if (!$id) jsonResponse(['success' => false, 'error' => 'id required.'], 400);

    // Prevent self-deactivation
    if ((string)$actor['id'] === (string)$id) {
        jsonResponse(['success' => false, 'error' => 'Cannot deactivate your own account.'], 400);
    }

    $db->prepare('UPDATE admin_users SET is_active = 0, updated_at = NOW() WHERE id = ?')
       ->execute([$id]);

    // Revoke all sessions for this user
    $db->prepare('UPDATE admin_sessions SET is_revoked = 1 WHERE user_id = ?')
       ->execute([$id]);

    auditLog('deleted', 'admin_users', $id, null, null, $actor);
    jsonResponse(['success' => true, 'message' => 'User deactivated.']);
}

jsonResponse(['success' => false, 'error' => 'Method not allowed.'], 405);

function dbRowToAdminUser(array $r): array {
    return [
        'id'          => $r['id'],
        'uid'         => $r['uid'],
        'email'       => $r['email'],
        'displayName' => $r['display_name'],
        'role'        => $r['role'],
        'isSuperAdmin'=> (bool) $r['is_super_admin'],
        'isActive'    => (bool) $r['is_active'],
        'lastLoginAt' => $r['last_login_at'],
        'createdAt'   => $r['created_at'],
    ];
}
