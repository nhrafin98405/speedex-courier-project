<?php
/** Reusable admin shell helper */
require_once __DIR__ . '/../config/database.php';
if (session_status() === PHP_SESSION_NONE) session_start();
function spx_admin_shell_open(string $title) {
  global $pageTitle, $dashboardType;
  $pageTitle = $title; $dashboardType = 'admin';
  require __DIR__ . '/header.php';
  if (!isset($_SESSION['user_id']) || $_SESSION['user_role']!=='admin') { header("Location: " . BASE_URL . "/auth/login.php"); exit; }
  require __DIR__ . '/sidebar.php';
  echo '<div class="main-content">';
  echo '<div class="dashboard-navbar"><div class="d-flex align-items-center gap-3">';
  echo '<button id="sidebarToggle" class="d-lg-none" style="background:none;border:none;color:var(--text-primary);font-size:1.2rem;"><i class="fas fa-bars"></i></button>';
  echo '<h5 style="margin:0;font-weight:600;">' . sanitize($title) . '</h5></div>';
  echo '<div class="d-flex align-items-center gap-3" style="position:relative;">';
  echo '<div class="theme-toggle theme-switcher"><i class="fas fa-moon"></i><i class="fas fa-sun"></i><div class="toggle-ball"></div></div>';
  echo '<div style="position:relative;cursor:pointer;" id="notifBellBtn"><i class="fas fa-bell" style="color:var(--text-secondary);font-size:1.1rem;"></i><span id="notifBadge" class="notification-badge" style="display:none;background:#ef4444;color:#fff;border-radius:999px;font-size:.65rem;padding:1px 6px;position:absolute;top:-6px;right:-8px;">0</span><div id="notifDropdown" class="notif-dropdown"></div></div>';
  echo '<a href="' . BASE_URL . '/auth/logout.php" class="btn btn-sm" style="background:#ef4444;color:#fff;padding:6px 14px;border-radius:8px;text-decoration:none;font-size:.8rem;font-weight:600;" title="Logout"><i class="fas fa-sign-out-alt"></i> Logout</a>';
  echo '</div></div>';
}
function spx_admin_shell_close() {
  echo '</div>';
  require __DIR__ . '/footer.php';
}
