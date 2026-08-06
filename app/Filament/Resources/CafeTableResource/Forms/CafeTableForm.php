<?php

namespace App\Filament\Resources\CafeTableResource\Forms;

use App\Filament\Forms\Components\NumericInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CafeTableForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            NumericInput::apply(TextInput::make('table_number'), maxDigits: 9)
                ->label('Nomor Meja')
                ->required()
                ->minValue(1)
                ->unique(ignoreRecord: true),
        ]);
    }
}
