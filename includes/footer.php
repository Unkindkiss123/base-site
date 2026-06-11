<?php
/**
 * includes/footer.php
 * Footer section — driven by the `settings` table where possible.
 */
$siteName = setting('site_name', APP_NAME);
$siteEmail = setting('site_email', 'info@example.com');
$sitePhone = setting('site_phone', '+1 (555) 000-0000');
$siteAddress = setting('site_address', '123 Main Street, City');
$facebook = setting('facebook_url', '');
$twitter = setting('twitter_url', '');
$instagram = setting('instagram_url', '');
$linkedin = setting('linkedin_url', '');
?>
<footer class="bg-dark text-light py-5 mt-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-3 mb-4">
                <h5 class="mb-3"><?php echo e($siteName); ?></h5>
                <p class="text-muted"><?php echo e(setting('site_description', 'Professional website template')); ?></p>
                <div class="social-links">
                    <?php if ($facebook): ?><a href="<?php echo e($facebook); ?>" class="text-light me-2" aria-label="Facebook"><i class="fab fa-facebook" aria-hidden="true"></i></a><?php endif; ?>
                    <?php if ($twitter): ?><a href="<?php echo e($twitter); ?>" class="text-light me-2" aria-label="Twitter"><i class="fab fa-twitter" aria-hidden="true"></i></a><?php endif; ?>
                    <?php if ($instagram): ?><a href="<?php echo e($instagram); ?>" class="text-light me-2" aria-label="Instagram"><i class="fab fa-instagram" aria-hidden="true"></i></a><?php endif; ?>
                    <?php if ($linkedin): ?><a href="<?php echo e($linkedin); ?>" class="text-light" aria-label="LinkedIn"><i class="fab fa-linkedin" aria-hidden="true"></i></a><?php endif; ?>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <h5 class="mb-3">Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="<?php echo url('/'); ?>" class="text-muted text-decoration-none">Home</a></li>
                    <li><a href="<?php echo url('/about'); ?>" class="text-muted text-decoration-none">About Us</a></li>
                    <li><a href="<?php echo url('/services'); ?>" class="text-muted text-decoration-none">Services</a></li>
                    <li><a href="<?php echo url('/contact'); ?>" class="text-muted text-decoration-none">Contact</a></li>
                </ul>
            </div>

            <div class="col-md-3 mb-4">
                <h5 class="mb-3">Services</h5>
                <ul class="list-unstyled">
                    <li><a href="<?php echo url('/services'); ?>" class="text-muted text-decoration-none">Web Design</a></li>
                    <li><a href="<?php echo url('/services'); ?>" class="text-muted text-decoration-none">Development</a></li>
                    <li><a href="<?php echo url('/services'); ?>" class="text-muted text-decoration-none">Consulting</a></li>
                    <li><a href="<?php echo url('/services'); ?>" class="text-muted text-decoration-none">Support</a></li>
                </ul>
            </div>

            <div class="col-md-3 mb-4">
                <h5 class="mb-3">Contact</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fas fa-map-marker-alt text-primary" aria-hidden="true"></i> <?php echo e($siteAddress); ?></li>
                    <li class="mb-2"><i class="fas fa-phone text-primary" aria-hidden="true"></i>
                        <a href="tel:<?php echo e(preg_replace('/[^0-9+]/', '', $sitePhone)); ?>" class="text-muted text-decoration-none"><?php echo e($sitePhone); ?></a>
                    </li>
                    <li><i class="fas fa-envelope text-primary" aria-hidden="true"></i>
                        <a href="mailto:<?php echo e($siteEmail); ?>" class="text-muted text-decoration-none"><?php echo e($siteEmail); ?></a>
                    </li>
                </ul>
            </div>
        </div>

        <hr class="bg-secondary">

        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="text-muted mb-0">&copy; <?php echo date('Y'); ?> <?php echo e($siteName); ?>. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-end">
                <a href="<?php echo url('/privacy-policy'); ?>" class="text-muted text-decoration-none me-3">Privacy Policy</a>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo asset('js/main.js?v=1.0.0'); ?>"></script>

</body>
</html>
