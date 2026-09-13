<?php
include __DIR__ . '/../include/config.php';
$res = mysqli_query($conn, "SELECT service_id, service_name FROM service WHERE service_id='whatsapp' OR service_id='vk' OR service_name LIKE '%<span%' LIMIT 5");
while ($row = mysqli_fetch_assoc($res)) {
    print_r($row);
}
