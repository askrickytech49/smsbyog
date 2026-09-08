<?php
$host = 'verifysms.io';
$port = 443;
$waitTimeoutInSeconds = 2; 

if ($fp = @fsockopen($host, $port, $errCode, $errStr, $waitTimeoutInSeconds)) {   
   echo "✅ Connection to $host is OPEN on port $port";
   fclose($fp);
} else {
   echo "❌ Connection to $host is CLOSED. Error: $errStr ($errCode)";
}
include("auth.php");
if(!isset($_SESSION['token'])){
	if(isset($_COOKIE['remember_me'])) {
		$_SESSION['token'] = $_COOKIE['remember_me'];
	}else{
		header('Location: login.php'); exit;
	}
}

$admin_sql = mysqli_query($conn,"SELECT * FROM login_token WHERE token='".$_SESSION['token']."'");
if(mysqli_num_rows($admin_sql) == 0) {
	header('Location: login.php'); exit;
}

$admin_data = mysqli_fetch_array($admin_sql);
$admin_sql2 = mysqli_query(
	$conn,
	"SELECT * FROM user_data WHERE id='".$admin_data['user_id']."' AND status='1'"
);
$final_admin = mysqli_fetch_array($admin_sql2);

if(!in_array($final_admin['type'], ["admin", "super_admin"])){
	header('Location: login.php'); exit;
}

$sql = mysqli_query($conn, "SELECT * FROM api_detail ORDER BY id DESC");

/* ===== CONVERSION RATES ===== */
$RUB_TO_USD = 0.011;   // Tiger SMS (RUB → USD)
$USD_TO_NGN = 1500;    // USD → NGN
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Show Api - @getallscripts</title>
<?php include ("include/head.php"); ?>
<link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
</head>

<script>
$(document).ready(function () {
	$('#dashboard').removeClass("active");
	$("#show_api").addClass("active");
});
</script>

<body id="page-top">
<div id="wrapper">

<?php include ("include/slidebar.php"); ?>

<div id="content-wrapper" class="d-flex flex-column">
<div id="content">

<?php include ("include/topbar.php"); ?>

<div class="container-fluid" id="container-wrapper">

<div class="d-sm-flex align-items-center justify-content-between mb-4">
	<ol class="breadcrumb">
		<li class="breadcrumb-item"><a href="#">Home</a></li>
		<li class="breadcrumb-item active">Show Api</li>
	</ol>
</div>

<div class="row">
<div class="col">
<div class="card mb-4">
<div class="card-header py-3 d-flex align-items-center justify-content-between">
	<h6 class="m-0 font-weight-bold text-primary">Show Api</h6>
</div>

<div class="table-responsive p-3">

<?php
if (isset($_POST['delete'])) {
	$id = (int)$_POST['id'];
	mysqli_query($conn,"DELETE FROM api_detail WHERE id='$id'");
	echo '<div class="alert alert-success">Delete success</div>';
	echo "<meta http-equiv='refresh' content='0'>";
}
?>

<table class="table align-items-center table-flush" id="dataTable">
<thead class="thead-light">
<tr>
	<th>Api Name</th>
	<th>Api Balance</th>
	<th>Api Url</th>
	<th>Api Key</th>
	<th>Edit</th>
	<th>Actions</th>
</tr>
</thead>
<tbody>

<?php
while ($data = mysqli_fetch_array($sql)) {
if($data['id'] == '2'){
    // 5sim uses a Bearer Token in the headers and returns JSON
    $url = "https://5sim.net/v1/user/profile";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $data['api_key'],
        'Accept: application/json'
    ]);
    $response1 = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $profile = json_decode($response1, true);

    if ($httpCode === 200 && isset($profile['balance'])) {
        $bal_usd = (float)$profile['balance'];
        
        $bal_ngn = $bal_usd * $USD_TO_NGN;

        $bal = "
            <small class='text-muted'>$".number_format($bal_usd, 2)."</small><br>
            <small class='text-muted'>₦".number_format($bal_ngn, 2)."</small>
        ";
    } else {
        $bal = "<span class='text-danger'>5sim Auth Failed</span>";
    }
}elseif($data['id'] == '1'){
    $url = $data['api_url'] . "/api/balance";
$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_URL            => $url,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_SSL_VERIFYPEER => false, // Bypass SSL for testing
    CURLOPT_HTTPHEADER     => [
        "API-KEY: " . $data['api_key'],
        "User-Agent: SMM-Platform-Agent"
    ],
]);

$response_raw = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    $bal = "<span class='text-danger'>Connection Error: " . curl_error($ch) . "</span>";
} else {
    // Process response logic here...
    if ($http_code == 200 && is_numeric(trim($response_raw))) {
        
        $raw_balance = (float)trim($response_raw);
        
            // SmsVerify usually provides balance in USD
            $bal_usd = $raw_balance;
            $bal_ngn = $bal_usd * $USD_TO_NGN;
    
            $bal = "
                <strong>$".number_format($bal_usd, 2)."</strong><br>
                <small class='text-muted'>₦".number_format($bal_ngn, 2)."</small>
            ";
    } elseif ($http_code == 401) {
        $bal = "<span class='text-warning'>Invalid API Key</span>";
    }
}
}
elseif($data['id'] == '3'){
    //Dino
   $url = $data['api_url']."/me/balance";
    $rate = $data['rate'];
    $percentage = $data['percentage'];
    $response = getfunction($url, $data['api_key']);
    
   
    
    /* DEFAULT */
    $bal = "<span class='text-danger'>Invalid / Not Supported</span>";
    
    // 2. Process Response (SmsVerify returns a plain number on success)
    if (isset($response['available_balance'])) {
        
        $raw_balance = (float)trim($response['available_balance']);
        
            // SmsVerify usually provides balance in USD
            $bal_usd = $raw_balance;
            $bal_ngn = $bal_usd * $USD_TO_NGN;
    
            $bal = "
                <strong>$".number_format($bal_usd, 2)."</strong><br>
                <small class='text-muted'>₦".number_format($bal_ngn, 2)."</small>
            ";
    } elseif ($http_code == 401) {
        $bal = "<span class='text-warning'>Invalid API Key</span>";
    }
}
else{
$url = $data['api_url']."/stubs/handler_api.php?api_key=".$data['api_key']."&action=getBalance";
$rate = $data['rate'];
$percentage = $data['percentage'];
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response1 = curl_exec($ch);
curl_close($ch);

$response = explode(':', $response1);

/* DEFAULT */
$bal = "<span class='text-danger'>Invalid / Not Supported</span>";

if ($response[0] === "ACCESS_BALANCE" && is_numeric($response[1])) {

	$raw_balance = (float)$response[1];

	$isTiger = stripos($data['api_name'], 'tiger') !== false
	        || stripos($data['api_url'], 'tiger-sms') !== false;

	if ($isTiger) {
		$bal_usd = $raw_balance;
		$bal_ngn = $bal_usd * $USD_TO_NGN;

		$bal = "
			<strong>$".number_format($bal_usd,2)."</strong><br>
			<small class='text-muted'>₦".number_format($bal_ngn,2)."</small>
		";
	} else {
		$bal_usd = $raw_balance;
		$bal_ngn = $bal_usd * $USD_TO_NGN;

		$bal = "
			<strong>$".number_format($bal_usd,2)."</strong><br>
			<small class='text-muted'>₦".number_format($bal_ngn,2)."</small>
		";
	}
}
}
?>
<tr>
<td><?php echo htmlspecialchars($data['api_name']); ?></td>
<td><?php echo $bal; ?></td>
<td><?php echo htmlspecialchars($data['api_url']); ?></td>
<td><?php echo htmlspecialchars($data['api_key']); ?></td>
<td>
	<a href="edit_api?id=<?php echo $data['id']; ?>" class="btn btn-sm btn-primary">
		Edit
	</a>
</td>
<td>
	<form method="post">
		<input type="hidden" name="id" value="<?php echo $data['id']; ?>">
		<button class="btn btn-sm btn-danger" type="submit" name="delete">
			Delete
		</button>
	</form>
</td>
</tr>
<?php } ?>

</tbody>
</table>

</div>
</div>
</div>
</div>

</div>
</div>

<a class="scroll-to-top rounded" href="#page-top">
<i class="fas fa-angle-up"></i>
</a>

<?php include ("include/script.php"); ?>
<script src="vendor/datatables/jquery.dataTables.min.js"></script>
<script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script>
$(document).ready(function () {
	$('#dataTable').DataTable();
});
</script>

</body>
</html>
<?php
mysqli_close($conn);
?>
