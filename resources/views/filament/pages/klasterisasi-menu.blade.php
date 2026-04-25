<x-filament-panels::page>

    {{-- ── Error ─────────────────────────────────────────────────────── --}}
    @if($errorMsg)
        <div class="rounded-lg border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 p-4 mb-6">
            <p class="text-sm font-semibold text-red-700 dark:text-red-300">Clustering gagal</p>
            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $errorMsg }}</p>
            <p class="text-xs text-red-500 mt-2">
                Pastikan FastAPI sudah berjalan:
                <code class="bg-red-100 dark:bg-red-900 px-1.5 py-0.5 rounded font-mono">cd datamining &amp;&amp; uvicorn api:app --port 8001</code>
            </p>
        </div>
    @endif

    {{-- ── Belum ada hasil ─────────────────────────────────────────────── --}}
    @if(! $hasResult)
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-10 text-center">
            <p class="text-base font-semibold text-gray-700 dark:text-gray-200">Belum Ada Hasil Clustering</p>
            <p class="text-sm text-gray-400 mt-2">
                Tekan <span class="font-semibold">"Jalankan Clustering"</span> di kanan atas untuk memulai analisis K-Means.
            </p>
            <div class="mt-6 inline-block text-left bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 p-4 text-xs font-mono text-gray-500 dark:text-gray-400">
                <p class="mb-1">cd datamining</p>
                <p>uvicorn api:app --port 8001 --reload</p>
            </div>
        </div>

    @else
    {{-- ════════════════════════════════════════════════════════════════ --}}
    {{-- HASIL CLUSTERING                                                  --}}
    {{-- ════════════════════════════════════════════════════════════════ --}}

        {{-- Info terakhir dijalankan --}}
        <div class="flex items-center justify-between mb-6 text-xs text-gray-400 dark:text-gray-500">
            <span>Terakhir dijalankan: <span class="text-gray-600 dark:text-gray-300 font-medium">{{ $lastRunAt }}</span></span>
            <span>Data: <span class="text-gray-600 dark:text-gray-300 font-medium">{{ $dateFrom }} s/d {{ $dateTo }}</span></span>
        </div>

        {{-- ── Stat bar ──────────────────────────────────────────────── --}}
        <div class="grid grid-cols-3 gap-4 mb-8">
            @foreach([
                ['label' => 'K Optimal',        'value' => $bestK,                              'sub' => 'Jumlah klaster terbaik'],
                ['label' => 'Silhouette Score',  'value' => number_format($silhouetteScore, 3), 'sub' => $silhouetteScore >= 0.7 ? 'Kualitas tinggi' : ($silhouetteScore >= 0.5 ? 'Kualitas sedang' : 'Kualitas rendah')],
                ['label' => 'Menu Dianalisis',   'value' => $totalMenu,                         'sub' => 'Dari data riwayat pesanan'],
            ] as $s)
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-6 py-5">
                    <p class="text-xs text-gray-400 uppercase tracking-widest mb-2">{{ $s['label'] }}</p>
                    <p class="text-3xl font-bold text-gray-800 dark:text-white">{{ $s['value'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $s['sub'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- TABEL HASIL KLASTERISASI                                    --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @if(count($tableRows))
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 mb-8 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Hasil Klasterisasi Menu</h2>
                <p class="text-xs text-gray-400 mt-0.5">
                    Diurutkan berdasarkan jumlah penjualan — data {{ $dateFrom }} s/d {{ $dateTo }}
                </p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50">
                            <th class="py-3 px-5 text-right text-xs font-semibold text-gray-400 uppercase tracking-wide w-14 border border-gray-200 dark:border-gray-600">No</th>
                            <th class="py-3 px-5 text-left   text-xs font-semibold text-gray-400 uppercase tracking-wide border border-gray-200 dark:border-gray-600">Nama Item</th>
                            <th class="py-3 px-5 text-right  text-xs font-semibold text-gray-400 uppercase tracking-wide border border-gray-200 dark:border-gray-600">Jumlah Terjual</th>
                            <th class="py-3 px-5 text-center text-xs font-semibold text-gray-400 uppercase tracking-wide border border-gray-200 dark:border-gray-600">Klaster</th>
                            <th class="py-3 px-5 text-left   text-xs font-semibold text-gray-400 uppercase tracking-wide border border-gray-200 dark:border-gray-600">Kategori</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $badge = [
                                'Sangat Laris' => 'bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300',
                                'Laris'        => 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300',
                                'Cukup Laris'  => 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300',
                                'Kurang Laris' => 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300',
                                'Tidak Laris'  => 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400',
                            ];
                        @endphp
                        @foreach($tableRows as $i => $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors">
                                <td class="py-3 px-5 text-right text-gray-400 dark:text-gray-500 tabular-nums border border-gray-200 dark:border-gray-600">{{ $i }}</td>
                                <td class="py-3 px-5 font-medium text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-600">{{ $row['Nama Item'] }}</td>
                                <td class="py-3 px-5 text-right text-gray-600 dark:text-gray-300 tabular-nums font-medium border border-gray-200 dark:border-gray-600">
                                    {{ number_format($row['Jumlah'], 1) }}
                                </td>
                                <td class="py-3 px-5 text-center text-gray-500 dark:text-gray-400 tabular-nums border border-gray-200 dark:border-gray-600">
                                    {{ $row['Klaster'] }}
                                </td>
                                <td class="py-3 px-5 border border-gray-200 dark:border-gray-600">
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold {{ $badge[$row['Kategori']] ?? '' }}">
                                        {{ $row['Kategori'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- VISUALISASI BAR CHART                                       --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @if($chartBar)
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 mb-8 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Visualisasi Clustering Penjualan Menu</h2>
                <p class="text-xs text-gray-400 mt-0.5">Setiap batang mewakili total penjualan menu, dikelompokkan per kategori klaster</p>
            </div>
            <div class="p-4">
                <img src="data:image/png;base64,{{ $chartBar }}"
                     alt="Clustering Bar Chart"
                     class="w-full rounded"/>
            </div>
        </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- ELBOW & SILHOUETTE                                          --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            @if($chartElbow)
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Elbow Method</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Menentukan jumlah klaster optimal berdasarkan inertia</p>
                </div>
                <div class="p-4">
                    <img src="data:image/png;base64,{{ $chartElbow }}" alt="Elbow Chart" class="w-full rounded"/>
                </div>
            </div>
            @endif
            @if($chartSilhouette)
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Silhouette Score</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Kualitas pengelompokan — semakin tinggi semakin baik</p>
                </div>
                <div class="p-4">
                    <img src="data:image/png;base64,{{ $chartSilhouette }}" alt="Silhouette Chart" class="w-full rounded"/>
                </div>
            </div>
            @endif
        </div>

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- LOG PREPROCESSING                                           --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @if(count($preprocessLogs))
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Log Preprocessing</h2>
                <p class="text-xs text-gray-400 mt-0.5">Tahapan pembersihan dan persiapan data sebelum clustering</p>
            </div>
            <div class="divide-y divide-gray-50 dark:divide-gray-700/40">
                @foreach($preprocessLogs as $i => $log)
                    <div class="flex items-start gap-4 px-6 py-4">
                        <span class="shrink-0 w-6 h-6 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-xs font-bold flex items-center justify-center mt-0.5">
                            {{ $i + 1 }}
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ $log['tahap'] }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $log['detail'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

    @endif {{-- end hasResult --}}

</x-filament-panels::page>
