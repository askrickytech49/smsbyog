<?php
require 'include/config.php';

// First, empty the tables we are migrating to avoid duplicates
$tablesToMigrate = [
    'user_data', 'user_wallet', 'refer_data', 'user_transaction', 
    'user_api', 'user_dynamic_va', 'active_number', 'crypto_data', 
    'crypto_recharge', 'login_token'
];
foreach ($tablesToMigrate as $table) {
    $conn->query("TRUNCATE TABLE `$table`");
}

$oldSqlPath = "C:\\Users\\ricky\\Downloads\\smsbyogc_main.sql";
$oldSql = file_get_contents($oldSqlPath);
if (!$oldSql) {
    $oldSqlPath = "C:\\Users\\ricky\\Downloads\\smsbyogc_main .sql";
    $oldSql = file_get_contents($oldSqlPath);
}

if (!$oldSql) {
    die("Could not read old sql file.");
}

foreach ($tablesToMigrate as $table) {
    $pattern = "/INSERT INTO `$table`.*?;/s";
    if (preg_match($pattern, $oldSql, $matches)) {
        $insertQuery = $matches[0];
        if ($conn->query($insertQuery)) {
            echo "Successfully imported `$table`\n";
        } else {
            echo "Failed to import `$table`: " . $conn->error . "\n";
        }
    }
}
echo "Direct DB Import Complete!\n";
?>
