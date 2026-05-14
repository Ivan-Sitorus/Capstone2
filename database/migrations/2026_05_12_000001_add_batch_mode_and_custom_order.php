<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add batch_mode to ingredients table
        Schema::table('ingredients', function (Blueprint $table) {
            $table->string('batch_mode')->default('fefo')->after('is_active');
        });

        // Add custom_order to ingredient_batches table
        Schema::table('ingredient_batches', function (Blueprint $table) {
            $table->integer('custom_order')->nullable()->after('cost_per_unit');
        });

        // Add check constraint to enforce valid batch_mode values
        DB::statement("ALTER TABLE ingredients ADD CONSTRAINT ingredients_batch_mode_check CHECK (batch_mode IN ('fifo', 'fefo', 'custom'))");
    }

    public function down(): void
    {
        Schema::table('ingredient_batches', function (Blueprint $table) {
            $table->dropColumn('custom_order');
        });

        DB::statement('ALTER TABLE ingredients DROP CONSTRAINT IF EXISTS ingredients_batch_mode_check');

        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropColumn('batch_mode');
        });
    }
};
