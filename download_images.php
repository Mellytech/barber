<?php
// Create images directory if it doesn't exist
$imageDir = __DIR__ . '/images/services';
if (!file_exists($imageDir)) {
    mkdir($imageDir, 0755, true);
}

// List of images to download
$images = [
    'haircut.jpg' => 'https://images.unsplash.com/photo-1519501025264-65ba15a82390?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80',
    'beard.jpg' => 'https://images.unsplash.com/photo-1601409764624-8b828b305e33?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80',
    'shave.jpg' => 'https://images.unsplash.com/photo-1522512115668-c09725d9de7f?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80',
    'facial.jpg' => 'https://images.unsplash.com/photo-1540337706094-da10342c93d8?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80',
    'gallery1.jpg' => 'https://images.unsplash.com/photo-1567892243-bb5c71a7a4b4?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80',
    'gallery2.jpg' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80',
];

// Download each image
foreach ($images as $filename => $url) {
    $filepath = $imageDir . '/' . $filename;
    if (!file_exists($filepath)) {
        $imageData = @file_get_contents($url);
        if ($imageData !== false) {
            file_put_contents($filepath, $imageData);
            echo "Downloaded: $filename<br>";
        } else {
            echo "Failed to download: $filename<br>";
        }
    } else {
        echo "Already exists: $filename<br>";
    }
}

echo "<p>All images have been processed. <a href='services.php'>View Services Page</a></p>";
?>
