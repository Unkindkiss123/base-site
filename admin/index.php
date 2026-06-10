<?php
/**
 * admin/index.php
 * Admin Dashboard
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/Helper.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../config/database.php';

// Check authentication
if (!isAuthenticated()) {
    redirect(url('/admin/login.php'));
}

$db = Database::getInstance();
user_data = getUser();

// Get statistics
$db->prepare('SELECT COUNT(*) as count FROM users');
$userCount = $db->fetch()['count'];

$db->prepare('SELECT COUNT(*) as count FROM blog_posts WHERE status = "published"');
$postCount = $db->fetch()['count'];

$db->prepare('SELECT COUNT(*) as count FROM leads WHERE status = "new"');
$leadCount = $db->fetch()['count'];

$db->prepare('SELECT COUNT(*) as count FROM pages WHERE status = "published"');
$pageCount = $db->fetch()['count'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin <?php echo e(APP_NAME); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            background-color: #343a40;
            min-height: 100vh;
            padding: 20px 0;
        }
        .sidebar a {
            color: #adb5bd;
            text-decoration: none;
            padding: 10px 20px;
            display: block;
            transition: all 0.3s ease;
        }
        .sidebar a:hover,
        .sidebar a.active {
            color: #fff;
            background-color: #007bff;
            padding-left: 30px;
        }
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #007bff;
        }
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .stat-icon {
            font-size: 3rem;
            color: #e9ecef;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 sidebar">
                <h4 class="text-white mb-4 px-3">
                    <i class="fas fa-tachometer-alt"></i> Admin Panel
                </h4>
                <a href="<?php echo url('/admin'); ?>" class="active">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="<?php echo url('/admin/users.php'); ?>">
                    <i class="fas fa-users"></i> Users
                </a>
                <a href="<?php echo url('/admin/pages.php'); ?>">
                    <i class="fas fa-file"></i> Pages
                </a>
                <a href="<?php echo url('/admin/blog.php'); ?>">
                    <i class="fas fa-blog"></i> Blog Posts
                </a>
                <a href="<?php echo url('/admin/leads.php'); ?>">
                    <i class="fas fa-envelope"></i> Leads
                </a>
                <a href="<?php echo url('/admin/settings.php'); ?>">
                    <i class="fas fa-cog"></i> Settings
                </a>
                <hr class="bg-secondary">
                <a href="<?php echo url('/admin/logout.php'); ?>">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 p-4">
                <h1 class="mb-4">Dashboard</h1>
                <p class="text-muted">Welcome back, <?php echo e($user_data['first_name'] ?? 'User'); ?>!</p>
                
                <!-- Statistics -->
                <div class="row">
                    <div class="col-md-6 col-lg-3">
                        <div class="stat-card">
                            <div class="row align-items-center">
                                <div class="col">
                                    <div class="stat-number"><?php echo $userCount; ?></div>
                                    <div class="stat-label">Total Users</div>
                                </div>
                                <div class="col-auto stat-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="stat-card">
                            <div class="row align-items-center">
                                <div class="col">
                                    <div class="stat-number"><?php echo $postCount; ?></div>
                                    <div class="stat-label">Blog Posts</div>
                                </div>
                                <div class="col-auto stat-icon">
                                    <i class="fas fa-blog"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="stat-card">
                            <div class="row align-items-center">
                                <div class="col">
                                    <div class="stat-number"><?php echo $pageCount; ?></div>
                                    <div class="stat-label">Pages</div>
                                </div>
                                <div class="col-auto stat-icon">
                                    <i class="fas fa-file"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="stat-card">
                            <div class="row align-items-center">
                                <div class="col">
                                    <div class="stat-number"><?php echo $leadCount; ?></div>
                                    <div class="stat-label">New Leads</div>
                                </div>
                                <div class="col-auto stat-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="row mt-5">
                    <div class="col-12">
                        <h3 class="mb-3">Quick Actions</h3>
                        <a href="<?php echo url('/admin/pages.php?action=create'); ?>" class="btn btn-primary me-2">
                            <i class="fas fa-plus"></i> New Page
                        </a>
                        <a href="<?php echo url('/admin/blog.php?action=create'); ?>" class="btn btn-primary me-2">
                            <i class="fas fa-plus"></i> New Post
                        </a>
                        <a href="<?php echo url('/admin/users.php?action=create'); ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> New User
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
