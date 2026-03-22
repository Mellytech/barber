<?php
require_once __DIR__ . '/functions.php';

$errors = [];
$success = '';

// Check if user has a pending reset
if (!isset($_SESSION['pending_reset_user_id'], $_SESSION['reset_code'])) {
    header('Location: forgot_password.php');
    exit;
}

// Check if code has expired
if (isset($_SESSION['reset_code_expires_at'])) {
    $expiryTime = new DateTimeImmutable($_SESSION['reset_code_expires_at']);
    $now = new DateTimeImmutable('now');
    if ($now > $expiryTime) {
        $errors[] = 'The reset code has expired. Please request a new one.';
        unset($_SESSION['pending_reset_user_id'], $_SESSION['reset_code'], $_SESSION['reset_code_expires_at']);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');
    $newPassword = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validate inputs
    if ($code === '') {
        $errors[] = 'Verification code is required.';
    } elseif ($code !== $_SESSION['reset_code']) {
        $errors[] = 'Invalid verification code.';
    }
    
    if (strlen($newPassword) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }
    
    if ($newPassword !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        try {
            $pdo = get_pdo();
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare('UPDATE users SET password_hash = :password_hash WHERE id = :id');
            $result = $stmt->execute([
                ':password_hash' => $passwordHash,
                ':id' => $_SESSION['pending_reset_user_id']
            ]);

            if ($result) {
                // Clear reset session
                unset($_SESSION['pending_reset_user_id'], $_SESSION['reset_code'], $_SESSION['reset_code_expires_at']);
                
                // Set success message
                $_SESSION['flash_message'] = 'Your password has been reset successfully. You can now log in with your new password.';
                header('Location: login.php');
                exit;
            } else {
                $errors[] = 'Failed to update password. Please try again.';
            }
        } catch (PDOException $e) {
            error_log('Password reset error: ' . $e->getMessage());
            $errors[] = 'Database Error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Barber Shop</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 500px; margin: 40px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { text-align: center; margin-bottom: 30px; color: #333; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
            margin-bottom: 15px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            margin-top: 10px;
        }
        button:hover { background-color: #45a049; }
        .error { color: #d32f2f; margin: 10px 0; padding: 10px; background: #ffebee; border-radius: 4px; }
        .success { color: #388e3c; margin: 10px 0; padding: 10px; background: #e8f5e9; border-radius: 4px; }
        .back-link { text-align: center; margin-top: 20px; }
        .back-link a { color: #1976d2; text-decoration: none; }
        .back-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Reset Your Password</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <p>Please enter the verification code sent to your email and your new password.</p>
        
        <form method="post" action="reset_password.php">
            <div class="form-group">
                <label for="code">Verification Code</label>
                <input type="text" id="code" name="code" required 
                       placeholder="Enter the 6-digit code">
            </div>
            
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" required 
                       placeholder="Enter your new password (min 8 characters)">
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required 
                       placeholder="Confirm your new password">
            </div>
            
            <button type="submit">Reset Password</button>
        </form>
        
        <div class="back-link">
            <a href="forgot_password.php">Back to Forgot Password</a>
        </div>
    </div>
</body>
</html>
