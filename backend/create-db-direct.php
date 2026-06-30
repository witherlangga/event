<?php
// Direct database creation script
$path = 'd:\\mobile_computing\\event\\backend\\database\\database.sqlite';
$dir = dirname($path);

// Create directory if it doesn't exist
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
    echo "Created directory: $dir\n";
}

// Create SQLite database
try {
    $pdo = new PDO("sqlite:$path");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Database file created: $path\n";
    echo "File exists: " . (file_exists($path) ? "YES" : "NO") . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
