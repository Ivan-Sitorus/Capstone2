<x-filament-panels::page>

    @php $period = $this->period; @endphp

    <div style="display:flex;flex-direction:column;gap:26px;">

        {{-- Period tabs --}}
        <x-filament::tabs label="Periode" contained>
            <x-filament::tabs.item :active="$period === 'day'" wire:click="$set('period','day')">
                Hari Ini
            </x-filament::tabs.item>
            <x-filament::tabs.item :active="$period === 'month'" wire:click="$set('period','month')">
                Bulan Ini
            </x-filament::tabs.item>
            <x-filament::tabs.item :active="$period === 'year'" wire:click="$set('period','year')">
                Tahun Ini
            </x-filament::tabs.item>
            <x-filament::tabs.item :active="$period === 'all_time'" wire:click="$set('period','all_time')">
                Semua Waktu
            </x-filament::tabs.item>
        </x-filament::tabs>

        {{-- Stats Widget --}}
        @livewire(\App\Filament\Widgets\CashFlowStatsWidget::class, ['period' => $this->period])

        {{-- Chart Widget --}}
        @livewire(\App\Filament\Widgets\CashFlowChartWidget::class, ['period' => $this->period])

        {{-- Unexpected Transaction Widget --}}
        @livewire(\App\Filament\Widgets\UnexpectedTransactionWidget::class)

    </div>

</x-filament-panels::page>
