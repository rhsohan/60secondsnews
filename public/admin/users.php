<?php
// admin/users.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/helpers.php';
require_once __DIR__ . '/../../app/rbac.php';

require_login();
require_permission('manage_users');

$db = DB::getInstance()->getConnection();

// Handle User Deletion/Status change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    if ($_POST['action'] === 'delete_user') {
        $user_id = (int) $_POST['user_id'];
        if ($user_id !== (int) $_SESSION['user_id'] && $user_id !== 1) {
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            log_activity("Deleted user ID $user_id", 'users', $user_id);
            set_flash_message('success', "User deleted successfully.");
        } else {
            set_flash_message('danger', "Cannot delete this user.");
        }
        header('Location: users.php');
        exit;
    }

    if ($_POST['action'] === 'toggle_status') {
        $user_id = (int) $_POST['user_id'];
        $new_status = $_POST['status'] === 'active' ? 'banned' : 'active';

        if ($user_id !== (int) $_SESSION['user_id'] && $user_id !== 1) { // Prevent locking self or superadmin
            if ($_POST['status'] === 'inactive') {
                $new_status = 'active';
            } else {
                $new_status = $_POST['status'] === 'active' ? 'banned' : 'active';
            }
            $stmt = $db->prepare("UPDATE users SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $user_id]);
            log_activity("Changed user status to $new_status", 'users', $user_id);
            if ($_POST['status'] === 'inactive' && $new_status === 'active') {
                set_flash_message('success', "User approved successfully.");
            } else {
                set_flash_message('success', "User status updated.");
            }
        } else {
            set_flash_message('danger', "Cannot modify status for this user.");
        }
        header('Location: users.php');
        exit;
    }
}

require_once __DIR__ . '/layout/header.php';

// Fetch Users with Roles
$users = $db->query("
    SELECT u.id, u.username, u.email, u.status, u.created_at, r.name as role_name 
    FROM users u 
    LEFT JOIN roles r ON u.role_id = r.id 
    ORDER BY u.id ASC
")->fetchAll();

?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-white">Manage Users</h1>
    <a href="user_edit.php" class="btn btn-primary"><i class="bi bi-person-plus"></i> Add New User</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <?= $user['id'] ?>
                            </td>
                            <td>
                                <?= e($user['username']) ?>
                            </td>
                            <td>
                                <?= e($user['email']) ?>
                            </td>
                            <td><span class="badge bg-secondary">
                                    <?= e($user['role_name']) ?>
                                </span></td>
                            <td>
                                <?php if ($user['status'] == 'active'): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php elseif ($user['status'] == 'inactive'): ?>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> Pending
                                        Approval</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Banned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= date('M j, Y', strtotime($user['created_at'])) ?>
                            </td>
                            <td class="text-end">
                                <?php if ($user['id'] != 1): // Cannot edit superadmin directly ?>
                                    <a href="user_edit.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-outline-info me-1"><i
                                            class="bi bi-pencil"></i></a>

                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <form method="POST" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <input type="hidden" name="status" value="<?= $user['status'] ?>">
                                            <?php if ($user['status'] == 'inactive'): ?>
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Approve User"
                                                    onclick="return confirm('Are you sure you want to approve this user?');">
                                                    <i class="bi bi-check-lg"></i> Approve
                                                </button>
                                            <?php else: ?>
                                                <button type="submit"
                                                    class="btn btn-sm <?= $user['status'] == 'active' ? 'btn-outline-warning' : 'btn-outline-success' ?>"
                                                    title="Toggle Status"
                                                    onclick="return confirm('Are you sure you want to change this user\'s status?');">
                                                    <i class="bi <?= $user['status'] == 'active' ? 'bi-ban' : 'bi-check-circle' ?>"></i>
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                        <form method="POST" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete User"
                                                onclick="return confirm('Are you sure you want to PERMANENTLY delete this user? All their data will be removed.');">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layout/footer.php'; ?>


