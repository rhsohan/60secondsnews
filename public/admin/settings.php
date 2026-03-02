<?php
// admin/settings.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/helpers.php';
require_once __DIR__ . '/../../app/rbac.php';

require_login();
require_permission('manage_settings');
$db = DB::getInstance()->getConnection();

$settings_file = BASE_PATH . '/config/settings.json';

// Default settings if file absent
$settings = [
    'maintenance_mode' => false,
    'site_title' => '60-Second News',
    'require_account_comments' => false
];

if (file_exists($settings_file)) {
    $loaded = json_decode(file_get_contents($settings_file), true);
    if (is_array($loaded)) {
        $settings = array_merge($settings, $loaded);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $settings['maintenance_mode'] = isset($_POST['maintenance_mode']);
    $settings['site_title'] = trim($_POST['site_title']);
    $settings['require_account_comments'] = isset($_POST['require_account_comments']);

    file_put_contents($settings_file, json_encode($settings, JSON_PRETTY_PRINT));
    log_activity("Updated system settings", 'config');
    clear_cache(); // Auto-clear frontend cache
    set_flash_message('success', 'System settings saved.');
    header('Location: settings.php');
    exit;
}

require_once __DIR__ . '/layout/header.php';
?>

<h1 class="h3 mb-4 text-white">System Settings</h1>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <?= csrf_field() ?>

                    <div class="mb-4">
                        <label class="form-label text-muted">Site Title</label>
                        <input type="text" name="site_title" class="form-control bg-dark text-white border-secondary"
                            value="<?= e($settings['site_title']) ?>" required>
                    </div>

                    <hr class="border-secondary my-4">
                    <h5 class="text-white mb-3">Toggles</h5>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="maintenance_mode" id="maintenance_mode"
                            <?= $settings['maintenance_mode'] ? 'checked' : '' ?>>
                        <label class="form-check-label text-white" for="maintenance_mode">
                            Maintenance Mode <br>
                            <small class="text-muted">When enabled, the public frontend will display a maintenance
                                page.</small>
                        </label>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="require_account_comments"
                            id="require_account_comments" <?= $settings['require_account_comments'] ? 'checked' : '' ?>>
                        <label class="form-check-label text-white" for="require_account_comments">
                            Disable Anonymous Comments <br>
                            <small class="text-muted">Only logged-in users can post comments.</small>
                        </label>
                    </div>



                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Settings</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layout/footer.php'; ?>