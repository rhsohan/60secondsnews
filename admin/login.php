<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = 'admin'");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Login success
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid credentials or you are not an admin.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - 60 Seconds News</title>
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
<body class="bg-parchment h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-8 border border-almond-silk">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-space-indigo">Admin Access</h1>
            <p class="text-dusty-grape mt-2">Sign in to manage 60 Seconds News</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 text-sm" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-6">
            <div>
                <label for="username" class="block text-sm font-medium text-space-indigo mb-2">Username</label>
                <input type="text" id="username" name="username" class="w-full bg-parchment border border-almond-silk rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-space-indigo transition-colors" required>
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-space-indigo mb-2">Password</label>
                <input type="password" id="password" name="password" class="w-full bg-parchment border border-almond-silk rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-space-indigo transition-colors" required>
            </div>
            <button type="submit" class="w-full bg-space-indigo text-parchment hover:bg-dusty-grape font-bold py-3 px-4 rounded-lg transition-colors shadow-md">
                Sign In
            </button>
        </form>
        
        <div class="mt-6 text-center text-xs text-lilac-ash">
            <p>&copy; <?php echo date('Y'); ?> 60 Seconds News. For authorized personnel only.</p>
        </div>
    </div>
</body>
</html>
