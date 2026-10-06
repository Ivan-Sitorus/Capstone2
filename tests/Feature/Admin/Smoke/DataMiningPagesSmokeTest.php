<?php

namespace Tests\Feature\Admin\Smoke;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataMiningPagesSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_data_mining_pages_render(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $pages = [
            '/admin/prediksi-menu',
            '/admin/klasterisasi-menu',
            '/admin/asosiatif-menu',
            '/admin/prediksi-bahan-baku',
            '/admin/klasterisasi-bahan-baku',
        ];

        $failed = [];

        foreach ($pages as $path) {
            $response = $this->actingAs($admin, 'admin')->get($path);

            if (! $response->isSuccessful()) {
                $failed[] = "{$path} ({$response->status()})";
            }
        }

        $this->assertEmpty($failed, 'Halaman gagal: ' . implode(', ', $failed));
    }
}
