<?php
// Removed from flow: redirect to compose page.
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$dest = './make-offer-compose.php' . ($id ? ('?id='.(int)$id) : '');
header('Location: ' . $dest, true, 302);
exit;
?>


