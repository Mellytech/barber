<?php
// Create necessary directories
$baseDir = __DIR__ . '/images';
$dirs = [
    $baseDir,
    $baseDir . '/about',
    $baseDir . '/team',
    $baseDir . '/services',
    $baseDir . '/testimonials'
];

foreach ($dirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
        echo "Created directory: $dir<br>";
    }
}

// List of images to download
$images = [
    'hero-barber.jpg' => 'https://images.unsplash.com/photo-1599351431202-1e0f0137899a?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80',
    'about/barbershop-interior.jpg' => 'https://images.unsplash.com/photo-1567892243-bb5c71a7a4b4?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80',
    'team/barber1.jpg' => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80',
    'team/barber2.jpg' => 'https://images.unsplash.com/photo-1552058544-f2b84afd8e4c?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80',
    'team/barber3.jpg' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80',
    'services/haircut-preview.jpg' => 'https://images.unsplash.com/photo-1519501025264-65ba15a82390?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80',
    'services/beard-trim-preview.jpg' => 'https://images.unsplash.com/photo-1601409764624-8b828b305e33?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80',
    'services/shave-preview.jpg' => 'https://images.unsplash.com/photo-1596704017259-4c9a063632d4?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80',
    'testimonials/client1.jpg' => 'https://randomuser.me/api/portraits/men/32.jpg',
    'testimonials/client2.jpg' => 'https://randomuser.me/api/portraits/women/44.jpg',
    'testimonials/client3.jpg' => 'https://randomuser.me/api/portraits/men/75.jpg'
];

// Download images
foreach ($images as $path => $url) {
    $filePath = $baseDir . '/' . $path;
    if (!file_exists($filePath)) {
        $imageData = @file_get_contents($url);
        if ($imageData !== false) {
            file_put_contents($filePath, $imageData);
            echo "Downloaded: $path<br>";
        } else {
            // Create a blank image as fallback
            $im = imagecreatetruecolor(800, 600);
            $bgColor = imagecolorallocate($im, 200, 200, 200);
            $textColor = imagecolorallocate($im, 100, 100, 100);
            imagefill($im, 0, 0, $bgColor);
            imagestring($im, 5, 50, 280, 'Image: ' . basename($path), $textColor);
            imagejpeg($im, $filePath, 80);
            imagedestroy($im);
            echo "Created placeholder: $path<br>";
        }
    } else {
        echo "Exists: $path<br>";
    }
}

echo "<br>Image setup complete! You can now access all images from your website.";
?>
