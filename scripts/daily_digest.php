<?php
// cron/daily_digest.php
// This script should be invoked via CRON: `php c:\xampp\htdocs\60secondsnews\scripts\daily_digest.php`

// Prevent HTTP execution for security
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Forbidden. This script can only be run from the command line.");
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';

$db = DB::getInstance()->getConnection();

// Fetch Top 5 Articles from the last 24 hours
$yesterday = date('Y-m-d H:i:s', strtotime('-24 hours'));

$articles = $db->prepare("
    SELECT title, slug, summary, category_id 
    FROM articles 
    WHERE status = 'published' AND publish_at >= ?
    ORDER BY is_breaking DESC, is_pinned DESC, publish_at DESC 
    LIMIT 5
");
$articles->execute([$yesterday]);
$top_stories = $articles->fetchAll();

if (empty($top_stories)) {
    echo "No articles published in the last 24 hours. Exiting.\n";
    exit;
}

// Fetch Subscribers
$subscribers = $db->query("SELECT email FROM subscribers")->fetchAll(PDO::FETCH_COLUMN);

if (empty($subscribers)) {
    echo "No subscribers found. Exiting.\n";
    exit;
}

// In CLI, we might not have $_SERVER['HTTP_HOST'], but we can fallback or use BASE_URL if absolute
$domain = (defined('BASE_URL') && strpos(BASE_URL, 'http') === 0) ? BASE_URL : "http://localhost" . BASE_URL;
$domain = rtrim($domain, '/') . '/public';

// Build Email Content
$subject = "Your Daily 60-Second News Digest - " . date('M j, Y');

$message = "<html><body>";
$message .= "<h2 style='color:#d90429;'>60SecNews Daily Digest</h2>";
$message .= "<p>Here are the top stories you need to know today, condensed for speed:</p>";
$message .= "<hr>";

foreach ($top_stories as $story) {
    $article_url = $domain . "/article.php?slug=" . e($story['slug']);
    $message .= "<h3><a href='{$article_url}' style='color:#0f1014; text-decoration:none;'>" . e($story['title']) . "</a></h3>";
    $message .= "<p style='color:#6c757d; font-size:14px;'>" . e($story['summary']) . "</p>";
    $message .= "<a href='{$article_url}' style='color:#d90429; font-weight:bold; font-size:12px;'>READ IN 60s &rarr;</a>";
    $message .= "<br><br>";
}

$message .= "<hr>";
$message .= "<p style='font-size:11px; color:#aaa;'>You are receiving this because you subscribed to 60SecNews. <a href='{$domain}/unsubscribe.php'>Unsubscribe</a></p>";
$message .= "</body></html>";

$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=utf-8\r\n";
$headers .= "From: Editor <editor@60secnews.com>\r\n";

$count = 0;
foreach ($subscribers as $email) {
    if (mail($email, $subject, $message, $headers)) {
        $count++;
    }
}

echo "Successfully sent {$count} digest emails.\n";
