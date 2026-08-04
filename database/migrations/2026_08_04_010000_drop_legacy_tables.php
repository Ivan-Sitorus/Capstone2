<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop 8 legacy tables that are no longer used anywhere in the codebase:
     * data mining, generated reports, expenses, incomes, payments (superseded
     * by order_payments/batch_payments/receivable_payments), stock reports,
     * unexpected transactions, and ingredient unit conversions.
     */
    public function up(): void
    {
        Schema::dropIfExists('data_mining_runs');
        Schema::dropIfExists('generated_reports');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('incomes');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('stock_reports');
        Schema::dropIfExists('unexpected_transactions');
        Schema::dropIfExists('ingredient_unit_conversions');
    }

    /**
     * Recreate the legacy tables with their original schemas for safe rollback.
     */
    public function down(): void
    {
        Schema::create('data_mining_runs', function (Blueprint $table) {
            $table->id();
            $table->string('analysis_type', 50);
            $table->string('status', 20)->default('completed');
            $table->date('date_range_start');
            $table->date('date_range_end');
            $table->jsonb('parameters')->nullable();
            $table->jsonb('preprocessing_logs')->nullable();
            $table->jsonb('result')->nullable();
            $table->jsonb('charts')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('run_at')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['analysis_type', 'run_at']);
            $table->index('status');
        });

        Schema::create('generated_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 255);
            $table->string('type', 50);
            $table->date('date_start');
            $table->date('date_end');
            $table->string('aggregation', 50)->default('monthly');
            $table->json('categories')->nullable();
            $table->json('result');
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('vendor', 100);
            $table->string('category', 50);
            $table->unsignedBigInteger('amount');
            $table->date('date');
            $table->string('description')->nullable();
            $table->string('payment_method', 20)->nullable();
            $table->timestamps();
        });

        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->string('source');
            $table->string('category');
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('date');
            $table->index('category');
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->enum('payment_method', ['qris', 'ewallet', 'cash', 'transfer']);
            $table->enum('payment_gateway', ['manual']);
            $table->string('transaction_id')->nullable()->unique();
            $table->unsignedBigInteger('amount');
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnDelete();
            $table->foreignId('reported_by')->constrained('users')->cascadeOnDelete();
            $table->enum('report_type', ['increase', 'decrease']);
            $table->decimal('quantity', 12, 2);
            $table->decimal('quantity_before', 12, 2)->nullable();
            $table->decimal('quantity_after', 12, 2)->nullable();
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['ingredient_id', 'status']);
        });

        Schema::create('unexpected_transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('jenis', ['pemasukan', 'pengeluaran']);
            $table->decimal('nominal', 15, 2);
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        Schema::create('ingredient_unit_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('from_unit_id')->constrained('units');
            $table->foreignId('to_unit_id')->constrained('units');
            $table->decimal('conversion_factor', 16, 6);
            $table->timestamps();
            $table->unique(['from_unit_id', 'to_unit_id', 'ingredient_id']);
        });
    }
};
