<?php
session_start();
include __DIR__ . '/../include/config.php';

// Already logged in as admin — go straight to dashboard
if (isset($_SESSION['token'])) {
    $check = mysqli_query($conn, "SELECT u.type FROM login_token lt JOIN user_data u ON lt.user_id = u.id WHERE lt.token='" . mysqli_real_escape_string($conn, $_SESSION['token']) . "' AND lt.status='1' AND u.type='admin' LIMIT 1");
    if (mysqli_num_rows($check) > 0) {
        header('Location: dashboard');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login – <?= htmlspecialchars($site_data['web_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }

        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            min-height: 100vh;
            margin: 0;
        }

        /* Mobile: card fills full screen */
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
            .auth-card::before {
                content: '';
                display: block;
                height: 6px;
                background: linear-gradient(135deg, #1a1a2e, #16213e);
                margin: -32px -24px 28px;
            }
        }

        .auth-card {
            max-width: 420px;
            width: 100%;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
        }
        .admin-badge {
            background: #e10700;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 999px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .form-control { height: 48px; border-radius: 10px; font-size: 15px; }
        .form-control:focus { border-color: #e10700; box-shadow: 0 0 0 0.2rem rgba(225,7,0,0.15); }
        .btn-admin {
            height: 48px;
            border-radius: 10px;
            font-weight: 700;
            background: #e10700;
            border-color: #e10700;
            color: #fff;
        }
        .btn-admin:hover { background: #c00600; border-color: #c00600; color: #fff; }

        /* Red eye toggle hover */
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

        #errorBox { display: none; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">

<div class="card auth-card p-4 my-4">
    <div class="text-center mb-4">
        <span class="admin-badge">Admin Panel</span>
        <h4 class="fw-bold mt-3 mb-1">Admin Sign In</h4>
        <p class="text-muted" style="font-size:14px;">Access restricted to administrators only</p>
    </div>

    <div id="errorBox" class="alert alert-danger text-center" style="font-size:14px;"></div>

    <form id="adminLoginForm">
        <div class="mb-3">
            <label class="form-label fw-semibold">Email address</label>
            <input type="email" id="email" class="form-control" placeholder="Enter admin email">
        </div>

        <div class="mb-4">
            <label class="form-label fw-semibold">Password</label>
            <div class="input-group">
                <input type="password" id="password" class="form-control" placeholder="Enter password"
                    style="border-radius:10px 0 0 10px;">
                <button type="button" id="togglePassword" tabindex="-1"
                    class="btn btn-outline-secondary"
                    style="border-radius:0 10px 10px 0; border-color:#dee2e6;">
                    <i class="bi bi-eye" id="eyeIcon"></i>
                </button>
            </div>
        </div>

        <button type="submit" id="loginBtn" class="btn btn-admin w-100">Sign In</button>
    </form>

    <p class="text-center text-muted mt-4" style="font-size:12px;">
        <a href="../login" style="color:#e10700;">Back to User Panel</a>
    </p>
</div>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
// Eye toggle
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

// Login submit
$('#adminLoginForm').on('submit', function(e) {
    e.preventDefault();

    const email    = $('#email').val().trim();
    const password = $('#password').val();
    const btn      = $('#loginBtn');
    const errorBox = $('#errorBox');

    if (!email || !password) {
        errorBox.text('Please enter both email and password.').show();
        return;
    }

    btn.prop('disabled', true).text('Signing in...');
    errorBox.hide();

    $.ajax({
        type: 'POST',
        url: '../api/auth/login',
        data: { email, password },
        dataType: 'json',
        success: function(res) {
            if (res.status == 1 || res.status === '1') {
                // Verify the logged-in user is actually an admin
                $.ajax({
                    type: 'GET',
                    url: 'check_admin.php',
                    dataType: 'json',
                    success: function(check) {
                        if (check.is_admin) {
                            window.location.href = 'dashboard';
                        } else {
                            // Log them back out — not an admin
                            $.get('../logout');
                            errorBox.text('Access denied. This account does not have admin privileges.').show();
                            btn.prop('disabled', false).text('Sign In');
                        }
                    }
                });
            } else {
                errorBox.text(res.msg || 'Invalid email or password.').show();
                btn.prop('disabled', false).text('Sign In');
            }
        },
        error: function() {
            errorBox.text('Server error. Please try again.').show();
            btn.prop('disabled', false).text('Sign In');
        }
    });
});
</script>
</body>
</html>
