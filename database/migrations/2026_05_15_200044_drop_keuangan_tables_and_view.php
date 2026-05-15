<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('report_templates');
        Schema::dropIfExists('report_header_templates');
        DB::statement('DROP VIEW IF EXISTS transaksi_keuangans');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Feature permanently removed — no recreation needed
    }
};
