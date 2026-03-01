<?php
// admin/layout/header.php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../app/db.php';
require_once __DIR__ . '/../../../app/helpers.php';
require_once __DIR__ . '/../../../app/rbac.php';

require_login();

// Fetch pending users count for sidebar badge
$sc_db = DB::getInstance()->getConnection();
$pending_users_count = 0;
if (has_permission('manage_users')) {
    $pending_users_count = $sc_db->query("SELECT COUNT(*) FROM users WHERE status = 'inactive'")->fetchColumn();
}

// Load Theme Settings
$settings_file = BASE_PATH . '/config/settings.json';
$app_settings = ['admin_theme' => 'premium', 'user_theme' => 'premium'];
if (file_exists($settings_file)) {
    $loaded = json_decode(file_get_contents($settings_file), true);
    if (is_array($loaded))
        $app_settings = array_merge($app_settings, $loaded);
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>60SecNews - Admin Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;600;800&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.08);
            --sidebar-width: 260px;
            --radius-premium: 16px;
        }

        /* Default Admin Styles (Dark) */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0c0e14;
            color: #e2e8f0;
            min-height: 100vh;
        }

        /* Premium Theme Overrides */
        body.theme-admin-premium {
            background-image:
                radial-gradient(at 0% 0%, rgba(37, 99, 235, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(79, 70, 229, 0.08) 0px, transparent 50%),
                radial-gradient(at 50% 100%, rgba(30, 58, 138, 0.1) 0px, transparent 50%);
            background-attachment: fixed;
        }

        body.theme-admin-premium .sidebar {
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-right: 1px solid var(--glass-border);
        }

        body.theme-admin-premium .card {
            background: rgba(30, 41, 59, 0.4);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
        }

        /* Midnight Admin Theme */
        body.theme-admin-midnight {
            background-color: #020617;
            background-image:
                radial-gradient(at 0% 0%, rgba(139, 92, 246, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(217, 70, 239, 0.1) 0px, transparent 50%);
            color: #f8fafc;
        }

        body.theme-admin-midnight .sidebar {
            background: rgba(2, 6, 23, 0.95);
            border-right: 1px solid rgba(255, 255, 255, 0.05);
        }

        body.theme-admin-midnight .card {
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.6);
        }

        body.theme-admin-midnight .nav-link:hover {
            color: #d946ef !important;
        }

        body.theme-admin-midnight .nav-link.active {
            background: linear-gradient(135deg, #8b5cf6 0%, #d946ef 100%) !important;
        }

        /* Dashboard/General styles */
        h1,
        h2,
        h3,
        h4,
        .fw-bold,
        .sidebar-brand {
            font-family: 'Outfit', sans-serif;
        }

        .sidebar {
            min-height: 100vh;
            background: #1a1a1a;
            border-right: 1px solid #333;
            padding-top: 2rem;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.6) !important;
            padding: 0.8rem 1.2rem !important;
            border-radius: 12px !important;
            margin-bottom: 8px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 500;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.05);
            color: #fff !important;
            transform: translateX(4px);
        }

        .nav-link.active {
            background: var(--primary-gradient) !important;
            color: #fff !important;
            box-shadow: 0 8px 20px -6px rgba(37, 99, 235, 0.4);
        }

        .nav-link i {
            font-size: 1.1rem;
            margin-right: 12px;
            opacity: 0.8;
        }

        .main-content {
            padding: 2rem;
        }

        .card {
            background: rgba(30, 41, 59, 0.4);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-premium);
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .card-header {
            background: rgba(15, 23, 42, 0.2);
            border-bottom: 1px solid var(--glass-border);
            padding: 1.25rem;
            font-weight: 700;
        }

        .table {
            color: #cbd5e1;
            margin-bottom: 0;
        }

        .table thead th {
            background: rgba(15, 23, 42, 0.2);
            border-bottom: 1px solid var(--glass-border);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            color: rgba(255, 255, 255, 0.5);
            padding: 1rem;
        }

        .table td {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding: 1rem;
            vertical-align: middle;
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            box-shadow: 0 4px 14px 0 rgba(37, 99, 235, 0.39);
            border-radius: 10px;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.45);
            background: linear-gradient(135deg, #2563eb 0%, #1e3a8a 100%);
        }

        .badge-premium {
            background: var(--primary-gradient);
            border-radius: 6px;
            padding: 0.4em 0.8em;
        }
    </style>
</head>

<body class="theme-admin-<?= e($app_settings['admin_theme']) ?>">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse px-3">
                <a href="<?= ADMIN_URL ?>/index.php"
                    class="d-flex align-items-center mb-4 text-white text-decoration-none">
                    <span class="fs-4 fw-bold">60Sec<span class="text-primary">News</span></span>
                </a>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>"
                            href="<?= ADMIN_URL ?>/index.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <?php if (has_permission('manage_settings')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'themes.php' ? 'active' : '' ?>"
                                href="<?= ADMIN_URL ?>/themes.php">
                                <i class="bi bi-palette"></i> Appearance
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (has_permission('create_article') || has_permission('edit_any_article')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'articles.php' ? 'active' : '' ?>"
                                href="<?= ADMIN_URL ?>/articles.php">
                                <i class="bi bi-file-earmark-text"></i> Articles
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (has_permission('upload_media')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'media.php' ? 'active' : '' ?>"
                                href="<?= ADMIN_URL ?>/media.php">
                                <i class="bi bi-image"></i> Media
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (has_permission('manage_categories')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : '' ?>"
                                href="<?= ADMIN_URL ?>/categories.php">
                                <i class="bi bi-tags"></i> Categories
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (has_permission('manage_users')): ?>
                        <li class="nav-item">
                            <a class="nav-link d-flex justify-content-between align-items-center <?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : '' ?>"
                                href="<?= ADMIN_URL ?>/users.php">
                                <span><i class="bi bi-people"></i> Users & Roles</span>
                                <?php if ($pending_users_count > 0): ?>
                                    <span class="badge bg-warning text-dark rounded-pill"><?= $pending_users_count ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (has_permission('view_logs')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'logs.php' ? 'active' : '' ?>"
                                href="<?= ADMIN_URL ?>/logs.php">
                                <i class="bi bi-journal-text"></i> Audit Logs
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (has_permission('manage_settings')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>"
                                href="<?= ADMIN_URL ?>/settings.php">
                                <i class="bi bi-gear"></i> Settings
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (has_permission('manage_settings')): ?>
                        <li class="nav-item mt-3">
                            <form action="<?= ADMIN_URL ?>/clear_cache.php" method="POST"
                                onsubmit="return confirm('Are you sure you want to clear the frontend cache?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="nav-link w-100 text-start border-0 bg-transparent text-danger">
                                    <i class="bi bi-trash text-danger"></i> Clear Cache
                                </button>
                            </form>
                        </li>
                    <?php endif; ?>
                </ul>

                <hr class="border-secondary mt-5">
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle fs-4 me-2"></i>
                        <strong>
                            <?= e($_SESSION['username']) ?>
                        </strong>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/" target="_blank">View Site</a></li>
                        <li><a class="dropdown-item" href="<?= AUTH_URL ?>/logout.php">Sign out</a></li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content Area -->
            <main class="col-md-9 ms-sm-auto col-lg-10 main-content">
                <?= display_flash_message(); ?>