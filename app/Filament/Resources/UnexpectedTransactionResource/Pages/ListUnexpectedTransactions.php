<?php

namespace App\Filament\Resources\UnexpectedTransactionResource\Pages;

use App\Filament\Resources\UnexpectedTransactionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUnexpectedTransactions extends ListRecords
{
    protected static string $resource = UnexpectedTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah Transaksi'),
        ];
    }
}
