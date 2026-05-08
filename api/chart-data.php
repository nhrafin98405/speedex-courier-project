<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
try {
  $db = Database::getConnection();
  // Last 6 months
  $monthlyParcels = []; $monthlyRevenue = []; $labels = [];
  for ($i = 5; $i >= 0; $i--) {
    $m = date('Y-m', strtotime("-$i month"));
    $labels[] = date('M', strtotime("-$i month"));
    $r = $db->prepare("SELECT COUNT(*) c, COALESCE(SUM(CASE WHEN status='delivered' THEN total_amount ELSE 0 END),0) rev FROM parcels WHERE DATE_FORMAT(created_at,'%Y-%m')=?");
    $r->execute([$m]); $row = $r->fetch();
    $monthlyParcels[] = (int)$row['c'];
    $monthlyRevenue[] = (float)$row['rev'];
  }
  $ov = $db->query("SELECT
        SUM(status='delivered')  delivered,
        SUM(status IN('in_transit','picked_up','at_hub','out_for_delivery')) in_transit,
        SUM(status='pending') pending FROM parcels")->fetch();
  $routes = $db->query("SELECT CONCAT(s.name,' → ',r.name) route, COUNT(*) c
        FROM parcels p JOIN hubs s ON s.id=p.sender_hub_id JOIN hubs r ON r.id=p.receiver_hub_id
        GROUP BY route ORDER BY c DESC LIMIT 5")->fetchAll();
  echo json_encode([
    'monthly'  => ['labels'=>$labels,'parcels'=>$monthlyParcels,'revenue'=>$monthlyRevenue],
    'overview' => ['delivered'=>(int)$ov['delivered'],'in_transit'=>(int)$ov['in_transit'],'pending'=>(int)$ov['pending']],
    'routes'   => ['labels'=>array_column($routes,'route'),'values'=>array_map('intval',array_column($routes,'c'))],
  ]);
} catch (Exception $e) { echo json_encode(['error'=>'failed']); }
