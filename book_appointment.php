<?php

require_once __DIR__ . '/functions.php';
require_login();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load PHPMailer
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$user = current_user();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

$serviceId = $_POST['service_id'] ?? '';
$datetime = trim($_POST['datetime'] ?? '');

$errors = [];

if ($serviceId === '' || !ctype_digit((string)$serviceId)) {
    $errors[] = 'Valid service is required.';
}
if ($datetime === '') {
    $errors[] = 'Appointment date and time is required.';
}

// Basic future date validation
if ($datetime !== '') {
    try {
        $dt = new DateTime($datetime);
        $now = new DateTime();
        if ($dt < $now) {
            $errors[] = 'Appointment time must be in the future.';
        }
    } catch (Exception $e) {
        $errors[] = 'Invalid date and time format.';
    }
}

if (!empty($errors)) {
    // Store errors in session and redirect back
    $_SESSION['booking_errors'] = $errors;
    header('Location: dashboard.php');
    exit;
}

$pdo = get_pdo();

// Start transaction
$pdo->beginTransaction();

try {
    // Load service from DB to ensure correct name and price
    $serviceStmt = $pdo->prepare('SELECT name, default_price FROM services WHERE id = :id AND is_active = 1');
    $serviceStmt->execute([':id' => (int)$serviceId]);
    $serviceRow = $serviceStmt->fetch();

    if (!$serviceRow) {
        throw new Exception('Selected service is not available.');
    }

    $serviceName = $serviceRow['name'];
    $servicePrice = (float)$serviceRow['default_price'];

    // Generate a unique appointment number (format: APP-YYMMDD-XXXX)
    $datePrefix = date('ymd');
    $randomSuffix = strtoupper(substr(uniqid(), -4));
    $appointmentNumber = 'APP-' . $datePrefix . '-' . $randomSuffix;

    // Insert the appointment
    $stmt = $pdo->prepare('
        INSERT INTO appointments (user_id, service, price, appointment_number, appointment_datetime)
        VALUES (:uid, :service, :price, :appointment_number, :datetime)
    ');
    
    $stmt->execute([
        ':uid' => $user['id'],
        ':service' => $serviceName,
        ':price' => $servicePrice,
        ':appointment_number' => $appointmentNumber,
        ':datetime' => $dt->format('Y-m-d H:i:s'),
    ]);

    // Get the appointment ID
    $appointmentId = $pdo->lastInsertId();

    // Commit the transaction
    $pdo->commit();

    // After successfully saving the appointment, send confirmation email
    try {
        // Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);

        // Server settings - Using the working configuration from test_email_simple.php
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'blessb0243@gmail.com';
        $mail->Password = 'cdcv sboc roma yywn';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = function($str, $level) {
            error_log("PHPMailer: $str");
            file_put_contents(__DIR__ . '/email_debug.log', "$level: $str\n", FILE_APPEND);
        };

        // Recipients
        $fromEmail = 'blessb0243@gmail.com';
        $fromName = 'Your Barber Shop';
        $toEmail = $user['email'];
        $toName = $user['name'];

        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($toEmail, $toName);
        $mail->addReplyTo($fromEmail, $fromName);

        // Format the appointment date
        $appointmentDate = clone $dt;  // Use the existing DateTime object
        $formattedDate = $appointmentDate->format('l, F j, Y');
        $formattedTime = $appointmentDate->format('g:i A');

        // Email content
        $mail->isHTML(true);
        $mail->Subject = "Appointment Confirmation #$appointmentNumber";
        
        // HTML Email Body
        $mail->Body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #111827; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                .content { padding: 25px; background: #f9fafb; border: 1px solid #e5e7eb; border-top: none; }
                .details { background: white; padding: 20px; margin: 15px 0; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
                .footer { margin-top: 25px; font-size: 0.9em; color: #6b7280; text-align: center; }
                .button { display: inline-block; padding: 10px 20px; background: #111827; color: white; text-decoration: none; border-radius: 4px; margin: 15px 0; }
                .highlight { color: #111827; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>✅ Appointment Confirmed!</h2>
                </div>
                <div class='content'>
                    <p>Hello " . htmlspecialchars($user['name']) . ",</p>
                    <p>Thank you for booking with us! We're excited to see you soon.</p>
                    
                    <div class='details'>
                        <h3 style='margin-top: 0; color: #111827;'>Appointment Details</h3>
                        <p><strong>Appointment #:</strong> $appointmentNumber</p>
                        <p><strong>Service:</strong> " . htmlspecialchars($serviceName) . "</p>
                        <p><strong>Date:</strong> $formattedDate</p>
                        <p><strong>Time:</strong> $formattedTime</p>
                        <p><strong>Duration:</strong> 30 minutes</p>
                        <p><strong>Amount to Pay:</strong> <span class='highlight'>GHS " . number_format($servicePrice, 2) . "</span></p>
                    </div>

                    <p>We're located at: <strong>123 Barber Street, Accra, Ghana</strong></p>
                    <p>Please arrive 10 minutes before your scheduled time.</p>
                    
                    <div style='text-align: center; margin: 25px 0;'>
                        <a href='https://maps.google.com' class='button'>View on Map</a>
                    </div>

                    <p>If you need to reschedule or cancel, please contact us at least 24 hours in advance.</p>
                    
                    <div class='footer'>
                        <p>Thank you for choosing our service!</p>
                        <p><small>This is an automated message, please do not reply directly to this email.</small></p>
                    </div>
                </div>
            </div>
        </body>
        </html>";

        // Plain text version for non-HTML email clients
        $mail->AltBody = "APPOINTMENT CONFIRMATION #$appointmentNumber\n\n" .
            "Hello " . $user['name'] . ",\n\n" .
            "Thank you for booking with us! We're excited to see you soon.\n\n" .
            "APPOINTMENT DETAILS:\n" .
            "------------------\n" .
            "Appointment #: $appointmentNumber\n" .
            "Service: " . $serviceName . "\n" .
            "Date: $formattedDate\n" .
            "Time: $formattedTime\n" .
            "Duration: 30 minutes\n" .
            "Amount to Pay: GHS " . number_format($servicePrice, 2) . "\n\n" .
            "LOCATION:\n" .
            "123 Barber Street, Accra, Ghana\n\n" .
            "IMPORTANT:\n" .
            "- Please arrive 10 minutes before your scheduled time.\n" .
            "- If you need to reschedule or cancel, please contact us at least 24 hours in advance.\n\n" .
            "Thank you for choosing our service!\n\n" .
            "Best regards,\n" .
            $fromName;

        // Send the email
        $mail->send();
        
        // Log successful email sending
        error_log("Confirmation email sent to: " . $user['email']);
        
    } catch (Exception $e) {
        // Log the error but don't show to user
        $errorMsg = "Failed to send confirmation email: " . $e->getMessage();
        error_log($errorMsg);
        file_put_contents(__DIR__ . '/email_errors.log', date('Y-m-d H:i:s') . " - " . $errorMsg . "\n", FILE_APPEND);
    }

    // Set success message with appointment details
    $_SESSION['booking_success'] = [
        'message' => 'Appointment booked successfully!',
        'appointment_number' => $appointmentNumber,
        'service' => $serviceName,
        'datetime' => $dt->format('M j, Y H:i'),
        'price' => $servicePrice
    ];

    // Redirect to dashboard
    header('Location: dashboard.php');
    exit;

} catch (Exception $e) {
    // If we're here, something went wrong with the database transaction
    $pdo->rollBack();
    $_SESSION['booking_errors'][] = 'Failed to book appointment: ' . $e->getMessage();
    header('Location: book_appointment.php');
    exit;
}
