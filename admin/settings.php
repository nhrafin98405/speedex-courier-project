<?php
require_once __DIR__ . '/../includes/admin_shell.php';
spx_admin_shell_open('Settings');
?>
<div class="glass-form" style="max-width:760px;">
  <h5 class="mb-3"><i class="fas fa-cog me-2 text-primary-green"></i>System Settings</h5>
  <div class="row g-3">
    <div class="col-md-6"><label class="form-label-speedex">Site Name</label><input class="form-control-speedex" value="<?= SITE_NAME ?>"></div>
    <div class="col-md-6"><label class="form-label-speedex">Default Theme</label><select class="form-control-speedex"><option>Dark</option><option>Light</option></select></div>
    <div class="col-md-6"><label class="form-label-speedex">SMTP Host</label><input class="form-control-speedex" value="smtp.gmail.com"></div>
    <div class="col-md-6"><label class="form-label-speedex">From Email</label><input class="form-control-speedex" value="no-reply@speedex.com"></div>
  </div>
  <button class="btn-speedex mt-4" type="button" onclick="toast('Settings saved')"><i class="fas fa-save"></i> Save</button>
</div>
<?php spx_admin_shell_close(); ?>
