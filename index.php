<?php
require_once __DIR__ . '/includes/header.php';

// Fetch Latest 6 News
$stmtLatest = $pdo->prepare("
    SELECT n.*, c.category_name 
    FROM news n
    JOIN categories c ON n.category_id = c.id
    ORDER BY n.publish_date DESC
    LIMIT 6
");
$stmtLatest->execute();
$latestNews = $stmtLatest->fetchAll();

// Fetch Top 5 Trending News (by read_count)
$stmtTrending = $pdo->prepare("
    SELECT n.id, n.title, n.publish_date, n.read_count, c.category_name
    FROM news n
    JOIN categories c ON n.category_id = c.id
    ORDER BY n.read_count DESC
    LIMIT 5
");
$stmtTrending->execute();
$trendingNews = $stmtTrending->fetchAll();
?>

<!-- Hero Section -->
<section class="bg-hero-gradient text-parchment py-20 px-4">
    <div class="container mx-auto max-w-4xl text-center fade-in">
        <h1 class="text-4xl md:text-5xl font-bold mb-6 tracking-tight leading-tight">News Without Noise.<br>Just the Facts.</h1>
        <p class="text-lg md:text-xl text-lilac-ash max-w-2xl mx-auto mb-10 font-light">
            Clear, accurate, structured news summaries designed for busy readers.
        </p>
        <a href="#featured" class="inline-block bg-parchment text-space-indigo hover:bg-almond-silk font-semibold py-3 px-8 rounded-full transition-all shadow-lg transform hover:-translate-y-1">
            Read Today's Top Stories
        </a>
    </div>
</section>

<!-- Main Content Area -->
<section id="featured" class="py-16 px-4">
    <div class="container mx-auto">
        <div class="flex flex-col lg:flex-row gap-12">
            
            <!-- Featured News Grid -->
            <div class="lg:w-2/3">
                <h2 class="text-3xl font-bold text-space-indigo border-b-2 border-almond-silk pb-2 mb-8 inline-block">Featured News</h2>
                
                <?php if (count($latestNews) > 0): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <?php foreach($latestNews as $news): ?>
                            <article class="bg-almond-silk rounded-xl overflow-hidden shadow-md hover:shadow-xl transition-all flex flex-col fade-in">
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
                                            // Ensure max 120 words for 60-second summary
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
                    <p class="text-dusty-grape">No news articles found. Please check back later.</p>
                <?php endif; ?>
            </div>
            
            <!-- Trending Sidebar -->
            <aside class="lg:w-1/3">
                <div class="bg-parchment border border-almond-silk rounded-xl p-6 shadow-sm sticky top-24 fade-in">
                    <div class="flex items-center space-x-2 mb-6 border-b border-almond-silk pb-3">
                        <svg class="w-6 h-6 text-dusty-grape" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        <h2 class="text-2xl font-bold text-space-indigo">Most Read News</h2>
                    </div>
                    
                    <?php if (count($trendingNews) > 0): ?>
                        <div class="space-y-6">
                            <?php foreach($trendingNews as $index => $trend): ?>
                                <article class="flex space-x-4 group">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-space-indigo text-parchment flex items-center justify-center font-bold text-sm">
                                        <?php echo $index + 1; ?>
                                    </div>
                                    <div>
                                        <a href="/niloy/article.php?id=<?php echo $trend['id']; ?>">
                                            <h3 class="text-space-indigo font-semibold group-hover:text-dusty-grape transition-colors leading-tight mb-1">
                                                <?php echo htmlspecialchars($trend['title']); ?>
                                            </h3>
                                        </a>
                                        <div class="flex items-center justify-between mt-2">
                                            <span class="text-xs text-dusty-grape uppercase font-medium"><?php echo htmlspecialchars($trend['category_name']); ?></span>
                                            <span class="text-xs text-lilac-ash font-medium flex items-center">
                                                👁 <?php echo number_format($trend['read_count']); ?> reads
                                            </span>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-sm text-dusty-grape">No trending news right now.</p>
                    <?php endif; ?>
                </div>
            </aside>
            
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
