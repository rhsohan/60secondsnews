<?php
// admin/index.php
require_once __DIR__ . '/layout/header.php';

$db = DB::getInstance()->getConnection();

// Fetch basic stats
$total_articles = $db->query("SELECT COUNT(*) FROM articles")->fetchColumn();
$published_articles = $db->query("SELECT COUNT(*) FROM articles WHERE status = 'published'")->fetchColumn();
$pending_articles = $db->query("SELECT COUNT(*) FROM articles WHERE status = 'pending'")->fetchColumn();
$total_views = $db->query("SELECT SUM(view_count) FROM views")->fetchColumn() ?: 0;
$total_users = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Fetch recent activity
$recent_logs = $db->query("
    SELECT a.*, u.username 
    FROM audit_logs a 
    JOIN users u ON a.user_id = u.id 
    ORDER BY a.timestamp DESC 
    LIMIT 5
")->fetchAll();

?>

<h1 class="h3 mb-4 text-white">Dashboard Overview</h1>



<div class="row g-4 mb-4">
    <!-- Stat Cards -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0 bg-primary bg-opacity-10 rounded-3 p-3 text-primary">
                        <i class="bi bi-eye fs-3"></i>
                    </div>
                    <div class="ms-3">
                        <h6 class="text-muted mb-0 small text-uppercase fw-bold">Total Views</h6>
                        <h2 class="mb-0 fw-bold"><?= number_format($total_views) ?></h2>
                    </div>
                </div>
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-primary" style="width: 75%"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0 bg-success bg-opacity-10 rounded-3 p-3 text-success">
                        <i class="bi bi-file-text fs-3"></i>
                    </div>
                    <div class="ms-3">
                        <h6 class="text-muted mb-0 small text-uppercase fw-bold">Published</h6>
                        <h2 class="mb-0 fw-bold"><?= number_format($published_articles) ?></h2>
                    </div>
                </div>
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-success" style="width: 60%"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0 bg-warning bg-opacity-10 rounded-3 p-3 text-warning">
                        <i class="bi bi-hourglass-split fs-3"></i>
                    </div>
                    <div class="ms-3">
                        <h6 class="text-muted mb-0 small text-uppercase fw-bold">Pending</h6>
                        <h2 class="mb-0 fw-bold"><?= number_format($pending_articles) ?></h2>
                    </div>
                </div>
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-warning" style="width: 25%"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0 bg-info bg-opacity-10 rounded-3 p-3 text-info">
                        <i class="bi bi-people fs-3"></i>
                    </div>
                    <div class="ms-3">
                        <h6 class="text-muted mb-0 small text-uppercase fw-bold">Users</h6>
                        <h2 class="mb-0 fw-bold"><?= number_format($total_users) ?></h2>
                    </div>
                </div>
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-info" style="width: 45%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Activity -->
    <div class="col-md-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Recent Activity</h5>
                <?php if (has_permission('view_logs')): ?>
                    <a href="logs.php" class="btn btn-sm btn-outline-primary px-3 rounded-pill">View All</a>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <tbody>
                            <?php foreach ($recent_logs as $log): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                                <i class="bi bi-activity text-primary"></i>
                                            </div>
                                            <div>
                                                <span class="fw-bold text-white"><?= e($log['username']) ?></span>
                                                <span class="text-muted small d-block"><?= e($log['action']) ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($log['table_name']): ?>
                                            <span
                                                class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 small">
                                                <?= e($log['table_name']) ?> #<?= e($log['record_id']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <small class="text-muted"><?= time_ago($log['timestamp']) ?></small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recent_logs)): ?>
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">No recent activity.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-md-5">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0 fw-bold">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-3">
                    <?php if (has_permission('create_article')): ?>
                        <a href="article_edit.php"
                            class="btn btn-primary d-flex align-items-center justify-content-between p-3">
                            <span><i class="bi bi-plus-circle me-2"></i> Create New Article</span>
                            <i class="bi bi-chevron-right small"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (has_permission('manage_users')): ?>
                        <a href="users.php"
                            class="btn btn-dark d-flex align-items-center justify-content-between p-3 border border-secondary border-opacity-25">
                            <span><i class="bi bi-person-plus me-2"></i> Add New User</span>
                            <i class="bi bi-chevron-right small"></i>
                        </a>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>/" target="_blank"
                        class="btn btn-outline-light d-flex align-items-center justify-content-between p-3 border border-secondary border-opacity-25">
                        <span><i class="bi bi-box-arrow-up-right me-2"></i> Visit Website</span>
                        <i class="bi bi-chevron-right small"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layout/footer.php'; ?>