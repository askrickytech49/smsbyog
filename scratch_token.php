<?php
include 'include/config.php';
$res = mysqli_query($conn, 'SELECT token FROM user LIMIT 1');
$row = mysqli_fetch_assoc($res);
$token = $row['token'];
echo "Token: $token\n";
$_GET['token'] = $token;
include 'api/service/getServices2All.php';
