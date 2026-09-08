<?php
include __DIR__ . '/../../include/config.php';
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
if(!isset($_POST['token']) || $_POST['token'] ==""){
echo'{"status": "3","msg": "token missing"}';
} else {
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
$token = mysqli_real_escape_string($conn,$_POST['token']); 
// $find_token = new radiumsahil();
$check_token = check_token($token, $conn);
// $find_token->closeConnection();
if($check_token === false){
echo'{"status": "3","msg": "Token Expired Please Logout And Login Again"}';
}else{
    $fileType = $_FILES['image']['type'];
    if (!in_array($fileType, array('image/jpeg', 'image/png'))) {
        echo 'Error: Only PNG and JPG images are allowed.';
        exit;
    }
    $uploadDir = 'upload/';
 $fileSize = $_FILES['image']['size'];
    if ($fileSize > 1024 * 1024) { // 1MB
           echo'{"status": "2","msg": "File size exceeds 1MB."}';
        exit;
    }
        $fileName = generateRandomString() . '-' . time() . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

   if (move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../../' . $uploadDir . $fileName)) {
$image_link = $uploadDir . $fileName;
$sql2=$conn->query("UPDATE user_data SET image_url='$image_link' WHERE id='$check_token'");

echo'{"status": "1","msg": "Upload Success","image_url":"'.$image_link.'"}';
} else {
    echo'{"status": "2","msg": "Error uploading file."}';
}
}
    }else{
echo'{"status": "2","msg": "No file uploaded or file upload error occurred."}';

    }
mysqli_close($conn);
}
?>