<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_batch_id')->constrained('ingredient_batches')->cascadeOnDelete();
            $table->unsignedBigInteger('amount');
            $table->timestamp('payment_date');
            $table->string('payment_method')->default('cash');
            $table->timestamps();

            $table->index('ingredient_batch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_payments');
    }
};
