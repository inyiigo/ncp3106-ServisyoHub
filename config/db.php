<?php
$host = getenv('DB_HOST') ?: getenv('MYSQLHOST') ?: '127.0.0.1';
$port = (int)(getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: 3306);
$name = getenv('DB_NAME') ?: getenv('MYSQLDATABASE') ?: 'login';
$user = getenv('DB_USER') ?: getenv('MYSQLUSER') ?: 'root';
$pass = getenv('DB_PASS') ?: getenv('MYSQLPASSWORD') ?: '';

$databaseUrl = getenv('DATABASE_URL') ?: getenv('MYSQL_URL') ?: '';
if (!empty($databaseUrl)) {
    $parsed = parse_url($databaseUrl);
    if ($parsed !== false) {
        if (!empty($parsed['host'])) {
            $host = $parsed['host'];
        }
        if (!empty($parsed['port'])) {
            $port = (int)$parsed['port'];
        }
        if (!empty($parsed['user'])) {
            $user = $parsed['user'];
        }
        if (array_key_exists('pass', $parsed)) {
            $pass = urldecode((string)$parsed['pass']);
        }
        if (!empty($parsed['path'])) {
            $name = ltrim($parsed['path'], '/');
        }
    }
}

$dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];
$pdo = new PDO($dsn, $user, $pass, $options);
