<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
        <div>
            {{ $this->form }}
        </div>

        <div>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Pratinjau Struk
                </h3>

                <x-filament::button
                    wire:click="refreshPreview"
                    color="gray"
                    size="sm"
                >
                    ↻ Refresh Preview
                </x-filament::button>
            </div>

            <div class="overflow-hidden bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-800">
                @include('filament.pages.receipt-preview', ['data' => $this->previewData ?? []])
            </div>
        </div>
    </div>
</x-filament-panels::page>
