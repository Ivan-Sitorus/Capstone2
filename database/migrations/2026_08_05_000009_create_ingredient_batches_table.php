<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredient_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnDelete();
            $table->decimal('quantity', 10, 3);
            $table->date('expiry_date')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->decimal('cost_per_unit', 10, 3)->default(0);
            $table->integer('custom_order')->nullable();
            $table->string('status')->default('active');
            $table->string('batch_code')->nullable()->unique();
            $table->boolean('allow_expired_usage')->default(false);
            $table->decimal('initial_quantity', 10, 3)->nullable();
            $table->string('supplier_name')->nullable();
            $table->decimal('total_cost', 15, 2)->nullable();
            $table->string('payment_status')->default('unpaid');
            $table->timestamps();

            $table->index(['ingredient_id', 'expiry_date']);
            $table->index('received_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredient_batches');
    }
};
