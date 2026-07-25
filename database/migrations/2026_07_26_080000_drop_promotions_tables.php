<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('applied_promotions');
        Schema::dropIfExists('promotion_rules');
        Schema::dropIfExists('promotions');
    }

    public function down(): void
    {
    }
};
