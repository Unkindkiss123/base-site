<?php
/**
 * admin/login.php
 * Admin Login Page
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/Helper.php';
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/RateLimiter.php';

// Redirect if already authenticated
if (isAuthenticated()) {
    redirect(url('/admin'));
}

$auth = new Auth();
$rateLimiter = new RateLimiter('login');
$message = null;
$error = false;

if (isPost()) {
    // CSRF check
    if (!Security::verifyCSRFToken(post('csrf_token', ''))) {
        $message = 'Invalid request token. Please try again.';
        $error = true;
    } elseif ($rateLimiter->isLimited(5, 15)) {
        $message = 'Too many login attempts. Please try again later.';
        $error = true;
    } else {
        $email      = post('email', '');
        $password   = post('password', '');
        $rememberMe = post('remember_me') === '1';

        $result = $auth->login($email, $password, $rememberMe);

        if (!empty($result['success'])) {
            redirect(url('/admin'));
        } elseif (!empty($result['requires_mfa'])) {
            redirect(url('/2fa'));
        } else {
            $rateLimiter->hit();
            $message = $result['message'];
            $error = true;
        }
    }
}

// Don't include full head for login page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo e(APP_NAME); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        }
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 15px;
        }
        .login-card {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border-radius: 8px;
            border: none;
        }
        .login-header {
            text-align: center;
            padding: 2rem 1rem 1rem;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        .login-header h1 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        .login-body {
            padding: 2rem 1.5rem;
        }
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        .btn-login {
            padding: 0.75rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card login-card">
            <div class="login-header">
                <h1><i class="fas fa-lock"></i> Admin Login</h1>
                <p class="text-muted mb-0">Sign in to your account</p>
            </div>
            <div class="login-body">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $error ? 'danger' : 'success'; ?> alert-dismissible fade show">
                        <?php echo e($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <?php echo Security::getCSRFField(); ?>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required autofocus>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <div class="mb-3 form-check">
                        <input class="form-check-input" type="checkbox" id="remember_me" name="remember_me" value="1">
                        <label class="form-check-label" for="remember_me">Remember me for 30 days</label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-login w-100">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>
                
                <hr>
                
                <p class="text-center text-muted small mb-0">
                    <a href="<?php echo url('/forgot-password'); ?>" class="text-decoration-none">Forgot your password?</a>
                    &middot;
                    <a href="<?php echo url('/contact'); ?>" class="text-decoration-none">Need help?</a>
                </p>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
