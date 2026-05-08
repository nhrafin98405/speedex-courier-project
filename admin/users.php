<?php
require_once __DIR__ . '/../includes/admin_shell.php';
spx_admin_shell_open('Users');
$rows = Database::getConnection()->query("SELECT u.*, h.name AS hub_name FROM users u LEFT JOIN hubs h ON h.id=u.hub_id ORDER BY u.created_at DESC")->fetchAll();
?>
<div class="table-wrapper"><div class="table-header"><h6 style="margin:0;font-weight:600;">All Users (<?= count($rows) ?>)</h6></div>
<div style="overflow-x:auto;"><table class="table-speedex">
<thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Hub</th><th>Status</th></tr></thead><tbody>
<?php foreach ($rows as $u): ?>
<tr><td><?= sanitize($u['full_name']) ?></td><td><?= sanitize($u['email']) ?></td><td><?= sanitize($u['phone']) ?></td>
<td><?= ucfirst(str_replace('_',' ',$u['role'])) ?></td><td><?= sanitize($u['hub_name'] ?? '—') ?></td>
<td><span class="badge-status badge-<?= $u['status']==='active'?'delivered':'pending' ?>"><?= ucfirst($u['status']) ?></span></td></tr>
<?php endforeach; ?>
</tbody></table></div></div>
<?php spx_admin_shell_close(); ?>
