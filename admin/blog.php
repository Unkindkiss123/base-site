<?php
/**
 * admin/blog.php
 * Blog Post Management
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/Helper.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../config/database.php';

if (!isAuthenticated() || !hasPermission('manage_blog')) {
    redirect(url('/admin/login.php'));
}

$db = Database::getInstance();

// Get blog posts
$db->prepare(
    'SELECT bp.*, u.first_name, u.last_name, c.name as category_name 
     FROM blog_posts bp 
     LEFT JOIN users u ON bp.author_id = u.id 
     LEFT JOIN categories c ON bp.category_id = c.id 
     ORDER BY bp.created_at DESC'
);
$posts = $db->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Posts - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="background-color: #f8f9fa;">
    <div class="container-fluid p-4">
        <div class="row mb-4">
            <div class="col">
                <h1>Blog Post Management</h1>
            </div>
            <div class="col-auto">
                <a href="<?php echo url('/admin'); ?>" class="btn btn-secondary me-2">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <a href="#" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Post
                </a>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Views</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($posts as $post): ?>
                                <tr>
                                    <td><?php echo e($post['title']); ?></td>
                                    <td><?php echo e($post['first_name'] . ' ' . $post['last_name']); ?></td>
                                    <td><?php echo e($post['category_name'] ?? 'Uncategorized'); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $post['status'] === 'published' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst(e($post['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $post['view_count']; ?></td>
                                    <td><?php echo formatDate($post['created_at'], 'M d, Y'); ?></td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="#" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
