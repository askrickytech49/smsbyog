<?php
/**
 * toggle_operator_mode.php — Admin AJAX endpoint
 * Saves excluded operators for a given API (used for 5sim operator switching).
 *
 * POST params:
 *   api_id           - The API ID (e.g. 2 for 5sim)
 *   excluded_operators - "virtual28_only" for Premium Mode, or empty for all operators
 */
include __DIR__ . '/../../include/config.php';

session_start();

if (empty($_SESSION['token'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$session_token = mysqli_real_escape_string($conn, $_SESSION['token']);
$admin_check = mysqli_query($conn, "
    SELECT u.type
    FROM login_token lt
    JOIN user_data u ON u.id = lt.user_id
    WHERE lt.token='$session_token' AND lt.status='1' AND u.status='1'
    LIMIT 1
");
$admin = $admin_check ? mysqli_fetch_assoc($admin_check) : null;
if (!$admin || !in_array($admin['type'], ['admin', 'super_admin'], true)) {
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

session_write_close();

header('Content-Type: application/json');

$api_id = (int)($_POST['api_id'] ?? 0);
$excluded = isset($_POST['premium_mode'])
    ? trim($_POST['premium_mode'])
    : trim($_POST['excluded_operators'] ?? '');
$is_redirect = isset($_POST['redirect']) && $_POST['redirect'] === '1';

if ($api_id <= 0) {
    if ($is_redirect) { header('Location: ../edit_api?id=2&mode_error=invalid'); exit; }
    echo json_encode(['success' => false, 'message' => 'Invalid API ID']);
    exit;
}

// Sanitize: only allow comma-separated alphanumeric operator names
$clean = '';
if ($excluded !== '') {
    if ($excluded === 'virtual28_only') {
        $clean = $excluded;
    } else {
        $parts = array_map('trim', explode(',', $excluded));
        $parts = array_filter($parts, function($p) { return preg_match('/^[a-zA-Z0-9_]+$/', $p); });
        $clean = implode(',', $parts);
    }
}

$esc_clean = mysqli_real_escape_string($conn, $clean);
$result = mysqli_query($conn, "UPDATE api_detail SET excluded_operators='$esc_clean' WHERE id='$api_id'");

if ($result) {
    if ($is_redirect) { header('Location: ../edit_api?id=' . $api_id . '&mode_saved=1'); exit; }
    echo json_encode(['success' => true, 'excluded_operators' => $clean]);
} else {
    if ($is_redirect) { header('Location: ../edit_api?id=' . $api_id . '&mode_error=database'); exit; }
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
}
