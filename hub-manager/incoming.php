<?php
$pageTitle='Incoming'; $dashboardType='hub_manager';
require_once __DIR__ . '/../includes/header.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role']!=='hub_manager') { header("Location: " . BASE_URL . "/auth/login.php"); exit; }
require_once __DIR__ . '/../config/mail.php';
$db = Database::getConnection();
$hub = (int)($_SESSION['hub_id'] ?? 0);

$flash = null;
if ($_SERVER['REQUEST_METHOD']==='POST' && !empty($_POST['action']) && !empty($_POST['parcel_id'])) {
  if (!validateCSRFToken($_POST['csrf_token'] ?? '')) { $flash=['err','Invalid security token.']; }
  else {
    $pid = (int)$_POST['parcel_id']; $act = $_POST['action'];
    $p = $db->prepare("SELECT * FROM parcels WHERE id=? AND receiver_hub_id=?");
    $p->execute([$pid,$hub]); $parcel = $p->fetch();
    if (!$parcel) { $flash=['err','Parcel not found.']; }
    else {
      $map = ['receive'=>['at_hub','Arrived at destination hub'],
              'out_for_delivery'=>['out_for_delivery','Out for delivery'],
              'cancel'=>['cancelled','Parcel cancelled by hub'],
              'return'=>['cancelled','Parcel returned to sender']];
      if (isset($map[$act])) {
        [$newStatus,$remarks] = $map[$act];
        $db->prepare("UPDATE parcels SET status=? WHERE id=?")->execute([$newStatus,$pid]);
        $hubName = $db->query("SELECT name FROM hubs WHERE id=$hub")->fetchColumn();
        $db->prepare("INSERT INTO parcel_tracking (parcel_id,status,location,hub_id,remarks) VALUES (?,?,?,?,?)")
           ->execute([$pid, ucwords(str_replace('_',' ',$newStatus)), $hubName, $hub, $remarks]);
        if ($act==='receive' && method_exists('Mailer','parcelArrivedAtHub')) {
          try { Mailer::parcelArrivedAtHub($parcel); } catch(Exception $e){}
        }
        if (function_exists('logActivity')) logActivity($_SESSION['user_id'], 'parcel_'.$act, "Parcel {$parcel['tracking_id']} -> {$newStatus}");
        $flash=['ok','Parcel updated successfully.'];
      }
    }
  }
}
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/sidebar.php';

$validStatuses = ['pending','in_transit','at_hub','out_for_delivery','delivered','cancelled'];
$status = $_GET['status'] ?? 'all';
if ($status !== 'all' && !in_array($status, $validStatuses, true)) $status = 'all';

$sql = "SELECT p.*, s.name AS from_hub FROM parcels p LEFT JOIN hubs s ON s.id=p.sender_hub_id WHERE p.receiver_hub_id=?";
$params = [$hub];
if ($status !== 'all') { $sql .= " AND p.status=?"; $params[]=$status; }
$sql .= " ORDER BY p.created_at DESC";
$stmt = $db->prepare($sql); $stmt->execute($params); $rows = $stmt->fetchAll();

function sb($s){$c=['pending'=>'#f59e0b','in_transit'=>'#3b82f6','at_hub'=>'#8b5cf6','out_for_delivery'=>'#ef4444','delivered'=>'#10b981','cancelled'=>'#6b7280'][$s]??'#6b7280';return '<span style="background:'.$c.'22;color:'.$c.';padding:3px 10px;border-radius:999px;font-size:.75rem;font-weight:600;text-transform:capitalize;">'.str_replace('_',' ',$s).'</span>';}
$labels=['all'=>'All','pending'=>'Pending','in_transit'=>'In Transit','at_hub'=>'At Hub','out_for_delivery'=>'Out for Delivery','delivered'=>'Delivered'];
?>
<div class="main-content">
  <div class="dashboard-navbar"><h5 style="margin:0;font-weight:600;">Incoming Parcels</h5>
  <div class="theme-toggle theme-switcher"><i class="fas fa-moon"></i><i class="fas fa-sun"></i><div class="toggle-ball"></div></div></div>

  <div class="filter-chips">
    <?php foreach($labels as $k=>$lab): ?>
      <a class="filter-chip <?= $status===$k?'active':'' ?>" href="?status=<?= $k ?>"><?= $lab ?></a>
    <?php endforeach; ?>
  </div>

  <?php if($flash): ?>
    <div class="glass-card mb-3" style="color:<?= $flash[0]==='ok'?'#10b981':'#ef4444' ?>;">
      <i class="fas fa-<?= $flash[0]==='ok'?'circle-check':'circle-exclamation' ?> me-2"></i><?= htmlspecialchars($flash[1]) ?>
    </div>
  <?php endif; ?>

  <div class="glass-card">
    <h4><?= $labels[$status] ?> Incoming Parcels (<?= count($rows) ?>)</h4>
    <p style="color:var(--text-secondary);">Use the action buttons to update parcel status.</p>
    <?php if(empty($rows)): ?>
      <p style="text-align:center;padding:30px;color:var(--text-secondary);">No incoming parcels match this filter.</p>
    <?php else: ?>
    <div style="overflow-x:auto;">
      <table class="table" style="width:100%;color:var(--text-primary);">
        <thead><tr style="border-bottom:1px solid var(--border);">
          <th style="padding:10px;">Tracking ID</th><th style="padding:10px;">From Hub</th><th style="padding:10px;">Sender</th><th style="padding:10px;">Receiver</th><th style="padding:10px;">Amount</th><th style="padding:10px;">Status</th><th style="padding:10px;">Actions</th>
        </tr></thead>
        <tbody>
          <?php foreach($rows as $r): ?>
          <tr style="border-bottom:1px solid var(--border);">
            <td style="padding:10px;font-weight:600;"><a href="<?= BASE_URL ?>/track.php?id=<?= urlencode($r['tracking_id']) ?>" style="color:var(--primary);"><?= htmlspecialchars($r['tracking_id']) ?></a></td>
            <td style="padding:10px;"><?= htmlspecialchars($r['from_hub'] ?? '-') ?></td>
            <td style="padding:10px;"><?= htmlspecialchars($r['sender_name']) ?></td>
            <td style="padding:10px;"><?= htmlspecialchars($r['receiver_name']) ?></td>
            <td style="padding:10px;">৳<?= number_format($r['total_amount'],2) ?></td>
            <td style="padding:10px;"><?= sb($r['status']) ?></td>
            <td style="padding:10px;white-space:nowrap;">
              <form method="POST" style="display:inline-flex;gap:6px;flex-wrap:wrap;">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <input type="hidden" name="parcel_id" value="<?= (int)$r['id'] ?>">
                <?php if(in_array($r['status'],['pending','in_transit'])): ?>
                  <button name="action" value="receive" class="btn-speedex" style="padding:5px 10px;font-size:.75rem;" title="Mark as arrived at hub"><i class="fas fa-warehouse"></i> Receive</button>
                <?php endif; ?>
                <?php if($r['status']==='at_hub'): ?>
                  <button name="action" value="out_for_delivery" class="btn-speedex" style="padding:5px 10px;font-size:.75rem;background:#3b82f6;border-color:#3b82f6;"><i class="fas fa-truck"></i> Out</button>
                <?php endif; ?>
                <?php if(!in_array($r['status'],['delivered','cancelled'])): ?>
                  <button name="action" value="cancel" onclick="return confirm('Cancel this parcel?')" class="btn-speedex-outline" style="padding:5px 10px;font-size:.75rem;color:#ef4444;border-color:#ef4444;"><i class="fas fa-times"></i></button>
                  <button name="action" value="return" onclick="return confirm('Return to sender?')" class="btn-speedex-outline" style="padding:5px 10px;font-size:.75rem;"><i class="fas fa-undo"></i></button>
                <?php endif; ?>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
