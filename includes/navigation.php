<?php
/**
 * includes/navigation.php
 * Main navigation menu
 */
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?php echo url('/'); ?>">
            <img src="<?php echo asset('images/logo.png'); ?>" alt="<?php echo e(APP_NAME); ?>" height="40" class="d-inline-block align-text-top">
            <?php echo e(APP_NAME); ?>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo url('/'); ?>">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo url('/pages/about.php'); ?>">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo url('/pages/services.php'); ?>">Services</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo url('/pages/portfolio.php'); ?>">Portfolio</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo url('/pages/blog.php'); ?>">Blog</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo url('/pages/contact.php'); ?>">Contact</a>
                </li>
                <?php if (isAuthenticated()): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle"></i> <?php echo e(getUser()['first_name'] ?? 'User'); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="<?php echo url('/admin'); ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo url('/admin/logout.php'); ?>"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo url('/admin/login.php'); ?>"><i class="fas fa-sign-in-alt"></i> Login</a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
