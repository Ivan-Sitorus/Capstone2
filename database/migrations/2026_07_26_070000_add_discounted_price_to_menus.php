<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->unsignedBigInteger('discounted_price')->nullable()->after('price');
        });

        DB::table('menus')->whereNotNull('student_price')->update([
            'discounted_price' => DB::raw('student_price'),
        ]);

        Schema::table('menus', function (Blueprint $table) {
            $table->dropColumn(['student_price', 'cashback', 'is_student_discount']);
        });
    }

    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->unsignedBigInteger('student_price')->nullable();
            $table->unsignedBigInteger('cashback')->default(0);
            $table->boolean('is_student_discount')->default(false);
        });

        DB::table('menus')->whereNotNull('discounted_price')->update([
            'student_price' => DB::raw('discounted_price'),
            'is_student_discount' => true,
        ]);

        Schema::table('menus', function (Blueprint $table) {
            $table->dropColumn('discounted_price');
        });
    }
};
