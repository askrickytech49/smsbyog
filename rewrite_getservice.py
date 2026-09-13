import re

with open("c:\\xampp\\htdocs\\smsbyog\\api\\service\\getService.php", "r", encoding="utf-8") as f:
    content = f.read()

target = """        $server = mysqli_real_escape_string($conn, $_GET['server']);
        
        // Fetch server and API details
        $server_sql = mysqli_query($conn, "SELECT * FROM otp_server WHERE id='" . $server . "'");
        $server_data = mysqli_fetch_assoc($server_sql);
        
        $api_sql = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='" . $server_data['api_id'] . "'");
        $api_data = mysqli_fetch_assoc($api_sql);
        
        $conversion_rate = $api_data['rate'];
        $fixed_profit = $api_data['profit_amount'];
        // $markup_percent = $api_data['percentage'];

        $url = $api_data['api_url'] . '/stubs/handler_api.php';
        $country_code = $server_data['server_code'];"""

replacement = """        $country_code = mysqli_real_escape_string($conn, $_GET['server']);
        $server = $country_code; // Keeping $server variable name for compatibility with custom_price
        
        // Fetch API details for TigerSMS (Server 1 is API ID 8)
        $api_sql = mysqli_query($conn, "SELECT * FROM api_detail WHERE id='8'");
        $api_data = mysqli_fetch_assoc($api_sql);
        
        $conversion_rate = $api_data['rate'];
        $fixed_profit = $api_data['profit_amount'];

        $url = $api_data['api_url'] . '/stubs/handler_api.php';"""

if target in content:
    content = content.replace(target, replacement)
    with open("c:\\xampp\\htdocs\\smsbyog\\api\\service\\getService.php", "w", encoding="utf-8") as f:
        f.write(content)
    print("Replaced getService chunk successfully")
else:
    print("getService target not found")
