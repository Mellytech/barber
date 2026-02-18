<?php

require_once __DIR__ . '/config.php';

/**
 * Get a PDO database connection
 * 
 * @return PDO
 * @throws PDOException If connection fails
 */
function get_pdo(): PDO
{
    static $pdo = null;
    
    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8mb4',
            DB_HOST,
            DB_NAME
        );
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false,
        ];
        
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            throw new PDOException(
                'Could not connect to the database. Please check your database configuration.',
                (int)$e->getCode(),
                $e
            );
        }
    }
    
    return $pdo;
}

/**
 * Execute a query with parameters and return the statement
 * 
 * @param string $sql
 * @param array $params
 * @return PDOStatement
 */
function query(string $sql, array $params = []): PDOStatement
{
    $pdo = get_pdo();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Begin a transaction
 * 
 * @return bool
 */
function begin_transaction(): bool
{
    return get_pdo()->beginTransaction();
}

/**
 * Commit a transaction
 * 
 * @return bool
 */
function commit(): bool
{
    return get_pdo()->commit();
}

/**
 * Rollback a transaction
 * 
 * @return bool
 */
function rollback(): bool
{
    return get_pdo()->rollBack();
}
