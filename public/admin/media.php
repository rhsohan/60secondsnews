<?php
// admin/media.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/helpers.php';
require_once __DIR__ . '/../../app/rbac.php';
require_once __DIR__ . '/../../app/media_svc.php';

require_login();
require_permission('upload_media');
$db = DB::getInstance()->getConnection();
$mediaSvc = new MediaService();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_media') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $media_id = (int) $_POST['media_id'];

    // Check if another article is inextricably linked to it
    $stmtUsage = $db->prepare("SELECT COUNT(*) FROM articles WHERE featured_image_id = ?");
    $stmtUsage->execute([$media_id]);
    if ($stmtUsage->fetchColumn() > 0) {
        set_flash_message('danger', 'Cannot delete this image because it is currently being used as a featured image by an active article.');
    } else {
        $stmtDelMedia = $db->prepare("SELECT folder, filename FROM media WHERE id = ?");
        $stmtDelMedia->execute([$media_id]);
        $md = $stmtDelMedia->fetch();
        if ($md) {
            $filePath = __DIR__ . '/../uploads/' . $md['folder'] . '/' . $md['filename'];
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
            $db->prepare("DELETE FROM media WHERE id = ?")->execute([$media_id]);
            log_activity("Deleted media ID $media_id", 'media', $media_id);
            set_flash_message('success', 'Image successfully deleted.');
        }
    }

    $redirectFolder = isset($_GET['folder']) ? $_GET['folder'] : 'all';
    header('Location: media.php?folder=' . urlencode($redirectFolder));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $folder = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['folder'] ?? 'general');
    if (empty($folder))
        $folder = 'general';

    $alt_text = trim($_POST['alt_text'] ?? '');

    $result = $mediaSvc->uploadImage($_FILES['file'], $alt_text, $folder, $_SESSION['user_id']);

    if ($result['success']) {
        log_activity("Uploaded media ID {$result['media_id']}", 'media');
        set_flash_message('success', 'Image uploaded successfully!');
        header('Location: media.php?folder=' . urlencode($folder));
        exit;
    } else {
        $error = $result['error'];
    }
}

require_once __DIR__ . '/layout/header.php';

// Handle folder filter
$current_folder = isset($_GET['folder']) ? $_GET['folder'] : 'all';
$folders = $db->query("SELECT DISTINCT folder FROM media ORDER BY folder ASC")->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('general', $folders))
    array_unshift($folders, 'general');

$sql = "SELECT m.*, u.username FROM media m LEFT JOIN users u ON m.uploader_id = u.id";
$params = [];
if ($current_folder !== 'all') {
    $sql .= " WHERE m.folder = ?";
    $params[] = $current_folder;
}
$sql .= " ORDER BY m.uploaded_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$media_files = $stmt->fetchAll();

?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-white">Media Library</h1>
</div>

<div class="row">
    <!-- Upload Form -->
    <div class="col-md-4 mb-4">
        <div class="card bg-dark border-secondary">
            <div class="card-header bg-black border-secondary">
                <h5 class="mb-0 text-white"><i class="bi bi-cloud-arrow-up"></i> Upload Image</h5>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger p-2">
                        <?= e($error) ?>
                    </div>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label text-muted">Select Image (JPG, PNG, WEBP)</label>
                        <input type="file" name="file" class="form-control bg-dark text-white border-secondary"
                            accept=".jpg,.jpeg,.png,.webp" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Alt Text (SEO Required) *</label>
                        <input type="text" name="alt_text" class="form-control bg-dark text-white border-secondary"
                            placeholder="Description for screen readers" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Folder</label>
                        <input type="text" name="folder" class="form-control bg-dark text-white border-secondary"
                            placeholder="e.g. world-news"
                            value="<?= e($current_folder === 'all' ? 'general' : $current_folder) ?>">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Upload & Compress</button>
                </form>
            </div>
        </div>

        <!-- Folder Filter -->
        <div class="card bg-dark border-secondary mt-4">
            <div class="card-header bg-black border-secondary">
                <h5 class="mb-0 text-white"><i class="bi bi-folder"></i> Folders</h5>
            </div>
            <div class="list-group list-group-flush bg-dark">
                <a href="?folder=all"
                    class="list-group-item list-group-item-action bg-dark text-white <?= $current_folder === 'all' ? 'active' : '' ?>">
                    All Media
                </a>
                <?php foreach ($folders as $f): ?>
                    <a href="?folder=<?= urlencode($f) ?>"
                        class="list-group-item list-group-item-action bg-dark text-white <?= $current_folder === $f ? 'active' : '' ?>">
                        <i class="bi bi-folder2 text-warning me-2"></i>
                        <?= e($f) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Media Grid -->
    <div class="col-md-8">
        <div class="row g-3">
            <?php foreach ($media_files as $media): ?>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="card h-100 bg-dark border-secondary text-white position-relative">
                        <img src="<?= BASE_URL ?>/uploads/<?= e($media['folder']) ?>/<?= e($media['filename']) ?>"
                            class="card-img-top" alt="<?= e($media['alt_text']) ?>"
                            style="height: 120px; object-fit: cover; border-bottom: 1px solid #444;">

                        <form method="POST" class="position-absolute top-0 end-0 p-1 m-1">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete_media">
                            <input type="hidden" name="media_id" value="<?= $media['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger border-0 rounded-circle"
                                style="width: 28px; height: 28px; padding: 0;" title="Delete Image"
                                onclick="return confirm('Are you sure you want to permanently delete this media file?');">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>

                        <div class="card-body p-2" style="font-size: 0.8rem;">
                            <div class="text-truncate mb-1" title="<?= e($media['filename']) ?>"><strong>
                                    <?= e($media['filename']) ?>
                                </strong></div>
                            <div class="text-muted text-truncate mb-1"><i class="bi bi-info-circle"></i>
                                <?= e($media['alt_text']) ?>
                            </div>
                            <div class="text-muted"><i class="bi bi-person"></i>
                                <?= e($media['username'] ?? 'Unknown') ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($media_files)): ?>
                <div class="col-12 py-5 text-center text-muted">
                    <i class="bi bi-images fs-1 mb-3 d-block"></i>
                    <h5>No media found in this folder.</h5>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layout/footer.php'; ?>


