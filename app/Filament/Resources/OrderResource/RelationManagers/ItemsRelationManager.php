<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Support\Formatter;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Item Pesanan';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('menu.name')
                    ->label('Menu'),
                TextColumn::make('quantity')
                    ->label('Qty'),
                TextColumn::make('unit_price')
                    ->label('Harga Satuan')
                    ->formatStateUsing(fn ($state) => Formatter::rupiah($state)),
                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->formatStateUsing(fn ($state) => Formatter::rupiah($state)),
            ])
            ->paginated(false);
    }
}
