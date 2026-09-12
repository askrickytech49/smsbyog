<?php
include("auth.php");
if (!isset($_SESSION['token'])) {
    header('Location: login.php');
    exit;
}
$aq = mysqli_query($conn, "SELECT * FROM login_token WHERE token='" . $_SESSION['token'] . "'");
if (mysqli_num_rows($aq) == 0) {
    header('Location: login.php');
    exit;
}
$ad = mysqli_fetch_array($aq);
$au = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM user_data WHERE id='" . $ad['user_id'] . "' AND status='1'"));
if (!in_array($au['type'], ["admin", "super_admin"])) {
    header('Location: login.php');
    exit;
}

$response = @file_get_contents("https://5sim.net/v1/guest/countries");
if (!$response) {
    echo "<script>alert('Failed to connect to 5SIM API.'); window.location.href='show_server.php';</script>";
    exit;
}

$countries = json_decode($response, true);
if (!is_array($countries)) {
    echo "<script>alert('Invalid response from 5SIM API.'); window.location.href='show_server.php';</script>";
    exit;
}

$imported = 0;
foreach ($countries as $code => $data) {
    // Basic mapping check
    if (!isset($data['text_en'])) continue;

    $name = mysqli_real_escape_string($conn, $data['text_en']);
    $code = mysqli_real_escape_string($conn, $code);
    
    // Check if it already exists for 5sim (api_id = 2)
    $chk = mysqli_query($conn, "SELECT id FROM otp_server WHERE server_code='$code' AND api_id='2'");
    if (mysqli_num_rows($chk) == 0) {
        $server_name = $name . " (Server 1)";
        mysqli_query($conn, "INSERT INTO otp_server (server_name, server_code, api_id, status) VALUES ('$server_name', '$code', '2', '1')");
        $imported++;
    }
}

echo "<script>alert('Successfully imported $imported new countries from 5SIM.'); window.location.href='show_server';</script>";
exit;
