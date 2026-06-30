<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo 'CWD=' . getcwd() . "\n";
echo '.env exists=' . (file_exists(__DIR__ . '/.env') ? 'yes' : 'no') . "\n";
echo 'getenv(DB_CONNECTION)=' . getenv('DB_CONNECTION') . "\n";
echo 'env(DB_CONNECTION)=' . env('DB_CONNECTION') . "\n";
echo 'config(database.default)=' . config('database.default') . "\n";
echo 'config(session.driver)=' . config('session.driver') . "\n";
echo 'config(db.mysql.database)=' . config('database.connections.mysql.database') . "\n";
echo 'config(db.sqlite.database)=' . config('database.connections.sqlite.database') . "\n";
