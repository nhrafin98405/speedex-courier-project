<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
try {
    $db = Database::getConnection();
    $row = $db->query("SELECT
        COUNT(*) total,
        SUM(status='in_transit') in_transit,
        SUM(status='delivered')  delivered,
        SUM(status='pending')    pending,
        COALESCE(SUM(CASE WHEN status='delivered' THEN total_amount ELSE 0 END),0) revenue
        FROM parcels")->fetch();
    echo json_encode([
        'total'      => (int)($row['total']??0),
        'in_transit' => (int)($row['in_transit']??0),
        'delivered'  => (int)($row['delivered']??0),
        'pending'    => (int)($row['pending']??0),
        'revenue'    => (float)($row['revenue']??0),
        'ts'         => time(),
    ]);
} catch (Exception $e) { echo json_encode(['error'=>'failed']); }
