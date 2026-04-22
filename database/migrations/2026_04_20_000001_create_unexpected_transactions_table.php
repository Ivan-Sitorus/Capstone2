<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unexpected_transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('jenis', ['pemasukan', 'pengeluaran']);
            $table->decimal('nominal', 15, 2);
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unexpected_transactions');
    }
};
