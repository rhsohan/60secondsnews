<?php
// admin/logs.php
require_once __DIR__ . '/layout/header.php';

require_permission('view_logs');
$db = DB::getInstance()->getConnection();

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$per_page = 50;
$offset = ($page - 1) * $per_page;

$total_logs = $db->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn();
$total_pages = ceil($total_logs / $per_page);

$logs = $db->query("
    SELECT a.*, u.username 
    FROM audit_logs a 
    JOIN users u ON a.user_id = u.id 
    ORDER BY a.timestamp DESC 
    LIMIT $per_page OFFSET $offset
")->fetchAll();

?>

<h1 class="h3 mb-4 text-white">System Audit Logs</h1>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0" style="font-size: 0.9rem;">
                <thead class="text-uppercase text-muted">
                    <tr>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Table</th>
                        <th>Record ID</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="text-nowrap">
                                <?= $log['timestamp'] ?>
                            </td>
                            <td class="fw-bold text-primary">
                                <?= e($log['username']) ?>
                            </td>
                            <td>
                                <?= e($log['action']) ?>
                            </td>
                            <td><code class="text-info"><?= $log['table_name'] ?></code></td>
                            <td>
                                <?= $log['record_id'] ?>
                            </td>
                            <td class="text-muted text-nowrap">
                                <?= e($log['ip_address']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">No audit logs found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($total_pages > 1): ?>
        <div class="card-footer bg-dark border-secondary bg-transparent d-flex justify-content-end pb-0">
            <ul class="pagination pagination-sm">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link bg-dark border-secondary text-white" href="?page=<?= $page - 1 ?>">Previous</a>
                </li>
                <li class="page-item disabled">
                    <span class="page-link bg-dark border-secondary text-white-50">Page
                        <?= $page ?> of
                        <?= $total_pages ?>
                    </span>
                </li>
                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                    <a class="page-link bg-dark border-secondary text-white" href="?page=<?= $page + 1 ?>">Next</a>
                </li>
            </ul>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/layout/footer.php'; ?>


