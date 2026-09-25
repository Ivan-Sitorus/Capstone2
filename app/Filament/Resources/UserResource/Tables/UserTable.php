<?php

namespace App\Filament\Resources\UserResource\Tables;

use App\Enums\UserRole;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->color(fn (UserRole $state): string => match ($state) {
                        UserRole::Admin => 'success',
                        UserRole::Cashier => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (UserRole $state): string => $state->label())
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Role')
                    ->options([
                        UserRole::Admin->value => 'Admin',
                        UserRole::Cashier->value => 'Kasir',
                    ]),
                Filter::make('created_range')
                    ->label('Rentang Tanggal Daftar')
                    ->form([
                        DatePicker::make('created_from')->label('Dari'),
                        DatePicker::make('created_until')->label('Sampai'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['created_from'], fn (Builder $q, $date): Builder => $q->whereDate('created_at', '>=', $date))
                        ->when($data['created_until'], fn (Builder $q, $date): Builder => $q->whereDate('created_at', '<=', $date))
                    ),
            ])
            ->recordActions([
                EditAction::make()->modal(),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
