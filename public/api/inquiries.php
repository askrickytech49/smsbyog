<?php
/**
 * Karl Peace Legacy Foundation — Inquiries API
 * ---------------------------------------------
 * POST   /api/inquiries.php          — public submission
 * GET    /api/inquiries.php          — list all (auth)
 * GET    /api/inquiries.php?id=X     — single inquiry (auth)
 * PATCH  /api/inquiries.php?id=X     — update status / add reply notes (auth)
 * DELETE /api/inquiries.php?id=X     — delete (auth)
 */
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$db     = getDB();

// ── POST — public submission ──────────────────────────────────
if ($method === 'POST') {
    checkRateLimit('inquiry_' . ($_SERVER['REMOTE_ADDR'] ?? 'x'), 5, 120);

    $body    = getRequestBody();
    $name    = sanitizeString($body['name']    ?? '', 255);
    $email   = sanitizeString($body['email']   ?? '', 191);
    $message = trim($body['message'] ?? '');

    if (empty($name) || empty($email) || !validateEmail($email) || empty($message)) {
        jsonResponse(['success' => false, 'error' => 'Name, valid email, and message are required.'], 400);
    }

    $id = generateId('inq_');
    $db->prepare(
        'INSERT INTO inquiries (id, name, email, subject, message, role, ip_address)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    )->execute([
        $id,
        $name,
        strtolower($email),
        sanitizeString($body['subject'] ?? 'General Inquiry', 255),
        mb_substr(trim(strip_tags($message)), 0, 5000),
        sanitizeString($body['role'] ?? '', 128),
        $_SERVER['REMOTE_ADDR'] ?? null,
    ]);

    $item = $db->prepare('SELECT * FROM inquiries WHERE id = ?');
    $item->execute([$id]);

    jsonResponse(['success' => true, 'item' => dbRowToInquiry($item->fetch())], 201);
}

// ── GET — list or single ──────────────────────────────────────
if ($method === 'GET') {
    requireAdmin();

    $id = $_GET['id'] ?? null;

    // Single item
    if ($id) {
        $stmt = $db->prepare('SELECT * FROM inquiries WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) jsonResponse(['success' => false, 'error' => 'Not found.'], 404);
        jsonResponse(['success' => true, 'inquiry' => dbRowToInquiry($row)]);
    }

    // List with filters
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
        $where[]  = '(name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)';
        $term     = '%' . $search . '%';
        $params   = array_merge($params, [$term, $term, $term, $term]);
    }

    $whereClause = implode(' AND ', $where);
    $countStmt   = $db->prepare("SELECT COUNT(*) FROM inquiries WHERE $whereClause");
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    $params[] = $limit;
    $params[] = $offset;
    $stmt     = $db->prepare(
        "SELECT * FROM inquiries WHERE $whereClause ORDER BY
         FIELD(status,'unread','replied','archived'), created_at DESC LIMIT ? OFFSET ?"
    );
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    jsonResponse([
        'success'   => true,
        'inquiries' => array_map('dbRowToInquiry', $rows),
        'total'     => $total,
        'limit'     => $limit,
        'offset'    => $offset,
    ]);
}

// ── PATCH — update status / notes ────────────────────────────
if ($method === 'PATCH') {
    $actor = requireAdmin();
    $id    = $_GET['id'] ?? '';
    $body  = getRequestBody();

    if (!$id) jsonResponse(['success' => false, 'error' => 'id required.'], 400);

    $sets   = [];
    $params = [];

    if (isset($body['status'])) {
        $allowed = ['unread', 'replied', 'archived'];
        if (!in_array($body['status'], $allowed, true)) {
            jsonResponse(['success' => false, 'error' => 'Invalid status.'], 400);
        }
        $sets[]   = 'status = ?';
        $params[] = $body['status'];

        if ($body['status'] === 'replied') {
            $sets[]   = 'replied_at = NOW()';
            $sets[]   = 'replied_by = ?';
            $params[] = $actor['email'];
        }
    }

    if (isset($body['replyNotes'])) {
        $sets[]   = 'reply_notes = ?';
        $params[] = mb_substr(trim($body['replyNotes']), 0, 5000);
    }

    if (empty($sets)) {
        jsonResponse(['success' => false, 'error' => 'No fields to update.'], 400);
    }

    $sets[]   = 'updated_at = NOW()';
    $params[] = $id;

    $db->prepare('UPDATE inquiries SET ' . implode(', ', $sets) . ' WHERE id = ?')
       ->execute($params);

    auditLog('status_changed', 'inquiries', $id, null, $body, $actor);
    jsonResponse(['success' => true, 'message' => 'Inquiry updated.']);
}

// ── DELETE ────────────────────────────────────────────────────
if ($method === 'DELETE') {
    $actor = requireAdmin();
    $id    = $_GET['id'] ?? '';
    if (!$id) jsonResponse(['success' => false, 'error' => 'id required.'], 400);

    $db->prepare('DELETE FROM inquiries WHERE id = ?')->execute([$id]);
    auditLog('deleted', 'inquiries', $id, null, null, $actor);
    jsonResponse(['success' => true, 'message' => 'Inquiry deleted.']);
}

jsonResponse(['success' => false, 'error' => 'Method not allowed.'], 405);

// ── MAPPER ────────────────────────────────────────────────────
function dbRowToInquiry(array $r): array {
    return [
        'id'         => $r['id'],
        'name'       => $r['name'],
        'email'      => $r['email'],
        'subject'    => $r['subject'],
        'message'    => $r['message'],
        'role'       => $r['role'],
        'status'     => $r['status'],
        'repliedAt'  => $r['replied_at'],
        'repliedBy'  => $r['replied_by'],
        'replyNotes' => $r['reply_notes'],
        'createdAt'  => $r['created_at'],
    ];
}
