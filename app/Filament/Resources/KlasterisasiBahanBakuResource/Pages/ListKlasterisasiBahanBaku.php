<?php

namespace App\Filament\Resources\KlasterisasiBahanBakuResource\Pages;

use App\Filament\Resources\KlasterisasiBahanBakuResource;
use App\Services\DataMiningRunner;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;

class ListKlasterisasiBahanBaku extends ListRecords
{
    protected static string $resource = KlasterisasiBahanBakuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('jalankan')
                ->label('Buat Klasterisasi Bahan Baku')
                ->icon('heroicon-o-sparkles')
                ->color('primary')
                ->modalHeading('Buat Klasterisasi Bahan Baku')
                ->form([
                    DatePicker::make('date_from')
                        ->label('Dari Tanggal')
                        ->required()
                        ->native(true)
                        ->displayFormat('d M Y')
                        ->maxDate(now()),
                    DatePicker::make('date_to')
                        ->label('Sampai Tanggal')
                        ->required()
                        ->native(true)
                        ->displayFormat('d M Y')
                        ->after('date_from')
                        ->maxDate(now()),
                ])
                ->action(function (array $data): void {
                    app(DataMiningRunner::class)->dispatch(
                        KlasterisasiBahanBakuResource::TYPE,
                        $data['date_from'],
                        $data['date_to'],
                    );
                })
                ->successNotificationTitle('Data mining dijalankan — hasil akan muncul otomatis.'),
        ];
    }
}
