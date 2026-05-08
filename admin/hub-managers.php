<?php
require_once __DIR__ . '/../includes/admin_shell.php';
$db = Database::getConnection();

$flash = null;
// Handle POST actions: delete, update, toggle status
if ($_SERVER['REQUEST_METHOD']==='POST') {
  if (!validateCSRFToken($_POST['csrf_token'] ?? '')) { $flash=['err','Invalid security token.']; }
  else {
    $action = $_POST['action'] ?? '';
    $uid = (int)($_POST['user_id'] ?? 0);
    $u = $db->prepare("SELECT * FROM users WHERE id=? AND role='hub_manager'");
    $u->execute([$uid]); $mgr = $u->fetch();
    if (!$mgr) { $flash=['err','Manager not found.']; }
    else {
      try {
        if ($action === 'delete') {
          // Detach from any hub first
          $db->prepare("UPDATE hubs SET manager_id=NULL WHERE manager_id=?")->execute([$uid]);
          $db->prepare("DELETE FROM users WHERE id=? AND role='hub_manager'")->execute([$uid]);
          if (function_exists('logActivity')) logActivity($_SESSION['user_id']??null,'hub_manager_deleted',"Removed manager {$mgr['email']} (#{$uid})");
          $flash=['ok','Hub manager removed.'];
        } elseif ($action === 'toggle_status') {
          $new = $mgr['status']==='active' ? 'inactive' : 'active';
          $db->prepare("UPDATE users SET status=? WHERE id=?")->execute([$new,$uid]);
          $flash=['ok','Status changed to '.$new.'.'];
        } elseif ($action === 'update') {
          $name  = trim($_POST['full_name'] ?? '');
          $email = trim($_POST['email'] ?? '');
          $phone = trim($_POST['phone'] ?? '');
          $hubId = $_POST['hub_id'] !== '' ? (int)$_POST['hub_id'] : null;
          $status= $_POST['status'] ?? 'active';
          $pw    = $_POST['password'] ?? '';
          if ($name==='' || $email==='' || $phone==='') throw new Exception('Name, email, phone are required.');
          // unique email check
          $chk = $db->prepare("SELECT id FROM users WHERE email=? AND id<>?");
          $chk->execute([$email,$uid]);
          if ($chk->fetch()) throw new Exception('Another user already uses this email.');
          if ($pw !== '') {
            if (strlen($pw)<6) throw new Exception('Password must be at least 6 characters.');
            $hash = password_hash($pw, PASSWORD_DEFAULT);
            $db->prepare("UPDATE users SET full_name=?, email=?, phone=?, hub_id=?, status=?, password=? WHERE id=?")
               ->execute([$name,$email,$phone,$hubId,$status,$hash,$uid]);
          } else {
            $db->prepare("UPDATE users SET full_name=?, email=?, phone=?, hub_id=?, status=? WHERE id=?")
               ->execute([$name,$email,$phone,$hubId,$status,$uid]);
          }
          // Sync hub.manager_id if hub assigned
          if ($hubId) $db->prepare("UPDATE hubs SET manager_id=? WHERE id=?")->execute([$uid,$hubId]);
          if (function_exists('logActivity')) logActivity($_SESSION['user_id']??null,'hub_manager_updated',"Updated manager {$email} (#{$uid})");
          $flash=['ok','Manager updated successfully.'];
        }
      } catch(Exception $e){ $flash=['err',$e->getMessage()]; }
    }
  }
}

spx_admin_shell_open('Hub Managers');
$rows = $db->query("SELECT u.*, h.name AS hub_name FROM users u LEFT JOIN hubs h ON h.id=u.hub_id WHERE u.role='hub_manager' ORDER BY u.created_at DESC")->fetchAll();
$hubs = $db->query("SELECT id,name,code FROM hubs ORDER BY name")->fetchAll();
$csrf = generateCSRFToken();
?>

<?php if($flash): ?>
  <div class="glass-card mb-3" style="color:<?= $flash[0]==='ok'?'#10b981':'#ef4444' ?>;">
    <i class="fas fa-<?= $flash[0]==='ok'?'circle-check':'circle-exclamation' ?> me-2"></i><?= htmlspecialchars($flash[1]) ?>
  </div>
<?php endif; ?>

<div class="table-wrapper">
  <div class="table-header">
    <h6 style="margin:0;font-weight:600;">Hub Managers (<?= count($rows) ?>)</h6>
    <a href="<?= BASE_URL ?>/admin/add-hub.php" class="btn-speedex" style="padding:6px 16px;font-size:.75rem;"><i class="fas fa-plus"></i> New Manager (with Hub)</a>
  </div>
  <div style="overflow-x:auto;"><table class="table-speedex">
    <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Hub</th><th>Status</th><th style="width:200px;">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $u): ?>
      <tr>
        <td><?= sanitize($u['full_name']) ?></td>
        <td><?= sanitize($u['email']) ?></td>
        <td><?= sanitize($u['phone']) ?></td>
        <td><?= sanitize($u['hub_name'] ?? '—') ?></td>
        <td><span class="badge-status badge-<?= $u['status']==='active'?'delivered':'pending' ?>"><?= ucfirst($u['status']) ?></span></td>
        <td style="white-space:nowrap;">
          <button type="button" class="btn-speedex" style="padding:4px 9px;font-size:.7rem;" onclick='openEditModal(<?= json_encode($u, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'><i class="fas fa-pen"></i> Edit</button>
          <form method="POST" style="display:inline;">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
            <button name="action" value="toggle_status" class="btn-speedex-outline" style="padding:4px 9px;font-size:.7rem;" title="Toggle active/inactive"><i class="fas fa-power-off"></i></button>
          </form>
          <form method="POST" style="display:inline;" onsubmit="return confirm('Permanently remove this hub manager? Their hub will become unassigned.');">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
            <button name="action" value="delete" class="btn-speedex-outline" style="padding:4px 9px;font-size:.7rem;color:#ef4444;border-color:#ef4444;" title="Remove manager"><i class="fas fa-trash"></i> Remove</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody></table></div>
</div>

<!-- Edit Modal -->
<div id="editMgrModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:2000;align-items:center;justify-content:center;padding:20px;">
  <div class="glass-card" style="max-width:520px;width:100%;max-height:90vh;overflow-y:auto;">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 style="margin:0;font-weight:600;"><i class="fas fa-user-pen me-2" style="color:var(--primary);"></i>Edit Hub Manager</h5>
      <button type="button" onclick="closeEditModal()" style="background:none;border:none;color:var(--text-secondary);font-size:1.3rem;cursor:pointer;">&times;</button>
    </div>
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="user_id" id="em_id">
      <div class="row g-3">
        <div class="col-md-6"><label class="form-label-speedex">Full Name</label><input class="form-control-speedex" name="full_name" id="em_name" required></div>
        <div class="col-md-6"><label class="form-label-speedex">Phone</label><input class="form-control-speedex" name="phone" id="em_phone" required></div>
        <div class="col-12"><label class="form-label-speedex">Email</label><input class="form-control-speedex" type="email" name="email" id="em_email" required></div>
        <div class="col-md-6"><label class="form-label-speedex">Assigned Hub</label>
          <select class="form-control-speedex" name="hub_id" id="em_hub">
            <option value="">— None —</option>
            <?php foreach($hubs as $h): ?>
              <option value="<?= $h['id'] ?>"><?= sanitize($h['name']) ?> (<?= sanitize($h['code']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6"><label class="form-label-speedex">Status</label>
          <select class="form-control-speedex" name="status" id="em_status">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <div class="col-12"><label class="form-label-speedex">New Password <span style="color:var(--text-secondary);font-weight:400;font-size:.75rem;">(leave blank to keep current)</span></label>
          <input class="form-control-speedex" type="text" name="password" placeholder="Min 6 characters">
        </div>
      </div>
      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn-speedex"><i class="fas fa-save"></i> Save Changes</button>
        <button type="button" class="btn-speedex-outline" onclick="closeEditModal()">Cancel</button>
      </div>
    </form>
  </div>
</div>
<script>
function openEditModal(u){
  document.getElementById('em_id').value    = u.id;
  document.getElementById('em_name').value  = u.full_name || '';
  document.getElementById('em_email').value = u.email || '';
  document.getElementById('em_phone').value = u.phone || '';
  document.getElementById('em_hub').value   = u.hub_id || '';
  document.getElementById('em_status').value= u.status || 'active';
  var m = document.getElementById('editMgrModal'); m.style.display='flex';
}
function closeEditModal(){ document.getElementById('editMgrModal').style.display='none'; }
document.getElementById('editMgrModal').addEventListener('click', function(e){ if(e.target===this) closeEditModal(); });
</script>
<?php spx_admin_shell_close(); ?>
