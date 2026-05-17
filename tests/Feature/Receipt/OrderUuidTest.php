<?php

namespace Tests\Feature\Receipt;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class OrderUuidTest extends TestCase
{
    use RefreshDatabase;

    public function test_uuid_v7_matches_valid_format(): void
    {
        $uuid = (string) Uuid::uuid7();

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $uuid,
            'UUID v7 must match RFC 9562 format: version nibble=7, variant=10xx'
        );
    }

    public function test_reject_duplicate_uuid(): void
    {
        $uuid = (string) Uuid::uuid7();

        Order::factory()->create(['uuid' => $uuid]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Order::factory()->create(['uuid' => $uuid]);
    }

    public function test_accepts_uuid_v7_and_stores_correctly(): void
    {
        $uuid = (string) Uuid::uuid7();

        $order = Order::factory()->create([
            'uuid' => $uuid,
            'total_amount' => 45000,
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'uuid' => $uuid,
        ]);

        $retrieved = Order::find($order->id);
        $this->assertSame($uuid, $retrieved->uuid);
    }

    public function test_backfill_assigns_uuid_to_order_with_null_uuid(): void
    {
        $order = Order::factory()->create([
            'uuid' => null,
            'total_amount' => 30000,
            'order_code' => 'ORD-BACKFILL-001',
        ]);

        $this->assertNull($order->uuid, 'Order must start with null uuid');

        DB::statement(
            "UPDATE orders SET uuid = gen_random_uuid() WHERE id = ?",
            [$order->id]
        );

        $order->refresh();

        $this->assertNotNull($order->uuid, 'Backfill must assign a UUID');
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $order->uuid,
            'Backfilled UUID must be a valid hexadecimal UUID string'
        );
    }
}
