<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tambah nilai 'dibatalkan' ke check constraint kolom status orders.
     * Diperlukan untuk fitur pembatalan pesanan oleh kasir.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_status_check');
        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_status_check CHECK (status IN ('pending', 'diproses', 'selesai', 'dibatalkan'))");
    }

    public function down(): void
    {
        // Kembalikan pesanan dibatalkan agar tidak melanggar constraint lama
        DB::statement("UPDATE orders SET status = 'selesai' WHERE status = 'dibatalkan'");
        DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_status_check');
        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_status_check CHECK (status IN ('pending', 'diproses', 'selesai'))");
    }
};
