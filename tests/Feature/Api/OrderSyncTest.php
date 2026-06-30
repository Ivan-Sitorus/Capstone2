<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\Menu;
use App\Models\MenuIngredient;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderSyncTest extends TestCase
{
    use RefreshDatabase;

    private User $cashier;
    private Menu $menu;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cashier = User::factory()->create(['role' => 'cashier']);
        $this->menu = Menu::factory()->create();
    }

    /** @return array<string, mixed> */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'orders' => [
                [
                    'uuid'          => Str::uuid()->toString(),
                    'items'         => [
                        ['menu_id' => $this->menu->id, 'quantity' => 2, 'price' => 15000],
                    ],
                    'paymentMethod' => 'cash',
                    'customerName'  => 'Test Customer',
                    'total'         => 30000,
                ],
            ],
        ], $overrides);
    }

    // ─────────────────────────────────────────────────────
    //  1. Single order sync → 200, order exists
    // ─────────────────────────────────────────────────────
    public function test_sync_single_order_successfully(): void
    {
        $uuid  = Str::uuid()->toString();
        $payload = $this->validPayload(['orders' => [
            array_merge($this->validPayload()['orders'][0], ['uuid' => $uuid]),
        ]]);

        $response = $this->actingAs($this->cashier, 'web')
            ->postJson('/api/sync-orders', $payload);

        $response->assertStatus(200);
        $response->assertJsonPath('summary.synced', 1);
        $response->assertJsonPath('summary.failed', 0);
        $response->assertJsonPath('synced.0.localUuid', $uuid);

        $this->assertDatabaseHas('orders', ['uuid' => $uuid]);
    }

    // ─────────────────────────────────────────────────────
    //  2. Correct status: selesai + payment_method=cash
    // ─────────────────────────────────────────────────────
    public function test_sync_order_creates_correct_status(): void
    {
        $uuid    = Str::uuid()->toString();
        $payload = $this->validPayload(['orders' => [
            array_merge($this->validPayload()['orders'][0], ['uuid' => $uuid]),
        ]]);

        $this->actingAs($this->cashier, 'web')
            ->postJson('/api/sync-orders', $payload)
            ->assertStatus(200);

        $order = Order::where('uuid', $uuid)->firstOrFail();

        $this->assertEquals(Order::STATUS_SELESAI, $order->status);
        $this->assertEquals('cash', $order->payment_method);
    }

    // ─────────────────────────────────────────────────────
    //  3. Stock deduction – ingredient quantity decreased
    // ─────────────────────────────────────────────────────
    public function test_sync_order_deducts_stock(): void
    {
        // Create ingredient with stock
        $ingredient = Ingredient::factory()->create(['unit' => 'gram']);
        $batch = IngredientBatch::factory()->create([
            'ingredient_id' => $ingredient->id,
            'quantity'      => 100.00,
        ]);

        // Link menu to ingredient: 5 g per serving
        $menu = Menu::factory()->create();
        MenuIngredient::create([
            'menu_id'       => $menu->id,
            'ingredient_id' => $ingredient->id,
            'quantity_used' => 5.00,
        ]);

        $uuid = Str::uuid()->toString();
        $payload = [
            'orders' => [[
                'uuid'          => $uuid,
                'items'         => [
                    ['menu_id' => $menu->id, 'quantity' => 3, 'price' => 15000],
                ],
                'paymentMethod' => 'cash',
                'customerName'  => 'Stock Test',
                'total'         => 45000,
            ]],
        ];

        $this->actingAs($this->cashier, 'web')
            ->postJson('/api/sync-orders', $payload)
            ->assertStatus(200);

        // 3 items × 5g = 15g deducted → 100 − 15 = 85
        $remaining = (float) IngredientBatch::where('ingredient_id', $ingredient->id)->sum('quantity');
        $this->assertEquals(85.00, $remaining);
    }

    // ─────────────────────────────────────────────────────
    //  4. Negative stock — order > stock, still succeeds
    // ─────────────────────────────────────────────────────
    public function test_sync_order_allows_negative_stock(): void
    {
        $ingredient = Ingredient::factory()->create(['unit' => 'gram']);
        IngredientBatch::factory()->create([
            'ingredient_id' => $ingredient->id,
            'quantity'      => 10.00,
        ]);

        $menu = Menu::factory()->create();
        MenuIngredient::create([
            'menu_id'       => $menu->id,
            'ingredient_id' => $ingredient->id,
            'quantity_used' => 5.00,
        ]);

        // 4 items × 5g = 20g needed, only 10g available
        $uuid = Str::uuid()->toString();
        $payload = [
            'orders' => [[
                'uuid'          => $uuid,
                'items'         => [
                    ['menu_id' => $menu->id, 'quantity' => 4, 'price' => 15000],
                ],
                'paymentMethod' => 'cash',
                'customerName'  => 'Over-stock Test',
                'total'         => 60000,
            ]],
        ];

        // The controller catches InventoryService exception → order still created
        $response = $this->actingAs($this->cashier, 'web')
            ->postJson('/api/sync-orders', $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('orders', ['uuid' => $uuid]);

        // Stock was NOT deducted because processSaleForOrder threw (no skipStockValidation)
        $remaining = (float) IngredientBatch::where('ingredient_id', $ingredient->id)->sum('quantity');
        $this->assertEquals(10.00, $remaining, 'Stock should remain unchanged when validation fails');
    }

    // ─────────────────────────────────────────────────────
    //  5. Idempotency — same UUID twice, no duplicate
    // ─────────────────────────────────────────────────────
    public function test_sync_order_idempotent_by_uuid(): void
    {
        $uuid    = Str::uuid()->toString();
        $payload = $this->validPayload(['orders' => [
            array_merge($this->validPayload()['orders'][0], ['uuid' => $uuid]),
        ]]);

        // First request — creates order
        $this->actingAs($this->cashier, 'web')
            ->postJson('/api/sync-orders', $payload)
            ->assertStatus(200);

        $this->assertEquals(1, Order::where('uuid', $uuid)->count());

        // Second request — same UUID → skipped (idempotent)
        $response = $this->actingAs($this->cashier, 'web')
            ->postJson('/api/sync-orders', $payload);

        $response->assertStatus(200);
        $response->assertJsonPath('synced.0.serverOrderCode', Order::where('uuid', $uuid)->value('order_code'));
        $response->assertJsonPath('summary.synced', 1);

        // Still exactly one order
        $this->assertEquals(1, Order::where('uuid', $uuid)->count());
    }

    // ─────────────────────────────────────────────────────
    //  6. Invalid payload → 422
    // ─────────────────────────────────────────────────────
    public function test_sync_order_rejects_invalid_payload(): void
    {
        // Missing required 'orders' key
        $response = $this->actingAs($this->cashier, 'web')
            ->postJson('/api/sync-orders', ['uuid' => 'abc']);

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'Validation failed');
    }

    // ─────────────────────────────────────────────────────
    //  7. Unauthenticated → 401
    // ─────────────────────────────────────────────────────
    public function test_sync_order_requires_authentication(): void
    {
        $payload = $this->validPayload();

        $response = $this->postJson('/api/sync-orders', $payload);

        $response->assertStatus(401);
    }

    // ─────────────────────────────────────────────────────
    //  8. Multiple orders in one request → all created
    // ─────────────────────────────────────────────────────
    public function test_sync_multiple_orders_in_one_request(): void
    {
        $uuid1 = Str::uuid()->toString();
        $uuid2 = Str::uuid()->toString();
        $uuid3 = Str::uuid()->toString();

        $payload = [
            'orders' => [
                [
                    'uuid'          => $uuid1,
                    'items'         => [['menu_id' => $this->menu->id, 'quantity' => 1, 'price' => 10000]],
                    'paymentMethod' => 'cash',
                    'total'         => 10000,
                ],
                [
                    'uuid'          => $uuid2,
                    'items'         => [['menu_id' => $this->menu->id, 'quantity' => 2, 'price' => 12000]],
                    'paymentMethod' => 'cash',
                    'total'         => 24000,
                ],
                [
                    'uuid'          => $uuid3,
                    'items'         => [['menu_id' => $this->menu->id, 'quantity' => 3, 'price' => 8000]],
                    'paymentMethod' => 'qris',
                    'total'         => 24000,
                ],
            ],
        ];

        $response = $this->actingAs($this->cashier, 'web')
            ->postJson('/api/sync-orders', $payload);

        $response->assertStatus(200);
        $response->assertJsonPath('summary.total', 3);
        $response->assertJsonPath('summary.synced', 3);
        $response->assertJsonPath('summary.failed', 0);

        $this->assertDatabaseHas('orders', ['uuid' => $uuid1]);
        $this->assertDatabaseHas('orders', ['uuid' => $uuid2]);
        $this->assertDatabaseHas('orders', ['uuid' => $uuid3]);
    }

    // ─────────────────────────────────────────────────────
    //  9. Partial failure — 1 invalid + 2 valid
    // ─────────────────────────────────────────────────────
    public function test_sync_partial_failure(): void
    {
        // Create 3 menus; soft-delete the one we want to fail
        $menuValid1 = Menu::factory()->create();
        $menuValid2 = Menu::factory()->create();
        $menuFail   = Menu::factory()->create();
        $failId     = $menuFail->id;
        $menuFail->delete(); // soft-delete → exists raw check passes, Eloquent query misses

        $uuid1 = Str::uuid()->toString();
        $uuid2 = Str::uuid()->toString();
        $uuid3 = Str::uuid()->toString();

        $payload = [
            'orders' => [
                [
                    'uuid'          => $uuid1,
                    'items'         => [['menu_id' => $menuValid1->id, 'quantity' => 1, 'price' => 10000]],
                    'paymentMethod' => 'cash',
                    'total'         => 10000,
                ],
                [
                    'uuid'          => $uuid2,
                    'items'         => [['menu_id' => $failId, 'quantity' => 1, 'price' => 5000]],
                    'paymentMethod' => 'cash',
                    'total'         => 5000,
                ],
                [
                    'uuid'          => $uuid3,
                    'items'         => [['menu_id' => $menuValid2->id, 'quantity' => 1, 'price' => 15000]],
                    'paymentMethod' => 'cash',
                    'total'         => 15000,
                ],
            ],
        ];

        $response = $this->actingAs($this->cashier, 'web')
            ->postJson('/api/sync-orders', $payload);

        $response->assertStatus(200);

        // Summary: 2 synced, 1 failed
        $response->assertJsonPath('summary.total', 3);
        $response->assertJsonPath('summary.synced', 2);
        $response->assertJsonPath('summary.failed', 1);

        // The failed entry should reference the correct UUID
        $response->assertJsonPath('failed.0.localUuid', $uuid2);

        // Valid orders exist in DB, invalid one does not
        $this->assertDatabaseHas('orders', ['uuid' => $uuid1]);
        $this->assertDatabaseHas('orders', ['uuid' => $uuid3]);
        $this->assertDatabaseMissing('orders', ['uuid' => $uuid2]);
    }

    // ─────────────────────────────────────────────────────
    // 10. Rate limiting — 31 requests in 1 min → 429
    // ─────────────────────────────────────────────────────
    public function test_sync_rate_limited(): void
    {
        // Use a menu without ingredients so each request is fast
        $menu = Menu::factory()->create();

        $basePayload = [
            'orders' => [[
                'uuid'          => '', // placeholder
                'items'         => [['menu_id' => $menu->id, 'quantity' => 1, 'price' => 5000]],
                'paymentMethod' => 'cash',
                'total'         => 5000,
            ]],
        ];

        // 30 requests — all should pass
        for ($i = 0; $i < 30; $i++) {
            $basePayload['orders'][0]['uuid'] = Str::uuid()->toString();
            $this->actingAs($this->cashier, 'web')
                ->postJson('/api/sync-orders', $basePayload)
                ->assertStatus(200);
        }

        // 31st request → rate limited
        $basePayload['orders'][0]['uuid'] = Str::uuid()->toString();
        $response = $this->actingAs($this->cashier, 'web')
            ->postJson('/api/sync-orders', $basePayload);

        $response->assertStatus(429);
    }
}
