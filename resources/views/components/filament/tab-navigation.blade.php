{{-- Reusable tab navigation bar for Filament pages --}}
@props(['tabs' => [], 'active' => '', 'property' => 'activeTab'])

<div {{ $attributes->merge(['class' => 'flex gap-0 border-b border-gray-200 dark:border-gray-700']) }}>
    @foreach($tabs as $tab)
        <button
            wire:click="$set('{{ $property }}', '{{ $tab['key'] }}')"
            @class([
                'flex items-center gap-1.5 px-4 py-2.5 text-sm font-medium border-b-2 transition-colors whitespace-nowrap',
                'border-primary-500 text-primary-600 dark:text-primary-400' => $active === $tab['key'],
                'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' => $active !== $tab['key'],
            ])
        >
            @if(!empty($tab['icon']))
                <x-dynamic-component :component="$tab['icon']" class="w-4 h-4"/>
            @endif
            {{ $tab['label'] }}
        </button>
    @endforeach
</div>
