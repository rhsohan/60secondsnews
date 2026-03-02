<?php
// includes/helpers.php

/**
 * Check if maintenance mode is active
 */
function check_maintenance()
{
    $settings_file = BASE_PATH . '/config/settings.json';
    if (file_exists($settings_file)) {
        $settings = json_decode(file_get_contents($settings_file), true);
        if (!empty($settings['maintenance_mode']) && empty($_SESSION['user_id'])) {
            header('HTTP/1.1 503 Service Temporarily Unavailable');
            header('Status: 503 Service Temporarily Unavailable');
            header('Retry-After: 3600');

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' || strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) {
                header('Content-Type: application/json');
                die(json_encode(['success' => false, 'message' => 'Site is currently in maintenance mode.']));
            }

            $login_url = defined('AUTH_URL') ? AUTH_URL . '/login.php' : '/auth/login.php';
            $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Maintenance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; font-family: system-ui, -apple-system, sans-serif; }
        .maintenance-card { background: white; padding: 3rem; border-radius: 1rem; box-shadow: 0 10px 30px rgba(0,0,0,0.05); text-align: center; max-width: 500px; width: 90%; }
        .icon { font-size: 4rem; color: #0d6efd; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="maintenance-card">
        <div class="icon"><i class="bi bi-gear-wide-connected"></i></div>
        <h1 class="mb-3 fw-bold">We\'ll be right back!</h1>
        <p class="text-muted mb-4 pb-2">Our newsroom is currently updating to bring you a better experience. We apologize for the inconvenience.</p>
        <a href="' . $login_url . '" class="btn btn-outline-dark rounded-pill px-4 fw-bold"><i class="bi bi-box-arrow-in-right me-2"></i>Admin Login</a>
    </div>
</body>
</html>';
            die($html);
        }
    }
}

/**
 * Sanitize output for HTML
 */
function e($string)
{
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Generate a CSRF token
 */
function generate_csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify a CSRF token
 */
function verify_csrf_token($token)
{
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        die('CSRF token validation failed.');
    }
}

/**
 * Generate CSRF hidden input field
 */
function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' . e(generate_csrf_token()) . '">';
}

/**
 * Validate word count (max 150)
 */
function validate_word_count($text, $max = MAX_WORD_COUNT)
{
    $clean_text = strip_tags($text);
    $word_count = str_word_count($clean_text);
    return $word_count <= $max;
}

/**
 * Flash messages
 */
function set_flash_message($type, $message)
{
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'danger', 'info', 'warning'
        'message' => $message
    ];
}

function display_flash_message()
{
    if (isset($_SESSION['flash'])) {
        $type = $_SESSION['flash']['type'];
        $message = $_SESSION['flash']['message'];
        unset($_SESSION['flash']);
        return '<div class="alert alert-' . e($type) . ' alert-dismissible fade show" role="alert">'
            . e($message) .
            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    }
    return '';
}

/**
 * Time ago formatting
 */
function time_ago($datetime, $full = false)
{
    if (!$datetime)
        return 'unknown time';

    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $weeks = floor($diff->d / 7);
    $days = $diff->d - ($weeks * 7);

    $string = [
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    ];

    $values = [
        'y' => $diff->y,
        'm' => $diff->m,
        'w' => $weeks,
        'd' => $days,
        'h' => $diff->h,
        'i' => $diff->i,
        's' => $diff->s,
    ];

    foreach ($string as $k => &$v) {
        if ($values[$k]) {
            $v = $values[$k] . ' ' . $v . ($values[$k] > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full)
        $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

/**
 * Automatically clear front-end cache files
 */
function clear_cache()
{
    $cache_dir = CACHE_PATH . '/';
    if (!is_dir($cache_dir)) {
        return;
    }

    // Clear homepage and article caches
    $patterns = ['home_*.html', 'article_*.html'];
    foreach ($patterns as $pattern) {
        $files = glob($cache_dir . $pattern);
        if ($files) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
    }
}

