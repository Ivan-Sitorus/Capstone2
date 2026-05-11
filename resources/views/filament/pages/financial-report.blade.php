<x-filament-panels::page>

    <x-filament::tabs>
        <x-filament::tabs.item
            :active="$activeTab === 'generated'"
            icon="heroicon-o-document-text"
            wire:click="$set('activeTab', 'generated')"
        >
            Generated Reports
        </x-filament::tabs.item>
        <x-filament::tabs.item
            :active="$activeTab === 'templates'"
            icon="heroicon-o-bookmark"
            wire:click="$set('activeTab', 'templates')"
        >
            Saved Templates
        </x-filament::tabs.item>
    </x-filament::tabs>

    <div class="pt-6">
        {{ $this->table }}
    </div>

</x-filament-panels::page>
