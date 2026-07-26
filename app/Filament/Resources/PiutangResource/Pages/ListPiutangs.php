<?php

namespace App\Filament\Resources\PiutangResource\Pages;

use App\Filament\Resources\PiutangResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPiutangs extends ListRecords
{
    protected static string $resource = PiutangResource::class;

    public function getTitle(): string
    {
        return 'Piutang';
    }

    public function getBreadcrumb(): ?string
    {
        return 'Piutang';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Buat Piutang')
                ->icon('heroicon-o-plus'),
        ];
    }
}
