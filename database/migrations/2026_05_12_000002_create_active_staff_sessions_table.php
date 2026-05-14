<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('active_staff_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_session_id')
                ->constrained('device_sessions')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->timestamp('pin_verified_at')->nullable();
            $table->string('active_context')->nullable();
            $table->timestamps();

            $table->index('pin_verified_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('active_staff_sessions');
    }
};
