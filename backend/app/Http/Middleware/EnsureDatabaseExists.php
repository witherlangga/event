<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PDO;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

class EnsureDatabaseExists
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        static $initialized = false;
        
        if (!$initialized && config('database.default') === 'sqlite') {
            $path = config('database.connections.sqlite.database');
            
            if ($path && !file_exists($path)) {
                $dir = dirname($path);
                
                try {
                    // Create directory if needed.
                    if (!is_dir($dir)) {
                        mkdir($dir, 0777, true);
                    }
                    
                    // Create SQLite database file.
                    $pdo = new PDO("sqlite:$path");
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    unset($pdo);
                    
                    // Run migrations only after SQLite DB is created.
                    \Artisan::call('migrate', ['--force' => true]);
                    
                    \Log::info('Database initialized successfully');
                } catch (\Exception $e) {
                    \Log::error('Database initialization error: ' . $e->getMessage());
                }
            } elseif (!$path) {
                \Log::warning('SQLite database initialization skipped: sqlite database path is not configured.');
            }
            
            $initialized = true;
        }
        
        return $next($request);
    }
}
