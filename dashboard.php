<?php
session_start();

include 'include/config.php';
include __DIR__ . '/include/mode_check.php';
require __DIR__ . '/class/class.control.php';
require_once __DIR__ . '/include/tiger_sms_health.php';

/* AUTH */
if (empty($_SESSION['token'])) {
    session_destroy();
    redirect('login');
}

$wallet = new radiumsahil();
if ($wallet->balancedata() === false) {
    session_destroy();
    redirect('login');
}

// Admin accounts should not access user dashboard — redirect to admin panel
$tk_check = mysqli_real_escape_string($conn, $_SESSION['token']);
$tk_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT u.type FROM login_token lt JOIN user_data u ON lt.user_id=u.id WHERE lt.token='$tk_check' AND lt.status='1' LIMIT 1"));
if ($tk_row && in_array($tk_row['type'], ['admin', 'super_admin'])) {
    redirect('admin/dashboard');
}
date_default_timezone_set('Africa/Lagos');


// get current hour
$hour = date("H");

// greeting logic
if ($hour >= 5 && $hour < 12) {
    $greeting = "Good Morning";
    $icon = "☀️";
} elseif ($hour >= 12 && $hour < 17) {
    $greeting = "Good Afternoon";
    $icon = "🌤️";
} else {
    $greeting = "Good Evening";
    $icon = "🌙";
}

/* DATA */
$tigerHealth  = tigerSmsHealth();
$userdata     = $wallet->userdata();
$userwallet   = $wallet->userwallet();
$referwallet  = $wallet->refer_data();
$top_services = $wallet->top_services();
$wallet->closeConnection();
/* ================= OTP ================= */
$total_otp_sell = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM active_number WHERE status='1'"));

$page_title = "Dashboard - " . $site_data['web_name'];
include('partial/header.php');
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@600;700&display=swap');

:root{
  --orange:#e10700;
  --orange-soft:#fd0001;
  --bg:#f6f7fb;
  --white:#fff;
  --text:#111;
  --muted:#6b7280;
  --border:#e3e7f0;
}

/* ================= BASE ================= */
html,body{
  background:var(--bg);
  font-family:'Nunito Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif !important;
  font-weight:600;
  color:var(--text);
}

.page-body{padding:14px!important}

/* ================= KPI ================= */
.kpi-grid{
  display:grid;
  grid-template-columns:repeat(2,1fr);
  gap:16px;
  margin-bottom:18px;
  margin: 10px;
}

.kpi-card{
  background:#fff;
  border:1.5px solid var(--border, #e5e7eb);
  border-radius:18px;
  padding:18px;
  position:relative;
  overflow:hidden;
}

.kpi-card.primary{
  background:linear-gradient(135deg,#e10700,#fd0001);
  color:#fff;
  border:none;
}

/* ICON */
.kpi-icon{
  width:40px;
  height:40px;
  border-radius:12px;
  display:flex;
  align-items:center;
  justify-content:center;
  margin-bottom:14px;
  background:#e10700;
  color: white;
}

.kpi-card.primary .kpi-icon{
  background:rgba(255,255,255,.2);
  color:#fff;
}

.kpi-icon svg{
  width:18px;
  height:18px;
}

/* VALUE + LABEL */
.kpi-value{
  font-size:22px;
  font-weight:800;
  letter-spacing:-0.3px;
}

.kpi-label{
  font-size:12px;
  font-weight:600;
  margin-top:4px;
  color:#6b7280;
}

.kpi-card.primary .kpi-label{
  color:#ffe3c7;
}

/* PERCENT BADGE */
.kpi-badge{
  position:absolute;
  top:14px;
  right:14px;
  display:flex;
  align-items:center;
  gap:4px;
  font-size:11px;
  font-weight:700;
  padding:5px 8px;
  border-radius:999px;
  background:#e7f9ef;
  color:#16a34a;
}

.kpi-badge.down{
  background:#fee2e2;
  color:#dc2626;
}

.kpi-card.primary .kpi-badge{
  background:rgba(255,255,255,.25);
  color:#fff;
}

.kpi-badge svg{
  width:12px;
  height:12px;
}

/* ================= CARDS ================= */
.card-saas{
  background:#fff;
  border:1.5px solid var(--border);
  border-radius:16px;
  padding:16px;
  margin-bottom:18px;
  margin: 10px;
}

.section-title{
  font-weight:700;
  font-size:15px;
  margin-bottom:12px;
}

/* ================= WALLET INSIGHT ================= */
.wallet-chart-wrap{
  position:relative;
  min-height:220px;
}

.wallet-chart-wrap canvas{
  width:100%!important;
  height:200px!important;
}

/* ================= QUICK ACTIONS ================= */
.quick-actions {
  margin-top: 16px;
}

.quick-grid{
  display:grid;
  grid-template-columns:repeat(4,1fr);
  gap:16px;
}

.quick-card{
  background:#ffffff;
  border:2px solid #e5e7eb; /* 👈 stronger border */
  border-radius:18px;
  padding:22px 16px;
  text-align:center;
  text-decoration:none;
  transition:all .25s ease;
}

.quick-card:hover{
  transform:translateY(-3px);
  border-color:var(--orange); /* 👈 pops on hover */
  box-shadow:0 10px 26px rgba(0,0,0,.12);
}

.quick-icon{
  width:46px;
  height:46px;
  border-radius:50%;
  display:flex;
  align-items:center;
  justify-content:center;
  margin:0 auto 12px;
  background:#e10700; /* subtle contrast */
  border:2px solid black; /* 👈 icon border */
  color:white;
  font-size:20px;
}

.quick-title{
  font-weight:800;
  font-size:14px;
  color:#111827;
  margin-bottom:4px;
}

.quick-sub{
  font-size:12px;
  color:#6b7280;
}

/* Mobile */
@media(max-width:768px){
  .quick-grid{
    grid-template-columns:repeat(2,1fr);
  }
}
.quick-card{
  background:linear-gradient(#fff,#fff) padding-box,
             linear-gradient(180deg,#e5e7eb,#f9fafb) border-box;
  border:2px solid transparent;
}


/* ================= SERVICE HEALTH ================= */
.health-item{
  border:1.5px solid var(--border);
  border-radius:12px;
  padding:14px;
  margin-bottom:10px;
  display:flex;
  justify-content:space-between;
  align-items:center;
}

.health-meta{
  font-size:12px;
  color:var(--muted);
}

.pill{
  font-size:11px;
  font-weight:700;
  padding:6px 12px;
  border-radius:20px;
}

.ok{background:#e7f8ea;color:#1b8f1b}
.warn{background:#fff1dc;color:#e10700}

/* ================= TOP SERVICES ================= */
.service-row{
  display:flex;
  justify-content:space-between;
  align-items:center;
  border:1.5px solid var(--border);
  border-radius:12px;
  padding:14px;
  margin-bottom:10px;
}

.service-left{
  display:flex;
  gap:12px;
}

.service-left img{
  width:38px;
  height:38px;
  border-radius:10px;
}

.service-meta{
  font-size:12px;
  color:var(--muted);
}

.service-buy{
  border:2px solid var(--orange);
  color:var(--orange);
  padding:6px 14px;
  border-radius:10px;
  font-size:12px;
  font-weight:700;
}

.service-buy:hover{
  background:var(--orange);
  color:#fff;
}
.kpi-val {
  color: #16a34a; /* clean modern green */
  font-weight: 800;
}




/* ================= DESKTOP ================= */
@media(min-width:992px){
  .page-body{padding:24px!important}
  .kpi-grid{grid-template-columns:repeat(4,1fr)}
  .quick-grid{grid-template-columns:repeat(4,1fr)}
}

/*==============Greeting Box ================ */
.greeting-box {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;

    max-width: 100%;
    margin: 20px auto;
    padding: 16px 20px;

    border: 2px solid #e10700;
    border-radius: 14px;

    background: #ffffff;
    font-size: 16px;
    font-weight: 500;
    color: #111827;

    box-shadow: 0 4px 14px rgba(0,0,0,0.05);
    text-align: center;
}

.greeting-icon {
    font-size: 22px;
    display: inline-flex;
    animation: iconPop 0.9s ease-out forwards;
}

/* Subtle pop + fade */
@keyframes iconPop {
    0% {
        opacity: 0;
        transform: scale(0.6) rotate(-10deg);
    }
    60% {
        opacity: 1;
        transform: scale(1.15) rotate(4deg);
    }
    100% {
        transform: scale(1) rotate(0deg);
    }
}


.greeting-text strong {
    font-weight: 700;
    color: #e10700;
}

/* Optional: force single line on large screens */
@media (min-width: 768px) {
    .greeting-box {
        white-space: nowrap;
    }
}

</style>

<?php include('partial/loader.php'); ?>

<div class="page-wrapper compact-wrapper">
<?php include('partial/topbar.php'); ?>

<div class="page-body-wrapper">

<?php
$is_admin = false;

if(isset($userdata['type']) && $userdata['type'] === 'admin'){
    $is_admin = true;
}
?>

<?php include('partial/sidebar.php'); ?>

<!-- Greeting Box -->
<div class="page-body">
<div class="greeting-box">
    <span class="greeting-icon"><?php echo $icon; ?></span>

    <span class="greeting-text">
        <?php echo $greeting; ?>, 
        <strong><?php echo $userdata['name']; ?></strong> | Welcome Back
    </span>
</div>
<!-- KPI -->
<div class="kpi-grid">

  <!-- Wallet Balance -->
  <div class="kpi-card primary">
    <div class="kpi-badge">
      <i data-feather="trending-up"></i> +12.5%
    </div>

    <div class="kpi-icon">
      <i data-feather="credit-card"></i>
    </div>

    <div class="kpi-value">₦<?= number_format($userwallet['balance']) ?></div>
    <div class="kpi-label">Wallet Balance</div>
  </div>

  <!-- Numbers Purchased -->
  <div class="kpi-card">
    <div class="kpi-badge">
      <i data-feather="trending-up"></i> +8.2%
    </div>

    <div class="kpi-icon">
      <i data-feather="smartphone"></i>
    </div>

    <div class="kpi-value"><?= number_format($userwallet['total_otp']) ?></div>
    <div class="kpi-label">Numbers Purchased</div>
  </div>

  <!-- SMS Received -->
  <div class="kpi-card">
    <div class="kpi-badge">
      <i data-feather="trending-up"></i> +23.1%
    </div>

    <div class="kpi-icon">
      <i data-feather="message-circle"></i>
    </div>

    <div class="kpi-value">Active</div>
    <div class="kpi-label">SMS Server Status</div>
  </div>

  <!-- Total Funded -->
  <div class="kpi-card">
    <div class="kpi-badge">
      <i data-feather="trending-up"></i> +5.4%
    </div>

    <div class="kpi-icon">
      <i data-feather="activity"></i>
    </div>

    <div class="kpi-value">₦<?= number_format((float)($userwallet['total_recharge'] ?? 0)) ?></div>
    
    <div class="kpi-label">Total Amount Funded</div>
  </div>

</div>



<!-- QUICK ACTIONS -->
<div class="card-saas quick-actions">
  <div class="section-title">Quick Actions</div>

  <div class="quick-grid">

    <a href="buy-number" class="quick-card">
      <div class="quick-icon">
        <i class="bi bi-telephone"></i>
      </div>
      <div class="quick-title">Buy Numbers</div>
      <div class="quick-sub">Get virtual numbers</div>
    </a>

    <a href="recharge" class="quick-card">
      <div class="quick-icon">
        <i class="bi bi-credit-card"></i>
      </div>
      <div class="quick-title">Fund Wallet</div>
      <div class="quick-sub">Add funds instantly</div>
    </a>

    <a href="https://myoglog.com/" target="_blank" class="quick-card">
      <div class="quick-icon">
        <i class="bi bi-graph-up-arrow"></i>
      </div>
      <div class="quick-title">Buy Logs</div>
      <div class="quick-sub">All Social Logs</div>
    </a>

    <a href="https://t.me/myogsocial" target="_blank" class="quick-card">
      <div class="quick-icon">
        <i class="bi bi-headset"></i>
      </div>
      <div class="quick-title">Support</div>
      <div class="quick-sub">24/7 assistance</div>
    </a>

  </div>
</div>


<!-- SERVICE HEALTH -->
<div class="card-saas">
  <div class="section-title">Service Health</div>

  <div class="health-item">
    <div>
      <strong>Virtual Numbers</strong><br>
      <span class="health-meta">Latency <?= $tigerHealth['latency'] ?>ms</span>
    </div>
    <span class="pill ok">Live</span>
  </div>

  <div class="health-item">
    <div>
      <strong>OTP Delivery</strong><br>
      <span class="health-meta">Uptime <?= $tigerHealth['uptime'] ?></span>
    </div>
    <span class="pill ok">Delivering</span>
  </div>

  <div class="health-item">
    <strong>SMS Routing</strong>
    <span class="pill <?= $tigerHealth['latency'] < 1500 ? 'ok' : 'warn' ?>">Stable</span>
  </div>
</div>

<!-- TOP SERVICES -->
<div class="card-saas">
  <div class="section-title">Top Services</div>

   <?php if (!empty($top_services)): ?>
   
    <?php foreach ($top_services as $s): ?>
      <div class="service-row">
        <div class="service-left">

          <!-- Brand icon from service_icon table or getServiceIcon helper -->
          <img 
            src="<?= htmlspecialchars($s['service_logo'] ?? '') ?>"
            alt="<?= htmlspecialchars(strip_tags($s['service_name'])) ?>"
            style="width:38px;height:38px;border-radius:10px;object-fit:contain;padding:4px;"
            onerror="this.src='https://www.google.com/s2/favicons?sz=64&domain=<?= urlencode(strtolower(preg_replace('/[^a-zA-Z]/', '', strip_tags($s['service_name'])))) ?>.com'"
          >

          <div>
            <!-- FIXED: strip HTML from DB -->
            <strong><?= htmlspecialchars(strip_tags($s['service_name'])) ?></strong><br>
            <span class="service-meta">
              <?= htmlspecialchars($s['service_code']) ?> • <?= htmlspecialchars($s['server_name']) ?>
            </span>
          </div>
        </div>

        <a href="buy-number-1" class="service-buy">Buy</a>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="health-item">
      <span class="health-meta">No top services available yet</span>
    </div>
  <?php endif; ?>
</div>

<!-- WALLET INSIGHT -->


</div>
</div>
</div>

<script>
feather.replace();

new Chart(document.getElementById('walletChart'),{
  type:'line',
  data:{
    labels:['Balance','Recharge','Numbers','Referral'],
    datasets:[{
      data:[
        <?= (float)$userwallet['balance'] ?>,
        <?= (float)$userwallet['total_recharge'] ?>,
        <?= (float)$userwallet['total_otp'] ?>,
        <?= (float)$referwallet['balance'] ?>
      ],
      borderColor:'#e10700',
      backgroundColor:'rgba(255,122,0,.18)',
      fill:true,
      tension:.4
    }]
  },
  options:{
    responsive:true,
    maintainAspectRatio:false,
    plugins:{legend:{display:false}},
    scales:{y:{beginAtZero:true}}
  }
});
</script>
<style>
/* ================= OVERLAY ================= */
#authpadi-popup-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999999;
}

/* ================= POPUP ================= */
.authpadi-popup {
  background: #ffffff;
  width: 92%;
  max-width: 420px;
  max-height: 80vh;
  padding: 22px 20px;
  border-radius: 16px;
  text-align: center;
  font-family: 'Nunito Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif !important;
  position: relative;
  animation: popupFade 0.25s ease;
  overflow-y: auto;
}

/* ================= CLOSE BUTTON ================= */
.authpadi-close {
  position: absolute;
  top: 10px;
  right: 14px;
  background: none;
  border: none;
  font-size: 22px;
  cursor: pointer;
}

/* ================= TITLE ================= */
.authpadi-popup h3 {
  margin: 0 0 14px;
  font-weight: 700;
  font-size: 18px;
}

/* ================= SUBTITLE ================= */
.popup-subtitle {
  text-align: left;
  margin-bottom: 6px;
  font-weight: 700;
  font-size: 14px;
}

/* ================= LIST (FIXED) ================= */
.authpadi-popup ul {
  list-style-type: disc !important;        /* 🔥 force bullets */
  list-style-position: outside !important; /* 🔥 proper spacing */
  padding-left: 20px !important;
  margin: 0 0 18px 0 !important;
  text-align: left;
  font-size: 14px;
  line-height: 1.6;
  color: #444;
}

/* ensure li doesn’t kill bullets */
.authpadi-popup ul li {
  display: list-item !important;
  margin-bottom: 6px;
}

/* optional: make bullets visible even in aggressive CSS resets */
.authpadi-popup ul li::marker {
  color: #e10700;
}

/* ================= BUTTON ================= */
.authpadi-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  width: 100%;
  background: #e10700;
  color: #fff;
  text-decoration: none;
  padding: 14px;
  border-radius: 999px;
  font-weight: 700;
  margin: 10px 0 6px;
}

/* Telegram Icon */
.authpadi-btn svg {
  width: 20px;
  height: 20px;
  fill: #ffffff;
}

/* ================= CLOSE TEXT ================= */
.popup-close-text {
  display: inline-block;
  margin-top: 8px;
  font-size: 13px;
  color: #777;
  cursor: pointer;
}

/* ================= ANIMATION ================= */
@keyframes popupFade {
  from {
    transform: scale(0.92);
    opacity: 0;
  }
  to {
    transform: scale(1);
    opacity: 1;
  }
}
</style>


<div id="authpadi-popup-overlay">
  <div class="authpadi-popup">
    <button class="authpadi-close" onclick="closeAuthpadiPopup()">×</button>

    <h3>📌 Important Announcement</h3>

    <p class="popup-subtitle">Safety Tips:</p>

    <ul>
      <li>Delete and reinstall your WhatsApp 📱 before getting a number.</li>
      <li>Avoid WhatsApp Business — they ban faster. Use normal WhatsApp.</li>
      <li>Always enable 2FA immediately after creating WhatsApp or Telegram to stay protected if the number is later reassigned.</li>
      <li>Ensure your ⏰ Time Zone & 🌍 VPN match the country of the number.</li>
    </ul>

    <a href="https://t.me/+GYjImzLVwnYxMTY0" target="_blank" class="authpadi-btn">
      <svg viewBox="0 0 24 24">
        <path d="M9.993 15.674 9.84 19.2c.59 0 .845-.252 1.153-.553l2.768-2.63 5.738 4.192c1.053.58 1.8.275 2.067-.977L23.94 3.93c.318-1.57-.57-2.186-1.59-1.79L1.51 10.34c-1.53.6-1.508 1.46-.278 1.84l5.3 1.656L18.9 6.24c.59-.39 1.13-.174.687.216z"/>
      </svg>
      Join Telegram Channel
    </a>

    <span class="popup-close-text" onclick="closeAuthpadiPopup()">Close</span>
  </div>
</div>


<script>
function closeAuthpadiPopup() {
  document.getElementById('authpadi-popup-overlay').style.display = 'none';
}
</script>

<!-- TikTok Pixel Code Start -->
<script>
!function (w, d, t) {
  w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie","holdConsent","revokeConsent","grantConsent"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(
var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var r="https://analytics.tiktok.com/i18n/pixel/events.js",o=n&&n.partner;ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=r,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};n=document.createElement("script")
;n.type="text/javascript",n.async=!0,n.src=r+"?sdkid="+e+"&lib="+t;e=document.getElementsByTagName("script")[0];e.parentNode.insertBefore(n,e)};


  ttq.load('D5NKGLJC77UFLMP0BDJG');
  ttq.page();
}(window, document, 'ttq');
</script>
<!-- TikTok Pixel Code End -->

<?php include('partial/scripts.php'); ?>
<?php include('partial/footer-end.php'); ?>
