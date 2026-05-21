
<x-filament-panels::page>

    {{-- ── Error ──────────────────────────────────────────────────── --}}
    @if($errorMsg)
        <div class="rounded-lg border border-yellow-200 dark:border-yellow-800 bg-yellow-50 dark:bg-yellow-900/20 p-4 mb-6">
            <p class="text-sm font-semibold text-yellow-700 dark:text-yellow-300">{{ $errorMsg }}</p>
            <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">
                Buka halaman <span class="font-semibold">Klasterisasi Menu Penjualan</span>,
                tekan <span class="font-semibold">"Jalankan Clustering"</span>, lalu kembali ke sini.
            </p>
        </div>
    @endif

    {{-- ── Belum ada data ───────────────────────────────────────── --}}
    @if(! $hasResult)
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-10 text-center">
            <p class="text-base font-semibold text-gray-700 dark:text-gray-200">Belum Ada Data Klasterisasi</p>
            <p class="text-sm text-gray-400 mt-2">
                Jalankan proses di halaman
                <span class="font-semibold">Klasterisasi Menu Penjualan</span>
                terlebih dahulu, lalu tekan
                <span class="font-semibold">"Perbarui Data Klasterisasi Menu"</span> di kanan atas.
            </p>
        </div>

    @else

        {{-- Info terakhir dijalankan --}}
        <div class="flex items-center justify-between mb-6 text-xs text-gray-400 dark:text-gray-500">
            <span>Terakhir dijalankan: <span class="text-gray-600 dark:text-gray-300 font-medium">{{ $lastRunAt }}</span></span>
            <span>Data: <span class="text-gray-600 dark:text-gray-300 font-medium">{{ $dateFrom }} s/d {{ $dateTo }}</span></span>
        </div>

        {{-- ── Stat bar ─────────────────────────────────────────── --}}
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

        {{-- ── Tabel Hasil Klasterisasi ─────────────────────────── --}}
        @if(count($tableRows))
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 mb-8 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Hasil Klasterisasi Menu</h2>
                <p class="text-xs text-gray-400 mt-0.5">Data {{ $dateFrom }} s/d {{ $dateTo }}</p>
            </div>
            <div class="overflow-x-auto">
                <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
                    <thead>
                        <tr style="background-color:#7d7d7d;">
                            <th style="padding:10px 20px; text-align:right; font-size:0.75rem; font-weight:600; color:#d5d5d5; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #aed100; width:56px;">No</th>
                            <th style="padding:10px 20px; text-align:left;  font-size:0.75rem; font-weight:600; color:#487fff; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #aed100;">Nama Item</th>
                            <th style="padding:10px 20px; text-align:right; font-size:0.75rem; font-weight:600; color:#487fff; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #aed100;">Jumlah Terjual</th>
                            <th style="padding:10px 20px; text-align:center;font-size:0.75rem; font-weight:600; color:#487fff; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #aed100;">Klaster</th>
                            <th style="padding:10px 20px; text-align:left;  font-size:0.75rem; font-weight:600; color:#487fff; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #aed100;">Kategori</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $badge = [
                                'Sangat Laris' => 'background-color:#dcfce7; color:#15803d;',
                                'Laris'        => 'background-color:#dbeafe; color:#1d4ed8;',
                                'Cukup Laris'  => 'background-color:#fef9c3; color:#b45309;',
                                'Kurang Laris' => 'background-color:#fee2e2; color:#dc2626;',
                                'Tidak Laris'  => 'background-color:#f3f4f6; color:#6b7280;',
                            ];
                        @endphp
                        @foreach($tableRows as $i => $row)
                            <tr style="border-bottom:1px solid #aed100;">
                                <td style="padding:10px 20px; text-align:right;  color:#487fff; border:1px solid #aed100;">{{ $i + 1 }}</td>
                                <td style="padding:10px 20px; text-align:left;   color:#487fff; font-weight:500; border:1px solid #aed100;">{{ $row['Nama Item'] }}</td>
                                <td style="padding:10px 20px; text-align:right;  color:#487fff; font-weight:500; border:1px solid #aed100;">{{ number_format($row['Jumlah'], 1) }}</td>
                                <td style="padding:10px 20px; text-align:center; color:#487fff; border:1px solid #aed100;">{{ $row['Klaster'] }}</td>
                                <td style="padding:10px 20px; border:1px solid #aed100;">
                                    <span style="display:inline-block; padding:2px 12px; border-radius:9999px; font-size:0.75rem; font-weight:600; {{ $badge[$row['Kategori']] ?? '' }}">
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

        {{-- ── Bar Chart ────────────────────────────────────────── --}}
        @if($chartBar)
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 mb-8 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Visualisasi Clustering Penjualan Menu</h2>
                <p class="text-xs text-gray-400 mt-0.5">Setiap batang mewakili total penjualan menu, dikelompokkan per kategori klaster</p>
            </div>
            <div class="p-4">
                <img src="data:image/png;base64,{{ $chartBar }}" alt="Clustering Bar Chart" class="w-full rounded"/>
            </div>
        </div>
        @endif

        {{-- ── Elbow & Silhouette ───────────────────────────────── --}}
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

    @endif

</x-filament-panels::page>