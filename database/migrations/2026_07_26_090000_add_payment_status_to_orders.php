<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // First, drop the existing CHECK constraint to allow 'piutang' value
        DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_payment_method_check');

        // Add 'piutang' to the allowed values
        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_payment_method_check CHECK (payment_method IN ('cash', 'qris', 'bayar_nanti', 'piutang'))");

        // Add payment_status column
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_status', 20)->default('lunas')->after('payment_method');
        });

        // Existing bayar_nanti orders → piutang, belum_lunas
        DB::table('orders')->where('payment_method', 'bayar_nanti')
            ->update(['payment_status' => 'belum_lunas', 'payment_method' => 'piutang']);

        // All other existing orders → lunas (redundant with default, but explicit for safety)
        DB::table('orders')->where('payment_status', '!=', 'belum_lunas')
            ->update(['payment_status' => 'lunas']);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('payment_status');
        });
    }
};
