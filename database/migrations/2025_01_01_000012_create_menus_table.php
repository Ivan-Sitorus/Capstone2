<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('price')->default(0);
            $table->string('image')->nullable();
            $table->boolean('is_available')->default(true);
            $table->boolean('is_student_discount')->default(false);
            $table->unsignedBigInteger('student_price')->nullable();
            $table->unsignedBigInteger('cashback')->nullable();
            $table->timestamps();

            $table->index('category_id');
            $table->index('is_available');
            $table->index(['category_id', 'is_available']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
