<?php
require_once __DIR__ . '/../includes/admin_shell.php';
$db = Database::getConnection();

$flash = null;
if ($_SERVER['REQUEST_METHOD']==='POST') {
  if (!validateCSRFToken($_POST['csrf_token'] ?? '')) { $flash=['err','Invalid security token.']; }
  else {
    $action = $_POST['action'] ?? '';
    $hid = (int)($_POST['hub_id'] ?? 0);
    $h = $db->prepare("SELECT * FROM hubs WHERE id=?");
    $h->execute([$hid]); $hub = $h->fetch();
    if (!$hub) { $flash=['err','Hub not found.']; }
    else {
      try {
        if ($action === 'delete') {
          // safety: detach managers and parcels references
          $db->prepare("UPDATE users SET hub_id=NULL WHERE hub_id=?")->execute([$hid]);
          $db->prepare("DELETE FROM hubs WHERE id=?")->execute([$hid]);
          if (function_exists('logActivity')) logActivity($_SESSION['user_id']??null,'hub_deleted',"Deleted hub {$hub['code']}");
          $flash=['ok','Hub deleted.'];
        } elseif ($action === 'update') {
          $name = trim($_POST['name'] ?? '');
          $district = trim($_POST['district'] ?? '');
          $area = trim($_POST['area'] ?? '');
          $address = trim($_POST['address'] ?? '');
          $phone = trim($_POST['phone'] ?? '');
          $email = trim($_POST['email'] ?? '');
          $mgr = $_POST['manager_id'] !== '' ? (int)$_POST['manager_id'] : null;
          $status = $_POST['status'] ?? 'active';
          if ($name==='' || $district==='' || $area==='' || $address==='' || $phone==='') throw new Exception('All required fields must be filled.');
          $db->prepare("UPDATE hubs SET name=?, district=?, area=?, address=?, phone=?, email=?, manager_id=?, status=? WHERE id=?")
             ->execute([$name,$district,$area,$address,$phone,$email,$mgr,$status,$hid]);
          if ($mgr) $db->prepare("UPDATE users SET hub_id=? WHERE id=? AND role='hub_manager'")->execute([$hid,$mgr]);
          if (function_exists('logActivity')) logActivity($_SESSION['user_id']??null,'hub_updated',"Updated hub {$hub['code']}");
          $flash=['ok','Hub updated.'];
        }
      } catch(Exception $e){ $flash=['err',$e->getMessage()]; }
    }
  }
}

spx_admin_shell_open('Hubs');
$rows = $db->query("SELECT h.*, u.full_name AS manager FROM hubs h LEFT JOIN users u ON u.id=h.manager_id ORDER BY h.name")->fetchAll();
$managers = $db->query("SELECT id,full_name FROM users WHERE role='hub_manager' ORDER BY full_name")->fetchAll();
$csrf = generateCSRFToken();
?>

<?php if($flash): ?>
  <div class="glass-card mb-3" style="color:<?= $flash[0]==='ok'?'#10b981':'#ef4444' ?>;">
    <i class="fas fa-<?= $flash[0]==='ok'?'circle-check':'circle-exclamation' ?> me-2"></i><?= htmlspecialchars($flash[1]) ?>
  </div>
<?php endif; ?>

<div class="table-wrapper">
  <div class="table-header"><h6 style="font-weight:600;margin:0;">All Hubs (<?= count($rows) ?>)</h6>
    <a href="add-hub.php" class="btn-speedex" style="padding:6px 16px;font-size:.75rem;"><i class="fas fa-plus"></i> Add Hub</a></div>
  <div style="overflow-x:auto;"><table class="table-speedex">
    <thead><tr><th>Code</th><th>Name</th><th>District</th><th>Address</th><th>Phone</th><th>Manager</th><th>Status</th><th style="width:170px;">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $h): ?>
      <tr>
        <td class="text-primary-green"><?= sanitize($h['code']) ?></td>
        <td><?= sanitize($h['name']) ?></td>
        <td><?= sanitize($h['district']) ?></td>
        <td><?= sanitize($h['address']) ?></td>
        <td><?= sanitize($h['phone']) ?></td>
        <td><?= sanitize($h['manager'] ?? '—') ?></td>
        <td><span class="badge-status badge-<?= $h['status']==='active'?'delivered':'pending' ?>"><?= ucfirst($h['status']) ?></span></td>
        <td style="white-space:nowrap;">
          <button type="button" class="btn-speedex" style="padding:4px 9px;font-size:.7rem;" onclick='openEditHub(<?= json_encode($h, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'><i class="fas fa-pen"></i> Edit</button>
          <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this hub? Assigned managers will be detached.');">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <input type="hidden" name="hub_id" value="<?= (int)$h['id'] ?>">
            <button name="action" value="delete" class="btn-speedex-outline" style="padding:4px 9px;font-size:.7rem;color:#ef4444;border-color:#ef4444;" title="Delete hub"><i class="fas fa-trash"></i></button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody></table></div>
</div>

<!-- Edit Hub Modal -->
<div id="editHubModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:2000;align-items:center;justify-content:center;padding:20px;">
  <div class="glass-card" style="max-width:640px;width:100%;max-height:90vh;overflow-y:auto;">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 style="margin:0;font-weight:600;"><i class="fas fa-warehouse me-2" style="color:var(--primary);"></i>Edit Hub</h5>
      <button type="button" onclick="document.getElementById('editHubModal').style.display='none'" style="background:none;border:none;color:var(--text-secondary);font-size:1.3rem;cursor:pointer;">&times;</button>
    </div>
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="hub_id" id="eh_id">
      <div class="row g-3">
        <div class="col-md-6"><label class="form-label-speedex">Hub Name</label><input class="form-control-speedex" name="name" id="eh_name" required></div>
        <div class="col-md-6"><label class="form-label-speedex">District</label><input class="form-control-speedex" name="district" id="eh_district" required></div>
        <div class="col-md-6"><label class="form-label-speedex">Thana / Area</label><input class="form-control-speedex" name="area" id="eh_area" required></div>
        <div class="col-md-6"><label class="form-label-speedex">Phone</label><input class="form-control-speedex" name="phone" id="eh_phone" required></div>
        <div class="col-12"><label class="form-label-speedex">Address</label><textarea class="form-control-speedex" rows="2" name="address" id="eh_address" required></textarea></div>
        <div class="col-md-6"><label class="form-label-speedex">Email</label><input class="form-control-speedex" type="email" name="email" id="eh_email"></div>
        <div class="col-md-6"><label class="form-label-speedex">Manager</label>
          <select class="form-control-speedex" name="manager_id" id="eh_mgr">
            <option value="">— None —</option>
            <?php foreach($managers as $m): ?>
              <option value="<?= $m['id'] ?>"><?= sanitize($m['full_name']) ?></option>
            <?php endforeach; ?>
          </select></div>
        <div class="col-md-6"><label class="form-label-speedex">Status</label>
          <select class="form-control-speedex" name="status" id="eh_status">
            <option value="active">Active</option><option value="inactive">Inactive</option>
          </select></div>
      </div>
      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn-speedex"><i class="fas fa-save"></i> Save Changes</button>
        <button type="button" class="btn-speedex-outline" onclick="document.getElementById('editHubModal').style.display='none'">Cancel</button>
      </div>
    </form>
  </div>
</div>
<script>
function openEditHub(h){
  document.getElementById('eh_id').value      = h.id;
  document.getElementById('eh_name').value    = h.name || '';
  document.getElementById('eh_district').value= h.district || '';
  document.getElementById('eh_area').value    = h.area || '';
  document.getElementById('eh_phone').value   = h.phone || '';
  document.getElementById('eh_address').value = h.address || '';
  document.getElementById('eh_email').value   = h.email || '';
  document.getElementById('eh_mgr').value     = h.manager_id || '';
  document.getElementById('eh_status').value  = h.status || 'active';
  document.getElementById('editHubModal').style.display='flex';
}
document.getElementById('editHubModal').addEventListener('click', function(e){ if(e.target===this) this.style.display='none'; });
</script>
<?php spx_admin_shell_close(); ?>
