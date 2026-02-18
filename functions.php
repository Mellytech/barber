<?php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/includes/email_functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Send an email using PHPMailer with SMTP
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $body Email body (plain text)
 * @param string $name Recipient name (optional)
 * @return bool True if email was sent successfully, false otherwise
 */
function send_email($to, $subject, $body, $name = '') {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'blessb0243@gmail.com';
        $mail->Password = 'cdcv sboc roma yywn';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Recipients
        $mail->setFrom('blessb0243@gmail.com', 'Barber Shop');
        $mail->addAddress($to, $name);
        
        // Content
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body = $body;
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Email sending failed: " . $e->getMessage());
        return false;
    }
}

// Include other function files
require_once __DIR__ . '/functions/user.php';
require_once __DIR__ . '/functions/auth.php';

/**
 * Ensure there is an admin user with the requested credentials.
 *
 * Admin email:    blessingbaidoo71@gmail.com
 * Admin password: admin123
 */
function ensure_default_admin(): void
{
    static $initialized = false;
    if ($initialized) {
        return;
    }
    $initialized = true;

    try {
        $pdo = get_pdo();
        $email = 'blessingbaidoo71@gmail.com';

        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $existing = $stmt->fetch();

        $passwordHash = password_hash('admin123', PASSWORD_DEFAULT);

        if ($existing) {
            $update = $pdo->prepare('UPDATE users SET is_admin = 1, password_hash = :password_hash WHERE id = :id');
            $update->execute([
                ':password_hash' => $passwordHash,
                ':id' => $existing['id'],
            ]);
        } else {
            $insert = $pdo->prepare('
                INSERT INTO users (name, email, password_hash, is_admin)
                VALUES (:name, :email, :password_hash, 1)
            ');
            $insert->execute([
                ':name' => 'Admin',
                ':email' => $email,
                ':password_hash' => $passwordHash,
            ]);
        }
    } catch (Throwable $e) {
        // If this fails (e.g. table not ready), just skip; other features still work.
    }
}

// Make sure the default admin exists before continuing.
ensure_default_admin();

function generate_verification_code(): string
{
    $digits = '';
    for ($i = 0; $i < VERIFICATION_CODE_LENGTH; $i++) {
        $digits .= random_int(0, 9);
    }
    return $digits;
}

function create_verification_code(int $userId, string $purpose): ?string
{
    $pdo = get_pdo();

    // Invalidate old unused codes for this purpose
    $stmt = $pdo->prepare('UPDATE email_verification_codes SET is_used = 1 WHERE user_id = :uid AND purpose = :purpose AND is_used = 0');
    $stmt->execute([
        ':uid' => $userId,
        ':purpose' => $purpose,
    ]);

    $code = generate_verification_code();
    // Store last code in session for debugging on verify pages
    $_SESSION['last_verification_code_' . $purpose] = $code;
    $expiresAt = (new DateTimeImmutable('now'))->modify('+' . VERIFICATION_CODE_EXPIRY_MINUTES . ' minutes');

    $stmt = $pdo->prepare('
        INSERT INTO email_verification_codes (user_id, code, purpose, expires_at)
        VALUES (:uid, :code, :purpose, :expires_at)
    ');
    $stmt->execute([
        ':uid' => $userId,
        ':code' => $code,
        ':purpose' => $purpose,
        ':expires_at' => $expiresAt->format('Y-m-d H:i:s'),
    ]);

    return $code;
}

function verify_code(int $userId, string $purpose, string $code): bool
{
    $pdo = get_pdo();

    $stmt = $pdo->prepare('
        SELECT id, expires_at
        FROM email_verification_codes
        WHERE user_id = :uid AND purpose = :purpose AND code = :code AND is_used = 0
        ORDER BY id DESC
        LIMIT 1
    ');
    $stmt->execute([
        ':uid' => $userId,
        ':purpose' => $purpose,
        ':code' => $code,
    ]);
    $row = $stmt->fetch();

    if (!$row) {
        return false;
    }

    $now = new DateTimeImmutable('now');
    $expiresAt = new DateTimeImmutable($row['expires_at']);

    if ($now > $expiresAt) {
        return false;
    }

    // Mark code as used
    $update = $pdo->prepare('UPDATE email_verification_codes SET is_used = 1 WHERE id = :id');
    $update->execute([':id' => $row['id']]);

    return true;
}

/**
 * Generate a unique appointment number
 * Format: APP-YYYYMMDD-XXXX (where XXXX is an incrementing number)
 * 
 * @param PDO $pdo Database connection
 * @return string Unique appointment number
 */
function generate_appointment_number($pdo) {
    $prefix = 'APP-' . date('Ymd') . '-';
    
    // Get the latest appointment number for today
    $stmt = $pdo->prepare("
        SELECT appointment_number 
        FROM appointments 
        WHERE appointment_number LIKE ? 
        ORDER BY id DESC 
        LIMIT 1
    ");
    $stmt->execute([$prefix . '%']);
    $lastNumber = $stmt->fetchColumn();
    
    if ($lastNumber) {
        // Extract the numeric part and increment
        $parts = explode('-', $lastNumber);
        $number = (int)end($parts) + 1;
    } else {
        // First appointment of the day
        $number = 1;
    }
    
    // Format with leading zeros
    return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
}

// Authentication functions have been moved to functions/auth.php
