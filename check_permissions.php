<?php
// Check if directory is writable
$dir = __DIR__ . '/images';

echo "<h2>Directory Permission Check</h2>";

echo "<p>Checking if directory exists: " . (file_exists($dir) ? '✅ Yes' : '❌ No') . "</p>";

// Try to create directory if it doesn't exist
if (!file_exists($dir)) {
    echo "<p>Attempting to create directory: $dir</p>";
    if (mkdir($dir, 0777, true)) {
        echo "<p>✅ Directory created successfully!</p>";
        
        // Create a test file
        $testFile = $dir . '/test.txt';
        if (file_put_contents($testFile, 'test')) {
            echo "<p>✅ Test file created successfully!</p>";
            unlink($testFile); // Clean up
        } else {
            echo "<p>❌ Could not create test file. Check write permissions.</p>";
        }
    } else {
        echo "<p>❌ Failed to create directory. Please check server permissions.</p>";
        echo "<p>Try creating the 'images' directory manually in your project root with full write permissions.</p>";
    }
} else {
    echo "<p>✅ Directory already exists.</p>";
    
    // Check if directory is writable
    if (is_writable($dir)) {
        echo "<p>✅ Directory is writable.</p>";
    } else {
        echo "<p>❌ Directory is not writable. Please set write permissions.</p>";
    }
}

// Show current directory structure
echo "<h3>Current directory structure:</h3>";
echo "<pre>" . shell_exec('dir /w ' . escapeshellarg(__DIR__)) . "</pre>";
?>
