<?php
require_once __DIR__ . '/../includes/admin_shell.php';
spx_admin_shell_open('Parcels');
require_once __DIR__ . '/../config/database.php';
$db = Database::getConnection();

$validStatuses = ['pending','in_transit','at_hub','out_for_delivery','delivered','cancelled'];
$status = $_GET['status'] ?? 'all';
if ($status !== 'all' && !in_array($status, $validStatuses, true)) $status = 'all';

$sql = "SELECT p.*, s.name AS from_hub, r.name AS to_hub
        FROM parcels p
        JOIN hubs s ON s.id=p.sender_hub_id
        JOIN hubs r ON r.id=p.receiver_hub_id";
$params = [];
if ($status !== 'all') { $sql .= " WHERE p.status = ?"; $params[] = $status; }
$sql .= " ORDER BY p.created_at DESC";
$st = $db->prepare($sql); $st->execute($params); $rows = $st->fetchAll();

$labels = [
  'all'=>'All Parcels','pending'=>'Pending','in_transit'=>'In Transit',
  'at_hub'=>'At Hub','out_for_delivery'=>'Out for Delivery',
  'delivered'=>'Delivered','cancelled'=>'Cancelled'
];
$chips = ['all','pending','in_transit','out_for_delivery','delivered','cancelled'];
?>
<div class="filter-chips">
  <?php foreach ($chips as $c): ?>
    <a class="filter-chip <?= $status===$c?'active':'' ?>" href="?status=<?= $c ?>"><?= $labels[$c] ?></a>
  <?php endforeach; ?>
</div>
<div class="table-wrapper">
  <div class="table-header">
    <h6 style="font-weight:600;margin:0;"><?= $labels[$status] ?? 'Parcels' ?> (<?= count($rows) ?>)</h6>
    <a href="<?= BASE_URL ?>/send-parcel.php" class="btn-speedex" style="padding:6px 16px;font-size:.75rem;"><i class="fas fa-plus"></i> New Parcel</a>
  </div>
  <div style="overflow-x:auto;"><table class="table-speedex">
    <thead><tr><th>Tracking</th><th>Sender</th><th>Receiver</th><th>From</th><th>To</th><th>Status</th><th>Amount</th><th>Date</th></tr></thead>
    <tbody>
    <?php if (empty($rows)): ?>
      <tr><td colspan="8" style="text-align:center;padding:30px;color:var(--text-secondary);">No parcels found for this filter.</td></tr>
    <?php else: foreach ($rows as $p): ?>
      <tr class="parcel-row" onclick="window.location='<?= BASE_URL ?>/track.php?id=<?= urlencode($p['tracking_id']) ?>'">
        <td class="text-primary-green"><?= sanitize($p['tracking_id']) ?></td>
        <td><?= sanitize($p['sender_name']) ?></td><td><?= sanitize($p['receiver_name']) ?></td>
        <td><?= sanitize($p['from_hub']) ?></td><td><?= sanitize($p['to_hub']) ?></td>
        <td><span class="badge-status badge-<?= str_replace('_','-',$p['status']) ?>"><?= ucwords(str_replace('_',' ',$p['status'])) ?></span></td>
        <td>৳ <?= number_format($p['total_amount'],2) ?></td>
        <td><?= date('d M Y', strtotime($p['created_at'])) ?></td>
      </tr>
    <?php endforeach; endif; ?>
    </tbody></table></div>
</div>
<?php spx_admin_shell_close(); ?>
