<?php
session_start();

require '../include/config.php';
require '../class/class.control.php';
require '../include/xixapay.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['token'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        exit;
    }

    // 🟢 EXTRACT REQUESTED PROVIDER CHANNELS
    $provider = isset($_POST['provider']) ? strtolower(trim($_POST['provider'])) : 'paymentpoint';
    
    if (!in_array($provider, ['paymentpoint', 'pocketfi'])) {
        echo json_encode(['success' => false, 'message' => 'Unsupported virtual account channel platform requested.']);
        exit;
    }

    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 100;

    $wallet = new radiumsahil();
    $user = $wallet->userdata();

    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'User not found or session expired'
        ]);
        exit;
    }

    $user_id = $user['id'];
    
    // 🟢 CHECK EXPLICIT PROVIDER ROWS
    $stmt = $conn->prepare("SELECT * FROM user_dynamic_va WHERE user_id = ? AND virtual_name = ? LIMIT 1");
    $stmt->bind_param("is", $user_id, $provider);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing = $result->fetch_assoc();

    if ($existing) {
        echo json_encode([
            "success" => true,
            "existing" => true,
            "accountNumber" => $existing['account_number'],
            "accountName" => $existing['account_name'],
            "bankName" => $existing['bank_name']
        ]);
        exit;
    }

    $response = '';
    
    if ($provider === 'pocketfi') {
        // Updated URL path extension targeting .ng instead of .co
        $endpoint = "https://api.pocketfi.ng/api/v1/virtual-accounts/create";
        $pocketfiSecret = defined('POCKETFI_API') ? POCKETFI_API : '';
        $businessId = defined('POCKETFI_BUSINESS') ? POCKETFI_BUSINESS : '';
        
        $headers = [
            "Authorization: Bearer {$pocketfiSecret}",
            "Content-Type: application/json"
        ];

        // 🟢 SPLIT USER FULLNAME STRING PROFILE FOR THE SEPARATE KEY VALUE MAPPINGS
        $nameParts = explode(' ', trim($user['name']), 2);
        $firstName = isset($nameParts[0]) ? $nameParts[0] : 'Ibrahim';
        $lastName  = isset($nameParts[1]) ? $nameParts[1] : 'Musa';
        
        // Clean phone strings to keep it standard
        $phoneNum = !empty($user['phone']) ? $user['phone'] : '09029163518';
        
        $response = virtualRequest($endpoint, "POST", [
            "first_name"  => $firstName,
            "last_name"   => $lastName,
            "phone"       => $phoneNum,
            "email"       => $user['email'],
            "businessId"  => $businessId,
            "bank"        => "kuda", 
            "nin"         => "",
            "bvn"         => "",
        ], $headers);

    } else {
        $endpoint = "https://api.paymentpoint.co/api/v1/createVirtualAccount";
        $secretKey  = defined('XIXA_SECRET') ? XIXA_SECRET : '';
        $apiKey     = defined('XIXA_API') ? XIXA_API : '';
        $businessId = defined('XIXA_BUSINESS') ? XIXA_BUSINESS : '';

        $headers = [
            "Authorization: Bearer {$secretKey}",
            "api-key: {$apiKey}",
            "Content-Type: application/json"
        ];
        
        $response = virtualRequest($endpoint, "POST", [
            "email"       => $user['email'],
            "name"        => $user['name'],
            "phoneNumber" => "08000000000",
            "bankCode"    => ["20946"],
            "businessId"  => $businessId
        ], $headers);
    }
    
    // -------------------------------------------------------------------------
    // 🟢 RESPONSE STRUCTURAL PARSING LAYER
    // -------------------------------------------------------------------------
    if ($provider === 'pocketfi') {
        if (isset($response['error'])) {
            echo json_encode([
                'success' => false,
                'message' => 'cURL Transport Layer Failed: ' . $response['error'],
                'debug'   => $response
            ]);
            exit;
        }
        // 🛠️ DEBUG CHECK B: Did the server return an HTTP error page or empty string?
        if (empty($response) || !isset($response['status'])) {
            echo json_encode([
                'success' => false,
                'message' => 'PocketFi sent an unparseable or blank response payload.',
                'raw_response_received' => $response, 
                'hint' => 'Check if POCKETFI_SECRET is empty or if the server threw a 500 error.'
            ]);
            exit;
        }

        if ($response['status'] !== true) {
            echo json_encode([
                'success' => false,
                'message' => 'PocketFi API explicitly rejected validation fields.',
                'debug'   => $response
            ]);
            exit;
        }
        
        if (!isset($response['banks'][0])) {
            echo json_encode([
                'success' => false,
                'message' => 'No account array records returned from PocketFi.',
            ]);
            exit;
        }

        $bank = $response['banks'][0];
        
        $accountNumber = $bank['accountNumber'];
        $accountName   = $bank['accountName'];
        $bankName      = $bank['bankName'];
        $reservedId    = 'N/A';
        
    } else {
        if (!isset($response['status']) || $response['status'] !== 'success') {
            echo json_encode([
                'success' => false,
                'message' => 'PaymentPoint API generation failed.',
                'debug'   => $response
            ]);
            exit;
        }

        if (!isset($response['bankAccounts'][0])) {
            echo json_encode([
                'success' => false,
                'message' => 'No account array records returned from PaymentPoint.',
            ]);
            exit;
        }

        $bank = $response['bankAccounts'][0];
        
        $accountNumber = $bank['accountNumber'];
        $accountName   = $bank['accountName'];
        $bankName      = $bank['bankName'];
        $reservedId    = isset($bank['Reserved_Account_Id']) ? $bank['Reserved_Account_Id'] : (isset($bank['reserved_account_id']) ? $bank['reserved_account_id'] : 'N/A');
    }

    // 🟢 SAFE SQL TRANSACTION STORAGE INSERTION LAYER WITH CORRECT VARIABLE MAP
    $stmt = $wallet->conn->prepare("
        INSERT INTO user_dynamic_va 
        (user_id, account_number, account_name, bank_name, reserved_id, virtual_name, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");

    $stmt->bind_param(
        "isssss",
        $user_id,
        $accountNumber, // 🟢 Typo Fix: Changed from $aacountNumber to $accountNumber
        $accountName,
        $bankName,
        $reservedId,
        $provider
    );

    $stmt->execute();

    echo json_encode([
        "success"       => true,
        "accountNumber" => $accountNumber,
        "accountName"   => $accountName,
        "bankName"      => $bankName,
        "amount"        => $amount
    ]);
    exit;

} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server script exception processing fail: ' . $e->getMessage()
    ]);
    exit;
}