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
use App\Models\Receivable;
use App\Models\UnexpectedTransaction;
use App\Models\User;
use App\Services\FinancialReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialReportServiceTest extends TestCase
{
    use RefreshDatabase;

    private FinancialReportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FinancialReportService();
    }

    public function test_generate_simple_report_returns_report_data_dto(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-06 12:00:00'));

        Order::create([
            'order_code'    => 'ORD-FS-001',
            'customer_name' => 'Test Customer',
            'total_amount'  => 100000,
            'is_paid'       => true,
            'payment_method' => 'cash',
            'status'        => Order::STATUS_SELESAI,
        ]);

        Order::create([
            'order_code'    => 'ORD-FS-002',
            'customer_name' => 'Test Customer 2',
            'total_amount'  => 200000,
            'is_paid'       => true,
            'payment_method' => 'qris',
            'status'        => Order::STATUS_SELESAI,
        ]);

        UnexpectedTransaction::create([
            'jenis'       => 'pemasukan',
            'nominal'     => 50000,
            'description' => 'Donation',
        ]);

        $ingredient = Ingredient::create([
            'name'               => 'Coffee Beans',
            'unit'               => 'kg',
            'low_stock_threshold' => 1,
            'is_active'          => true,
        ]);

        IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity'      => 5,
            'cost_per_unit' => 20000,
            'received_at'   => now(),
            'expiry_date'   => now()->addYear(),
        ]);

        UnexpectedTransaction::create([
            'jenis'       => 'pengeluaran',
            'nominal'     => 25000,
            'description' => 'Penalty',
        ]);

        Expense::create([
            'vendor'         => 'Supplier Indo',
            'category'       => 'inventory',
            'amount'         => 75000,
            'date'           => now()->toDateString(),
            'description'    => 'Restock',
            'payment_method' => 'cash',
        ]);

        $start = now()->subDay()->toDateString();
        $end   = now()->addDay()->toDateString();

        $dto = $this->service->generate('simple', [
            'date_start' => $start,
            'date_end'   => $end,
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
            'order_code'    => 'ORD-RG-001',
            'customer_name' => 'Rigid Customer',
            'total_amount'  => 300000,
            'is_paid'       => true,
            'payment_method' => 'cash',
            'status'        => Order::STATUS_SELESAI,
        ]);

        UnexpectedTransaction::create([
            'jenis'       => 'pemasukan',
            'nominal'     => 100000,
            'description' => 'Service fee',
        ]);

        UnexpectedTransaction::create([
            'jenis'       => 'pengeluaran',
            'nominal'     => 50000,
            'description' => 'Maintenance',
        ]);

        $start = now()->startOfMonth()->toDateString();
        $end   = now()->toDateString();

        $dto = $this->service->generate('rigid', [
            'date_start' => $start,
            'date_end'   => $end,
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
            'name'      => 'Kopi',
            'slug'      => 'kopi',
            'is_active' => true,
        ]);

        $menu = Menu::create([
            'category_id' => $category->id,
            'name'        => 'Kopi Robusta',
            'slug'        => 'kopi-robusta',
            'price'       => 15000,
            'is_available' => true,
        ]);

        $order = Order::create([
            'order_code'    => 'ORD-CS-001',
            'customer_name' => 'Custom Customer',
            'total_amount'  => 45000,
            'is_paid'       => true,
            'payment_method' => 'cash',
            'status'        => Order::STATUS_SELESAI,
        ]);

        \DB::table('order_items')->insert([
            'order_id'   => $order->id,
            'menu_id'    => $menu->id,
            'quantity'   => 3,
            'unit_price' => 15000,
            'subtotal'   => 45000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        UnexpectedTransaction::create([
            'jenis'       => 'pengeluaran',
            'nominal'     => 20000,
            'description' => 'Unexpected cost',
        ]);

        $start = now()->startOfMonth()->toDateString();
        $end   = now()->endOfMonth()->toDateString();

        $dto = $this->service->generate('custom', [
            'date_start'  => $start,
            'date_end'    => $end,
            'categories'  => ['menu:' . $category->id, 'unexpected_expense'],
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
            'name'     => 'Test User',
            'email'    => 'test@example.com',
            'password' => bcrypt('password'),
            'role'     => 'cashier',
        ]);

        $result = [
            'type'        => 'simple',
            'title'       => 'Test Report 2026-05-01 — 2026-05-06',
            'date_start'  => '2026-05-01',
            'date_end'    => '2026-05-06',
            'aggregation' => 'daily',
            'rows'        => [
                [
                    'date'          => '2026-05-01',
                    'category'      => 'cash',
                    'type'          => 'Income',
                    'amount'        => 100000,
                    'running_total' => 100000,
                    'indent_level'  => 0,
                    'is_bold'       => false,
                ],
            ],
            'summary' => [
                [
                    'label'          => 'Total Pendapatan',
                    'formatted_value' => 'Rp 100.000',
                    'raw_value'      => 100000,
                    'is_highlighted' => false,
                ],
            ],
        ];

        $report = GeneratedReport::create([
            'user_id'     => $user->id,
            'name'        => 'Test Report',
            'type'        => 'simple',
            'date_start'  => '2026-05-01',
            'date_end'    => '2026-05-06',
            'aggregation' => 'daily',
            'categories'  => [],
            'result'      => $result,
        ]);

        $dto = $report->toReportData();

        $this->assertInstanceOf(ReportData::class, $dto);
        $this->assertSame('simple', $dto->type);
        $this->assertSame('2026-05-01', $dto->dateStart);
        $this->assertCount(1, $dto->rows);
        $this->assertCount(1, $dto->summary);
        $this->assertSame('cash', $dto->rows[0]->category);
    }
}
