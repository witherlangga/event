<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('band_profiles', function (Blueprint $table) {
            $table->string('next_show_title')->nullable();
            $table->string('next_show_date')->nullable();
            $table->string('next_show_price_text')->nullable();
            $table->string('next_show_location_name')->nullable();
            $table->text('next_show_location_address')->nullable();
            $table->string('next_show_map_link')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('band_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'next_show_title',
                'next_show_date',
                'next_show_price_text',
                'next_show_location_name',
                'next_show_location_address',
                'next_show_map_link',
            ]);
        });
    }
};
