<?php
ob_start(); // Prevent XAMPP warnings from corrupting JSON
// public/ajax_load_more.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';

$db = DB::getInstance()->getConnection();

$page = isset($_GET['page']) ? (int) $_GET['page'] : 2;
$limit = 6;
$offset = ($limit - 1) + ($page - 2) * $limit;

$category_slug = $_GET['category'] ?? 'all';
$params = [];
$cat_condition = "";

if ($category_slug !== 'all') {
    $cat_condition = " AND c.slug = ?";
    $params[] = $category_slug;
}

// Need to know the featured article ID to exclude it
$top_story_id = 0;
$ts_sql = "SELECT a.id FROM articles a JOIN categories c ON a.category_id = c.id WHERE a.status = 'published' AND a.is_pinned = 1 AND a.publish_at <= NOW() " . $cat_condition . " ORDER BY a.publish_at DESC LIMIT 1";
$ts_stmt = $db->prepare($ts_sql);
$ts_stmt->execute($params);
$top_story = $ts_stmt->fetch();

if (!$top_story) {
    $ts_sql = "SELECT a.id FROM articles a JOIN categories c ON a.category_id = c.id WHERE a.status = 'published' AND a.publish_at <= NOW() " . $cat_condition . " ORDER BY a.is_breaking DESC, a.is_pinned DESC, a.publish_at DESC LIMIT 1";
    $ts_stmt = $db->prepare($ts_sql);
    $ts_stmt->execute($params);
    $top_story = $ts_stmt->fetch();
}
$top_story_id = $top_story ? (int) $top_story['id'] : 0;

$latest_sql = "
    SELECT a.*, c.name as category_name, c.slug as category_slug, u.username as author_name, m.filename, m.folder,
           (SELECT SUM(view_count) FROM views WHERE article_id = a.id) as total_views
    FROM articles a
    JOIN categories c ON a.category_id = c.id
    JOIN users u ON a.author_id = u.id
    LEFT JOIN media m ON a.featured_image_id = m.id
    WHERE a.status = 'published' AND a.publish_at <= NOW()
    ";

if ($top_story_id > 0) {
    $latest_sql .= " AND a.id != " . $top_story_id;
}
$latest_sql .= $cat_condition . " GROUP BY a.id, c.name, c.slug, u.username, m.filename, m.folder ORDER BY a.publish_at DESC LIMIT $limit OFFSET $offset";

try {
    $stmt = $db->prepare($latest_sql);
    $stmt->execute($params);
    $articles = $stmt->fetchAll();

    $html = '';
    foreach ($articles as $article) {
        $html .= '<div class="col-md-6 col-sm-12">';
        $html .= '    <a href="' . BASE_URL . '/article.php?slug=' . e($article['slug']) . '" class="news-card grid-card h-100 d-flex flex-column">';

        if ($article['filename']) {
            $html .= '        <div class="card-img-wrapper">';
            $html .= '            <img src="' . BASE_URL . '/uploads/' . e($article['folder']) . '/' . e($article['filename']) . '" class="card-img-top w-100 h-100 object-fit-cover" alt="' . e($article['title']) . '">';
            $html .= '        </div>';
        }

        $html .= '        <div class="news-card-body p-4 d-flex flex-column flex-grow-1">';
        $html .= '            <div class="mb-3 d-flex flex-wrap gap-2">';
        $html .= '                <span class="badge bg-primary text-uppercase">' . e($article['category_name']) . '</span>';
        $html .= '                <span class="badge bg-dark"><i class="bi bi-clock"></i> 60 sec read</span>';
        $html .= '                <span class="badge bg-light text-dark border"><i class="bi bi-eye"></i> ' . number_format($article['total_views'] ?: 0) . '</span>';
        $html .= '            </div>';
        $html .= '            <h4 class="mb-2 fw-bold headline" style="letter-spacing: -0.5px; line-height: 1.3;">' . e($article['title']) . '</h4>';
        $html .= '            <p class="text-muted mb-4 small flex-grow-1">' . e(mb_strimwidth($article['summary'], 0, 110, '...')) . '</p>';

        $html .= '            <div class="mt-auto px-4 pb-4">';
        $html .= '                <button class="btn btn-dark w-100 btn-sm rounded-3 py-2 fw-bold" style="border: none;">';
        $html .= '                    Read in 60 Seconds';
        $html .= '                </button>';
        $html .= '            </div>';
        $html .= '        </div>';
        $html .= '    </a>';
        $html .= '</div>';
    }

    // Discard any XAMPP string warnings or empty spaces
    $warnings = ob_get_clean();

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'html' => $html,
        'has_more' => count($articles) === $limit
    ]);
    exit;

} catch (PDOException $e) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit;
}


