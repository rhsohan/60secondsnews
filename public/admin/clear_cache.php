<?php
// admin/clear_cache.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/helpers.php';
require_once __DIR__ . '/../../app/rbac.php';

require_login();
require_permission('manage_settings');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    clear_cache();
    log_activity("Manually cleared frontend cache", 'system');
    set_flash_message('success', 'Cache cleared successfully.');
}

header('Location: ' . ADMIN_URL . '/index.php');
exit;


