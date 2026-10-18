<?php
$environment = getenv('APP_ENV') ?: 'local';
$isProduction = $environment === 'production';
$host = getenv('DB_HOST') ?: ($isProduction ? '' : 'localhost');
$port = getenv('DB_PORT') ?: '3306';
$dbName = getenv('DB_NAME') ?: 'campusmarket';
$username = getenv('DB_USER') ?: ($isProduction ? '' : 'root');
$password = getenv('DB_PASSWORD') ?: '';
$charset = 'utf8mb4';

$pdo = null;

if ($host === '' || $username === '' || ($isProduction && $password === '')) {
    error_log('Database configuration is incomplete. Set DB_HOST, DB_USER, and DB_PASSWORD for production.');
} else {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbName;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $pdo = new PDO($dsn, $username, $password, $options);
    } catch (PDOException $exception) {
        error_log('Database connection failed: ' . $exception->getMessage());
    }
}
