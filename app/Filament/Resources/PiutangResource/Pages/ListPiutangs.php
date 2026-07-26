<?php

namespace App\Filament\Resources\PiutangResource\Pages;

use App\Filament\Resources\PiutangResource;
use Filament\Resources\Pages\ListRecords;

class ListPiutangs extends ListRecords
{
    protected static string $resource = PiutangResource::class;

    public function getTitle(): string
    {
        return 'Piutang';
    }

    protected function getBreadcrumb(): ?string
    {
        return 'Piutang';
    }
}
