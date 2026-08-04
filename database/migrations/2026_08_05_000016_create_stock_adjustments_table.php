<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->nullable()->constrained('ingredients')->cascadeOnDelete();
            $table->string('adjustment_type');
            $table->string('category')->nullable();
            $table->decimal('quantity', 10, 3);
            $table->decimal('quantity_before', 10, 3);
            $table->decimal('quantity_after', 10, 3);
            $table->string('reason')->nullable();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('adjusted_at')->useCurrent();
            $table->string('adjustable_type');
            $table->foreignId('menu_id')->nullable()->constrained('menus')->nullOnDelete();
            $table->string('code')->nullable()->unique();
            $table->timestamps();

            $table->index(['adjustment_type', 'adjusted_at']);
            $table->index(['ingredient_id', 'adjusted_at']);
            $table->index(['category', 'adjusted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
