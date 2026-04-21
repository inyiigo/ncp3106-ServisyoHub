<?php

$host = getenv('DB_HOST') ?: getenv('MYSQLHOST') ?: '127.0.0.1';
if (!filter_var($host, FILTER_VALIDATE_IP) && strtolower($host) !== 'localhost') {
	$resolvedHost = gethostbyname($host);
	if ($resolvedHost === $host) {
		$host = '127.0.0.1';
	}
}
$port = (int)(getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: 3306);
$user = getenv('DB_USER') ?: getenv('MYSQLUSER') ?: 'root';
$pass = getenv('DB_PASS') ?: getenv('MYSQLPASSWORD') ?: '';
$dbname = getenv('DB_NAME') ?: getenv('MYSQLDATABASE') ?: 'login';

$conn = new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
	die('Database connection failed. Please check DB environment variables.');
}
?>