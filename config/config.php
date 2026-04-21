<?php
// Database configuration
$db_host = getenv('DB_HOST') ?: getenv('MYSQLHOST') ?: '127.0.0.1';
$db_port = (int)(getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: 3306);
$db_user = getenv('DB_USER') ?: getenv('MYSQLUSER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: getenv('MYSQLPASSWORD') ?: '';
$db_name = getenv('DB_NAME') ?: getenv('MYSQLDATABASE') ?: 'login';

$databaseUrl = getenv('DATABASE_URL') ?: getenv('MYSQL_URL') ?: '';
if (!empty($databaseUrl)) {
	$parsed = parse_url($databaseUrl);
	if ($parsed !== false) {
		if (!empty($parsed['host'])) {
			$db_host = $parsed['host'];
		}
		if (!empty($parsed['port'])) {
			$db_port = (int)$parsed['port'];
		}
		if (!empty($parsed['user'])) {
			$db_user = $parsed['user'];
		}
		if (array_key_exists('pass', $parsed)) {
			$db_pass = urldecode((string)$parsed['pass']);
		}
		if (!empty($parsed['path'])) {
			$db_name = ltrim($parsed['path'], '/');
		}
	}
}

// Timezone
date_default_timezone_set('Asia/Manila');
?>
