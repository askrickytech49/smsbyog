<?php
// profile image fallback
if ($userdata['image_url'] == "") {
    $img_url = 'https://user.smsbyog.com/images/logopng.png';
} else {
    $img_url = $userdata['image_url'];
}
?>


<style>
/* ===============================
   AUTHPADI – SAAS TOPBAR
================================ */

.page-header {
  background: #ffffff;
  border-bottom: 1px solid #eef0f4;
}

.header-wrapper {
  padding: 12px 18px;
  display: flex;
  align-items: center;
  gap: 16px;
}

/* LEFT */
.header-left {
  display: flex;
  align-items: center;
  gap: 14px;
}

/* SIDEBAR TOGGLE */
.toggle-sidebar {
  cursor: pointer;
  color: #6b7280;
}

/* SEARCH */
.topbar-search input {
  border: none;
  outline: none;
  background: #f6f7f9;
  padding: 8px 12px;
  border-radius: 10px;
  font-size: 13px;
  width: 220px;
}

.topbar-search input::placeholder {
  color: #9ca3af;
}

/* RIGHT */
.header-right {
  margin-left: auto;
  display: flex;
  align-items: center;
  gap: 18px;
}

/* WALLET */
.wallet-pill {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 20px;
  border: 2px solid #e10700;
  font-weight: 700;
  font-size: 14px;
  color: #111827;
}

/* PROFILE */
.profile-media {
  display: flex;
  align-items: center;
  gap: 10px;
}

.profile-media img {
  width: 38px;
  height: 38px;
  border-radius: 50%;
}

.profile-media span {
  font-weight: 600;
  font-size: 14px;
}

.profile-media p {
  margin: 0;
  font-size: 12px;
  color: #6b7280;
}

/* ===============================
   MODERN SAAS PROFILE DROPDOWN
================================ */

.profile-nav{
  position: relative;
}

/* DROPDOWN CONTAINER */
.profile-dropdown{
  position: absolute;
  right: 0;
  top: 55px;
  width: 190px;
  background: #fff;
  border-radius: 14px;
  padding: 8px;
  border: 1px solid #f0f2f6;
  box-shadow: 0 12px 35px rgba(0,0,0,0.08);
  opacity: 0;
  transform: translateY(8px);
  pointer-events: none;
  transition: all .2s ease;
  z-index: 999;
}

/* SHOW DROPDOWN */
.onhover-dropdown:hover .profile-dropdown{
  opacity: 1;
  transform: translateY(0);
  pointer-events: auto;
}

/* MENU ITEMS */
.profile-dropdown li{
  list-style: none;
}

.profile-dropdown li a{
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  border-radius: 10px;
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
  color: #374151;
  transition: all .15s ease;
}

/* ICONS */
.profile-dropdown li a i{
  width: 16px;
  height: 16px;
  color: #6b7280;
}

/* HOVER EFFECT */
.profile-dropdown li a:hover{
  background: #f6f7fb;
  color: #111827;
}

/* LOGOUT COLOR */
.profile-dropdown li:last-child a{
  color: #ef4444;
}

.profile-dropdown li:last-child a:hover{
  background: #fff1f1;
}

/* DIVIDER */
.profile-dropdown li + li{
  margin-top: 4px;
}


/* MOBILE */
@media (max-width: 768px) {
  .topbar-search {
    display: none;
  }
  /* PROFILE HEADER */
.profile-media {
  display: flex;
  align-items: center;
  gap: 10px;
}

/* NAME + ARROW INLINE */
.profile-info {
  display: flex;
  align-items: center;
  gap: 6px;
}

.profile-name {
  font-weight: 600;
  font-size: 14px;
  color: #111;
}

.profile-arrow {
  font-size: 14px;
  color: #555;
  transition: transform 0.2s ease;
}

/* OPTIONAL: Rotate arrow on hover */
.onhover-dropdown:hover .profile-arrow {
  transform: rotate(180deg);
}

}
</style>

<div class="page-header">
  <div class="header-wrapper">

    <!-- LEFT -->
    <div class="header-left">

      <!-- SIDEBAR TOGGLE (PRESERVED) -->
      <div class="toggle-sidebar">
        <i class="status_toggle middle sidebar-toggle" data-feather="grid"></i>
      </div>

     

    </div>

    <!-- RIGHT -->
    <div class="header-right">

    <!-- WALLET -->
<div class="wallet-pill">
  Balance: ₦<span id="current_balance"><?= number_format((float)$userwallet['balance'], 2) ?></span>
</div>


     <!-- PROFILE -->
<div class="profile-nav onhover-dropdown">

  <div class="profile-media">
    <img src="https://smsbyog.com/favicon.png" alt="">
    <div class="profile-info">
      <i class="fa fa-angle-down profile-arrow"></i>
    </div>
  </div>

  <ul class="profile-dropdown">

    <li>
      <a href="profile">
        <i data-feather="user"></i>
        <span>Account</span>
      </a>
    </li>

    <li>
      <a href="logout">
        <i data-feather="log-out"></i>
        <span>Logout</span>
      </a>
    </li>

  </ul>

</div>


    </div>

  </div>
</div>

<script>
  feather.replace();
</script>
