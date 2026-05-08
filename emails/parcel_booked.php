<?php
$p = $parcel ?? [];
$tid = htmlspecialchars($p['tracking_id'] ?? '');
$heading = "Parcel Booked Successfully 📦";
$intro   = "Hi " . htmlspecialchars($p['sender_name'] ?? '') . ", your parcel has been booked with nhr.";
$bodyHtml = '
<table width="100%" cellpadding="8" style="background:#0b1220;border:1px solid rgba(34,197,94,0.2);border-radius:10px;color:#cbd5e1;font-size:13px;">
  <tr><td>Tracking ID</td><td style="color:#22c55e;font-weight:700;">'.$tid.'</td></tr>
  <tr><td>Receiver</td><td>'.htmlspecialchars($p['receiver_name'] ?? '').'</td></tr>
  <tr><td>Type</td><td>'.htmlspecialchars($p['parcel_type'] ?? '').'</td></tr>
  <tr><td>Weight</td><td>'.htmlspecialchars((string)($p['weight'] ?? '')).' kg</td></tr>
  <tr><td>Total</td><td>৳ '.htmlspecialchars((string)($p['total_amount'] ?? '')).'</td></tr>
</table>';
$cta = ['label' => 'Track Parcel', 'url' => 'http://localhost/speedex-courier/tracking.php?id=' . $tid];
include __DIR__ . '/_layout.php';
