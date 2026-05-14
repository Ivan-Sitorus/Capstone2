<?php

namespace App\Filament\Resources\ReportHeaderTemplateResource\Pages;

use App\Filament\Resources\ReportHeaderTemplateResource;
use App\Models\ReportHeaderTemplate;
use Filament\Resources\Pages\CreateRecord;

class CreateReportHeaderTemplate extends CreateRecord
{
    protected static string $resource = ReportHeaderTemplateResource::class;

    protected function afterCreate(): void
    {
        if ($this->record->is_default) {
            ReportHeaderTemplate::where('id', '!=', $this->record->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }
    }
}
