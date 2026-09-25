<?php

namespace App\Filament\Resources\UserResource\Forms;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama Lengkap')
                ->required()
                ->maxLength(255),
            TextInput::make('email')
                ->label('Email')
                ->email()
                ->required()
                ->maxLength(255),
            TextInput::make('password')
                ->label('Password')
                ->password()
                ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                ->dehydrated(fn ($state) => filled($state))
                ->required(fn (string $context): bool => $context === 'create')
                ->maxLength(255),
            Select::make('role')
                ->label('Role')
                ->options([
                    UserRole::Admin->value => 'Admin',
                    UserRole::Cashier->value => 'Kasir',
                ])
                ->required()
                ->native(false)
                ->default(UserRole::Cashier->value),
            Select::make('status')
                ->label('Status')
                ->options(UserStatus::options())
                ->required()
                ->native(false)
                ->default(UserStatus::Active->value),
        ]);
    }
}
