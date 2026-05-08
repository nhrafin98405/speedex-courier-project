<?php
$pageTitle='Delivered'; $dashboardType='hub_manager';
require_once __DIR__ . '/../includes/header.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role']!=='hub_manager') { header("Location: " . BASE_URL . "/auth/login.php"); exit; }
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/sidebar.php';
$db = Database::getConnection();
$hub = (int)($_SESSION['hub_id'] ?? 0);
$stmt = $db->prepare("SELECT p.*, s.name AS from_hub, r.name AS to_hub FROM parcels p
  LEFT JOIN hubs s ON s.id=p.sender_hub_id LEFT JOIN hubs r ON r.id=p.receiver_hub_id
  WHERE (p.receiver_hub_id=? OR p.sender_hub_id=?) AND p.status='delivered'
  ORDER BY p.updated_at DESC, p.created_at DESC");
$stmt->execute([$hub,$hub]); $rows = $stmt->fetchAll();
?>
<div class="main-content">
  <div class="dashboard-navbar"><h5 style="margin:0;font-weight:600;">Delivered Parcels</h5>
  <div class="theme-toggle theme-switcher"><i class="fas fa-moon"></i><i class="fas fa-sun"></i><div class="toggle-ball"></div></div></div>
  <div class="glass-card">
    <h4>Delivered Parcels (<?= count($rows) ?>)</h4>
    <p style="color:var(--text-secondary);">All successfully delivered parcels for your hub. Click a row for details.</p>
    <?php if(empty($rows)): ?>
      <p style="text-align:center;padding:30px;color:var(--text-secondary);">No delivered parcels yet.</p>
    <?php else: ?>
    <div style="overflow-x:auto;">
      <table class="table" style="width:100%;color:var(--text-primary);">
        <thead><tr style="border-bottom:1px solid var(--border);">
          <th style="padding:10px;">Tracking ID</th><th style="padding:10px;">From</th><th style="padding:10px;">To</th>
          <th style="padding:10px;">Sender</th><th style="padding:10px;">Receiver</th>
          <th style="padding:10px;">Amount</th><th style="padding:10px;">Date</th>
        </tr></thead>
        <tbody>
          <?php foreach($rows as $r): ?>
          <tr class="parcel-row" style="border-bottom:1px solid var(--border);" onclick="window.location='<?= BASE_URL ?>/track.php?id=<?= urlencode($r['tracking_id']) ?>'">
            <td style="padding:10px;font-weight:600;"><?= htmlspecialchars($r['tracking_id']) ?></td>
            <td style="padding:10px;"><?= htmlspecialchars($r['from_hub'] ?? '-') ?></td>
            <td style="padding:10px;"><?= htmlspecialchars($r['to_hub'] ?? '-') ?></td>
            <td style="padding:10px;"><?= htmlspecialchars($r['sender_name']) ?></td>
            <td style="padding:10px;"><?= htmlspecialchars($r['receiver_name']) ?></td>
            <td style="padding:10px;">৳<?= number_format($r['total_amount'],2) ?></td>
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
