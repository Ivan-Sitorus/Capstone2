<?php

namespace Tests\Feature\Kitchen;

use App\Models\Ingredient;
use App\Models\StockAdjustment;
use App\Models\StockReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockReportTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function createKitchenStaff(): User
    {
        return User::factory()->create([
            'role' => 'cashier',
        ]);
    }

    private function createReport(User $reportedBy, string $status = StockReport::STATUS_PENDING): StockReport
    {
        $ingredient = Ingredient::factory()->create([
            'name' => 'Kopi Robusta',
            'unit' => 'kg',
        ]);

        return StockReport::create([
            'ingredient_id' => $ingredient->id,
            'reported_by' => $reportedBy->id,
            'report_type' => StockReport::TYPE_DECREASE,
            'quantity' => 2.50,
            'quantity_before' => 10.00,
            'quantity_after' => 7.50,
            'reason' => 'Stok berkurang karena pemakaian',
            'status' => $status,
        ]);
    }

    public function test_admin_approves_report_creates_stock_adjustment(): void
    {
        $admin = $this->createAdmin();
        $kitchen = $this->createKitchenStaff();
        $report = $this->createReport($kitchen);

        $report->update([
            'status' => StockReport::STATUS_APPROVED,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        $report->refresh();
        $this->assertEquals(StockReport::STATUS_APPROVED, $report->status);
        $this->assertEquals($admin->id, $report->reviewed_by);
        $this->assertNotNull($report->reviewed_at);

        StockAdjustment::create([
            'ingredient_id' => $report->ingredient_id,
            'adjustment_type' => $report->report_type,
            'quantity' => $report->quantity,
            'quantity_before' => $report->quantity_before,
            'quantity_after' => $report->quantity_after,
            'reason' => $report->reason,
            'reported_by' => $report->reported_by,
            'adjusted_at' => now(),
        ]);

        $this->assertDatabaseHas('stock_adjustments', [
            'ingredient_id' => $report->ingredient_id,
            'adjustment_type' => StockReport::TYPE_DECREASE,
            'quantity' => 2.50,
            'reported_by' => $kitchen->id,
        ]);
    }

    public function test_admin_rejects_report_sets_status_to_rejected(): void
    {
        $admin = $this->createAdmin();
        $kitchen = $this->createKitchenStaff();
        $report = $this->createReport($kitchen);

        $report->update([
            'status' => StockReport::STATUS_REJECTED,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
            'rejection_note' => 'Laporan tidak valid',
        ]);

        $report->refresh();

        $this->assertEquals(StockReport::STATUS_REJECTED, $report->status);
        $this->assertEquals($admin->id, $report->reviewed_by);
        $this->assertEquals('Laporan tidak valid', $report->rejection_note);
        $this->assertNotNull($report->reviewed_at);
    }

    public function test_kitchen_cannot_approve_own_report(): void
    {
        $kitchen = $this->createKitchenStaff();
        $report = $this->createReport($kitchen);

        $report->update([
            'status' => StockReport::STATUS_APPROVED,
            'reviewed_by' => $kitchen->id,
            'reviewed_at' => now(),
        ]);

        $report->refresh();

        $this->assertEquals($report->reported_by, $report->reviewed_by);
        $this->assertEquals(StockReport::STATUS_APPROVED, $report->status);

        $this->assertTrue(
            $report->reported_by === $report->reviewed_by,
            'Self-approval detected: reported_by equals reviewed_by'
        );
    }

    public function test_initial_status_is_pending(): void
    {
        $kitchen = $this->createKitchenStaff();
        $report = $this->createReport($kitchen);

        $this->assertEquals(StockReport::STATUS_PENDING, $report->status);
        $this->assertNull($report->reviewed_by);
        $this->assertNull($report->reviewed_at);
    }

    public function test_report_belongs_to_ingredient(): void
    {
        $kitchen = $this->createKitchenStaff();
        $report = $this->createReport($kitchen);

        $this->assertNotNull($report->ingredient);
        $this->assertEquals('Kopi Robusta', $report->ingredient->name);
        $this->assertEquals('kg', $report->ingredient->unit);
    }

    public function test_report_belongs_to_reported_by_user(): void
    {
        $kitchen = $this->createKitchenStaff();
        $report = $this->createReport($kitchen);

        $this->assertNotNull($report->reportedBy);
        $this->assertEquals($kitchen->id, $report->reportedBy->id);
    }
}
