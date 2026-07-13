<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('unit', ['gram', 'kg', 'ml', 'liter', 'pcs', 'sachet', 'sdm', 'sdt']);
            $table->decimal('low_stock_threshold', 12, 2)->default(0);
            $table->string('batch_mode')->default('fefo');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
