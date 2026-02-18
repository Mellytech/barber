<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

/**
 * Send a verification code to a user's email
 * 
 * @param string $email User's email address
 * @param string $name User's name
 * @param string $verificationCode Verification code
 * @return bool True if email was sent successfully, false otherwise
 */
function send_verification_email_new($email, $name, $verificationCode) {
    $subject = 'Your Login Verification Code';
    
    // Plain text email body
    $body = "Hello $name,\n\n";
    $body .= "Your verification code is: $verificationCode\n\n";
    $body .= "This code will expire in 10 minutes.\n\n";
    $body .= "If you didn't request this code, please ignore this email.\n\n";
    $body .= "Best regards,\n";
    $body .= "Barber Shop Team";
    
    // Send the email using the main send_email function
    return send_email($email, $subject, $body, $name);
}

/**
 * Send a password reset email with a code
 * 
 * @param string $email User's email address
 * @param string $name User's name
 * @param string $resetToken Password reset token
 * @return bool True if email was sent successfully, false otherwise
 */
function send_password_reset_email($email, $name, $resetToken) {
    $subject = 'Password Reset Request';
    
    // Create reset link with token
    $resetLink = BASE_URL . '/reset_password.php?token=' . urlencode($resetToken);
    
    // Plain text email body
    $body = "Hello $name,\n\n";
    $body .= "You have requested to reset your password. Use the code below to verify your identity:\n\n";
    $body .= "$resetToken\n\n";
    $body .= "This code will expire in 1 hour.\n\n";
    $body .= "If you didn't request a password reset, please ignore this email.\n\n";
    $body .= "Best regards,\n";
    $body .= "Barber Shop Team";
    
    // Send the email using the main send_email function
    return send_email($email, $subject, $body, $name);
}

/**
 * Send contact form submission to admin
 * 
 * @param string $name Sender's name
 * @param string $email Sender's email
 * @param string $phone Sender's phone
 * @param string $message Sender's message
 * @return bool True if email was sent successfully, false otherwise
 */
function send_contact_email($name, $email, $phone, $message) {
    $adminEmail = 'blessingbaidoo71@gmail.com';
    $subject = 'New Contact Form Submission from ' . $name;
    
    // Build the email body
    $body = "You have received a new message from your website contact form.\n\n";
    $body .= "Name: $name\n";
    $body .= "Email: $email\n";
    $body .= "Phone: " . ($phone ?: 'Not provided') . "\n\n";
    $body .= "Message:\n$message";
    
    // Send the email using the main send_email function
    return send_email($adminEmail, $subject, $body, 'Website Contact Form');
}
