<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnDelete();
            $table->foreignId('ingredient_batch_id')->nullable()->constrained('ingredient_batches')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
            $table->foreignId('stock_adjustment_id')->nullable()->constrained('stock_adjustments')->nullOnDelete();
            $table->string('movement_type');
            $table->string('source_type')->nullable();
            $table->string('source_id')->nullable();
            $table->decimal('quantity_before', 10, 3);
            $table->decimal('quantity_change', 10, 3);
            $table->decimal('quantity_after', 10, 3);
            $table->unsignedBigInteger('unit_cost')->nullable();
            $table->string('reference')->nullable();
            $table->timestamps();

            $table->index('ingredient_id');
            $table->index('order_id');
            $table->index(['ingredient_id', 'created_at']);
            $table->index(['movement_type', 'created_at']);
            $table->index(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
