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
    <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
        <thead>
            <tr style="background-color:#515151;">
                <th style="padding:10px 20px; text-align:right;  font-size:0.75rem; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #ffcc00; width:48px;">No</th>
                <th style="padding:10px 20px; text-align:left;   font-size:0.75rem; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #ffcc00;">Nama Bahan Baku</th>
                <th style="padding:10px 20px; text-align:center; font-size:0.75rem; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #ffcc00;">Satuan</th>
                <th style="padding:10px 20px; text-align:right;  font-size:0.75rem; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #ffcc00;">Total Prediksi</th>
                <th style="padding:10px 20px; text-align:right;  font-size:0.75rem; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #ffcc00;">Rata-rata/Hari</th>
                <th style="padding:10px 20px; text-align:right;  font-size:0.75rem; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #ffcc00;">MAE</th>
                <th style="padding:10px 20px; text-align:right;  font-size:0.75rem; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #ffcc00;">RMSE</th>
                <th style="padding:10px 20px; text-align:right;  font-size:0.75rem; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #ffcc00;">MAPE (%)</th>
                <th style="padding:10px 20px; text-align:center; font-size:0.75rem; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #ffcc00;">Model</th>
            </tr>
        </thead>
        <tbody>
            @foreach($summaryTable as $i => $row)
            @php
                $mape = $row['mape'] ?? 0;
                $mapeColor = $mape <= 10
                    ? 'color:#16a34a; font-weight:700;'
                    : ($mape <= 25 ? 'color:#d97706; font-weight:700;' : 'color:#dc2626; font-weight:700;');
            @endphp
            <tr>
                <td style="padding:10px 20px; text-align:right;  color:#626b78; font-size:0.75rem; border:1px solid #ffcc00;">{{ $i + 1 }}</td>
                <td style="padding:10px 20px; text-align:left;   color:#626b78; font-weight:600; border:1px solid #ffcc00;">{{ $row['nama_bahan_baku'] }}</td>
                <td style="padding:10px 20px; text-align:center; color:#626b78; font-size:0.75rem; border:1px solid #ffcc00;">{{ $row['satuan'] ?? '-' }}</td>
                <td style="padding:10px 20px; text-align:right;  color:#4338ca; font-weight:700; border:1px solid #ffcc00;">{{ number_format($row['total_forecast'] ?? 0, 1) }}</td>
                <td style="padding:10px 20px; text-align:right;  color:#626b78; border:1px solid #ffcc00;">{{ number_format($row['avg_per_day'] ?? 0, 1) }}</td>
                <td style="padding:10px 20px; text-align:right;  color:#626b78; font-size:0.75rem; border:1px solid #ffcc00;">{{ number_format($row['mae'] ?? 0, 2) }}</td>
                <td style="padding:10px 20px; text-align:right;  color:#626b78; font-size:0.75rem; border:1px solid #ffcc00;">{{ number_format($row['rmse'] ?? 0, 2) }}</td>
                <td style="padding:10px 20px; text-align:right;  font-size:0.75rem; border:1px solid #ffcc00; {{ $mapeColor }}">{{ number_format($mape, 2) }}%</td>
                <td style="padding:10px 20px; text-align:center; border:1px solid #ffcc00;">
                    <span style="display:inline-block; background-color:#f3e8ff; color:#7e22ce; padding:2px 10px; border-radius:9999px; font-size:0.75rem; font-weight:600;">
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
<div style="border-bottom:1px solid #ffcc00;">

    {{-- Sub-header per bahan baku --}}
    <div style="padding:10px 24px; background-color:#a8a8a8; display:flex; flex-wrap:wrap; align-items:center; gap:12px; border-bottom:1px solid #ffcc00;">
        <span style="font-weight:700; font-size:0.875rem; color:#626b78;">{{ $pred['nama_bahan_baku'] }}</span>
        @if(!empty($pred['satuan']))
            <span style="display:inline-block; background-color:#515151; color:#b1b1b1; padding:1px 8px; border-radius:9999px; font-size:0.75rem;">
                {{ $pred['satuan'] }}
            </span>
        @endif
        <span style="font-size:0.75rem; color:#393939;">
            Total:
            <span style="font-weight:600; color:#393939;">
                {{ number_format($pred['total_forecast'] ?? 0, 1) }} {{ $pred['satuan'] ?? '' }}
            </span>
        </span>
        <span style="margin-left:auto; display:flex; gap:16px; font-size:0.75rem; color:#4d4d4d;">
            <span>MAPE:
                @php $mape = $pred['mape'] ?? 0; @endphp
                <span style="{{ $mape <= 10 ? 'color:#16a34a;' : ($mape <= 25 ? 'color:#d97706;' : 'color:#dc2626;') }} font-weight:600;">
                    {{ number_format($mape, 2) }}%
                </span>
            </span>
            <span>MAE: <span style="font-weight:600; color:#374151;">{{ number_format($pred['mae'] ?? 0, 2) }}</span></span>
        </span>
    </div>

    {{-- Tabel forecast harian --}}
    @if(count($pred['forecast'] ?? []))
    <div class="overflow-x-auto">
        <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
            <thead>
                <tr style="background-color:#565656;">
                    <th style="padding:10px 20px; text-align:left;  font-size:0.75rem; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #ffcc00;">Tanggal</th>
                    <th style="padding:10px 20px; text-align:left;  font-size:0.75rem; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #ffcc00;">Hari</th>
                    <th style="padding:10px 20px; text-align:left;  font-size:0.75rem; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #ffcc00;">Tipe</th>
                    <th style="padding:10px 20px; text-align:right; font-size:0.75rem; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #ffcc00;">Prediksi</th>
                    <th style="padding:10px 20px; text-align:right; font-size:0.75rem; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #ffcc00;">Batas Bawah</th>
                    <th style="padding:10px 20px; text-align:right; font-size:0.75rem; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #ffcc00;">Batas Atas</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pred['forecast'] as $day)
                <tr>
                    <td style="padding:10px 20px; text-align:left;  color:#9ca3af; font-family:monospace; border:1px solid #ffcc00;">{{ $day['tanggal'] }}</td>
                    <td style="padding:10px 20px; text-align:left;  color:#9ca3af; border:1px solid #ffcc00;">{{ $day['hari'] }}</td>
                    <td style="padding:10px 20px; border:1px solid #ffcc00;">
                        @if(($day['day_type'] ?? '') === 'Weekend')
                            <span style="display:inline-block; background-color:#fef9c3; color:#92400e; padding:2px 10px; border-radius:9999px; font-size:0.75rem; font-weight:600;">Weekend</span>
                        @else
                            <span style="display:inline-block; background-color:#dbeafe; color:#1d4ed8; padding:2px 10px; border-radius:9999px; font-size:0.75rem; font-weight:600;">Weekday</span>
                        @endif
                    </td>
                    <td style="padding:10px 20px; text-align:right; border:1px solid #ffcc00;">
                        <span style="display:inline-block; background-color:#e0e7ff; color:#4338ca; padding:2px 10px; border-radius:9999px; font-size:0.75rem; font-weight:700;">
                            {{ number_format($day['prediksi'] ?? 0, 1) }}
                        </span>
                    </td>
                    <td style="padding:10px 20px; text-align:right; color:#6b7280; border:1px solid #ffcc00;">{{ number_format($day['batas_bawah'] ?? 0, 1) }}</td>
                    <td style="padding:10px 20px; text-align:right; color:#6b7280; border:1px solid #ffcc00;">{{ number_format($day['batas_atas'] ?? 0, 1) }}</td>
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