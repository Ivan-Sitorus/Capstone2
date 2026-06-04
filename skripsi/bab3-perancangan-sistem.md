# BAB III PERANCANGAN SISTEM

## 3.1 Gambaran Proses Bisnis
### 3.1.1 Proses Bisnis Saat Ini

Pada sistem yang berjalan saat ini, pencatatan inventori di kafe masih dilakukan secara manual. Admin mencatat penerimaan bahan baku di buku stok, kasir menulis pesanan pada nota kertas, dan stok tidak langsung berkurang saat pesanan diproses. Penyesuaian stok dilakukan secara periodik melalui stock opname manual. Permasalahan yang muncul meliputi kesalahan pencatatan, keterlambatan informasi stok, dan tidak adanya mekanisme deduksi stok otomatis.

### 3.1.2 Proses Bisnis yang Dikembangkan

Sistem yang dikembangkan mengotomatiskan pencatatan dan deduksi stok. Admin mengelola data bahan baku dan resep melalui panel Filament. Ketika kasir memproses pesanan, sistem secara otomatis mendeduksi stok berdasarkan resep menu. Setiap perubahan stok dicatat secara immutable. Admin dapat memantau stok secara real-time melalui dashboard.

## 3.2 Perancangan Proses dan Alur Sistem
### 3.2.1 Use Case Diagram

Use case diagram pada Gambar 3.1 menggambarkan interaksi antara dua aktor utama dalam sistem manajemen inventori, yaitu Admin dan Sistem. Admin merupakan pengguna yang memiliki hak akses penuh terhadap seluruh fitur pengelolaan data inventori melalui panel administrasi. Sistem merupakan aktor yang secara otomatis menjalankan proses deduksi stok ketika pesanan diproses melalui modul transaksi kasir.

1. Aktor Admin

Admin merupakan aktor yang bertanggung jawab mengelola seluruh data inventori dalam sistem. Admin dapat melakukan aktivitas sebagai berikut:

* Login Sistem: Admin melakukan login untuk dapat mengakses panel administrasi. Proses login melibatkan autentikasi untuk memverifikasi identitas berdasarkan data akun dan create session untuk membuat sesi pengguna setelah login berhasil.
* Cek Session: Setiap aktivitas yang dilakukan oleh Admin selalu melalui proses pengecekan session untuk memastikan bahwa pengguna masih dalam kondisi login aktif dan memiliki hak akses yang valid.
* Kelola Bahan Baku: Admin memilih menu Bahan Baku pada panel navigasi, sistem menampilkan tabel daftar ingredient. Admin dapat menekan tombol "Tambah" untuk mengisi form yang terdiri dari input nama bahan baku, select unit (gram, kg, ml, liter, pcs, sachet), dan select mode batch (FEFO/FIFO). Data disimpan ke tabel ingredients melalui StockResource Filament.
* Kelola Batch Stok: Admin memilih bahan baku tertentu dari daftar, lalu masuk ke halaman ManageBatches. Admin mengisi form batch dengan jumlah stok, tanggal kedaluwarsa (untuk mode FEFO), tanggal penerimaan (untuk mode FIFO), dan harga satuan. Sistem menyimpan data ke tabel ingredient_batches dengan foreign key ke ingredients.id.
* Kelola Resep Menu: Admin memilih menu pada daftar, lalu membuka tab IngredientsRelationManager yang menampilkan tabel pivot. Admin memilih ingredient dari dropdown dan mengisi quantity_used (jumlah bahan baku per unit menu). Sistem menyimpan relasi ke tabel pivot menu_ingredients.
* Penyesuaian Stok: Admin memilih bahan baku, memilih tipe penyesuaian (increase atau decrease) pada form, mengisi jumlah, dan memberikan alasan. Sistem memvalidasi input melalui StockReconciliationService, mencatat perubahan ke stock_adjustments, dan memperbarui stok pada ingredient_batches.
* Kelola Stok Menu: Admin dapat mengelola stok menu produk jadi melalui halaman MenuStock dengan form yang identik dengan management batch bahan baku.
* Melihat Dashboard: Admin membuka halaman dashboard yang menampilkan widget ringkasan berupa total bahan baku, total batch stok, dan daftar menu tidak aktif. Data diambil melalui query agregat pada model Ingredient, Menu, dan MenuStock.

Seluruh aktivitas yang dilakukan oleh Admin selalu terhubung dengan fitur Cek Session untuk menjaga keamanan sistem. Apabila session tidak valid atau pengguna belum melakukan login, maka sistem akan menolak akses terhadap seluruh fitur.

2. Aktor Sistem

Sistem merupakan aktor yang secara otomatis menjalankan proses-proses berikut tanpa interaksi langsung dari pengguna:

* Deduksi Stok Otomatis: Sistem secara otomatis mendebet stok bahan baku ketika pesanan diproses melalui modul transaksi kasir. Proses ini mencakup pengecekan idempotensi, pre-validasi stok, dan pencatatan stock movement.
* Pembuatan MenuStock: Sistem secara otomatis membuat MenuStock ketika menu baru tanpa resep dibuat atau ketika seluruh bahan baku dihapus dari resep suatu menu.

### 3.2.2 Flowchart Proses Deduksi Stok

Proses deduksi stok dimulai ketika pesanan masuk melalui modul transaksi kasir. Sistem akan memeriksa apakah menu yang dipesan memiliki resep atau tidak. Jika memiliki resep, sistem akan mendekomposisi jumlah pemakaian berdasarkan komposisi bahan baku yang terdaftar pada tabel pivot menu_ingredients, kemudian mendebet stok dari IngredientBatch sesuai mode deduksi yang dikonfigurasi. Jika tidak memiliki resep (produk jadi), sistem akan mendebet stok dari MenuStockBatch melalui MenuStockService. Seluruh proses dilakukan dalam satu transaksi basis data dengan pessimistic locking untuk mencegah race condition.

Gambar 3.2 Flowchart proses deduksi stok.

## 3.3 Kebutuhan Sistem
### 3.3.1 Kebutuhan Fungsional

Berdasarkan use case diagram pada Gambar 3.1, dapat diidentifikasi kebutuhan fungsional dari masing-masing stakeholder dalam sistem manajemen inventori. Setiap kebutuhan fungsional diberi kode unik sebagai identifikasi dengan awalan INV (Inventori), diikuti oleh nomor urut kebutuhan. Daftar kebutuhan fungsional sistem disajikan pada Tabel 3.1.

Tabel 3.1 Kebutuhan fungsional sistem.

| No | Kode | Deskripsi | Aktor | Prioritas |
|:--:|------|-----------|-------|:---------:|
| 1 | INV-F01 | Admin dapat mengelola data bahan baku | Admin | Tinggi |
| 2 | INV-F02 | Admin dapat mengelola batch stok | Admin | Tinggi |
| 3 | INV-F03 | Sistem mendukung deduksi batch FEFO dan FIFO | Sistem | Tinggi |
| 4 | INV-F04 | Sistem mencatat pergerakan stok secara immutable | Sistem | Tinggi |
| 5 | INV-F05 | Admin dapat melakukan penyesuaian stok manual | Admin | Tinggi |
| 6 | INV-F06 | Admin dapat mengelola resep menu | Admin | Tinggi |
| 7 | INV-F07 | Sistem otomatis membuat MenuStock untuk menu tanpa resep | Sistem | Tinggi |
| 8 | INV-F08 | Admin dapat mengelola penyesuaian stok menu | Admin | Tinggi |
| 9 | INV-F09 | Sistem mencatat pergerakan stok menu secara immutable | Sistem | Tinggi |
| 10 | INV-F10 | Sistem melakukan deduksi stok otomatis saat pesanan diproses | Sistem | Tinggi |
| 11 | INV-F11 | Sistem menggunakan pessimistic locking | Sistem | Tinggi |
| 12 | INV-F12 | Admin dapat login dan logout | Admin | Tinggi |
| 13 | INV-F13 | Admin dapat melihat dashboard ringkasan stok | Admin | Sedang |
| 14 | INV-F14 | Admin dapat mengelola data kategori | Admin | Sedang |
| 15 | INV-F15 | Admin dapat mengelola data menu | Admin | Sedang |

### 3.3.2 Kebutuhan Non-Fungsional

Kebutuhan non-fungsional merupakan kebutuhan yang berkaitan dengan kualitas sistem agar aplikasi dapat berjalan secara optimal, aman, dan mudah digunakan. Kebutuhan non-fungsional sistem manajemen inventori disajikan pada Tabel 3.2.

Tabel 3.2 Kebutuhan non-fungsional.

| No | Kode | Parameter | Target | Verifikasi |
|:--:|------|-----------|--------|------------|
| 1 | INV-NF01 | Konsistensi Data | Pessimistic locking pada deduksi | Uji transaksi konkuren |
| 2 | INV-NF02 | Auditability | Catatan immutable | Uji update/delete model movement |
| 3 | INV-NF03 | Integritas | Foreign key constraints | Uji cascade |
| 4 | INV-NF04 | Modularitas | Service Layer | Verifikasi struktur |
| 5 | INV-NF05 | Ketahanan | Transaksi konkuren tanpa deadlock | Uji simultan |

## 3.4 Lingkungan Pengembangan Sistem
### 3.4.1 Lingkungan Pengembangan

Dalam proses pengembangan sistem manajemen inventori berbasis web, digunakan beberapa perangkat keras dan perangkat lunak pendukung. Rincian spesifikasi perangkat keras yang digunakan selama proses pengembangan dapat dilihat pada Tabel 3.3.

Tabel 3.3 Perangkat keras pengembangan.

| Perangkat Keras | Nama | Spesifikasi |
|----------------|------|-------------|
| Laptop Pengembangan | Lenovo IdeaPad Slim 1 | Prosesor: AMD Ryzen 3 3250U, RAM: 8 GB DDR4, Storage: 256 GB SSD, Layar: 14 inci HD, OS: Windows 11 |

Selain perangkat keras, beberapa perangkat lunak juga digunakan untuk mendukung proses pengembangan sistem. Detail spesifikasi perangkat lunak yang digunakan dapat dilihat pada Tabel 3.4.

Tabel 3.4 Perangkat lunak pengembangan.

| Perangkat Lunak | Nama Perangkat | Versi |
|----------------|----------------|-------|
| Kerangka Kerja | Laravel Framework | v13.8.0 |
| Bahasa Pemrograman | PHP | ^8.5 |
| Basis Data | PostgreSQL | 18 |
| Admin Panel | Filament | v5.6.2 |
| Container | Docker | 29.4.3 |
| Code Editor | VS Code | Terbaru |
| Version Control | Git | 2.45.1 |
| Web Browser | Chrome | Terbaru |

### 3.4.2 Lingkup Operasional

Untuk menjalankan sistem manajemen inventori berbasis web ini, spesifikasi perangkat keras dan perangkat lunak tidak perlu terlalu tinggi karena pemrosesan utama dilakukan di sisi server.

Spesifikasi perangkat keras minimum yang diperlukan:

1. Prosesor (CPU): Intel Core i3 generasi ke-8 atau setara. Prosesor ini sudah memadai untuk membuka browser, memproses data stok, dan transaksi tanpa lag berarti.
2. RAM: Minimum 8 GB. Dengan 4 GB masih bisa berjalan untuk penggunaan sangat ringan, tetapi 8 GB menjadi pilihan yang lebih realistis agar sistem tidak terasa lambat saat multitasking.
3. Penyimpanan: Minimal 64 GB, disarankan 128 GB SSD. SSD membuat pengalaman pengguna jauh lebih responsif saat booting dan loading halaman.
4. Perangkat pendukung: Minimum ponsel untuk akses darurat, disarankan PC atau laptop dengan monitor resolusi minimal 1366x768 piksel, mouse, dan keyboard.
5. Koneksi internet: Stabil dengan kecepatan minimal 5 Mbps agar akses data dan loading halaman berjalan lancar.

## 3.5 Perancangan Arsitektur Sistem
### 3.5.1 Arsitektur Umum

Sistem dirancang dengan arsitektur MVC yang diperluas dengan Service Layer yang terdiri dari empat lapisan:

1. Lapisan Presentasi: Panel administrasi Filament yang menyediakan antarmuka admin untuk mengelola data inventori.
2. Lapisan Controller: Controller Laravel yang menangani permintaan HTTP dari pengguna dan mengoordinasikan respons.
3. Lapisan Service: Service Layer berisi logika bisnis deduksi stok dan penyesuaian stok yang terisolasi dari lapisan presentasi.
4. Lapisan Data: Model Eloquent dan migration untuk interaksi dengan basis data PostgreSQL.

Gambar 3.3 Arsitektur umum sistem.

Lingkup perancangan meliputi dua jalur stok paralel:

1. Jalur Bahan Baku (berbasis resep): Menu dengan resep mendebet IngredientBatch melalui tabel pivot menu_ingredients.
2. Jalur Stok Menu (produk jadi): Menu tanpa resep dilacak melalui MenuStock yang terhubung 1:1 dengan Menu.

### 3.5.2 Service Layer

Service Layer terdiri dari empat layanan yang masing-masing menangani aspek spesifik dari logika bisnis inventori:

1. InventoryService: Mesin utama deduksi stok bahan baku dengan metode processSaleForOrder(), decreaseStockForOrder(), canFulfillOrder(), dan deductIngredientStock().
2. MenuStockService: Deduksi stok untuk produk jadi dengan metode deductMenuStockBatch().
3. StockReconciliationService: Penyesuaian manual stok bahan baku.
4. MenuStockReconciliationService: Penyesuaian manual stok menu.

### 3.5.3 Observer Pattern

Observer digunakan untuk menjaga konsistensi data secara otomatis:

1. MenuObserver: Membuat MenuStock otomatis saat menu tanpa resep dibuat, menangani toggle is_stock_calculated, serta cascade soft-delete dan restore MenuStock.
2. MenuIngredientObserver: Menyegarkan flag is_stock_calculated saat data resep berubah melalui event created, updated, dan deleted.

### 3.5.4 Immutable Audit Trail

Immutable audit trail diimplementasikan pada model StockMovement dan MenuStockMovement dengan mencegah operasi update dan delete melalui metode booted(). Setiap perubahan stok hanya dapat dilakukan dengan membuat entri pergerakan baru, bukan dengan mengubah atau menghapus catatan yang sudah ada. Jika terjadi kesalahan pencatatan, koreksi dilakukan melalui penyesuaian stok (stock adjustment) yang juga mencatat entri pergerakan baru.

Kode 3.1 Immutable audit trail pada StockMovement.

### 3.5.5 Strategi Deduksi Batch

Setiap bahan baku dan stok menu memiliki kolom batch_mode yang menentukan urutan deduksi batch:

| Mode | Urutan Deduksi | Cocok Untuk |
|------|----------------|-------------|
| FEFO (default) | expiry_date ASC | Bahan dengan masa kedaluwarsa |
| FIFO | received_at ASC | Bahan tanpa masa kedaluwarsa signifikan |

Semua deduksi menggunakan pessimistic locking dengan lockForUpdate() dan urutan ORDER BY id ASC untuk mencegah deadlock.

### 3.5.6 Idempotensi

Idempotensi diterapkan untuk mencegah deduksi stok ganda pada pesanan yang sama. Sebelum melakukan deduksi, sistem memeriksa terlebih dahulu apakah sudah terdapat catatan StockMovement dengan movement_type bernilai "sale" dan order_id yang sesuai dengan pesanan yang akan diproses. Pemeriksaan ini dilakukan di dalam method processSaleForOrder() pada InventoryService sebelum transaksi basis data dimulai. Jika catatan sudah ada, sistem akan melewatkan proses deduksi dan mengembalikan respons bahwa pesanan sudah diproses sebelumnya. Mekanisme ini penting untuk menjaga konsistensi data, terutama dalam skenario di mana metode pembayaran yang berbeda (tunai atau QRIS) dapat memicu pemrosesan yang sama secara tidak sengaja.

## 3.6 Perancangan Basis Data
### 3.6.1 Entity Relationship Diagram

Terdapat 10 tabel utama yang dikelompokkan ke dalam dua sub-sistem, yaitu sub-sistem bahan baku dengan 6 tabel dan sub-sistem stok menu dengan 4 tabel. Sub-sistem bahan baku terdiri dari tabel ingredients, ingredient_batches, menu_ingredients, stock_movements, stock_adjustments, dan daily_ingredient_usages yang saling berelasi untuk mendukung pencatatan dan pelacakan bahan baku. Sub-sistem stok menu terdiri dari tabel menu_stocks, menu_stock_batches, menu_stock_adjustments, dan menu_stock_movements yang dirancang dengan struktur identik untuk mengelola stok produk jadi. Kedua sub-sistem menggunakan pola desain yang sama: tabel master, tabel batch, tabel pergerakan stok (immutable), dan tabel penyesuaian stok.

Gambar 3.4 ERD sistem inventori.

### 3.6.2 Deskripsi Entitas

Pada implementasi sistem manajemen inventori, desain fisik database dijelaskan secara rinci melalui tabel-tabel berikut yang mencakup seluruh spesifikasi teknis penyimpanan data berdasarkan hasil transformasi dari Entity Relationship Diagram (ERD).

Tabel 3.5 Struktur tabel ingredients.

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | BIGINT PK | Primary key, auto-increment |
| name | VARCHAR(255) | Nama bahan baku |
| unit | ENUM | Satuan unit (gram, kg, ml, liter, pcs, sachet) |
| batch_mode | VARCHAR(255) | Mode deduksi batch (fefo atau fifo) |
| is_active | BOOLEAN | Status aktif bahan baku |
| deleted_at | TIMESTAMP | Timestamp soft delete |

Tabel 3.6 Struktur tabel ingredient_batches.

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | BIGINT PK | Primary key, auto-increment |
| ingredient_id | BIGINT FK | Foreign key ke tabel ingredients |
| quantity | DECIMAL(12,2) | Jumlah stok saat ini |
| expiry_date | DATE | Tanggal kedaluwarsa untuk mode FEFO |
| received_at | TIMESTAMP | Tanggal penerimaan untuk mode FIFO |
| cost_per_unit | DECIMAL(12,2) | Harga per unit bahan baku |

Tabel 3.7 Struktur tabel menu_ingredients.

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | BIGINT PK | Primary key, auto-increment |
| menu_id | BIGINT FK | Foreign key ke tabel menus |
| ingredient_id | BIGINT FK | Foreign key ke tabel ingredients |
| quantity_used | DECIMAL(12,2) | Jumlah bahan baku per unit menu |

Tabel 3.8 Struktur tabel stock_movements (immutable).

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | BIGINT PK | Primary key, auto-increment |
| ingredient_id | BIGINT FK | Foreign key ke tabel ingredients |
| movement_type | ENUM | Jenis pergerakan (sale, purchase, adjustment) |
| quantity_before, quantity_change, quantity_after | DECIMAL(12,2) | Snapshot stok sebelum, perubahan, dan setelah |
| recorded_by | BIGINT FK | Foreign key ke tabel users |

Tabel 3.9 Struktur tabel menu_stocks.

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | BIGINT PK | Primary key, auto-increment |
| menu_id | BIGINT FK UNIQUE | Foreign key unique ke tabel menus |
| unit | ENUM | Satuan unit stok menu |
| batch_mode | VARCHAR(255) | Mode deduksi batch (fefo atau fifo) |

## 3.7 Perancangan Antarmuka

Perancangan antarmuka bertujuan untuk memberikan gambaran tentang bagaimana pengguna akan berinteraksi dengan sistem manajemen inventori melalui panel administrasi. Antarmuka dirancang menggunakan framework Filament yang menyediakan komponen siap pakai seperti tabel data, formulir, dan elemen navigasi yang terintegrasi dengan model Eloquent.

### 3.7.1 Panel Filament

Panel administrasi Filament terdiri dari enam resource dan dua halaman tabbed yang dikelompokkan dalam grup navigasi "Inventori". Halaman StokPage menampilkan dua tab yaitu tab Bahan Baku yang berisi daftar ingredient dengan informasi stok total, mode batch, dan status, serta tab Menu yang menampilkan daftar MenuStock untuk menu tanpa resep. Halaman AdjustmentsPage menampilkan riwayat penyesuaian stok bahan baku dan stok menu dalam dua tab terpisah. Masing-masing resource menyediakan fungsionalitas CRUD (Create, Read, Update, Delete) yang dapat diakses melalui antarmuka tabel dan formulir yang telah dikonfigurasi.

### 3.7.2 Halaman Login dan Dashboard

Halaman Login merupakan halaman pertama yang muncul ketika admin mengakses panel administrasi. Halaman ini menyediakan formulir autentikasi yang meminta email dan password admin. Setelah admin berhasil login, sistem akan mengarahkan ke halaman Dashboard. Halaman Dashboard menampilkan ringkasan data inventori berupa total bahan baku yang terdaftar, total batch stok, jumlah menu yang tersedia, dan daftar menu yang tidak aktif. Data pada dashboard diambil melalui query agregat pada model Ingredient, Menu, dan MenuStock untuk memberikan gambaran cepat tentang kondisi stok kepada admin.

### 3.7.3 Halaman Manajemen Inventori

Halaman manajemen inventori merupakan halaman utama yang digunakan admin untuk mengelola data inventori. Halaman ini terdiri dari daftar bahan baku yang menampilkan seluruh ingredient beserta informasi stok total dan mode batch. Setiap bahan baku memiliki halaman manajemen batch yang memungkinkan admin menambah dan mengedit batch stok dengan informasi jumlah, tanggal kedaluwarsa, tanggal penerimaan, dan harga satuan. Halaman penyesuaian stok memungkinkan admin untuk menambah atau mengurangi stok secara manual dengan menyertakan alasan penyesuaian. Terdapat pula halaman laporan pemakaian harian yang menampilkan agregasi pemakaian bahan baku per tanggal secara read-only.

### 3.7.4 Halaman Pendukung

Halaman pendukung terdiri dari halaman manajemen kategori dan halaman manajemen menu. Halaman manajemen kategori menyediakan fungsionalitas CRUD untuk data kategori yang digunakan sebagai pengelompokan menu. Halaman manajemen menu dilengkapi dengan IngredientsRelationManager yang memungkinkan admin mengelola resep menu dengan memilih bahan baku dan menentukan jumlah pemakaian per unit menu. Kedua halaman ini menunjang data inventori karena kategori digunakan untuk mengelompokkan menu, dan data menu diperlukan dalam proses deduksi stok berbasis resep.
