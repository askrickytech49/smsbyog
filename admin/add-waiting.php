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

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Add Waiting - @getallscripts</title>
<?php include("include/head.php"); ?>  
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" integrity="sha512-nMNlpuaDPrqlEls3IX/Q56H36qvBASwb3ipuo3MxeWbsQB1881ox0cRv7UPTgBlriqoynt35KjEwgGUeUXIPnw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
.select2-container--classic .select2-selection--single,
.select2-container--default .select2-selection--single,
.select2-container--default .select2-selection--single .select2-selection__rendered,
.select2-container--default .select2-selection--single .select2-selection__arrow,
.select2-container--default .select2-selection--multiple,
.select2-container--classic .select2-selection--single .select2-selection__arrow,
.select2-container--classic .select2-selection--single .select2-selection__rendered {
  border-color: #ebf1f6;
  color: #5A6A85;
  height: 40px;
  line-height: 40px;
}

.select2-container--default .select2-selection--multiple {
  line-height: 27px;
  height: auto;
}

.select2-container--classic .select2-selection--multiple .select2-selection__choice,
.select2-container--default .select2-selection--multiple .select2-selection__choice,
.select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
  background-color: #539BFF;
  border-color: #539BFF;
  color: #fff;
}
</style>
</head>
<script>
        $(document).ready(function() {
            // Remove "active" class from all <a> elements
            $('#dashboard').removeClass("active");
            
            // Add "active" class to the specific element with ID "faq"
            $("#number-wait").addClass("active");
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
              <li class="breadcrumb-item active" aria-current="page">Add Waiting</li>
            </ol>
          </div>
         <input type="hidden" id="server_no" value=""> 
                       
          <div class="row">
            <div class="col">
              <!-- Form Basic -->
              <div class="card mb-4" id="loading">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Add Waiting</h6>
                </div>
                <div class="card-body">
                     <div class="form-group">
                      <label for="exampleInputEmail1">Select Server </label>
                   <?php
              $query = "SELECT * FROM otp_server";
            $statement = mysqli_query($conn,$query);
                                            ?>  
                  <select name="server_id" id="server_id" class="form-control mb-3">
                  <option value="">SELECT SERVER</option>
                      <?php
                                                   while($row=mysqli_fetch_array($statement))
 
                                                    {
                                                        ?>
                                                            <option value="<?php echo $row['id']; ?>"><?php echo $row['server_name']; ?></option>
                                                        <?php
                                                    }
                                                ?>
                  </select>
                    </div>
                 
                    <div class="form-group">
                      <label for="exampleInputEmail1">Select Service</label>
                  <select name="service-id" id="service-id" class="form-control mb-3">
                        <option value="">SELECT SERVICE</option>            
                 </select>
                    </div>
                    <div class="form-group">
                      <label for="exampleInputPassword1">Waiting Period (sec)</label>
                      <input type="number" class="form-control" id="waiting_sec"  placeholder="Enter Waiting Period">
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
    var server_id = $("#server_id").val();
    var service_id = $("#service-id").val();
    var waiting_sec = $("#waiting_sec").val();
        var params = {
        server_id: server_id,
        service_id: service_id,
        waiting_sec: waiting_sec,
        };

        $.ajax({
            type: "POST",
            url: "ajax/add_waiting.php",
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
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js" integrity="sha512-2ImtlRlf2VVmiGZsjm9bEyhjGW4dU7B6TNwh/hx/iSByxNENtj3WVE6o/9Lj4TJeVXPi4bnOIMXFIJJAeufa0A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    $(document).ready(function() {
    $('#server_id').change(function () {
        // ...
      if ($(this).val() == '') {
    $("#service-id").empty();
    var option = "<option value=\"\">SELECT SERVICE</option>"; // Escape the double quotes
    $("#service-id").append(option);
 document.getElementById("server_no").value = '';              
    return;
}

        var params = { server: $(this).val() };
        
        // AJAX call for fetching services based on selected category
      
         $.ajax({
    type: "GET",
    url: "ajax/getService",
    data: params,
    dataType: "json", // Set the expected data type
    error: function (e) {
        console.log("AJAX error:", e);
    },
    success: function (data) {
        $("#service-id").empty();
document.getElementById("server_no").value = data['service'][0]['server_id'];          
        data['service'].forEach(service => {
            var option = "<option value='" + service['id'] + "'>" + service['service_name'] + ' - ' + '₦' + service['service_price'] + "</option>";
            $("#service-id").append(option);
            //  $("#getting").hide();   
        });
    }
});

    });
         
        // Initialize Select2
        $('select').select2();
                
    });
</script>

</body>

</html>
<?php
}else{
    header('Location: login.php'); exit;
}
}
?>