<?php
include("auth.php");
if(!isset($_SESSION['token'])){ if(isset($_COOKIE['remember_me'])){$_SESSION['token']=$_COOKIE['remember_me'];}else{header('Location: login.php');exit;} }
$aq=mysqli_query($conn,"SELECT * FROM login_token WHERE token='".$_SESSION['token']."'");
if(mysqli_num_rows($aq)==0){header('Location: login.php');exit;}
$ad=mysqli_fetch_array($aq); $au=mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM user_data WHERE id='".$ad['user_id']."' AND status='1'"));
if(!in_array($au['type'],["admin","super_admin"])){header('Location: login.php');exit;}

$msg=''; $msg_type='';
if(isset($_POST['update_bank'])){
    $bank_name    = mysqli_real_escape_string($conn,trim($_POST['bank_name']??''));
    $account_name = mysqli_real_escape_string($conn,trim($_POST['account_name']??''));
    $account_num  = mysqli_real_escape_string($conn,trim($_POST['account_number']??''));
    if(!$bank_name||!$account_name||!$account_num){ $msg='All fields are required.'; $msg_type='danger'; }
    else{
        $exists=mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM system_bank_details"))[0];
        if($exists){ mysqli_query($conn,"UPDATE system_bank_details SET bank_name='$bank_name',account_name='$account_name',account_number='$account_num'"); }
        else{ mysqli_query($conn,"INSERT INTO system_bank_details(bank_name,account_name,account_number) VALUES('$bank_name','$account_name','$account_num')"); }
        $msg='Bank details updated.'; $msg_type='success';
    }
}
$bank=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM system_bank_details LIMIT 1"))??[];
$page_title='Bank Settings';
?>
<?php include __DIR__.'/include/layout_start.php'; ?>
<div class="page-header">
  <div>
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li><li class="breadcrumb-item active">Bank Settings</li></ol></nav>
  </div>
</div>
<div class="row justify-content-center">
  <div class="col-12 col-md-7 col-lg-5">
    <?php if($msg): ?><div class="alert alert-<?=$msg_type?> mb-3"><?=$msg?></div><?php endif; ?>
    <div class="admin-card">
      <div class="admin-card-header"><h6>Manual Payment Bank Account</h6></div>
      <div class="admin-card-body">
        <form method="post">
          <div class="mb-3">
            <label class="form-label">Bank Name</label>
            <input type="text" name="bank_name" class="form-control" required value="<?=htmlspecialchars($bank['bank_name']??'')?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Account Name</label>
            <input type="text" name="account_name" class="form-control" required value="<?=htmlspecialchars($bank['account_name']??'')?>">
          </div>
          <div class="mb-4">
            <label class="form-label">Account Number</label>
            <input type="text" name="account_number" class="form-control" required value="<?=htmlspecialchars($bank['account_number']??'')?>">
          </div>
          <button type="submit" name="update_bank" class="btn btn-primary w-100"><i class="bi bi-floppy me-2"></i>Save Bank Details</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__.'/include/layout_end.php'; ?>
