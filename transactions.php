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

<style>
:root {
  --auth-orange:#e10700;
  --border:#e6e9f0;
  --muted:#6c757d;
}

.auth-card {
  background:#fff;
  border:1px solid var(--border);
  border-radius:16px;
  padding:24px;
  box-shadow:0 12px 32px rgba(15,23,42,.07);
}

.transaction-page .auth-card {
  position: relative;
  overflow: hidden;
  margin-top: 40px;
}

.transaction-page .auth-card::before {
  display: none;
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

.transaction-page {
  background:#f5f7fa;
  min-height:calc(100vh - 72px);
  padding:180px 24px 56px;
  overflow-x:hidden;
}

.transaction-page > .container-fluid {
  max-width:1080px;
  margin:0 auto;
  padding:0;
}

.desktop-only{ display:block; }
.mobile-only{ display:none; }

@media(max-width:768px){
  .desktop-only{ display:none; }
  .mobile-only{ display:block; }
}

@media(max-width:575px){
  .transaction-page { padding:16px 12px 36px; }
  .auth-card {
    padding:16px;
    border-radius:14px;
    margin:0 8px;
  }
  .transaction-page .auth-card::before { margin:-16px -16px 18px; }
  .auth-header h4 { font-size:19px; }
  .badge-auth { font-size:10px; white-space:nowrap; }
}

.payment-history-table {
  table-layout: fixed;
  border-collapse: collapse !important;
  margin-top: 4px !important;
}

.payment-history-table thead th {
  border-bottom: 1px solid #edf0f3 !important;
  color: #64748b !important;
  font-size: 9px !important;
  font-weight: 800 !important;
  letter-spacing: .04em;
  padding: 8px 10px !important;
  text-transform: uppercase;
}

.payment-history-table tbody tr {
  background: #fff;
  border-bottom: 1px solid #edf0f3;
  box-shadow: none;
}

.transaction-page .payment-history-table tbody tr:hover {
  background: #fff8f5;
}

.payment-history-table tbody td {
  border: 0 !important;
  color: #273247;
  font-size: 11px;
  padding: 12px 10px !important;
  vertical-align: middle;
}

.payment-history-table tbody td:nth-child(2) {
  font-weight: 700;
}

.payment-history-table tbody td:nth-child(4),
.payment-history-table tbody td:nth-child(5) {
  color: #64748b;
  overflow-wrap: anywhere;
}

.payment-history-table tbody td:nth-child(6) {
  color: #08a879;
  font-weight: 800;
  white-space: nowrap;
}

.payment-history-table th:first-child,
.payment-history-table td:first-child { width: 38px; color: #94a3b8 !important; }
.payment-history-table th:nth-child(2) { width: 21%; }
.payment-history-table th:nth-child(3) { width: 13%; }
.payment-history-table th:nth-child(4) { width: 17%; }
.payment-history-table th:nth-child(5) { width: 24%; }
.payment-history-table th:nth-child(6) { width: 14%; }
.payment-history-table th:nth-child(7) { width: 18%; }

.amount {
  font-weight:800;
  color:var(--auth-orange);
}

.badge-success{
  background:#e9f8ef;
  color:#16834a;
}

.badge-danger{
  background:#fdecea;
  color:#dc3545;
}

.tx-card {
  background:#fff;
  border:1px solid #edf0f3;
  border-radius:12px;
  padding:12px 14px;
  margin-bottom:12px;
  box-shadow:0 4px 12px rgba(15,23,42,.04);
  display:flex;
  justify-content:space-between;
  align-items:center;
  gap:12px;
}

.tx-top {
  display:flex;
  justify-content:space-between;
  margin-bottom:6px;
}

.tx-type{
  background:#fff1ed;
  color:#c80700;
  padding:6px 10px;
  border-radius:8px;
  font-weight:600;
  font-size:13px;
}

.tx-status {
  font-size:12px;
  font-weight:700;
  padding:4px 10px;
  border-radius:5px;
}

.tx-row {
  display:flex;
  justify-content:space-between;
  font-size:12px;
  margin-top:6px;
  word-break: break-all;
  padding-top:8px;
  border-top:1px solid #f0f2f5;
}

.tx-main {
  display:flex;
  flex-direction:column;
  gap:6px;
  flex:1;
  min-width:0;
}

.tx-title {
  font-size:14px;
  font-weight:700;
  color:#1f2937;
}

.tx-status-text {
  font-size:11px;
  font-weight:700;
  color:#16834a;
  letter-spacing:.01em;
}

.tx-status-text.success { color:#16834a; }
.tx-status-text.failed { color:#c80700; }

.tx-meta {
  display:flex;
  flex-direction:column;
  align-items:flex-end;
  gap:4px;
  white-space:nowrap;
}

.tx-amount {
  font-size:14px;
  font-weight:800;
  color:#08a879;
}

.tx-amount.positive { color:#08a879; }
.tx-amount.negative { color:#c80700; }

.tx-date {
  font-size:10px;
  color:#8b95a7;
}

.tx-label{ color:var(--muted); }

.text-muteds {
  font-size:12px;
  color:#4a4a4a !important;
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

<div class="desktop-only table-responsive">
  <table class="table align-middle payment-history-table">
    <thead>
      <tr>
        <th>#</th>
        <th>Type</th>
        <th>Status</th>
        <th>Method</th>
        <th>Reference</th>
        <th>Amount</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
    <?php $i=0; foreach($transactions as $tx): $i++; ?>
      <tr>
        <td><?= $i ?></td>
        <td><?= htmlspecialchars($tx['type']) ?></td>
        <td>
          <span class="badge rounded-pill <?= $tx['status']==1?'badge-success':'badge-danger' ?>">
            <?= $tx['status']==1?'Success':'Failed' ?>
          </span>
        </td>
        <td>Wallet funding</td>
        <td><?= htmlspecialchars($tx['txn_id']) ?></td>
        <td>₦<?= number_format($tx['amount'], 2) ?></td>
        <td><?= htmlspecialchars(date('j M Y', strtotime($tx['date']))) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="mobile-only">
<?php foreach($transactions as $tx): ?>
  <div class="tx-card">
    <div class="tx-main">
      <strong class="tx-title"><?= htmlspecialchars($tx['type']) ?></strong>
      <span class="tx-status-text <?= $tx['status']==1 ? 'success' : 'failed' ?>">
        <?= $tx['status']==1?'Success':'Failed' ?>
      </span>
    </div>
    <div class="tx-meta">
      <strong class="tx-amount <?= $tx['status']==1 ? 'positive' : 'negative' ?>">₦<?= number_format($tx['amount'], 2) ?></strong>
      <span class="tx-date"><?= htmlspecialchars(date('j M Y', strtotime($tx['date']))) ?></span>
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
<?php include ('partial/footer-end.php'); ?>
