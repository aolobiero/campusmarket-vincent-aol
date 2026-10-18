<?php
$environment = getenv('APP_ENV') ?: 'local';
$isProduction = $environment === 'production';
$host = getenv('DB_HOST') ?: ($isProduction ? '' : 'localhost');
$port = getenv('DB_PORT') ?: '3306';
$dbName = getenv('DB_NAME') ?: 'campusmarket';
$username = getenv('DB_USER') ?: ($isProduction ? '' : 'root');
$password = getenv('DB_PASSWORD') ?: '';
$sslCa = getenv('DB_SSL_CA') ?: '';
$charset = 'utf8mb4';

$pdo = null;

if ($host === '' || $username === '' || ($isProduction && ($password === '' || $sslCa === '' || !is_readable($sslCa)))) {
    error_log('Database configuration is incomplete. Set DB_HOST, DB_USER, DB_PASSWORD, and a readable DB_SSL_CA file for production.');
} elseif ($isProduction && (!defined('PDO::MYSQL_ATTR_SSL_CA') || !defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT'))) {
    error_log('Production database TLS is unavailable: PDO MySQL SSL attributes are missing.');
} else {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbName;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    if ($isProduction) {
        $options[constant('PDO::MYSQL_ATTR_SSL_CA')] = $sslCa;
        $options[constant('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')] = true;
    }

    try {
        $pdo = new PDO($dsn, $username, $password, $options);
    } catch (PDOException $exception) {
        error_log('Database connection failed: ' . $exception->getMessage());
    }
}
