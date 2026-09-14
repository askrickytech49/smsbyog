<?php
/**
 * Karl Peace Legacy Foundation — Subscribers API
 * -----------------------------------------------
 * POST   /api/subscribers.php          — public sign-up
 * GET    /api/subscribers.php          — list all (auth)
 * PATCH  /api/subscribers.php?id=X     — update status (auth)
 * DELETE /api/subscribers.php?id=X     — delete (auth)
 */
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$db     = getDB();

// ── POST — public sign-up ─────────────────────────────────────
if ($method === 'POST') {
    checkRateLimit('subscribe_' . ($_SERVER['REMOTE_ADDR'] ?? 'x'), 5, 60);

    $body  = getRequestBody();
    $name  = sanitizeString($body['name']  ?? '', 255);
    $email = sanitizeString($body['email'] ?? '', 191);

    if (empty($name) || empty($email) || !validateEmail($email)) {
        jsonResponse(['success' => false, 'error' => 'Valid name and email are required.'], 400);
    }

    // Duplicate check
    $check = $db->prepare('SELECT id FROM subscribers WHERE email = ?');
    $check->execute([strtolower($email)]);
    if ($check->fetch()) {
        jsonResponse(['success' => false, 'error' => 'This email is already registered for notifications.'], 409);
    }

    $id = generateId('sub_');
    $db->prepare(
        'INSERT INTO subscribers (id, name, email, institution, course, ip_address, source_page, unsubscribe_token)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    )->execute([
        $id,
        $name,
        strtolower($email),
        sanitizeString($body['institution'] ?? '', 255),
        sanitizeString($body['course'] ?? '', 255),
        $_SERVER['REMOTE_ADDR'] ?? null,
        sanitizeString($body['sourcePage'] ?? '', 255),
        bin2hex(random_bytes(16)),
    ]);

    $item = $db->prepare('SELECT * FROM subscribers WHERE id = ?');
    $item->execute([$id]);
    $row = $item->fetch();

    jsonResponse(['success' => true, 'item' => dbRowToSubscriber($row)], 201);
}

// ── GET — list (auth required) ────────────────────────────────
if ($method === 'GET') {
    requireAdmin();

    $status = $_GET['status'] ?? null;
    $search = $_GET['search'] ?? null;
    $limit  = min((int)($_GET['limit'] ?? 200), 500);
    $offset = (int)($_GET['offset'] ?? 0);

    $where  = ['1=1'];
    $params = [];

    if ($status) {
        $where[]  = 'status = ?';
        $params[] = $status;
    }
    if ($search) {
        $where[]  = '(name LIKE ? OR email LIKE ? OR institution LIKE ?)';
        $term     = '%' . $search . '%';
        $params   = array_merge($params, [$term, $term, $term]);
    }

    $whereClause = implode(' AND ', $where);
    $countStmt   = $db->prepare("SELECT COUNT(*) FROM subscribers WHERE $whereClause");
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    $params[] = $limit;
    $params[] = $offset;
    $stmt = $db->prepare(
        "SELECT * FROM subscribers WHERE $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?"
    );
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    jsonResponse([
        'success'     => true,
        'subscribers' => array_map('dbRowToSubscriber', $rows),
        'total'       => $total,
        'limit'       => $limit,
        'offset'      => $offset,
    ]);
}

// ── PATCH — update status ─────────────────────────────────────
if ($method === 'PATCH') {
    $actor = requireAdmin();
    $id    = $_GET['id'] ?? (getRequestBody()['id'] ?? '');
    $body  = getRequestBody();

    if (!$id) jsonResponse(['success' => false, 'error' => 'id required.'], 400);

    $allowed = ['new', 'notified', 'archived'];
    $status  = $body['status'] ?? '';
    if (!in_array($status, $allowed, true)) {
        jsonResponse(['success' => false, 'error' => 'Invalid status. Must be: ' . implode(', ', $allowed)], 400);
    }

    $db->prepare('UPDATE subscribers SET status = ?, updated_at = NOW() WHERE id = ?')
       ->execute([$status, $id]);

    auditLog('status_changed', 'subscribers', $id, null, ['status' => $status], $actor);
    jsonResponse(['success' => true, 'message' => 'Status updated.']);
}

// ── DELETE ────────────────────────────────────────────────────
if ($method === 'DELETE') {
    $actor = requireAdmin();
    $id    = $_GET['id'] ?? '';
    if (!$id) jsonResponse(['success' => false, 'error' => 'id required.'], 400);

    $db->prepare('DELETE FROM subscribers WHERE id = ?')->execute([$id]);
    auditLog('deleted', 'subscribers', $id, null, null, $actor);
    jsonResponse(['success' => true, 'message' => 'Subscriber deleted.']);
}

jsonResponse(['success' => false, 'error' => 'Method not allowed.'], 405);

// ── MAPPER ────────────────────────────────────────────────────
function dbRowToSubscriber(array $r): array {
    return [
        'id'          => $r['id'],
        'name'        => $r['name'],
        'email'       => $r['email'],
        'institution' => $r['institution'],
        'course'      => $r['course'],
        'status'      => $r['status'],
        'createdAt'   => $r['created_at'],
    ];
}
