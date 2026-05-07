<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\Order;
use App\Models\Receivable;
use App\Models\UnexpectedTransaction;
use App\Services\SimpleReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FinanceModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_receivable_has_order_fk(): void
    {
        $order = Order::create([
            'order_code' => 'ORD-FK-001',
            'customer_name' => 'Cust FK',
            'total_amount' => 50000,
            'is_paid' => false,
            'payment_method' => 'cash',
            'status' => Order::STATUS_PENDING,
        ]);

        $receivable = Receivable::create([
            'customer_name' => 'Cust FK',
            'amount' => 50000,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'status' => Receivable::STATUS_PENDING,
            'paid_amount' => 0,
            'order_id' => $order->id,
        ]);

        $this->assertDatabaseHas('receivables', [
            'id' => $receivable->id,
            'order_id' => $order->id,
        ]);

        $receivable->refresh();
        $this->assertNotNull($receivable->order);
        $this->assertSame($order->id, $receivable->order->id);
        $this->assertSame($order->order_code, $receivable->order->order_code);
    }

    public function test_bayar_nanti_auto_creates_receivable(): void
    {
        $order = Order::create([
            'order_code' => 'ORD-BN-001',
            'customer_name' => 'Bayar Nanti Cust',
            'total_amount' => 75000,
            'is_paid' => false,
            'payment_method' => 'bayar_nanti',
            'status' => Order::STATUS_PENDING,
        ]);

        $this->assertDatabaseHas('receivables', [
            'order_id' => $order->id,
            'amount' => 75000,
            'paid_amount' => 0,
            'status' => Receivable::STATUS_PENDING,
        ]);

        $receivable = Receivable::where('order_id', $order->id)->first();
        $this->assertNotNull($receivable);
        $this->assertSame('Bayar Nanti Cust', $receivable->customer_name);
        $this->assertSame(75000.0, (float) $receivable->amount);
        $this->assertSame(0.0, (float) $receivable->paid_amount);
        $this->assertSame(now()->format('Y-m-d'), $receivable->invoice_date->format('Y-m-d'));
        $this->assertSame(now()->addDays(30)->format('Y-m-d'), $receivable->due_date->format('Y-m-d'));
        $this->assertStringContainsString($order->order_code, $receivable->notes);
    }

    public function test_bayar_nanti_does_not_create_receivable_for_cash(): void
    {
        $order = Order::create([
            'order_code' => 'ORD-CASH-001',
            'customer_name' => 'Cash Cust',
            'total_amount' => 25000,
            'is_paid' => true,
            'payment_method' => 'cash',
            'status' => Order::STATUS_DIPROSES,
        ]);

        $this->assertNull(Receivable::where('order_id', $order->id)->first());
    }

    public function test_receivable_payment_tracking(): void
    {
        $receivable = Receivable::create([
            'customer_name' => 'Cust Payment',
            'amount' => 100000,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'status' => Receivable::STATUS_PENDING,
            'paid_amount' => 0,
        ]);

        $receivable->recordPayment(40000);
        $receivable->refresh();

        $this->assertSame(Receivable::STATUS_PARTIAL, $receivable->status);
        $this->assertSame(40000.0, (float) $receivable->paid_amount);
        $this->assertSame(60000.0, (float) $receivable->remaining_amount);

        $receivable->recordPayment(60000);
        $receivable->refresh();

        $this->assertSame(Receivable::STATUS_PAID, $receivable->status);
        $this->assertSame(100000.0, (float) $receivable->paid_amount);
        $this->assertSame(0.0, (float) $receivable->remaining_amount);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('already fully paid');
        $receivable->recordPayment(1000);
    }

    public function test_receivable_overdue_detection(): void
    {
        $pastDue = Receivable::create([
            'customer_name' => 'Overdue Cust',
            'amount' => 50000,
            'invoice_date' => now()->subDays(60)->toDateString(),
            'due_date' => now()->subDay()->toDateString(),
            'status' => Receivable::STATUS_PENDING,
            'paid_amount' => 0,
        ]);

        $this->assertTrue($pastDue->isOverdue());

        $pastDue->recordPayment(50000);
        $pastDue->refresh();
        $this->assertFalse($pastDue->isOverdue());

        $stillOverdue = Receivable::create([
            'customer_name' => 'Still Overdue',
            'amount' => 30000,
            'invoice_date' => now()->subDays(30)->toDateString(),
            'due_date' => now()->subDay()->toDateString(),
            'status' => Receivable::STATUS_PENDING,
            'paid_amount' => 0,
        ]);

        $overdueIds = Receivable::overdue()->pluck('id')->toArray();
        $this->assertContains($stillOverdue->id, $overdueIds);
        $this->assertNotContains($pastDue->id, $overdueIds);
    }

    public function test_simple_report_matches_raw_query(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-06 12:00:00'));

        Order::create([
            'order_code' => 'ORD-RPT-001',
            'customer_name' => 'Rpt Cash',
            'total_amount' => 100000,
            'is_paid' => true,
            'payment_method' => 'cash',
            'status' => Order::STATUS_SELESAI,
        ]);

        Order::create([
            'order_code' => 'ORD-RPT-002',
            'customer_name' => 'Rpt QRIS',
            'total_amount' => 200000,
            'is_paid' => true,
            'payment_method' => 'qris',
            'status' => Order::STATUS_SELESAI,
        ]);

        Order::create([
            'order_code' => 'ORD-RPT-003',
            'customer_name' => 'Rpt BN',
            'total_amount' => 50000,
            'is_paid' => false,
            'payment_method' => 'bayar_nanti',
            'status' => Order::STATUS_PENDING,
        ]);

        UnexpectedTransaction::create([
            'jenis' => 'pemasukan',
            'nominal' => 50000,
            'description' => 'Donation',
        ]);

        Expense::create([
            'vendor' => 'Supplier',
            'category' => 'inventory',
            'amount' => 75000,
            'date' => now()->toDateString(),
            'description' => 'Restock',
            'payment_method' => 'cash',
        ]);

        $ingredient = Ingredient::create([
            'name' => 'Coffee Beans',
            'unit' => 'kg',
            'low_stock_threshold' => 1,
            'is_active' => true,
        ]);

        IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 5,
            'cost_per_unit' => 20000,
            'received_at' => now(),
            'expiry_date' => now()->addYear(),
        ]);

        UnexpectedTransaction::create([
            'jenis' => 'pengeluaran',
            'nominal' => 25000,
            'description' => 'Penalty',
        ]);

        $start = now()->subDay()->startOfDay()->toDateString();
        $end = now()->addDay()->endOfDay()->toDateString();

        $report = (new SimpleReportService())->generate($start, $end);

        $rawOrderIncome = (float) DB::table('orders')
            ->where('is_paid', true)
            ->whereBetween('created_at', [now()->subDay()->startOfDay(), now()->addDay()->endOfDay()])
            ->sum('total_amount');

        $rawUnexpectedIncome = (float) DB::table('unexpected_transactions')
            ->where('jenis', 'pemasukan')
            ->whereBetween('created_at', [now()->subDay()->startOfDay(), now()->addDay()->endOfDay()])
            ->sum('nominal');

        $rawTotalIncome = $rawOrderIncome + $rawUnexpectedIncome;

        $this->assertSame($rawTotalIncome, $report['total_income']);
        $this->assertSame(350000.0, $report['total_income']);

        $rawExpenseAmount = (float) DB::table('expenses')
            ->whereBetween('date', [now()->subDay()->startOfDay(), now()->addDay()->endOfDay()])
            ->sum('amount');

        $rawBatchCost = (float) DB::table('ingredient_batches')
            ->whereBetween('received_at', [now()->subDay(), now()->addDay()->endOfDay()])
            ->selectRaw('COALESCE(SUM(quantity * cost_per_unit), 0) as total')
            ->value('total');

        $rawUnexpectedExpense = (float) DB::table('unexpected_transactions')
            ->where('jenis', 'pengeluaran')
            ->whereBetween('created_at', [now()->subDay()->startOfDay(), now()->addDay()->endOfDay()])
            ->sum('nominal');

        $rawTotalExpense = $rawExpenseAmount + $rawBatchCost + $rawUnexpectedExpense;

        $this->assertSame($rawTotalExpense, $report['total_expense']);
        $this->assertSame(200000.0, $report['total_expense']);

        $this->assertSame($rawTotalIncome - $rawTotalExpense, $report['net']);

        $this->assertCount(3, $report['income_breakdown']);

        $cashEntry = collect($report['income_breakdown'])->firstWhere('source', 'cash');
        $this->assertNotNull($cashEntry);
        $this->assertSame(100000.0, (float) $cashEntry['total']);
        $this->assertSame(1, $cashEntry['count']);

        $qrisEntry = collect($report['income_breakdown'])->firstWhere('source', 'qris');
        $this->assertNotNull($qrisEntry);
        $this->assertSame(200000.0, (float) $qrisEntry['total']);
        $this->assertSame(1, $qrisEntry['count']);

        $unexpectedIncomeEntry = collect($report['income_breakdown'])->firstWhere('source', 'unexpected_income');
        $this->assertNotNull($unexpectedIncomeEntry);
        $this->assertSame(50000.0, (float) $unexpectedIncomeEntry['total']);
        $this->assertSame(1, $unexpectedIncomeEntry['count']);

        $this->assertCount(3, $report['expense_breakdown']);

        $expenseInventory = collect($report['expense_breakdown'])->firstWhere('source', 'inventory');
        $this->assertNotNull($expenseInventory);
        $this->assertSame(75000.0, (float) $expenseInventory['total']);

        $expenseIngredient = collect($report['expense_breakdown'])->firstWhere('source', 'ingredient_purchase');
        $this->assertNotNull($expenseIngredient);
        $this->assertSame(100000.0, (float) $expenseIngredient['total']);

        $expenseUnexpected = collect($report['expense_breakdown'])->firstWhere('source', 'unexpected_expense');
        $this->assertNotNull($expenseUnexpected);
        $this->assertSame(25000.0, (float) $expenseUnexpected['total']);

        $this->assertSame(50000.0, $report['receivables_outstanding']);

        $this->assertSame($start, $report['date_range']['start']);
        $this->assertSame($end, $report['date_range']['end']);

        Carbon::setTestNow();
    }
}
