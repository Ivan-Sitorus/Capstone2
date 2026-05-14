<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW transaksi_keuangans AS
            SELECT
                i.id,
                'pemasukan'::varchar AS transaction_type,
                i.source,
                NULL::varchar AS vendor,
                i.category,
                i.amount,
                i.date,
                i.description,
                NULL::varchar AS payment_method,
                i.created_at,
                i.updated_at
            FROM incomes i
            UNION ALL
            SELECT
                (-e.id) AS id,
                'pengeluaran'::varchar AS transaction_type,
                NULL::varchar AS source,
                e.vendor,
                e.category,
                e.amount::numeric(15,2) AS amount,
                e.date,
                e.description,
                e.payment_method,
                e.created_at,
                e.updated_at
            FROM expenses e
        ");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS transaksi_keuangans');
    }
};
