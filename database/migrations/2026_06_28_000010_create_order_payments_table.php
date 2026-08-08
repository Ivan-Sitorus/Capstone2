<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->unsignedBigInteger('amount');
            $table->timestamp('payment_date');
            $table->string('payment_method');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_payments');
    }
};
