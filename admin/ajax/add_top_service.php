<?php
include("../auth.php");

/* =========================
   AUTH CHECK
========================= */
if (!isset($_SESSION['token']) || $_SESSION['token'] === "") {
    echo "Session expired. Please login again.";
    exit;
}

$admin_sql = mysqli_query($conn, "SELECT * FROM login_token WHERE token='" . $_SESSION['token'] . "'");
if (!$admin_sql || mysqli_num_rows($admin_sql) === 0) {
    echo "Unauthorized access";
    exit;
}

$admin_data = mysqli_fetch_assoc($admin_sql);
$admin_sql2 = mysqli_query(
    $conn,
    "SELECT * FROM user_data 
     WHERE id='" . $admin_data['user_id'] . "' 
       AND status='1'"
);

$final_admin = mysqli_fetch_assoc($admin_sql2);

if (!$final_admin || $final_admin['type'] !== "admin") {
    echo "Access denied";
    exit;
}

/* =========================
   VALIDATE INPUT
========================= */
if (
    !isset($_POST['server_id'], $_POST['service_id']) ||
    $_POST['server_id'] === "" ||
    $_POST['service_id'] === ""
) {
    echo "Please fill all fields";
    exit;
}

$server_id  = mysqli_real_escape_string($conn, $_POST['server_id']);
$service_id = mysqli_real_escape_string($conn, $_POST['service_id']);

/* =========================
   CHECK DUPLICATE
   NOTE: column is server_name
========================= */
$check_sql = "
    SELECT id 
    FROM top_services 
    WHERE server_name = '$server_id'
      AND service_id  = '$service_id'
    LIMIT 1
";

$check = mysqli_query($conn, $check_sql);

if ($check === false) {
    echo "Database error: " . mysqli_error($conn);
    exit;
}

if (mysqli_num_rows($check) > 0) {
    echo "Already added";
    exit;
}

/* =========================
   INSERT TOP SERVICE
========================= */
$insert_sql = "
    INSERT INTO top_services (service_id, server_name, status)
    VALUES ('$service_id', '$server_id', '1')
";

$insert = mysqli_query($conn, $insert_sql);

if ($insert) {
    echo "Details added successfully";
} else {
    echo "Insert failed: " . mysqli_error($conn);
}

mysqli_close($conn);
