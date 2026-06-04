<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\Menu;
use App\Models\MenuIngredient;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderSyncDeltaReconciliationTest extends TestCase
{
    use RefreshDatabase;

    private User $cashier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cashier = User::factory()->create(['role' => 'cashier']);
    }

    public function test_skip_mode_distributes_across_batches_fefo(): void
    {
        $category = Category::create(['name' => 'Minuman', 'slug' => 'minuman-' . uniqid(), 'is_active' => true]);

        $menu = Menu::create([
            'category_id'  => $category->id,
            'name'         => 'Kopi FEFO Test',
            'slug'         => 'kopi-fefo-test-' . uniqid(),
            'price'        => 10000,
            'is_available' => true,
        ]);

        $ingredient = Ingredient::create([
            'name'               => 'Kopi Bubuk FEFO',
            'unit'               => 'gram',
            'low_stock_threshold' => 5,
            'is_active'          => true,
            'batch_mode'         => Ingredient::BATCH_MODE_FEFO,
        ]);

        $batch1 = IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity'      => 50.00,
            'expiry_date'   => '2025-01-15',
            'received_at'   => '2025-01-01',
            'cost_per_unit' => 100,
        ]);
        $batch2 = IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity'      => 50.00,
            'expiry_date'   => '2025-06-15',
            'received_at'   => '2025-03-01',
            'cost_per_unit' => 100,
        ]);
        $batch3 = IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity'      => 50.00,
            'expiry_date'   => '2025-12-15',
            'received_at'   => '2025-06-01',
            'cost_per_unit' => 100,
        ]);

        MenuIngredient::create([
            'menu_id'       => $menu->id,
            'ingredient_id'  => $ingredient->id,
            'quantity_used' => 10.00,
        ]);

        $uuid = Str::uuid()->toString();

        $response = $this->actingAs($this->cashier, 'web')
            ->postJson('/api/sync-orders', [
                'orders' => [[
                    'uuid'          => $uuid,
                    'items'         => [
                        ['menu_id' => $menu->id, 'quantity' => 12, 'price' => 10000],
                    ],
                    'paymentMethod' => 'cash',
                    'total'         => 120000,
                ]],
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('summary.synced', 1);

        $this->assertSame(0.0,  (float) $batch1->fresh()->quantity);
        $this->assertSame(0.0,  (float) $batch2->fresh()->quantity);
        $this->assertSame(30.0, (float) $batch3->fresh()->quantity);
    }

    public function test_skip_mode_allows_negative_with_multiple_batches(): void
    {
        $category = Category::create(['name' => 'Snack', 'slug' => 'snack-' . uniqid(), 'is_active' => true]);

        $menu = Menu::create([
            'category_id'  => $category->id,
            'name'         => 'Roti Oversell Test',
            'slug'         => 'roti-oversell-' . uniqid(),
            'price'        => 15000,
            'is_available' => true,
        ]);

        $ingredient = Ingredient::create([
            'name'               => 'Tepung Oversell',
            'unit'               => 'gram',
            'low_stock_threshold' => 10,
            'is_active'          => true,
            'batch_mode'         => Ingredient::BATCH_MODE_FEFO,
        ]);

        $batch1 = IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity'      => 20.00,
            'expiry_date'   => '2025-01-10',
            'received_at'   => '2025-01-01',
            'cost_per_unit' => 50,
        ]);
        $batch2 = IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity'      => 20.00,
            'expiry_date'   => '2025-03-10',
            'received_at'   => '2025-02-01',
            'cost_per_unit' => 50,
        ]);
        $batch3 = IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity'      => 20.00,
            'expiry_date'   => '2025-06-10',
            'received_at'   => '2025-04-01',
            'cost_per_unit' => 50,
        ]);

        MenuIngredient::create([
            'menu_id'       => $menu->id,
            'ingredient_id'  => $ingredient->id,
            'quantity_used' => 20.00,
        ]);

        $uuid = Str::uuid()->toString();

        $response = $this->actingAs($this->cashier, 'web')
            ->postJson('/api/sync-orders', [
                'orders' => [[
                    'uuid'          => $uuid,
                    'items'         => [
                        ['menu_id' => $menu->id, 'quantity' => 8, 'price' => 15000],
                    ],
                    'paymentMethod' => 'cash',
                    'total'         => 120000,
                ]],
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('summary.synced', 1);
        $this->assertDatabaseHas('orders', ['uuid' => $uuid]);

        $this->assertSame(0.0,   (float) $batch1->fresh()->quantity);
        $this->assertSame(0.0,   (float) $batch2->fresh()->quantity);
        $this->assertSame(-100.0, (float) $batch3->fresh()->quantity);
    }

    public function test_reconciliation_audit_trail_recorded(): void
    {
        $category = Category::create(['name' => 'Audit', 'slug' => 'audit-' . uniqid(), 'is_active' => true]);

        $menu = Menu::create([
            'category_id'  => $category->id,
            'name'         => 'Teh Audit Test',
            'slug'         => 'teh-audit-' . uniqid(),
            'price'        => 5000,
            'is_available' => true,
        ]);

        $ingredient = Ingredient::create([
            'name'               => 'Teh Celup Audit',
            'unit'               => 'pcs',
            'low_stock_threshold' => 5,
            'is_active'          => true,
        ]);

        IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity'      => 100.00,
            'expiry_date'   => '2026-12-31',
            'received_at'   => now(),
            'cost_per_unit' => 10,
        ]);

        MenuIngredient::create([
            'menu_id'       => $menu->id,
            'ingredient_id'  => $ingredient->id,
            'quantity_used' => 1.00,
        ]);

        $uuid = Str::uuid()->toString();

        $this->actingAs($this->cashier, 'web')
            ->postJson('/api/sync-orders', [
                'orders' => [[
                    'uuid'          => $uuid,
                    'items'         => [
                        ['menu_id' => $menu->id, 'quantity' => 3, 'price' => 5000],
                    ],
                    'paymentMethod' => 'cash',
                    'total'         => 15000,
                ]],
            ])
            ->assertStatus(200);

        $movementCount = StockMovement::where('notes', 'like', '%reconciliation_type: offline_sync%')->count();

        $this->assertGreaterThanOrEqual(1, $movementCount);
    }

    public function test_stock_before_and_after_match_delta(): void
    {
        $category = Category::create(['name' => 'Delta', 'slug' => 'delta-' . uniqid(), 'is_active' => true]);

        $menu = Menu::create([
            'category_id'  => $category->id,
            'name'         => 'Matcha Delta Test',
            'slug'         => 'matcha-delta-' . uniqid(),
            'price'        => 20000,
            'is_available' => true,
        ]);

        $ingredient = Ingredient::create([
            'name'               => 'Matcha Bubuk Delta',
            'unit'               => 'gram',
            'low_stock_threshold' => 10,
            'is_active'          => true,
        ]);

        IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity'      => 200.00,
            'expiry_date'   => '2026-06-01',
            'received_at'   => now(),
            'cost_per_unit' => 200,
        ]);

        MenuIngredient::create([
            'menu_id'       => $menu->id,
            'ingredient_id'  => $ingredient->id,
            'quantity_used' => 15.00,
        ]);

        $quantityOrdered = 5;
        $perServingUsage = 15.00;
        $totalUsage      = $quantityOrdered * $perServingUsage;

        $uuid = Str::uuid()->toString();

        $response = $this->actingAs($this->cashier, 'web')
            ->postJson('/api/sync-orders', [
                'orders' => [[
                    'uuid'          => $uuid,
                    'items'         => [
                        ['menu_id' => $menu->id, 'quantity' => $quantityOrdered, 'price' => 20000],
                    ],
                    'paymentMethod' => 'cash',
                    'total'         => 100000,
                ]],
            ]);

        $response->assertStatus(200);

        $reconciliation = $response->json('synced.0.reconciliation');

        $this->assertNotNull($reconciliation);
        $this->assertNotEmpty($reconciliation);

        $entry = $reconciliation[0];
        $this->assertArrayHasKey('ingredient_id', $entry);
        $this->assertArrayHasKey('before', $entry);
        $this->assertArrayHasKey('after', $entry);
        $this->assertArrayHasKey('delta', $entry);

        $this->assertSame((float) round($entry['after'] - $entry['before'], 2), (float) $entry['delta']);

        $stockDecrease = (float) $entry['before'] - (float) $entry['after'];
        $this->assertSame($totalUsage, $stockDecrease);

        $remaining = (float) IngredientBatch::where('ingredient_id', $ingredient->id)->sum('quantity');
        $this->assertSame(125.0, $remaining);
    }

    public function test_admin_stock_change_during_offline_reconciled(): void
    {
        $category = Category::create(['name' => 'AdminEdit', 'slug' => 'adminedit-' . uniqid(), 'is_active' => true]);

        $menu = Menu::create([
            'category_id'  => $category->id,
            'name'         => 'Susu Admin Edit Test',
            'slug'         => 'susu-admin-edit-' . uniqid(),
            'price'        => 8000,
            'is_available' => true,
        ]);

        $ingredient = Ingredient::create([
            'name'               => 'Susu Cair Admin',
            'unit'               => 'ml',
            'low_stock_threshold' => 100,
            'is_active'          => true,
        ]);

        $batch = IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity'      => 50.00,
            'expiry_date'   => '2026-01-01',
            'received_at'   => now(),
            'cost_per_unit' => 30,
        ]);

        MenuIngredient::create([
            'menu_id'       => $menu->id,
            'ingredient_id'  => $ingredient->id,
            'quantity_used' => 10.00,
        ]);

        $batch->quantity = 100.00;
        $batch->save();

        $uuid = Str::uuid()->toString();

        $response = $this->actingAs($this->cashier, 'web')
            ->postJson('/api/sync-orders', [
                'orders' => [[
                    'uuid'          => $uuid,
                    'items'         => [
                        ['menu_id' => $menu->id, 'quantity' => 3, 'price' => 8000],
                    ],
                    'paymentMethod' => 'cash',
                    'total'         => 24000,
                ]],
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('summary.synced', 1);

        $finalStock = (float) IngredientBatch::where('ingredient_id', $ingredient->id)->sum('quantity');
        $this->assertSame(70.0, $finalStock);

        $reconciliation = $response->json('synced.0.reconciliation');
        $this->assertNotNull($reconciliation);
        $entry = $reconciliation[0];

        $this->assertSame(100.0, (float) $entry['before']);
        $this->assertSame(70.0,  (float) $entry['after']);
        $this->assertSame(-30.0, (float) $entry['delta']);
    }
}
