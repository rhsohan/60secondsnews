<?php
// public/article.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';

$slug = $_GET['slug'] ?? '';
if (!$slug) {
    header('Location: index.php');
    exit;
}

$db = DB::getInstance()->getConnection();

// Clean all caches when page loads
clear_cache();
check_maintenance();

$stmt = $db->prepare("
    SELECT a.*, c.name as category_name, c.slug as category_slug, u.username as author_name, r.name as role_name, m.filename, m.folder
    FROM articles a
    JOIN categories c ON a.category_id = c.id
    JOIN users u ON a.author_id = u.id
    JOIN roles r ON u.role_id = r.id
    LEFT JOIN media m ON a.featured_image_id = m.id
    WHERE a.slug = ? AND a.status = 'published' AND a.publish_at <= NOW()
");
$stmt->execute([$slug]);
$article = $stmt->fetch();

if (!$article) {
    die('Article not found or not published.');
}


$settings_file = BASE_PATH . '/config/settings.json';
$settings = ['site_title' => '60-Second News', 'require_account_comments' => false, 'auto_approve_comments' => true];
if (file_exists($settings_file))
    $settings = array_merge($settings, json_decode(file_get_contents($settings_file), true));

// Track View
$today = date('Y-m-d');
$db->prepare("INSERT INTO views (article_id, view_date, view_count) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE view_count = view_count + 1")->execute([$article['id'], $today]);


$comment_error = '';
$comment_success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $content = trim($_POST['comment']);

    if (empty($content)) {
        $comment_error = "Comment cannot be empty.";
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
        $author_name = trim($_POST['author_name'] ?? '');
        $status = ($settings['auto_approve_comments'] ?? true) ? 'approved' : 'pending';
        $db->prepare("INSERT INTO comments (article_id, user_ip, author_name, content, status) VALUES (?, ?, ?, ?, ?)")->execute([$article['id'], $ip, $author_name, $content, $status]);
        $comment_success = ($status === 'approved') ? "Your comment has been posted!" : "Your comment has been submitted and is pending moderation.";
    }
}


$comments = $db->prepare("SELECT author_name, content, created_at FROM comments WHERE article_id = ? AND status = 'approved' ORDER BY created_at DESC");
$comments->execute([$article['id']]);
$approved_comments = $comments->fetchAll();

$word_count = str_word_count(strip_tags($article['content']));
// Reading speed avg 250 wpm. At 150 words max, it's always < 1 min, so we output "60 Second Read"

$breaking_news = $db->query("SELECT title,slug,publish_at FROM articles WHERE status='published' AND is_breaking=1 AND publish_at<=NOW() ORDER BY publish_at DESC LIMIT 5")->fetchAll();

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

// Fetch Engagement Status for current session
$session_id = session_id();
$status_stmt = $db->prepare("SELECT type FROM bookmarks WHERE session_id = ? AND article_id = ?");
$status_stmt->execute([$session_id, $article['id']]);
$interactions = $status_stmt->fetchAll(PDO::FETCH_COLUMN);
$is_liked = in_array('like', $interactions);
$is_bookmarked = in_array('bookmark', $interactions);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= e($article['title']) ?> -
        <?= e($settings['site_title']) ?>
    </title>
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?= e($article['summary']) ?>">
    <meta property="og:title" content="<?= e($article['title']) ?>">
    <meta property="og:description" content="<?= e($article['summary']) ?>">
    <?php if ($article['filename']): ?>
        <meta property="og:image"
            content="<?= BASE_URL ?>/uploads/<?= e($article['folder']) ?>/<?= e($article['filename']) ?>">
    <?php endif; ?>
    <meta property="og:type" content="article">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="<?= BASE_URL ?>/css/style.css?v=<?= filemtime(BASE_PATH . '/public/css/style.css') ?>" rel="stylesheet">

    <!-- Schema.org Markup -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "NewsArticle",
      "headline": "<?= e($article['title']) ?>",
      "datePublished": "<?= date('c', strtotime($article['publish_at'])) ?>",
      "author": [{
          "@type": "Person",
          "name": "<?= e($article['author_name']) ?>"
      }]
    }
    </script>
</head>

<body class="theme-<?= e($settings['user_theme'] ?? 'premium') ?>">

    <div id="reading-progress"></div>

    <div class="main-content-wrapper">
        <!-- Upgraded Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light py-3 sticky-top">
            <div class="container">
                <a class="navbar-brand" href="<?= BASE_URL ?>/">
                    60Sec<span>News</span>
                </a>
                <div class="d-flex align-items-center">

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
                    <a href="<?= BASE_URL ?>/?category=all" class="filter-btn text-decoration-none text-nowrap">All
                        News</a>
                    <?php foreach ($category_tree as $parent): ?>
                        <?php if (!empty($parent['children'])): ?>
                            <div class="dropdown">
                                <a href="#"
                                    class="filter-btn text-decoration-none text-nowrap <?= $article['category_slug'] === $parent['slug'] ? 'active' : '' ?>"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <?= e($parent['name']) ?>
                                </a>
                                <ul class="dropdown-menu shadow border-0" style="border-radius: 12px; z-index: 1050;">
                                    <li>
                                        <a class="dropdown-item fw-bold text-primary bg-primary bg-opacity-10 rounded mx-2 mb-1 py-2 d-flex justify-content-between align-items-center"
                                            style="width: calc(100% - 1rem);"
                                            href="<?= BASE_URL ?>/?category=<?= $parent['slug'] ?>">
                                            <span><?= e($parent['name']) ?></span>
                                            <i class="bi bi-arrow-right-circle-fill"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <?php foreach ($parent['children'] as $child): ?>
                                        <li><a class="dropdown-item <?= $article['category_slug'] === $child['slug'] ? 'active bg-primary text-white' : '' ?>"
                                                href="<?= BASE_URL ?>/?category=<?= $child['slug'] ?>"><?= e($child['name']) ?></a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>/?category=<?= $parent['slug'] ?>"
                                class="filter-btn text-decoration-none text-nowrap <?= $article['category_slug'] === $parent['slug'] ? 'active' : '' ?>">
                                <?= e($parent['name']) ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="container py-5">
            <div class="row justify-content-center">

                <div class="col-lg-8 main-article-column">
                    <div class="glass-panel p-4 p-md-5 mt-4 mb-5 shadow-sm">

                        <!-- Article Header -->
                        <header class="mb-5 text-center">
                            <div class="mb-4">
                                <a href="<?= BASE_URL ?>/index.php?category=<?= $article['category_slug'] ?>"
                                    class="badge bg-primary rounded-pill text-uppercase px-3 py-2 text-decoration-none shadow-sm">
                                    <?= e($article['category_name']) ?>
                                </a>
                            </div>

                            <h1 class="display-3 fw-800 mb-4 article-title">
                                <?= e($article['title']) ?>
                            </h1>

                            <p class="lead text-muted fst-italic mx-auto mb-5"
                                style="max-width: 700px; font-size: 1.25rem;">
                                "<?= e($article['summary']) ?>"
                            </p>

                            <div
                                class="d-inline-flex align-items-center gap-4 text-muted small fw-bold text-uppercase p-3 rounded-pill bg-white bg-opacity-50 border border-white border-opacity-30 shadow-sm mb-4">
                                <span><i class="bi bi-person-circle text-primary"></i>
                                    <?= e($article['author_name']) ?> <span
                                        class="bg-primary bg-opacity-10 text-primary px-2 py-1 rounded-pill ms-1"
                                        style="font-size: 0.75rem; vertical-align: middle;"><?= e($article['role_name']) ?></span>
                                </span>
                                <div class="vr"></div>
                                <span><i class="bi bi-calendar3"></i>
                                    <?= date('M j, Y', strtotime($article['publish_at'])) ?></span>
                                <div class="vr"></div>
                                <span class="text-primary"><i class="bi bi-stopwatch"></i> <?= $word_count ?>
                                    WORDS</span>
                                <?php if ($article['fact_checked']): ?>
                                    <div class="vr"></div>
                                    <span class="text-success"><i class="bi bi-shield-check"></i> FACT CHECKED</span>
                                <?php endif; ?>
                            </div>

                            <!-- Reading Utilities -->
                            <div class="d-flex justify-content-center gap-2 mb-4">
                                <button class="btn btn-sm btn-light border-0 rounded-pill px-3 shadow-none"
                                    id="btn-font-dec" title="Decrease font size">
                                    <i class="bi bi-type" style="font-size: 0.8rem;"></i>
                                </button>
                                <button class="btn btn-sm btn-light border-0 rounded-pill px-3 shadow-none"
                                    id="btn-font-inc" title="Increase font size">
                                    <i class="bi bi-type" style="font-size: 1.2rem;"></i>
                                </button>
                                <button class="btn btn-sm btn-light border-0 rounded-pill px-3 shadow-none ms-2"
                                    id="btn-reading-mode" title="Toggle Focused Reading">
                                    <i class="bi bi-eye-fill"></i> Optimized Reading
                                </button>
                            </div>
                        </header>

                        <?php if ($article['filename']): ?>
                            <figure class="mb-5 text-center rounded-4 overflow-hidden shadow-sm">
                                <img src="<?= BASE_URL ?>/uploads/<?= e($article['folder']) ?>/<?= e($article['filename']) ?>"
                                    class="img-fluid w-100 object-fit-cover" style="max-height: 500px;"
                                    alt="<?= e($article['title']) ?>">
                            </figure>
                        <?php endif; ?>

                        <!-- Article Body -->
                        <article class="article-text mb-5" id="article-body">
                            <?= nl2br(e($article['content'])) ?>
                        </article>

                        <hr class="my-5 border-secondary opacity-25">

                        <!-- Engagement Bar -->
                        <div
                            class="d-flex justify-content-between align-items-center mb-5 bg-white bg-opacity-50 border p-3 rounded-4 shadow-sm">
                            <div class="d-flex gap-3">
                                <button onclick="toggleInteraction('like')" id="btn-like"
                                    class="btn rounded-pill px-4 fw-bold interaction-btn <?= $is_liked ? 'btn-danger' : 'btn-outline-danger' ?>">
                                    <i class="bi <?= $is_liked ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                                    <span class="btn-text"><?= $is_liked ? 'Liked' : 'Like' ?></span>
                                </button>
                                <button onclick="toggleInteraction('bookmark')" id="btn-bookmark"
                                    class="btn rounded-pill px-4 fw-bold interaction-btn <?= $is_bookmarked ? 'btn-dark' : 'btn-outline-dark' ?>">
                                    <i class="bi <?= $is_bookmarked ? 'bi-bookmark-fill' : 'bi-bookmark' ?>"></i>
                                    <span class="btn-text"><?= $is_bookmarked ? 'Saved' : 'Save' ?></span>
                                </button>
                            </div>
                            <div class="d-flex gap-2">
                                <button onclick="shareArticle('twitter')"
                                    class="btn btn-light text-primary border rounded-circle shadow-sm"
                                    style="width:44px; height:44px;"><i class="bi bi-twitter-x"></i></button>
                                <button onclick="shareArticle('facebook')"
                                    class="btn btn-light text-primary border rounded-circle shadow-sm"
                                    style="width:44px; height:44px;"><i class="bi bi-facebook"></i></button>
                            </div>
                        </div>

                        <!-- Comments Section -->
                        <section class="comments-section" id="comments">
                            <h4 class="fw-bold mb-4">Join the Conversation</h4>

                            <?php if ($comment_error): ?>
                                <div class="alert alert-danger px-3 py-2">
                                    <?= e($comment_error) ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($comment_success): ?>
                                <div class="alert alert-success px-3 py-2"><i class="bi bi-check-circle"></i>
                                    <?= e($comment_success) ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($settings['require_account_comments'] && !isset($_SESSION['user_id'])): ?>
                                <div class="alert alert-info border-0 bg-light text-dark rounded-4 p-4 text-center">
                                    You must <a href="<?= BASE_URL ?>/auth/login.php"
                                        class="fw-bold text-decoration-none">log
                                        in</a> to post a comment.
                                </div>
                            <?php else: ?>
                                <form method="POST" class="mb-5">
                                    <?= csrf_field() ?>
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <input type="text" name="author_name"
                                                class="form-control rounded-4 border-light shadow-sm"
                                                placeholder="Your Name (Optional)">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <textarea name="comment" class="form-control rounded-4 border-light shadow-sm"
                                            rows="3" placeholder="What are your thoughts on this?" required></textarea>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">Comments are moderated.</small>
                                        <button type="submit" class="btn btn-dark rounded-pill px-4 fw-bold">Post
                                            Comment</button>
                                    </div>
                                </form>
                            <?php endif; ?>

                            <div class="d-flex flex-column gap-4">
                                <?php foreach ($approved_comments as $c): ?>
                                    <div class="bg-white p-4 rounded-4 shadow-sm border border-light">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="fw-bold"><?= e($c['author_name'] ?: 'Anonymous Reader') ?></span>
                                            <small class="text-muted">
                                                <?= time_ago($c['created_at']) ?>
                                            </small>
                                        </div>
                                        <p class="mb-0 text-dark">
                                            <?= nl2br(e($c['content'])) ?>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (empty($approved_comments)): ?>
                                    <p class="text-muted text-center pt-3">No comments yet. Be the first to share your
                                        thoughts!</p>
                                <?php endif; ?>
                            </div>
                        </section>

                    </div>
                </div>
            </div>
        </div>

        <!-- ORIGINAL THEME FOOTER -->
        <footer class="glass-panel py-5 mt-auto mb-0"
            style="border-radius:0; border-left:0; border-right:0; border-bottom:0;">
            <div class="container text-center text-muted">
                <h4 class="fw-bold text-dark mb-3">60Sec<span class="text-primary">News</span></h4>
                <p class="mb-4 small">Condensing the world's facts into 60 seconds.</p>
                <p class="small">&copy; <?= date('Y') ?> 60SecNews. All rights reserved.</p>
            </div>
        </footer>
    </div> <!-- End .main-content-wrapper -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Minimal JS for Reading Tools -->
    <!-- Engagement and Tools JS -->
    <script>
        const ARTICLE_ID = <?= intval($article['id']) ?>;
        const ARTICLE_TITLE = "<?= addslashes($article['title']) ?>";
        const ARTICLE_URL = window.location.href;

        function toggleInteraction(type) {
            const formData = new FormData();
            formData.append('article_id', ARTICLE_ID);
            formData.append('type', type);

            fetch('<?= BASE_URL ?>/ajax_interaction.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const btn = document.getElementById('btn-' + type);
                        const icon = btn.querySelector('i');
                        const textSpan = btn.querySelector('.btn-text');

                        if (type === 'like') {
                            if (data.action === 'added') {
                                btn.classList.replace('btn-outline-danger', 'btn-danger');
                                icon.classList.replace('bi-heart', 'bi-heart-fill');
                                textSpan.innerText = 'Liked';
                            } else {
                                btn.classList.replace('btn-danger', 'btn-outline-danger');
                                icon.classList.replace('bi-heart-fill', 'bi-heart');
                                textSpan.innerText = 'Like';
                            }
                        } else if (type === 'bookmark') {
                            if (data.action === 'added') {
                                btn.classList.replace('btn-outline-dark', 'btn-dark');
                                icon.classList.replace('bi-bookmark', 'bi-bookmark-fill');
                                textSpan.innerText = 'Saved';
                            } else {
                                btn.classList.replace('btn-dark', 'btn-outline-dark');
                                icon.classList.replace('bi-bookmark-fill', 'bi-bookmark');
                                textSpan.innerText = 'Save';
                            }
                        }
                    }
                });
        }

        function shareArticle(platform) {
            let url = '';
            const text = encodeURIComponent(ARTICLE_TITLE);
            const link = encodeURIComponent(ARTICLE_URL);

            if (platform === 'twitter') {
                url = `https://twitter.com/intent/tweet?text=${text}&url=${link}`;
            } else if (platform === 'facebook') {
                url = `https://www.facebook.com/sharer/sharer.php?u=${link}`;
            }

            if (url) {
                window.open(url, '_blank', 'width=600,height=400');
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Reading Progress
            window.addEventListener('scroll', () => {
                const docElem = document.documentElement;
                const docBody = document.body;
                let scrollTop = docElem.scrollTop || docBody.scrollTop;
                let scrollHeight = docElem.scrollHeight || docBody.scrollHeight;
                let clientHeight = docElem.clientHeight;
                let percent = (scrollTop / (scrollHeight - clientHeight)) * 100;
                const progress = document.getElementById('reading-progress');
                if (progress) progress.style.width = percent + '%';
            });

            // Font Resizer
            const articleBody = document.getElementById('article-body');
            let currentSize = 1.15; // default rem (matches CSS article-text)

            const incBtn = document.getElementById('btn-font-inc');
            const decBtn = document.getElementById('btn-font-dec');

            if (incBtn && articleBody) {
                incBtn.addEventListener('click', () => {
                    if (currentSize < 1.6) {
                        currentSize += 0.1;
                        articleBody.style.fontSize = currentSize + 'rem';
                    }
                });
            }

            if (decBtn && articleBody) {
                decBtn.addEventListener('click', () => {
                    if (currentSize > 0.9) {
                        currentSize -= 0.1;
                        articleBody.style.fontSize = currentSize + 'rem';
                    }
                });
            }

            // Reading Focus Mode
            const readBtn = document.getElementById('btn-reading-mode');
            if (readBtn) {
                readBtn.addEventListener('click', () => {
                    document.body.classList.toggle('reading-mode');
                    const isMode = document.body.classList.contains('reading-mode');
                    readBtn.innerHTML = isMode ?
                        '<i class="bi bi-eye"></i> Standard View' :
                        '<i class="bi bi-eye-fill"></i> Optimized Reading';
                });
            }
        });
    </script>
</body>

</html>
<?php

?>
