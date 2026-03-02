<?php
// public/index.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';

$db = DB::getInstance()->getConnection();

// Clean all caches when page loads
clear_cache();
check_maintenance();

// Handle Filters
$category_slug = $_GET['category'] ?? 'all';
$search_query = trim($_GET['q'] ?? '');
$params = [];
$cat_condition = "";

if ($category_slug !== 'all') {
    // Find the category to see if it has children
    $tgt = $db->prepare("SELECT id FROM categories WHERE slug = ?");
    $tgt->execute([$category_slug]);
    if ($tgt_id = $tgt->fetchColumn()) {
        $cat_condition = " AND (c.id = ? OR c.parent_id = ?)";
        $params[] = $tgt_id;
        $params[] = $tgt_id;
    } else {
        $cat_condition = " AND c.slug = ?";
        $params[] = $category_slug;
    }
}

if ($search_query !== '') {
    $cat_condition .= " AND (a.title LIKE ? OR a.summary LIKE ? OR a.content LIKE ?)";
    $params[] = '%' . $search_query . '%';
    $params[] = '%' . $search_query . '%';
    $params[] = '%' . $search_query . '%';
}

// Fetch basic settings
$settings_file = BASE_PATH . '/config/settings.json';
$settings = ['site_title' => '60-Second News'];
if (file_exists($settings_file))
    $settings = array_merge($settings, json_decode(file_get_contents($settings_file), true));

// Fetch Featured Article (Hero Section)
$top_story_sql = "
    SELECT a.*, c.name as category_name, c.slug as category_slug, u.username as author_name, m.filename, m.folder,
           SUM(v.view_count) as total_views
    FROM articles a
    JOIN categories c ON a.category_id = c.id
    JOIN users u ON a.author_id = u.id
    LEFT JOIN media m ON a.featured_image_id = m.id
    LEFT JOIN views v ON a.id = v.article_id
    WHERE a.status = 'published' AND a.is_pinned = 1 AND a.publish_at <= NOW()
    " . $cat_condition . "
    GROUP BY a.id, c.name, c.slug, u.username, m.filename, m.folder
    ORDER BY a.publish_at DESC
    LIMIT 1
";
$stmt = $db->prepare($top_story_sql);
$stmt->execute($params);
$top_story = $stmt->fetch();

// If no featured article exists, fallback to the latest breaking or pinned
if (!$top_story) {
    $fallback_sql = "
        SELECT a.*, c.name as category_name, c.slug as category_slug, u.username as author_name, m.filename, m.folder,
               SUM(v.view_count) as total_views
        FROM articles a
        JOIN categories c ON a.category_id = c.id
        JOIN users u ON a.author_id = u.id
        LEFT JOIN media m ON a.featured_image_id = m.id
        LEFT JOIN views v ON a.id = v.article_id
        WHERE a.status = 'published' AND a.publish_at <= NOW()
        " . $cat_condition . "
        GROUP BY a.id, c.name, c.slug, u.username, m.filename, m.folder
        ORDER BY a.is_breaking DESC, a.is_pinned DESC, a.publish_at DESC
        LIMIT 1
    ";
    $stmt = $db->prepare($fallback_sql);
    $stmt->execute($params);
    $top_story = $stmt->fetch();
}

// Fetch Top 5 Most Read (All Time)
$trending = $db->query("
    SELECT a.title, a.slug, c.name as category_name, SUM(v.view_count) as total_views
    FROM articles a 
    JOIN categories c ON a.category_id = c.id
    JOIN views v ON a.id = v.article_id
    WHERE a.status = 'published' AND a.publish_at <= NOW()
    GROUP BY a.id 
    ORDER BY total_views DESC, a.publish_at DESC
    LIMIT 5
")->fetchAll();

// Fetch Top 5 Latest News for Sidebar
$sidebar_latest = $db->query("
    SELECT a.title, a.slug, a.publish_at, c.name as category_name, m.filename, m.folder
    FROM articles a 
    JOIN categories c ON a.category_id = c.id
    LEFT JOIN media m ON a.featured_image_id = m.id
    WHERE a.status = 'published' AND a.publish_at <= NOW()
    ORDER BY a.publish_at DESC
    LIMIT 5
")->fetchAll();

// Pagination Setup
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$limit = 6;

if ($top_story) {
    if ($page == 1) {
        $fetch_limit = $limit - 1; // Top story takes 1 slot in the grid
        $offset = 0;
    } else {
        $fetch_limit = $limit;
        // Page 1 took ($limit - 1) items. So skip those, plus any full pages before this one
        $offset = ($limit - 1) + ($page - 2) * $limit;
    }

    // Total count query
    $count_sql = "
        SELECT COUNT(*) FROM articles a 
        JOIN categories c ON a.category_id = c.id 
        WHERE a.status = 'published' AND a.publish_at <= NOW() AND a.id != " . (int) $top_story['id'] . $cat_condition;
    $stmt = $db->prepare($count_sql);
    $stmt->execute($params);
    $total_articles = $stmt->fetchColumn();
    $total_items = $total_articles + 1; // Include top story
} else {
    $fetch_limit = $limit;
    $offset = ($page - 1) * $limit;

    $count_sql = "
        SELECT COUNT(*) FROM articles a 
        JOIN categories c ON a.category_id = c.id 
        WHERE a.status = 'published' AND a.publish_at <= NOW() " . $cat_condition;
    $stmt = $db->prepare($count_sql);
    $stmt->execute($params);
    $total_items = $stmt->fetchColumn();
}
$total_pages = ceil($total_items / $limit);

// Fetch News Feed Grid
$latest_sql = "
    SELECT a.*, c.name as category_name, c.slug as category_slug, u.username as author_name, m.filename, m.folder,
           SUM(v.view_count) as total_views
    FROM articles a
    JOIN categories c ON a.category_id = c.id
    JOIN users u ON a.author_id = u.id
    LEFT JOIN media m ON a.featured_image_id = m.id
    LEFT JOIN views v ON a.id = v.article_id
    WHERE a.status = 'published' AND a.publish_at <= NOW()
    ";
if ($top_story) {
    $latest_sql .= " AND a.id != " . (int) $top_story['id'];
}
$latest_sql .= $cat_condition . " GROUP BY a.id, c.name, c.slug, u.username, m.filename, m.folder ORDER BY a.publish_at DESC LIMIT " . $fetch_limit . " OFFSET " . $offset;

$stmt = $db->prepare($latest_sql);
$stmt->execute($params);
$articles = $stmt->fetchAll();

$categories_raw = $db->query("SELECT id, name, slug, parent_id FROM categories ORDER BY parent_id IS NOT NULL, name ASC")->fetchAll();
$category_tree = [];
$children = [];
foreach ($categories_raw as $c) {
    if ($c['parent_id'] === null) {
        $category_tree[$c['id']] = $c;
        $category_tree[$c['id']]['children'] = [];
    } else {
        $children[$c['parent_id']][] = $c;
    }
}
foreach ($children as $pid => $kids) {
    if (isset($category_tree[$pid]))
        $category_tree[$pid]['children'] = $kids;
}

$breaking_news = $db->query("SELECT title,slug,publish_at FROM articles WHERE status='published' AND is_breaking=1 AND publish_at<=NOW() ORDER BY publish_at DESC LIMIT 5")->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= e($settings['site_title']) ?> - News in 60 Seconds
    </title>
    <meta name="description" content="Get the full story in 60 seconds with our condensed, fact-checked news updates.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="<?= BASE_URL ?>/css/style.css?v=<?= filemtime(BASE_PATH . '/public/css/style.css') ?>" rel="stylesheet">
</head>

<body class="theme-<?= e($settings['user_theme'] ?? 'premium') ?>">

    <div class="main-content-wrapper">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light py-3 sticky-top">
            <div class="container">
                <a class="navbar-brand" href="<?= BASE_URL ?>/">
                    60Sec<span>News</span>
                </a>

                <div class="d-flex align-items-center ms-auto">
                    <!-- Desktop Search Bar (beside notification) -->
                    <form action="<?= BASE_URL ?>/" method="GET" class="d-none d-md-block me-3"
                        style="max-width: 300px; width: 250px;">
                        <div class="search-box-premium">
                            <i class="bi bi-search"></i>
                            <input type="text" name="q" value="<?= e($search_query) ?>" placeholder="Search news..."
                                aria-label="Search">
                            <?php if ($category_slug !== 'all'): ?>
                                <input type="hidden" name="category" value="<?= e($category_slug) ?>">
                            <?php endif; ?>
                        </div>
                    </form>

                    <!-- Mobile Search Trigger -->
                    <button class="btn btn-link link-dark d-md-none me-2 p-0 shadow-none border-0" type="button"
                        data-bs-toggle="collapse" data-bs-target="#mobileSearch">
                        <i class="bi bi-search" style="font-size: 1.2rem;"></i>
                    </button>
                    <!-- Breaking News Notification Bell -->
                    <div class="dropdown me-3">
                        <a href="#" class="text-dark position-relative" id="breakingNewsDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 1.2rem;">
                            <i class="bi bi-bell-fill"></i>
                            <?php if (!empty($breaking_news)): ?>
                                <span
                                    class="position-absolute top-10 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                                    <span class="visually-hidden">New breaking news</span>
                                </span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2"
                            aria-labelledby="breakingNewsDropdown"
                            style="width: 320px; max-height: 400px; overflow-y: auto;">
                            <li>
                                <h6 class="dropdown-header text-danger fw-bold"><i class="bi bi-lightning-fill"></i>
                                    Breaking News Alerts</h6>
                            </li>
                            <?php if (!empty($breaking_news)): ?>
                                <?php foreach ($breaking_news as $news): ?>
                                    <li>
                                        <a class="dropdown-item py-2 text-wrap border-bottom"
                                            href="<?= BASE_URL ?>/article.php?slug=<?= e($news['slug']) ?>">
                                            <div class="fw-bold mb-1 lh-sm" style="font-size: 0.9rem;">
                                                <?= e($news['title']) ?>
                                            </div>
                                            <small class="text-muted"><i class="bi bi-clock"></i>
                                                <?= time_ago($news['publish_at']) ?>
                                            </small>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li><span class="dropdown-item-text text-muted text-center py-3">No breaking news right
                                        now.</span></li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <!-- My Library (Consolidated Loved & Saved) -->
                    <a href="<?= BASE_URL ?>/bookmarks.php" class="text-dark me-3 position-relative" title="My Library"
                        style="font-size: 1.2rem;">
                        <i class="bi bi-bookmarks-fill"></i>
                    </a>

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
        <!-- Mobile Search Collapse -->
        <div class="collapse d-md-none bg-white border-bottom" id="mobileSearch">
            <div class="container py-3">
                <form action="<?= BASE_URL ?>/" method="GET">
                    <div class="search-box-premium w-100">
                        <i class="bi bi-search"></i>
                        <input type="text" name="q" value="<?= e($search_query) ?>" placeholder="Search news stories..."
                            class="w-100">
                    </div>
                </form>
            </div>
        </div>

        <?php if (!empty($breaking_news)): ?>
            <!-- Breaking News Ticker -->
            <div class="breaking-ticker py-2 shadow-sm" style="overflow: hidden; white-space: nowrap;">
                <div class="container d-flex align-items-center">
                    <div class="fw-bold pe-3 ps-2 py-1 text-uppercase ticker-box rounded-1 me-3" style="z-index: 2;">
                        <i class="bi bi-broadcast"></i> Breaking
                    </div>
                    <!-- CSS Marquee Animation -->
                    <style>
                        .ticker-wrapper {
                            flex-grow: 1;
                            overflow: hidden;
                            position: relative;
                        }

                        .ticker-content {
                            display: inline-block;
                            white-space: nowrap;
                            animation: ticker 30s linear infinite;
                        }

                        .ticker-content:hover {
                            animation-play-state: paused;
                        }

                        @keyframes ticker {
                            0% {
                                transform: translateX(100%);
                            }

                            100% {
                                transform: translateX(-100%);
                            }
                        }
                    </style>
                    <div class="ticker-wrapper ps-2 border-start border-light border-opacity-50">
                        <div class="ticker-content">
                            <?php foreach ($breaking_news as $news): ?>
                                <a href="<?= BASE_URL ?>/article.php?slug=<?= e($news['slug']) ?>"
                                    class="text-white text-decoration-none me-5 fw-medium">
                                    <span class="me-2">&bull;</span>
                                    <?= e($news['title']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Sticky Filter Bar -->
        <div class="sticky-filters py-3 mb-4 sticky-top border-bottom"
            style="top: 76px; z-index: 1010; background: var(--glass-bg); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);">
            <div class="container">
                <div class="d-flex flex-wrap gap-2 pb-2" style="scrollbar-width: thin; overflow: visible;">
                    <a href="?category=all<?= $search_query ? '&q=' . urlencode($search_query) : '' ?>"
                        class="filter-btn text-decoration-none text-nowrap <?= $category_slug === 'all' ? 'active' : '' ?>">All
                        News</a>
                    <?php foreach ($category_tree as $parent): ?>
                        <?php if (!empty($parent['children'])): ?>
                            <div class="dropdown">
                                <a href="#"
                                    class="filter-btn text-decoration-none text-nowrap <?= $category_slug === $parent['slug'] ? 'active' : '' ?>"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <?= e($parent['name']) ?>
                                </a>
                                <ul class="dropdown-menu shadow border-0" style="border-radius: 12px; z-index: 1050;">
                                    <li>
                                        <a class="dropdown-item fw-bold text-primary bg-primary bg-opacity-10 rounded mx-2 mb-1 py-2 d-flex justify-content-between align-items-center"
                                            style="width: calc(100% - 1rem);"
                                            href="?category=<?= $parent['slug'] ?><?= $search_query ? '&q=' . urlencode($search_query) : '' ?>">
                                            <span><?= e($parent['name']) ?></span>
                                            <i class="bi bi-arrow-right-circle-fill"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                        <?php foreach ($parent['children'] as $child): ?>
                                        <li><a class="dropdown-item"
                                                href="?category=<?= $child['slug'] ?><?= $search_query ? '&q=' . urlencode($search_query) : '' ?>"><?= e($child['name']) ?></a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php else: ?>
                            <a href="?category=<?= $parent['slug'] ?><?= $search_query ? '&q=' . urlencode($search_query) : '' ?>"
                                class="filter-btn text-decoration-none text-nowrap <?= $category_slug === $parent['slug'] ? 'active' : '' ?>">
                                <?= e($parent['name']) ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="container mb-4">
            <?php if ($search_query): ?>
                <div
                    class="d-flex align-items-center justify-content-between bg-white bg-opacity-50 backdrop-blur rounded-4 p-3 border shadow-sm">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3 me-3">
                            <i class="bi bi-search fs-5"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0">Search Results for</p>
                            <h5 class="mb-0 fw-bold">"<?= e($search_query) ?>"</h5>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-dark rounded-pill px-3 py-2 mb-1"><?= $total_items ?> found</span>
                        <br>
                        <a href="?category=<?= e($category_slug) ?>"
                            class="text-muted small text-decoration-none hover-primary">
                            <i class="bi bi-x-circle me-1"></i> Clear search
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="container pb-5">
            <div class="row g-4">

                <!-- Main Content Area -->
                <div class="col-lg-8">

                    <!-- ORIGINAL THEME CONTENT -->
                    <div class="d-flex align-items-center mb-4 pb-2 border-bottom">
                        <h3 class="fw-bold mb-0 text-dark">Featured News</h3>
                    </div>

                    <?php
                    // Prepend the top story to the main articles array so it renders in the grid
                    if ($page == 1 && $top_story && !in_array($top_story, $articles)) {
                        array_unshift($articles, $top_story);
                    }
                    ?>

                    <!-- News Feed Grid -->
                    <div class="row g-4 align-items-stretch mb-4" id="news-grid">
                        <?php foreach ($articles as $article): ?>
                            <div class="col-md-6 col-sm-12">
                                <a href="<?= BASE_URL ?>/article.php?slug=<?= e($article['slug']) ?>"
                                    class="news-card grid-card h-100 d-flex flex-column">
                                    <?php if ($article['filename']): ?>
                                        <div class="card-img-wrapper" style="height: 220px; overflow: hidden;">
                                            <img src="<?= BASE_URL ?>/uploads/<?= e($article['folder']) ?>/<?= e($article['filename']) ?>"
                                                class="card-img-top w-100 h-100 object-fit-cover" loading="lazy"
                                                alt="<?= e($article['title']) ?>">
                                        </div>
                                    <?php endif; ?>
                                    <div class="news-card-body p-4 d-flex flex-column flex-grow-1">
                                        <div class="mb-3 d-flex flex-wrap gap-2">
                                            <span class="badge bg-primary text-uppercase">
                                                <?= e($article['category_name']) ?>
                                            </span>
                                            <span class="badge bg-dark"><i class="bi bi-clock"></i> 60 sec read</span>
                                            <span class="badge bg-light text-dark border"><i class="bi bi-eye"></i>
                                                <?= number_format($article['total_views'] ?: 0) ?>
                                            </span>
                                        </div>
                                        <h4 class="mb-2 fw-bold headline" style="letter-spacing: -0.5px; line-height: 1.3;">
                                            <?= e($article['title']) ?>
                                        </h4>
                                        <p class="text-muted mb-4 small flex-grow-1">
                                            <?= e(mb_strimwidth($article['summary'], 0, 110, '...')) ?>
                                        </p>

                                        <div class="mt-auto px-4 pb-4">
                                            <button class="btn btn-dark w-100 btn-sm rounded-3 py-2 fw-bold"
                                                style="border: none;">
                                                Read in 60 Seconds
                                            </button>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>

                        <?php if (empty($articles) && !$top_story): ?>
                            <div class="col-12 py-5">
                                <div class="glass-panel text-center py-5 shadow-sm rounded-4">
                                    <div
                                        class="bg-primary bg-opacity-10 text-primary p-4 rounded-circle d-inline-flex mb-4">
                                        <i class="bi bi-search display-5"></i>
                                    </div>
                                    <h3 class="fw-bold">No results found</h3>
                                    <p class="text-muted mx-auto" style="max-width: 400px;">We couldn't find any articles
                                        matching your current filters or search query. Try broadening your terms or checking
                                        different categories.</p>
                                    <a href="?category=all"
                                        class="btn btn-outline-primary rounded-pill px-5 mt-3 fw-bold">Reset Filters</a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>


                    <?php if (!empty($articles) && $total_pages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-5 mb-5 d-flex justify-content-center">
                            <ul class="pagination-premium">
                                <li class="page-item-premium <?= $page <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link-premium"
                                        href="?category=<?= urlencode($category_slug) ?>&page=<?= $page - 1 ?><?= $search_query ? '&q=' . urlencode($search_query) : '' ?>"
                                        aria-label="Previous">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>

                                <?php
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);

                                if ($start_page > 1) {
                                    echo '<li class="page-item-premium"><a class="page-link-premium" href="?category=' . urlencode($category_slug) . '&page=1' . ($search_query ? '&q=' . urlencode($search_query) : '') . '">1</a></li>';
                                    if ($start_page > 2) {
                                        echo '<li class="page-item-premium disabled"><span class="page-link-premium">...</span></li>';
                                    }
                                }

                                for ($i = $start_page; $i <= $end_page; $i++):
                                    ?>
                                    <li class="page-item-premium <?= $i == $page ? 'active' : '' ?>">
                                        <a class="page-link-premium"
                                            href="?category=<?= urlencode($category_slug) ?>&page=<?= $i ?><?= $search_query ? '&q=' . urlencode($search_query) : '' ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                    <?php
                                endfor;

                                if ($end_page < $total_pages) {
                                    if ($end_page < $total_pages - 1) {
                                        echo '<li class="page-item-premium disabled"><span class="page-link-premium">...</span></li>';
                                    }
                                    echo '<li class="page-item-premium"><a class="page-link-premium" href="?category=' . urlencode($category_slug) . '&page=' . $total_pages . ($search_query ? '&q=' . urlencode($search_query) : '') . '">' . $total_pages . '</a></li>';
                                }
                                ?>

                                <li class="page-item-premium <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                    <a class="page-link-premium"
                                        href="?category=<?= urlencode($category_slug) ?>&page=<?= $page + 1 ?><?= $search_query ? '&q=' . urlencode($search_query) : '' ?>"
                                        aria-label="Next">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>

                </div>

                <!-- Sidebar -->
                <div class="col-lg-4 ps-lg-4">

                    <!-- Most Read Sidebar -->
                    <div class="position-sticky" style="top: 130px; z-index: 5;">
                        <style>
                            .sidebar-glass-card {
                                background: var(--glass-bg);
                                backdrop-filter: blur(10px);
                                -webkit-backdrop-filter: blur(10px);
                                border: 1px solid var(--glass-border);
                                border-radius: var(--radius-card);
                                overflow: hidden;
                                margin-bottom: 2rem;
                                box-shadow: var(--shadow-soft);
                            }

                            /* Mixture Card Style */
                            .mixture-card .card-header-premium {
                                background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
                                color: white !important;
                                padding: 1.5rem 2rem;
                                border-bottom: none;
                                position: relative;
                                overflow: hidden;
                            }

                            .mixture-card .card-header-premium::after {
                                content: '';
                                position: absolute;
                                top: 0;
                                right: 0;
                                width: 100px;
                                height: 100px;
                                background: radial-gradient(circle, rgba(99, 102, 241, 0.4) 0%, transparent 70%);
                                transform: translate(30%, -30%);
                            }

                            .trending-item {
                                transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
                                border-left: 4px solid transparent !important;
                                margin-bottom: 2px;
                            }

                            .trending-item:hover {
                                background: rgba(99, 102, 241, 0.08) !important;
                                border-left-color: var(--primary) !important;
                                transform: scale(1.02);
                                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
                                z-index: 10;
                            }

                            .trending-item:hover h6 {
                                color: var(--primary) !important;
                            }

                            .rank-number {
                                font-family: var(--font-display);
                                font-size: 2rem;
                                font-weight: 800;
                                color: var(--primary);
                                opacity: 0.2;
                                transition: all 0.3s ease;
                            }

                            .trending-item:hover .rank-number {
                                opacity: 1;
                                transform: scale(1.2);
                            }
                        </style>

                        <!-- Latest News Sidebar -->
                        <div class="sidebar-glass-card mixture-card">
                            <div class="card-header-premium">
                                <h5 class="fw-bolder mb-0 text-white">
                                    <i class="bi bi-lightning-fill me-2" style="color: #fbbf24;"></i> Latest News
                                </h5>
                                <small class="opacity-75 ps-4 ms-1" style="font-size: 0.7rem;">Quick updates from around
                                    the world</small>
                            </div>
                            <div class="list-group list-group-flush py-2 bg-transparent text-start">
                                <?php foreach ($sidebar_latest as $latest): ?>
                                    <a href="<?= BASE_URL ?>/article.php?slug=<?= e($latest['slug']) ?>"
                                        class="list-group-item list-group-item-action border-0 py-3 px-4 d-flex align-items-center trending-item bg-transparent">
                                        <div class="me-3">
                                            <?php if ($latest['filename']): ?>
                                                <img src="<?= BASE_URL ?>/uploads/<?= e($latest['folder']) ?>/<?= e($latest['filename']) ?>"
                                                    class="rounded-3" style="width: 60px; height: 60px; object-fit: cover;"
                                                    loading="lazy" alt="<?= e($latest['title']) ?>">
                                            <?php else: ?>
                                                <div class="bg-light rounded-3 d-flex align-items-center justify-content-center"
                                                    style="width: 60px; height: 60px;">
                                                    <i class="bi bi-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <span class="text-uppercase fw-bold text-primary mb-1 d-block"
                                                style="font-size: 0.65rem;">
                                                <?= e($latest['category_name']) ?>
                                            </span>
                                            <h6 class="fw-bold mb-1 lh-sm text-dark" style="font-size: 0.9rem;">
                                                <?= e($latest['title']) ?>
                                            </h6>
                                            <small class="text-muted" style="font-size: 0.7rem;">
                                                <?= date('M j, Y', strtotime($latest['publish_at'])) ?>
                                            </small>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Most Read Sidebar -->
                        <div class="sidebar-glass-card mixture-card mt-4">
                            <div class="card-header-premium"
                                style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);">
                                <h5 class="fw-bolder mb-0 text-white">
                                    <i class="bi bi-graph-up-arrow me-2" style="color: #60a5fa;"></i> Top Trending News
                                </h5>
                                <small class="opacity-75 ps-4 ms-1" style="font-size: 0.7rem;">Highest engagement and
                                    trending stories</small>
                            </div>
                            <div class="list-group list-group-flush py-2 bg-transparent">
                                <?php foreach ($trending as $index => $trend): ?>
                                    <a href="<?= BASE_URL ?>/article.php?slug=<?= e($trend['slug']) ?>"
                                        class="list-group-item list-group-item-action border-0 py-3 px-4 d-flex align-items-center trending-item bg-transparent">
                                        <div class="me-3">
                                            <span class="rank-number">
                                                <?= $index + 1 ?>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex flex-column mb-1">
                                                <small class="fw-bold text-uppercase pb-1"
                                                    style="font-size: 0.7rem; color: var(--primary);">
                                                    <?= e($trend['category_name']) ?>
                                                </small>
                                                <h6 class="fw-bold mb-1 lh-sm text-dark" style="font-size: 0.95rem;">
                                                    <?= e($trend['title']) ?>
                                                </h6>
                                                <div class="d-flex justify-content-between align-items-center mt-1">
                                                    <small class="text-muted d-flex align-items-center gap-1"
                                                        style="font-size: 0.7rem;">
                                                        <i class="bi bi-eye-fill"></i>
                                                        <?= number_format($trend['total_views']) ?> reads
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>

        <!-- Back to Top Button -->
        <button id="backToTop" class="btn btn-primary rounded-circle shadow-lg"
            style="position: fixed; bottom: 30px; right: 30px; width: 50px; height: 50px; display: none; z-index: 1050; border: 2px solid rgba(255,255,255,0.2); backdrop-filter: blur(5px);">
            <i class="bi bi-arrow-up"></i>
        </button>

        <!-- ORIGINAL THEME FOOTER -->
        <footer class="bg-white py-5 mt-auto mb-0">
            <div class="container text-center text-muted">
                <h4 class="fw-bold text-dark mb-3">60Sec<span class="text-primary">News</span></h4>
                <p class="mb-4 small">Condensing the world's facts into 60 seconds.</p>
                <p class="small">&copy;
                    <?= date('Y') ?> 60SecNews. All rights reserved.
                </p>
            </div>
        </footer>
    </div> <!-- End .main-content-wrapper -->


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/masonry-layout@4.2.2/dist/masonry.pkgd.min.js" async></script>

    <script>
        // Back to Top Logic
        const btt = document.getElementById('backToTop');
        window.onscroll = function () {
            if (document.body.scrollTop > 500 || document.documentElement.scrollTop > 500) {
                btt.style.display = "block";
            } else {
                btt.style.display = "none";
            }
        };
        btt.onclick = function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        };
    </script>
</body>

</html>
<?php

?>