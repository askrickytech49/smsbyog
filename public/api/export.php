<?php
/**
 * Karl Peace Legacy Foundation — Data Export API
 * -----------------------------------------------
 * GET /api/export.php?type=json          — full JSON backup
 * GET /api/export.php?type=csv&table=X   — CSV export of one table
 *   tables: subscribers, inquiries, applications
 */
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed.'], 405);
}

$actor = requireAdmin();
$db    = getDB();

$type  = $_GET['type']  ?? 'json';
$table = $_GET['table'] ?? 'all';

auditLog('exported', $table, null, null, ['type' => $type], $actor);

// ─────────────────────────────────────────────────────────────
// JSON FULL BACKUP
// ─────────────────────────────────────────────────────────────
if ($type === 'json') {
    $data = [];

    $settings = $db->query('SELECT * FROM site_settings LIMIT 1')->fetch();
    $data['settings'] = $settings ?: [];

    $data['programs']     = $db->query('SELECT * FROM programs')->fetchAll();
    $data['news']         = $db->query('SELECT * FROM news_articles')->fetchAll();
    $data['leaders']      = $db->query('SELECT * FROM team_members')->fetchAll();
    $data['gallery']      = $db->query('SELECT * FROM gallery_photos')->fetchAll();
    $data['testimonials'] = $db->query('SELECT * FROM testimonials')->fetchAll();
    $data['faqs']         = $db->query('SELECT * FROM faqs')->fetchAll();
    $data['subscribers']  = $db->query('SELECT * FROM subscribers')->fetchAll();
    $data['inquiries']    = $db->query('SELECT * FROM inquiries')->fetchAll();

    // Omit sensitive document URLs from application export
    $data['applications'] = $db->query(
        'SELECT id, first_name, last_name, email, phone, institution, department,
                academic_level, cgpa, cgpa_scale, scholarship_cycle,
                status, submitted_at, created_at FROM scholarship_applications'
    )->fetchAll();

    $filename = 'karl_peace_backup_' . date('Y-m-d_His') . '.json';

    header('Content-Type: application/json; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store');

    echo json_encode([
        'exportedAt' => date('c'),
        'exportedBy' => $actor['email'],
        'version'    => '2.0',
        'data'       => $data,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit();
}

// ─────────────────────────────────────────────────────────────
// CSV EXPORT
// ─────────────────────────────────────────────────────────────
if ($type === 'csv') {
    $allowed = ['subscribers', 'inquiries', 'applications'];
    if (!in_array($table, $allowed, true)) {
        jsonResponse(['success' => false, 'error' => 'Invalid table. Choose: ' . implode(', ', $allowed)], 400);
    }

    $queries = [
        'subscribers'  => 'SELECT id, name, email, institution, course, status, created_at FROM subscribers ORDER BY created_at DESC',
        'inquiries'    => 'SELECT id, name, email, subject, role, status, created_at FROM inquiries ORDER BY created_at DESC',
        'applications' => 'SELECT id, first_name, last_name, email, phone, institution, department,
                                  academic_level, cgpa, cgpa_scale, scholarship_cycle,
                                  status, submitted_at, created_at
                           FROM scholarship_applications ORDER BY submitted_at DESC',
    ];

    $rows = $db->query($queries[$table])->fetchAll();

    $filename = 'karl_peace_' . $table . '_' . date('Y-m-d') . '.csv';

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store');

    // BOM for Excel UTF-8
    echo "\xEF\xBB\xBF";

    $out = fopen('php://output', 'w');

    if (!empty($rows)) {
        fputcsv($out, array_keys($rows[0]));
        foreach ($rows as $row) {
            fputcsv($out, $row);
        }
    }

    fclose($out);
    exit();
}

jsonResponse(['success' => false, 'error' => 'Unknown export type.'], 400);
