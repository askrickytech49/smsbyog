<?php
require 'include/config.php';

$file = file_get_contents("C:\\Users\\ricky\\Downloads\\smsbyogc_app.sql");

if (!$file) {
    die("Could not read smsbyogc_app.sql");
}

// Find all INSERT INTO statements in the app SQL file
if (preg_match_all("/INSERT INTO `(.*?)`.*?VALUES(.*?);/s", $file, $matches)) {
    foreach ($matches[0] as $index => $insertQuery) {
        $table = $matches[1][$index];
        // We only care about user data tables to merge
        $tablesToMerge = [
            'user_data', 'user_wallet', 'refer_data', 'user_transaction', 
            'user_api', 'user_dynamic_va', 'active_number', 'crypto_data', 
            'crypto_recharge', 'login_token'
        ];
        
        if (in_array($table, $tablesToMerge)) {
            // Replace INSERT INTO with INSERT IGNORE INTO to safely put back missing users
            $ignoreQuery = preg_replace("/^INSERT INTO/i", "INSERT IGNORE INTO", $insertQuery);
            if ($conn->query($ignoreQuery)) {
                echo "Restored missing data into `$table` successfully.\n";
            } else {
                echo "Failed to restore `$table`: " . $conn->error . "\n";
            }
        }
    }
} else {
    echo "Could not parse INSERT statements from smsbyogc_app.sql.\n";
}
echo "Recovery complete!\n";
?>
