<?php
include 'include/config.php';
// $res = mysqli_query($conn, 'SELECT token FROM user LIMIT 1');
// $row = mysqli_fetch_assoc($res);
// $_GET['token'] = $row['token'];
$_GET['service'] = 'whatsapp';
function check_token() { return 1; }
include 'api/service/getCountriesForService2.php';
echo json_encode(array_slice($final, 0, 3));
