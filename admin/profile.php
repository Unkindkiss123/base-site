<?php
/**
 * admin/profile.php — current user's profile + 2FA setup + email verification.
 */
require_once __DIR__ . '/../includes/admin_layout.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/Totp.php';
require_once __DIR__ . '/../config/database.php';

admin_require_auth();

$db = Database::getInstance();
$me = (int)getUser()['id'];

if (isPost()) {
    if (!Security::verifyCSRFToken(post('csrf_token', ''))) {
        flash('danger', 'Invalid request token.');
        redirect(url('/admin/profile'));
    }
    $action = post('do', '');

    if ($action === 'profile') {
        $email = trim((string)post('email', ''));
        $first = trim((string)post('first_name', ''));
        $last  = trim((string)post('last_name', ''));
        $pw    = (string)post('password', '');
        if (!Security::validateEmail($email)) { flash('danger', 'Invalid email.'); redirect(url('/admin/profile')); }
        $sql = 'UPDATE users SET email=:email, first_name=:f, last_name=:l';
        if ($pw !== '') {
            if (strlen($pw) < 8) { flash('danger', 'Password must be at least 8 characters.'); redirect(url('/admin/profile')); }
            $sql .= ', password=:pw';
        }
        $sql .= ' WHERE id=:id';
        $db->prepare($sql);
        $db->bind(':email', $email); $db->bind(':f', $first); $db->bind(':l', $last); $db->bind(':id', $me);
        if ($pw !== '') $db->bind(':pw', Security::hashPassword($pw));
        $db->execute();
        flash('success', 'Profile updated.');
        redirect(url('/admin/profile'));
    }

    if ($action === 'send_verification') {
        (new Auth())->sendEmailVerification($me);
        flash('success', 'Verification email sent (or queued via the configured MAIL_DRIVER).');
        redirect(url('/admin/profile'));
    }

    if ($action === 'mfa_start') {
        $secret = Totp::generateSecret(32);
        $_SESSION['mfa_setup_secret'] = $secret;
        // fall through to render setup
    } elseif ($action === 'mfa_confirm') {
        $secret = $_SESSION['mfa_setup_secret'] ?? '';
        $code = post('code', '');
        if (!$secret || !Totp::verify($secret, $code)) {
            flash('danger', 'Invalid code. Please scan the QR and try again.');
            redirect(url('/admin/profile'));
        }
        $db->prepare('UPDATE users SET mfa_secret = :s, mfa_enabled = 1 WHERE id = :id');
        $db->bind(':s', $secret); $db->bind(':id', $me);
        $db->execute();
        unset($_SESSION['mfa_setup_secret']);
        flash('success', 'Two-factor authentication enabled.');
        redirect(url('/admin/profile'));
    } elseif ($action === 'mfa_disable') {
        $db->prepare('UPDATE users SET mfa_secret = NULL, mfa_enabled = 0 WHERE id = :id');
        $db->bind(':id', $me);
        $db->execute();
        unset($_SESSION['mfa_setup_secret']);
        flash('success', 'Two-factor authentication disabled.');
        redirect(url('/admin/profile'));
    }
}

$db->prepare('SELECT id, email, username, first_name, last_name, mfa_enabled, email_verified_at FROM users WHERE id = :id');
$db->bind(':id', $me);
$user = $db->fetch();

$setupSecret = $_SESSION['mfa_setup_secret'] ?? null;
$qrUrl = $setupSecret
    ? 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' . urlencode(Totp::provisioningUri($setupSecret, $user['email'], APP_NAME))
    : null;

admin_layout_start('My Profile', '');
?>
<div class="row g-3">
    <div class="col-lg-6">
        <form method="POST" class="card card-body">
            <h2 class="h5">Profile</h2>
            <?php echo Security::getCSRFField(); ?>
            <input type="hidden" name="do" value="profile">
            <?php
            admin_field('Email', 'email', $user['email'], 'email', ['required' => true]);
            admin_field('First name', 'first_name', $user['first_name'] ?? '');
            admin_field('Last name', 'last_name', $user['last_name'] ?? '');
            admin_field('New password', 'password', '', 'password', ['help' => 'Leave blank to keep current password. Min 8 chars.']);
            ?>
            <div><button class="btn btn-primary"><i class="fas fa-save" aria-hidden="true"></i> <span>Save</span></button></div>
        </form>

        <div class="card card-body mt-3">
            <h2 class="h5">Email verification</h2>
            <?php if (!empty($user['email_verified_at'])): ?>
                <p class="text-success mb-0"><i class="fas fa-check-circle" aria-hidden="true"></i> Verified on <?php echo e(formatDate($user['email_verified_at'], 'M d, Y')); ?>.</p>
            <?php else: ?>
                <p class="text-muted">Your email isn't verified yet.</p>
                <form method="POST">
                    <?php echo Security::getCSRFField(); ?>
                    <input type="hidden" name="do" value="send_verification">
                    <button class="btn btn-outline-primary"><i class="fas fa-paper-plane" aria-hidden="true"></i> <span>Send verification email</span></button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card card-body">
            <h2 class="h5">Two-factor authentication</h2>
            <?php if ($user['mfa_enabled']): ?>
                <p class="text-success"><i class="fas fa-shield-alt" aria-hidden="true"></i> 2FA is <strong>enabled</strong> on your account.</p>
                <form method="POST" onsubmit="return confirm('Disable 2FA?');">
                    <?php echo Security::getCSRFField(); ?>
                    <input type="hidden" name="do" value="mfa_disable">
                    <button class="btn btn-outline-danger"><i class="fas fa-power-off" aria-hidden="true"></i> <span>Disable 2FA</span></button>
                </form>
            <?php elseif ($setupSecret): ?>
                <p>Scan this QR code with Google Authenticator, Authy, or 1Password, then enter the 6-digit code to confirm.</p>
                <div class="text-center mb-3">
                    <img src="<?php echo e($qrUrl); ?>" alt="Two-factor authentication QR code">
                    <p class="text-muted small mt-2 mb-0">Or enter this key manually: <code><?php echo e(chunk_split($setupSecret, 4, ' ')); ?></code></p>
                </div>
                <form method="POST">
                    <?php echo Security::getCSRFField(); ?>
                    <input type="hidden" name="do" value="mfa_confirm">
                    <?php admin_field('Confirmation code', 'code', '', 'text', ['required' => true, 'help' => '6-digit code from your authenticator app']); ?>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary"><i class="fas fa-check" aria-hidden="true"></i> <span>Confirm &amp; enable</span></button>
                        <a class="btn btn-outline-secondary" href="<?php echo url('/admin/profile'); ?>">Cancel</a>
                    </div>
                </form>
            <?php else: ?>
                <p class="text-muted">Add an extra layer of security to your admin account.</p>
                <form method="POST">
                    <?php echo Security::getCSRFField(); ?>
                    <input type="hidden" name="do" value="mfa_start">
                    <button class="btn btn-primary"><i class="fas fa-shield-alt" aria-hidden="true"></i> <span>Enable 2FA</span></button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
admin_layout_end();
