<?php

namespace App\Filament\Resources\PrediksiMenuResource\Pages;

use App\Filament\Resources\PrediksiMenuResource;
use App\Services\DataMiningRunner;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;

class ListPrediksiMenu extends ListRecords
{
    protected static string $resource = PrediksiMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('jalankan')
                ->label('Buat Prediksi Menu')
                ->icon('heroicon-o-sparkles')
                ->color('primary')
                ->modalHeading('Buat Prediksi Menu')
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
                        PrediksiMenuResource::TYPE,
                        $data['date_from'],
                        $data['date_to'],
                    );
                })
                ->successNotificationTitle('Data mining dijalankan — hasil akan muncul otomatis.'),
        ];
    }
}
