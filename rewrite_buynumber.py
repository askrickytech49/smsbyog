import re

with open("c:\\xampp\\htdocs\\smsbyog\\api\\service\\buynumber.php", "r", encoding="utf-8") as f:
    content = f.read()

target = """        $server = mysqli_real_escape_string($conn, $_GET['server']);
        $service = mysqli_real_escape_string($conn, $_GET['service']);
        $user_id = $check_token;

        // 1. Get Server and API Details
        $sql3 = mysqli_query($conn, "SELECT * FROM otp_server WHERE id='" . $server . "'");
        if (mysqli_num_rows($sql3) == 0) {
            echo '{"status":"500","message":"Server Not Found"}';
            exit;
        }
        $server_data = mysqli_fetch_assoc($sql3);
        $server_code = $server_data['server_code'];

        $sql4 = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='" . $server_data['api_id'] . "'");
        $api_data = mysqli_fetch_assoc($sql4);
        $api_key = $api_data['api_key'];
        $api_url = $api_data['api_url'];
        $conversion_rate = $api_data['rate'];
        $fixed_profit = $api_data['profit_amount'];

        // 2. Fetch REAL-TIME PRICE from API (Crucial Step)"""

replacement = """        $server_code = mysqli_real_escape_string($conn, $_GET['server']);
        $server = $server_code; // for custom_price and active_number db compatibility
        $service = mysqli_real_escape_string($conn, $_GET['service']);
        $user_id = $check_token;

        // 1. Get API Details for TigerSMS (Server 1 is API ID 8)
        $sql4 = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='8'");
        if (mysqli_num_rows($sql4) == 0) {
            echo '{"status":"500","message":"API Configuration Not Found"}';
            exit;
        }
        $api_data = mysqli_fetch_assoc($sql4);
        $api_key = $api_data['api_key'];
        $api_url = $api_data['api_url'];
        $conversion_rate = $api_data['rate'];
        $fixed_profit = $api_data['profit_amount'];

        // 2. Fetch REAL-TIME PRICE from API (Crucial Step)"""

if target in content:
    content = content.replace(target, replacement)
    with open("c:\\xampp\\htdocs\\smsbyog\\api\\service\\buynumber.php", "w", encoding="utf-8") as f:
        f.write(content)
    print("Replaced buynumber chunk successfully")
else:
    print("buynumber target not found")
