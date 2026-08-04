<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashier_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->nullOnDelete();
            $table->string('type');
            $table->string('session_id')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamp('last_activity_at')->useCurrent();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('type');
            $table->index('session_id');
            $table->index('is_active');
            $table->index(['user_id', 'is_active']);
            $table->index(['is_active', 'last_activity_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashier_histories');
    }
};
