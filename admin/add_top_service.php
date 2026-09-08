<?php
include("auth.php");

if (!isset($_SESSION['token'])) {
    if (isset($_COOKIE['remember_me'])) {
        $_SESSION['token'] = $_COOKIE['remember_me'];
    } else {
        header('Location: login.php'); exit;
    }
}

$admin_sql = mysqli_query($conn, "SELECT * FROM login_token WHERE token='" . $_SESSION['token'] . "'");
if (mysqli_num_rows($admin_sql) == 0) {
    header('Location: login.php'); exit;
}

$admin_data = mysqli_fetch_array($admin_sql);
$admin_sql2 = mysqli_query(
    $conn,
    "SELECT * FROM user_data WHERE id='" . $admin_data['user_id'] . "' AND status='1'"
);
$final_admin = mysqli_fetch_array($admin_sql2);

if ($final_admin['type'] !== "admin") {
    header('Location: login.php'); exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add Top Service - @getallscripts</title>
    <?php include("include/head.php"); ?>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">

    <style>
        .select2-container--default .select2-selection--single {
            height: 40px;
            border-color: #ebf1f6;
            line-height: 40px;
        }
    </style>
</head>

<body id="page-top">
<div id="wrapper">

<?php include("include/slidebar.php"); ?>

<div id="content-wrapper" class="d-flex flex-column">
<div id="content">

<?php include("include/topbar.php"); ?>

<div class="container-fluid" id="container-wrapper">

<div class="breadcrumb mb-4">
    <a href="#">Home</a> / Add Top Service
</div>

<div class="row">
<div class="col">

<div class="card mb-4" id="loading">
<div class="card-header">
    <h6 class="m-0 font-weight-bold text-primary">Add Top Service</h6>
</div>

<div class="card-body">

<!-- SELECT SERVER -->
<div class="form-group">
<label>Select Server</label>
<select id="server_id" class="form-control">
    <option value="">SELECT SERVER</option>
    <?php
    $servers = mysqli_query($conn, "SELECT * FROM otp_server ORDER BY server_name ASC");
    while ($row = mysqli_fetch_array($servers)) {
        echo "<option value='{$row['id']}'>{$row['server_name']}</option>";
    }
    ?>
</select>
</div>

<!-- SELECT SERVICE -->
<div class="form-group">
<label>Select Service</label>
<select id="service_id" class="form-control">
    <option value="">SELECT SERVICE</option>
</select>
</div>

<button type="button" id="update" class="btn btn-primary w-100">
    Submit
</button>

</div>
</div>

</div>
</div>

</div>
</div>

<!-- ✅ LOAD CORE JS FIRST -->
<?php include("include/script.php"); ?>

<!-- Select2 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<!-- ✅ CUSTOM JS AFTER jQuery -->
<script>
$(document).ready(function () {

    $('#dashboard').removeClass("active");
    $("#top_service").addClass("active");

    $('select').select2();

    // Load services when server changes
    $("#server_id").on('change', function () {

        let server_id = $(this).val();
        $("#service_id").html('<option value="">SELECT SERVICE</option>');

        if (!server_id) return;

        $.ajax({
            url: "ajax/getService.php",
            type: "GET",
            dataType: "json",
            success: function (res) {

                if (!res.service || res.service.length === 0) {
                    $("#service_id").append(
                        '<option value="">No services found</option>'
                    );
                    return;
                }

                res.service.forEach(s => {
                    $("#service_id").append(
                        `<option value="${s.id}">
                            ${s.service_name} (${s.service_code})
                         </option>`
                    );
                });

                // refresh select2
                $("#service_id").trigger('change');
            }
        });
    });

    // Submit top service
    $("#update").click(function () {

        let server_id  = $("#server_id").val();
        let service_id = $("#service_id").val();

        if (!server_id || !service_id) {
            alert("Please select server and service");
            return;
        }

        Notiflix.Block.Dots('#loading', 'Please Wait');

        $.ajax({
            url: "ajax/add_top_service.php",
            type: "POST",
            data: {
                server_id: server_id,
                service_id: service_id
            },
            success: function (res) {
                Notiflix.Block.Remove('#loading');
                alert(res);
            }
        });
    });

});
</script>

</body>
</html>
