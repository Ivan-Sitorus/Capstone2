# BAB III PERANCANGAN SISTEM

## 3.1 Gambaran Proses Bisnis
### 3.1.1 Proses Bisnis Saat Ini

Pada sistem yang berjalan saat ini, pencatatan inventori di kafe masih dilakukan secara manual. Admin mencatat penerimaan bahan baku di buku stok, kasir menulis pesanan pada nota kertas, dan stock opname dilakukan secara periodik, yang menyebabkan kesalahan pencatatan dan keterlambatan informasi stok.

### 3.1.2 Proses Bisnis yang Dikembangkan

Sistem yang dikembangkan mengotomatiskan pencatatan dan deduksi stok. Admin mengelola data bahan baku dan resep melalui panel Filament, dan ketika kasir memproses pesanan, sistem secara otomatis mendeduksi stok berdasarkan resep menu dengan pencatatan immutable.

## 3.2 Perancangan Proses dan Alur Sistem
### 3.2.1 Use Case Diagram

Gambar 3.1 Use case diagram sistem manajemen inventori.

Use case diagram pada Gambar 3.1 menggambarkan interaksi antara dua aktor: Admin dan Sistem. Admin bertanggung jawab mengelola data inventori (login, kelola bahan baku, batch stok, resep menu, penyesuaian stok, dan dashboard), sedangkan Sistem secara otomatis menjalankan deduksi stok ketika pesanan diproses.

### 3.2.2 Flowchart Proses Deduksi Stok

Proses deduksi stok dimulai ketika pesanan masuk melalui modul transaksi kasir. Sistem mendekomposisi setiap menu berdasarkan resep pada tabel pivot `menu_ingredients`, kemudian mendebet stok dari `IngredientBatch` sesuai mode FEFO atau FIFO dalam satu proses yang konsisten.

Gambar 3.2 Flowchart proses deduksi stok.

### 3.2.3 Activity Diagram

*Activity diagram* merupakan salah satu jenis diagram dalam *Unified Modeling Language* (UML) yang digunakan untuk menggambarkan alur aktivitas atau proses dalam suatu sistem. Diagram ini menampilkan urutan kegiatan dari awal hingga akhir melalui aliran kontrol antar aktivitas [16].

Gambar 3.5 Activity diagram kelola bahan baku.

Gambar 3.5 memperlihatkan activity diagram ketika admin ingin menambah bahan baku baru ke dalam sistem. Pertama-tama, admin membuka halaman daftar bahan baku dan menekan tombol "Tambah Bahan Baku". Sistem akan menampilkan formulir yang berisi input nama bahan baku, pilihan unit satuan, dan mode batch (FEFO atau FIFO). Setelah admin mengisi data dan menekan tombol "Simpan", sistem memvalidasi input dan menyimpan data ke dalam tabel `ingredients` di database. Database mengembalikan respons sukses, dan sistem menampilkan notifikasi bahwa data bahan baku berhasil ditambahkan.

Gambar 3.6 Activity diagram kelola batch stok.

Gambar 3.6 memperlihatkan activity diagram ketika admin ingin menambah batch stok untuk suatu bahan baku. Admin memilih bahan baku dari daftar, lalu menekan tombol "Kelola Batch" dan kemudian "Tambah Batch". Sistem menampilkan formulir batch yang berisi input jumlah stok, tanggal kedaluwarsa (untuk mode FEFO) atau tanggal diterima (untuk mode FIFO), dan harga per unit. Setelah admin mengisi data dan menekan "Simpan", sistem memvalidasi input dan menyimpan data ke tabel `ingredient_batches` dengan relasi ke `ingredient_id`. Database mengembalikan respons sukses, dan sistem menampilkan notifikasi bahwa batch baru berhasil ditambahkan.

Gambar 3.7 Activity diagram penyesuaian stok.

Gambar 3.7 memperlihatkan activity diagram ketika admin melakukan penyesuaian stok manual. Admin membuka halaman penyesuaian stok dan memilih bahan baku yang akan disesuaikan, kemudian memilih tipe penyesuaian (penambahan atau pengurangan) serta mengisi jumlah dan alasan. Setelah menekan "Simpan", sistem memvalidasi bahwa jumlah lebih besar dari nol, kemudian mencatat penyesuaian ke tabel `stock_adjustments` dan pergerakan stok ke tabel `stock_movements`, serta memperbarui kuantitas pada batch terkait di database. Database melakukan COMMIT untuk menjaga konsistensi data, dan sistem menampilkan notifikasi bahwa penyesuaian stok berhasil.

Gambar 3.8 Activity diagram kelola resep menu.

Gambar 3.8 memperlihatkan activity diagram ketika admin ingin mengelola resep (komposisi bahan baku) suatu menu. Admin membuka halaman daftar menu dan memilih menu yang akan diatur resepnya, kemudian membuka tab "Resep" pada panel Filament. Admin memilih bahan baku dari dropdown dan mengisi jumlah pemakaian per unit menu, lalu menekan "Simpan". Sistem memvalidasi bahwa bahan baku yang dipilih belum ada di resep menu tersebut, kemudian menyimpan relasi ke tabel pivot `menu_ingredients`. Database mengembalikan respons sukses, dan sistem menampilkan notifikasi bahwa resep berhasil disimpan.

### 3.2.4 Rumus Prediksi Sisa Jual

Rumus 3.1 Rumus prediksi Sisa Jual.

```
SisaJual(m) = MIN( floor( s(i) / u(i) ) )
```

Di mana:
- **m** adalah menu yang akan dihitung
- **i** adalah setiap bahan baku dalam resep menu *m*
- **s(i)** adalah total stok tersedia untuk bahan baku *i*
- **u(i)** adalah jumlah bahan baku *i* yang digunakan per unit menu *m*
- **floor()** adalah fungsi pembulatan ke bawah
- **MIN** adalah fungsi pengambilan nilai minimum

Rumus ini digunakan untuk menghitung jumlah maksimum suatu menu yang dapat dipesan berdasarkan ketersediaan stok bahan baku. Semakin kecil nilai *s(i) / u(i)* untuk suatu bahan baku, semakin terbatas kemampuan menu tersebut untuk diproduksi. Informasi ini membantu admin dalam menentukan prioritas pengadaan stok dan memberikan estimasi akurat mengenai ketersediaan menu.

**Contoh Perhitungan:**

Menu "Kopi Susu" membutuhkan 10 gram kopi bubuk dan 5 gram susu per porsi. Dengan stok kopi 100 gram dan susu 20 gram, maka:
- floor(100 / 10) = 10
- floor(20 / 5) = 4
- **SisaJual = MIN(10, 4) = 4 porsi**

## 3.3 Kebutuhan Sistem
### 3.3.1 Kebutuhan Fungsional

Kebutuhan fungsional merupakan kebutuhan yang berkaitan dengan fungsi-fungsi spesifik yang harus disediakan oleh sistem. Seluruh kebutuhan fungsional sistem diverifikasi melalui pengujian *black box* yang berfokus pada validasi input dan output sistem.

Kebutuhan fungsional sistem manajemen inventori diidentifikasi dari use case diagram dan diberi kode unik berawalan INV, sebagaimana disajikan pada Tabel 3.1.

Tabel 3.1 Kebutuhan fungsional sistem.

| No | Kode | Deskripsi | Aktor | Prioritas |
|:--:|------|-----------|-------|:---------:|
| 1 | INV-F01 | Admin dapat mengelola data bahan baku | Admin | Tinggi |
| 2 | INV-F02 | Admin dapat mengelola batch stok | Admin | Tinggi |
| 3 | INV-F03 | Sistem mendukung deduksi batch FEFO dan FIFO | Sistem | Tinggi |
| 4 | INV-F04 | Sistem mencatat pergerakan stok secara permanen | Sistem | Tinggi |
| 5 | INV-F05 | Admin dapat melakukan penyesuaian stok manual | Admin | Tinggi |
| 6 | INV-F06 | Admin dapat mengelola resep menu | Admin | Tinggi |
| 7 | INV-F07 | Sistem melakukan deduksi stok otomatis saat pesanan diproses | Sistem | Tinggi |
| 8 | INV-F08 | Sistem menjaga konsistensi data stok | Sistem | Tinggi |
| 9 | INV-F09 | Admin dapat login dan logout | Admin | Tinggi |
| 10 | INV-F10 | Admin dapat melihat dashboard ringkasan stok | Admin | Sedang |
| 11 | INV-F11 | Admin dapat mengelola data kategori | Admin | Sedang |
| 12 | INV-F12 | Admin dapat mengelola data menu | Admin | Sedang |

### 3.3.2 Kebutuhan Non-Fungsional

Kebutuhan non-fungsional merupakan kebutuhan yang berkaitan dengan kualitas sistem dan diuji melalui pengujian *white box* serta verifikasi struktur.

Kebutuhan non-fungsional berkaitan dengan kualitas sistem sebagaimana disajikan pada Tabel 3.2.

Tabel 3.2 Kebutuhan non-fungsional.

| No | Kode | Parameter | Target | Verifikasi |
|:--:|------|-----------|--------|------------|
| 1 | INV-NF01 | Akurasi Deduksi | Deduksi stok sesuai resep menu | Uji deduksi berbasis resep |
| 2 | INV-NF02 | Prioritas Batch | Mode deduksi FIFO dan FEFO | Uji algoritma FIFO dan FEFO |
| 3 | INV-NF03 | Akurasi Penyesuaian | Penyesuaian stok increase dan decrease | Uji penyesuaian stok |
| 4 | INV-NF04 | Pemulihan Stok | Pembatalan mengembalikan stok awal | Uji pembatalan penyesuaian |
| 5 | INV-NF05 | Konsistensi Data | Transaksi dalam satu proses yang konsisten | Uji konsistensi batch |

Seluruh kebutuhan fungsional pada Tabel 3.1 akan diuji melalui pengujian *black box* di Bab IV, sedangkan kebutuhan non-fungsional pada Tabel 3.2 akan diuji melalui pengujian *white box*.

## 3.4 Lingkungan Pengembangan Sistem
### 3.4.1 Lingkungan Pengembangan

Dalam proses pengembangan sistem manajemen inventori berbasis web, digunakan beberapa perangkat keras dan perangkat lunak pendukung. Rincian spesifikasi perangkat keras yang digunakan selama proses pengembangan dapat dilihat pada Tabel 3.3.

Tabel 3.3 Perangkat keras pengembangan.

| Perangkat Keras | Nama | Spesifikasi |
|----------------|------|-------------|
| Laptop Pengembangan | Lenovo IdeaPad Slim 1 | Prosesor: AMD Ryzen 3 3250U, RAM: 8 GB DDR4, Storage: 256 GB SSD, Layar: 14 inci HD, OS: Windows 11 |

Selain perangkat keras, beberapa perangkat lunak juga digunakan untuk mendukung proses pengembangan sistem. Detail spesifikasi perangkat lunak yang digunakan dapat dilihat pada Tabel 3.4.

Tabel 3.4 Perangkat lunak pengembangan.

| Perangkat Lunak | Nama Perangkat |
|----------------|----------------|
| Kerangka Kerja | Laravel Framework |
| Bahasa Pemrograman | PHP |
| Basis Data | PostgreSQL |
| Admin Panel | Filament |
| Container | Docker |
| Code Editor | VS Code |
| Version Control | Git |
| Web Browser | Chrome |

## 3.5 Perancangan Arsitektur Sistem
### 3.5.1 Arsitektur Umum

Arsitektur umum sistem terbagi menjadi tiga subsistem utama, yaitu Sistem Transaksi, Sistem Inventori, dan Sistem Data Mining. Sistem Transaksi menangani proses pemesanan yang dilakukan oleh kasir dan pelanggan melalui antarmuka masing-masing, yang kemudian diproses oleh Modul Transaksi. Sistem Inventori mencakup Panel Admin (Filament) yang digunakan oleh admin untuk mengelola data inventori, serta *business logic* inventori yang menangani seluruh logika pencatatan dan perubahan stok. Sistem Data Mining (dikembangkan dalam penelitian terpisah) melakukan analisis pola penjualan dan prediksi kebutuhan bahan baku. Seluruh data disimpan dan dikelola pada PostgreSQL sebagai basis data utama.

Gambar 3.3 Arsitektur umum keseluruhan sistem.

Admin mengakses Panel Admin melalui *web browser* untuk mengelola data inventori seperti bahan baku, *batch* stok, resep menu, dan penyesuaian stok. Kasir dan pelanggan masing-masing mengakses antarmuka melalui *web browser* untuk terhubung ke antarmuka masing-masing, yang kemudian akan terhubung ke modul transaksi untuk memproses pesanan. Ketika transaksi diproses, modul transaksi secara otomatis memicu deduksi stok ke sistem inventori. Panel Admin juga terhubung ke sistem inventori untuk seluruh operasi pengelolaan data. *Business logic* sistem inventori kemudian membaca dan menyimpan data ke PostgreSQL. Penelitian ini berfokus pada pengembangan sistem inventori yang mencakup panel admin dan *business logic* sistem inventori, sedangkan sistem transaksi merupakan modul yang sudah dikembangkan dalam penelitian terpisah. Seluruh data disimpan dan dikelola pada PostgreSQL sebagai basis data utama, yang juga digunakan oleh modul *Data Mining* (pengembangan terpisah) untuk analisis pola penjualan dan prediksi bahan baku.

### 3.5.2 Arsitektur Detail Sistem Inventori

Arsitektur detail sistem inventori menggambarkan lapisan-lapisan yang menyusun subsistem inventori secara lebih rinci. Sistem inventori terdiri dari lima lapisan yang saling terhubung.

Lapisan pertama adalah **Lapisan Presentasi** yang terdiri dari Panel Admin Filament yang digunakan oleh admin untuk mengelola data inventori, serta halaman kasir (Inertia.js + React) yang menampilkan informasi menu dan stok kepada kasir. Lapisan kedua adalah **Lapisan Controller** yang terdiri dari CashierPesananBaruController dan CashierOrderController yang bertugas menerima permintaan dari pengguna dan mendelegasikannya ke lapisan service.

Lapisan ketiga adalah **Lapisan Service (Business Logic)** yang merupakan inti dari sistem inventori. Lapisan ini terdiri dari InventoryService yang mengimplementasikan algoritma deduksi batch FEFO/FIFO, StockReconciliationService yang menangani logika penyesuaian stok dan pembatalannya, serta OrderPromotionService yang menangani perhitungan diskon. Lapisan keempat adalah **Lapisan Model (Data Access)** yang terdiri dari model Eloquent: Menu, Ingredient, IngredientBatch, StockMovement, StockAdjustment, dan DailyIngredientUsage. Model-model ini bertanggung jawab untuk membaca dan menulis data ke database.

Lapisan kelima adalah **Database PostgreSQL** yang menyimpan seluruh data inventori. Alur data dimulai ketika pesanan masuk melalui Lapisan Presentasi, kemudian Controller mendelegasikan ke InventoryService. Service melakukan query ke IngredientBatch dengan urutan FEFO atau FIFO, mendeduksi stok dari batch yang sesuai, mencatat perubahan ke StockMovement, dan memperbarui DailyIngredientUsage.

## 3.6 Perancangan Basis Data
### 3.6.1 Entity Relationship Diagram

Terdapat lima tabel utama: ingredients, ingredient_batches, menu_ingredients, stock_movements, dan stock_adjustments.

Gambar 3.4 ERD sistem inventori.

### 3.6.2 Deskripsi Entitas

Pada implementasi sistem manajemen inventori, desain fisik database dijelaskan secara rinci melalui tabel-tabel berikut yang mencakup seluruh spesifikasi teknis penyimpanan data berdasarkan hasil transformasi dari Entity Relationship Diagram (ERD).

Tabel 3.6 Struktur tabel ingredients.

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | BIGINT PK | Primary key, auto-increment |
| name | VARCHAR(255) | Nama bahan baku |
| unit | ENUM | Satuan unit |
| batch_mode | VARCHAR(255) | Mode deduksi batch (fefo/fifo) |
| is_active | BOOLEAN | Status aktif |
| deleted_at | TIMESTAMP | Soft delete |

Tabel 3.7 Struktur tabel ingredient_batches.

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | BIGINT PK | Primary key, auto-increment |
| ingredient_id | BIGINT FK | Foreign key ke ingredients |
| quantity | DECIMAL(12,2) | Jumlah stok |
| expiry_date | DATE | Kedaluwarsa (FEFO) |
| received_at | TIMESTAMP | Penerimaan (FIFO) |
| cost_per_unit | DECIMAL(12,2) | Harga per unit |

Tabel 3.8 Struktur tabel menu_ingredients.

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | BIGINT PK | Primary key, auto-increment |
| menu_id | BIGINT FK | Foreign key ke menus |
| ingredient_id | BIGINT FK | Foreign key ke ingredients |
| quantity_used | DECIMAL(12,2) | Jumlah bahan per unit menu |

Tabel 3.9 Struktur tabel stock_movements (immutable).

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | BIGINT PK | Primary key, auto-increment |
| ingredient_id | BIGINT FK | Foreign key ke ingredients |
| movement_type | ENUM | sale, purchase, adjustment |
| quantity_before, change, after | DECIMAL(12,2) | Snapshot stok |
| recorded_by | BIGINT FK | Foreign key ke users |


