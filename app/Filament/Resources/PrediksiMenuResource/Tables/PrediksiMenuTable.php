<?php

namespace App\Filament\Resources\PrediksiMenuResource\Tables;

use App\Filament\Resources\PrediksiMenuResource;
use App\Filament\Tables\DataminingRunTable;
use Filament\Tables\Table;

class PrediksiMenuTable
{
    public static function configure(Table $table): Table
    {
        return DataminingRunTable::configure(
            $table,
            PrediksiMenuResource::class,
            'Jumlah Menu',
            'summary_table',
        );
    }
}
