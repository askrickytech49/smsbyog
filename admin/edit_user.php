<?php
include("auth.php");
if(!isset($_SESSION['token'])){
	if(isset($_COOKIE['remember_me'])) {
		$radium_token = $_COOKIE['remember_me'];
		$_SESSION['token'] = $radium_token;
	}else{
	header('Location: login.php'); exit;
	}
}

$admin_sql = mysqli_query($conn,"SELECT * FROM login_token WHERE token='".$_SESSION['token']."'");
if(mysqli_num_rows($admin_sql) == 0) {
    header('Location: login.php'); exit;
}else{
$admin_data = mysqli_fetch_array($admin_sql);
$admin_sql2 = mysqli_query($conn,"SELECT * FROM user_data WHERE  id='".$admin_data['user_id']."' AND status='1'");
$final_admin = mysqli_fetch_array($admin_sql2);

if(in_array($final_admin['type'], ["admin", "super_admin"])){

if($_GET['user_id']==""){
echo"invalid id";
return;

}else{
$user_id = $_GET['user_id'];
}

/* LOAD WALLET FIRST */
$sql2=mysqli_query($conn,"SELECT * FROM user_wallet WHERE user_id='".$user_id."'");
$user_wallet = mysqli_fetch_assoc($sql2);

/* =========================
DIRECT UPDATE HANDLER
========================= */

if(isset($_POST['submit_update']) && isset($_POST['balance'])){

$balance     = isset($_POST['balance']) ? $_POST['balance'] : $user_wallet['balance'];
$recharge    = isset($_POST['recharge']) ? $_POST['recharge'] : $user_wallet['total_recharge'];
$total_otp   = isset($_POST['total_otp']) ? $_POST['total_otp'] : $user_wallet['total_otp'];
$total_sms   = isset($_POST['total_sms']) ? $_POST['total_sms'] : $user_wallet['total_sms'];

    // OPTIONAL PASSWORD UPDATE
    if(!empty($_POST['new_password']) && !empty($_POST['confirm_password'])){
        if($_POST['new_password'] === $_POST['confirm_password']){
            $hashed_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            mysqli_query($conn,"UPDATE user_data SET password='$hashed_password' WHERE id='$user_id'");
        }
    }

    mysqli_query($conn,"
        UPDATE user_wallet 
        SET 
            balance = '$balance',
            total_recharge = '$recharge',
            total_otp = '$total_otp',
            total_sms = '$total_sms'
        WHERE user_id = '$user_id'
    ");
}

$sql=mysqli_query($conn,"SELECT * FROM user_data WHERE id='".$user_id."'");
if(mysqli_num_rows($sql)==0){
echo"invalid id";
return;
}
$user_data = mysqli_fetch_assoc($sql);

$sql000 = mysqli_query($conn, "SELECT *, SUM(service_price) AS total_amount FROM active_number WHERE user_id = '".$user_id."' AND status = '1'");
$final_admin0 = mysqli_fetch_assoc($sql000);

if(isset($_GET['update_status'])){
if($user_data['status'] ==1){
  mysqli_query($conn,"UPDATE user_data SET status='2' WHERE id='".$user_id."'");
}else{
  mysqli_query($conn,"UPDATE user_data SET status='1' WHERE id='".$user_id."'");
}
}
/* MAKE ADMIN */
if(isset($_GET['make_admin'])){
    mysqli_query($conn,"UPDATE user_data SET type='admin' WHERE id='".$user_id."'");
    header("Location: edit_user.php?user_id=".$user_id."&admin=1");
    exit;
}

// $sql2=mysqli_query($conn,"SELECT * FROM user_wallet WHERE user_id='".$user_id."'");
// $user_wallet = mysqli_fetch_assoc($sql2);
if(isset($_GET['funded'])){
    $show_success = true;
}
if(isset($_GET['admin'])){
    $show_admin = true;
}
if(isset($_GET['deducted'])){
    $show_deduct = true;
}

if(isset($_GET['deduct_error'])){
    $show_deduct_error = true;
}


function generateTxnId($length = 12) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $txn = '';
    for ($i = 0; $i < $length; $i++) {
        $txn .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $txn;
}

/* MANUAL FUNDING */
if(isset($_POST['fund_wallet'])){

    $amount = floatval($_POST['fund_amount']);
    $note   = mysqli_real_escape_string($conn,$_POST['fund_note']);

    if($amount > 0){

        $txn_id = generateTxnId();

        mysqli_query($conn,"
            UPDATE user_wallet 
            SET balance = balance + '$amount',
                total_recharge = total_recharge + '$amount'
            WHERE user_id = '$user_id'
        ");

        mysqli_query($conn,"
            INSERT INTO user_transaction
            (user_id, txn_id, amount, type, status, date, admin_note)
            VALUES
            ('$user_id','$txn_id','$amount','AdminFund','1',NOW(),'$note')
        ");

        header("Location: edit_user.php?user_id=".$user_id."&funded=1");
        exit;
    }
}
/* DEDUCT WALLET */
if(isset($_POST['deduct_wallet'])){

    $amount = floatval($_POST['deduct_amount']);
    $note   = mysqli_real_escape_string($conn,$_POST['deduct_note']);

    if($amount > 0){

        // prevent negative balance
        if($user_wallet['balance'] < $amount){
            header("Location: edit_user.php?user_id=".$user_id."&deduct_error=1");
            exit;
        }

        $txn_id = generateTxnId();

       mysqli_query($conn,"
UPDATE user_wallet 
SET 
balance = balance - '$amount',
total_recharge = total_recharge,
total_otp = total_otp,
total_sms = total_sms
WHERE user_id = '$user_id'
");

        mysqli_query($conn,"
            INSERT INTO user_transaction
            (user_id, txn_id, amount, type, status, date, admin_note)
            VALUES
            ('$user_id','$txn_id','$amount','AdminDebit','1',NOW(),'$note')
        ");

        header("Location: edit_user.php?user_id=".$user_id."&deducted=1");
        exit;
    }
}

if($user_data['status'] ==1){
$ban_status = "Block User";
$ban_class2 = "btn-success";
}else{
$ban_status = "Unblock User";
$ban_class2 = "btn-danger";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Edit User - @getallscripts</title>
<?php include("include/head.php"); ?>  
</head>

<body id="page-top">
<div id="wrapper">
<?php include ("include/slidebar.php"); ?>
<div id="content-wrapper" class="d-flex flex-column">
<div id="content">
<?php include ("include/topbar.php"); ?>              

<div class="container-fluid" id="container-wrapper">
<div class="row">
<div class="col">
<div class="card mb-4" id="loading">
<div class="card-header py-3">
<h6 class="m-0 font-weight-bold text-primary">User Details</h6>
</div>
<div class="card-body">

    <form method="POST">
        <input type="hidden" name="submit_update" value="1">

        <div class="form-group">
            <label>User Email (Not Editable)</label>
            <input type="email" class="form-control" value="<?php echo $user_data['email'];?>" readonly>
        </div>

        <div class="form-group">
            <label>Total Purchase Amount</label>
            <input type="text" class="form-control" value="₦<?php echo $final_admin0['total_amount'];?>" readonly>
        </div>

        <div class="form-group">
            <label>Balance</label>
            <input type="number" step="0.01" class="form-control" name="balance" value="<?php echo $user_wallet['balance'];?>">
        </div>

        <div class="form-group">
            <label>Total Recharge</label>
            <input type="number" step="0.01" class="form-control" name="recharge" value="<?php echo $user_wallet['total_recharge'];?>">
        </div>

        <div class="form-group">
            <label>Total OTP Buy</label>
            <input type="number" class="form-control" name="total_otp" value="<?php echo $user_wallet['total_otp'];?>">
        </div>

        <div class="form-group">
            <label>Total SMS Count</label>
            <input type="number" class="form-control" name="total_sms" value="<?php echo $user_wallet['total_sms'];?>">
        </div>

        <hr>

        <div class="form-group">
            <label>New Password (Optional)</label>
            <input type="password" class="form-control" name="new_password" placeholder="Leave empty to keep current password">
        </div>

        <div class="form-group">
            <label>Confirm New Password</label>
            <input type="password" class="form-control" name="confirm_password">
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-4">Update User Details</button>
    </form>

    <hr>

    <h6 class="font-weight-bold">Manual Wallet Funding</h6>
    <form method="POST">
        <input type="hidden" name="fund_wallet" value="1">

        <div class="form-group">
            <label>Amount to Add</label>
            <input type="number" step="0.01" class="form-control" name="fund_amount" required>
        </div>

        <div class="form-group">
            <label>Description</label>
            <input type="text" class="form-control" name="fund_note" placeholder="Manual Admin Funding">
        </div>

        <button type="submit" class="btn btn-success w-100 mb-4" onclick="this.disabled=true;this.form.submit();">
            Fund User Wallet
        </button>
    </form>

    <hr>

    <h6 class="font-weight-bold text-danger">Deduct Wallet Balance</h6>
    <form method="POST">
        <input type="hidden" name="deduct_wallet" value="1">

        <div class="form-group">
            <label>Amount to Deduct</label>
            <input type="number" step="0.01" class="form-control" name="deduct_amount" required>
        </div>

        <div class="form-group">
            <label>Reason</label>
            <input type="text" class="form-control" name="deduct_note" placeholder="Admin deduction">
        </div>

        <button type="submit" class="btn btn-danger w-100 mb-4" onclick="this.disabled=true;this.form.submit();">
            Deduct From Wallet
        </button>
    </form>

    <hr>

    <a href="login_user?user_id=<?php echo $user_id;?>" target="_blank">
        <button type="button" class="btn btn-success w-100 mb-2">Login As User</button>
    </a>

    <form method="get">
        <input type="hidden" name="user_id" value="<?php echo $user_id;?>">
        <button type="submit" name="update_status" value="update" class="btn <?php echo $ban_class2; ?> w-100 mb-2">
            <?php echo $ban_status;?>
        </button>
    </form>

    <form method="get">
        <input type="hidden" name="user_id" value="<?php echo $user_id;?>">
        <button type="submit" name="make_admin" value="1" class="btn btn-warning w-100 mb-2">
            Make Admin
        </button>
    </form>

</div>

</div>
</div>
</div>

<?php include("include/copyright.php"); ?>
</div>
</div>

<?php include("include/script.php"); ?>

<script>
$(document).ready(function() {
    $("#update").click(function() {
        Notiflix.Block.Dots('#loading', 'Please Wait');
    });
});
</script>
<script>
<?php if(isset($show_success)){ ?>
Notiflix.Notify.Success('Wallet funded successfully');
<?php } ?>
</script>
<script>
<?php if(isset($show_admin)){ ?>
Notiflix.Notify.Success('User promoted to Admin');
<?php } ?>
</script>

<script>
<?php if(isset($show_deduct)){ ?>
Notiflix.Notify.Success('Balance deducted successfully');
<?php } ?>

<?php if(isset($show_deduct_error)){ ?>
Notiflix.Notify.Failure('User balance is too low');
<?php } ?>
</script>

</body>
</html>
<?php
}else{
header('Location: login.php'); exit;
}
}
mysqli_close($conn);
?>
