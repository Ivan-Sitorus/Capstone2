<?php

namespace Tests\Feature\Admin;

use App\Filament\Widgets\PemakaianBahanBakuWidget;
use App\Filament\Widgets\TopBahanBakuWidget;
use App\Models\Ingredient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use ReflectionMethod;
use Tests\TestCase;

class BahanBakuWidgetValidityTest extends TestCase
{
    use RefreshDatabase;

    private function movement(int $ingredientId, string $type, float $change, Carbon $at): void
    {
        DB::table('stock_movements')->insert([
            'ingredient_id' => $ingredientId,
            'movement_type' => $type,
            'quantity_before' => 100,
            'quantity_change' => $change,
            'quantity_after' => 100 + $change,
            'reference' => $type,
            'created_at' => $at->toDateTimeString(),
            'updated_at' => $at->toDateTimeString(),
        ]);
    }

    private function invokeData(object $widget): array
    {
        $method = new ReflectionMethod($widget, 'getData');
        $method->setAccessible(true);

        return $method->invoke($widget);
    }

    public function test_top_bahan_baku_uses_only_sale_movements(): void
    {
        $ingredient = Ingredient::factory()->create(['name' => 'Bahan Uji', 'unit' => 'kg', 'batch_mode' => 'fefo']);
        $at = now()->subDay();

        DB::table('ingredient_batches')->insert([
            'ingredient_id' => $ingredient->id,
            'quantity' => 10,
            'expiry_date' => null,
            'received_at' => $at,
            'batch_code' => 'BATCH-UJI',
            'allow_expired_usage' => false,
            'initial_quantity' => 10,
            'supplier_name' => null,
            'total_cost' => 1000,
            'payment_status' => 'paid',
            'created_at' => $at,
            'updated_at' => $at,
        ]);

        $this->movement($ingredient->id, 'sale', -5, $at);
        $this->movement($ingredient->id, 'purchase', 100, $at);
        $this->movement($ingredient->id, 'adjustment_decrease', -3, $at);
        $this->movement($ingredient->id, 'adjustment_increase', 7, $at);

        $widget = new TopBahanBakuWidget();
        $widget->from = $at->toDateString();
        $widget->until = $at->toDateString();

        $data = $this->invokeData($widget);

        $this->assertSame([500.0], array_map('floatval', $data['datasets'][0]['data']));
        $this->assertSame(['Bahan Uji (kg)'], $data['labels']);
    }

    public function test_pemakaian_bahan_baku_uses_only_sale_movements(): void
    {
        $ingredient = Ingredient::factory()->create(['name' => 'Bahan Uji', 'unit' => 'kg', 'batch_mode' => 'fefo']);
        $at = now()->subDay();

        $this->movement($ingredient->id, 'sale', -4, $at);
        $this->movement($ingredient->id, 'sale', -1, $at);
        $this->movement($ingredient->id, 'purchase', 50, $at);
        $this->movement($ingredient->id, 'adjustment_decrease', -2, $at);

        $widget = new PemakaianBahanBakuWidget();
        $widget->from = $at->toDateString();
        $widget->until = $at->toDateString();
        $widget->filter = 'Bahan Uji';

        $data = $this->invokeData($widget);

        $this->assertSame([5.0], array_map('floatval', $data['datasets'][0]['data']));
    }
}
