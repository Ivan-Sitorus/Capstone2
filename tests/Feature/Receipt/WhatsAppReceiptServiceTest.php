<?php

namespace Tests\Feature\Receipt;

use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Services\WhatsAppReceiptService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppReceiptServiceTest extends TestCase
{
    use RefreshDatabase;

    private WhatsAppReceiptService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new WhatsAppReceiptService;
    }

    // ─────────────────────────────────────────────────────────────
    // normalizePhone tests
    // ─────────────────────────────────────────────────────────────

    public function test_normalize_phone_0812_to_62812(): void
    {
        $result = $this->service->normalizePhone('081234567890');
        $this->assertSame('6281234567890', $result);
    }

    public function test_normalize_phone_plus62_with_space_to_62812(): void
    {
        $result = $this->service->normalizePhone('+62 812-3456-7890');
        $this->assertSame('6281234567890', $result);
    }

    public function test_normalize_phone_812_to_62812(): void
    {
        $result = $this->service->normalizePhone('81234567890');
        $this->assertSame('6281234567890', $result);
    }

    public function test_normalize_phone_62812_stays_62812(): void
    {
        $result = $this->service->normalizePhone('6281234567890');
        $this->assertSame('6281234567890', $result);
    }

    // ─────────────────────────────────────────────────────────────
    // validatePhone / reject invalid
    // ─────────────────────────────────────────────────────────────

    public function test_reject_invalid_phone_too_short(): void
    {
        // Phone number with only 5 digits after normalization
        $shortPhone = '62812'; // 5 digits

        $this->assertFalse(
            $this->service->validatePhone($shortPhone),
            'Phone number with fewer than 10 digits must be rejected'
        );
    }

    public function test_build_wa_me_link_throws_on_invalid_phone(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $order = Order::factory()->create([
            'total_amount' => 45000,
            'customer_name' => 'Test Customer',
        ]);

        // Too short after normalization
        $this->service->buildWaMeLink($order, '12345');
    }

    // ─────────────────────────────────────────────────────────────
    // wa.me link format
    // ─────────────────────────────────────────────────────────────

    public function test_build_wa_me_link_has_correct_format(): void
    {
        Setting::create(['key' => 'cafe_name', 'value' => 'W9 Cafe']);

        $order = Order::factory()->create([
            'total_amount' => 45000,
            'customer_name' => 'Test Customer',
            'created_at' => now()->setDate(2026, 5, 18)->setTime(10, 30),
        ]);

        $link = $this->service->buildWaMeLink($order, '081234567890');

        $this->assertStringStartsWith('https://wa.me/6281234567890?text=', $link);
        $this->assertStringContainsString(urlencode('W9 Cafe'), $link);
        $this->assertStringContainsString(urlencode('Rp 45.000'), $link);
    }

    // ─────────────────────────────────────────────────────────────
    // Template interpolation
    // ─────────────────────────────────────────────────────────────

    public function test_template_interpolation_replaces_all_placeholders(): void
    {
        Setting::create(['key' => 'cafe_name', 'value' => 'W9 Cafe STIE']);
        Setting::create([
            'key' => 'receipt_whatsapp_template',
            'value' => 'Pesanan {{order_code}} dari {{cafe_name}} total {{total}} pada {{date}}. Lihat: {{receipt_url}}',
        ]);

        $order = Order::factory()->create([
            'order_code' => 'ORD-20260518-0001',
            'total_amount' => 45000,
            'customer_name' => 'Test Customer',
            'created_at' => now()->setDate(2026, 5, 18)->setTime(10, 30),
            'uuid' => '550e8400-e29b-41d4-a716-446655440000',
        ]);

        $message = $this->service->generateMessage($order);

        $this->assertStringContainsString('ORD-20260518-0001', $message);
        $this->assertStringContainsString('W9 Cafe STIE', $message);
        $this->assertStringContainsString('Rp 45.000', $message);
        $this->assertStringContainsString('18 May 2026', $message);
        $this->assertStringContainsString('550e8400-e29b-41d4-a716-446655440000', $message);
        $this->assertStringContainsString('/struk-pesanan/', $message);
    }

    // ─────────────────────────────────────────────────────────────
    // Items summary truncated to 2
    // ─────────────────────────────────────────────────────────────

    public function test_items_summary_truncated_to_two_items(): void
    {
        Setting::create(['key' => 'cafe_name', 'value' => 'W9 Cafe']);

        $order = Order::factory()->create([
            'total_amount' => 75000,
            'customer_name' => 'Test Customer',
            'created_at' => now()->setDate(2026, 5, 18)->setTime(10, 30),
        ]);

        $menu1 = Menu::factory()->create(['name' => 'Kopi Robusta', 'price' => 12000]);
        $menu2 = Menu::factory()->create(['name' => 'Roti Bakar', 'price' => 15000]);
        $menu3 = Menu::factory()->create(['name' => 'Es Jeruk', 'price' => 8000]);
        $menu4 = Menu::factory()->create(['name' => 'Nasi Goreng', 'price' => 25000]);

        OrderItem::insert([
            [
                'order_id' => $order->id,
                'menu_id' => $menu1->id,
                'quantity' => 2,
                'unit_price' => 12000,
                'subtotal' => 24000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'order_id' => $order->id,
                'menu_id' => $menu2->id,
                'quantity' => 1,
                'unit_price' => 15000,
                'subtotal' => 15000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'order_id' => $order->id,
                'menu_id' => $menu3->id,
                'quantity' => 3,
                'unit_price' => 8000,
                'subtotal' => 24000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'order_id' => $order->id,
                'menu_id' => $menu4->id,
                'quantity' => 1,
                'unit_price' => 25000,
                'subtotal' => 25000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Use generateMessage which internally calls summarizeItems
        $order->load('items.menu');
        $message = $this->service->generateMessage($order);

        // First two items should appear
        $this->assertStringContainsString('2x Kopi Robusta', $message);
        $this->assertStringContainsString('1x Roti Bakar', $message);

        // Remaining count indicator should appear
        $this->assertStringContainsString('dan 2 item lainnya', $message);

        // Third and fourth items should NOT appear individually
        $this->assertStringNotContainsString('Es Jeruk', $message);
        $this->assertStringNotContainsString('Nasi Goreng', $message);
    }

    // ─────────────────────────────────────────────────────────────
    // Edge case: summarizeItems with fewer than 3 items
    // ─────────────────────────────────────────────────────────────

    public function test_items_summary_two_items_no_truncation_suffix(): void
    {
        Setting::create(['key' => 'cafe_name', 'value' => 'W9 Cafe']);

        $order = Order::factory()->create([
            'total_amount' => 27000,
            'customer_name' => 'Test Customer',
            'created_at' => now()->setDate(2026, 5, 18)->setTime(10, 30),
        ]);

        $menu1 = Menu::factory()->create(['name' => 'Kopi Latte', 'price' => 15000]);
        $menu2 = Menu::factory()->create(['name' => 'Matcha Latte', 'price' => 12000]);

        OrderItem::insert([
            [
                'order_id' => $order->id,
                'menu_id' => $menu1->id,
                'quantity' => 1,
                'unit_price' => 15000,
                'subtotal' => 15000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'order_id' => $order->id,
                'menu_id' => $menu2->id,
                'quantity' => 1,
                'unit_price' => 12000,
                'subtotal' => 12000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $order->load('items.menu');
        $message = $this->service->generateMessage($order);

        $this->assertStringContainsString('1x Kopi Latte', $message);
        $this->assertStringContainsString('1x Matcha Latte', $message);
        $this->assertStringNotContainsString('item lainnya', $message);
    }
}
