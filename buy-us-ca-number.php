<?php
session_start();
include 'include/config.php';
include __DIR__ . '/include/mode_check.php';
include __DIR__ . '/include/api_status.php';
require __DIR__ . '/class/class.control.php';

if (!isset($_SESSION['token'])) {
    if (isset($_COOKIE['remember_me'])) {
        $_ck = mysqli_real_escape_string($conn, $_COOKIE['remember_me']);
        $_cv = mysqli_query($conn, "SELECT lt.user_id FROM login_token lt JOIN user_data u ON lt.user_id=u.id WHERE lt.token='$_ck' AND lt.status='1' AND u.status='1' LIMIT 1");
        if ($_cv && mysqli_num_rows($_cv) === 1) {
            $_SESSION['token'] = $_COOKIE['remember_me'];
        } else {
            session_destroy();
            setcookie('remember_me', '', ['expires' => time()-3600, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
            redirect('login');
        }
    } else {
        session_destroy();
        redirect('login');
    }
}

$wallet   = new radiumsahil();
$userdata = $wallet->userdata();

if ($userdata === false) {
    unset($_SESSION['token']);
    session_destroy();
    setcookie('remember_me', '', ['expires' => time() - 3600, 'path' => '/', 'httponly' => true]);
    redirect('login');
}

$userwallet = $wallet->userwallet();
$wallet->closeConnection();
// Check if this API is active — redirect if disabled
$_api_st = get_api_status($conn);
if (!$_api_st['usaca']) { redirect('buy-number'); }


$page_title = "Buy USA + Canada Numbers — " . $site_data['web_name'];
?>
<?php include 'partial/header.php'; ?>
<link rel="stylesheet" href="css/buy-flow.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css">
<?php include 'partial/loader.php'; ?>

<div class="page-wrapper compact-wrapper" id="pageWrapper">
  <?php include 'partial/topbar.php'; ?>
  <div class="page-body-wrapper">
    <?php include 'partial/sidebar.php'; ?>
    <div class="page-body">
      <div class="container-fluid py-4">
        <div class="row justify-content-center">
          <div class="col-xl-7 col-lg-8 col-md-10 col-12">

            <!-- SERVER SWITCHER — dynamic -->
            <div class="server-switcher">
              <?php $_as = get_api_status($conn); ?>
              <?php if($_as['server1']): ?><a href="buy-number-1" class="server-switch-btn"><i class="bi bi-flag-fill"></i> Server 1</a><?php endif; ?>
              <?php if($_as['server2']): ?><a href="buy-number-server2" class="server-switch-btn"><i class="bi bi-globe"></i> Server 2</a><?php endif; ?>
              <?php if($_as['usa']): ?><a href="buy-usa-number" class="server-switch-btn"><i class="bi bi-flag"></i> USA Only</a><?php endif; ?>
              <?php if($_as['usaca']): ?><span class="server-switch-btn current"><i class="bi bi-globe2"></i> USA + Canada</span><?php endif; ?>
            </div>

            <!-- MAIN CARD -->
            <div class="card shadow-sm" style="border-radius:20px; border:1.5px solid #f0f0f0;">
              <div class="card-body p-4">

                <!-- STEP INDICATOR -->
                <div class="step-indicator">
                  <div class="step-item active" id="si-1">
                    <div class="step-circle">1</div>
                    <span class="step-label">Country</span>
                  </div>
                  <div class="step-item" id="si-2">
                    <div class="step-circle">2</div>
                    <span class="step-label">Service</span>
                  </div>
                  <div class="step-item" id="si-3">
                    <div class="step-circle">3</div>
                    <span class="step-label">OTP</span>
                  </div>
                </div>

                <!-- HIDDEN FIELDS -->
                <input type="hidden" id="token"      value="<?= htmlspecialchars($_SESSION['token']) ?>">
                <input type="hidden" id="server_no"  value="">
                <input type="hidden" id="service_id" value="">

                <!-- ── STEP 1: COUNTRY ── -->
                <div class="step-panel active" id="step1">
                  <h6 class="fw-bold mb-3 text-center" style="color:#111;">Select a Country</h6>
                  <div class="country-grid" id="country-grid">

                    <div class="country-card" data-server="us" onclick="selectCountry(this)">
                      <span class="fi fi-us country-flag-img"></span>
                      <div class="country-name">United States</div>
                    </div>

                    <div class="country-card" data-server="ca" onclick="selectCountry(this)">
                      <span class="fi fi-ca country-flag-img"></span>
                      <div class="country-name">Canada</div>
                    </div>

                  </div>
                </div>

                <!-- ── STEP 2: SERVICE ── -->
                <div class="step-panel" id="step2">
                  <button class="step-back-btn" onclick="goStep(1)">
                    <i class="bi bi-arrow-left"></i> Back
                  </button>
                  <h6 class="fw-bold mb-3" id="step2-title" style="color:#111;">Select a Service</h6>

                  <div class="buy-bar">
                    <div class="buy-bar-info">
                      <span class="buy-bar-label">Selected service</span>
                      <span class="buy-bar-name"  id="selected-name">—</span>
                      <span class="buy-bar-price" id="selected-price">₦0</span>
                    </div>
                    <button class="buy-bar-btn" id="buy-btn" disabled onclick="doBuy()">
                      <i class="bi bi-cart-plus-fill"></i> Buy Number
                    </button>
                  </div>

                  <div class="service-search-wrap">
                    <i class="bi bi-search"></i>
                    <input type="text" id="service-search" placeholder="Search service...">
                  </div>

                  <div class="service-list" id="service-list">
                    <div class="skeleton-row"></div>
                    <div class="skeleton-row"></div>
                    <div class="skeleton-row"></div>
                  </div>
                </div>

                <!-- ── STEP 3: OTP ── -->
                <div class="step-panel" id="step3">
                  <div class="step3-header" id="step3-header"><div class="success-icon"><i class="bi bi-check-lg"></i></div><h5 id="step3-title-text">Your Active Number</h5><p id="step3-subtitle">Waiting for your OTP code…</p></div>
                  <div id="card-container"></div>
                  <div class="text-center mt-3">
                    <button class="step-back-btn" onclick="buyAnother()">
                      <i class="bi bi-plus-circle"></i> Buy Another Number
                    </button>
                  </div>
                </div>

              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
    <?php include 'partial/footer.php'; ?>
  </div>
</div>

<?php include 'partial/scripts.php'; ?>
<script>window.jQuery || document.write('<script src="https://code.jquery.com/jquery-3.7.1.min.js"><\/script>')</script>
<script src="assets/js/notiflix-aio-3.2.7.min.js"></script>
<script src="js/main_usa_ca.js"></script>
<?php include 'partial/footer-end.php'; ?>
