<?php

namespace App\Filament\Resources\StockResource\Pages;

use App\Filament\Resources\StockResource;
use App\Filament\Resources\StockResource\Tables\StockHistoryTable;
use App\Models\Ingredient;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;

class ViewStockHistory extends ListRecords
{
    protected static string $resource = StockResource::class;

    protected static ?string $breadcrumb = 'Riwayat Stok';

    public ?Ingredient $ingredient = null;

    public function mount(): void
    {
        $this->ingredient = Ingredient::findOrFail(request()->route('record'));
        parent::mount();
    }

    public function getTitle(): string
    {
        return 'Riwayat Stok: ' . ($this->ingredient?->name ?? '');
    }

    public function table(Table $table): Table
    {
        return StockHistoryTable::configure($table, $this->ingredient);
    }
}
