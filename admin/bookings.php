<?php
require_once __DIR__ . '/../includes/admin_shell.php';
spx_admin_shell_open('Bookings');
$rows = Database::getConnection()->query("SELECT p.tracking_id, p.sender_name, p.receiver_name, p.total_amount, p.status, p.created_at FROM parcels p ORDER BY p.created_at DESC LIMIT 100")->fetchAll();
?>
<div class="table-wrapper"><div class="table-header"><h6 style="margin:0;font-weight:600;">Recent Bookings</h6></div>
<div style="overflow-x:auto;"><table class="table-speedex">
<thead><tr><th>Tracking</th><th>Sender</th><th>Receiver</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead><tbody>
<?php foreach ($rows as $p): ?>
<tr><td class="text-primary-green"><?= sanitize($p['tracking_id']) ?></td><td><?= sanitize($p['sender_name']) ?></td>
<td><?= sanitize($p['receiver_name']) ?></td><td>৳ <?= number_format($p['total_amount'],2) ?></td>
<td><span class="badge-status badge-<?= str_replace('_','-',$p['status']) ?>"><?= ucwords(str_replace('_',' ',$p['status'])) ?></span></td>
<td><?= date('d M Y', strtotime($p['created_at'])) ?></td></tr>
<?php endforeach; ?>
</tbody></table></div></div>
<?php spx_admin_shell_close(); ?>
