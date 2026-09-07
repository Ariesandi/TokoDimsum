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
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('payment_proof')->nullable()->after('payment_method');
            $table->enum('payment_status', ['pending', 'verified', 'rejected'])->default('pending')->after('payment_proof');
            $table->string('tracking_number')->nullable()->after('payment_status');
            $table->text('payment_notes')->nullable()->after('tracking_number');
            $table->timestamp('payment_verified_at')->nullable()->after('payment_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'payment_proof',
                'payment_status', 
                'tracking_number',
                'payment_notes',
                'payment_verified_at'
            ]);
        });
    }
};