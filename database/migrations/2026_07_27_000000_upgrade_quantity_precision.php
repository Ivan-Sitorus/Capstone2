<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ingredient_batches
        DB::statement('ALTER TABLE ingredient_batches ALTER COLUMN quantity TYPE DECIMAL(12,3)');
        DB::statement('ALTER TABLE ingredient_batches ALTER COLUMN cost_per_unit TYPE DECIMAL(15,3)');

        // menu_ingredients
        DB::statement('ALTER TABLE menu_ingredients ALTER COLUMN quantity_used TYPE DECIMAL(12,3)');

        // stock_movements
        DB::statement('ALTER TABLE stock_movements ALTER COLUMN quantity_before TYPE DECIMAL(12,3)');
        DB::statement('ALTER TABLE stock_movements ALTER COLUMN quantity_change TYPE DECIMAL(12,3)');
        DB::statement('ALTER TABLE stock_movements ALTER COLUMN quantity_after TYPE DECIMAL(12,3)');
        DB::statement('ALTER TABLE stock_movements ALTER COLUMN unit_cost TYPE DECIMAL(12,3)');

        // stock_adjustments
        DB::statement('ALTER TABLE stock_adjustments ALTER COLUMN quantity TYPE DECIMAL(12,3)');
        DB::statement('ALTER TABLE stock_adjustments ALTER COLUMN quantity_before TYPE DECIMAL(12,3)');
        DB::statement('ALTER TABLE stock_adjustments ALTER COLUMN quantity_after TYPE DECIMAL(12,3)');

        // daily_ingredient_usages
        DB::statement('ALTER TABLE daily_ingredient_usages ALTER COLUMN jumlah_digunakan TYPE DECIMAL(12,3)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE ingredient_batches ALTER COLUMN quantity TYPE DECIMAL(12,2)');
        DB::statement('ALTER TABLE ingredient_batches ALTER COLUMN cost_per_unit TYPE DECIMAL(15,2)');
        DB::statement('ALTER TABLE menu_ingredients ALTER COLUMN quantity_used TYPE DECIMAL(12,2)');
        DB::statement('ALTER TABLE stock_movements ALTER COLUMN quantity_before TYPE DECIMAL(12,2)');
        DB::statement('ALTER TABLE stock_movements ALTER COLUMN quantity_change TYPE DECIMAL(12,2)');
        DB::statement('ALTER TABLE stock_movements ALTER COLUMN quantity_after TYPE DECIMAL(12,2)');
        DB::statement('ALTER TABLE stock_movements ALTER COLUMN unit_cost TYPE DECIMAL(12,2)');
        DB::statement('ALTER TABLE stock_adjustments ALTER COLUMN quantity TYPE DECIMAL(12,2)');
        DB::statement('ALTER TABLE stock_adjustments ALTER COLUMN quantity_before TYPE DECIMAL(12,2)');
        DB::statement('ALTER TABLE stock_adjustments ALTER COLUMN quantity_after TYPE DECIMAL(12,2)');
        DB::statement('ALTER TABLE daily_ingredient_usages ALTER COLUMN jumlah_digunakan TYPE DECIMAL(12,2)');
    }
};
