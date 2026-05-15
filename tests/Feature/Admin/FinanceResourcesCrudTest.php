<?php

namespace Tests\Feature\Admin;

use App\Models\Expense;
use App\Models\Receivable;
use App\Models\UnexpectedTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceResourcesCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_income_can_be_created_and_updated(): void
    {
        $transaction = UnexpectedTransaction::create([
            'jenis' => 'pemasukan',
            'nominal' => 250000,
            'deskripsi' => 'Daily summary',
        ]);

        $this->assertDatabaseHas('unexpected_transactions', [
            'id' => $transaction->id,
            'jenis' => 'pemasukan',
        ]);

        $transaction->update([
            'nominal' => 275000,
        ]);

        $this->assertDatabaseHas('unexpected_transactions', [
            'id' => $transaction->id,
            'nominal' => 275000,
        ]);
    }

    public function test_expense_can_be_created_and_updated(): void
    {
        $expense = Expense::create([
            'vendor' => 'PT Supplier',
            'category' => 'inventory',
            'amount' => 120000,
            'date' => now()->toDateString(),
            'description' => 'Milk and beans',
            'payment_method' => 'cash',
        ]);

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'vendor' => 'PT Supplier',
        ]);

        $expense->update([
            'payment_method' => 'bank_transfer',
        ]);

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'payment_method' => 'bank_transfer',
        ]);
    }

    public function test_receivable_remaining_amount_and_overdue_logic(): void
    {
        $receivable = Receivable::create([
            'customer_name' => 'Customer A',
            'amount' => 500000,
            'invoice_date' => now()->subDays(10)->toDateString(),
            'due_date' => now()->subDay()->toDateString(),
            'status' => Receivable::STATUS_PENDING,
            'paid_amount' => 150000,
            'notes' => 'Manual invoice',
        ]);

        $this->assertSame(350000.0, (float) $receivable->remaining_amount);
        $this->assertTrue($receivable->isOverdue());

        $receivable->update([
            'status' => Receivable::STATUS_PAID,
            'paid_amount' => 500000,
        ]);

        $receivable->refresh();

        $this->assertSame(0.0, (float) $receivable->remaining_amount);
        $this->assertFalse($receivable->isOverdue());
    }
}
