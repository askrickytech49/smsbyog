<?php
$oldSqlPath = "C:\\Users\\ricky\\Downloads\\smsbyogc_main.sql"; // no space, as per user's last message
$newSqlPath = "c:\\xampp\\htdocs\\smsbyog\\smsbyogc_main.sql";

$oldSql = file_get_contents($oldSqlPath);
if (!$oldSql) {
    // fallback to space
    $oldSqlPath = "C:\\Users\\ricky\\Downloads\\smsbyogc_main .sql";
    $oldSql = file_get_contents($oldSqlPath);
}

$newSql = file_get_contents($newSqlPath);

if ($oldSql === false || $newSql === false) {
    die("Error reading files.");
}

$tablesToMigrate = [
    'user_data',
    'user_wallet',
    'refer_data',
    'user_transaction',
    'user_api',
    'user_dynamic_va',
    'active_number',
    'crypto_data',
    'crypto_recharge',
    'login_token'
];

foreach ($tablesToMigrate as $table) {
    $pattern = "/INSERT INTO `$table`.*?;/s";
    
    if (preg_match($pattern, $oldSql, $oldMatches)) {
        $oldInsertBlock = $oldMatches[0];
        
        // Use preg_replace_callback to avoid $ backreference issues
        if (preg_match($pattern, $newSql)) {
            $newSql = preg_replace_callback($pattern, function($matches) use ($oldInsertBlock) {
                return $oldInsertBlock;
            }, $newSql);
            echo "Replaced `$table` safely.\n";
        } else {
            // If it doesn't have one, we append it after the CREATE TABLE
            $createTablePattern = "/CREATE TABLE `$table`.*?;/s";
            if (preg_match($createTablePattern, $newSql, $createMatches)) {
                $newSql = preg_replace_callback($createTablePattern, function($matches) use ($oldInsertBlock) {
                    return $matches[0] . "\n\n--\n-- Dumping data for table\n--\n\n" . $oldInsertBlock . "\n";
                }, $newSql);
                echo "Appended `$table` data safely.\n";
            }
        }
    }
}

// Check for and remove the weird duplicate yINSERT INTO string I caused
$newSql = preg_replace('/yINSERT INTO `user_data`/', 'INSERT INTO `user_data`', $newSql);

file_put_contents($newSqlPath, $newSql);
echo "Migration fixed and complete!\n";
?>
