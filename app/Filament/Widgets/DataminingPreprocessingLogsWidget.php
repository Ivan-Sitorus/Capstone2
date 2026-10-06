<?php

namespace App\Filament\Widgets;

use App\Models\DataminingRun;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class DataminingPreprocessingLogsWidget extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    public ?int $runId = null;

    protected function getTableHeading(): ?string
    {
        return 'Tahapan Preprocessing';
    }

    public function table(Table $table): Table
    {
        return $table
            ->records(function (): array {
                $run = DataminingRun::find($this->runId);
                $logs = $run?->payload['preprocessing_logs'] ?? [];

                return collect($logs)
                    ->map(fn (array $row, int $i): array => ['__key' => $i] + $row)
                    ->all();
            })
            ->columns([
                TextColumn::make('tahap')->label('Tahap'),
                TextColumn::make('detail')->label('Detail')->wrap(),
            ]);
    }
}
