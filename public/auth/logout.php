<?php
// auth/logout.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/helpers.php';

if (isset($_SESSION['user_id'])) {
    $db = DB::getInstance()->getConnection();
    $log = $db->prepare("INSERT INTO audit_logs (user_id, action, ip_address) VALUES (?, 'logout', ?)");
    $log->execute([$_SESSION['user_id'], $_SERVER['REMOTE_ADDR']]);
}

// Unset all of the session variables
$_SESSION = [];

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy the session
session_destroy();

header('Location: ' . BASE_URL . '/index.php');
exit;


