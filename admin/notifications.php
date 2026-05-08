<?php
require_once __DIR__ . '/../includes/admin_shell.php';
spx_admin_shell_open('Notifications');
$rows = Database::getConnection()->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 200")->fetchAll();
?>
<div class="glass-card">
  <h5 class="mb-3"><span class="live-dot"></span> All Notifications</h5>
  <?php foreach ($rows as $n): ?>
    <div class="notif-item hover-glow" style="border-radius:10px;margin-bottom:8px;background:rgba(255,255,255,.03);">
      <div class="t"><?= sanitize($n['title']) ?></div>
      <div class="m"><?= sanitize($n['message']) ?></div>
      <div class="d"><?= date('d M Y, H:i', strtotime($n['created_at'])) ?> · <?= sanitize($n['type']) ?></div>
    </div>
  <?php endforeach; ?>
  <?php if (!$rows): ?><p style="color:var(--text-muted);">No notifications yet.</p><?php endif; ?>
</div>
<?php spx_admin_shell_close(); ?>
