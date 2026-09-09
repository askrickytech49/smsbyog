<?php
/**
 * api_active_check.php
 * Call this at the top of any getService*.php file.
 * Usage: require_api_active($conn, $api_id);
 * If the API is disabled, outputs JSON error and exits.
 */
function require_api_active($conn, $api_id) {
    $id  = (int)$api_id;
    $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT is_active FROM api_detail WHERE id='$id' LIMIT 1"));
    if (!$row || $row['is_active'] != 1) {
        echo json_encode(['status' => '503', 'message' => 'This service is temporarily unavailable.', 'service' => []]);
        exit;
    }
}
