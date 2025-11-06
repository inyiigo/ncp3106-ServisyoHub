<?php
// Removed from flow: redirect to compose page.
if (session_status() === PHP_SESSION_NONE) { @session_start(); }

// Build absolute redirect URL to compose page, preserving id and amount (if valid)
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base   = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
$path   = $base . '/make-offer-compose.php';

$params = [];
if (isset($_GET['id']) && ctype_digit((string)$_GET['id'])) {
	$params['id'] = (int)$_GET['id'];
}
if (isset($_GET['amount'])) {
	$amt = (float)$_GET['amount'];
	if ($amt > 0) $params['amount'] = number_format($amt, 2, '.', '');
}

$url = $scheme . '://' . $host . $path . (empty($params) ? '' : ('?' . http_build_query($params)));

if (!headers_sent()) {
	header('Location: ' . $url, true, 302);
	exit;
}

// Fallback if headers already sent
?>
<!DOCTYPE html>
<html><head><meta http-equiv="refresh" content="0;url=<?php echo htmlspecialchars($url, ENT_QUOTES); ?>"></head><body></body></html>


