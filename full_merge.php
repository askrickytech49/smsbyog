<?php
/*
 * PROPER MERGE STRATEGY:
 * 1. Drop DB and import smsbyogc_app.sql (NEW backup = correct balances/histories)
 * 2. Then add missing users from smsbyogc_main.sql (OLD backup) 
 * 3. Apply schema migrations
 */

$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'smsbyogc_main';

// ===== STEP 1: Count users in each backup for comparison =====
$oldSql = file_get_contents("C:\\Users\\ricky\\Downloads\\smsbyogc_main.sql") 
       ?: file_get_contents("C:\\Users\\ricky\\Downloads\\smsbyogc_main .sql");
$newSql = file_get_contents("C:\\Users\\ricky\\Downloads\\smsbyogc_app.sql");

if (!$oldSql) die("Cannot read old SQL file (smsbyogc_main.sql)\n");
if (!$newSql) die("Cannot read new SQL file (smsbyogc_app.sql)\n");

// Extract emails from old backup
preg_match_all("/'([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})'/", $oldSql, $oldEmails);
preg_match_all("/'([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})'/", $newSql, $newEmails);

// Get user_data emails specifically  
preg_match("/INSERT INTO `user_data`.*?VALUES(.*?);/s", $oldSql, $oldUserBlock);
preg_match("/INSERT INTO `user_data`.*?VALUES(.*?);/s", $newSql, $newUserBlock);

preg_match_all("/'([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})'/", $oldUserBlock[1], $oldUserEmails);
preg_match_all("/'([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})'/", $newUserBlock[1], $newUserEmails);

$oldEmailList = $oldUserEmails[1];
$newEmailList = $newUserEmails[1];

// Find emails in OLD that are NOT in NEW (these are the ones we need to add)
$missingInNew = array_diff($oldEmailList, $newEmailList);
// Find emails in NEW that are NOT in OLD
$onlyInNew = array_diff($newEmailList, $oldEmailList);

echo "=== BACKUP COMPARISON ===\n";
echo "Old backup (smsbyogc_main.sql): " . count($oldEmailList) . " users\n";
echo "New backup (smsbyogc_app.sql):  " . count($newEmailList) . " users\n";
echo "Users ONLY in old (need to add): " . count($missingInNew) . "\n";
echo "Users ONLY in new (already there): " . count($onlyInNew) . "\n\n";

if (count($missingInNew) > 0) {
    echo "Users that need to be added from old backup:\n";
    foreach ($missingInNew as $email) {
        echo "  - $email\n";
    }
}

echo "\n=== STEP 1: Drop DB and import NEW backup (smsbyogc_app.sql) ===\n";

// Drop and recreate database
$conn = new mysqli($dbHost, $dbUser, $dbPass);
$conn->query("DROP DATABASE IF EXISTS `$dbName`");
$conn->query("CREATE DATABASE `$dbName`");
$conn->close();

// Import new SQL file
$importCmd = "C:\\xampp\\mysql\\bin\\mysql.exe -u root $dbName < \"C:\\Users\\ricky\\Downloads\\smsbyogc_app.sql\" 2>&1";
$output = shell_exec($importCmd);
if ($output) {
    echo "Import output: $output\n";
} else {
    echo "New backup imported successfully!\n";
}

// ===== STEP 2: Add missing users from old backup =====
echo "\n=== STEP 2: Adding missing users from old backup ===\n";

$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

// Parse old user_data rows  
$oldBlock = $oldUserBlock[1];
preg_match_all("/\((\d+),\s*'((?:[^'\\\\]|\\\\.)*)',\s*'((?:[^'\\\\]|\\\\.)*)',\s*'((?:[^'\\\\]|\\\\.)*)',\s*'((?:[^'\\\\]|\\\\.)*)',\s*'((?:[^'\\\\]|\\\\.)*)',\s*'((?:[^'\\\\]|\\\\.)*)',\s*(\d+),\s*'((?:[^'\\\\]|\\\\.)*)',\s*(\d+)\)/s", $oldBlock, $oldRows, PREG_SET_ORDER);

$insertedUsers = 0;
$userIdMap = []; // old_id => new_id mapping

foreach ($oldRows as $row) {
    $oldId = $row[1];
    $email = $row[4];
    
    // Only insert if email is missing from the new backup
    if (!in_array($email, $missingInNew)) continue;
    
    $name = $conn->real_escape_string($row[2]);
    $username = $conn->real_escape_string($row[3]);
    $emailEsc = $conn->real_escape_string($row[4]);
    $password = $conn->real_escape_string($row[5]);
    $type = $conn->real_escape_string($row[6]);
    $image_url = $conn->real_escape_string($row[7]);
    $status = $row[8];
    $register_date = $conn->real_escape_string($row[9]);
    $va_processing = $row[10];
    
    // Check if ID conflicts
    $checkId = $conn->query("SELECT id FROM user_data WHERE id = $oldId");
    if ($checkId->num_rows > 0) {
        // ID conflict, let auto-increment assign new ID
        $query = "INSERT INTO user_data (name, username, email, password, type, image_url, status, register_date, va_processing) VALUES ('$name', '$username', '$emailEsc', '$password', '$type', '$image_url', $status, '$register_date', $va_processing)";
    } else {
        $query = "INSERT INTO user_data (id, name, username, email, password, type, image_url, status, register_date, va_processing) VALUES ($oldId, '$name', '$username', '$emailEsc', '$password', '$type', '$image_url', $status, '$register_date', $va_processing)";
    }
    
    try {
        if ($conn->query($query)) {
            $newId = $conn->insert_id ?: $oldId;
            $userIdMap[$oldId] = $newId;
            echo "  Added user: $email (Type: {$row[6]}, OldID: $oldId, NewID: $newId)\n";
            $insertedUsers++;
        }
    } catch (Exception $e) {
        echo "  ERROR adding $email: " . $e->getMessage() . "\n";
    }
}

echo "Inserted $insertedUsers missing users.\n";

// Now add their wallets
echo "\n=== Adding wallets for missing users ===\n";
preg_match("/INSERT INTO `user_wallet`.*?VALUES(.*?);/s", $oldSql, $oldWalletBlock);
if ($oldWalletBlock) {
    preg_match_all("/\((\d+),\s*(\d+),\s*'?([\d.]+)'?,\s*'?([\d.]+)'?,\s*'?([\d.]+)'?,\s*'?([\d.]+)'?\)/", $oldWalletBlock[1], $walletRows, PREG_SET_ORDER);
    
    $walletsAdded = 0;
    foreach ($walletRows as $wRow) {
        $oldUserId = $wRow[2];
        if (isset($userIdMap[$oldUserId])) {
            $newUserId = $userIdMap[$oldUserId];
            $balance = $wRow[3];
            $totalRecharge = $wRow[4];
            $totalOtp = $wRow[5];
            $totalSms = $wRow[6];
            
            try {
                $conn->query("INSERT INTO user_wallet (user_id, balance, total_recharge, total_otp, total_sms) VALUES ($newUserId, '$balance', '$totalRecharge', '$totalOtp', '$totalSms')");
                echo "  Wallet for user $newUserId: Balance=$balance, Recharge=$totalRecharge\n";
                $walletsAdded++;
            } catch (Exception $e) {
                echo "  Wallet error for user $newUserId: " . $e->getMessage() . "\n";
            }
        }
    }
    echo "Added $walletsAdded wallets.\n";
}

// Add their transactions
echo "\n=== Adding transactions for missing users ===\n";
preg_match("/INSERT INTO `user_transaction`.*?VALUES(.*?);/s", $oldSql, $oldTxnBlock);
if ($oldTxnBlock) {
    preg_match_all("/\((\d+),\s*(\d+),\s*'((?:[^'\\\\]|\\\\.)*)',\s*'?([\d.]+)'?,\s*'((?:[^'\\\\]|\\\\.)*)',\s*'((?:[^'\\\\]|\\\\.)*)',\s*'((?:[^'\\\\]|\\\\.)*)',\s*'((?:[^'\\\\]|\\\\.)*)'\)/", $oldTxnBlock[1], $txnRows, PREG_SET_ORDER);
    
    $txnsAdded = 0;
    foreach ($txnRows as $tRow) {
        $oldUserId = $tRow[2];
        if (isset($userIdMap[$oldUserId])) {
            $newUserId = $userIdMap[$oldUserId];
            $txnId = $conn->real_escape_string($tRow[3]);
            $amount = $tRow[4];
            $type = $conn->real_escape_string($tRow[5]);
            $status = $conn->real_escape_string($tRow[6]);
            $date = $conn->real_escape_string($tRow[7]);
            $adminNote = $conn->real_escape_string($tRow[8]);
            
            try {
                $conn->query("INSERT INTO user_transaction (user_id, txn_id, amount, type, status, date, admin_note) VALUES ($newUserId, '$txnId', '$amount', '$type', '$status', '$date', '$adminNote')");
                $txnsAdded++;
            } catch (Exception $e) {}
        }
    }
    echo "Added $txnsAdded transactions.\n";
}

// Add their referral data
echo "\n=== Adding referral data for missing users ===\n";
preg_match("/INSERT INTO `refer_data`.*?VALUES(.*?);/s", $oldSql, $oldReferBlock);
if ($oldReferBlock) {
    preg_match_all("/\((\d+),\s*(\d+),\s*'((?:[^'\\\\]|\\\\.)*)',\s*'((?:[^'\\\\]|\\\\.)*)',\s*'?([\d.]+)'?,\s*'?([\d.]+)'?,\s*'?([\d.]+)'?\)/", $oldReferBlock[1], $referRows, PREG_SET_ORDER);
    
    $refersAdded = 0;
    foreach ($referRows as $rRow) {
        $oldUserId = $rRow[2];
        if (isset($userIdMap[$oldUserId])) {
            $newUserId = $userIdMap[$oldUserId];
            $ownCode = $conn->real_escape_string($rRow[3]);
            $referBy = $conn->real_escape_string($rRow[4]);
            $balance = $rRow[5];
            $transfer = $rRow[6];
            $totalEarn = $rRow[7];
            
            try {
                $conn->query("INSERT INTO refer_data (user_id, own_code, refer_by, balance, transfer, total_earn) VALUES ($newUserId, '$ownCode', '$referBy', '$balance', '$transfer', '$totalEarn')");
                $refersAdded++;
            } catch (Exception $e) {}
        }
    }
    echo "Added $refersAdded referral records.\n";
}

// Add their API keys
echo "\n=== Adding API keys for missing users ===\n";
preg_match("/INSERT INTO `user_api`.*?VALUES(.*?);/s", $oldSql, $oldApiBlock);
if ($oldApiBlock) {
    preg_match_all("/\((\d+),\s*(\d+),\s*'((?:[^'\\\\]|\\\\.)*)',\s*'((?:[^'\\\\]|\\\\.)*)'\)/", $oldApiBlock[1], $apiRows, PREG_SET_ORDER);
    
    $apisAdded = 0;
    foreach ($apiRows as $aRow) {
        $oldUserId = $aRow[2];
        if (isset($userIdMap[$oldUserId])) {
            $newUserId = $userIdMap[$oldUserId];
            $apiKey = $conn->real_escape_string($aRow[3]);
            $createTime = $conn->real_escape_string($aRow[4]);
            
            try {
                $conn->query("INSERT INTO user_api (user_id, api_key, create_time) VALUES ($newUserId, '$apiKey', '$createTime')");
                $apisAdded++;
            } catch (Exception $e) {}
        }
    }
    echo "Added $apisAdded API keys.\n";
}

// ===== STEP 3: Apply schema upgrades =====
echo "\n=== STEP 3: Applying schema upgrades ===\n";
$upgrades = [
    "ALTER TABLE `settings` ADD COLUMN `maintenance_mode` TINYINT(1) NOT NULL DEFAULT 0",
    "ALTER TABLE `settings` ADD COLUMN `dev_payment_mode` TINYINT(1) NOT NULL DEFAULT 0",
    "ALTER TABLE `active_number` ADD COLUMN `expires_at` DATETIME NULL DEFAULT NULL AFTER `buy_time`",
    "ALTER TABLE `api_detail` ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `profit_amount`",
    "UPDATE `service_icon` SET img_url='https://cdn.jsdelivr.net/gh/walkxcode/dashboard-icons/png/telegram.png' WHERE short_code='tg'",
    "UPDATE `service_icon` SET img_url='https://cdn.jsdelivr.net/gh/walkxcode/dashboard-icons/png/whatsapp.png' WHERE short_code='wa'",
    "UPDATE `service_icon` SET img_url='https://cdn.jsdelivr.net/gh/walkxcode/dashboard-icons/png/instagram.png' WHERE short_code='idg'",
    "UPDATE `service_icon` SET img_url='https://cdn.jsdelivr.net/gh/walkxcode/dashboard-icons/png/facebook.png' WHERE short_code='fb'"
];

foreach ($upgrades as $query) {
    try {
        $conn->query($query);
        echo "  OK: " . substr($query, 0, 60) . "...\n";
    } catch (Exception $e) {
        echo "  Skipped (already exists): " . substr($e->getMessage(), 0, 60) . "\n";
    }
}

// ===== FINAL VERIFICATION =====
echo "\n=== FINAL VERIFICATION ===\n";
$totalUsers = $conn->query("SELECT COUNT(*) as c FROM user_data")->fetch_assoc()['c'];
echo "Total users: $totalUsers\n";

echo "\nAll admin accounts:\n";
$admins = $conn->query("SELECT id, name, email, type FROM user_data WHERE type IN ('admin', 'super_admin')");
while ($row = $admins->fetch_assoc()) {
    echo "  {$row['email']} ({$row['name']}) - {$row['type']}\n";
}

echo "\nVerifying key accounts:\n";
$keyEmails = ['rickyessential49@gmail.com', 'junioranthony0011@gmail.com', 'admin@myogsms.com'];
foreach ($keyEmails as $e) {
    $r = $conn->query("SELECT u.id, u.name, u.email, u.type, w.balance FROM user_data u LEFT JOIN user_wallet w ON u.id = w.user_id WHERE u.email = '$e'");
    if ($r->num_rows > 0) {
        $d = $r->fetch_assoc();
        echo "  ✓ {$d['email']} - {$d['name']} ({$d['type']}) Balance: {$d['balance']}\n";
    } else {
        echo "  ✗ $e - NOT FOUND\n";
    }
}

$conn->close();
echo "\n=== DONE! Database fully merged and upgraded. ===\n";
?>
