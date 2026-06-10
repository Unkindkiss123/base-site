<?php
/**
 * pages/privacy-policy.php
 * Privacy Policy Page
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/Helper.php';

$pageTitle = 'Privacy Policy - ' . APP_NAME;

include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navigation.php';
?>

<section style="padding: 60px 0;">
    <div class="container" style="max-width: 800px;">
        <h1>Privacy Policy</h1>
        <p class="text-muted">Last Updated: June 2026</p>
        
        <h3 class="mt-5">Introduction</h3>
        <p>We are committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website.</p>
        
        <h3 class="mt-5">Information We Collect</h3>
        <p>We may collect information about you in a variety of ways. The information we may collect on the site includes:</p>
        <ul>
            <li><strong>Personal Data:</strong> Name, email address, phone number, and other contact information you provide voluntarily.</li>
            <li><strong>Device Data:</strong> IP address, browser type, operating system, and pages visited.</li>
            <li><strong>Usage Data:</strong> How you interact with our website and services.</li>
        </ul>
        
        <h3 class="mt-5">Use of Your Information</h3>
        <p>Having accurate information about you permits us to provide you with a smooth, efficient, and customized experience. Specifically, we may use information collected about you via the site to:</p>
        <ul>
            <li>Generate a personal profile about you so that future visits to the site will be personalized as possible.</li>
            <li>Increase the efficiency and operation of the site.</li>
            <li>Monitor and analyze usage and trends to improve your experience with the site.</li>
            <li>Notify you of updates to the site.</li>
            <li>Offer new products, services, and/or recommendations to you.</li>
        </ul>
        
        <h3 class="mt-5">Disclosure of Your Information</h3>
        <p>We may share information we have collected about you in certain situations:</p>
        <ul>
            <li><strong>By Law or to Protect Rights:</strong> If we believe the release of information about you is necessary to comply with the law.</li>
            <li><strong>Third-Party Service Providers:</strong> We may share your information with parties who perform services for us.</li>
            <li><strong>Business Transfers:</strong> If we are involved in a merger, acquisition, or sale of all or a portion of our assets.</li>
        </ul>
        
        <h3 class="mt-5">Security of Your Information</h3>
        <p>We use administrative, technical, and physical security measures to protect your personal information. However, perfect security cannot be guaranteed.</p>
        
        <h3 class="mt-5">Contact Us</h3>
        <p>If you have questions or comments about this Privacy Policy, please contact us at:</p>
        <p>
            <strong>Email:</strong> <a href="mailto:privacy@example.com">privacy@example.com</a>
        </p>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
