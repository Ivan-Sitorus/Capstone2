<x-filament-panels::page>

    {{-- ── Error ─────────────────────────────────────────────────────── --}}
    @if($errorMsg)
        <div class="rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 p-4 mb-6">
            <p class="text-sm font-semibold text-red-700 dark:text-red-300">Clustering gagal</p>
            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $errorMsg }}</p>
            <p class="text-xs text-red-500 mt-2">
                Pastikan FastAPI sudah berjalan:
                <code class="bg-red-100 dark:bg-red-900 px-1.5 py-0.5 rounded font-mono">
                    uvicorn datamining.api:app --port 8001 --reload
                </code>
            </p>
        </div>
    @endif

    {{-- ── Belum ada hasil ─────────────────────────────────────────────── --}}
    @if(! $hasResult)
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-10 text-center">
            <p class="text-base font-semibold text-gray-700 dark:text-gray-200">Belum Ada Hasil Clustering Bahan Baku</p>
            <p class="text-sm text-gray-400 mt-2">
                Tekan <span class="font-semibold text-primary-600">"Jalankan Clustering Bahan Baku"</span>
                di kanan atas untuk memulai analisis K-Means.
            </p>

            {{-- Penjelasan singkat klaster --}}
            <div class="mt-8 grid grid-cols-2 md:grid-cols-5 gap-3 max-w-3xl mx-auto text-left">
                @foreach([
                    ['label' => 'Sangat Banyak Digunakan', 'color' => 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-700', 'text' => 'text-green-700 dark:text-green-300'],
                    ['label' => 'Banyak Digunakan',        'color' => 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-700',   'text' => 'text-blue-700 dark:text-blue-300'],
                    ['label' => 'Cukup Digunakan',         'color' => 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-700', 'text' => 'text-yellow-700 dark:text-yellow-300'],
                    ['label' => 'Sedikit Digunakan',       'color' => 'bg-orange-50 dark:bg-orange-900/20 border-orange-200 dark:border-orange-700', 'text' => 'text-orange-700 dark:text-orange-300'],
                    ['label' => 'Paling Sedikit Digunakan','color' => 'bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-600',   'text' => 'text-gray-500 dark:text-gray-400'],
                ] as $k)
                    <div class="rounded-lg border {{ $k['color'] }} p-3">
                        <p class="text-xs font-semibold {{ $k['text'] }}">{{ $k['label'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 inline-block text-left bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 p-3 text-xs font-mono text-gray-500 dark:text-gray-400">
                uvicorn datamining.api:app --port 8001 --reload
            </div>
        </div>

    @else
    {{-- ════════════════════════════════════════════════════════════════ --}}
    {{-- HASIL CLUSTERING                                                   --}}
    {{-- ════════════════════════════════════════════════════════════════ --}}

        {{-- Meta bar --}}
        <div class="flex flex-wrap items-center justify-between gap-2 mb-5 text-xs text-gray-400 dark:text-gray-500">
            <span>Dijalankan: <span class="font-medium text-gray-600 dark:text-gray-300">{{ $lastRunAt }}</span></span>
            <span>Data historis: <span class="font-medium text-gray-600 dark:text-gray-300">{{ $dateFrom }} s/d {{ $dateTo }}</span></span>
        </div>

        {{-- ── Stat bar ──────────────────────────────────────────────── --}}
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-6 py-5">
                <p class="text-xs text-gray-400 uppercase tracking-widest mb-1">K Optimal</p>
                <p class="text-3xl font-bold text-gray-800 dark:text-white">{{ $bestK }}</p>
                <p class="text-xs text-gray-400 mt-1">Jumlah klaster terbaik</p>
            </div>
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-6 py-5">
                <p class="text-xs text-gray-400 uppercase tracking-widest mb-1">Silhouette Score</p>
                <p class="text-3xl font-bold text-gray-800 dark:text-white">{{ number_format($silhouetteScore, 3) }}</p>
                <p class="text-xs mt-1 {{ $silhouetteScore >= 0.7 ? 'text-green-500' : ($silhouetteScore >= 0.5 ? 'text-yellow-500' : 'text-red-400') }}">
                    {{ $silhouetteScore >= 0.7 ? 'Kualitas tinggi' : ($silhouetteScore >= 0.5 ? 'Kualitas sedang' : 'Kualitas rendah') }}
                </p>
            </div>
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-6 py-5">
                <p class="text-xs text-gray-400 uppercase tracking-widest mb-1">Bahan Baku Dianalisis</p>
                <p class="text-3xl font-bold text-gray-800 dark:text-white">{{ $totalIngredients }}</p>
                <p class="text-xs text-gray-400 mt-1">Dari data pemakaian harian</p>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- RINGKASAN PER KLASTER                                        --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @if(count($clusters))
        @php
            $clusterColors = [
                'Sangat Banyak Digunakan' => ['ring' => 'ring-green-200 dark:ring-green-700',  'bg' => 'bg-green-50 dark:bg-green-900/20',   'badge' => 'bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300',  'dot' => 'bg-green-500'],
                'Banyak Digunakan'        => ['ring' => 'ring-blue-200 dark:ring-blue-700',    'bg' => 'bg-blue-50 dark:bg-blue-900/20',     'badge' => 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300',    'dot' => 'bg-blue-500'],
                'Cukup Digunakan'         => ['ring' => 'ring-yellow-200 dark:ring-yellow-700','bg' => 'bg-yellow-50 dark:bg-yellow-900/20', 'badge' => 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300','dot' => 'bg-yellow-500'],
                'Sedikit Digunakan'       => ['ring' => 'ring-orange-200 dark:ring-orange-700','bg' => 'bg-orange-50 dark:bg-orange-900/20', 'badge' => 'bg-orange-100 dark:bg-orange-900/40 text-orange-700 dark:text-orange-300','dot' => 'bg-orange-500'],
                'Paling Sedikit Digunakan'=> ['ring' => 'ring-gray-200 dark:ring-gray-600',   'bg' => 'bg-gray-50 dark:bg-gray-700/40',     'badge' => 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400',      'dot' => 'bg-gray-400'],
            ];
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mb-6">
            @foreach($clusters as $cluster)
            @php $c = $clusterColors[$cluster['label']] ?? $clusterColors['Paling Sedikit Digunakan']; @endphp
            <div class="rounded-xl ring-1 {{ $c['ring'] }} {{ $c['bg'] }} p-5">
                <div class="flex items-center gap-2 mb-3">
                    <span class="w-2.5 h-2.5 rounded-full {{ $c['dot'] }} shrink-0"></span>
                    <span class="text-sm font-semibold text-gray-800 dark:text-white">{{ $cluster['label'] }}</span>
                    <span class="ml-auto text-xs text-gray-400">{{ $cluster['count'] }} item</span>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                    Rata-rata: <span class="font-semibold text-gray-700 dark:text-gray-200">{{ number_format($cluster['avg_usage'], 1) }}</span>
                    &nbsp;·&nbsp;
                    Total: <span class="font-semibold text-gray-700 dark:text-gray-200">{{ number_format($cluster['total_usage'], 1) }}</span>
                </p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($cluster['ingredients'] as $ing)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium {{ $c['badge'] }}">
                            {{ $ing['name'] }}
                            @if($ing['unit'])
                                <span class="opacity-60">({{ $ing['unit'] }})</span>
                            @endif
                        </span>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- TABEL HASIL KLASTERISASI                                    --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @if(count($tableRows))
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 mb-6 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-base font-bold text-gray-900 dark:text-white">Hasil Klasterisasi Bahan Baku</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Diurutkan berdasarkan total penggunaan — data {{ $dateFrom }} s/d {{ $dateTo }}
                </p>
            </div>
            <div class="overflow-x-auto">
                <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
                    <thead>
                        <tr style="background-color:#9c9c9c;">
                <th style="padding:10px 20px; text-align:right; font-size:0.75rem; font-weight:600; color:#5a5a5a; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #afb800; width:56px;">No</th>
                <th style="padding:10px 20px; text-align:left;  font-size:0.75rem; font-weight:600; color:#5a5a5a; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #afb800;">Nama Bahan Baku</th>
               <th style="padding:10px 20px; text-align:left;  font-size:0.75rem; font-weight:600; color:#5a5a5a; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #afb800;">Satuan</th>
                <th style="padding:10px 20px; text-align:right; font-size:0.75rem; font-weight:600; color:#5a5a5a; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #afb800;">Total Penggunaan</th>
                <th style="padding:10px 20px; text-align:center;font-size:0.75rem; font-weight:600; color:#5a5a5a; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #afb800;">Klaster</th>
                <th style="padding:10px 20px; text-align:left;  font-size:0.75rem; font-weight:600; color:#5a5a5a; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #afb800;">Kategori</th>
            </tr>
                    </thead>
                    <tbody>
            @php
                $badge = [
                    'Sangat Banyak Digunakan' => 'background-color:#dcfce7; color:#15803d;',
                    'Banyak Digunakan'        => 'background-color:#dbeafe; color:#1d4ed8;',
                    'Cukup Digunakan'  => 'background-color:#fef9c3; color:#b45309;',
                    'Sedikit Digunakan' => 'background-color:#fee2e2; color:#c2410c;',
                    'Paling Sedikit Digunakan'  => 'background-color:#f3f4f6; color:#6b7280;',
                ];
            @endphp
                        @foreach($tableRows as $i => $row)
<tr>
    <td style="padding:10px 20px; text-align:right;  color:#9ca3af; border:1px solid #e5e7eb;">{{ $i + 1 }}</td>
    <td style="padding:10px 20px; text-align:left;   color:#374151; font-weight:500; border:1px solid #e5e7eb;">{{ $row['Nama Bahan Baku'] }}</td>
    <td style="padding:10px 20px; text-align:left;   color:#6b7280; border:1px solid #e5e7eb;">{{ $row['Satuan'] ?? '-' }}</td>
    <td style="padding:10px 20px; text-align:right;  color:#374151; font-weight:500; border:1px solid #e5e7eb;">{{ number_format($row['Total Penggunaan'], 2) }}</td>
    <td style="padding:10px 20px; text-align:center; color:#6b7280; border:1px solid #e5e7eb;">{{ $row['Klaster'] }}</td>
    <td style="padding:10px 20px; border:1px solid #e5e7eb;">
        <span style="display:inline-block; padding:2px 12px; border-radius:9999px; font-size:0.75rem; font-weight:600; {{ $badge[$row['Kategori']] ?? 'background-color:#f3f4f6; color:#6b7280;' }}">
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
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Visualisasi Clustering Penggunaan Bahan Baku</h2>
                <p class="text-xs text-gray-400 mt-0.5">Setiap batang mewakili total penggunaan bahan baku, dikelompokkan per kategori klaster</p>
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
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            @if($chartElbow)
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Elbow Method</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Menentukan jumlah klaster optimal berdasarkan inertia</p>
                </div>
                <div class="p-4">
                    <img src="data:image/png;base64,{{ $chartElbow }}" alt="Elbow Chart" class="w-full rounded"/>
                </div>
            </div>
            @endif
            @if($chartSilhouette)
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Silhouette Score per K</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kualitas pengelompokan — semakin tinggi semakin baik</p>
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
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-base font-bold text-gray-900 dark:text-white">Log Preprocessing</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Tahapan pembersihan dan persiapan data sebelum clustering</p>
            </div>
            <div class="divide-y divide-gray-50 dark:divide-gray-700/30">
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