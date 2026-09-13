<?php
include 'include/config.php';
$res = mysqli_query($conn, 'SELECT service_code FROM api_active_services WHERE api_id=3 LIMIT 5');
while ($r = mysqli_fetch_assoc($res)) {
    echo $r['service_code'] . "\n";
}
