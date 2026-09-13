<?php
include '../../include/config.php';

header('Content-Type: application/json');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['orders']) || !is_array($data['orders'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

mysqli_begin_transaction($conn);

try {
    foreach ($data['orders'] as $item) {
        $id = (int)$item['id'];
        $order = (int)$item['order'];
        
        $sql = "UPDATE api_detail SET sort_order = $order WHERE id = $id";
        mysqli_query($conn, $sql);
    }
    
    mysqli_commit($conn);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
