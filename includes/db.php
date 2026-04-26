<?php
// includes/db.php — PDO Singleton Connection

declare(strict_types=1);

// ─── Base URL (auto-detects subfolder on any server) ─────────────────────────
// Works for: http://localhost/marinduque_shop/   OR   http://localhost/
if (!defined('BASE_URL')) {
    $scriptDir  = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    // Walk up if we're inside /admin/ so links still point to root
    // We always define BASE_URL as the shop root (where index.php lives).
    // includes/ and admin/ each go one level deeper, so we detect depth:
    $depth = substr_count(trim($scriptDir, '/'), '/');
    $root  = $scriptDir;
    // If script is in admin/ subdir, go one level up
    if (basename($root) === 'admin') {
        $root = dirname($root);
    }
    $root = rtrim($root, '/');
    define('BASE_URL', $root . '/');
}


class Database {
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $host   = 'localhost';
            $dbname = 'isla_finds';
            $user   = 'root';       // Change to your MySQL username
            $pass   = '';           // Change to your MySQL password
            $charset = 'utf8mb4';

            $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                // In production, log this instead of echoing
                error_log('DB Connection failed: ' . $e->getMessage());
                die(json_encode(['error' => 'Database connection failed. Please try again later.']));
            }
        }
        return self::$instance;
    }
}

// Convenience alias used throughout the app
function db(): PDO {
    return Database::getInstance();
}