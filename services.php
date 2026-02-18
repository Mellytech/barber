<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db.php';
$pageTitle = 'Our Services';

// Default services (fallback)
$defaultServices = [
    ['id' => 1, 'name' => 'Haircut', 'description' => 'Professional haircut and styling tailored to your preferences', 'price' => 50.00],
    ['id' => 2, 'name' => 'Beard Trim', 'description' => 'Precision beard trimming and shaping', 'price' => 30.00],
    ['id' => 3, 'name' => 'Haircut & Beard', 'description' => 'Complete grooming package with haircut and beard trim', 'price' => 70.00],
    ['id' => 4, 'name' => 'Kids Cut', 'description' => 'Haircut for children under 12 years old', 'price' => 35.00],
    ['id' => 5, 'name' => 'Shave', 'description' => 'Traditional hot towel shave with straight razor', 'price' => 40.00],
    ['id' => 6, 'name' => 'Facial', 'description' => 'Deep cleansing facial treatment', 'price' => 45.00]
];

// Get services from database
$services = [];
try {
    $pdo = get_pdo();
    $stmt = $pdo->query("SELECT id, name, description, price FROM services WHERE is_active = 1 ORDER BY name");
    $dbServices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ensure all required fields exist for each service
    foreach ($dbServices as $service) {
        $services[] = [
            'id' => $service['id'] ?? 0,
            'name' => $service['name'] ?? 'Service',
            'description' => $service['description'] ?? 'Service description',
            'price' => isset($service['price']) ? (float)$service['price'] : 0.00
        ];
    }
    
    // If no services found in DB, use defaults
    if (empty($services)) {
        $services = $defaultServices;
    }
} catch (PDOException $e) {
    error_log("Error fetching services: " . $e->getMessage());
    $services = $defaultServices;
}

// Local image paths
$serviceImages = [
    'haircut' => 'images/services/haircut.jpg',
    'beard' => 'images/services/beard.jpg',
    'shave' => 'images/services/shave.jpg',
    'facial' => 'images/services/facial.jpg',
    'gallery1' => 'images/services/haircut.jpg',
    'gallery2' => 'images/services/beard.jpg',
    'gallery3' => 'images/services/shave.jpg',
    'gallery4' => 'images/services/facial.jpg',
    'gallery5' => 'images/services/haircut.jpg',
    'gallery6' => 'images/services/beard.jpg',
];

// Fallback to placeholder if image doesn't exist
foreach ($serviceImages as &$image) {
    if (!file_exists(__DIR__ . '/' . $image)) {
        $image = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA4MDAgNjAwIiBzdHlsZT0iYmFja2dyb3VuZC1jb2xvcjojZWVlIj48dGV4dCB4PSI1MCUiIHk9IjUwJSIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjE2IiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmaWxsPSI2NjYiPkltYWdlIExvYWRpbmcuLi48L3RleHQ+PC9zdmc+';
    }
}
unset($image);

include 'header.php';
?>

<div class="container py-5">
    <h1 class="text-center mb-5">Our Services</h1>
    
    <?php foreach ($services as $index => $service): 
        $imageKey = strtolower(str_replace([' ', '&'], ['_', '_and_'], $service['name']));
        $image = $serviceImages[$imageKey] ?? $serviceImages['haircut'];
        $imageAlt = htmlspecialchars($service['name'] . ' service');
    ?>
    <div class="row align-items-center mb-5 <?php echo $index % 2 === 0 ? '' : 'flex-md-row-reverse'; ?>">
        <div class="col-md-6">
            <a href="<?php echo htmlspecialchars($image); ?>" data-lightbox="service-gallery" data-title="<?php echo $imageAlt; ?>" class="d-block">
                <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo $imageAlt; ?>" class="img-fluid rounded shadow">
            </a>
        </div>
        <div class="col-md-6">
            <h2><?php echo htmlspecialchars($service['name']); ?></h2>
            <p><?php echo htmlspecialchars($service['description']); ?></p>
            <p class="h4 text-primary">GHS <?php echo number_format($service['price'], 2); ?></p>
            <a href="book_appointment.php?service_id=<?php echo (int)$service['id']; ?>" class="btn btn-primary mt-3">Book Now</a>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Gallery Section -->
<div class="container my-5">
    <h2 class="text-center mb-4">Our Work</h2>
    <div class="row g-4">
        <?php 
        // Display gallery images with lightbox
        for ($i = 1; $i <= 6; $i++): 
            $imageKey = 'gallery' . $i;
            $image = $serviceImages[$imageKey] ?? $serviceImages['gallery1'];
            $altText = 'Our Work ' . $i;
        ?>
        <div class="col-md-4 col-6">
            <a href="<?php echo htmlspecialchars($image); ?>" 
               data-lightbox="our-work-gallery" 
               data-title="<?php echo htmlspecialchars($altText); ?>"
               class="d-block">
                <img src="<?php echo htmlspecialchars($image); ?>" 
                     alt="<?php echo htmlspecialchars($altText); ?>" 
                     class="img-fluid rounded shadow-sm hover-zoom">
            </a>
        </div>
        <?php endfor; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
