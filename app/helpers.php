<?php
// includes/helpers.php

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

    $files = glob($cache_dir . 'home_*.html');
    if ($files) {
        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }
}

