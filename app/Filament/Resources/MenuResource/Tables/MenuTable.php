<?php

namespace App\Filament\Resources\MenuResource\Tables;

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
                    ->label("Sisa Jual")
                    ->getStateUsing(fn ($record) => $record->computeAvailableServings())
                    ->formatStateUsing(fn ($state) => $state === null ? "-" : number_format($state, 0, ",", "."))
                    ->color(fn ($state) => match (true) {
                        $state === null || $state > 10 => "success",
                        $state > 0 => "warning",
                        default => "danger",
                    }),
                TextColumn::make("status")
                    ->label(new \Illuminate\Support\HtmlString(
                        'Status <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 inline text-gray-400" title="Aktif: menu tampil di POS &amp; aplikasi pelanggan. Nonaktif: menu disembunyikan, tidak bisa dipesan."><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>'
                    ))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'active' ? 'Aktif' : 'Nonaktif')
                    ->color(fn ($state) => $state === 'active' ? 'success' : 'danger')
                    ->tooltip(fn ($state) => $state === 'active'
                        ? 'Aktif: menu tampil di POS & aplikasi pelanggan'
                        : 'Nonaktif: menu disembunyikan, tidak bisa dipesan')
                    ->headerTooltip('Status menu: Aktif = tampil di POS & pelanggan. Nonaktif = disembunyikan.')
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
                            $record->status === 'active' ? 'Nonaktifkan' : 'Aktifkan')
                        ->icon(fn (\App\Models\Menu $record) => 
                            $record->status === 'active' 
                                ? Heroicon::OutlinedArchiveBox 
                                : Heroicon::OutlinedCheckCircle)
                        ->color(fn (\App\Models\Menu $record) => 
                            $record->status === 'active' ? 'warning' : 'success')
                        ->requiresConfirmation()
                        ->modalHeading(fn (\App\Models\Menu $record) => 
                            $record->status === 'active' ? 'Nonaktifkan Menu' : 'Aktifkan Menu')
                        ->modalDescription(fn (\App\Models\Menu $record) => 
                            $record->status === 'active' 
                                ? 'Menu akan dinonaktifkan dan tidak muncul di POS serta pelanggan. Data pesanan lama tetap aman.'
                                : 'Menu akan diaktifkan kembali dan muncul di POS serta pelanggan.')
                        ->action(function (\App\Models\Menu $record) {
                            $status = $record->status === 'active' ? 'inactive' : 'active';
                            $record->update(['status' => $status]);
                            Notification::make()
                                ->success()
                                ->title($status === 'active' ? 'Menu diaktifkan' : 'Menu dinonaktifkan')
                                ->body("\"{$record->name}\" berhasil " . ($status === 'active' ? 'diaktifkan' : 'dinonaktifkan'))
                                ->send();
                        }),
                ])
                ->icon(Heroicon::OutlinedEllipsisVertical),
            ]);
    }
}
