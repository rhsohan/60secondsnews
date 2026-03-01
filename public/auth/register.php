<?php
// auth/register.php — Public registration is disabled.
// User accounts are created exclusively by administrators.
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/helpers.php';

set_flash_message('info', 'Account registration is not available. Please contact an administrator for access.');
header('Location: login.php');
exit;

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Basic Validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = "Username can only contain letters, numbers, and underscores.";
    } else {
        $db = DB::getInstance()->getConnection();

        // Check if username or email already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error = "Username or email is already taken.";
        } else {
            // Hash password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Default role is Writer (4), status is 'inactive' by default
            try {
                $insert = $db->prepare("INSERT INTO users (username, email, password_hash, role_id, status) VALUES (?, ?, ?, 4, 'inactive')");
                $insert->execute([$username, $email, $password_hash]);

                $success = "Registration successful! Your account is pending admin approval. You will be able to log in once approved.";

                // Clear fields on success
                $username = '';
                $email = '';

            } catch (PDOException $e) {
                // Log detail secretly, show generic error
                error_log("Registration Error: " . $e->getMessage());
                $error = "An error occurred during registration. Please try again later.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - 60 Second News</title>
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
            max-width: 450px;
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
    <div class="login-card my-4 overflow-auto" style="max-height: 90vh;">
        <h3 class="text-center mb-4 fw-bold">Join 60Sec<span class="text-primary">News</span></h3>

        <?php if ($success): ?>
            <div class="alert alert-success p-3 text-center mb-4">
                <i class="bi bi-check-circle-fill fs-4 d-block mb-2"></i>
                <?= e($success); ?>
                <div class="mt-3">
                    <a href="login.php" class="btn btn-outline-success btn-sm">Return to Login</a>
                </div>
            </div>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="alert alert-danger p-2 text-center">
                    <?= e($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <?= csrf_field(); ?>
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" value="<?= e($username ?? '') ?>" required
                        autofocus>
                    <div class="form-text text-muted small">Letters, numbers, and underscores only.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" value="<?= e($email ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required minlength="8">
                </div>
                <div class="mb-4">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" required minlength="8">
                </div>
                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary py-2 fw-bold">Sign Up as Writer</button>
                </div>
            </form>

            <div class="text-center mt-4">
                <p class="text-muted small">Already part of the newsroom? <a href="login.php"
                        class="text-primary text-decoration-none fw-bold">Log in here</a>.</p>
            </div>
        <?php endif; ?>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</body>

</html>