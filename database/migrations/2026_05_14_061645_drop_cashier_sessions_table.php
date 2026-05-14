<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('cashier_sessions');
    }

    public function down(): void
    {
        // Table creation removed — CashierSession feature is deleted.
    }
};
