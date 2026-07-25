<?php

namespace App\Filament\Resources\MenuResource\Tables;

use App\Models\Ingredient;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MenuTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query
                ->with('menuIngredients.ingredient.batches')
            )
            ->searchPlaceholder("Cari Nama Menu")
            ->defaultSort('name', 'asc')
            ->columns([
                TextColumn::make("name")
                    ->label("Nama Menu")
                    ->searchable()
                    ->sortable(),
                TextColumn::make("category.name")
                    ->label("Kategori Menu")
                    ->sortable()
                    ->searchable(),
                TextColumn::make("price")
                    ->label("Harga")
                    ->formatStateUsing(fn ($state) => "Rp".number_format($state, 0, ",", "."))
                    ->sortable(),
                TextColumn::make("student_price")
                    ->label("Diskon Mahasiswa")
                    ->formatStateUsing(fn ($state) => $state ? "Rp".number_format($state, 0, ",", ".") : "-")
                    ->sortable(),
                TextColumn::make("available_servings")
                    ->label("Sisa Jual")
                    ->getStateUsing(fn ($record) => $record->computeAvailableServings())
                    ->formatStateUsing(fn ($state) => $state === null ? "-" : number_format($state, 0, ",", "."))
                    ->color(fn ($state) => match (true) {
                        $state === null || $state > 10 => "success",
                        $state > 0 => "warning",
                        default => "danger",
                    }),
                TextColumn::make("status")
                    ->label("Status")
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'active' ? 'Aktif' : 'Nonaktif')
                    ->color(fn ($state) => $state === 'active' ? 'success' : 'danger'),
            ])
            ->filters([
                SelectFilter::make("category")
                    ->relationship("category", "name")
                    ->label("Kategori Menu")
                    ->placeholder("Semua"),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Aktif',
                        'inactive' => 'Nonaktif',
                    ]),
            ])
                    ->default('active'),
                SelectFilter::make("ingredient")
                    ->label("Bahan Baku")
                    ->placeholder("Semua")
                    ->options(Ingredient::pluck("name", "id"))
                    ->searchable()
                    ->query(fn (Builder $query, array $data) =>
                        $query->when($data["value"] ?? null, fn ($q, $id) =>
                            $q->whereHas("menuIngredients", fn ($q) =>
                                $q->where("ingredient_id", $id)
                            )
                        )
                    ),
            ])
            ->recordActions([
                EditAction::make()->modal()
                    ->before(function (EditAction $action, \App\Models\Menu $record) {
                        if (! $record->menuIngredients()->exists()) {
                            Notification::make()
                                ->warning()
                                ->title("Belum ada bahan baku")
                                ->body("Menu \"{$record->name}\" belum memiliki bahan baku. Tambahkan bahan baku terlebih dahulu agar stok dapat terdeduksi saat menu terjual.")
                                ->send();
                        }
                    }),
                Action::make('nonaktifkan')
                    ->label('Nonaktifkan')
                    ->icon('heroicon-o-archive-box')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Nonaktifkan Menu')
                    ->modalDescription('Menu akan dinonaktifkan dan tidak muncul di POS serta pelanggan. Data pesanan lama tetap aman.')
                    ->action(fn (\App\Models\Menu $record) => $record->update(['status' => 'inactive'])),
            ]);
    }
}
