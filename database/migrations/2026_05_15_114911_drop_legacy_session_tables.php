<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop order: active_staff_sessions first (FK to device_sessions),
        // then device_sessions, then work_sessions
        Schema::dropIfExists('active_staff_sessions');
        Schema::dropIfExists('device_sessions');
        Schema::dropIfExists('work_sessions');
    }

    public function down(): void
    {
        // Tables removed as part of staff-session-tracking refactor
    }
};
