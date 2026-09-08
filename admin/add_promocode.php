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
if($final_admin['type'] == "admin"){

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Add Promocode - @getallscripts</title>
<?php include("include/head.php"); ?>  
</head>
<script>
        $(document).ready(function() {
            // Remove "active" class from all <a> elements
            $('#dashboard').removeClass("active");
            
            // Add "active" class to the specific element with ID "faq"
            $("#promocode").addClass("active");
        });
    </script>
<body id="page-top">
  <div id="wrapper">
    <!-- Sidebar -->
<?php include ("include/slidebar.php"); ?>
    <!-- Sidebar -->
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">
        <!-- TopBar -->
<?php include ("include/topbar.php"); ?>              
        <!-- Topbar -->

        <!-- Container Fluid-->
        <div class="container-fluid" id="container-wrapper">
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
           <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">Add Promocode</li>
            </ol>
          </div>

          <div class="row">
            <div class="col">
              <!-- Form Basic -->
              <div class="card mb-4" id="loading">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Add Promocode</h6>
                </div>
                <div class="card-body">
                     <div class="form-group">
                          <label for="exampleInputPassword1">Enter Promocode</label>                 
                    <div style="position: relative; margin-top: 8px;">
    <input type="text" class="form-control" id="promo_code" placeholder="Enter Promocode" style="padding-right: 40px; height:40px;">
   <a onclick="codespromo()">  <i class="fa fa-random" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-size: 24px;"></i></a>                    
                </div>   
                </div>
                    <div class="form-group">
                      <label for="exampleInputPassword1">How Many User</label>
                      <input type="number" class="form-control" id="for_user"  placeholder="How Many User">
                    </div>
                     <div class="form-group">
                      <label for="exampleInputPassword1">Per User Amount</label>
                      <input type="number" class="form-control" id="per_amount"  placeholder="Enter Per User Amount">
                    </div>
                   <button type="submit" id="update" class="btn btn-primary w-100 mb-2">Submit</button><br>
                </div>
              
        <!---Container Fluid-->
      </div>
      <!-- Footer -->
<?php // include("include/copyright.php");
 ?>
      <!-- Footer -->
    </div>
  </div>

  <!-- Scroll to top -->
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>
<?php include("include/script.php"); ?>
<script>
$(document).ready(function() {
    // Attach a click event handler to the button

    $("#update").click(function() {
        Notiflix.Block.Dots('#loading', 'Please Wait');
    var promo_code = $("#promo_code").val();
    var for_user = $("#for_user").val();
    var per_amount = $("#per_amount").val();
        var params = {
        promo_code: promo_code,
        for_user: for_user,
        per_amount: per_amount,
        };

        $.ajax({
            type: "POST",
            url: "ajax/add_promocode.php",
            data: params,
            error: function (e) {
                console.log(e);
            },
            success: function (data) {
                   Notiflix.Block.Remove('#loading');
             $('#update').html(data);
                $('#update').html("Submit");

            }
        });
    });
});
function generateFormatString() {
    const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let result = '';

    for (let i = 0; i < 4; i++) {
        for (let j = 0; j < 4; j++) {
            result += characters.charAt(Math.floor(Math.random() * characters.length));
        }
        if (i < 3) {
            result += '-';
        }
    }

    return result;
}

function codespromo() {
            const generatedString = generateFormatString();
            document.getElementById('promo_code').value = generatedString;
        }
</script>




</body>

</html>
<?php
}else{
    header('Location: login.php'); exit;
}
}
?>