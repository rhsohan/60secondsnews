<?php
require_once __DIR__ . '/includes/header.php';

$message = '';
$messageType = '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    header("Location: manage_news.php");
    exit;
}

// Fetch categories for dropdown
$stmtCats = $pdo->query("SELECT id, category_name FROM categories ORDER BY category_name");
$categories = $stmtCats->fetchAll();

// Fetch existing news data
$stmtNews = $pdo->prepare("SELECT * FROM news WHERE id = ?");
$stmtNews->execute([$id]);
$news = $stmtNews->fetch();

if (!$news) {
    echo "<div class='p-8 text-center text-red-600 font-bold'>Article not found.</div>";
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $category_id = (int)$_POST['category_id'];
    $summary_60 = trim($_POST['summary_60']);
    $summary_30 = trim($_POST['summary_30']);
    $full_content = trim($_POST['full_content']);
    $image = $news['image']; // default to existing
    
    // File upload logic (optional replacing)
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (in_array($_FILES['image']['type'], $allowedTypes)) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('news_') . '.' . $ext;
            $upload_path = dirname(__DIR__) . '/uploads/' . $filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Delete old image
                if ($image && file_exists(dirname(__DIR__) . '/uploads/' . $image)) {
                    unlink(dirname(__DIR__) . '/uploads/' . $image);
                }
                $image = $filename;
            } else {
                $message = "Failed to upload new image.";
                $messageType = "error";
            }
        } else {
            $message = "Invalid image format. Only JPG, PNG, WEBP are allowed.";
            $messageType = "error";
        }
    }
    
    if (empty($message)) {
        if (!empty($title) && !empty($category_id) && !empty($summary_60) && !empty($summary_30) && !empty($full_content)) {
            $stmt = $pdo->prepare("UPDATE news SET title = ?, summary_60 = ?, summary_30 = ?, full_content = ?, category_id = ?, image = ? WHERE id = ?");
            if ($stmt->execute([$title, $summary_60, $summary_30, $full_content, $category_id, $image, $id])) {
                $message = "News article updated successfully!";
                $messageType = "success";
                
                // Refresh data
                $stmtNews->execute([$id]);
                $news = $stmtNews->fetch();
            } else {
                $message = "Failed to update news article in database.";
                $messageType = "error";
            }
        } else {
            $message = "Please fill in all required fields.";
            $messageType = "error";
        }
    }
}
?>

<div class="flex items-center mb-8">
    <a href="manage_news.php" class="text-dusty-grape hover:text-space-indigo mr-4 transition-colors">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
    </a>
    <h2 class="text-3xl font-bold text-space-indigo">Edit Article</h2>
</div>

<?php if ($message): ?>
    <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700 border border-green-400' : 'bg-red-100 text-red-700 border border-red-400'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="bg-white rounded-xl shadow-sm border border-almond-silk p-8 max-w-4xl">
    <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-6 border-b border-almond-silk">
            <div class="md:col-span-2">
                <label for="title" class="block text-sm font-bold text-space-indigo mb-2">Article Title *</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($news['title']); ?>" required class="w-full bg-parchment border border-almond-silk rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-space-indigo transition-colors">
            </div>
            
            <div>
                <label for="category_id" class="block text-sm font-bold text-space-indigo mb-2">Category *</label>
                <select id="category_id" name="category_id" required class="w-full bg-parchment border border-almond-silk rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-space-indigo transition-colors">
                    <?php foreach($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $cat['id'] == $news['category_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label for="image" class="block text-sm font-bold text-space-indigo mb-2">Featured Image</label>
                <input type="file" id="image" name="image" accept="image/*" class="w-full bg-parchment border border-almond-silk rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-space-indigo file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-space-indigo file:text-parchment hover:file:bg-dusty-grape transition-colors">
                <?php if($news['image']): ?>
                    <p class="text-xs mt-2 text-dusty-grape">Current image: <?php echo htmlspecialchars($news['image']); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="space-y-6 pt-2">
            <div>
                <label for="summary_60" class="block text-sm font-bold text-space-indigo mb-2">What Happened? (60-sec Summary) *</label>
                <textarea id="summary_60" name="summary_60" rows="3" required class="w-full bg-parchment border border-almond-silk rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-space-indigo transition-colors"><?php echo htmlspecialchars($news['summary_60']); ?></textarea>
            </div>
            
            <div>
                <label for="summary_30" class="block text-sm font-bold text-space-indigo mb-2">30-Second Summary *</label>
                <textarea id="summary_30" name="summary_30" rows="2" required class="w-full bg-parchment border border-almond-silk rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-space-indigo transition-colors"><?php echo htmlspecialchars($news['summary_30']); ?></textarea>
            </div>
            
            <div>
                <label for="full_content" class="block text-sm font-bold text-space-indigo mb-2">Why It Matters & Key Facts (Full Content) *</label>
                <textarea id="full_content" name="full_content" rows="10" required class="w-full bg-parchment border border-almond-silk rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-space-indigo transition-colors font-mono text-sm"><?php echo htmlspecialchars($news['full_content']); ?></textarea>
            </div>
        </div>
        
        <div class="pt-6 border-t border-almond-silk flex justify-end">
            <button type="submit" class="bg-space-indigo text-parchment hover:bg-dusty-grape font-bold py-3 px-8 rounded-lg transition-colors shadow-md text-lg">
                Save Changes
            </button>
        </div>
        
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
