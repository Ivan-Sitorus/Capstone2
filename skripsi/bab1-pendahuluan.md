# BAB I PENDAHULUAN

## 1.1 Latar Belakang

Industri penyediaan makanan dan minuman di Indonesia merupakan sektor yang besar dan terus berkembang. Badan Pusat Statistik (BPS) mencatat bahwa sektor penyediaan makanan dan minuman berkontribusi signifikan terhadap perekonomian nasional dengan jumlah usaha yang mencapai jutaan unit dan menyerap tenaga kerja dalam jumlah besar [1]. Perkembangan ini mendorong peningkatan persaingan layanan di tingkat operasional, terutama pada aspek kecepatan, akurasi, dan konsistensi pelayanan, termasuk bagi kafe skala kecil hingga menengah.

Dalam operasional kafe, manajemen inventori atau pengelolaan stok bahan baku merupakan salah satu proses inti yang sangat menentukan kelancaran produksi dan kepuasan pelanggan. Namun pada praktiknya, banyak kafe masih menghadapi kendala dalam pengelolaan stok karena menggunakan metode pencatatan manual, seperti nota kertas atau spreadsheet. Metode manual ini rentan terhadap kesalahan pencatatan, keterlambatan informasi stok, serta sulitnya melacak riwayat pergerakan stok secara akurat.

Salah satu contoh nyata dari permasalahan ini adalah W9 Cafe, sebuah kafe yang berlokasi di Semarang dan dikelola oleh STIE Totalwin. Dalam operasional sehari-hari, W9 Cafe masih mengandalkan pencatatan stok secara manual menggunakan buku stok dan spreadsheet. Proses ini menyebabkan sering terjadinya ketidaksesuaian antara catatan stok dengan kondisi aktual di lapangan, keterlambatan informasi ketersediaan bahan baku, serta sulitnya melacak riwayat pemakaian bahan baku secara akurat. Kondisi ini mendorong perlunya sistem manajemen inventori yang terkomputerisasi dan terintegrasi dengan sistem POS yang sudah ada.

Permasalahan pencatatan manual pada sistem inventori telah diidentifikasi dalam beberapa penelitian sebelumnya. Saputra, dkk. [2] menyatakan bahwa pencatatan stok secara manual menggunakan kertas berpotensi menimbulkan penumpukan dokumen dan menghambat efektivitas operasional.

Sistem *Point of Sale* (POS) modern umumnya menyediakan fitur pencatatan transaksi penjualan, namun belum tentu dilengkapi dengan modul manajemen inventori yang komprehensif. Penelitian oleh Susila [3] mengembangkan sistem POS berbasis web untuk Restoran Bakmi Djowo, namun belum mengintegrasikan deduksi stok secara otomatis dengan alur pemrosesan pesanan.

Berdasarkan uraian di atas, penelitian ini bertujuan untuk merancang dan mengimplementasikan sistem manajemen inventori pada POS W9 Cafe menggunakan Laravel dan Filament.

## 1.2 Rumusan Masalah

Berdasarkan latar belakang yang telah diuraikan, dapat dirumuskan beberapa permasalahan penelitian sebagai berikut:

1. Bagaimana merancang dan membangun sistem manajemen inventori pada POS W9 Cafe yang mampu mengelola dua jalur stok paralel, yaitu bahan baku berbasis resep dan stok menu produk jadi, menggunakan Laravel dan Filament?

2. Bagaimana mengimplementasikan mode deduksi batch FEFO dan FIFO dengan *pessimistic locking* untuk menjaga konsistensi data pada transaksi konkuren?

3. Bagaimana mengintegrasikan deduksi stok secara otomatis dengan modul transaksi kasir melalui mekanisme *Service Layer* sehingga setiap pemrosesan pesanan menghasilkan pencatatan pergerakan stok yang immutable?

4. Bagaimana melakukan pengujian sistem menggunakan pengujian *black box*, *white box*, dan pengujian performa untuk memvalidasi kebenaran algoritma deduksi, konsistensi data, dan ketahanan terhadap transaksi konkuren?

## 1.3 Batasan Masalah

Penelitian ini memiliki batasan-batasan sebagai berikut:

1. Sistem berfokus pada manajemen inventori untuk bahan baku dan stok menu pada kafe, serta fitur pendukung terkait seperti autentikasi admin, dashboard, dan manajemen data master (kategori dan menu). Sistem dikembangkan pada platform POS W9 Cafe yang sudah memiliki modul transaksi kasir dan pelanggan, sehingga penelitian ini berfokus pada modul inventori dan panel administrasi Filament. Fitur seperti manajemen akun kasir, laporan keuangan, dan modul di luar konteks inventori tidak termasuk.

2. Mode deduksi batch yang diimplementasikan terbatas pada FEFO (*First-Expiry-First-Out*) dan FIFO (*First-In-First-Out*).

3. Sistem hanya dapat diakses jika perangkat terhubung dengan jaringan internet.

4. Pengujian terbatas pada pengujian *black box*, *white box*, dan pengujian performa yang mencakup verifikasi algoritma deduksi, konsistensi data, validasi fungsionalitas sistem, dan ketahanan terhadap transaksi konkuren.

## 1.4 Tujuan Penelitian

Berdasarkan masalah yang telah dirumuskan, tujuan dari penelitian ini adalah sebagai berikut:

1. Mengimplementasikan sistem manajemen inventori pada POS W9 Cafe yang mampu mengelola dua jalur stok paralel (bahan baku berbasis resep dan stok menu produk jadi) menggunakan Laravel dan Filament.

2. Mengimplementasikan mode deduksi batch FEFO dan FIFO dengan *pessimistic locking* untuk memastikan konsistensi data pada lingkungan transaksi konkuren.

3. Mengintegrasikan deduksi stok secara otomatis dengan modul transaksi kasir melalui mekanisme *Service Layer* yang dipanggil dari beberapa titik masuk pemrosesan pesanan, serta memastikan *immutability* catatan pergerakan stok.

4. Melakukan pengujian sistem dengan metode *black box*, *white box*, dan pengujian performa untuk memvalidasi seluruh fungsionalitas sistem.

## 1.5 Manfaat Penelitian

Penelitian ini diharapkan dapat memberikan manfaat sebagai berikut:

1. **Manfaat bagi Pengembang**: Memberikan pengalaman dalam merancang sistem manajemen inventori yang terstruktur menggunakan Laravel dan Filament, khususnya dalam penerapan pola arsitektur *Service Layer*, *Observer*, serta implementasi algoritma deduksi batch dengan *pessimistic locking*.

2. **Manfaat bagi Kafe**: Mendapatkan sistem manajemen inventori yang terintegrasi dengan POS untuk mengelola stok bahan baku dan stok menu secara efisien, serta menyediakan riwayat pergerakan stok yang lengkap dan immutable untuk mendukung audit.

3. **Manfaat bagi Karyawan**: Mempermudah admin dalam memantau ketersediaan stok bahan baku melalui panel Filament, serta mengurangi pencatatan manual karena deduksi stok terjadi secara otomatis.

4. **Manfaat bagi Peneliti Selanjutnya**: Menjadi referensi bagi pengembangan sistem manajemen inventori serupa, khususnya dalam implementasi algoritma deduksi batch dan integrasi *Service Layer*.

## 1.6 Metodologi Penelitian

Penelitian Tugas Akhir ini dilaksanakan melalui tahapan-tahapan yang perlu ditempuh. Berikut adalah penjabaran tahapan dalam pengerjaan penelitian Tugas Akhir ini:

1. **Requirements (Pengumpulan Kebutuhan)**: Mengidentifikasi dan memprioritaskan kebutuhan sistem manajemen inventori melalui observasi proses bisnis di W9 Cafe dan studi literatur terkait algoritma deduksi batch, pessimistic locking, Laravel, dan Filament.

2. **Design (Perancangan)**: Membuat rancangan solusi berupa pemodelan proses, perancangan basis data, arsitektur aplikasi, algoritma deduksi, dan antarmuka panel Filament.

3. **Development (Pengembangan)**: Melakukan implementasi secara bertahap per modul dalam dua iterasi. Iterasi pertama mencakup pembuatan migration, model, service layer, dan observer untuk fungsionalitas inti inventori. Iterasi kedua mencakup pengembangan panel administrasi Filament dan integrasi dengan modul transaksi kasir.

4. **Testing (Pengujian)**: Melakukan pengujian menggunakan metode black box, white box, dan pengujian performa untuk memvalidasi seluruh fungsionalitas sistem.

5. **Deployment (Penyebaran)**: Sistem dikerahkan ke lingkungan produksi di W9 Cafe melalui server lokal berbasis Docker.

6. **Review (Tinjauan)**: Evaluasi hasil pengujian dan penyusunan dokumentasi sistem sebagai bagian dari laporan tugas akhir.

## 1.7 Sistematika Penulisan

Tugas akhir ini terdiri atas lima bab dengan susunan sebagai berikut.

**BAB I PENDAHULUAN**

Berisi latar belakang, rumusan masalah, batasan masalah, tujuan penelitian, manfaat penelitian, metodologi penelitian, dan sistematika penulisan.

**BAB II KAJIAN PUSTAKA**

Membahas penelitian terdahulu, metode penelitian yang digunakan, serta landasan teori yang meliputi konsep sistem manajemen inventori, algoritma FEFO dan FIFO, *pessimistic locking*, Laravel, Filament, dan metode pengujian.

**BAB III PERANCANGAN SISTEM**

Berisi gambaran umum sistem, lingkungan pengembangan, analisis kebutuhan fungsional dan non-fungsional, perancangan proses dan alur sistem, perancangan basis data, perancangan arsitektur aplikasi, serta perancangan antarmuka.

**BAB IV IMPLEMENTASI DAN PENGUJIAN**

Menyajikan implementasi basis data, *service layer*, observer, panel administrasi, serta hasil pengujian *black box*, *white box*, dan performa.

**BAB V PENUTUP**

Bab ini berisi kesimpulan dari perancangan, implementasi, dan pengujian yang telah dilakukan, serta saran pengembangan dan penelitian lebih lanjut pada masa mendatang.
