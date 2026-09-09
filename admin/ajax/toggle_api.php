<?php
session_start();
include __DIR__ . '/../../include/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['token'])) { echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit; }
$aq = mysqli_query($conn,"SELECT * FROM login_token WHERE token='".mysqli_real_escape_string($conn,$_SESSION['token'])."'");
if(mysqli_num_rows($aq)==0){ echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit; }
$ad = mysqli_fetch_array($aq);
$au = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM user_data WHERE id='".$ad['user_id']."' AND status='1'"));
if(!in_array($au['type'],["admin","super_admin"])){ echo json_encode(['success'=>false,'message'=>'Forbidden']); exit; }

$id = (int)($_POST['id'] ?? 0);
if (!$id) { echo json_encode(['success'=>false,'message'=>'Invalid ID']); exit; }

// Get current state and flip it
$cur = mysqli_fetch_assoc(mysqli_query($conn,"SELECT is_active FROM api_detail WHERE id='$id'"));
if (!$cur) { echo json_encode(['success'=>false,'message'=>'API not found']); exit; }

$new_state = $cur['is_active'] == 1 ? 0 : 1;
mysqli_query($conn,"UPDATE api_detail SET is_active='$new_state' WHERE id='$id'");

echo json_encode([
    'success'   => true,
    'is_active' => $new_state,
    'message'   => $new_state ? 'API enabled' : 'API disabled',
]);
