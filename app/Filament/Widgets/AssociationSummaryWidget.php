<?php

namespace App\Filament\Widgets;

use App\Filament\Tables\Components\ColumnInfoTooltip;
use App\Models\DataminingRun;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class AssociationSummaryWidget extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    public ?int $runId = null;

    protected function getPollingInterval(): ?string
    {
        return '5s';
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Association Rules (Jika membeli X, maka membeli Y)')
            ->records(function () {
                $run = DataminingRun::find($this->runId) ?? DataminingRun::latestCompleted('association');
                $rules = $run?->payload['rules'] ?? [];

                return collect($rules)
                    ->map(fn (array $row, int $i): array => ['__key' => $i] + $row)
                    ->all();
            })
            ->columns([
                TextColumn::make('menu_pertama')->label('Menu Pertama')->searchable(),
                TextColumn::make('menu_kedua')->label('Menu Kedua')->searchable(),
                TextColumn::make('support')->label(ColumnInfoTooltip::label('Support', 'Persentase transaksi yang memuat kedua menu. Makin besar makin sering muncul bersama.'))->formatStateUsing(fn ($state) => round((float) $state * 100, 2) . '%'),
                TextColumn::make('confidence')->label(ColumnInfoTooltip::label('Confidence', 'Peluang menu kedua dibeli bila menu pertama dibeli.'))->formatStateUsing(fn ($state) => round((float) $state * 100, 2) . '%'),
                TextColumn::make('lift')->label(ColumnInfoTooltip::label('Lift', 'Kekuatan keterkaitan. >1 berarti berasosiasi positif; makin tinggi makin kuat.'))->numeric()->color(fn ($state) => (float) $state >= 1.5 ? 'success' : 'warning'),
            ]);
    }
}
