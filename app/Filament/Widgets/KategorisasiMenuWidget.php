<?php

namespace App\Filament\Widgets;

use App\Models\DataminingRun;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class KategorisasiMenuWidget extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    public ?int $runId = null;

    protected function getTableHeading(): ?string
    {
        return 'Kategorisasi Penjualan Menu';
    }

    public function table(Table $table): Table
    {
        return $table
            ->records(function (): array {
                $run = DataminingRun::find($this->runId);
                $rows = $run?->payload['kategorisasi_rows'] ?? [];

                return collect($rows)
                    ->map(fn (array $row, int $i): array => ['__key' => $i] + $row)
                    ->all();
            })
            ->columns([
                TextColumn::make('Nama Item')->label('Menu')->searchable(),
                TextColumn::make('Kategori')->label('Kategori')->badge(),
                TextColumn::make('Klaster')->label('Klaster')->badge()->color('primary'),
                TextColumn::make('Total_Jumlah')->label('Total Jumlah')->numeric(),
                TextColumn::make('Total_Keuntungan')->label('Total Keuntungan')->formatStateUsing(fn ($state) => 'Rp'.number_format((float) $state, 0, ',', '.')),
            ]);
    }
}
