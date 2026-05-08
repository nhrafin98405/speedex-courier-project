<?php
require_once __DIR__ . '/../includes/admin_shell.php';
spx_admin_shell_open('Profile');
$db = Database::getConnection();
$me = $db->prepare("SELECT * FROM users WHERE id=?"); $me->execute([$_SESSION['user_id']]); $me=$me->fetch();
?>
<div class="glass-form" style="max-width:680px;">
  <div class="text-center mb-4">
    <div style="width:96px;height:96px;border-radius:50%;background:linear-gradient(135deg,#22c55e,#15803d);margin:0 auto;display:flex;align-items:center;justify-content:center;font-size:2.5rem;color:#fff;font-weight:700;"><?= strtoupper(substr($me['full_name'],0,1)) ?></div>
    <h4 class="mt-3"><?= sanitize($me['full_name']) ?></h4>
    <p style="color:var(--text-muted);"><?= ucfirst($me['role']) ?> · <?= sanitize($me['email']) ?></p>
  </div>
  <div class="row g-3">
    <div class="col-md-6"><label class="form-label-speedex">Full Name</label><input class="form-control-speedex" value="<?= sanitize($me['full_name']) ?>"></div>
    <div class="col-md-6"><label class="form-label-speedex">Phone</label><input class="form-control-speedex" value="<?= sanitize($me['phone']) ?>"></div>
  </div>
</div>
<?php spx_admin_shell_close(); ?>
