<?php
/**
 * pages/portfolio.php — driven by the `gallery` table
 */
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/Helper.php';
require_once __DIR__ . '/../config/database.php';

$pageTitle = 'Portfolio - ' . APP_NAME;
$pageDescription = 'View our latest projects and case studies';

$db = Database::getInstance();
$db->prepare('SELECT * FROM gallery WHERE is_active=1 ORDER BY display_order, id');
$items = $db->fetchAll();

include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navigation.php';
?>
<section class="hero">
    <div class="container text-center">
        <h1>Our Portfolio</h1>
        <p class="lead">Showcase of Recent Projects</p>
    </div>
</section>

<section>
    <div class="container">
        <h2 class="text-center mb-5">Featured Projects</h2>
        <?php if (empty($items)): ?>
            <p class="text-center text-muted">Portfolio coming soon.</p>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($items as $g): ?>
                    <div class="col-md-6 col-lg-4">
                        <article class="card h-100">
                            <img src="<?php echo e($g['image']); ?>" alt="<?php echo e($g['alt_text'] ?: $g['title']); ?>" class="card-img-top" style="aspect-ratio:16/10;object-fit:cover;" loading="lazy">
                            <div class="card-body">
                                <h3 class="h5 card-title"><?php echo e($g['title']); ?></h3>
                                <?php if (!empty($g['description'])): ?>
                                    <p class="card-text text-muted"><?php echo e($g['description']); ?></p>
                                <?php endif; ?>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
