# BAB V PENUTUP

## 5.1 Kesimpulan

Berdasarkan hasil perancangan, implementasi, dan pengujian sistem manajemen inventori pada *Point of Sale* W9 Cafe menggunakan Laravel dan Filament, dapat ditarik kesimpulan sebagai berikut:

1. Sistem berhasil mengimplementasikan manajemen inventori yang mampu mengelola dua jalur stok paralel: bahan baku berbasis resep melalui dekomposisi komposisi resep pada tabel pivot `menu_ingredients`, dan stok menu produk jadi melalui `MenuStock` yang terhubung 1:1 dengan menu.

2. Sistem berhasil mengimplementasikan mode deduksi batch FEFO dan FIFO dengan *pessimistic locking* menggunakan `lockForUpdate()` dan `ORDER BY id ASC` untuk mencegah deadlock pada transaksi konkuren.

3. Sistem berhasil diintegrasikan dengan modul transaksi kasir melalui mekanisme *Service Layer* yang dipanggil dari beberapa titik masuk (*entry point*), serta mengimplementasikan *immutable audit trail* pada `StockMovement` dan `MenuStockMovement` yang mencegah operasi `update` dan `delete`, sehingga setiap perubahan stok tercatat secara permanen.

4. Pengujian *black box* terhadap 6 modul menunjukkan seluruh 15 skenario berhasil. Pengujian *white box* memvalidasi kebenaran algoritma FIFO, FEFO, *immutable audit trail*, idempotensi, dan rollback transaksi. Pengujian performa membuktikan sistem mampu menangani transaksi konkuren tanpa deadlock.

Selama proses implementasi, panel administrasi Filament berhasil dibangun dengan 6 *resource*, 2 halaman *tabbed*, dan 3 *relation manager* yang menyediakan antarmuka intuitif untuk pengelolaan data inventori.

## 5.2 Saran

Berdasarkan hasil penelitian, terdapat beberapa saran untuk pengembangan lebih lanjut:

1. **Notifikasi Stok Menipis**: Sistem dapat dilengkapi notifikasi otomatis ketika stok bahan baku di bawah ambang batas.

2. **Prediksi Kebutuhan Stok**: Berdasarkan data historis pemakaian harian.

3. **Integrasi Barcode Scanner**: Untuk mempercepat penerimaan dan inventarisasi stok.

4. **Mode Batch Tambahan**: LIFO atau *Average Cost*.

5. **Dashboard Analitik**: Visualisasi tren pemakaian dan efisiensi stok.
