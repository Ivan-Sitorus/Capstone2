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
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_student_verified')) {
                $table->dropColumn('is_student_verified');
            }
            if (Schema::hasColumn('users', 'nim')) {
                $table->dropColumn('nim');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nim', 20)->nullable()->after('email');
            $table->boolean('is_student_verified')->default(false)->after('nim');
        });
    }
};
