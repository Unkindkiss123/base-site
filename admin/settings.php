<?php
/**
 * admin/settings.php — Site settings (reads/writes the `settings` table)
 */
require_once __DIR__ . '/../includes/admin_layout.php';
require_once __DIR__ . '/../config/database.php';

admin_require_auth('manage_settings');

$db = Database::getInstance();

if (isPost()) {
    if (!Security::verifyCSRFToken(post('csrf_token', ''))) {
        flash('danger', 'Invalid request token.');
        redirect(url('/admin/settings'));
    }
    $allowed = ['site_name', 'site_description', 'site_email', 'site_phone', 'site_address',
                'facebook_url', 'twitter_url', 'instagram_url', 'linkedin_url'];
    foreach ($allowed as $k) {
        $v = trim((string)post($k, ''));
        $db->prepare('UPDATE settings SET `value` = :v WHERE `key` = :k');
        $db->bind(':v', $v); $db->bind(':k', $k);
        $db->execute();
    }
    flash('success', 'Settings updated.');
    redirect(url('/admin/settings'));
}

$db->prepare('SELECT `key`,`value` FROM settings');
$settings = [];
foreach ($db->fetchAll() as $r) { $settings[$r['key']] = $r['value']; }

admin_layout_start('Site Settings', 'settings');
?>
<form method="POST" class="card card-body">
    <?php echo Security::getCSRFField(); ?>
    <?php
    $fields = [
        'site_name'        => ['Site name', 'text'],
        'site_description' => ['Site description', 'textarea'],
        'site_email'       => ['Contact email', 'email'],
        'site_phone'       => ['Contact phone', 'tel'],
        'site_address'     => ['Address', 'text'],
        'facebook_url'     => ['Facebook URL', 'url'],
        'twitter_url'      => ['Twitter URL', 'url'],
        'instagram_url'    => ['Instagram URL', 'url'],
        'linkedin_url'     => ['LinkedIn URL', 'url'],
    ];
    foreach ($fields as $k => [$label, $type]) {
        admin_field($label, $k, $settings[$k] ?? '', $type);
    }
    ?>
    <div>
        <button class="btn btn-primary" type="submit">
            <i class="fas fa-save" aria-hidden="true"></i> <span>Save settings</span>
        </button>
    </div>
</form>
<?php
admin_layout_end();
