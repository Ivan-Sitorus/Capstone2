<?php

namespace App\Filament\Resources\MenuResource\Tables;

use App\Enums\MenuStatus;
use App\Filament\Tables\Components\ColumnInfoTooltip;
use App\Models\Ingredient;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
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
                TextColumn::make("discounted_price")
                    ->label("Harga Diskon")
                    ->formatStateUsing(fn ($state) => $state ? "Rp".number_format($state, 0, ",", ".") : "-")
                    ->sortable(),
                TextColumn::make("available_servings")
                    ->label(ColumnInfoTooltip::label('Sisa Jual', "Sisa porsi yang bisa dibuat dari stok tersedia.\nMenu dengan bahan baku yang sama bisa saling mengurangi sisa jual."))
                    ->getStateUsing(fn ($record) => $record->computeAvailableServings())
                    ->formatStateUsing(fn ($state) => $state === null ? "-" : number_format($state, 0, ",", "."))
                    ->color(fn ($state) => match (true) {
                        $state === null || $state > 10 => "success",
                        $state > 0 => "warning",
                        default => "danger",
                    }),
                TextColumn::make("status")
                    ->label(ColumnInfoTooltip::label('Status', "Aktif: Menu tampil di kasir dan pelanggan, bisa dipesan.\nNonaktif: Menu tidak tampil di kasir dan pelanggan, tidak bisa dipesan."))
                    ->badge()
                    ->formatStateUsing(fn (MenuStatus $state) => $state === MenuStatus::Active ? 'Aktif' : 'Nonaktif')
                    ->color(fn (MenuStatus $state) => $state === MenuStatus::Active ? 'success' : 'danger')
                    ->tooltip(fn (MenuStatus $state) => $state === MenuStatus::Active
                        ? 'Aktif: menu tampil di POS & aplikasi pelanggan'
                        : 'Nonaktif: menu disembunyikan, tidak bisa dipesan'),
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
                ActionGroup::make([
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
                    Action::make('toggle_status')
                        ->label(fn (\App\Models\Menu $record) => 
                            $record->status === MenuStatus::Active ? 'Nonaktifkan' : 'Aktifkan')
                        ->icon(fn (\App\Models\Menu $record) => 
                            $record->status === MenuStatus::Active 
                                ? Heroicon::OutlinedArchiveBox 
                                : Heroicon::OutlinedCheckCircle)
                        ->color(fn (\App\Models\Menu $record) => 
                            $record->status === MenuStatus::Active ? 'warning' : 'success')
                        ->requiresConfirmation()
                        ->modalHeading(fn (\App\Models\Menu $record) => 
                            $record->status === MenuStatus::Active ? 'Nonaktifkan Menu' : 'Aktifkan Menu')
                        ->modalDescription(fn (\App\Models\Menu $record) => 
                            $record->status === MenuStatus::Active 
                                ? 'Menu akan dinonaktifkan dan tidak muncul di POS serta pelanggan. Data pesanan lama tetap aman.'
                                : 'Menu akan diaktifkan kembali dan muncul di POS serta pelanggan.')
                        ->action(function (\App\Models\Menu $record) {
                            $status = $record->status === MenuStatus::Active ? MenuStatus::Inactive : MenuStatus::Active;
                            $record->update(['status' => $status]);
                            Notification::make()
                                ->success()
                                ->title($status === MenuStatus::Active ? 'Menu diaktifkan' : 'Menu dinonaktifkan')
                                ->body("\"{$record->name}\" berhasil " . ($status === MenuStatus::Active ? 'diaktifkan' : 'dinonaktifkan'))
                                ->send();
                        }),
                ])
                ->icon(Heroicon::OutlinedEllipsisVertical),
            ]);
    }
}
