<x-filament-panels::page>

    {{-- Header info --}}
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 mb-6">
        <div class="flex items-start gap-3">
            <x-heroicon-o-information-circle class="w-5 h-5 text-blue-500 mt-0.5 shrink-0"/>
            <div>
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Integrasi Model Data Mining</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Halaman ini menampilkan hasil analitik dari model data mining. Data di bawah adalah
                    <span class="font-medium text-orange-500">placeholder dummy</span> —
                    ganti dengan pemanggilan ke endpoint FastAPI / Colab model Anda.
                </p>
            </div>
        </div>
    </div>

    {{-- Tab navigasi --}}
    <div class="flex gap-2 mb-6 border-b border-gray-200 dark:border-gray-700">
        @foreach([
            ['key' => 'forecast',     'label' => 'Prediksi Penjualan',    'icon' => 'heroicon-o-chart-bar'],
            ['key' => 'top_menu',     'label' => 'Menu Terlaris',         'icon' => 'heroicon-o-star'],
            ['key' => 'association',  'label' => 'Aturan Asosiasi',       'icon' => 'heroicon-o-link'],
            ['key' => 'peak_hour',    'label' => 'Jam Ramai',             'icon' => 'heroicon-o-clock'],
        ] as $tab)
            <button
                wire:click="$set('activeTab', '{{ $tab['key'] }}')"
                class="flex items-center gap-1.5 px-4 py-2.5 text-sm font-medium border-b-2 transition-colors
                    {{ $activeTab === $tab['key']
                        ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                        : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}"
            >
                <x-dynamic-component :component="$tab['icon']" class="w-4 h-4"/>
                {{ $tab['label'] }}
            </button>
        @endforeach
    </div>

    {{-- Tab: Prediksi Penjualan --}}
    @if($activeTab === 'forecast')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wide">Prediksi Besok</p>
                <p class="text-2xl font-bold text-gray-800 dark:text-white mt-1">Rp 510.000</p>
                <p class="text-xs text-green-600 mt-1">↑ 8.5% dari rata-rata</p>
            </div>
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wide">Akurasi Model (MAE)</p>
                <p class="text-2xl font-bold text-gray-800 dark:text-white mt-1">92.3%</p>
                <p class="text-xs text-gray-400 mt-1">Berdasarkan 30 hari terakhir</p>
            </div>
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5">
                <p class="text-xs text-gray-500 uppercase tracking-wide">Prediksi Minggu Ini</p>
                <p class="text-2xl font-bold text-gray-800 dark:text-white mt-1">Rp 3.550.000</p>
                <p class="text-xs text-blue-500 mt-1">Estimasi 7 hari ke depan</p>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-4">Prediksi vs Aktual — 7 Hari</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700">
                            <th class="text-left py-2 px-3 text-xs text-gray-500 font-medium">Hari</th>
                            <th class="text-right py-2 px-3 text-xs text-gray-500 font-medium">Prediksi</th>
                            <th class="text-right py-2 px-3 text-xs text-gray-500 font-medium">Aktual</th>
                            <th class="text-right py-2 px-3 text-xs text-gray-500 font-medium">Selisih</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($this->getForecastData() as $row)
                            <tr class="border-b border-gray-50 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="py-2.5 px-3 font-medium text-gray-700 dark:text-gray-200">{{ $row['date'] }}</td>
                                <td class="py-2.5 px-3 text-right text-indigo-600 dark:text-indigo-400">
                                    Rp {{ number_format($row['predicted'], 0, ',', '.') }}
                                </td>
                                <td class="py-2.5 px-3 text-right text-gray-600 dark:text-gray-300">
                                    @if($row['actual'])
                                        Rp {{ number_format($row['actual'], 0, ',', '.') }}
                                    @else
                                        <span class="text-gray-300 dark:text-gray-600">—</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 text-right">
                                    @if($row['actual'])
                                        @php $diff = $row['predicted'] - $row['actual']; @endphp
                                        <span class="{{ $diff >= 0 ? 'text-green-600' : 'text-red-500' }} text-xs">
                                            {{ $diff >= 0 ? '+' : '' }}Rp {{ number_format($diff, 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-xs text-orange-400 bg-orange-50 dark:bg-orange-900/20 px-2 py-0.5 rounded-full">Prediksi</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Tab: Menu Terlaris --}}
    @if($activeTab === 'top_menu')
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-4">Top 5 Menu Terlaris</h3>
            <div class="space-y-3">
                @foreach($this->getTopMenuData() as $item)
                    <div class="flex items-center gap-4 p-3 rounded-lg bg-gray-50 dark:bg-gray-700/30">
                        <span class="w-7 h-7 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 text-sm font-bold flex items-center justify-center">
                            {{ $item['rank'] }}
                        </span>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ $item['name'] }}</p>
                            <p class="text-xs text-gray-400">{{ $item['orders'] }} pesanan</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                                Rp {{ number_format($item['revenue'], 0, ',', '.') }}
                            </p>
                            <p class="text-xs {{ $item['trend'] === 'up' ? 'text-green-500' : ($item['trend'] === 'down' ? 'text-red-500' : 'text-gray-400') }}">
                                {{ $item['trend'] === 'up' ? '↑ Naik' : ($item['trend'] === 'down' ? '↓ Turun' : '→ Stabil') }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Tab: Aturan Asosiasi --}}
    @if($activeTab === 'association')
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5">
            <div class="flex items-center gap-2 mb-1">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Aturan Asosiasi Menu</h3>
            </div>
            <p class="text-xs text-gray-400 mb-4">Hasil algoritma Apriori / FP-Growth — menu yang sering dibeli bersamaan</p>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700">
                            <th class="text-left py-2 px-3 text-xs text-gray-500 font-medium">Jika beli</th>
                            <th class="text-left py-2 px-3 text-xs text-gray-500 font-medium">Maka beli</th>
                            <th class="text-center py-2 px-3 text-xs text-gray-500 font-medium">Support</th>
                            <th class="text-center py-2 px-3 text-xs text-gray-500 font-medium">Confidence</th>
                            <th class="text-center py-2 px-3 text-xs text-gray-500 font-medium">Lift</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($this->getAssociationRules() as $rule)
                            <tr class="border-b border-gray-50 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="py-3 px-3">
                                    <span class="bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 px-2 py-1 rounded text-xs font-medium">
                                        {{ $rule['antecedent'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 px-2 py-1 rounded text-xs font-medium">
                                        {{ $rule['consequent'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-center text-xs text-gray-600 dark:text-gray-300">{{ $rule['support'] }}</td>
                                <td class="py-3 px-3 text-center">
                                    <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ $rule['confidence'] }}</span>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span class="text-xs font-bold {{ (float)$rule['lift'] >= 2 ? 'text-green-600' : 'text-yellow-500' }}">
                                        {{ $rule['lift'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Tab: Jam Ramai --}}
    @if($activeTab === 'peak_hour')
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-4">Distribusi Pesanan per Jam</h3>
            @php $maxOrders = max(array_column($this->getPeakHourData(), 'orders')); @endphp
            <div class="space-y-2">
                @foreach($this->getPeakHourData() as $slot)
                    @php
                        $pct = round(($slot['orders'] / $maxOrders) * 100);
                        $isPeak = $slot['orders'] >= ($maxOrders * 0.7);
                    @endphp
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-gray-500 w-14 shrink-0">{{ $slot['hour'] }}</span>
                        <div class="flex-1 bg-gray-100 dark:bg-gray-700 rounded-full h-5 relative overflow-hidden">
                            <div
                                class="h-full rounded-full transition-all {{ $isPeak ? 'bg-indigo-500' : 'bg-indigo-200 dark:bg-indigo-800' }}"
                                style="width: {{ $pct }}%"
                            ></div>
                        </div>
                        <span class="text-xs font-medium text-gray-600 dark:text-gray-300 w-16 text-right">
                            {{ $slot['orders'] }} pesanan
                            @if($isPeak) <span class="text-orange-500">🔥</span> @endif
                        </span>
                    </div>
                @endforeach
            </div>
            <p class="text-xs text-gray-400 mt-4">
                Warna lebih gelap = jam ramai (≥70% dari puncak). Gunakan ini untuk optimasi jadwal staf dan stok bahan.
            </p>
        </div>
    @endif

</x-filament-panels::page>
