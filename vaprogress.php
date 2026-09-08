<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require 'include/config.php';

// Total active users
$totalResult = $conn->query("
    SELECT COUNT(*) as total 
    FROM user_data 
    WHERE status = '1'
");
$total = $totalResult->fetch_assoc()['total'] ?? 0;

// Users with VA
$withVAResult = $conn->query("
    SELECT COUNT(DISTINCT u.id) as total
    FROM user_data u
    INNER JOIN user_dynamic_va v ON u.id = v.user_id
    WHERE u.status = '1'
");
$withVA = $withVAResult->fetch_assoc()['total'] ?? 0;

// Users without VA
$withoutVAResult = $conn->query("
    SELECT COUNT(*) as total
    FROM user_data u
    LEFT JOIN user_dynamic_va v ON u.id = v.user_id
    WHERE v.user_id IS NULL
    AND u.status = '1'
");
$withoutVA = $withoutVAResult->fetch_assoc()['total'] ?? 0;

// Percentage
$percent = $total > 0 ? round(($withVA / $total) * 100, 2) : 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>VA Stats</title>
    <style>
        body { font-family: Arial; padding: 40px; background: #f4f6f9; }
        .card {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 8px 20px rgba(0,0,0,.05);
        }
        .big { font-size: 28px; font-weight: bold; }
        .progress {
            height: 25px;
            background: #eee;
            border-radius: 20px;
            overflow: hidden;
        }
        .bar {
            height: 100%;
            background: #28a745;
        }
    </style>
</head>
<body>

<div class="card">
    <div>Total Active Users</div>
    <div class="big"><?= $total ?></div>
</div>

<div class="card">
    <div>Users With Virtual Account</div>
    <div class="big"><?= $withVA ?></div>
</div>

<div class="card">
    <div>Users Without Virtual Account</div>
    <div class="big"><?= $withoutVA ?></div>
</div>

<div class="card">
    <div>Completion Progress (<?= $percent ?>%)</div>
    <div class="progress">
        <div class="bar" style="width: <?= $percent ?>%;"></div>
    </div>
</div>

<script>
// Auto refresh every 5 seconds
setTimeout(() => location.reload(), 5000);
</script>

</body>
</html>