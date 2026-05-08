<?php
$pageTitle = 'Forgot Password';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/mail.php';

$db = Database::getConnection();
// Ensure password_resets table exists
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

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $email = sanitize($_POST['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            try {
                $stmt = $db->prepare("SELECT id, full_name, email FROM users WHERE email = ? AND status='active' LIMIT 1");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if ($user) {
                    // Invalidate previous unused tokens
                    $db->prepare("UPDATE password_resets SET used_at=NOW() WHERE user_id=? AND used_at IS NULL")
                       ->execute([$user['id']]);

                    $token = bin2hex(random_bytes(32));
                    $hash  = hash('sha256', $token);
                    $exp   = date('Y-m-d H:i:s', time() + 3600);

                    $db->prepare("INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?,?,?)")
                       ->execute([$user['id'], $hash, $exp]);

                    $base = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
                          . '://' . $_SERVER['HTTP_HOST'] . BASE_URL;
                    $resetUrl = $base . '/auth/reset-password.php?token=' . $token;

                    Mailer::send($user['email'], $user['full_name'], 'Reset Your nhr Courier Password',
                        'password_reset', ['name' => $user['full_name'], 'resetUrl' => $resetUrl]);

                    try {
                        $db->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, 'password_reset_requested', 'Password reset email sent', ?)")
                           ->execute([$user['id'], $_SERVER['REMOTE_ADDR'] ?? '']);
                    } catch (Exception $e) {}
                }

                // Always show same message (avoid email enumeration)
                $success = 'If an account exists for that email, we have sent a password reset link. Please check your inbox (and spam folder).';
            } catch (Exception $e) {
                $error = 'Something went wrong. Please try again.';
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
      <h2>Forgot Password? 🔐</h2>
      <p>Enter your email and we'll send you a secure reset link.</p>
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
    <?php endif; ?>

    <form method="POST" data-validate>
      <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

      <div class="mb-3">
        <label class="form-label-speedex">Email</label>
        <input type="email" name="email" class="form-control-speedex" placeholder="you@example.com" required>
      </div>

      <button type="submit" class="btn-speedex w-100 justify-content-center mb-4">
        <i class="fas fa-paper-plane"></i> Send Reset Link
      </button>

      <p class="text-center" style="font-size: 0.85rem;">
        <a href="login.php" style="color: var(--primary);"><i class="fas fa-arrow-left me-1"></i> Back to Login</a>
      </p>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body></html>
