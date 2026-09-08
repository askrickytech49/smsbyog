<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once __DIR__ . '/../include/config.php';
require_once __DIR__ . '/../class/class.control.php';

header('Content-Type: application/json');

if (!isset($_SESSION['token'])) {
    echo json_encode(["success"=>false]);
    exit;
}

$control = new radiumsahil();
$user_id = $control->check_token($_SESSION['token']);

if (!$user_id) {
    echo json_encode(["success"=>false]);
    exit;
}

$wallet = $control->userwallet();

echo json_encode([
    "success"=>true,
    "balance"=>$wallet['balance']
]);
exit;