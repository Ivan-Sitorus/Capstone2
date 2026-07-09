# BAB I PENDAHULUAN

## 1.1 Latar Belakang

Manajemen inventori atau pengelolaan stok bahan baku merupakan proses inti dalam operasional kafe yang menentukan kelancaran produksi dan kepuasan pelanggan. Namun pada praktiknya, banyak kafe masih mencatat stok secara manual menggunakan buku atau spreadsheet, yang rentan terhadap kesalahan pencatatan, keterlambatan informasi stok, serta sulitnya melacak riwayat pergerakan stok secara akurat.

Salah satu contohnya adalah W9 Cafe di Semarang yang dikelola oleh STIE Totalwin. Dalam operasional sehari-hari, W9 Cafe masih menggunakan buku stok dan spreadsheet sehingga sering terjadi ketidaksesuaian antara catatan dengan kondisi aktual. Sistem POS yang ada belum dilengkapi modul manajemen inventori yang terintegrasi.

Sistem yang dikembangkan mencakup fitur pengelolaan data menu dan bahan baku, pengaturan stok bahan baku dengan mode deduksi FEFO (*First-Expiry-First-Out*) dan FIFO (*First-In-First-Out*), penyesuaian stok manual, serta pencatatan riwayat pemakaian bahan baku. Sistem dibangun menggunakan kerangka kerja Laravel dengan panel administrasi Filament untuk memudahkan pengelolaan data inventori.

## 1.2 Rumusan Masalah

Berdasarkan latar belakang, dirumuskan permasalahan sebagai berikut:

1. Bagaimana merancang dan membangun sistem manajemen inventori pada POS W9 Cafe menggunakan Laravel dan Filament?

2. Bagaimana sistem dapat meminimalkan pemborosan bahan baku melalui prioritas penggunaan stok berdasarkan masa kedaluwarsa serta memastikan konsistensi data stok?

3. Bagaimana sistem dapat mencatat pemakaian bahan baku secara otomatis setiap terjadi transaksi penjualan serta melacak riwayat perubahan stok?

4. Bagaimana hasil pengujian sistem dalam meningkatkan akurasi pencatatan stok dan efisiensi operasional?

5. Bagaimana hasil pengujian integrasi antara modul inventori dan modul transaksi dalam memastikan konsistensi data stok?

## 1.3 Batasan Masalah

1. Sistem berfokus pada manajemen inventori bahan baku dan fitur pendukung (autentikasi, dashboard, kategori, menu). Penelitian tidak mencakup laporan keuangan dan modul di luar konteks inventori.

2. Mode deduksi batch terbatas pada FEFO dan FIFO.

3. Sistem memerlukan koneksi internet untuk diakses.

4. Pengujian terbatas pada *black box*, *white box*, dan *integration test* yang mencakup verifikasi algoritma deduksi, konsistensi data antar modul, dan validasi fungsionalitas sistem.

## 1.4 Tujuan Penelitian

1. Mengimplementasikan sistem manajemen inventori pada POS W9 Cafe menggunakan Laravel dan Filament.

2. Menerapkan prioritas penggunaan stok berdasarkan masa kedaluwarsa (FEFO) dan urutan penerimaan (FIFO) untuk meminimalkan pemborosan bahan baku.

3. Mengintegrasikan pencatatan pemakaian bahan baku secara otomatis dengan setiap transaksi penjualan serta menyediakan riwayat perubahan stok yang dapat dilacak.

4. Melakukan pengujian sistem untuk memvalidasi akurasi pencatatan stok dan efisiensi operasional.

5. Melakukan pengujian integrasi untuk memvalidasi konsistensi aliran data antar modul inventori dan modul transaksi.

## 1.5 Manfaat Penelitian

1. **Manfaat bagi Pengembang**: Mendapatkan pengalaman dalam merancang sistem manajemen inventori yang terstruktur menggunakan Laravel dan Filament.

2. **Manfaat bagi Kafe**: Mendapatkan sistem manajemen inventori dengan deduksi stok otomatis dan riwayat pergerakan stok yang dapat dilacak.

3. **Manfaat bagi Peneliti Selanjutnya**: Menjadi referensi implementasi sistem manajemen inventori dengan deduksi stok otomatis.

## 1.6 Metodologi Penelitian

Penelitian ini menggunakan metode *Agile* melalui tahapan sebagai berikut:

1. **Requirements**: Identifikasi kebutuhan melalui observasi di W9 Cafe dan studi literatur.

2. **Design**: Perancangan basis data, arsitektur aplikasi, algoritma deduksi, dan antarmuka panel Filament.

3. **Development**: Implementasi secara bertahap dalam dua iterasi, yaitu inti inventori dan panel administrasi.

4. **Testing**: Pengujian *black box*, *white box*, dan *integration testing* untuk memvalidasi seluruh fungsionalitas dan integrasi sistem.

5. **Review**: Evaluasi hasil pengujian dan penyusunan dokumentasi.

## 1.7 Sistematika Penulisan

Tugas akhir ini terdiri atas lima bab. BAB I berisi pendahuluan. BAB II membahas kajian pustaka yang meliputi penelitian terdahulu dan landasan teori termasuk metode pengujian *black box*, *white box*, dan *integration testing*. BAB III menjelaskan perancangan sistem. BAB IV menyajikan implementasi algoritma inti dan hasil pengujian *black box*, *white box*, serta *integration*. BAB V berisi penutup.
