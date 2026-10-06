<?php

namespace App\Filament\Resources\KlasterisasiMenuResource\Tables;

use App\Filament\Resources\KlasterisasiMenuResource;
use App\Filament\Tables\DataminingRunTable;
use Filament\Tables\Table;

class KlasterisasiMenuTable
{
    public static function configure(Table $table): Table
    {
        return DataminingRunTable::configure(
            $table,
            KlasterisasiMenuResource::class,
            'Jumlah Menu',
            'table_rows',
        );
    }
}
