<?php
/**
 * includes/navigation.php
 * Main navigation menu
 */
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?php echo url('/'); ?>">
            <?php echo e(setting('site_name', APP_NAME)); ?>
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
                    <a class="nav-link" href="<?php echo url('/about'); ?>">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo url('/services'); ?>">Services</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo url('/portfolio'); ?>">Portfolio</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo url('/blog'); ?>">Blog</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo url('/contact'); ?>">Contact</a>
                </li>
                <?php if (isAuthenticated()): $u = getUser(); ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle" aria-hidden="true"></i> <?php echo e($u['first_name'] ?: $u['username'] ?? 'User'); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="<?php echo url('/admin'); ?>"><i class="fas fa-tachometer-alt" aria-hidden="true"></i> Dashboard</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo url('/logout'); ?>"><i class="fas fa-sign-out-alt" aria-hidden="true"></i> Logout</a></li>
                    </ul>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo url('/login'); ?>"><i class="fas fa-sign-in-alt" aria-hidden="true"></i> Login</a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
