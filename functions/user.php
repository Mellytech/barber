<?php

/**
 * Get user by ID
 * 
 * @param int $userId
 * @return array|null User data or null if not found
 */
function get_user_by_id($userId) {
    $pdo = get_pdo();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Get user by email
 * 
 * @param string $email
 * @return array|null User data or null if not found
 */
function get_user_by_email($email) {
    $pdo = get_pdo();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Update user password
 * 
 * @param int $userId
 * @param string $hashedPassword
 * @return bool True on success, false on failure
 */
function update_user_password($userId, $hashedPassword) {
    $pdo = get_pdo();
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    return $stmt->execute([$hashedPassword, $userId]);
}
