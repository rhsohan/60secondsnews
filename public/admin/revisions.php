<?php
// admin/revisions.php
require_once __DIR__ . '/layout/header.php';

require_login();
$db = DB::getInstance()->getConnection();

$article_id = isset($_GET['article_id']) ? (int) $_GET['article_id'] : 0;

if (!$article_id) {
    header('Location: articles.php');
    exit;
}

// Ensure they can view this article
$stmt = $db->prepare("SELECT id, title, author_id FROM articles WHERE id = ?");
$stmt->execute([$article_id]);
$article = $stmt->fetch();

if (!$article) {
    set_flash_message('danger', 'Article not found.');
    header('Location: articles.php');
    exit;
}

if (!has_permission('edit_any_article') && $article['author_id'] != $_SESSION['user_id']) {
    set_flash_message('danger', 'You do not have permission to view this article\'s history.');
    header('Location: articles.php');
    exit;
}

// Fetch Revisions
$revs_stmt = $db->prepare("
    SELECT rev.*, u.username as editor_name 
    FROM article_versions rev 
    LEFT JOIN users u ON rev.editor_id = u.id 
    WHERE rev.article_id = ? 
    ORDER BY rev.saved_at DESC
");
$revs_stmt->execute([$article_id]);
$revisions = $revs_stmt->fetchAll();

?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 text-white">Revision History</h1>
        <p class="text-muted mb-0">For article: <span class="fw-bold text-light">
                <?= e($article['title']) ?>
            </span></p>
    </div>
    <a href="article_edit.php?id=<?= $article_id ?>" class="btn btn-outline-light"><i class="bi bi-arrow-left"></i> Back
        to Editor</a>
</div>

<div class="card mb-4">
    <div class="card-body p-0">
        <ul class="list-group list-group-flush bg-dark">
            <?php foreach ($revisions as $index => $rev):
                $diff = json_decode($rev['content_diff'], true);
                $is_initial = ($index === count($revisions) - 1);
                ?>
                <li class="list-group-item bg-dark text-white border-secondary">
                    <div class="d-flex justify-content-between align-items-center cursor-pointer" type="button"
                        data-bs-toggle="collapse" data-bs-target="#rev-<?= $rev['id'] ?>">
                        <div>
                            <strong class="text-primary"><i
                                    class="bi <?= $is_initial ? 'bi-file-earmark-plus' : 'bi-pencil-square' ?>"></i>
                                <?= e($rev['editor_name']) ?>
                            </strong>
                            <?= $is_initial ? 'created the initial draft.' : 'saved a revision.' ?>
                        </div>
                        <div class="text-muted text-end">
                            <?= date('M j, Y - H:i', strtotime($rev['saved_at'])) ?><br>
                            <i class="bi bi-chevron-down fs-6"></i>
                        </div>
                    </div>

                    <div class="collapse mt-3" id="rev-<?= $rev['id'] ?>">
                        <div class="card card-body bg-black border-secondary">
                            <?php if ($diff): ?>
                                <h6 class="text-muted mb-1">Headline</h6>
                                <p class="fw-bold mb-3">
                                    <?= e($diff['title']) ?>
                                </p>

                                <h6 class="text-muted mb-1">Content</h6>
                                <div class="border border-secondary p-2 rounded" style="background-color: #111;">
                                    <?= nl2br(e($diff['content'])) ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted mb-0">No readable difference captured.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>

            <?php if (empty($revisions)): ?>
                <li class="list-group-item bg-dark text-muted text-center py-4">No revision history found.</li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<?php require_once __DIR__ . '/layout/footer.php'; ?>


