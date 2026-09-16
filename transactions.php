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
  padding:24px;
  box-shadow:0 12px 32px rgba(15,23,42,.07);
}

.transaction-page .auth-card {
  position: relative;
  overflow: hidden;
}

.transaction-page .auth-card::before {
  content: "";
  display: block;
  height: 4px;
  margin: -24px -24px 22px;
  background: linear-gradient(90deg, #e10700 0%, #ff5a52 55%, #ffd3cf 100%);
}

.auth-header{
  display:flex;
  align-items:flex-start;
  justify-content:space-between;
  gap:16px;
  margin-bottom:18px;
}

.auth-header h4 {
  margin: 0 0 4px;
  color: #172033;
  font-size: 22px;
  font-weight: 750;
}

.badge-auth {
  color: var(--auth-orange);
  background: #fff1ed;
  border: 1px solid #ffd7d1;
  border-radius: 999px;
  padding: 6px 10px;
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

@media(max-width:575px){
  .transaction-page { padding:16px 12px 36px; }
  .auth-card { padding:16px; border-radius:14px; }
  .transaction-page .auth-card::before { margin:-16px -16px 18px; }
  .auth-header h4 { font-size:19px; }
  .badge-auth { font-size:10px; white-space:nowrap; }
}

/* ===== TABLE ===== */
.dataTables_wrapper table{
  border-collapse:separate!important;
  border-spacing:0 8px!important;
  margin-top:4px!important;
}

.dataTables_wrapper tbody tr{
  background:#fff;
  border:1px solid var(--border);
  border-radius:10px;
  box-shadow:0 3px 10px rgba(15,23,42,.03);
}

.transaction-page .dataTables_wrapper tbody tr:hover {
  background:#fff8f5;
  transform:translateY(-1px);
}

.dataTables_wrapper thead th {
  border-bottom:1px solid #e5e9ef!important;
  color:#64748b!important;
  font-size:11px!important;
  font-weight:750!important;
  letter-spacing:.05em;
  text-transform:uppercase;
  padding:11px 14px!important;
}

.dataTables_wrapper tbody td{
  padding:14px!important;
  border:none!important;
  color:#263247;
  font-size:13px;
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

.transaction-page {
  background:#f5f7fa;
  min-height:calc(100vh - 72px);
  padding:28px 24px 56px;
  overflow-x:hidden;
}

.transaction-page > .container-fluid {
  max-width:1080px;
  margin:0 auto;
  padding:0;
}

.transaction-page .dataTables_wrapper .dataTables_length,
.transaction-page .dataTables_wrapper .dataTables_filter {
  color:#64748b;
  font-size:12px;
  padding:6px 0 10px;
}

.transaction-page .dataTables_wrapper .dataTables_filter input,
.transaction-page .dataTables_wrapper .dataTables_length select {
  border:1px solid #dfe4eb!important;
  border-radius:7px;
  background:#fff;
  color:#172033;
  padding:7px 9px;
}

.transaction-page .dataTables_wrapper .dataTables_filter input:focus {
  border-color:#e10700!important;
  box-shadow:0 0 0 3px rgba(225,7,0,.1);
  outline:none;
}

.transaction-page .dataTables_wrapper .dataTables_info {
  color:#64748b;
  font-size:12px;
  padding:14px 0 0;
}

.transaction-page .dataTables_wrapper .dataTables_paginate {
  padding:10px 0 0;
}

.transaction-page .dataTables_wrapper .dataTables_paginate .paginate_button.current {
  border:1px solid #e10700!important;
  background:#e10700!important;
  color:#fff!important;
}

.transaction-page .dt-container {
  color:#64748b;
  font-size:12px;
}

.transaction-page .dt-container .dt-length,
.transaction-page .dt-container .dt-search {
  padding:6px 0 10px;
}

.transaction-page .dt-container .dt-search input,
.transaction-page .dt-container .dt-length select {
  border:1px solid #dfe4eb!important;
  border-radius:7px;
  padding:7px 9px;
}

.transaction-page .dt-container .dt-paging .dt-paging-button.current {
  border:1px solid #e10700!important;
  background:#e10700!important;
  color:#fff!important;
}

/* =========================
   MOBILE CARDS (REAL FIX)
========================= */
.tx-card{
  background:#fff;
  border:1px solid var(--border);
  border-radius:14px;
  padding:16px;
  margin-bottom:14px;
  box-shadow:0 5px 16px rgba(15,23,42,.05);
  position:relative;
  overflow:hidden;
}

.tx-card::before {
  content:"";
  position:absolute;
  left:0;
  top:0;
  bottom:0;
  width:3px;
  background:#e10700;
}

.tx-top{
  display:flex;
  justify-content:space-between;
  margin-bottom:6px;
}

.tx-status {
  border:1px solid transparent;
}

.tx-type{
  background:#fff1ed;
  color:#c80700;
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
  padding-top:8px;
  border-top:1px solid #f0f2f5;
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

<div class="page-body transaction-page">
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
