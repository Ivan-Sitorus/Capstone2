<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('staff_sessions', 'cashier_histories');
    }

    public function down(): void
    {
        Schema::rename('cashier_histories', 'staff_sessions');
    }
};
