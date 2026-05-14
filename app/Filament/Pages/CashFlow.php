<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Livewire\Attributes\On;

class CashFlow extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|\UnitEnum|null $navigationGroup = 'Detail Keuangan';

    protected static ?string $navigationLabel = 'Arus Kas';

    protected static ?string $title = 'Arus Kas';

    protected static ?int $navigationSort = 0;

    protected string $view = 'filament.pages.cash-flow';

    public string $period = 'day';

    public ?string $date_start = null;

    public ?string $date_end = null;

    public function mount(): void
    {
        $this->period = 'day';
        $this->date_start = null;
        $this->date_end = null;
    }

    public function updatedPeriod(): void
    {
        $this->date_start = null;
        $this->date_end = null;
        $this->dispatch('cashflow-period-changed', period: $this->period);
    }

    #[On('cashflow-date-changed')]
    public function updateDateRange(string $start, string $end): void
    {
        $this->date_start = $start;
        $this->date_end = $end;
        $this->period = 'custom';
    }

    public function getTitle(): string
    {
        return 'Arus Kas';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
