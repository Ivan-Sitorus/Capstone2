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
            $table->string('customer_phone', 20)->nullable();
            $table->string('order_code', 50)->unique();
            $table->foreignId('table_id')->nullable()->constrained('cafe_tables')->onDelete('restrict');
            $table->foreignId('cashier_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['pending', 'diproses', 'selesai'])->default('pending');
            $table->enum('order_type', ['qr', 'cashier'])->default('qr');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->enum('payment_method', ['cash', 'qris', 'bayar_nanti'])->nullable();
            $table->string('payment_proof', 500)->nullable();
            $table->string('rejection_note', 255)->nullable();
            $table->boolean('is_paid')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('created_at');
            $table->index('payment_method');
            $table->index('cashier_id');
            $table->index('is_paid');
            $table->index(['status', 'created_at']);
            $table->index('customer_phone');
            $table->index('order_type');
            $table->index(['status', 'order_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
