<?php

namespace App\Filament\Resources\TransaksiKeuanganResource\Pages;

use App\Filament\Resources\TransaksiKeuanganResource;
use App\Models\Expense;
use App\Models\Income;
use App\Models\TransaksiKeuangan;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTransaksiKeuangan extends EditRecord
{
    protected static string $resource = TransaksiKeuanganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate($record, array $data): TransaksiKeuangan
    {
        $type = $data['transaction_type'];
        unset($data['transaction_type']);

        $originalId = abs($record->id);

        if ($record->id > 0) {
            Income::findOrFail($originalId)->update($data);
        } else {
            Expense::findOrFail($originalId)->update($data);
        }

        return $record;
    }
}
