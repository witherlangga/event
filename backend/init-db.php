<?php
// Ultra-simple database initialization
error_reporting(E_ALL);
ini_set('display_errors', 1);

$baseDir = __DIR__;
$dbPath = $baseDir . '/database/database.sqlite';
$dbDir = dirname($dbPath);

// Step 1: Create directory
if (!is_dir($dbDir)) {
    if (!mkdir($dbDir, 0777, true)) {
        die("Failed to create directory: $dbDir\n");
    }
    echo "✓ Created database directory\n";
} else {
    echo "✓ Database directory exists\n";
}

// Step 2: Create empty SQLite file if it doesn't exist
if (!file_exists($dbPath)) {
    try {
        // Create empty SQLite database
        $pdo = new PDO("sqlite:$dbPath");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "✓ Created SQLite database file\n";
    } catch (Exception $e) {
        die("Failed to create database: " . $e->getMessage() . "\n");
    }
} else {
    echo "✓ Database file already exists\n";
}

// Step 3: Verify file exists
if (file_exists($dbPath)) {
    $size = filesize($dbPath);
    echo "✓ Database file verified (size: $size bytes)\n";
} else {
    die("✗ Database file still doesn't exist!\n");
}

// Step 4: Bootstrap Laravel and run migrations
echo "\nLoading Laravel...\n";
require $baseDir . '/bootstrap/app.php';

$app = require $baseDir . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

echo "Running migrations...\n";
$exitCode = $kernel->call('migrate', ['--force' => true]);
if ($exitCode === 0) {
    echo "✓ Migrations completed successfully\n";
} else {
    echo "✗ Migrations failed with code: $exitCode\n";
}

echo "\n✓ Database initialization complete!\n";
?>
