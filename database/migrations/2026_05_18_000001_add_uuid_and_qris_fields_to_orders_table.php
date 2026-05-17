<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->uuid('uuid')->nullable();
            $table->integer('resubmit_count')->default(0);
            $table->string('qris_status')->nullable();
            $table->string('whatsapp_phone', 20)->nullable();
        });

        // Backfill UUIDv4 for existing rows so unique index can be created
        // (UUIDv7 will be generated via tinker after migration)
        DB::statement('UPDATE orders SET uuid = gen_random_uuid() WHERE uuid IS NULL');

        // PostgreSQL 15+ unique index with NULLS NOT DISTINCT
        DB::statement('CREATE UNIQUE INDEX orders_uuid_unique ON orders (uuid) NULLS NOT DISTINCT');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS orders_uuid_unique');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['uuid', 'resubmit_count', 'qris_status', 'whatsapp_phone']);
        });
    }
};
