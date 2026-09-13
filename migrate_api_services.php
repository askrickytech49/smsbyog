<?php
require 'include/config.php';

$sql = "CREATE TABLE IF NOT EXISTS api_active_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    api_id INT NOT NULL,
    service_code VARCHAR(255) NOT NULL,
    UNIQUE KEY api_service_unique (api_id, service_code)
)";

if (mysqli_query($conn, $sql)) {
    echo "Table api_active_services created successfully\n";
} else {
    echo "Error creating table: " . mysqli_error($conn) . "\n";
}
