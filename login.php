<?php
// Session is already started in config.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

$pageTitle = 'Login - Barber Shop';
$errors = [];
$email = '';

// Debug logging
function log_debug($message) {
    $logFile = __DIR__ . '/debug_login.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $errors[] = 'Both email and password are required.';
    } else {
        try {
            $pdo = get_pdo();
            log_debug("Login attempt for: $email");
            
            $stmt = $pdo->prepare('SELECT id, name, email, password_hash FROM users WHERE LOWER(email) = LOWER(?)');
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $errors[] = 'No account found with this email.';
                log_debug("No user found for email: $email");
            } else {
                $passwordMatch = password_verify($password, $user['password_hash']);
                
                if (!$passwordMatch) {
                    $trimmedPassword = trim($password);
                    if ($trimmedPassword !== $password) {
                        $passwordMatch = password_verify($trimmedPassword, $user['password_hash']);
                    }
                    
                    if (!$passwordMatch) {
                        $errors[] = 'Incorrect password.';
                        log_debug("Password verification failed for user: {$user['email']}");
                    }
                }

                if ($passwordMatch) {
                    if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
                        $newHash = password_hash($password, PASSWORD_DEFAULT);
                        $update = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
                        $update->execute([$newHash, $user['id']]);
                    }
                    
                    $code = create_verification_code((int)$user['id'], 'login');
                    if ($code === null) {
                        $errors[] = 'Failed to generate verification code.';
                    } else if (!send_verification_email_new($user['email'], $user['name'], $code)) {
                        $errors[] = 'Failed to send verification email. Please try again.';
                    } else {
                        $_SESSION['pending_login_user_id'] = (int)$user['id'];
                        header('Location: verify_login.php');
                        exit;
                    }
                }
            }
        } catch (PDOException $e) {
            error_log('Database error in login.php: ' . $e->getMessage());
            $errors[] = 'A database error occurred. Please try again later.';
        } catch (Exception $e) {
            error_log('Error in login.php: ' . $e->getMessage());
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
        /* Additional custom styles */
        .brand-logo {
            font-size: 2.5rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 0.5rem;
            display: inline-block;
        }
        
        .brand-tagline {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            display: block;
        }
        
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 1.5rem 0;
            color: #6c757d;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #dee2e6;
        }
        
        .divider:not(:empty)::before {
            margin-right: 1rem;
        }
        
        .divider:not(:empty)::after {
            margin-left: 1rem;
        }
        
        .social-login {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .btn-social {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.6rem;
            border-radius: var(--border-radius);
            color: white;
            text-decoration: none;
            transition: var(--transition);
        }
        
        .btn-google {
            background-color: #DB4437;
        }
        
        .btn-facebook {
            background-color: #4267B2;
        }
        
        .btn-social:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        .btn-social i {
            margin-right: 8px;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <span class="brand-logo">BarberShop</span>
                <span class="brand-tagline">Your perfect haircut awaits</span>
                <h1>Welcome Back</h1>
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

                <form method="POST" action="login.php" id="loginForm">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-with-icon">
                            <input type="email" id="email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($email); ?>" 
                                   placeholder="Enter your email" required>
                            <i class="fas fa-envelope input-icon"></i>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-with-icon">
                            <input type="password" id="password" name="password" 
                                   class="form-control" placeholder="••••••••" required>
                            <i class="fas fa-lock input-icon"></i>
                            <button type="button" id="togglePassword" class="btn-icon" aria-label="Toggle password visibility">
                                <i class="far fa-eye"></i>
                            </button>
                        </div>
                        <div class="text-right mt-1">
                            <a href="forgot_password.php" class="text-small" style="text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">Forgot password?</a>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary" id="loginButton">
                            <span class="btn-text">Sign In</span>
                            <span class="btn-loading" style="display: none;">
                                <i class="fas fa-spinner fa-spin"></i> Signing in...
                            </span>
                        </button>
                    </div>
                </form>
                
                <div class="auth-links">
                    Don't have an account? <a href="register.php">Create an account</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Form submission with loading state
        document.getElementById('loginForm').addEventListener('submit', function() {
            const button = document.querySelector('#loginButton');
            const buttonText = document.querySelector('.btn-text');
            const buttonLoading = document.querySelector('.btn-loading');
            
            button.disabled = true;
            buttonText.style.display = 'none';
            buttonLoading.style.display = 'inline-block';
        });
    </script>
</body>
</html>
