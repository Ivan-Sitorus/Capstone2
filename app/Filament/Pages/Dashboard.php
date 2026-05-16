<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\ToggleButtons;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Schema;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->schema([
                ToggleButtons::make('period')
                    ->options([
                        'today' => 'Hari Ini',
                        'this_week' => 'Minggu Ini',
                        'this_month' => 'Bulan Ini',
                    ])
                    ->default('today')
                    ->grouped()
                    ->inline(),
            ]);
    }

    public function getWidgets(): array
    {
        return [];
    }

    public function getColumns(): int | array
    {
        return [
            'default' => 4,
            'md' => 4,
            'xl' => 4,
        ];
    }
}
