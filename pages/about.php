<?php
/**
 * pages/about.php
 * About Us Page
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/Helper.php';

$pageTitle = 'About Us - ' . APP_NAME;
$pageDescription = 'Learn more about our company, mission, and values';

include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navigation.php';
?>

<!-- Hero Section -->
<section class="hero" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);">
    <div class="container text-center">
        <h1>About Us</h1>
        <p class="lead">Our Story, Mission, and Values</p>
    </div>
</section>

<!-- About Content -->
<section>
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 mb-4">
                <img src="<?php echo asset('images/about-placeholder.jpg'); ?>" alt="About Us" class="img-fluid rounded" loading="lazy">
            </div>
            <div class="col-md-6">
                <h2>Who We Are</h2>
                <p>We are a team of passionate web professionals dedicated to creating exceptional digital experiences. With years of experience in web design and development, we've helped businesses of all sizes achieve their online goals.</p>
                <p>Our commitment to quality, innovation, and customer satisfaction drives everything we do.</p>
                <ul class="list-unstyled mt-4">
                    <li class="mb-3"><i class="fas fa-check text-primary"></i> Professional & Experienced Team</li>
                    <li class="mb-3"><i class="fas fa-check text-primary"></i> Custom Solutions for Your Needs</li>
                    <li class="mb-3"><i class="fas fa-check text-primary"></i> 24/7 Support & Maintenance</li>
                    <li class="mb-3"><i class="fas fa-check text-primary"></i> Affordable Pricing</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="light-bg">
    <div class="container">
        <h2 class="text-center mb-5">Our Team</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card text-center">
                    <img src="<?php echo asset('images/team-1.jpg'); ?>" alt="Team Member" class="card-img-top" loading="lazy">
                    <div class="card-body">
                        <h5 class="card-title">John Doe</h5>
                        <p class="card-text text-muted">Lead Designer</p>
                        <p class="card-text">Creative designer with 10+ years of experience</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card text-center">
                    <img src="<?php echo asset('images/team-2.jpg'); ?>" alt="Team Member" class="card-img-top" loading="lazy">
                    <div class="card-body">
                        <h5 class="card-title">Jane Smith</h5>
                        <p class="card-text text-muted">Full Stack Developer</p>
                        <p class="card-text">Expert developer specializing in modern web technologies</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card text-center">
                    <img src="<?php echo asset('images/team-3.jpg'); ?>" alt="Team Member" class="card-img-top" loading="lazy">
                    <div class="card-body">
                        <h5 class="card-title">Mike Johnson</h5>
                        <p class="card-text text-muted">Project Manager</p>
                        <p class="card-text">Ensuring projects are delivered on time and on budget</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
