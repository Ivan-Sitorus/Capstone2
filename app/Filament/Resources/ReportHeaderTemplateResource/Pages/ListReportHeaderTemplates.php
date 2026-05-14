<?php

namespace App\Filament\Resources\ReportHeaderTemplateResource\Pages;

use App\Filament\Resources\ReportHeaderTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReportHeaderTemplates extends ListRecords
{
    protected static string $resource = ReportHeaderTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
