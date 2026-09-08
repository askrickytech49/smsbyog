<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<?php
// session_start();
?>

<style>
/* ===============================
   AUTHPADI – SAAS SIDEBAR (SECTIONED)
   FIXED + FUNCTIONALITY PRESERVED
================================ */

/* WRAPPER */
.sidebar-wrapper {
  background: #ffffff;
  border-right: 1px solid #eef0f4;
  width: 260px;
}

/* LOGO AREA */
.logo-wrapper {
  padding: 18px 18px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid #eef0f4;
}

.logo-wrapper a {
  display: flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
}

.logo-wrapper img {
  width: 36px;
}

.logo-wrapper span {
  font-size: 18px;
  font-weight: 800;
  color: #e10700;
}

/* TOGGLE */
.toggle-sidebar {
  cursor: pointer;
  color: #9ca3af;
}

/* SIDEBAR MAIN */
.sidebar-main {
  height: calc(100vh - 72px);
  display: flex;
  flex-direction: column;
}

/* SCROLL */
#simple-bar {
  padding: 12px 12px 24px;
  overflow-y: auto;
  scrollbar-width: thin;
}

/* SECTION HEADERS */
.sidebar-section {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  color: #e10700;
  padding: 18px 12px 8px;
  letter-spacing: .04em;
}

/* LIST ITEM */
.sidebar-list {
  margin: 4px 0;
  list-style: none;
}

/* LINK */
.sidebar-link {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 12px 16px;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 600;
  color: #000000;
  transition: all .2s ease;
  text-decoration: none;
  position: relative;
}

/* ICON NORMALIZATION (SVG + FA) */
.sidebar-link svg,
.sidebar-link i {
  width: 20px;
  height: 20px;
  font-size: 18px;
  opacity: 1;
  color: black;
  flex-shrink: 0;
  text-align: center;
}

/* FA BRAND ICON FIX */
.sidebar-link i.fa-brands {
  width: 20px;
  text-align: center;
}

/* HOVER */
.sidebar-list:hover .sidebar-link {
  background: #f6f7f9;
  color: #000000;
}

.sidebar-list:hover .sidebar-link i,
.sidebar-list:hover .sidebar-link svg {
  color: #000000;
}

/* ACTIVE STATE (AUTHPADI ORANGE) */
.sidebar-link.active,
.sidebar-list.active .sidebar-link {
  background: rgba(255, 122, 0, 0.14) !important;
  color: #e10700;
  font-weight: 700;
}

/* ACTIVE ICON */
.sidebar-link.active i,
.sidebar-link.active svg {
  color: #e10700;
}

/* ACTIVE RIGHT INDICATOR */
.sidebar-link.active::after {
  content: "";
  position: absolute;
  right: 6px;
  top: 50%;
  transform: translateY(-50%);
  width: 4px;
  height: 22px;
  background: #e10700;
  border-radius: 6px;
}
.simplebar-placeholder{
      display:none;
  }

/* ARROWS (PRESERVED) */
.left-arrow,
.right-arrow {
  padding: 6px 14px;
  color: #9ca3af;
  cursor: pointer;
}

/* MOBILE BACK (PRESERVED) */
.mobile-back {
  font-size: 13px;
  color: #6b7280;
}

/* SIDEBAR LOGO FIX */
.sidebar-logo {
  display: flex;
  align-items: center;
}

.sidebar-logo img {
  max-width: 160px;
  width: 100%;
  height: auto;
  object-fit: contain;
}

/* MOBILE */
@media (max-width: 768px) {
  .sidebar-logo img {
    max-width: 140px;
  }
  
  .simplebar-placeholder{
      display:none;
  }
}

</style>

<div class="sidebar-wrapper" sidebar-layout="stroke-svg">
  <div>

    <!-- LOGO -->
    <div class="logo-wrapper">
      <a href="dashboard" class="sidebar-logo">
  <img src="https://smsbyog.com/SmsByOglogo.png" alt="AuthPadi">
</a>


      <div class="toggle-sidebar">
        <i class="status_toggle middle sidebar-toggle" data-feather="grid"></i>
      </div>
    </div>

    <!-- SIDEBAR -->
    <nav class="sidebar-main">

      <div class="left-arrow" id="left-arrow">
        <i data-feather="arrow-left"></i>
      </div>

      <div id="sidebar-menu">
        <ul class="sidebar-links" id="simple-bar">

          <!-- CORE -->
          <li class="sidebar-section">Core</li>
          <li class="sidebar-list">
            <a class="sidebar-link sidebar-title link-nav active" href="dashboard">
              <svg class="stroke-icon">
                <use href="assets/svg/icon-sprite.svg#stroke-home"></use>
              </svg>
              <span>Dashboard</span>
            </a>
          </li>
          
      <?php if(isset($is_admin) && $is_admin === true){ ?>

<li class="sidebar-list">
  <a class="sidebar-link sidebar-title link-nav" href="/admin/dashboard">
    <svg class="stroke-icon">
      <use href="assets/svg/icon-sprite.svg#stroke-home"></use>
    </svg>
    <span>Admin Dashboard</span>
  </a>
</li>

<?php } ?>


          <li class="sidebar-list">
            <a class="sidebar-link sidebar-title link-nav" href="buy-number-1">
              <svg class="stroke-icon">
                <use href="assets/svg/icon-sprite.svg#stroke-chat"></use>
              </svg>
              <span>Numbers (Server 1)</span>
            </a>
          </li>
          
          <li class="sidebar-list">
            <a class="sidebar-link sidebar-title link-nav" href="buy-number-server2">
              <svg class="stroke-icon">
                <use href="assets/svg/icon-sprite.svg#stroke-chat"></use>
              </svg>
              <span>Numbers (Server 2)</span>
            </a>
          </li>
           <li class="sidebar-list">
            <a class="sidebar-link sidebar-title link-nav" href="buy-usa-number">
              <svg class="stroke-icon">
                <use href="assets/svg/icon-sprite.svg#stroke-chat"></use>
              </svg>
              <span>Buy USA Numbers</span>
            </a>
          </li> 
           <li class="sidebar-list">
            <a class="sidebar-link sidebar-title link-nav" href="buy-us-ca-number">
              <svg class="stroke-icon">
                <use href="assets/svg/icon-sprite.svg#stroke-chat"></use>
              </svg>
              <span>Buy USA & Canada…</span>
            </a>
          </li> 

          <!-- WALLET -->
          <li class="sidebar-section">Wallet</li>
          <li class="sidebar-list">
            <a class="sidebar-link sidebar-title link-nav" href="recharge">
              <svg class="stroke-icon">
                <use href="assets/svg/icon-sprite.svg#stroke-learning"></use>
              </svg>
              <span>Fund Wallet</span>
            </a>
          </li>
          
          <!--<li class="sidebar-list">-->
          <!--  <a class="sidebar-link sidebar-title link-nav" href="deposit">-->
          <!--    <svg class="stroke-icon">-->
          <!--      <use href="assets/svg/icon-sprite.svg#stroke-learning"></use>-->
          <!--    </svg>-->
          <!--    <span>Fund Wallet (Manual)</span>-->
          <!--  </a>-->
          <!--</li>-->

          <!-- History -->
          <li class="sidebar-section">History</li>
          <li class="sidebar-list">
            <a class="sidebar-link sidebar-title link-nav" href="numbers">
              <svg class="stroke-icon">
                <use href="assets/svg/icon-sprite.svg#stroke-form"></use>
              </svg>
              <span>Numbers History</span>
            </a>
          </li>

          <li class="sidebar-list">
            <a class="sidebar-link sidebar-title link-nav" href="transactions">
              <svg class="stroke-icon">
                <use href="assets/svg/icon-sprite.svg#stroke-task"></use>
              </svg>
              <span>Transaction History</span>
            </a>
          </li>

          <!-- Developers Tool -->
          <li class="sidebar-section">Developers Tool</li>
          <li class="sidebar-list">
            <a class="sidebar-link sidebar-title link-nav" href="api-tool">
              <svg class="stroke-icon">
                <use href="assets/svg/icon-sprite.svg#stroke-internationalization"></use>
              </svg>
              <span>API Documentation</span>
            </a>
          </li>
           <!-- My Account Tool -->
          <li class="sidebar-section">My Account</li>
          <li class="sidebar-list">
            <a class="sidebar-link sidebar-title link-nav" href="profile">
              <i data-feather="user"></i>
              <span>My Account</span>
            </a>
          </li>
          
          <li class="sidebar-list">
            <a class="sidebar-link sidebar-title link-nav" href="logout" >
              <i data-feather="log-out"></i>
              <span>Logout</span>
            </a>
          </li>


          <!-- COMMUNITY -->
          <li class="sidebar-section">Community</li>
          <li class="sidebar-list">
            <a class="sidebar-link sidebar-title link-nav" href="https://t.me/myogsocial" target="_blank">
              <svg class="stroke-icon">
                <use href="assets/svg/icon-sprite.svg#stroke-support-tickets"></use>
              </svg>
              <span>Support</span>
            </a>
          </li>

          <li class="sidebar-list">
            <a class="sidebar-link sidebar-title link-nav" href="https://t.me/+GYjImzLVwnYxMTY0" target="_blank">
              <svg class="stroke-icon">
                <use href="assets/svg/icon-sprite.svg#stroke-button"></use>
              </svg>
              <span>Telegram Channel</span>
            </a>
          </li>
          
          <!-- <li class="sidebar-list">
  <a class="sidebar-link sidebar-title link-nav" href="https://www.tiktok.com/@authpadi" 
     target="_blank">
     
    <i class="fa-brands fa-tiktok"></i>
    <span>TikTok</span>
  </a>
</li> -->
<li class="sidebar-section">Others</li>
  <li class="sidebar-list">
  <a class="sidebar-link sidebar-title link-nav" 
     href="https://myoglog.com/" 
     target="_blank">
     
    <i class="fa-solid fa-chart-line"></i>

    <span>Buy Logs</span>
  </a>
</li> 
<!-- <li class="sidebar-list">
  <a class="sidebar-link sidebar-title link-nav" 
     href="https://g.page/r/CYeOfYQ4Q-S_EBM/review" 
     target="_blank">
     
    <i class="fa-brands fa-google"></i>


    <span>Give us a Review on Google</span>
  </a>
</li> -->




          

          <!-- li class="sidebar-list">
            <a class="sidebar-link sidebar-title link-nav" href="http://www.tiktok.com/@authpadi" target="_blank">
              <svg class="stroke-icon">
                <use href="assets/svg/icon-sprite.svg#stroke-button"></use>
              </svg>
              <span>Instagram</span>
            </a>
          <!-- /li -->

        </ul>
      </div>

      <div class="right-arrow" id="right-arrow">
        <i data-feather="arrow-right"></i>
      </div>

    </nav>
  </div>
</div>

<!-- this website is design by @radiumsahil -->
