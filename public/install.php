<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Karl Peace Foundation — Installer</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: system-ui, sans-serif; background: #f5f5f5; color: #1a1a1a; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
  .card { background: #fff; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,.1); padding: 40px; width: 100%; max-width: 640px; }
  h1 { font-size: 1.6rem; font-weight: 700; margin-bottom: 4px; color: #1e1b4b; }
  .subtitle { color: #666; font-size: .9rem; margin-bottom: 28px; }
  .step { border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px 20px; margin-bottom: 12px; }
  .step h3 { font-size: 1rem; font-weight: 600; margin-bottom: 6px; display: flex; align-items: center; gap: 8px; }
  .step p  { font-size: .85rem; color: #555; line-height: 1.5; }
  .badge { display: inline-block; padding: 2px 8px; border-radius: 99px; font-size: .75rem; font-weight: 600; }
  .ok   { background: #d1fae5; color: #065f46; }
  .fail { background: #fee2e2; color: #991b1b; }
  .warn { background: #fef3c7; color: #92400e; }
  label { display: block; font-size: .85rem; font-weight: 500; margin-bottom: 4px; margin-top: 14px; }
  input { width: 100%; padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: .9rem; }
  input:focus { outline: 2px solid #6366f1; border-color: #6366f1; }
  button { margin-top: 20px; width: 100%; padding: 12px; background: #1e1b4b; color: #fff; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; border-radius: 8px; }
  button:hover { background: #312e81; }
  .result { margin-top: 20px; padding: 16px; border-radius: 8px; font-size: .85rem; line-height: 1.8; }
  .result.success { background: #ecfdf5; border: 1px solid #6ee7b7; color: #064e3b; }
  .result.error   { background: #fef2f2; border: 1px solid #fca5a5; color: #7f1d1d; }
  code { background: #f3f4f6; padding: 2px 6px; border-radius: 4px; font-size: .8rem; }
  .divider { border: none; border-top: 1px solid #e5e7eb; margin: 24px 0; }
  a { color: #4f46e5; text-decoration: none; }
  a:hover { text-decoration: underline; }
</style>
</head>
<body>
<div class="card">
  <h1>🏛 Karl Peace Foundation</h1>
  <p class="subtitle">Installation &amp; Setup Wizard — Step 1 of 1</p>

  <?php
  $checks = [];

  // PHP version
  $phpOk = version_compare(PHP_VERSION, '8.0.0', '>=');
  $checks[] = ['PHP ' . PHP_VERSION, $phpOk, 'PHP 8.0+ required'];

  // Extensions
  foreach (['pdo', 'pdo_mysql', 'json', 'mbstring', 'fileinfo'] as $ext) {
      $checks[] = ["ext-$ext", extension_loaded($ext), "PHP extension required"];
  }

  // Uploads dir writable
  $uploadDir = __DIR__ . '/uploads/';
  if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
  $checks[] = ['uploads/ writable', is_writable($uploadDir), 'chmod 755 public/uploads/'];

  // API dir readable
  $checks[] = ['api/ readable', is_dir(__DIR__ . '/api/') && is_readable(__DIR__ . '/api/config.php'), 'api/config.php must exist'];

  foreach ($checks as [$label, $ok, $hint]) {
      $cls = $ok ? 'ok' : 'fail';
      $ico = $ok ? '✓' : '✗';
      echo "<div class='step'><h3><span class='badge $cls'>$ico</span> $label</h3>";
      if (!$ok) echo "<p>$hint</p>";
      echo "</div>";
  }

  $allOk = array_reduce($checks, fn($c, $i) => $c && $i[1], true);
  ?>

  <hr class="divider">
  <h2 style="font-size:1.1rem;font-weight:600;margin-bottom:4px">Database Configuration</h2>
  <p style="font-size:.85rem;color:#666;margin-bottom:4px">
    These values will be written to <code>public/api/config.php</code>.<br>
    XAMPP defaults: host = <code>localhost</code>, user = <code>root</code>, password = <em>empty</em>.
  </p>

  <?php if (!empty($_POST['action']) && $_POST['action'] === 'install'): ?>
    <?php
    $host   = trim($_POST['db_host']   ?? 'localhost');
    $port   = trim($_POST['db_port']   ?? '3306');
    $name   = trim($_POST['db_name']   ?? 'karl_peace_foundation');
    $user   = trim($_POST['db_user']   ?? 'root');
    $pass   = $_POST['db_pass']        ?? '';
    $adminEmail = trim($_POST['admin_email'] ?? 'admin@karlpeacelegacy.org');
    $adminPass  = trim($_POST['admin_pass']  ?? '');

    $errors = [];
    if (empty($name))  $errors[] = 'Database name is required.';
    if (empty($user))  $errors[] = 'Database user is required.';
    if (strlen($adminPass) < 8) $errors[] = 'Admin password must be at least 8 characters.';
    if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid admin email required.';

    if (!empty($errors)):
    ?>
      <div class="result error">
        <?php foreach ($errors as $e) echo "✗ $e<br>"; ?>
      </div>
    <?php else:
      // Test DB connection
      try {
          $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
          $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

          // Create database
          $pdo->exec("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
          $pdo->exec("USE `$name`");

          // Run schema
          $schema = file_get_contents(__DIR__ . '/api/schema.sql');
          // Strip USE statement since we already selected db
          $schema = preg_replace('/^USE\s+\S+;\s*/im', '', $schema);
          $schema = preg_replace('/^CREATE DATABASE.*?;\s*/im', '', $schema);

          foreach (array_filter(array_map('trim', explode(';', $schema))) as $sql) {
              if (!empty($sql)) $pdo->exec($sql);
          }

          // Insert super-admin
          $uid  = 'admin_' . bin2hex(random_bytes(8));
          $hash = password_hash($adminPass, PASSWORD_BCRYPT);
          $check = $pdo->prepare('SELECT id FROM admin_users WHERE email = ?');
          $check->execute([$adminEmail]);
          if (!$check->fetch()) {
              $pdo->prepare(
                  'INSERT INTO admin_users (uid, email, display_name, password_hash, role, is_super_admin)
                   VALUES (?,?,?,?,?,?)'
              )->execute([$uid, $adminEmail, 'Foundation Admin', $hash, 'admin', 1]);
          }

          // Update config.php with new credentials
          $configPath = __DIR__ . '/api/config.php';
          $configContent = file_get_contents($configPath);
          $configContent = preg_replace("/define\('DB_HOST',\s*'[^']*'\)/",    "define('DB_HOST', '$host')", $configContent);
          $configContent = preg_replace("/define\('DB_PORT',\s*'[^']*'\)/",    "define('DB_PORT', '$port')", $configContent);
          $configContent = preg_replace("/define\('DB_NAME',\s*'[^']*'\)/",    "define('DB_NAME', '$name')", $configContent);
          $configContent = preg_replace("/define\('DB_USER',\s*'[^']*'\)/",    "define('DB_USER', '$user')", $configContent);
          $configContent = preg_replace("/define\('DB_PASS',\s*'[^']*'\)/",    "define('DB_PASS', '$pass')", $configContent);
          file_put_contents($configPath, $configContent);
    ?>
      <div class="result success">
        <strong>✓ Installation complete!</strong><br><br>
        Database <code><?= htmlspecialchars($name) ?></code> created and schema installed.<br>
        Admin account created: <code><?= htmlspecialchars($adminEmail) ?></code><br><br>
        <strong>Next steps:</strong><br>
        1. <a href="/karl-peace/public/api/seed.php?token=kplf_seed_2026" target="_blank">Run the seeder</a> to populate default content.<br>
        2. Start the Vite dev server: <code>npm run dev</code> (in the project root).<br>
        3. Open <a href="http://localhost:5173" target="_blank">http://localhost:5173</a> and log in.<br>
        4. <strong>Delete this file</strong> (<code>public/install.php</code>) for security.<br>
      </div>
    <?php
      } catch (PDOException $e) {
    ?>
      <div class="result error">
        <strong>✗ Database connection failed</strong><br>
        <?= htmlspecialchars($e->getMessage()) ?>
      </div>
    <?php } endif; ?>

  <?php else: ?>

  <form method="POST">
    <input type="hidden" name="action" value="install">

    <label>Database Host</label>
    <input type="text" name="db_host" value="localhost" placeholder="localhost">

    <label>Database Port</label>
    <input type="text" name="db_port" value="3306" placeholder="3306">

    <label>Database Name</label>
    <input type="text" name="db_name" value="karl_peace_foundation" placeholder="karl_peace_foundation">

    <label>Database User</label>
    <input type="text" name="db_user" value="root" placeholder="root">

    <label>Database Password</label>
    <input type="password" name="db_pass" value="" placeholder="Leave empty for XAMPP default">

    <hr class="divider">
    <h3 style="font-size:.95rem;font-weight:600;margin-bottom:2px">Admin Account</h3>

    <label>Admin Email</label>
    <input type="email" name="admin_email" value="admin@karlpeacelegacy.org">

    <label>Admin Password (min 8 characters)</label>
    <input type="password" name="admin_pass" placeholder="Choose a strong password">

    <button type="submit" <?= $allOk ? '' : 'disabled style="opacity:.5;cursor:not-allowed"' ?>>
      <?= $allOk ? 'Install Database & Create Admin' : 'Fix Requirements Above First' ?>
    </button>
  </form>

  <?php endif; ?>
</div>
</body>
</html>
