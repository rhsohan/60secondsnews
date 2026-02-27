<?php
session_start();
// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
require_once __DIR__ . '/../../includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - 60 Seconds News</title>
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
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-parchment font-sans antialiased text-space-indigo">
    <div class="flex h-screen overflow-hidden">
        
        <!-- Sidebar -->
        <aside class="w-64 bg-space-indigo text-parchment flex flex-col h-full flex-shrink-0">
            <div class="p-6 border-b border-dusty-grape flex-shrink-0">
                <h1 class="text-xl font-bold tracking-tight">60 Seconds News</h1>
                <p class="text-xs text-lilac-ash mt-1">Admin Panel</p>
            </div>
            <nav class="flex-grow p-4 space-y-2 overflow-y-auto">
                <a href="index.php" class="block py-2.5 px-4 rounded transition-colors hover:bg-dusty-grape bg-dusty-grape bg-opacity-20 flex items-center">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Dashboard
                </a>
                <a href="categories.php" class="block py-2.5 px-4 rounded transition-colors hover:bg-dusty-grape flex items-center">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                    Categories
                </a>
                <a href="manage_news.php" class="block py-2.5 px-4 rounded transition-colors hover:bg-dusty-grape flex items-center">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                    News Articles
                </a>
                <a href="messages.php" class="block py-2.5 px-4 rounded transition-colors hover:bg-dusty-grape flex items-center">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                    Messages
                </a>
                <a href="../index.php" target="_blank" class="block py-2.5 px-4 rounded transition-colors hover:bg-dusty-grape flex items-center mt-8 text-lilac-ash border-t border-dusty-grape pt-4">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    View Homepage
                </a>
            </nav>
            <div class="p-4 border-t border-dusty-grape flex-shrink-0">
                <a href="logout.php" class="block py-2 px-4 bg-red-600 hover:bg-red-700 text-white rounded text-center transition-colors text-sm font-bold">
                    Logout
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-grow bg-parchment h-full overflow-y-auto">
            <div class="p-8">
