<?php

namespace App\Filament\Resources\StaffSessionResource\Tables;

use App\Filament\Resources\StaffSessionResource;
use App\Models\StaffSession;
use App\Services\StaffSessionService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StaffSessionTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(
                StaffSession::with('user')
                    ->orderByDesc('started_at')
            )
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Role')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cashier' => 'Kasir',
                        'kitchen' => 'Dapur',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'cashier' => 'blue',
                        'kitchen' => 'orange',
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
                    ->getStateUsing(fn ($record) => app(StaffSessionService::class)->getOrderCount($record)),
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
                SelectFilter::make('type')
                    ->label('Role')
                    ->options([
                        'cashier' => 'Kasir',
                        'kitchen' => 'Dapur',
                    ]),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Detail')
                    ->icon(Heroicon::OutlinedEye)
                    ->url(fn (StaffSession $record) => StaffSessionResource::getUrl('view', ['record' => $record])),
            ])
            ->defaultSort('started_at', 'desc');
    }
}
