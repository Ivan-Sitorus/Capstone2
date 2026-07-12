<?php

namespace App\Filament\Resources\MenuResource\Tables;

use App\Models\Ingredient;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MenuTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->searchPlaceholder("Cari Nama Menu")
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
                TextColumn::make("stock")
                    ->label("Sisa Jual")
                    ->formatStateUsing(fn ($state) => $state === null ? "-" : number_format($state, 0, ",", "."))
                    ->color(fn ($state) => match (true) {
                        $state === null || $state > 10 => "success",
                        $state > 0 => "warning",
                        default => "danger",
                    })
                    ->badge()
                    ->sortable(),
                TextColumn::make("is_available")
                    ->label("Tersedia")
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? "Tersedia" : "Tidak")
                    ->color(fn ($state) => $state ? "success" : "danger"),
            ])
            ->filters([
                SelectFilter::make("category")
                    ->relationship("category", "name")
                    ->label("Kategori Menu")
                    ->placeholder("Semua"),
                TernaryFilter::make("is_available")
                    ->label("Tersedia")
                    ->placeholder("Semua")
                    ->trueLabel("Tersedia")
                    ->falseLabel("Tidak Tersedia"),
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
                DeleteAction::make()
                    ->modalDescription("Apakah Anda yakin ingin melakukan ini? Seluruh data pesanan menu ini tetap aman dan tidak berubah."),
            ]);
    }
}
