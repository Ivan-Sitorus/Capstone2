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

    {{-- ── Belum ada hasil ───────────────────────────────────────────── --}}
    @if(! $hasResult)
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-10 text-center">
            <p class="text-base font-semibold text-gray-700 dark:text-gray-200">Belum Ada Hasil Prediksi</p>
            <p class="text-sm text-gray-400 mt-2">
                Tekan <span class="font-semibold text-primary-600">"Jalankan Prediksi"</span> di kanan atas untuk memulai analisis.
            </p>
            <div class="mt-5 inline-block text-left bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 p-3 text-xs font-mono text-gray-500 dark:text-gray-400">
                uvicorn datamining.api:app --port 8001 --reload
            </div>
        </div>

    @else
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- HASIL PREDIKSI                                                   --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}

        {{-- Meta bar --}}
        <div class="flex flex-wrap items-center justify-between gap-2 mb-5 text-xs text-gray-400 dark:text-gray-500">
            <span>Dijalankan: <span class="font-medium text-gray-600 dark:text-gray-300">{{ $lastRunAt }}</span></span>
            <span>
                Data historis: <span class="font-medium text-gray-600 dark:text-gray-300">{{ $dateFrom }} s/d {{ $dateTo }}</span>
                &nbsp;·&nbsp;
                Periode prediksi: <span class="font-medium text-primary-600 dark:text-primary-400">{{ $dateForecastFrom }} s/d {{ $dateForecastTo }}</span>
            </span>
        </div>

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- 1. PREDIKSI PENJUALAN 2 HARI KE DEPAN                      --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @if(count($predictions))
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 mb-6 overflow-hidden">

            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-base font-bold text-gray-900 dark:text-white">Prediksi Penjualan 2 Hari ke Depan</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Periode: <span class="font-medium">{{ $dateForecastFrom }}</span> s/d <span class="font-medium">{{ $dateForecastTo }}</span>
                </p>
            </div>

            @foreach($predictions as $pred)
            <div class="border-b border-gray-100 dark:border-gray-700/60 last:border-0">

                {{-- Sub-header per menu --}}
                <div class="px-6 py-2.5 bg-gray-50 dark:bg-gray-700/40 flex flex-wrap items-center gap-3">
                    <span class="font-bold text-sm text-gray-800 dark:text-white">{{ $pred['nama_menu'] }}</span>
                    <span class="text-xs text-gray-400">
                        Total:
                        <span class="font-semibold text-gray-700 dark:text-gray-200">
                            {{ number_format($pred['total_forecast'] ?? 0, 0) }} unit
                        </span>
                    </span>
                </div>

                {{-- Tabel forecast harian --}}
                @if(count($pred['forecast'] ?? []))
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-700">
                                <th class="py-2.5 px-5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Tanggal</th>
                                <th class="py-2.5 px-5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Hari</th>
                                <th class="py-2.5 px-5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Tipe</th>
                                <th class="py-2.5 px-5 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Prediksi</th>
                                <th class="py-2.5 px-5 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Batas Bawah</th>
                                <th class="py-2.5 px-5 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Batas Atas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-700/30">
                            @foreach($pred['forecast'] as $f)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors">
                                <td class="py-3 px-5 font-mono text-sm text-gray-700 dark:text-gray-200">{{ $f['tanggal'] ?? '-' }}</td>
                                <td class="py-3 px-5 text-gray-700 dark:text-gray-200">{{ $f['hari'] ?? '-' }}</td>
                                <td class="py-3 px-5">
                                    @if(($f['day_type'] ?? '') === 'Weekend')
                                        <span class="fi-badge flex items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset px-2 min-w-[theme(spacing.6)] py-1 fi-color-custom"
                                              style="background-color: rgb(254 243 199); color: rgb(146 64 14); --tw-ring-color: rgb(253 230 138);">
                                            Weekend
                                        </span>
                                    @else
                                        <span class="fi-badge flex items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset px-2 min-w-[theme(spacing.6)] py-1 fi-color-custom"
                                              style="background-color: rgb(219 234 254); color: rgb(29 78 216); --tw-ring-color: rgb(191 219 254);">
                                            Weekday
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-5 text-right">
                                    <span class="fi-badge flex items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset px-2 min-w-[theme(spacing.6)] py-1 fi-color-primary tabular-nums ml-auto w-fit"
                                          style="background-color: rgb(224 231 255); color: rgb(67 56 202); --tw-ring-color: rgb(199 210 254);">
                                        {{ $f['prediksi'] ?? 0 }}
                                    </span>
                                </td>
                                <td class="py-3 px-5 text-right tabular-nums text-gray-500 dark:text-gray-400">{{ $f['batas_bawah'] ?? '-' }}</td>
                                <td class="py-3 px-5 text-right tabular-nums text-gray-500 dark:text-gray-400">{{ $f['batas_atas'] ?? '-' }}</td>
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
        {{-- 2. HASIL EVALUASI MODEL PER ITEM                            --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @if(count($summaryTable))
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 mb-6 overflow-hidden">

            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-base font-bold text-gray-900 dark:text-white">Hasil Evaluasi Model per Item</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">pada data test 25% (split 75:25)</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/40">
                            <th class="py-3 px-5 text-left  text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Item</th>
                            <th class="py-3 px-5 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">MAE</th>
                            <th class="py-3 px-5 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">RMSE</th>
                            <th class="py-3 px-5 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">MAPE (%)</th>
                            <th class="py-3 px-5 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">SMAPE (%)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/30">
                        @foreach($summaryTable as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors">
                            <td class="py-3 px-5 font-semibold text-gray-800 dark:text-white">{{ $row['nama_menu'] }}</td>
                            <td class="py-3 px-5 text-right tabular-nums text-gray-600 dark:text-gray-300">
                                {{ isset($row['mae']) ? number_format($row['mae'], 2) : '-' }}
                            </td>
                            <td class="py-3 px-5 text-right tabular-nums text-gray-600 dark:text-gray-300">
                                {{ isset($row['rmse']) ? number_format($row['rmse'], 2) : '-' }}
                            </td>
                            <td class="py-3 px-5 text-right tabular-nums font-semibold
                                {{ isset($row['mape']) ? ($row['mape'] <= 20 ? 'text-success-600 dark:text-success-400' : ($row['mape'] <= 50 ? 'text-warning-600 dark:text-warning-400' : 'text-danger-600 dark:text-danger-400')) : 'text-gray-400' }}">
                                {{ isset($row['mape']) ? number_format($row['mape'], 2) . '%' : '-' }}
                            </td>
                            <td class="py-3 px-5 text-right tabular-nums text-gray-600 dark:text-gray-300">
                                {{ isset($row['smape']) ? number_format($row['smape'], 2) . '%' : '-' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-3 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/20 flex flex-wrap gap-x-6 gap-y-1 text-xs text-gray-400">
                <span><span class="font-semibold text-gray-500 dark:text-gray-300">MAE</span> — rata-rata error absolut</span>
                <span><span class="font-semibold text-gray-500 dark:text-gray-300">RMSE</span> — sensitif terhadap outlier</span>
                <span><span class="font-semibold text-gray-500 dark:text-gray-300">MAPE</span> — error persentase terhadap nilai aktual</span>
                <span><span class="font-semibold text-gray-500 dark:text-gray-300">SMAPE</span> — MAPE simetris, stabil saat nilai mendekati 0</span>
            </div>
        </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- 3. VISUALISASI FEATURE IMPORTANCE                           --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @if($chartFeatureImportance)
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 mb-6 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-base font-bold text-gray-900 dark:text-white">Feature Importance — Weekday vs Weekend</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Rata-rata penjualan berdasarkan tipe hari per menu</p>
            </div>
            <div class="p-4">
                <img src="data:image/png;base64,{{ $chartFeatureImportance }}"
                     alt="Feature Importance" class="w-full rounded"/>
            </div>
        </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- 4. VISUALISASI PREDIKSI vs AKTUAL PER ITEM                  --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @if($chartAllItems)
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-base font-bold text-gray-900 dark:text-white">Visualisasi Prediksi vs Aktual per Item</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Abu = data training &nbsp;·&nbsp; Biru = aktual test &nbsp;·&nbsp;
                    Merah dashed = prediksi &nbsp;·&nbsp; Area merah = interval kepercayaan 95% &nbsp;·&nbsp;
                    Kuning = Weekend
                </p>
            </div>
            <div class="p-4">
                <img src="data:image/png;base64,{{ $chartAllItems }}"
                     alt="Prediksi vs Aktual Semua Menu" class="w-full rounded"/>
            </div>
        </div>
        @endif

    @endif {{-- end hasResult --}}

</x-filament-panels::page>
