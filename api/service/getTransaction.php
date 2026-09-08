<?php
include __DIR__ . '/../../include/config.php';

if(!isset($_GET['token']) || $_GET['token'] == "") {
echo'{"status":"500","message":"Token Blank"}';

} else {
$token = mysqli_real_escape_string($conn,$_GET['token']); 
// $find_token = new radiumsahil();
$check_token = check_token($token, $conn);
// $find_token->closeConnection();
if($check_token === false){
echo'{"status":"500","message":"Token Expired Please Logout And Login Again"}';
}else{
$user_id = $check_token;
$sql = "SELECT * FROM user_transaction WHERE user_id = '" . $user_id . "' ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
$currentTime = date("Y-m-d H:i:s");

$final = array(); 
$i = 1;
    while ($row = mysqli_fetch_array($result)) {
   $timestamp = strtotime($row['date']);
                                  $formattedDate = date("M d, Y - h:i A", $timestamp);   
        array_push($final, array(

            'id' => $i,
            'type' => $row['type'], // Corrected column name
            'date' => $formattedDate,
           'amount' => $row['amount'],
            'status' => $row['status'],
         ));
$i++;
    }
    


$data = array(
'status' => '200',
'data' => $final
);

echo json_encode($data);
mysqli_close($conn);
}
}


