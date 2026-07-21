<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropIndex(['category_id', 'is_available']);
            $table->dropIndex(['is_available']);

            $table->string('status', 20)->default('active')->after('id');

            $table->dropColumn('is_available');
            $table->dropSoftDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->boolean('is_available')->default(true);
            $table->softDeletes();
            $table->dropColumn('status');
            $table->index('is_available');
            $table->index(['category_id', 'is_available']);
        });
    }
};
