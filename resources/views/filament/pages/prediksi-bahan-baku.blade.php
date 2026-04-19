<x-filament-panels::page>

    {{-- ── Error ─────────────────────────────────────────────────────── --}}
    @if($errorMsg)
        <div class="rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 p-4 mb-6">
            <p class="text-sm font-semibold text-red-700 dark:text-red-300">Prediksi gagal</p>
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
            <p class="text-base font-semibold text-gray-700 dark:text-gray-200">Belum Ada Hasil Prediksi Bahan Baku</p>
            <p class="text-sm text-gray-400 mt-2">
                Tekan <span class="font-semibold text-primary-600">"Jalankan Prediksi Bahan Baku"</span>
                di kanan atas untuk memulai analisis Prophet.
            </p>

            {{-- Info apa yang akan ditampilkan --}}
            <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4 max-w-2xl mx-auto text-left">
                <div class="rounded-lg border border-blue-200 dark:border-blue-700 bg-blue-50 dark:bg-blue-900/20 p-4">
                    <p class="text-xs font-semibold text-blue-700 dark:text-blue-300 mb-1">📈 Prediksi Penggunaan</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Estimasi jumlah pemakaian tiap bahan baku untuk hari-hari ke depan</p>
                </div>
                <div class="rounded-lg border border-green-200 dark:border-green-700 bg-green-50 dark:bg-green-900/20 p-4">
                    <p class="text-xs font-semibold text-green-700 dark:text-green-300 mb-1">📊 Evaluasi Model</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">MAE, RMSE, MAPE, dan SMAPE per bahan baku dari data uji</p>
                </div>
                <div class="rounded-lg border border-purple-200 dark:border-purple-700 bg-purple-50 dark:bg-purple-900/20 p-4">
                    <p class="text-xs font-semibold text-purple-700 dark:text-purple-300 mb-1">📉 Visualisasi</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Grafik prediksi vs aktual dan ringkasan forecast semua bahan baku</p>
                </div>
            </div>

            <div class="mt-6 inline-block text-left bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 p-3 text-xs font-mono text-gray-500 dark:text-gray-400">
                uvicorn datamining.api:app --port 8001 --reload
            </div>
        </div>

    @else
    {{-- ════════════════════════════════════════════════════════════════ --}}
    {{-- HASIL PREDIKSI                                                     --}}
    {{-- ════════════════════════════════════════════════════════════════ --}}

        {{-- Meta bar --}}
        <div class="flex flex-wrap items-center justify-between gap-2 mb-5 text-xs text-gray-400 dark:text-gray-500">
            <span>Dijalankan: <span class="font-medium text-gray-600 dark:text-gray-300">{{ $lastRunAt }}</span></span>
            <span>
                Data historis: <span class="font-medium text-gray-600 dark:text-gray-300">{{ $dateFrom }} s/d {{ $dateTo }}</span>
                &nbsp;·&nbsp;
                Periode prediksi: <span class="font-medium text-primary-600 dark:text-primary-400">{{ $dateForecastFrom }} s/d {{ $dateForecastTo }}</span>
            </span>
        </div>

        {{-- ── Stat bar ──────────────────────────────────────────────── --}}
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-6 py-5">
                <p class="text-xs text-gray-400 uppercase tracking-widest mb-1">Bahan Baku</p>
                <p class="text-3xl font-bold text-gray-800 dark:text-white">{{ $totalIngredients }}</p>
                <p class="text-xs text-gray-400 mt-1">Total bahan baku diprediksi</p>
            </div>
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-6 py-5">
                <p class="text-xs text-gray-400 uppercase tracking-widest mb-1">Horizon Prediksi</p>
                <p class="text-3xl font-bold text-gray-800 dark:text-white">{{ $forecastDays }} hari</p>
                <p class="text-xs text-gray-400 mt-1">Ke depan dari data terakhir</p>
            </div>
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-6 py-5">
                <p class="text-xs text-gray-400 uppercase tracking-widest mb-1">Model</p>
                <p class="text-xl font-bold text-gray-800 dark:text-white mt-1">Prophet</p>
                <p class="text-xs text-gray-400 mt-1">Time Series Forecasting</p>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- 1. RINGKASAN FORECAST SEMUA BAHAN BAKU                      --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @if(count($summaryTable))
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 mb-6 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-base font-bold text-gray-900 dark:text-white">Ringkasan Prediksi Semua Bahan Baku</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Periode: <span class="font-medium">{{ $dateForecastFrom }}</span> s/d <span class="font-medium">{{ $dateForecastTo }}</span>
                    — diurutkan berdasarkan total prediksi tertinggi
                </p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/40">
                            <th class="py-3 px-5 text-right  text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide w-12">No</th>
                            <th class="py-3 px-5 text-left   text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Nama Bahan Baku</th>
                            <th class="py-3 px-5 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Satuan</th>
                            <th class="py-3 px-5 text-right  text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Prediksi</th>
                            <th class="py-3 px-5 text-right  text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Rata-rata/Hari</th>
                            <th class="py-3 px-5 text-right  text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">MAE</th>
                            <th class="py-3 px-5 text-right  text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">RMSE</th>
                            <th class="py-3 px-5 text-right  text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">MAPE (%)</th>
                            <th class="py-3 px-5 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Model</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/30">
                        @foreach($summaryTable as $i => $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors">
                            <td class="py-3 px-5 text-right tabular-nums text-gray-400 text-xs">{{ $i + 1 }}</td>
                            <td class="py-3 px-5 font-semibold text-gray-800 dark:text-gray-100">{{ $row['nama_bahan_baku'] }}</td>
                            <td class="py-3 px-5 text-center text-xs text-gray-500 dark:text-gray-400">{{ $row['satuan'] ?? '-' }}</td>
                            <td class="py-3 px-5 text-right tabular-nums font-bold text-primary-600 dark:text-primary-400">
                                {{ number_format($row['total_forecast'] ?? 0, 1) }}
                            </td>
                            <td class="py-3 px-5 text-right tabular-nums text-gray-600 dark:text-gray-300">
                                {{ number_format($row['avg_per_day'] ?? 0, 1) }}
                            </td>
                            <td class="py-3 px-5 text-right tabular-nums text-gray-500 dark:text-gray-400 text-xs">{{ number_format($row['mae'] ?? 0, 2) }}</td>
                            <td class="py-3 px-5 text-right tabular-nums text-gray-500 dark:text-gray-400 text-xs">{{ number_format($row['rmse'] ?? 0, 2) }}</td>
                            <td class="py-3 px-5 text-right tabular-nums text-xs">
                                @php $mape = $row['mape'] ?? 0; @endphp
                                <span class="{{ $mape <= 10 ? 'text-green-600 dark:text-green-400' : ($mape <= 25 ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-500 dark:text-red-400') }} font-semibold">
                                    {{ number_format($mape, 2) }}%
                                </span>
                            </td>
                            <td class="py-3 px-5 text-center">
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300">
                                    {{ $row['model'] ?? 'Prophet' }}
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
        {{-- 2. GRAFIK RINGKASAN TOTAL FORECAST                          --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @if($chartForecastAll)
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 mb-6 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-base font-bold text-gray-900 dark:text-white">Total Prediksi per Bahan Baku</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Perbandingan jumlah prediksi penggunaan seluruh bahan baku</p>
            </div>
            <div class="p-4">
                <img src="data:image/png;base64,{{ $chartForecastAll }}" alt="Forecast All Ingredients" class="w-full rounded"/>
            </div>
        </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- 3. DETAIL PREDIKSI PER BAHAN BAKU                          --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @if(count($predictions))
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 mb-6 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-base font-bold text-gray-900 dark:text-white">Detail Prediksi per Bahan Baku</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Periode: <span class="font-medium">{{ $dateForecastFrom }}</span> s/d <span class="font-medium">{{ $dateForecastTo }}</span>
                </p>
            </div>

            @foreach($predictions as $pred)
            <div class="border-b border-gray-100 dark:border-gray-700/60 last:border-0">

                {{-- Sub-header per bahan baku --}}
                <div class="px-6 py-2.5 bg-gray-50 dark:bg-gray-700/40 flex flex-wrap items-center gap-3">
                    <span class="font-bold text-sm text-gray-800 dark:text-white">{{ $pred['nama_bahan_baku'] }}</span>
                    @if(!empty($pred['satuan']))
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">
                            {{ $pred['satuan'] }}
                        </span>
                    @endif
                    <span class="text-xs text-gray-400">
                        Total: <span class="font-semibold text-gray-700 dark:text-gray-200">
                            {{ number_format($pred['total_forecast'] ?? 0, 1) }} {{ $pred['satuan'] ?? '' }}
                        </span>
                    </span>
                    <span class="ml-auto flex items-center gap-3 text-xs text-gray-400">
                        <span>MAPE:
                            @php $mape = $pred['mape'] ?? 0; @endphp
                            <span class="font-semibold {{ $mape <= 10 ? 'text-green-600' : ($mape <= 25 ? 'text-yellow-600' : 'text-red-500') }}">
                                {{ number_format($mape, 2) }}%
                            </span>
                        </span>
                        <span>MAE: <span class="font-semibold text-gray-600 dark:text-gray-300">{{ number_format($pred['mae'] ?? 0, 2) }}</span></span>
                    </span>
                </div>

                {{-- Tabel forecast harian --}}
                @if(count($pred['forecast'] ?? []))
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-700">
                                <th class="py-2.5 px-5 text-left   text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Tanggal</th>
                                <th class="py-2.5 px-5 text-left   text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Hari</th>
                                <th class="py-2.5 px-5 text-left   text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Tipe</th>
                                <th class="py-2.5 px-5 text-right  text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Prediksi</th>
                                <th class="py-2.5 px-5 text-right  text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Batas Bawah</th>
                                <th class="py-2.5 px-5 text-right  text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Batas Atas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-700/30">
                            @foreach($pred['forecast'] as $day)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors">
                                <td class="py-2.5 px-5 tabular-nums text-gray-700 dark:text-gray-300">{{ $day['tanggal'] }}</td>
                                <td class="py-2.5 px-5 text-gray-600 dark:text-gray-400">{{ $day['hari'] }}</td>
                                <td class="py-2.5 px-5">
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ ($day['day_type'] ?? '') === 'Weekend'
                                            ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300'
                                            : 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' }}">
                                        {{ $day['day_type'] ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-5 text-right tabular-nums font-bold text-primary-600 dark:text-primary-400">
                                    {{ number_format($day['prediksi'] ?? 0, 1) }}
                                </td>
                                <td class="py-2.5 px-5 text-right tabular-nums text-gray-400 dark:text-gray-500 text-xs">
                                    {{ number_format($day['batas_bawah'] ?? 0, 1) }}
                                </td>
                                <td class="py-2.5 px-5 text-right tabular-nums text-gray-400 dark:text-gray-500 text-xs">
                                    {{ number_format($day['batas_atas'] ?? 0, 1) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

            </div>
            @endforeach
        </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- 4. GRAFIK EVALUASI 2×2                                      --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @if($chartEvaluation)
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 mb-6 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-base font-bold text-gray-900 dark:text-white">Evaluasi Model per Bahan Baku</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">MAE, RMSE, MAPE, SMAPE dihitung pada data test (25%)</p>
            </div>
            <div class="p-4">
                <img src="data:image/png;base64,{{ $chartEvaluation }}" alt="Evaluation Chart" class="w-full rounded"/>
            </div>
        </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- 5. FEATURE IMPORTANCE: WEEKDAY VS WEEKEND                   --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @if($chartFeatureImportance)
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 mb-6 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-base font-bold text-gray-900 dark:text-white">Feature Importance: Weekday vs Weekend</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Rata-rata pemakaian bahan baku berdasarkan tipe hari</p>
            </div>
            <div class="p-4">
                <img src="data:image/png;base64,{{ $chartFeatureImportance }}" alt="Feature Importance" class="w-full rounded"/>
            </div>
        </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- 6. VISUALISASI PREDIKSI VS AKTUAL (SEMUA BAHAN BAKU)        --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @if($chartAllItems)
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 mb-6 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-base font-bold text-gray-900 dark:text-white">Prediksi vs Aktual — Semua Bahan Baku</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Grafik gabungan seluruh bahan baku pada data test</p>
            </div>
            <div class="p-4">
                <img src="data:image/png;base64,{{ $chartAllItems }}" alt="All Ingredients Chart" class="w-full rounded"/>
            </div>
        </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- 7. GRAFIK PER BAHAN BAKU                                    --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @if(count($chartPerIngredient))
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 mb-6 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-base font-bold text-gray-900 dark:text-white">Prediksi vs Aktual per Bahan Baku</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Grafik individual tiap bahan baku pada data test (25%)</p>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-700/40">
                @foreach($chartPerIngredient as $item)
                <div class="p-4">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2 px-2">{{ $item['nama'] }}</p>
                    <img src="data:image/png;base64,{{ $item['chart'] }}" alt="Chart {{ $item['nama'] }}" class="w-full rounded"/>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- 8. LOG PREPROCESSING                                        --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @if(count($preprocessLogs))
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-base font-bold text-gray-900 dark:text-white">Log Preprocessing</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Tahapan pembersihan dan persiapan data sebelum prediksi</p>
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
