<?php
require 'include/config.php';

// Check rickyessential49 specifically
$email = 'rickyessential49@gmail.com';

// Check by email
$sql = $conn->prepare("SELECT id, name, email, type FROM user_data WHERE email = ?");
$sql->bind_param("s", $email);
$sql->execute();
$result = $sql->get_result();
echo "Search by email '$email': " . $result->num_rows . " results\n";
if ($result->num_rows > 0) {
    var_dump($result->fetch_assoc());
}

// Check by ID 483816
$sql2 = $conn->prepare("SELECT id, name, email, type FROM user_data WHERE id = 483816");
$sql2->execute();
$result2 = $sql2->get_result();
echo "\nSearch by ID 483816: " . $result2->num_rows . " results\n";
if ($result2->num_rows > 0) {
    var_dump($result2->fetch_assoc());
}

// Count total users
$countResult = $conn->query("SELECT COUNT(*) as cnt FROM user_data");
$count = $countResult->fetch_assoc();
echo "\nTotal users in database: " . $count['cnt'] . "\n";

// Show all admins
echo "\nAll admin accounts:\n";
$admins = $conn->query("SELECT id, name, email, type FROM user_data WHERE type IN ('admin', 'super_admin')");
while ($row = $admins->fetch_assoc()) {
    echo "  ID: {$row['id']}, Name: {$row['name']}, Email: {$row['email']}, Type: {$row['type']}\n";
}

// Show last 10 users
echo "\nLast 10 registered users:\n";
$recent = $conn->query("SELECT id, name, email, type, register_date FROM user_data ORDER BY id DESC LIMIT 10");
while ($row = $recent->fetch_assoc()) {
    echo "  ID: {$row['id']}, Name: {$row['name']}, Email: {$row['email']}, Type: {$row['type']}, Registered: {$row['register_date']}\n";
}
?>
