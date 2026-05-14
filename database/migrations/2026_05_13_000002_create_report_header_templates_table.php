<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('report_header_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('entity_name');
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->jsonb('additional_info')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_header_templates');
    }
};
