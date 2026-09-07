<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Perluasan nilai enum status agar selaras dengan alur status di aplikasi (confirmed, ready, shipped, delivered)
        DB::statement("ALTER TABLE `transactions` MODIFY COLUMN `status` ENUM('pending','confirmed','processing','ready','shipped','delivered','completed','cancelled') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Normalisasi terlebih dahulu status yang tidak ada pada enum lama
        DB::statement("UPDATE `transactions` SET `status` = 'processing' WHERE `status` IN ('confirmed','ready','shipped','delivered')");

        // Kembalikan enum ke definisi awal
        DB::statement("ALTER TABLE `transactions` MODIFY COLUMN `status` ENUM('pending','processing','completed','cancelled') NOT NULL DEFAULT 'pending'");
    }
};