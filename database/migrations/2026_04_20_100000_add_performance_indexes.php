<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // order_items: menu_id dipakai JOIN setiap tampil detail pesanan
        Schema::table('order_items', function (Blueprint $table) {
            $table->index('menu_id');
            $table->index('order_id');
        });

        // orders: kolom yang sering di-WHERE/filter
        Schema::table('orders', function (Blueprint $table) {
            $table->index('payment_method');
            $table->index('cashier_id');
            $table->index('is_paid');
            // Composite index untuk query dashboard (status + created_at)
            $table->index(['status', 'created_at']);
        });

        // menus: category_id dipakai filter menu per kategori
        Schema::table('menus', function (Blueprint $table) {
            $table->index('category_id');
            $table->index('is_available');
        });

        // stock_movements: frequently queried for inventory reports
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->index('ingredient_id');
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['menu_id']);
            $table->dropIndex(['order_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['payment_method']);
            $table->dropIndex(['cashier_id']);
            $table->dropIndex(['is_paid']);
            $table->dropIndex(['status', 'created_at']);
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->dropIndex(['category_id']);
            $table->dropIndex(['is_available']);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex(['ingredient_id']);
            $table->dropIndex(['order_id']);
        });
    }
};
