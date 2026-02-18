<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo '<h2>Environment Test</h2>';
echo '<pre>';

// Check if .env file exists
$envFile = __DIR__ . '/.env';
echo "Checking .env file: " . (file_exists($envFile) ? 'Exists' : 'Missing') . "\n";

// Try to load environment variables
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    echo "Environment variables loaded successfully\n\n";
} else {
    die("Error: Composer autoloader not found. Please run 'composer install' first.\n");
}

// List all environment variables
$envVars = [
    'SMTP_HOST',
    'SMTP_USERNAME',
    'SMTP_PASSWORD',
    'SMTP_PORT',
    'SMTP_ENCRYPTION',
    'EMAIL_FROM',
    'EMAIL_FROM_NAME'
];

echo "Environment Variables:\n";
foreach ($envVars as $var) {
    $value = getenv($var);
    if ($var === 'SMTP_PASSWORD') {
        echo sprintf("%s: %s\n", $var, $value ? '**********' : 'Not set');
    } else {
        echo sprintf("%s: %s\n", $var, $value ?: 'Not set');
    }
}

// Test PHPMailer connection
if (in_array(false, array_map('getenv', $envVars), true)) {
    echo "\n❌ Error: Some required environment variables are not set.\n";
} else {
    echo "\n✅ All required environment variables are set.\n";
    
    // Test PHPMailer
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = getenv('SMTP_HOST');
        $mail->SMTPAuth = true;
        $mail->Username = getenv('SMTP_USERNAME');
        $mail->Password = getenv('SMTP_PASSWORD');
        $mail->SMTPSecure = getenv('SMTP_ENCRYPTION');
        $mail->Port = getenv('SMTP_PORT');
        
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = function($str, $level) {
            echo "PHPMailer: $str\n";
        };
        
        $mail->setFrom(getenv('EMAIL_FROM'), getenv('EMAIL_FROM_NAME'));
        $mail->addAddress('baidoob7525@gmail.com', 'Test User');
        $mail->Subject = 'Test Email from ' . getenv('EMAIL_FROM_NAME');
        $mail->Body = 'This is a test email from ' . getenv('EMAIL_FROM_NAME');
        
        if ($mail->send()) {
            echo "\n✅ Test email sent successfully!\n";
        } else {
            echo "\n❌ Failed to send test email.\n";
        }
    } catch (Exception $e) {
        echo "\n❌ PHPMailer Error: " . $e->getMessage() . "\n";
    }
}

echo '</pre>';
