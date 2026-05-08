<?php
$name = $name ?? 'User';
$resetUrl = $resetUrl ?? '#';
$heading = "Reset your password 🔐";
$intro   = "Hi " . htmlspecialchars($name) . ", we received a request to reset the password for your nhr Courier account.";
$bodyHtml = '<p style="color:#cbd5e1;font-size:14px;line-height:1.7;">Click the button below to choose a new password. This link will expire in <strong>60 minutes</strong>. If you did not request a password reset, you can safely ignore this email — your password will remain unchanged.</p>';
$cta = ['label' => 'Reset Password', 'url' => $resetUrl];
include __DIR__ . '/_layout.php';
