<?php

namespace Tests\Feature\Temp;

use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnumRuntimeSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_pages_render_with_enum_casts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $cashier = User::factory()->create(['role' => 'cashier']);
        $ingredient = Ingredient::create([
            'name' => 'Kopi', 'unit' => 'gram', 'batch_mode' => 'fefo', 'low_stock_threshold' => 10,
        ]);
        IngredientBatch::create([
            'ingredient_id' => $ingredient->id, 'quantity' => 100, 'cost_per_unit' => 5000,
            'received_at' => now(), 'payment_status' => 'paid',
        ]);
        Order::factory()->create(['cashier_id' => $cashier->id, 'payment_method' => 'pay_later', 'status' => 'unpaid']);

        $pages = [
            '/admin/bahan-baku',
            '/admin/penyesuaian-stok',
            '/admin/pesanan',
            '/admin/piutang',
            '/admin/akun-staff',
            '/admin/riwayat-kasir',
        ];

        foreach ($pages as $path) {
            $response = $this->actingAs($admin, 'admin')->get($path);
            $this->assertTrue(
                $response->status() === 200 || $response->status() === 302,
                "Halaman {$path} gagal: status {$response->status()}"
            );
        }

        $this->assertTrue(true);
    }
}
