<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('order_code')->unique();
            $table->foreignId('table_id')->nullable()->constrained('cafe_tables')->restrictOnDelete();
            $table->foreignId('cashier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('pending');
            $table->string('order_type')->default('qr');
            $table->uuid('uuid')->nullable();
            $table->integer('resubmit_count')->default(0);
            $table->string('qris_status')->nullable();
            $table->unsignedBigInteger('total_amount')->default(0);
            $table->string('payment_method')->nullable();
            $table->string('payment_proof')->nullable();
            $table->string('rejection_note')->nullable();
            $table->string('notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('created_at');
            $table->index('cashier_id');
            $table->index('order_type');
            $table->index('payment_method');
            $table->index(['status', 'created_at']);
            $table->index(['status', 'order_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
