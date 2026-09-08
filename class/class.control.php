<?php
// class radiumsahil {
//     public $conn;

//     public function __construct() {
//         $this->conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

//         if ($this->conn->connect_error) {
//             die("Connection failed: " . $this->conn->connect_error);
//         }
//     }
    
//     public function get_token(){
//         return $_SESSION['token'];
//     }    
// public function check_token($token){
// //  $token = $this->get_token();                                
// $sql=mysqli_query($this->conn,"SELECT * FROM `login_token` WHERE token='$token' and status='1'");
// if(mysqli_num_rows($sql) == 0){
// return false;
// }else{
// $data=mysqli_fetch_assoc($sql);
// $user_id=$data['user_id'];
// $sql20=mysqli_query($this->conn,"SELECT * FROM `user_wallet` WHERE user_id='$user_id'");
// if (mysqli_num_rows($sql20) == 0) {
//     return false; // Or handle the missing wallet gracefully
// }
// $data1=mysqli_fetch_assoc($sql20);
// $xy=$this->check_activities($data1['balance'], $data1['total_otp'], $data1['total_recharge'], $user_id);
// $sql2=mysqli_query($this->conn,"SELECT * FROM `user_data` WHERE id='$user_id' and status='1'");
// if(mysqli_num_rows($sql2) == 0){
// return false;
// }else{
// return $user_id;
// }
// }
// }
//     public function balancedata() {
//                 $token = $this->get_token();                
//     $user_id=$this->check_token($token);
//     if($user_id === false){
//     return false;
//     }else{
//         $sql="SELECT * FROM `user_wallet` WHERE user_id='".$user_id."'";
//     	$result=mysqli_query($this->conn, $sql);
//         return $result->fetch_array();        
//     }    
//     }
//   public function userdata(){
//       $token = $this->get_token();                
//     $user_id=$this->check_token($token);
//     if($user_id === false){
//     return false;
//     }else{
//         $sql="SELECT * FROM `user_data` WHERE id='".$user_id."'";
//     	$result=mysqli_query($this->conn, $sql);
//         return $result->fetch_array();                    
// }    
//   }
//     public function userwallet(){
//       $token = $this->get_token();                
//     $user_id=$this->check_token($token);
//     if($user_id === false){
//     return false;
//     }else{
//         $sql="SELECT * FROM `user_wallet` WHERE user_id='".$user_id."'";
//     	$result=mysqli_query($this->conn, $sql);
//         return $result->fetch_array();                    
// }    
//   }
//  public function all_server(){
//     $final = array(); // Initialize an empty array to store results

//     $sql = mysqli_query($this->conn, "SELECT * FROM `otp_server` WHERE status='1'");
    
//     while ($row = $sql->fetch_array()) {
//         array_push($final, array(
//             'id' => $row['id'],
//             'server_name' => $row['server_name'],
//         ));
//     }
    
//     return $final;
// }
//   public function number_history(){
//         $token = $this->get_token();                
//     $user_id=$this->check_token($token);
//     if($user_id === false){
//     return false;
//     }else{
//         $final = array(); // Initialize an empty array to store results

//     $sql = mysqli_query($this->conn, "SELECT * FROM `active_number` WHERE user_id='".$user_id."' ORDER BY id DESC");
    
//     while ($row = $sql->fetch_array()) {
//         // $sql2 = mysqli_query($this->conn,"SELECT * FROM otp_server WHERE id='".$row['server_id']."'");
      
//             $sql4 = mysqli_query($this->conn,"SELECT * FROM service_icon WHERE short_code='".$row['service_id']."'");
//             if(mysqli_num_rows($sql4) == 1){
//             $img_data = mysqli_fetch_assoc($sql4);
//                 $img_url = $img_data['img_url'];
//             }else{
//                 $img_url = "https://i.ibb.co/ySRhxqh/default.png";
//             }
//             $timestamp = strtotime($row['buy_time']);
//             $formattedDate = date("M d, Y - h:i A", $timestamp);   
//         array_push($final, array(
//             'number' => $row['number'],
//             'service_name' => $row['service_name'],
//             'server_id' => $row['server_id'],
//             'buy_time' => $formattedDate,  
//             'sms' => $row['sms_text'],    
//             'service_price' => $row['service_price'], 
//             'logo' =>  $img_url                                 
//         ));
//     }
    
//     }
//     return $final;
//   }
//  public function all_service(){
//     $final = array(); // Initialize an empty array to store results

//     $sql = mysqli_query($this->conn, "SELECT * FROM `categorys`");
    
//     while ($row = $sql->fetch_array()) {
//         array_push($final, array(
//             'id' => $row['id'],
//             'name' => $row['name'],
//             'amount' => $row['amount'],
//             'stock' => $row['total_stock'],
//         ));
//     }
    
//     return $final;
//     }
//   public function account_history(){
//         $token = $this->get_token();                
//     $user_id=$this->check_token($token);
//     if($user_id === false){
//     return false;
//     }else{
//         $final = array(); // Initialize an empty array to store results

//     $sql = mysqli_query($this->conn, "SELECT * FROM `sold` WHERE user_id='".$user_id."'");
    
//     while ($row = $sql->fetch_array()) {
//         array_push($final, array(
//             'name' => $row['name'],
//             'username' => $row['username'],
//             'password' => $row['password'],
//         ));
//     }
    
//     }
//     return $final;
//   }  
//       public function transaction_history(){
//         $token = $this->get_token();                
//     $user_id=$this->check_token($token);
//     if($user_id === false){
//     return false;
//     }else{
//         $final = array(); // Initialize an empty array to store results

//     $sql = mysqli_query($this->conn, "SELECT * FROM `user_transaction` WHERE user_id='".$user_id."' ORDER BY id DESC");
    
//     while ($row = $sql->fetch_array()) {
//         array_push($final, array(
//             'amount' => $row['amount'],
//             'date' => $row['date'],
//             'type' => $row['type'],
//             'txn_id' => $row['txn_id'],  
//             'status' => $row['status'],    
//         ));
//     }
    
//     }
//     return $final;
//   }
//     public function closeConnection() {
//         $this->conn->close();
//     }
//      public function negativebal($balance) {
//     if ($balance < 0) {
//         return "1";
//     } else {
//         return "2";
//     }
// }
// public function check_activities($balance, $total_otp, $lifetime, $token) {
//     $oauthid = $token;
    
//     if ($this->negativebal($balance)==1 || $this->negativebal($total_otp)==1 || $this->negativebal($lifetime)==1 || ($balance > $lifetime)) {
//         $sql2 = mysqli_query($this->conn, "SELECT * FROM user_data WHERE id = '$oauthid' AND status = '1'");
        
//         if (mysqli_num_rows($sql2) > 0) {
//             return "Already Action";
//         } else {
//             $sql3 = mysqli_query($this->conn, "UPDATE user_data SET status = '1' WHERE id = '$oauthid'");
//             return "#1";
//         }
//     }
    
//     // Return a default message if none of the conditions are met
//     return "No action required";
// }
// public function generateRandomString($length = 25) {
//     $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
//     $random_string = '';

//     for ($i = 0; $i < $length; $i++) {
//         $random_string .= $characters[rand(0, strlen($characters) - 1)];
//     }

//     return $random_string;
// }
// public function generateRandomString32($length = 32) {
//     $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
//     $random_string = '';

//     for ($i = 0; $i < $length; $i++) {
//         $random_string .= $characters[rand(0, strlen($characters) - 1)];
//     }

//     return $random_string;
// }
//       public function refer_data(){
//           $token = $this->get_token();                
//     $user_id=$this->check_token($token);
//     if($user_id === false){
//     return false;
//     }else{       
//     $random=$this->generateRandomString();
//         $sql="SELECT * FROM `refer_data` WHERE user_id='".$user_id."'";
//          	$result=mysqli_query($this->conn, $sql);
//       if(mysqli_num_rows($result)==0){
//           mysqli_query($this->conn,"INSERT INTO refer_data (user_id, balance, own_code, refer_by, transfer, total_earn) VALUES ('".$user_id."', '0', '".$random."', '', '0','0')");
//               $sql="SELECT * FROM `refer_data` WHERE user_id='".$user_id."'";
//          	$result=mysqli_query($this->conn, $sql);
//           return $result->fetch_array();
//       }else{        	
//         return $result->fetch_array();
//         }
//     }
//     }
//     public function refer_users(){
//           $token = $this->get_token();                
//     $user_id=$this->check_token($token);
//     if($user_id === false){
//     return false;
//     }else{       
//     $refers = $this->refer_data();
//         $sql="SELECT * FROM `refer_data` WHERE refer_by='".$refers['own_code']."'";
//          	$result=mysqli_query($this->conn, $sql);
//       if(mysqli_num_rows($result)==0){
//       return 0;
//       }else{        	
//         return mysqli_num_rows($result);
//         }
//     }
//     }
//         public function recent_history(){
//         $token = $this->get_token();                
//     $user_id=$this->check_token($token);
//     if($user_id === false){
//     return false;
//     }else{
//         $final = array(); // Initialize an empty array to store results

//     $sql = mysqli_query($this->conn, "SELECT * FROM `user_transaction` WHERE user_id='".$user_id."' ORDER BY id DESC LIMIT 15 ");
    
//     while ($row = $sql->fetch_array()) {
//         array_push($final, array(
//             'amount' => $row['amount'],
//             'date' => $row['date'],
//             'type' => $row['type'],
//             'txn_id' => $row['txn_id'],  
//             'status' => $row['status'],    
//         ));
//     }
    
//     }
//     return $final;
//   }  
//       public function api_data(){
//           $token = $this->get_token();                
//     $user_id=$this->check_token($token);
//     if($user_id === false){
//     return false;
//     }else{     
//     $current_time_in_ist = date('Y-m-d H:i:s');        
      
//     $random=$this->generateRandomString32();
//         $sql="SELECT * FROM `user_api` WHERE user_id='".$user_id."'";
//          	$result=mysqli_query($this->conn, $sql);
//       if(mysqli_num_rows($result)==0){
//           mysqli_query($this->conn,"INSERT INTO user_api (user_id, api_key, create_time) VALUES ('".$user_id."', '".$random."', '".$current_time_in_ist."')");
//               $sql="SELECT * FROM `user_api` WHERE user_id='".$user_id."'";
//          	$result=mysqli_query($this->conn, $sql);
//           return $result->fetch_array();
//       }else{        	
//         return $result->fetch_array();
//         }
//     }
//     }


//   public function top_services()
// {
//     $final = [];

//     $sql = mysqli_query(
//         $this->conn,
//         "SELECT * FROM top_services WHERE status = 1 ORDER BY id DESC"
//     );

//     if (!$sql) {
//         return $final;
//     }

//     while ($row = mysqli_fetch_assoc($sql)) {

//         /* =====================
//           Resolve Server
//         ===================== */
//         $server_sql = mysqli_query(
//             $this->conn,
//             "SELECT server_name 
//              FROM otp_server 
//              WHERE id = '" . $row['server_name'] . "' 
//              LIMIT 1"
//         );

//         if (!$server_sql || mysqli_num_rows($server_sql) !== 1) {
//             continue; // skip broken server link
//         }

//         $server_data = mysqli_fetch_assoc($server_sql);

//         /* =====================
//           Resolve Service
//         ===================== */
//         $service_sql = mysqli_query(
//             $this->conn,
//             "SELECT id, service_name, service_id 
//              FROM service 
//              WHERE id = '" . $row['service_id'] . "' 
//              LIMIT 1"
//         );

//         if (!$service_sql || mysqli_num_rows($service_sql) !== 1) {
//             continue; // skip broken service link
//         }

//         $service_data = mysqli_fetch_assoc($service_sql);

//         /* =====================
//           Resolve Service Icon
//         ===================== */
//         $icon_sql = mysqli_query(
//             $this->conn,
//             "SELECT img_url 
//              FROM service_icon 
//              WHERE short_code = '" . $service_data['service_id'] . "' 
//              LIMIT 1"
//         );

//         if ($icon_sql && mysqli_num_rows($icon_sql) === 1) {
//             $img_data = mysqli_fetch_assoc($icon_sql);
//             $img_url  = $img_data['img_url'];
//         } else {
//             $img_url = "https://i.ibb.co/ySRhxqh/default.png";
//         }

//         /* =====================
//           Final Payload
//         ===================== */
//         $final[] = [
//             'service_name'  => $service_data['service_name'],
//             'service_code'  => $service_data['service_id'], // wa, tg, etc
//             'service_price' => 0, // placeholder
//             'server_name'   => $server_data['server_name'],
//             'service_logo'  => $img_url,
//         ];
//     }

//     return $final;
// }

//     /* =====================================================
//   PAYMENT HELPERS (ADDED – DO NOT TOUCH EXISTING CODE)
//   ===================================================== */

// /**
//  * Prevent duplicate transaction credit
//  */
// public function transactionExists($txn_id){
//     $txn_id = mysqli_real_escape_string($this->conn, $txn_id);
//     $sql = mysqli_query(
//         $this->conn,
//         "SELECT id FROM user_transaction WHERE txn_id='$txn_id' AND status='1' LIMIT 1"
//     );
//     return mysqli_num_rows($sql) > 0;
// }

// /**
//  * Credit wallet balance (NGN)
//  */
// public function creditWalletAmount($user_id, $amount){
//     $amount = floatval($amount);
//     if ($amount <= 0) return false;

//     mysqli_query(
//         $this->conn,
//         "UPDATE user_wallet SET balance = balance + $amount WHERE user_id='$user_id'"
//     );
//     return true;
// }

// /**
//  * Log successful payment
//  */
// public function logPaymentTransaction($user_id, $amount, $type, $txn_id){
//     $amount = floatval($amount);
//     $type   = mysqli_real_escape_string($this->conn, $type);
//     $txn_id = mysqli_real_escape_string($this->conn, $txn_id);

//     mysqli_query($this->conn, "
//         INSERT INTO user_transaction 
//         (user_id, amount, date, type, txn_id, status)
//         VALUES ('$user_id', '$amount', NOW(), '$type', '$txn_id', '1')
//     ");
// }

// /**
//  * USD → NGN conversion (for Cryptomus)
//  */
// public function convertUsdToNgn($usd){
//     $usd = floatval($usd);
//     if ($usd <= 0) return 0;

//     $res = @file_get_contents(
//         'https://api.coingecko.com/api/v3/simple/price?ids=tether&vs_currencies=ngn'
//     );
//     if (!$res) return 0;

//     $data = json_decode($res, true);
//     if (!isset($data['tether']['ngn'])) return 0;

//     return round($usd * floatval($data['tether']['ngn']), 2);
// }

  
    
// }


class radiumsahil {
    public $conn;

    public function __construct() {
        $this->conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }
    
    public function get_token(){
        return $_SESSION['token'];
    }    

    public function check_token($token){
        $stmt = $this->conn->prepare("SELECT * FROM `login_token` WHERE token=? AND status='1'");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows == 0){
            return false;
        } else {
            $data = $result->fetch_assoc();
            $user_id = $data['user_id'];

            // Secure Wallet Check
            $stmt2 = $this->conn->prepare("SELECT * FROM `user_wallet` WHERE user_id=?");
            $stmt2->bind_param("s", $user_id);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            
            // Handle missing wallet data gracefully
            if($result2->num_rows == 0){
                $xy = $this->check_activities(0, 0, 0, $user_id);
            } else {
                $data1 = $result2->fetch_assoc();
                $xy = $this->check_activities($data1['balance'], $data1['total_otp'], $data1['total_recharge'], $user_id);
            }

            // Secure User Data Check
            $stmt3 = $this->conn->prepare("SELECT * FROM `user_data` WHERE id=? AND status='1'");
            $stmt3->bind_param("s", $user_id);
            $stmt3->execute();
            if($stmt3->get_result()->num_rows == 0){
                return false;
            } else {
                return $user_id;
            }
        }
    }

    public function balancedata() {
        $token = $this->get_token();                
        $user_id = $this->check_token($token);
        if($user_id === false){
            return array('balance' => 0, 'total_recharge' => 0, 'total_otp' => 0);
        } else {
            $stmt = $this->conn->prepare("SELECT * FROM `user_wallet` WHERE user_id=?");
            $stmt->bind_param("s", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if($result->num_rows == 0) return array('balance' => 0, 'total_recharge' => 0, 'total_otp' => 0);
            return $result->fetch_array();        
        }    
    }

    public function userdata(){
        $token = $this->get_token();                
        $user_id = $this->check_token($token);
        if($user_id === false){
            return false;
            // return array('name' => 'Unknown', 'email' => 'N/A');
        } else {
            $stmt = $this->conn->prepare("SELECT * FROM `user_data` WHERE id=?");
            $stmt->bind_param("s", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_array();                    
        }    
    }

    public function userwallet(){
        $token = $this->get_token();                
        $user_id = $this->check_token($token);
        if($user_id === false){
            return array('balance' => 0, 'total_recharge' => 0, 'total_otp' => 0);
        } else {
            $stmt = $this->conn->prepare("SELECT * FROM `user_wallet` WHERE user_id=?");
            $stmt->bind_param("s", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if($result->num_rows == 0) return array('balance' => 0, 'total_recharge' => 0, 'total_otp' => 0);
            return $result->fetch_array();                    
        }    
    }

    public function all_server(){
        $final = array(); 
        $sql = mysqli_query($this->conn, "SELECT * FROM `otp_server` WHERE status='1'");
        while ($row = $sql->fetch_array()) {
            array_push($final, array(
                'id' => $row['id'],
                'server_name' => $row['server_name'],
            ));
        }
        return $final;
    }

    public function number_history(){
        $token = $this->get_token();                
        $user_id = $this->check_token($token);
        if($user_id === false){
            return array();
        } else {
            $final = array(); 
            $stmt = $this->conn->prepare("SELECT * FROM `active_number` WHERE user_id=? ORDER BY id DESC");
            $stmt->bind_param("s", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_array()) {
                $stmt_icon = $this->conn->prepare("SELECT * FROM service_icon WHERE short_code=?");
                $stmt_icon->bind_param("s", $row['service_id']);
                $stmt_icon->execute();
                $icon_res = $stmt_icon->get_result();
                
                if($icon_res->num_rows == 1){
                    $img_data = $icon_res->fetch_assoc();
                    $img_url = $img_data['img_url'];
                } else {
                    $img_url = "https://i.ibb.co/ySRhxqh/default.png";
                }
                
                $timestamp = strtotime($row['buy_time']);
                $formattedDate = date("M d, Y - h:i A", $timestamp);   
                
                array_push($final, array(
                    'number' => $row['number'],
                    'service_name' => $row['service_name'],
                    'server_id' => $row['server_id'],
                    'buy_time' => $formattedDate,  
                    'sms' => $row['sms_text'],    
                    'service_price' => $row['service_price'], 
                    'logo' =>  $img_url,
                    'status' => $row['status'],
                ));
            }
            return $final;
        }
    }

    public function all_service(){
        $final = array(); 
        $sql = mysqli_query($this->conn, "SELECT * FROM `categorys`");
        while ($row = $sql->fetch_array()) {
            array_push($final, array(
                'id' => $row['id'],
                'name' => $row['name'],
                'amount' => $row['amount'],
                'stock' => $row['total_stock'],
            ));
        }
        return $final;
    }

    public function account_history(){
        $token = $this->get_token();                
        $user_id = $this->check_token($token);
        if($user_id === false){
            return array();
        } else {
            $final = array(); 
            $stmt = $this->conn->prepare("SELECT * FROM `sold` WHERE user_id=?");
            $stmt->bind_param("s", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_array()) {
                array_push($final, array(
                    'name' => $row['name'],
                    'username' => $row['username'],
                    'password' => $row['password'],
                ));
            }
            return $final;
        }
    }  

    public function transaction_history(){
        $token = $this->get_token();                
        $user_id = $this->check_token($token);
        if($user_id === false){
            return array();
        } else {
            $final = array(); 
            $stmt = $this->conn->prepare("SELECT * FROM `user_transaction` WHERE user_id=? ORDER BY id DESC");
            $stmt->bind_param("s", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_array()) {
                array_push($final, array(
                    'amount' => $row['amount'],
                    'date' => $row['date'],
                    'type' => $row['type'],
                    'txn_id' => $row['txn_id'],  
                    'status' => $row['status'],    
                ));
            }
            return $final;
        }
    }

    public function closeConnection() {
        $this->conn->close();
    }

    public function negativebal($balance) {
        if ($balance < 0) {
            return "1";
        } else {
            return "2";
        }
    }

    public function check_activities($balance, $total_otp, $lifetime, $token) {
        $oauthid = $token;
        if ($this->negativebal($balance)==1 || $this->negativebal($total_otp)==1 || $this->negativebal($lifetime)==1 || ($balance > $lifetime)) {
            $stmt = $this->conn->prepare("SELECT * FROM user_data WHERE id = ? AND status = '1'");
            $stmt->bind_param("s", $oauthid);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return "Already Action";
            } else {
                $stmt2 = $this->conn->prepare("UPDATE user_data SET status = '1' WHERE id = ?");
                $stmt2->bind_param("s", $oauthid);
                $stmt2->execute();
                return "#1";
            }
        }
        return "No action required";
    }

    public function generateRandomString($length = 25) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $random_string = '';
        for ($i = 0; $i < $length; $i++) {
            $random_string .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $random_string;
    }

    public function generateRandomString32($length = 32) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $random_string = '';
        for ($i = 0; $i < $length; $i++) {
            $random_string .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $random_string;
    }

    public function refer_data(){
        $token = $this->get_token();                
        $user_id = $this->check_token($token);
        if($user_id === false){
            return array('own_code' => '');
        } else {       
            $stmt = $this->conn->prepare("SELECT * FROM `refer_data` WHERE user_id=?");
            $stmt->bind_param("s", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if($result->num_rows == 0){
                $random = $this->generateRandomString();
                $insert_stmt = $this->conn->prepare("INSERT INTO refer_data (user_id, balance, own_code, refer_by, transfer, total_earn) VALUES (?, '0', ?, '', '0','0')");
                $insert_stmt->bind_param("ss", $user_id, $random);
                $insert_stmt->execute();
                
                $stmt->execute();
                $new_result = $stmt->get_result();
                return $new_result->fetch_array();
            } else {        	
                return $result->fetch_array();
            }
        }
    }

    public function refer_users(){
        $token = $this->get_token();                
        $user_id = $this->check_token($token);
        if($user_id === false){
            return 0;
        } else {       
            $refers = $this->refer_data();
            $stmt = $this->conn->prepare("SELECT * FROM `refer_data` WHERE refer_by=?");
            $stmt->bind_param("s", $refers['own_code']);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->num_rows;
        }
    }

    public function recent_history(){
        $token = $this->get_token();                
        $user_id = $this->check_token($token);
        if($user_id === false){
            return array();
        } else {
            $final = array(); 
            $stmt = $this->conn->prepare("SELECT * FROM `user_transaction` WHERE user_id=? ORDER BY id DESC LIMIT 15");
            $stmt->bind_param("s", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_array()) {
                array_push($final, array(
                    'amount' => $row['amount'],
                    'date' => $row['date'],
                    'type' => $row['type'],
                    'txn_id' => $row['txn_id'],  
                    'status' => $row['status'],    
                ));
            }
            return $final;
        }
    }  

    public function api_data(){
        $token = $this->get_token();                
        $user_id = $this->check_token($token);
        if($user_id === false){
            return false;
        } else {     
            $stmt = $this->conn->prepare("SELECT * FROM `user_api` WHERE user_id=?");
            $stmt->bind_param("s", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if($result->num_rows == 0){
                $current_time_in_ist = date('Y-m-d H:i:s');        
                $random = $this->generateRandomString32();
                $insert_stmt = $this->conn->prepare("INSERT INTO user_api (user_id, api_key, create_time) VALUES (?, ?, ?)");
                $insert_stmt->bind_param("sss", $user_id, $random, $current_time_in_ist);
                $insert_stmt->execute();
                
                $stmt->execute();
                $new_result = $stmt->get_result();
                return $new_result->fetch_array();
            } else {        	
                return $result->fetch_array();
            }
        }
    }

    public function top_services(){
        $final = [];
        $sql = mysqli_query($this->conn, "SELECT * FROM top_services WHERE status = 1 ORDER BY id DESC");

        if (!$sql) { return $final; }

        while ($row = mysqli_fetch_assoc($sql)) {
            $stmt_server = $this->conn->prepare("SELECT server_name FROM otp_server WHERE id = ? LIMIT 1");
            $stmt_server->bind_param("s", $row['server_name']);
            $stmt_server->execute();
            $server_sql = $stmt_server->get_result();

            if ($server_sql->num_rows !== 1) continue; 
            $server_data = $server_sql->fetch_assoc();

            $stmt_service = $this->conn->prepare("SELECT id, service_name, service_id FROM service WHERE id = ? LIMIT 1");
            $stmt_service->bind_param("s", $row['service_id']);
            $stmt_service->execute();
            $service_sql = $stmt_service->get_result();

            if ($service_sql->num_rows !== 1) continue; 
            $service_data = $service_sql->fetch_assoc();

            $stmt_icon = $this->conn->prepare("SELECT img_url FROM service_icon WHERE short_code = ? LIMIT 1");
            $stmt_icon->bind_param("s", $service_data['service_id']);
            $stmt_icon->execute();
            $icon_sql = $stmt_icon->get_result();

            if ($icon_sql->num_rows === 1) {
                $img_data = $icon_sql->fetch_assoc();
                $img_url  = $img_data['img_url'];
            } else {
                $img_url = "https://i.ibb.co/ySRhxqh/default.png";
            }

            $final[] = [
                'service_name'  => $service_data['service_name'],
                'service_code'  => $service_data['service_id'], 
                'service_price' => 0, 
                'server_name'   => $server_data['server_name'],
                'service_logo'  => $img_url,
            ];
        }
        return $final;
    }

    /* =====================================================
       PAYMENT HELPERS 
       ===================================================== */

    public function transactionExists($txn_id){
        $stmt = $this->conn->prepare("SELECT id FROM user_transaction WHERE txn_id=? AND status='1' LIMIT 1");
        $stmt->bind_param("s", $txn_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    public function creditWalletAmount($user_id, $amount){
        $amount = floatval($amount);
        if ($amount <= 0) return false;
        
        // Secured mathematically and syntactically
        $stmt = $this->conn->prepare("UPDATE user_wallet SET balance = balance + ? WHERE user_id=?");
        $stmt->bind_param("ds", $amount, $user_id);
        $stmt->execute();
        return true;
    }

    public function logPaymentTransaction($user_id, $amount, $type, $txn_id){
        $amount = floatval($amount);
        $stmt = $this->conn->prepare("INSERT INTO user_transaction (user_id, amount, date, type, txn_id, status) VALUES (?, ?, NOW(), ?, ?, '1')");
        $stmt->bind_param("sdss", $user_id, $amount, $type, $txn_id);
        $stmt->execute();
    }

    public function convertUsdToNgn($usd){
        $usd = floatval($usd);
        if ($usd <= 0) return 0;

        $res = @file_get_contents('https://api.coingecko.com/api/v3/simple/price?ids=tether&vs_currencies=ngn');
        if (!$res) return 0;

        $data = json_decode($res, true);
        if (!isset($data['tether']['ngn'])) return 0;

        return round($usd * floatval($data['tether']['ngn']), 2);
    }
}
?>