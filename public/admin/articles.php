<?php
// admin/articles.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/helpers.php';
require_once __DIR__ . '/../../app/rbac.php';

require_login();
$db = DB::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_article') {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $article_id = (int) $_POST['article_id'];
    $stmt = $db->prepare("SELECT author_id, featured_image_id FROM articles WHERE id = ?");
    $stmt->execute([$article_id]);
    $article_check = $stmt->fetch();

    if ($article_check && (has_permission('delete_article') || $article_check['author_id'] == $_SESSION['user_id'])) {
        $db->prepare("DELETE FROM articles WHERE id = ?")->execute([$article_id]);
        log_activity("Deleted article ID $article_id", 'articles', $article_id);
        clear_cache(); // Auto-clear frontend cache

        set_flash_message('success', "Article deleted successfully.");
    } else {
        set_flash_message('danger', "You do not have permission to delete this article.");
    }
    header('Location: articles.php');
    exit;
}

require_once __DIR__ . '/layout/header.php';

$status_filter = $_GET['status'] ?? 'all';
$can_edit_all = has_permission('edit_any_article');
$can_publish = has_permission('publish_article');

// Build query depending on role & filters
$sql = "SELECT a.*, u.username as author_name, c.name as category_name 
        FROM articles a 
        JOIN users u ON a.author_id = u.id 
        JOIN categories c ON a.category_id = c.id";
$params = [];
$conditions = [];

if (!$can_edit_all) {
    // Writers can only see their own stories unless published
    $conditions[] = "(a.author_id = ? OR a.status = 'published')";
    $params[] = $_SESSION['user_id'];
}

if ($status_filter !== 'all') {
    $conditions[] = "a.status = ?";
    $params[] = $status_filter;
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}

$sql .= " ORDER BY a.updated_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll();

?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-white">Articles</h1>
    <?php if (has_permission('create_article')): ?>
        <a href="article_edit.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Write Article</a>
    <?php endif; ?>
</div>

<!-- Tabs for statuses -->
<ul class="nav nav-tabs border-secondary mb-4">
    <li class="nav-item">
        <a class="nav-link <?= $status_filter === 'all' ? 'active bg-dark text-white border-secondary border-bottom-0' : 'text-muted' ?>"
            href="?status=all">All</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $status_filter === 'published' ? 'active bg-dark text-white border-secondary border-bottom-0' : 'text-muted' ?>"
            href="?status=published">Published</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $status_filter === 'pending' ? 'active bg-dark text-white border-secondary border-bottom-0' : 'text-muted' ?>"
            href="?status=pending">Pending Review <span class="badge bg-warning text-dark ms-1">!</span></a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $status_filter === 'embargoed' ? 'active bg-dark text-white border-secondary border-bottom-0' : 'text-muted' ?>"
            href="?status=embargoed">Embargoed</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $status_filter === 'trashed' ? 'active bg-dark text-white border-secondary border-bottom-0' : 'text-muted' ?>"
            href="?status=trashed">Trash</a>
    </li>
</ul>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0">
                <thead>
                    <tr>
                        <th>Headline</th>
                        <th>Category</th>
                        <th>Author</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($articles as $article): ?>
                        <tr>
                            <td style="max-width: 300px;">
                                <div class="text-truncate fw-bold <?= $article['is_breaking'] ? 'text-danger' : '' ?>">
                                    <?php if ($article['is_breaking'])
                                        echo '<i class="bi bi-lightning-fill"></i> '; ?>
                                    <?= e($article['title']) ?>
                                </div>
                                <small class="text-muted d-block text-truncate">
                                    <?= e($article['summary']) ?>
                                </small>
                            </td>
                            <td><span class="badge bg-secondary">
                                    <?= e($article['category_name']) ?>
                                </span></td>
                            <td>
                                <?= e($article['author_name']) ?>
                            </td>
                            <td>
                                <?php
                                $badges = [
                                    'pending' => 'bg-warning text-dark',
                                    'published' => 'bg-success',
                                    'embargoed' => 'bg-info text-dark',
                                    'trashed' => 'bg-danger'
                                ];
                                $badge_class = $badges[$article['status']] ?? 'bg-secondary';
                                ?>
                                <span class="badge <?= $badge_class ?> text-uppercase">
                                    <?= $article['status'] ?>
                                </span>
                            </td>
                            <td>
                                <small>
                                    <?= $article['status'] === 'published' && $article['publish_at'] ? date('M j, Y H:i', strtotime($article['publish_at'])) : date('M j', strtotime($article['updated_at'])) ?>
                                </small>
                            </td>
                            <td class="text-end">
                                <?php if ($can_edit_all || $article['author_id'] == $_SESSION['user_id']): ?>
                                    <a href="article_edit.php?id=<?= $article['id'] ?>"
                                        class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
                                <?php endif; ?>

                                <?php if ($article['status'] === 'published'): ?>
                                    <a href="<?= BASE_URL ?>/article.php?slug=<?= e($article['slug']) ?>" target="_blank"
                                        class="btn btn-sm btn-outline-light"><i class="bi bi-eye"></i></a>
                                <?php endif; ?>

                                <?php if (has_permission('delete_article') || $article['author_id'] == $_SESSION['user_id']): ?>
                                    <form method="POST" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete_article">
                                        <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Article"
                                            onclick="return confirm('Are you sure you want to PERMANENTLY delete this article?');">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($articles)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No articles found in this view.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layout/footer.php'; ?>