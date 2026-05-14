<?php

namespace App\Filament\Resources\ReportHeaderTemplateResource\Pages;

use App\Filament\Resources\ReportHeaderTemplateResource;
use App\Models\ReportHeaderTemplate;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReportHeaderTemplate extends EditRecord
{
    protected static string $resource = ReportHeaderTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        if ($this->record->is_default) {
            ReportHeaderTemplate::where('id', '!=', $this->record->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }
    }
}
