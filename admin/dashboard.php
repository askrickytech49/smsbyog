<?php
include("auth.php");

/* ================= AUTH ================= */
if (!isset($_SESSION['token'])) {
    if (isset($_COOKIE['remember_me'])) {
        $_SESSION['token'] = $_COOKIE['remember_me'];
    } else {
        header('Location: login.php'); exit;
    }
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/* ================= EXPORT USERS TO CSV ================= */
if (isset($_GET['export_users']) && $_GET['export_users'] === 'csv') {

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=authpadi_users.csv');

    $output = fopen('php://output', 'w');

    // CSV header
    fputcsv($output, ['Name', 'Email', 'Registered Date']);

    $query = mysqli_query($conn, "
        SELECT name, email, register_date 
        FROM user_data
        ORDER BY id DESC
    ");

    while ($row = mysqli_fetch_assoc($query)) {
        fputcsv($output, [
            $row['name'],
            $row['email'],
            $row['register_date']
        ]);
    }

    fclose($output);
    exit;
}

$admin_sql = mysqli_query($conn,"SELECT * FROM login_token WHERE token='".$_SESSION['token']."'");
if (mysqli_num_rows($admin_sql) == 0) {
    header('Location: login.php'); exit;
}

$admin_data = mysqli_fetch_array($admin_sql);
$admin_sql2 = mysqli_query($conn,"SELECT * FROM user_data WHERE id='".$admin_data['user_id']."' AND status='1'");
$final_admin = mysqli_fetch_array($admin_sql2);

if (!in_array($final_admin['type'], ["admin", "super_admin"])) {
    header('Location: login.php'); exit;
}


/* ================= DATE ================= */
$today = date('Y-m-d');

/* ================= USERS ================= */
$total_user = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM user_data"));
$today_register = mysqli_num_rows(mysqli_query(
    $conn,"SELECT id FROM user_data WHERE DATE(register_date)='$today'"
));

/* ================= WEEKLY GROWTH ================= */
$this_week = mysqli_num_rows(mysqli_query($conn,"
    SELECT id FROM user_data 
    WHERE YEARWEEK(register_date,1)=YEARWEEK(CURDATE(),1)
"));

$last_week = mysqli_num_rows(mysqli_query($conn,"
    SELECT id FROM user_data 
    WHERE YEARWEEK(register_date,1)=YEARWEEK(CURDATE()-INTERVAL 1 WEEK,1)
"));

$weekly_growth = $last_week > 0
    ? round((($this_week - $last_week) / $last_week) * 100,1)
    : 100;

/* ================= RECHARGE (SUCCESS ONLY) ================= */
$total_recharge = (int)(mysqli_fetch_assoc(
    mysqli_query($conn,"
        SELECT SUM(amount) t 
        FROM user_transaction 
        WHERE status = 1
    ")
)['t'] ?? 0);

$total_recharge_today = (int)(mysqli_fetch_assoc(
    mysqli_query($conn,"
        SELECT SUM(amount) t 
        FROM user_transaction 
        WHERE status = 1 
        AND DATE(date) = '$today'
    ")
)['t'] ?? 0);

/* ================= OTP ================= */
$total_otp_sell = mysqli_num_rows(mysqli_query(
    $conn,"SELECT id FROM active_number WHERE status='1'"
));

$total_otp_sell_today = mysqli_num_rows(mysqli_query(
    $conn,"SELECT id FROM active_number WHERE status='1' AND DATE(buy_time)='$today'"
));

/* ================= BALANCES ================= */
$total_balances = (int)(mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT SUM(balance) t FROM user_wallet")
)['t'] ?? 0);

$total_block = (int)(mysqli_fetch_assoc(
    mysqli_query($conn,"
        SELECT SUM(uw.balance) t
        FROM user_wallet uw
        JOIN user_data ud ON uw.user_id = ud.id
        WHERE ud.status = '2'
    ")
)['t'] ?? 0);

/* ================= ADMIN ALERTS ================= */
$alerts = [];

if ($total_recharge_today <= 0) {
    $alerts[] = ['type'=>'danger','msg'=>'No successful recharge recorded today'];
}
if ($total_balances < 50000) {
    $alerts[] = ['type'=>'danger','msg'=>'System user balance critically low'];
}

/* ================= REVENUE CHARTS (SUCCESS ONLY) ================= */
$rev7 = []; $rev7_labels = [];
$q7 = mysqli_query($conn,"
    SELECT DATE(date) d, SUM(amount) total
    FROM user_transaction
    WHERE status = 1
    AND date >= CURDATE() - INTERVAL 6 DAY
    GROUP BY DATE(date)
    ORDER BY d ASC
");
while($r = mysqli_fetch_assoc($q7)){
    $rev7_labels[] = $r['d'];
    $rev7[] = (int)$r['total'];
}

$rev30 = []; $rev30_labels = [];
$q30 = mysqli_query($conn,"
    SELECT DATE(date) d, SUM(amount) total
    FROM user_transaction
    WHERE status = 1
    AND date >= CURDATE() - INTERVAL 29 DAY
    GROUP BY DATE(date)
    ORDER BY d ASC
");
while($r = mysqli_fetch_assoc($q30)){
    $rev30_labels[] = $r['d'];
    $rev30[] = (int)$r['total'];
}

/* ================= TOP USERS ================= */
$top_users = mysqli_query($conn,"
    SELECT uw.total_recharge, uw.balance, ud.name, ud.email
    FROM user_wallet uw
    JOIN user_data ud ON uw.user_id = ud.id
    ORDER BY uw.total_recharge DESC
    LIMIT 10
");
/* ================= NEW SECTIONS ================= */

/* 1️⃣ Payment Status Breakdown */
$success_tx = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT COUNT(*) c, SUM(amount) s
    FROM user_transaction
    WHERE status = 1 AND DATE(date) = CURDATE()
"));

$pending_tx = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT COUNT(*) c, SUM(amount) s
    FROM user_transaction
    WHERE status = 0 AND DATE(date) = CURDATE()
"));


/* 7️⃣ User Funnel */
$funded_users = mysqli_fetch_row(mysqli_query($conn,"
    SELECT COUNT(DISTINCT user_id)
    FROM user_transaction WHERE status = 1
"))[0];

$otp_users = mysqli_fetch_row(mysqli_query($conn,"
    SELECT COUNT(DISTINCT user_id)
    FROM active_number WHERE status = 1
"))[0];

/* 8️⃣ Risk Signals */
$risk_users = mysqli_query($conn,"
    SELECT user_id, COUNT(*) attempts
    FROM user_transaction
    WHERE status = 0
    AND date >= NOW() - INTERVAL 24 HOUR
    GROUP BY user_id
    HAVING attempts >= 3
");

/* 9️⃣ Daily Snapshot */
$daily_snapshot = "₦".number_format($total_recharge_today).
" revenue • ".$today_register." new users • ".$total_otp_sell_today." OTP sold";
?>


<!DOCTYPE html>
<html lang="en">
<head>
<title>Admin Dashboard</title>
<?php include("include/head.php"); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
.card{border-radius:14px;box-shadow:0 10px 25px rgba(0,0,0,.06)}
.metric{font-size:1.6rem;font-weight:800}
.submetric{font-size:13px;color:#6b7280}
@media(max-width:768px){
  .hide-mobile{display:none}
}
.badge {
  margin-top: 6px;
  font-size: 12px;
}
/* =========================
   USER FUNNEL – KPI STYLE
========================= */

.uf-section {
  margin-top: 2rem;
}

.uf-title {
  font-weight: 700;
  font-size: 15px;
  margin-bottom: 14px;
  padding-bottom: 10px;
  border-bottom: 3px solid #e10700;
}

.uf-card {
  background: #ffffff;
  border-radius: 16px;
  box-shadow: 0 10px 25px rgba(0,0,0,.06);
  padding: 18px 20px;
  height: 100%;
}

.uf-label {
  font-size: 13px;
  color: #6b7280;
  margin-bottom: 6px;
}

.uf-metric {
  font-size: 1.6rem;
  font-weight: 800;
  color: #111827;
}

.uf-sub {
  font-size: 12px;
  margin-top: 6px;
}

/* Color helpers (same logic as KPIs) */
.uf-blue {
  color: #3730a3;
}

.uf-green {
  color: #166534;
}

.sambt{
    margin-bottom: 20px;
}

/* Mobile */
@media (max-width: 768px) {
  .uf-metric {
    font-size: 1.4rem;
  }
}


</style>
</head>

<body>
<div id="wrapper">
<?php include("include/slidebar.php"); ?>
<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
<?php include("include/topbar.php"); ?>

<div class="container-fluid">


<!-- KPIs -->
<div class="row">
<div class="col-md-3 mb-4">
<div class="card"><div class="card-body">
<span>Total Users</span>
<div class="metric"><?php echo $total_user; ?></div>
<div class="submetric">+<?php echo $today_register; ?> today</div>
<span class="badge <?php echo $weekly_growth >= 0 ? 'bg-success' : 'bg-danger'; ?>">
        <?php echo $weekly_growth >= 0 ? '↑' : '↓'; ?>
        <?php echo abs($weekly_growth); ?>% this week
      </span>
</div></div>
</div>

<div class="col-md-3 mb-4">
<div class="card bg-warning text-white"><div class="card-body">
<span>Total Amout Funded</span>
<div class="metric">₦<?php echo number_format($total_recharge); ?></div>
<small>Today ₦<?php echo number_format($total_recharge_today); ?></small>
</div></div>
</div>

<div class="col-md-3 mb-4">
<div class="card"><div class="card-body">
<span>OTP Sold</span>
<div class="metric"><?php echo $total_otp_sell; ?></div>
<div class="submetric">+<?php echo $total_otp_sell_today; ?> today</div>
</div></div>
</div>

<div class="col-md-3 mb-4">
<div class="card"><div class="card-body">
<span>User Balances</span>
<div class="metric">₦<?php echo number_format($total_balances); ?></div>
<div class="submetric">Blocked ₦<?php echo number_format($total_block); ?></div>
</div></div>
</div>
</div>
<!-- USER FUNNEL (KPI CARD STYLE) -->
<div class="container-fluid">
    <!-- DAILY SNAPSHOT -->
<div class="alert alert-info mb-4">
<strong>Today:</strong> <?= $daily_snapshot ?>
</div>
<div class="uf-section">

  <div class="uf-title">User Funnel</div>

  <div class="row">

    <div class="col-md-4 mb-4">
      <div class="uf-card">
        <div class="uf-label">Registered Users</div>
        <div class="uf-metric uf-blue"><?= $total_user ?></div>
        <div class="uf-sub text-muted">All time</div>
      </div>
    </div>

    <div class="col-md-4 mb-4">
      <div class="uf-card">
        <div class="uf-label">Funded Wallet</div>
        <div class="uf-metric uf-green"><?= $funded_users ?></div>
        <div class="uf-sub text-muted">At least one recharge</div>
      </div>
    </div>

    <div class="col-md-4 mb-4">
      <div class="uf-card">
        <div class="uf-label">Bought OTP</div>
        <div class="uf-metric uf-green"><?= $otp_users ?></div>
        <div class="uf-sub text-muted">Converted users</div>
      </div>
    </div>

  </div>
</div>

<a href="?export_users=csv" class="btn btn-outline-success btn-sm sambt">
  Export Users
</a>


<!-- CHARTS -->
<div class="row">
<div class="col-md-6"><div class="card"><div class="card-body">
<h6>7-Day Revenue</h6>
<canvas id="rev7"></canvas>
</div></div></div>

<div class="col-md-6"><div class="card"><div class="card-body">
<h6>30-Day Revenue</h6>
<canvas id="rev30"></canvas>
</div></div></div>
</div>

<!-- TOP USERS -->
<div class="card mt-4">
<div class="card-body">
<h5>Top 10 Users</h5>
<div class="table-responsive">
<table class="table table-striped">
<thead>
<tr>
<th>#</th>
<th>Name</th>
<th class="hide-mobile">Email</th>
<th>Total Recharge</th>
<th>Balance</th>
</tr>
</thead>
<tbody>
<?php $i=1; while($u=mysqli_fetch_assoc($top_users)){ ?>
<tr>
<td><?php echo $i++; ?></td>
<td><?php echo htmlspecialchars($u['name']); ?></td>
<td class="hide-mobile"><?php echo htmlspecialchars($u['email']); ?></td>
<td>₦<?php echo number_format($u['total_recharge']); ?></td>
<td>₦<?php echo number_format($u['balance']); ?></td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
</div>




<!-- RISK SIGNALS -->
<div class="card mt-4 border-danger">
<div class="card-body">
<h6>Risk Alerts</h6>
<?php if(mysqli_num_rows($risk_users)>0){ ?>
<span class="text-danger">Multiple failed payment attempts detected</span>
<?php } else { ?>
<span class="text-success">No risk alerts</span>
<?php } ?>
</div>
</div>


</div>
</div>
<?php include("include/script.php"); ?>
</body>
</html>

<script>
new Chart(document.getElementById('rev7'),{
type:'line',
data:{labels:<?=json_encode($rev7_labels)?>,
datasets:[{data:<?=json_encode($rev7)?>,borderColor:'#e10700',tension:.4}]}
});

new Chart(document.getElementById('rev30'),{
type:'bar',
data:{labels:<?=json_encode($rev30_labels)?>,
datasets:[{data:<?=json_encode($rev30)?>,backgroundColor:'#ff9c33'}]}
});
</script>
