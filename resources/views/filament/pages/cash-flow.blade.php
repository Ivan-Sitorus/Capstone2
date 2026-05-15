<x-filament-panels::page>

    @php $period = $this->period; @endphp

    <div style="display:flex;flex-direction:column;gap:26px;">

        {{-- Period tabs --}}
        <x-filament::tabs>
            <x-filament::tabs.item
                :active="$period === 'day'"
                icon="heroicon-o-calendar-days"
                wire:click="$set('period', 'day')"
            >
                Hari Ini
            </x-filament::tabs.item>
            <x-filament::tabs.item
                :active="$period === 'week'"
                icon="heroicon-o-calendar"
                wire:click="$set('period', 'week')"
            >
                Minggu Ini
            </x-filament::tabs.item>
            <x-filament::tabs.item
                :active="$period === 'month'"
                icon="heroicon-o-calendar"
                wire:click="$set('period', 'month')"
            >
                Bulan Ini
            </x-filament::tabs.item>
            <x-filament::tabs.item
                :active="$period === 'year'"
                icon="heroicon-o-calendar"
                wire:click="$set('period', 'year')"
            >
                Tahun Ini
            </x-filament::tabs.item>
            <x-filament::tabs.item
                :active="$period === 'all_time'"
                icon="heroicon-o-globe-alt"
                wire:click="$set('period', 'all_time')"
            >
                Semua Waktu
            </x-filament::tabs.item>
        </x-filament::tabs>

        {{-- Stats Widget --}}
        @livewire(\App\Filament\Widgets\CashFlowStatsWidget::class, ['period' => $this->period])

        {{-- Chart Widget --}}
        @livewire(\App\Filament\Widgets\CashFlowChartWidget::class, ['period' => $this->period])

    </div>

</x-filament-panels::page>
