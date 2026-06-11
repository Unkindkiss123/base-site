<?php
/**
 * pages/about.php — driven by the `pages` table (slug='about'), with a built-in default body.
 */
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/Helper.php';
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance();
$db->prepare("SELECT * FROM pages WHERE slug = 'about' AND status = 'published' LIMIT 1");
$page = $db->fetch();

$pageTitle = ($page['title'] ?? 'About Us') . ' - ' . APP_NAME;
$pageDescription = $page['excerpt'] ?? 'Learn more about our company, mission, and values';

include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navigation.php';
?>
<section class="hero">
    <div class="container text-center">
        <h1><?php echo e($page['title'] ?? 'About Us'); ?></h1>
        <?php if (!empty($page['excerpt'])): ?>
            <p class="lead"><?php echo e($page['excerpt']); ?></p>
        <?php else: ?>
            <p class="lead">Our Story, Mission, and Values</p>
        <?php endif; ?>
    </div>
</section>

<section>
    <div class="container" style="max-width:820px;">
        <?php if (!empty($page['content'])): ?>
            <div class="page-content">
                <?php echo safe_html($page['content']); ?>
            </div>
        <?php else: ?>
            <p>We are a team of passionate web professionals dedicated to creating exceptional digital experiences. With years of experience, we've helped businesses of all sizes achieve their online goals.</p>
            <p>Our commitment to quality, innovation, and customer satisfaction drives everything we do.</p>
            <ul class="list-unstyled mt-4">
                <li class="mb-3"><i class="fas fa-check text-primary" aria-hidden="true"></i> <span class="visually-hidden">Yes — </span>Professional &amp; Experienced Team</li>
                <li class="mb-3"><i class="fas fa-check text-primary" aria-hidden="true"></i> <span class="visually-hidden">Yes — </span>Custom Solutions for Your Needs</li>
                <li class="mb-3"><i class="fas fa-check text-primary" aria-hidden="true"></i> <span class="visually-hidden">Yes — </span>24/7 Support &amp; Maintenance</li>
                <li class="mb-3"><i class="fas fa-check text-primary" aria-hidden="true"></i> <span class="visually-hidden">Yes — </span>Affordable Pricing</li>
            </ul>
            <p class="mt-4 text-muted"><em>Tip: an admin can edit this page at <code>/admin/pages</code> (create a page with slug <code>about</code>).</em></p>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
