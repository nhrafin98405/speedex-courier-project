<?php
$p = $parcel ?? []; $tid = htmlspecialchars($p['tracking_id'] ?? '');
$heading = "Delivered Successfully ✅";
$intro = "Hi " . htmlspecialchars($p['receiver_name'] ?? '') . ", your nhr parcel (" . $tid . ") has been delivered successfully. Thank you for using nhr Courier Service!";
$bodyHtml = '<p style="color:#cbd5e1;">We hope you had a great experience. Share your feedback to help us improve.</p>';
$cta = ['label'=>'Send Another Parcel','url'=>'http://localhost/speedex-courier/send-parcel.php'];
include __DIR__ . '/_layout.php';
