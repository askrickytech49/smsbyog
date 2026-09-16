<?php
$_GET['token'] = 'valid_dummy';
// Mock auth
session_start();
$_SESSION['token'] = 'valid_dummy';

// Override db query for token
include 'c:/xampp/htdocs/smsbyog/include/config.php';
// just echo what it returns without auth checking by mocking user_data
$conn->query("CREATE TEMPORARY TABLE IF NOT EXISTS login_token (token VARCHAR(255), user_id INT)");
$conn->query("INSERT INTO login_token (token, user_id) VALUES ('valid_dummy', 1)");
$conn->query("CREATE TEMPORARY TABLE IF NOT EXISTS user_data (id INT, status INT)");
$conn->query("INSERT INTO user_data (id, status) VALUES (1, 1)");

include 'c:/xampp/htdocs/smsbyog/api/service/getServicesUsaCaAll.php';
