<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Setiap GET /customer/menu dan /cashier/pesanan-baru query ini
        Schema::table('categories', function (Blueprint $table) {
            $table->index('is_active');
        });

        // Composite lebih efisien daripada dua index terpisah
        // Query: WHERE category_id = ? AND is_available = true
        Schema::table('menus', function (Blueprint $table) {
            $table->index(['category_id', 'is_available']);
        });

        // PromotionService query: WHERE status = ? AND start_date <= ? AND end_date >= ?
        // Composite index menutupi ketiga kondisi sekaligus
        Schema::table('promotions', function (Blueprint $table) {
            $table->index(['status', 'start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->dropIndex(['category_id', 'is_available']);
        });

        Schema::table('promotions', function (Blueprint $table) {
            $table->dropIndex(['status', 'start_date', 'end_date']);
        });
    }
};
