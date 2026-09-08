<?php
include("auth.php");

/* ================= AUTH ================= */
if (!isset($_SESSION['token'])) {
    header('Location: login.php'); exit;
}

/* ================= ADMIN CHECK ================= */
$admin_sql = mysqli_query($conn,"SELECT * FROM login_token WHERE token='".$_SESSION['token']."'");
if(mysqli_num_rows($admin_sql) == 0){
    header('Location: login.php'); exit;
}

$admin = mysqli_fetch_assoc($admin_sql);
$admin_user = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT * FROM user_data WHERE id='{$admin['user_id']}' AND type IN ('admin','super_admin') AND status='1'")
);

if(!$admin_user){
    header('Location: login.php'); exit;
}

/* ================= HANDLE BULK APPROVE ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_approve']) && !empty($_POST['txn_ids'])) {

    mysqli_begin_transaction($conn);
    try {
        foreach ($_POST['txn_ids'] as $txn_id) {

            $txn_id = mysqli_real_escape_string($conn, $txn_id);

            $txn = mysqli_fetch_assoc(
                mysqli_query($conn,"SELECT * FROM user_transaction WHERE txn_id='$txn_id' AND status=0")
            );

            if ($txn) {
                $user_id = (int)$txn['user_id'];
                $amount  = (int)$txn['amount'];

                mysqli_query($conn,"
                    UPDATE user_transaction
                    SET status = 1
                    WHERE txn_id = '$txn_id'
                ");

                mysqli_query($conn,"
                    UPDATE user_wallet
                    SET 
                        balance = balance + $amount,
                        total_recharge = total_recharge + $amount
                    WHERE user_id = $user_id
                ");
            }
        }

        mysqli_commit($conn);
        $success = "Selected transactions approved successfully.";

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error = "Bulk approval failed.";
    }
}

/* ================= HANDLE BULK REJECT ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_reject']) && !empty($_POST['txn_ids'])) {

    mysqli_begin_transaction($conn);
    try {
        foreach ($_POST['txn_ids'] as $txn_id) {

            $txn_id = mysqli_real_escape_string($conn, $txn_id);

            mysqli_query($conn,"
                UPDATE user_transaction
                SET status = -1
                WHERE txn_id = '$txn_id' AND status = 0
            ");
        }

        mysqli_commit($conn);
        $success = "Selected transactions rejected successfully.";

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error = "Bulk rejection failed.";
    }
}

/* ================= HANDLE ADMIN NOTE ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_note'])) {

    $note   = mysqli_real_escape_string($conn, $_POST['admin_note']);
    $txn_id = mysqli_real_escape_string($conn, $_POST['note_txn_id']);

    mysqli_query($conn,"
        UPDATE user_transaction
        SET admin_note = '$note'
        WHERE txn_id = '$txn_id'
    ");

    $success = "Admin note saved.";
}

/* ================= FILTERS ================= */
$limit = 50;
$page  = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';

$where = "1=1";

if ($search !== '') {
    $search = mysqli_real_escape_string($conn, $search);
    $where .= " AND (ud.email LIKE '%$search%' OR ut.txn_id LIKE '%$search%')";
}

if ($status !== '' && in_array($status, ['0','1','2'])) {
    $where .= " AND ut.status = '$status'";
}

/* ================= COUNT ================= */
$total_rows = mysqli_fetch_assoc(
    mysqli_query($conn,"
        SELECT COUNT(*) total
        FROM user_transaction ut
        JOIN user_data ud ON ut.user_id = ud.id
        WHERE $where
    ")
)['total'];

$total_pages = ceil($total_rows / $limit);

/* ================= FETCH TRANSACTIONS ================= */
$transactions = mysqli_query($conn,"
    SELECT ut.*, ud.name, ud.email
    FROM user_transaction ut
    JOIN user_data ud ON ut.user_id = ud.id
    WHERE $where
    ORDER BY ut.date DESC
    LIMIT $limit OFFSET $offset
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>All Transactions</title>
<?php include("include/head.php"); ?>
<style>
.badge-pending {background:#facc15;color:#000}
.badge-approved {background:#22c55e;color:#fff}
.badge-rejected {background:#ef4444;color:#fff}
textarea{min-width:200px}
</style>
</head>

<body>
<div id="wrapper">
<?php include("include/slidebar.php"); ?>
<div id="content-wrapper" class="d-flex flex-column">
<div id="content">
<?php include("include/topbar.php"); ?>

<div class="container-fluid">

<h3 class="mb-3">All Transactions</h3>

<?php if(isset($success)): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
<?php if(isset($error)): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

<!-- SEARCH & FILTER -->
<form class="row g-2 mb-3" method="get">
<div class="col-md-4">
<input type="text" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
class="form-control" placeholder="Search email or txn ID">
</div>

<div class="col-md-3">
<select name="status" class="form-control">
<option value="">All Status</option>
<option value="0" <?php if($status==='0') echo 'selected'; ?>>Pending</option>
<option value="1" <?php if($status==='1') echo 'selected'; ?>>Approved</option>
<option value="-1" <?php if($status==='2') echo 'selected'; ?>>Rejected</option>
</select>
</div>

<div class="col-md-2">
<button class="btn btn-primary w-100">Filter</button>
</div>
</form>

<form method="post">

<!-- BULK ACTION -->
<div class="mb-2 d-flex gap-2">
<button type="submit" name="bulk_approve" class="btn btn-success btn-sm"
onclick="return confirm('Approve selected transactions?')">
Approve Selected
</button>

<button type="submit" name="bulk_reject" class="btn btn-danger btn-sm"
onclick="return confirm('Reject selected transactions?')">
Reject Selected
</button>
</div>

<div class="card">
<div class="card-body table-responsive">

<table class="table table-bordered table-hover">
<thead>
<tr>
<th><input type="checkbox" onclick="toggleAll(this)"></th>
<th>#</th>
<th>User</th>
<th>Email</th>
<th>Amount</th>
<th>Type</th>
<th>Txn ID</th>
<th>Date</th>
<th>Status</th>
<th>Admin Note</th>
<th>Save</th>
</tr>
</thead>
<tbody>

<?php
$i = $offset + 1;
while($row = mysqli_fetch_assoc($transactions)):
?>
<tr>
<td>
<?php if($row['status']==0): ?>
<input type="checkbox" name="txn_ids[]" value="<?php echo $row['txn_id']; ?>">
<?php endif; ?>
</td>
<td><?php echo $i++; ?></td>
<td><?php echo htmlspecialchars($row['name']); ?></td>
<td><?php echo htmlspecialchars($row['email']); ?></td>
<td>₦<?php echo number_format($row['amount']); ?></td>
<td><?php echo htmlspecialchars($row['type']); ?></td>
<td><?php echo htmlspecialchars($row['txn_id']); ?></td>
<td><?php echo $row['date']; ?></td>
<td>
<?php
if($row['status']==0){
    echo '<span class="badge badge-pending">Pending</span>';
}elseif($row['status']==1){
    echo '<span class="badge badge-approved">Approved</span>';
}else{
    echo '<span class="badge badge-rejected">Rejected</span>';
}
?>
</td>
<td>
<textarea name="admin_note"><?php echo htmlspecialchars($row['admin_note']); ?></textarea>
<input type="hidden" name="note_txn_id" value="<?php echo $row['txn_id']; ?>">
</td>
<td>
<button type="submit" name="save_note" class="btn btn-info btn-sm">Save</button>
</td>
</tr>
<?php endwhile; ?>

</tbody>
</table>

</div>
</div>
</form>

<!-- PAGINATION -->
<nav class="mt-4">
<ul class="pagination justify-content-center">
<?php if($page>1): ?>
<li class="page-item">
<a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>">Prev</a>
</li>
<?php endif; ?>

<?php for($p=max(1,$page-2);$p<=min($total_pages,$page+2);$p++): ?>
<li class="page-item <?php echo $p==$page?'active':''; ?>">
<a class="page-link" href="?page=<?php echo $p; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>">
<?php echo $p; ?>
</a>
</li>
<?php endfor; ?>

<?php if($page<$total_pages): ?>
<li class="page-item">
<a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>">Next</a>
</li>
<?php endif; ?>
</ul>
</nav>

</div>
</div>

<?php include("include/script.php"); ?>
</body>
</html>

<script>
function toggleAll(source){
document.querySelectorAll('input[name="txn_ids[]"]').forEach(cb=>cb.checked=source.checked);
}
</script>
