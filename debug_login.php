<?php
require_once __DIR__ . '/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get form data
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'debug' => []
];

try {
    // Check if email and password are provided
    if (empty($email) || empty($password)) {
        throw new Exception('Email and password are required');
    }

    // Get database connection
    $pdo = get_pdo();
    $response['debug']['database_connection'] = $pdo ? 'Connected to database successfully' : 'Failed to connect to database';

    // Find user by email
    $stmt = $pdo->prepare('SELECT id, name, email, password_hash FROM users WHERE email = :email');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('No user found with this email');
    }

    $response['debug']['user_found'] = true;
    $response['debug']['user_id'] = $user['id'];

    // Verify password
    $passwordMatch = password_verify($password, $user['password_hash']);
    $response['debug']['password_verified'] = $passwordMatch;

    if (!$passwordMatch) {
        throw new Exception('Incorrect password');
    }

    // Check if user is active
    if (isset($user['is_active']) && !$user['is_active']) {
        throw new Exception('This account has been deactivated');
    }

    $response['success'] = true;
    $response['message'] = 'Login successful';
    $response['user'] = [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email']
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Output debug information
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
?>
