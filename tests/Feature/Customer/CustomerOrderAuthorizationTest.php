<?php

namespace Tests\Feature\Customer;

use App\Models\CafeTable;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Order;
use App\Models\User;
use App\Support\CustomerSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CustomerOrderAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private const PHONE_OWNER = '081200000001';

    private const PHONE_OTHER = '089900000009';

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function makeOrder(array $attributes = []): Order
    {
        return Order::factory()->create(array_merge([
            'phone' => self::PHONE_OWNER,
            'status' => 'pending',
            'payment_method' => 'cash',
            'cashier_id' => null,
        ], $attributes));
    }

    // ── History (IDOR) ──────────────────────────────────────────────────

    public function test_history_only_returns_orders_for_the_session_bound_phone(): void
    {
        $mine = $this->makeOrder(['phone' => self::PHONE_OWNER]);
        $other = $this->makeOrder(['phone' => self::PHONE_OTHER]);

        $this->withSession([CustomerSession::PHONE_KEY => self::PHONE_OWNER])
            ->get(route('customer.history'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Customer/History/Index')
                ->has('orders', 1)
                ->where('orders.0.id', $mine->id)
            );

        $this->assertNotSame($mine->id, $other->id);
    }

    public function test_history_ignores_the_attacker_supplied_phone_query_parameter(): void
    {
        $this->makeOrder(['phone' => self::PHONE_OTHER]);

        $this->withSession([CustomerSession::PHONE_KEY => '081200000099'])
            ->get(route('customer.history', ['phone' => self::PHONE_OTHER]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Customer/History/Index')
                ->has('orders', 0)
            );
    }

    public function test_history_is_empty_without_a_session_identity(): void
    {
        $this->makeOrder(['phone' => self::PHONE_OTHER]);

        $this->get(route('customer.history'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Customer/History/Index')
                ->has('orders', 0)
            );
    }

    // ── Order detail / status ───────────────────────────────────────────

    public function test_status_page_forbids_a_foreign_order_without_a_session(): void
    {
        $foreign = $this->makeOrder(['phone' => self::PHONE_OTHER]);

        $this->get(route('customer.order.status', $foreign))->assertForbidden();
    }

    public function test_status_page_forbids_a_session_with_a_different_phone(): void
    {
        $foreign = $this->makeOrder(['phone' => self::PHONE_OTHER]);

        $this->withSession([CustomerSession::PHONE_KEY => self::PHONE_OWNER])
            ->get(route('customer.order.status', $foreign))
            ->assertForbidden();
    }

    public function test_phone_less_orders_are_isolated_by_the_session_order_list(): void
    {
        $mine = $this->makeOrder(['phone' => null]);
        $foreign = $this->makeOrder(['phone' => null]);

        $this->withSession([CustomerSession::ORDERS_KEY => [(string) $mine->id]])
            ->get(route('customer.order.status', $mine))
            ->assertOk();

        // Two phone-less orders must never match each other.
        $this->withSession([CustomerSession::ORDERS_KEY => [(string) $mine->id]])
            ->get(route('customer.order.status', $foreign))
            ->assertForbidden();
    }

    public function test_status_page_allows_the_owner_bound_by_phone(): void
    {
        $mine = $this->makeOrder(['phone' => self::PHONE_OWNER]);

        $this->withSession([CustomerSession::PHONE_KEY => self::PHONE_OWNER])
            ->get(route('customer.order.status', $mine))
            ->assertOk();
    }

    public function test_status_page_allows_the_order_the_session_created(): void
    {
        $mine = $this->makeOrder(['phone' => null]);

        $this->withSession([CustomerSession::ORDERS_KEY => [(string) $mine->id]])
            ->get(route('customer.order.status', $mine))
            ->assertOk();
    }

    public function test_unknown_order_still_returns_not_found(): void
    {
        $this->get(route('customer.order.status', ['order' => 999999]))->assertNotFound();
    }

    // ── Payment / QRIS proof API ────────────────────────────────────────

    public function test_choose_payment_api_forbids_a_foreign_order(): void
    {
        $foreign = $this->makeOrder(['phone' => self::PHONE_OTHER, 'payment_method' => 'qris']);

        $this->postJson(route('customer.payment.cash', $foreign))->assertForbidden();

        $this->assertSame('qris', $foreign->fresh()->payment_method->value);
    }

    public function test_qris_proof_api_rejects_a_foreign_order_and_leaves_it_untouched(): void
    {
        Storage::fake('public');

        $foreign = $this->makeOrder([
            'phone' => self::PHONE_OTHER,
            'payment_method' => 'qris',
            'rejection_note' => 'Bukti tidak jelas, kirim ulang.',
            'payment_proof' => null,
        ]);

        $this->postJson(route('customer.payment.qris-proof', $foreign), [
            'proof' => 'x',
        ])->assertForbidden();

        $fresh = $foreign->fresh();
        $this->assertNull($fresh->payment_proof);
        $this->assertSame('qris', $fresh->payment_method->value);
        $this->assertSame('Bukti tidak jelas, kirim ulang.', $fresh->rejection_note);
    }

    public function test_qris_proof_api_allows_the_owning_session(): void
    {
        Storage::fake('public');

        $mine = $this->makeOrder(['phone' => null, 'payment_method' => 'qris']);

        $this->withSession([CustomerSession::ORDERS_KEY => [(string) $mine->id]])
            ->post(route('customer.payment.qris-proof', $mine), [
                'proof' => UploadedFile::fake()->image('proof.png'),
            ])
            ->assertOk();

        $this->assertNotNull($mine->fresh()->payment_proof);
    }

    public function test_web_qris_upload_forbids_a_foreign_order(): void
    {
        $foreign = $this->makeOrder(['phone' => self::PHONE_OTHER, 'payment_method' => 'qris']);

        $this->get(route('customer.payment.qris', $foreign))->assertForbidden();
    }

    // ── Identity binding on order placement ─────────────────────────────

    public function test_order_placement_binds_the_session_identity_and_remembers_the_order(): void
    {
        $category = Category::create(['name' => 'Kopi']);
        $menu = Menu::create([
            'category_id' => $category->id,
            'name' => 'Espresso',
            'price' => 12000,
            'cost_price' => 7000,
            'status' => 'active',
        ]);
        $table = CafeTable::create(['table_number' => 1]);

        $response = $this->postJson(route('customer.order.store'), [
            'customer_name' => 'Budi',
            'customer_phone' => self::PHONE_OWNER,
            'table_id' => $table->id,
            'items' => [['menu_id' => $menu->id, 'quantity' => 2]],
        ]);

        $response->assertCreated();
        $response->assertSessionHas(CustomerSession::PHONE_KEY, self::PHONE_OWNER);

        $order = Order::where('phone', self::PHONE_OWNER)->firstOrFail();

        $response->assertSessionHas(CustomerSession::ORDERS_KEY, function ($ids) use ($order) {
            return in_array((string) $order->id, array_map('strval', (array) $ids), true);
        });

        // The `customer_phone` alias must be persisted, otherwise the session
        // phone could never match the stored row.
        $this->assertSame(self::PHONE_OWNER, $order->phone);
    }

    // ── Props minimisation ──────────────────────────────────────────────

    public function test_cashier_pos_page_does_not_expose_cost_or_supplier_data(): void
    {
        $category = Category::create(['name' => 'Kopi']);
        Menu::create([
            'category_id' => $category->id,
            'name' => 'Espresso',
            'price' => 12000,
            'cost_price' => 7000,
            'status' => 'active',
        ]);

        $cashier = User::factory()->create(['role' => 'cashier']);

        $this->actingAs($cashier)
            ->get(route('kasir.new-order'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Cashier/NewOrder')
                ->has('categories')
                ->missing('categories.0.menus.0.cost_price')
                ->missing('categories.0.menus.0.menuIngredients')
                ->missing('categories.0.menus.0.image')
            );
    }

    public function test_cashier_profile_page_does_not_expose_the_full_user_model(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $this->actingAs($cashier)
            ->get(route('kasir.profile'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Cashier/Profile')
                ->where('user.email', $cashier->email)
                ->missing('user.password')
                ->missing('user.status')
                ->missing('user.remember_token')
            );
    }

    // ── Cashier flow regression ─────────────────────────────────────────

    public function test_cashier_can_still_render_the_dashboard_and_complete_an_order(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $order = $this->makeOrder([
            'cashier_id' => $cashier->id,
            'order_type' => 'cashier',
            'status' => 'processing',
            'payment_method' => 'cash',
        ]);

        $this->actingAs($cashier)->get(route('kasir.dashboard'))->assertOk();

        $this->actingAs($cashier)
            ->patch(route('kasir.order.update-status', $order), ['status' => 'completed'])
            ->assertOk()
            ->assertJson(['message' => 'Status diperbarui.']);

        $this->assertSame('completed', $order->fresh()->status->value);
    }
}
