<?php

namespace App\Filament\Resources\MenuResource\Pages;

use App\Filament\Resources\MenuResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMenu extends CreateRecord
{
    protected static string $resource = MenuResource::class;

    // Simpan sementara data bahan dari Repeater
    protected array $menuIngredientsData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ambil data repeater sebelum dikirim ke Menu::create()
        $this->menuIngredientsData = $data['menu_ingredients_create'] ?? [];
        unset($data['menu_ingredients_create']);

        return $data;
    }

    protected function afterCreate(): void
    {
        if (empty($this->menuIngredientsData)) {
            return;
        }

        foreach ($this->menuIngredientsData as $item) {
            if (empty($item['ingredient_id']) || empty($item['quantity_used'])) {
                continue;
            }

            $this->record->menuIngredients()->create([
                'ingredient_id' => $item['ingredient_id'],
                'quantity_used' => $item['quantity_used'],
            ]);
        }

        // Sinkronkan flag is_stock_calculated berdasarkan ada/tidaknya resep
        $this->record->refreshStockCalculatedFlag();
    }
}
