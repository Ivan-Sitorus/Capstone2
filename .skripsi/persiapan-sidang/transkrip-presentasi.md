# Transkrip Presentasi Sidang — Muhammad Nio Hastungkoro
## 15 Juli 2026 | S2T25K09 | Teknik Komputer | Universitas Diponegoro

---

## Slide 1 — Pembukaan

> **"Assalamualaikum warahmatullahi wabarakatuh.**
>
> Selamat pagi, Bapak/Ibu dosen penguji. Terima kasih atas kesediaan waktu dan kehadirannya pada sidang tugas akhir saya hari ini.
>
> Perkenalkan, saya Muhammad Nio Hastungkoro, NIM 21120122140155, dari Departemen Teknik Komputer, Fakultas Teknik, Universitas Diponegoro.
>
> Judul tugas akhir saya adalah **'Perancangan Sistem Point of Sale Cafe Berbasis Web dengan Penerapan Data Mining untuk Analisis Pola Pembelian dan Pengembangan Strategi Penjualan'**.
>
>
> Saya akan mempresentasikan hasil capstone saya dalam waktu kurang lebih 15 menit. Mohon izin untuk memulai."

---

## Slide 2 — Ucapan Pembimbing dan Penguji

> **"Sebelumnya, saya ingin menyampaikan terima kasih dan penghargaan yang setinggi-tingginya kepada:**
>
> **Dosen pembimbing,** Bapak Yudi Eko Windarto, S.T., M.Kom. dan Ibu Rinta Kridalukmana, S.Kom., M.T., Ph.D., yang telah membimbing saya selama pengerjaan tugas akhir ini.
>
> **Dosen penguji,** yang telah meluangkan waktu untuk menilai dan memberikan masukan pada sidang hari ini.
>
> Serta seluruh pihak yang telah mendukung kelancaran capstone ini."

---

## Slide 3 — Anggota Capstone

> **"Tugas akhir ini dikerjakan sebagai proyek capstone yang terdiri dari tiga orang anggota tim, masing-masing dengan tanggung jawab yang berbeda:**
>
> **Ruben Shandova Sigalingging** sebagai *Data Mining Engineer*, bertanggung jawab atas pengembangan modul data mining untuk analisis pola pembelian, prediksi stok, klasterisasi bahan baku, dan asosiasi menu.
>
> **Ivan Benhard Sitorus** sebagai *Fullstack Transaction Engineer*, bertanggung jawab atas modul transaksi kasir dan pelanggan, termasuk sistem pemesanan, pembayaran, dan riwayat transaksi.
>
> **Saya sendiri, Muhammad Nio Hastungkoro**, sebagai *Fullstack Inventory Engineer*, bertanggung jawab atas sistem manajemen inventori yang mencakup manajemen bahan baku, batch stok, resep menu, penyesuaian stok, deduksi otomatis menggunakan algoritma FEFO/FIFO, dan pelaporan inventori. Fokus presentasi hari ini adalah pada modul inventori yang menjadi tanggung jawab saya."

---

## Slide 4 — Latar Belakang

> **"W9 Cafe adalah sebuah kafe yang berlokasi di STIE Totalwin Semarang. Dalam operasionalnya, kafe ini menghadapi beberapa permasalahan:**

> **Pertama,** pencatatan penjualan dan stok masih dilakukan secara manual menggunakan buku nota. Tidak ada sistem yang menghubungkan antara transaksi penjualan dengan stok bahan baku. Akibatnya, stok sering tidak akurat dan pembuatan laporan stok harian memakan waktu hingga dua jam.

> **Kedua,** sistem POS berbayar yang tersedia di pasaran umumnya terlalu kompleks untuk kebutuhan cafe skala kecil. Mereka menawarkan fitur CRM, manajemen SDM, dan modul akuntansi yang tidak diperlukan. Namun fitur penting seperti manajemen batch stok berbasis FEFO/FIFO, penelusuran tanggal kedaluwarsa bahan baku, dan resep menu justru tidak tersedia.

> **Ketiga,** tidak ada fitur analitik yang dapat membantu pemilik cafe dalam memahami pola penjualan, memprediksi kebutuhan stok, dan menyusun strategi penjualan berdasarkan data.

> Solusi yang kami rancang adalah sebuah sistem POS terintegrasi berbasis web, dengan modul data mining untuk analisis pola pembelian dan pengembangan strategi penjualan."

---

## Slide 5 — Diagram Arsitektur Sistem

> **"Arsitektur sistem terdiri dari tiga komponen utama:**

> **Pertama, Aplikasi Laravel** yang mencakup Panel Admin Filament untuk manajemen inventori, analitik, dan transaksi. Admin mengakses melalui web browser, melakukan operasional cafe seperti mengelola bahan baku, batch stok, menu, dan penyesuaian stok.

> **Kedua, Sistem Transaksi berbasis React** yang terdiri dari antarmuka pelanggan untuk pemesanan menu dan antarmuka kasir untuk memproses pesanan. Keduanya terhubung dengan modul inventori melalui Inertia.js, sehingga ketika kasir memproses pesanan, stok bahan baku berkurang secara otomatis.

> **Ketiga, Algoritma Data Mining dengan Python** yang terintegrasi dengan panel admin untuk menyediakan analisis pola pembelian, prediksi stok, dan klasterisasi bahan baku. Ketiga komponen ini berbagi satu database PostgreSQL yang sama.

> **Pada sistem transaksi,** kami menggunakan Inertia.js sebagai penghubung antara Laravel backend dan React frontend. Berbeda dengan REST API yang membutuhkan endpoint JSON terpisah, Inertia.js memungkinkan controller Laravel mengirimkan data langsung sebagai props ke komponen React. Setiap navigasi dikendalikan oleh routing Laravel, bukan frontend. Pendekatan ini kami pilih karena seluruh pengguna bersifat internal — tidak perlu menyediakan API untuk aplikasi pihak ketiga. Untuk modul data mining, FastAPI Python digunakan sebagai REST API service terpisah karena ekosistem machine learning Python yang lebih matang, dan penggunaannya hanya untuk analitik yang tidak memerlukan respons real-time."

---

## Slide 6 — Implementasi Sistem Manajemen Inventori

> **"Selanjutnya, saya akan fokus pada modul yang menjadi tanggung jawab saya, yaitu Sistem Manajemen Inventori menggunakan Laravel dan Filament."**

---

## Slide 7 — Kebutuhan Fungsional

> **"Sebelum membangun sistem, kami merumuskan kebutuhan fungsional — fitur-fitur yang harus ada di dalam sistem:**

> Sistem harus mendukung autentikasi admin — hanya admin yang terdaftar yang dapat mengakses panel. Admin dapat mengelola bahan baku, batch stok, dan resep menu. Sistem harus dapat melakukan deduksi stok otomatis berdasarkan pesanan menggunakan algoritma FEFO untuk bahan yang memiliki tanggal kedaluwarsa dan FIFO untuk bahan tanpa kedaluwarsa. Admin dapat melakukan penyesuaian stok, membatalkan penyesuaian, melihat riwayat pemakaian bahan baku harian, serta menonaktifkan menu yang stoknya tidak mencukupi.

> Pada modul transaksi, kasir dapat memproses pesanan dan mencatat pembayaran. Pelanggan dapat melihat menu, memesan, dan memilih metode pembayaran. Admin dapat memverifikasi akun mahasiswa untuk memberikan diskon, dan sistem dapat menghasilkan struk digital untuk setiap pesanan."

---

## Slide 8 — Kebutuhan Non-Fungsional

> **"Sedangkan kebutuhan non-fungsional mencakup: sistem harus responsif di perangkat desktop dan mobile, memiliki waktu respons di bawah 3 detik untuk halaman standar, menggunakan PostgreSQL sebagai database dan Docker untuk containerisasi, serta mendukung tiga peran pengguna — admin, kasir, dan pelanggan — dengan hak akses yang berbeda."**

---

## Slide 9 — Use Case Diagram

> **"Use case diagram ini menggambarkan interaksi admin dengan sistem manajemen inventori. Admin dapat melakukan login dan autentikasi, mengelola bahan baku — menambah, mengedit, menghapus, serta melihat riwayat penggunaan — mengelola batch stok bahan baku, melakukan penyesuaian stok, mengelola menu beserta resep bahan bakunya, dan mengelola kategori menu.**

> Setiap use case melewati pengecekan session dan validasi role. Saat admin login, sistem mengecek kredensial — jika valid, sistem membuat session. Fitur-fitur seperti tambah resep menu merupakan bagian dari tambah atau edit menu. Batalkan penyesuaian stok merupakan perluasan dari penyesuaian stok. Semua ini memastikan bahwa hanya admin yang sudah terautentikasi dan memiliki peran yang sesuai yang dapat mengakses setiap fitur."

---

## Slide 10 — Activity Diagram Tambah Bahan Baku

> **"Activity diagram ini menunjukkan alur admin saat menambah bahan baku baru. Dimulai dari admin membuka halaman daftar bahan baku, mengklik tombol 'Buat Bahan Baku', lalu mengisi form yang mencakup nama, unit, dan mode batch — apakah FEFO atau FIFO.**

> **Sistem menerima input tersebut dan melakukan validasi. Jika data tidak valid, admin menerima pesan gagal dan diminta mengisi ulang form. Jika valid, sistem menyimpan bahan baku baru di database dan admin menerima pesan berhasil serta data baru muncul di tabel."**

---

## Slide 11 — Activity Diagram Buat Menu

> **"Untuk pembuatan menu, alurnya serupa. Admin membuka halaman menu, mengklik 'Buat Menu', mengisi form yang mencakup nama, kategori, harga, diskon mahasiswa, status ketersediaan, dan yang terpenting — resep bahan baku. Setiap menu harus memiliki minimal satu bahan baku sebagai komposisi pembuatan. Inilah yang membedakan sistem ini dengan POS biasa: setiap menu yang dijual memiliki dampak langsung terhadap stok bahan baku.**

> Sistem memvalidasi input. Jika valid, menu beserta resepnya disimpan di database dan muncul di tabel."

---

## Slide 12 — Activity Diagram Penyesuaian Stok

> **"Activity diagram penyesuaian stok: admin membuka halaman penyesuaian stok, mengklik 'Buat Penyesuaian', mengisi form yang mencakup tipe — penambahan atau pengurangan — jumlah, kategori, waktu penyesuaian, dan catatan.**

> **Sistem memvalidasi input. Jika valid, penyesuaian stok disimpan. Jika pengurangan melebihi stok tersedia, sistem akan menolak dan menampilkan pesan error."**

---

## Slide 13 — Diagram Arsitektur Sistem Inventori

> **"Diagram ini menunjukkan arsitektur modul inventori secara detail.**
>
> **Panel Admin Filament** berada di lapisan paling atas, berinteraksi dengan model-model data seperti StockAdjustment, StockMovement, IngredientBatch, Ingredient, Menu, dan Category.
>
> **Di lapisan service business logic**, terdapat dua service utama:
> - **InventoryService** yang merupakan engine utama FEFO dan FIFO — menangani deduksi stok, pemilihan batch, dan pencatatan pergerakan stok.
> - **StockReconciliationService** yang menangani logika penyesuaian stok — baik untuk ingredient maupun menu.
>
> **Di lapisan model data access**, setiap model berkomunikasi dengan database PostgreSQL. Model StockMovement berfungsi sebagai catatan audit untuk setiap perubahan stok, memastikan semua perubahan dapat dilacak."
>
>
> **Saya menggunakan arsitektur service layer pattern.** Semua logika bisnis inventori ditempatkan di InventoryService dan StockReconciliationService, bukan di controller. Controller hanya bertugas menerima request dan mengembalikan response. Service layer ini memisahkan business logic dari presentation logic, sehingga kode menjadi lebih terstruktur, mudah diuji, dan mudah dipelihara. Setiap service bisa diuji secara independen menggunakan PHPUnit tanpa perlu melalui HTTP request, dan setiap perubahan pada logika inventori cukup dilakukan di satu tempat tanpa mempengaruhi controller atau frontend.

---

## Slide 14 — ERD Sistem Inventori

> **"Entity Relationship Diagram ini menunjukkan relasi antar tabel dalam sistem inventori.**
>
> **Tabel `ingredients`** — pusat dari sistem — memiliki relasi one-to-many ke empat tabel: `menu_ingredients` sebagai pivot ke menu, `stock_adjustments` untuk penyesuaian stok, `ingredient_batches` untuk batch stok, dan `stock_movements` untuk riwayat pergerakan stok.
>
> **Setiap bahan baku bisa memiliki banyak batch** (`ingredient_batches`). Setiap batch memiliki quantity, expiry_date, received_at, dan cost_per_unit. Ketika pesanan diproses, sistem memilih batch mana yang akan dikurangi berdasarkan algoritma FEFO atau FIFO.
>
> **StockMovements** mencatat setiap perubahan stok — quantity_before, quantity_change, dan quantity_after — sehingga setiap perubahan stok dapat dilacak secara lengkap. Inilah yang memungkinkan audit trail end-to-end. 
>
> **Menu dan Category** terhubung melalui relasi standar, dan `menu_ingredients` menghubungkan menu dengan bahan-bahan penyusunnya."

---

## Slide 15 — Flowchart Algoritma FEFO dan FIFO

> **"Flowchart ini menjelaskan proses deduksi stok — inti dari sistem inventori.**
>
> **Ketika pesanan masuk, langkah pertama adalah menghitung total kebutuhan bahan baku: `quantity_used dikali jumlah_pesan` untuk setiap bahan yang terdaftar di resep menu.**
>
> **Kemudian sistem mengambil batch stok yang tersedia untuk bahan tersebut. Urutan pengambilan batch ditentukan oleh mode yang dipilih:**
> - **Jika mode FEFO**, batch diurutkan berdasarkan expiry_date ascending — batch yang paling cepat kedaluwarsa akan dipakai terlebih dahulu. Ini mencegah bahan terbuang sia-sia.
> - **Jika mode FIFO**, batch diurutkan berdasarkan received_at ascending — batch yang lebih lama diterima akan dipakai terlebih dahulu. Ini memastikan rotasi stok yang sehat.
>
> **Selanjutnya sistem memvalidasi kecukupan stok. Jika `totalAvailable < requiredQuantity`, sistem mengembalikan error 'Stok tidak mencukupi' dan pesanan tidak dapat diproses. Jika stok mencukupi, sistem mengurangi quantity batch yang terpilih, mencatat pergerakan stok ke tabel stock_movements, dan mencatat pemakaian harian ke tabel daily_ingredient_usages.**
>
> **Seluruh proses ini dibungkus dalam database transaction, sehingga jika ada kegagalan di satu langkah — misalnya stok berubah karena ada transaksi concurrent — seluruh perubahan di-rollback. Ini memastikan konsistensi data.**"

---

## Slide 16 — Pengujian Black Box (Autentikasi & Bahan Baku)

> **"Memasuki bagian pengujian, saya menggunakan tiga metode: Black Box, White Box, dan Gray Box."**

> **Pengujian black box autentikasi admin:**
> - Login dengan kredensial valid — admin memasukkan email dan password yang benar, klik Masuk — berhasil masuk ke panel admin. **Status: Berhasil.**
> - Login dengan password salah — admin memasukkan password yang salah — muncul pesan error. **Status: Berhasil.**
> - Logout — admin klik Logout — kembali ke halaman login. **Status: Berhasil.**

> **Pengujian black box manajemen bahan baku:**
> - Tambah bahan baku — admin mengisi form dan menyimpan — data muncul di tabel. **Berhasil.**
> - Ubah bahan baku — admin mengedit nama atau unit — data berubah. **Berhasil.**
> - Hapus bahan baku — admin klik hapus — data hilang secara soft delete. **Berhasil.**
> - Tambah batch stok — admin mengisi jumlah, tanggal, dan menyimpan — batch tersimpan. **Berhasil.**"

---

## Slide 17 — Pengujian Black Box (Menu & Penyesuaian Stok)

> **"Pengujian black box manajemen menu:**
> - Tambah menu dengan resep — admin mengisi form menu beserta bahan bakunya — menu tersimpan. **Berhasil.**
> - Ubah menu — admin mengedit data menu — data berubah. **Berhasil.**
> - Hapus menu — admin menghapus menu — data hilang soft delete. **Berhasil.**
> - Tambah resep menu — admin menambahkan bahan ke menu — resep tersimpan. **Berhasil.**
> - Hapus resep menu — admin menghapus bahan — muncul peringatan jika hanya tersisa satu bahan. **Berhasil.**

> **Pengujian black box penyesuaian stok:**
> - Penyesuaian penambahan stok — admin menambah stok — stok bertambah sesuai input. **Berhasil.**
> - Penyesuaian pengurangan stok — admin mengurangi stok — stok berkurang sesuai input. **Berhasil.**
> - Input jumlah tidak valid — admin memasukkan angka negatif atau nol — ditolak sistem. **Berhasil.**

> **Seluruh 15 skenario black box menunjukkan status Berhasil."**

---

## Slide 18 — Pengujian White Box: Deduksi Stok

> **"Pengujian white box memvalidasi kebenaran logika internal dan algoritma sistem secara langsung melalui PHPUnit.**
>
> **Skenario pertama: pengujian deduksi stok berdasarkan resep menu.** Skenario ini memvalidasi bahwa ketika suatu menu diproses, sistem mendeduksi stok bahan baku sesuai quantity_used yang terdaftar di tabel pivot menu_ingredients. Saya membuat menu 'Kopi Susu' dengan resep 30 gram kopi per porsi. Ketika dipesan sebanyak 2 porsi, sistem memanggil InventoryService, menghitung kebutuhan 60 gram, dan mengurangi stok dari batch yang terpilih. Hasilnya: stok batch berkurang tepat 60 gram — sesuai dengan yang diharapkan. **Status: Berhasil.**"

---

## Slide 19 — Pengujian White Box: Algoritma FIFO

> **"Pengujian white box kedua: algoritma FIFO. Skenario ini memvalidasi bahwa batch dengan received_at paling awal dikonsumsi terlebih dahulu.**
>
> Saya membuat dua batch bahan baku dengan mode FIFO:
> - Batch A: diterima 5 hari lalu, quantity 100
> - Batch B: diterima 1 hari lalu, quantity 200
>
> Setelah dilakukan deduksi sebesar 60 gram, yang terjadi adalah Batch A berkurang menjadi 40 gram, Batch B tetap 200 gram. Hasil ini sesuai dengan prinsip FIFO karena batch yang diterima lebih awal diproses lebih dulu. **Status: Berhasil.**"

---

## Slide 20 — Pengujian White Box: Algoritma FEFO

> **"Pengujian white box ketiga: algoritma FEFO. Skenario ini memvalidasi bahwa batch dengan expiry_date terdekat dikonsumsi terlebih dahulu.**
>
> Saya membuat dua batch:
> - Batch A: kedaluwarsa dalam 3 hari, quantity 80
> - Batch B: kedaluwarsa dalam 30 hari, quantity 80
>
> Menu membutuhkan 90 ml susu — setara 3 porsi. Setelah deduksi, Batch A habis (quantity 0) dan Batch B berkurang menjadi 70. Artinya sistem benar memprioritaskan batch yang paling cepat kedaluwarsa. **Status: Berhasil.**"

---

## Slide 21 — Pengujian White Box: Penyesuaian Stok

> **"Pengujian white box keempat: penyesuaian stok tipe increase.**
>
> Saya membuat batch dengan quantity 100, kemudian melakukan penyesuaian tipe increase sebesar 50. Hasilnya, quantity batch bertambah menjadi 150. Pergerakan stok tercatat dengan quantity_change +50. **Status: Berhasil.**"

---

## Slide 22 — Pengujian White Box: Pembatalan Penyesuaian Stok

> **"Pengujian white box kelima: pembatalan penyesuaian stok.**
>
> Saya melakukan penyesuaian increase yang menambah stok dari 100 menjadi 150, kemudian membatalkan penyesuaian tersebut — sistem mencatat stock movement baru dengan quantity_change -50 dan stok kembali ke 100. Validasi ini memastikan bahwa fitur pembatalan penyesuaian mengembalikan stok ke kondisi awal. **Status: Berhasil.**

> **Seluruh 5 skenario pengujian white box menunjukkan status Berhasil. Algoritma FEFO, FIFO, deduksi resep, penyesuaian, dan pembatalan penyesuaian — semua berjalan sesuai perancangan."**

---

## Slide 23 — Pengujian Gray Box: Deduksi dan Catat Riwayat Stok

> **"Pengujian gray box dilakukan untuk memverifikasi bahwa aliran data antara sistem transaksi dan sistem inventori berjalan dengan benar.**
>
> Berbeda dengan white box yang memanggil service secara langsung, pengujian gray box mengirimkan HTTP request ke controller transaksi dan memverifikasi efeknya di database inventori.
>
> **Skenario 1: Deduksi stok dan pencatatan riwayat.** Saya membuat user kasir, kategori, menu 'Kopi Susu', bahan baku 'Kopi Bubuk', batch dengan quantity 100, dan resep 10 gram per porsi. Saya mengirimkan POST request ke route kasir dengan 2 porsi kopi susu dan metode pembayaran cash.
>
> Hasilnya: response sukses, quantity batch berkurang menjadi 80 gram (berkurang 20 gram sesuai 2 porsi dikali 10 gram). Sistem mencatat stock_movement dengan quantity_before 100, quantity_change -20, quantity_after 80. Serta mencatat daily_ingredient_usage. **Status: Berhasil.**
>
> Skenario ini membuktikan bahwa seluruh lapisan sistem — dari HTTP request, autentikasi, validasi, controller, service inventori, hingga database — bekerja secara terintegrasi dan sesuai yang diharapkan."

---

## Slide 24 — Pengujian Gray Box: Order Gagal (Stok Tidak Cukup)

> **"Skenario 2: Order gagal karena stok bahan baku tidak mencukupi.**
>
> Skenario ini penting untuk memvalidasi bahwa sistem mencegah pesanan ketika stok tidak memadai — bukan hanya menampilkan peringatan di UI, tetapi benar-benar memblokirnya di level backend.
>
> Setup: batch dengan quantity 20 gram. Saya mengirimkan request untuk 10 porsi yang membutuhkan 100 gram — melebihi stok yang tersedia.
>
> Hasilnya: response gagal dengan pesan error 'Stok tidak mencukupi'. Batch quantity tetap 20 — tidak ada perubahan. Tidak ada stock_movement yang tercatat. **Status: Berhasil.**

> Skenario ini memvalidasi bahwa mekanisme rollback bekerja dengan benar: ketika deduksi stok gagal karena stok tidak mencukupi, tidak ada data yang berubah di database."

---

## Slide 25 — Pengujian Gray Box: Order Gagal (Silakan lihat skripsi untuk detail)

> **"Skenario 3: Alur pemesanan pelanggan yang dikonfirmasi oleh kasir.**
>
> Skenario ini memvalidasi alur end-to-end: pelanggan memesan melalui Customer Order, kasir mengkonfirmasi pembayaran di POS, sistem mendeduksi stok dan mencatat pergerakannya. Detail skenario ini dapat dilihat pada buku skripsi bagian 4.3.3.

> **Seluruh skenario pengujian gray box menunjukkan status Berhasil. Sistem berhasil memvalidasi bahwa data transaksi dan data inventori tetap konsisten di setiap alur bisnis.**"

---

## Slide 26 — Kesimpulan dan Saran

> **"Sebagai penutup, berikut kesimpulan dari capstone ini:**

> **Pertama,** sistem berhasil menerapkan manajemen inventori berbasis bahan baku dengan resep terintegrasi, yang memungkinkan setiap menu yang terjual memberikan dampak langsung terhadap stok bahan baku terkait.

> **Kedua,** sistem berhasil menerapkan prioritas penggunaan stok berdasarkan masa kedaluwarsa (FEFO) dan urutan penerimaan (FIFO), yang meminimalkan pemborosan bahan baku serta menjaga konsistensi data stok.

> **Ketiga,** sistem berhasil mencatat pemakaian bahan baku secara otomatis setiap kali terjadi transaksi penjualan dan menyediakan riwayat perubahan stok yang dapat dilacak secara lengkap.

> **Keempat,** pengujian black box terhadap seluruh modul menunjukkan hasil berhasil. Pengujian white box memvalidasi kebenaran deduksi stok berbasis resep, algoritma FIFO dan FEFO, penyesuaian stok, serta pembatalan penyesuaian stok. Pengujian gray box memvalidasi konsistensi aliran data antar modul inventori dan modul transaksi.

> **Saran pengembangan ke depan meliputi:**
> - Pengembangan aplikasi mobile agar sistem dapat diakses ketika admin tidak berada di depan laptop.
> - Notifikasi stok menipis dan kedaluwarsa melalui WhatsApp.
> - Integrasi barcode scanner untuk mempercepat identifikasi batch stok bahan baku.

> **Demikian presentasi dari saya. Terima kasih atas perhatian Bapak/Ibu dosen. Saya siap menerima pertanyaan dan masukan.**

> **Wassalamualaikum warahmatullahi wabarakatuh.**"
