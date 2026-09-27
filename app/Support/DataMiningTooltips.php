<?php

namespace App\Support;

/**
 * Teks tooltip istilah data mining.
 *
 * Format: kepanjangan istilah (jika ada), lalu penjelasan singkat dengan
 * bahasa sehari-hari. Dipakai oleh halaman Filament data mining lewat
 * App\Filament\Tables\Components\ColumnInfoTooltip.
 */
final class DataMiningTooltips
{
    public const MAE = 'Mean Absolute Error: rata-rata selisih absolut antara nilai aktual dan prediksi. Semakin kecil nilainya, semakin akurat model.';

    public const RMSE = 'Root Mean Squared Error: akar dari rata-rata kuadrat selisih aktual dan prediksi. Selisih besar dihukum lebih berat. Semakin kecil nilainya, semakin akurat.';

    public const MAPE = 'Mean Absolute Percentage Error: rata-rata persentase selisih absolut terhadap nilai aktual. Semakin kecil nilainya, semakin akurat.';

    public const SMAPE = 'Symmetric Mean Absolute Percentage Error: versi simetris MAPE yang membagi selisih dengan rata-rata nilai aktual dan prediksi. Semakin kecil nilainya, semakin akurat.';

    public const SUPPORT = 'Persentase transaksi yang memuat kombinasi menu ini dari seluruh transaksi yang dianalisis.';

    public const CONFIDENCE = 'Peluang menu B juga dipesan bila menu A sudah dipesan.';

    public const LIFT = 'Perbandingan peluang B dipesan setelah A dengan peluang B dipesan secara umum. Nilai di atas 1 berarti hubungan positif.';

    public const SILHOUETTE = 'Skor -1 sampai 1 yang mengukur seberapa padat dan terpisah antar klaster. Semakin tinggi, semakin baik.';

    public const INERTIA = 'Inertia (SSE): total jarak kuadrat titik data ke pusat klasternya. Nilai menurun saat jumlah klaster bertambah; titik siku grafik (elbow) menandai K optimal.';

    public const K_OPTIMAL = 'Jumlah klaster (K) terbaik berdasarkan skor Silhouette tertinggi.';

    public const K_SIL_BADGE = "K: jumlah klaster optimal berdasarkan skor Silhouette tertinggi.\nSil: Silhouette Score, skor kualitas pengelompokan (-1 sampai 1, semakin tinggi semakin baik).";

    public const TOTAL_PREDICTION = 'Total unit yang diprediksi selama periode prediksi.';

    public const AVG_PER_DAY = 'Rata-rata hasil prediksi per hari.';

    public const LOWER_BOUND = 'Batas bawah interval kepercayaan 95% dari nilai prediksi.';

    public const UPPER_BOUND = 'Batas atas interval kepercayaan 95% dari nilai prediksi.';

    public const DAY_TYPE = 'Klasifikasi hari menjadi Weekday (Senin-Jumat) atau Weekend (Sabtu-Minggu).';

    public const MODEL = 'Algoritma peramalan yang dipakai. Prophet adalah model deret waktu dari Meta.';

    public const TOTAL_QTY = 'Total unit menu yang terjual selama periode data.';

    public const TOTAL_PROFIT = 'Total keuntungan kotor dari penjualan menu (harga jual dikurangi biaya modal) selama periode data.';

    public const CLUSTER = 'Kelompok menu hasil algoritma K-Means berdasarkan kemiripan jumlah penjualan dan keuntungan.';

    public const CATEGORY = 'Kategori penjualan menu berdasarkan total jumlah penjualannya.';

    public const AVG_QTY = 'Rata-rata unit terjual per menu dalam klaster ini.';

    public const AVG_PROFIT = 'Rata-rata keuntungan per menu dalam klaster ini.';

    public const TOTAL_USAGE = 'Total pemakaian bahan baku selama periode data.';

    public const AVG_USAGE = 'Rata-rata pemakaian bahan baku selama periode data.';

    public const ITEM_COUNT = 'Jumlah bahan baku yang tergabung dalam klaster ini.';

    public const CLUSTER_AVG_USAGE = 'Rata-rata pemakaian bahan baku pada klaster ini.';

    public const MENU_ANALYZED = 'Jumlah menu yang ikut dianalisis.';

    public const INGREDIENTS_ANALYZED = 'Jumlah bahan baku yang ikut dianalisis.';

    public const TOTAL_RULES = 'Jumlah aturan asosiasi yang lolos ambang Min Support dan Min Confidence.';

    public const TOTAL_TRANSACTIONS = 'Jumlah transaksi yang dianalisis.';

    public const MIN_SUPPORT = 'Ambang batas minimum Support agar aturan dianggap layak.';

    public const MIN_CONFIDENCE = 'Ambang batas minimum Confidence agar aturan dianggap layak.';

    public const PAIR_COUNT = 'Jumlah transaksi yang memuat menu A lalu menu B.';
}
