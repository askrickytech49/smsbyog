<?php
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
$http_code = 0; // Initialize to prevent undefined variable warning
while ($data = mysqli_fetch_array($sql)) {
$bal = "<span class='text-warning'>Checking...</span>"; // safe default

if($data['id'] == '2'){
    // 5sim — Bearer token auth
    $url = "https://5sim.net/v1/user/profile";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . trim($data['api_key']),
        'Accept: application/json'
    ]);
    $response1 = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $profile = json_decode($response1, true);

    if ($http_code === 200 && isset($profile['balance'])) {
        $bal_usd = (float)$profile['balance'];
        $bal_ngn = $bal_usd * $USD_TO_NGN;
        $bal = "<strong>\$".number_format($bal_usd, 2)."</strong><br>
                <small class='text-muted'>₦".number_format($bal_ngn, 2)."</small>";
    } else {
        $err = $profile['message'] ?? $profile['error'] ?? "HTTP $http_code";
        $bal = "<span class='text-danger'>5sim Error: $err</span>";
    }

} elseif($data['id'] == '1'){
    // VerifySMS — API-KEY header, returns plain number
    $url = rtrim($data['api_url'], '/') . "/api/balance";
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER     => [
            "API-KEY: " . trim($data['api_key']),
            "Accept: application/json"
        ],
    ]);
    $response_raw = curl_exec($ch);
    $http_code    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_err     = curl_error($ch);
    curl_close($ch);

    if ($curl_err) {
        $bal = "<span class='text-danger'>Connection Error: $curl_err</span>";
    } elseif ($http_code == 200 && is_numeric(trim($response_raw))) {
        $bal_usd = (float)trim($response_raw);
        $bal_ngn = $bal_usd * $USD_TO_NGN;
        $bal = "<strong>\$".number_format($bal_usd, 2)."</strong><br>
                <small class='text-muted'>₦".number_format($bal_ngn, 2)."</small>";
    } elseif ($http_code == 401) {
        $bal = "<span class='text-warning'>Invalid API Key (401)</span>";
    } else {
        // Try JSON response
        $json = json_decode($response_raw, true);
        if (isset($json['balance'])) {
            $bal_usd = (float)$json['balance'];
            $bal_ngn = $bal_usd * $USD_TO_NGN;
            $bal = "<strong>\$".number_format($bal_usd, 2)."</strong><br>
                    <small class='text-muted'>₦".number_format($bal_ngn, 2)."</small>";
        } else {
            $bal = "<span class='text-danger'>HTTP $http_code: " . htmlspecialchars(substr($response_raw, 0, 80)) . "</span>";
        }
    }

} elseif($data['id'] == '3'){
    // DinoMMO — X-API-Key header
    // Try common balance endpoints
    $endpoints = [
        rtrim($data['api_url'], '/') . "/account/balance",
        rtrim($data['api_url'], '/') . "/me/balance",
        rtrim($data['api_url'], '/') . "/sms-otp/balance",
    ];
    $bal = "<span class='text-danger'>Invalid / Not Supported</span>";
    foreach ($endpoints as $url) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => [
                "X-API-Key: " . trim($data['api_key']),
                "Accept: application/json"
            ],
        ]);
        $resp     = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $json = json_decode($resp, true);
        // Try common balance field names
        $balance_val = $json['balance'] ?? $json['available_balance'] ?? $json['wallet_balance'] ?? null;
        if ($http_code == 200 && $balance_val !== null) {
            $bal_usd = (float)$balance_val;
            $bal_ngn = $bal_usd * $USD_TO_NGN;
            $bal = "<strong>\$".number_format($bal_usd, 2)."</strong><br>
                    <small class='text-muted'>₦".number_format($bal_ngn, 2)."</small>";
            break;
        }
    }

} else {
    // TigerSMS & others — sms-activate style getBalance
    $url = rtrim($data['api_url'], '/') . "/stubs/handler_api.php?api_key=" . urlencode(trim($data['api_key'])) . "&action=getBalance";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response1 = curl_exec($ch);
    $http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_err   = curl_error($ch);
    curl_close($ch);

    $bal = "<span class='text-danger'>Invalid / Not Supported</span>";

    if ($curl_err) {
        $bal = "<span class='text-danger'>Connection Error: $curl_err</span>";
    } else {
        $response = explode(':', trim($response1));
        if (isset($response[0]) && $response[0] === "ACCESS_BALANCE" && isset($response[1]) && is_numeric(trim($response[1]))) {
            $bal_usd = (float)trim($response[1]);
            $bal_ngn = $bal_usd * $USD_TO_NGN;
            $bal = "<strong>\$".number_format($bal_usd, 2)."</strong><br>
                    <small class='text-muted'>₦".number_format($bal_ngn, 2)."</small>";
        } elseif ($http_code !== 200) {
            $bal = "<span class='text-danger'>HTTP $http_code</span>";
        } else {
            $bal = "<span class='text-danger'>" . htmlspecialchars(substr($response1, 0, 80)) . "</span>";
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
