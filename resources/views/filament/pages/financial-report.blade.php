<x-filament-panels::page>
    <x-filament::tabs>
        <x-filament::tabs.item
            :active="$activeTab === 'generated'"
            wire:click="$set('activeTab', 'generated')"
        >
            Generated Reports
        </x-filament::tabs.item>
        <x-filament::tabs.item
            :active="$activeTab === 'templates'"
            wire:click="$set('activeTab', 'templates')"
        >
            Saved Templates
        </x-filament::tabs.item>
    </x-filament::tabs>

    <div class="space-y-6">
        {{ $this->table }}
    </div>
</x-filament-panels::page>
