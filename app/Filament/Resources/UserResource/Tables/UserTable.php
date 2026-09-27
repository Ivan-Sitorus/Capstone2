<?php

namespace App\Filament\Resources\UserResource\Tables;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Filament\Resources\UserResource\UserLockGuard;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
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
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (UserStatus $state): string => $state->color())
                    ->formatStateUsing(fn (UserStatus $state): string => $state->label())
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
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(UserStatus::options()),
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
                EditAction::make()->modal()
                    ->before(function (EditAction $action, User $record): void {
                        $actor = Filament::auth()->user();

                        if (! $actor instanceof User) {
                            return;
                        }

                        $data = $action->getData();

                        $rawRole = $data['role'] ?? null;
                        $newRole = $rawRole instanceof UserRole
                            ? $rawRole
                            : UserRole::tryFrom((string) $rawRole);

                        $rawStatus = $data['status'] ?? null;
                        $newStatus = $rawStatus instanceof UserStatus
                            ? $rawStatus
                            : UserStatus::tryFrom((string) $rawStatus);

                        $violation = UserLockGuard::updateViolation($actor, $record, $newRole, $newStatus);

                        if ($violation === null) {
                            return;
                        }

                        $operation = $newStatus === UserStatus::Inactive
                            ? UserLockGuard::OPERATION_DEACTIVATE
                            : UserLockGuard::OPERATION_DEMOTE;

                        Notification::make()
                            ->danger()
                            ->title(UserLockGuard::title($violation, $operation))
                            ->body(UserLockGuard::message($violation, $operation))
                            ->send();

                        $action->halt();
                    }),
                DeleteAction::make()
                    ->before(function (DeleteAction $action, User $record): void {
                        $actor = Filament::auth()->user();

                        if (! $actor instanceof User) {
                            return;
                        }

                        $violation = UserLockGuard::deleteViolation($actor, $record);

                        if ($violation === null) {
                            return;
                        }

                        Notification::make()
                            ->danger()
                            ->title(UserLockGuard::title($violation, UserLockGuard::OPERATION_DELETE))
                            ->body(UserLockGuard::message($violation, UserLockGuard::OPERATION_DELETE))
                            ->send();

                        $action->halt();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
