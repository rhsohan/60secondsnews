<?php
require_once __DIR__ . '/includes/header.php';

$message = '';
$messageType = '';

// Handle Delete News
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // First get image to delete file
    $stmtImg = $pdo->prepare("SELECT image FROM news WHERE id = ?");
    $stmtImg->execute([$id]);
    $img = $stmtImg->fetchColumn();
    
    $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
    if ($stmt->execute([$id])) {
        if ($img && file_exists(dirname(__DIR__) . '/uploads/' . $img)) {
            unlink(dirname(__DIR__) . '/uploads/' . $img);
        }
        $message = "News article deleted successfully.";
        $messageType = "success";
    } else {
        $message = "Failed to delete news article.";
        $messageType = "error";
    }
}

// Fetch all news
$stmtNews = $pdo->query("
    SELECT n.id, n.title, n.publish_date, n.read_count, c.category_name 
    FROM news n 
    JOIN categories c ON n.category_id = c.id 
    ORDER BY n.publish_date DESC
");
$newsList = $stmtNews->fetchAll();
?>

<div class="flex justify-between items-center mb-8">
    <h2 class="text-3xl font-bold text-space-indigo">Manage News</h2>
    <a href="add_news.php" class="bg-space-indigo text-parchment hover:bg-dusty-grape font-bold py-2 px-6 rounded-lg transition-colors shadow-sm flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        Add New Article
    </a>
</div>

<?php if ($message): ?>
    <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700 border border-green-400' : 'bg-red-100 text-red-700 border border-red-400'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="bg-white rounded-xl shadow-sm border border-almond-silk overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-parchment text-dusty-grape text-sm">
                    <th class="py-3 px-6 font-medium w-16">ID</th>
                    <th class="py-3 px-6 font-medium">Title</th>
                    <th class="py-3 px-6 font-medium">Category</th>
                    <th class="py-3 px-6 font-medium">Reads</th>
                    <th class="py-3 px-6 font-medium">Publish Date</th>
                    <th class="py-3 px-6 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php if (count($newsList) > 0): ?>
                    <?php foreach($newsList as $news): ?>
                        <tr class="border-b border-almond-silk hover:bg-gray-50 transition-colors">
                            <td class="py-4 px-6 text-dusty-grape"><?php echo $news['id']; ?></td>
                            <td class="py-4 px-6 font-medium text-space-indigo truncate max-w-xs" title="<?php echo htmlspecialchars($news['title']); ?>">
                                <?php echo htmlspecialchars($news['title']); ?>
                            </td>
                            <td class="py-4 px-6 text-dusty-grape">
                                <span class="bg-almond-silk bg-opacity-50 text-space-indigo px-2 py-1 rounded text-xs font-semibold">
                                    <?php echo htmlspecialchars($news['category_name']); ?>
                                </span>
                            </td>
                            <td class="py-4 px-6 text-dusty-grape"><?php echo number_format($news['read_count']); ?></td>
                            <td class="py-4 px-6 text-lilac-ash whitespace-nowrap"><?php echo date('M j, Y', strtotime($news['publish_date'])); ?></td>
                            <td class="py-4 px-6 text-right space-x-2 whitespace-nowrap">
                                <a href="../article.php?id=<?php echo $news['id']; ?>" target="_blank" class="bg-gray-200 text-gray-700 hover:bg-gray-300 px-3 py-1 rounded transition-colors text-xs font-semibold">
                                    View
                                </a>
                                <a href="edit_news.php?id=<?php echo $news['id']; ?>" class="bg-almond-silk text-space-indigo hover:bg-dusty-grape hover:text-parchment px-3 py-1 rounded transition-colors text-xs font-semibold">
                                    Edit
                                </a>
                                <a href="?delete=<?php echo $news['id']; ?>" onclick="return confirm('Are you sure you want to delete this news article?')" class="bg-red-100 text-red-600 hover:bg-red-600 hover:text-white px-3 py-1 rounded transition-colors text-xs font-semibold">
                                    Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="py-8 text-center text-dusty-grape">No news articles found. <a href="add_news.php" class="text-space-indigo font-bold hover:underline">Create one</a>!</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
