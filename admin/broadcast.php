<?php
include __DIR__ . '/auth.php';

if (empty($_SESSION['token'])) {
    header('Location: login.php');
    exit;
}

$token = mysqli_real_escape_string($conn, $_SESSION['token']);
$admin_query = mysqli_query($conn, "SELECT u.type FROM login_token lt JOIN user_data u ON lt.user_id=u.id WHERE lt.token='$token' AND lt.status='1' AND u.status='1' LIMIT 1");
$admin = $admin_query ? mysqli_fetch_assoc($admin_query) : null;
if (!$admin || !in_array($admin['type'], ['admin', 'super_admin'], true)) {
    header('Location: login.php');
    exit;
}

$message = '';
$is_active = 0;
$notice = '';
$error = '';
$broadcast_table_ready = true;

try {
  $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'broadcast'");
  $broadcast_table_ready = $table_check && mysqli_num_rows($table_check) > 0;
} catch (Throwable $e) {
  $broadcast_table_ready = false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim((string)($_POST['message'] ?? ''));
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (!$broadcast_table_ready) {
      $error = 'The broadcast table is missing. Run migrate_broadcast.sql in phpMyAdmin first.';
    } elseif ($message === '') {
        $is_active = 0;
        $error = 'Enter a message before turning the broadcast on.';
    } elseif (mb_strlen($message) > 500) {
        $error = 'The broadcast message must be 500 characters or fewer.';
    } else {
        $safe_message = mysqli_real_escape_string($conn, $message);
        $saved = mysqli_query($conn, "INSERT INTO broadcast (id, message, is_active) VALUES (1, '$safe_message', '$is_active') ON DUPLICATE KEY UPDATE message='$safe_message', is_active='$is_active'");
        if ($saved) {
            $notice = $is_active ? 'Broadcast is now live for users.' : 'Broadcast saved and turned off.';
        } else {
            $error = 'Could not save the broadcast. Confirm the broadcast table migration has been run.';
        }
    }
} else {
  if ($broadcast_table_ready) {
    try {
      $broadcast_query = mysqli_query($conn, "SELECT message, is_active FROM broadcast WHERE id=1 LIMIT 1");
      if ($broadcast_query && ($broadcast = mysqli_fetch_assoc($broadcast_query))) {
        $message = (string)$broadcast['message'];
        $is_active = (int)$broadcast['is_active'];
      }
    } catch (Throwable $e) {
      $broadcast_table_ready = false;
      $error = 'The broadcast table is unavailable. Run migrate_broadcast.sql in phpMyAdmin first.';
    }
    }
}

$page_title = 'Broadcast';
include __DIR__ . '/include/layout_start.php';
?>
<div class="page-header">
  <div>
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li><li class="breadcrumb-item active">Broadcast</li></ol></nav>
  </div>
</div>

<div class="row justify-content-center">
  <div class="col-xl-8 col-lg-10">
    <div class="card shadow-sm border-0" style="border-radius:16px;">
      <div class="card-body p-4">
        <div class="d-flex align-items-start gap-3 mb-4">
          <div style="width:44px;height:44px;border-radius:12px;background:#fff3d6;color:#b7791f;display:flex;align-items:center;justify-content:center;font-size:20px;flex:0 0 auto;">
            <i class="bi bi-megaphone-fill"></i>
          </div>
          <div>
            <h2 class="h5 mb-1">User broadcast</h2>
            <p class="text-muted mb-0" style="font-size:13px;">This message appears across the user area when it is active.</p>
          </div>
        </div>

        <?php if ($notice): ?><div class="alert alert-success"><?= htmlspecialchars($notice) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <form method="post">
          <div class="mb-3">
            <label class="form-label fw-semibold" for="message">Broadcast message</label>
            <textarea class="form-control" id="message" name="message" rows="8" maxlength="500" placeholder="Write an important update for users..." required><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></textarea>
            <div class="form-text">Maximum 500 characters.</div>
          </div>
          <div class="form-check form-switch mb-4">
            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" <?= $is_active ? 'checked' : '' ?>>
            <label class="form-check-label fw-semibold" for="is_active">Broadcast is active</label>
          </div>
          <button type="submit" class="btn btn-primary"><i class="bi bi-broadcast-pin me-1"></i> Save Broadcast</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/include/layout_end.php'; ?>
