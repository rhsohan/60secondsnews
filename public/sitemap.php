<?php
// public/sitemap.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/db.php';

header("Content-Type: application/xml; charset=utf-8");

$db = DB::getInstance()->getConnection();

$articles = $db->query("SELECT slug, updated_at FROM articles WHERE status = 'published' AND publish_at <= NOW() ORDER BY updated_at DESC");
$categories = $db->query("SELECT slug FROM categories");

$base = rtrim(BASE_URL, '/');
// When accessed via localhost, BASE_URL might be a relative path, so we ensure a full domain if necessary.
// We'll use a dummy domain for the example, but normally this would pull from $_SERVER['HTTP_HOST']
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domain = $protocol . $_SERVER['HTTP_HOST'] . $base;

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// Homepage
echo "  <url>\n";
echo "    <loc>{$domain}/</loc>\n";
echo "    <changefreq>always</changefreq>\n";
echo "    <priority>1.0</priority>\n";
echo "  </url>\n";

// Categories
while ($c = $categories->fetch()) {
    echo "  <url>\n";
    echo "    <loc>{$domain}/index.php?category=" . htmlspecialchars($c['slug']) . "</loc>\n";
    echo "    <changefreq>hourly</changefreq>\n";
    echo "    <priority>0.8</priority>\n";
    echo "  </url>\n";
}

// Articles
while ($a = $articles->fetch()) {
    echo "  <url>\n";
    echo "    <loc>{$domain}/article.php?slug=" . htmlspecialchars($a['slug']) . "</loc>\n";
    echo "    <lastmod>" . date('Y-m-d\TH:i:sP', strtotime($a['updated_at'])) . "</lastmod>\n";
    echo "    <changefreq>never</changefreq>\n";
    echo "    <priority>0.6</priority>\n";
    echo "  </url>\n";
}

echo "</urlset>";


