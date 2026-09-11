<?php
require 'include/config.php';

$file = file_get_contents("smsbyogc_main.sql");

// Find all INSERT INTO statements in the local SQL file
// We want to change them to INSERT IGNORE to merge without duplicate key errors
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
            // Replace INSERT INTO with INSERT IGNORE INTO
            $ignoreQuery = preg_replace("/^INSERT INTO/i", "INSERT IGNORE INTO", $insertQuery);
            if ($conn->query($ignoreQuery)) {
                echo "Merged new data into `$table` successfully.\n";
            } else {
                echo "Failed to merge `$table`: " . $conn->error . "\n";
            }
        }
    }
} else {
    echo "Could not parse INSERT statements from local SQL.\n";
}
echo "Merge complete.\n";
?>
