<?php
session_start();
include 'include/config.php';
include __DIR__ . '/include/mode_check.php';
require __DIR__ . '/class/class.control.php';

/* ===============================
   AUTH CHECK
================================ */
if (empty($_SESSION['token'])) {
    session_destroy();
    redirect('login');
}

$wallet = new radiumsahil();
$userdata = $wallet->userdata();
$userwallet = $wallet->userwallet();

if ($userdata === false) {
    session_destroy();
    redirect('login');
}

$numbers = $wallet->number_history();
$wallet->closeConnection();

$page_title = "Number Purchase History - ".$site_data['web_name'];
include ('partial/header.php');
?>

<link rel="stylesheet" href="https://cdn.datatables.net/2.0.5/css/dataTables.dataTables.min.css">

<style>
:root{
  --auth-orange:#e10700;
  --border:#e6e9f0;
  --muted:#6c757d;
}

/* =========================
   SHARED
========================= */
.auth-card{
  background:#fff;
  border:1px solid var(--border);
  border-radius:16px;
  padding:20px;
  box-shadow:0 10px 35px rgba(0,0,0,.06);
}

.auth-header{
  display:flex;
  justify-content:space-between;
  margin-bottom:16px;
}

.badge-auth {
  color: var(--auth-orange);
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.3px;
  text-transform: uppercase;
}


/* =========================
   RESPONSIVE SWITCH
========================= */
.desktop-only{display:block}
.mobile-only{display:none}

@media(max-width:768px){
  .desktop-only{display:none}
  .mobile-only{display:block}
}

/* =========================
   DESKTOP TABLE
========================= */
.dataTables_wrapper table{
  border-collapse:separate!important;
  border-spacing:0 14px!important;
}

.dataTables_wrapper tbody tr{
  background:#fff;
  border:1px solid var(--border);
  border-radius:14px;
}

.dataTables_wrapper tbody td{
  padding:16px 18px;
  border:none!important;
}

.number{
  font-family:ui-monospace, monospace;
  font-weight:600;
}

.amount{
  font-weight:800;
  color:var(--auth-orange);
}

.sms-badge{
  background:#f1f3f9;
  padding:6px 12px;
  border-radius:999px;
  font-size:12px;
  font-weight:600;
}

.status-success {
  background:#d1fae5; color:#065f46;
  padding:4px 10px; border-radius:999px;
  font-size:12px; font-weight:700;
}
.status-cancelled {
  background:#fee2e2; color:#991b1b;
  padding:4px 10px; border-radius:999px;
  font-size:12px; font-weight:700;
}
.status-active {
  background:#fef9c3; color:#854d0e;
  padding:4px 10px; border-radius:999px;
  font-size:12px; font-weight:700;
}

/* =========================
   MOBILE CARDS (REAL UI)
========================= */
.num-card{
  background:#fff;
  border:1px solid var(--border);
  border-radius:14px;
  padding:14px 16px;
  margin-bottom:14px;
  box-shadow:0 6px 20px rgba(0,0,0,.05);
}

.num-top{
  display:flex;
  justify-content:space-between;
  margin-bottom:6px;
}

.num-service{
  background:#f1f3f9;
  padding:6px 10px;
  border-radius:8px;
  font-weight:600;
  font-size:13px;
}

.num-row{
  display:flex;
  justify-content:space-between;
  font-size:14px;
  margin-top:6px;
}

.num-label{
  color:var(--muted);
}

.num-amount{
  font-weight:800;
  color:var(--auth-orange);
}

.num-number{
  font-family:ui-monospace, monospace;
  font-weight:600;
}
</style>

<?php include ('partial/loader.php'); ?>

<div class="page-wrapper compact-wrapper">
<?php include ('partial/topbar.php'); ?>
<div class="page-body-wrapper">
<?php include ('partial/sidebar.php'); ?>

<div class="page-body"><br><br>
<div class="container-fluid">

<div class="auth-card">
  <div class="auth-header">
    <div>
      <h4>Number Purchase History</h4>
      <p class="text-muted">All virtual numbers you’ve purchased</p>
    </div>
    <span class="badge-auth"><?= count($numbers); ?> Records</span>
  </div>

<?php if(!$numbers): ?>
  <p class="text-center text-muted">No number history yet.</p>

<?php else: ?>

<!-- ================= DESKTOP ================= -->
<div class="desktop-only">
  <table id="myTable" class="display w-100">
    <thead>
      <tr>
        <th>Service</th>
        <th>Number</th>
        <th>Amount</th>
        <th>SMS</th>
        <th>Status</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach($numbers as $row):
        if ($row['status'] == '1') {
            $statusBadge = '<span class="status-success">Received</span>';
        } elseif ($row['status'] == '3') {
            $statusBadge = '<span class="status-cancelled">Cancelled</span>';
        } else {
            $statusBadge = '<span class="status-active">Active</span>';
        }
    ?>
      <tr>
        <td><?= htmlspecialchars($row['service_name']) ?></td>
        <td class="number">+<?= htmlspecialchars(ltrim($row['number'], '+')) ?></td>
        <td class="amount">₦<?= number_format($row['service_price']) ?></td>
        <td><span class="sms-badge"><?= $row['sms'] ? htmlspecialchars($row['sms']) : '—' ?></span></td>
        <td><?= $statusBadge ?></td>
        <td><?= htmlspecialchars($row['buy_time']) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- ================= MOBILE ================= -->
<div class="mobile-only">
<?php foreach($numbers as $row):
    if ($row['status'] == '1') {
        $statusBadge = '<span class="status-success">Received</span>';
    } elseif ($row['status'] == '3') {
        $statusBadge = '<span class="status-cancelled">Cancelled</span>';
    } else {
        $statusBadge = '<span class="status-active">Active</span>';
    }
?>
  <div class="num-card">
    <div class="num-top">
      <span class="num-service"><?= htmlspecialchars($row['service_name']) ?></span>
      <?= $statusBadge ?>
    </div>

    <div class="num-row">
      <span class="num-label">Number</span>
      <span class="num-number">+<?= htmlspecialchars(ltrim($row['number'], '+')) ?></span>
    </div>

    <div class="num-row">
      <span class="num-label">Amount</span>
      <span class="num-amount">₦<?= number_format($row['service_price']) ?></span>
    </div>

    <div class="num-row">
      <span class="num-label">SMS</span>
      <span><?= $row['sms'] ? htmlspecialchars($row['sms']) : '—' ?></span>
    </div>

    <div class="num-row">
      <span class="num-label">Date</span>
      <span><?= htmlspecialchars($row['buy_time']) ?></span>
    </div>
  </div>
<?php endforeach; ?>
</div>

<?php endif; ?>
</div>

</div>
</div>
</div>

<?php include ('partial/scripts.php'); ?>
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdn.datatables.net/2.0.5/js/dataTables.min.js"></script>

<script>
$(document).ready(function(){
  if(window.innerWidth > 768){
    $('#myTable').DataTable({
      ordering:false,
      pageLength:10
    });
  }
});
</script>

<?php include ('partial/footer-end.php'); ?>
