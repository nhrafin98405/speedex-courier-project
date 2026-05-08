<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
try {
  $db = Database::getConnection();
  $rows = $db->query("SELECT h.id, h.name, h.code, h.district,
        (SELECT COUNT(*) FROM parcels p WHERE p.sender_hub_id=h.id OR p.receiver_hub_id=h.id) total,
        (SELECT COUNT(*) FROM parcels p WHERE p.receiver_hub_id=h.id AND p.status IN('in_transit','at_hub','out_for_delivery')) incoming,
        (SELECT COUNT(*) FROM parcels p WHERE p.sender_hub_id=h.id AND p.status IN('in_transit','out_for_delivery')) outgoing,
        (SELECT COUNT(*) FROM parcels p WHERE p.receiver_hub_id=h.id AND p.status='delivered') delivered
        FROM hubs h ORDER BY h.name")->fetchAll();
  echo json_encode(['hubs'=>$rows]);
} catch (Exception $e) { echo json_encode(['hubs'=>[]]); }
