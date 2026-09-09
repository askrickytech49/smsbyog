<?php
include("auth.php");
if(!isset($_SESSION['token'])){ if(isset($_COOKIE['remember_me'])){$_SESSION['token']=$_COOKIE['remember_me'];}else{header('Location: login.php');exit;} }
$aq=mysqli_query($conn,"SELECT * FROM login_token WHERE token='".$_SESSION['token']."'");
if(mysqli_num_rows($aq)==0){header('Location: login.php');exit;}
$ad=mysqli_fetch_array($aq); $au=mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM user_data WHERE id='".$ad['user_id']."' AND status='1'"));
if(!in_array($au['type'],["admin","super_admin"])){header('Location: login.php');exit;}

$msg=''; $msg_type='';
if(isset($_POST['approve'])){
    $pid=(int)$_POST['id'];
    $pay=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM manual_payments WHERE id='$pid'"));
    if($pay && $pay['status']==0){
        mysqli_begin_transaction($conn);
        try{
            mysqli_query($conn,"UPDATE manual_payments SET status=1 WHERE id='$pid'");
            mysqli_query($conn,"UPDATE user_wallet SET balance=balance+{$pay['amount']},total_recharge=total_recharge+{$pay['amount']} WHERE user_id={$pay['user_id']}");
            $ref='MAN'.time();
            mysqli_query($conn,"INSERT INTO user_transaction(user_id,txn_id,amount,type,status,date) VALUES('{$pay['user_id']}','$ref','{$pay['amount']}','Manual Payment','1',NOW())");
            mysqli_commit($conn); $msg='Payment approved.'; $msg_type='success';
        }catch(Exception $e){ mysqli_rollback($conn); $msg='Error.'; $msg_type='danger'; }
    }
}
if(isset($_POST['reject'])){
    $pid=(int)$_POST['id'];
    mysqli_query($conn,"UPDATE manual_payments SET status=-1 WHERE id='$pid'");
    $msg='Payment rejected.'; $msg_type='warning';
}

$sql=mysqli_query($conn,"SELECT m.*,u.name,u.email FROM manual_payments m LEFT JOIN user_data u ON m.user_id=u.id ORDER BY m.id DESC");
$page_title='Manual Payments';
?>
<?php include __DIR__.'/include/layout_start.php'; ?>
<div class="page-header">
  <div>
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li><li class="breadcrumb-item active">Manual Payments</li></ol></nav>
  </div>
</div>
<?php if($msg): ?><div class="alert alert-<?=$msg_type?> mb-3"><?=$msg?></div><?php endif; ?>
<div class="admin-card">
  <div class="admin-card-body p-0">
    <div class="table-responsive">
      <table class="admin-table admin-datatable" style="width:100%">
        <thead><tr><th>User</th><th>Amount</th><th>Receipt</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php while($r=mysqli_fetch_assoc($sql)):
          if($r['status']==1){ $bs='badge-approved'; $bl='Approved'; }
          elseif($r['status']==-1){ $bs='badge-rejected'; $bl='Rejected'; }
          else{ $bs='badge-pending'; $bl='Pending'; }
        ?>
        <tr>
          <td>
            <div style="font-weight:600;font-size:13px"><?=htmlspecialchars($r['name']??'-')?></div>
            <div style="font-size:11px;color:var(--text-muted)"><?=htmlspecialchars($r['email']??'')?></div>
          </td>
          <td><strong>₦<?=number_format($r['amount']??0)?></strong></td>
          <td>
            <?php if(!empty($r['receipt_url'])): ?>
              <a href="<?=htmlspecialchars($r['receipt_url'])?>" target="_blank" class="btn btn-sm btn-light-action"><i class="bi bi-image me-1"></i>View</a>
            <?php else: ?>
              <span style="color:var(--text-muted);font-size:12px">None</span>
            <?php endif; ?>
          </td>
          <td style="font-size:12px;color:var(--text-muted)"><?=htmlspecialchars($r['date']??'')?></td>
          <td><span class="status-badge <?=$bs?>"><?=$bl?></span></td>
          <td>
            <?php if($r['status']==0): ?>
            <div class="d-flex gap-1">
              <form method="post" style="display:inline">
                <input type="hidden" name="id" value="<?=$r['id']?>">
                <button class="btn btn-sm btn-success" name="approve" onclick="return confirm('Approve this payment?')"><i class="bi bi-check-lg"></i></button>
              </form>
              <form method="post" style="display:inline">
                <input type="hidden" name="id" value="<?=$r['id']?>">
                <button class="btn btn-sm btn-outline-danger" name="reject" onclick="return confirm('Reject this payment?')"><i class="bi bi-x-lg"></i></button>
              </form>
            </div>
            <?php else: ?>
              <span style="font-size:12px;color:var(--text-muted)">Processed</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__.'/include/layout_end.php'; ?>
