<x-filament-panels::page>
    <x-filament.tab-navigation
        :tabs="[
            ['key' => 'generated', 'label' => 'Generated Reports'],
            ['key' => 'templates', 'label' => 'Saved Templates'],
        ]"
        :active="$activeTab"
    />

    <div class="space-y-6">
        {{ $this->table }}
    </div>
</x-filament-panels::page>
