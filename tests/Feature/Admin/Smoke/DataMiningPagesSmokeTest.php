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
            '/admin/data-mining',
            '/admin/asosiatif-menu',
            '/admin/klasterisasi-bahan-baku',
            '/admin/klasterisasi-menu',
            '/admin/prediksi-bahan-baku',
            '/admin/prediksi-menu',
            '/admin/prediksi-ring-menu',
            '/admin/prediction-ring-bahan-baku',
            '/admin/ringkasan-menu',
            '/admin/ringkasan-asosiatif',
            '/admin/ringkasan-clustering-bahan-baku',
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
