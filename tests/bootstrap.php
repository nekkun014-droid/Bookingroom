<?php
// Test bootstrap: enable testing mode and create in-memory schema
define('TESTING', true);
// load constants (DB_HOST etc) if needed but avoid connecting to MySQL
require_once __DIR__ . '/../app/config/constants.php';
require_once __DIR__ . '/../app/config/db.php';

$pdo = getPDO();
// create minimal schema required for tests
$pdo->exec("CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT,
    email TEXT,
    password TEXT,
    role_id INTEGER,
    created_at DATETIME
);");
$pdo->exec("CREATE TABLE rooms (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT,
    location TEXT,
    capacity INTEGER,
    created_at DATETIME
);");
$pdo->exec("CREATE TABLE bookings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    room_id INTEGER,
    start_time DATETIME,
    end_time DATETIME,
    status TEXT,
    created_at DATETIME
);");
$pdo->exec("CREATE TABLE timeslots (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT,
    start_time TEXT,
    end_time TEXT,
    created_at DATETIME
);");

// Ensure models can be autoloaded in tests
spl_autoload_register(function ($class) {
    $paths = [__DIR__ . '/../app/models/', __DIR__ . '/../app/controllers/'];
    foreach ($paths as $p) {
        $file = $p . $class . '.php';
        if (file_exists($file)) require_once $file;
    }
});
