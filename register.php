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
    <title>Register – SmsByOg</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

    <link rel="shortcut icon" href="https://smsbyog.com/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- jQuery -->
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
            .auth-card::before {
                content: '';
                display: block;
                height: 6px;
                background: linear-gradient(135deg, #e10700, #fd0001);
                margin: -32px -24px 28px;
            }
        }

        .auth-card {
            max-width: 460px;
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
            opacity: 0.85;
        }

        .small-link {
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

        a:hover {
            text-decoration: underline;
        }
    @supports (-webkit-touch-callout: none) { input, textarea, select { font-size: 16px !important; } }
    </style>
</head>

<body class="d-flex align-items-center justify-content-center">

<div class="card auth-card p-4 my-4">

    <div class="text-center auth-logo mb-4">
        <img src="https://smsbyog.com/SmsByOglogo.png" alt="SmsByOg Logo">
    </div>

    <h4 class="text-center fw-bold mb-1">Create an account</h4>

    <p class="text-center text-muted mb-4">
        Get started with SmsByOg in Minutes
    </p>

    <form>

        <div class="row mb-3">

            <div class="col-md-6 mb-3 mb-md-0">
                <label class="form-label">First name</label>

                <input
                    type="text"
                    id="first_name"
                    class="form-control"
                >
            </div>

            <div class="col-md-6">
                <label class="form-label">Last name</label>

                <input
                    type="text"
                    id="last_name"
                    class="form-control"
                >
            </div>

        </div>

        <?php
        if (isset($_GET['ref'])) {
            echo '<input type="hidden" id="refer_id" value="' . htmlspecialchars($_GET['ref']) . '">';
        } else {
            echo '<input type="hidden" id="refer_id" value="">';
        }
        ?>

        <div class="mb-3">

            <label class="form-label">
                Email address
            </label>

            <input
                type="email"
                id="email"
                class="form-control"
                placeholder="Enter your email address"
            >

        </div>

        <div class="mb-3">

            <label class="form-label">
                Password
            </label>

            <div class="input-group">
                <input
                    type="password"
                    id="password"
                    class="form-control"
                    placeholder="Create a strong password"
                >
                <button type="button" id="togglePassword" tabindex="-1"
                    class="btn btn-outline-secondary"
                    style="border-radius:0 10px 10px 0; border-color:#dee2e6;">
                    <i class="bi bi-eye" id="eyeIcon"></i>
                </button>
            </div>

        </div>

        <!-- TERMS AND CONDITIONS -->
        <div class="form-check mb-4">

            <input
                class="form-check-input"
                type="checkbox"
                id="termsCheck"
            >

            <label
                class="form-check-label small-link"
                for="termsCheck"
            >
                I agree to the

                <a href="https://smsbyog.com/privacy">
                    Privacy Policy
                </a>

                and

                <a href="https://smsbyog.com/terms">
                    Terms of Service
                </a>

            </label>

        </div>

        <button
            type="button"
            id="register"
            class="btn btn-primary w-100"
        >
            Create Account
        </button>

    </form>

    <p class="text-center mt-4 small-link">

        Already have an account?

        <a href="login" class="fw-semibold">
            Sign in
        </a>

    </p>

    <p
        class="text-center text-muted mt-3"
        style="font-size:13px;"
    >
        Protected by SmsByOg security
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


<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<script>
function validateEmail(email) {
    const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    return emailRegex.test(email);
}

$(document).ready(function () {

    $("#register").on("click", function () {

        console.log("Register button clicked");

        var first_name = $("#first_name").val().trim();
        var last_name = $("#last_name").val().trim();
        var email = $("#email").val().trim();
        var password = $("#password").val();
        var refer_id = $("#refer_id").val();
        var termsCheck = $("#termsCheck").is(":checked");

        if (first_name === "") {
            alert("Please enter your first name.");
            return;
        }

        if (last_name === "") {
            alert("Please enter your last name.");
            return;
        }

        if (email === "") {
            alert("Please enter your email address.");
            return;
        }

        if (!validateEmail(email)) {
            alert("Please enter a valid email address.");
            return;
        }

        if (password === "") {
            alert("Please enter your password.");
            return;
        }

        if (!termsCheck) {
            alert("Please agree to the Privacy Policy and Terms of Service.");
            return;
        }

        var name = first_name + " " + last_name;

        $("#register")
            .prop("disabled", true)
            .text("Signing up...");

        $.ajax({
            type: "POST",
            url: "api/auth/register",
            data: {
                name: name,
                email: email,
                password: password,
                refer_id: refer_id
            },
            dataType: "json",

            success: function (json) {

                console.log("Server response:", json);

                $("#register")
                    .prop("disabled", false)
                    .text("Create Account");

                if (json.status === "1" || json.status === 1) {

                    alert(json.msg);

                    setTimeout(function () {
                        window.location.href = "account_created";
                    }, 1000);

                } else {
                    alert(json.msg || "Registration failed.");
                }
            },

            error: function (xhr, status, error) {

                console.error("AJAX ERROR:", error);
                console.log("SERVER RESPONSE:", xhr.responseText);

                $("#register")
                    .prop("disabled", false)
                    .text("Create Account");

                alert("Registration error. Check the browser console.");
            }
        });

    });

});
</script>


<!-- TikTok Pixel Code Start -->
<script>
!function (w, d, t) {

    w.TiktokAnalyticsObject = t;

    var ttq = w[t] = w[t] || [];

    ttq.methods = [
        "page",
        "track",
        "identify",
        "instances",
        "debug",
        "on",
        "off",
        "once",
        "ready",
        "alias",
        "group",
        "enableCookie",
        "disableCookie",
        "holdConsent",
        "revokeConsent",
        "grantConsent"
    ];

    ttq.setAndDefer = function(t, e) {
        t[e] = function() {
            t.push(
                [e].concat(
                    Array.prototype.slice.call(arguments, 0)
                )
            );
        };
    };

    for (var i = 0; i < ttq.methods.length; i++) {
        ttq.setAndDefer(ttq, ttq.methods[i]);
    }

    ttq.instance = function(t) {

        var e = ttq._i[t] || [];

        for (var n = 0; n < ttq.methods.length; n++) {
            ttq.setAndDefer(e, ttq.methods[n]);
        }

        return e;

    };

    ttq.load = function(e, n) {

        var r = "https://analytics.tiktok.com/i18n/pixel/events.js";

        o = n && n.partner;

        ttq._i = ttq._i || [];

        ttq._i[e] = [];

        ttq._i[e]._u = r;

        ttq._t = ttq._t || {};

        ttq._t[e] = +new Date;

        ttq._o = ttq._o || {};

        ttq._o[e] = n || {};

        n = document.createElement("script");

        n.type = "text/javascript";

        n.async = !0;

        n.src = r + "?sdkid=" + e + "&lib=" + t;

        e = document.getElementsByTagName("script")[0];

        e.parentNode.insertBefore(n, e);

    };

    ttq.load('D5NKGLJC77UFLMP0BDJG');

    ttq.page();

}(window, document, 'ttq');
</script>
<!-- TikTok Pixel Code End -->

</body>
</html>