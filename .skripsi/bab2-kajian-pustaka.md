# BAB II KAJIAN PUSTAKA

## 2.1 Penelitian Terdahulu

Kajian penelitian ini membahas berbagai penelitian terdahulu yang memiliki keterkaitan dengan topik pengembangan sistem manajemen inventori dan POS berbasis web. Penelitian-penelitian tersebut menjadi referensi penting dalam memahami konsep sistem informasi manajemen stok, algoritma deduksi batch, serta penerapan teknologi pendukung seperti Laravel dan Filament. Kajian ini juga memperkuat landasan teoritis dan memberikan gambaran tentang efektivitas sistem digital dalam meningkatkan akurasi dan efisiensi operasional di bidang inventori serta transaksi penjualan.

Penelitian oleh Supron dan Susila [3] berjudul "Aplikasi *Point of Sales* (POS) Berbasis *Website* Dengan Menggunakan Laravel (Studi Kasus: Bakmi Djowo)" mengembangkan sistem POS berbasis web yang mencakup transaksi penjualan dan pengelolaan stok menu. Persamaan dengan penelitian ini adalah penggunaan Laravel sebagai kerangka kerja utama dan fokus pada pengelolaan stok, sedangkan perbedaannya terletak pada konteks: penelitian Supron dan Susila pada restoran Bakmi Djowo tanpa dukungan manajemen batch, sementara penelitian ini pada kafe dengan implementasi mode deduksi FEFO dan FIFO.

Selanjutnya, Devega, dkk. [4] dalam "Pembangunan Sistem Inventori Apotek Menggunakan Metode FIFO dan FEFO" mengimplementasikan kedua algoritma deduksi batch pada sistem inventori apotek. Persamaan dengan penelitian ini adalah fokus pada algoritma FIFO dan FEFO, sedangkan perbedaannya adalah penelitian Devega, dkk. diterapkan pada lingkungan apotek dengan pengelolaan obat, sementara penelitian ini menerapkan kedua algoritma pada sistem inventori bahan baku kafe yang terintegrasi dengan POS.

Al Farisi, dkk. [6] dalam "Implementasi Sistem Informasi Akademik Pengelolaan Tugas Akhir Berbasis Laravel dan Filament" membahas implementasi Filament sebagai panel administrasi Laravel. Persamaan dengan penelitian ini adalah penggunaan Filament untuk antarmuka administrasi, sedangkan perbedaannya adalah penelitian Al Farisi, dkk. berfokus pada sistem informasi akademik, sementara penelitian ini menerapkannya secara spesifik untuk manajemen inventori kafe.

Terakhir, Garbarz dan Plechawska-Wójcik [7] melakukan analisis komparatif kerangka kerja PHP Laravel dan Symfony. Persamaan dengan penelitian ini adalah pemilihan Laravel sebagai kerangka kerja utama, sedangkan perbedaannya adalah penelitian tersebut bersifat komparatif umum tanpa studi kasus spesifik.

Tabel 2.1 Kajian penelitian terdahulu.

| No | Peneliti, Tahun | Tujuan | Hasil |
|:--:|-----------------|--------|-------|
| 1 | Supron dan A. Susila (2023) | Sistem POS berbasis web | Transaksi dan stok, tanpa batch management |
| 2 | M. Devega, dkk. (2024) | FIFO dan FEFO inventory | FIFO dan FEFO untuk manajemen stok |
| 3 | R. Al Farisi, dkk. (2025) | Laravel Filament | Filament untuk panel administrasi |
| 4 | P. Garbarz dan M. Plechawska-Wójcik (2022) | Analisis Laravel dan Symfony | Laravel sebagai framework utama |

Sistem yang dikembangkan memiliki keunggulan dibanding penelitian terdahulu berupa deduksi stok otomatis berbasis resep bahan baku dengan modulasi per-bahan, mode deduksi FEFO dan FIFO yang dapat dikonfigurasi, pencatatan riwayat pergerakan stok yang tidak dapat diubah, mekanisme pengamanan transaksi untuk mencegah konflik data, serta panel administrasi Filament untuk pengelolaan data inventori.

## 2.2 Metode Penelitian

Metode penelitian menggunakan *Agile* dengan tahapan: *Requirements*, *Design*, *Development*, *Testing*, dan *Review* [12]. Setiap iterasi menghasilkan fitur yang telah diuji, kemudian dievaluasi untuk perbaikan pada iterasi berikutnya. Diagram alur metode Agile dapat dilihat pada Gambar 2.1.

## 2.3 Landasan Teori
### 2.3.1 Sistem Manajemen Inventori

Sistem manajemen inventori adalah serangkaian proses yang digunakan untuk mengelola, memantau, dan mengendalikan persediaan barang dalam suatu organisasi. Tujuan utamanya adalah memastikan ketersediaan stok yang cukup untuk memenuhi permintaan tanpa menimbulkan kelebihan stok yang dapat meningkatkan biaya penyimpanan [8]. Dalam konteks industri makanan dan minuman, inventori mencakup bahan baku dan barang jadi yang memerlukan pengelolaan khusus terkait masa kedaluwarsa.

### 2.3.2 PostgreSQL

PostgreSQL adalah sistem manajemen basis data relasional objek open source dengan reputasi stabil dan kaya fitur. PostgreSQL mendukung *row-level locking* melalui `SELECT ... FOR UPDATE`, *transaction isolation levels*, dan *foreign key constraints* yang diperlukan untuk menjaga integritas data pada aplikasi berbasis web [11].

### 2.3.3 Algoritma Deduksi Batch (FEFO dan FIFO)

Algoritma deduksi batch menentukan urutan penggunaan stok berdasarkan karakteristik masing-masing batch. **FEFO (*First-Expiry-First-Out*)** memprioritaskan batch dengan tanggal kedaluwarsa terdekat, sangat penting untuk bahan baku dengan masa simpan terbatas seperti bahan segar dan produk *dairy* [4]. Implementasi teknisnya menggunakan batch dengan tanggal kedaluwarsa terdekat terlebih dahulu. **FIFO** menggunakan batch yang diterima lebih awal terlebih dahulu [4].

### 2.3.4 Point of Sale (POS)

*Point of Sale* (POS) merupakan sistem yang digunakan untuk memproses transaksi penjualan dan mengelola data penjualan, produk, serta stok secara terintegrasi [15].

### 2.3.5 PHP

PHP (*Hypertext Preprocessor*) adalah bahasa pemrograman skrip yang banyak digunakan untuk pengembangan aplikasi web dinamis. PHP dapat disisipkan langsung ke dalam HTML dan memiliki dukungan luas terhadap berbagai basis data. Laravel sebagai kerangka kerja PHP yang digunakan dalam tugas akhir ini memanfaatkan kemampuan PHP untuk membangun aplikasi web yang terstruktur [7].

### 2.3.6 Laravel

Laravel adalah kerangka kerja aplikasi web berbasis PHP yang bersifat terbuka dan menyediakan berbagai fitur seperti sistem *routing*, manajemen basis data melalui migration, dan sistem autentikasi bawaan yang memudahkan pengembangan aplikasi web modern [8].

### 2.3.7 Filament

Filament adalah pustaka antarmuka pengguna yang dibangun di atas Laravel dan menyediakan komponen antarmuka siap pakai seperti tabel data dan formulir yang terintegrasi dengan basis data, sehingga mempercepat pembuatan antarmuka administrasi [9].

### 2.3.8 Entity Relationship Diagram

*Entity Relationship Diagram* (ERD) merupakan model pemodelan data yang disusun berdasarkan objek-objek yang ada di dunia nyata. ERD digunakan untuk menggambarkan hubungan antar data dalam sebuah basis data secara logis sehingga mudah dipahami oleh pengembang dan pengguna [16]. ERD terdiri dari entitas (seperti Ingredient, Menu, StockMovement), atribut (kolom-kolom dalam tabel), dan relasi antar entitas (seperti one-to-many, many-to-many). Pada sistem inventori, ERD digunakan untuk memodelkan hubungan antara bahan baku dengan batch stok, menu dengan resep, serta keterkaitan antara stok dengan pergerakannya.

### 2.3.9 Activity Diagram

*Activity diagram* merupakan salah satu diagram dalam *Unified Modeling Language* (UML) yang digunakan untuk menggambarkan alur aktivitas atau proses dalam suatu sistem. Diagram ini menampilkan urutan kegiatan dari awal hingga akhir melalui aliran kontrol antar aktivitas. *Activity diagram* terdiri dari elemen-elemen seperti *initial node* (titik awal), *action node* (aktivitas), *decision node* (percabangan berdasarkan kondisi), serta *swimlane* yang memisahkan aktivitas berdasarkan aktor yang bertanggung jawab [16].
