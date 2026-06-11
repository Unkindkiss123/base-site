<?php
/**
 * pages/two-factor.php — 2FA challenge after successful password login
 */
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/Helper.php';
require_once __DIR__ . '/../includes/Security.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/RateLimiter.php';

if (empty($_SESSION['mfa_pending_user'])) {
    redirect(url('/login'));
}

$pageTitle = 'Two-Factor Authentication - ' . APP_NAME;
$pageDescription = 'Enter your authenticator code to complete sign-in.';
$message = null; $error = false;

if (isPost()) {
    $rl = new RateLimiter('mfa');
    if (!Security::verifyCSRFToken(post('csrf_token', ''))) {
        $message = 'Invalid request token. Please refresh and try again.'; $error = true;
    } elseif ($rl->isLimited(8, 15)) {
        $message = 'Too many attempts. Please try again later.'; $error = true;
    } else {
        $code = post('code', '');
        $auth = new Auth();
        $r = $auth->verifyMfa($code);
        if (!empty($r['success'])) {
            redirect(url('/admin'));
        } else {
            $rl->hit();
            $message = $r['message'] ?? 'Invalid code'; $error = true;
        }
    }
}

include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navigation.php';
?>
<section style="padding:60px 0;min-height:60vh;">
    <div class="container" style="max-width:420px;">
        <h1>Two-Factor Authentication</h1>
        <p class="text-muted">Open your authenticator app and enter the 6-digit code.</p>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $error ? 'danger' : 'success'; ?>" role="status"><?php echo e($message); ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <?php echo Security::getCSRFField(); ?>
            <div class="mb-3">
                <label class="form-label" for="code">Authentication code</label>
                <input class="form-control form-control-lg" type="text" id="code" name="code"
                       inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autofocus
                       autocomplete="one-time-code">
            </div>
            <button class="btn btn-primary w-100" type="submit">
                <i class="fas fa-check" aria-hidden="true"></i> <span>Verify</span>
            </button>
        </form>
    </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
