<?php

$host = getenv('DB_HOST') ?: getenv('MYSQLHOST') ?: '127.0.0.1';
$port = (int)(getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: 3306);
$user = getenv('DB_USER') ?: getenv('MYSQLUSER') ?: 'root';
$pass = getenv('DB_PASS') ?: getenv('MYSQLPASSWORD') ?: '';
$dbname = getenv('DB_NAME') ?: getenv('MYSQLDATABASE') ?: 'login';

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
			$dbname = ltrim($parsed['path'], '/');
		}
	}
}

mysqli_report(MYSQLI_REPORT_OFF);

$isRender = filter_var(getenv('RENDER') ?: 'false', FILTER_VALIDATE_BOOLEAN);
if ($isRender && in_array(strtolower($host), ['127.0.0.1', 'localhost'], true)) {
	die('Render database is not configured. Set DATABASE_URL (mysql://user:pass@host:3306/login) in Render Environment and redeploy.');
}

try {
	$conn = @new mysqli($host, $user, $pass, $dbname, $port);
} catch (Throwable $e) {
	die('Database connection failed. Verify DATABASE_URL or DB_* environment variables in Render.');
}

if ($conn->connect_error) {
	die('Database connection failed. Check DB_HOST/MYSQLHOST, DB_PORT/MYSQLPORT, DB_USER/MYSQLUSER, DB_PASS/MYSQLPASSWORD, DB_NAME/MYSQLDATABASE.');
}
?>