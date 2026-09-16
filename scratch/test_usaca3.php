<?php
$_GET['token'] = 'valid_dummy';
// Mock auth
session_start();
$_SESSION['token'] = 'valid_dummy';

// Override db query for token
include 'c:/xampp/htdocs/smsbyog/include/config.php';
$conn->query("CREATE TEMPORARY TABLE IF NOT EXISTS login_token (token VARCHAR(255), user_id INT)");
$conn->query("INSERT INTO login_token (token, user_id) VALUES ('valid_dummy', 1)");
$conn->query("CREATE TEMPORARY TABLE IF NOT EXISTS user_data (id INT, status INT)");
$conn->query("INSERT INTO user_data (id, status) VALUES (1, 1)");

// fetch Dino SMS directly
$api_sql = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='3'");
$api_data = mysqli_fetch_assoc($api_sql);
$api_url = rtrim($api_data['api_url'], '/');
$api_key = $api_data['api_key'];
$services_url = "{$api_url}/sms-otp/services";
echo "Fetching: $services_url with key: $api_key\n";
$res = getfunction($services_url, $api_key);
echo "Response type: " . gettype($res) . "\n";
echo "Response JSON: " . json_encode($res) . "\n";
