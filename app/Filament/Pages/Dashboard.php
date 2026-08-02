<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DashboardStatsWidget;
use App\Filament\Widgets\PemakaianBahanBakuWidget;
use App\Filament\Widgets\PenjualanChartWidget;
use App\Filament\Widgets\TopBahanBakuWidget;
use App\Filament\Widgets\TopMenuWidget;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Schema;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function mount(): void
    {
        $this->mountHasFilters();

        if (empty($this->filters['from'])) {
            $this->filters['from'] = now()->subDays(6)->toDateString();
        }
        if (empty($this->filters['until'])) {
            $this->filters['until'] = now()->toDateString();
        }
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                DatePicker::make('from')
                    ->label('Dari')
                    ->native(false)
                    ->maxDate(now()),
                DatePicker::make('until')
                    ->label('Sampai')
                    ->native(false)
                    ->maxDate(now()),
            ]);
    }

    public function updatedFilters(): void
    {
        $this->dispatch('dashboard-filters-changed',
            from: $this->filters['from'] ?? null,
            until: $this->filters['until'] ?? null,
        );
    }

    public function getWidgets(): array
    {
        return [
            DashboardStatsWidget::class,
            PenjualanChartWidget::class,
            PemakaianBahanBakuWidget::class,
            TopMenuWidget::class,
            TopBahanBakuWidget::class,
        ];
    }

    public function getColumns(): int | array
    {
        return 4;
    }
}
