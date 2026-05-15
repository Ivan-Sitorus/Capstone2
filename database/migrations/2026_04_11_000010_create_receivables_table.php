<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receivables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('amount');
            $table->string('customer_name', 100);
            $table->unsignedBigInteger('paid_amount')->default(0);
            $table->string('status', 20)->default('pending');
            $table->date('due_date')->nullable();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('due_date');
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receivables');
    }
};
