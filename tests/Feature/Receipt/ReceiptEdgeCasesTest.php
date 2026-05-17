<?php

namespace Tests\Feature\Receipt;

use App\Models\Order;
use App\Models\Setting;
use App\Services\WhatsAppReceiptService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class ReceiptEdgeCasesTest extends TestCase
{
    use RefreshDatabase;

    private WhatsAppReceiptService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new WhatsAppReceiptService;
    }

    // ─────────────────────────────────────────────────────────────
    // UUID collision & retry
    // ─────────────────────────────────────────────────────────────

    public function test_uuid_collision_throws_unique_constraint_exception(): void
    {
        $uuid = (string) Uuid::uuid7();

        // Insert first order with this UUID
        Order::factory()->create(['uuid' => $uuid]);

        // Second insert with the same UUID must throw
        $this->expectException(UniqueConstraintViolationException::class);

        Order::factory()->create(['uuid' => $uuid]);
    }

    public function test_uuid_collision_retry_assigns_new_uuid(): void
    {
        $firstUuid = (string) Uuid::uuid7();
        Order::factory()->create(['uuid' => $firstUuid]);

        $attemptUuid = $firstUuid; // deliberately reused
        $orderModel = null;

        // First attempt — will fail due to duplicate UUID inside a savepoint
        $collisionDetected = false;
        try {
            DB::transaction(function () use ($attemptUuid, &$orderModel) {
                $orderModel = Order::factory()->create(['uuid' => $attemptUuid]);
            });
        } catch (UniqueConstraintViolationException) {
            $collisionDetected = true;
        }

        $this->assertTrue($collisionDetected, 'UUID collision must be detected');

        // Retry with a fresh UUID in a new savepoint
        $attemptUuid = (string) Uuid::uuid7();
        DB::transaction(function () use ($attemptUuid, &$orderModel) {
            $orderModel = Order::factory()->create(['uuid' => $attemptUuid]);
        });

        $this->assertNotNull($orderModel, 'Order must be created after retry');
        $this->assertNotSame($firstUuid, $orderModel->uuid, 'Retried order must have a different UUID');
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $orderModel->uuid,
            'Retried UUID must be a valid UUID v7'
        );
    }

    public function test_uuid_collision_retry_succeeds_with_unique_uuid(): void
    {
        // Pre-seed with multiple UUIDs to force potential collision
        $existingUuids = [];
        for ($i = 0; $i < 5; $i++) {
            $existingUuids[] = (string) Uuid::uuid7();
        }
        // Insert all so the table has entries
        foreach ($existingUuids as $eu) {
            Order::factory()->create(['uuid' => $eu]);
        }

        // Simulate retry loop — generate a UUID that definitely doesn't collide
        $retryUuid = (string) Uuid::uuid7();
        while (in_array($retryUuid, $existingUuids) || Order::where('uuid', $retryUuid)->exists()) {
            $retryUuid = (string) Uuid::uuid7();
        }

        $order = Order::factory()->create(['uuid' => $retryUuid]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'uuid' => $retryUuid,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Phone format edge cases
    // ─────────────────────────────────────────────────────────────

    public function test_normalize_phone_empty_string(): void
    {
        $result = $this->service->normalizePhone('');
        $this->assertSame('', $result);
    }

    public function test_normalize_phone_with_only_special_chars(): void
    {
        $result = $this->service->normalizePhone('+ - ( ) # .');
        $this->assertSame('', $result, 'Phone with only special chars must yield empty string');
    }

    public function test_normalize_phone_with_leading_62_space(): void
    {
        $result = $this->service->normalizePhone('62 812 3456 7890');
        $this->assertSame('6281234567890', $result);
    }

    public function test_normalize_phone_with_country_code_plus62(): void
    {
        $result = $this->service->normalizePhone('+6281234567890');
        $this->assertSame('6281234567890', $result);
    }

    public function test_normalize_phone_with_country_code_plus62_no_digits(): void
    {
        // +62 followed by non-digit characters should yield just '62'
        $result = $this->service->normalizePhone('+62abc');
        $this->assertSame('62', $result);
    }

    public function test_normalize_phone_with_08_prefix_and_dashes(): void
    {
        $result = $this->service->normalizePhone('08-123-456-789');
        $this->assertSame('628123456789', $result);
    }

    public function test_normalize_phone_with_parentheses(): void
    {
        $result = $this->service->normalizePhone('(0812) 3456 7890');
        $this->assertSame('6281234567890', $result);
    }

    public function test_normalize_phone_already_62_long_number(): void
    {
        // 15 digits (max allowed length)
        $result = $this->service->normalizePhone('628123456789012');
        $this->assertSame('628123456789012', $result);
    }

    public function test_validate_phone_rejects_empty_string(): void
    {
        $this->assertFalse($this->service->validatePhone(''));
    }

    public function test_validate_phone_rejects_too_short_9_digits(): void
    {
        // 9 digits — below minimum of 10
        $this->assertFalse(
            $this->service->validatePhone('628123456'),
            'Phone with 9 digits must be rejected'
        );
    }

    public function test_validate_phone_rejects_too_long_16_digits(): void
    {
        // 16 digits — above maximum of 15
        $phone = '6281234567890123'; // 16 digits
        $this->assertFalse(
            $this->service->validatePhone($phone),
            'Phone with 16 digits must be rejected'
        );
    }

    public function test_validate_phone_rejects_letters(): void
    {
        $this->assertFalse($this->service->validatePhone('62812abc3456'));
    }

    public function test_validate_phone_rejects_special_chars(): void
    {
        $this->assertFalse($this->service->validatePhone('62812-3456-78'));
    }

    public function test_validate_phone_accepts_minimum_10_digits(): void
    {
        $phone = '6281234567'; // exactly 10 digits
        $this->assertTrue(
            $this->service->validatePhone($phone),
            'Phone with exactly 10 digits must be accepted'
        );
    }

    public function test_validate_phone_accepts_maximum_15_digits(): void
    {
        $phone = '628123456789012'; // exactly 15 digits
        $this->assertTrue(
            $this->service->validatePhone($phone),
            'Phone with exactly 15 digits must be accepted'
        );
    }

    public function test_normalize_then_validate_round_trip_for_08_prefix(): void
    {
        $raw = '0812-3456-7890';
        $normalized = $this->service->normalizePhone($raw);
        $this->assertSame('6281234567890', $normalized);
        $this->assertTrue($this->service->validatePhone($normalized));
    }

    public function test_normalize_then_validate_round_trip_for_plus62_prefix(): void
    {
        $raw = '+62 812-3456-7890';
        $normalized = $this->service->normalizePhone($raw);
        $this->assertSame('6281234567890', $normalized);
        $this->assertTrue($this->service->validatePhone($normalized));
    }

    // ─────────────────────────────────────────────────────────────
    // Invalid WhatsApp link
    // ─────────────────────────────────────────────────────────────

    public function test_build_wa_me_link_throws_on_empty_phone(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid phone number');

        $order = Order::factory()->create(['total_amount' => 45000]);
        $this->service->buildWaMeLink($order, '');
    }

    public function test_build_wa_me_link_throws_on_non_numeric_phone(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $order = Order::factory()->create(['total_amount' => 45000]);
        // After normalization this becomes '62abc' which fails validation
        $this->service->buildWaMeLink($order, 'abc123');
    }

    public function test_build_wa_me_link_throws_on_too_short_after_normalize(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $order = Order::factory()->create(['total_amount' => 45000]);
        // Normalized becomes '628' (3 digits — too short)
        $this->service->buildWaMeLink($order, '08');
    }

    public function test_build_wa_me_link_throws_on_too_long_after_normalize(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $order = Order::factory()->create(['total_amount' => 45000]);
        // 16 digits after normalization
        $this->service->buildWaMeLink($order, '0812345678901234');
    }

    public function test_build_wa_me_link_with_null_phone_throws_type_error(): void
    {
        $this->expectException(\TypeError::class);

        $order = Order::factory()->create(['total_amount' => 45000]);
        // @phpstan-ignore argument.type
        $this->service->buildWaMeLink($order, null);
    }

    public function test_build_wa_me_link_handles_phone_with_only_emoji(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $order = Order::factory()->create(['total_amount' => 45000]);
        // Emojis are non-digit chars — stripped by normalizePhone, leaving empty string
        $this->service->buildWaMeLink($order, '📱😀🎉');
    }

    public function test_build_wa_me_link_with_leading_zeroes_only(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $order = Order::factory()->create(['total_amount' => 45000]);
        // Normalized from '0000000000' stays '62000000000' — 11 digits, valid!
        // Let's use '000000' which normalizes to '6200000' — 7 digits, invalid
        $this->service->buildWaMeLink($order, '000000');
    }

    // ─────────────────────────────────────────────────────────────
    // WhatsApp link URL encoding edge cases
    // ─────────────────────────────────────────────────────────────

    public function test_build_wa_me_link_encodes_special_characters_in_message(): void
    {
        Setting::create(['key' => 'cafe_name', 'value' => 'W9 Cafe & Eatery']);
        Setting::create([
            'key' => 'receipt_whatsapp_template',
            'value' => 'Pesanan dari {{cafe_name}} total {{total}}',
        ]);

        $order = Order::factory()->create([
            'total_amount' => 45000,
            'customer_name' => 'Test & User',
            'created_at' => now()->setDate(2026, 5, 18)->setTime(10, 30),
        ]);

        $link = $this->service->buildWaMeLink($order, '081234567890');

        // & in cafe name must be URL-encoded
        $this->assertStringContainsString(urlencode('W9 Cafe & Eatery'), $link);
        $this->assertStringNotContainsString('W9 Cafe & Eatery', $link);
    }

    public function test_generate_message_with_empty_items_collection(): void
    {
        Setting::create(['key' => 'cafe_name', 'value' => 'W9 Cafe']);

        $order = Order::factory()->create([
            'total_amount' => 0,
            'customer_name' => 'Empty Order',
            'created_at' => now()->setDate(2026, 5, 18)->setTime(10, 30),
        ]);

        // Order has no items yet
        $message = $this->service->generateMessage($order);

        // Pesan harus tetap dihasilkan tanpa prefix "Pesanan:"
        $this->assertStringNotContainsString('Pesanan:', $message);
        $this->assertStringContainsString('W9 Cafe', $message);
    }

    // ─────────────────────────────────────────────────────────────
    // Template with missing placeholders
    // ─────────────────────────────────────────────────────────────

    public function test_generate_message_with_missing_optional_placeholder(): void
    {
        Setting::create(['key' => 'cafe_name', 'value' => 'W9 Cafe']);
        Setting::create([
            'key' => 'receipt_whatsapp_template',
            'value' => '{{cafe_name}} — {{total}} — {{non_existent_placeholder}}',
        ]);

        $order = Order::factory()->create([
            'total_amount' => 45000,
            'customer_name' => 'Test Customer',
            'created_at' => now()->setDate(2026, 5, 18)->setTime(10, 30),
        ]);

        $message = $this->service->generateMessage($order);

        // Unrecognized placeholder must remain in message as-is (no crash)
        $this->assertStringContainsString('{{non_existent_placeholder}}', $message);
        $this->assertStringContainsString('W9 Cafe', $message);
        $this->assertStringContainsString('Rp 45.000', $message);
    }

    // ─────────────────────────────────────────────────────────────
    // formatRupiah edge cases
    // ─────────────────────────────────────────────────────────────

    public function test_format_rupiah_zero(): void
    {
        $result = $this->service->formatRupiah(0);
        $this->assertSame('Rp 0', $result);
    }

    public function test_format_rupiah_large_number(): void
    {
        $result = $this->service->formatRupiah(99999999);
        $this->assertSame('Rp 99.999.999', $result);
    }

    public function test_format_rupiah_negative_handling(): void
    {
        // formatRupiah menerima int, negative diserahkan ke number_format
        $result = $this->service->formatRupiah(-5000);
        $this->assertSame('Rp -5.000', $result);
    }
}
