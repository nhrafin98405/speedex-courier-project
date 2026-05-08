<?php
$name = $name ?? 'Customer';
$heading = "Welcome aboard, " . htmlspecialchars($name) . "! 👋";
$intro   = "Your nhr account has been created successfully. You can now book parcels, track shipments in real time, and receive instant email updates.";
$bodyHtml = '<p style="color:#cbd5e1;font-size:14px;line-height:1.7;">Use your registered email and password to sign in to your dashboard. If you didn\'t create this account, please ignore this message.</p>';
$cta = ['label' => 'Open Dashboard', 'url' => 'http://localhost/speedex-courier/auth/login.php'];
include __DIR__ . '/_layout.php';
