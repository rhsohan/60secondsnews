<?php
// admin/themes.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/helpers.php';
require_once __DIR__ . '/../../app/rbac.php';

require_login();
require_permission('manage_settings');

$settings_file = BASE_PATH . '/config/settings.json';

// Default settings
$settings = [
    'user_theme' => 'premium',
    'admin_theme' => 'premium'
];

if (file_exists($settings_file)) {
    $loaded = json_decode(file_get_contents($settings_file), true);
    if (is_array($loaded)) {
        $settings = array_merge($settings, $loaded);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $settings['user_theme'] = $_POST['user_theme'] ?? 'premium';
    $settings['admin_theme'] = $_POST['admin_theme'] ?? 'premium';

    file_put_contents($settings_file, json_encode($settings, JSON_PRETTY_PRINT));

    clear_cache();

    log_activity("Updated theme settings", 'config');
    set_flash_message('success', 'Theme settings updated and cache cleared.');
    header('Location: themes.php');
    exit;
}

// Now include visual header after logic and potential redirects
require_once __DIR__ . '/layout/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-white">Appearance & Themes</h1>
</div>

<form method="POST">
    <?= csrf_field() ?>

    <div class="row g-4">
        <!-- User Panel Theme -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <i class="bi bi-display me-2 text-primary"></i> User Panel Theme
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-4">Select the visual aesthetic for your website visitors.</p>

                    <div class="row g-3">
                        <div class="col-12">
                            <div class="form-check theme-selector-card p-0">
                                <input class="form-check-input d-none" type="radio" name="user_theme" id="user_premium"
                                    value="premium" <?= $settings['user_theme'] == 'premium' ? 'checked' : '' ?>>
                                <label class="form-check-label w-100" for="user_premium">
                                    <div
                                        class="theme-card-inner p-3 rounded-3 border <?= $settings['user_theme'] == 'premium' ? 'border-primary bg-primary bg-opacity-10' : 'border-secondary border-opacity-25' ?>">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 bg-primary rounded-circle p-2 me-3">
                                                <i class="bi bi-gem text-white"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold">Premium (Recommended)</h6>
                                                <small class="text-muted">Platinum base with subtle mesh
                                                    gradients.</small>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-check theme-selector-card p-0">
                                <input class="form-check-input d-none" type="radio" name="user_theme"
                                    id="user_professional" value="professional"
                                    <?= $settings['user_theme'] == 'professional' ? 'checked' : '' ?>>
                                <label class="form-check-label w-100" for="user_professional">
                                    <div
                                        class="theme-card-inner p-3 rounded-3 border <?= $settings['user_theme'] == 'professional' ? 'border-primary bg-primary bg-opacity-10' : 'border-secondary border-opacity-25' ?>">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 bg-info rounded-circle p-2 me-3">
                                                <i class="bi bi-briefcase text-white"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold">Professional</h6>
                                                <small class="text-muted">Clean slate-white with bold navy
                                                    accents.</small>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>



                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Panel Theme -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <i class="bi bi-speedometer2 me-2 text-primary"></i> Admin Panel Theme
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-4">Select the interface style for your administrative team.</p>

                    <div class="row g-3">
                        <div class="col-12">
                            <div class="form-check theme-selector-card p-0">
                                <input class="form-check-input d-none" type="radio" name="admin_theme"
                                    id="admin_premium" value="premium" <?= $settings['admin_theme'] == 'premium' ? 'checked' : '' ?>>
                                <label class="form-check-label w-100" for="admin_premium">
                                    <div
                                        class="theme-card-inner p-3 rounded-3 border <?= $settings['admin_theme'] == 'premium' ? 'border-primary bg-primary bg-opacity-10' : 'border-secondary border-opacity-25' ?>">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 bg-primary rounded-circle p-2 me-3">
                                                <i class="bi bi-mask text-white"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold">Premium Mashup</h6>
                                                <small class="text-muted">Dark glassmorphism with mesh glows.</small>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-check theme-selector-card p-0">
                                <input class="form-check-input d-none" type="radio" name="admin_theme"
                                    id="admin_default" value="default" <?= $settings['admin_theme'] == 'default' ? 'checked' : '' ?>>
                                <label class="form-check-label w-100" for="admin_default">
                                    <div
                                        class="theme-card-inner p-3 rounded-3 border <?= $settings['admin_theme'] == 'default' ? 'border-primary bg-primary bg-opacity-10' : 'border-secondary border-opacity-25' ?>">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 bg-secondary rounded-circle p-2 me-3">
                                                <i class="bi bi-square text-white"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold">Classic Dark</h6>
                                                <small class="text-muted">Original flat dark interface.</small>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-check theme-selector-card p-0">
                                <input class="form-check-input d-none" type="radio" name="admin_theme"
                                    id="admin_midnight" value="midnight" <?= $settings['admin_theme'] == 'midnight' ? 'checked' : '' ?>>
                                <label class="form-check-label w-100" for="admin_midnight">
                                    <div
                                        class="theme-card-inner p-3 rounded-3 border <?= $settings['admin_theme'] == 'midnight' ? 'border-primary bg-primary bg-opacity-10' : 'border-secondary border-opacity-25' ?>">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 bg-dark rounded-circle p-2 me-3"
                                                style="background-color: #020617 !important;">
                                                <i class="bi bi-moon-stars-fill text-white"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold">Midnight Pro</h6>
                                                <small class="text-muted">Maximum dark mode with violet accents.</small>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="bi bi-check2-circle me-1"></i> Apply Theme Changes
        </button>
    </div>
</form>

<style>
    .theme-card-inner {
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .theme-card-inner:hover {
        background: rgba(255, 255, 255, 0.05);
        transform: translateY(-2px);
    }

    .form-check-input:checked+.form-check-label .theme-card-inner {
        border-color: #2563eb !important;
        background: rgba(37, 99, 235, 0.1) !important;
    }
</style>

<script>
    // Simple script to toggle visual active state immediately on click
    document.querySelectorAll('.theme-selector-card input').forEach(input => {
        input.addEventListener('change', function () {
            const name = this.getAttribute('name');
            document.querySelectorAll(`.theme-selector-card input[name="${name}"]`).forEach(node => {
                const inner = node.nextElementSibling.querySelector('.theme-card-inner');
                inner.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
                inner.classList.add('border-secondary', 'border-opacity-25');
            });

            const selectedInner = this.nextElementSibling.querySelector('.theme-card-inner');
            selectedInner.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
            selectedInner.classList.remove('border-secondary', 'border-opacity-25');
        });
    });
</script>

<?php require_once __DIR__ . '/layout/footer.php'; ?>