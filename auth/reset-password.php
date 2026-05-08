<?php
$pageTitle = 'Reset Password';
require_once __DIR__ . '/../includes/header.php';

$db = Database::getConnection();
$db->exec("CREATE TABLE IF NOT EXISTS password_resets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pr_user (user_id),
    INDEX idx_pr_exp (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$token = $_GET['token'] ?? $_POST['token'] ?? '';
$error = $success = '';
$validToken = false;
$tokenRow = null;

if ($token && preg_match('/^[a-f0-9]{64}$/', $token)) {
    $hash = hash('sha256', $token);
    $stmt = $db->prepare("SELECT pr.*, u.full_name, u.email FROM password_resets pr
        JOIN users u ON u.id = pr.user_id
        WHERE pr.token_hash = ? AND pr.used_at IS NULL AND pr.expires_at > NOW()
        LIMIT 1");
    $stmt->execute([$hash]);
    $tokenRow = $stmt->fetch();
    $validToken = (bool)$tokenRow;
}

if (!$validToken && !$success) {
    $error = $error ?: 'This password reset link is invalid or has expired. Please request a new one.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $pw  = $_POST['password'] ?? '';
        $pw2 = $_POST['password_confirm'] ?? '';

        if (strlen($pw) < 8) {
            $error = 'Password must be at least 8 characters long.';
        } elseif ($pw !== $pw2) {
            $error = 'Passwords do not match.';
        } else {
            try {
                $hashPw = password_hash($pw, PASSWORD_DEFAULT);
                $db->prepare("UPDATE users SET password = ? WHERE id = ?")
                   ->execute([$hashPw, $tokenRow['user_id']]);
                $db->prepare("UPDATE password_resets SET used_at = NOW() WHERE id = ?")
                   ->execute([$tokenRow['id']]);
                // Invalidate any other outstanding tokens
                $db->prepare("UPDATE password_resets SET used_at=NOW() WHERE user_id=? AND used_at IS NULL")
                   ->execute([$tokenRow['user_id']]);
                try {
                    $db->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, 'password_reset', 'Password reset successfully', ?)")
                       ->execute([$tokenRow['user_id'], $_SERVER['REMOTE_ADDR'] ?? '']);
                } catch (Exception $e) {}

                $success = 'Your password has been reset successfully. You can now log in with your new password.';
                $validToken = false;
            } catch (Exception $e) {
                $error = 'Failed to reset password. Please try again.';
            }
        }
    }
}
?>

<div class="auth-wrapper">
  <div class="auth-card">
    <div class="text-center mb-4">
      <a href="<?= BASE_URL ?>/" class="d-flex align-items-center justify-content-center gap-2 mb-3 text-decoration-none">
        <span class="brand-logo-mark" style="width:54px;height:54px;border-radius:14px;font-size:1.4rem;"><i class="fas fa-shipping-fast"></i></span>
        <div class="brand-logo-text"><span class="brand-name" style="font-size:1.5rem;">NHR</span><span class="brand-tag">Courier Service</span></div>
      </a>
      <h2>Set a New Password 🔑</h2>
      <p>Choose a strong password for your account.</p>
    </div>

    <?php if ($error): ?>
    <div style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); border-radius: var(--radius-md); padding: 12px 16px; margin-bottom: 20px; color: #ef4444; font-size: 0.85rem;">
      <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <?php if ($success): ?>
    <div style="background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.3); border-radius: var(--radius-md); padding: 12px 16px; margin-bottom: 20px; color: #22c55e; font-size: 0.85rem;">
      <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
    </div>
    <p class="text-center mt-3">
      <a href="login.php" class="btn-speedex w-100 justify-content-center"><i class="fas fa-sign-in-alt"></i> Go to Login</a>
    </p>
    <?php elseif ($validToken): ?>
    <form method="POST" data-validate>
      <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
      <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

      <div class="mb-3">
        <label class="form-label-speedex">Account</label>
        <input type="email" class="form-control-speedex" value="<?= htmlspecialchars($tokenRow['email']) ?>" disabled>
      </div>

      <div class="mb-3">
        <label class="form-label-speedex">New Password</label>
        <div class="input-group">
          <input type="password" name="password" class="form-control-speedex" placeholder="At least 8 characters" required minlength="8" style="border-radius: var(--radius-md) 0 0 var(--radius-md);">
          <button type="button" class="toggle-password" style="background: var(--bg-input); border: 1px solid var(--border-subtle); border-left: none; border-radius: 0 var(--radius-md) var(--radius-md) 0; padding: 0 12px; color: var(--text-muted); cursor: pointer;">
            <i class="fas fa-eye"></i>
          </button>
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label-speedex">Confirm New Password</label>
        <input type="password" name="password_confirm" class="form-control-speedex" placeholder="Re-enter password" required minlength="8">
      </div>

      <button type="submit" class="btn-speedex w-100 justify-content-center mb-4">
        <i class="fas fa-key"></i> Reset Password
      </button>

      <p class="text-center" style="font-size: 0.85rem;">
        <a href="login.php" style="color: var(--primary);"><i class="fas fa-arrow-left me-1"></i> Back to Login</a>
      </p>
    </form>
    <?php else: ?>
    <p class="text-center mt-3">
      <a href="forgot-password.php" class="btn-speedex w-100 justify-content-center"><i class="fas fa-redo"></i> Request New Link</a>
    </p>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body></html>
