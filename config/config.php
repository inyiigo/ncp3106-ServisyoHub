<?php
// Database configuration
$db_host = getenv('DB_HOST') ?: getenv('MYSQLHOST') ?: '127.0.0.1';
$db_port = (int)(getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: 3306);
$db_user = getenv('DB_USER') ?: getenv('MYSQLUSER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: getenv('MYSQLPASSWORD') ?: '';
$db_name = getenv('DB_NAME') ?: getenv('MYSQLDATABASE') ?: 'login';

// Timezone
date_default_timezone_set('Asia/Manila');
?>
