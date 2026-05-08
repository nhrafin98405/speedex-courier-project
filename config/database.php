<?php
/**
 * nhr Courier - Database & Core Helpers
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'speedex_courier');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

class Database {
    private static ?PDO $instance = null;
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                error_log("DB Error: " . $e->getMessage());
                die("Database connection failed.");
            }
        }
        return self::$instance;
    }
}

function generateCSRFToken(): string {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function validateCSRFToken(string $token): bool {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
function sanitize($input): string {
    return htmlspecialchars(trim((string)$input), ENT_QUOTES, 'UTF-8');
}
function generateTrackingId(): string {
    return 'SPX' . str_pad((string)mt_rand(1, 99999999999), 11, '0', STR_PAD_LEFT);
}
function generateBookingId(): string {
    return 'BKG' . date('Ymd') . str_pad((string)mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
}
function generatePaymentId(): string {
    return 'PAY' . date('Ymd') . str_pad((string)mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

function logActivity(?int $userId, string $action, string $desc = ''): void {
    try {
        $db = Database::getConnection();
        $db->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?,?,?,?)")
           ->execute([$userId, $action, $desc, $_SERVER['REMOTE_ADDR'] ?? null]);
    } catch (Exception $e) {}
}

function pushNotification(?int $userId, string $type, string $title, string $message, ?string $link = null): void {
    try {
        $db = Database::getConnection();
        $db->prepare("INSERT INTO notifications (user_id, type, title, message, link) VALUES (?,?,?,?,?)")
           ->execute([$userId, $type, $title, $message, $link]);
    } catch (Exception $e) {}
}

define('BASE_URL', '/speedex-courier');
define('SITE_NAME', 'nhr Courier Service');
