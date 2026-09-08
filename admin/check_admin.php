<?php
session_start();
include __DIR__ . '/../include/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['token'])) {
    echo json_encode(['is_admin' => false]);
    exit;
}

$token = mysqli_real_escape_string($conn, $_SESSION['token']);
$sql = mysqli_query($conn, "
    SELECT u.type 
    FROM login_token lt 
    JOIN user_data u ON lt.user_id = u.id 
    WHERE lt.token='$token' AND lt.status='1' AND u.status='1'
    LIMIT 1
");

if (mysqli_num_rows($sql) == 0) {
    echo json_encode(['is_admin' => false]);
    exit;
}

$row = mysqli_fetch_assoc($sql);
echo json_encode(['is_admin' => ($row['type'] === 'admin')]);
