<?php

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db.php';

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

// Fetch active services from database
$services = [];
try {
    $pdo = get_pdo(); // Get PDO instance
    $stmt = $pdo->query("SELECT * FROM services WHERE is_active = 1 ORDER BY name");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Log error and continue with empty services array
    error_log("Error fetching services: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elite Cuts - Premium Barber Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #d4af37;
            --primary-dark: #b3922e;
            --dark: #0f172a;
            --light: #f8fafc;
            --gray: #64748b;
            --dark-bg: #020617;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--dark-bg);
            color: var(--light);
            line-height: 1.6;
        }
        
        /* Navigation */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(15, 23, 42, 0.95);
            padding: 1.2rem 5%;
            z-index: 1000;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }
        
        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }
        
        .logo {
            color: var(--primary);
            font-size: 1.8rem;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            z-index: 1001;
        }
        
        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
            margin-left: auto; /* Push nav links to the right */
        }
        
        .nav-links a {
            color: var(--light);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
            padding: 0.5rem 0;
            position: relative;
        }
        
        .nav-links a:hover {
            color: var(--primary);
        }
        
        .nav-links a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: var(--primary);
            transition: width 0.3s ease;
        }
        
        .nav-links a:hover::after {
            width: 100%;
        }
        
        /* Navigation Buttons */
        .nav-buttons {
            display: flex;
            gap: 1.5rem;
            margin-left: 2rem;
            align-items: center;
            perspective: 1000px;
        }
        
        .nav-buttons .btn {
            padding: 0.6rem 1.2rem;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            text-align: center;
            white-space: nowrap;
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.19, 1, 0.22, 1);
            transform-style: preserve-3d;
            will-change: transform, box-shadow;
            backface-visibility: hidden;
            z-index: 1;
            border: 1px solid transparent;
        }
        
        .nav-buttons .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            transform: scaleX(0);
            transform-origin: right;
            transition: transform 0.5s cubic-bezier(0.19, 1, 0.22, 1);
            z-index: -1;
        }
        
        .nav-buttons .btn:hover::before {
            transform: scaleX(1);
            transform-origin: left;
        }
        
        /* Admin Button */
        .nav-buttons .btn-admin {
            background: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
            box-shadow: 0 4px 15px rgba(255, 200, 0, 0.2);
        }
        
        .nav-buttons .btn-admin:hover {
            background: var(--primary);
            color: #000;
            transform: translateY(-3px) rotateX(10deg);
            box-shadow: 0 7px 20px rgba(255, 200, 0, 0.3);
        }
        
        /* Login Button */
        .nav-buttons .btn-login {
            background: transparent;
            color: var(--light);
            border: 2px solid var(--light);
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.1);
        }
        
        .nav-buttons .btn-login:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            transform: translateY(-3px) rotateX(10deg);
            box-shadow: 0 7px 20px rgba(255, 255, 255, 0.2);
        }
        
        /* Sign Up Button */
        .nav-buttons .btn-signup {
            background: var(--primary);
            color: #000;
            font-weight: 600;
            border: 2px solid var(--primary);
            transition: all 0.3s ease;
        }
        
        .nav-buttons .btn-signup:hover {
            background: transparent;
            color: #fff;
            border-color: var(--primary);
        }
        
        .nav-buttons .btn-signup::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transform: translateX(-100%);
            transition: 0.6s;
        }
        
        .nav-buttons .btn-signup:hover::after {
            transform: translateX(100%);
        }
        
        /* Button Press Effect */
        .nav-buttons .btn:active {
            transform: translateY(-1px) scale(0.98);
            transition: transform 0.1s ease;
        }
        
        /* Mobile Styles */
        @media (max-width: 768px) {
            .nav-buttons {
                flex-direction: column;
                gap: 1rem;
                margin: 1.5rem 0 0;
                padding: 0.5rem 0;
                width: 100%;
                perspective: none;
            }
            
            .nav-buttons .btn {
                width: 100%;
                padding: 1rem;
                margin: 0.5rem 0;
                transform: none !important;
            }
            
            .nav-buttons .btn:hover {
                transform: translateY(-2px) !important;
            }
        }
        
        /* Mobile menu button */
        .mobile-menu-btn {
            display: none;
            background: transparent;
            border: none;
            padding: 0.5rem;
            cursor: pointer;
            z-index: 1001;
        }
        
        .mobile-menu-btn .menu-icon {
            display: block;
            width: 25px;
            height: 2px;
            background: var(--light);
            margin: 5px 0;
            transition: all 0.3s ease;
        }
        
        /* Mobile styles */
        @media (max-width: 768px) {
            .mobile-menu-btn {
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                -webkit-tap-highlight-color: transparent;
            }
            
            .nav-links {
                position: fixed;
                top: 0;
                right: -100%;
                width: 85%;
                max-width: 320px;
                height: 100vh;
                background: #0f172a; /* Solid color instead of semi-transparent */
                padding: 5rem 1.5rem 2rem;
                flex-direction: column;
                align-items: stretch;
                justify-content: flex-start;
                transition: right 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                z-index: 1000;
                margin: 0;
                overflow-y: auto;
                -webkit-transform: translateZ(0);
                transform: translateZ(0);
                -webkit-backface-visibility: hidden;
                backface-visibility: hidden;
                will-change: transform;
                border-left: 1px solid rgba(255, 255, 255, 0.1);
                box-shadow: -4px 0 15px rgba(0, 0, 0, 0.1);
            }
            
            .nav-links.show {
                right: 0;
            }
            
            .nav-links a {
                width: 100%;
                padding: 1rem 0;
                position: relative;
                color: #e2e8f0;
                transition: color 0.2s ease;
            }
            
            .nav-links a:hover {
                color: #ffffff;
            }
            
            .nav-links a::after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 0;
                width: 100%;
                height: 1px;
                background: rgba(255, 255, 255, 0.05);
            }
            
            .nav-buttons {
                flex-direction: column;
                width: 100%;
                margin: 2rem 0 0;
                gap: 1rem;
                padding-top: 1rem;
                position: relative;
            }
            
            .nav-buttons::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 1px;
                background: rgba(255, 255, 255, 0.1);
            }
            
            .nav-buttons .btn {
                width: 100%;
                text-align: center;
                background: rgba(255, 255, 255, 0.05);
                border: 1px solid rgba(255, 255, 255, 0.1);
                color: #e2e8f0;
                transition: all 0.2s ease;
            }
            
            .nav-buttons .btn:hover {
                background: rgba(255, 255, 255, 0.1);
                transform: translateY(-1px);
            }
            
            body.menu-open {
                overflow: hidden;
                height: 100vh;
                position: fixed;
                width: 100%;
            }
        }
        
        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 7rem 5% 5rem;
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(2, 6, 23, 0.9) 0%, rgba(15, 23, 42, 0.8) 100%), 
                        url('images/hero-bg.jpg') center/cover no-repeat;
            z-index: -1;
        }
        
        .hero-content {
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }
        
        .hero-text h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            line-height: 1.2;
            background: linear-gradient(to right, #fff, var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .hero-text p {
            font-size: 1.1rem;
            color: var(--gray);
            margin-bottom: 2.5rem;
            max-width: 90%;
        }
        
        .hero-buttons {
            display: flex;
            gap: 1.5rem;
            margin: 2.5rem 0;
            flex-wrap: wrap;
        }
        
        .hero-btn {
            display: inline-block;
            padding: 1rem 2rem;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            min-width: 200px;
        }
        
        .btn-primary {
            background: var(--primary);
            color: #000;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .btn-primary:hover {
            background: #f5a623;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }
        
        .btn-outline:hover {
            background: var(--primary);
            color: #000;
            transform: translateY(-2px);
        }
        
        .hero-btn i {
            margin-right: 8px;
        }
        
        @media (max-width: 768px) {
            .hero-buttons {
                flex-direction: column;
                gap: 1rem;
            }
            
            .hero-btn {
                width: 100%;
                padding: 1rem;
            }
        }
        
        .stats {
            display: flex;
            gap: 3rem;
        }
        
        .stat-item h3 {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }
        
        .stat-item p {
            font-size: 0.9rem;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .hero-image {
            position: relative;
        }
        
        .hero-image img {
            width: 100%;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        
        /* Services Section */
        .services {
            padding: 6rem 5%;
            background: #0f172a;
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 4rem;
        }
        
        .section-header h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--light);
        }
        
        .section-header p {
            color: var(--gray);
            max-width: 600px;
            margin: 0 auto;
        }
        
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .service-card {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 15px;
            padding: 2.5rem 2rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            border-color: rgba(212, 175, 55, 0.2);
        }
        
        .service-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 1.5rem;
        }
        
        .service-card h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--light);
        }
        
        .service-card p {
            color: var(--gray);
            margin-bottom: 1.5rem;
        }
        
        .service-price {
            color: var(--primary);
            font-weight: 600;
            font-size: 1.2rem;
        }
        
        /* CTA Section */
        .cta {
            padding: 6rem 5%;
            text-align: center;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.9) 0%, rgba(2, 6, 23, 0.9) 100%), 
                        url('images/cta-bg.jpg') center/cover no-repeat;
            position: relative;
        }
        
        .cta h2 {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            color: var(--light);
        }
        
        .cta p {
            color: var(--gray);
            max-width: 600px;
            margin: 0 auto 2.5rem;
            font-size: 1.1rem;
        }
        
        /* Footer */
        footer {
            background: #0a0f1f;
            padding: 4rem 5% 2rem;
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 3rem;
            margin-bottom: 3rem;
        }
        
        .footer-logo {
            color: var(--primary);
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: inline-block;
        }
        
        .footer-about p {
            color: var(--gray);
            margin-bottom: 1.5rem;
        }
        
        .social-links {
            display: flex;
            gap: 1rem;
        }
        
        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            color: var(--light);
            transition: all 0.3s ease;
        }
        
        .social-links a:hover {
            background: var(--primary);
            color: var(--dark);
            transform: translateY(-3px);
        }
        
        .footer-links h3 {
            color: var(--light);
            margin-bottom: 1.5rem;
            font-size: 1.2rem;
        }
        
        .footer-links ul {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 0.8rem;
        }
        
        .footer-links a {
            color: var(--gray);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer-links a:hover {
            color: var(--primary);
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .hero-content {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 3rem;
            }
            
            .hero-text {
                margin: 0 auto;
                max-width: 700px;
            }
            
            .hero-text p {
                margin: 0 auto 2rem;
            }
            
            .hero-buttons {
                justify-content: center;
            }
            
            .stats {
                justify-content: center;
            }
            
            .hero-image {
                max-width: 500px;
                margin: 0 auto;
            }
        }
        
        @media (max-width: 768px) {
            .navbar {
                padding: 1rem 5%;
            }
            
            .nav-links {
                display: none;
            }
            
            .hero {
                padding: 6rem 5% 4rem;
            }
            
            .hero-text h1 {
                font-size: 2.5rem;
            }
            
            .hero-buttons {
                flex-direction: column;
                gap: 1rem;
            }
            
            .btn {
                width: 100%;
            }
            
            .stats {
                flex-direction: column;
                gap: 1.5rem;
                text-align: center;
            }
            
            .service-card {
                text-align: center;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            #navLinks {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: var(--dark);
                padding: 1rem;
                border-radius: 10px;
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            }
            
            #navLinks.show {
                display: block;
            }
            
            #navLinks a {
                display: block;
                padding: 1rem;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }
            
            #navLinks a:last-child {
                border-bottom: none;
            }
        }
        
        @media (max-width: 480px) {
            .hero-text h1 {
                font-size: 2rem;
            }
            
            .section-header h2, .cta h2 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">
                <i class="fas fa-cut"></i>
                <span>EliteCuts</span>
            </a>
            
            <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle menu">
                <span class="menu-icon"></span>
                <span class="menu-icon"></span>
                <span class="menu-icon"></span>
            </button>
            
            <div class="nav-links" id="navLinks">
                <a href="#home" class="nav-link">Home</a>
                <a href="#services" class="nav-link">Services</a>
                <a href="#about" class="nav-link">About</a>
                <a href="#testimonials" class="nav-link">Testimonials</a>
                <a href="#contact" class="nav-link">Contact</a>
                <a href="book_appointment.php" class="nav-link btn-book">Book Appointment</a>
                <div class="nav-buttons">
                    <a href="admin_login.php" class="btn btn-admin">Admin</a>
                    <a href="login.php" class="btn btn-login">Login</a>
                    <a href="register.php" class="btn btn-signup">Sign Up</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content">
            <div class="hero-text">
                <h1>Experience the Art of Precision Haircuts</h1>
                <p>Book your appointment with our professional barbers and get the perfect look that suits your style. Our experts are ready to give you a fresh, clean cut that makes a statement.</p>
                <div class="hero-buttons">
                    <a href="register.php" class="hero-btn btn-primary">Book Appointment</a>
                    <a href="#services" class="hero-btn btn-outline">Our Services</a>
                </div>
                <div class="stats">
                    <div class="stat-item">
                        <h3>500+</h3>
                        <p>Happy Clients</p>
                    </div>
                    <div class="stat-item">
                        <h3>15+</h3>
                        <p>Expert Barbers</p>
                    </div>
                    <div class="stat-item">
                        <h3>100%</h3>
                        <p>Satisfaction</p>
                    </div>
                </div>
            </div>
            <div class="hero-image">
                <img src="https://media.istockphoto.com/id/506514230/photo/beard-grooming.jpg?s=612x612&w=0&k=20&c=QDwo1L8-f3gu7mcHf00Az84fVU8oNpQLgvUw6eGPEkc=" alt="Barber at work">
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services" id="services">
        <div class="section-header">
            <h2>Our Services</h2>
            <p>We offer a wide range of professional barbering services to keep you looking sharp and stylish.</p>
        </div>
        <div class="services-grid">
            <?php if (!empty($services)): ?>
                <?php 
                // Map service names to icons
                $serviceIcons = [
                    'haircut' => 'cut',
                    'beard' => 'user-tie',
                    'shave' => 'spa',
                    'kids' => 'child',
                    'styling' => 'cut',
                    'facial' => 'spa',
                    'hair color' => 'fill-drip',
                    'hair treatment' => 'spa',
                    'head massage' => 'spa',
                    'hot towel' => 'spa',
                    'eyebrow' => 'eye',
                    'mustache' => 'user-tie'
                ];
                
                // Default icon if no match found
                $defaultIcon = 'cut';
                
                foreach ($services as $service): 
                    // Find the most appropriate icon based on service name
                    $icon = $defaultIcon;
                    $serviceName = strtolower($service['name']);
                    
                    foreach ($serviceIcons as $keyword => $iconName) {
                        if (strpos($serviceName, $keyword) !== false) {
                            $icon = $iconName;
                            break;
                        }
                    }
                    
                    // Format price as Ghanaian Cedis
                    $formattedPrice = '₵' . number_format($service['default_price'], 2);
                ?>
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-<?php echo htmlspecialchars($icon); ?>"></i>
                        </div>
                        <h3><?php echo htmlspecialchars($service['name']); ?></h3>
                        <p>Professional <?php echo strtolower(htmlspecialchars($service['name'])); ?> service to keep you looking your best.</p>
                        <div class="service-price"><?php echo $formattedPrice; ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-services">No services available at the moment. Please check back later.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta" id="cta">
        <div class="container">
            <h2>Ready for a Fresh New Look?</h2>
            <p>Book your appointment today and experience the difference of a professional barber.</p>
            <a href="register.php" class="btn btn-primary">Book Now</a>
        </div>
    </section>

    <!-- Footer -->
     <section class="footer" id="footer">
    <footer>
        <div class="footer-content">
            <div class="footer-about">
                <a href="index.php" class="footer-logo">EliteCuts</a>
                <p>Providing premium barbering services with attention to detail and customer satisfaction.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
            <div class="footer-links">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#home">Home</a></li>
                    <li><a href="#services">Services</a></li>
                    <li><a href="#cta">Book Now</a></li>
                    <li><a href="#footer">Contact</a></li>
                </ul>
            </div>
            <div class="footer-links">
                <h3>Services</h3>
                <ul>
                    <li><a href="#">Haircuts</a></li>
                    <li><a href="#">Beard Trims</a></li>
                    <li><a href="#">Hot Towel Shaves</a></li>
                    <li><a href="#">Styling</a></li>
                </ul>
            </div>
            <div class="footer-contact">
                <h3>Contact Us</h3>
                <p><i class="fas fa-map-marker-alt"></i> 123 Barber St, Accra, Ghana</p>
                <p><i class="fas fa-phone"></i> +233 123 456 789</p>
                <p><i class="fas fa-envelope"></i> info@elitecuts.com</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> MellyTech. All rights reserved.</p>
        </div>
    </footer>
    </section>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const navLinks = document.getElementById('navLinks');
            const navContainer = document.querySelector('.nav-container');
            const body = document.body;
            let menuOpen = false;
            
            // Close menu when clicking outside
            function handleOutsideClick(e) {
                if (menuOpen && !navContainer.contains(e.target) && !e.target.closest('.mobile-menu-btn')) {
                    closeMenu();
                }
            }
            
            // Close menu when pressing Escape key
            function handleKeyDown(e) {
                if (e.key === 'Escape' && menuOpen) {
                    closeMenu();
                }
            }
            
            function openMenu() {
                navLinks.classList.add('show');
                mobileMenuBtn.classList.add('active');
                body.classList.add('menu-open');
                menuOpen = true;
                
                // Add event listeners when menu is open
                document.addEventListener('click', handleOutsideClick);
                document.addEventListener('keydown', handleKeyDown);
                
                // Set focus to first focusable element in menu
                const firstFocusable = navLinks.querySelector('a, button, [tabindex="0"]');
                if (firstFocusable) firstFocusable.focus();
            }
            
            function closeMenu() {
                navLinks.classList.remove('show');
                mobileMenuBtn.classList.remove('active');
                body.classList.remove('menu-open');
                menuOpen = false;
                
                // Remove event listeners when menu is closed
                document.removeEventListener('click', handleOutsideClick);
                document.removeEventListener('keydown', handleKeyDown);
                
                // Return focus to menu button
                mobileMenuBtn.focus();
            }
            
            function toggleMenu() {
                if (menuOpen) {
                    closeMenu();
                } else {
                    openMenu();
                }
            }
            
            // Toggle menu on button click
            if (mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    toggleMenu();
                });
            }
            
            // Close menu when clicking on nav links
            const navItems = navLinks.querySelectorAll('a, button');
            navItems.forEach(item => {
                item.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        closeMenu();
                    }
                });
            });
            
            // Handle window resize
            function handleResize() {
                if (window.innerWidth > 768) {
                    closeMenu();
                }
            }
            
            // Add touch event for better mobile experience
            if ('ontouchstart' in window) {
                let startX = 0;
                let startY = 0;
                
                document.addEventListener('touchstart', function(e) {
                    startX = e.touches[0].clientX;
                    startY = e.touches[0].clientY;
                }, { passive: true });
                
                document.addEventListener('touchend', function(e) {
                    if (!menuOpen) return;
                    
                    const endX = e.changedTouches[0].clientX;
                    const endY = e.changedTouches[0].clientY;
                    const diffX = startX - endX;
                    const diffY = startY - endY;
                    
                    // If swiped right to left and menu is open
                    if (diffX > 50 && Math.abs(diffY) < 50) {
                        closeMenu();
                    }
                }, { passive: true });
            }
            
            // Initialize
            window.addEventListener('resize', handleResize);
            
            // Close menu when orientation changes
            window.addEventListener('orientationchange', function() {
                setTimeout(handleResize, 100);
            });
        });
    </script>
</body>
</html>
