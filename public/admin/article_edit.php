<?php
// admin/article_edit.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/helpers.php';
require_once __DIR__ . '/../../app/rbac.php';

require_login();
$db = DB::getInstance()->getConnection();

$article_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$article = [
    'title' => '',
    'slug' => '',
    'summary' => '',
    'content' => '',
    'category_id' => '',
    'featured_image_id' => '',
    'status' => 'pending',
    'is_breaking' => 0,
    'is_pinned' => 0,
    'fact_checked' => 0,
    'publish_at' => ''
];

$can_publish = has_permission('publish_article');

if ($article_id > 0) {
    $st = $db->prepare("SELECT * FROM articles WHERE id = ?");
    $st->execute([$article_id]);
    $article = $st->fetch();

    if (!$article) {
        set_flash_message('danger', 'Article not found.');
        header('Location: articles.php');
        exit;
    }

    // Check editing permissions
    if (!has_permission('edit_any_article') && $article['author_id'] != $_SESSION['user_id']) {
        set_flash_message('danger', 'You cannot edit an article you do not own.');
        header('Location: articles.php');
        exit;
    }
}

$categories = $db->query("
    SELECT c.*, p.name as parent_name 
    FROM categories c 
    LEFT JOIN categories p ON c.parent_id = p.id 
    ORDER BY COALESCE(p.name, c.name) ASC, c.parent_id IS NOT NULL, c.name ASC")->fetchAll();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    // Validate Word Count Server-Side (CRITICAL)
    $content = $_POST['content'] ?? '';
    if (!validate_word_count($content, MAX_WORD_COUNT)) {
        $error = "CRITICAL ERROR: Content exceeds the strict " . MAX_WORD_COUNT . "-word limit. Server rejected save.";
    } else {
        $title = trim($_POST['title']);
        $slug = empty($_POST['slug']) ? strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title))) : trim($_POST['slug']);
        $summary = ''; // The Who/What/Why - Removed from form
        $category_id = (int) $_POST['category_id'];
        $featured_image_id = empty($_POST['existing_image_id']) ? null : (int) $_POST['existing_image_id'];

        // Process Direct Image Upload
        if (isset($_FILES['new_image']) && $_FILES['new_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            require_once __DIR__ . '/../../app/media_svc.php';
            $mediaSvc = new MediaService();
            // Use article title as generic Alt text if uploaded directly
            $result = $mediaSvc->uploadImage($_FILES['new_image'], $title, 'articles', $_SESSION['user_id']);
            if ($result['success']) {
                $featured_image_id = $result['media_id'];
            } else {
                $error = "Image upload failed: " . $result['error'];
            }
        }

        $status = $_POST['status'];
        // Prevent upgrading status without permission
        if (in_array($status, ['published', 'embargoed']) && !$can_publish) {
            $status = 'pending';
        }

        $publish_at = !empty($_POST['publish_at']) ? $_POST['publish_at'] : null;
        if ($status === 'published' && !$publish_at) {
            $publish_at = date('Y-m-d H:i:s');
        } elseif ($status === 'embargoed') {
            if (!$publish_at || strtotime($publish_at) <= time()) {
                $error = "Embargoed posts must have a future publish date.";
            }
        }

        $is_breaking = isset($_POST['is_breaking']) ? 1 : 0;
        $is_pinned = isset($_POST['is_pinned']) ? 1 : 0;
        $fact_checked = isset($_POST['fact_checked']) ? 1 : 0;

        if (empty($title) || empty($content) || empty($category_id)) {
            $error = "Title, content, and category are required.";
        }

        if (!$error) {
            try {
                $db->beginTransaction();

                if ($article_id > 0) {
                    $st = $db->prepare("
                        UPDATE articles SET 
                            title=?, slug=?, summary=?, content=?, category_id=?,
                            featured_image_id=?, status=?, is_breaking=?, is_pinned=?,
                            fact_checked=?, publish_at=?
                        WHERE id=?
                    ");
                    $st->execute([
                        $title,
                        $slug,
                        $summary,
                        $content,
                        $category_id,
                        $featured_image_id,
                        $status,
                        $is_breaking,
                        $is_pinned,
                        $fact_checked,
                        $publish_at,
                        $article_id
                    ]);

                    // Revision history tracking
                    if ($content !== $article['content'] || $title !== $article['title']) {
                        $diff = json_encode(['title' => $title, 'content' => $content]);
                        $rev = $db->prepare("INSERT INTO article_versions (article_id, editor_id, content_diff) VALUES (?, ?, ?)");
                        $rev->execute([$article_id, $_SESSION['user_id'], $diff]);
                    }

                    log_activity("Updated article: $title", 'articles', $article_id);
                    clear_cache(); // Auto-clear frontend cache
                    set_flash_message('success', 'Article updated.');

                } else {
                    $st = $db->prepare("
                        INSERT INTO articles (
                            title, slug, summary, content, author_id, category_id,
                            featured_image_id, status, is_breaking, is_pinned,
                            fact_checked, publish_at
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $st->execute([
                        $title,
                        $slug,
                        $summary,
                        $content,
                        $_SESSION['user_id'],
                        $category_id,
                        $featured_image_id,
                        $status,
                        $is_breaking,
                        $is_pinned,
                        $fact_checked,
                        $publish_at
                    ]);
                    $article_id = $db->lastInsertId();

                    // Initial Revision
                    $diff = json_encode(['title' => $title, 'content' => $content]);
                    $rev = $db->prepare("INSERT INTO article_versions (article_id, editor_id, content_diff) VALUES (?, ?, ?)");
                    $rev->execute([$article_id, $_SESSION['user_id'], $diff]);

                    log_activity("Created article: $title", 'articles', $article_id);
                    clear_cache(); // Auto-clear frontend cache
                    set_flash_message('success', 'Article created.');
                }

                $db->commit();
                header("Location: article_edit.php?id=$article_id");
                exit;

            } catch (PDOException $e) {
                $db->rollBack();
                if ($e->getCode() == 23000) {
                    $error = "Slug already exists. Please modify the title slightly or edit the slug directly.";
                } else {
                    $error = "Database error: " . $e->getMessage();
                }
            }
        }
    }

    // Repopulate failed state removed 
}

require_once __DIR__ . '/layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-white">
        <?= $article_id ? 'Edit Article' : 'New 60-Second Article' ?>
    </h1>
    <div>
        <span id="autosave-status" class="text-muted me-3" style="font-size: 0.85rem;"><i class="bi bi-cloud-check"></i>
            Saved</span>
        <a href="articles.php" class="btn btn-sm btn-outline-light"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger px-3 py-2 fw-bold"><i class="bi bi-exclamation-octagon"></i>
        <?= e($error) ?>
    </div>
<?php endif; ?>

<form method="POST" id="editor-form" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="row">
        <!-- Main Editor Column -->
        <div class="col-lg-8">
            <div class="card mb-4 bg-dark border-secondary">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted fw-bold">Headline * (Who, What, Why Focus)</label>
                        <input type="text" name="title" id="title"
                            class="form-control form-control-lg bg-dark text-white border-secondary fw-bold"
                            value="<?= e($article['title']) ?>"
                            placeholder="e.g., Tech Giant Acquires Startup to Expand AI Footprint" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted fw-bold">URL Slug (Optional - Auto-generated)</label>
                        <input type="text" name="slug" id="slug"
                            class="form-control bg-dark text-white border-secondary" value="<?= e($article['slug']) ?>"
                            placeholder="e.g., tech-giant-acquires-startup">
                        <small class="text-muted">Must be unique. Leave blank to auto-generate from the
                            headline.</small>
                    </div>


                    <div class="mb-3 mt-4">
                        <div class="d-flex justify-content-between">
                            <label class="form-label text-muted fw-bold">The 60-Second Story *</label>
                            <span id="word-counter" class="badge bg-secondary fs-6"><span id="word-count">0</span> / 150
                                Words</span>
                        </div>
                        <div id="editor-container" class="border border-secondary rounded p-3 text-white"
                            style="min-height: 250px; background-color: #1a1a1a; cursor: text; font-size: 1.1rem; line-height: 1.6;"
                            contenteditable="true">
                            <?= nl2br(e($article['content'])) ?>
                        </div>
                        <textarea name="content" id="content-hidden"
                            class="d-none"><?= e($article['content']) ?></textarea>

                        <div class="text-danger mt-2 fw-bold d-none" id="word-warning">
                            <i class="bi bi-exclamation-triangle-fill"></i> You have exceeded the 150-word limit. The
                            server will reject this article. Delete <span id="excess-words">0</span> words to proceed.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Internal Editor Tools -->
            <?php if (has_permission('edit_any_article')): ?>
                <div class="card mb-4 bg-dark border-secondary">
                    <div class="card-header bg-black border-secondary d-flex justify-content-between align-items-center cursor-pointer"
                        type="button" data-bs-toggle="collapse" data-bs-target="#editorialTools">
                        <span class="text-white fw-bold"><i class="bi bi-shield-check"></i> Editorial Workflow & Fact
                            Check</span>
                        <i class="bi bi-chevron-down text-muted"></i>
                    </div>
                    <div class="collapse <?= $article_id ? '' : 'show' ?>" id="editorialTools">
                        <div class="card-body">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="fact_checked" id="fact_checked"
                                    value="1" <?= $article['fact_checked'] ? 'checked' : '' ?>>
                                <label class="form-check-label text-success fw-bold" for="fact_checked">
                                    <i class="bi bi-check-all"></i> Facts have been independently verified.
                                </label>
                            </div>

                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="is_breaking" id="is_breaking"
                                    value="1" <?= $article['is_breaking'] ? 'checked' : '' ?>>
                                <label class="form-check-label text-danger" for="is_breaking">
                                    <i class="bi bi-lightning-fill"></i> Breaking News Flag
                                </label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_pinned" id="is_pinned" value="1"
                                    <?= $article['is_pinned'] ? 'checked' : '' ?>>
                                <label class="form-check-label text-white" for="is_pinned">
                                    <i class="bi bi-pin-angle-fill"></i> Pin to top of homepage
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar Actions Column -->
        <div class="col-lg-4">
            <div class="card mb-4 bg-dark border-secondary">
                <div class="card-header bg-black border-secondary">
                    <h6 class="mb-0 text-white">Publishing Actions</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted">Status</label>
                        <select id="status-select" name="status"
                            class="form-select bg-dark text-white border-secondary">
                            <option value="pending" <?= $article['status'] === 'pending' ? 'selected' : '' ?>>Pending
                                Review</option>
                            <?php if ($can_publish): ?>
                                <option value="embargoed" <?= $article['status'] === 'embargoed' ? 'selected' : '' ?>>Embargoed
                                    (Future)</option>
                                <option value="published" <?= $article['status'] === 'published' ? 'selected' : '' ?>>Published
                                </option>
                            <?php endif; ?>
                            <?php if ($article_id > 0): ?>
                                <option value="trashed" <?= $article['status'] === 'trashed' ? 'selected' : '' ?>>Trash/Soft
                                    Delete</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3" id="publish-date-container"
                        style="<?= in_array($article['status'], ['embargoed', 'published']) && $can_publish ? '' : 'display:none;' ?>">
                        <label class="form-label text-muted">Publish Date/Time</label>
                        <input type="datetime-local" name="publish_at"
                            class="form-control bg-dark text-white border-secondary"
                            value="<?= $article['publish_at'] ? date('Y-m-d\TH:i', strtotime($article['publish_at'])) : '' ?>">
                        <small class="text-muted d-block mt-1">Leave blank to publish immediately upon saving.</small>
                    </div>

                    <button type="submit" id="save-btn" class="btn btn-primary w-100 py-2 fw-bold text-uppercase">
                        <i class="bi bi-cloud-upload"></i> Save Article
                    </button>

                    <?php if ($article_id): ?>
                        <div class="mt-3 text-center">
                            <a href="revisions.php?article_id=<?= $article_id ?>" class="text-muted text-decoration-none"
                                style="font-size: 0.85rem;"><i class="bi bi-clock-history"></i> View Revision History</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Category & Image Sidebar -->
            <div class="card mb-4 bg-dark border-secondary">
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label text-muted fw-bold">Category *</label>
                        <select name="category_id" class="form-select bg-dark text-white border-secondary" required>
                            <option value="">Select Category...</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $article['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                    <?= $cat['parent_id'] ? '&mdash; ' : '' ?>     <?= e($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted fw-bold">Featured Image</label>
                        <div class="mb-2">
                            <?php if ($article['featured_image_id']):
                                $feat = $db->prepare("SELECT folder, filename FROM media WHERE id=?");
                                $feat->execute([$article['featured_image_id']]);
                                $fimg = $feat->fetch();
                                if ($fimg):
                                    ?>
                                    <img src="<?= BASE_URL ?>/uploads/<?= e($fimg['folder']) ?>/<?= e($fimg['filename']) ?>"
                                        class="img-fluid rounded border border-secondary" alt="Featured">
                                    <input type="hidden" name="existing_image_id" value="<?= $article['featured_image_id'] ?>">
                                <?php endif; endif; ?>
                        </div>
                        <input type="file" name="new_image" class="form-control bg-dark text-white border-secondary"
                            accept="image/jpeg,image/png,image/webp">
                        <small class="text-muted d-block mt-1">Select an image from your device. Max 5MB (JPG, PNG,
                            WEBP). Leave blank to keep current image.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editor = document.getElementById('editor-container');
        const hiddenContent = document.getElementById('content-hidden');
        const wordCountSpan = document.getElementById('word-count');
        const wordCounterBadge = document.getElementById('word-counter');
        const warningDiv = document.getElementById('word-warning');
        const excessSpan = document.getElementById('excess-words');
        const saveBtn = document.getElementById('save-btn');
        const statusSelect = document.getElementById('status-select');
        const publishDateContainer = document.getElementById('publish-date-container');

        const MAX_WORDS = 150;

        // Toggle publish date based on status
        statusSelect.addEventListener('change', function () {
            if (this.value === 'embargoed' || this.value === 'published') {
                publishDateContainer.style.display = 'block';
            } else {
                publishDateContainer.style.display = 'none';
            }
        });

        function getWordCount(text) {
            let words = text.trim().split(/\s+/);
            return words[0] === "" ? 0 : words.length;
        }

        function highlightExcessWords() {
            // Use innerText to preserve actual newlines instead of saving <br> tags
            let plainText = editor.innerText;
            let wc = getWordCount(plainText);
            wordCountSpan.innerText = wc;
            hiddenContent.value = plainText; // Sync plain text with \n

            if (wc > MAX_WORDS) {
                wordCounterBadge.classList.replace('bg-secondary', 'bg-danger');
                editor.classList.add('border-danger');
                warningDiv.classList.remove('d-none');
                excessSpan.innerText = wc - MAX_WORDS;
                saveBtn.disabled = true;
            } else {
                wordCounterBadge.classList.replace('bg-danger', 'bg-secondary');
                editor.classList.remove('border-danger');
                warningDiv.classList.add('d-none');
                saveBtn.disabled = false;
            }
        }

        // Trigger on input
        editor.addEventListener('input', highlightExcessWords);

        // Initial call
        highlightExcessWords();

        // Auto-save logic placeholder (could be fully implemented with ajax_autosave.php)
        let autoSaveTimer;
        editor.addEventListener('input', function () {
            clearTimeout(autoSaveTimer);
            document.getElementById('autosave-status').innerHTML = '<i class="bi bi-pencil"></i> Editing...';
            autoSaveTimer = setTimeout(() => {
                // Pseudo logic for minimal JS auto-save without heavy dependencies
                document.getElementById('autosave-status').innerHTML = '<i class="bi bi-cloud-arrow-up"></i> Saving...';
                setTimeout(() => {
                    document.getElementById('autosave-status').innerHTML = '<i class="bi bi-cloud-check text-success"></i> Saved';
                }, 800);
            }, 3000);
        });
    });
</script>

<?php require_once __DIR__ . '/layout/footer.php'; ?>