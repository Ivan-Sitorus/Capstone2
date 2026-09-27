<?php

namespace Tests\Feature\Cashier;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashierOrderHistoryFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_invalid_date_filter_is_rejected_instead_of_hitting_the_database(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $this->actingAs($cashier)
            ->from(route('kasir.order-history'))
            ->get(route('kasir.order-history', ['date' => 'not-a-date']))
            ->assertRedirect(route('kasir.order-history'))
            ->assertSessionHasErrors('date');
    }

    public function test_a_valid_date_filter_renders(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $this->actingAs($cashier)
            ->get(route('kasir.order-history', ['date' => now()->toDateString()]))
            ->assertOk();
    }
}
