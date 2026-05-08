<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mail.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$selectedRole = sanitize($_GET['role'] ?? '');
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $name = sanitize($_POST['full_name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $role = sanitize($_POST['role'] ?? '');
        $hubId = intval($_POST['hub_id'] ?? 0);
        $selectedRole = $role;

        if ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif (!in_array($role, ['admin', 'hub_manager'])) {
            $error = 'Invalid role selected.';
        } else {
            try {
                $db = Database::getConnection();
                $check = $db->prepare("SELECT id FROM users WHERE email = ?");
                $check->execute([$email]);
                if ($check->fetch()) {
                    $error = 'Email already registered.';
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("INSERT INTO users (full_name, email, phone, password, role, hub_id) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $email, $phone, $hash, $role, $hubId ?: null]);
                    Mailer::registrationSuccess($email, $name);
                    pushNotification(null,'user','New Registration', "{$name} registered as {$role}");
                    $_SESSION['flash_success'] = 'Account created successfully! A welcome email has been sent. Please login to continue.';
                    header('Location: login.php');
                    exit;
                }
            } catch (Exception $e) {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

$pageTitle = 'Register';
require_once __DIR__ . '/../includes/header.php';

// Fetch hubs for selection
try {
    $db = Database::getConnection();
    $hubs = $db->query("SELECT id, name FROM hubs WHERE status = 'active' ORDER BY name")->fetchAll();
} catch (Exception $e) {
    $hubs = [];
}
?>

<div class="auth-wrapper">
  <div class="auth-card" style="max-width: 560px;">
    <div class="text-center mb-4">
      <a href="<?= BASE_URL ?>/" class="d-flex align-items-center justify-content-center gap-2 mb-3 text-decoration-none">
        <span class="brand-logo-mark" style="width:54px;height:54px;border-radius:14px;font-size:1.4rem;"><i class="fas fa-shipping-fast"></i></span><div class="brand-logo-text"><span class="brand-name" style="font-size:1.5rem;">NHR</span><span class="brand-tag">Courier Service</span></div>
      </a>
      <h2>Create Account</h2>
      <p>Choose how you want to register</p>
    </div>

    <?php if (isset($error)): ?>
    <div style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); border-radius: var(--radius-md); padding: 12px 16px; margin-bottom: 20px; color: #ef4444; font-size: 0.85rem;">
      <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
    </div>
    <?php endif; ?>

    <!-- Role Selection -->
    <div class="row g-3 mb-4">
      <div class="col-6">
        <div class="role-card <?= $selectedRole === 'admin' ? 'selected' : '' ?>" onclick="selectRole('admin')">
          <div class="role-icon"><i class="fas fa-user-shield"></i></div>
          <h6 style="font-weight: 600; margin-bottom: 4px;">I am an Admin</h6>
          <p style="font-size: 0.75rem; color: var(--text-muted);">Full access to manage the entire system.</p>
        </div>
      </div>
      <div class="col-6">
        <div class="role-card <?= $selectedRole === 'hub_manager' ? 'selected' : '' ?>" onclick="selectRole('hub_manager')">
          <div class="role-icon"><i class="fas fa-user-tie"></i></div>
          <h6 style="font-weight: 600; margin-bottom: 4px;">I am a Hub Manager</h6>
          <p style="font-size: 0.75rem; color: var(--text-muted);">Manage and monitor hub activities.</p>
        </div>
      </div>
    </div>

    <form method="POST" data-validate>
      <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
      <input type="hidden" name="role" id="roleInput" value="<?= $selectedRole ?>">

      <div class="row g-3">
        <div class="col-12">
          <label class="form-label-speedex">Full Name</label>
          <input type="text" name="full_name" class="form-control-speedex" placeholder="Enter your full name" required>
        </div>
        <div class="col-md-6">
          <label class="form-label-speedex">Email</label>
          <input type="email" name="email" class="form-control-speedex" placeholder="your@email.com" required>
        </div>
        <div class="col-md-6">
          <label class="form-label-speedex">Phone</label>
          <input type="tel" name="phone" class="form-control-speedex" placeholder="01XXX-XXXXXX" required>
        </div>
        <div class="col-md-6">
          <label class="form-label-speedex">Password</label>
          <div class="input-group">
            <input type="password" name="password" class="form-control-speedex" placeholder="Min 8 characters" required style="border-radius: var(--radius-md) 0 0 var(--radius-md);">
            <button type="button" class="toggle-password" style="background: var(--bg-input); border: 1px solid var(--border-subtle); border-left: none; border-radius: 0 var(--radius-md) var(--radius-md) 0; padding: 0 12px; color: var(--text-muted); cursor: pointer;">
              <i class="fas fa-eye"></i>
            </button>
          </div>
        </div>
        <div class="col-md-6">
          <label class="form-label-speedex">Confirm Password</label>
          <input type="password" name="confirm_password" class="form-control-speedex" placeholder="Confirm password" required>
        </div>
        <div class="col-12" id="hubSelect" style="<?= $selectedRole === 'hub_manager' ? '' : 'display:none;' ?>">
          <label class="form-label-speedex">Select Hub</label>
          <select name="hub_id" class="form-control-speedex">
            <option value="">Choose a hub</option>
            <?php foreach ($hubs as $hub): ?>
            <option value="<?= $hub['id'] ?>"><?= sanitize($hub['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <button type="submit" class="btn-speedex w-100 justify-content-center mt-4">
        <i class="fas fa-user-plus"></i> Create Account
      </button>

      <p class="text-center mt-3" style="font-size: 0.85rem; color: var(--text-muted);">
        Already have an account? <a href="login.php" style="color: var(--primary);">Login</a>
      </p>
    </form>
  </div>
</div>

<script>
function selectRole(role) {
  document.getElementById('roleInput').value = role;
  document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
  event.currentTarget.classList.add('selected');
  document.getElementById('hubSelect').style.display = role === 'hub_manager' ? '' : 'none';
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body></html>
