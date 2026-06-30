<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['organizer_id']);
            $table->dropColumn('organizer_id');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('organizer_id')->nullable()->constrained('users')->cascadeOnDelete();
        });
    }
};
