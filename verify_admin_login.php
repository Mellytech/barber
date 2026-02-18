<?php

require_once __DIR__ . '/functions.php';

if (!isset($_SESSION['pending_admin_login_user_id'])) {
    header('Location: admin_login.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');

    if (!preg_match('/^\d{6}$/', $code)) {
        $errors[] = 'Please enter the 6-digit verification code sent to your email.';
    } else {
        $userId = (int)$_SESSION['pending_admin_login_user_id'];

        // Double-check this user is still an admin
        $pdo = get_pdo();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE id = :id AND is_admin = 1');
        $stmt->execute([':id' => $userId]);
        if (!$stmt->fetch()) {
            unset($_SESSION['pending_admin_login_user_id']);
            $errors[] = 'Admin access not allowed.';
        } elseif (verify_code($userId, 'admin_login', $code)) {
            unset($_SESSION['pending_admin_login_user_id']);
            $_SESSION['user_id'] = $userId;
            header('Location: admin_services.php');
            exit;
        } else {
            $errors[] = 'Invalid or expired verification code.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify Admin Login - Barber Shop</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 400px; margin: 60px auto; background: #fff; padding: 20px 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); box-sizing: border-box; }
        h1 { text-align: center; margin-bottom: 20px; }
        label { display: block; margin-top: 10px; }
        input[type="text"] {
            width: 100%; padding: 10px; margin-top: 5px; border-radius: 4px; border: 1px solid #ccc;
            letter-spacing: 4px; text-align: center; font-size: 18px;
        }
        button { margin-top: 20px; width: 100%; padding: 10px; background: #111827; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background: #000; }
        .errors { background: #fee2e2; color: #b91c1c; padding: 10px; border-radius: 4px; margin-top: 10px; }
        .hint { margin-top: 10px; font-size: 14px; color: #4b5563; }
        .link { margin-top: 15px; text-align: center; }
        a { color: #2563eb; text-decoration: none; }

        @media (max-width: 600px) {
            .container { margin: 24px 12px; padding: 20px 16px; }
            h1 { font-size: 22px; }
            input[type="text"] { font-size: 16px; }
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Verify Admin Login</h1>

    <?php if (!empty($errors)): ?>
        <div class="errors">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?php echo htmlspecialchars($e); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="verify_admin_login.php">
        <label for="code">Enter 6-digit code</label>
        <input type="text" id="code" name="code" maxlength="6" required>

        <button type="submit">Verify &amp; Continue</button>
    </form>

    <div class="hint">
        We sent a 6-digit verification code to your email. It expires in <?php echo VERIFICATION_CODE_EXPIRY_MINUTES; ?> minutes.
    </div>

    <div class="link">
        <a href="admin_login.php">Back</a>
    </div>
</div>
</body>
</html>

