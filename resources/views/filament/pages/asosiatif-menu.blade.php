<x-filament-panels::page>

    {{-- ── Error ─────────────────────────────────────────────────────── --}}
    @if($errorMsg)
        <div class="rounded-lg border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 p-4 mb-6">
            <p class="text-sm font-semibold text-red-700 dark:text-red-300">Association Rule gagal</p>
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
            <p class="text-base font-semibold text-gray-700 dark:text-gray-200">Belum Ada Hasil Association Rule</p>
            <p class="text-sm text-gray-400 mt-2">
                Tekan <span class="font-semibold">"Jalankan Association Rule"</span> di kanan atas untuk memulai analisis.
            </p>

            {{-- Penjelasan metode --}}
            <div class="mt-8 grid grid-cols-3 gap-4 max-w-2xl mx-auto text-left">
                <div class="rounded-lg border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 p-4">
                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Support</p>
                    <p class="text-xs text-gray-400">Seberapa sering kombinasi menu muncul dari seluruh transaksi.</p>
                </div>
                <div class="rounded-lg border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 p-4">
                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Confidence</p>
                    <p class="text-xs text-gray-400">Seberapa sering menu B dipesan jika menu A sudah dipesan.</p>
                </div>
                <div class="rounded-lg border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 p-4">
                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Lift</p>
                    <p class="text-xs text-gray-400">Kekuatan asosiasi. Lift > 1 berarti hubungan positif.</p>
                </div>
            </div>

            {{-- Command --}}
            <div class="mt-6 inline-block text-left bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 p-4 text-xs font-mono text-gray-500 dark:text-gray-400">
                <p class="mb-1">cd datamining</p>
                <p>uvicorn api:app --port 8001 --reload</p>
            </div>
        </div>

    @else
    {{-- ════════════════════════════════════════════════════════════════ --}}
    {{-- HASIL ASSOCIATION RULE                                            --}}
    {{-- ════════════════════════════════════════════════════════════════ --}}

        {{-- Meta --}}
        <div class="flex items-center justify-between mb-6 text-xs text-gray-400 dark:text-gray-500">
            <span>Terakhir dijalankan: <span class="text-gray-600 dark:text-gray-300 font-medium">{{ $lastRunAt }}</span></span>
            <span>Data: <span class="text-gray-600 dark:text-gray-300 font-medium">{{ $dateFrom }} s/d {{ $dateTo }}</span></span>
        </div>

        {{-- ── Stat bar ──────────────────────────────────────────────── --}}
        <div class="grid grid-cols-4 gap-4 mb-8">
            @foreach([
                ['label' => 'Total Rules',     'value' => $totalRules],
                ['label' => 'Total Transaksi', 'value' => $totalTransactions],
                ['label' => 'Min Support',     'value' => number_format($minSupport * 100, 1) . '%'],
                ['label' => 'Min Confidence',  'value' => number_format($minConfidence * 100, 1) . '%'],
            ] as $s)
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-6 py-5">
                    <p class="text-xs text-gray-400 uppercase tracking-widest mb-2">{{ $s['label'] }}</p>
                    <p class="text-3xl font-bold text-gray-800 dark:text-white">{{ $s['value'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- TABEL TOP 8 ASSOCIATION RULES                              --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        @if(count($rules))
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 mb-8 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Top 8 Association Rules</h2>
                <p class="text-xs text-gray-400 mt-0.5">
                    Kombinasi menu yang sering dipesan bersamaan — diurutkan berdasarkan Lift tertinggi
                </p>
            </div>
            <div class="overflow-x-auto">
                <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
                    <thead>
                        <tr style="background-color:#1381ef;">
                            <th style="padding:10px 16px; text-align:right;  font-size:0.75rem; font-weight:600; color:#000000; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #ffd000; width:48px;">No</th>
                            <th style="padding:10px 16px; text-align:left;   font-size:0.75rem; font-weight:600; color:#000000; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #ffd000;">Jika Pesan</th>
                            <th style="padding:10px 16px; text-align:left;   font-size:0.75rem; font-weight:600; color:#000000; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #ffd000;">Maka Pesan</th>
                            <th style="padding:10px 16px; text-align:right;  font-size:0.75rem; font-weight:600; color:#000000; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #ffd000;">Jml A</th>
                            <th style="padding:10px 16px; text-align:right;  font-size:0.75rem; font-weight:600; color:#000000; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #ffd000;">Jml B</th>
                            <th style="padding:10px 16px; text-align:right;  font-size:0.75rem; font-weight:600; color:#000000; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #ffd000;">Bersama</th>
                            <th style="padding:10px 16px; text-align:right;  font-size:0.75rem; font-weight:600; color:#000000; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #ffd000;">Support</th>
                            <th style="padding:10px 16px; text-align:right;  font-size:0.75rem; font-weight:600; color:#000000; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #ffd000;">Confidence</th>
                            <th style="padding:10px 16px; text-align:right;  font-size:0.75rem; font-weight:600; color:#000000; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #ffd000;">Lift</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rules as $i => $rule)
                            @php
                                $liftColor = $rule['lift'] >= 2
                                    ? 'color:#16a34a; font-weight:700;'
                                    : ($rule['lift'] >= 1
                                        ? 'color:#d97706; font-weight:700;'
                                        : 'color:#dc2626; font-weight:700;');
                            @endphp

                            {{-- Baris data utama --}}
                            <tr>
                                <td style="padding:10px 16px; text-align:right;  color:#008910; border:1px solid #ffd000;">{{ $i + 1 }}</td>
                                <td style="padding:10px 16px; text-align:left;   border:1px solid #ffd000;">
                                    <span style="display:inline-block; background-color:#ffd000; color:#000000; padding:2px 10px; border-radius:4px; font-size:0.75rem; font-weight:600;">
                                        {{ $rule['menu_pertama'] }}
                                    </span>
                                </td>
                                <td style="padding:10px 16px; text-align:left;   border:1px solid #ffd000;">
                                    <span style="display:inline-block; background-color:#dcfce7; color:#15803d; padding:2px 10px; border-radius:4px; font-size:0.75rem; font-weight:600;">
                                        {{ $rule['menu_kedua'] }}
                                    </span>
                                </td>
                                <td style="padding:10px 16px; text-align:right;  color:#008910; font-size:0.75rem; border:1px solid #ffd000;">{{ $rule['jumlah_menu_pertama'] }}</td>
                                <td style="padding:10px 16px; text-align:right;  color:#008910; font-size:0.75rem; border:1px solid #ffd000;">{{ $rule['jumlah_menu_kedua'] }}</td>
                                <td style="padding:10px 16px; text-align:right;  color:#008910; font-weight:600; font-size:0.75rem; border:1px solid #ffd000;">{{ $rule['jumlah_bersamaan'] }}</td>
                                <td style="padding:10px 16px; text-align:right;  color:#008910; border:1px solid #ffd000;">{{ number_format($rule['support'] * 100, 2) }}%</td>
                                <td style="padding:10px 16px; text-align:right;  color:#008910; font-weight:500; border:1px solid #ffd000;">{{ number_format($rule['confidence'] * 100, 2) }}%</td>
                                <td style="padding:10px 16px; text-align:right;  border:1px solid #ffd000; {{ $liftColor }}">{{ number_format($rule['lift'], 2) }}</td>
                            </tr>

                            {{-- Baris interpretasi --}}
                            <tr style="background-color:#c7c7c7;">
                                <td style="border:1px solid #ffd000; border-bottom:2px solid #ffd000;"></td>
                                <td colspan="8" style="padding:8px 16px; border:1px solid #ffd000; border-bottom:2px solid #ffd000;">
                                    <span style="font-size:0.75rem; color:#41454c; font-style:italic; line-height:1.6;">
                                        💡 {{ $rule['interpretasi'] }}
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
        {{-- VISUALISASI                                                  --}}
        {{-- ══════════════════════════════════════════════════════════ --}}

        @if($chartTopRules)
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 mb-6 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Top Rules Berdasarkan Lift</h2>
                <p class="text-xs text-gray-400 mt-0.5">Rule dengan nilai lift tertinggi menunjukkan asosiasi terkuat antar menu</p>
            </div>
            <div class="p-4">
                <img src="data:image/png;base64,{{ $chartTopRules }}" alt="Top Rules Chart" class="w-full rounded"/>
            </div>
        </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            @if($chartSupConf)
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Support vs Confidence</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Sebaran rules berdasarkan nilai support dan confidence</p>
                </div>
                <div class="p-4">
                    <img src="data:image/png;base64,{{ $chartSupConf }}" alt="Support vs Confidence" class="w-full rounded"/>
                </div>
            </div>
            @endif
            @if($chartFreqItem)
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Frequent 1-Itemsets</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Frekuensi kemunculan tiap menu dalam transaksi</p>
                </div>
                <div class="p-4">
                    <img src="data:image/png;base64,{{ $chartFreqItem }}" alt="Frequent Itemsets" class="w-full rounded"/>
                </div>
            </div>
            @endif
        </div>

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- TABEL FREQUENT ITEMSETS                                     --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

            {{-- ── Frequent 1-Itemsets ──────────────────────────────── --}}
            @if(count($freq1Itemsets))
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Frequent 1-Itemsets</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Menu yang sering muncul sendiri</p>
                </div>
                <div class="overflow-x-auto">
                    <table style="width:100%; border-collapse:collapse; font-size:0.75rem;">
                        <thead>
                            <tr style="background-color:#6c6c6c;">
                                <th style="padding:8px 16px; text-align:left;  font-size:0.7rem; font-weight:600; color:#000000; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #e5e7eb;">Menu</th>
                                <th style="padding:8px 16px; text-align:right; font-size:0.7rem; font-weight:600; color:#000000; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #e5e7eb;">Support</th>
                                <th style="padding:8px 16px; text-align:right; font-size:0.7rem; font-weight:600; color:#000000; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #e5e7eb;">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($freq1Itemsets as $fi)
                            <tr>
                                <td style="padding:8px 16px; text-align:left;  color:#0082be; font-weight:500; border:1px solid #b8c200;">{{ $fi['item'] }}</td>
                                <td style="padding:8px 16px; text-align:right; color:#0082be; border:1px solid #b8c200;">{{ number_format($fi['support'] * 100, 2) }}%</td>
                                <td style="padding:8px 16px; text-align:right; color:#0082be; font-weight:600; border:1px solid #b8c200;">{{ $fi['jumlah_kemunculan'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- ── Frequent 2-Itemsets ──────────────────────────────── --}}
            @if(count($freq2Itemsets))
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Frequent 2-Itemsets</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Pasangan menu yang sering dipesan bersamaan</p>
                </div>
                <div class="overflow-x-auto">
                    <table style="width:100%; border-collapse:collapse; font-size:0.75rem;">
                        <thead>
                            <tr style="background-color:#6c6c6c;">
                                <th style="padding:8px 16px; text-align:left;  font-size:0.7rem; font-weight:600; color:#6200ff; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #b8c200;">Pasangan Menu</th>
                                <th style="padding:8px 16px; text-align:right; font-size:0.7rem; font-weight:600; color:#6200ff; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #b8c200;">Support</th>
                                <th style="padding:8px 16px; text-align:right; font-size:0.7rem; font-weight:600; color:#6200ff; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #b8c200;">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($freq2Itemsets as $fi)
                            <tr>
                                <td style="padding:8px 16px; text-align:left;  color:#0082be; font-weight:500; border:1px solid #b8c200;">{{ $fi['items'] }}</td>
                                <td style="padding:8px 16px; text-align:right; color:#0082be; border:1px solid #b8c200;">{{ number_format($fi['support'] * 100, 2) }}%</td>
                                <td style="padding:8px 16px; text-align:right; color:#0082be; font-weight:600; border:1px solid #b8c200;">{{ $fi['jumlah_kemunculan'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- ── Frequent 3-Itemsets ──────────────────────────────── --}}
            @if(count($freq3Itemsets))
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Frequent 3-Itemsets</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Triplet menu yang sering dipesan bersamaan</p>
                </div>
                <div class="overflow-x-auto">
                    <table style="width:100%; border-collapse:collapse; font-size:0.75rem;">
                        <thead>
                            <tr style="background-color:#f9fafb;">
                                <th style="padding:8px 16px; text-align:left;  font-size:0.7rem; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #e5e7eb;">Kombinasi Menu</th>
                                <th style="padding:8px 16px; text-align:right; font-size:0.7rem; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #e5e7eb;">Support</th>
                                <th style="padding:8px 16px; text-align:right; font-size:0.7rem; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:0.05em; border:1px solid #e5e7eb;">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($freq3Itemsets as $fi)
                            <tr>
                                <td style="padding:8px 16px; text-align:left;  color:#374151; font-weight:500; border:1px solid #e5e7eb;">{{ $fi['items'] }}</td>
                                <td style="padding:8px 16px; text-align:right; color:#6b7280; border:1px solid #e5e7eb;">{{ number_format($fi['support'] * 100, 2) }}%</td>
                                <td style="padding:8px 16px; text-align:right; color:#374151; font-weight:600; border:1px solid #e5e7eb;">{{ $fi['jumlah_kemunculan'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @elseif(count($freq2Itemsets))
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Frequent 3-Itemsets</h2>
                </div>
                <div class="p-6 text-center text-xs text-gray-400">
                    Tidak ada 3-itemset dengan support ≥ 5%.
                </div>
            </div>
            @endif

        </div>

        {{-- ── Log Preprocessing ─────────────────────────────────────── --}}
        @if(count($preprocessLogs))
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">Log Preprocessing</h2>
                <p class="text-xs text-gray-400 mt-0.5">Tahapan persiapan data sebelum analisis association rule</p>
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