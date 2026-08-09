<?php

namespace App\Filament\Resources\MenuResource\RelationManagers;

use App\Filament\Forms\Components\NumericInput;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class IngredientsRelationManager extends RelationManager
{
    protected static string $relationship = "menuIngredients";

    protected static ?string $title = "Resep (Bahan)";

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make("ingredient_id")
                ->label("Bahan")
                ->relationship("ingredient", "name")
                ->required()
                ->searchable()
                ->preload()
                ->native(false)
                ->live()
                ->distinct()
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->name." (".($record->unit?->value ?? '').")"),
            NumericInput::quantity("quantity_used")
                ->label("Jumlah per Porsi")
                ->required()
                ->minValue(0.001)
                ->step(0.001),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('ingredient.batches'))
            ->columns([
                TextColumn::make("ingredient.name")
                    ->label("Bahan")
                    ->searchable()
                    ->sortable(),
                TextColumn::make("quantity_used")
                    ->label("Jumlah/Porsi")
                    ->sortable()
                    ->formatStateUsing(fn ($state, $record) => $state . " " . ($record->ingredient?->unit?->value ?? "")),
                TextColumn::make("ingredient.total_stock")
                    ->label("Stok Tersedia")
                    ->getStateUsing(function ($record) {
                        $stock = $record->ingredient->batches->sum('quantity');
                        return number_format($stock, 2);
                    })
                    ->suffix(fn ($record) => " ".($record->ingredient->unit?->value ?? ""))
                    ->badge()
                    ->color(function ($record) {
                        $stock = $record->ingredient->batches->sum('quantity');
                        return $stock < (float) ($record->ingredient->low_stock_threshold ?? 0)
                            ? "danger" : "success";
                    }),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (DeleteAction $action) {
                        /** @var \App\Models\Menu $menu */
                        $menu = $this->getOwnerRecord();
                        if ($menu->menuIngredients()->count() <= 1) {
                            Notification::make()
                                ->danger()
                                ->title("Bahan terakhir tidak dapat dihapus")
                                ->body("Setiap menu minimal harus memiliki satu bahan baku.")
                                ->send();
                            $action->cancel();
                        }
                    }),
            ])
            ->toolbarActions([]);
    }
}
