<?php
require_once __DIR__ . '/includes/header.php';

$message = '';
$messageType = '';

// Fetch categories for dropdown
$stmtCats = $pdo->query("SELECT id, category_name FROM categories ORDER BY category_name");
$categories = $stmtCats->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $category_id = (int)$_POST['category_id'];
    $summary_60 = trim($_POST['summary_60']);
    $summary_30 = trim($_POST['summary_30']);
    $full_content = trim($_POST['full_content']);
    
    // File upload logic
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (in_array($_FILES['image']['type'], $allowedTypes)) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('news_') . '.' . $ext;
            // The __DIR__ is c:\xampp\htdocs\niloy\admin, so going up one level gets us to \niloy
            $upload_path = dirname(__DIR__) . '/uploads/' . $filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image = $filename;
            } else {
                $message = "Failed to upload image.";
                $messageType = "error";
            }
        } else {
            $message = "Invalid image format. Only JPG, PNG, WEBP are allowed.";
            $messageType = "error";
        }
    }
    
    if (empty($message)) {
        if (!empty($title) && !empty($category_id) && !empty($summary_60) && !empty($summary_30) && !empty($full_content)) {
            $stmt = $pdo->prepare("INSERT INTO news (title, summary_60, summary_30, full_content, category_id, image) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$title, $summary_60, $summary_30, $full_content, $category_id, $image])) {
                $message = "News article created successfully!";
                $messageType = "success";
                // Redirect after successful submission to avoid resubmission
                header("Refresh: 2; url=manage_news.php");
            } else {
                $message = "Failed to create news article in database.";
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
    <h2 class="text-3xl font-bold text-space-indigo">Add New Article</h2>
</div>

<?php if ($message): ?>
    <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700 border border-green-400' : 'bg-red-100 text-red-700 border border-red-400'; ?>">
        <?php echo htmlspecialchars($message); ?>
        <?php if($messageType === 'success') echo '<br>Redirecting to manage news...'; ?>
    </div>
<?php endif; ?>

<div class="bg-white rounded-xl shadow-sm border border-almond-silk p-8 max-w-4xl">
    <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
        
        <!-- Header Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-6 border-b border-almond-silk">
            <div class="md:col-span-2">
                <label for="title" class="block text-sm font-bold text-space-indigo mb-2">Article Title *</label>
                <input type="text" id="title" name="title" required class="w-full bg-parchment border border-almond-silk rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-space-indigo transition-colors" placeholder="Keep it clear and objective. No clickbait.">
            </div>
            
            <div>
                <label for="category_id" class="block text-sm font-bold text-space-indigo mb-2">Category *</label>
                <select id="category_id" name="category_id" required class="w-full bg-parchment border border-almond-silk rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-space-indigo transition-colors">
                    <option value="">Select a Category</option>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label for="image" class="block text-sm font-bold text-space-indigo mb-2">Featured Image</label>
                <input type="file" id="image" name="image" accept="image/*" class="w-full bg-parchment border border-almond-silk rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-space-indigo file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-space-indigo file:text-parchment hover:file:bg-dusty-grape transition-colors">
            </div>
        </div>
        
        <!-- Content Section -->
        <div class="space-y-6 pt-2">
            <div>
                <label for="summary_60" class="block text-sm font-bold text-space-indigo mb-2">What Happened? (60-sec Summary) *</label>
                <p class="text-xs text-dusty-grape mb-2">Provide a short paragraph (2-3 lines max 120 words) explaining the main event.</p>
                <textarea id="summary_60" name="summary_60" rows="3" required class="w-full bg-parchment border border-almond-silk rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-space-indigo transition-colors"></textarea>
            </div>
            
            <div>
                <label for="summary_30" class="block text-sm font-bold text-space-indigo mb-2">30-Second Summary *</label>
                <p class="text-xs text-dusty-grape mb-2">Provide a highlighted, ultra-short summary (max 2 sentences).</p>
                <textarea id="summary_30" name="summary_30" rows="2" required class="w-full bg-parchment border border-almond-silk rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-space-indigo transition-colors"></textarea>
            </div>
            
            <div>
                <label for="full_content" class="block text-sm font-bold text-space-indigo mb-2">Why It Matters & Key Facts (Full Content) *</label>
                <p class="text-xs text-dusty-grape mb-2">Provide context, explanation, and bullet points of key facts. Use standard text Formatting (newlines will be preserved).</p>
                <textarea id="full_content" name="full_content" rows="10" required class="w-full bg-parchment border border-almond-silk rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-space-indigo transition-colors font-mono text-sm"></textarea>
            </div>
        </div>
        
        <div class="pt-6 border-t border-almond-silk flex justify-end">
            <button type="submit" class="bg-space-indigo text-parchment hover:bg-dusty-grape font-bold py-3 px-8 rounded-lg transition-colors shadow-md text-lg">
                Publish Article
            </button>
        </div>
        
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
