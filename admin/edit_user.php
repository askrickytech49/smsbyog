<?php
include("auth.php");
if(!isset($_SESSION['token'])){ if(isset($_COOKIE['remember_me'])){$_SESSION['token']=$_COOKIE['remember_me'];}else{header('Location: login.php');exit;} }
$aq=mysqli_query($conn,"SELECT * FROM login_token WHERE token='".$_SESSION['token']."'");
if(mysqli_num_rows($aq)==0){header('Location: login.php');exit;}
$ad=mysqli_fetch_array($aq); $au=mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM user_data WHERE id='".$ad['user_id']."' AND status='1'"));
if(!in_array($au['type'],["admin","super_admin"])){header('Location: login.php');exit;}

$uid=(int)($_GET['user_id']??0);
if(!$uid){ header('Location: all_user'); exit; }

$user=mysqli_fetch_assoc(mysqli_query($conn,"SELECT u.*,w.balance,w.total_recharge,w.total_otp FROM user_data u LEFT JOIN user_wallet w ON u.id=w.user_id WHERE u.id='$uid'"));
if(!$user){ header('Location: all_user'); exit; }

$msg=''; $msg_type='';

// Fund wallet
if(isset($_POST['fund_wallet'])){
    $amt=(float)($_POST['fund_amount']??0);
    if($amt>0){
        mysqli_query($conn,"UPDATE user_wallet SET balance=balance+$amt,total_recharge=total_recharge+$amt WHERE user_id='$uid'");
        $ref='ADM'.time();
        mysqli_query($conn,"INSERT INTO user_transaction(user_id,txn_id,amount,type,status,date) VALUES('$uid','$ref','$amt','Admin Fund','1',NOW())");
        $msg="₦".number_format($amt)." credited successfully."; $msg_type='success';
    }
}

// Deduct wallet
if(isset($_POST['deduct_wallet'])){
    $amt=(float)($_POST['deduct_amount']??0);
    if($amt>0){
        mysqli_query($conn,"UPDATE user_wallet SET balance=balance-$amt WHERE user_id='$uid'");
        $msg="₦".number_format($amt)." deducted."; $msg_type='warning';
    }
}

// Update password
if(isset($_POST['update_password'])){
    $pw=trim($_POST['new_password']??'');
    if(strlen($pw)>=6){
        $hash=password_hash($pw,PASSWORD_BCRYPT);
        mysqli_query($conn,"UPDATE user_data SET password='$hash' WHERE id='$uid'");
        $msg='Password updated.'; $msg_type='success';
    } else { $msg='Password must be at least 6 characters.'; $msg_type='danger'; }
}

// Block/unblock
if(isset($_POST['toggle_block'])){
    $new_status=$user['status']=='1'?'2':'1';
    mysqli_query($conn,"UPDATE user_data SET status='$new_status' WHERE id='$uid'");
    header("Location: edit_user?user_id=$uid"); exit;
}

// Make/remove admin
if(isset($_POST['toggle_admin'])){
    $new_type=$user['type']=='admin'?'user':'admin';
    mysqli_query($conn,"UPDATE user_data SET type='$new_type' WHERE id='$uid'");
    header("Location: edit_user?user_id=$uid"); exit;
}

// Refresh user data
$user=mysqli_fetch_assoc(mysqli_query($conn,"SELECT u.*,w.balance,w.total_recharge,w.total_otp FROM user_data u LEFT JOIN user_wallet w ON u.id=w.user_id WHERE u.id='$uid'"));

// Recent transactions
$txns=mysqli_query($conn,"SELECT * FROM user_transaction WHERE user_id='$uid' ORDER BY id DESC LIMIT 10");
// Recent numbers
$nums=mysqli_query($conn,"SELECT * FROM active_number WHERE user_id='$uid' ORDER BY id DESC LIMIT 10");

$page_title='Edit User — '.htmlspecialchars($user['name']??'');
?>
<?php include __DIR__.'/include/layout_start.php'; ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-person-gear me-2 text-red"></i>Edit User</h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li><li class="breadcrumb-item"><a href="all_user">All Users</a></li><li class="breadcrumb-item active"><?=htmlspecialchars($user['name']??$uid)?></li></ol></nav>
  </div>
  <a href="all_user" class="btn btn-light-action"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<?php if($msg): ?><div class="alert alert-<?=$msg_type?> mb-3"><?=$msg?></div><?php endif; ?>

<div class="row g-4">
  <!-- Left: User info + quick stats -->
  <div class="col-12 col-lg-4">

    <!-- Profile card -->
    <div class="admin-card mb-4">
      <div class="admin-card-body text-center py-4">
        <div class="user-avatar mx-auto mb-3" style="width:64px;height:64px;font-size:24px"><?=strtoupper(substr($user['name']??'U',0,1))?></div>
        <div style="font-size:16px;font-weight:700"><?=htmlspecialchars($user['name']??'-')?></div>
        <div style="font-size:13px;color:var(--text-muted)"><?=htmlspecialchars($user['email'])?></div>
        <div class="mt-2">
          <span class="status-badge <?=$user['status']=='1'?'badge-active':'badge-blocked'?>"><?=$user['status']=='1'?'Active':'Blocked'?></span>
          <?php $type_class = ($user['type']=='admin'||$user['type']=='super_admin')?'badge-pending':''; ?>
          <span class="status-badge ms-1 <?=$type_class?>" style="background:rgba(2,132,199,.1);color:var(--info)"><?=htmlspecialchars($user['type'])?></span>
        </div>
      </div>
      <div class="row g-0 text-center border-top">
        <div class="col-4 py-3 border-end">
          <div style="font-size:16px;font-weight:800">₦<?=number_format($user['balance']??0)?></div>
          <div style="font-size:11px;color:var(--text-muted)">Balance</div>
        </div>
        <div class="col-4 py-3 border-end">
          <div style="font-size:16px;font-weight:800">₦<?=number_format($user['total_recharge']??0)?></div>
          <div style="font-size:11px;color:var(--text-muted)">Recharged</div>
        </div>
        <div class="col-4 py-3">
          <div style="font-size:16px;font-weight:800"><?=number_format($user['total_otp']??0)?></div>
          <div style="font-size:11px;color:var(--text-muted)">OTP</div>
        </div>
      </div>
    </div>

    <!-- Quick actions -->
    <div class="admin-card mb-4">
      <div class="admin-card-header"><h6>Quick Actions</h6></div>
      <div class="admin-card-body d-flex flex-column gap-2">
        <form method="post">
          <button name="toggle_block" class="btn w-100 <?=$user['status']=='1'?'btn-outline-danger':'btn-success'?>" onclick="return confirm('Are you sure?')">
            <i class="bi <?=$user['status']=='1'?'bi-person-slash':'bi-person-check'?> me-2"></i>
            <?=$user['status']=='1'?'Block User':'Unblock User'?>
          </button>
        </form>
        <?php if($user['type']!='super_admin'): ?>
        <form method="post">
          <button name="toggle_admin" class="btn w-100 btn-outline-primary" onclick="return confirm('Are you sure?')">
            <i class="bi <?=$user['type']=='admin'?'bi-person-dash':'bi-shield-plus'?> me-2"></i>
            <?=$user['type']=='admin'?'Remove Admin':'Make Admin'?>
          </button>
        </form>
        <?php endif; ?>
        <a href="login_user?user_id=<?=$uid?>" class="btn btn-outline-secondary" onclick="return confirm('Login as this user?')">
          <i class="bi bi-box-arrow-in-right me-2"></i>Login As User
        </a>
      </div>
    </div>

  </div>

  <!-- Right: Edit forms -->
  <div class="col-12 col-lg-8">

    <!-- Fund / Deduct wallet -->
    <div class="admin-card mb-4">
      <div class="admin-card-header"><h6><i class="bi bi-wallet2 me-2 text-red"></i>Wallet Management</h6></div>
      <div class="admin-card-body">
        <div class="row g-3">
          <div class="col-12 col-sm-6">
            <form method="post">
              <label class="form-label">Fund Wallet (₦)</label>
              <div class="input-group">
                <input type="number" name="fund_amount" class="form-control" placeholder="0.00" min="1" step="0.01" required>
                <button class="btn btn-success" name="fund_wallet" type="submit"><i class="bi bi-plus-lg"></i></button>
              </div>
            </form>
          </div>
          <div class="col-12 col-sm-6">
            <form method="post">
              <label class="form-label">Deduct Wallet (₦)</label>
              <div class="input-group">
                <input type="number" name="deduct_amount" class="form-control" placeholder="0.00" min="1" step="0.01" required>
                <button class="btn btn-outline-danger" name="deduct_wallet" type="submit" onclick="return confirm('Deduct this amount?')"><i class="bi bi-dash-lg"></i></button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Change password -->
    <div class="admin-card mb-4">
      <div class="admin-card-header"><h6><i class="bi bi-key me-2 text-red"></i>Change Password</h6></div>
      <div class="admin-card-body">
        <form method="post" class="d-flex gap-2">
          <input type="password" name="new_password" class="form-control" placeholder="New password (min 6 chars)" required>
          <button class="btn btn-primary flex-shrink-0" name="update_password"><i class="bi bi-floppy me-1"></i>Save</button>
        </form>
      </div>
    </div>

    <!-- Recent transactions -->
    <div class="admin-card mb-4">
      <div class="admin-card-header"><h6><i class="bi bi-clock-history me-2 text-red"></i>Recent Transactions</h6></div>
      <div class="admin-card-body p-0">
        <div class="table-responsive">
          <table class="admin-table">
            <thead><tr><th>Amount</th><th>Type</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
            <?php while($t=mysqli_fetch_assoc($txns)):
              $ts=$t['status']==1?'badge-approved':($t['status']==-1?'badge-rejected':'badge-pending');
              $tl=$t['status']==1?'Approved':($t['status']==-1?'Rejected':'Pending'); ?>
            <tr>
              <td><strong>₦<?=number_format($t['amount']??0)?></strong></td>
              <td style="font-size:12px"><?=htmlspecialchars($t['type']??'')?></td>
              <td><span class="status-badge <?=$ts?>"><?=$tl?></span></td>
              <td style="font-size:12px;color:var(--text-muted)"><?=htmlspecialchars($t['date']??'')?></td>
            </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Recent numbers -->
    <div class="admin-card">
      <div class="admin-card-header"><h6><i class="bi bi-phone me-2 text-red"></i>Recent Numbers</h6></div>
      <div class="admin-card-body p-0">
        <div class="table-responsive">
          <table class="admin-table">
            <thead><tr><th>Number</th><th>Service</th><th>Price</th><th>OTP</th><th>Time</th></tr></thead>
            <tbody>
            <?php while($n=mysqli_fetch_assoc($nums)): ?>
            <tr>
              <td><strong>+<?=htmlspecialchars($n['number'])?></strong></td>
              <td style="font-size:12px"><?=htmlspecialchars($n['service_name']??$n['service_id'])?></td>
              <td>₦<?=number_format($n['service_price']??0)?></td>
              <td>
                <?php if($n['sms_text']): ?>
                  <code style="background:rgba(22,163,74,.1);color:var(--success);padding:2px 6px;border-radius:4px;font-size:12px"><?=htmlspecialchars($n['sms_text'])?></code>
                <?php else: ?><span style="color:var(--text-muted);font-size:12px">—</span><?php endif; ?>
              </td>
              <td style="font-size:11px;color:var(--text-muted)"><?=htmlspecialchars($n['buy_time']??'')?></td>
            </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>
<?php include __DIR__.'/include/layout_end.php'; ?>
