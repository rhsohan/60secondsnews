<?php
require_once __DIR__ . '/includes/header.php';

// Fetch Quick Stats
$stmtNewsCount = $pdo->query("SELECT COUNT(*) FROM news");
$totalNews = $stmtNewsCount->fetchColumn();

$stmtCatsCount = $pdo->query("SELECT COUNT(*) FROM categories");
$totalCats = $stmtCatsCount->fetchColumn();

$stmtReadsCount = $pdo->query("SELECT SUM(read_count) FROM news");
$totalReads = $stmtReadsCount->fetchColumn();

$stmtRecentNews = $pdo->query("
    SELECT n.title, n.publish_date, c.category_name 
    FROM news n 
    JOIN categories c ON n.category_id = c.id 
    ORDER BY n.publish_date DESC 
    LIMIT 5
");
$recentNews = $stmtRecentNews->fetchAll();
?>

<div class="flex justify-between items-center mb-8">
    <h2 class="text-3xl font-bold text-space-indigo">Dashboard Overview</h2>
    <div class="text-dusty-grape">
        Welcome back, <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong>!
    </div>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-almond-silk p-6 flex items-center justify-between">
        <div>
            <p class="text-dusty-grape text-sm font-medium mb-1">Total News Articles</p>
            <p class="text-3xl font-bold text-space-indigo"><?php echo number_format($totalNews); ?></p>
        </div>
        <div class="bg-almond-silk p-4 rounded-lg text-space-indigo">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm border border-almond-silk p-6 flex items-center justify-between">
        <div>
            <p class="text-dusty-grape text-sm font-medium mb-1">Total Categories</p>
            <p class="text-3xl font-bold text-space-indigo"><?php echo number_format($totalCats); ?></p>
        </div>
        <div class="bg-almond-silk p-4 rounded-lg text-space-indigo">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm border border-almond-silk p-6 flex items-center justify-between">
        <div>
            <p class="text-dusty-grape text-sm font-medium mb-1">Total Views</p>
            <p class="text-3xl font-bold text-space-indigo"><?php echo number_format($totalReads ?: 0); ?></p>
        </div>
        <div class="bg-almond-silk p-4 rounded-lg text-space-indigo">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
        </div>
    </div>
</div>

<!-- Recent News Table -->
<div class="bg-white rounded-xl shadow-sm border border-almond-silk overflow-hidden">
    <div class="p-6 border-b border-almond-silk flex justify-between items-center bg-gray-50">
        <h3 class="text-lg font-bold text-space-indigo">Recently Added News</h3>
        <a href="manage_news.php" class="text-sm text-space-indigo font-medium hover:text-dusty-grape">View All News &rarr;</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-parchment text-dusty-grape text-sm">
                    <th class="py-3 px-6 font-medium">Title</th>
                    <th class="py-3 px-6 font-medium">Category</th>
                    <th class="py-3 px-6 font-medium">Publish Date</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php if (count($recentNews) > 0): ?>
                    <?php foreach($recentNews as $news): ?>
                        <tr class="border-b border-almond-silk hover:bg-gray-50 transition-colors">
                            <td class="py-4 px-6 font-medium text-space-indigo"><?php echo htmlspecialchars($news['title']); ?></td>
                            <td class="py-4 px-6 text-dusty-grape">
                                <span class="bg-almond-silk bg-opacity-50 text-space-indigo px-2 py-1 rounded text-xs font-semibold">
                                    <?php echo htmlspecialchars($news['category_name']); ?>
                                </span>
                            </td>
                            <td class="py-4 px-6 text-lilac-ash"><?php echo date('M j, Y g:i A', strtotime($news['publish_date'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="py-8 text-center text-dusty-grape">No news articles found. Start publishing!</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
