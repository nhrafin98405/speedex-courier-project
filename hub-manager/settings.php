<?php
$pageTitle=ucfirst('settings'); $dashboardType='hub_manager';
require_once __DIR__ . '/../includes/header.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role']!=='hub_manager') { header("Location: " . BASE_URL . "/auth/login.php"); exit; }
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>
<div class="main-content">
  <div class="dashboard-navbar"><h5 style="margin:0;font-weight:600;"><?= $pageTitle ?></h5>
  <div class="theme-toggle theme-switcher"><i class="fas fa-moon"></i><i class="fas fa-sun"></i><div class="toggle-ball"></div></div></div>
  <div class="glass-card"><h4><?= $pageTitle ?> Parcels</h4><p style="color:var(--text-secondary);">Live data view for your hub.</p></div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
