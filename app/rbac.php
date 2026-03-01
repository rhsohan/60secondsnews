<?php
// includes/rbac.php
require_once __DIR__ . '/db.php';

/**
 * Check if the user is logged in
 */
function is_logged_in()
{
    return isset($_SESSION['user_id']) && isset($_SESSION['role_id']);
}

/**
 * Require login (redirects if not logged in)
 */
function require_login()
{
    if (!is_logged_in()) {
        $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
}

/**
 * Check if the current user has a specific permission
 */
function has_permission($permission)
{
    if (!is_logged_in()) {
        return false;
    }

    $role_id = $_SESSION['role_id'] ?? 0;

    // Admins (role_id 1) have all permissions implicitly or configured in the DB
    if ($role_id == 1) {
        return true;
    }

    $db = DB::getInstance()->getConnection();
    $stmt = $db->prepare("
        SELECT 1 
        FROM role_permissions 
        WHERE role_id = ? AND permission_string = ?
    ");
    $stmt->execute([$role_id, $permission]);
    return $stmt->fetchColumn() !== false;
}

/**
 * Require a specific permission (redirects if they don't have it)
 */
function require_permission($permission)
{
    require_login();
    if (!has_permission($permission)) {
        set_flash_message('danger', 'You do not have permission to perform this action.');
        header('Location: ' . ADMIN_URL . '/index.php');
        exit;
    }
}

/**
 * Log user activity for audit
 */
function log_activity($action, $table_name = null, $record_id = null)
{
    if (!isset($_SESSION['user_id']))
        return;

    $db = DB::getInstance()->getConnection();
    $stmt = $db->prepare("
        INSERT INTO audit_logs (user_id, action, table_name, record_id, ip_address) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $_SESSION['user_id'],
        $action,
        $table_name,
        $record_id,
        $_SERVER['REMOTE_ADDR']
    ]);
}
