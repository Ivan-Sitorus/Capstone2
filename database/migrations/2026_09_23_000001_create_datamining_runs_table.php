<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('datamining_runs', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('status')->default('running');
            $table->json('parameters')->nullable();
            $table->json('payload')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('datamining_runs');
    }
};
