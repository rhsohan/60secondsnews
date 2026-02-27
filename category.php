<?php
require_once __DIR__ . '/includes/header.php';

$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch Category Details
$stmtCat = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmtCat->execute([$category_id]);
$category = $stmtCat->fetch();

if (!$category) {
    echo "<div class='container mx-auto px-4 py-20 text-center'><h1 class='text-2xl text-space-indigo font-bold'>Category not found.</h1><a href='/niloy/index.php' class='text-dusty-grape hover:underline mt-4 inline-block'>Return Home</a></div>";
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Fetch News in Category
$stmtNews = $pdo->prepare("
    SELECT n.*, c.category_name 
    FROM news n
    JOIN categories c ON n.category_id = c.id
    WHERE n.category_id = ?
    ORDER BY n.publish_date DESC
");
$stmtNews->execute([$category_id]);
$newsList = $stmtNews->fetchAll();
?>

<!-- Category Header -->
<div class="bg-space-indigo text-parchment py-12 px-4 shadow-inner">
    <div class="container mx-auto max-w-4xl text-center fade-in">
        <h1 class="text-4xl md:text-5xl font-bold mb-4 tracking-tight">
            <?php echo htmlspecialchars($category['category_name']); ?> News
        </h1>
        <p class="text-lilac-ash max-w-2xl mx-auto font-light">
            Latest 60-second updates from the <?php echo htmlspecialchars($category['category_name']); ?> section.
        </p>
    </div>
</div>

<section class="py-16 px-4">
    <div class="container mx-auto">
        <?php if (count($newsList) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 fade-in">
                <?php foreach($newsList as $news): ?>
                    <article class="bg-almond-silk rounded-xl overflow-hidden shadow-md hover:shadow-xl transition-all flex flex-col">
                        <?php if($news['image']): ?>
                            <div class="h-48 overflow-hidden">
                                <img src="/niloy/uploads/<?php echo htmlspecialchars($news['image']); ?>" alt="News Image" class="w-full h-full object-cover transform hover:scale-105 transition-transform duration-500">
                            </div>
                        <?php else: ?>
                            <div class="h-48 bg-dusty-grape flex items-center justify-center text-parchment opacity-80">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            </div>
                        <?php endif; ?>
                        
                        <div class="p-6 flex flex-col flex-grow">
                            <div class="flex justify-between items-center mb-3">
                                <span class="text-xs font-semibold uppercase tracking-wider text-space-indigo bg-parchment px-2 py-1 rounded">
                                    <?php echo htmlspecialchars($news['category_name']); ?>
                                </span>
                                <div class="flex items-center space-x-3 text-xs text-dusty-grape font-medium">
                                    <span title="Read time">⏱️ 60 sec read</span>
                                    <span title="Total reads">👁 <?php echo number_format($news['read_count']); ?></span>
                                </div>
                            </div>
                            
                            <h3 class="text-xl font-bold text-space-indigo mb-3 leading-snug">
                                <a href="/niloy/article.php?id=<?php echo $news['id']; ?>" class="hover:text-dusty-grape transition-colors">
                                    <?php echo htmlspecialchars($news['title']); ?>
                                </a>
                            </h3>
                            
                            <p class="text-sm text-dusty-grape mb-6 flex-grow leading-relaxed">
                                <?php 
                                    $words = explode(' ', htmlspecialchars($news['summary_60']));
                                    if(count($words) > 120) {
                                        echo implode(' ', array_slice($words, 0, 120)) . '...';
                                    } else {
                                        echo htmlspecialchars($news['summary_60']);
                                    }
                                ?>
                            </p>
                            
                            <a href="/niloy/article.php?id=<?php echo $news['id']; ?>" class="inline-block w-full text-center bg-space-indigo text-parchment hover:bg-dusty-grape py-2 rounded-lg font-medium transition-colors text-sm mt-auto">
                                Read in 60 Seconds
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <svg class="w-16 h-16 text-lilac-ash mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                <h3 class="text-2xl font-bold text-space-indigo mb-2">No articles found</h3>
                <p class="text-dusty-grape">We haven't published any news in this category yet.</p>
                <a href="/niloy/index.php" class="inline-block mt-6 px-6 py-2 bg-space-indigo text-parchment rounded-full hover:bg-dusty-grape transition-colors">Browse other news</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
