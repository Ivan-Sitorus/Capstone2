# BAB II KAJIAN PUSTAKA

## 2.1 Penelitian Terdahulu

Kajian penelitian ini membahas berbagai penelitian terdahulu yang memiliki keterkaitan dengan topik pengembangan sistem manajemen inventori dan POS berbasis web. Penelitian-penelitian tersebut menjadi referensi penting dalam memahami konsep sistem informasi manajemen stok, algoritma deduksi batch, serta penerapan teknologi pendukung seperti Laravel dan Filament. Kajian ini juga memperkuat landasan teoritis dan memberikan gambaran tentang efektivitas sistem digital dalam meningkatkan akurasi dan efisiensi operasional di bidang inventori serta transaksi penjualan.

Penelitian oleh Susila [3] berjudul "Aplikasi *Point of Sales* (POS) Berbasis *Website* Dengan Menggunakan Laravel (Studi Kasus: Bakmi Djowo)" mengembangkan sistem POS berbasis web yang mencakup transaksi penjualan dan pengelolaan stok menu. Persamaan dengan penelitian ini adalah penggunaan Laravel sebagai kerangka kerja utama dan fokus pada pengelolaan stok, sedangkan perbedaannya terletak pada konteks: penelitian Susila pada restoran Bakmi Djowo tanpa dukungan manajemen batch, sementara penelitian ini pada kafe dengan implementasi mode deduksi FEFO dan FIFO.

Selanjutnya, Pratama dan Wijaya [4] dalam "Analisis perbandingan algoritma FEFO dan FIFO pada sistem *inventory*" menganalisis perbandingan efektivitas algoritma FEFO dan FIFO pada sistem inventori bahan baku makanan. Persamaan dengan penelitian ini adalah fokus pada algoritma deduksi batch, sedangkan perbedaannya adalah penelitian Pratama dan Wijaya masih berupa simulasi tanpa implementasi pada sistem nyata, sementara penelitian ini menerapkan kedua algoritma tersebut secara langsung pada sistem POS kafe.

Penelitian oleh Sari, dkk. [5] berjudul "Pengembangan sistem POS berbasis *web* untuk usaha kecil menengah" mengembangkan sistem POS dengan modul inventori terintegrasi. Persamaan dengan penelitian ini adalah integrasi antara POS dan inventori, sedangkan perbedaannya adalah sistem yang dikembangkan belum mendukung *batch tracking* atau mode deduksi stok, sementara penelitian ini menyediakan kedua fitur tersebut.

Nugroho [6] dalam "Implementasi Filament *admin panel* pada aplikasi berbasis Laravel" membahas implementasi Filament sebagai panel administrasi Laravel. Persamaan dengan penelitian ini adalah penggunaan Filament untuk antarmuka administrasi, sedangkan perbedaannya adalah penelitian Nugroho berfokus pada teknis implementasi Filament secara umum, sementara penelitian ini menerapkannya secara spesifik untuk manajemen inventori kafe.

Terakhir, Garbarz dan Plechawska-Wójcik [7] melakukan analisis komparatif kerangka kerja PHP Laravel dan Symfony. Persamaan dengan penelitian ini adalah pemilihan Laravel sebagai kerangka kerja utama, sedangkan perbedaannya adalah penelitian tersebut bersifat komparatif umum tanpa studi kasus spesifik.

Tabel 2.1 Kajian penelitian terdahulu.

| No | Peneliti, Tahun | Tujuan | Hasil |
|:--:|-----------------|--------|-------|
| 1 | A. Susila (2023) | Sistem POS berbasis web | Transaksi dan stok, tanpa batch management |
| 2 | C. D. Pratama dan E. Wijaya (2023) | Perbandingan FEFO dan FIFO | FEFO untuk bahan kedaluwarsa, FIFO untuk bahan stabil |
| 3 | R. K. Sari, dkk. (2024) | POS dengan inventori | Integrasi POS-inventori, tanpa batch tracking |
| 4 | S. Nugroho (2025) | Filament admin panel | Filament efektif untuk panel administrasi |
| 5 | P. Garbarz dan M. Plechawska-Wójcik (2022) | Analisis Laravel dan Symfony | Laravel sebagai framework utama |

Sistem manajemen inventori yang dikembangkan memiliki keunggulan dibanding dengan penelitian terdahulu, yaitu sistem ini dirancang khusus untuk mengatasi permasalahan manajemen stok bahan baku pada W9 Cafe yang memerlukan pengelolaan dua jalur stok paralel secara terintegrasi. Kelebihan utama sistem yang dikembangkan:

1. **Dual Path Stock Management**: Mampu mengelola dua jalur stok paralel: bahan baku berbasis resep dengan dekomposisi otomatis melalui tabel pivot `menu_ingredients`, dan stok menu produk jadi melalui `MenuStock` yang terhubung 1:1 dengan menu.

2. **Mode Deduksi FEFO dan FIFO**: Mendukung dua mode deduksi batch yang dapat dikonfigurasi per bahan baku sesuai karakteristik masing-masing.

3. ***Immutable Audit Trail***: Setiap pergerakan stok dicatat secara immutable dan tidak dapat diubah atau dihapus, menyediakan jejak audit lengkap.

4. ***Pessimistic Locking***: Menggunakan `lockForUpdate()` dengan urutan `ORDER BY id ASC` untuk mencegah *race condition* dan deadlock pada transaksi konkuren.

5. **Filament Admin Panel**: Panel administrasi dibangun menggunakan Filament yang menyediakan antarmuka CRUD intuitif dengan manajemen batch, *tabbed pages*, dan *relation manager*.

## 2.2 Metode Penelitian

Metode penelitian yang digunakan dalam tugas akhir ini mengadopsi metode *Agile*, yang menekankan kolaborasi erat antara pengembang dan pengguna, serta pengembangan sistem yang dilakukan secara iteratif, bertahap, dan fleksibel agar dapat menyesuaikan diri dengan perubahan kebutuhan selama proses berlangsung [16]. Tahapan dalam pengembangan perangkat lunak dengan menggunakan metode *Agile* dapat dilihat pada gambar 2.1.

Gambar 2.1 Metode *Agile*.

Berikut adalah tahapan dalam pengembangan perangkat lunak dengan menggunakan metode *Agile* [25]:

1. ***Requirements*** (Pengumpulan Kebutuhan) - Tim bekerja sama dengan *stakeholder* untuk mengidentifikasi dan memprioritaskan kebutuhan sistem dalam bentuk *product backlog* atau daftar *user stories* yang jelas dan terukur.

2. ***Design*** (Perancangan) - Tim membuat rancangan solusi sederhana namun efektif untuk fitur-fitur yang dipilih di iterasi ini, seperti sketsa antarmuka, alur sistem, atau arsitektur teknis awal.

3. ***Development*** (Pengembangan) - *Developer* mulai menulis kode, membangun fitur secara nyata, dan melakukan pengujian kecil-kecilan (*unit testing*) sambil berkomunikasi melalui *daily meeting*.

4. ***Testing*** (Pengujian) - Tim memverifikasi bahwa fitur yang sudah dibuat berfungsi dengan baik melalui berbagai jenis tes.

5. ***Deployment*** (Penyebaran) - Hasil kerja yang sudah lolos pengujian dikeluarkan ke lingkungan *staging* atau produksi.

6. ***Review*** (Tinjauan) - Mencakup *Sprint Review* untuk mendemonstrasikan hasil ke *stakeholder* dan *Sprint Retrospective* untuk merefleksikan proses kerja.

Setelah tahap *review* selesai, siklus kembali ke tahap *Requirements* untuk memulai iterasi baru dengan memasukkan umpan balik, perubahan prioritas, atau kebutuhan tambahan. Pendekatan siklus berulang ini memungkinkan proyek terus disempurnakan secara adaptif [26].

## 2.3 Landasan Teori
### 2.3.1 Sistem Manajemen Inventori

Sistem manajemen inventori adalah serangkaian proses yang digunakan untuk mengelola, memantau, dan mengendalikan persediaan barang dalam suatu organisasi. Tujuan utamanya adalah memastikan ketersediaan stok yang cukup untuk memenuhi permintaan tanpa menimbulkan kelebihan stok yang dapat meningkatkan biaya penyimpanan [8]. Dalam konteks industri makanan dan minuman, inventori mencakup bahan baku dan barang jadi yang memerlukan pengelolaan khusus terkait masa kedaluwarsa.

### 2.3.2 PostgreSQL

PostgreSQL adalah sistem manajemen basis data relasional objek open source dengan reputasi stabil dan kaya fitur. PostgreSQL mendukung *row-level locking* melalui `SELECT ... FOR UPDATE`, *transaction isolation levels*, dan *foreign key constraints* yang diperlukan untuk menjaga integritas data pada aplikasi berbasis web [14].

### 2.3.3 Algoritma Deduksi Batch (FEFO dan FIFO)

Algoritma deduksi batch menentukan urutan penggunaan stok berdasarkan karakteristik masing-masing batch. **FEFO (*First-Expiry-First-Out*)** memprioritaskan batch dengan tanggal kedaluwarsa terdekat, sangat penting untuk bahan baku dengan masa simpan terbatas seperti bahan segar dan produk *dairy* [4]. Implementasi teknisnya mengurutkan batch berdasarkan `expiry_date ASC`. **FIFO (*First-In-First-Out*)** memprioritaskan batch yang diterima lebih awal, cocok untuk bahan baku tanpa masa kedaluwarsa signifikan, dengan urutan `received_at ASC` [4].

### 2.3.4 Pessimistic Locking

*Pessimistic locking* adalah mekanisme penguncian baris data pada basis data relasional yang mencegah transaksi lain mengubah data yang sama secara bersamaan. Mekanisme ini diimplementasikan menggunakan klausa `SELECT ... FOR UPDATE` pada PostgreSQL. Untuk mencegah deadlock, semua transaksi harus mengunci baris dalam urutan yang konsisten [9].

### 2.3.5 Point of Sale (POS)

*Point of Sale* (POS) merupakan titik terjadinya transaksi penjualan antara pelanggan dan penjual, di mana total harga dihitung, pajak atau diskon diterapkan, dan pembayaran diproses. Sistem POS modern tidak hanya mencakup perangkat kasir, tetapi juga perangkat lunak yang mengelola transaksi, data produk, stok, dan laporan penjualan secara terintegrasi [18].

### 2.3.6 PHP

PHP (*Hypertext Preprocessor*) adalah bahasa pemrograman skrip yang banyak digunakan untuk pengembangan aplikasi web dinamis. PHP dapat disisipkan langsung ke dalam HTML dan memiliki dukungan luas terhadap berbagai basis data. Laravel sebagai kerangka kerja PHP yang digunakan dalam tugas akhir ini memanfaatkan kemampuan PHP untuk membangun aplikasi web yang terstruktur [19].

### 2.3.7 Laravel

Laravel adalah kerangka kerja aplikasi web berbasis PHP yang bersifat terbuka dan mendukung pola arsitektur MVC. Laravel menyediakan berbagai fitur seperti sistem *routing*, manajemen basis data melalui migration, sistem autentikasi bawaan, dan dukungan middleware yang memudahkan pengembangan aplikasi web modern [10].

### 2.3.8 Pola Arsitektur MVC

*Model View Controller* (MVC) merupakan pola arsitektur perangkat lunak yang memisahkan aplikasi menjadi tiga komponen utama: *Model* (data dan logika bisnis), *View* (antarmuka pengguna), dan *Controller* (alur aplikasi). Penerapan pola ini membantu menjaga struktur aplikasi agar tetap terpisah antara logika bisnis dan tampilan [15].

### 2.3.9 Eloquent ORM

*Eloquent ORM* adalah sistem *object relational mapping* bawaan Laravel yang memungkinkan pengembang berinteraksi dengan basis data menggunakan pendekatan berorientasi objek. Setiap tabel dalam basis data direpresentasikan sebagai kelas model Eloquent, dan relasi antar tabel didefinisikan secara deklaratif [27]. Eloquent mengimplementasikan pola *Active Record* di mana setiap model mewakili sebuah baris dalam tabel basis data [28].

### 2.3.10 Pola Arsitektur Service Layer

*Service Layer* adalah pola arsitektur yang mendefinisikan batas layanan dan menyediakan sekumpulan operasi yang tersedia bagi klien. Pola ini memisahkan logika bisnis dari lapisan presentasi dan lapisan akses data, sehingga logika bisnis dapat dienkapsulasi dalam kelas-kelas layanan yang terfokus dan dapat diuji secara terisolasi [11].

### 2.3.11 Pola Observer

Pola *Observer* adalah pola desain perangkat lunak di mana sebuah objek memelihara daftar objek-objek dependen dan memberi tahu mereka secara otomatis tentang setiap perubahan keadaan. Dalam Laravel, pola ini diimplementasikan melalui kelas observer yang merespon event Eloquent seperti `created`, `updated`, dan `deleted` [12].

### 2.3.12 Filament

Filament adalah pustaka antarmuka pengguna yang dibangun di atas Tailwind CSS, Alpine.js, Laravel, dan Livewire. Filament menyediakan komponen antarmuka siap pakai seperti tabel data, formulir, dan berbagai elemen tampilan lain yang terintegrasi dengan model Eloquent, sehingga mempercepat pembuatan antarmuka administrasi [13].

### 2.3.13 Use Case Diagram

*Use case diagram* merupakan model yang digunakan untuk memetakan interaksi antara aktor dan fungsi yang disediakan oleh sistem. Diagram ini membantu menggambarkan fungsionalitas sistem dari perspektif pengguna dan menjadi dasar dalam perancangan sistem lebih lanjut [20]. Dalam konteks sistem manajemen inventori, use case diagram digunakan untuk mengidentifikasi aktor-aktor yang terlibat (seperti Admin) dan hak akses mereka terhadap berbagai fitur seperti pengelolaan bahan baku, batch stok, dan resep menu.



### 2.3.14 Docker

Docker merupakan teknologi *containerization* yang memungkinkan pengemasan aplikasi beserta seluruh dependensinya ke dalam lingkungan terisolasi yang disebut *container* [25]. Docker memastikan aplikasi dapat dijalankan secara konsisten di berbagai lingkungan tanpa konflik konfigurasi. Dalam pengembangan aplikasi web, Docker banyak digunakan untuk memastikan konsistensi lingkungan kerja antar pengembang serta mempermudah proses deployment. Pada penelitian ini, Docker digunakan untuk menjalankan container Laravel (PHP-FPM + Nginx), PostgreSQL, dan Redis dalam lingkungan pengembangan yang terisolasi dan konsisten.

### 2.3.15 Entity Relationship Diagram

*Entity Relationship Diagram* (ERD) merupakan model pemodelan data yang disusun berdasarkan objek-objek yang ada di dunia nyata. ERD digunakan untuk menggambarkan hubungan antar data dalam sebuah basis data secara logis sehingga mudah dipahami oleh pengembang dan pengguna [20]. ERD terdiri dari entitas (seperti Ingredient, Menu, StockMovement), atribut (kolom-kolom dalam tabel), dan relasi antar entitas (seperti one-to-many, many-to-many). Pada sistem inventori, ERD digunakan untuk memodelkan hubungan antara bahan baku dengan batch stok, menu dengan resep, serta keterkaitan antara stok dengan pergerakannya.
