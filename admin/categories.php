<?php
require_once __DIR__ . '/includes/header.php';

$message = '';
$messageType = '';

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $category_name = trim($_POST['category_name']);
    if (!empty($category_name)) {
        $stmt = $pdo->prepare("INSERT INTO categories (category_name) VALUES (?)");
        if ($stmt->execute([$category_name])) {
            $message = "Category added successfully.";
            $messageType = "success";
        } else {
            $message = "Failed to add category.";
            $messageType = "error";
        }
    }
}

// Handle Delete Category
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Note: Deleting a category will cascade and delete associated news due to ON DELETE CASCADE in SQL setup.
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    if ($stmt->execute([$id])) {
        $message = "Category deleted successfully.";
        $messageType = "success";
    } else {
        $message = "Failed to delete category.";
        $messageType = "error";
    }
}

// Handle Edit Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = (int)$_POST['category_id'];
    $category_name = trim($_POST['category_name']);
    if(!empty($category_name)) {
        $stmt = $pdo->prepare("UPDATE categories SET category_name = ? WHERE id = ?");
        if ($stmt->execute([$category_name, $id])) {
            $message = "Category updated successfully.";
            $messageType = "success";
        } else {
            $message = "Failed to update category.";
            $messageType = "error";
        }
    }
}

// Fetch all categories
$stmtCats = $pdo->query("SELECT * FROM categories ORDER BY id DESC");
$categories = $stmtCats->fetchAll();
?>

<div class="flex justify-between items-center mb-8">
    <h2 class="text-3xl font-bold text-space-indigo">Manage Categories</h2>
</div>

<?php if ($message): ?>
    <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700 border border-green-400' : 'bg-red-100 text-red-700 border border-red-400'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Add Category Form -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-sm border border-almond-silk p-6">
            <h3 class="text-lg font-bold text-space-indigo mb-4 border-b border-almond-silk pb-2">Add New Category</h3>
            <form action="" method="POST">
                <input type="hidden" name="action" value="add">
                <div class="mb-4">
                    <label for="category_name" class="block text-sm font-medium text-space-indigo mb-2">Category Name</label>
                    <input type="text" id="category_name" name="category_name" class="w-full bg-parchment border border-almond-silk rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-space-indigo transition-colors" required>
                </div>
                <button type="submit" class="w-full bg-space-indigo text-parchment hover:bg-dusty-grape font-bold py-2 px-4 rounded-lg transition-colors">
                    Add Category
                </button>
            </form>
        </div>
    </div>

    <!-- Category List -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm border border-almond-silk overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-parchment text-dusty-grape text-sm">
                            <th class="py-3 px-6 font-medium w-16">ID</th>
                            <th class="py-3 px-6 font-medium">Category Name</th>
                            <th class="py-3 px-6 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        <?php if (count($categories) > 0): ?>
                            <?php foreach($categories as $cat): ?>
                                <tr class="border-b border-almond-silk hover:bg-gray-50 transition-colors">
                                    <td class="py-4 px-6 text-dusty-grape"><?php echo $cat['id']; ?></td>
                                    <td class="py-4 px-6 font-medium text-space-indigo">
                                        <!-- Keep simple for now, can implement inline JS edit -->
                                        <?php echo htmlspecialchars($cat['category_name']); ?>
                                    </td>
                                    <td class="py-4 px-6 text-right space-x-2 flex justify-end">
                                        <button onclick="editCategory(<?php echo $cat['id']; ?>, '<?php echo htmlspecialchars(addslashes($cat['category_name'])); ?>')" class="bg-almond-silk text-space-indigo hover:bg-dusty-grape hover:text-parchment px-3 py-1 rounded transition-colors text-xs font-semibold">
                                            Edit
                                        </button>
                                        <a href="?delete=<?php echo $cat['id']; ?>" onclick="return confirm('Are you sure you want to delete this category? All related news will also be deleted.')" class="bg-red-100 text-red-600 hover:bg-red-600 hover:text-white px-3 py-1 rounded transition-colors text-xs font-semibold">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="py-8 text-center text-dusty-grape">No categories found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit Category Modal (Hidden by default) -->
<div id="editModal" class="fixed inset-0 bg-space-indigo bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
        <h3 class="text-xl font-bold text-space-indigo mb-4 border-b border-almond-silk pb-2">Edit Category</h3>
        <form action="" method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="category_id" id="edit_category_id">
            <div class="mb-4">
                <label for="edit_category_name" class="block text-sm font-medium text-space-indigo mb-2">Category Name</label>
                <input type="text" id="edit_category_name" name="category_name" class="w-full bg-parchment border border-almond-silk rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-space-indigo transition-colors" required>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeEditModal()" class="bg-gray-200 text-gray-800 hover:bg-gray-300 font-bold py-2 px-4 rounded-lg transition-colors">
                    Cancel
                </button>
                <button type="submit" class="bg-space-indigo text-parchment hover:bg-dusty-grape font-bold py-2 px-4 rounded-lg transition-colors">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function editCategory(id, name) {
        document.getElementById('edit_category_id').value = id;
        document.getElementById('edit_category_name').value = name;
        document.getElementById('editModal').classList.remove('hidden');
        document.getElementById('editModal').classList.add('flex');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
        document.getElementById('editModal').classList.remove('flex');
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
