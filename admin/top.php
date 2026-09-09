<?php
include("auth.php");
if(!isset($_SESSION['token'])){ if(isset($_COOKIE['remember_me'])){$_SESSION['token']=$_COOKIE['remember_me'];}else{header('Location: login.php');exit;} }
$aq=mysqli_query($conn,"SELECT * FROM login_token WHERE token='".$_SESSION['token']."'");
if(mysqli_num_rows($aq)==0){header('Location: login.php');exit;}
$ad=mysqli_fetch_array($aq); $au=mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM user_data WHERE id='".$ad['user_id']."' AND status='1'"));
if(!in_array($au['type'],["admin","super_admin"])){header('Location: login.php');exit;}

// Date filter — default last 7 days
$date_from = isset($_GET['date_from']) && $_GET['date_from'] ? $_GET['date_from'] : date('Y-m-d', strtotime('-6 days'));
$date_to   = isset($_GET['date_to'])   && $_GET['date_to']   ? $_GET['date_to']   : date('Y-m-d');
$date_from_esc = mysqli_real_escape_string($conn, $date_from);
$date_to_esc   = mysqli_real_escape_string($conn, $date_to);

// Sales grouped by service + server in date range
$sells = mysqli_query($conn,
    "SELECT service_name, server_id, COUNT(*) as cnt, SUM(service_price) as total
     FROM active_number
     WHERE status='1'
     AND DATE(buy_time) BETWEEN '$date_from_esc' AND '$date_to_esc'
     GROUP BY service_name, server_id
     ORDER BY cnt DESC"
);

// Summary stats for range
$total_sales   = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM active_number WHERE status='1' AND DATE(buy_time) BETWEEN '$date_from_esc' AND '$date_to_esc'"))[0] ?? 0;
$total_revenue = mysqli_fetch_row(mysqli_query($conn, "SELECT IFNULL(SUM(service_price),0) FROM active_number WHERE status='1' AND DATE(buy_time) BETWEEN '$date_from_esc' AND '$date_to_esc'"))[0] ?? 0;

// Currently active users (numbers still in countdown)
$active_users = mysqli_query($conn,
    "SELECT DISTINCT u.name, u.email
     FROM active_number a JOIN user_data u ON a.user_id=u.id
     WHERE a.active_status='2'
     ORDER BY a.id DESC LIMIT 30"
);
$active_count = mysqli_num_rows($active_users);

$page_title = 'Sell History';
?>
<?php include __DIR__.'/include/layout_start.php'; ?>

<div class="page-header">
  <div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Sell History</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Summary KPIs -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="kpi-card">
      <div class="kpi-icon red"><i class="bi bi-phone-fill"></i></div>
      <div class="kpi-info">
        <div class="kpi-label">Sales in Range</div>
        <div class="kpi-value"><?=number_format($total_sales)?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="kpi-card">
      <div class="kpi-icon green"><i class="bi bi-cash-coin"></i></div>
      <div class="kpi-info">
        <div class="kpi-label">Revenue</div>
        <div class="kpi-value">₦<?=number_format($total_revenue)?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="kpi-card">
      <div class="kpi-icon blue"><i class="bi bi-activity"></i></div>
      <div class="kpi-info">
        <div class="kpi-label">Live Numbers</div>
        <div class="kpi-value"><?=number_format($active_count)?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="kpi-card">
      <div class="kpi-icon orange"><i class="bi bi-calendar-range"></i></div>
      <div class="kpi-info">
        <div class="kpi-label">Date Range</div>
        <div class="kpi-value" style="font-size:13px;font-weight:600"><?=date('M d', strtotime($date_from))?> – <?=date('M d', strtotime($date_to))?></div>
      </div>
    </div>
  </div>
</div>

<!-- Date Filter -->
<div class="admin-card mb-4">
  <div class="admin-card-body">
    <form method="get" class="d-flex gap-2 flex-wrap align-items-end">
      <div>
        <label class="form-label">From</label>
        <input type="date" name="date_from" class="form-control" value="<?=htmlspecialchars($date_from)?>">
      </div>
      <div>
        <label class="form-label">To</label>
        <input type="date" name="date_to" class="form-control" value="<?=htmlspecialchars($date_to)?>">
      </div>
      <div class="d-flex gap-2 align-items-end">
        <button class="btn btn-primary" type="submit"><i class="bi bi-search me-1"></i>Filter</button>
        <a href="top" class="btn btn-light-action">Reset</a>
      </div>
      <!-- Quick range buttons -->
      <div class="d-flex gap-1 align-items-end flex-wrap">
        <a href="top?date_from=<?=date('Y-m-d')?>&date_to=<?=date('Y-m-d')?>" class="btn btn-sm btn-light-action">Today</a>
        <a href="top?date_from=<?=date('Y-m-d',strtotime('-6 days'))?>&date_to=<?=date('Y-m-d')?>" class="btn btn-sm btn-light-action">7 Days</a>
        <a href="top?date_from=<?=date('Y-m-d',strtotime('-29 days'))?>&date_to=<?=date('Y-m-d')?>" class="btn btn-sm btn-light-action">30 Days</a>
      </div>
    </form>
  </div>
</div>

<div class="row g-4">
  <!-- Sales table -->
  <div class="col-12 col-lg-7">
    <div class="admin-card">
      <div class="admin-card-header">
        <h6><i class="bi bi-bar-chart-line me-2 text-red"></i>Sales by Service</h6>
        <span style="font-size:12px;color:var(--text-muted)"><?=htmlspecialchars($date_from)?> to <?=htmlspecialchars($date_to)?></span>
      </div>
      <div class="admin-card-body p-0">
        <div class="table-responsive">
          <table class="admin-table admin-datatable" style="width:100%">
            <thead><tr><th>Service</th><th>Server</th><th>Count</th><th>Revenue</th></tr></thead>
            <tbody>
            <?php
            $sells_rows = [];
            while($r=mysqli_fetch_assoc($sells)) $sells_rows[] = $r;
            if(empty($sells_rows)): ?>
            <tr><td colspan="4" style="text-align:center;color:var(--text-muted);padding:32px">No sales in this date range</td></tr>
            <?php else: foreach($sells_rows as $r): ?>
            <tr>
              <td><strong><?=htmlspecialchars(strip_tags($r['service_name']??'-'))?></strong></td>
              <td style="font-size:12px;color:var(--text-muted)"><?=htmlspecialchars($r['server_id']??'')?></td>
              <td><span class="status-badge badge-active"><?=number_format($r['cnt'])?></span></td>
              <td><strong>₦<?=number_format($r['total']??0)?></strong></td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Active users -->
  <div class="col-12 col-lg-5">
    <div class="admin-card">
      <div class="admin-card-header">
        <h6><i class="bi bi-circle-fill me-2" style="color:#16a34a;font-size:9px"></i>Live Active Numbers</h6>
        <span class="status-badge badge-active"><?=$active_count?> active</span>
      </div>
      <div class="admin-card-body p-0" style="max-height:420px;overflow-y:auto">
        <?php if($active_count===0): ?>
          <div style="text-align:center;padding:32px;color:var(--text-muted)">
            <i class="bi bi-moon" style="font-size:28px;display:block;margin-bottom:8px"></i>
            No active numbers right now
          </div>
        <?php else: ?>
        <table class="admin-table">
          <thead><tr><th>User</th></tr></thead>
          <tbody>
          <?php mysqli_data_seek($active_users,0); while($r=mysqli_fetch_assoc($active_users)): ?>
          <tr>
            <td>
              <div class="d-flex align-items-center gap-2">
                <div class="user-avatar"><?=strtoupper(substr($r['name']??'U',0,1))?></div>
                <div>
                  <div style="font-size:13px;font-weight:600"><?=htmlspecialchars($r['name']??'-')?></div>
                  <div style="font-size:11px;color:var(--text-muted)"><?=htmlspecialchars($r['email']??'')?></div>
                </div>
              </div>
            </td>
          </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__.'/include/layout_end.php'; ?>
