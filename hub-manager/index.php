<?php
$pageTitle='Hub Dashboard'; $dashboardType='hub_manager';
require_once __DIR__ . '/../includes/header.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role']!=='hub_manager') { header("Location: " . BASE_URL . "/auth/login.php"); exit; }
require_once __DIR__ . '/../config/mail.php';
require_once __DIR__ . '/../includes/navbar.php';
$db = Database::getConnection();
$hub = (int)($_SESSION['hub_id'] ?? 0);

// Handle quick actions from dashboard
$flash = null;
if ($_SERVER['REQUEST_METHOD']==='POST' && !empty($_POST['action']) && !empty($_POST['parcel_id'])) {
  if (!validateCSRFToken($_POST['csrf_token'] ?? '')) { $flash=['err','Invalid security token.']; }
  else {
    $pid = (int)$_POST['parcel_id']; $act = $_POST['action'];
    $p = $db->prepare("SELECT * FROM parcels WHERE id=? AND receiver_hub_id=?");
    $p->execute([$pid,$hub]); $parcel = $p->fetch();
    if (!$parcel) { $flash=['err','Parcel not found.']; }
    else {
      $map = [
        'receive'         => ['at_hub','Arrived at destination hub'],
        'out_for_delivery'=> ['out_for_delivery','Out for delivery'],
        'delivered'       => ['delivered','Delivered to receiver'],
        'cancel'          => ['cancelled','Parcel cancelled by hub'],
        'return'          => ['cancelled','Parcel returned to sender'],
      ];
      // Custom status from dropdown
      if ($act==='set_status' && !empty($_POST['new_status'])) {
        $valid = ['pending','in_transit','at_hub','out_for_delivery','delivered','cancelled'];
        $ns = $_POST['new_status'];
        if (in_array($ns,$valid,true)) { $map['set_status'] = [$ns, 'Status updated to '.$ns]; }
      }
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
      } else { $flash=['err','Invalid action.']; }
    }
  }
}

require_once __DIR__ . '/../includes/sidebar.php';

$stat = $db->prepare("SELECT
  SUM(receiver_hub_id=? AND status IN('in_transit','at_hub','pending')) incoming,
  SUM(sender_hub_id=? AND status='in_transit') in_transit,
  SUM(sender_hub_id=? AND status='out_for_delivery') outgoing,
  SUM(receiver_hub_id=? AND status='delivered') delivered,
  SUM(receiver_hub_id=? AND status='cancelled') cancelled FROM parcels");
$stat->execute([$hub,$hub,$hub,$hub,$hub]); $s = $stat->fetch();

// Incoming parcels (coming to this hub)
$incomingStmt = $db->prepare("SELECT p.id, p.tracking_id, p.sender_name, p.receiver_name, p.status, p.total_amount, p.created_at,
    s.name AS from_hub, r.name AS to_hub
    FROM parcels p
    LEFT JOIN hubs s ON s.id=p.sender_hub_id
    LEFT JOIN hubs r ON r.id=p.receiver_hub_id
    WHERE p.receiver_hub_id=? AND p.status IN('in_transit','at_hub','pending','out_for_delivery')
    ORDER BY p.created_at DESC LIMIT 15");
$incomingStmt->execute([$hub]);
$incomingParcels = $incomingStmt->fetchAll();

// Outgoing parcels (going from this hub)
$outgoingStmt = $db->prepare("SELECT p.tracking_id, p.sender_name, p.receiver_name, p.status, p.total_amount, p.created_at,
    s.name AS from_hub, r.name AS to_hub
    FROM parcels p
    LEFT JOIN hubs s ON s.id=p.sender_hub_id
    LEFT JOIN hubs r ON r.id=p.receiver_hub_id
    WHERE p.sender_hub_id=? AND p.status IN('pending','in_transit','out_for_delivery','at_hub')
    ORDER BY p.created_at DESC LIMIT 10");
$outgoingStmt->execute([$hub]);
$outgoingParcels = $outgoingStmt->fetchAll();

function statusBadge($s) {
  $colors = ['pending'=>'#f59e0b','in_transit'=>'#3b82f6','at_hub'=>'#8b5cf6','out_for_delivery'=>'#ef4444','delivered'=>'#10b981','cancelled'=>'#6b7280'];
  $c = $colors[$s] ?? '#6b7280';
  return '<span style="background:'.$c.'22;color:'.$c.';padding:3px 10px;border-radius:999px;font-size:.75rem;font-weight:600;text-transform:capitalize;">'.str_replace('_',' ',$s).'</span>';
}
?>
<div class="main-content">
  <div class="dashboard-navbar"><div class="d-flex align-items-center gap-3">
    <button id="sidebarToggle" class="d-lg-none" style="background:none;border:none;color:var(--text-primary);font-size:1.2rem;"><i class="fas fa-bars"></i></button>
    <h5 style="margin:0;font-weight:600;"><span class="live-dot"></span>Hub Manager</h5></div>
    <div class="d-flex align-items-center gap-3" style="position:relative;">
      <div class="theme-toggle theme-switcher"><i class="fas fa-moon"></i><i class="fas fa-sun"></i><div class="toggle-ball"></div></div>
      <div style="position:relative;cursor:pointer;" id="notifBellBtn"><i class="fas fa-bell" style="color:var(--text-secondary);font-size:1.1rem;"></i><span id="notifBadge" class="notification-badge" style="display:none;background:#ef4444;color:#fff;border-radius:999px;font-size:.65rem;padding:1px 6px;position:absolute;top:-6px;right:-8px;">0</span><div id="notifDropdown" class="notif-dropdown"></div></div>
    </div></div>

  <?php if($flash): ?>
    <div class="glass-card mb-3" style="color:<?= $flash[0]==='ok'?'#10b981':'#ef4444' ?>;">
      <i class="fas fa-<?= $flash[0]==='ok'?'circle-check':'circle-exclamation' ?> me-2"></i><?= htmlspecialchars($flash[1]) ?>
    </div>
  <?php endif; ?>

  <div class="row g-4 mb-4">
    <?php $cards=[
      ['Incoming',(int)$s['incoming'],'fa-arrow-down','blue', BASE_URL.'/hub-manager/incoming.php'],
      ['In Transit',(int)$s['in_transit'],'fa-truck','yellow', BASE_URL.'/hub-manager/outgoing.php?status=in_transit'],
      ['Outgoing',(int)$s['outgoing'],'fa-arrow-up','red', BASE_URL.'/hub-manager/outgoing.php'],
      ['Delivered',(int)$s['delivered'],'fa-check-circle','green', BASE_URL.'/hub-manager/delivered.php'],
    ]; foreach($cards as $c): ?>
    <div class="col-lg-3 col-md-6">
      <a class="stat-card-link" href="<?= $c[4] ?>">
        <div class="stat-card hover-glow"><div class="stat-icon <?= $c[3] ?>"><i class="fas <?= $c[2] ?>"></i></div><div class="stat-value"><?= $c[1] ?></div><div class="stat-label"><?= $c[0] ?></div></div>
      </a>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="row g-4">
    <div class="col-12">
      <div class="glass-card">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
          <h4 style="margin:0;"><i class="fas fa-arrow-down" style="color:#3b82f6;"></i> Incoming Parcels <span style="font-size:.85rem;color:var(--text-secondary);font-weight:400;">(<?= (int)$s['incoming'] ?> active)</span></h4>
          <a href="<?= BASE_URL ?>/hub-manager/incoming.php" style="font-size:.85rem;color:var(--primary);text-decoration:none;">View all →</a>
        </div>
        <?php if (empty($incomingParcels)): ?>
          <p style="color:var(--text-secondary);text-align:center;padding:20px;">No incoming parcels right now.</p>
        <?php else: ?>
        <div style="overflow-x:auto;">
          <table class="table" style="width:100%;color:var(--text-primary);">
            <thead><tr style="border-bottom:1px solid var(--border);">
              <th style="padding:8px;font-size:.8rem;">Tracking ID</th>
              <th style="padding:8px;font-size:.8rem;">From</th>
              <th style="padding:8px;font-size:.8rem;">Sender</th>
              <th style="padding:8px;font-size:.8rem;">Receiver</th>
              <th style="padding:8px;font-size:.8rem;">Status</th>
              <th style="padding:8px;font-size:.8rem;">Actions</th>
            </tr></thead>
            <tbody>
              <?php foreach($incomingParcels as $p): ?>
              <tr style="border-bottom:1px solid var(--border);">
                <td style="padding:8px;font-size:.85rem;font-weight:600;"><a href="<?= BASE_URL ?>/track.php?id=<?= urlencode($p['tracking_id']) ?>" style="color:var(--primary);text-decoration:none;"><?= htmlspecialchars($p['tracking_id']) ?></a></td>
                <td style="padding:8px;font-size:.85rem;"><?= htmlspecialchars($p['from_hub'] ?? '-') ?></td>
                <td style="padding:8px;font-size:.85rem;"><?= htmlspecialchars($p['sender_name']) ?></td>
                <td style="padding:8px;font-size:.85rem;"><?= htmlspecialchars($p['receiver_name']) ?></td>
                <td style="padding:8px;"><?= statusBadge($p['status']) ?></td>
                <td style="padding:8px;white-space:nowrap;">
                  <form method="POST" style="display:inline-flex;gap:4px;flex-wrap:wrap;align-items:center;">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    <input type="hidden" name="parcel_id" value="<?= (int)$p['id'] ?>">
                    <?php if(in_array($p['status'],['pending','in_transit'])): ?>
                      <button name="action" value="receive" class="btn-speedex" style="padding:4px 9px;font-size:.7rem;" title="Receive at hub"><i class="fas fa-warehouse"></i> Receive</button>
                    <?php endif; ?>
                    <?php if($p['status']==='at_hub'): ?>
                      <button name="action" value="out_for_delivery" class="btn-speedex" style="padding:4px 9px;font-size:.7rem;background:#3b82f6;border-color:#3b82f6;" title="Out for delivery"><i class="fas fa-truck"></i> Out</button>
                    <?php endif; ?>
                    <?php if($p['status']==='out_for_delivery'): ?>
                      <button name="action" value="delivered" class="btn-speedex" style="padding:4px 9px;font-size:.7rem;background:#10b981;border-color:#10b981;" title="Mark delivered"><i class="fas fa-check"></i></button>
                    <?php endif; ?>
                    <?php if(!in_array($p['status'],['delivered','cancelled'])): ?>
                      <button name="action" value="cancel" onclick="return confirm('Cancel this parcel?')" class="btn-speedex-outline" style="padding:4px 9px;font-size:.7rem;color:#ef4444;border-color:#ef4444;" title="Cancel"><i class="fas fa-times"></i></button>
                      <button name="action" value="return" onclick="return confirm('Return to sender?')" class="btn-speedex-outline" style="padding:4px 9px;font-size:.7rem;" title="Return"><i class="fas fa-undo"></i></button>
                      <select name="new_status" class="form-select" style="padding:3px 6px;font-size:.7rem;width:auto;display:inline-block;">
                        <option value="">Status…</option>
                        <option value="pending">Pending</option>
                        <option value="in_transit">In Transit</option>
                        <option value="at_hub">At Hub</option>
                        <option value="out_for_delivery">Out for Delivery</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                      </select>
                      <button name="action" value="set_status" class="btn-speedex-outline" style="padding:4px 9px;font-size:.7rem;" title="Update status"><i class="fas fa-sync-alt"></i></button>
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

    <div class="col-12">
      <div class="glass-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 style="margin:0;"><i class="fas fa-arrow-up" style="color:#ef4444;"></i> Outgoing Parcels</h4>
          <a href="<?= BASE_URL ?>/hub-manager/outgoing.php" style="font-size:.85rem;color:var(--primary);text-decoration:none;">View all →</a>
        </div>
        <?php if (empty($outgoingParcels)): ?>
          <p style="color:var(--text-secondary);text-align:center;padding:20px;">No outgoing parcels right now.</p>
        <?php else: ?>
        <div style="overflow-x:auto;">
          <table class="table" style="width:100%;color:var(--text-primary);">
            <thead><tr style="border-bottom:1px solid var(--border);">
              <th style="padding:8px;font-size:.8rem;">Tracking ID</th>
              <th style="padding:8px;font-size:.8rem;">To</th>
              <th style="padding:8px;font-size:.8rem;">Receiver</th>
              <th style="padding:8px;font-size:.8rem;">Status</th>
            </tr></thead>
            <tbody>
              <?php foreach($outgoingParcels as $p): ?>
              <tr class="parcel-row" style="border-bottom:1px solid var(--border);" onclick="window.location='<?= BASE_URL ?>/track.php?id=<?= urlencode($p['tracking_id']) ?>'">
                <td style="padding:8px;font-size:.85rem;font-weight:600;"><?= htmlspecialchars($p['tracking_id']) ?></td>
                <td style="padding:8px;font-size:.85rem;"><?= htmlspecialchars($p['to_hub'] ?? '-') ?></td>
                <td style="padding:8px;font-size:.85rem;"><?= htmlspecialchars($p['receiver_name']) ?></td>
                <td style="padding:8px;"><?= statusBadge($p['status']) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
