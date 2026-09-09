<?php
include("auth.php");
if(!isset($_SESSION['token'])){ if(isset($_COOKIE['remember_me'])){$_SESSION['token']=$_COOKIE['remember_me'];}else{header('Location: login.php');exit;} }
$aq=mysqli_query($conn,"SELECT * FROM login_token WHERE token='".$_SESSION['token']."'");
if(mysqli_num_rows($aq)==0){header('Location: login.php');exit;}
$ad=mysqli_fetch_array($aq); $au=mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM user_data WHERE id='".$ad['user_id']."' AND status='1'"));
if(!in_array($au['type'],["admin","super_admin"])){header('Location: login.php');exit;}

$msg=''; $msg_type='';

// Bulk approve
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['bulk_approve']) && !empty($_POST['txn_ids'])){
    mysqli_begin_transaction($conn);
    try {
        foreach($_POST['txn_ids'] as $tid){
            $tid=mysqli_real_escape_string($conn,$tid);
            $txn=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM user_transaction WHERE txn_id='$tid' AND status=0"));
            if($txn){
                mysqli_query($conn,"UPDATE user_transaction SET status=1 WHERE txn_id='$tid'");
                mysqli_query($conn,"UPDATE user_wallet SET balance=balance+{$txn['amount']},total_recharge=total_recharge+{$txn['amount']} WHERE user_id={$txn['user_id']}");
            }
        }
        mysqli_commit($conn); $msg='Selected transactions approved.'; $msg_type='success';
    } catch(Exception $e){ mysqli_rollback($conn); $msg='Approval failed.'; $msg_type='danger'; }
}

// Bulk reject
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['bulk_reject']) && !empty($_POST['txn_ids'])){
    mysqli_begin_transaction($conn);
    try {
        foreach($_POST['txn_ids'] as $tid){
            $tid=mysqli_real_escape_string($conn,$tid);
            mysqli_query($conn,"UPDATE user_transaction SET status=-1 WHERE txn_id='$tid'");
        }
        mysqli_commit($conn); $msg='Selected transactions rejected.'; $msg_type='warning';
    } catch(Exception $e){ mysqli_rollback($conn); $msg='Rejection failed.'; $msg_type='danger'; }
}

// Save note
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save_note']) && isset($_POST['txn_id'])){
    $tid=mysqli_real_escape_string($conn,$_POST['txn_id']);
    $note=mysqli_real_escape_string($conn,$_POST['note']??'');
    mysqli_query($conn,"UPDATE user_transaction SET admin_note='$note' WHERE txn_id='$tid'");
    $msg='Note saved.'; $msg_type='success';
}

// Filter
$where='1'; $limit=50; $offset=(int)($_GET['page']??0)*$limit;
if(!empty($_GET['search'])){ $s=mysqli_real_escape_string($conn,$_GET['search']); $where.=" AND (u.email LIKE '%$s%' OR t.txn_id LIKE '%$s%')"; }
if(isset($_GET['status']) && $_GET['status']!==''){ $st=(int)$_GET['status']; $where.=" AND t.status=$st"; }
$total=mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM user_transaction t LEFT JOIN user_data u ON t.user_id=u.id WHERE $where"))[0];
$sql=mysqli_query($conn,"SELECT t.*,u.name,u.email FROM user_transaction t LEFT JOIN user_data u ON t.user_id=u.id WHERE $where ORDER BY t.id DESC LIMIT $limit OFFSET $offset");
$pages=ceil($total/$limit);
$page_title='Transactions';
?>
<?php include __DIR__.'/include/layout_start.php'; ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-credit-card me-2 text-red"></i>Transactions</h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li><li class="breadcrumb-item active">Transactions</li></ol></nav>
  </div>
</div>

<?php if($msg): ?>
<div class="alert alert-<?=$msg_type?> mb-3"><?=$msg?></div>
<?php endif; ?>

<!-- Filter bar -->
<div class="admin-card mb-3">
  <div class="admin-card-body">
    <form method="get" class="d-flex gap-2 flex-wrap align-items-end">
      <div>
        <label class="form-label">Search</label>
        <input type="text" name="search" class="form-control" placeholder="Email or Txn ID" value="<?=htmlspecialchars($_GET['search']??'')?>">
      </div>
      <div>
        <label class="form-label">Status</label>
        <select name="status" class="form-select" style="width:140px">
          <option value="">All</option>
          <option value="0" <?=($_GET['status']??'')==='0'?'selected':''?>>Pending</option>
          <option value="1" <?=($_GET['status']??'')==='1'?'selected':''?>>Approved</option>
          <option value="-1" <?=($_GET['status']??'')=='-1'?'selected':''?>>Rejected</option>
        </select>
      </div>
      <div class="d-flex gap-2 align-items-end">
        <button class="btn btn-primary" type="submit"><i class="bi bi-search me-1"></i>Filter</button>
        <a href="transactions" class="btn btn-light-action">Clear</a>
      </div>
    </form>
  </div>
</div>

<form method="post">
<div class="admin-card">
  <div class="admin-card-header">
    <h6>Transaction List <span class="text-red">(<?=$total?>)</span></h6>
    <div class="d-flex gap-2">
      <button type="submit" name="bulk_approve" class="btn btn-sm btn-success" onclick="return confirm('Approve selected?')"><i class="bi bi-check-lg me-1"></i>Approve</button>
      <button type="submit" name="bulk_reject" class="btn btn-sm btn-outline-danger" onclick="return confirm('Reject selected?')"><i class="bi bi-x-lg me-1"></i>Reject</button>
    </div>
  </div>
  <div class="admin-card-body p-0">
    <div class="table-responsive">
      <table class="admin-table" style="width:100%">
        <thead>
          <tr>
            <th><input type="checkbox" id="selectAll" class="form-check-input"></th>
            <th>User</th><th>Amount</th><th>Type</th><th>Txn ID</th><th>Date</th><th>Status</th><th>Note</th>
          </tr>
        </thead>
        <tbody>
        <?php while($r=mysqli_fetch_assoc($sql)):
          if($r['status']==1){ $bs='badge-approved'; $bl='Approved'; }
          elseif($r['status']==-1){ $bs='badge-rejected'; $bl='Rejected'; }
          else{ $bs='badge-pending'; $bl='Pending'; }
        ?>
        <tr>
          <td><?php if($r['status']==0): ?><input type="checkbox" name="txn_ids[]" value="<?=htmlspecialchars($r['txn_id'])?>" class="form-check-input row-check"><?php endif; ?></td>
          <td>
            <div style="font-size:13px;font-weight:600"><?=htmlspecialchars($r['name']??'-')?></div>
            <div style="font-size:11px;color:var(--text-muted)"><?=htmlspecialchars($r['email']??'')?></div>
          </td>
          <td><strong>₦<?=number_format($r['amount'])?></strong></td>
          <td style="font-size:12px"><?=htmlspecialchars($r['type']??'')?></td>
          <td><code style="font-size:11px;background:var(--bg);padding:2px 6px;border-radius:4px"><?=htmlspecialchars($r['txn_id']??'')?></code></td>
          <td style="font-size:12px;color:var(--text-muted)"><?=htmlspecialchars($r['date']??'')?></td>
          <td><span class="status-badge <?=$bs?>"><?=$bl?></span></td>
          <td>
            <div class="d-flex gap-1">
              <input type="hidden" name="txn_id_<?=$r['id']?>" value="<?=htmlspecialchars($r['txn_id']??' ')?>">
              <input type="text" name="note_<?=$r['id']?>" class="form-control form-control-sm" style="min-width:120px;font-size:12px" placeholder="Add note..." value="<?=htmlspecialchars($r['admin_note']??'')?>">
              <button type="submit" name="save_note" value="<?=$r['id']?>" class="btn btn-sm btn-light-action" onclick="document.querySelector('[name=txn_id]').value=document.querySelector('[name=txn_id_<?=$r['id']?>]').value;document.querySelector('[name=note]').value=document.querySelector('[name=note_<?=$r['id']?>]').value"><i class="bi bi-floppy"></i></button>
            </div>
          </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</form>

<!-- Pagination -->
<?php if($pages>1): ?>
<nav class="mt-3 d-flex justify-content-center">
  <ul class="pagination pagination-sm">
    <?php for($p=0;$p<$pages;$p++): $cur_page=(int)($_GET['page']??0); ?>
    <li class="page-item <?=$p==$cur_page?'active':''?>">
      <a class="page-link" href="?<?=http_build_query(array_merge($_GET,['page'=>$p]))?>"><?=$p+1?></a>
    </li>
    <?php endfor; ?>
  </ul>
</nav>
<?php endif; ?>

<script>
document.getElementById('selectAll')?.addEventListener('change',function(){
  document.querySelectorAll('.row-check').forEach(c=>c.checked=this.checked);
});
// Wire note save
document.querySelectorAll('[name^="note_"]').forEach(function(inp){
  const id = inp.name.replace('note_','');
  const btn = document.querySelector('[name="save_note"][value="'+id+'"]');
  if(btn){ btn.addEventListener('click',function(e){
    e.preventDefault();
    const form = this.closest('form');
    const ti = document.querySelector('[name="txn_id_'+id+'"]');
    const ni = inp;
    // Add hidden fields
    let hTxn = form.querySelector('[name="txn_id"]');
    let hNote = form.querySelector('[name="note"]');
    if(!hTxn){ hTxn=document.createElement('input'); hTxn.type='hidden'; hTxn.name='txn_id'; form.appendChild(hTxn); }
    if(!hNote){ hNote=document.createElement('input'); hNote.type='hidden'; hNote.name='note'; form.appendChild(hNote); }
    hTxn.value = ti ? ti.value : '';
    hNote.value = ni.value;
    form.submit();
  });}
});
</script>

<?php include __DIR__.'/include/layout_end.php'; ?>
