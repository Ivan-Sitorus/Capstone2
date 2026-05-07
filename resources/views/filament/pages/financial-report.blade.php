<x-filament-panels::page>
    <div class="flex gap-0 -mx-6 -mt-6 mb-6 border-b border-gray-200 dark:border-gray-700">
        <button wire:click="$set('activeTab', 'generated')"
            @class(['px-6 py-3 text-sm font-medium border-b-2','border-primary-500 text-primary-600'=> $activeTab === 'generated','border-transparent text-gray-500 hover:text-gray-700'=> $activeTab !== 'generated',])>
            Generated Reports
        </button>
        <button wire:click="$set('activeTab', 'templates')"
            @class(['px-6 py-3 text-sm font-medium border-b-2','border-primary-500 text-primary-600'=> $activeTab === 'templates','border-transparent text-gray-500 hover:text-gray-700'=> $activeTab !== 'templates',])>
            Saved Templates
        </button>
    </div>

    @if($activeTab === 'generated')
        @php $reports = \App\Models\GeneratedReport::with('user')->latest()->get(); @endphp
        <div class="fi-ta-ctn divide-y divide-gray-200 dark:divide-white/5 rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
            <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
                <thead class="bg-gray-50 dark:bg-white/5">
                    <tr>
                        <th class="fi-ta-header-cell px-3 py-3.5 text-sm font-semibold text-gray-950 dark:text-white">Name</th>
                        <th class="fi-ta-header-cell px-3 py-3.5 text-sm font-semibold text-gray-950 dark:text-white">Type</th>
                        <th class="fi-ta-header-cell px-3 py-3.5 text-sm font-semibold text-gray-950 dark:text-white">Period</th>
                        <th class="fi-ta-header-cell px-3 py-3.5 text-sm font-semibold text-gray-950 dark:text-white">Aggregation</th>
                        <th class="fi-ta-header-cell px-3 py-3.5 text-sm font-semibold text-gray-950 dark:text-white">Created</th>
                        <th class="fi-ta-header-cell px-3 py-3.5 text-sm font-semibold text-gray-950 dark:text-white text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                    @forelse($reports as $report)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-3 py-3 text-sm text-gray-950 dark:text-white font-medium">{{ $report->name }}</td>
                        <td class="px-3 py-3 text-sm">
                            <span @class(['fi-badge text-xs font-medium rounded-full px-2 py-0.5','bg-gray-100 text-gray-800'=> $report->type === 'simple','bg-blue-100 text-blue-800'=> $report->type === 'rigid','bg-orange-100 text-orange-800'=> $report->type === 'custom',])>{{ ucfirst($report->type) }}</span>
                        </td>
                        <td class="px-3 py-3 text-sm text-gray-500">{{ $report->date_start }} → {{ $report->date_end }}</td>
                        <td class="px-3 py-3 text-sm text-gray-500">{{ ucfirst($report->aggregation) }}</td>
                        <td class="px-3 py-3 text-sm text-gray-500">{{ $report->created_at->format('d M Y, H:i') }}</td>
                        <td class="px-3 py-3 text-right">
                            <div class="flex justify-end gap-1">
                                <a href="{{ \App\Filament\Pages\ViewReport::getUrl(['id' => $report->id]) }}" class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:border-primary-500 fi-btn-color-gray fi-color-gray fi-btn-size-sm gap-1 px-2 py-1 text-sm inline-grid rounded-lg bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300">View</a>
                                <a href="/admin/view-report/{{ $report->id }}/download-pdf" class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 gap-1 px-2 py-1 text-sm inline-grid rounded-lg bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300">PDF</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-3 py-12 text-center text-gray-400">Belum ada laporan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        @php $templates = \App\Models\ReportTemplate::where('user_id', Auth::id())->latest()->get(); @endphp
        <div class="fi-ta-ctn divide-y divide-gray-200 dark:divide-white/5 rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
            <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
                <thead class="bg-gray-50 dark:bg-white/5">
                    <tr>
                        <th class="fi-ta-header-cell px-3 py-3.5 text-sm font-semibold text-gray-950 dark:text-white">Name</th>
                        <th class="fi-ta-header-cell px-3 py-3.5 text-sm font-semibold text-gray-950 dark:text-white">Type</th>
                        <th class="fi-ta-header-cell px-3 py-3.5 text-sm font-semibold text-gray-950 dark:text-white">Created</th>
                        <th class="fi-ta-header-cell px-3 py-3.5 text-sm font-semibold text-gray-950 dark:text-white text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                    @forelse($templates as $template)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-3 py-3 text-sm text-gray-950 dark:text-white font-medium">{{ $template->name }}</td>
                        <td class="px-3 py-3 text-sm">
                            <span @class(['fi-badge text-xs font-medium rounded-full px-2 py-0.5','bg-gray-100 text-gray-800'=> $template->type === 'simple','bg-blue-100 text-blue-800'=> $template->type === 'rigid','bg-orange-100 text-orange-800'=> $template->type === 'custom',])>{{ ucfirst($template->type) }}</span>
                        </td>
                        <td class="px-3 py-3 text-sm text-gray-500">{{ $template->created_at->format('d M Y') }}</td>
                        <td class="px-3 py-3 text-right">
                            <div class="flex justify-end gap-1">
                                <button wire:click="loadTemplate({{ $template->id }})" class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 fi-btn-color-primary fi-color-primary fi-btn-size-sm gap-1 px-2 py-1 text-sm inline-grid rounded-lg bg-primary-600 hover:bg-primary-500 text-white">Generate</button>
                                <button wire:click="deleteTemplate({{ $template->id }})" class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 fi-btn-color-danger fi-color-danger fi-btn-size-sm gap-1 px-2 py-1 text-sm inline-grid rounded-lg bg-danger-600 hover:bg-danger-500 text-white">Delete</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-3 py-12 text-center text-gray-400">Belum ada template</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</x-filament-panels::page>
