<?php
/**
 * index.php
 * Homepage
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/Helper.php';

$pageTitle = APP_NAME . ' - Professional Website Template';
$pageDescription = 'Welcome to our professional website template built with HTML5, CSS3, Bootstrap 5, and PHP 8.3+';

include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navigation.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container text-center">
        <h1>Welcome to <?php echo e(APP_NAME); ?></h1>
        <p class="lead">Professional Website Template for Modern Web Development</p>
        <a href="<?php echo url('/services'); ?>" class="btn btn-light btn-lg me-2">Get Started</a>
        <a href="<?php echo url('/contact'); ?>" class="btn btn-outline-light btn-lg">Contact Us</a>
    </div>
</section>

<!-- Features Section -->
<section>
    <div class="container">
        <h2 class="text-center mb-5">Why Choose Us?</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="feature-card">
                    <i class="fas fa-rocket"></i>
                    <h4>Fast Performance</h4>
                    <p>Optimized for speed and performance with modern best practices and caching strategies.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="feature-card">
                    <i class="fas fa-shield-alt"></i>
                    <h4>Secure</h4>
                    <p>Enterprise-grade security with CSRF protection, SQL injection prevention, and secure authentication.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="feature-card">
                    <i class="fas fa-mobile-alt"></i>
                    <h4>Responsive</h4>
                    <p>Mobile-first design that works perfectly on all devices, from smartphones to desktops.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Preview -->
<section class="light-bg">
    <div class="container">
        <h2 class="text-center mb-5">Our Services</h2>
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Web Design</h4>
                        <p class="card-text">Beautiful, modern designs that engage your audience and convert visitors into customers.</p>
                        <a href="<?php echo url('/services'); ?>" class="btn btn-primary">Learn More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Web Development</h4>
                        <p class="card-text">Custom solutions built with latest technologies for optimal performance and scalability.</p>
                        <a href="<?php echo url('/services'); ?>" class="btn btn-primary">Learn More</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container text-center">
        <h2>Ready to Get Started?</h2>
        <p>Contact us today to discuss your project needs</p>
        <a href="<?php echo url('/contact'); ?>" class="btn btn-light btn-lg">Contact Us Today</a>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
