<?php
$pageTitle = 'Admin Dashboard'; $dashboardType='admin';
require_once __DIR__ . '/../includes/header.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role']!=='admin') { header("Location: " . BASE_URL . "/auth/login.php"); exit; }
require_once __DIR__ . '/../includes/sidebar.php';
?>
<div class="main-content">
  <div class="dashboard-navbar">
    <div class="d-flex align-items-center gap-3">
      <button id="sidebarToggle" class="d-lg-none" style="background:none;border:none;color:var(--text-primary);font-size:1.2rem;"><i class="fas fa-bars"></i></button>
      <h5 style="margin:0;font-weight:600;"><span class="live-dot"></span>Live Dashboard</h5>
    </div>
    <div class="d-flex align-items-center gap-3" style="position:relative;">
      <div class="theme-toggle theme-switcher"><i class="fas fa-moon"></i><i class="fas fa-sun"></i><div class="toggle-ball"></div></div>
      <div style="position:relative;cursor:pointer;" id="notifBellBtn">
        <i class="fas fa-bell" style="color:var(--text-secondary);font-size:1.1rem;"></i>
        <span id="notifBadge" class="notification-badge" style="display:none;background:#ef4444;color:#fff;border-radius:999px;font-size:.65rem;padding:1px 6px;position:absolute;top:-6px;right:-8px;">0</span>
        <div id="notifDropdown" class="notif-dropdown"></div>
      </div>
      <div class="d-flex align-items-center gap-2">
        <div style="width:36px;height:36px;background:var(--primary);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:600;">SA</div>
        <div class="d-none d-md-block"><small style="font-weight:600;font-size:.8rem;"><?= sanitize($_SESSION['user_name']) ?></small><br><small style="color:var(--text-muted);font-size:.7rem;">Super Admin</small></div>
      </div>
    </div>
  </div>

  <div id="liveStatsGrid" class="row g-4 mb-4">
    <?php
    $cards = [
      ['id'=>'stat-total','icon'=>'fa-box','color'=>'blue','label'=>'Total Parcels','status'=>'all'],
      ['id'=>'stat-transit','icon'=>'fa-truck','color'=>'yellow','label'=>'In Transit','status'=>'in_transit'],
      ['id'=>'stat-delivered','icon'=>'fa-check-circle','color'=>'green','label'=>'Delivered','status'=>'delivered'],
      ['id'=>'stat-pending','icon'=>'fa-clock','color'=>'red','label'=>'Pending','status'=>'pending'],
    ];
    foreach ($cards as $c): ?>
    <div class="col-lg-3 col-md-6">
      <a class="stat-card-link" href="<?= BASE_URL ?>/admin/parcels.php?status=<?= $c['status'] ?>">
        <div class="stat-card hover-glow">
          <div class="stat-icon <?= $c['color'] ?>"><i class="fas <?= $c['icon'] ?>"></i></div>
          <div class="stat-value" id="<?= $c['id'] ?>">…</div>
          <div class="stat-label"><?= $c['label'] ?></div>
        </div>
      </a>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="row g-4 mb-4">
    <div class="col-lg-8"><div class="chart-card"><h6>Monthly Parcels</h6><div style="height:300px;"><canvas id="deliveryChart"></canvas></div></div></div>
    <div class="col-lg-4"><div class="chart-card"><h6>Parcels Overview</h6><div style="height:300px;"><canvas id="parcelOverviewChart"></canvas></div></div></div>
  </div>

  <div class="table-wrapper mb-4">
    <div class="table-header"><h6 style="font-weight:600;margin:0;"><span class="live-dot"></span>Recent Parcels</h6>
      <a href="parcels.php" class="btn-speedex" style="padding:6px 16px;font-size:.75rem;">View All</a></div>
    <div style="overflow-x:auto;">
      <table class="table-speedex">
        <thead><tr><th>Tracking ID</th><th>Sender</th><th>Receiver</th><th>From Hub</th><th>To Hub</th><th>Status</th><th>Payment</th><th>Date</th></tr></thead>
        <tbody id="recentParcelsBody"><tr><td colspan="8"><div class="skeleton" style="height:30px;"></div></td></tr></tbody>
      </table>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-6"><div class="chart-card"><h6>Top Hub Routes</h6><div style="height:250px;"><canvas id="topRoutesChart"></canvas></div></div></div>
    <div class="col-lg-6"><div class="chart-card"><h6>Monthly Revenue</h6><div style="height:250px;"><canvas id="revenueChart"></canvas></div></div></div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
