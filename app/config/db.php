<?php
require_once __DIR__ . '/constants.php';

function getPDO()
{
    static $pdo = null;
    if ($pdo) return $pdo;
    // If running tests, use SQLite in-memory to keep tests fast and isolated
    if (defined('TESTING') && TESTING) {
        $dsn = 'sqlite::memory:';
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        try {
            $pdo = new PDO($dsn, null, null, $options);
        } catch (PDOException $e) {
            exit('Test DB connection failed: ' . $e->getMessage());
        }
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        // In production, do not reveal details
        exit('Database connection failed: ' . $e->getMessage());
    }
    return $pdo;
}
