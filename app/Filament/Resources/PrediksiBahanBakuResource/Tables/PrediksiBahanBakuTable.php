<?php

namespace App\Filament\Resources\PrediksiBahanBakuResource\Tables;

use App\Filament\Resources\PrediksiBahanBakuResource;
use App\Filament\Tables\DataminingRunTable;
use Filament\Tables\Table;

class PrediksiBahanBakuTable
{
    public static function configure(Table $table): Table
    {
        return DataminingRunTable::configure(
            $table,
            PrediksiBahanBakuResource::class,
            'Jumlah Bahan Baku',
            'summary_table',
        );
    }
}
