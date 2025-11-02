<?php
/**
 * Database Setup Script for Search History
 * Run this file once to create the search_history table
 */

// Include database configuration
$configPath = __DIR__ . '/config.php';
if (file_exists($configPath)) {
    require_once $configPath;
}

// Default connection parameters
$attempts = [];
if (isset($db_host, $db_user, $db_pass, $db_name)) {
    $attempts[] = [$db_host, $db_user, $db_pass, $db_name];
}
$attempts[] = ['localhost', 'root', '', 'servisyohub'];

$mysqli = null;
$connected = false;

foreach ($attempts as $creds) {
    list($h, $u, $p, $n) = $creds;
    mysqli_report(MYSQLI_REPORT_OFF);
    try {
        $conn = @mysqli_connect($h, $u, $p, $n);
        if ($conn && !mysqli_connect_errno()) {
            $mysqli = $conn;
            $connected = true;
            echo "✓ Connected to database: $n<br>";
            break;
        }
    } catch (Throwable $ex) {
        echo "✗ Connection failed: " . $ex->getMessage() . "<br>";
    }
}

if (!$connected) {
    die("<br><strong>ERROR:</strong> Could not connect to database. Please check your configuration.");
}

// Create search_history table
$sql = "CREATE TABLE IF NOT EXISTS search_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    search_query VARCHAR(255) NOT NULL,
    searched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_searched_at (searched_at DESC),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($mysqli, $sql)) {
    echo "✓ Table 'search_history' created successfully or already exists<br>";
    
    // Insert some sample data
    $sampleSearches = [
        'Buy and deliver item',
        'Booth Staff for pop-up',
        'Help me with moving',
        'Helper for an event',
        'House cleaning service',
        'Plumbing repair needed'
    ];
    
    foreach ($sampleSearches as $search) {
        $stmt = mysqli_prepare($mysqli, "INSERT INTO search_history (user_id, search_query, searched_at) VALUES (NULL, ?, NOW())");
        mysqli_stmt_bind_param($stmt, 's', $search);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    
    echo "✓ Sample search data inserted<br>";
    echo "<br><strong>Setup completed successfully!</strong><br>";
    echo "You can now use the search feature. Recent searches will appear as suggestions.";
} else {
    echo "✗ Error creating table: " . mysqli_error($mysqli) . "<br>";
}

mysqli_close($mysqli);
?>
