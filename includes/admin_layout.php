<?php
/**
 * includes/admin_layout.php
 * Shared admin chrome (header + sidebar + footer) so every admin page is
 * a few lines of business logic instead of 100+ lines of duplicated HTML.
 *
 * Usage:
 *   require_once __DIR__ . '/../includes/admin_layout.php';
 *   admin_layout_start('Users');
 *   ... page body ...
 *   admin_layout_end();
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/Helper.php';
require_once __DIR__ . '/Security.php';

function admin_require_auth($permission = null) {
    if (!isAuthenticated()) {
        redirect(url('/login'));
    }
    if ($permission !== null && !hasPermission($permission)) {
        http_response_code(403);
        echo '<h1>403 Forbidden</h1>';
        exit;
    }
}

function admin_layout_start($title, $active = '') {
    Security::setSecurityHeaders();
    $u = getUser();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo e($title . ' — Admin — ' . APP_NAME); ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="<?php echo asset('css/theme.css'); ?>">
        <link rel="stylesheet" href="<?php echo asset('css/admin.css'); ?>">
    </head>
    <body class="admin-body">
    <a class="skip-link" href="#admin-main">Skip to main content</a>
    <div class="admin-shell">
        <aside class="admin-sidebar" aria-label="Admin navigation">
            <div class="admin-brand">
                <i class="fas fa-bolt" aria-hidden="true"></i>
                <span><?php echo e(setting('site_name', APP_NAME)); ?></span>
            </div>
            <nav>
                <?php
                $items = [
                    'dashboard' => ['/admin', 'fa-tachometer-alt', 'Dashboard'],
                    'users'     => ['/admin/users', 'fa-users', 'Users'],
                    'pages'     => ['/admin/pages', 'fa-file-lines', 'Pages'],
                    'blog'      => ['/admin/blog', 'fa-blog', 'Blog Posts'],
                    'services'  => ['/admin/services', 'fa-briefcase', 'Services'],
                    'gallery'   => ['/admin/gallery', 'fa-images', 'Gallery'],
                    'uploads'   => ['/admin/uploads', 'fa-cloud-upload-alt', 'Uploads'],
                    'leads'     => ['/admin/leads', 'fa-envelope', 'Leads'],
                    'settings'  => ['/admin/settings', 'fa-cog', 'Settings'],
                    'profile'   => ['/admin/profile', 'fa-user-circle', 'My Profile'],
                ];
                foreach ($items as $key => [$path, $icon, $label]):
                    $isActive = ($active === $key) ? ' active' : '';
                ?>
                    <a href="<?php echo url($path); ?>" class="admin-nav-link<?php echo $isActive; ?>"<?php if ($isActive) echo ' aria-current="page"'; ?>>
                        <i class="fas <?php echo $icon; ?>" aria-hidden="true"></i>
                        <span><?php echo e($label); ?></span>
                    </a>
                <?php endforeach; ?>
                <a href="<?php echo url('/logout'); ?>" class="admin-nav-link admin-nav-logout">
                    <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </aside>

        <main id="admin-main" class="admin-main" tabindex="-1">
            <header class="admin-header">
                <h1 class="admin-title"><?php echo e($title); ?></h1>
                <div class="admin-user">
                    <i class="fas fa-user-circle" aria-hidden="true"></i>
                    <span><?php echo e(($u['first_name'] ?? '') ?: ($u['username'] ?? 'User')); ?></span>
                </div>
            </header>
            <div class="admin-content">
                <?php
                $f = getFlash();
                if ($f):
                ?>
                    <div class="alert alert-<?php echo e($f['type']); ?>" role="status">
                        <?php echo e($f['message']); ?>
                    </div>
                <?php endif;
}

function admin_layout_end($extra_scripts = '') {
            ?>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php echo $extra_scripts; ?>
    </body>
    </html>
    <?php
}

/* ---------- TinyMCE convenience ----------
 * Echo the result inside admin_layout_end() to enable WYSIWYG on textareas
 * whose name matches the given selector (default: textarea[name="content"]).
 */
function admin_wysiwyg_scripts($selector = 'textarea[name="content"]') {
    ob_start();
    ?>
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
    tinymce.init({
        selector: <?php echo json_encode($selector); ?>,
        height: 480,
        menubar: false,
        promotion: false,
        branding: false,
        plugins: 'lists link image table code autolink',
        toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | link image | table | code',
        content_style: 'body { font-family:Inter,system-ui,sans-serif; font-size:15px; line-height:1.6; }',
        license_key: 'gpl'
    });
    </script>
    <?php
    return ob_get_clean();
}

/* ---------- tiny form helpers ---------- */

function admin_field($label, $name, $value = '', $type = 'text', $extra = []) {
    $id = 'f_' . preg_replace('/[^a-z0-9_]/i', '_', $name);
    $required = !empty($extra['required']) ? ' required' : '';
    $help = $extra['help'] ?? '';
    ?>
    <div class="mb-3">
        <label class="form-label" for="<?php echo e($id); ?>"><?php echo e($label); ?><?php if ($required) echo ' <span class="text-danger" aria-hidden="true">*</span>'; ?></label>
        <?php if ($type === 'textarea'): ?>
            <textarea class="form-control" id="<?php echo e($id); ?>" name="<?php echo e($name); ?>" rows="<?php echo (int)($extra['rows'] ?? 5); ?>"<?php echo $required; ?>><?php echo e($value); ?></textarea>
        <?php elseif ($type === 'select'): ?>
            <select class="form-select" id="<?php echo e($id); ?>" name="<?php echo e($name); ?>"<?php echo $required; ?>>
                <?php foreach (($extra['options'] ?? []) as $optVal => $optLabel): ?>
                    <option value="<?php echo e($optVal); ?>"<?php if ((string)$optVal === (string)$value) echo ' selected'; ?>><?php echo e($optLabel); ?></option>
                <?php endforeach; ?>
            </select>
        <?php else: ?>
            <input class="form-control" type="<?php echo e($type); ?>" id="<?php echo e($id); ?>" name="<?php echo e($name); ?>" value="<?php echo e($value); ?>"<?php echo $required; ?>>
        <?php endif; ?>
        <?php if ($help): ?><div class="form-text"><?php echo e($help); ?></div><?php endif; ?>
    </div>
    <?php
}

function status_badge($status) {
    $map = [
        'active'    => ['success', 'Active'],
        'inactive'  => ['secondary', 'Inactive'],
        'suspended' => ['danger', 'Suspended'],
        'published' => ['success', 'Published'],
        'draft'     => ['warning text-dark', 'Draft'],
        'archived'  => ['secondary', 'Archived'],
        'new'       => ['primary', 'New'],
        'contacted' => ['info text-dark', 'Contacted'],
        'qualified' => ['success', 'Qualified'],
        'rejected'  => ['danger', 'Rejected'],
        'converted' => ['success', 'Converted'],
    ];
    [$cls, $label] = $map[$status] ?? ['secondary', ucfirst($status)];
    return '<span class="badge bg-' . $cls . '"><span class="visually-hidden">Status: </span>' . htmlspecialchars($label) . '</span>';
}
