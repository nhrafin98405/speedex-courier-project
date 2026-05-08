<?php
require_once __DIR__ . '/../includes/admin_shell.php';
spx_admin_shell_open('Tracking');
?>
<div class="glass-card">
  <h5 class="mb-3"><span class="live-dot"></span> Live Tracking Console</h5>
  <form id="liveTrackForm">
    <div class="tracking-input-wrapper">
      <i class="fas fa-search" style="color:var(--text-muted);padding-left:16px;"></i>
      <input type="text" id="trackingInput" placeholder="Enter Tracking ID e.g. SPX12345678901" required>
      <button type="submit" class="btn-speedex"><i class="fas fa-search"></i> Track</button>
    </div>
  </form>
  <div id="trackingResults" class="mt-4" style="display:none;"></div>
</div>
<?php spx_admin_shell_close(); ?>
