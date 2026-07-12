<?php

namespace App\Filament\Resources\PromotionResource\Tables;

use App\Models\Promotion;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PromotionTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Promosi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Promotion::TYPE_PERCENTAGE => 'success',
                        Promotion::TYPE_FIXED_AMOUNT => 'info',
                        Promotion::TYPE_BUY_X_GET_Y => 'warning',
                        Promotion::TYPE_BUNDLE => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('discount_value')
                    ->label('Diskon')
                    ->formatStateUsing(function (Promotion $record): string {
                        if ($record->type === Promotion::TYPE_PERCENTAGE) {
                            return number_format((float) $record->discount_value, 2).'%';
                        }

                        return 'Rp'.number_format((float) $record->discount_value, 0, ',', '.');
                    }),
                TextColumn::make('start_date')
                    ->label('Tgl Mulai')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Tgl Berakhir')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Promotion::STATUS_SCHEDULED => 'warning',
                        Promotion::STATUS_ACTIVE => 'success',
                        Promotion::STATUS_INACTIVE => 'danger',
                        Promotion::STATUS_EXPIRED => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('usage_count')
                    ->label('Dipakai')
                    ->formatStateUsing(function (Promotion $record): string {
                        if ($record->usage_limit !== null) {
                            return $record->usage_count.' / '.$record->usage_limit;
                        }

                        return (string) $record->usage_count;
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        Promotion::STATUS_SCHEDULED => 'Terjadwal',
                        Promotion::STATUS_ACTIVE => 'Aktif',
                        Promotion::STATUS_INACTIVE => 'Tidak Aktif',
                        Promotion::STATUS_EXPIRED => 'Kadaluwarsa',
                    ]),
                SelectFilter::make('type')
                    ->options([
                        Promotion::TYPE_PERCENTAGE => 'Persen',
                        Promotion::TYPE_FIXED_AMOUNT => 'Nominal',
                        Promotion::TYPE_BUY_X_GET_Y => 'Beli X Dapat Y',
                        Promotion::TYPE_BUNDLE => 'Bundle',
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()->modal(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
