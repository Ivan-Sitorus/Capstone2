<x-filament-panels::page>

    @php $period = $this->period; @endphp

    <div style="display:flex;flex-direction:column;gap:26px;">

        {{-- Period tabs --}}
        <x-filament.tab-navigation
            :tabs="[
                ['key' => 'day',      'label' => 'Hari Ini',    'icon' => 'heroicon-o-calendar-days'],
                ['key' => 'week',     'label' => 'Minggu Ini',   'icon' => 'heroicon-o-calendar'],
                ['key' => 'month',    'label' => 'Bulan Ini',    'icon' => 'heroicon-o-calendar'],
                ['key' => 'year',     'label' => 'Tahun Ini',    'icon' => 'heroicon-o-calendar'],
                ['key' => 'all_time', 'label' => 'Semua Waktu',  'icon' => 'heroicon-o-globe-alt'],
            ]"
            :active="$period"
            property="period"
        />

        {{-- Stats Widget --}}
        @livewire(\App\Filament\Widgets\CashFlowStatsWidget::class, ['period' => $this->period])

        {{-- Chart Widget --}}
        @livewire(\App\Filament\Widgets\CashFlowChartWidget::class, ['period' => $this->period])

        {{-- Unexpected Transaction Widget --}}
        @livewire(\App\Filament\Widgets\UnexpectedTransactionWidget::class)

    </div>

</x-filament-panels::page>
