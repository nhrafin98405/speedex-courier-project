<?php
$p = $parcel ?? []; $tid = htmlspecialchars($p['tracking_id'] ?? '');
$heading = "Out for Delivery 🛵";
$intro = "Hi " . htmlspecialchars($p['receiver_name'] ?? '') . ", your nhr parcel is out for delivery and will reach you shortly.";
$bodyHtml = '<p style="color:#cbd5e1;">Tracking ID: <b style="color:#22c55e;">'.$tid.'</b><br>Please keep your phone available so our delivery agent can reach you.</p>';
$cta = ['label'=>'Track Live','url'=>'http://localhost/speedex-courier/tracking.php?id='.$tid];
include __DIR__ . '/_layout.php';
