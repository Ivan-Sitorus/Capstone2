<?php

namespace App\Filament\Resources\CategoryResource\Forms;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Kategori Menu')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(100),
        ]);
    }
}
