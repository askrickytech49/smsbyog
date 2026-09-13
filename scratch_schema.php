<?php
require 'include/config.php';

$sql = "ALTER TABLE api_detail ADD COLUMN sort_order INT DEFAULT 9999";

if (mysqli_query($conn, $sql)) {
    echo "Added sort_order column to api_detail successfully\n";
} else {
    echo "Error: " . mysqli_error($conn) . "\n";
}
