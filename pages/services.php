<?php
/**
 * pages/services.php
 * Services Page
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/Helper.php';

$pageTitle = 'Services - ' . APP_NAME;
$pageDescription = 'Explore our professional services and solutions';

include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navigation.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container text-center">
        <h1>Our Services</h1>
        <p class="lead">Comprehensive Solutions for Your Business</p>
    </div>
</section>

<!-- Services Grid -->
<section>
    <div class="container">
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div style="font-size: 3rem; color: #007bff; margin-bottom: 20px;">
                            <i class="fas fa-paint-brush"></i>
                        </div>
                        <h4 class="card-title">Web Design</h4>
                        <p class="card-text">Custom, responsive web designs that capture your brand essence and engage your audience.</p>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-primary"></i> UI/UX Design</li>
                            <li><i class="fas fa-check text-primary"></i> Responsive Design</li>
                            <li><i class="fas fa-check text-primary"></i> Branding</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div style="font-size: 3rem; color: #007bff; margin-bottom: 20px;">
                            <i class="fas fa-code"></i>
                        </div>
                        <h4 class="card-title">Web Development</h4>
                        <p class="card-text">Full-stack development services using modern technologies and best practices.</p>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-primary"></i> Frontend Development</li>
                            <li><i class="fas fa-check text-primary"></i> Backend Development</li>
                            <li><i class="fas fa-check text-primary"></i> API Development</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div style="font-size: 3rem; color: #007bff; margin-bottom: 20px;">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h4 class="card-title">Mobile Development</h4>
                        <p class="card-text">Native and cross-platform mobile applications for iOS and Android.</p>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-primary"></i> iOS Development</li>
                            <li><i class="fas fa-check text-primary"></i> Android Development</li>
                            <li><i class="fas fa-check text-primary"></i> Cross-Platform Apps</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div style="font-size: 3rem; color: #007bff; margin-bottom: 20px;">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4 class="card-title">SEO & Marketing</h4>
                        <p class="card-text">Digital marketing strategies to increase your online visibility and traffic.</p>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-primary"></i> SEO Optimization</li>
                            <li><i class="fas fa-check text-primary"></i> Content Marketing</li>
                            <li><i class="fas fa-check text-primary"></i> Analytics</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container text-center">
        <h2>Get Started Today</h2>
        <p>Let's discuss how we can help your business grow</p>
        <a href="<?php echo url('/pages/contact.php'); ?>" class="btn btn-light btn-lg">Request a Quote</a>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
