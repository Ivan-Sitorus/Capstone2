<?php

namespace App\Filament\Resources\AsosiatifMenuResource\Tables;

use App\Filament\Resources\AsosiatifMenuResource;
use App\Filament\Tables\DataminingRunTable;
use Filament\Tables\Table;

class AsosiatifMenuTable
{
    public static function configure(Table $table): Table
    {
        return DataminingRunTable::configure(
            $table,
            AsosiatifMenuResource::class,
            'Jumlah Aturan',
            'rules',
        );
    }
}
