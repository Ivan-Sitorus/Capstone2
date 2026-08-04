<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('receivable_payments');
        Schema::dropIfExists('receivables');
    }

    public function down(): void
    {
        Schema::create('receivables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('amount');
            $table->string('customer_name', 100)->nullable();
            $table->unsignedBigInteger('paid_amount')->default(0);
            $table->string('status', 20)->default('pending');
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('order_id');
        });

        Schema::create('receivable_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receivable_id')->constrained('receivables')->cascadeOnDelete();
            $table->unsignedBigInteger('amount');
            $table->timestamp('payment_date')->useCurrent();
            $table->string('payment_method', 50)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }
};
