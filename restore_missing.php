<?php
require 'include/config.php';

// Read the new backup (smsbyogc_app.sql) which has the correct newer users
$appSql = file_get_contents("C:\\Users\\ricky\\Downloads\\smsbyogc_app.sql");

// Extract user_data rows from the new backup
preg_match("/INSERT INTO `user_data`\s*\(.*?\)\s*VALUES\s*(.*?);/s", $appSql, $m);
$valuesBlock = $m[1];
preg_match_all("/\((\d+),\s*'((?:[^'\\\\]|\\\\.)*)',\s*'((?:[^'\\\\]|\\\\.)*)',\s*'((?:[^'\\\\]|\\\\.)*)',\s*'((?:[^'\\\\]|\\\\.)*)',\s*'((?:[^'\\\\]|\\\\.)*)',\s*'((?:[^'\\\\]|\\\\.)*)',\s*(\d+),\s*'((?:[^'\\\\]|\\\\.)*)',\s*(\d+)\)/s", $valuesBlock, $rows, PREG_SET_ORDER);

echo "Found " . count($rows) . " users in smsbyogc_app.sql\n\n";

// For each user in the new backup, check if their EMAIL exists in the active DB
// If not, insert them (with a new ID if needed to avoid conflicts)
$inserted = 0;
$updated = 0;
foreach ($rows as $row) {
    $id = $row[1];
    $name = $conn->real_escape_string($row[2]);
    $username = $conn->real_escape_string($row[3]);
    $email = $conn->real_escape_string($row[4]);
    $password = $conn->real_escape_string($row[5]);
    $type = $conn->real_escape_string($row[6]);
    $image_url = $conn->real_escape_string($row[7]);
    $status = $row[8];
    $register_date = $conn->real_escape_string($row[9]);
    $va_processing = $row[10];
    
    // Check if email exists
    $check = $conn->prepare("SELECT id FROM user_data WHERE email = ?");
    $rawEmail = $row[4];
    $check->bind_param("s", $rawEmail);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows == 0) {
        // User missing! Check if ID is taken
        $checkId = $conn->query("SELECT id FROM user_data WHERE id = $id");
        if ($checkId->num_rows > 0) {
            // ID conflict - insert without specifying ID (auto increment)
            $query = "INSERT INTO user_data (name, username, email, password, type, image_url, status, register_date, va_processing) VALUES ('$name', '$username', '$email', '$password', '$type', '$image_url', $status, '$register_date', $va_processing)";
        } else {
            $query = "INSERT INTO user_data (id, name, username, email, password, type, image_url, status, register_date, va_processing) VALUES ($id, '$name', '$username', '$email', '$password', '$type', '$image_url', $status, '$register_date', $va_processing)";
        }
        
        try {
            if ($conn->query($query)) {
                $newId = $conn->insert_id ? $conn->insert_id : $id;
                echo "INSERTED: $rawEmail (Name: {$row[2]}, Type: {$row[6]}, New ID: $newId)\n";
                $inserted++;
                
                // Also create their wallet if it exists in the backup
                if (preg_match("/\($id,\s*$id,\s*'?([\d.]+)'?,\s*'?([\d.]+)'?,\s*'?([\d.]+)'?,\s*'?([\d.]+)'?\)/", $appSql, $walletMatch)) {
                    $balance = $walletMatch[1];
                    $totalRecharge = $walletMatch[2];
                    $totalOtp = $walletMatch[3];
                    $totalSms = $walletMatch[4];
                    $conn->query("INSERT IGNORE INTO user_wallet (user_id, balance, total_recharge, total_otp, total_sms) VALUES ($newId, '$balance', '$totalRecharge', '$totalOtp', '$totalSms')");
                    echo "  -> Wallet restored: Balance=$balance\n";
                }
            }
        } catch (Exception $e) {
            echo "ERROR: $rawEmail - " . $e->getMessage() . "\n";
        }
    }
}

echo "\nTotal inserted: $inserted\n";

// Verify rickyessential49
$verify = $conn->prepare("SELECT id, name, email, type FROM user_data WHERE email = 'rickyessential49@gmail.com'");
$verify->execute();
$verifyResult = $verify->get_result();
if ($verifyResult->num_rows > 0) {
    echo "\nVERIFIED: rickyessential49@gmail.com is now in the database!\n";
    var_dump($verifyResult->fetch_assoc());
} else {
    echo "\nWARNING: rickyessential49@gmail.com still not found!\n";
}

echo "\nTotal users now: ";
$cnt = $conn->query("SELECT COUNT(*) as c FROM user_data")->fetch_assoc();
echo $cnt['c'] . "\n";
?>
