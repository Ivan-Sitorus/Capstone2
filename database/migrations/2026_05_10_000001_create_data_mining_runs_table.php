<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
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
    }

    public function down(): void
    {
        Schema::dropIfExists('data_mining_runs');
    }
};
