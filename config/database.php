<?php
declare(strict_types=1);

require_once __DIR__ . '/app.php';

try {
    define('DB_HOST', configEnv('DB_HOST', '127.0.0.1'));
    define('DB_PORT', configEnv('DB_PORT', '3306'));
    define('DB_NAME', configEnv('DB_NAME', 'gamegear_hub'));
    define('DB_USER', configEnv('DB_USER'));
    define('DB_PASS', configEnv('DB_PASS'));
} catch (RuntimeException $error) {
    error_log($error->getMessage());
    http_response_code(500);
    exit('Application configuration is incomplete. Check the server environment.');
}

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $error) {
    error_log($error->getMessage());
    http_response_code(500);
    exit('Database connection failed. Apply the database migration and check the server environment.');
}
