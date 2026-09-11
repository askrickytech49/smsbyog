<?php
require 'include/config.php';

$queries = [
    "ALTER TABLE `active_number` ADD COLUMN `expires_at` DATETIME NULL DEFAULT NULL AFTER `buy_time`",
    "ALTER TABLE `api_detail` ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `profit_amount`",
    "UPDATE `service_icon` SET img_url='https://cdn.jsdelivr.net/gh/walkxcode/dashboard-icons/png/telegram.png' WHERE short_code='tg'",
    "UPDATE `service_icon` SET img_url='https://cdn.jsdelivr.net/gh/walkxcode/dashboard-icons/png/whatsapp.png' WHERE short_code='wa'",
    "UPDATE `service_icon` SET img_url='https://cdn.jsdelivr.net/gh/walkxcode/dashboard-icons/png/instagram.png' WHERE short_code='idg'",
    "UPDATE `service_icon` SET img_url='https://cdn.jsdelivr.net/gh/walkxcode/dashboard-icons/png/facebook.png' WHERE short_code='fb'"
];

foreach ($queries as $query) {
    try {
        $conn->query($query);
        echo "Success: $query\n";
    } catch (Exception $e) {
        echo "Skipped/Error (already exists): " . $e->getMessage() . "\n";
    }
}
echo "Database schema fully patched.\n";
?>
