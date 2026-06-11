<?php
/**
 * pages/verify-email.php — confirms a token from a verification email.
 */
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/Helper.php';
require_once __DIR__ . '/../includes/Security.php';
require_once __DIR__ . '/../includes/Auth.php';

$pageTitle = 'Verify Email - ' . APP_NAME;
$token = (string)($_GET['token'] ?? '');
$ok = false; $msg = '';

if ($token === '') {
    $msg = 'Missing verification token.';
} else {
    $auth = new Auth();
    if ($auth->verifyEmail($token)) {
        $ok = true; $msg = 'Your email is now verified.';
    } else {
        $msg = 'This link is invalid or has expired.';
    }
}

include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navigation.php';
?>
<section style="padding:80px 0;min-height:60vh;">
    <div class="container text-center" style="max-width:520px;">
        <h1><?php echo $ok ? 'Email verified' : 'Verification failed'; ?></h1>
        <div class="alert alert-<?php echo $ok ? 'success' : 'danger'; ?> mt-3" role="status"><?php echo e($msg); ?></div>
        <a class="btn btn-primary" href="<?php echo url('/login'); ?>">
            <i class="fas fa-sign-in-alt" aria-hidden="true"></i> <span>Go to login</span>
        </a>
    </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
