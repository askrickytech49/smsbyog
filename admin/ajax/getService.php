<?php
require_once __DIR__ . '/../../include/config.php';
header('Content-Type: application/json');

$services = [];

$sql = mysqli_query(
    $conn,
    "SELECT id, service_name, service_id 
     FROM service 
     WHERE status = '1'
     ORDER BY service_name ASC"
);

if ($sql) {
    while ($row = mysqli_fetch_assoc($sql)) {
        $services[] = [
            'id'           => $row['id'],          // numeric ID
            'service_name' => $row['service_name'],
            'service_code' => $row['service_id']   // wa, tg, vk, etc
        ];
    }
}

echo json_encode(['service' => $services]);
mysqli_close($conn);
