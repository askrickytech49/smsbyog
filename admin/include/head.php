  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="SmsByOg Admin">
  <meta name="author" content="">
  <link href="<?= $site_data['logo_url'] ?? '' ?>" rel="icon">
  <?php
    // Build correct base path — works on both localhost/smsbyog and live domain root
    $admin_base = rtrim(dirname(dirname($_SERVER['PHP_SELF'])), '/') . '/admin/';
  ?>
  <link href="<?= $admin_base ?>vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="<?= $admin_base ?>vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <link href="<?= $admin_base ?>css/ruang-admin.min.css" rel="stylesheet">
  <link href="<?= $admin_base ?>libs/sweetalert2/sweetalert2.min.css" rel="stylesheet" type="text/css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>