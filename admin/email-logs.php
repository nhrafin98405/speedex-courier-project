<?php
require_once __DIR__ . '/../includes/admin_shell.php';
spx_admin_shell_open('Email Logs');
$rows = Database::getConnection()->query("SELECT * FROM email_logs ORDER BY created_at DESC LIMIT 200")->fetchAll();
?>
<div class="table-wrapper">
  <div class="table-header"><h6 style="margin:0;font-weight:600;"><span class="live-dot"></span>Recent Email Notifications</h6></div>
  <div style="overflow-x:auto;"><table class="table-speedex">
    <thead><tr><th>Date</th><th>Recipient</th><th>Subject</th><th>Template</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $e): ?>
      <tr><td><?= date('d M, H:i', strtotime($e['created_at'])) ?></td>
          <td><?= sanitize($e['to_name'] ?: '—') ?><br><small style="color:var(--text-muted);"><?= sanitize($e['to_email']) ?></small></td>
          <td><?= sanitize($e['subject']) ?></td>
          <td><?= sanitize($e['template'] ?? '—') ?></td>
          <td><span class="email-pill <?= sanitize($e['status']) ?>"><i class="fas fa-circle"></i> <?= ucfirst($e['status']) ?></span></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="5" class="text-center" style="color:var(--text-muted);padding:24px;">No email logs yet</td></tr><?php endif; ?>
    </tbody></table></div>
</div>
<?php spx_admin_shell_close(); ?>
