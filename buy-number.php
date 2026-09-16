<!DOCTYPE html>
<?php
session_start();
include __DIR__ . '/include/config.php';

// Check which APIs are active
$api_status = [];
$api_q = mysqli_query($conn, "SELECT id, is_active FROM api_detail");
while($ar = mysqli_fetch_assoc($api_q)) $api_status[(int)$ar['id']] = (int)$ar['is_active'];
$s1_on   = ($api_status[8] ?? 1) == 1; // TigerSMS
$s2_on   = ($api_status[2] ?? 1) == 1; // 5sim
$usa_on  = ($api_status[1] ?? 1) == 1; // VerifySMS
$ca_on   = ($api_status[3] ?? 1) == 1; // DinoMMO
?>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Buy Virtual Numbers | user.smsbyog.com</title>
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="user.smsbyog.com">
  <meta property="og:title" content="Buy Virtual Numbers | user.smsbyog.com">
  <meta property="og:description" content="Choose a server and buy virtual numbers for OTP verification.">
  <meta property="og:url" content="https://user.smsbyog.com/buy-number.php">
  <meta property="og:image" content="https://user.smsbyog.com/assets/images/Weblink.webp">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Buy Virtual Numbers | user.smsbyog.com">
  <meta name="twitter:image" content="https://user.smsbyog.com/assets/images/Weblink.webp">

  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700&display=swap" rel="stylesheet">


  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Nunito Sans", system-ui, -apple-system, BlinkMacSystemFont, sans-serif;

    }

    body {
      background: linear-gradient(135deg, #120a02, #070300);
      color: #fef3c7;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .container {
      max-width: 850px;
      width: 100%;
      background: #0b0501;
      border-radius: 14px;
      padding: 30px;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.7);
    }

    .header {
      text-align: center;
      margin-bottom: 25px;
    }

    .header h1 {
      font-size: 2rem;
      margin-bottom: 10px;
      color: #e10700;
    }

    .header p {
      font-size: 1rem;
      line-height: 1.6;
      color: white;
    }

    .cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 20px;
      margin-top: 25px;
    }

    .card {
      background: #0b0501;
      border: 1px solid #e10700;
      border-radius: 12px;
      padding: 22px;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 35px rgba(249, 115, 22, 0.25);
    }

    .card h3 {
      margin-bottom: 10px;
      color: #e10700;
    }

    .card p {
      font-size: 0.95rem;
      line-height: 1.6;
      color: #fff;
      margin-bottom: 18px;
    }

    .btn {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      width: 100%;
      padding: 12px;
      border-radius: 8px;
      font-weight: 600;
      text-decoration: none;
      color: white;
      background: #e10700;
      transition: all 0.3s ease;
    }

    .btn:hover {
      background: black;
      border:1PX solid #e10700 ;
    }

    .btn.secondary {
      background: #fb923c;
    }

    .btn.secondary:hover {
      background: #e10700;
    }

    .btn.usa {
      background: #fdba74;
    }

    .btn.usa:hover {
      background: #fb923c;
    }

    .footer-btn {
      margin-top: 30px;
      text-align: center;
    }

    .footer-btn a {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 12px 25px;
      border-radius: 10px;
      background: transparent;
      border: 1px solid #e10700;
      color: #e10700;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .footer-btn a:hover {
      background: black;
      color: white;
      border: 1px solid green;
    }

    @media (max-width: 480px) {
      .header h1 {
        font-size: 1.6rem;
      }
    }
  </style>
  <style>
    :root {
      --brand-red: #e10700;
      --ink: #172033;
      --muted: #718096;
      --surface: #ffffff;
      --page: #f5f7fa;
      --line: #e6eaf0;
    }

    body {
      align-items: flex-start;
      background: var(--page);
      color: var(--ink);
      padding: 28px 16px 48px;
    }

    .container {
      max-width: 980px;
      padding: 34px;
      background: var(--surface);
      border: 1px solid var(--line);
      border-radius: 20px;
      box-shadow: 0 16px 40px rgba(15, 23, 42, .07);
    }

    .header {
      position: relative;
      margin-bottom: 30px;
      padding: 4px 12px 26px;
      border-bottom: 1px solid var(--line);
    }

    .header::before {
      content: "";
      display: block;
      width: 42px;
      height: 4px;
      margin: 0 auto 18px;
      border-radius: 99px;
      background: var(--brand-red);
    }

    .header h1 {
      color: var(--ink);
      font-size: 28px;
      font-weight: 800;
      letter-spacing: -.3px;
    }

    .header h1 i { color: var(--brand-red); margin-right: 8px; }

    .header p {
      max-width: 570px;
      margin: 0 auto;
      color: var(--muted);
      font-size: 14px;
      line-height: 1.65;
    }

    .cards {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 16px;
      margin-top: 0;
    }

    .card {
      display: flex;
      flex-direction: column;
      min-height: 238px;
      padding: 22px;
      background: #fbfcfd;
      border: 1px solid var(--line);
      border-radius: 14px;
      box-shadow: none;
      transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    }

    .card:hover {
      border-color: #ffb9b4;
      box-shadow: 0 10px 24px rgba(225, 7, 0, .08);
      transform: translateY(-2px);
    }

    .card h3 {
      margin-bottom: 10px;
      color: var(--ink);
      font-size: 18px;
      font-weight: 800;
    }

    .card h3::before {
      content: "";
      display: inline-block;
      width: 8px;
      height: 8px;
      margin: 0 8px 2px 0;
      border-radius: 50%;
      background: var(--brand-red);
    }

    .card p {
      flex: 1;
      margin-bottom: 22px;
      color: var(--muted);
      font-size: 13px;
      line-height: 1.65;
    }

    .btn {
      min-height: 44px;
      padding: 11px 14px;
      border: 1px solid var(--brand-red);
      border-radius: 9px;
      background: var(--brand-red);
      color: #fff;
      font-size: 13px;
      font-weight: 750;
    }

    .btn:hover {
      border-color: #bd0600;
      background: #bd0600;
      color: #fff;
    }

    .footer-btn { margin-top: 24px; }

    .footer-btn a {
      padding: 10px 18px;
      border: 1px solid var(--line);
      border-radius: 9px;
      background: #fff;
      color: var(--muted);
      font-size: 13px;
    }

    .footer-btn a:hover {
      border-color: var(--brand-red);
      background: #fff8f5;
      color: var(--brand-red);
    }

    @media (max-width: 640px) {
      body { padding: 12px 10px 30px; }
      .container { padding: 24px 14px; border-radius: 16px; }
      .header { padding: 0 4px 22px; margin-bottom: 20px; }
      .header h1 { font-size: 22px; }
      .header p { font-size: 13px; }
      .cards { grid-template-columns: 1fr; gap: 12px; }
      .card { min-height: 0; padding: 18px; }
      .card h3 { font-size: 17px; }
      .footer-btn a { width: 100%; justify-content: center; }
    }
  </style>
</head>
<body>

  <div class="container">
    <div class="header">
      <h1><i class="bi bi-phone-fill"></i> Buy Virtual Numbers</h1>
      <p>
        Choose the server that best fits your verification needs.
        Each option below is optimized to reduce OTP failure
        and maximize success rate.
      </p>
    </div>

    <div class="cards">

      <?php if($s1_on): ?>
      <div class="card">
        <h3>Server 1</h3>
        <p>
          Best for general verifications and everyday platforms.
          Balanced speed, availability, and reliability.
        </p>
        <a href="buy-number-1" class="btn">
          <i class="bi bi-hdd-network-fill"></i>
          Buy Numbers (Server 1)
        </a>
      </div>
      <?php endif; ?>

      <?php if($s2_on): ?>
      <div class="card">
        <h3>Server 2</h3>
        <p>
          Designed for stricter platforms with higher rejection rates.
          Fresh number pools with better success odds.
        </p>
        <a href="buy-number-server2" class="btn">
          <i class="bi bi-cloud-check-fill"></i>
          Buy Numbers (Server 2)
        </a>
      </div>
      <?php endif; ?>

      <?php if($usa_on): ?>
      <div class="card">
        <h3>USA Only</h3>
        <p>
          Dedicated United States numbers for platforms that
          strictly enforce US-only verification.
        </p>
        <a href="buy-usa-number" class="btn">
          <i class="bi bi-flag-fill"></i>
          Buy Numbers (USA Only)
        </a>
      </div>
      <?php endif; ?>
      
      <?php if($ca_on): ?>
      <div class="card">
        <h3>USA Only Server 2</h3>
        <p>
          Dedicated United States & Canada numbers for platforms that
          strictly enforce US-only verification, Cheap and Fast.
        </p>
        <a href="buy-us-ca-number" class="btn">
          <i class="bi bi-flag-fill"></i>
          Buy Numbers (USA Only)
        </a>
      </div>
      <?php endif; ?>

    </div>

    <div class="footer-btn">
      <a href="/dashboard">
        Back to Dashboard
      </a>
    </div>
  </div>

</body>
</html>
