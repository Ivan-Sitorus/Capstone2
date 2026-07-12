<?php

namespace App\Filament\Resources\CafeTableResource\Forms;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CafeTableForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('table_number')
                ->label('Nomor Meja')
                ->required()
                ->numeric()
                ->minValue(1)
                ->maxValue(99)
                ->unique(ignoreRecord: true),
        ]);
    }
}
