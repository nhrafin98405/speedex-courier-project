<?php
$pageTitle = 'Login';
require_once __DIR__ . '/../includes/header.php';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $userType = sanitize($_POST['user_type'] ?? '');

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND role = ? AND status = 'active'");
            $stmt->execute([$email, $userType]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['hub_id'] = $user['hub_id'];

                // Update last login
                $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);

                // Log activity
                $db->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, 'login', 'User logged in', ?)")
                   ->execute([$user['id'], $_SERVER['REMOTE_ADDR']]);

                // Redirect based on role (or back to ?redirect= if provided)
                $redirectParam = $_GET['redirect'] ?? $_POST['redirect'] ?? '';
                if ($redirectParam && preg_match('#^/[^/]#', $redirectParam) && strpos($redirectParam, '//') === false) {
                    $redirect = $redirectParam;
                } else {
                    $redirect = $user['role'] === 'admin' ? BASE_URL . '/admin/' : BASE_URL . '/hub-manager/';
                }
                header("Location: $redirect");
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        } catch (Exception $e) {
            $error = 'Login failed. Please try again.';
        }
    }
}
?>

<div class="auth-wrapper">
  <div class="auth-card">
    <div class="text-center mb-4">
      <a href="<?= BASE_URL ?>/" class="d-flex align-items-center justify-content-center gap-2 mb-3 text-decoration-none">
        <span class="brand-logo-mark" style="width:54px;height:54px;border-radius:14px;font-size:1.4rem;"><i class="fas fa-shipping-fast"></i></span><div class="brand-logo-text"><span class="brand-name" style="font-size:1.5rem;">NHR</span><span class="brand-tag">Courier Service</span></div>
      </a>
      <h2>Welcome Back! 👋</h2>
      <p>Login to your account</p>
    </div>

    <?php if (isset($error)): ?>
    <div style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); border-radius: var(--radius-md); padding: 12px 16px; margin-bottom: 20px; color: #ef4444; font-size: 0.85rem;">
      <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash_success'])): ?>
    <div style="background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.3); border-radius: var(--radius-md); padding: 12px 16px; margin-bottom: 20px; color: #22c55e; font-size: 0.85rem;">
      <i class="fas fa-check-circle me-2"></i><?= $_SESSION['flash_success'] ?>
    </div>
    <?php unset($_SESSION['flash_success']); endif; ?>

    <form method="POST" data-validate>
      <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
      <input type="hidden" name="redirect" value="<?= htmlspecialchars($_GET['redirect'] ?? '', ENT_QUOTES) ?>">

      <div class="mb-3">
        <label class="form-label-speedex">User Type</label>
        <select name="user_type" class="form-control-speedex" required>
          <option value="">Select user type</option>
          <option value="admin">Admin</option>
          <option value="hub_manager">Hub Manager</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label-speedex">Email</label>
        <input type="email" name="email" class="form-control-speedex" placeholder="Enter your email" required value="<?= isset($_POST['email']) ? sanitize($_POST['email']) : '' ?>">
      </div>

      <div class="mb-3">
        <label class="form-label-speedex">Password</label>
        <div class="input-group">
          <input type="password" name="password" class="form-control-speedex" placeholder="Enter your password" required style="border-radius: var(--radius-md) 0 0 var(--radius-md);">
          <button type="button" class="toggle-password" style="background: var(--bg-input); border: 1px solid var(--border-subtle); border-left: none; border-radius: 0 var(--radius-md) var(--radius-md) 0; padding: 0 12px; color: var(--text-muted); cursor: pointer;">
            <i class="fas fa-eye"></i>
          </button>
        </div>
      </div>

      <div class="d-flex justify-content-between align-items-center mb-4">
        <label style="font-size: 0.8rem; color: var(--text-secondary); cursor: pointer;">
          <input type="checkbox" name="remember" style="margin-right: 6px;"> Remember me
        </label>
        <a href="forgot-password.php" style="font-size: 0.8rem; color: #ef4444;">Forgot Password?</a>
      </div>

      <button type="submit" class="btn-speedex w-100 justify-content-center mb-4">
        <i class="fas fa-sign-in-alt"></i> Login
      </button>

      <div style="background: rgba(34,197,94,0.08); border: 1px solid rgba(34,197,94,0.2); border-radius: var(--radius-md); padding: 12px; margin-bottom: 20px;">
        <p style="font-size: 0.75rem; color: var(--primary); margin-bottom: 4px;"><i class="fas fa-info-circle me-1"></i> <strong>Note</strong></p>
        <p style="font-size: 0.75rem; color: var(--text-secondary); margin: 0;">Customer account is not required. They can send and receive parcels without creating an account.</p>
      </div>

      <p class="text-center" style="font-size: 0.85rem; color: var(--text-secondary);">
        or
      </p>
      <p class="text-center" style="font-size: 0.85rem;">
        <a href="register.php" style="color: var(--primary);">Create an account</a>
      </p>
      <p class="text-center" style="font-size: 0.75rem; color: var(--text-muted);">
        <a href="register.php?role=admin" style="color: var(--primary);">Register as Admin</a> or
        <a href="register.php?role=hub_manager" style="color: var(--primary);">Hub Manager</a>
      </p>
      <p class="text-center mt-3" style="font-size: 0.8rem; color: var(--text-muted);">
        Already have an account? <a href="login.php" style="color: var(--primary);">Login</a>
      </p>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body></html>
