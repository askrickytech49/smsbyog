<?php
session_start();

include 'include/config.php';

if (isset($_SESSION['token'])) {
    redirect('dashboard');
}

if (isset($_COOKIE['remember_me'])) {

    require __DIR__ . '/class/class.control.php';

    $_SESSION['token'] = $_COOKIE['remember_me'];

    $wallet = new radiumsahil();

    if ($wallet->balancedata() !== false) {
        redirect('dashboard');
    } else {
        session_destroy();
        setcookie("remember_me", "", time() - 3600, "/");
    }
}

$msg1 = null;
$button_msg = null;
$button_url = null;

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === "not_found") {
        $msg1 = "Account not found. Please register to continue.";
        $button_msg = "Register Now";
        $button_url = "register";
    } elseif ($_GET['msg'] === "block") {
        $msg1 = "Your account has been blocked. Please contact support.";
        $button_msg = "Contact Support";
        $button_url = $site_data['support_url'];
    } else {
        $msg1 = "You don’t have permission to access this page.";
        $button_msg = "Go Home";
        $button_url = "index";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login – SmsByOg</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

    <link rel="shortcut icon" href="https://smsbyog.com/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <wc-toast id="tt" position="top-right"></wc-toast>

    <style>
        * { box-sizing: border-box; }

        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, #e10700, #fd0001);
            min-height: 100vh;
            margin: 0;
        }

        /* On mobile: card fills full screen, no red showing */
        @media (max-width: 480px) {
            body {
                display: block !important;
                background: #ffffff;
                padding: 0;
            }
            .auth-card {
                max-width: 100% !important;
                width: 100% !important;
                min-height: 100vh;
                border-radius: 0 !important;
                box-shadow: none !important;
                margin: 0 !important;
                padding: 32px 24px !important;
            }
            /* Red top bar on mobile */
            .auth-card::before {
                content: '';
                display: block;
                height: 6px;
                background: linear-gradient(135deg, #e10700, #fd0001);
                margin: -32px -24px 28px;
            }
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

        .form-control {
            height: 48px;
            border-radius: 10px;
            font-size: 15px;
        }

        .form-control:focus {
            border-color: #e10700;
            box-shadow: 0 0 0 0.2rem rgba(225,7,0,.15);
        }

        .btn-primary {
            height: 48px;
            border-radius: 10px;
            font-weight: 700;
            background-color: #e10700;
            border-color: #e10700;
        }

        .btn-primary:hover {
            background-color: #c00600;
            border-color: #c00600;
        }

        .small-text {
            font-size: 14px;
        }

        .input-group .btn-outline-secondary {
            border-color: #dee2e6;
            color: #6b7280;
            transition: all .2s;
        }

        .input-group .btn-outline-secondary:hover {
            background-color: #e10700;
            border-color: #e10700;
            color: #ffffff;
        }

        a {
            color: #e10700;
            text-decoration: none;
        }
    @supports (-webkit-touch-callout: none) { input, textarea, select { font-size: 16px !important; } }
    * { scrollbar-width: none !important; } *::-webkit-scrollbar { display: none !important; }
    </style>
</head>

<body class="d-flex align-items-center justify-content-center">

<div class="card auth-card p-4 my-4">

    <div class="text-center auth-logo mb-4">
        <img src="https://smsbyog.com/SmsByOglogo.png" alt="SmsByOg Logo">
    </div>

    <h4 class="text-center fw-bold mb-1">Welcome back</h4>
    <p class="text-center text-muted mb-4">Sign in to your SmsByOg account</p>

    <?php if ($msg1): ?>
        <div class="alert alert-warning text-center small-text">
            <?= htmlspecialchars($msg1) ?><br>
            <a href="<?= $button_url ?>" class="fw-semibold"><?= $button_msg ?></a>
        </div>
    <?php endif; ?>

    <form>
        <div class="mb-3">
            <label class="form-label">Email address</label>
            <input type="email" id="email" class="form-control" placeholder="Enter your email address">
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <div class="input-group">
                <input type="password" id="password" class="form-control" placeholder="••••••••">
                <button type="button" class="btn btn-outline-secondary" id="togglePassword" tabindex="-1"
                    style="border-radius:0 10px 10px 0; border-color:#dee2e6;">
                    <i class="bi bi-eye" id="eyeIcon"></i>
                </button>
            </div>
        </div>

        <div class="d-flex justify-content-end mb-4">
            <a href="forgot" class="small-text">Forgot password?</a>
        </div>

        <button type="button" id="login" class="btn btn-primary w-100">
            Sign In
        </button>
    </form>

    <p class="text-center mt-4 small-text">
        Don’t have an account? <a href="register" class="fw-semibold">Sign up</a>
    </p>

    <p class="text-center text-muted mt-3" style="font-size:13px;">
        Protected by SmsByOg<br>
        <a href="https://smsbyog.com/privacy">Privacy</a> ·
        <a href="https://smsbyog.com/terms">Terms</a>
    </p>
</div>


<script>
document.getElementById('togglePassword').addEventListener('click', function () {
    const pwd = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');
    if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        pwd.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
});
</script>

<!--<script src="js/signin.js"></script>-->
<?php include('partial/custom_js.php'); ?>

</body>
</html>
