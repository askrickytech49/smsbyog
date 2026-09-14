<?php
/**
 * Karl Peace Legacy Foundation — Audit Log API
 * ---------------------------------------------
 * GET /api/audit.php              — list audit entries (super-admin)
 * GET /api/audit.php?entity_type=X&entity_id=Y — filter by entity
 */
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed.'], 405);
}

requireSuperAdmin();
$db = getDB();

$entityType = $_GET['entity_type'] ?? null;
$entityId   = $_GET['entity_id']   ?? null;
$action     = $_GET['action']      ?? null;
$userId     = $_GET['user_id']     ?? null;
$limit      = min((int)($_GET['limit'] ?? 100), 500);
$offset     = (int)($_GET['offset'] ?? 0);

$where  = ['1=1'];
$params = [];

if ($entityType) { $where[] = 'entity_type = ?'; $params[] = $entityType; }
if ($entityId)   { $where[] = 'entity_id = ?';   $params[] = $entityId;   }
if ($action)     { $where[] = 'action = ?';       $params[] = $action;     }
if ($userId)     { $where[] = 'user_id = ?';      $params[] = $userId;     }

$whereClause = implode(' AND ', $where);

$countStmt = $db->prepare("SELECT COUNT(*) FROM audit_log WHERE $whereClause");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();

$params[] = $limit;
$params[] = $offset;
$stmt = $db->prepare(
    "SELECT id, user_id, user_email, action, entity_type, entity_id,
            old_value, new_value, ip_address, performed_at
     FROM audit_log WHERE $whereClause
     ORDER BY performed_at DESC LIMIT ? OFFSET ?"
);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$entries = array_map(function ($r) {
    return [
        'id'          => $r['id'],
        'userId'      => $r['user_id'],
        'userEmail'   => $r['user_email'],
        'action'      => $r['action'],
        'entityType'  => $r['entity_type'],
        'entityId'    => $r['entity_id'],
        'oldValue'    => $r['old_value'] ? json_decode($r['old_value'], true) : null,
        'newValue'    => $r['new_value'] ? json_decode($r['new_value'], true) : null,
        'ipAddress'   => $r['ip_address'],
        'performedAt' => $r['performed_at'],
    ];
}, $rows);

jsonResponse([
    'success' => true,
    'entries' => $entries,
    'total'   => $total,
    'limit'   => $limit,
    'offset'  => $offset,
]);
