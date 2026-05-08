<?php
require_once __DIR__ . '/../includes/admin_shell.php';
spx_admin_shell_open('Activity Logs');
$rows = Database::getConnection()->query("SELECT a.*, u.full_name FROM activity_logs a LEFT JOIN users u ON u.id=a.user_id ORDER BY a.created_at DESC LIMIT 200")->fetchAll();
?>
<div class="table-wrapper"><div class="table-header"><h6 style="margin:0;font-weight:600;">Recent Activity</h6></div>
<div style="overflow-x:auto;"><table class="table-speedex">
<thead><tr><th>Date</th><th>User</th><th>Action</th><th>Description</th><th>IP</th></tr></thead><tbody>
<?php foreach ($rows as $a): ?>
<tr><td><?= date('d M, H:i', strtotime($a['created_at'])) ?></td><td><?= sanitize($a['full_name'] ?? '—') ?></td>
<td><span class="text-primary-green"><?= sanitize($a['action']) ?></span></td>
<td><?= sanitize($a['description'] ?? '') ?></td><td><?= sanitize($a['ip_address'] ?? '') ?></td></tr>
<?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="5" class="text-center" style="color:var(--text-muted);padding:24px;">No logs yet</td></tr><?php endif; ?>
</tbody></table></div></div>
<?php spx_admin_shell_close(); ?>
