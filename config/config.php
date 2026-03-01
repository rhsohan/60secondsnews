<?php
// config/config.php
session_start();

// Environment & Paths
// Dynamically detect the base URL to support both root (localhost:8000) and subdirectories (localhost/60secnews)
$script_name = $_SERVER['SCRIPT_NAME'] ?? '';
$base_dir = str_replace('\\', '/', dirname(dirname($script_name)));
$base_url = rtrim($base_dir, '/');
define('BASE_URL', $base_url);
define('ADMIN_URL', BASE_URL . '/admin');
define('AUTH_URL', BASE_URL . '/auth');
define('BASE_PATH', dirname(__DIR__));
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
