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
            $table->string('phone', 20)->nullable()->after('customer_name');
        });

        DB::table('orders')->update([
            'phone' => DB::raw("COALESCE(NULLIF(TRIM(whatsapp_phone), ''), NULLIF(TRIM(customer_phone), ''))"),
        ]);

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['customer_phone', 'whatsapp_phone']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('customer_phone', 20)->nullable();
            $table->string('whatsapp_phone', 20)->nullable();
        });

        DB::table('orders')->update([
            'customer_phone' => DB::raw('phone'),
        ]);

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('phone');
        });
    }
};
