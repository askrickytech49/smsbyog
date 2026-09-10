  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <meta name="description" content="SmsByOg Admin Panel">

  <link rel="icon" href="https://smsbyog.com/favicon.png" type="image/png">

  <!-- Poppins Font -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <!-- DataTables (no bootstrap flavor — we style it ourselves) -->
  <link href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css" rel="stylesheet">

  <!-- Admin Theme -->
  <?php
    $admin_base = rtrim(dirname(dirname($_SERVER['PHP_SELF'])), '/\\') . '/admin/';
  ?>
  <link href="<?= $admin_base ?>css/admin-theme.css" rel="stylesheet">

  <!-- jQuery (needed before any inline scripts) -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
