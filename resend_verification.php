<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

header('Content-Type: application/json');

// Check if user has a pending login
if (!isset($_SESSION['pending_login_user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No pending login session']);
    exit;
}

$userId = (int)$_SESSION['pending_login_user_id'];

try {
    $pdo = get_pdo();
    
    // Get user data
    $stmt = $pdo->prepare('SELECT id, name, email FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception('User not found');
    }
    
    // Delete any existing verification codes for this user
    $pdo->prepare('DELETE FROM verification_codes WHERE user_id = ? AND type = ?')
        ->execute([$userId, 'login']);
    
    // Generate a new 6-digit verification code
    $verificationCode = sprintf('%06d', mt_rand(0, 999999));
    
    // Set expiration time (10 minutes from now)
    $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    
    // Save the verification code
    $stmt = $pdo->prepare('INSERT INTO verification_codes (user_id, code, type, expires_at) VALUES (?, ?, ?, ?)');
    $stmt->execute([$userId, $verificationCode, 'login', $expiresAt]);
    
    // Send the verification email
    if (send_verification_email_new($user['email'], $user['name'], $verificationCode)) {
        echo json_encode(['code' => $verificationCode]);
    } else {
        throw new Exception('Failed to send verification email');
    }
    
} catch (Exception $e) {
    error_log('Error in resend_verification.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);Error: ' . $e->getMessage()
    ]);
}
