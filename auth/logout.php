<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (isset($_SESSION['user_id'])) {
    try {
        $db = Database::getConnection();
        $db->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, 'logout', 'User logged out', ?)")
           ->execute([$_SESSION['user_id'], $_SERVER['REMOTE_ADDR']]);
    } catch (Exception $e) {}
}

session_destroy();
header("Location: " . BASE_URL . "/auth/login.php");
exit;
