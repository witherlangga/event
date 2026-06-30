<?php
// Simple migration runner script
require __DIR__ . '/bootstrap/app.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

$status = $kernel->call('migrate:fresh', ['--force' => true, '--seed' => true]);

exit($status);
?>
