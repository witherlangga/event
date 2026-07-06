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
            $table->string('payment_method')->nullable()->after('status')->comment('Simulated payment method selected');
            $table->string('payment_channel')->nullable()->after('payment_method')->comment('Selected bank or e-wallet provider');
            $table->string('payment_reference')->nullable()->after('payment_channel')->comment('Virtual account / e-wallet reference or payment token');
            $table->text('payment_instructions')->nullable()->after('payment_reference')->comment('Payment instructions for simulation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'payment_channel',
                'payment_reference',
                'payment_instructions',
            ]);
        });
    }
};
