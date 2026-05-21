<x-filament-panels::page>

    {{-- ── Error ─────────────────────────────────────────────────────── --}}
    @if($errorMsg)
        <div class="rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 p-4 mb-6">
            <p class="text-sm font-semibold text-red-700 dark:text-red-300">Gagal memuat data</p>
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
            <p class="text-base font-semibold text-gray-700 dark:text-gray-200">Belum Ada Data Ringkasan</p>
            <p class="text-sm text-gray-400 mt-2">
                Tekan <span class="font-semibold text-primary-600">"Perbarui Data Klasterisasi Bahan Baku"</span>
                di kanan atas untuk menampilkan hasil klasterisasi terakhir.
            </p>
            <p class="text-xs text-gray-400 mt-3">
                Pastikan sudah menjalankan analisis terlebih dahulu di halaman
                <span class="font-semibold">Klasterisasi Bahan Baku</span>.
            </p>
        </div>

    @else
    {{-- ════════════════════════════════════════════════════════════════ --}}
    {{-- HASIL RINGKASAN KLASTERISASI                                      --}}
    {{-- ════════════════════════════════════════════════════════════════ --}}

        {{-- Meta bar --}}
        <div class="flex flex-wrap items-center justify-between gap-2 mb-5 text-xs text-gray-400 dark:text-gray-500">
            <span>Diperbarui: <span class="font-medium text-gray-600 dark:text-gray-300">{{ $lastRunAt }}</span></span>
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
        {{-- TABEL HASIL KLASTERISASI                                    --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @if(count($tableRows))
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 mb-6 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-base font-bold text-gray-900 dark:text-white">Tabel Hasil Klasterisasi Bahan Baku</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Diurutkan berdasarkan total penggunaan — data {{ $dateFrom }} s/d {{ $dateTo }}
                </p>
            </div>
            <div class="overflow-x-auto">
                @php
                    $badge = [
                        'Sangat Banyak Digunakan'  => 'background-color:#dcfce7; color:#15803d;',
                        'Banyak Digunakan'         => 'background-color:#dbeafe; color:#1d4ed8;',
                        'Cukup Digunakan'          => 'background-color:#fef9c3; color:#b45309;',
                        'Sedikit Digunakan'        => 'background-color:#fee2e2; color:#c2410c;',
                        'Paling Sedikit Digunakan' => 'background-color:#f3f4f6; color:#6b7280;',
                    ];
                @endphp
                <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
                    <thead>
                        <tr style="background-color:#f9fafb;">
                            <th style="padding:10px 20px; text-align:right;  font-size:0.75rem; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #e5e7eb; width:48px;">No</th>
                            <th style="padding:10px 20px; text-align:left;   font-size:0.75rem; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #e5e7eb;">Nama Bahan Baku</th>
                            <th style="padding:10px 20px; text-align:left;   font-size:0.75rem; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #e5e7eb;">Satuan</th>
                            <th style="padding:10px 20px; text-align:right;  font-size:0.75rem; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #e5e7eb;">Total Penggunaan</th>
                            <th style="padding:10px 20px; text-align:center; font-size:0.75rem; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #e5e7eb;">Klaster</th>
                            <th style="padding:10px 20px; text-align:left;   font-size:0.75rem; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #e5e7eb;">Kategori</th>
                        </tr>
                    </thead>
                    <tbody>
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
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 mb-6 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-base font-bold text-gray-900 dark:text-white">Visualisasi Clustering Penggunaan Bahan Baku</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Setiap batang mewakili total penggunaan bahan baku, dikelompokkan per kategori klaster</p>
            </div>
            <div class="p-4">
                <img src="data:image/png;base64,{{ $chartBar }}" alt="Clustering Bar Chart" class="w-full rounded"/>
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

    @endif {{-- end hasResult --}}

</x-filament-panels::page>