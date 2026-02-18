<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the main functions file which includes our email config
require_once __DIR__ . '/functions.php';

// Test email configuration
$to = 'blessb0243@gmail.com';
$subject = 'Test Email from Barber Shop';
$body = "This is a test email to verify that the email system is working correctly.\n\n";
$body .= "If you're reading this, the email was sent successfully!\n";
$body .= "Timestamp: " . date('Y-m-d H:i:s');
$name = 'Test User';

// Send the email
$result = send_email($to, $subject, $body, $name);

// Output the result
echo "<h1>Email Test Results</h1>";
if ($result) {
    echo "<p style='color: green;'>✅ Email sent successfully to $to</p>";
} else {
    echo "<p style='color: red;'>❌ Failed to send email. Check the error logs for details.</p>";
}

// Show debug information
echo "<h2>Debug Information</h2>";
echo "<pre>";

echo "SMTP Server: " . ($_ENV['SMTP_HOST'] ?? 'smtp.gmail.com') . "\n";
echo "Port: " . ($_ENV['SMTP_PORT'] ?? '587') . "\n";
echo "Encryption: " . ($_ENV['SMTP_ENCRYPTION'] ?? 'tls') . "\n";
echo "From: " . ($_ENV['EMAIL_FROM'] ?? 'noreply@example.com') . "\n";

// Check if email_debug.log exists
$debugLog = __DIR__ . '/email_debug.log';
if (file_exists($debugLog)) {
    echo "\n<hr><h3>Debug Log:</h3><pre>";
    echo htmlspecialchars(file_get_contents($debugLog));
    echo "</pre>";
}

// Check if email_error.log exists
$errorLog = __DIR__ . '/email_error.log';
if (file_exists($errorLog)) {
    echo "<hr><h3>Error Log:</h3><pre>";
    echo htmlspecialchars(file_get_contents($errorLog));
    echo "</pre>";
}

echo "</pre>";
?>
