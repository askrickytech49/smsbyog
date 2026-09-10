<?php
session_start();
include 'include/config.php';
include __DIR__ . '/include/mode_check.php';
require __DIR__ . '/class/class.control.php';

if (empty($_SESSION['token'])) {
    session_destroy();
    redirect('login');
}

$wallet = new radiumsahil();
$userdata = $wallet->userdata();
$userwallet = $wallet->userwallet(); // <-- ADD THIS LINE RIGHT HERE

if ($userdata === false) {
    session_destroy();
    redirect('login');
}

// Fetch active bank details
// Note: adjusting to standard procedurals if object model doesn't support system config reads directly.
// Assuming $conn database connection is imported through config.php
$bank_query = mysqli_query($conn, "SELECT * FROM system_bank_details WHERE id = 1");
$bank = mysqli_fetch_assoc($bank_query);

$msg = "";
if (isset($_POST['submit_deposit'])) {
    $amount = floatval($_POST['amount']);
    $txn_id = mysqli_real_escape_string($conn, trim($_POST['txn_id']));
    $user_id = $userdata['id'];

    if ($amount <= 0 || empty($txn_id) || empty($_FILES['receipt']['name'])) {
        $msg = "<div class='alert alert-danger'>Please fill all fields accurately.</div>";
    } else {
        // Check if transaction ID has already been submitted
        $check = mysqli_query($conn, "SELECT id FROM manual_payments WHERE txn_id = '$txn_id'");
        if (mysqli_num_rows($check) > 0) {
            $msg = "<div class='alert alert-danger'>This Transaction ID has already been used.</div>";
        } else {
            // Handle File Upload
            $target_dir = "uploads/receipts/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            
            $file_ext = pathinfo($_FILES["receipt"]["name"], PATHINFO_EXTENSION);
            $new_filename = "REC_" . time() . "_" . rand(1000, 9999) . "." . $file_ext;
            $target_file = $target_dir . $new_filename;
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array(strtolower($file_ext), $allowed_types)) {
                if (move_uploaded_file($_FILES["receipt"]["tmp_tmp_name"] ?? $_FILES["receipt"]["tmp_name"], $target_file)) {
                    $insert = mysqli_query($conn, "INSERT INTO manual_payments (user_id, txn_id, amount, receipt_image, status) VALUES ('$user_id', '$txn_id', '$amount', '$target_file', 'pending')");
                    if ($insert) {
                        $msg = "<div class='alert alert-success'>Payment submitted successfully! Awaiting validation.</div>";
                    } else {
                        $msg = "<div class='alert alert-danger'>Database submission error. Try again.</div>";
                    }
                } else {
                    $msg = "<div class='alert alert-danger'>Error uploading payment receipt.</div>";
                }
            } else {
                $msg = "<div class='alert alert-danger'>Invalid image format. Allowed: JPG, PNG, GIF</div>";
            }
        }
    }
}

$page_title = "Manual Deposit - " . $site_data['web_name'];
include('partial/header.php');
?>
<div class="page-wrapper compact-wrapper">
<?php include('partial/topbar.php'); ?>
<div class="page-body-wrapper">
<?php include('partial/sidebar.php'); ?>

<div class="page-body"><br><br>
<div class="container-fluid" style="max-width: 600px;">
    <div class="card p-4 shadow-sm" style="border-radius:16px;">
        <h4>Manual Wallet Funding</h4>
        <p class="text-muted text-sm">Transfer the money to the system account listed below, then upload proof.</p>
        <hr>
        
        <?= $msg; ?>

        <div class="p-3 rounded mb-4" style="border-left: 4px solid #e10700; color: black;">
            <strong>Bank Name:</strong> <?= htmlspecialchars($bank['bank_name'] ?? 'N/A') ?><br>
            <strong>Account Name:</strong> <?= htmlspecialchars($bank['account_name'] ?? 'N/A') ?><br>
            <strong>Account Number:</strong> <strong class="text-dark" style="font-size:1.1rem;"><?= htmlspecialchars($bank['account_number'] ?? 'N/A') ?></strong>
        </div>

        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group mb-3">
                <label class="form-label font-weight-bold">Amount Sent (₦)</label>
                <input type="number" step="0.01" name="amount" class="form-control" placeholder="e.g. 5000" required>
            </div>
            
            <div class="form-group mb-3">
                <label class="form-label font-weight-bold">Transaction Reference ID / Session ID</label>
                <input type="text" name="txn_id" class="form-control" placeholder="Enter bank transfer reference string" required>
            </div>

            <div class="form-group mb-4">
                <label class="form-label font-weight-bold">Upload Receipt Screenshot</label>
                <input type="file" name="receipt" class="form-control" accept="image/*" required>
            </div>

            <button type="submit" name="submit_deposit" class="btn w-100 text-white" style="background:#e10700; border-radius:8px;">Submit Proof of Payment</button>
        </form>
    </div>
</div>
</div>
</div>
</div>
<?php include('partial/scripts.php'); ?>
<?php include('partial/footer-end.php'); ?>