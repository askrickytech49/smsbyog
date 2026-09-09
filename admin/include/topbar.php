<?php
// Get admin info from session token
$admin_name  = 'Admin';
$admin_email = '';
if (!empty($_SESSION['token'])) {
    $tk  = mysqli_real_escape_string($conn, $_SESSION['token']);
    $tkq = mysqli_query($conn, "SELECT u.name, u.email FROM login_token lt JOIN user_data u ON lt.user_id=u.id WHERE lt.token='$tk' AND lt.status='1' LIMIT 1");
    if ($tkq && mysqli_num_rows($tkq)) {
        $tkr = mysqli_fetch_assoc($tkq);
        $admin_name  = $tkr['name']  ?? 'Admin';
        $admin_email = $tkr['email'] ?? '';
    }
}
$initials = strtoupper(substr($admin_name, 0, 1));
?>
<header id="adminTopbar">

  <!-- Sidebar toggle (hamburger) -->
  <button id="sidebarToggle" class="topbar-toggle" title="Toggle sidebar">
    <i class="bi bi-list"></i>
  </button>

  <!-- Desktop collapse toggle -->
  <button id="sidebarCollapseBtn" class="topbar-toggle d-none d-lg-inline-flex" title="Collapse sidebar">
    <i class="bi bi-layout-sidebar"></i>
  </button>

  <!-- Page title (set per page via #pageTitle span or use a default) -->
  <div class="topbar-title" id="pageTitle">
    <?= isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard' ?>
  </div>

  <!-- Right side actions -->
  <div class="d-flex align-items-center gap-3">

    <!-- Back to site -->
    <a href="../dashboard" class="btn btn-sm btn-light-action d-none d-md-inline-flex align-items-center gap-1" target="_blank">
      <i class="bi bi-box-arrow-up-right"></i>
      <span>View Site</span>
    </a>

    <!-- Admin avatar dropdown -->
    <div class="dropdown">
      <button class="d-flex align-items-center gap-2 btn p-0 border-0 bg-transparent" data-bs-toggle="dropdown">
        <div style="width:36px;height:36px;border-radius:50%;background:var(--red-soft);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:var(--red);">
          <?= $initials ?>
        </div>
        <span class="topbar-admin-label d-none d-md-block"><?= htmlspecialchars($admin_name) ?></span>
        <i class="bi bi-chevron-down" style="font-size:11px;color:var(--text-muted);"></i>
      </button>
      <ul class="dropdown-menu dropdown-menu-end shadow" style="min-width:200px;border-radius:12px;border:1px solid var(--border);font-size:13px;">
        <li>
          <div class="px-3 py-2 border-bottom">
            <div class="fw-600" style="font-size:13px;"><?= htmlspecialchars($admin_name) ?></div>
            <div style="font-size:12px;color:var(--text-muted);"><?= htmlspecialchars($admin_email) ?></div>
          </div>
        </li>
        <li>
          <a class="dropdown-item py-2" href="../dashboard">
            <i class="bi bi-box-arrow-up-right me-2"></i> View Site
          </a>
        </li>
        <li><hr class="dropdown-divider my-1"></li>
        <li>
          <a class="dropdown-item py-2 text-danger" href="../logout">
            <i class="bi bi-box-arrow-right me-2"></i> Logout
          </a>
        </li>
      </ul>
    </div>

  </div>
</header>
