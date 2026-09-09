<?php
include("auth.php");
if(!isset($_SESSION['token'])){ if(isset($_COOKIE['remember_me'])){$_SESSION['token']=$_COOKIE['remember_me'];}else{header('Location: login.php');exit;} }
$aq=mysqli_query($conn,"SELECT * FROM login_token WHERE token='".$_SESSION['token']."'");
if(mysqli_num_rows($aq)==0){header('Location: login.php');exit;}
$ad=mysqli_fetch_array($aq); $au=mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM user_data WHERE id='".$ad['user_id']."' AND status='1'"));
if(!in_array($au['type'],["admin","super_admin"])){header('Location: login.php');exit;}

if(isset($_POST['delete'])){ $did=(int)$_POST['id']; mysqli_query($conn,"DELETE FROM api_detail WHERE id='$did'"); header('Location: show_api'); exit; }

$sql=mysqli_query($conn,"SELECT * FROM api_detail ORDER BY id ASC");
$USD_TO_NGN=1500;
$page_title='API Providers';
?>
<?php include __DIR__.'/include/layout_start.php'; ?>
<div class="page-header">
  <div>
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li><li class="breadcrumb-item active">API Providers</li></ol></nav>
  </div>
  <a href="add_api" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add API</a>
</div>
<div class="row g-4">
<?php
$http_code = 0;
while($data=mysqli_fetch_assoc($sql)):
  $bal='<span style="color:var(--text-muted);font-size:12px">Checking...</span>';

  if($data['id']=='2'){
    $ch=curl_init('https://5sim.net/v1/user/profile');
    curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>8,CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_HTTPHEADER=>['Authorization: Bearer '.trim($data['api_key']),'Accept: application/json']]);
    $r=curl_exec($ch); $http_code=curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch);
    $p=json_decode($r,true);
    if($http_code===200&&isset($p['balance'])){
      $u=(float)$p['balance']; $n=$u*$USD_TO_NGN;
      $bal='<div style="font-size:15px;font-weight:700">$'.number_format($u,2).'</div><div style="font-size:12px;color:var(--text-muted)">₦'.number_format($n,2).'</div>';
    } else { $bal='<span style="color:var(--danger);font-size:12px">'.htmlspecialchars($p['message']??'Error '.$http_code).'</span>'; }
  }
  elseif($data['id']=='1'){
    $url=rtrim($data['api_url'],'/').'  /api/balance';
    $ch=curl_init(rtrim($data['api_url'],'/').'/api/balance');
    curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>8,CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_HTTPHEADER=>['API-KEY: '.trim($data['api_key']),'Accept: application/json']]);
    $r=curl_exec($ch); $http_code=curl_getinfo($ch,CURLINFO_HTTP_CODE); $cerr=curl_error($ch); curl_close($ch);
    if($cerr){ $bal='<span style="color:var(--danger);font-size:12px">'.htmlspecialchars($cerr).'</span>'; }
    elseif($http_code==200&&is_numeric(trim($r))){ $u=(float)trim($r); $n=$u*$USD_TO_NGN; $bal='<div style="font-size:15px;font-weight:700">$'.number_format($u,2).'</div><div style="font-size:12px;color:var(--text-muted)">₦'.number_format($n,2).'</div>'; }
    else{ $j=json_decode($r,true); $bv=$j['balance']??null; if($bv!==null){ $u=(float)$bv; $n=$u*$USD_TO_NGN; $bal='<div style="font-size:15px;font-weight:700">$'.number_format($u,2).'</div><div style="font-size:12px;color:var(--text-muted)">₦'.number_format($n,2).'</div>'; } else{ $bal='<span style="color:var(--danger);font-size:12px">HTTP '.$http_code.'</span>'; } }
  }
  elseif($data['id']=='3'){
    $endpoints=[rtrim($data['api_url'],'/').'  /account/balance',rtrim($data['api_url'],'/').'/sms-otp/balance',rtrim($data['api_url'],'/').'/me/balance'];
    foreach($endpoints as $ep){
      $ch=curl_init(trim($ep)); curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>6,CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_HTTPHEADER=>['X-API-Key: '.trim($data['api_key']),'Accept: application/json']]);
      $r=curl_exec($ch); $http_code=curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch);
      $j=json_decode($r,true); $bv=$j['balance']??$j['available_balance']??$j['wallet_balance']??null;
      if($http_code==200&&$bv!==null){ $u=(float)$bv; $n=$u*$USD_TO_NGN; $bal='<div style="font-size:15px;font-weight:700">$'.number_format($u,2).'</div><div style="font-size:12px;color:var(--text-muted)">₦'.number_format($n,2).'</div>'; break; }
      else{ $bal='<span style="color:var(--warning);font-size:12px">Not Supported</span>'; }
    }
  }
  else{
    $url=rtrim($data['api_url'],'/').'/stubs/handler_api.php?api_key='.urlencode(trim($data['api_key'])).'&action=getBalance';
    $ch=curl_init($url); curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>8,CURLOPT_SSL_VERIFYPEER=>false]);
    $r=curl_exec($ch); $http_code=curl_getinfo($ch,CURLINFO_HTTP_CODE); $cerr=curl_error($ch); curl_close($ch);
    if($cerr){ $bal='<span style="color:var(--danger);font-size:12px">'.htmlspecialchars($cerr).'</span>'; }
    else{ $resp=explode(':',trim($r)); if(isset($resp[0])&&$resp[0]==='ACCESS_BALANCE'&&isset($resp[1])&&is_numeric(trim($resp[1]))){ $u=(float)trim($resp[1]); $n=$u*$USD_TO_NGN; $bal='<div style="font-size:15px;font-weight:700">$'.number_format($u,2).'</div><div style="font-size:12px;color:var(--text-muted)">₦'.number_format($n,2).'</div>'; } else{ $bal='<span style="color:var(--danger);font-size:12px">'.htmlspecialchars(substr($r,0,60)).'</span>'; } }
  }
?>
<div class="col-12 col-md-6">
  <div class="admin-card h-100" id="api-card-<?=$data['id']?>" style="<?=$data['is_active']?'':'opacity:.6;'?>">
    <div class="admin-card-header">
      <h6><i class="bi bi-plug me-2 text-red"></i><?=htmlspecialchars($data['api_name'])?></h6>
      <div class="d-flex align-items-center gap-2">
        <!-- Toggle switch -->
        <div class="form-check form-switch mb-0" title="<?=$data['is_active']?'Disable API':'Enable API'?>">
          <input class="form-check-input api-toggle"
                 type="checkbox"
                 role="switch"
                 data-id="<?=$data['id']?>"
                 <?=$data['is_active']?'checked':''?>
                 style="width:40px;height:22px;cursor:pointer;">
        </div>
        <a href="edit_api?id=<?=$data['id']?>" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i></a>
        <form method="post" style="display:inline" onsubmit="return confirm('Delete this API?')">
          <input type="hidden" name="id" value="<?=$data['id']?>">
          <button class="btn btn-sm btn-outline-danger" name="delete"><i class="bi bi-trash"></i></button>
        </form>
      </div>
    </div>
    <div class="admin-card-body">
      <div class="row g-3">
        <div class="col-6">
          <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Balance</div>
          <?=$bal?>
        </div>
        <div class="col-6">
          <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Rate</div>
          <div style="font-size:13px;font-weight:600">₦<?=number_format($data['rate']??0)?> / $1</div>
          <div style="font-size:12px;color:var(--text-muted)">+₦<?=number_format($data['profit_amount']??0)?> profit</div>
        </div>
        <div class="col-12">
          <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">API URL</div>
          <code style="font-size:12px;background:var(--bg);padding:4px 8px;border-radius:6px;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?=htmlspecialchars($data['api_url'])?></code>
        </div>
        <div class="col-12">
          <div style="font-size:11px;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">API Key</div>
          <code style="font-size:11px;background:var(--bg);padding:4px 8px;border-radius:6px;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?=htmlspecialchars(substr($data['api_key'],0,40))?>...</code>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endwhile; ?>
</div>
<script>
document.querySelectorAll('.api-toggle').forEach(function(toggle) {
    toggle.addEventListener('change', function() {
        const id      = this.dataset.id;
        const card    = document.getElementById('api-card-' + id);
        const checked = this.checked;
        this.disabled = true;

        fetch('ajax/toggle_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + encodeURIComponent(id)
        })
        .then(r => r.json())
        .then(data => {
            this.disabled = false;
            if (data.success) {
                card.style.opacity = data.is_active ? '1' : '0.6';
                // Toast notification
                const toast = document.createElement('div');
                toast.style.cssText = 'position:fixed;bottom:20px;right:20px;background:' + (data.is_active?'#16a34a':'#dc2626') + ';color:#fff;padding:10px 18px;border-radius:10px;font-size:13px;font-weight:600;z-index:9999;font-family:var(--font,Poppins)';
                toast.textContent = data.message;
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 3000);
            } else {
                // Revert toggle on error
                this.checked = !checked;
                alert(data.message || 'Toggle failed.');
            }
        })
        .catch(() => {
            this.disabled = false;
            this.checked = !checked;
            alert('Network error. Please try again.');
        });
    });
});
</script>
<?php include __DIR__.'/include/layout_end.php'; ?>
