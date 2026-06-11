<?php
/**
 * pages/services.php — driven by the `services` table
 */
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/Helper.php';
require_once __DIR__ . '/../config/database.php';

$pageTitle = 'Services - ' . APP_NAME;
$pageDescription = 'Explore our professional services and solutions';

$db = Database::getInstance();
$db->prepare('SELECT * FROM services WHERE is_active=1 ORDER BY display_order, id');
$services = $db->fetchAll();

include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navigation.php';
?>
<section class="hero">
    <div class="container text-center">
        <h1>Our Services</h1>
        <p class="lead">Comprehensive Solutions for Your Business</p>
    </div>
</section>

<section>
    <div class="container">
        <?php if (empty($services)): ?>
            <p class="text-center text-muted">Services coming soon.</p>
        <?php else: ?>
            <div class="row">
                <?php foreach ($services as $s): ?>
                    <div class="col-md-6 mb-4">
                        <article class="card h-100">
                            <div class="card-body">
                                <div style="font-size:3rem;color:var(--brand-primary);margin-bottom:20px;">
                                    <i class="fas <?php echo e($s['icon'] ?: 'fa-cube'); ?>" aria-hidden="true"></i>
                                </div>
                                <h2 class="card-title h4"><?php echo e($s['title']); ?></h2>
                                <p class="card-text"><?php echo nl2br(e($s['description'])); ?></p>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="cta-section">
    <div class="container text-center">
        <h2>Get Started Today</h2>
        <p>Let's discuss how we can help your business grow</p>
        <a href="<?php echo url('/contact'); ?>" class="btn btn-light btn-lg">Request a Quote</a>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
