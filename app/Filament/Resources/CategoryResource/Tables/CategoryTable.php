<?php

namespace App\Filament\Resources\CategoryResource\Tables;

use App\Models\Category;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoryTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Cari Nama Kategori')
            ->columns([
                TextColumn::make('name')
                    ->label('Kategori Menu')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                TextColumn::make('menus_count')
                    ->label('Jumlah Menu')
                    ->counts('menus')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make()->modal(),
                DeleteAction::make()
                    ->before(function (DeleteAction $action, Category $record) {
                        if ($record->menus()->exists()) {
                            Notification::make()
                                ->danger()
                                ->title('Kategori tidak dapat dihapus')
                                ->body("Kategori '{$record->name}' masih memiliki {$record->menus()->count()} menu. Pindahkan atau hapus menu terlebih dahulu.")
                                ->send();

                            $action->cancel();
                        }
                    }),
            ]);
    }
}
