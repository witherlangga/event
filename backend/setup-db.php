#!/usr/bin/env php
<?php
$baseDir = __DIR__;
$dbPath = $baseDir . '/database/database.sqlite';

// Ensure directory exists
@mkdir(dirname($dbPath), 0777, true);

// Create the SQLite database if it doesn't exist
if (!file_exists($dbPath)) {
    try {
        $pdo = new PDO("sqlite:$dbPath");
        echo "[✓] SQLite database created at: $dbPath\n";
    } catch (Exception $e) {
        echo "[✗] Failed to create database: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// Run the migrations
echo "[*] Running migrations...\n";
passthru('php ' . $baseDir . '/artisan migrate --force 2>&1');

echo "[✓] Database setup complete!\n";
