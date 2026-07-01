<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_qr_data')->nullable()->comment('QRIS data for payment');
            $table->timestamp('payment_deadline')->nullable()->comment('Deadline for payment (24 hours)');
            $table->timestamp('paid_at')->nullable()->comment('When payment was confirmed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_qr_data', 'payment_deadline', 'paid_at']);
        });
    }
};
