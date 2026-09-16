<?php
$broadcast_message = '';
$broadcast_active = false;
$broadcast_query = false;
try {
  $broadcast_query = mysqli_query($conn, "SELECT message, is_active FROM broadcast WHERE id=1 LIMIT 1");
} catch (Throwable $e) {
  // Keep the user pages available until the broadcast migration is applied.
}
if ($broadcast_query && ($broadcast_row = mysqli_fetch_assoc($broadcast_query))) {
    $broadcast_message = trim((string)$broadcast_row['message']);
    $broadcast_active = (int)$broadcast_row['is_active'] === 1 && $broadcast_message !== '';
}
?>
<?php if ($broadcast_active): ?>
<style>
.broadcast-banner {
  display: flex;
  align-items: center;
  gap: 12px;
  min-height: 48px;
  margin: 12px 10px 18px;
  padding: 10px 16px;
  border-radius: 24px;
  background: #fff5f5;
  border: 1px solid rgba(225, 7, 0, .18);
  color: #111827;
  overflow: hidden;
  box-sizing: border-box;
}
.broadcast-banner-icon {
  flex: 0 0 auto;
  font-size: 18px;
  color: #e10700;
}
.broadcast-banner-track {
  min-width: 0;
  flex: 1;
  overflow: hidden;
  white-space: nowrap;
}
.broadcast-banner-message {
  display: inline-block;
  color: #111827;
  font-size: 15px;
  font-weight: 700;
  line-height: 1.3;
  white-space: nowrap;
  padding-left: 100%;
  animation: broadcast-marquee 36s linear infinite;
  will-change: transform;
}
@keyframes broadcast-marquee {
  from { transform: translateX(0); }
  to { transform: translateX(-100%); }
}
@media (prefers-reduced-motion: reduce) {
  .broadcast-banner-message {
    padding-left: 0;
    animation: none;
    white-space: normal;
  }
}
.broadcast-banner-arrow {
  flex: 0 0 auto;
  font-size: 18px;
  color: #e10700;
}
@media (max-width: 576px) {
  .broadcast-banner { margin: 10px 8px 14px; }
  .broadcast-banner-message { font-size: 12px; }
}
</style>
<div class="broadcast-banner" role="status" aria-label="Broadcast message">
  <span class="broadcast-banner-icon"><i class="bi bi-megaphone-fill"></i></span>
  <div class="broadcast-banner-track">
    <span class="broadcast-banner-message"><?= htmlspecialchars($broadcast_message, ENT_QUOTES, 'UTF-8') ?></span>
  </div>
  <span class="broadcast-banner-arrow"><i class="bi bi-chevron-right"></i></span>
</div>
<?php endif; ?>
