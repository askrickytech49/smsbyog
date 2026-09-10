<?php
/**
 * api_status.php
 * Returns active status for all API providers.
 * Include after config.php is loaded.
 */
function get_api_status($conn) {
    $status = [];
    $q = mysqli_query($conn, "SELECT id, is_active FROM api_detail");
    while ($r = mysqli_fetch_assoc($q)) {
        $status[(int)$r['id']] = (int)$r['is_active'];
    }
    return [
        'server1' => ($status[8]  ?? 1) == 1, // TigerSMS
        'server2' => ($status[2]  ?? 1) == 1, // 5sim
        'usa'     => ($status[1]  ?? 1) == 1, // VerifySMS
        'usaca'   => ($status[3]  ?? 1) == 1, // DinoMMO
    ];
}
