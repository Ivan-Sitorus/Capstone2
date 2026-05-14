<?php

namespace App\Filament\Resources\TransaksiKeuanganResource\Pages;

use App\Filament\Resources\TransaksiKeuanganResource;
use App\Models\Expense;
use App\Models\Income;
use App\Models\TransaksiKeuangan;
use Filament\Resources\Pages\CreateRecord;

class CreateTransaksiKeuangan extends CreateRecord
{
    protected static string $resource = TransaksiKeuanganResource::class;

    protected function handleRecordCreation(array $data): TransaksiKeuangan
    {
        $type = $data['transaction_type'];
        unset($data['transaction_type']);

        if ($type === 'pemasukan') {
            Income::create($data);
        } else {
            Expense::create($data);
        }

        return new TransaksiKeuangan;
    }
}
