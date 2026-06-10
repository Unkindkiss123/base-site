<?php
/**
 * includes/head.php
 * HTML head section
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/Security.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    Security::setSessionCookie();
    session_start();
}

// Set security headers
Security::setSecurityHeaders();

// Get page variables
$pageTitle = $pageTitle ?? APP_NAME;
$pageDescription = $pageDescription ?? 'Professional Website Template';
$pageImage = $pageImage ?? asset('images/og-image.png');
$canonical = $canonical ?? $_SERVER['REQUEST_URI'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo e($pageDescription); ?>">
    <meta name="keywords" content="website, template, bootstrap">
    <meta name="author" content="<?php echo e(APP_NAME); ?>">
    
    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo e($pageTitle); ?>">
    <meta property="og:description" content="<?php echo e($pageDescription); ?>">
    <meta property="og:image" content="<?php echo e($pageImage); ?>">
    <meta property="og:url" content="<?php echo e(APP_URL); ?>">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo e($pageTitle); ?>">
    <meta name="twitter:description" content="<?php echo e($pageDescription); ?>">
    <meta name="twitter:image" content="<?php echo e($pageImage); ?>">
    
    <!-- Canonical -->
    <link rel="canonical" href="<?php echo e(APP_URL . $canonical); ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo asset('images/favicon.ico'); ?>">
    <link rel="apple-touch-icon" href="<?php echo asset('images/apple-touch-icon.png'); ?>">
    
    <title><?php echo e($pageTitle); ?></title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    
    <!-- Custom Styles -->
    <link rel="stylesheet" href="<?php echo asset('css/style.css?v=1.0.0'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/custom.css?v=1.0.0'); ?>">
    
    <!-- Preload critical resources -->
    <link rel="preload" href="<?php echo asset('images/logo.png'); ?>" as="image">
    
    <!-- Schema.org Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "<?php echo e(APP_NAME); ?>",
        "url": "<?php echo e(APP_URL); ?>",
        "image": "<?php echo e(asset('images/logo.png')); ?>"
    }
    </script>
</head>
<body>
