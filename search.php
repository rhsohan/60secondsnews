<?php
require_once __DIR__ . '/includes/header.php';

$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';

// Fetch Search Results
$newsList = [];
if (!empty($searchQuery)) {
    $stmtSearch = $pdo->prepare("
        SELECT n.*, c.category_name 
        FROM news n
        JOIN categories c ON n.category_id = c.id
        WHERE n.title LIKE ? OR n.summary_60 LIKE ? OR n.full_content LIKE ?
        ORDER BY n.publish_date DESC
    ");
    $searchLike = "%{$searchQuery}%";
    $stmtSearch->execute([$searchLike, $searchLike, $searchLike]);
    $newsList = $stmtSearch->fetchAll();
}
?>

<!-- Search Header -->
<div class="bg-space-indigo text-parchment py-12 px-4 shadow-inner">
    <div class="container mx-auto max-w-4xl text-center fade-in">
        <h1 class="text-3xl md:text-4xl font-bold mb-4 tracking-tight">
            Search Results
        </h1>
        <p class="text-lilac-ash max-w-2xl mx-auto font-light">
            <?php if (!empty($searchQuery)): ?>
                Found <?php echo count($newsList); ?> results for "<strong><?php echo htmlspecialchars($searchQuery); ?></strong>"
            <?php else: ?>
                Please enter a search term to find news.
            <?php endif; ?>
        </p>
        
        <form action="/niloy/search.php" method="GET" class="mt-8 max-w-lg mx-auto relative">
            <input type="text" name="q" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Search news..." class="w-full bg-white text-space-indigo placeholder-lilac-ash rounded-full py-3 px-6 pr-12 focus:outline-none focus:ring-4 focus:ring-dusty-grape focus:border-transparent text-lg shadow-lg">
            <button type="submit" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-dusty-grape hover:text-space-indigo transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </button>
        </form>
    </div>
</div>

<section class="py-16 px-4 flex-grow">
    <div class="container mx-auto">
        <?php if (!empty($searchQuery) && count($newsList) > 0): ?>
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
        <?php elseif (!empty($searchQuery)): ?>
            <div class="text-center py-12">
                <svg class="w-16 h-16 text-lilac-ash mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <h3 class="text-2xl font-bold text-space-indigo mb-2">No results found</h3>
                <p class="text-dusty-grape">We couldn't find any articles matching "<?php echo htmlspecialchars($searchQuery); ?>".</p>
                <a href="/niloy/index.php" class="inline-block mt-6 px-6 py-2 bg-space-indigo text-parchment rounded-full hover:bg-dusty-grape transition-colors">Return to Home</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
