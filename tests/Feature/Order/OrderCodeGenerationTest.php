<?php

namespace Tests\Feature\Order;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Order;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PDOException;
use RuntimeException;
use Tests\TestCase;

class OrderCodeGenerationTest extends TestCase
{
    use RefreshDatabase;

    private function prefix(): string
    {
        return 'ORD-'.now()->format('dmy').'-';
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function baseAttributes(array $overrides = []): array
    {
        return array_merge([
            'status' => OrderStatus::Pending->value,
            'order_type' => OrderType::Cashier->value,
            'total_amount' => 10_000,
        ], $overrides);
    }

    private function uniqueViolation(string $index, array $columns): UniqueConstraintViolationException
    {
        $previous = new PDOException("duplicate key value violates unique constraint \"{$index}\"");

        return (new UniqueConstraintViolationException(
            'pgsql',
            'insert into "orders" ("order_code") values (?)',
            ['ORD-000000-0001'],
            $previous,
        ))->setIndex($index)->setColumns($columns);
    }

    public function test_first_order_code_follows_expected_format(): void
    {
        $order = Order::create($this->baseAttributes());

        $this->assertSame($this->prefix().'0001', $order->order_code);
        $this->assertMatchesRegularExpression('/^ORD-\d{6}-\d{4}$/', $order->order_code);
    }

    public function test_sequential_orders_receive_distinct_codes(): void
    {
        $prefix = $this->prefix();
        $codes = [];

        for ($i = 0; $i < 5; $i++) {
            $codes[] = Order::create($this->baseAttributes())->order_code;
        }

        $expected = array_map(
            fn (int $n): string => $prefix.str_pad((string) $n, 4, '0', STR_PAD_LEFT),
            range(1, 5),
        );

        $this->assertSame($expected, $codes);
        $this->assertCount(5, array_unique($codes), 'Setiap kode pesanan harus unik.');
    }

    public function test_generation_uses_highest_existing_suffix_not_row_count(): void
    {
        $prefix = $this->prefix();

        // Simulate prior orders that were later removed: only one row remains,
        // but its suffix is 0007, so the next code must continue from there.
        Order::create($this->baseAttributes(['order_code' => $prefix.'0007']));

        $this->assertSame($prefix.'0008', Order::generateCode());

        $order = Order::create($this->baseAttributes());

        $this->assertSame($prefix.'0008', $order->order_code);
    }

    public function test_create_gives_up_with_clear_exception_after_capped_retries(): void
    {
        $prefix = $this->prefix();
        Order::create($this->baseAttributes(['order_code' => $prefix.'0001']));

        try {
            Order::create($this->baseAttributes(['order_code' => $prefix.'0001']));
            $this->fail('Duplikat kode pesanan seharusnya menaikkan exception setelah batas percobaan.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('5 percobaan', $e->getMessage());
            $this->assertInstanceOf(UniqueConstraintViolationException::class, $e->getPrevious());
        }

        $this->assertSame(1, Order::where('order_code', $prefix.'0001')->count());
    }

    public function test_retry_helper_recovers_from_order_code_collision(): void
    {
        $attempts = 0;

        $result = Order::retryOnCodeCollision(function (int $attempt) use (&$attempts) {
            $attempts++;

            if ($attempt === 1) {
                throw $this->uniqueViolation('orders_order_code_unique', ['order_code']);
            }

            return 'recovered';
        });

        $this->assertSame('recovered', $result);
        $this->assertSame(2, $attempts);
    }

    public function test_retry_helper_does_not_swallow_unrelated_unique_violations(): void
    {
        $attempts = 0;

        try {
            Order::retryOnCodeCollision(function () use (&$attempts) {
                $attempts++;
                throw $this->uniqueViolation('users_email_unique', ['email']);
            });
            $this->fail('Unique violation yang tidak terkait tidak boleh di-retry.');
        } catch (UniqueConstraintViolationException $e) {
            $this->assertSame('users_email_unique', $e->index);
        }

        $this->assertSame(1, $attempts);
    }
}
