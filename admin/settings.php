<?php
/**
 * admin/settings.php
 * Site Settings
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/Helper.php';
require_once __DIR__ . '/../includes/Auth.php';

if (!isAuthenticated() || !hasPermission('manage_settings')) {
    redirect(url('/admin/login.php'));
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="background-color: #f8f9fa;">
    <div class="container-fluid p-4">
        <div class="row mb-4">
            <div class="col">
                <h1>Site Settings</h1>
            </div>
            <div class="col-auto">
                <a href="<?php echo url('/admin'); ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Settings management panel. Update your site configuration here.
                </div>
                
                <form>
                    <div class="mb-3">
                        <label for="site_name" class="form-label">Site Name</label>
                        <input type="text" class="form-control" id="site_name" name="site_name" value="Base Site">
                    </div>
                    
                    <div class="mb-3">
                        <label for="site_description" class="form-label">Site Description</label>
                        <textarea class="form-control" id="site_description" name="site_description" rows="3">Professional website template</textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="site_email" class="form-label">Contact Email</label>
                        <input type="email" class="form-control" id="site_email" name="site_email" value="info@example.com">
                    </div>
                    
                    <div class="mb-3">
                        <label for="site_phone" class="form-label">Contact Phone</label>
                        <input type="tel" class="form-control" id="site_phone" name="site_phone" value="+1 (555) 000-0000">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Settings
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
