<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load PHPMailer
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Recipient email - change this to your email
$toEmail = 'baidoob7525@gmail.com';
$toName = 'Test User';

echo '<h2>Testing Email Sending</h2>';
echo '<pre>';

try {
    // Create an instance; passing `true` enables exceptions
    $mail = new PHPMailer(true);

    // Server settings - Hardcoded for testing
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'blessb0243@gmail.com';
    $mail->Password = 'cdcv sboc roma yywn'; // Your App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    
    // Enable verbose debug output
    $mail->SMTPDebug = 2;
    $mail->Debugoutput = function($str, $level) {
        echo "$level: $str<br>\n";
    };

    // Recipients
    $mail->setFrom('blessb0243@gmail.com', 'Barber Shop');
    $mail->addAddress($toEmail, $toName);
    $mail->addReplyTo('blessb0243@gmail.com', 'Barber Shop');

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email from Barber Shop';
    $mail->Body    = 'This is a test email from the Barber Shop application.';
    $mail->AltBody = 'This is a test email from the Barber Shop application.';

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}

echo '</pre>';
