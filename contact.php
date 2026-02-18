<?php
require_once __DIR__ . '/functions.php';
$pageTitle = 'Contact Us';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Basic validation
    $errors = [];
    if (empty($name)) $errors[] = 'Name is required';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
    if (empty($message)) $errors[] = 'Message is required';
    
    if (empty($errors)) {
        // Include email functions
        require_once __DIR__ . '/includes/email_functions.php';
        
        // Send the email
        if (send_contact_email($name, $email, $phone, $message)) {
            $_SESSION['success'] = 'Thank you for your message! We will get back to you soon.';
            // Clear the form
            $_POST = [];
        } else {
            $_SESSION['error'] = 'Sorry, there was an error sending your message. Please try again later.';
        }
        
        header('Location: contact.php');
        exit;
    } else {
        $_SESSION['error'] = implode('<br>', $errors);
    }
}

include 'header.php';
?>

<!-- Contact Header -->
<section class="py-5 bg-light">
    <div class="container text-center">
        <h1 class="display-5 fw-bold">Get In Touch</h1>
        <p class="lead">We'd love to hear from you. Contact us for appointments or any inquiries.</p>
    </div>
</section>

<!-- Contact Form & Info -->
<section class="py-5">
    <div class="container">
        <div class="row g-5">
            <!-- Contact Form -->
            <div class="col-lg-7">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4 p-lg-5">
                        <h2 class="h4 mb-4">Send us a message</h2>
                        
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                        <?php endif; ?>
                        
                        <form action="contact.php" method="POST" id="contactForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" required 
                                           value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="email" name="email" required
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone"
                                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">Your Message *</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required><?php 
                                    echo htmlspecialchars($_POST['message'] ?? ''); 
                                ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg">Send Message</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Contact Information -->
            <div class="col-lg-5">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body p-4 p-lg-5">
                        <h2 class="h4 mb-4">Contact Information</h2>
                        <p>Feel free to reach out to us through any of these channels. We're here to help!</p>
                        
                        <div class="d-flex mb-4">
                            <div class="flex-shrink-0">
                                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                                    <i class="fas fa-map-marker-alt fa-2x"></i>
                                </div>
                            </div>
                            <div class="ms-4">
                                <h5 class="mb-1">Our Location</h5>
                                <p class="mb-0">123 Barber Street, Accra, Ghana</p>
                            </div>
                        </div>
                        
                        <div class="d-flex mb-4">
                            <div class="flex-shrink-0">
                                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                                    <i class="fas fa-phone-alt fa-2x"></i>
                                </div>
                            </div>
                            <div class="ms-4">
                                <h5 class="mb-1">Phone Number</h5>
                                <p class="mb-0">+233 12 345 6789</p>
                                <p class="mb-0">+233 98 765 4321</p>
                            </div>
                        </div>
                        
                        <div class="d-flex mb-4">
                            <div class="flex-shrink-0">
                                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                                    <i class="fas fa-envelope fa-2x"></i>
                                </div>
                            </div>
                            <div class="ms-4">
                                <h5 class="mb-1">Email Address</h5>
                                <p class="mb-0">info@barbershop.com</p>
                                <p class="mb-0">bookings@barbershop.com</p>
                            </div>
                        </div>
                        
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                            </div>
                            <div class="ms-4">
                                <h5 class="mb-1">Working Hours</h5>
                                <ul class="list-unstyled mb-0">
                                    <li>Monday - Friday: 9:00 AM - 8:00 PM</li>
                                    <li>Saturday: 9:00 AM - 6:00 PM</li>
                                    <li>Sunday: 10:00 AM - 4:00 PM</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="mt-5">
                            <h5 class="mb-3">Follow Us</h5>
                            <div class="d-flex gap-3">
                                <a href="#" class="text-primary"><i class="fab fa-facebook-f fa-2x"></i></a>
                                <a href="#" class="text-primary"><i class="fab fa-instagram fa-2x"></i></a>
                                <a href="#" class="text-primary"><i class="fab fa-twitter fa-2x"></i></a>
                                <a href="#" class="text-primary"><i class="fab fa-tiktok fa-2x"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="bg-light py-5">
    <div class="container">
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3970.75505676757!2d-0.2006829248620971!3d5.603639932782052!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xfdf9086b2cda305%3A0x5f6a990914dbd8a9!2sAccra%2C%20Ghana!5e0!3m2!1sen!2sus!4v1620000000000!5m2!1sen!2sus" 
                    width="100%" 
                    height="450" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy"
                    class="rounded">
                </iframe>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
