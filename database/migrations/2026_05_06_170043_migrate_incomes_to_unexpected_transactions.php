<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if incomes table exists and has data
        if (!Schema::hasTable('incomes')) {
            echo('incomes table does not exist. Skipping migration.');
            return;
        }

        $incomeCount = DB::table('incomes')->count();

        if ($incomeCount === 0) {
            echo('No income records to migrate.');
        } else {
            echo("Migrating {$incomeCount} income records...");

            // Get all income records and insert into unexpected_transactions
            $incomes = DB::table('incomes')->get();

            foreach ($incomes as $income) {
                // Build deskripsi: "{source} - {category} - {description}"
                $deskripsi = $income->source;
                if ($income->category) {
                    $deskripsi .= ' - ' . $income->category;
                }
                if ($income->description) {
                    $deskripsi .= ' - ' . $income->description;
                }

                DB::table('unexpected_transactions')->insert([
                    'jenis' => 'pemasukan',
                    'nominal' => $income->amount,
                    'deskripsi' => $deskripsi,
                    'created_at' => $income->date,
                    'updated_at' => $income->updated_at ?? now(),
                ]);
            }

            echo("Successfully migrated {$incomeCount} income records to unexpected_transactions.");
        }

        // Drop the incomes table
        Schema::dropIfExists('incomes');
        echo('incomes table has been dropped.');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Create the incomes table
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->string('source');
            $table->string('category');
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('date');
            $table->index('category');
        });

        echo('incomes table has been recreated.');

        // Restore data from unexpected_transactions where jenis = 'pemasukan'
        $restoredCount = DB::table('unexpected_transactions')
            ->where('jenis', 'pemasukan')
            ->count();

        if ($restoredCount > 0) {
            echo("Restoring {$restoredCount} income records...");

            $transactions = DB::table('unexpected_transactions')
                ->where('jenis', 'pemasukan')
                ->get();

            foreach ($transactions as $transaction) {
                // Parse deskripsi to extract source, category, description
                // Format: "{source} - {category} - {description}"
                $parts = array_filter(array_map('trim', explode(' - ', $transaction->deskripsi ?? '')));

                $source = $parts[0] ?? 'Unknown';
                $category = $parts[1] ?? null;
                $description = $parts[2] ?? null;

                DB::table('incomes')->insert([
                    'source' => $source,
                    'category' => $category,
                    'amount' => $transaction->nominal,
                    'date' => $transaction->created_at,
                    'description' => $description,
                    'created_at' => $transaction->created_at,
                    'updated_at' => $transaction->updated_at,
                ]);
            }

            echo("Successfully restored {$restoredCount} income records.");
        }
    }
};