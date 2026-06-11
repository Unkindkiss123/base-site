<?php
/**
 * pages/blog-post.php — single blog post by slug
 */
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/Helper.php';
require_once __DIR__ . '/../config/database.php';

$slug = (string)($_GET['slug'] ?? '');
$db = Database::getInstance();
$db->prepare('SELECT bp.*, u.first_name, u.last_name, c.name AS category_name FROM blog_posts bp LEFT JOIN users u ON bp.author_id=u.id LEFT JOIN categories c ON bp.category_id=c.id WHERE bp.slug = :slug AND bp.status = "published" LIMIT 1');
$db->bind(':slug', $slug);
$post = $db->fetch();

if (!$post) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    return;
}

// Increment view counter (best-effort)
$db->prepare('UPDATE blog_posts SET view_count = view_count + 1 WHERE id = :id');
$db->bind(':id', (int)$post['id']);
$db->execute();

$pageTitle = $post['title'] . ' - ' . APP_NAME;
$pageDescription = $post['excerpt'] ?: $post['meta_description'] ?: '';

include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navigation.php';
?>
<article style="padding:60px 0;">
    <div class="container" style="max-width:820px;">
        <a href="<?php echo url('/blog'); ?>" class="text-decoration-none">
            <i class="fas fa-arrow-left" aria-hidden="true"></i> Back to blog
        </a>
        <h1 class="mt-3"><?php echo e($post['title']); ?></h1>
        <p class="text-muted">
            <?php if (!empty($post['published_at'])): ?>
                <i class="fas fa-calendar" aria-hidden="true"></i> <time datetime="<?php echo e($post['published_at']); ?>"><?php echo e(formatDate($post['published_at'], 'M d, Y')); ?></time>
            <?php endif; ?>
            <?php if (!empty($post['category_name'])): ?>
                · <i class="fas fa-folder" aria-hidden="true"></i> <?php echo e($post['category_name']); ?>
            <?php endif; ?>
            <?php $author = trim(($post['first_name'] ?? '') . ' ' . ($post['last_name'] ?? '')); ?>
            <?php if ($author): ?>
                · <i class="fas fa-user" aria-hidden="true"></i> <?php echo e($author); ?>
            <?php endif; ?>
        </p>
        <?php if (!empty($post['featured_image'])): ?>
            <img src="<?php echo e($post['featured_image']); ?>" alt="" class="img-fluid rounded mb-4" loading="lazy">
        <?php endif; ?>
        <div class="page-content" style="line-height:1.75;">
            <?php echo safe_html($post['content']); ?>
        </div>
    </div>
</article>
<?php include __DIR__ . '/../includes/footer.php'; ?>
