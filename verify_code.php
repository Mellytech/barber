<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

$pageTitle = 'Verify Your Code - Barber Shop';
$errors = [];

// Check if user has a pending login
if (!isset($_SESSION['pending_login_user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = (int)$_SESSION['pending_login_user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['verification_code'] ?? '');
    
    if (empty($code)) {
        $errors[] = 'Please enter the verification code.';
    } else {
        try {
            $pdo = get_pdo();
            
            // Check if the code is valid and not expired
            $stmt = $pdo->prepare('SELECT * FROM verification_codes WHERE user_id = ? AND code = ? AND type = ? AND expires_at > NOW()');
            $stmt->execute([$userId, $code, 'login']);
            $verification = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$verification) {
                $errors[] = 'Invalid or expired verification code. Please try again.';
            } else {
                // Get user data
                $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
                $stmt->execute([$userId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    // Log the user in
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    
                    // Delete used verification code
                    $pdo->prepare('DELETE FROM verification_codes WHERE id = ?')->execute([$verification['id']]);
                    
                    // Clear pending login
                    unset($_SESSION['pending_login_user_id']);
                    
                    // Redirect to dashboard or home page
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $errors[] = 'User not found.';
                }
            }
        } catch (PDOException $e) {
            error_log('Database error in verify_code.php: ' . $e->getMessage());
            $errors[] = 'Database Error: ' . $e->getMessage();
        } catch (Exception $e) {
            error_log('Error in verify_code.php: ' . $e->getMessage());
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
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/auth.css">
    <style>
        .verification-code {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 20px 0;
        }
        
        .verification-code input {
            width: 50px;
            height: 60px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            border: 2px solid #ddd;
            border-radius: 8px;
        }
        
        .verification-code input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(74, 111, 165, 0.25);
        }
        
        .resend-code {
            text-align: center;
            margin-top: 20px;
        }
        
        .resend-code a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .resend-code a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <span class="brand-logo">BarberShop</span>
                <h1>Enter Verification Code</h1>
                <p>We've sent a 6-digit code to your email</p>
            </div>
            
            <div class="auth-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul style="margin: 0; padding-left: 1.2rem;">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="verify_code.php" id="verificationForm">
                    <div class="form-group">
                        <p style="text-align: center; margin-bottom: 1.5rem;">
                            Please enter the 6-digit code sent to your email
                        </p>
                        
                        <div class="verification-code">
                            <input type="text" name="code1" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="one-time-code" required>
                            <input type="text" name="code2" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                            <input type="text" name="code3" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                            <span style="width: 10px;"></span>
                            <input type="text" name="code4" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                            <input type="text" name="code5" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                            <input type="text" name="code6" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                            <input type="hidden" name="verification_code" id="verificationCode">
                        </div>
                        
                        <div class="resend-code">
                            Didn't receive a code? <a href="#" id="resendCode">Resend Code</a>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary" id="verifyButton">
                            <span class="btn-text">Verify Code</span>
                            <span class="btn-loading" style="display: none;">
                                <i class="fas fa-spinner fa-spin"></i> Verifying...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Auto-focus and auto-tab between code inputs
        const inputs = document.querySelectorAll('.verification-code input[type="text"]');
        const hiddenInput = document.getElementById('verificationCode');
        
        inputs.forEach((input, index) => {
            // Handle input
            input.addEventListener('input', (e) => {
                if (e.target.value.length === 1) {
                    // Move to next input or submit form if last input
                    if (index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    } else {
                        document.getElementById('verificationForm').requestSubmit();
                    }
                }
                updateHiddenInput();
            });
            
            // Handle backspace
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    inputs[index - 1].focus();
                }
            });
            
            // Handle paste
            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const paste = (e.clipboardData || window.clipboardData).getData('text');
                const numbers = paste.replace(/\D/g, '');
                
                if (numbers.length >= 6) {
                    for (let i = 0; i < 6; i++) {
                        if (inputs[i]) {
                            inputs[i].value = numbers[i];
                        }
                    }
                    updateHiddenInput();
                    document.getElementById('verificationForm').requestSubmit();
                }
            });
        });
        
        function updateHiddenInput() {
            let code = '';
            inputs.forEach(input => {
                code += input.value;
            });
            hiddenInput.value = code;
        }
        
        // Handle form submission
        document.getElementById('verificationForm').addEventListener('submit', function() {
            const button = document.getElementById('verifyButton');
            const buttonText = button.querySelector('.btn-text');
            const buttonLoading = button.querySelector('.btn-loading');
            
            button.disabled = true;
            buttonText.style.display = 'none';
            buttonLoading.style.display = 'inline-block';
        });
        
        // Handle resend code
        document.getElementById('resendCode').addEventListener('click', function(e) {
            e.preventDefault();
            
            // Show loading state
            const resendLink = this;
            const originalText = resendLink.textContent;
            resendLink.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            resendLink.style.pointerEvents = 'none';
            
            // Send AJAX request to resend code
            fetch('resend_verification.php')
            .then(response => response.text())
            .then(code => {
                if (code.startsWith('ERROR:')) {
                    throw new Error(code);
                }
                // Code sent successfully
                alert('A new verification code has been sent.');
            })
            .catch(error => {
                console.error('Error:', error);
                alert(error.message || 'Failed to resend code. Please try again.');
            })
            .finally(() => {
                // Reset button after delay
                setTimeout(() => {
                    resendLink.textContent = originalText;
                    resendLink.style.pointerEvents = 'auto';
                }, 3000);
            });
        });
    </script>
</body>
</html>
