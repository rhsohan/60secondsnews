<?php
// admin/comments.php
require_once __DIR__ . '/layout/header.php';

require_permission('manage_comments');
$db = DB::getInstance()->getConnection();

$status_filter = $_GET['status'] ?? 'pending';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $comment_id = (int) $_POST['comment_id'];
    $new_status = $_POST['action']; 

    if (in_array($new_status, ['approved', 'rejected'])) {
        $st = $db->prepare("UPDATE comments SET status = ? WHERE id = ?");
        $st->execute([$new_status, $comment_id]);
        set_flash_message('success', "Comment marked as $new_status.");
        log_activity("Moderated comment ID $comment_id to $new_status", 'comments');
    } elseif ($new_status === 'delete') {
        $st = $db->prepare("DELETE FROM comments WHERE id = ?");
        $st->execute([$comment_id]);
        set_flash_message('warning', "Comment deleted.");
        log_activity("Deleted comment ID $comment_id", 'comments');
    }

    header("Location: comments.php?status=" . urlencode($status_filter));
    exit;
}

$s = "SELECT c.id, c.author_name, c.user_ip, c.content, c.status, c.created_at, a.title as article_title, a.slug as article_slug 
        FROM comments c 
        JOIN articles a ON c.article_id = a.id 
        WHERE c.status = ? 
        ORDER BY c.created_at DESC";
$st = $db->prepare($s);
$st->execute([$status_filter]);
$comments = $st->fetchAll();

?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-white">Comment Moderation</h1>
</div>

<ul class="nav nav-tabs border-secondary mb-4">
    <li class="nav-item">
        <a class="nav-link <?= $status_filter === 'pending' ? 'active bg-dark text-white border-secondary border-bottom-0' : 'text-muted' ?>"
            href="?status=pending">
            Pending <span class="badge bg-warning text-dark ms-1">!</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $status_filter === 'approved' ? 'active bg-dark text-white border-secondary border-bottom-0' : 'text-muted' ?>"
            href="?status=approved">Approved</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $status_filter === 'rejected' ? 'active bg-dark text-white border-secondary border-bottom-0' : 'text-muted' ?>"
            href="?status=rejected">Rejected</a>
    </li>
</ul>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width:15%">Date / IP</th>
                        <th style="width:40%">Comment</th>
                        <th style="width:25%">Article</th>
                        <th style="width:20%" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comments as $c): ?>
                    <tr>
                        <td>
                            <div class="text-nowrap fw-bold">
                                <?= e($c['author_name'] ?: 'Anonymous') ?>
                            </div>
                            <div class="text-nowrap small text-muted">
                                <?= date('M j, Y H:i', strtotime($c['created_at'])) ?>
                            </div>
                            <small class="text-muted"><i class="bi bi-hdd-network"></i>
                                <?= e($c['user_ip']) ?>
                            </small>
                        </td>
                        <td>
                            <div style="max-height: 80px; overflow-y: auto;" class="pe-2 text-wrap">
                                <?= nl2br(e($c['content'])) ?>
                            </div>
                        </td>
                        <td>
                            <a href="<?= BASE_URL ?>/article.php?slug=<?= e($c['article_slug']) ?>" target="_blank"
                                class="text-info text-decoration-none small text-truncate d-block"
                                style="max-width:250px;">
                                <?= e($c['article_title']) ?> <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        </td>
                        <td class="text-end text-nowrap">
                            <form method="POST" class="d-inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="comment_id" value="<?= $c['id'] ?>">

                                <?php if ($c['status'] !== 'approved'): ?>
                                <button type="submit" name="action" value="approved"
                                    class="btn btn-sm btn-outline-success" title="Approve"><i
                                        class="bi bi-check-lg"></i></button>
                                <?php endif; ?>

                                <?php if ($c['status'] !== 'rejected'): ?>
                                <button type="submit" name="action" value="rejected"
                                    class="btn btn-sm btn-outline-warning" title="Reject"><i
                                        class="bi bi-x-lg"></i></button>
                                <?php endif; ?>

                                <button type="submit" name="action" value="delete"
                                    class="btn btn-sm btn-outline-danger ms-1" title="Delete Permanently"
                                    onclick="return confirm('Delete this comment permanently?');"><i
                                        class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                    <?php if (empty($comments)): ?>
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">No comments found in this queue.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layout/footer.php'; ?>