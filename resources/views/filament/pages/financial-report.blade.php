<x-filament-panels::page>

    <div style="display:flex;flex-direction:column;gap:24px;">

        {{-- Info card --}}
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
            <div class="flex items-start gap-3">
                <x-heroicon-o-information-circle class="w-5 h-5 text-blue-500 mt-0.5 shrink-0"/>
                <div>
                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Financial Report Generator</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Generate financial reports by selecting a date range, report type, and aggregation level.
                        Use the <span class="font-medium text-gray-700 dark:text-gray-300">Simple</span> type for
                        basic income/expense summaries, <span class="font-medium text-gray-700 dark:text-gray-300">Rigid</span>
                        for structured recurring reports, or <span class="font-medium text-gray-700 dark:text-gray-300">Custom</span>
                        to filter by specific categories.
                    </p>
                </div>
            </div>
        </div>

        {{-- Template load dropdown --}}
        @php $templates = $this->getTemplates(); @endphp
        @if($templates->isNotEmpty())
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Load Template</label>
                <select
                    wire:model.change="selectedTemplateId"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-3 py-2 text-sm text-gray-700 dark:text-gray-200 shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                >
                    <option value="">— Select a template —</option>
                    @foreach($templates as $template)
                        <option value="{{ $template->id }}">
                            {{ $template->name }} ({{ $template->type }})
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        {{-- Main form --}}
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6">
            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100 mb-4">Report Configuration</h3>
            {{-- $this->form already renders a <form> tag via InteractsWithForms --}}
            {{ $this->form }}
        </div>

        {{-- Report Results --}}
        @if($hasResult && $reportResult)
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Report Results</h3>
                    <span class="text-xs text-gray-500">
                        Generated: {{ $reportResult['generated_at'] }}
                    </span>
                </div>

                {{-- Summary cards --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="rounded-lg bg-gray-50 dark:bg-gray-700/50 p-4">
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Report Type</p>
                        <p class="text-lg font-bold text-gray-800 dark:text-gray-100 mt-1 capitalize">
                            {{ $reportResult['type'] }}
                        </p>
                    </div>
                    <div class="rounded-lg bg-gray-50 dark:bg-gray-700/50 p-4">
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Period</p>
                        <p class="text-lg font-bold text-gray-800 dark:text-gray-100 mt-1">
                            {{ $reportResult['date_start'] }} → {{ $reportResult['date_end'] }}
                        </p>
                    </div>
                    <div class="rounded-lg bg-gray-50 dark:bg-gray-700/50 p-4">
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Aggregation</p>
                        <p class="text-lg font-bold text-gray-800 dark:text-gray-100 mt-1 capitalize">
                            {{ $reportResult['aggregation'] }}
                        </p>
                    </div>
                    <div class="rounded-lg bg-gray-50 dark:bg-gray-700/50 p-4">
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Categories</p>
                        <p class="text-lg font-bold text-gray-800 dark:text-gray-100 mt-1">
                            @if(!empty($reportResult['categories']))
                                {{ count($reportResult['categories']) }} selected
                            @else
                                <span class="text-gray-400">All</span>
                            @endif
                        </p>
                    </div>
                </div>

                {{-- Stub notice --}}
                <div class="rounded-lg border border-dashed border-yellow-300 dark:border-yellow-600 bg-yellow-50 dark:bg-yellow-900/20 p-4">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-beaker class="w-5 h-5 text-yellow-500 shrink-0"/>
                        <div>
                            <p class="text-sm font-medium text-yellow-700 dark:text-yellow-300">Stub Implementation</p>
                            <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-0.5">
                                {{ $reportResult['summary'] }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Placeholder for future report data tables/charts --}}
                <div class="mt-6 rounded-lg border border-dashed border-gray-300 dark:border-gray-600 p-8 text-center">
                    <x-heroicon-o-document-chart-bar class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-3"/>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Report data tables, charts, and export options will be rendered here
                        once the report services (Tasks 17-19) are implemented.
                    </p>
                </div>
            </div>
        @endif

    </div>

</x-filament-panels::page>
