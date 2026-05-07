<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Back Button & Header --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ \App\Filament\Pages\FinancialReport::getUrl() }}"
                   class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                    <x-heroicon-o-arrow-left class="w-4 h-4"/>
                    <span>Kembali ke Daftar Laporan</span>
                </a>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ url('/admin/view-report/' . $this->report->id . '/download-pdf') }}"
                   target="_blank"
                   class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10 hover:bg-red-100 dark:hover:bg-red-500/20 rounded-lg transition-colors">
                    <x-heroicon-o-document-arrow-down class="w-4 h-4"/>
                    Download PDF
                </a>
                <a href="{{ url('/admin/view-report/' . $this->report->id . '/download-excel') }}"
                   target="_blank"
                   class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-500/10 hover:bg-green-100 dark:hover:bg-green-500/20 rounded-lg transition-colors">
                    <x-heroicon-o-document-arrow-down class="w-4 h-4"/>
                    Download Excel
                </a>
            </div>
        </div>

        {{-- Report Title --}}
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $this->report->name }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Dibuat oleh {{ $this->report->user?->name ?? 'Unknown' }}
                · {{ $this->report->created_at->format('d F Y, H:i') }} WIB
            </p>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tipe Laporan</p>
                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 mt-1">{{ $this->getTypeLabel() }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Periode</p>
                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 mt-1">
                    {{ \Carbon\Carbon::parse($this->report->date_start)->format('d M Y') }}
                    →
                    {{ \Carbon\Carbon::parse($this->report->date_end)->format('d M Y') }}
                </p>
            </div>
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Agregasi</p>
                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 mt-1">{{ $this->getAggregationLabel() }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</p>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300 mt-1">
                    ● Generated
                </span>
            </div>
        </div>

        {{-- Key Metrics --}}
        @php $summary = $this->getReportSummary(); @endphp
        @if(!empty($summary))
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Ringkasan Keuangan</h3>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($summary as $label => $value)
                        <div class="flex items-center justify-between px-6 py-3">
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ $label }}</span>
                            <span @class([
                                'text-sm font-semibold',
                                'text-green-600 dark:text-green-400' => str_starts_with($value, 'Rp') && !str_contains($label, 'Pengeluaran') && !str_contains($label, 'HPP') && !str_contains($label, 'Keluar'),
                                'text-red-600 dark:text-red-400' => str_contains($label, 'Pengeluaran') || str_contains($label, 'HPP') || str_contains($label, 'Keluar'),
                                'text-primary-600 dark:text-primary-400 font-bold' => $loop->last || str_contains($label, 'Net') || str_contains($label, 'Bersih'),
                                'text-gray-900 dark:text-gray-100' => true,
                            ])>{{ $value }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Detailed Data Table --}}
        @php $rows = $this->getReportRows(); @endphp
        @if(!empty($rows))
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Data Detail</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                                <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 text-left">Tanggal</th>
                                <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 text-left">Kategori</th>
                                <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 text-left">Tipe</th>
                                <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 text-right">Jumlah</th>
                                @if(isset($rows[0]['running_total']))
                                    <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 text-right">Running Total</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($rows as $row)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                    <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300">
                                        {{ \Carbon\Carbon::parse($row['date'])->format('d M Y') }}
                                    </td>
                                    <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300">{{ $row['category'] }}</td>
                                    <td class="px-4 py-2.5">
                                        <span @class([
                                            'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium',
                                            'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300' => ($row['type'] ?? '') === 'Income',
                                            'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300' => ($row['type'] ?? '') === 'Expense',
                                        ])>
                                            {{ $row['type'] ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2.5 text-right font-medium text-gray-900 dark:text-gray-100">
                                        Rp {{ number_format((float)($row['amount'] ?? 0), 0, ',', '.') }}
                                    </td>
                                    @if(isset($row['running_total']))
                                        <td class="px-4 py-2.5 text-right font-medium text-gray-900 dark:text-gray-100">
                                            Rp {{ number_format((float)$row['running_total'], 0, ',', '.') }}
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Raw JSON for debugging (collapsible) --}}
        <div x-data="{ open: false }" class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
            <button @click="open = !open"
                    class="w-full flex items-center justify-between px-6 py-3 text-sm text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/30 rounded-t-xl transition-colors">
                <span class="flex items-center gap-2">
                    <x-heroicon-o-code-bracket class="w-4 h-4"/>
                    Raw Report Data
                </span>
                <x-heroicon-o-chevron-down x-show="!open" class="w-4 h-4"/>
                <x-heroicon-o-chevron-up x-show="open" class="w-4 h-4"/>
            </button>
            <div x-show="open" x-cloak class="px-6 pb-4">
                <pre class="text-xs text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900 rounded-lg p-4 overflow-x-auto max-h-96">{{ json_encode($this->report->result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </div>
    </div>
</x-filament-panels::page>
