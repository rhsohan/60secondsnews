<?php
// config/config.php
session_start();

// Environment & Paths
// Dynamically detect the base URL to support both root (localhost:8000) and subdirectories (localhost/60secnews)
define('BASE_PATH', dirname(__DIR__));
$doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? '');
$base_path = str_replace('\\', '/', BASE_PATH);
$base_url = str_replace($doc_root, '', $base_path);

// Fallback for command line or weird server setups
if ($_SERVER['DOCUMENT_ROOT'] === '' || $base_url === $base_path) {
    // If str_replace didn't find the doc_root (e.g. symlinks or case mismatch)
    // Try to guess from SCRIPT_NAME if available
    $script_path = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    // If script is in public/ or public/admin, remove that part
    $base_url = preg_replace('/(\/public(\/admin)?)?$/', '', $script_path);
}

define('BASE_URL', rtrim($base_url, '/'));
define('ADMIN_URL', BASE_URL . '/admin');
define('AUTH_URL', BASE_URL . '/auth');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('CACHE_PATH', STORAGE_PATH . '/cache');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', '60secnews');

// Application Settings
define('MAX_WORD_COUNT', 150);
define('TIMEZONE', 'Asia/Almaty'); // Based on +06:00 offset

date_default_timezone_set(TIMEZONE);
