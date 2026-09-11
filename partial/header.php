<!DOCTYPE html>
<html lang="en">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="description" content=""Discover the power of seamless communication with our virtual number service! Enhance your business's accessibility and professionalism with virtual numbers that effortlessly connect you with clients worldwide. Our user-friendly platform ensures easy setup and management, empowering you to streamline communication and boost productivity. Explore our range of customizable virtual number solutions today and elevate your business's presence online!"">
  <meta name="keywords" content="Virtual numbers, Virtual phone numbers, Virtual telephony, Virtual communication solutions">
  <meta name="author" content="smsbyog">
  <link rel="icon" href="https://smsbyog.com/favicon.png" type="image/x-icon">
  <link rel="shortcut icon" href="https://smsbyog.com/favicon.png" type="image/x-icon">
  <title><?php echo $page_title; ?></title>
<?php include('style.php'); ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body>

<?php if (isset($_SESSION['ghost_admin_token'])): ?>
<style>
  /* Ghost Mode – shift entire page down 40px for the banner */
  body {
    padding-top: 40px !important;
  }
  /* Fixed sidebar: push down from viewport top */
  .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper {
    top: 40px !important;
    height: calc(100vh - 40px) !important;
  }
  /* Fixed header: push down from viewport top */
  .page-wrapper.compact-wrapper .page-header {
    top: 40px !important;
  }
</style>
<div style="
    position: fixed;
    top: 0; left: 0; right: 0;
    z-index: 999999;
    height: 40px;
    background: #e10700;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 20px;
    font-size: 13px;
    font-weight: 600;
    font-family: inherit;
    width: 100%;
    box-sizing: border-box;
">
    <span>
        <i class="bi bi-eye-fill" style="margin-right:6px;"></i>
        Ghost Mode &mdash; Viewing account of:
        <strong style="margin-left:4px;"><?= htmlspecialchars($_SESSION['ghost_user_name'] ?? '') ?></strong>
        <span style="opacity:0.8;margin-left:6px;font-weight:400;">(<?= htmlspecialchars($_SESSION['ghost_user_email'] ?? '') ?>)</span>
    </span>
    <a href="ghost_exit" style="
        background: #fff;
        color: #e10700;
        text-decoration: none;
        padding: 3px 14px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        white-space: nowrap;
    ">
        <i class="bi bi-arrow-left-circle-fill"></i> Return to Admin Panel
    </a>
</div>
<?php endif; ?>
