<?php
$conn_live = new mysqli('localhost', 'root', '', 'smsbyogc_live');
$conn_main = new mysqli('localhost', 'root', '', 'smsbyogc_main');

// Compare columns in api_detail
$res_live = $conn_live->query("SHOW COLUMNS FROM api_detail");
$live_cols = [];
while ($row = $res_live->fetch_assoc()) $live_cols[] = $row['Field'];

$res_main = $conn_main->query("SHOW COLUMNS FROM api_detail");
$main_cols = [];
while ($row = $res_main->fetch_assoc()) $main_cols[] = $row['Field'];

$missing_in_live = array_diff($main_cols, $live_cols);
echo "Columns only in local api_detail: " . implode(", ", $missing_in_live) . "\n";
