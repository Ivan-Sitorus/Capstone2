<?php

namespace App\Filament\Resources\AsosiatifMenuResource\Pages;

use App\Filament\Resources\AsosiatifMenuResource;
use App\Services\DataMiningRunner;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;

class ListAsosiatifMenu extends ListRecords
{
    protected static string $resource = AsosiatifMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('jalankan')
                ->label('Buat Asosiasi Menu')
                ->icon('heroicon-o-sparkles')
                ->color('primary')
                ->modalHeading('Buat Asosiasi Menu')
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
                        AsosiatifMenuResource::TYPE,
                        $data['date_from'],
                        $data['date_to'],
                    );
                })
                ->successNotificationTitle('Data mining dijalankan — hasil akan muncul otomatis.'),
        ];
    }
}
