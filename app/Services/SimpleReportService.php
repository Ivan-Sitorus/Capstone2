<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\IngredientBatch;
use App\Models\Order;
use App\Models\Receivable;
use App\Models\UnexpectedTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SimpleReportService
{
    /**
     * Generate a simple financial report for the given date range.
     *
     * @param string|null $dateStart Start date (inclusive)
     * @param string|null $dateEnd End date (inclusive)
     * @return array Financial report data
     */
    public function generate(?string $dateStart = null, ?string $dateEnd = null): array
    {
        // Parse dates with defaults
        $start = $dateStart ? Carbon::parse($dateStart)->startOfDay() : Carbon::createFromDate(2000, 1, 1)->startOfDay();
        $end = $dateEnd ? Carbon::parse($dateEnd)->endOfDay() : Carbon::now()->endOfDay();

        // Calculate totals using database-level aggregation
        $totalIncome = $this->calculateTotalIncome($start, $end);
        $totalExpense = $this->calculateTotalExpense($start, $end);
        $net = $totalIncome - $totalExpense;

        // Calculate breakdowns
        $incomeBreakdown = $this->calculateIncomeBreakdown($start, $end);
        $expenseBreakdown = $this->calculateExpenseBreakdown($start, $end);

        // Calculate receivables outstanding
        $receivablesOutstanding = $this->calculateReceivablesOutstanding($start, $end);

        return [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net' => $net,
            'income_breakdown' => $incomeBreakdown,
            'expense_breakdown' => $expenseBreakdown,
            'receivables_outstanding' => $receivablesOutstanding,
            'date_range' => [
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
            ],
        ];
    }

    /**
     * Calculate total income from orders and unexpected transactions.
     */
    private function calculateTotalIncome(Carbon $start, Carbon $end): float
    {
        // Income from paid orders
        $fromOrders = (float) Order::where('is_paid', true)
            ->whereBetween('created_at', [$start, $end])
            ->sum('total_amount');

        // Income from unexpected transactions (pemasukan)
        $fromUnexpected = (float) UnexpectedTransaction::where('jenis', 'pemasukan')
            ->whereBetween('created_at', [$start, $end])
            ->sum('nominal');

        return $fromOrders + $fromUnexpected;
    }

    /**
     * Calculate total expense from expenses, ingredient batches, and unexpected transactions.
     */
    private function calculateTotalExpense(Carbon $start, Carbon $end): float
    {
        // Expense from Expense table
        $fromExpenses = (float) Expense::whereBetween('date', [$start, $end])
            ->sum('amount');

        // Expense from ingredient batches (quantity * cost_per_unit)
        $fromBatches = (float) IngredientBatch::whereBetween('received_at', [$start, $end])
            ->selectRaw('SUM(quantity * cost_per_unit) as total')
            ->value('total') ?? 0;

        // Expense from unexpected transactions (pengeluaran)
        $fromUnexpected = (float) UnexpectedTransaction::where('jenis', 'pengeluaran')
            ->whereBetween('created_at', [$start, $end])
            ->sum('nominal');

        return $fromExpenses + $fromBatches + $fromUnexpected;
    }

    /**
     * Calculate income breakdown by source.
     */
    private function calculateIncomeBreakdown(Carbon $start, Carbon $end): array
    {
        // Orders breakdown by payment method
        $ordersByPayment = Order::where('is_paid', true)
            ->whereBetween('created_at', [$start, $end])
            ->select('payment_method', DB::raw('SUM(total_amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get()
            ->map(fn ($item) => [
                'source' => $item->payment_method ?? 'unknown',
                'total' => (float) $item->total,
                'count' => (int) $item->count,
            ])
            ->toArray();

        // Unexpected transactions (pemasukan)
        $unexpectedIncome = (float) UnexpectedTransaction::where('jenis', 'pemasukan')
            ->whereBetween('created_at', [$start, $end])
            ->sum('nominal');

        $breakdown = array_merge($ordersByPayment, [
            [
                'source' => 'unexpected_income',
                'total' => $unexpectedIncome,
                'count' => UnexpectedTransaction::where('jenis', 'pemasukan')
                    ->whereBetween('created_at', [$start, $end])
                    ->count(),
            ],
        ]);

        return $breakdown;
    }

    /**
     * Calculate expense breakdown by source.
     */
    private function calculateExpenseBreakdown(Carbon $start, Carbon $end): array
    {
        // Expenses by category
        $expensesByCategory = Expense::whereBetween('date', [$start, $end])
            ->select('category', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->get()
            ->map(fn ($item) => [
                'source' => $item->category ?? 'uncategorized',
                'total' => (float) $item->total,
                'count' => (int) $item->count,
            ])
            ->toArray();

        // Ingredient batch costs
        $ingredientCosts = (float) IngredientBatch::whereBetween('received_at', [$start, $end])
            ->selectRaw('SUM(quantity * cost_per_unit) as total')
            ->value('total') ?? 0;

        $ingredientCount = IngredientBatch::whereBetween('received_at', [$start, $end])
            ->count();

        // Unexpected transactions (pengeluaran)
        $unexpectedExpense = (float) UnexpectedTransaction::where('jenis', 'pengeluaran')
            ->whereBetween('created_at', [$start, $end])
            ->sum('nominal');

        $unexpectedCount = UnexpectedTransaction::where('jenis', 'pengeluaran')
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $breakdown = array_merge($expensesByCategory, [
            [
                'source' => 'ingredient_purchase',
                'total' => $ingredientCosts,
                'count' => $ingredientCount,
            ],
            [
                'source' => 'unexpected_expense',
                'total' => $unexpectedExpense,
                'count' => $unexpectedCount,
            ],
        ]);

        return $breakdown;
    }

    /**
     * Calculate receivables outstanding (unpaid or partially paid).
     * Uses invoice_date for filtering as per admin's choice.
     */
    private function calculateReceivablesOutstanding(Carbon $start, Carbon $end): float
    {
        // Get receivables with status != 'paid' and invoice_date within range
        $receivables = Receivable::where('status', '!=', Receivable::STATUS_PAID)
            ->whereBetween('invoice_date', [$start, $end])
            ->get();

        // Calculate remaining amount (amount - paid_amount)
        return (float) $receivables->sum(function ($receivable) {
            return max(0, (float) $receivable->amount - (float) $receivable->paid_amount);
        });
    }
}