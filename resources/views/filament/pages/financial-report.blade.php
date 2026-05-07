<x-filament-panels::page @loaded.window="$wire.activeTab = 'generated'">
    <div x-data="{ activeTab: $wire.entangle('activeTab') }" class="space-y-6">
        {{-- Tab Navigation --}}
        <div class="fi-tabs flex gap-0 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
            <button
                @click="activeTab = 'generated'"
                :class="activeTab === 'generated'
                    ? 'bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-400 border-b-2 border-primary-500 font-semibold'
                    : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                class="flex-1 px-6 py-3.5 text-sm transition-all duration-150 relative cursor-pointer"
            >
                <div class="flex items-center justify-center gap-2">
                    <x-heroicon-o-document-text class="w-4 h-4"/>
                    <span>Generated Reports</span>
                    @php $reportCount = \App\Models\GeneratedReport::count(); @endphp
                    @if($reportCount > 0)
                        <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-primary-500 rounded-full">
                            {{ $reportCount }}
                        </span>
                    @endif
                </div>
            </button>
            <button
                @click="activeTab = 'templates'"
                :class="activeTab === 'templates'
                    ? 'bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-400 border-b-2 border-primary-500 font-semibold'
                    : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                class="flex-1 px-6 py-3.5 text-sm transition-all duration-150 relative cursor-pointer"
            >
                <div class="flex items-center justify-center gap-2">
                    <x-heroicon-o-bookmark class="w-4 h-4"/>
                    <span>Saved Templates</span>
                    @php $templateCount = $this->getTemplates()->count(); @endphp
                    @if($templateCount > 0)
                        <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-gray-400 dark:bg-gray-500 rounded-full">
                            {{ $templateCount }}
                        </span>
                    @endif
                </div>
            </button>
        </div>

        {{-- Tab Content --}}
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
            {{-- Tab 1: Generated Reports --}}
            <div x-show="activeTab === 'generated'" x-cloak>
                @php $reports = $this->getGeneratedReports(); @endphp
                @if($reports->isEmpty())
                    <div class="flex flex-col items-center justify-center py-16 px-4">
                        <x-heroicon-o-document-chart-bar class="w-16 h-16 text-gray-300 dark:text-gray-600 mb-4"/>
                        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-1">Belum Ada Laporan</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center max-w-md">
                            Klik tombol <span class="font-medium text-primary-600 dark:text-primary-400">Buat Laporan Baru</span>
                            di atas untuk membuat laporan keuangan pertama Anda.
                        </p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Nama Laporan</th>
                                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Tipe</th>
                                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Periode</th>
                                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Agregasi</th>
                                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Dibuat</th>
                                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach($reports as $report)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                        <td class="px-4 py-3">
                                            <div>
                                                <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $report->name }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">by {{ $report->user?->name ?? 'Unknown' }}</p>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span @class([
                                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                                'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' => $report->type === 'simple',
                                                'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300' => $report->type === 'rigid',
                                                'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' => $report->type === 'custom',
                                            ])>
                                                {{ ucfirst($report->type) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                            {{ \Carbon\Carbon::parse($report->date_start)->format('d M Y') }}
                                            →
                                            {{ \Carbon\Carbon::parse($report->date_end)->format('d M Y') }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="text-gray-600 dark:text-gray-400 capitalize">{{ $report->aggregation }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs">
                                            {{ $report->created_at->format('d M Y, H:i') }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <a href="{{ \App\Filament\Pages\ViewReport::getUrl(['id' => $report->id]) }}"
                                                   class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-500/10 rounded-lg transition-colors">
                                                    <x-heroicon-o-eye class="w-3.5 h-3.5"/>
                                                    View
                                                </a>
                                                <a href="{{ url('/admin/view-report/' . $report->id . '/download-pdf') }}"
                                                   target="_blank"
                                                   class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg transition-colors">
                                                    <x-heroicon-o-document-arrow-down class="w-3.5 h-3.5"/>
                                                    PDF
                                                </a>
                                                <a href="{{ url('/admin/view-report/' . $report->id . '/download-excel') }}"
                                                   target="_blank"
                                                   class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-500/10 rounded-lg transition-colors">
                                                    <x-heroicon-o-document-arrow-down class="w-3.5 h-3.5"/>
                                                    Excel
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Tab 2: Saved Templates --}}
            <div x-show="activeTab === 'templates'" x-cloak>
                @php $templates = $this->getTemplates(); @endphp
                @if($templates->isEmpty())
                    <div class="flex flex-col items-center justify-center py-16 px-4">
                        <x-heroicon-o-bookmark class="w-16 h-16 text-gray-300 dark:text-gray-600 mb-4"/>
                        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-1">Belum Ada Template</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center max-w-md">
                            Template laporan akan muncul di sini setelah Anda menyimpan konfigurasi laporan sebagai template.
                        </p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Nama Template</th>
                                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Tipe</th>
                                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Dibuat</th>
                                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach($templates as $template)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                        <td class="px-4 py-3">
                                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $template->name }}</p>
                                            @php $config = $template->config ?? []; @endphp
                                            @if(!empty($config))
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $config['date_start'] ?? '?' }} → {{ $config['date_end'] ?? '?' }}
                                                    · {{ $config['aggregation'] ?? '?' }}
                                                </p>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <span @class([
                                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                                'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' => $template->type === 'simple',
                                                'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300' => $template->type === 'rigid',
                                                'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' => $template->type === 'custom',
                                            ])>
                                                {{ ucfirst($template->type) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs">
                                            {{ $template->created_at->format('d M Y, H:i') }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <button
                                                    wire:click="loadTemplate({{ $template->id }})"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-500/10 rounded-lg transition-colors cursor-pointer">
                                                    <x-heroicon-o-arrow-path class="w-3.5 h-3.5"/>
                                                    Load
                                                </button>
                                                <button
                                                    wire:click="deleteTemplate({{ $template->id }})"
                                                    wire:confirm="Apakah Anda yakin ingin menghapus template '{{ $template->name }}'?"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-danger-600 dark:text-danger-400 hover:bg-danger-50 dark:hover:bg-danger-500/10 rounded-lg transition-colors cursor-pointer"
                                                >
                                                    <x-heroicon-o-trash class="w-3.5 h-3.5"/>
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
