<?php

namespace Tests\Feature;

use App\DTO\ReportData;
use App\DTO\ReportRow;
use App\Models\Category;
use App\Models\Expense;
use App\Models\GeneratedReport;
use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\UnexpectedTransaction;
use App\Models\User;
use App\Services\FinancialReportService;
use Carbon\Carbon;
use Database\Seeders\FinancialReportTestDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialReportServiceTest extends TestCase
{
    use RefreshDatabase;

    private FinancialReportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FinancialReportService;
    }

    public function test_generate_simple_report_returns_report_data_dto(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-06 12:00:00'));

        Order::create([
            'order_code' => 'ORD-FS-001',
            'customer_name' => 'Test Customer',
            'total_amount' => 100000,
            'is_paid' => true,
            'payment_method' => 'cash',
            'status' => Order::STATUS_SELESAI,
        ]);

        Order::create([
            'order_code' => 'ORD-FS-002',
            'customer_name' => 'Test Customer 2',
            'total_amount' => 200000,
            'is_paid' => true,
            'payment_method' => 'qris',
            'status' => Order::STATUS_SELESAI,
        ]);

        UnexpectedTransaction::create([
            'jenis' => 'pemasukan',
            'nominal' => 50000,
            'deskripsi' => 'Donation',
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
            'deskripsi' => 'Penalty',
        ]);

        Expense::create([
            'vendor' => 'Supplier Indo',
            'category' => 'inventory',
            'amount' => 75000,
            'date' => now()->toDateString(),
            'description' => 'Restock',
            'payment_method' => 'cash',
        ]);

        $start = now()->subDay()->toDateString();
        $end = now()->addDay()->toDateString();

        $dto = $this->service->generate('simple', [
            'date_start' => $start,
            'date_end' => $end,
        ]);

        $this->assertInstanceOf(ReportData::class, $dto);
        $this->assertSame(ReportData::TYPE_SIMPLE, $dto->type);
        $this->assertSame($start, $dto->dateStart);
        $this->assertSame($end, $dto->dateEnd);
        $this->assertSame('daily', $dto->aggregation);
        $this->assertNotEmpty($dto->rows);
        $this->assertNotEmpty($dto->summary);

        $this->assertEquals(350000.0, $dto->getTotalIncome());
        $this->assertEquals(200000.0, $dto->getTotalExpense());
        $this->assertEquals(150000.0, $dto->getNet());

        $incomeRows = array_filter($dto->rows, fn ($r) => $r->isIncome());
        $expenseRows = array_filter($dto->rows, fn ($r) => $r->isExpense());
        $this->assertNotEmpty($incomeRows);
        $this->assertNotEmpty($expenseRows);

        Carbon::setTestNow();
    }

    public function test_generate_rigid_report_returns_report_data_dto(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-06 12:00:00'));

        Order::create([
            'order_code' => 'ORD-RG-001',
            'customer_name' => 'Rigid Customer',
            'total_amount' => 300000,
            'is_paid' => true,
            'payment_method' => 'cash',
            'status' => Order::STATUS_SELESAI,
        ]);

        UnexpectedTransaction::create([
            'jenis' => 'pemasukan',
            'nominal' => 100000,
            'deskripsi' => 'Service fee',
        ]);

        UnexpectedTransaction::create([
            'jenis' => 'pengeluaran',
            'nominal' => 50000,
            'deskripsi' => 'Maintenance',
        ]);

        $start = now()->startOfMonth()->toDateString();
        $end = now()->toDateString();

        $dto = $this->service->generate('rigid', [
            'date_start' => $start,
            'date_end' => $end,
        ]);

        $this->assertInstanceOf(ReportData::class, $dto);
        $this->assertSame(ReportData::TYPE_RIGID, $dto->type);
        $this->assertSame($start, $dto->dateStart);
        $this->assertSame($end, $dto->dateEnd);
        $this->assertNotEmpty($dto->rows);

        $sectionRows = array_filter($dto->rows, fn ($r) => $r->isSection());
        $this->assertCount(2, $sectionRows);

        $grandTotalRows = array_filter($dto->rows, fn ($r) => $r->isGrandTotal());
        $this->assertNotEmpty($grandTotalRows);

        Carbon::setTestNow();
    }

    public function test_generate_custom_report_returns_report_data_dto(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-06 12:00:00'));

        $category = Category::create([
            'name' => 'Kopi',
            'slug' => 'kopi',
            'is_active' => true,
        ]);

        $menu = Menu::create([
            'category_id' => $category->id,
            'name' => 'Kopi Robusta',
            'slug' => 'kopi-robusta',
            'price' => 15000,
            'is_available' => true,
        ]);

        $order = Order::create([
            'order_code' => 'ORD-CS-001',
            'customer_name' => 'Custom Customer',
            'total_amount' => 45000,
            'is_paid' => true,
            'payment_method' => 'cash',
            'status' => Order::STATUS_SELESAI,
        ]);

        \DB::table('order_items')->insert([
            'order_id' => $order->id,
            'menu_id' => $menu->id,
            'quantity' => 3,
            'unit_price' => 15000,
            'subtotal' => 45000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        UnexpectedTransaction::create([
            'jenis' => 'pengeluaran',
            'nominal' => 20000,
            'deskripsi' => 'Unexpected cost',
        ]);

        $start = now()->startOfMonth()->toDateString();
        $end = now()->endOfMonth()->toDateString();

        $dto = $this->service->generate('custom', [
            'date_start' => $start,
            'date_end' => $end,
            'categories' => ['menu:'.$category->id, 'unexpected_expense'],
            'aggregation' => 'monthly',
        ]);

        $this->assertInstanceOf(ReportData::class, $dto);
        $this->assertSame(ReportData::TYPE_CUSTOM, $dto->type);
        $this->assertSame('monthly', $dto->aggregation);
        $this->assertNotEmpty($dto->rows);
        $this->assertNotEmpty($dto->config);

        $incomeRow = collect($dto->rows)->first(fn ($r) => $r->category === 'Kopi');
        $this->assertNotNull($incomeRow);
        $this->assertSame(ReportRow::TYPE_INCOME, $incomeRow->type);

        $expenseRow = collect($dto->rows)->first(fn ($r) => $r->category === 'Unexpected');
        $this->assertNotNull($expenseRow);
        $this->assertSame(ReportRow::TYPE_EXPENSE, $expenseRow->type);

        Carbon::setTestNow();
    }

    public function test_invalid_type_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown report type: invalid');

        $this->service->generate('invalid');
    }

    public function test_default_date_range_used_when_not_provided(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-06 12:00:00'));

        $dto = $this->service->generate('simple');

        $this->assertInstanceOf(ReportData::class, $dto);
        $this->assertNotEmpty($dto->dateStart);
        $this->assertNotEmpty($dto->dateEnd);

        Carbon::setTestNow();
    }

    public function test_generated_report_model_to_report_data(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'cashier',
        ]);

        $result = [
            'type' => 'simple',
            'title' => 'Test Report 2026-05-01 — 2026-05-06',
            'date_start' => '2026-05-01',
            'date_end' => '2026-05-06',
            'aggregation' => 'daily',
            'rows' => [
                [
                    'date' => '2026-05-01',
                    'category' => 'cash',
                    'type' => 'Income',
                    'amount' => 100000,
                    'running_total' => 100000,
                    'indent_level' => 0,
                    'is_bold' => false,
                ],
            ],
            'summary' => [
                [
                    'label' => 'Total Pendapatan',
                    'formatted_value' => 'Rp 100.000',
                    'raw_value' => 100000,
                    'is_highlighted' => false,
                ],
            ],
        ];

        $report = GeneratedReport::create([
            'user_id' => $user->id,
            'name' => 'Test Report',
            'type' => 'simple',
            'date_start' => '2026-05-01',
            'date_end' => '2026-05-06',
            'aggregation' => 'daily',
            'categories' => [],
            'result' => $result,
        ]);

        $dto = $report->toReportData();

        $this->assertInstanceOf(ReportData::class, $dto);
        $this->assertSame('simple', $dto->type);
        $this->assertSame('2026-05-01', $dto->dateStart);
        $this->assertCount(1, $dto->rows);
        $this->assertCount(1, $dto->summary);
        $this->assertSame('cash', $dto->rows[0]->category);
    }

    // ────────────────────────────────────────────────────────────────────
    // TDD Correctness Tests (Wave 1 — RED phase)
    // ────────────────────────────────────────────────────────────────────

    private function seedTestData(): void
    {
        $this->seed(FinancialReportTestDataSeeder::class);
    }

    private function simpleReportParams(): array
    {
        return [
            'date_start' => '2026-05-01',
            'date_end' => '2026-05-31',
        ];
    }

    public function test_simple_report_total_income_matches_database_sum(): void
    {
        $this->seedTestData();

        $dto = $this->service->generate('simple', $this->simpleReportParams());

        $this->assertEquals(
            1050000.0,
            $dto->getTotalIncome(),
            'Total income must equal 5 orders (1,000,000) + unexpected pemasukan (50,000)'
        );
    }

    public function test_simple_report_total_expense_matches_database_sum(): void
    {
        $this->seedTestData();

        $dto = $this->service->generate('simple', $this->simpleReportParams());

        $this->assertEquals(
            130000.0,
            $dto->getTotalExpense(),
            'Total expense must equal ingredient batches (100,000) + expenses (25,000) + unexpected pengeluaran (5,000)'
        );
    }

    public function test_simple_report_net_equals_income_minus_expense(): void
    {
        $this->seedTestData();

        $dto = $this->service->generate('simple', $this->simpleReportParams());

        $expectedNet = $dto->getTotalIncome() - $dto->getTotalExpense();

        $this->assertEqualsWithDelta(
            920000.0,
            $dto->getNet(),
            0.01,
            'Net must equal income (1,050,000) minus expense (130,000)'
        );
        $this->assertEqualsWithDelta(
            $expectedNet,
            $dto->getNet(),
            0.01,
            'getNet() must be internally consistent with getTotalIncome() - getTotalExpense()'
        );
    }

    public function test_rigid_report_laba_rugi_equation(): void
    {
        $this->seedTestData();

        $dto = $this->service->generate('rigid', $this->simpleReportParams());

        $pendapatan = $this->extractRigidAmount($dto, 'Pendapatan', 0);
        $hpp = $this->extractRigidAmount($dto, 'HPP', 0);
        $bebanOperasional = $this->extractRigidAmount($dto, 'Beban Operasional', 0);
        $bebanTakTerduga = $this->extractRigidAmount($dto, 'Beban Tak Terduga', 0);
        $labaRugiBersih = $this->extractRigidAmount($dto, 'Laba Rugi Bersih', 0);

        $computed = $pendapatan - $hpp - $bebanOperasional - $bebanTakTerduga;

        $this->assertEqualsWithDelta(
            $computed,
            $labaRugiBersih,
            0.01,
            "Laba Rugi Bersih ({$labaRugiBersih}) must equal pendapatan ({$pendapatan}) - hpp ({$hpp}) - beban_operasional ({$bebanOperasional}) - beban_tak_terduga ({$bebanTakTerduga}) = {$computed}"
        );
    }

    /**
     * Helper: extract a named row's amount from the rigid report DTO.
     */
    private function extractRigidAmount(ReportData $dto, string $category, int $indentLevel): float
    {
        foreach ($dto->rows as $row) {
            if ($row->category === $category && $row->indentLevel === $indentLevel) {
                return $row->amount;
            }
        }
        $this->fail("Rigid row not found: category={$category}, indent={$indentLevel}");
    }

    public function test_zero_transaction_period_returns_zero_not_null(): void
    {
        $dto = $this->service->generate('simple', [
            'date_start' => '2000-01-01',
            'date_end' => '2000-01-31',
        ]);

        $this->assertSame(0.0, $dto->getTotalIncome());
        $this->assertSame(0.0, $dto->getTotalExpense());
        $this->assertSame(0.0, $dto->getNet());
    }

    public function test_date_range_boundaries_inclusive(): void
    {
        $dateStart = Carbon::parse('2026-05-01 00:00:00');
        $dateEnd = Carbon::parse('2026-05-31 23:59:59');

        Order::create([
            'order_code' => 'ORD-BND-001',
            'customer_name' => 'Boundary Start',
            'total_amount' => 100000,
            'is_paid' => true,
            'payment_method' => 'cash',
            'status' => Order::STATUS_SELESAI,
            'created_at' => $dateStart,
        ]);

        Order::create([
            'order_code' => 'ORD-BND-002',
            'customer_name' => 'Boundary End',
            'total_amount' => 50000,
            'is_paid' => true,
            'payment_method' => 'cash',
            'status' => Order::STATUS_SELESAI,
            'created_at' => $dateEnd,
        ]);

        $dto = $this->service->generate('simple', [
            'date_start' => $dateStart->toDateString(),
            'date_end' => $dateEnd->toDateString(),
        ]);

        $this->assertNotNull($dto);
        $this->assertEqualsWithDelta(
            150000.0,
            $dto->getTotalIncome(),
            0.01,
            'Both boundary transactions (100000 + 50000) must be included'
        );
    }

    public function test_date_start_after_date_end_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->service->generate('simple', [
            'date_start' => '2026-06-01',
            'date_end' => '2026-05-01',
        ]);
    }

    public function test_custom_report_running_total_consistency(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-06 12:00:00'));

        $category = Category::create([
            'name' => 'Minuman',
            'slug' => 'minuman',
            'is_active' => true,
        ]);

        $menu = Menu::create([
            'category_id' => $category->id,
            'name' => 'Es Teh Manis',
            'slug' => 'es-teh-manis',
            'price' => 8000,
            'is_available' => true,
        ]);

        $order = Order::create([
            'order_code' => 'ORD-RT-001',
            'customer_name' => 'RT Customer',
            'total_amount' => 40000,
            'is_paid' => true,
            'payment_method' => 'cash',
            'status' => Order::STATUS_SELESAI,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'menu_id' => $menu->id,
            'quantity' => 5,
            'unit_price' => 8000,
            'subtotal' => 40000,
        ]);

        UnexpectedTransaction::create([
            'jenis' => 'pemasukan',
            'nominal' => 30000,
            'deskripsi' => 'Tips',
        ]);

        UnexpectedTransaction::create([
            'jenis' => 'pengeluaran',
            'nominal' => 10000,
            'deskripsi' => 'Biaya parkir',
        ]);

        $dto = $this->service->generate('custom', [
            'date_start' => now()->startOfMonth()->toDateString(),
            'date_end' => now()->toDateString(),
            'categories' => ['menu:'.$category->id, 'unexpected_income', 'unexpected_expense'],
            'aggregation' => 'daily',
        ]);

        $this->assertNotEmpty($dto->rows, 'Custom report must have rows for running total test');

        $cumulative = 0.0;
        $rowIndex = 0;
        foreach ($dto->rows as $row) {
            if ($row->isSection() || $row->isGrandTotal()) {
                continue;
            }
            $sign = $row->isIncome() ? 1 : -1;
            $cumulative += $sign * $row->amount;

            $this->assertEqualsWithDelta(
                round($cumulative, 2),
                $row->runningTotal ?? 0.0,
                0.02,
                "Row {$rowIndex} ({$row->category}/{$row->type}): running_total must equal cumulative sum"
            );
            $rowIndex++;
        }

        Carbon::setTestNow();
    }
}
