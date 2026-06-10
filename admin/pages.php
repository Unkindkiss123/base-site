<?php
/**
 * admin/pages.php
 * Page Management
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/Helper.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../config/database.php';

if (!isAuthenticated() || !hasPermission('manage_pages')) {
    redirect(url('/admin/login.php'));
}

$db = Database::getInstance();

// Get pages
$db->prepare(
    'SELECT p.*, u.first_name, u.last_name 
     FROM pages p 
     LEFT JOIN users u ON p.author_id = u.id 
     ORDER BY p.created_at DESC'
);
$pages = $db->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pages - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="background-color: #f8f9fa;">
    <div class="container-fluid p-4">
        <div class="row mb-4">
            <div class="col">
                <h1>Page Management</h1>
            </div>
            <div class="col-auto">
                <a href="<?php echo url('/admin'); ?>" class="btn btn-secondary me-2">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <a href="#" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Page
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
                                <th>Slug</th>
                                <th>Author</th>
                                <th>Status</th>
                                <th>Views</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pages as $page): ?>
                                <tr>
                                    <td><?php echo e($page['title']); ?></td>
                                    <td><code><?php echo e($page['slug']); ?></code></td>
                                    <td><?php echo e($page['first_name'] . ' ' . $page['last_name']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $page['status'] === 'published' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst(e($page['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $page['view_count']; ?></td>
                                    <td><?php echo formatDate($page['created_at'], 'M d, Y'); ?></td>
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
