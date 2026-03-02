<?php
// public/rss.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';
check_maintenance();

header("Content-Type: application/rss+xml; charset=utf-8");

$db = DB::getInstance()->getConnection();

$settings_file = BASE_PATH . '/config/settings.json';
$settings = ['site_title' => '60-Second News'];
if (file_exists($settings_file)) {
    $loaded = json_decode(file_get_contents($settings_file), true);
    if (is_array($loaded))
        $settings = array_merge($settings, $loaded);
}

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domain = $protocol . $_SERVER['HTTP_HOST'] . rtrim(BASE_URL, '/');

$articles = $db->query("
    SELECT a.*, u.username 
    FROM articles a 
    JOIN users u ON a.author_id = u.id
    WHERE a.status = 'published' AND a.publish_at <= NOW()
    ORDER BY a.publish_at DESC 
    LIMIT 50
")->fetchAll();

echo '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";
echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n";
echo "<channel>\n";
echo "  <title>" . e($settings['site_title']) . "</title>\n";
echo "  <link>{$domain}</link>\n";
echo "  <description>The world's facts condensed into 60 seconds.</description>\n";
echo "  <language>en-us</language>\n";
echo "  <atom:link href=\"{$domain}/rss.php\" rel=\"self\" type=\"application/rss+xml\" />\n";

if (!empty($articles)) {
    echo "  <pubDate>" . date(DATE_RSS, strtotime($articles[0]['publish_at'])) . "</pubDate>\n";
}

foreach ($articles as $a) {
    echo "  <item>\n";
    echo "    <title>" . htmlspecialchars($a['title']) . "</title>\n";
    echo "    <link>{$domain}/article.php?slug=" . htmlspecialchars($a['slug']) . "</link>\n";
    echo "    <guid>{$domain}/article.php?slug=" . htmlspecialchars($a['slug']) . "</guid>\n";
    echo "    <description><![CDATA[" . htmlspecialchars($a['summary']) . "]]></description>\n";
    echo "    <pubDate>" . date(DATE_RSS, strtotime($a['publish_at'])) . "</pubDate>\n";
    echo "    <author>editor@60secnews.com (" . htmlspecialchars($a['username']) . ")</author>\n";
    echo "  </item>\n";
}

echo "</channel>\n";
echo "</rss>";


