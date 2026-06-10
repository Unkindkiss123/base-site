<?php
/**
 * includes/footer.php
 * Footer section
 */
?>
<footer class="bg-dark text-light py-5 mt-5">
    <div class="container">
        <div class="row mb-4">
            <!-- About -->
            <div class="col-md-3 mb-4">
                <h5 class="mb-3">
                    <img src="<?php echo asset('images/logo-white.png'); ?>" alt="<?php echo e(APP_NAME); ?>" height="30" class="d-inline-block">
                    <?php echo e(APP_NAME); ?>
                </h5>
                <p class="text-muted">Professional website template built with HTML5, CSS3, Bootstrap 5, and PHP 8.3+.</p>
                <div class="social-links">
                    <a href="#" class="text-light me-2"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="text-light me-2"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-light me-2"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-light"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="col-md-3 mb-4">
                <h5 class="mb-3">Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="<?php echo url('/'); ?>" class="text-muted text-decoration-none">Home</a></li>
                    <li><a href="<?php echo url('/pages/about.php'); ?>" class="text-muted text-decoration-none">About Us</a></li>
                    <li><a href="<?php echo url('/pages/services.php'); ?>" class="text-muted text-decoration-none">Services</a></li>
                    <li><a href="<?php echo url('/pages/contact.php'); ?>" class="text-muted text-decoration-none">Contact</a></li>
                </ul>
            </div>
            
            <!-- Services -->
            <div class="col-md-3 mb-4">
                <h5 class="mb-3">Services</h5>
                <ul class="list-unstyled">
                    <li><a href="#" class="text-muted text-decoration-none">Web Design</a></li>
                    <li><a href="#" class="text-muted text-decoration-none">Development</a></li>
                    <li><a href="#" class="text-muted text-decoration-none">Consulting</a></li>
                    <li><a href="#" class="text-muted text-decoration-none">Support</a></li>
                </ul>
            </div>
            
            <!-- Contact -->
            <div class="col-md-3 mb-4">
                <h5 class="mb-3">Contact</h5>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="fas fa-map-marker-alt text-primary"></i>
                        123 Main Street, City
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-phone text-primary"></i>
                        <a href="tel:+15550000000" class="text-muted text-decoration-none">+1 (555) 000-0000</a>
                    </li>
                    <li>
                        <i class="fas fa-envelope text-primary"></i>
                        <a href="mailto:info@example.com" class="text-muted text-decoration-none">info@example.com</a>
                    </li>
                </ul>
            </div>
        </div>
        
        <hr class="bg-secondary">
        
        <!-- Bottom -->
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="text-muted mb-0">&copy; <?php echo date('Y'); ?> <?php echo e(APP_NAME); ?>. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-end">
                <a href="<?php echo url('/pages/privacy-policy.php'); ?>" class="text-muted text-decoration-none me-3">Privacy Policy</a>
                <a href="<?php echo url('/pages/terms.php'); ?>" class="text-muted text-decoration-none me-3">Terms & Conditions</a>
                <a href="<?php echo url('/pages/cookie-policy.php'); ?>" class="text-muted text-decoration-none">Cookie Policy</a>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script src="<?php echo asset('js/main.js?v=1.0.0'); ?>"></script>

</body>
</html>
