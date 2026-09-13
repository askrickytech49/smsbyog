<?php
include '../../include/config.php';

header('Content-Type: application/json');

if (!isset($_POST['api_id']) || !isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$api_id = (int)$_POST['api_id'];
$action = $_POST['action']; // 'select_all' or 'deselect_all'

if ($action === 'deselect_all') {
    // Remove all active services for this API
    mysqli_query($conn, "DELETE FROM api_active_services WHERE api_id='$api_id'");
    echo json_encode(['success' => true, 'message' => 'All services deactivated']);
    exit;
}

if ($action === 'select_all') {
    // Get all service codes from POST
    $codes = json_decode($_POST['codes'] ?? '[]', true);
    if (!is_array($codes) || empty($codes)) {
        echo json_encode(['success' => false, 'message' => 'No service codes provided']);
        exit;
    }
    
    // Build batch insert
    $values = [];
    foreach ($codes as $code) {
        $esc_code = mysqli_real_escape_string($conn, $code);
        $values[] = "('$api_id', '$esc_code')";
    }
    
    $batch = implode(',', $values);
    mysqli_query($conn, "INSERT IGNORE INTO api_active_services (api_id, service_code) VALUES $batch");
    
    echo json_encode(['success' => true, 'message' => 'All services activated']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
