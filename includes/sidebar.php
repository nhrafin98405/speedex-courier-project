<?php
$dashboardType = $dashboardType ?? 'admin';
$sidebarPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<div id="sidebarOverlay" class="sidebar-overlay"></div>
<aside class="sidebar" id="speedexSidebar">
  <div class="sidebar-brand">
    <div class="brand-logo-stack">
      <button type="button" id="sidebarCollapseBtn" class="sidebar-collapse-btn brand-toggle-above" title="Toggle sidebar">
        <i class="fas fa-angles-left"></i>
      </button>
      <span class="brand-logo-mark"><i class="fas fa-shipping-fast"></i></span>
    </div>
    <a href="<?= BASE_URL ?>/" class="brand-logo-text-wrap" style="flex:1;min-width:0;text-decoration:none;">
      <span class="brand-logo-text">
        <span class="brand-name">NHR</span>
        <span class="brand-tag"><?= $dashboardType === 'admin' ? 'Super Admin' : 'Hub Manager' ?></span>
      </span>
    </a>
  </div>

  <ul class="sidebar-menu">
    <?php if ($dashboardType === 'admin'): ?>
      <li><a href="<?= BASE_URL ?>/admin/index.php"           class="<?= $sidebarPage==='index'?'active':'' ?>"><i class="fas fa-th-large"></i> <span>Dashboard</span></a></li>
      <li><a href="<?= BASE_URL ?>/admin/parcels.php"         class="<?= $sidebarPage==='parcels'?'active':'' ?>"><i class="fas fa-box"></i> <span>Parcels</span></a></li>
      <li><a href="<?= BASE_URL ?>/admin/bookings.php"        class="<?= $sidebarPage==='bookings'?'active':'' ?>"><i class="fas fa-calendar-check"></i> <span>Bookings</span></a></li>
      <li><a href="<?= BASE_URL ?>/admin/tracking.php"        class="<?= $sidebarPage==='tracking'?'active':'' ?>"><i class="fas fa-location-crosshairs"></i> <span>Tracking</span></a></li>
      <li><a href="<?= BASE_URL ?>/send-parcel.php"           class="<?= $sidebarPage==='send-parcel'?'active':'' ?>"><i class="fas fa-paper-plane"></i> <span>Send Parcel</span></a></li>
      <li><a href="<?= BASE_URL ?>/admin/hubs.php"            class="<?= $sidebarPage==='hubs'?'active':'' ?>"><i class="fas fa-warehouse"></i> <span>Hubs</span></a></li>
      <li><a href="<?= BASE_URL ?>/admin/add-hub.php"         class="<?= $sidebarPage==='add-hub'?'active':'' ?>"><i class="fas fa-plus-circle"></i> <span>Add Hub</span></a></li>
      <li><a href="<?= BASE_URL ?>/admin/users.php"           class="<?= $sidebarPage==='users'?'active':'' ?>"><i class="fas fa-users"></i> <span>Users</span></a></li>
      <li><a href="<?= BASE_URL ?>/admin/hub-managers.php"    class="<?= $sidebarPage==='hub-managers'?'active':'' ?>"><i class="fas fa-user-tie"></i> <span>Hub Managers</span></a></li>
      <li><a href="<?= BASE_URL ?>/admin/reports.php"         class="<?= $sidebarPage==='reports'?'active':'' ?>"><i class="fas fa-chart-bar"></i> <span>Reports</span></a></li>
      <li><a href="<?= BASE_URL ?>/admin/notifications.php"   class="<?= $sidebarPage==='notifications'?'active':'' ?>"><i class="fas fa-bell"></i> <span>Notifications</span></a></li>
      <li><a href="<?= BASE_URL ?>/admin/email-logs.php"      class="<?= $sidebarPage==='email-logs'?'active':'' ?>"><i class="fas fa-envelope-open-text"></i> <span>Email Logs</span></a></li>
      <li><a href="<?= BASE_URL ?>/admin/settings.php"        class="<?= $sidebarPage==='settings'?'active':'' ?>"><i class="fas fa-cog"></i> <span>Settings</span></a></li>
      <li><a href="<?= BASE_URL ?>/admin/activity-logs.php"   class="<?= $sidebarPage==='activity-logs'?'active':'' ?>"><i class="fas fa-history"></i> <span>Activity Logs</span></a></li>
      <li><a href="<?= BASE_URL ?>/admin/profile.php"         class="<?= $sidebarPage==='profile'?'active':'' ?>"><i class="fas fa-user-circle"></i> <span>Profile</span></a></li>
    <?php else: ?>
      <li><a href="<?= BASE_URL ?>/hub-manager/index.php"     class="<?= $sidebarPage==='index'?'active':'' ?>"><i class="fas fa-th-large"></i> <span>Dashboard</span></a></li>
      <li><a href="<?= BASE_URL ?>/hub-manager/incoming.php"  class="<?= $sidebarPage==='incoming'?'active':'' ?>"><i class="fas fa-arrow-down"></i> <span>Incoming</span></a></li>
      <li><a href="<?= BASE_URL ?>/hub-manager/outgoing.php"  class="<?= $sidebarPage==='outgoing'?'active':'' ?>"><i class="fas fa-arrow-up"></i> <span>Outgoing</span></a></li>
      <li><a href="<?= BASE_URL ?>/hub-manager/delivered.php" class="<?= $sidebarPage==='delivered'?'active':'' ?>"><i class="fas fa-check-circle"></i> <span>Delivered</span></a></li>
      <li><a href="<?= BASE_URL ?>/send-parcel.php"           class="<?= $sidebarPage==='send-parcel'?'active':'' ?>"><i class="fas fa-paper-plane"></i> <span>Send Parcel</span></a></li>
      <li><a href="<?= BASE_URL ?>/hub-manager/reports.php"   class="<?= $sidebarPage==='reports'?'active':'' ?>"><i class="fas fa-chart-bar"></i> <span>Reports</span></a></li>
      <li><a href="<?= BASE_URL ?>/hub-manager/settings.php"  class="<?= $sidebarPage==='settings'?'active':'' ?>"><i class="fas fa-cog"></i> <span>Settings</span></a></li>
    <?php endif; ?>
    <li class="sidebar-divider">
      <a href="<?= BASE_URL ?>/auth/logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
    </li>
  </ul>
</aside>
