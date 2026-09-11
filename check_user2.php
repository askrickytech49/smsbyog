<?php
require 'include/config.php';
$email = 'rickyessential49@gmail.com';
$sql = $conn->prepare("SELECT * FROM user_data WHERE email = ?");
$sql->bind_param("s", $email);
$sql->execute();
$result = $sql->get_result();

if ($result->num_rows > 0) {
    echo "User exists in database!\n";
    $data = $result->fetch_assoc();
    var_dump($data);
} else {
    echo "User DOES NOT EXIST in the active database!\n";
}
?>
