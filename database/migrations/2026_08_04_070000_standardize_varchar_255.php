<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('notes')->nullable()->change();
            $table->string('order_code')->change();
            $table->string('payment_proof')->nullable()->change();
            $table->string('phone', 20)->nullable()->change();
        });

        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->string('cancel_reason')->nullable()->change();
            $table->string('adjustable_type')->change();
            $table->string('code')->nullable()->change();
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->string('notes')->nullable()->change();
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->string('value')->change();
            $table->string('key')->change();
        });

        Schema::table('cafe_tables', function (Blueprint $table) {
            $table->string('qr_code')->change();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->string('name')->change();
        });

        Schema::table('daily_ingredient_usages', function (Blueprint $table) {
            $table->string('unit')->change();
        });

        Schema::table('ingredient_batches', function (Blueprint $table) {
            $table->string('batch_code')->nullable()->change();
            $table->string('payment_status')->default('unpaid')->change();
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->string('status')->default('active')->change();
        });

        Schema::table('order_payments', function (Blueprint $table) {
            $table->string('payment_method')->change();
        });

        Schema::table('batch_payments', function (Blueprint $table) {
            $table->string('payment_method')->default('cash')->change();
        });

        Schema::table('units', function (Blueprint $table) {
            $table->string('name')->change();
            $table->string('abbreviation')->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('cashier')->change();
        });

        Schema::table('cashier_histories', function (Blueprint $table) {
            $table->string('type')->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->text('notes')->nullable()->change();
            $table->string('order_code', 50)->change();
            $table->string('payment_proof', 500)->nullable()->change();
            $table->string('phone', 20)->nullable()->change();
        });

        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->text('cancel_reason')->nullable()->change();
            $table->string('adjustable_type', 50)->change();
            $table->string('code', 50)->nullable()->change();
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->text('notes')->nullable()->change();
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->text('value')->change();
            $table->string('key', 100)->change();
        });

        Schema::table('cafe_tables', function (Blueprint $table) {
            $table->string('qr_code', 500)->change();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->string('name', 100)->change();
        });

        Schema::table('daily_ingredient_usages', function (Blueprint $table) {
            $table->string('unit', 20)->change();
        });

        Schema::table('ingredient_batches', function (Blueprint $table) {
            $table->string('batch_code', 50)->nullable()->change();
            $table->string('payment_status', 20)->default('unpaid')->change();
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->string('status', 20)->default('active')->change();
        });

        Schema::table('order_payments', function (Blueprint $table) {
            $table->string('payment_method', 20)->change();
        });

        Schema::table('batch_payments', function (Blueprint $table) {
            $table->string('payment_method', 20)->default('cash')->change();
        });

        Schema::table('units', function (Blueprint $table) {
            $table->string('name', 50)->change();
            $table->string('abbreviation', 10)->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('cashier')->change();
        });

        Schema::table('cashier_histories', function (Blueprint $table) {
            $table->string('type', 20)->change();
        });
    }
};
