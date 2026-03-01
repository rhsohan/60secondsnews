<?php
// admin/user_edit.php
require_once __DIR__ . '/layout/header.php';

require_permission('manage_users');

$db = DB::getInstance()->getConnection();
$user_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$user = null;

if ($user_id > 0) {
    if ($user_id === 1 && $_SESSION['user_id'] !== 1) {
        set_flash_message('danger', 'Only Superadmin can edit this account.');
        header('Location: users.php');
        exit;
    }

    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        set_flash_message('danger', 'User not found.');
        header('Location: users.php');
        exit;
    }
}

$roles = $db->query("SELECT * FROM roles ORDER BY id ASC")->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role_id = (int) $_POST['role_id'];
    $password = $_POST['password'];

    if (empty($username) || empty($email) || empty($role_id)) {
        $error = "Please fill in all required fields.";
    } else {
        try {
            if ($user_id > 0) {
                // Update
                if (!empty($password)) {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("UPDATE users SET username=?, email=?, role_id=?, password_hash=? WHERE id=?");
                    $stmt->execute([$username, $email, $role_id, $hash, $user_id]);
                } else {
                    $stmt = $db->prepare("UPDATE users SET username=?, email=?, role_id=? WHERE id=?");
                    $stmt->execute([$username, $email, $role_id, $user_id]);
                }
                log_activity("Updated user ($username)", 'users', $user_id);
                set_flash_message('success', 'User updated successfully.');
            } else {
                // Create
                if (empty($password)) {
                    $error = "Password is required for new users.";
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("INSERT INTO users (username, email, role_id, password_hash) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$username, $email, $role_id, $hash]);
                    $new_id = $db->lastInsertId();
                    log_activity("Created user ($username)", 'users', $new_id);
                    set_flash_message('success', 'User created successfully.');
                    header('Location: users.php');
                    exit;
                }
            }
            if (empty($error)) {
                header('Location: users.php');
                exit;
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "Username or Email already exists.";
            } else {
                $error = "Database error occurred.";
            }
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-white">
        <?= $user_id ? 'Edit User' : 'Add New User' ?>
    </h1>
    <a href="users.php" class="btn btn-outline-light"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="card" style="max-width: 600px;">
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger px-3 py-2">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label text-muted">Username *</label>
                <input type="text" name="username" class="form-control bg-dark text-white border-secondary"
                    value="<?= e($_POST['username'] ?? $user['username'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label text-muted">Email *</label>
                <input type="email" name="email" class="form-control bg-dark text-white border-secondary"
                    value="<?= e($_POST['email'] ?? $user['email'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label text-muted">Role *</label>
                <select name="role_id" class="form-select bg-dark text-white border-secondary" required>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= $role['id'] ?>" <?= (($user['role_id'] ?? '') == $role['id']) ? 'selected' : '' ?>>
                            <?= e($role['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label text-muted">Password
                    <?= $user_id ? '(Leave blank to keep current)' : '*' ?>
                </label>
                <input type="password" name="password" class="form-control bg-dark text-white border-secondary"
                    <?= $user_id ? '' : 'required' ?>>
            </div>

            <button type="submit" class="btn btn-primary w-100">Save User</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/layout/footer.php'; ?>


