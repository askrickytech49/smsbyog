<?php
include '../../include/config.php';

header('Content-Type: application/json');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['api_id']) || !isset($data['orders']) || !is_array($data['orders'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$api_id = (int)$data['api_id'];

mysqli_begin_transaction($conn);

try {
    foreach ($data['orders'] as $item) {
        $code = mysqli_real_escape_string($conn, $item['code']);
        $order = (int)$item['order'];
        
        $sql = "INSERT INTO api_service_order (api_id, service_code, sort_order) 
                VALUES ($api_id, '$code', $order) 
                ON DUPLICATE KEY UPDATE sort_order = $order";
                
        mysqli_query($conn, $sql);
    }
    
    mysqli_commit($conn);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
