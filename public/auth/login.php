<?php
// auth/login.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/helpers.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ' . ADMIN_URL . '/index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $db = DB::getInstance()->getConnection();

        // Fetch user
        $stmt = $db->prepare("SELECT id, username, password_hash, role_id, login_attempts, lockout_time, status FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if ($user) {
            // Check lockout
            if ($user['status'] !== 'active') {
                $error = "This account is disabled or banned.";
            } elseif ($user['lockout_time'] && strtotime($user['lockout_time']) > time()) {
                $error = "Account is temporarily locked due to multiple failed login attempts. Try again later.";
            } else {
                if (password_verify($password, $user['password_hash'])) {
                    // Success login
                    session_regenerate_id(true); // Prevent session fixation
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role_id'] = $user['role_id'];

                    // Reset attempts & update last login
                    $update = $db->prepare("UPDATE users SET login_attempts = 0, lockout_time = NULL, last_login = NOW() WHERE id = ?");
                    $update->execute([$user['id']]);

                    // Audit log
                    $log = $db->prepare("INSERT INTO audit_logs (user_id, action, ip_address) VALUES (?, 'login', ?)");
                    $log->execute([$user['id'], $_SERVER['REMOTE_ADDR']]);

                    $redirect = $_SESSION['redirect_to'] ?? ADMIN_URL . '/index.php';
                    unset($_SESSION['redirect_to']);

                    set_flash_message('success', 'Welcome back, ' . e($user['username']) . '!');
                    header("Location: $redirect");
                    exit;
                } else {
                    // Failed attempt
                    $attempts = $user['login_attempts'] + 1;
                    $lockout = null;
                    if ($attempts >= 5) { // lockout after 5 attempts
                        $lockout = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                        $error = "Too many failed attempts. Account locked for 15 minutes.";
                    } else {
                        $error = "Invalid username or password.";
                    }
                    $update = $db->prepare("UPDATE users SET login_attempts = ?, lockout_time = ? WHERE id = ?");
                    $update->execute([$attempts, $lockout, $user['id']]);
                }
            }
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - 60 Second News CMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #121212;
            color: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        .login-card {
            background-color: #1e1e1e;
            border: 1px solid #333;
            border-radius: 10px;
            padding: 2rem;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .form-control {
            background-color: #2a2a2a;
            border-color: #444;
            color: #fff;
        }

        .form-control:focus {
            background-color: #333;
            border-color: #0d6efd;
            color: #fff;
            box-shadow: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #0d6efd, #004bbf);
            border: none;
        }
    </style>
</head>

<body>
    <div class="login-card">
        <h3 class="text-center mb-4 fw-bold">60Sec<span class="text-primary">News</span> CMS</h3>
        <?= display_flash_message(); ?>
        <?php if ($error): ?>
            <div class="alert alert-danger p-2 text-center">
                <?= e($error); ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="">
            <?= csrf_field(); ?>
            <div class="mb-3">
                <label class="form-label">Username or Email</label>
                <input type="text" name="username" class="form-control" required autofocus autocomplete="username">
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required autocomplete="current-password">
            </div>
            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary py-2 fw-bold">Sign In</button>
            </div>
        </form>
        <div class="text-center mt-3">
            <p class="text-muted small">Contact an administrator to get access.</p>
        </div>
    </div>
</body>

</html>