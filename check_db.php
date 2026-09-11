<?php
require_once __DIR__ . '/include/config.php';

$email = 'junioranthony0011@gmail.com';
$sql = $conn->prepare("SELECT * FROM user_data WHERE email = ?");
$sql->bind_param("s", $email);
$sql->execute();
$result = $sql->get_result();

if ($result->num_rows > 0) {
    echo "User exists in the database!\n";
    $data = $result->fetch_assoc();
    var_dump($data);
} else {
    echo "User does NOT exist in the database!\n";
}
?>
