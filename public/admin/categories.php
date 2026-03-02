<?php
// admin/categories.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/helpers.php';
require_once __DIR__ . '/../../app/rbac.php';

require_login();
require_permission('manage_categories');

$db = DB::getInstance()->getConnection();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    if ($_POST['action'] === 'add') {
        $name = trim($_POST['name']);

        // Auto-generate slug
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

        if (!empty($name)) {
            try {
                $stmt = $db->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
                $stmt->execute([$name, $slug]);
                log_activity("Created category: $name", 'categories', $db->lastInsertId());
                // Clear frontend cache so the nav updates immediately
                clear_cache();
                set_flash_message('success', 'Category added.');
                header('Location: categories.php');
                exit;
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $error = "Category name or slug already exists.";
                } else {
                    $error = "Database error.";
                }
            }
        }
    } elseif ($_POST['action'] === 'delete') {
        $id = (int) $_POST['category_id'];

        $stmt = $db->prepare("SELECT id FROM articles WHERE category_id = ? LIMIT 1");
        $stmt->execute([$id]);
        if ($stmt->fetch()) {
            set_flash_message('danger', 'Cannot delete category. There are articles assigned to it.');
        } else {
            $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
            if ($stmt->execute([$id])) {
                log_activity("Deleted category ID: $id", 'categories', $id);
                clear_cache();
                set_flash_message('success', 'Category deleted successfully.');
            } else {
                set_flash_message('danger', 'Database error. Could not delete category.');
            }
        }
        header('Location: categories.php');
        exit;
    }
}
// DIR 
require_once __DIR__ . '/layout/header.php';

$categories = $db->query("
    SELECT c.*, COUNT(a.id) as article_count 
    FROM categories c 
    LEFT JOIN articles a ON c.id = a.category_id 
    GROUP BY c.id 
    ORDER BY c.name ASC")->fetchAll();


?>

<h1 class="h3 mb-4 text-white">Manage Categories</h1>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Articles</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td class="fw-bold">
                                    <?= e($cat['name']) ?>
                                </td>

                                <td>
                                    <span class="badge bg-primary rounded-pill">
                                        <?= $cat['article_count'] ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <?php if ($cat['article_count'] == 0): ?>
                                    <form method="POST" action="categories.php" class="d-inline"
                                        onsubmit="return confirm('Are you sure you want to delete this category?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <button class="btn btn-sm btn-outline-secondary" disabled
                                        title="Cannot delete category with associated articles">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mt-4 mt-md-0">
        <div class="card">
            <div class="card-header bg-dark border-secondary">
                <h5 class="mb-0 text-white">Add New Category</h5>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                <div class="alert alert-danger px-3 py-2">
                    <?= e($error) ?>
                </div>
                <?php endif; ?>
                <form method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label text-muted">Category Name</label>
                        <input type="text" name="name" class="form-control bg-dark text-white border-secondary" required
                            placeholder="e.g. Technology">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Add Category</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layout/footer.php'; ?>