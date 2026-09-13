<?php
include '../../include/config.php';

header('Content-Type: application/json');

if (!isset($_POST['api_id']) || !isset($_POST['service_code']) || !isset($_POST['is_active'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$api_id = (int)$_POST['api_id'];
$service_code = mysqli_real_escape_string($conn, $_POST['service_code']);
$is_active = (int)$_POST['is_active'];

if ($is_active == 1) {
    // Insert if not exists (using INSERT IGNORE because of unique key)
    $sql = "INSERT IGNORE INTO api_active_services (api_id, service_code) VALUES ('$api_id', '$service_code')";
} else {
    // Delete
    $sql = "DELETE FROM api_active_services WHERE api_id='$api_id' AND service_code='$service_code'";
}

if (mysqli_query($conn, $sql)) {
    echo json_encode(['success' => true, 'message' => 'Service updated']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
