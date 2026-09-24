<?php

namespace App\Filament\Resources\KlasterisasiBahanBakuResource\Tables;

use App\Filament\Resources\KlasterisasiBahanBakuResource;
use App\Filament\Tables\DataminingRunTable;
use Filament\Tables\Table;

class KlasterisasiBahanBakuTable
{
    public static function configure(Table $table): Table
    {
        return DataminingRunTable::configure(
            $table,
            KlasterisasiBahanBakuResource::class,
            'Jumlah Bahan Baku',
            'table_rows',
        );
    }
}
