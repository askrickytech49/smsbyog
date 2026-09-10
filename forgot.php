<?php
session_start();
include 'include/config.php';
include __DIR__ . '/include/mode_check.php';

if (isset($_SESSION['token'])) {
    redirect('dashboard');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Forgot Password – MyOgSms</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

    <link rel="shortcut icon" href="https://myogsms.com/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, #e10700, #fd0001);
            min-height: 100vh;
        }

        .auth-card {
            max-width: 420px;
            width: 100%;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 20px 40px rgba(0,0,0,.18);
        }

        .auth-logo img {
            max-width: 160px;
        }

        .btn-primary {
            height: 48px;
            border-radius: 10px;
            font-weight: 700;
            background-color: #e10700;
            border-color: #e10700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-primary:hover {
            background-color: #e96f00;
            border-color: #e96f00;
        }

        .small-text {
            font-size: 14px;
        }

        a {
            color: #e10700;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .instruction-box {
            background: #fff7f0;
            border: 1px solid #ffe0c2;
            border-radius: 12px;
            padding: 16px;
            font-size: 15px;
            color: #333;
        }
    @supports (-webkit-touch-callout: none) { input, textarea, select { font-size: 16px !important; } }
    * { scrollbar-width: none !important; } *::-webkit-scrollbar { display: none !important; }
    </style>
</head>

<body class="d-flex align-items-center justify-content-center">

<div class="card auth-card p-4 my-4">

    <div class="text-center auth-logo mb-4">
        <img src=https://myogsms.com/assets/myogsmslogo.png" alt="AuthPadi Logo">
    </div>

    <h4 class="text-center fw-bold mb-2">Forgot your password?</h4>

    <div class="instruction-box text-center mb-4">
        Contact Admin on Telegram with your <strong>account email</strong>
        to reset your password.
    </div>

    <a
        href="https://t.me/myogsocial"
        target="_blank"
        class="btn btn-primary w-100 mb-3"
    >
        Contact Admin on Telegram
    </a>

    <p class="text-center small-text">
        Remembered your password?
        <a href="login" class="fw-semibold">Sign in</a>
    </p>

    <p class="text-center text-muted mt-3" style="font-size:13px;">
        Protected by AuthPadi security
    </p>
</div>

<?php include('partial/custom_js.php'); ?>
</body>
</html>
