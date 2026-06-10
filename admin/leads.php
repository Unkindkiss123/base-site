<?php
/**
 * admin/leads.php
 * Lead Management
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/Helper.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../config/database.php';

if (!isAuthenticated() || !hasPermission('manage_leads')) {
    redirect(url('/admin/login.php'));
}

$db = Database::getInstance();

// Get leads
$db->prepare(
    'SELECT * FROM leads 
     ORDER BY created_at DESC 
     LIMIT 100'
);
$leads = $db->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leads - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="background-color: #f8f9fa;">
    <div class="container-fluid p-4">
        <div class="row mb-4">
            <div class="col">
                <h1>Lead Management</h1>
            </div>
            <div class="col-auto">
                <a href="<?php echo url('/admin'); ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leads as $lead): ?>
                                <tr>
                                    <td><?php echo e($lead['name']); ?></td>
                                    <td><a href="mailto:<?php echo e($lead['email']); ?>"><?php echo e($lead['email']); ?></a></td>
                                    <td><?php echo e($lead['subject']); ?></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo ucfirst(e($lead['status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $lead['priority'] === 'high' ? 'danger' : ($lead['priority'] === 'medium' ? 'warning' : 'success'); ?>">
                                            <?php echo ucfirst(e($lead['priority'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDate($lead['created_at'], 'M d, Y'); ?></td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i>
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
