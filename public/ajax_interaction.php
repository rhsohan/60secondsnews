<?php
// public/ajax_interaction.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';
check_maintenance();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$article_id = intval($_POST['article_id'] ?? 0);
$type = $_POST['type'] ?? ''; // 'like' or 'bookmark'

if (!$article_id || !in_array($type, ['like', 'bookmark'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
    exit;
}

// Ensure session exists
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$session_id = session_id();

try {
    $db = DB::getInstance()->getConnection();

    // Check if interaction already exists
    $stmt = $db->prepare("SELECT id FROM bookmarks WHERE session_id = ? AND article_id = ? AND type = ?");
    $stmt->execute([$session_id, $article_id, $type]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Toggle: Remove if exists
        $db->prepare("DELETE FROM bookmarks WHERE id = ?")->execute([$existing['id']]);
        $action = 'removed';
    } else {
        // Toggle: Add if not exists
        $db->prepare("INSERT INTO bookmarks (session_id, article_id, type, created_at) VALUES (?, ?, ?, NOW())")
            ->execute([$session_id, $article_id, $type]);
        $action = 'added';
    }

    echo json_encode(['success' => true, 'action' => $action]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}


