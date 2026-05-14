<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\Order;
use App\Models\UnexpectedTransaction;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Seeds the database with exact financial data for TDD correctness tests.
 *
 * Expected totals for the seeded date range:
 *   Total Income:  Rp 1,050,000  (Orders 1,000,000 + Unexpected pemasukan 50,000)
 *   Total Expense: Rp   130,000  (IngredientBatch 100,000 + Expense 25,000 + Unexpected pengeluaran 5,000)
 *   Net:           Rp   920,000
 *
 * All records fall within 2026-05-01 00:00:00 — 2026-05-31 23:59:59.
 */
class FinancialReportTestDataSeeder extends Seeder
{
    /**
     * Base date for all seeded records. Keep consistent so tests are deterministic.
     */
    public readonly Carbon $baseDate;

    public function __construct()
    {
        $this->baseDate = Carbon::parse('2026-05-01 08:00:00');
    }

    public function run(): void
    {
        $this->seedOrders();
        $this->seedUnexpectedIncome();
        $this->seedIngredientBatches();
        $this->seedExpenses();
        $this->seedUnexpectedExpense();
    }

    /**
     * 5 paid orders with exact total_amount.
     * Sum: 100000 + 200000 + 150000 + 300000 + 250000 = 1,000,000
     */
    private function seedOrders(): void
    {
        $amounts = [100000, 200000, 150000, 300000, 250000];
        $names = ['Budi', 'Ani', 'Cici', 'Dedi', 'Eni'];

        foreach ($amounts as $i => $amount) {
            Order::create([
                'order_code' => 'ORD-TDD-'.($i + 1),
                'customer_name' => $names[$i],
                'total_amount' => $amount,
                'is_paid' => true,
                'payment_method' => $i % 2 === 0 ? 'cash' : 'qris',
                'status' => Order::STATUS_SELESAI,
                'created_at' => $this->baseDate->copy()->addHours($i),
            ]);
        }
    }

    /**
     * 1 unexpected pemasukan: 50,000
     */
    private function seedUnexpectedIncome(): void
    {
        UnexpectedTransaction::create([
            'jenis' => 'pemasukan',
            'nominal' => 50000,
            'deskripsi' => 'Donasi pelanggan',
            'created_at' => $this->baseDate->copy()->addHours(2),
        ]);
    }

    /**
     * 3 ingredient batches.
     * Sum: (5*5000=25000) + (3*10000=30000) + (15*3000=45000) = 100,000
     */
    private function seedIngredientBatches(): void
    {
        $ingredient = Ingredient::create([
            'name' => 'Biji Kopi Arabika',
            'unit' => 'kg',
            'low_stock_threshold' => 1,
            'is_active' => true,
        ]);

        $batches = [
            ['quantity' => 5,  'cost_per_unit' => 5000],   // 25,000
            ['quantity' => 3,  'cost_per_unit' => 10000],  // 30,000
            ['quantity' => 15, 'cost_per_unit' => 3000],   // 45,000
        ];

        foreach ($batches as $i => $batch) {
            IngredientBatch::create([
                'ingredient_id' => $ingredient->id,
                'quantity' => $batch['quantity'],
                'cost_per_unit' => $batch['cost_per_unit'],
                'received_at' => $this->baseDate->copy()->addHours(5 + $i),
                'expiry_date' => $this->baseDate->copy()->addYear(),
            ]);
        }
    }

    /**
     * 2 expenses.
     * Sum: 10000 + 15000 = 25,000
     */
    private function seedExpenses(): void
    {
        Expense::create([
            'vendor' => 'Toko ATK Makmur',
            'category' => 'ATK',
            'amount' => 10000,
            'date' => $this->baseDate->toDateString(),
            'description' => 'Pembelian kertas HVS',
            'payment_method' => 'cash',
            'created_at' => $this->baseDate->copy()->addHours(8),
        ]);

        Expense::create([
            'vendor' => 'UD Gas LPG',
            'category' => 'Utilitas',
            'amount' => 15000,
            'date' => $this->baseDate->copy()->addDay()->toDateString(),
            'description' => 'Isi ulang gas LPG 3kg',
            'payment_method' => 'transfer',
            'created_at' => $this->baseDate->copy()->addDay()->addHours(2),
        ]);
    }

    /**
     * 1 unexpected pengeluaran: 5,000
     */
    private function seedUnexpectedExpense(): void
    {
        UnexpectedTransaction::create([
            'jenis' => 'pengeluaran',
            'nominal' => 5000,
            'deskripsi' => 'Biaya parkir mendadak',
            'created_at' => $this->baseDate->copy()->addHours(4),
        ]);
    }
}
