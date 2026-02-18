<?php
require_once __DIR__ . '/functions.php';
$pageTitle = 'About Us';

// Image URLs from a reliable hosting service
$images = [
    'about' => 'images/services/haircut.jpg',
    'team1' => 'images/services/beard.jpg',
    'team2' => 'images/services/shave.jpg',
    'team3' => 'images/services/beard.jpg'
];

include 'header.php';
?>

<!-- Hero Section -->
<section class="hero-section bg-light py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Our Story</h1>
                <p class="lead">Crafting exceptional grooming experiences since 2010</p>
                <p>At Barber Shop, we believe that a great haircut is more than just a service – it's an art form. Our skilled barbers combine traditional techniques with modern styles to give you the perfect look.</p>
            </div>
            <div class="col-lg-6">
                <img src="<?php echo $images['about']; ?>" alt="Our Barbershop" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</section>

<!-- Our Team Section -->
<section class="team-section py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Meet Our Expert Barbers</h2>
            <p class="section-subtitle">Professional, skilled, and passionate about their craft</p>
        </div>
        
        <div class="row g-4">
            <!-- Team Member 1 -->
            <div class="col-md-4">
                <div class="team-card text-center p-4 rounded shadow-sm">
                    <div class="team-img mb-3">
                        <img src="<?php echo $images['team1']; ?>" alt="John Doe" class="img-fluid rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                    </div>
                    <h4>John Doe</h4>
                    <p class="text-muted">Master Barber / Owner</p>
                    <p>With over 15 years of experience, John specializes in classic and modern haircuts.</p>
                    <div class="social-links">
                        <a href="#" class="text-primary me-2"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-primary me-2"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-primary"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>
            
            <!-- Team Member 2 -->
            <div class="col-md-4">
                <div class="team-card text-center p-4 rounded shadow-sm">
                    <div class="team-img mb-3">
                        <img src="<?php echo $images['team2']; ?>" alt="Mike Smith" class="img-fluid rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                    </div>
                    <h4>Mike Smith</h4>
                    <p class="text-muted">Senior Stylist</p>
                    <p>Specializing in beard grooming and straight razor shaves, Mike brings 10 years of expertise.</p>
                    <div class="social-links">
                        <a href="#" class="text-primary me-2"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-primary me-2"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            
            <!-- Team Member 3 -->
            <div class="col-md-4">
                <div class="team-card text-center p-4 rounded shadow-sm">
                    <div class="team-img mb-3">
                        <img src="<?php echo $images['team3']; ?>" alt="David Wilson" class="img-fluid rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                    </div>
                    <h4>David Wilson</h4>
                    <p class="text-muted">Master Barber</p>
                    <p>David is our expert in modern fades and creative hairstyling with 8 years of experience.</p>
                    <div class="social-links">
                        <a href="#" class="text-primary me-2"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-primary"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="why-choose-us bg-light py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Why Choose Us</h2>
            <p class="section-subtitle">Experience the difference with our premium services</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-box text-center p-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-cut fa-3x text-primary"></i>
                    </div>
                    <h4>Expert Barbers</h4>
                    <p>Our team consists of highly skilled and experienced barbers who are passionate about their craft.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-box text-center p-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-spa fa-3x text-primary"></i>
                    </div>
                    <h4>Premium Products</h4>
                    <p>We use only the highest quality grooming products for the best results.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-box text-center p-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-heart fa-3x text-primary"></i>
                    </div>
                    <h4>Customer Satisfaction</h4>
                    <p>Your satisfaction is our top priority. We ensure you leave looking and feeling your best.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
