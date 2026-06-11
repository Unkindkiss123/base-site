<?php
/**
 * pages/reset-password.php
 * Handles /reset-password/{token} (token in $_GET['token']).
 */
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/Helper.php';
require_once __DIR__ . '/../includes/Security.php';
require_once __DIR__ . '/../includes/Auth.php';

$pageTitle = 'Reset Password - ' . APP_NAME;
$pageDescription = 'Choose a new password for your account.';

$token = (string)($_GET['token'] ?? post('token', ''));
$auth  = new Auth();
$valid = $token !== '' ? (bool)$auth->verifyResetToken($token) : false;
$message = null;
$success = false;

if (isPost() && $valid) {
    if (!Security::verifyCSRFToken(post('csrf_token', ''))) {
        $message = 'Invalid request token. Please refresh and try again.';
    } else {
        $p1 = (string)post('password', '');
        $p2 = (string)post('password_confirm', '');
        if (strlen($p1) < 8) {
            $message = 'Password must be at least 8 characters.';
        } elseif ($p1 !== $p2) {
            $message = 'Passwords do not match.';
        } else {
            $res = $auth->resetPassword($token, $p1);
            if (!empty($res['success'])) {
                $success = true;
                $message = 'Password updated. You can now sign in with your new password.';
            } else {
                $message = $res['message'] ?? 'Password reset failed.';
            }
        }
    }
}

include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navigation.php';
?>
<section style="padding:60px 0;min-height:60vh;">
    <div class="container" style="max-width:480px;">
        <h1>Reset your password</h1>

        <?php if (!$valid && !$success): ?>
            <div class="alert alert-danger" role="status">
                This reset link is invalid or has expired.
                <a href="<?php echo url('/forgot-password'); ?>">Request a new one</a>.
            </div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $success ? 'success' : 'warning'; ?>" role="status">
                <?php echo e($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($valid && !$success): ?>
            <form method="POST" novalidate>
                <?php echo Security::getCSRFField(); ?>
                <input type="hidden" name="token" value="<?php echo e($token); ?>">
                <div class="mb-3">
                    <label class="form-label" for="password">New password</label>
                    <input class="form-control" type="password" id="password" name="password" required minlength="8" autofocus>
                    <div class="form-text">At least 8 characters.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password_confirm">Confirm password</label>
                    <input class="form-control" type="password" id="password_confirm" name="password_confirm" required minlength="8">
                </div>
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-key" aria-hidden="true"></i>
                    <span>Update password</span>
                </button>
            </form>
        <?php endif; ?>

        <?php if ($success): ?>
            <a class="btn btn-primary mt-3" href="<?php echo url('/login'); ?>">
                <i class="fas fa-sign-in-alt" aria-hidden="true"></i> <span>Go to login</span>
            </a>
        <?php endif; ?>
    </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
