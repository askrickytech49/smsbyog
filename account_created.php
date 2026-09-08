<?php
session_start();
include 'include/config.php';
require_once __DIR__ . '/class/class.control.php';

if (isset($_SESSION['token'])) {
    redirect('dashboard');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Account Created – smsbyog</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="shortcut icon" href="https://smsbyog.com/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, #e10700, #fd0001);
            min-height: 100vh;
        }

        /* ===== CARD ===== */
        .auth-card {
            max-width: 420px;
            width: 100%;
            border-radius: 18px;
            background: #ffffff;
            box-shadow: 0 25px 60px rgba(0,0,0,.2);
            animation: fadeUp .6s ease;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .auth-logo img {
            max-width: 150px;
        }

        /* ===== SUCCESS ICON ===== */
        .success-icon {
            font-size: 72px;
            color: #e10700;
            animation: pop .5s ease;
        }

        @keyframes pop {
            0% { transform: scale(.6); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }

        /* ===== BUTTON ===== */
        .btn-primary {
            height: 52px;
            border-radius: 14px;
            font-weight: 800;
            background-color: #e10700;
            border-color: #e10700;

            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;

            transition: all .25s ease;
        }

        .btn-primary:hover {
            opacity: 0.5;
            /*background-color: #e96f00;*/
            /*border-color: #e96f00;*/
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255,122,0,.35);
        }

        .btn-primary i {
            font-size: 18px;
        }

        /* ===== TEXT ===== */
        .title {
            font-weight: 800;
            color: #1a1a1a;
        }

        .lead-text {
            color: #333;
            font-weight: 600;
            font-size: 15px;
        }

        .welcome-note {
            font-size: 14px;
            line-height: 1.7;
            color: #444;
        }

        .next-steps {
            background: #fff5eb;
            border: 1px solid #ffe0c2;
            border-radius: 12px;
            padding: 14px;
            font-size: 14px;
            color: #3a2a1a;
        }

        .next-steps i {
            color: #e10700;
        }
    </style>
</head>

<body class="d-flex align-items-center justify-content-center">

<div class="card auth-card p-4 text-center">
    <div class="auth-logo mb-3">
        <img src="https://smsbyog.com/myogsmslogo.png" alt="smsbyog Logo">
    </div>

    <div class="success-icon mb-3">
        <i class="bi bi-check-circle-fill"></i>
    </div>

    <h4 class="title mb-2">Account Created Successfully 🎉</h4>

    <p class="lead-text mb-3">
        Welcome to <strong>smsbyog</strong> — your all-in-one platform for
        fast, secure, and reliable virtual numbers and SMS verification.
    </p>

    <p class="welcome-note mb-3">
        Your account is fully set up and ready to go. With smsbyog, you can
        receive OTPs, verify accounts, and manage SMS services seamlessly
        all in just a few clicks.
    </p>

    <div class="next-steps mb-4 text-start">
        <strong class="d-block mb-2">🚀 What to do next:</strong>
        <div class="mb-1"><i class="bi bi-arrow-right-circle"></i> Log in to your dashboard</div>
        <div class="mb-1"><i class="bi bi-wallet2"></i> Fund your wallet to add balance</div>
        <div class="mb-1"><i class="bi bi-sim"></i> Get a virtual number instantly</div>
        <div><i class="bi bi-chat-dots"></i> Receive SMS & OTPs with ease</div>
    </div>

    <a href="login" class="btn btn-primary w-100">
        <i class="bi bi-box-arrow-in-right"></i>
        <span>Proceed to Login</span>
    </a>

    <p class="text-muted mt-4" style="font-size:13px;">
        You’re officially ready to get started 🚀 and we are happy to have you onboard.
    </p>
</div>

<!-- TikTok Pixel Code Start -->
<script>
!function (w, d, t) {
  w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie","holdConsent","revokeConsent","grantConsent"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(
var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var r="https://analytics.tiktok.com/i18n/pixel/events.js",o=n&&n.partner;ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=r,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};n=document.createElement("script")
;n.type="text/javascript",n.async=!0,n.src=r+"?sdkid="+e+"&lib="+t;e=document.getElementsByTagName("script")[0];e.parentNode.insertBefore(n,e)};


  ttq.load('D5NKGLJC77UFLMP0BDJG');
  ttq.page();
}(window, document, 'ttq');
</script>
<!-- TikTok Pixel Code End -->

</body>
</html>
