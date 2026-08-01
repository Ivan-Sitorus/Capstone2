<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingredient_batches', function (Blueprint $table) {
            $table->decimal('initial_quantity', 12, 3)->nullable()->after('quantity');
            $table->string('supplier_name')->nullable()->after('ingredient_id');
            $table->decimal('total_cost', 15, 2)->nullable()->after('cost_per_unit');
            $table->string('payment_status', 20)->default('belum_lunas')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('ingredient_batches', function (Blueprint $table) {
            $table->dropColumn(['initial_quantity', 'supplier_name', 'total_cost', 'payment_status']);
        });
    }
};
