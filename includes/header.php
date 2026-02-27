<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Include config if not already included
require_once __DIR__ . '/config.php';

// Notification Logic
$stmtLastNews = $pdo->query("SELECT MAX(id) as max_id FROM news");
$latestNewsRow = $stmtLastNews->fetch();
$latest_news_id = $latestNewsRow['max_id'] ? (int)$latestNewsRow['max_id'] : 0;

$has_new_notification = false;
if (!isset($_COOKIE['last_seen_news_id'])) {
    // First time visitor, set cookie to current max
    setcookie('last_seen_news_id', (string)$latest_news_id, time() + (86400 * 30), "/"); 
} else {
    $last_seen = (int)$_COOKIE['last_seen_news_id'];
    if ($latest_news_id > $last_seen) {
        $has_new_notification = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>60 Seconds News - Smart News for Busy Minds</title>
    <meta name="description" content="Clear, accurate, structured news summaries designed for busy readers. Understand the full story in just 60 seconds.">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/niloy/assets/css/style.css">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'space-indigo': '#22223b',
                        'dusty-grape': '#4a4e69',
                        'lilac-ash': '#9a8c98',
                        'almond-silk': '#c9ada7',
                        'parchment': '#f2e9e4',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-parchment text-space-indigo antialiased min-h-screen flex flex-col">

<!-- Progress Bar (Used primarily on article page) -->
<div id="scrollProgressBar" class="fixed top-0 left-0 h-1 bg-dusty-grape z-50 w-0 transition-all duration-150"></div>

<!-- Sticky Header -->
<header class="bg-space-indigo text-white sticky top-0 z-40 shadow-md">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between h-16">
            <!-- Logo -->
            <a href="/niloy/index.php" class="flex items-center space-x-2">
                <div class="bg-parchment text-space-indigo font-bold rounded-full w-10 h-10 flex items-center justify-center text-xl">60</div>
                <div class="flex flex-col">
                    <span class="text-xl font-bold tracking-tight text-parchment leading-none">News</span>
                    <span class="text-[10px] text-lilac-ash hidden md:block uppercase tracking-widest mt-1">Smart summaries</span>
                </div>
            </a>
            
            <!-- Desktop Nav -->
            <nav class="hidden md:flex space-x-8 items-center">
                <a href="/niloy/index.php" class="hover:text-almond-silk transition-colors text-sm font-semibold uppercase tracking-wide">Home</a>
                
                <!-- News Dropdown -->
                <div class="relative group">
                    <!-- Added py-4 to expand the hover area of the trigger -->
                    <button class="flex items-center hover:text-almond-silk transition-colors text-sm font-semibold uppercase tracking-wide focus:outline-none py-4">
                        News
                        <svg class="w-4 h-4 ml-1 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <!-- Dropdown Menu Wrapper (pt-0 removes gap but keeps element right below trigger) -->
                    <div class="absolute left-0 top-full w-48 z-50 hidden group-hover:block opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <div class="bg-white rounded-md shadow-lg py-2 border border-almond-silk">
                            <a href="/niloy/category.php?id=1" class="block px-4 py-2 text-sm text-space-indigo hover:bg-parchment hover:text-dusty-grape transition-colors">World</a>
                            <a href="/niloy/category.php?id=2" class="block px-4 py-2 text-sm text-space-indigo hover:bg-parchment hover:text-dusty-grape transition-colors">Bangladesh</a>
                            <a href="/niloy/category.php?id=4" class="block px-4 py-2 text-sm text-space-indigo hover:bg-parchment hover:text-dusty-grape transition-colors">Business</a>
                            <a href="/niloy/category.php?id=5" class="block px-4 py-2 text-sm text-space-indigo hover:bg-parchment hover:text-dusty-grape transition-colors">Sports</a>
                            <a href="/niloy/category.php?id=6" class="block px-4 py-2 text-sm text-space-indigo hover:bg-parchment hover:text-dusty-grape transition-colors">Entertainment</a>
                        </div>
                    </div>
                </div>

                <a href="/niloy/contact.php" class="hover:text-almond-silk transition-colors text-sm font-semibold uppercase tracking-wide">Contact</a>
            </nav>
            
            <!-- Search & Actions -->
            <div class="hidden md:flex items-center space-x-6">
                <form action="/niloy/search.php" method="GET" class="relative">
                    <input type="text" name="q" placeholder="Search news..." class="bg-dusty-grape text-parchment placeholder-lilac-ash rounded-full py-1.5 px-4 focus:outline-none focus:ring-2 focus:ring-almond-silk text-sm border-none shadow-inner">
                    <button type="submit" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-parchment hover:text-almond-silk">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                </form>
                
                <!-- Notification Bell (Desktop) -->
                <div class="relative group">
                    <button onclick="clearNotifications(<?php echo $latest_news_id; ?>)" class="relative text-parchment hover:text-almond-silk transition-colors focus:outline-none py-4" title="Latest News">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        <?php if($has_new_notification): ?>
                            <span id="desktopNotifDot" class="absolute top-3 right-0 block h-2.5 w-2.5 rounded-full ring-2 ring-space-indigo bg-red-500"></span>
                        <?php endif; ?>
                    </button>
                    <!-- Notification Dropdown -->
                    <div class="absolute right-0 top-full w-80 z-50 hidden group-hover:block opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <div class="bg-white rounded-md shadow-xl border border-almond-silk overflow-hidden">
                            <div class="bg-space-indigo text-parchment px-4 py-3 font-semibold text-sm border-b border-dusty-grape flex justify-between items-center">
                                <span>Recent News</span>
                            </div>
                            <div class="max-h-80 overflow-y-auto">
                                <?php
                                $stmtNotif = $pdo->query("SELECT id, title, publish_date FROM news ORDER BY publish_date DESC LIMIT 5");
                                $recentNews = $stmtNotif->fetchAll();
                                if (count($recentNews) > 0):
                                    foreach($recentNews as $n):
                                ?>
                                    <a href="/niloy/article.php?id=<?php echo $n['id']; ?>" class="block px-4 py-3 border-b border-parchment hover:bg-parchment transition-colors last:border-0">
                                        <p class="text-sm text-space-indigo font-medium line-clamp-2"><?php echo htmlspecialchars($n['title']); ?></p>
                                        <p class="text-xs text-lilac-ash mt-1"><?php echo date('M d, H:i', strtotime($n['publish_date'])); ?></p>
                                    </a>
                                <?php 
                                    endforeach;
                                else:
                                ?>
                                    <div class="px-4 py-6 text-center text-dusty-grape text-sm">No recent news available.</div>
                                <?php endif; ?>
                            </div>
                            <a href="/niloy/index.php" class="block bg-parchment text-space-indigo text-center text-xs font-semibold py-2 hover:bg-almond-silk hover:text-white transition-colors">
                                View Homepage
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Mobile Actions (Bell + Menu Toggle) -->
            <div class="flex md:hidden items-center space-x-4">
                <!-- Desktop hidden trigger for mobile bell dot so JS doesn't break -->
                <div class="hidden">
                     <?php if($has_new_notification): ?>
                        <span id="mobileNotifDot" class="hidden"></span>
                    <?php endif; ?>
                </div>
                
                <!-- Mobile Menu Toggle -->
                <button id="mobileMenuBtn" class="text-parchment hover:text-almond-silk focus:outline-none p-1 rounded-md hover:bg-dusty-grape transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Mobile Nav -->
    <div id="mobileNav" class="md:hidden hidden bg-space-indigo border-t border-dusty-grape absolute w-full left-0 z-50 shadow-xl">
        <div class="container mx-auto px-4 py-4 flex flex-col space-y-4">
            <form action="/niloy/search.php" method="GET" class="mb-2 relative">
                <input type="text" name="q" placeholder="Search news..." class="w-full bg-dusty-grape text-parchment placeholder-lilac-ash rounded-lg py-2.5 px-4 focus:outline-none text-sm border border-lilac-ash border-opacity-30">
                <button type="submit" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-parchment">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </button>
            </form>
            
            <!-- Mobile Notifications Section -->
            <div class="py-1 border-b border-dusty-grape pb-3 mb-2">
                <div class="text-parchment font-medium flex items-center justify-between mb-2">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 text-lilac-ash" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        Recent News
                    </div>
                </div>
                <div class="flex flex-col space-y-2 mt-2">
                    <?php if (count($recentNews) > 0): ?>
                        <?php foreach(array_slice($recentNews, 0, 3) as $n): ?>
                        <a href="/niloy/article.php?id=<?php echo $n['id']; ?>" onclick="clearNotifications(<?php echo $latest_news_id; ?>)" class="bg-dusty-grape bg-opacity-30 rounded p-2 text-sm text-parchment hover:bg-opacity-50 transition-colors">
                            <span class="block font-medium truncate"><?php echo htmlspecialchars($n['title']); ?></span>
                            <span class="block text-xs text-lilac-ash mt-0.5"><?php echo date('M d', strtotime($n['publish_date'])); ?></span>
                        </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-sm text-lilac-ash italic px-2">No recent news.</div>
                    <?php endif; ?>
                </div>
            </div>

            <a href="/niloy/index.php" class="text-parchment font-medium hover:text-almond-silk py-1 flex items-center mt-2">
                <svg class="w-5 h-5 mr-3 text-lilac-ash" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                Home
            </a>
            
            <!-- Mobile News Categories -->
            <div class="py-1">
                <div class="text-parchment font-medium flex items-center mb-2">
                    <svg class="w-5 h-5 mr-3 text-lilac-ash" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                    News Categories
                </div>
                <div class="pl-8 flex flex-col space-y-3 mt-2 border-l-2 border-dusty-grape ml-2">
                    <a href="/niloy/category.php?id=1" class="text-lilac-ash hover:text-parchment text-sm">World</a>
                    <a href="/niloy/category.php?id=2" class="text-lilac-ash hover:text-parchment text-sm">Bangladesh</a>
                    <a href="/niloy/category.php?id=4" class="text-lilac-ash hover:text-parchment text-sm">Business</a>
                    <a href="/niloy/category.php?id=5" class="text-lilac-ash hover:text-parchment text-sm">Sports</a>
                    <a href="/niloy/category.php?id=6" class="text-lilac-ash hover:text-parchment text-sm">Entertainment</a>
                </div>
            </div>
            
            <a href="/niloy/contact.php" class="text-parchment font-medium hover:text-almond-silk py-1 flex items-center">
                <svg class="w-5 h-5 mr-3 text-lilac-ash" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                Contact
            </a>
        </div>
    </div>
</header>

<script>
function clearNotifications(latestId) {
    // Set cookie for 30 days
    document.cookie = "last_seen_news_id=" + latestId + "; path=/; max-age=" + (86400 * 30);
    // Hide dots immediately
    const desktopDot = document.getElementById('desktopNotifDot');
    const mobileDot = document.getElementById('mobileNotifDot');
    if (desktopDot) desktopDot.style.display = 'none';
    if (mobileDot) mobileDot.style.display = 'none';
}
</script>
<main class="flex-grow">
