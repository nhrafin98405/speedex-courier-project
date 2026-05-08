<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
$labels = ['pending'=>'Pending','picked_up'=>'Picked Up','in_transit'=>'In Transit','at_hub'=>'At Hub','out_for_delivery'=>'Out for Delivery','delivered'=>'Delivered','returned'=>'Returned','cancelled'=>'Cancelled'];
$cls    = ['pending'=>'pending','picked_up'=>'in-transit','in_transit'=>'in-transit','at_hub'=>'in-transit','out_for_delivery'=>'out-delivery','delivered'=>'delivered','returned'=>'pending','cancelled'=>'pending'];
$id = sanitize($_GET['id'] ?? '');
if (!$id) { echo json_encode(['success'=>false,'message'=>'Tracking ID required']); exit; }
try {
  $db = Database::getConnection();
  $stmt = $db->prepare("SELECT p.*, s.name AS sender_hub_name, r.name AS receiver_hub_name
                        FROM parcels p
                        JOIN hubs s ON s.id=p.sender_hub_id
                        JOIN hubs r ON r.id=p.receiver_hub_id
                        WHERE p.tracking_id=?");
  $stmt->execute([$id]);
  $p = $stmt->fetch();
  if (!$p) { echo json_encode(['success'=>false,'message'=>'No parcel found for this tracking ID']); exit; }
  $p['status_label'] = $labels[$p['status']] ?? $p['status'];
  $p['status_class'] = $cls[$p['status']] ?? 'pending';
  $tr = $db->prepare("SELECT t.status, t.location, t.remarks, t.created_at, h.name AS hub_name FROM parcel_tracking t LEFT JOIN hubs h ON h.id=t.hub_id WHERE t.parcel_id=? ORDER BY t.created_at ASC");
  $tr->execute([$p['id']]);
  $tracking = $tr->fetchAll();
  foreach ($tracking as &$t) { $t['created_at'] = date('d M Y, h:i A', strtotime($t['created_at'])); }
  echo json_encode(['success'=>true,'parcel'=>$p,'tracking'=>$tracking]);
} catch (Exception $e) { echo json_encode(['success'=>false,'message'=>'Server error']); }
