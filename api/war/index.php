<?php
require_once __DIR__ . '/../../include/config.php';
$today = date('Y-m-d');         


$sql = "SELECT * FROM upi_recharge WHERE DATE(recharge_time)='$today'";

$result = mysqli_query($conn, $sql);


if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $txn_id = $row['txn_id'];
    $sql2 = mysqli_query($conn,"SELECT * FROM upi_recharge WHERE txn_id='$txn_id' ");
    if(mysqli_num_rows($sql2) > 1){
    $txn_data = mysqli_fetch_assoc($sql2);
     $user_id = $txn_data['user_id'];
     $sql3 = mysqli_query($conn,"SELECT * FROM user_data WHERE id='$user_id' and status = '1'");
     if(mysqli_num_rows($sql3) == 1){
    mysqli_query($conn,"UPDATE user_data  SET status='2' WHERE id='$user_id' ");
        echo 'USER ID:'.$txn_data['user_id'].'ROW ID:'.$txn_data['id'];
        echo"<br>";
     }else{
         echo "already ban";
           echo"<br>";
     }
    }
    }
} else {
    // If no rows are returned, you can display a message
    echo "No rows found";
}

// Get the current date and time
$currentDateTime = date('Y-m-d h:i:s A');

// Output the formatted date and time
echo "Current date and time: " . $currentDateTime;
// Close the connection
mysqli_close($conn);
?>
