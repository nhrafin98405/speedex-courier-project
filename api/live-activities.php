<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
try {
  $db = Database::getConnection();
  $rows = $db->query("SELECT a.*, u.full_name FROM activity_logs a LEFT JOIN users u ON u.id=a.user_id ORDER BY a.created_at DESC LIMIT 15")->fetchAll();
  echo json_encode(['items'=>$rows]);
} catch (Exception $e) { echo json_encode(['items'=>[]]); }
