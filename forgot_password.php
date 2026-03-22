<?php

require_once __DIR__ . '/functions.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    } else {
        try {
            $pdo = get_pdo();
            $stmt = $pdo->prepare('SELECT id, name, email FROM users WHERE email = :email');
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if (!$user) {
                // For security, do not reveal whether the email exists
                $success = 'If an account with that email exists, a reset code has been sent.';
            } else {
                $userId = (int)$user['id'];
                $code = generate_verification_code();
                
                // Store in session
                $_SESSION['pending_reset_user_id'] = $userId;
                $_SESSION['reset_code'] = $code;
                $_SESSION['reset_code_expires_at'] = (new DateTimeImmutable('now'))
                    ->modify('+' . VERIFICATION_CODE_EXPIRY_MINUTES . ' minutes')
                    ->format('Y-m-d H:i:s');

                // Prepare email
                $subject = 'Your Password Reset Code';
                $message = "Hello " . htmlspecialchars($user['name']) . ",\n\n";
                $message .= "You have requested to reset your password.\n";
                $message .= "Your verification code is: " . $code . "\n";
                $message .= "This code will expire in " . VERIFICATION_CODE_EXPIRY_MINUTES . " minutes.\n\n";
                $message .= "If you did not request this, please ignore this email.\n";

                // Send email using PHPMailer
                if (send_email($user['email'], $subject, $message, $user['name'])) {
                    $success = 'A password reset code has been sent to your email. Please check your inbox.';
                    // Redirect to reset password page
                    header('Location: reset_password.php');
                    exit;
                } else {
                    $errors[] = 'Failed to send reset code. Please try again later.';
                    error_log('Failed to send password reset email to ' . $user['email']);
                }
            }
        } catch (PDOException $e) {
            error_log('Database error in forgot_password.php: ' . $e->getMessage());
            $errors[] = 'Database Error: ' . $e->getMessage();
        } catch (Exception $e) {
            error_log('Error in forgot_password.php: ' . $e->getMessage());
            $errors[] = 'An error occurred. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Barber Shop</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 500px; margin: 40px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { text-align: center; margin-bottom: 30px; color: #333; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; }
        input[type="email"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
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
        }
        button:hover { background-color: #45a049; }
        .error { color: #d32f2f; margin: 10px 0; padding: 10px; background: #ffebee; border-radius: 4px; }
        .success { color: #388e3c; margin: 10px 0; padding: 10px; background: #e8f5e9; border-radius: 4px; }
        .login-link { text-align: center; margin-top: 20px; }
        .login-link a { color: #1976d2; text-decoration: none; }
        .login-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Forgot Password</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success">
                <?php echo nl2br(htmlspecialchars($success)); ?>
            </div>
        <?php else: ?>
            <p>Enter your email address and we'll send you a code to reset your password.</p>
            
            <form method="post" action="forgot_password.php">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <button type="submit">Send Reset Code</button>
                </div>
            </form>
        <?php endif; ?>
        
        <div class="login-link">
            Remember your password? <a href="login.php">Log in</a>
        </div>
    </div>
</body>
</html>
