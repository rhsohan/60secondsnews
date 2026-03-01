<?php
// public/bookmarks.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';

$db = DB::getInstance()->getConnection();
$session_id = session_id();
$type_filter = $_GET['type'] ?? '';

// Fetch liked/saved articles
$sql = "
    SELECT a.*, b.type as interaction_type, c.name as category_name, m.filename, m.folder
    FROM articles a
    JOIN bookmarks b ON a.id = b.article_id
    JOIN categories c ON a.category_id = c.id
    LEFT JOIN media m ON a.featured_image_id = m.id
    WHERE b.session_id = ?
";

if (in_array($type_filter, ['like', 'bookmark'])) {
    $sql .= " AND b.type = ? ";
    $params = [$session_id, $type_filter];
} else {
    $params = [$session_id];
}

$sql .= " ORDER BY b.created_at DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$interacted_articles = $stmt->fetchAll();

// Settings
$settings_file = BASE_PATH . '/config/settings.json';
$settings = ['site_title' => '60-Second News'];
if (file_exists($settings_file))
    $settings = array_merge($settings, json_decode(file_get_contents($settings_file), true));

$breaking_news = $db->query("SELECT title,slug,publish_at FROM articles WHERE status='published' AND is_breaking=1 AND publish_at<=NOW() ORDER BY publish_at DESC LIMIT 5")->fetchAll();
$search_query = ""; // for header compatibility
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Collection -
        <?= e($settings['site_title']) ?>
    </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="<?= BASE_URL ?>/css/style.css?v=<?= filemtime(BASE_PATH . '/public/css/style.css') ?>" rel="stylesheet">
</head>

<body class="theme-<?= e($settings['user_theme'] ?? 'premium') ?>">

    <div class="main-content-wrapper">
        <!-- Navbar (Consistent with index.php) -->
        <nav class="navbar navbar-expand-lg navbar-light py-3 sticky-top">
            <div class="container">
                <a class="navbar-brand" href="<?= BASE_URL ?>/">60Sec<span>News</span></a>
                <div class="d-flex align-items-center ms-auto">
                    <a href="<?= BASE_URL ?>/" class="btn btn-link link-dark text-decoration-none me-3 fw-bold"><i
                            class="bi bi-house-door me-1"></i> Home</a>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="dropdown">
                            <button class="btn btn-dark btn-sm rounded-pill px-3 dropdown-toggle shadow-sm" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle me-1"></i> <?= e($_SESSION['username'] ?? 'Account') ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                                <li><a class="dropdown-item fw-bold" href="<?= ADMIN_URL ?>/index.php"><i
                                            class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-danger fw-bold" href="<?= AUTH_URL ?>/logout.php"><i
                                            class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="<?= AUTH_URL ?>/login.php"
                            class="btn btn-outline-dark btn-sm rounded-pill fw-bold px-3 shadow-sm">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>

        <div class="container py-5 mt-4">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h1 class="display-5 fw-800 mb-0">My <span class="text-primary">Collection</span></h1>
                            <p class="text-muted lead mt-2">Articles you've curated for your personal feed.</p>
                        </div>
                        <div class="d-flex gap-2 d-none d-md-flex">
                            <div
                                class="bg-danger bg-opacity-10 text-danger p-3 rounded-4 border border-danger border-opacity-20">
                                <i class="bi bi-heart-fill fs-4"></i>
                            </div>
                            <div
                                class="bg-dark bg-opacity-10 text-dark p-3 rounded-4 border border-dark border-opacity-20">
                                <i class="bi bi-bookmark-fill fs-4"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Collection Tabs -->
                    <div class="d-flex gap-2 mb-5 overflow-auto pb-2" style="scrollbar-width: none;">
                        <a href="bookmarks.php"
                            class="btn rounded-pill px-4 fw-bold <?= $type_filter === '' ? 'btn-primary' : 'btn-outline-primary' ?>">
                            <i class="bi bi-grid-fill me-1"></i> All
                        </a>
                        <a href="bookmarks.php?type=like"
                            class="btn rounded-pill px-4 fw-bold <?= $type_filter === 'like' ? 'btn-danger' : 'btn-outline-danger' ?>">
                            <i class="bi bi-heart-fill me-1"></i> Loved
                        </a>
                        <a href="bookmarks.php?type=bookmark"
                            class="btn rounded-pill px-4 fw-bold <?= $type_filter === 'bookmark' ? 'btn-dark' : 'btn-outline-dark' ?>">
                            <i class="bi bi-bookmark-fill me-1"></i> Saved
                        </a>
                    </div>

                    <?php if (empty($interacted_articles)): ?>
                        <div class="glass-panel text-center py-5 shadow-sm rounded-4">
                            <i class="bi bi-journal-x display-1 text-muted opacity-25 mb-4"></i>
                            <h3 class="fw-bold text-muted">Your collection is empty</h3>
                            <p class="text-muted">Like or Save articles from the news feed to see them here.</p>
                            <a href="<?= BASE_URL ?>/" class="btn btn-primary rounded-pill px-5 mt-3 fw-bold">Explore
                                News</a>
                        </div>
                    <?php else: ?>
                        <div class="row g-4">
                            <?php foreach ($interacted_articles as $article): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="news-card grid-card h-100 d-flex flex-column position-relative">
                                        <!-- Interaction Badge -->
                                        <div class="position-absolute top-0 end-0 m-3 z-3">
                                            <?php if ($article['interaction_type'] === 'like'): ?>
                                                <span class="badge bg-danger rounded-pill shadow-sm"><i
                                                        class="bi bi-heart-fill"></i> Liked</span>
                                            <?php else: ?>
                                                <span class="badge bg-dark rounded-pill shadow-sm"><i
                                                        class="bi bi-bookmark-fill"></i> Saved</span>
                                            <?php endif; ?>
                                        </div>

                                        <a href="<?= BASE_URL ?>/article.php?slug=<?= e($article['slug']) ?>"
                                            class="text-decoration-none text-dark h-100 d-flex flex-column">
                                            <?php if ($article['filename']): ?>
                                                <div class="card-img-wrapper">
                                                    <img src="<?= BASE_URL ?>/uploads/<?= e($article['folder']) ?>/<?= e($article['filename']) ?>"
                                                        class="card-img-top w-100 h-100 object-fit-cover"
                                                        alt="<?= e($article['title']) ?>">
                                                </div>
                                            <?php endif; ?>
                                            <div class="news-card-body p-4 d-flex flex-column flex-grow-1">
                                                <div class="mb-2">
                                                    <span
                                                        class="badge bg-primary bg-opacity-10 text-primary text-uppercase px-2 py-1"
                                                        style="font-size: 0.65rem;">
                                                        <?= e($article['category_name']) ?>
                                                    </span>
                                                </div>
                                                <h5 class="fw-bold mb-2 headline">
                                                    <?= e($article['title']) ?>
                                                </h5>
                                                <p class="text-muted small flex-grow-1 mb-0">
                                                    <?= e(mb_strimwidth($article['summary'], 0, 80, '...')) ?>
                                                </p>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <footer class="py-5 mt-auto">
            <div class="container text-center text-muted">
                <p class="small">&copy;
                    <?= date('Y') ?> 60SecNews. All rights reserved.
                </p>
            </div>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>