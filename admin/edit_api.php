<?php
include("auth.php");
if(!isset($_SESSION['token'])){ if(isset($_COOKIE['remember_me'])){$_SESSION['token']=$_COOKIE['remember_me'];}else{header('Location: login.php');exit;} }
$aq=mysqli_query($conn,"SELECT * FROM login_token WHERE token='".$_SESSION['token']."'");
if(mysqli_num_rows($aq)==0){header('Location: login.php');exit;}
$ad=mysqli_fetch_array($aq); $au=mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM user_data WHERE id='".$ad['user_id']."' AND status='1'"));
if(!in_array($au['type'],["admin","super_admin"])){header('Location: login.php');exit;}

$id=(int)($_GET['id']??0);
$api=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM api_detail WHERE id='$id'"));
if(!$api){ header('Location: show_api'); exit; }

$msg=''; $msg_type='';
if(isset($_POST['update'])){
    $name=mysqli_real_escape_string($conn,trim($_POST['api_name']??''));
    $url =mysqli_real_escape_string($conn,trim($_POST['api_url']??''));
    $key =mysqli_real_escape_string($conn,trim($_POST['api_key']??''));
    $rate=(float)($_POST['rate']??1500);
    $profit=(float)($_POST['profit_amount']??0);
    mysqli_query($conn,"UPDATE api_detail SET api_name='$name',api_url='$url',api_key='$key',rate='$rate',profit_amount='$profit' WHERE id='$id'");
    $api=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM api_detail WHERE id='$id'"));
    $msg='API updated.'; $msg_type='success';
}
$page_title='Edit API';
?>
<?php include __DIR__.'/include/layout_start.php'; ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-plug me-2 text-red"></i>Edit API: <?=htmlspecialchars($api['api_name'])?></h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li><li class="breadcrumb-item"><a href="show_api">API Providers</a></li><li class="breadcrumb-item active">Edit</li></ol></nav>
  </div>
  <a href="show_api" class="btn btn-light-action"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>
<div class="row justify-content-center">
  <div class="col-12 col-md-8 col-lg-6">
    <?php if($msg): ?><div class="alert alert-<?=$msg_type?> mb-3"><?=$msg?></div><?php endif; ?>
    <div class="admin-card">
      <div class="admin-card-header"><h6>Edit API Details</h6></div>
      <div class="admin-card-body">
        <form method="post">
          <div class="mb-3"><label class="form-label">API Name</label><input type="text" name="api_name" class="form-control" value="<?=htmlspecialchars($api['api_name'])?>" required></div>
          <div class="mb-3"><label class="form-label">API URL</label><input type="url" name="api_url" class="form-control" value="<?=htmlspecialchars($api['api_url'])?>" required></div>
          <div class="mb-3"><label class="form-label">API Key</label><textarea name="api_key" class="form-control" rows="3" required><?=htmlspecialchars($api['api_key'])?></textarea></div>
          <div class="row g-3 mb-4">
            <div class="col-6"><label class="form-label">Rate (₦ per $1)</label><input type="number" name="rate" class="form-control" value="<?=htmlspecialchars($api['rate']??1500)?>" step="0.01"></div>
            <div class="col-6"><label class="form-label">Profit (₦)</label><input type="number" name="profit_amount" class="form-control" value="<?=htmlspecialchars($api['profit_amount']??0)?>" step="0.01"></div>
          </div>
          <button type="submit" name="update" class="btn btn-primary w-100"><i class="bi bi-floppy me-2"></i>Save Changes</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__.'/include/layout_end.php'; ?>
