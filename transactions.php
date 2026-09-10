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

$transactions = $wallet->transaction_history();
$wallet->closeConnection();

$page_title = "Transaction History - ".$site_data['web_name'];
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
   DESKTOP TABLE
========================= */
.desktop-only{
  display:block;
}

.mobile-only{
  display:none;
}

@media(max-width:768px){
  .desktop-only{display:none}
  .mobile-only{display:block}
}

/* ===== TABLE ===== */
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

.amount{
  font-weight:800;
  color:var(--auth-orange);
}

.badge-success{
  background:#e8f7ef;
  color:#198754;
}

.badge-danger{
  background:#fdecea;
  color:#dc3545;
}

/* =========================
   MOBILE CARDS (REAL FIX)
========================= */
.tx-card{
  background:#fff;
  border:1px solid var(--border);
  border-radius:14px;
  padding:14px 16px;
  margin-bottom:14px;
  box-shadow:0 6px 20px rgba(0,0,0,.05);
}

.tx-top{
  display:flex;
  justify-content:space-between;
  margin-bottom:6px;
}

.tx-type{
  background:#f1f3f9;
  padding:6px 10px;
  border-radius:8px;
  font-weight:600;
  font-size:13px;
}

.tx-status{
  font-size:12px;
  font-weight:700;
  padding:4px 10px;
  border-radius:5px;
}

.tx-row{
  display:flex;
  justify-content:space-between;
  font-size:12px;
  margin-top:6px;
  word-break: break-all;
}

.text-muteds{
    font-size: 12px;
    color: #4a4a4a !important;
}

.tx-label{
  color:var(--muted);
}

.tx-amount{
  font-weight:800;
  color:var(--auth-orange);
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
      <h4>Transaction History</h4>
      <p class="text-muteds">All wallet recharges and payment activity</p>
    </div>
    <span class="badge-auth"><?= count($transactions); ?> Records</span>
  </div>

<?php if(!$transactions): ?>
  <p class="text-center text-muted">No transactions yet.</p>

<?php else: ?>

<!-- ================= DESKTOP ================= -->
<div class="desktop-only">
  <table id="myTable" class="display w-100">
    <thead>
      <tr>
        <th>#</th>
        <th>Type</th>
        <th>Amount</th>
        <th>Date</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
    <?php $i=0; foreach($transactions as $tx): $i++; ?>
      <tr>
        <td><?= $i ?></td>
        <td><?= htmlspecialchars($tx['type']) ?></td>
        <td class="amount">₦ <?= number_format($tx['amount']) ?></td>
        <td><?= htmlspecialchars($tx['date']) ?></td>
        <td>
          <span class="badge rounded-pill <?= $tx['status']==1?'badge-success':'badge-danger' ?>">
            <?= $tx['status']==1?'Success':'Failed' ?>
          </span>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- ================= MOBILE ================= -->
<div class="mobile-only">
<?php foreach($transactions as $tx): ?>
  <div class="tx-card">
    <div class="tx-top">
      <span class="tx-type"><?= htmlspecialchars($tx['type']) ?></span>
      <span class="tx-status <?= $tx['status']==1?'badge-success':'badge-danger' ?>">
        <?= $tx['status']==1?'Success':'Failed' ?>
      </span>
    </div>

    <div class="tx-row">
      <span class="tx-label">Amount</span>
      <span class="tx-amount">₦ <?= number_format($tx['amount']) ?></span>
    </div>

    <div class="tx-row">
      <span class="tx-label">Date</span>
      <span><?= htmlspecialchars($tx['date']) ?></span>
    </div>
    <div class="tx-row">
      <span class="tx-label">Trans ID </span>
      <span><?= htmlspecialchars($tx['txn_id']) ?></span>
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
