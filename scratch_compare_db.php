<?php
$conn_live = new mysqli('localhost', 'root', '', 'smsbyogc_live');
$conn_main = new mysqli('localhost', 'root', '', 'smsbyogc_main');

if ($conn_live->connect_error) die("Live DB Connection failed: " . $conn_live->connect_error);
if ($conn_main->connect_error) die("Main DB Connection failed: " . $conn_main->connect_error);

echo "=== Tables in Live DB ===\n";
$res_live = $conn_live->query("SHOW TABLES");
$tables_live = [];
while ($row = $res_live->fetch_row()) $tables_live[] = $row[0];

$res_main = $conn_main->query("SHOW TABLES");
$tables_main = [];
while ($row = $res_main->fetch_row()) $tables_main[] = $row[0];

$missing_in_live = array_diff($tables_main, $tables_live);
$missing_in_main = array_diff($tables_live, $tables_main);

echo "Tables only in local (main): " . implode(", ", $missing_in_live) . "\n";
echo "Tables only in live: " . implode(", ", $missing_in_main) . "\n\n";

// Compare api_detail
echo "=== api_detail ===\n";
$res_live = $conn_live->query("SELECT * FROM api_detail");
$live_apis = [];
while ($row = $res_live->fetch_assoc()) $live_apis[$row['id']] = $row;

$res_main = $conn_main->query("SELECT * FROM api_detail");
$main_apis = [];
while ($row = $res_main->fetch_assoc()) $main_apis[$row['id']] = $row;

foreach ($main_apis as $id => $data) {
    if (!isset($live_apis[$id])) {
        echo "NEW API in local: ID $id - {$data['api_name']} (URL: {$data['api_url']})\n";
    } else {
        $diffs = array_diff_assoc($data, $live_apis[$id]);
        if (!empty($diffs)) {
            echo "MODIFIED API in local: ID $id - {$data['api_name']}\n";
            print_r($diffs);
        }
    }
}
