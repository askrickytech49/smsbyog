<?php
// Detect current page for active state
$current = basename($_SERVER['PHP_SELF'], '.php');
function nav_active($pages, $current) {
    $pages = is_array($pages) ? $pages : [$pages];
    return in_array($current, $pages) ? 'active' : '';
}
?>
<!-- Sidebar Overlay (mobile) -->
<div id="sidebarOverlay"></div>

<!-- Sidebar -->
<nav id="adminSidebar">

  <!-- Brand -->
  <a class="sidebar-brand" href="dashboard">
    <!-- Full logo (shown when expanded) -->
    <img src="https://smsbyog.com/SmsByOglogo.png" alt="SmsByOg" class="sidebar-logo-full">
    <!-- Favicon (shown when collapsed) -->
    <img src="https://smsbyog.com/favicon.png" alt="SmsByOg" class="sidebar-logo-icon">
  </a>

  <!-- Navigation -->
  <div class="sidebar-nav">
    <ul class="sidebar-item">

      <!-- MAIN -->
      <li><span class="sidebar-section-label">Main</span></li>

      <li>
        <a href="dashboard" class="sidebar-link <?= nav_active('dashboard', $current) ?>">
          <i class="bi bi-speedometer2"></i>
          <span class="link-text">Dashboard</span>
        </a>
      </li>

      <!-- USERS -->
      <li><span class="sidebar-section-label">Users</span></li>

      <li>
        <a href="all_user" class="sidebar-link <?= nav_active('all_user', $current) ?>">
          <i class="bi bi-people"></i>
          <span class="link-text">All Users</span>
        </a>
      </li>

      <li>
        <a href="find_user" class="sidebar-link <?= nav_active('find_user', $current) ?>">
          <i class="bi bi-search"></i>
          <span class="link-text">Find User</span>
        </a>
      </li>

      <li>
        <a href="block_user" class="sidebar-link <?= nav_active('block_user', $current) ?>">
          <i class="bi bi-person-slash"></i>
          <span class="link-text">Blocked Users</span>
        </a>
      </li>

      <li>
        <a href="admins" class="sidebar-link <?= nav_active('admins', $current) ?>">
          <i class="bi bi-shield-check"></i>
          <span class="link-text">Admins</span>
        </a>
      </li>

      <!-- FINANCES -->
      <li><span class="sidebar-section-label">Finances</span></li>

      <li>
        <a href="transactions" class="sidebar-link <?= nav_active('transactions', $current) ?>">
          <i class="bi bi-credit-card"></i>
          <span class="link-text">Transactions</span>
        </a>
      </li>

      <li>
        <a href="admin_manual_payments" class="sidebar-link <?= nav_active('admin_manual_payments', $current) ?>">
          <i class="bi bi-cash-stack"></i>
          <span class="link-text">Manual Payments</span>
        </a>
      </li>

      <li>
        <a href="admin_bank_settings" class="sidebar-link <?= nav_active('admin_bank_settings', $current) ?>">
          <i class="bi bi-bank"></i>
          <span class="link-text">Bank Settings</span>
        </a>
      </li>

      <!-- NUMBERS -->
      <li><span class="sidebar-section-label">Numbers</span></li>

      <li>
        <a href="today_otp" class="sidebar-link <?= nav_active('today_otp', $current) ?>">
          <i class="bi bi-clock-history"></i>
          <span class="link-text">Number History</span>
        </a>
      </li>

      <li>
        <a href="number-wait" class="sidebar-link <?= nav_active('number-wait', $current) ?>">
          <i class="bi bi-x-circle"></i>
          <span class="link-text">Cancelled Numbers</span>
        </a>
      </li>

      <li>
        <a href="top" class="sidebar-link <?= nav_active('top', $current) ?>">
          <i class="bi bi-bar-chart-line"></i>
          <span class="link-text">Sell History</span>
        </a>
      </li>

      <li>
        <a href="top_service" class="sidebar-link <?= nav_active(['top_service','add_top_service'], $current) ?>">
          <i class="bi bi-star"></i>
          <span class="link-text">Top Services</span>
        </a>
      </li>

      <!-- CONFIGURATION -->
      <li><span class="sidebar-section-label">Config</span></li>

      <li>
        <a href="show_api" class="sidebar-link <?= nav_active(['show_api','add_api','edit_api'], $current) ?>">
          <i class="bi bi-plug"></i>
          <span class="link-text">API Providers</span>
        </a>
      </li>

      <li>
        <a href="show_server" class="sidebar-link <?= nav_active(['show_server','add_server','edit_server'], $current) ?>">
          <i class="bi bi-server"></i>
          <span class="link-text">OTP Servers</span>
        </a>
      </li>

      <li>
        <a href="show_service" class="sidebar-link <?= nav_active(['show_service','view_service','add_service','edit_service'], $current) ?>">
          <i class="bi bi-grid"></i>
          <span class="link-text">Services</span>
        </a>
      </li>

      <li>
        <a href="promocode" class="sidebar-link <?= nav_active(['promocode','add_promocode'], $current) ?>">
          <i class="bi bi-tags"></i>
          <span class="link-text">Promo Codes</span>
        </a>
      </li>

      <li>
        <a href="custom_price" class="sidebar-link <?= nav_active(['custom_price','add_custom_price'], $current) ?>">
          <i class="bi bi-percent"></i>
          <span class="link-text">Custom Prices</span>
        </a>
      </li>

    </ul>
  </div>

  <!-- Version -->
  <div class="sidebar-version">
    <span class="link-text">v3.0 — SmsByOg Admin</span>
  </div>

</nav>
