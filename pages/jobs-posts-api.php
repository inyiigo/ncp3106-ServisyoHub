<?php
session_start();
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../config/db.php'; // expects $pdo (PDO)
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'DB config error']);
    exit;
}

// Ensure table exists (idempotent)
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS job_posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        author_name VARCHAR(100) NOT NULL,
        content TEXT NOT NULL,
        location VARCHAR(200) NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX(created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
} catch (Throwable $e) {
    // ignore table creation errors for now; next operations will fail with clear message
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

function j($v){ return json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); }

if ($method === 'POST') {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)) { $data = $_POST; }

    $content = trim($data['content'] ?? '');
    $location = trim($data['location'] ?? '');

    if ($content === '') {
        http_response_code(400);
        echo j(['ok' => false, 'error' => 'Content is required']);
        exit;
    }

    // Limit lengths
    if (mb_strlen($content) > 1000) {
        $content = mb_substr($content, 0, 1000);
    }
    if (mb_strlen($location) > 200) {
        $location = mb_substr($location, 0, 200);
    }

    // Basic strip of HTML tags server-side
    $content = strip_tags($content);
    $location = strip_tags($location);

    $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    $author = isset($_SESSION['display_name']) && $_SESSION['display_name'] !== ''
        ? $_SESSION['display_name']
        : (isset($_SESSION['mobile']) ? $_SESSION['mobile'] : 'Guest');

    try {
        $stmt = $pdo->prepare('INSERT INTO job_posts (user_id, author_name, content, location) VALUES (?,?,?,?)');
        $stmt->execute([$userId, $author, $content, $location !== '' ? $location : null]);
        $id = (int)$pdo->lastInsertId();
        $row = $pdo->prepare('SELECT id, author_name AS author, content, location, created_at FROM job_posts WHERE id = ?');
        $row->execute([$id]);
        echo j(['ok' => true, 'post' => $row->fetch(PDO::FETCH_ASSOC)]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo j(['ok' => false, 'error' => 'Save failed']);
    }
    exit;
}

// GET: list posts (optionally since or limit)
$limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 50;
$sinceId = isset($_GET['since_id']) ? (int)$_GET['since_id'] : 0;

try {
    if ($sinceId > 0) {
        $stmt = $pdo->prepare('SELECT id, author_name AS author, content, location, created_at FROM job_posts WHERE id > ? ORDER BY id DESC LIMIT ?');
        $stmt->bindValue(1, $sinceId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
    } else {
        $stmt = $pdo->prepare('SELECT id, author_name AS author, content, location, created_at FROM job_posts ORDER BY id DESC LIMIT ?');
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
    }
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    echo j(['ok' => true, 'posts' => $rows]);
} catch (Throwable $e) {
    http_response_code(500);
    echo j(['ok' => false, 'error' => 'Load failed']);
}
