<?php
$pageTitle='Outgoing'; $dashboardType='hub_manager';
require_once __DIR__ . '/../includes/header.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role']!=='hub_manager') { header("Location: " . BASE_URL . "/auth/login.php"); exit; }
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/sidebar.php';
$db = Database::getConnection();
$hub = (int)($_SESSION['hub_id'] ?? 0);

$validStatuses = ['pending','in_transit','at_hub','out_for_delivery','delivered','cancelled'];
$status = $_GET['status'] ?? 'all';
if ($status !== 'all' && !in_array($status, $validStatuses, true)) $status = 'all';

$sql = "SELECT p.*, r.name AS to_hub FROM parcels p LEFT JOIN hubs r ON r.id=p.receiver_hub_id WHERE p.sender_hub_id=?";
$params = [$hub];
if ($status !== 'all') { $sql .= " AND p.status=?"; $params[]=$status; }
$sql .= " ORDER BY p.created_at DESC";
$stmt = $db->prepare($sql); $stmt->execute($params); $rows = $stmt->fetchAll();

function sb($s){$c=['pending'=>'#f59e0b','in_transit'=>'#3b82f6','at_hub'=>'#8b5cf6','out_for_delivery'=>'#ef4444','delivered'=>'#10b981','cancelled'=>'#6b7280'][$s]??'#6b7280';return '<span style="background:'.$c.'22;color:'.$c.';padding:3px 10px;border-radius:999px;font-size:.75rem;font-weight:600;text-transform:capitalize;">'.str_replace('_',' ',$s).'</span>';}
$labels=['all'=>'All','pending'=>'Pending','in_transit'=>'In Transit','at_hub'=>'At Hub','out_for_delivery'=>'Out for Delivery','delivered'=>'Delivered'];
?>
<div class="main-content">
  <div class="dashboard-navbar"><h5 style="margin:0;font-weight:600;">Outgoing Parcels</h5>
  <div class="theme-toggle theme-switcher"><i class="fas fa-moon"></i><i class="fas fa-sun"></i><div class="toggle-ball"></div></div></div>

  <div class="filter-chips">
    <?php foreach($labels as $k=>$lab): ?>
      <a class="filter-chip <?= $status===$k?'active':'' ?>" href="?status=<?= $k ?>"><?= $lab ?></a>
    <?php endforeach; ?>
  </div>

  <div class="glass-card">
    <h4><?= $labels[$status] ?> Outgoing Parcels (<?= count($rows) ?>)</h4>
    <p style="color:var(--text-secondary);">Click any row to view full tracking details.</p>
    <?php if(empty($rows)): ?>
      <p style="text-align:center;padding:30px;color:var(--text-secondary);">No outgoing parcels match this filter.</p>
    <?php else: ?>
    <div style="overflow-x:auto;">
      <table class="table" style="width:100%;color:var(--text-primary);">
        <thead><tr style="border-bottom:1px solid var(--border);">
          <th style="padding:10px;">Tracking ID</th><th style="padding:10px;">To Hub</th><th style="padding:10px;">Sender</th><th style="padding:10px;">Receiver</th><th style="padding:10px;">Amount</th><th style="padding:10px;">Status</th><th style="padding:10px;">Date</th>
        </tr></thead>
        <tbody>
          <?php foreach($rows as $r): ?>
          <tr class="parcel-row" style="border-bottom:1px solid var(--border);" onclick="window.location='<?= BASE_URL ?>/track.php?id=<?= urlencode($r['tracking_id']) ?>'">
            <td style="padding:10px;font-weight:600;"><?= htmlspecialchars($r['tracking_id']) ?></td>
            <td style="padding:10px;"><?= htmlspecialchars($r['to_hub'] ?? '-') ?></td>
            <td style="padding:10px;"><?= htmlspecialchars($r['sender_name']) ?></td>
            <td style="padding:10px;"><?= htmlspecialchars($r['receiver_name']) ?></td>
            <td style="padding:10px;">৳<?= number_format($r['total_amount'],2) ?></td>
            <td style="padding:10px;"><?= sb($r['status']) ?></td>
            <td style="padding:10px;font-size:.8rem;"><?= date('d M Y', strtotime($r['created_at'])) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
