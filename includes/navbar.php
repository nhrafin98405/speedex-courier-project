<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$role = $_SESSION['user_role'] ?? null;
$uname = $_SESSION['user_name'] ?? ($_SESSION['username'] ?? 'Account');
$cp = $currentPage ?? basename($_SERVER['PHP_SELF'], '.php');
?>
<!-- Site Navbar -->
<nav class="navbar navbar-expand-lg navbar-speedex">
  <div class="container">
    <a class="navbar-brand brand-logo-wrap" href="<?= BASE_URL ?>/">
      <span class="brand-logo-mark"><i class="fas fa-shipping-fast"></i></span>
      <span class="brand-logo-text">
        <span class="brand-name">NHR</span>
        <span class="brand-tag">Courier Service</span>
      </span>
    </a>

    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
      <i class="fas fa-bars" style="color: var(--text-primary);"></i>
    </button>

    <div class="collapse navbar-collapse" id="navbarMain">
      <ul class="navbar-nav mx-auto">
      <?php if ($role === 'hub_manager'): ?>
        <li class="nav-item"><a class="nav-link <?= $cp==='index' && strpos($_SERVER['PHP_SELF'],'hub-manager')!==false?'active':'' ?>" href="<?= BASE_URL ?>/hub-manager/index.php"><i class="fas fa-th-large me-1"></i> Dashboard</a></li>
        <li class="nav-item"><a class="nav-link <?= $cp==='send-parcel'?'active':'' ?>" href="<?= BASE_URL ?>/send-parcel.php"><i class="fas fa-paper-plane me-1"></i> Send Parcel</a></li>
        <li class="nav-item"><a class="nav-link <?= $cp==='incoming'?'active':'' ?>" href="<?= BASE_URL ?>/hub-manager/incoming.php"><i class="fas fa-arrow-down me-1"></i> Incoming</a></li>
        <li class="nav-item"><a class="nav-link <?= $cp==='outgoing'?'active':'' ?>" href="<?= BASE_URL ?>/hub-manager/outgoing.php"><i class="fas fa-arrow-up me-1"></i> Outgoing</a></li>
        <li class="nav-item"><a class="nav-link <?= $cp==='delivered'?'active':'' ?>" href="<?= BASE_URL ?>/hub-manager/delivered.php"><i class="fas fa-check-circle me-1"></i> Delivered</a></li>
        <li class="nav-item"><a class="nav-link <?= $cp==='tracking'?'active':'' ?>" href="<?= BASE_URL ?>/tracking.php"><i class="fas fa-location-crosshairs me-1"></i> Track</a></li>
        <li class="nav-item"><a class="nav-link <?= $cp==='find-hub'?'active':'' ?>" href="<?= BASE_URL ?>/find-hub.php"><i class="fas fa-map-marker-alt me-1"></i> Find Hub</a></li>
        <li class="nav-item"><a class="nav-link <?= $cp==='services'?'active':'' ?>" href="<?= BASE_URL ?>/services.php"><i class="fas fa-cogs me-1"></i> Services</a></li>
        <li class="nav-item"><a class="nav-link <?= $cp==='about'?'active':'' ?>" href="<?= BASE_URL ?>/about.php"><i class="fas fa-info-circle me-1"></i> About</a></li>
        <li class="nav-item"><a class="nav-link <?= $cp==='contact'?'active':'' ?>" href="<?= BASE_URL ?>/contact.php"><i class="fas fa-envelope me-1"></i> Contact</a></li>
        <li class="nav-item"><a class="nav-link <?= $cp==='reports'?'active':'' ?>" href="<?= BASE_URL ?>/hub-manager/reports.php"><i class="fas fa-chart-bar me-1"></i> Reports</a></li>
        <li class="nav-item"><a class="nav-link <?= $cp==='settings'?'active':'' ?>" href="<?= BASE_URL ?>/hub-manager/settings.php"><i class="fas fa-cog me-1"></i> Settings</a></li>
      <?php elseif ($role === 'admin'): ?>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/index.php"><i class="fas fa-th-large me-1"></i> Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/parcels.php">Parcels</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/send-parcel.php">Send Parcel</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/hubs.php">Hubs</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/users.php">Users</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/reports.php">Reports</a></li>
      <?php else: ?>
        <li class="nav-item"><a class="nav-link <?= $cp === 'index' ? 'active' : '' ?>" href="<?= BASE_URL ?>/">Home</a></li>
        <li class="nav-item"><a class="nav-link <?= $cp === 'tracking' ? 'active' : '' ?>" href="<?= BASE_URL ?>/tracking.php">Track Parcel</a></li>
        <li class="nav-item"><a class="nav-link <?= $cp === 'send-parcel' ? 'active' : '' ?>" href="<?= BASE_URL ?>/send-parcel.php">Send Parcel</a></li>
        <li class="nav-item"><a class="nav-link <?= $cp === 'find-hub' ? 'active' : '' ?>" href="<?= BASE_URL ?>/find-hub.php">Find Hub</a></li>
        <li class="nav-item"><a class="nav-link <?= $cp === 'services' ? 'active' : '' ?>" href="<?= BASE_URL ?>/services.php">Services</a></li>
        <li class="nav-item"><a class="nav-link <?= $cp === 'about' ? 'active' : '' ?>" href="<?= BASE_URL ?>/about.php">About Us</a></li>
        <li class="nav-item"><a class="nav-link <?= $cp === 'contact' ? 'active' : '' ?>" href="<?= BASE_URL ?>/contact.php">Contact</a></li>
      <?php endif; ?>
      </ul>

      <div class="d-flex align-items-center gap-3">
        <div class="theme-toggle" title="Toggle theme">
          <i class="fas fa-moon"></i>
          <i class="fas fa-sun"></i>
          <div class="toggle-ball"></div>
        </div>
        <span class="theme-label d-none d-lg-inline" style="font-size: 0.75rem; color: var(--text-muted);">Dark</span>

        <?php if ($role): ?>
          <span class="d-none d-lg-inline" style="font-size:.8rem;color:var(--text-secondary);"><i class="fas fa-user-circle me-1"></i><?= htmlspecialchars($uname) ?></span>
          <a href="<?= BASE_URL ?>/auth/logout.php" class="btn-speedex-outline" style="padding: 6px 20px; font-size: 0.8rem;">Logout</a>
        <?php else: ?>
          <a href="<?= BASE_URL ?>/auth/login.php" class="btn-speedex-outline" style="padding: 6px 20px; font-size: 0.8rem;">Login</a>
          <a href="<?= BASE_URL ?>/auth/register.php" class="btn-speedex" style="padding: 6px 20px; font-size: 0.8rem;">Register</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
