<?php
require_once __DIR__ . '/includes/header.php';

// Get article ID
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id === 0) {
    echo "<div class='container mx-auto px-4 py-20 text-center'><h1 class='text-2xl text-space-indigo font-bold'>Article not found.</h1><a href='/index.php' class='text-dusty-grape hover:underline mt-4 inline-block'>Return Home</a></div>";
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Increment Read Count
$stmtUpdate = $pdo->prepare("UPDATE news SET read_count = read_count + 1 WHERE id = ?");
$stmtUpdate->execute([$id]);

// Fetch Article Details
$stmt = $pdo->prepare("
    SELECT n.*, c.category_name 
    FROM news n
    JOIN categories c ON n.category_id = c.id
    WHERE n.id = ?
");
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article) {
    echo "<div class='container mx-auto px-4 py-20 text-center'><h1 class='text-2xl text-space-indigo font-bold'>Article not found.</h1><a href='/index.php' class='text-dusty-grape hover:underline mt-4 inline-block'>Return Home</a></div>";
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Fetch Top Trending News for Sidebar
$stmtTrending = $pdo->prepare("
    SELECT n.id, n.title, n.publish_date, n.read_count, c.category_name
    FROM news n
    JOIN categories c ON n.category_id = c.id
    WHERE n.id != ?
    ORDER BY n.read_count DESC
    LIMIT 5
");
$stmtTrending->execute([$id]);
$trendingNews = $stmtTrending->fetchAll();
?>

<!-- Article Header -->
<div class="bg-parchment py-8 px-4 border-b border-almond-silk fade-in">
    <div class="container mx-auto max-w-4xl">
        <div class="mb-4">
            <a href="javascript:history.back()"
                class="inline-flex items-center text-dusty-grape hover:text-space-indigo text-sm font-medium transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back
            </a>
        </div>

        <div class="flex items-center space-x-3 mb-4">
            <span class="bg-space-indigo text-parchment text-xs font-bold uppercase tracking-wider py-1 px-3 rounded">
                <?php echo htmlspecialchars($article['category_name']); ?>
            </span>
            <span class="text-sm text-lilac-ash font-medium">
                <?php echo date('F j, Y', strtotime($article['publish_date'])); ?>
            </span>
        </div>

        <h1 class="text-3xl md:text-5xl font-bold text-space-indigo leading-tight mb-6">
            <?php echo htmlspecialchars($article['title']); ?>
        </h1>

        <div class="flex items-center justify-between border-t border-almond-silk pt-4">
            <div class="flex items-center text-dusty-grape text-sm font-medium space-x-4">
                <span title="Read time">⏱️ 60 sec read</span>
                <span id="articleReadCount" title="Total reads"
                    class="flex items-center transition-all bg-almond-silk px-2 py-1 rounded-md text-space-indigo">
                    👁 <span class="ml-1"><?php echo number_format($article['read_count']); ?> reads</span>
                </span>
            </div>

            <!-- Dummy Share Buttons -->
            <div class="flex space-x-2">
                <button
                    class="w-8 h-8 rounded-full bg-almond-silk text-space-indigo flex items-center justify-center hover:bg-dusty-grape hover:text-parchment transition-colors"
                    title="Share on Twitter" aria-label="Share on Twitter">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
                    </svg>
                </button>
                <button
                    class="w-8 h-8 rounded-full bg-almond-silk text-space-indigo flex items-center justify-center hover:bg-dusty-grape hover:text-parchment transition-colors"
                    title="Share on Facebook" aria-label="Share on Facebook">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Area -->
<section class="py-12 px-4">
    <div class="container mx-auto">
        <div class="flex flex-col lg:flex-row gap-12">

            <!-- Article Content -->
            <article class="lg:w-2/3 fade-in">
                <?php if ($article['image']): ?>
                    <div class="mb-10 rounded-xl overflow-hidden shadow-md max-h-[500px]">
                        <img src="<?php echo BASE_URL; ?>/uploads/<?php echo htmlspecialchars($article['image']); ?>"
                            alt="Article Image" class="w-full h-full object-cover">
                    </div>
                <?php endif; ?>

                <div class="prose prose-lg max-w-none text-space-indigo">

                    <!-- 30 Second Summary Box -->
                    <div class="bg-almond-silk border-l-4 border-space-indigo p-6 rounded-r-xl mb-10 shadow-sm">
                        <div class="flex items-center mb-3 text-space-indigo">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            <h2 class="text-xl font-bold m-0">30-Second Summary</h2>
                        </div>
                        <p class="text-space-indigo m-0 font-medium">
                            <?php echo nl2br(htmlspecialchars($article['summary_30'])); ?>
                        </p>
                    </div>

                    <!-- What Happened -->
                    <h2 class="text-2xl font-bold text-space-indigo mt-8 mb-4 border-b border-almond-silk pb-2">What
                        Happened?</h2>
                    <p class="leading-relaxed mb-8">
                        <?php echo nl2br(htmlspecialchars($article['summary_60'])); ?>
                    </p>

                    <!-- Full Content / Why It Matters -->
                    <div class="bg-parchment rounded-xl p-0">
                        <?php
                        // The full_content might include HTML if using an editor block, or just text.
                        // We will assume it might have basic text and bullet points.
                        // For security, if it's plain text from a textarea, we use nl2br and htmlspecialchars
                        // But realistic news articles need HTML. For this assignment, we use nl2br(htmlspecialchars()).
                        echo nl2br(htmlspecialchars($article['full_content']));
                        ?>
                    </div>

                </div>
            </article>

            <!-- Trending Sidebar -->
            <aside class="lg:w-1/3">
                <div class="bg-parchment border border-almond-silk rounded-xl p-6 shadow-sm sticky top-24 fade-in">
                    <div class="flex items-center space-x-2 mb-6 border-b border-almond-silk pb-3">
                        <svg class="w-6 h-6 text-dusty-grape" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                        <h2 class="text-2xl font-bold text-space-indigo">Most Read News</h2>
                    </div>

                    <?php if (count($trendingNews) > 0): ?>
                        <div class="space-y-6">
                            <?php foreach ($trendingNews as $index => $trend): ?>
                                <article class="flex space-x-4 group">
                                    <div
                                        class="flex-shrink-0 w-8 h-8 rounded-full bg-space-indigo text-parchment flex items-center justify-center font-bold text-sm">
                                        <?php echo $index + 1; ?>
                                    </div>
                                    <div>
                                        <a href="<?php echo BASE_URL; ?>/article.php?id=<?php echo $trend['id']; ?>">
                                            <h3
                                                class="text-space-indigo font-semibold group-hover:text-dusty-grape transition-colors leading-tight mb-1">
                                                <?php echo htmlspecialchars($trend['title']); ?>
                                            </h3>
                                        </a>
                                        <div class="flex items-center justify-between mt-2">
                                            <span
                                                class="text-xs text-dusty-grape uppercase font-medium"><?php echo htmlspecialchars($trend['category_name']); ?></span>
                                            <span class="text-xs text-lilac-ash font-medium flex items-center">
                                                👁 <?php echo number_format($trend['read_count']); ?> reads
                                            </span>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-sm text-dusty-grape">No other trending news right now.</p>
                    <?php endif; ?>
                </div>
            </aside>

        </div>
    </div>
</section>

<!-- Read counter animation via JS -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const counterEl = document.getElementById('articleReadCount');
        if (counterEl) {
            counterEl.classList.add('transform', 'scale-110', 'bg-dusty-grape', 'text-parchment');
            setTimeout(() => {
                counterEl.classList.remove('scale-110', 'bg-dusty-grape', 'text-parchment');
            }, 600);
        }
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>