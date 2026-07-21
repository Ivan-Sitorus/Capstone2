# ABSTRAK

Manajemen inventori merupakan aspek kritis dalam operasional kafe yang mempengaruhi ketersediaan bahan baku, kelancaran produksi, dan kepuasan pelanggan. Pada praktiknya, banyak kafe masih melakukan pencatatan stok secara manual menggunakan nota kertas atau spreadsheet, yang rentan terhadap kesalahan pencatatan, keterlambatan informasi stok, serta sulitnya melacak riwayat pergerakan stok. Penelitian ini bertujuan merancang dan mengimplementasikan sistem manajemen inventori pada *Point of Sale* W9 Cafe menggunakan Laravel dan Filament yang mampu mengelola stok bahan baku berbasis resep.

Sistem dikembangkan menggunakan kerangka kerja Laravel 13 dengan panel administrasi Filament yang menyediakan antarmuka pengelolaan data dan manajemen batch secara intuitif. Sistem mengimplementasikan dua mode deduksi batch yaitu FEFO (*First-Expiry-First-Out*) dan FIFO (*First-In-First-Out*), dengan mekanisme penguncian data untuk mencegah konflik pada transaksi bersamaan. Integrasi dengan modul transaksi kasir dilakukan secara otomatis di mana deduksi stok terjadi sebagai bagian dari pemrosesan pesanan.

Pengujian dilakukan dengan metode *black box* dan *white box*. Hasil pengujian *black box* terhadap seluruh modul menunjukkan skenario berhasil. Pengujian *white box* memvalidasi kebenaran algoritma FIFO dan FEFO. Seluruh mekanisme deduksi stok dan pencatatan pergerakan berjalan sesuai perancangan.

Kata kunci: Sistem manajemen inventori, *Point of Sale*, Laravel, Filament, FEFO, FIFO.
