<?php

$api_key = $_GET['api_key'];
$id = $_GET['id'];

include("../auth.php");
if($api_key !=""  &&  $id !=""){
 
  $sql = "UPDATE api_detail SET api_key='$api_key' WHERE id='$id'";
  $done=mysqli_query($conn,$sql);
echo 1;  
}else{
echo 2;   
}

?>