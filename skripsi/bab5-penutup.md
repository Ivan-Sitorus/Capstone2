# BAB V PENUTUP

## 5.1 Kesimpulan

Berdasarkan hasil perancangan, implementasi, dan pengujian sistem manajemen inventori pada *Point of Sale* W9 Cafe menggunakan Laravel dan Filament, dapat ditarik kesimpulan sebagai berikut:

1. Sistem berhasil menerapkan manajemen inventori berbasis bahan baku dengan resep terintegrasi yang memungkinkan setiap menu yang terjual memberikan dampak langsung terhadap stok bahan baku terkait.

2. Sistem berhasil menerapkan prioritas penggunaan stok berdasarkan masa kedaluwarsa (FEFO) dan urutan penerimaan (FIFO) yang meminimalkan pemborosan bahan baku serta menjaga konsistensi data stok.

3. Sistem berhasil mencatat pemakaian bahan baku secara otomatis setiap kali terjadi transaksi penjualan dan menyediakan riwayat perubahan stok yang dapat dilacak secara lengkap.

4. Pengujian *black box* terhadap seluruh modul menunjukkan skenario berhasil. Pengujian *white box* memvalidasi kebenaran deduksi stok berbasis resep, algoritma FIFO dan FEFO, penyesuaian stok, serta pembatalan penyesuaian stok. Pengujian *gray box* memvalidasi konsistensi aliran data antar modul inventori dan modul transaksi.

## 5.2 Saran

Berdasarkan hasil penelitian, terdapat beberapa saran untuk pengembangan lebih lanjut:

1. **Aplikasi Mobile** agar sistem dapat diakses dengan mudah ketika admin atau kasir sedang tidak berada di dekat PC.

2. **Notifikasi Stok Menipis dan Kedaluwarsa** melalui aplikasi perpesanan seperti WhatsApp ketika stok bahan baku berada di bawah ambang batas atau mendekati tanggal kedaluwarsa.

3. **Integrasi Barcode Scanner** untuk mempercepat proses pencatatan dan identifikasi batch stok bahan baku.
