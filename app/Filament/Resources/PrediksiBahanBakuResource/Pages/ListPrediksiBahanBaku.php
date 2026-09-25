<?php

namespace App\Filament\Resources\PrediksiBahanBakuResource\Pages;

use App\Filament\Resources\PrediksiBahanBakuResource;
use App\Services\DataMiningRunner;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;

class ListPrediksiBahanBaku extends ListRecords
{
    protected static string $resource = PrediksiBahanBakuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('jalankan')
                ->label('Buat Prediksi Bahan Baku')
                ->icon('heroicon-o-sparkles')
                ->color('primary')
                ->modalHeading('Buat Prediksi Bahan Baku')
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
                        PrediksiBahanBakuResource::TYPE,
                        $data['date_from'],
                        $data['date_to'],
                    );
                })
                ->successNotificationTitle('Data mining dijalankan — hasil akan muncul otomatis.'),
        ];
    }
}
