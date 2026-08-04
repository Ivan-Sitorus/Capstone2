<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('ingredient_batches')
            ->where('payment_status', 'belum_lunas')
            ->update(['payment_status' => 'unpaid']);

        Schema::table('ingredient_batches', function (Blueprint $table) {
            $table->string('payment_status', 20)->default('unpaid')->change();
        });
    }

    public function down(): void
    {
        DB::table('ingredient_batches')
            ->where('payment_status', 'unpaid')
            ->update(['payment_status' => 'belum_lunas']);

        Schema::table('ingredient_batches', function (Blueprint $table) {
            $table->string('payment_status', 20)->default('belum_lunas')->change();
        });
    }
};
