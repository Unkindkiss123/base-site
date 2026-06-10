<?php
/**
 * pages/blog.php
 * Blog Listing Page
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/Helper.php';
require_once __DIR__ . '/../config/database.php';

$pageTitle = 'Blog - ' . APP_NAME;
$pageDescription = 'Read our latest articles and insights';

$db = Database::getInstance();
$page = max(1, (int)(get('page', 1)));
$perPage = 6;
$offset = ($page - 1) * $perPage;

// Get published posts
$db->prepare(
    'SELECT * FROM blog_posts 
     WHERE status = "published" 
     ORDER BY published_at DESC 
     LIMIT :limit OFFSET :offset'
);
$db->bind(':limit', $perPage, PDO::PARAM_INT);
$db->bind(':offset', $offset, PDO::PARAM_INT);
$posts = $db->fetchAll();

include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navigation.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container text-center">
        <h1>Blog</h1>
        <p class="lead">Articles, Tips, and Insights</p>
    </div>
</section>

<!-- Blog Posts -->
<section>
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <?php if (!empty($posts)): ?>
                    <?php foreach ($posts as $post): ?>
                        <article class="blog-card">
                            <img src="<?php echo e($post['featured_image']); ?>" alt="<?php echo e($post['title']); ?>" class="blog-card-image" loading="lazy">
                            <div class="blog-card-body">
                                <div class="blog-card-meta">
                                    <i class="fas fa-calendar"></i> <?php echo formatDate($post['published_at'], 'M d, Y'); ?>
                                </div>
                                <h3 class="blog-card-title"><?php echo e($post['title']); ?></h3>
                                <p class="blog-card-excerpt"><?php echo e(truncate($post['excerpt'], 150)); ?></p>
                                <a href="<?php echo url('/pages/blog-post.php?slug=' . $post['slug']); ?>" class="blog-card-link">
                                    Read More <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted">No posts found.</p>
                <?php endif; ?>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="card sticky-top" style="top: 20px;">
                    <div class="card-body">
                        <h5 class="card-title">Search Articles</h5>
                        <form method="GET">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search posts..." name="q">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
