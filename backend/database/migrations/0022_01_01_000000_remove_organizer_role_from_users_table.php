<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus user dengan role organizer (tidak lagi digunakan)
        DB::table('users')->where('role', 'organizer')->delete();

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('system_admin', 'customer') NOT NULL DEFAULT 'customer'");
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('system_admin', 'organizer', 'customer') NOT NULL DEFAULT 'customer'");
        }
    }
};
