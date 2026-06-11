<?php
/**
 * pages/404.php — Not Found page
 */
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/Helper.php';

$pageTitle = 'Page Not Found - ' . APP_NAME;
$pageDescription = 'The page you are looking for could not be found.';

include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navigation.php';
?>
<section class="hero" style="min-height:60vh;display:flex;align-items:center;">
    <div class="container text-center">
        <p style="font-size:7rem;font-weight:800;line-height:1;margin:0;opacity:.85;">404</p>
        <h1 class="mt-3">We can't find that page</h1>
        <p class="lead">The link may be broken, or the page may have moved.</p>
        <div class="mt-4">
            <a href="<?php echo url('/'); ?>" class="btn btn-light btn-lg me-2">
                <i class="fas fa-home" aria-hidden="true"></i> <span>Go Home</span>
            </a>
            <a href="<?php echo url('/contact'); ?>" class="btn btn-outline-light btn-lg">
                <i class="fas fa-envelope" aria-hidden="true"></i> <span>Contact Us</span>
            </a>
        </div>
    </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
