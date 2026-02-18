<?php
require 'vendor/autoload.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Recipient email - change this to your email
$toEmail = 'baidoob7525@gmail.com';
$toName = 'Test Recipient';

try {
    $mail = new PHPMailer(true);

    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'blessb0243@gmail.com';
    $mail->Password = 'cdcv sboc roma yywn';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    $mail->SMTPDebug = 2;
    
    $mail->Debugoutput = function($str, $level) {
        echo "$level: $str<br>";
        file_put_contents('email_test.log', "$level: $str\n", FILE_APPEND);
    };

    // Recipients
    $mail->setFrom('blessb0243@gmail.com', 'Barber Shop');
    $mail->addAddress($toEmail, $toName);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email from Barber Shop';
    $mail->Body    = 'This is a test email from the Barber Shop application.';
    $mail->AltBody = 'This is a test email from the Barber Shop application.';

    $mail->send();
    echo '<h2 style="color:green;">Test email sent successfully!</h2>';
} catch (Exception $e) {
    echo "<h2 style='color:red;'>Message could not be sent. Mailer Error: {$mail->ErrorInfo}</h2>";
}
?>
