<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'nim')) {
                $table->string('nim', 20)->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'is_student_verified')) {
                $table->boolean('is_student_verified')->default(false)->after('nim');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nim', 'is_student_verified']);
        });
    }
};
