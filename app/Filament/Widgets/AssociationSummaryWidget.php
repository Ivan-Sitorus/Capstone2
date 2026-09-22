<?php

namespace App\Filament\Widgets;

use App\Models\DataminingRun;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class AssociationSummaryWidget extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getPollingInterval(): ?string
    {
        return '5s';
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Association Rules (Jika membeli X, maka membeli Y)')
            ->records(function () {
                $run = DataminingRun::latestCompleted('association');
                $rules = $run?->payload['rules'] ?? [];

                return collect($rules)
                    ->map(fn (array $row, int $i): array => ['__key' => $i] + $row)
                    ->all();
            })
            ->columns([
                TextColumn::make('menu_pertama')->label('Menu Pertama')->searchable(),
                TextColumn::make('menu_kedua')->label('Menu Kedua')->searchable(),
                TextColumn::make('support')->label('Support')->formatStateUsing(fn ($state) => round((float) $state * 100, 2) . '%'),
                TextColumn::make('confidence')->label('Confidence')->formatStateUsing(fn ($state) => round((float) $state * 100, 2) . '%'),
                TextColumn::make('lift')->label('Lift')->numeric()->color(fn ($state) => (float) $state >= 1.5 ? 'success' : 'warning'),
            ]);
    }
}
