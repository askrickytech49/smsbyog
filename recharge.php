<?php
session_start();
include 'include/config.php';
require __DIR__ . '/class/class.control.php';

/**
 * -----------------------------
 * AUTH & SESSION VALIDATION
 * -----------------------------
 */
if (empty($_SESSION['token'])) {
    session_destroy();

    if (isset($_COOKIE['remember_me'])) {
        setcookie('remember_me', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'domain'   => $_SERVER['HTTP_HOST'],
            'secure'   => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    redirect('logout');
}

/**
 * -----------------------------
 * CORE WALLET CONTEXT (REQUIRED)
 * -----------------------------
 */
 $cryptoRef = $_GET['crypto_ref'] ?? null;
$cryptoPending = isset($_GET['crypto_pending']) && $cryptoRef;

 
$wallet = new radiumsahil();
$verifyRef = $_GET['reference'] ?? null;
$verifySuccess = isset($_GET['success']) && $verifyRef;

$userdata     = $wallet->userdata();     // REQUIRED BY topbar.php
$userwallet   = $wallet->userwallet();   // REQUIRED BY topbar.php
$referwallet  = $wallet->refer_data();   // REQUIRED BY sidebar / referrals
$history  = $wallet->transaction_history();

$user_id = $userdata['id'];

$stmt = $conn->prepare("SELECT * FROM user_dynamic_va WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$virtualAccounts = [];

while ($row = $result->fetch_assoc()) {
    $slug = strtolower(trim($row['virtual_name'])); 
    $virtualAccounts[$slug] = $row;
}




if ($userdata === false) {
    unset($_SESSION['token']);
    session_destroy();

    if (isset($_COOKIE['remember_me'])) {
        unset($_COOKIE['remember_me']);
        setcookie('remember_me', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'domain'   => $_SERVER['HTTP_HOST'],
            'secure'   => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    redirect('login');
}

/**
 * -----------------------------
 * PAGE META
 * -----------------------------
 */
$page_title = "Fund Wallet - " . $site_data['web_name'];
// echo json_encode($userdata); exit;
?>


<?php include ('partial/header.php'); ?>

<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
<?php include ('partial/loader.php'); ?>

<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <!-- Page Header Start-->
    <?php include ('partial/topbar.php'); ?>
    <!-- Page Header Ends -->

    <!-- Page Body Start-->
    <div class="page-body-wrapper">
        <!-- Page Sidebar Start-->
        <?php include ('partial/sidebar.php'); ?>
        <!-- Page Sidebar Ends-->

        <div class="page-body">
            <br><br>


    
     <style>
/* ===============================
   TUTORIAL DROPDOWN – BOOTSTRAP ICONS
================================ */

.tutorial-dropdown-wrapper{
  max-width:420px;
  margin:0 auto 20px auto;
  position:relative;
}

/* DESKTOP FULL WIDTH */
@media (min-width: 992px){
  .tutorial-dropdown-wrapper{
    max-width:100%;
  }
}

/* TAB */
.tutorial-tab{
  background:#ffffff;
  border:1px solid #e10700;
  margin: 10px;
  border-radius:12px;
  padding:14px 18px;
  font-weight:700;
  font-size:14px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  cursor:pointer;
  transition:all 0.2s ease;
}

.tutorial-tab:hover{
  border-color:#e10700;
}

/* LEFT */
.tab-left{
  display:flex;
  align-items:center;
  gap:10px;
}

.tab-left i{
  font-size:16px;
  color:#e10700;
}

/* CARET */
.caret{
  font-size:14px;
  transition:transform 0.2s ease;
}

/* DROPDOWN */
.tutorial-dropdown{
  display:none;
  margin-top:10px;
  background:#ffffff;
  border:2px solid #d6dbe8;
  border-radius:12px;
  overflow:hidden;
  box-shadow:0 8px 20px rgba(0,0,0,0.08);
}

/* ITEMS */
.tutorial-dropdown a{
  display:flex;
  align-items:flex-start;
  gap:12px;
  padding:14px 18px;
  font-size:13.5px;
  font-weight:600;
  color:#111;
  text-decoration:none;
  border-bottom:1px solid #eef1f6;
}

.tutorial-dropdown a:last-child{
  border-bottom:none;
}

.tutorial-dropdown a:hover{
  background:#fff6ef;
  color:#e10700;
}

.tutorial-dropdown a i{
  font-size:16px;
  color:#6b7280;
  margin-top:2px;
}

/* TOGGLE */
#tutorialToggle{
  display:none;
}

#tutorialToggle:checked + .tutorial-tab .caret{
  transform:rotate(180deg);
}

#tutorialToggle:checked ~ .tutorial-dropdown{
  display:block;
}
</style>

<!-- <div class="tutorial-dropdown-wrapper">
  <input type="checkbox" id="tutorialToggle">

  <label for="tutorialToggle" class="tutorial-tab">
    <div class="tab-left">
      <i class="bi bi-play-circle"></i>
      <span>Tutorials & Guides</span>
    </div>
    <i class="bi bi-chevron-down caret"></i>
  </label>

  <div class="tutorial-dropdown">

    BUY NUMBER
    <a href="https://myogsms.com/tutorials/how-to-buy-number-on-authpadi.html">
      <i class="bi bi-phone"></i>
      <span>How to Buy a Number on AuthPadi</span>
    </a>

    FUND WITH NAIRA
    <a href="https://myogsms.com/tutorials/fund-with-naira.html">
      <i class="bi bi-credit-card"></i>
      <span>How to Fund Your AuthPadi Wallet With Naira (Korapay)</span>
    </a> -->
<!-- 
     FUND WITH CRYPTO
    <a href="https://myogsms.com/tutorials/fund-with-crypto.html">
      <i class="bi bi-currency-bitcoin"></i>
      <span>How to Fund Your AuthPadi Wallet in Crypto (Cryptomus)</span>
    </a> 

  </div>
</div>  -->






            <style>
/* ===============================
   AUTHPADI – FUND WALLET (SAAS)
================================ */

.authpadi-card {
  background: #ffffff;
  border: 1px solid #ececec;
  border-radius: 18px;
  box-shadow: 0 10px 28px rgba(0,0,0,.05);
}

/* ===============================
   GATEWAY SELECT
================================ */

.gateway-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 18px;
  border-radius: 14px;
  border: 1.5px solid #e6e6e6;
  cursor: pointer;
  transition: all .25s ease;
  margin-bottom: 14px;
  background: #fff;
}

.gateway-card:hover {
  border-color: #000;
  background: #f7f7f7;
}

.gateway-card.active {
  background: #000;
  border-color: #000;
  color: #fff;
}

.gateway-left {
  display: flex;
  align-items: center;
  gap: 14px;
}

.gateway-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: #000;
  display: flex;
  align-items: center;
  justify-content: center;
}

.gateway-icon img {
  width: 20px;
  filter: invert(1);
}

.gateway-card.active .gateway-icon {
  background: #fff;
}

.gateway-card.active .gateway-icon img {
  filter: invert(0);
}

.gateway-title {
  font-weight: 700;
  font-size: 14px;
}

.gateway-sub {
  font-size: 12px;
  color: #777;
}

.gateway-card.active .gateway-sub {
  color: #ccc;
}

.gateway-arrow {
  font-size: 18px;
  font-weight: 600;
}

/* ===============================
   FORM
================================ */

.authpadi-input {
  border-radius: 14px;
  padding: 15px 16px;
  font-size: 16px;
  border: 1.5px solid #e6e6e6;
}

.authpadi-input:focus {
  border-color: #000;
  box-shadow: none;
}

/* ===============================
   AUTHPADI CTA BUTTON
================================ */

.btn-authpadi {
  background: #000;
  color: #fff;
  border: none;
  border-radius: 16px;
  padding: 16px;
  font-size: 15px;
  font-weight: 700;
  letter-spacing: .3px;
  transition: all .25s ease;
}

.btn-authpadi:hover {
  background: #111;
  box-shadow: 0 14px 30px rgba(0,0,0,.25);
  transform: translateY(-2px);
}

.btn-authpadi:active {
  transform: scale(.98);
}

/* ===============================
   HEADINGS
================================ */

.authpadi-title {
  font-weight: 800;
  letter-spacing: .3px;
}
</style>

<!--xixapay box -->
   <div class="xixa-wrapper mb-4">
    <div class="xixa-card">
        <div class="xixa-header">
            <div>
                <h5>Instant Bank Transfer</h5>
                <p>Fund your MyOgSMS wallet using your dedicated bank account</p>
            </div>
            <div class="xixa-badge">₦</div>
        </div>

        <?php
        // Define all available providers expected by your platform configuration layout
        $providers = [
            'pocketfi'     => 'Virtual'
        ];
        ?>

        <div class="xixa-providers-container" style="display: flex; flex-direction: column; gap: 20px; padding: 15px;">
            <?php foreach($providers as $slug => $displayName): 
                // Reference whether the authenticated user has already mapped a token row for this provider
                $account = isset($virtualAccounts[$slug]) ? $virtualAccounts[$slug] : null; 
            ?>

                <div class="provider-block" style="border: 1px solid #eef2f5; padding: 15px; border-radius: 8px; background: #fafbfc;">
                    <div class="provider-title-bar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 2px solid #eef2f5; padding-bottom: 6px;">
                        <span style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: bold; color: #6c757d;">
                            <?php echo $displayName; ?>
                        </span>
                        
                        <?php if($account): ?>
                            <span class="badge" style="background: #e2f7ed; color: #1f9254; font-size: 0.75rem; padding: 4px 8px; border-radius: 4px; font-weight: bold;">Active</span>
                        <?php else: ?>
                            <span class="badge" style="background: #fff3cd; color: #856404; font-size: 0.75rem; padding: 4px 8px; border-radius: 4px; font-weight: bold;">Not Assigned</span>
                        <?php endif; ?>
                    </div>

                    <?php if($account): ?>
                        <div class="xixa-details" style="margin-top: 0;">
                            <div class="xixa-row">
                                <span class="xixasam">Bank Name</span>
                                <strong><?php echo $account['bank_name']; ?></strong>
                            </div>

                            <div class="xixa-row">
                                <span class="xixasam">Account Name</span>
                                <span class="acctnamesam"><?php echo $account['account_name']; ?></span>
                            </div>

                            <div class="xixa-row account-number">
                                <span class="xixasam">Account Number</span>
                                <div>
                                    <strong class="acctNum" id="acctNum_<?php echo $slug; ?>"><?php echo $account['account_number']; ?></strong>
                                </div>
                            </div>
                            
                            <button onclick="copyAcctText('acctNum_<?php echo $slug; ?>')" class="copy-btn" style="width: 100%; margin-top: 8px;">
                                Copy Account Number ✓
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="xixa-generate" style="padding: 10px 0; margin-top: 0; text-align: left;">
                            <p style="font-size: 0.85rem; color: #6c757d; margin-bottom: 10px;">
                                You don’t have a dedicated <?php echo $displayName; ?> account mapped yet. Generate one now to increase transfer channel reliability options.
                            </p>
                            <button onclick="triggerVaGeneration('<?php echo $slug; ?>')" class="generate-btn direct-gen-btn" style="width: 100%; padding: 8px; font-size: 0.85rem;">
                                Generate <?php echo $displayName; ?> Account
                            </button>
                        </div>
                    <?php endif; ?>
                </div>

            <?php endforeach; ?>
        </div>

        <div class="xixa-actions" style="padding: 0 15px 15px 15px;">
            <hr style="border-top: 1px solid #eef2f5; margin-top: 0;">
            <center>
                <p><small style="color: #8c98a5;">After Successful Payment, Wait for about 30 Secs - 1 Minute for the Transaction to be Confirmed by Our System Then Refresh</small></p>
            </center>
        </div>
    </div>
</div>

<style>
  .xixa-wrapper {
    width: 100%;
    max-width: 100%;
}

.acctnamesam{
    font-weight: 900;
    text-align:right;
}

.xixa-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 20px 60px rgba(0,0,0,0.05);
    border: 1px solid #e10700;
    transition: all 0.3s ease;
    margin: 10px;
}

.xixa-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 30px 70px rgba(0,0,0,0.08);
}

.xixa-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.xixa-header h5 {
    font-weight: 700;
    margin: 0;
}

.xixa-header p {
    font-size: 10px;
    color: #535353;
    margin: 0;
}

.xixa-badge {
    background: #e10700;
    color: white;
    padding: 6px 12px;
    border-radius: 50px;
    font-size: 12px;
    font-weight: 600;
}

.xixa-details {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.xixa-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e6e6e6;
    padding-bottom: 10px;
}

.account-number strong {
    font-size: 26px;
    letter-spacing: 4px;
    font-weight: 700;
    color: #111;
}


.xixasam{
        font-weight: 700;
    color: #e10700;
}


/* Buttons */
.generate-btn,
.refresh-btn,
.copy-btn {
    cursor: pointer;
    transition: all 0.25s ease;
}

.generate-btn {
    background: linear-gradient(135deg, #000, #222);
    color: white;
    padding: 14px 26px;
    border-radius: 12px;
    border: none;
    font-weight: 600;
    font-size: 14px;
}

.generate-btn:hover {
    background: linear-gradient(135deg, #e10700, #ff5500);
    transform: translateY(-2px);
}

.generate-btn:active {
    transform: scale(0.96);
}

.generate-btn.loading {
    opacity: 0.7;
    pointer-events: none;
}

.refresh-btn {
    background: #e10700;
    color: white;
    border: none;
    padding: 12px 22px;
    border-radius: 12px;
    font-weight: 600;
}

.refresh-btn:hover {
    background: #e86600;
}

.copy-btn {
    background: #111;
    color: white;
    border: none;
    padding: 8px 14px;
    border-radius: 8px;
    font-weight: 900;
}

.copy-btn:hover {
    background: #e10700;
}

/* Spinner */
.spinner {
    width: 14px;
    height: 14px;
    border: 2px solid #fff;
    border-top: 2px solid transparent;
    border-radius: 50%;
    display: inline-block;
    margin-right: 8px;
    animation: spin 0.7s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Timer */
.va-timer {
    margin-top: 10px;
    font-size: 13px;
    color: #e10700;
    font-weight: 600;
}
</style>

<div class="container-fluid mt-4">
  <div class="row g-4">

    <input type="hidden" id="tokens" value="<?php echo $_SESSION['token']; ?>">
    

    <!-- ================= LEFT: PAYMENT METHOD ================= -->



    <!--<div class="col-md-5">-->
    <!--  <div class="authpadi-card p-3" id="radiumsahil">-->
    <!--    <h5 class="authpadi-title mb-3">Fund Wallet</h5>-->
        

        <!-- Korapay -->
    <!--    <div class="gateway-card" onclick="selectGateway('korapay', this)">-->
    <!--      <div class="gateway-left">-->
    <!--        <div class="gateway-icon">-->
    <!--          <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT0FTaHpSG2dpooRDB5k8ma-eVDvUaSuVRheA&s">-->
    <!--        </div>-->
    <!--        <div>-->
    <!--          <div class="gateway-title">Naira Deposit (Korapay)</div>-->
    <!--          <div class="gateway-sub">₦ NGN • Card / Bank Transfer/ Bank</div>-->
    <!--        </div>-->
    <!--      </div>-->
    <!--      <div class="gateway-arrow">→</div>-->
    <!--    </div>-->

        <!-- Cryptomus -->
    <!--    <div class="gateway-card" onclick="selectGateway('cryptomus', this)">-->
    <!--      <div class="gateway-left">-->
    <!--        <div class="gateway-icon">-->
    <!--          <img src="https://cryptomus.com/favicon.ico">-->
    <!--        </div>-->
    <!--        <div>-->
    <!--          <div class="gateway-title">Crypto Deposit (Cryptomus)</div>-->
    <!--          <div class="gateway-sub">USDT • BTC • ETH & More</div>-->
    <!--        </div>-->
    <!--      </div>-->
    <!--      <div class="gateway-arrow">→</div>-->
    <!--    </div>-->
 
    <!--  </div>-->
    <!--</div>-->
    

    <!-- ================= RIGHT: AMOUNT ================= -->
    <!--<div class="col-md-7">-->
    <!--  <div class="authpadi-card p-3">-->
    <!--    <h5 class="authpadi-title mb-3">Enter Amount</h5>-->

    <!--    <form id="rechargeForm">-->
    <!--      <input type="hidden" id="gateway" name="gateway">-->

    <!--      <div class="mb-3">-->
    <!--        <label class="form-label">Amount</label>-->
    <!--        <input-->
    <!--          type="number"-->
    <!--          class="form-control authpadi-input"-->
    <!--          id="amount"-->
    <!--          placeholder="Enter amount"-->
    <!--          required-->
    <!--        >-->
    <!--        <small class="text-muted" id="amountHint">-->
    <!--          Choose a payment method to continue-->
    <!--        </small>-->
    <!--      </div>-->
    
    
          <!--XIXAPAY -->
<!--          <div id="xixapayBox" style="display:none;" class="authpadi-card p-3 mt-3">-->
<!--  <h6>Transfer to this account</h6>-->

<!--  <p><strong>Bank:</strong> <span id="xBank"></span></p>-->
<!--  <p><strong>Account Name:</strong> <span id="xName"></span></p>-->
<!--  <p><strong>Account Number:</strong> <span id="xNumber"></span></p>-->

<!--  <p style="color:#dc3545; font-size:13px; margin-top:10px;">-->
<!--    ⚠ This is a temporary bank account.-->
<!--    Generate a new account for every transaction.-->
<!--    Do not reuse this account.-->
<!--  </p>-->

<!--  <p>-->
<!--  Expires in: <strong id="xixaTimer">10:00</strong>-->
<!--</p>-->
<!--</div>-->
<!--          <button type="submit" class="btn-authpadi w-100">-->
<!--            Proceed to Payment →-->
<!--          </button>-->
<!--        </form>-->
<!--      </div>-->
<!--    </div>-->

<!--  </div>-->
<!--</div>-->


<script>
// function selectGateway(gateway, el) {
//   document.getElementById('gateway').value = gateway;

//   document.getElementById('amountHint').innerText =
//     gateway === 'korapay'
//       ? 'You will be charged in Nigerian Naira (NGN)'
//       : 'Crypto payments are processed automatically';

//   document.querySelectorAll('.gateway-card')
//     .forEach(card => card.classList.remove('active'));

//   el.classList.add('active');
// }


</script>

<?php if ($verifySuccess): ?>
<div id="verifyBox" style="
    padding:15px;
    background:#f97218;
    border:1px solid #ffeeba;
    border-radius:6px;
    margin-bottom:15px;
">
    <strong>Verifying payment…</strong><br>
    Please wait while we confirm your transaction, do not reload this tab, Your Payment would be Funded Automatically Once Confirmed..
</div>
<?php endif; ?>
<?php if ($cryptoPending): ?>
<div id="cryptoVerifyBox" style="
    padding:16px;
    background:#f97218;
    border:1px solid #ffeeba;
    border-radius:6px;
    margin-bottom:15px;
">
    <strong>Waiting for crypto payment…</strong><br>
    Please complete payment in the opened tab.  
    We’ll verify it automatically.
</div>
<?php endif; ?>


                <?php
// ================= PAGINATION LOGIC =================

// Records per page
$limit = 20;

// Current page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);

// Total records
$totalRecords = !empty($history) ? count($history) : 0;

// Total pages
$totalPages = (int) ceil($totalRecords / $limit);

// Offset
$offset = ($page - 1) * $limit;

// Paginated records
$paginatedHistory = array_slice($history, $offset, $limit);
?>

<!-- ================= PAYMENT HISTORY ================= -->
<div class="row mt-4">
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-body">

        <h5 class="fw-bold mb-3">Payment History</h5>

        <!-- ===== DESKTOP TABLE ===== -->
        <div class="table-responsive desktop-history">
          <table class="table table-striped align-middle">
            <thead>
              <tr>
                <th>Date</th>
                <th>Amount</th>
                <th>Type</th>
                <th>Trans ID</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>

              <?php if (!empty($paginatedHistory)): foreach ($paginatedHistory as $row): ?>

                <?php
                  if ($row['status'] == 1) {
                    $statusText  = 'Success';
                    $statusClass = 'bg-success';
                  } elseif ($row['status'] == 0) {
                    $statusText  = 'Pending';
                    $statusClass = 'bg-warning';
                  } elseif ($row['status'] == -1) {
                    $statusText  = 'Failed';
                    $statusClass = 'bg-danger';
                  } else {
                    $statusText  = 'Unknown';
                    $statusClass = 'bg-secondary';
                  }
                ?>

                <tr>
                  <td><?= htmlspecialchars($row['date']) ?></td>
                  <td>₦<?= number_format($row['amount'], 2) ?></td>
                  <td><?= htmlspecialchars($row['type']) ?></td>
                  <td><?= htmlspecialchars($row['txn_id']) ?></td>
                  <td>
                    <span class="badge <?= $statusClass ?>">
                      <?= $statusText ?>
                    </span>
                  </td>
                </tr>

              <?php endforeach; else: ?>
                <tr>
                  <td colspan="5" class="text-center text-muted">No payment history</td>
                </tr>
              <?php endif; ?>

            </tbody>
          </table>
        </div>

        <!-- ===== MOBILE CARD VIEW ===== -->
        <div class="mobile-history">
          <?php if (!empty($paginatedHistory)): foreach ($paginatedHistory as $row): ?>

            <?php
              if ($row['status'] == 1) {
                $statusText  = 'Success';
                $statusClass = 'bg-success';
              } elseif ($row['status'] == 0) {
                $statusText  = 'Pending';
                $statusClass = 'bg-warning';
              } elseif ($row['status'] == -1) {
                $statusText  = 'Failed';
                $statusClass = 'bg-danger';
              } else {
                $statusText  = 'Unknown';
                $statusClass = 'bg-secondary';
              }
            ?>

            <div class="txn-card">
              <div class="txn-top">
                <span class="txn-type"><?= htmlspecialchars($row['type']) ?></span>
                <span class="badge <?= $statusClass ?>">
                  <?= $statusText ?>
                </span>
              </div>

              <div class="txn-row">
                <span>Amount</span>
                <strong class="amount">₦<?= number_format($row['amount'], 2) ?></strong>
              </div>

              <div class="txn-row">
                <span>Date</span>
                <strong><?= htmlspecialchars($row['date']) ?></strong>
              </div>

              <div class="txn-row ref">
                <span>Reference</span>
                <strong><?= htmlspecialchars($row['txn_id']) ?></strong>
              </div>
            </div>

          <?php endforeach; else: ?>
            <p class="text-center text-muted">No payment history</p>
          <?php endif; ?>
        </div>

        <!-- ===== PAGINATION ===== -->
        <?php if ($totalPages > 1): ?>
        <nav class="mt-3">
          <ul class="pagination justify-content-center">

            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
              <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
            </li>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
              <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
              </li>
            <?php endfor; ?>

            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
              <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
            </li>

          </ul>
        </nav>
        <?php endif; ?>

      </div>
    </div>
  </div>
</div>




        <?php include ('partial/footer.php'); ?>
    </div>
</div>

<?php include ('partial/scripts.php'); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {

    function selectGateway(gateway, el) {
        document.getElementById('gateway').value = gateway;

        let hint = 'Choose a payment method to continue';

        if (gateway === 'korapay') {
            hint = 'Minimum ₦100 • Bank Transfer / Card Payment';
        } else if (gateway === 'cryptomus') {
            hint = 'Minimum $1 • Crypto payment';
        }

        document.getElementById('amountHint').innerText = hint;

        document.querySelectorAll('.gateway-card')
            .forEach(card => card.classList.remove('active'));

        el.classList.add('active');
    }

    // Make it global for inline onclick
    window.selectGateway = selectGateway;

    const form = document.getElementById('rechargeForm');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const gateway = document.getElementById('gateway').value;
        const amount  = document.getElementById('amount').value;

        // if (!gateway) {
        //     alert("Please select a payment method");
        //     return;
        // }

        if (!amount || amount <= 0) {
            alert("Enter a valid amount");
            return;
        }

        // SQUAD
       


        // KORAPAY
        if (gateway === 'korapay') {
            const f = document.createElement('form');
            f.method = 'POST';
            f.action = '/payments/init_korapay.php';

            const amt = document.createElement('input');
            amt.type = 'hidden';
            amt.name = 'amount';
            amt.value = amount;

            f.appendChild(amt);
            document.body.appendChild(f);
            f.submit();
            return;
        }

        // CRYPTOMUS
        fetch('/payments/init_cryptomus.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'amount=' + encodeURIComponent(amount)
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'ok') {
                window.open(data.payment_url, '_blank');
                window.location.href =
                    '/recharge?crypto_pending=1&crypto_ref=' + data.reference;
            } else {
                alert('Unable to start crypto payment');
            }
        })
        .catch(() => {
            alert('Network error');
        });
    });

});
</script>
<!--//xixapay function -->
<script>
function copyAcct() {
    const text = document.getElementById("acctNum").innerText;

    navigator.clipboard.writeText(text);

    const btn = document.querySelector(".copy-btn");
    btn.innerText = "Account Number Copied ✓";

    setTimeout(()=>{
        btn.innerText = "Copy Account Number ✓";
    }, 5000);
}
</script>

<script>
function refreshStatus(){

    fetch("payments/check_xixapay_status.php")
    .then(res => {
        if(!res.success) throw new Error("Server error");
        return res.json();
    })
    .then(data => {
        if(data.success){
            document.querySelector(".wallet-pill").innerText =
                "Balance: ₦" + data.balance;
        }
    })
    .catch(err => {
        console.log("Refresh failed:", err);
    });

}
</script>


<script>
    document.addEventListener("DOMContentLoaded", function(){

    const generateBtn = document.getElementById("generateVA");
    if (!generateBtn) return;

    generateBtn.addEventListener("click", function(){

        // const amount = document.getElementById("amount").value;
        const amount = "100";

        if (!amount || amount < 100) {
            alert("Kindly Input a random amount above 100 Naira in the amount section then click the Generate Account Button to generate your account successfully");
            return;
        }

        generateBtn.classList.add("loading");
        generateBtn.innerHTML = '<span class="spinner"></span> Generating...';
        generateBtn.disabled = true;

        fetch('payments/generate_pay.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'amount=' + encodeURIComponent(amount)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success === false) {
                // This will now show the actual message from PHP
                alert(data.message + (data.error_detail ? ": " + data.error_detail : ""));
                
                generateBtn.classList.remove("loading");
                generateBtn.innerText = "Generate Bank Account";
                generateBtn.disabled = false;
                return;
            }
            window.location.reload();
        })
        .catch(() => {
            alert("Failed to generate bank");
            generateBtn.classList.remove("loading");
            generateBtn.innerText = "Try Again";
            generateBtn.disabled = false;
        });

    });

});
</script>
<script>
    function copyAcctText(elementId) {
        var textToCopy = document.getElementById(elementId).innerText;
        navigator.clipboard.writeText(textToCopy).then(function() {
            alert("Account number copied successfully: " + textToCopy);
        }).catch(function(err) {
            console.error('Could not copy string text structural selector lines: ', err);
        });
    }

    function triggerVaGeneration(providerSlug) {
    // 🟢 Target the active buttons dynamically based on class selectors
    const totalButtons = $('.direct-gen-btn');
    totalButtons.attr('disabled', true);

    // Track whichever button was clicked to display custom feedback text messages
    const currentBtn = window.event && window.event.target ? window.event.target : null;
    if (currentBtn) {
        currentBtn.classList.add("loading");
        currentBtn.innerText = "Generating Account...";
    }
    
    fetch('payments/generate_pay.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'provider=' + encodeURIComponent(providerSlug)
    })
    .then(res => {
        if (!res.ok) {
            throw new Error("HTTP structural processing server error status " + res.status);
        }
        return res.json();
    })
    .then(data => {
        if (data.success === false) {
            alert(data.message + (data.error_detail ? ": " + data.error_detail : "") + (data.debug ? "\n\nDebug: " + JSON.stringify(data.debug) : ""));
            resetVaGenerationButtons(currentBtn, totalButtons, "Generate Bank Account");
            return;
        }
        window.location.reload();
    })
    .catch((err) => {
        console.error("VA Processing Error:", err);
        alert("Failed to connect to server. Check the browser console (F12) for details.");
        resetVaGenerationButtons(currentBtn, totalButtons, "Try Again");
    });
}

// 🟢 Helper validation handler function to safely reset UI button state parameters 
function resetVaGenerationButtons(activeBtn, allButtons, targetText) {
    allButtons.attr('disabled', false);
    if (activeBtn) {
        activeBtn.classList.remove("loading");
        activeBtn.innerText = targetText;
    }
}
</script>
<!--<script>-->
<!--    document.addEventListener("DOMContentLoaded", function(){-->

<!--    if(window.existingVA){-->
<!--        document.getElementById("accountDetails").style.display = "block";-->
<!--        document.getElementById("generateBox").style.display = "none";-->

<!--        document.getElementById("bankName").innerText = existingVA.bankName;-->
<!--        document.getElementById("accountName").innerText = existingVA.accountName;-->
<!--        document.getElementById("accountNumber").innerText = existingVA.accountNumber;-->
<!--    } else {-->
<!--        document.getElementById("accountDetails").style.display = "none";-->
<!--    }-->

<!--});-->
<!--</script>-->
<!--<script>-->
<!--function startTimer(duration) {-->

<!--    let timer = duration;-->
<!--    const display = document.getElementById("xixaTimer");-->

<!--    if (!display) return;-->

<!--    const interval = setInterval(function () {-->

<!--        const minutes = Math.floor(timer / 60);-->
<!--        const seconds = timer % 60;-->

<!--        display.textContent =-->
<!--            minutes + ":" + (seconds < 10 ? "0" + seconds : seconds);-->

<!--        if (--timer < 0) {-->
<!--            clearInterval(interval);-->
<!--            display.textContent = "Expired";-->
<!--        }-->

<!--    }, 1000);-->
<!--}-->
<!--</script>-->


<style>
.fund-card {
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 14px 18px;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    transition: all .25s ease;
    background: #fff;
}
.fund-card:hover {
    border-color: #0d6efd;
    box-shadow: 0 8px 25px rgba(0,0,0,.06);
    transform: translateY(-2px);
}
.fund-left {
    display: flex;
    align-items: center;
    gap: 14px;
}
.fund-icon {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.fund-icon img {
    width: 22px;
}
.fund-text .title {
    font-weight: 700;
}
.fund-text .sub {
    font-size: 12px;
    color: #6b7280;
}
.fund-arrow {
    font-size: 20px;
    color: #9ca3af;
}
/* ===== MOBILE / DESKTOP TOGGLE ===== */
.desktop-history { display: block; }
.mobile-history { display: none; }

@media (max-width: 767px) {
  .desktop-history { display: none; }
  .mobile-history { display: block; }
}

/* ===== MOBILE TRANSACTION CARDS ===== */
.txn-card{
  background: #fff;
  border-radius: 14px;
  padding: 14px;
  margin-bottom: 12px;
  border: 1px solid #eef1f6;
  box-shadow: 0 4px 14px rgba(0,0,0,.04);
}

.txn-top{
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.txn-type{
  font-size: 13px;
  font-weight: 600;
  background: #f1f3f6;
  padding: 4px 10px;
  border-radius: 20px;
}

.txn-row{
  display: flex;
  justify-content: space-between;
  font-size: 13px;
  margin-bottom: 6px;
}

.txn-row span{
  color: #6b7280;
}

.txn-row .amount{
  color: #e10700;
  font-weight: 700;
}

.txn-row.ref{
  font-size: 12px;
  color: #555;
  word-break: break-all;
}

</style>
<?php if ($verifySuccess): ?>
<script>
(function () {
    const ref = "<?= htmlspecialchars($verifyRef) ?>";
    const box = document.getElementById('verifyBox');

    let attempts = 0;
    const maxAttempts = 10;

    const interval = setInterval(() => {
        attempts++;

        fetch(`/payments/verify_korapay.php?reference=${encodeURIComponent(ref)}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'completed') {
                    box.style.background = '##04dc08';
                    box.style.borderColor = '#c3e6cb';
                    box.innerHTML = "<strong>Payment successful!</strong><br>WYour Wallet Have Been Credited, The page would be refreshed automatically, Please do not refresh by yourself, it is automated.";

                    clearInterval(interval);

                    // reload after short delay
                    setTimeout(() => {     window.location.href = "/recharge"; }, 1500);
                }
            })
            .catch(() => {});

        if (attempts >= maxAttempts) {
            clearInterval(interval);
            box.innerHTML = "<strong>Payment received.</strong><br>Still verifying, please refresh later.";
        }
    }, 3000);
})();
</script>
<?php endif; ?>
<?php if ($cryptoPending): ?>
<script>
(function () {
    const ref = "<?= htmlspecialchars($cryptoRef) ?>";
    const box = document.getElementById('cryptoVerifyBox');

    let attempts = 0;
    const maxAttempts = 20; // ~80 seconds
    let done = false;

    const timer = setInterval(() => {
        if (done) return;
        attempts++;

        fetch(`/payments/verify_cryptomus.php?reference=${encodeURIComponent(ref)}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'completed') {
                    done = true;
                    clearInterval(timer);

                    box.style.background = '##04dc08';
                    box.style.borderColor = '#c3e6cb';
                    box.innerHTML = "<strong>Payment successful!</strong><br>WYour Wallet Have Been Credited, The page would be refreshed automatically, Please do not refresh by yourself, it is automated.";

                    setTimeout(() => {
                        window.location.href = "/recharge";
                    }, 1500);
                }
            })
            .catch(() => {});

        if (attempts >= maxAttempts && !done) {
            clearInterval(timer);
            box.innerHTML =
                "<strong>Payment received.</strong><br>" +
                "Blockchain confirmation may take a few minutes. You can safely return later.";
        }
    }, 4000);
})();
</script>


<?php endif; ?>

<?php include ('partial/footer-end.php'); ?>
