<!DOCTYPE html>
<html lang="en">
<head>
  <title><?= htmlspecialchars($page_title ?? 'Admin') ?> — SmsByOg</title>
  <?php include __DIR__ . '/head.php'; ?>
</head>
<body>
<div id="adminWrapper">
  <?php include __DIR__ . '/slidebar.php'; ?>
  <div id="mainContent">
    <?php include __DIR__ . '/topbar.php'; ?>
    <div class="page-content">
