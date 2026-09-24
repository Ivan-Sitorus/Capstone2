<?php

namespace App\Filament\Resources\PrediksiMenuResource\Pages;

use App\Filament\Resources\PrediksiMenuResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewPrediksiMenu extends ViewRecord
{
    protected static string $resource = PrediksiMenuResource::class;

    public function getTitle(): string
    {
        return 'Detail Prediksi Menu';
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Ringkasan Run')
                ->columns(4)
                ->schema([
                    TextEntry::make('created_at')
                        ->label('Waktu Run')
                        ->dateTime('d M Y, H:i:s'),
                    TextEntry::make('status')
                        ->label('Status')
                        ->badge()
                        ->formatStateUsing(fn (?string $state): string => match ($state) {
                            'completed' => 'Selesai',
                            'failed' => 'Gagal',
                            'running' => 'Diproses',
                            default => (string) $state,
                        })
                        ->color(fn (?string $state): string => match ($state) {
                            'completed' => 'success',
                            'failed' => 'danger',
                            'running' => 'warning',
                            default => 'gray',
                        }),
                    TextEntry::make('rentang')
                        ->label('Rentang Data')
                        ->state(fn (): string => ($this->record->parameters['date_from'] ?? '-') . ' s/d ' . ($this->record->parameters['date_to'] ?? '-')),
                    TextEntry::make('error')
                        ->label('Error')
                        ->default('-')
                        ->columnSpanFull(),
                ]),
            Grid::make()->schema(fn (): array => $this->getWidgetsSchemaComponents(
                PrediksiMenuResource::getResultWidgets(),
                ['runId' => $this->record->id],
            )),
        ]);
    }
}
