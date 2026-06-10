<?php
/**
 * pages/portfolio.php
 * Portfolio/Projects Page
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/Helper.php';

$pageTitle = 'Portfolio - ' . APP_NAME;
$pageDescription = 'View our latest projects and case studies';

include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navigation.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container text-center">
        <h1>Our Portfolio</h1>
        <p class="lead">Showcase of Recent Projects</p>
    </div>
</section>

<!-- Portfolio Grid -->
<section>
    <div class="container">
        <h2 class="text-center mb-5">Featured Projects</h2>
        <div class="gallery-grid">
            <div class="gallery-item">
                <img src="<?php echo asset('images/portfolio-1.jpg'); ?>" alt="Project 1" loading="lazy">
                <div style="padding: 20px;">
                    <h5>E-Commerce Platform</h5>
                    <p class="text-muted">Built with PHP and MySQL</p>
                </div>
            </div>
            <div class="gallery-item">
                <img src="<?php echo asset('images/portfolio-2.jpg'); ?>" alt="Project 2" loading="lazy">
                <div style="padding: 20px;">
                    <h5>Corporate Website</h5>
                    <p class="text-muted">Responsive Bootstrap Design</p>
                </div>
            </div>
            <div class="gallery-item">
                <img src="<?php echo asset('images/portfolio-3.jpg'); ?>" alt="Project 3" loading="lazy">
                <div style="padding: 20px;">
                    <h5>Mobile App</h5>
                    <p class="text-muted">iOS & Android Development</p>
                </div>
            </div>
            <div class="gallery-item">
                <img src="<?php echo asset('images/portfolio-4.jpg'); ?>" alt="Project 4" loading="lazy">
                <div style="padding: 20px;">
                    <h5>Blog Platform</h5>
                    <p class="text-muted">Content Management System</p>
                </div>
            </div>
            <div class="gallery-item">
                <img src="<?php echo asset('images/portfolio-5.jpg'); ?>" alt="Project 5" loading="lazy">
                <div style="padding: 20px;">
                    <h5>SaaS Solution</h5>
                    <p class="text-muted">Subscription-Based Service</p>
                </div>
            </div>
            <div class="gallery-item">
                <img src="<?php echo asset('images/portfolio-6.jpg'); ?>" alt="Project 6" loading="lazy">
                <div style="padding: 20px;">
                    <h5>Analytics Dashboard</h5>
                    <p class="text-muted">Real-Time Data Visualization</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
