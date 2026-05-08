<?php
require_once __DIR__ . '/../includes/admin_shell.php';
$success=$error=null;
if ($_SERVER['REQUEST_METHOD']==='POST') {
  if (!validateCSRFToken($_POST['csrf_token'] ?? '')) $error='Invalid token.';
  else {
    $db = Database::getConnection();
    try {
      $createMgr = !empty($_POST['create_manager']);
      $mgrId = $_POST['manager_id'] ?: null;
      $mgrEmail = trim($_POST['mgr_email'] ?? '');
      $mgrPass  = $_POST['mgr_password'] ?? '';
      $mgrName  = trim($_POST['mgr_name'] ?? '');
      $mgrPhone = trim($_POST['mgr_phone'] ?? '');

      if ($createMgr) {
        if ($mgrName==='' || $mgrEmail==='' || $mgrPhone==='' || strlen($mgrPass)<6) {
          throw new Exception('Manager name, email, phone & password (min 6 chars) are required.');
        }
        $chk = $db->prepare("SELECT id FROM users WHERE email=?");
        $chk->execute([$mgrEmail]);
        if ($chk->fetch()) throw new Exception('A user with this email already exists.');
      }

      $db->beginTransaction();

      $code = strtoupper(substr(sanitize($_POST['district']),0,3)) . '-' . str_pad((string)mt_rand(1,999),3,'0',STR_PAD_LEFT);

      // 1. Create hub first (manager_id may be null/existing)
      $stmt = $db->prepare("INSERT INTO hubs (name,code,district,area,address,phone,email,manager_id,status) VALUES (?,?,?,?,?,?,?,?,?)");
      $stmt->execute([
        sanitize($_POST['name']), $code, sanitize($_POST['district']), sanitize($_POST['area']),
        sanitize($_POST['address']), sanitize($_POST['phone']), sanitize($_POST['email'] ?? ''),
        $createMgr ? null : ($mgrId ?: null),
        sanitize($_POST['status'])
      ]);
      $hubId = (int)$db->lastInsertId();

      // 2. If creating new manager, create user + link both ways
      if ($createMgr) {
        $hash = password_hash($mgrPass, PASSWORD_DEFAULT);
        $u = $db->prepare("INSERT INTO users (full_name, email, phone, password, role, hub_id, status) VALUES (?,?,?,?,?,?,?)");
        $u->execute([$mgrName, $mgrEmail, $mgrPhone, $hash, 'hub_manager', $hubId, 'active']);
        $newMgrId = (int)$db->lastInsertId();
        $db->prepare("UPDATE hubs SET manager_id=? WHERE id=?")->execute([$newMgrId, $hubId]);
        logActivity($_SESSION['user_id']??null,'hub_manager_created',"Manager {$mgrEmail} created for hub {$code}");
      } elseif ($mgrId) {
        // Assign existing manager's hub_id to this hub too
        $db->prepare("UPDATE users SET hub_id=? WHERE id=? AND role='hub_manager'")->execute([$hubId, (int)$mgrId]);
      }

      $db->commit();
      logActivity($_SESSION['user_id']??null,'hub_created',"Hub {$code} created");
      pushNotification(null,'hub','New Hub Created',sanitize($_POST['name']).' added in '.sanitize($_POST['district']));
      $success = 'Hub created with code '.$code . ($createMgr ? ' and manager account ready (login: '.htmlspecialchars($mgrEmail).').' : '.');
    } catch(Exception $e){
      if ($db->inTransaction()) $db->rollBack();
      $error='Failed: '.$e->getMessage();
    }
  }
}
spx_admin_shell_open('Add Hub');
$managers = Database::getConnection()->query("SELECT id,full_name FROM users WHERE role='hub_manager' ORDER BY full_name")->fetchAll();
?>
<div class="glass-form" style="max-width:900px;">
  <?php if ($success): ?><div class="mb-3" style="color:var(--primary);"><i class="fas fa-circle-check me-2"></i><?= $success ?></div><?php endif; ?>
  <?php if ($error): ?><div class="mb-3" style="color:#ef4444;"><i class="fas fa-circle-exclamation me-2"></i><?= $error ?></div><?php endif; ?>
  <form method="POST">
    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

    <h5 style="margin:0 0 14px 0;font-weight:600;"><i class="fas fa-warehouse me-2" style="color:var(--primary);"></i>Hub Details</h5>
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label-speedex">Hub Name</label><input class="form-control-speedex" name="name" required></div>
      <div class="col-md-6"><label class="form-label-speedex">District</label><input class="form-control-speedex" name="district" required></div>
      <div class="col-md-6"><label class="form-label-speedex">Thana / Area</label><input class="form-control-speedex" name="area" required></div>
      <div class="col-md-6"><label class="form-label-speedex">Phone</label><input class="form-control-speedex" name="phone" required></div>
      <div class="col-12"><label class="form-label-speedex">Address</label><textarea class="form-control-speedex" rows="2" name="address" required></textarea></div>
      <div class="col-md-6"><label class="form-label-speedex">Hub Email</label><input class="form-control-speedex" type="email" name="email"></div>
      <div class="col-md-6"><label class="form-label-speedex">Status</label>
        <select class="form-control-speedex" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
    </div>

    <hr style="border-color:var(--border);margin:24px 0 18px;">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
      <h5 style="margin:0;font-weight:600;"><i class="fas fa-user-tie me-2" style="color:var(--primary);"></i>Hub Manager</h5>
      <div class="form-check form-switch" style="display:flex;align-items:center;gap:8px;">
        <input class="form-check-input" type="checkbox" id="createMgrToggle" name="create_manager" value="1" checked onchange="toggleMgrMode()">
        <label class="form-check-label" for="createMgrToggle" style="font-size:.85rem;color:var(--text-secondary);">Create new manager account</label>
      </div>
    </div>

    <div id="newMgrFields" class="row g-3">
      <div class="col-md-6"><label class="form-label-speedex">Manager Full Name</label><input class="form-control-speedex" name="mgr_name"></div>
      <div class="col-md-6"><label class="form-label-speedex">Manager Phone</label><input class="form-control-speedex" name="mgr_phone"></div>
      <div class="col-md-6"><label class="form-label-speedex">Login Email</label><input class="form-control-speedex" type="email" name="mgr_email"></div>
      <div class="col-md-6"><label class="form-label-speedex">Password (min 6)</label><input class="form-control-speedex" type="text" name="mgr_password" minlength="6" placeholder="Set login password"></div>
    </div>

    <div id="existingMgrFields" class="row g-3" style="display:none;">
      <div class="col-md-12"><label class="form-label-speedex">Assign Existing Manager</label>
        <select class="form-control-speedex" name="manager_id">
          <option value="">— None —</option>
          <?php foreach ($managers as $m): ?><option value="<?= $m['id'] ?>"><?= sanitize($m['full_name']) ?></option><?php endforeach; ?>
        </select>
      </div>
    </div>

    <button type="submit" class="btn-speedex mt-4"><i class="fas fa-plus"></i> Create Hub</button>
  </form>
</div>
<script>
function toggleMgrMode(){
  var on = document.getElementById('createMgrToggle').checked;
  document.getElementById('newMgrFields').style.display = on ? '' : 'none';
  document.getElementById('existingMgrFields').style.display = on ? 'none' : '';
  // toggle required attrs
  ['mgr_name','mgr_phone','mgr_email','mgr_password'].forEach(function(n){
    var el = document.querySelector('[name="'+n+'"]');
    if (el) { if(on) el.setAttribute('required','required'); else el.removeAttribute('required'); }
  });
}
toggleMgrMode();
</script>
<?php spx_admin_shell_close(); ?>
