<?php
require_once __DIR__ . '/functions.php';
$pageTitle = 'Home';

// Image URLs from a reliable hosting service
$images = [
    'hero' => 'https://images.unsplash.com/photo-1599351431202-1e0f0137899a?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80',
    'about' => 'https://images.unsplash.com/photo-1567892243-bb5c71a7a4b4?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80',
    // Add more image URLs as needed
];

// Create images directory if it doesn't exist
$imagesDir = __DIR__ . '/images';
if (!file_exists($imagesDir)) {
    mkdir($imagesDir, 0755, true);
}

include 'header.php';
?>

<!-- Hero Section -->
<section class="hero-section bg-dark text-white py-5">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <h1 class="display-4 fw-bold mb-4">Premium Barber Shop Experience</h1>
                <p class="lead mb-4">Your style is our passion. Experience the perfect blend of traditional barbering and modern techniques.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="book_appointment.php" class="btn btn-primary btn-lg px-4">Book Now</a>
                    <a href="services.php" class="btn btn-outline-light btn-lg px-4">Our Services</a>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="<?php echo $images['hero']; ?>" alt="Professional Barber" class="img-fluid rounded-3 shadow-lg">
            </div>
        </div>
    </div>
</section>

<!-- Rest of the home page content... -->

<?php include 'footer.php'; ?>
