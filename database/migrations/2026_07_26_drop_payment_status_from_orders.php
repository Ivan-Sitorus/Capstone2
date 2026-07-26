<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'belum_lunas' to status CHECK constraint before backfill
        DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_status_check');
        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_status_check CHECK (status::text = ANY (ARRAY['pending'::varchar, 'diproses'::varchar, 'selesai'::varchar, 'dibatalkan'::varchar, 'belum_lunas'::varchar]::text[]))");

        DB::table('orders')
            ->where('payment_status', 'belum_lunas')
            ->update(['status' => 'belum_lunas']);

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_status', 20)->default('lunas')->after('payment_method');
        });

        DB::table('orders')
            ->where('status', 'belum_lunas')
            ->where('payment_method', 'piutang')
            ->update(['payment_status' => 'belum_lunas']);

        DB::table('orders')
            ->where('payment_status', null)
            ->update(['payment_status' => 'lunas']);

        DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_status_check');
        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_status_check CHECK (status::text = ANY (ARRAY['pending'::varchar, 'diproses'::varchar, 'selesai'::varchar, 'dibatalkan'::varchar]::text[]))");
    }
};
