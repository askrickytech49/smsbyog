<?php

$id = $_GET['id'];

include("../auth.php");
if($id !=""){
 
  $sql = "SELECT * FROM  api_detail WHERE id='$id'";
  $done=mysqli_query($conn,$sql);
  $data = mysqli_fetch_assoc($done);
  echo $data['api_key'];
}else{
echo false;   
}

?>