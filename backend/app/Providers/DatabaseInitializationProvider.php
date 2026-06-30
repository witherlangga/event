<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use PDO;

class DatabaseInitializationProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Auto-create SQLite database if it doesn't exist
        if (config('database.default') === 'sqlite') {
            $path = config('database.connections.sqlite.database');
            
            if ($path && !file_exists($path)) {
                $dir = dirname($path);
                if (!is_dir($dir)) {
                    @mkdir($dir, 0777, true);
                }
                
                try {
                    // Create empty SQLite database
                    $db = new PDO("sqlite:$path");
                    $db = null;
                    \Log::info('SQLite database created at: ' . $path);
                    
                    // Run migrations
                    \Artisan::call('migrate', ['--force' => true]);
                    \Log::info('Migrations completed');
                } catch (\Exception $e) {
                    \Log::error('Database initialization failed: ' . $e->getMessage());
                }
            }
        }
    }
}
