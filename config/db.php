<?php
$host = getenv('DB_HOST') ?: getenv('MYSQLHOST') ?: '127.0.0.1';
if (!filter_var($host, FILTER_VALIDATE_IP) && strtolower($host) !== 'localhost') {
    $resolvedHost = gethostbyname($host);
    if ($resolvedHost === $host) {
        $host = '127.0.0.1';
    }
}
$port = (int)(getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: 3306);
$name = getenv('DB_NAME') ?: getenv('MYSQLDATABASE') ?: 'login';
$user = getenv('DB_USER') ?: getenv('MYSQLUSER') ?: 'root';
$pass = getenv('DB_PASS') ?: getenv('MYSQLPASSWORD') ?: '';
$dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];
$pdo = new PDO($dsn, $user, $pass, $options);
