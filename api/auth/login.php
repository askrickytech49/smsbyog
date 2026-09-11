<?php
ob_start();
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../include/config.php';
ob_clean();

function generateRandomString($length = 30) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $random_string = '';
    for ($i = 0; $i < $length; $i++) {
        $random_string .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $random_string;
}

// Detect device type
$user_agent = $_SERVER['HTTP_USER_AGENT'];
$deviceString = "Device: Unknown";
if (preg_match('/iPhone|iPad|iPod/i', $user_agent)) {
    $deviceString = "Device: Apple iOS";
} elseif (preg_match('/Android/i', $user_agent)) {
    $deviceString = "Device: Android";
} elseif (preg_match('/Windows Phone/i', $user_agent)) {
    $deviceString = "Device: Windows Phone";
} elseif (preg_match('/Macintosh|Mac OS X/i', $user_agent)) {
    $deviceString = "Device: Macintosh (Mac)";
} elseif (preg_match('/Windows/i', $user_agent)) {
    $deviceString = "Device: Windows";
} elseif (preg_match('/Linux/i', $user_agent)) {
    $deviceString = "Device: Linux";
}

// Detect browser
$browserData = "Browser information not found.";
if (preg_match('/(MSIE|Edge|Firefox|Chrome|Safari|Opera)[\/\s](\d+\.\d+)/i', $user_agent, $matches)) {
    $browser = $matches[1];
    $version = $matches[2];
    $browserData = "Browser: $browser $version";
}

// Validate input
if (!isset($_POST['email']) || trim($_POST['email']) === "") {
    echo json_encode(["status" => "2", "msg" => "Enter Email"]);
    exit;
}

if (!isset($_POST['password']) || trim($_POST['password']) === "") {
    echo json_encode(["status" => "2", "msg" => "Enter Password"]);
    exit;
}

$email = $_POST['email'];
$password_input = $_POST['password'];

// Query user
$sql = $conn->prepare("SELECT * FROM user_data WHERE email = ?");
$sql->bind_param("s", $email);
$sql->execute();
$result = $sql->get_result();

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    if (password_verify($password_input, $data['password'])) {
        if ($data['status'] == "1") {
            $user_id = $data['id'];
            $token = generateRandomString();
            $user_ip = $_SERVER['REMOTE_ADDR'];
            $_SESSION['token'] = $token;

            // Set remember_me cookie
            setcookie('remember_me', $token, [
                'expires' => strtotime('+1 month'),
                'path' => '/',
                'domain' => $_SERVER['HTTP_HOST'],
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            // If user is admin or super_admin
            if ($data['type'] === "admin" || $data['type'] === "super_admin") {
                $_SESSION['admin'] = $token;
            }

            // Save or update login token
            $checkToken = $conn->prepare("SELECT id FROM login_token WHERE user_id = ?");
            $checkToken->bind_param("i", $user_id);
            $checkToken->execute();
            $checkTokenResult = $checkToken->get_result();

            if ($checkTokenResult->num_rows === 0) {
                $insertToken = $conn->prepare("INSERT INTO login_token(user_id, token, create_date, device, browser, ip, status) VALUES (?, ?, ?, ?, ?, ?, 1)");
                $insertToken->bind_param("isssss", $user_id, $token, date('Y-m-d H:i:s'), $deviceString, $browserData, $user_ip);
                $insertToken->execute();
            } else {
                $updateToken = $conn->prepare("UPDATE login_token SET token = ? WHERE user_id = ?");
                $updateToken->bind_param("si", $token, $user_id);
                $updateToken->execute();
            }

            echo json_encode(["status" => "1", "msg" => "Login Successful, Redirecting you to your Dashboard"]);
        } else {
            echo json_encode(["status" => "2", "msg" => "Account Suspended, Contact Admin For Support"]);
        }
    } else {
        echo json_encode(["status" => "2", "msg" => "Invalid Email And Password"]);
    }
} else {
    echo json_encode(["status" => "2", "msg" => "Invalid Email And Password"]);
}

$conn->close();
?>
