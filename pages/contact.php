<?php
/**
 * pages/contact.php
 * Contact Us Page
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/Helper.php';
require_once __DIR__ . '/../includes/Security.php';
require_once __DIR__ . '/../includes/Validator.php';
require_once __DIR__ . '/../config/database.php';

$pageTitle = 'Contact Us - ' . APP_NAME;
$pageDescription = 'Get in touch with us for inquiries and support';

$message = null;
$db = Database::getInstance();

if (isPost()) {
    $name = Security::sanitize(post('name', ''));
    $email = Security::sanitize(post('email', ''));
    $phone = Security::sanitize(post('phone', ''));
    $subject = Security::sanitize(post('subject', ''));
    $message_text = Security::sanitize(post('message', ''));
    
    $validator = new Validator([
        'name' => $name,
        'email' => $email,
        'subject' => $subject,
        'message' => $message_text
    ]);
    
    $validator->required('name', 'Name')
              ->required('email', 'Email')
              ->email('email')
              ->required('subject', 'Subject')
              ->required('message', 'Message')
              ->minLength('message', 10, 'Message');
    
    if ($validator->passes()) {
        $db->prepare(
            'INSERT INTO leads (name, email, phone, subject, message, source, ip_address, user_agent) 
             VALUES (:name, :email, :phone, :subject, :message, :source, :ip, :agent)'
        );
        $db->bind(':name', $name);
        $db->bind(':email', $email);
        $db->bind(':phone', $phone);
        $db->bind(':subject', $subject);
        $db->bind(':message', $message_text);
        $db->bind(':source', 'website');
        $db->bind(':ip', Security::getClientIP());
        $db->bind(':agent', $_SERVER['HTTP_USER_AGENT'] ?? '');
        
        if ($db->execute()) {
            flash('success', 'Thank you for your message! We\'ll get back to you soon.');
            redirect(url('/pages/contact.php'));
        }
    }
}

include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/navigation.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container text-center">
        <h1>Contact Us</h1>
        <p class="lead">Get in Touch - We'd Love to Hear From You</p>
    </div>
</section>

<!-- Contact Section -->
<section>
    <div class="container">
        <div class="row">
            <!-- Contact Form -->
            <div class="col-lg-8 mb-5">
                <?php 
                $flash = getFlash();
                if ($flash): 
                ?>
                    <div class="alert alert-<?php echo e($flash['type']); ?> alert-dismissible fade show" role="alert">
                        <?php echo e($flash['message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <h3 class="mb-4">Send us a Message</h3>
                <form method="POST" class="contact-form needs-validation">
                    <?php echo Security::getCSRFField(); ?>
                    
                    <div class="form-group">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="phone" name="phone">
                    </div>
                    
                    <div class="form-group">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="subject" name="subject" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="message" class="form-label">Message</label>
                        <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </form>
            </div>
            
            <!-- Contact Info -->
            <div class="col-lg-4">
                <h3 class="mb-4">Contact Information</h3>
                
                <div class="mb-4">
                    <h5 class="mb-2">
                        <i class="fas fa-map-marker-alt text-primary"></i> Address
                    </h5>
                    <p class="text-muted">123 Main Street<br>City, State 12345<br>Country</p>
                </div>
                
                <div class="mb-4">
                    <h5 class="mb-2">
                        <i class="fas fa-phone text-primary"></i> Phone
                    </h5>
                    <p class="text-muted">
                        <a href="tel:+15550000000" class="text-decoration-none">+1 (555) 000-0000</a>
                    </p>
                </div>
                
                <div class="mb-4">
                    <h5 class="mb-2">
                        <i class="fas fa-envelope text-primary"></i> Email
                    </h5>
                    <p class="text-muted">
                        <a href="mailto:info@example.com" class="text-decoration-none">info@example.com</a>
                    </p>
                </div>
                
                <div class="mb-4">
                    <h5 class="mb-2">
                        <i class="fas fa-clock text-primary"></i> Hours
                    </h5>
                    <p class="text-muted">
                        Monday - Friday: 9:00 AM - 5:00 PM<br>
                        Saturday: 10:00 AM - 3:00 PM<br>
                        Sunday: Closed
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
