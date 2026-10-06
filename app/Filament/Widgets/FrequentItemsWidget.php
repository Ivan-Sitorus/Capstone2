<?php

namespace App\Filament\Widgets;

use App\Models\DataminingRun;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class FrequentItemsWidget extends TableWidget
{
    protected int | string | array $columnSpan = 1;

    public ?int $runId = null;

    protected function getTableHeading(): ?string
    {
        return 'Frequent 1-Itemsets';
    }

    public function table(Table $table): Table
    {
        return $table
            ->records(function (): array {
                $run = DataminingRun::find($this->runId);
                $rows = $run?->payload['freq_1_itemsets'] ?? [];

                return collect($rows)
                    ->map(fn (array $row, int $i): array => ['__key' => $i] + $row)
                    ->all();
            })
            ->columns([
                TextColumn::make('item')->label('Menu')->searchable(),
                TextColumn::make('jumlah_kemunculan')->label('Jumlah Kemunculan')->numeric(),
                TextColumn::make('support')->label('Support')->formatStateUsing(fn ($state) => round((float) $state * 100, 2) . '%'),
            ]);
    }
}
