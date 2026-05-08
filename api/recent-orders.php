<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
$labels = ['status'=>['pending'=>'Pending','picked_up'=>'Picked Up','in_transit'=>'In Transit','at_hub'=>'At Hub','out_for_delivery'=>'Out for Delivery','delivered'=>'Delivered','returned'=>'Returned','cancelled'=>'Cancelled']];
$cls = ['pending'=>'pending','picked_up'=>'in-transit','in_transit'=>'in-transit','at_hub'=>'in-transit','out_for_delivery'=>'out-delivery','delivered'=>'delivered','returned'=>'pending','cancelled'=>'pending'];
try {
  $db = Database::getConnection();
  $rows = $db->query("SELECT p.*, s.name AS from_hub, r.name AS to_hub
                      FROM parcels p
                      JOIN hubs s ON s.id=p.sender_hub_id
                      JOIN hubs r ON r.id=p.receiver_hub_id
                      ORDER BY p.created_at DESC LIMIT 8")->fetchAll();
  $out = [];
  foreach ($rows as $p) {
    $out[] = [
      'tracking_id' => $p['tracking_id'],
      'sender_name' => $p['sender_name'],
      'receiver_name' => $p['receiver_name'],
      'from_hub'    => $p['from_hub'],
      'to_hub'      => $p['to_hub'],
      'status_class'=> $cls[$p['status']] ?? 'pending',
      'status_label'=> $labels['status'][$p['status']] ?? $p['status'],
      'payment_method' => ucwords(str_replace('_',' ',$p['payment_method'])),
      'created_at'  => date('d M Y', strtotime($p['created_at'])),
    ];
  }
  echo json_encode(['parcels'=>$out]);
} catch (Exception $e) { echo json_encode(['parcels'=>[]]); }
