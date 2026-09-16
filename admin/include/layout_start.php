<!DOCTYPE html>
<html lang="en">
<head>
  <?php
    $admin_page_title = trim((string)($page_title ?? 'Admin'));
    $admin_page_title = preg_replace('/\s[-—|]\s.*$/u', '', $admin_page_title) ?: $admin_page_title;
  ?>
  <title><?= htmlspecialchars($admin_page_title, ENT_QUOTES, 'UTF-8') ?> | user.smsbyog.com</title>
  <?php include __DIR__ . '/head.php'; ?>
</head>
<body>
<div id="adminWrapper">
  <?php include __DIR__ . '/slidebar.php'; ?>
  <div id="mainContent">
    <?php include __DIR__ . '/topbar.php'; ?>
    <div class="page-content">
