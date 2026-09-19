<?php

namespace Tests\Feature\Admin\Smoke;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashierCustomerPagesSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_and_customer_pages_render(): void
    {
        $this->seed();

        $cashier = User::where('email', 'kasir@w9cafe.com')->firstOrFail();
        $customer = User::factory()->create(['role' => 'customer']);

        $cashierPages = [
            '/kasir/dashboard',
            '/kasir/pesanan-baru',
            '/kasir/pesanan-aktif',
            '/kasir/riwayat-pesanan',
            '/kasir/profil',
        ];

        $customerPages = [
            '/pelanggan/menu',
            '/pelanggan/keranjang',
            '/pelanggan/riwayat',
        ];

        $failed = [];

        foreach ($cashierPages as $path) {
            $response = $this->actingAs($cashier)->get($path);

            if (! $response->isSuccessful()) {
                $failed[] = "{$path} ({$response->status()})";
            }
        }

        foreach ($customerPages as $path) {
            $response = $this->actingAs($customer)->get($path);

            if (! $response->isSuccessful()) {
                $failed[] = "{$path} ({$response->status()})";
            }
        }

        $this->assertEmpty($failed, 'Halaman gagal: ' . implode(', ', $failed));
    }
}
