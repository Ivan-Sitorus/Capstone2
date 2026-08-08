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
            $table->foreignId('category_id')->constrained('menu_categories')->restrictOnDelete();
            $table->string('name');
            $table->integer('price');
            $table->string('image')->nullable();
            $table->string('status');
            $table->integer('discounted_price')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
