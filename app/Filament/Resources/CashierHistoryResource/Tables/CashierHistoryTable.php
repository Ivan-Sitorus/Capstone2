<?php

namespace App\Filament\Resources\CashierHistoryResource\Tables;

use App\Enums\UserRole;
use App\Filament\Resources\CashierHistoryResource;
use App\Models\CashierHistory;
use App\Services\CashierHistoryService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CashierHistoryTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(
                CashierHistory::with('user')
                    ->orderByDesc('started_at')
            )
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('user.role')
                    ->label('Role')
                    ->badge()
                    ->formatStateUsing(fn (UserRole $state): string => $state->label())
                    ->color(fn (UserRole $state): string => match ($state) {
                        UserRole::Admin => 'success',
                        UserRole::Cashier => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('started_at')
                    ->label('Waktu Masuk')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                TextColumn::make('ended_at')
                    ->label('Waktu Keluar')
                    ->formatStateUsing(fn ($state) => $state ? $state->format('d M Y, H:i') : '—')
                    ->sortable(),
                TextColumn::make('order_count')
                    ->label('Jumlah Pesanan')
                    ->getStateUsing(fn ($record) => app(CashierHistoryService::class)->getOrderCount($record)),
            ])
            ->filters([
                Filter::make('email')
                    ->schema([
                        TextInput::make('email')
                            ->label('Email')
                            ->placeholder('Cari email...'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(
                            $data['email'] ?? null,
                            fn (Builder $query, string $email): Builder => $query
                                ->whereHas('user', fn (Builder $q) => $q->where('email', 'ilike', "%{$email}%"))
                        )),
                Filter::make('started_at')
                    ->schema([
                        DatePicker::make('started_from')
                            ->label('Masuk dari')
                            ->native(false),
                        DatePicker::make('started_until')
                            ->label('Masuk sampai')
                            ->native(false),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(
                            $data['started_from'] ?? null,
                            fn (Builder $query, $date): Builder => $query->whereDate('started_at', '>=', $date)
                        )
                        ->when(
                            $data['started_until'] ?? null,
                            fn (Builder $query, $date): Builder => $query->whereDate('started_at', '<=', $date)
                        )),
                Filter::make('ended_at')
                    ->schema([
                        DatePicker::make('ended_from')
                            ->label('Keluar dari')
                            ->native(false),
                        DatePicker::make('ended_until')
                            ->label('Keluar sampai')
                            ->native(false),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(
                            $data['ended_from'] ?? null,
                            fn (Builder $query, $date): Builder => $query->whereDate('ended_at', '>=', $date)
                        )
                        ->when(
                            $data['ended_until'] ?? null,
                            fn (Builder $query, $date): Builder => $query->whereDate('ended_at', '<=', $date)
                        )),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Detail')
                    ->icon(Heroicon::OutlinedEye)
                    ->url(fn (CashierHistory $record) => CashierHistoryResource::getUrl('view', ['record' => $record])),
            ])
            ->defaultSort('started_at', 'desc');
    }
}
