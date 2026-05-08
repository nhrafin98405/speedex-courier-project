<?php
$p = $parcel ?? []; $tid = htmlspecialchars($p['tracking_id'] ?? '');
$heading = "Your Parcel Is On Its Way 🚚";
$intro = "Hi " . htmlspecialchars($p['receiver_name'] ?? '') . ", a parcel from " . htmlspecialchars($p['sender_name'] ?? '') . " is currently in transit.";
$bodyHtml = '<p style="color:#cbd5e1;">Tracking ID: <b style="color:#22c55e;">'.$tid.'</b></p>';
$cta = ['label'=>'Track Now','url'=>'http://localhost/speedex-courier/tracking.php?id='.$tid];
include __DIR__ . '/_layout.php';
