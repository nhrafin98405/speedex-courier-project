<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$uid = $_SESSION['user_id'] ?? null;
try {
  $db = Database::getConnection();
  if ($uid) {
    $rows = $db->prepare("SELECT * FROM notifications WHERE user_id=? OR user_id IS NULL ORDER BY created_at DESC LIMIT 15");
    $rows->execute([$uid]);
    $items = $rows->fetchAll();
    $unread = (int)$db->query("SELECT COUNT(*) FROM notifications WHERE (user_id={$uid} OR user_id IS NULL) AND is_read=0")->fetchColumn();
  } else {
    $items = $db->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 15")->fetchAll();
    $unread = (int)$db->query("SELECT COUNT(*) FROM notifications WHERE is_read=0")->fetchColumn();
  }
  echo json_encode(['items'=>$items,'unread'=>$unread]);
} catch (Exception $e) { echo json_encode(['items'=>[],'unread'=>0]); }
