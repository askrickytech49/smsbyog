<?php
session_start();
include __DIR__ . '/include/config.php';

// ── HARDCODED DEVELOPER CREDENTIALS ──────────────────────────────
// Only this email can access the dev panel
define('DEV_EMAIL',    '49rickyai@gmail.com');
// Password is verified against the DB user account (bcrypt)
// ─────────────────────────────────────────────────────────────────

$error = '';
$success = '';

// ── AUTH CHECK ────────────────────────────────────────────────────
if (!isset($_SESSION['dev_authenticated'])) {

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dev_login'])) {
        $pw = $_POST['password'] ?? '';

        // Fetch the password hash for the hardcoded dev email
        $em  = mysqli_real_escape_string($conn, DEV_EMAIL);
        $row = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT password FROM user_data WHERE email='$em' AND status='1' LIMIT 1"
        ));

        if ($row && password_verify($pw, $row['password'])) {
            $_SESSION['dev_authenticated'] = true;
            header('Location: dev');
            exit;
        } else {
            $error = 'Invalid password. Access denied.';
        }
    }

    // Show login form
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <title>Dev Access — SmsByOg</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    *{box-sizing:border-box;margin:0;padding:0;scrollbar-width:none}
    *::-webkit-scrollbar{display:none}
    body{font-family:'Poppins',sans-serif;background:#0f172a;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
    .card{background:#fff;border-radius:20px;padding:40px 36px;width:100%;max-width:380px;box-shadow:0 25px 60px rgba(0,0,0,.4)}
    .logo{text-align:center;margin-bottom:28px}
    .logo img{height:36px}
    .logo-text{font-size:22px;font-weight:800;color:#0f172a;letter-spacing:-.5px}
    .logo-text span{color:#e10700}
    .dev-badge{display:inline-block;background:#0f172a;color:#fff;font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;padding:3px 10px;border-radius:999px;margin-top:6px}
    h2{font-size:16px;font-weight:600;color:#0f172a;text-align:center;margin-bottom:4px}
    p{font-size:13px;color:#64748b;text-align:center;margin-bottom:24px}
    label{display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:6px}
    input{width:100%;height:46px;border:1.5px solid #e2e8f0;border-radius:10px;padding:0 14px;font-size:14px;font-family:'Poppins',sans-serif;outline:none;transition:border-color .2s}
    input:focus{border-color:#e10700;box-shadow:0 0 0 3px rgba(225,7,0,.1)}
    .btn{width:100%;height:46px;background:#e10700;color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:600;font-family:'Poppins',sans-serif;cursor:pointer;margin-top:20px;transition:background .2s}
    .btn:hover{background:#c00600}
    .error{background:#fee2e2;color:#991b1b;font-size:13px;padding:10px 14px;border-radius:8px;margin-bottom:16px;text-align:center}
    .hint{font-size:11px;color:#94a3b8;text-align:center;margin-top:16px}
  </style>
</head>
<body>
<div class="card">
  <div class="logo">
    <div class="logo-text">Sms<span>ByOg</span></div>
    <span class="dev-badge">Developer Panel</span>
  </div>
  <h2>Restricted Access</h2>
  <p>Enter your developer password to continue</p>

  <?php if ($error): ?>
  <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="post">
    <div>
      <label>Password</label>
      <input type="password" name="password" placeholder="Enter dev password" required autofocus>
    </div>
    <button class="btn" type="submit" name="dev_login">Access Dev Panel</button>
  </form>
  <p class="hint">Access restricted to authorized developer only</p>
</div>
</body>
</html>
    <?php
    exit;
}

// ── AUTHENTICATED — HANDLE TOGGLE ACTIONS ─────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['logout_dev'])) {
        unset($_SESSION['dev_authenticated']);
        header('Location: dev');
        exit;
    }

    if (isset($_POST['toggle_maintenance'])) {
        $cur = (int)($site_data['maintenance_mode'] ?? 0);
        $new = $cur ? 0 : 1;
        mysqli_query($conn, "UPDATE settings SET maintenance_mode='$new' WHERE id='1'");
        $success = $new ? 'Maintenance mode ENABLED — users will see maintenance page.' : 'Maintenance mode DISABLED — site is live.';
        // Reload settings
        $site_data['maintenance_mode'] = $new;
    }

    if (isset($_POST['toggle_paydev'])) {
        $cur = (int)($site_data['dev_payment_mode'] ?? 0);
        $new = $cur ? 0 : 1;
        mysqli_query($conn, "UPDATE settings SET dev_payment_mode='$new' WHERE id='1'");
        $success = $new ? 'Payment gate ENABLED — admins will see payment required screen.' : 'Payment gate DISABLED — admin access restored.';
        $site_data['dev_payment_mode'] = $new;
    }
}

// Reload fresh settings
$site_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM settings WHERE id='1'"));
$maintenance_on = (int)($site_data['maintenance_mode'] ?? 0);
$paydev_on      = (int)($site_data['dev_payment_mode'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <title>Dev Panel — SmsByOg</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    *{box-sizing:border-box;margin:0;padding:0;scrollbar-width:none}
    *::-webkit-scrollbar{display:none}
    body{font-family:'Poppins',sans-serif;background:#0f172a;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
    .panel{width:100%;max-width:480px}

    /* Header */
    .panel-header{text-align:center;margin-bottom:28px}
    .logo-text{font-size:26px;font-weight:800;color:#fff;letter-spacing:-.5px}
    .logo-text span{color:#e10700}
    .dev-badge{display:inline-block;background:#e10700;color:#fff;font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;padding:4px 12px;border-radius:999px;margin-top:8px}

    /* Cards */
    .toggle-card{background:#1e293b;border:1px solid #334155;border-radius:16px;padding:24px;margin-bottom:16px;transition:border-color .2s}
    .toggle-card.active{border-color:#e10700;box-shadow:0 0 0 1px #e10700}
    .card-top{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:16px}
    .card-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0}
    .card-icon.maintenance{background:rgba(245,158,11,.15);color:#f59e0b}
    .card-icon.paydev{background:rgba(225,7,0,.15);color:#e10700}
    .card-info h3{font-size:15px;font-weight:700;color:#f1f5f9;margin-bottom:4px}
    .card-info p{font-size:12px;color:#94a3b8;line-height:1.5}
    .status-pill{font-size:11px;font-weight:700;padding:4px 10px;border-radius:999px;white-space:nowrap;flex-shrink:0}
    .status-pill.on{background:rgba(225,7,0,.2);color:#fca5a5}
    .status-pill.off{background:rgba(100,116,139,.2);color:#94a3b8}
    .toggle-btn{width:100%;height:44px;border:none;border-radius:10px;font-size:13px;font-weight:700;font-family:'Poppins',sans-serif;cursor:pointer;transition:all .2s;letter-spacing:.3px}
    .toggle-btn.enable{background:#e10700;color:#fff}
    .toggle-btn.enable:hover{background:#c00600;transform:translateY(-1px)}
    .toggle-btn.disable{background:#1e293b;color:#94a3b8;border:1.5px solid #334155}
    .toggle-btn.disable:hover{background:#334155;color:#f1f5f9}

    /* Alert */
    .alert{padding:12px 16px;border-radius:10px;font-size:13px;margin-bottom:20px;text-align:center;font-weight:500}
    .alert.success{background:rgba(22,163,74,.15);color:#4ade80;border:1px solid rgba(22,163,74,.3)}

    /* Logout */
    .logout-btn{width:100%;background:none;border:1.5px solid #334155;color:#64748b;height:40px;border-radius:10px;font-size:13px;font-weight:600;font-family:'Poppins',sans-serif;cursor:pointer;margin-top:8px;transition:all .2s}
    .logout-btn:hover{border-color:#e10700;color:#e10700}

    /* Warning */
    .warning{background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.3);border-radius:10px;padding:12px 16px;margin-bottom:20px;font-size:12px;color:#fbbf24;line-height:1.5}
    .warning strong{display:block;margin-bottom:2px;font-size:13px}
  </style>
</head>
<body>
<div class="panel">

  <div class="panel-header">
    <div class="logo-text">Sms<span>ByOg</span></div>
    <br>
    <span class="dev-badge">Developer Control Panel</span>
  </div>

  <?php if ($success): ?>
  <div class="alert success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <div class="warning">
    <strong>Developer Access Only</strong>
    Changes made here affect all users immediately. Use with caution.
  </div>

  <!-- MAINTENANCE MODE -->
  <div class="toggle-card <?= $maintenance_on ? 'active' : '' ?>">
    <div class="card-top">
      <div class="card-info">
        <h3>User Maintenance Mode</h3>
        <p>When enabled, all user-facing pages show a "Scheduled Maintenance" screen. Admins are unaffected.</p>
      </div>
      <span class="status-pill <?= $maintenance_on ? 'on' : 'off' ?>"><?= $maintenance_on ? 'ON' : 'OFF' ?></span>
    </div>
    <form method="post">
      <button type="submit" name="toggle_maintenance"
        class="toggle-btn <?= $maintenance_on ? 'disable' : 'enable' ?>">
        Toggle on and off
      </button>
    </form>
  </div>

  <!-- PAYMENT GATE -->
  <div class="toggle-card <?= $paydev_on ? 'active' : '' ?>">
    <div class="card-top">
      <div class="card-info">
        <h3>Admin Payment Gate</h3>
        <p>When enabled, all admin panel pages show a "Complete Developer Payment" screen. Locks admin access until disabled.</p>
      </div>
      <span class="status-pill <?= $paydev_on ? 'on' : 'off' ?>"><?= $paydev_on ? 'ON' : 'OFF' ?></span>
    </div>
    <form method="post">
      <button type="submit" name="toggle_paydev"
        class="toggle-btn <?= $paydev_on ? 'disable' : 'enable' ?>">
        Toggle on and off
      </button>
    </form>
  </div>

  <!-- LOGOUT -->
  <form method="post">
    <button type="submit" name="logout_dev" class="logout-btn">Exit Dev Panel</button>
  </form>

</div>
</body>
</html>
