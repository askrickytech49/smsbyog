<?php
include __DIR__ . '/../../include/config.php';

function generateRandomString($length = 10) {
    return bin2hex(random_bytes($length/2));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function random($length = 25) {
    return bin2hex(random_bytes($length/2));
}

header('Content-Type: application/json');

// Input validation
if (!isset($_POST['email']) || $_POST['email'] == "") {
    echo json_encode(['status' => "2", "msg" => "Enter Email"]);
    exit;
} 
if (!isset($_POST['password']) || $_POST['password'] == "") {
    echo json_encode(['status' => "2", "msg" => "Enter Password"]);
    exit;
} 
if (!isset($_POST['name']) || $_POST['name'] == "") {
    echo json_encode(['status' => "2", "msg" => "Enter Name"]);
    exit;
} 
if (strlen($_POST['password']) < 6) {
    echo json_encode(['status' => "2", "msg" => "Password must be at least 6 characters long"]);
    exit;
} 
if (!validateEmail($_POST['email'])) {
    echo json_encode(['status' => "2", "msg" => "Enter a Valid Email Address"]);
    exit;
}

// Removed all reCAPTCHA verification code

// Proceed with registration
$date = date("Y-m-d H:i:s");
$email = mysqli_real_escape_string($conn, $_POST['email']);
$name = mysqli_real_escape_string($conn, $_POST['name']);
$username = generateRandomString();
$password = password_hash($_POST['password'], PASSWORD_BCRYPT);

// Check if email exists
$sql = $conn->prepare("SELECT id FROM user_data WHERE email = ?");
$sql->bind_param("s", $email);
$sql->execute();
$result = $sql->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['status' => "2", "msg" => "Email Already Registered"]);
    exit;
}

// Insert user data
$sql2 = $conn->prepare("INSERT INTO user_data(name, username, email, password, type, register_date, image_url, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$type = "user";
$img_url = "";
$status = 1;
$sql2->bind_param("sssssssi", $name, $username, $email, $password, $type, $date, $img_url, $status);

if (!$sql2->execute()) {
    echo json_encode(['status' => "2", "msg" => "Registration failed. Please try again."]);
    exit;
}

$inserted_id = $conn->insert_id;

// Initialize wallet
$balance = 0;
$total_recharge = 0;
$total_otp = 0;
$sql3 = $conn->prepare("INSERT INTO user_wallet(user_id, balance, total_recharge, total_otp) VALUES (?, ?, ?, ?)");
$sql3->bind_param("siii", $inserted_id, $balance, $total_recharge, $total_otp);
$sql3->execute();

// Handle referral
if (isset($_POST['refer_id']) && !empty($_POST['refer_id'])) {
    $random = random();
    $refer_id = mysqli_real_escape_string($conn, $_POST['refer_id']);
    
    $sql33 = $conn->prepare("SELECT id FROM refer_data WHERE own_code = ?");
    $sql33->bind_param("s", $refer_id);
    $sql33->execute();
    
    if ($sql33->get_result()->num_rows == 1) {
        $stmt = $conn->prepare("INSERT INTO refer_data (user_id, balance, own_code, refer_by, transfer, total_earn) VALUES (?, ?, ?, ?, ?, ?)");
        $zero = 0;
        $stmt->bind_param("isssii", $inserted_id, $zero, $random, $refer_id, $zero, $zero);
    } else {
        $stmt = $conn->prepare("INSERT INTO refer_data (user_id, balance, own_code, refer_by, transfer, total_earn) VALUES (?, ?, ?, ?, ?, ?)");
        $zero = 0;
        $empty = '';
        $stmt->bind_param("isssii", $inserted_id, $zero, $random, $empty, $zero, $zero);
    }
    $stmt->execute();
}

echo json_encode(['status' => "1", "msg" => "Registration Successful"]);
$conn->close();
?>