<?php
/**
 * pages/forgot-password.php
 */
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/Helper.php';
require_once __DIR__ . '/../includes/Security.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/RateLimiter.php';

$pageTitle = 'Forgot Password - ' . APP_NAME;
$pageDescription = 'Request a password reset link.';

$message = null;
$success = false;

if (isPost()) {
    if (!Security::verifyCSRFToken(post('csrf_token', ''))) {
        $message = 'Invalid request token. Please refresh and try again.';
    } else {
        $rl = new RateLimiter('forgot');
        if ($rl->isLimited(5, 15)) {
            $message = 'Too many attempts. Please try again later.';
        } else {
            $email = trim((string)post('email', ''));
            if (!Security::validateEmail($email)) {
                $message = 'Please enter a valid email address.';
                $rl->hit();
            } else {
                $auth = new Auth();
                $auth->requestPasswordReset($email);
                $rl->hit();
                $success = true;
                $message = 'If that email exists in our system, a reset link has been sent.';
            }
        }
    }
}

include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navigation.php';
?>
<section style="padding:60px 0;min-height:60vh;">
    <div class="container" style="max-width:480px;">
        <h1>Forgot your password?</h1>
        <p class="text-muted">Enter your email and we'll send you a link to reset it.</p>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $success ? 'success' : 'warning'; ?>" role="status">
                <?php echo e($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <?php echo Security::getCSRFField(); ?>
            <div class="mb-3">
                <label class="form-label" for="email">Email address</label>
                <input class="form-control" type="email" id="email" name="email" required autofocus value="<?php echo e(post('email', '')); ?>">
            </div>
            <button class="btn btn-primary" type="submit">
                <i class="fas fa-paper-plane" aria-hidden="true"></i>
                <span>Send reset link</span>
            </button>
            <a class="btn btn-link" href="<?php echo url('/login'); ?>">Back to login</a>
        </form>
    </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
