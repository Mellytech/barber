<?php

/**
 * Check if a user is logged in
 * 
 * @return bool True if user is logged in, false otherwise
 */
function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * Require the user to be logged in
 * Redirects to login page if not logged in
 * 
 * @return void
 */
function require_login(): void {
    if (!is_logged_in()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

/**
 * Get the current logged-in user
 * 
 * @return array|null User data if logged in, null otherwise
 */
function current_user(): ?array {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    return get_user_by_id($_SESSION['user_id']);
}

/**
 * Check if the current user is an admin
 * 
 * @return bool True if user is an admin, false otherwise
 */
function is_admin(): bool {
    $user = current_user();
    return $user && isset($user['is_admin']) && $user['is_admin'] == 1;
}

/**
 * Require the current user to be an admin
 * Redirects to home page if not an admin
 * 
 * @return void
 */
function require_admin(): void {
    require_login();
    
    if (!is_admin()) {
        $_SESSION['error'] = 'You do not have permission to access this page';
        header('Location: ' . BASE_URL . '/');
        exit;
    }
}

/**
 * Log a user in
 * 
 * @param int $userId User ID to log in
 * @param bool $remember Whether to remember the user
 * @return void
 */
function login_user(int $userId, bool $remember = false): void {
    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);
    
    // Set user ID in session
    $_SESSION['user_id'] = $userId;
    
    // Set last activity time
    $_SESSION['last_activity'] = time();
    
    // Handle remember me
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        $expires = time() + (86400 * 30); // 30 days
        
        // Store token in database
        $pdo = get_pdo();
        $stmt = $pdo->prepare("INSERT INTO user_sessions (user_id, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $token, date('Y-m-d H:i:s', $expires)]);
        
        // Set cookie
        setcookie(
            'remember_token',
            $token,
            $expires,
            '/',
            '',
            isset($_SERVER['HTTPS']),
            true // httpOnly
        );
    }
}

/**
 * Log the current user out
 * 
 * @return void
 */
function logout_user(): void {
    // If remember me cookie exists, delete it from database
    if (isset($_COOKIE['remember_token'])) {
        $pdo = get_pdo();
        $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE token = ?");
        $stmt->execute([$_COOKIE['remember_token']]);
        
        // Delete the cookie
        setcookie('remember_token', '', time() - 3600, '/');
    }
    
    // Clear session data
    $_SESSION = [];
    
    // Delete session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
}

/**
 * Verify user credentials
 * 
 * @param string $email User email
 * @param string $password User password
 * @return array|false User data if credentials are valid, false otherwise
 */
function verify_credentials(string $email, string $password) {
    $user = get_user_by_email($email);
    
    if (!$user) {
        return false;
    }
    
    if (password_verify($password, $user['password'])) {
        // Check if password needs rehashing
        if (password_needs_rehash($user['password'], PASSWORD_BCRYPT)) {
            update_user_password($user['id'], $password);
        }
        return $user;
    }
    
    return false;
}
