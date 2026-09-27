<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `cashier_histories.user_id` was created as NOT NULL while its foreign key was
 * declared `nullOnDelete()`. PostgreSQL then rejected any user deletion because
 * `ON DELETE SET NULL` tried to write NULL into a NOT NULL column, producing:
 *
 *   SQLSTATE[23502]: null value in column "user_id" violates not-null constraint
 *
 * The history rows are an audit trail and must survive the deletion of the
 * cashier account, so the column is made nullable instead of switching the FK
 * to CASCADE (which would erase the history) or RESTRICT (which would make user
 * deletion impossible). The existing `ON DELETE SET NULL` constraint is kept.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cashier_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('cashier_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }
};
