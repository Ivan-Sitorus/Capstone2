# PERANCANGAN DAN IMPLEMENTASI SISTEM ORDER
KAFE TERINTEGRASI DENGAN MODUL POS–KDS–WAITER
MENGGUNAKAN LARAVEL
Diajukan sebagai salah satu syarat
untuk memperoleh gelar Sarjana Teknik
YUDA NADHIKA
21120121140088
DEPARTEMEN TEKNIK KOMPUTER
FAKULTAS TEKNIK
UNIVERSITAS DIPONEGORO
2026
ii
HALAMAN PENGESAHAN
Tugas Akhir
PERANCANGAN DAN IMPLEMENTASI SISTEM ORDER KAFE
TERINTEGRASI DENGAN MODUL POS–KDS–WAITER
MENGGUNAKAN LARAVEL
Tugas Akhir ini diajukan oleh:
Yuda Nadhika
21120121140088
Kepada
Departemen Teknik Komputer
Universitas Diponegoro
Telah disetujui Oleh
Pembimbing I Pembimbing II
Patricia Evericho Ilmam Fauzi Hashbil
Mountaines, S.T., M.Cs. Alim, S.T., M.Kom.
NPPU.H.7.199203222022042001 NPPU.H.7.199611182022101001
iii
HALAMAN PERNYATAAN ORISINALITAS
Tugas Akhir ini adalah hasil karya saya sendiri, dan semua sumber baik yang
dikutip maupun yang dirujuk telah saya nyatakan dengan benar.
```
Nama : Yuda Nadhika
```
```
NIM : 21120121140088
```
Tanda Tangan :
```
Tanggal : 19 Januari 2026
```
iv
HALAMAN PERNYATAAN PERSETUJUAN PUBLIKASI
TUGAS AKHIR UNTUK KEPENTINGAN AKADEMIS
Sebagai sivitas akademika Universitas Diponegoro, saya yang bertanda tangan di
bawah ini:
```
Nama : YUDA NADHIKA
```
```
NIM : 21120121140088
```
```
Departemen : TEKNIK KOMPUTER
```
```
Fakultas : TEKNIK
```
Jenis Karya : TUGAS AKHIR
demi pengembangan ilmu pengetahuan, menyetujui untuk memberikan kepada
```
Universitas Diponegoro Hak Bebas Royalti Non Eksklusif (Non-exclusive
```
```
Royalty Free Right) atas karya ilmiah saya berjudul:
```
PERANCANGAN DAN IMPLEMENTASI SISTEM ORDER KAFE
TERINTEGRASI DENGAN MODUL POS–KDS–WAITER
MENGGUNAKAN LARAVEL
```
beserta perangkat yang ada (jika diperlukan). Dengan Hak Bebas Royalti/Non
```
Eksklusif ini Universitas Diponegoro berhak menyimpan, mengalih
```
media/formatkan, mengelola dalam bentuk pangkalan data (database), merawat
```
dan memublikasikan Tugas Akhir saya selama tetap mencantumkan nama saya
sebagai penulis/pencipta dan sebagai pemilik Hak Cipta. Demikian pernyataan ini
saya buat dengan sebenarnya.
Dibuat di: Semarang
Pada tanggal: 19 Januari 2026
Yang menyatakan,
```
(Yuda Nadhika)
```
v
KATA PENGANTAR
vi
DAFTAR ISI
HALAMAN PENGESAHAN ............................................................................... ii
HALAMAN PERNYATAAN ORISINALITAS................................................ iii
HALAMAN PERNYATAAN PERSETUJUAN PUBLIKASI......................... iv
KATA PENGANTAR ........................................................................................... v
DAFTAR ISI ......................................................................................................... vi
DAFTAR TABEL................................................................................................. ix
DAFTAR GAMBAR ........................................................................................... xii
ABSTRAK .......................................................................................................... xiv
ABSTRACT .......................................................................................................... xv
BAB I PENDAHULUAN ...................................................................................... 1
1.1 Latar Belakang .......................................................................................................... 1
1.2 Rumusan Masalah ..................................................................................................... 3
1.3 Tujuan Penelitian....................................................................................................... 3
1.4 Batasan Masalah ........................................................................................................ 4
1.5 Manfaat Penelitian..................................................................................................... 4
1.6 Metodologi Penelitian ............................................................................................... 6
1.7 Sistematika Penulisan ................................................................................................ 7
BAB II TINJAUAN PUSTAKA ......................................................................... 10
2.1 Kajian Penelitian ..................................................................................................... 10
2.2 Landasan Teori ........................................................................................................ 13
```
2.2.1 Point of Sales (POS) ..................................................................................................... 13
```
```
2.2.2 Kitchen Display System (KDS) ..................................................................................... 13
```
2.2.3 Waiter Orders ............................................................................................................... 14
2.2.4 Metode Pengembangan Iteratif ..................................................................................... 14
```
2.2.5 User Acceptance Test (UAT)........................................................................................ 16
```
2.2.6 Laravel .......................................................................................................................... 17
```
2.2.7 Model View Controller (MVC) .................................................................................... 17
```
2.2.8 Eloquent ORM .............................................................................................................. 18
2.2.9 Livewire ........................................................................................................................ 18
2.2.10 Filament Components ................................................................................................. 19
2.2.11 MySQL ....................................................................................................................... 19
2.2.12 PHP............................................................................................................................ 19
2.2.13 Containerization .......................................................................................................... 20
2.2.14 Docker......................................................................................................................... 20
2.2.15 Full-Stack .................................................................................................................... 21
vii
BAB III PERANCANGAN SISTEM ................................................................ 22
3.1 Gambaran Umum dan Ruang Lingkup Perancangan .............................................. 22
3.2 Analisis Kebutuhan Sistem ..................................................................................... 22
3.2.1 Wawancara Semi-Terstruktur ....................................................................................... 23
3.2.2 Proses Bisnis Sistem ..................................................................................................... 25
3.2.3 Kebutuhan Fungsional .................................................................................................. 25
3.2.4 Kebutuhan Non-Fungsional .......................................................................................... 29
3.3 Perancangan Proses dan Alur Sistem ...................................................................... 31
3.3.1 Use Case Diagram ........................................................................................................ 32
3.3.2 Skenario Use Case ........................................................................................................ 34
3.3.3 Diagram Alur Proses..................................................................................................... 45
3.4 Perancangan Struktur Sistem .................................................................................. 52
3.4.1 Desain Basis Data ......................................................................................................... 52
3.4.2 Entity Relationship Diagram ........................................................................................ 52
3.4.3 Diagram Kelas .............................................................................................................. 54
3.4.4 Relasi Antar Entitas ...................................................................................................... 56
3.4.5 Deskripsi Entitas ........................................................................................................... 57
```
3.5 Perancangan Arsitektur Aplikasi (MVC & Komponen Interaktif) ......................... 67
```
3.5.1 Model ............................................................................................................................ 67
3.5.2 Pengendalian Proses: MVC dan Livewire .................................................................... 68
3.5.3 View .............................................................................................................................. 69
3.6 Perancangan Antarmuka Modul POS-KDS-Waiter ................................................ 70
3.6.1 Perancangan Antarmuka POS....................................................................................... 70
3.6.2 Perancangan Antarmuka Kitchen Display System ........................................................ 71
3.6.3 Perancangan Antarmuka Waiter Orders ....................................................................... 73
3.6.4 Perancangan Antarmuka Waiter Take Order................................................................ 74
3.7 Dukungan Teknologi & Lingkungan Pengembangan ............................................. 75
3.7.1 Basis Data MySQL ....................................................................................................... 77
3.7.2 TablePlus sebagai Alat Inspeksi dan Validasi Basis Data ............................................ 77
3.7.3 Lingkungan Pengembangan Berbasis Docker .............................................................. 77
3.8 Rancangan Pengujian .............................................................................................. 78
3.8.1 Rancangan Pengujian Black Box .................................................................................. 78
3.8.2 Rancangan Pengujian User Acceptance Testing........................................................... 79
BAB IV IMPLEMENTASI SISTEM ................................................................ 84
4.1 Gambaran Umum Implementasi Sistem ................................................................. 84
4.2 Implementasi Basis Data ......................................................................................... 89
4.3 Implementasi Modul Pendukung ............................................................................ 91
4.3.1 Multi-Tenant dan Isolasi Data....................................................................................... 91
```
4.3.2 Login dan Otorisasi Berbasis Peran (RBAC) ............................................................... 92
```
4.3.3 Data Master Pemesanan................................................................................................ 94
4.3.4 Pengaturan Meja untuk Pesanan Dine-In...................................................................... 96
4.3.5 Diskon dan Pajak .......................................................................................................... 96
```
4.4 Implementasi Modul Point of Sale (POS) ............................................................... 97
```
4.4.1 Antarmuka POS ............................................................................................................ 98
4.4.2 Logika Bisnis POS ...................................................................................................... 102
```
4.5 Implementasi Modul Kitchen Display System (KDS) ........................................... 115
```
viii
4.5.1 Antarmuka KDS ......................................................................................................... 115
4.5.2 Logika Bisnis KDS ..................................................................................................... 119
4.6 Implementasi Modul Waiter Orders ..................................................................... 124
4.6.1 Antarmuka Halaman Waiter Orders ........................................................................... 125
4.6.2 Logika Bisnis Waiter Orders ...................................................................................... 127
4.7 Implementasi Modul Waiter Take Order .............................................................. 133
4.7.1 Antarmuka Waiter Take Order ................................................................................... 134
4.7.2 Logika Bisnis Waiter Take Order............................................................................... 137
4.8 Implementasi Customer Check Order dan Track Order ...................................... 147
4.8.1 Antarmuka Customer Check Order ............................................................................ 148
4.8.2 Logika Bisnis Customer Check Order ........................................................................ 150
```
4.8.3 Antarmuka Order Tracking (Customer Track Order) ................................................ 153
```
4.8.4 Logika Bisnis Order Tracking dan Sinkronisasi Status ............................................. 155
4.8.5 Integrasi POS-KDS-Waiter-Customer........................................................................ 156
BAB V PENGUJIAN DAN EVALUASI ......................................................... 158
5.1 Metode Pengujian .................................................................................................. 158
5.2 Pengujian Black Box.............................................................................................. 158
```
5.2.1 Pengujian Black Box – Point of Sales (POS) .............................................................. 159
```
```
5.2.2 Pengujian Black Box – Kitchen Display System (KDS) ............................................. 161
```
5.2.3 Pengujian Black Box – Waiter Orders ....................................................................... 163
5.2.4 Pengujian Black Box – Waiter Take Order................................................................ 164
5.2.5 Pengujian Black Box – Customer Check Order .......................................................... 166
5.3 User Acceptance Test ............................................................................................ 167
5.3.1 Skenario Pengujian pada Role Cashier....................................................................... 167
5.3.2 Skenario Pengujian pada Role Kitchen Staff............................................................... 169
3.5.3 Skenario Pengujian pada Role Waiter ........................................................................ 170
3.5.4 Skenario Pengujian pada Role Customer .................................................................... 171
3.5.5 Kesimpulan Pengujian UAT ....................................................................................... 173
5.4 Evaluasi, Temuan, dan Keterbatasan ................................................................... 173
BAB VI PENUTUP ........................................................................................... 176
6.1 Kesimpulan............................................................................................................ 176
6.2 Saran Pengembangan ............................................................................................ 177
DAFTAR PUSTAKA ........................................................................................ 178
BIODATA MAHASISWA................................................................................ 180
LAMPIRAN A ................................................................................................... 181
LAMPIRAN B ................................................................................................... 188
ix
DAFTAR TABEL
Tabel 3.1 Kebutuhan fungsional sistem. ............................................................... 26
Tabel 3.2 Kebutuhan non-fungsional. ................................................................... 30
Tabel 3.3 Use case login. ...................................................................................... 35
Tabel 3.4 Use case membuat pesanan................................................................... 36
Tabel 3.5 Use case memproses pembayaran pesanan. .......................................... 37
Tabel 3.6 Use case memproses pesanan. .............................................................. 39
Tabel 3.7 Use case menyajikan pesanan. .............................................................. 40
Tabel 3.8 Use case customer check order. ............................................................ 41
Tabel 3.9 Use case login admin platform. ............................................................ 43
Tabel 3.10 Use case mengelola tenant. ................................................................. 44
Tabel 3.11 Tabel relasi antar entitas ..................................................................... 56
Tabel 3.12 Deskripsi entitas orders. ..................................................................... 57
Tabel 3.13 Deskripsi entitas order_items.............................................................. 59
Tabel 3.14 Deskripsi entitas order_taxes. ............................................................. 60
Tabel 3.15 Deskripsi entitas cart_items. ............................................................... 61
Tabel 3.16 Deskripsi entitas customer_details...................................................... 61
Tabel 3.17 Deskripsi entitas product. ................................................................... 62
Tabel 3.18 Deskripsi entitas category. .................................................................. 63
Tabel 3.19 Deskripsi entitas product_options. ...................................................... 63
Tabel 3.20 Deskripsi entitas product_option_values. ........................................... 63
Tabel 3.21 Deskripsi entitas floors........................................................................ 64
Tabel 3.22 Deskripsi entitas dining_tables. .......................................................... 64
Tabel 3.23 Deskripsi entitas tenants. .................................................................... 65
Tabel 3.24 Deskripsi entitas taxes. ........................................................................ 65
Tabel 3.25 Deskripsi entitas discounts. ................................................................. 66
Tabel 3.26 Daftar model yang digunakan oleh sistem. ......................................... 67
Tabel 3.27 Model pendukung. .............................................................................. 68
Tabel 3.28 Controller MVC dan Livewire. .......................................................... 68
Tabel 3.29 View inti modul: POS-KDS-Waiter. ................................................... 69
Tabel 3.30 View pendukung. ................................................................................. 70
x
```
Tabel 3.31 Pengujian fungsional (black box). ....................................................... 79
```
Tabel 3.32 Penilaian skala Likert. ......................................................................... 80
Tabel 3.33 Pernyataan user acceptance test – Cashier. ........................................ 80
Tabel 3.34 Pernyataan user acceptance test – Kitchen Staff. ................................ 81
Tabel 3.35 Pernyataan user acceptance test – Waiter........................................... 82
Tabel 3.36 Pernyataan user acceptance test – Customer. ..................................... 83
Tabel 4.1 Konfigurasi compose.yaml untuk layanan laravel.test. ........................ 86
Tabel 4.2 Konfigurasi compose.yaml untuk layanan mysql. ................................. 87
Tabel 4.3 ProductComponent.php. ..................................................................... 103
Tabel 4.4 CartComponent.php. ........................................................................... 105
Tabel 4.5 CartHelper.php. .................................................................................. 105
Tabel 4.6 Ringkasan subtotal dan diskon pada CartHelper. ............................... 106
Tabel 4.7 PriceAfterDiscount.php....................................................................... 107
Tabel 4.8 OrderTaxCalculator.php..................................................................... 109
Tabel 4.9 OrderComponent.php ......................................................................... 110
Tabel 4.10 PaymentComponent.php. .................................................................. 111
Tabel 4.11 Penyegaran status pemenuhan pada Order.php. ............................... 112
Tabel 4.12 Guard validasi checkout pada OrderComponent.php. ...................... 113
Tabel 4.13 Validasi stok keranjang pada OrderComponent.php ........................ 114
```
Tabel 4.14 Kueri dan pemetaan tampilan KDS pada method render()............... 120
```
Tabel 4.15 Aksi start preparing item pada KitchenOrders................................. 121
Tabel 4.16 Aksi preparing/ready pada level order. ............................................ 122
Tabel 4.17 Kode items board pada KitchenOrders. ............................................ 124
```
Tabel 4.18 Pemuatan dan pemetaan daftar pesanan pada refreshOrders(). ........ 127
```
Tabel 4.19 Perhitungan statistik waiter orders. .................................................. 129
Tabel 4.20 Aksi markAsServed pada halaman waiter order. .............................. 130
Tabel 4.21 Aksi serveItem pada WaiterOrdersPage........................................... 131
Tabel 4.22 Perubahan status pada OrderFulfillmentService. .............................. 132
Tabel 4.23 TakeOrderService pada sumber data menu, opsi, dan diskon. ......... 138
Tabel 4.24 ProductBrowser untuk katalog menudan event pemilihan item. ...... 140
Tabel 4.25 CartDrawer. ...................................................................................... 142
Tabel 4.26 CartDrawer – sendToCashier. .......................................................... 144
xi
Tabel 4.27 Table modal pemilihan meja. ............................................................ 147
Tabel 4.28 CustomerOrderLookupController. ................................................... 151
Tabel 4.29 Implementasi kueri customer order. ................................................. 152
Tabel 4.30 OrderTrackingController.................................................................. 155
Tabel 4.31 OrderTrackingController.................................................................. 156
```
Tabel 5.1 Pengujian black box point of sales (POS). ......................................... 159
```
```
Tabel 5.2 Pengujian black box kitchen display system (KDS). ........................... 161
```
Tabel 5.3 Pengujian black box Waiter Orders. ................................................... 163
Tabel 5.4 Pengujian black box Waiter Take Order. ............................................ 164
Tabel 5.5 Pengujian black box customer check order......................................... 166
Tabel 5.6 Hasil pengujian terhadap role cashier. ............................................... 168
Tabel 5.7 Hasil pengujian terhadap role kitchen staff. ........................................ 169
Tabel 5.8 Hasil pengujian terhadap role waiter. ................................................. 170
Tabel 5.9 Hasil pengujian terhadap role customer.............................................. 172
xii
DAFTAR GAMBAR
Gambar 3.1 Use case POS-KDS-Waiter-Customer. ............................................. 33
Gambar 3.2 Use case tenant management Qash. .................................................. 34
Gambar 3.3 Proses tenant management ................................................................ 46
Gambar 3.4 Proses point of sale. ........................................................................... 48
Gambar 3.5 Proses Kitchen Display System. ........................................................ 49
Gambar 3.6 Proses Waiter..................................................................................... 50
Gambar 3.7 Proses customer check order. ............................................................ 51
Gambar 3.8 Proses customer check order. ............................................................ 51
Gambar 3.9 ERD sistem pemrosesan pesanan POS–KDS–Waiter. ...................... 53
Gambar 3.10 ERD sistem pemrosesan pesanan POS–KDS–Waiter. .................... 55
Gambar 3.11 Antarmuka POS............................................................................... 71
Gambar 3.12 Antarmuka kitchen display system. ................................................. 72
Gambar 3.13 Antarmuka waiter orders. ............................................................... 74
Gambar 3.14 Antarmuka waiter take order. ......................................................... 75
Gambar 4.1 Docker container pada saat server dimatikan. .................................. 86
Gambar 4.2 Docker kontainer berjalan pada server lokal..................................... 88
Gambar 4.3 Pembuatan tenant sebagai ruang kerja kafe. ..................................... 91
Gambar 4.4 Halaman login pada lingkungan tenant. ............................................ 92
Gambar 4.5 Tampilan sidebar berdasarkan peran pengguna ................................ 93
Gambar 4.6 Halaman admin memberikan batasan akses kepada karyawan. ........ 93
Gambar 4.7 Halaman 403. .................................................................................... 94
Gambar 4.8 Manajemen kategori produk sebagai pengelompokan menu. ........... 95
Gambar 4.9 Manajemen produk sebagai data master pemesanan. ....................... 95
Gambar 4.10 Manajemen meja untuk mendukung pesanan dine-in. .................... 96
Gambar 4.11 Konfigurasi pajak sebagai komponen pendukung transaksi. .......... 97
Gambar 4.12 Konfigurasi diskon sebagai komponen pendukung transaksi. ........ 97
Gambar 4.13 Halaman POS. ................................................................................. 99
Gambar 4.14 Halaman POS bagian pemesanan item. ........................................... 99
Gambar 4.15 Halamn POS pengisian data pelanggan. ....................................... 100
Gambar 4.16 Halaman POS mencari data pelanggan yang sudah terdaftar. ...... 100
xiii
Gambar 4.17 Informasi keranjang POS. ............................................................. 101
Gambar 4.18 Proses pembayaran pesanan. ......................................................... 101
Gambar 4.19 Halaman ringkasan pesanan. ......................................................... 102
```
Gambar 4.20 Halaman KDS – kondisi awal (tanpa pesanan aktif)..................... 116
```
Gambar 4.21 Halaman KDS saat pesanan masuk. .............................................. 116
Gambar 4.22 Halaman KDS menampilkan order dengan status preparing. ...... 117
Gambar 4.23 Halaman KDS ketika status item berubah menjadi ready. ............ 117
Gambar 4.24 Notifikasi mark order ready untuk seluruh item ........................... 118
Gambar 4.25 Halaman items board. ................................................................... 118
Gambar 4.26 Ringkasan kinerja kitchen hari ini. ................................................ 119
Gambar 4.27 Halaman waiter orders menampilkan pesanan ready to serve. .... 125
Gambar 4.28 Detail pesanan pada waiter orders. ............................................... 126
Gambar 4.29 Aksi serve all ready items. ............................................................ 126
Gambar 4.30 Detail rekap harian kinerja waiter. ................................................ 127
Gambar 4.31 Tampilan katalog produk pada halaman waiter take orders. ........ 135
Gambar 4.32 Modal detail produk pada waiter take orders. .............................. 135
```
Gambar 4.33 Keranjang (cart drawer)................................................................ 136
```
Gambar 4.34 Pemilihan meja pada waiter take order......................................... 136
Gambar 4.35 Notifikasi stok tidak mencukupi sebelum order dikirim. ............. 137
Gambar 4.36 Notifikasi setelah transaksi berhasil dibuat. .................................. 137
Gambar 4.37 Halaman customer check order. .................................................... 149
Gambar 4.38 Halaman customer check order menggunakan reference_no. ...... 149
Gambar 4.39 Form pencarian berdasarkan reference_no. .................................. 150
Gambar 4.40 Halaman customer track order ...................................................... 154
Gambar 4.41 Perubahan status pesanan pada halaman order tracking. .............. 154
Gambar 4.42 Ringkasan pesanan. ....................................................................... 155
xiv
ABSTRAK
Industri penyediaan makanan dan minuman di Indonesia terus berkembang sehingga persaingan
layanan pada level operasional, seperti kecepatan, akurasi, dan konsistensi, menjadi semakin
menentukan, termasuk bagi kafe kecil–menengah dengan layanan dine-in dan takeaway. Pada
praktiknya, alur pemesanan melibatkan kasir, dapur, dan waiter secara berurutan. Ketika proses
masih manual atau sistem tidak terintegrasi, terutama saat jam sibuk, risiko keterlambatan informasi,
salah pencatatan, antrean tidak terpantau, serta miskomunikasi meningkat. Penelitian ini bertujuan
merancang dan mengimplementasikan sistem pemrosesan pesanan kafe berbasis web yang
```
mengintegrasikan modul Point of Sale (POS), Kitchen Display System (KDS), dan waiter secara
```
terhubung, serta menyediakan fitur Customer Check/Track Order sebagai akses informasi read-only
bagi pelanggan.
Pengembangan sistem dilakukan menggunakan kerangka kerja Laravel dengan fokus pada
```
integrasi alur POS–KDS–Waiter, termasuk konsistensi perhitungan nilai transaksi (subtotal, diskon,
```
```
pajak, dan grand total) serta validasi berlapis untuk mencegah transaksi tidak valid dan menjaga
```
ketersediaan stok. Evaluasi dilakukan melalui pengujian Black Box berbasis skenario dan User
```
Acceptance Test (UAT) yang melibatkan kasir, staf dapur, staf pelayan, dan pelanggan. Hasil
```
pengujian menunjukkan fungsi utama setiap modul berjalan sesuai rancangan, sinkronisasi status
pesanan antarmodul berjalan baik dalam batasan pengujian, dan sistem dapat diterima untuk
mendukung kebutuhan operasional kafe dengan tingkat keberhasilan mencapai 100%. Selain itu,
hasil UAT menunjukkan tingkat penerimaan pengguna berada pada kisaran 88–96%, yang
mengindikasikan sistem dinilai mudah dipahami, konsisten digunakan, dan mampu mendukung
kebutuhan operasional sesuai ekspektasi pengguna.
Kata kunci: POS, sistem pemesanan kafe, integrasi modul.
xv
ABSTRACT
Indonesia’s food and beverage industry continues to grow, making operational performance
such as speed, accuracy, and service consistency increasingly decisive, especially for small to
medium cafés offering dine-in and takeaway services. In practice, the ordering workflow involves
the cashier, kitchen staff, and waiters in sequence. When this process remains manual or the system
is not integrated, particularly during peak hours, issues such as delayed order delivery to the
kitchen, incorrect order recording, unmonitored queues, and miscommunication are more likely to
occur. This study aims to design and implement a web-based café order processing system that
```
integrates the Point of Sale (POS), Kitchen Display System (KDS), and waiter modules in a
```
connected workflow, while also providing Customer Check/Track Order features as read-only
endpoints so customers can monitor order status without modifying transaction data.
The system was developed using Laravel framework with a primary focus on POS–KDS–Waiter
```
workflow integration, including consistent transaction calculations (subtotal, discounts, tax, and
```
```
grand total) and layered validations to prevent invalid transactions and ensure stock availability.
```
The evaluation was conducted through scenario-based Black Box testing and a User Acceptance
```
Test (UAT) involving cashiers, kitchen staff, waiters, and customers. The results show that the core
```
functions of each module operate as designed, order status synchronization across modules works
reliably within the testing scope, and the system is acceptable for supporting café operations,
achieving a 100% success rate in functional testing. In addition, the UAT results indicate user
acceptance levels ranging from 88% to 96%, suggesting that the system is easy to understand,
consistently usable, and capable of meeting operational needs as expected. This level of acceptance
shows that the system is capable of supporting café operational needs in line with user expectations.
```
Keywords: POS, cafe order system, module integration.
```
1
BAB I
PENDAHULUAN
1.1 Latar Belakang
Industri penyediaan makanan dan minuman di Indonesia merupakan sektor
yang besar dan terus berkembang, sehingga persaingan layanan di tingkat
operasional seperti kecepatan, akurasi, dan konsistensi menjadi semakin penting.
```
Badan Pusat Statistik (BPS) melaporkan bahwa pada tahun 2023 terdapat 4,85 juta
```
usaha penyediaan makanan dan minuman, dengan penyerapan tenaga kerja sekitar
9,80 juta pekerja serta nilai penjualan mencapai 998,37 triliun rupiah [1]. Skala dan
aktivitas ekonomi tersebut menunjukkan bahwa efisiensi proses layanan merupakan
kebutuhan nyata bagi pelaku usaha, termasuk kafe skala kecil hingga menengah
yang melayani pelanggan dine-in maupun takeaway.
Dalam operasional kafe, alur pemesanan merupakan proses inti yang sangat
menentukan kualitas layanan karena melibatkan beberapa peran yang saling
bergantung, mulai dari pencatatan pesanan, pemrosesan di dapur, hingga penyajian.
Namun pada praktiknya, banyak kafe masih menghadapi kendala ketika alur kerja
dijalankan secara manual atau menggunakan sistem yang tidak terintegrasi,
terutama saat jam sibuk. Kondisi ini dapat memunculkan masalah seperti informasi
pesanan terlambat diterima staf dapur, kesalahan pencatatan, antrean pesanan yang
tidak terpantau, serta ketidaksinkronan antara kasir, staf dapur, dan pelayan. Hal
tersebut sejalan dengan Saputra, dkk. [2] yang menyatakan bahwa pencatatan
manual menggunakan kertas berpotensi menimbulkan penumpukan nota dan
menghambat efektivitas operasional.
Proses pemesanan yang ideal perlu berjalan dengan alur yang rapi dan
terukur. Pesanan cukup dicatat sekali di titik transaksi, lalu informasi dan statusnya
otomatis tersinkron secara real-time ke dapur dan pelayan, sehingga setiap peran
bekerja berdasarkan informatic yang sama. Dengan kondisi tersebut, pesanan dapat
diproses lebih cepat, kesalahan komunikasi dapat ditekan, dan status pesanan
menjadi konsisten dari awal transaksi hingga pesanan disajikan.
2
```
Kesenjangan muncul ketika sistem transaksi atau point of sales (POS) dan
```
```
sistem pemantauan dapur atau kitchen display system (KDS) tidak terintegrasi
```
langsung, sehingga staf dapur masih perlu melakukan pengecekan manual atau
menerima informasi yang terlambat, sementara pelayan tidak selalu memiliki akses
real-time untuk mengetahui pesanan mana yang sudah siap disajikan. Pada kondisi
seperti ini, jeda waktu yang tidak diperlukan mudah terjadi dan peluang human
error meningkat karena status pesanan tidak dikelola dalam satu alur yang
konsisten lintas peran [3]. Artinya, harapan berupa sinkronisasi status pesanan dan
koordinasi real-time belum sepenuhnya tercapai dalam praktik operasional kafe.
Salah satu pendekatan yang banyak digunakan untuk memperbaiki pengelolaan
```
antrean pekerjaan di dapur adalah kitchen display system (KDS), yaitu tampilan
```
digital yang menggantikan tiket/nota kertas untuk menampilkan pesanan secara
real-time dan membantu pengendalian status pekerjaan. KDS mendukung
pengelompokan pesanan, pembaruan status, serta pemantauan progres agar proses
produksi lebih terstruktur. Namun dalam layanan dine-in, penyelesaian di dapur
belum menjadi akhir alur karena pesanan yang siap tetap memerlukan koordinasi
penyajian ke meja pelanggan. Karena itu, diperlukan modul Waiter yang
memungkinkan pemantauan pesanan siap saji dan konfirmasi penyajian secara
sistematis. Penelitian yang dilakukan oleh Dharmaadi dan Sasmitha [3] terkait
sistem manajemen restoran juga menekankan pentingnya integrasi antarmuka
antara pelayan dan staf dapur dalam satu platform untuk mengurangi
ketergantungan pada cara manual dan meningkatkan kelancaran alur layanan.
Pada penelitian ini, sistem yang dikembangkan merupakan bagian dari platform
bernama Qash, yaitu platform operasional kafe berbasis web yang dirancang untuk
mendukung penggunaan oleh lebih dari satu kafe dalam konteks tenant. Penelitian
ini membatasi pembahasan pada modul inti pemrosesan pesanan, yaitu integrasi
POS–KDS–Waiter, serta dukungan customer check/track order sebagai akses
informasi read-only. Fokus read-only pada sisi pelanggan dimaksudkan agar
pelanggan dapat memeriksa dan memantau status pesanan tanpa akun dan tanpa
mengubah data transaksi, sementara analisis utama tetap terpusat pada integrasi
proses internal. Modul lain seperti pengelolaan produk, meja, dan inventori
3
diposisikan sebagai konteks pendukung alur pemesanan, sedangkan modul
tambahan seperti HRM, absensi, notifikasi, dan dashboard tidak menjadi bagian
analisis utama agar ruang lingkup penelitian tetap terfokus. Pengembangan sistem
menggunakan Laravel sebagai fondasi karena menyediakan struktur aplikasi yang
terorganisasi dan mendukung pengembangan web modern.
1.2 Rumusan Masalah
Berdasarkan latar belakang yang telah diuraikan, dapat dirumuskan
beberapa permasalahan penelitian sebagai berikut:
1. Bagaimana membangun sistem pemrosesan pesanan kafe yang terintegrasi
antara POS, KDS, dan Waiter?
2. Bagaimana merancang dan mengimplementasikan KDS untuk
menampilkan pesanan masuk demi mendukung pembaruan status pesanan
oleh staf dapur?
3. Bagaimana mengintegrasikan alur POS–KDS–Waiter hingga pelanggan
dapat melakukan customer check order dan customer track order secara
read-only dengan status yang konsisten?
4. Bagaimana melakukan pengujian sistem menggunakan pengujian
```
fungsional (black box) serta user acceptance test (UAT) untuk menilai
```
kesesuaian fungsi serta tingkat penerimaan pengguna terhadap sistem yang
dikembangkan?
1.3 Tujuan Penelitian
Berdasarkan masalah yang telah dirumuskan, tujuan dari penelitian ini
adalah sebagai berikut:
1. Mengimplementasikan modul POS berbasis web untuk pencatatan pesanan,
```
pemilihan layanan (dine-in/takeaway), pengisian data pelanggan, serta
```
penyelesaian transaksi.
2. Mengimplementasikan KDS untuk menampilkan daftar pesanan
berdasarkan status, serta menyediakan mekanisme pembaruan status
produksi oleh staf dapur.
4
3. Mengintegrasikan status dan alur kerja POS–KDS–Waiter, serta
menyediakan fitur customer check order dan customer track order secara
read-only sebagai dukungan informasi bagi pelanggan.
4. Melakukan pengujian fungsional (black box) pada modul POS, KDS,
Waiter, dan customer check/track order serta melakukan user acceptance
```
test (UAT) untuk mengukur tingkat penerimaan pengguna terhadap sistem
```
yang dikembangkan.
1.4 Batasan Masalah
Untuk menjaga fokus penelitian agar tetap relevan dengan tujuan yang telah
ditetapkan, penelitian ini memiliki batasan-batasan sebagai berikut:
1. Sistem berfokus pada proses pemesanan di kafe melalui integrasi modul
```
point of sales (POS), kitchen display system (KDS), dan Waiter.
```
2. Sistem juga menyediakan fitur customer check order dan track order
sebagai keluaran akhir untuk pelanggan, tetapi tidak mencakup akun
pelanggan.
3. Fitur multi-tenant, diskon, pajak, login, dan otorisasi berperan sebagai
modul pendukung untuk menjelaskan prasyarat alur utama, namun tidak
dibahas secara mendalam di luar kebutuhan integrasi pemesanan.
4. Sistem dirancang untuk skenario dine-in dan take away pada usaha kafe
skala kecil hingga menengah.
5. Pengujian dibatasi pada pengujian fungsional (black box) pada modul POS,
KDS, Waiter, dan customer check/track order sesuai skenario yang disusun.
6. User acceptance test (UAT) dilakukan menggunakan kuesioner skala Likert
1–5 pada responden terbatas di lingkungan studi kasus, sehingga hasil UAT
merepresentasikan penerimaan pengguna pada konteks pengujian tersebut
dan belum mencakup evaluasi pada skala operasional yang lebih luas.
1.5 Manfaat Penelitian
Berdasarkan tujuan penelitian yang telah diuraikan, penelitian ini
diharapkan dapat memberikan manfaat sebagai berikut:
5
1. Manfaat bagi Pengembang
Penelitian ini memberikan pengalaman dalam merancang sistem informasi
terintegrasi menggunakan Laravel, Livewire, dan Eloquent ORM.
Pengembang memperoleh pemahaman yang lebih mendalam mengenai
pembuatan alur operasional pada aplikasi berbasis web serta penerapan pola
arsitektur yang relevan di industri.
2. Manfaat bagi Kafe
Mendapatkan platform operasional yang terpusat dan mudah digunakan
untuk mengelola POS, serta mendapatkan sistem yang dapat meningkatkan
efektivitas operasional melalui alur pemesanan yang cepat, terstruktur, dan
minim kesalahan. Integrasi antara kasir, dapur, dan pelayan memberikan
peningkatan pada kecepatan pelayanan serta akurasi pemrosesan pesanan.
3. Manfaat bagi Karyawan
Sistem yang dikembangkan membantu karyawan, khususnya kasir, pelayan,
dan staf dapur, dalam menjalankan tugas sehari-hari. Kasir dapat mencatat
pesanan dengan lebih cepat dan konsisten tanpa harus melakukan
pencatatan manual. Staf dapur memperoleh informasi pesanan secara real-
time melalui KDS sehingga tidak perlu menerima instruksi secara verbal.
Pelayan memperoleh daftar pesanan siap saji secara otomatis sehingga dapat
mengatur prioritas pengantaran secara lebih efektif. Dengan demikian,
beban koordinasi antar karyawan menjadi lebih ringan, dan alur kerja
menjadi lebih jelas serta terarah.
4. Manfaat bagi Pelanggan
Bagi pelanggan, penelitian ini memberikan manfaat dalam bentuk
peningkatan transparansi dan kenyamanan selama proses pemesanan di
kafe. Melalui fitur customer check order, pelanggan dapat memantau status
pesanan secara mandiri berdasarkan nomor referensi atau identitas yang
digunakan saat pemesanan, tanpa harus bertanya langsung kepada kasir atau
pelayan. Informasi status pesanan yang ditampilkan secara real-time
membantu pelanggan memahami tahapan pemrosesan pesanan, mulai dari
pesanan diterima, sedang disiapkan, hingga siap disajikan atau telah tersaji.
6
Selain itu, integrasi sistem antara modul POS, KDS, dan Waiter berkontribusi
pada peningkatan akurasi dan ketepatan pelayanan kepada pelanggan. Dengan alur
pemrosesan pesanan yang terkoordinasi, risiko kesalahan penyampaian pesanan
dapat diminimalkan, sehingga pelanggan memperoleh pengalaman layanan yang
lebih baik. Secara keseluruhan, sistem yang dirancang dalam penelitian ini
mendukung peningkatan kepuasan pelanggan melalui pelayanan yang lebih
informatif, efisien, dan terstruktur.
1.6 Metodologi Penelitian
Penelitian ini menggunakan model pengembangan iteratif karena
memungkinkan pengembangan sistem dilakukan secara bertahap, adaptif terhadap
perubahan kebutuhan, dan mengutamakan validasi fungsional secara berkala.
Proses pengembangan dilakukan melalui siklus berulang yang menghasilkan
peningkatan fitur pada setiap iterasi hingga sistem stabil.
1. Studi Literatur
Tahap ini dilakukan untuk mengumpulkan landasan teori dan referensi
terkait pengembangan sistem informasi, konsep modul yang dibangun
```
(misalnya alur pemesanan/pelayanan), serta metode pengujian perangkat
```
```
lunak (black box dan user acceptance test). Hasil studi literatur digunakan
```
sebagai acuan dalam menentukan pendekatan perancangan, implementasi,
dan evaluasi sistem.
2. Observasi Lapangan dan Perencanaan
Pada tahap ini dilakukan identifikasi kebutuhan sistem, mencakup
kebutuhan fungsional dan non-fungsional. Analisis kebutuhan diperoleh
melalui observasi proses bisnis di lapangan, diskusi dengan pihak terkait
untuk menggali tujuan dan kendala operasional, serta penetapan batasan
sistem agar ruang lingkup pengembangan tetap terarah. Hasil dari tahap ini
adalah daftar kebutuhan yang terstruktur dan terprioritas, yang kemudian
digunakan sebagai landasan perancangan sistem pada Bab III.
3. Pengembangan Iteratif
Tahap pengembangan dilakukan secara iteratif, yaitu sistem dibangun
bertahap per fitur atau modul, lalu disempurnakan melalui siklus perbaikan
7
berulang hingga mencapai kondisi stabil. Pada setiap iterasi, peneliti
menetapkan prioritas kebutuhan yang akan dikerjakan, kemudian
menjalankan rangkaian proses yang mencakup analisis kebutuhan untuk
merinci fungsi, alur proses, serta data yang diperlukan pada fitur tersebut.
Hasil analisis menjadi dasar tahap desain, meliputi perancangan logika
proses, struktur data, integrasi antar modul, dan rancangan antarmuka.
Selanjutnya dilakukan implementasi untuk membangun fitur pada sistem
sesuai desain yang telah ditetapkan. Setelah implementasi selesai, dilakukan
pengujian sebagai validasi awal untuk memastikan fitur berjalan sesuai
rancangan, memenuhi kebutuhan yang ditetapkan, dan tidak menimbulkan
gangguan pada modul lain. Temuan pada tahap pengujian kemudian
dirangkum dalam evaluasi untuk menentukan perbaikan yang diperlukan
serta menyusun rencana iterasi berikutnya. Siklus ini berulang sampai
seluruh modul terintegrasi dan sistem siap memasuki pengujian menyeluruh
pada Bab V.
4. Interpretasi Hasil dan Dokumentasi
Tahap ini merangkum hasil pengujian dan evaluasi sistem, termasuk
temuan, keterbatasan, serta rekomendasi perbaikan/pengembangan. Selain
```
itu, dilakukan penyusunan dokumentasi sistem (desain, implementasi, dan
```
```
hasil uji) sebagai bagian dari laporan tugas akhir.
```
1.7 Sistematika Penulisan
Sistematika penulisan tugas akhir terdiri atas enam bab dengan susunan sebagai
berikut.
BAB I PENDAHULUAN
Bab ini menjelaskan latar belakang, rumusan masalah, tujuan penelitian, batasan
masalah, manfaat penelitian, metodologi penelitian, serta sistematika penulisan.
Pada bab ini juga disajikan konteks sistem sebagai bagian dari platform operasional
kafe Qash, dengan fokus pembahasan penelitian dibatasi pada alur pemrosesan
pesanan terintegrasi POS–KDS–Waiter dan dukungan informasi pelanggan.
8
BAB II TINJAUAN PUSTAKA
Bab ini membahas kajian penelitian terdahulu dan landasan teori yang digunakan,
```
meliputi konsep sistem informasi operasional kafe, modul point of sales (POS),
```
```
modul kitchen display system (KDS), modul waiter, konsep multi-tenant, serta
```
```
teori/metode pendukung seperti arsitektur aplikasi web (MVC), basis data
```
```
relasional, dan metode pengujian (black box dan user acceptance test).
```
BAB III PERANCANGAN SISTEM
Bab ini berisi proses perancangan sistem sesuai ruang lingkup penelitian.
Pembahasan mencakup analisis kebutuhan fungsional dan non-fungsional,
pemodelan proses dan alur POS–KDS–Waiter hingga customer check/track order,
```
perancangan struktur sistem (ERD, diagram kelas, relasi antar entitas, dan deskripsi
```
```
entitas inti transaksi), perancangan arsitektur aplikasi (MVC dan komponen
```
```
interaktif), perancangan antarmuka modul POS, KDS, Waiter Orders, Waiter Take
```
Order, serta rancangan pengujian black box dan UAT. Bab ini juga memuat
```
dukungan teknologi dan lingkungan pengembangan (MySQL, TablePlus sebagai
```
alat inspeksi basis data, dan Docker sebagai standarisasi lingkungan
```
pengembangan/pengujian).
```
BAB IV IMPLEMENTASI SISTEM
Bab ini menyajikan hasil implementasi sistem dan pembahasan mencakup
gambaran umum lingkungan implementasi dan konfigurasi
pengembangan/pengujian, implementasi basis data, implementasi modul
```
pendukung yang diperlukan untuk menjalankan alur utama (misalnya konteks
```
```
tenant, RBAC, data master pemesanan, pengaturan meja, diskon/pajak),
```
implementasi modul inti POS, KDS, Waiter Orders, Waiter Take Order, serta
implementasi fitur customer check/track order. Bab ini juga menjelaskan integrasi
dan sinkronisasi status pesanan antarmodul POS–KDS–Waiter–Customer.
BAB V PENGUJIAN DAN EVALUASI
Bab ini membahas metode pengujian dan hasil pengujian sistem. Pengujian
```
meliputi pengujian fungsional (black box) pada modul POS, KDS, Waiter Orders,
```
Waiter Take Order, dan Customer Check Order, serta pengujian penerimaan
```
pengguna (UAT) pada peran kasir, staf dapur, staf pelayan, dan pelanggan. Bab ini
```
9
juga memuat evaluasi hasil pengujian, temuan, keterbatasan sistem, dan implikasi
pengembangan lanjutan.
BAB VI PENUTUP
Bab ini berisi kesimpulan berdasarkan tujuan penelitian serta hasil perancangan,
implementasi, dan pengujian. Selain itu, disajikan saran pengembangan untuk
penyempurnaan sistem pada penelitian atau implementasi berikutnya.
10
BAB II
TINJAUAN PUSTAKA
2.1 Kajian Penelitian
Beberapa penelitian terkait pengembangan sistem informasi berbasis web,
```
sistem point of sales (POS), serta manajemen operasional restoran/kafe telah
```
dilakukan sebelumnya. Penelitian-penelitian tersebut menjadi acuan untuk
menyusun kebutuhan, merancang arsitektur, serta menentukan pendekatan
implementasi pada tugas akhir ini, khususnya pada integrasi modul POS–KDS–
```
Waiter dan dukungan informasi bagi pelanggan (customer check/track order).
```
Subbab ini merangkum penelitian terdahulu yang relevan, menguraikan kontribusi
dan keterbatasannya, serta menegaskan posisi kontribusi tugas akhir ini.
Garbarz dan Plechawska-Wójcik [4] melakukan analisis komparatif terhadap
kerangka kerja PHP Laravel dan Symfony untuk menilai karakteristik
pengembangan aplikasi web. Temuan penelitian tersebut digunakan sebagai
landasan argumentatif dalam pemilihan Laravel sebagai kerangka kerja utama pada
tugas akhir ini. Meskipun demikian, penelitian tersebut tidak membahas penerapan
sistem operasional restoran/kafe secara terintegrasi seperti alur pesanan dari POS
menuju dapur dan dilanjutkan ke pelayan, sehingga diperlukan rancangan yang
lebih kontekstual terhadap kebutuhan pemrosesan pesanan.
Susila [5] mengembangkan sistem POS berbasis web untuk membantu
Restoran Bakmi Djowo dalam melakukan transaksi penjualan serta pengelolaan
stok menu. Sistem yang dibangun menyediakan otomatisasi laporan stok dan
laporan transaksi pada rentang waktu tertentu. Penelitian ini menunjukkan bahwa
Laravel efektif digunakan untuk membangun aplikasi POS yang mendukung
transaksi dan manajemen stok. Namun, cakupan penelitian masih berfokus pada
POS dan inventori, tanpa menguraikan integrasi alur pesanan dengan modul dapur
```
(misalnya KDS) serta antarmuka khusus pelayan untuk memantau status pesanan
```
siap disajikan.
11
Astriyani, dkk [6] mengembangkan sistem informasi pemesanan makanan
berbasis web untuk mengatasi pencatatan pesanan manual menggunakan kertas
pada Naonaru’s Kitchen. Penelitian tersebut menerapkan metode pengembangan
RAD, menggunakan pemodelan UML, serta mengimplementasikan sistem dengan
PHP dan basis data MySQL. Hasilnya menunjukkan bahwa sistem pemesanan
berbasis web dapat meningkatkan efektivitas operasional, terutama dalam
mengurangi penumpukan nota pesanan dan membantu penyusunan laporan
pendapatan harian. Namun, penelitian ini belum menonjolkan pemisahan modul
```
kitchen display system (KDS) secara eksplisit, dan belum membahas antarmuka
```
pelayan sebagai penghubung proses produksi dapur dengan proses penyajian.
Khandwani, dkk [7] mengembangkan “Restaurant Management System”
sebagai solusi manajemen restoran untuk skala kecil dan menengah. Sistem tersebut
mendukung pemesanan, pengelolaan kategori menu, pengelolaan pesanan oleh
pelayan dan koki, serta penagihan otomatis. Penelitian ini menegaskan pentingnya
integrasi antara peran pelayan, dapur, dan sistem penagihan dalam satu platform
untuk mengurangi ketergantungan pada proses manual. Meski demikian, penelitian
tersebut masih membahas alur dapur dan waiter secara umum, tanpa menekankan
implementasi KDS sebagai tampilan khusus status pesanan secara real-time dan
tanpa fokus pada mekanisme sinkronisasi status pesanan lintas modul.
Berdasarkan keempat penelitian terdahulu, dapat disimpulkan bahwa
pengembangan sistem POS berbasis web, sistem pemesanan makanan, dan sistem
manajemen restoran telah banyak dilakukan, serta penggunaan Laravel sebagai
kerangka kerja pengembangan web telah terbukti secara praktis pada berbagai studi
kasus. Namun, masih terdapat ruang pengembangan untuk sistem yang secara
spesifik menekankan integrasi alur pemrosesan pesanan dari POS → KDS →
Waiter dalam satu platform. Oleh karena itu, tugas akhir ini berupaya mengisi celah
tersebut dengan merancang dan mengimplementasikan modul POS, modul KDS,
modul waiter, serta dukungan customer check/track order yang terintegrasi,
sehingga alur pesanan dari pencatatan hingga penyajian dapat berjalan lebih
terstruktur dan mendukung pemantauan kinerja operasional dapur.
12
Tabel 2.1 Tabel kajian peneliti terdahulu.
No Peneliti, Tahun, Judul Tujuan Hasil
1
P. Garbarz dan M.
Plechawska-Wójcik.
```
(2022). Comparative
```
analysis of PHP
frameworks on the
example of Laravel and
Symfony.
Analisis komparatif
kerangka kerja PHP
pada Laravel dan
Symfony.
Pemilihan Laravel
sebagai kerangka kerja
utama yang paling baik
untuk digunakan.
2
A. Susila. (2023).
Aplikasi Point Of Sales
```
(POS) Berbasis Website
```
Dengan Menggunakan
```
Laravel (Studi Kasus:
```
```
Bakmi Djowo).
```
Membuat sistem
dalam bentuk aplikasi
yang mempermudah
Restoran Bakmi
Djowo dalam
melakukan transaksi
serta mengelola bisnis.
Dengan adanya sistem
aplikasi Point of Sales,
pemilik Restoran
Bakmi Djowo dapat
dengan mudah
mengelola transaksi
serta stok menu yang
ada.
3
Astriyani, E., Rahmani,
A., Elvina, A., &
```
Noviantika, F. (2025).
```
Web-Based Food
Ordering Information
System at Naonaru’s
Kitchen.
Mengembangkan
sistem informasi
pemesanan makanan
berbasis web untuk
membantu kasir dan
staf dapur dalam
mengelola pesanan
yang sebelumnya
dicatat secara manual
menggunakan kertas.
Sistem yang dihasilkan
membantu kasir
mengelola pesanan
makanan dan
minuman, mengurangi
penumpukan nota
kertas, serta
mempermudah
penyusunan laporan
pendapatan harian.
4
Khandwani, M. F., Lanke,
P., Harne, P., Sapkal, A.,
```
& Adhao, A. (2023).
```
Restaurant Management
System.
Mengembangkan
sistem manajemen
restoran berbasis
aplikasi yang
mendukung
pemesanan,
pengelolaan kategori
menu, pengelolaan
pesanan oleh waiter
dan chef, serta
penagihan otomatis
sebagai solusi POS
untuk restoran.
Sistem yang dibangun
mampu berfungsi
dengan modul
pemesanan di mana
waiter mengirim
pesanan ke chef dan
tagihan dapat
dihasilkan secara
otomatis, sehingga
mengurangi
ketergantungan pada
cara manual.
13
2.2 Landasan Teori
```
2.2.1 Point of Sales (POS)
```
```
Point of sales (POS) merupakan titik terjadinya transaksi penjualan antara
```
pelanggan dan penjual. Pada titik ini penjual menghitung total harga yang harus
dibayar, menerapkan pajak atau diskon jika ada, memproses pembayaran, dan
memberikan bukti transaksi kepada pelanggan. Sistem POS modern tidak hanya
mencakup perangkat kasir, tetapi juga perangkat lunak yang mengelola transaksi,
data produk, stok, dan laporan penjualan terintegrasi [8].
Dalam konteks operasional kafe, POS berperan sebagai pintu masuk utama
data pesanan. Pesanan yang dicatat pada POS menjadi dasar bagi proses berikutnya
di dapur dan pada saat penyajian oleh pelayan. Oleh karena itu, rancangan modul
POS harus memperhatikan kemudahan penggunaan, kecepatan pencatatan pesanan,
serta kemampuan untuk mengirimkan data pesanan ke modul lain secara konsisten
dan real-time.
```
2.2.2 Kitchen Display System (KDS)
```
```
Kitchen display system (KDS) adalah sistem berbasis layar yang digunakan
```
di dapur untuk menampilkan daftar pesanan yang harus diproses. KDS
menggantikan penggunaan nota kertas dan mempermudah staf dapur dalam
memantau antrean pesanan, mengubah status pesanan, serta mengatur prioritas
pekerjaan [9]. Dalam konteks operasional kafe, KDS berperan sebagai pusat kendali
pekerjaan dapur setelah pesanan dicatat melalui POS. Data pesanan yang masuk ke
KDS menjadi acuan utama bagi staf dapur untuk mulai menyiapkan menu,
memantau urutan antrean, dan memastikan tidak ada item yang terlewat. Karena
KDS berada di titik kritis antara pemesanan dan penyajian, rancangan modul KDS
harus menekankan keterbacaan informasi pesanan, kemudahan pembaruan status
```
(misalnya confirmed–preparing–ready), serta kemampuan sinkronisasi status ke
```
modul lain secara konsisten dan real-time agar pelayan dapat segera mengetahui
pesanan yang sudah siap disajikan.
14
2.2.3 Waiter Orders
Waiter Orders merupakan fitur atau modul dalam sistem operasional kafe
yang berfungsi untuk menampilkan daftar pesanan yang sedang diproses di dapur
maupun pesanan yang telah selesai disiapkan dan siap untuk diantarkan kepada
```
pelanggan. Modul ini menjadi penghubung antara kitchen display system (KDS)
```
dan proses penyajian pesanan di meja pelanggan. Keberadaan sistem ini penting
karena membantu waiter mengetahui dengan cepat pesanan mana yang memiliki
prioritas layanan, tanpa perlu menunggu instruksi langsung dari dapur atau
melakukan pengecekan manual.
Integrasi antara KDS dan Waiter Orders menjadi elemen penting dalam
menjaga kelancaran alur operasional kafe. Ketika staf dapur memperbarui status
pesanan pada KDS, perubahan tersebut secara otomatis ditampilkan pada modul
Waiter Orders sehingga pelayan dapat segera menindaklanjuti pesanan tersebut.
Dengan demikian, modul Waiter Orders berperan sebagai komponen yang
memastikan bahwa penyajian pesanan berlangsung tepat waktu, terkoordinasi, dan
konsisten dengan proses pemrosesan pesanan di dapur [10].
2.2.4 Metode Pengembangan Iteratif
Metode pengembangan iteratif merupakan bentuk pengembangan dari
kekurangan dan masalah yang ditemukan dalam metode waterfall [11]. Ide dasar
dari metode pengembangan iteratif adalah untuk mengembangkan suatu sistem
```
secara iteratif (siklus berulang) dan inkremental (sedikit demi sedikit). Dengan
```
pengembangan secara iteratif dan inkremental ini, pengembang dapat
memanfaatkan pembelajaran yang diperoleh dari iterasi yang sudah dilakukan.
Artinya, setiap versi sebelumnya bukan sekadar tahap sementara, tetapi menjadi
sumber informasi mengenai apa yang sudah berjalan baik, bagian mana yang masih
kurang, serta kebutuhan pengguna yang mungkin baru terlihat setelah sistem
dicoba. Temuan dari pengalaman pengembangan dan penggunaan versi awal
tersebut kemudian dipakai untuk memperbaiki keputusan desain, menyesuaikan
prioritas kebutuhan, dan menambah fungsionalitas pada iterasi berikutnya secara
lebih tepat [12].
15
Gambar 2.1 Model pengembangan iteratif
Pada Gambar 2.1 ditunjukkan kerangka kerja pengembangan iteratif, yang
diawali dengan initial planning sebagai tahap penetapan tujuan pengembangan,
ruang lingkup awal, serta prioritas kebutuhan. Setelah itu, pengembangan
berlangsung dalam siklus iterasi yang berulang. Setiap iterasi dimulai dari tahap
planning untuk memetakan kebutuhan dan menentukan prioritas fitur atau modul
yang akan dibangun, kemudian dilanjutkan ke requirements untuk merinci
kebutuhan menjadi alur proses, struktur data, serta rancangan antarmuka. Tahap
berikutnya adalah analysis dan design untuk memvalidasi rancangan secara
konseptual dan menyiapkan solusi teknis, yang kemudian diwujudkan pada tahap
implementation melalui pembangunan fitur pada sistem. Setelah implementasi
selesai, dilakukan testing sebagai validasi awal untuk memastikan fitur bekerja
sesuai rancangan. Hasil testing kemudian masuk ke tahap evaluation, yaitu
penilaian terhadap hasil iterasi dan pengumpulan umpan balik dari pengguna atau
pemangku kepentingan sebagai dasar perbaikan, sehingga siklus kembali lagi ke
planning untuk iterasi berikutnya.
Dalam penelitian ini, batasan proses difokuskan pada aktivitas hingga
evaluasi di lingkungan pengembangan. Tahap deployment sebagai rilis produksi
tidak termasuk ruang lingkup penelitian. Namun, secara konseptual, ketika hasil
iterasi telah dinilai stabil dan memenuhi kebutuhan, keluaran setelah tahap
implementation dapat dilanjutkan ke deployment. Dengan demikian, keluaran tiap
iterasi pada penelitian ini digunakan untuk memperoleh umpan balik dan
penyempurnaan berulang sampai sistem stabil, sebelum memasuki pengujian yang
lebih menyeluruh pada Bab V.
16
Dalam konteks pengembangan sistem manajemen operasional kafe,
pendekatan iteratif memungkinkan modul POS, KDS, dan Waiter dikembangkan
```
bertahap mulai dari fungsi inti (misalnya pencatatan pesanan, penampilan daftar
```
```
pesanan di dapur, dan pembaruan status pesanan), kemudian disempurnakan
```
berdasarkan hasil validasi awal dan masukan pengguna pada iterasi berikutnya.
Pendekatan ini membantu menekan risiko perubahan besar di tahap akhir dan
meningkatkan kesesuaian sistem dengan kebutuhan operasional.
```
2.2.5 User Acceptance Test (UAT)
```
User acceptance testing atau UAT adalah tahap terakhir dan paling penting
dalam siklus pengembangan perangkat lunak.[13] Pada fase ini, pengguna akhir
sebenarnya menguji perangkat lunak dalam kondisi dunia nyata untuk
memverifikasi bahwa perangkat lunak tersebut memenuhi semua persyaratan bisnis
sebelum diluncurkan. Tidak seperti pengujian sistem atau integrasi, UAT berfokus
pada validasi alur bisnis ujung-ke-ujung, memastikan perangkat lunak tidak hanya
berfungsi, tetapi juga berfungsi untuk orang-orang, proses, dan tujuan.
Dalam penelitian ini, UAT dilakukan menggunakan kuesioner dengan skala
Likert 1–5, di mana responden memberikan penilaian terhadap beberapa pernyataan
yang mewakili aspek penerimaan sistem. Skor yang diperoleh kemudian dihitung
untuk mendapatkan nilai total, rata-rata, dan persentase penerimaan. Perhitungan
ini digunakan untuk merangkum tingkat penerimaan pengguna terhadap modul-
modul sistem yang diuji.
Misalkan terdapat 𝐾 pernyataan UAT pada satu modul dan setiap
```
pernyataan memiliki skor 𝑠! dengan rentang 1 sampai 𝑠"#$ (pada skala Likert 1-5,
```
```
𝑠"#$ = 5).
```
Maka, total skor UAT dinyatakan sebagai:
𝑆 = ' 𝑠!
%
!&'
```
, (2.1)
```
dimana 𝑆 merupakan skor UAT dan 𝑠! merupakan skor untuk pernyataan ke-𝑘.
Setelah memperoleh total skor 𝑆, langkah berikutnya adalah menghitung rata-rata
skor per pernyataan. Rata-rata ini digunakan agar hasil tidak bergantung pada
17
banyaknya pernyataan. Dengan rata-rata, perbandingan antar modul menjadi lebih
adil karena berada pada skala yang sama. Rata-rata skor UAT dihitung dengan:
𝑅 =
𝑆
```
𝐾 , (2.2)
```
dimana 𝑅 merupakan rata-rata skor UAT, 𝑆 merupakan total skor UAT, dan 𝐾
merupakan jumlah pernyataan. Agar lebih mudah diinterpretasikan, nilai rata-rata
𝑅 dinormalisasi ke bentuk persentase dengan membandingkannya terhadap skor
maksimum skala Likert, yaitu 𝑠"#$. Hasil normalisasi ini menghasilkan persentase
penerimaan dari 0 – 100%. Persentase penerimaan dapat dihitung dengan:
𝑃 =
𝑅
```
𝑠"#$× 100% , (2.3)
```
dimana 𝑃 merupakan tingkat penerimaan pengguna dalam bentuk persentase.
Semakin besar nilai 𝑃, maka semakin tinggi tingkat penerimaan pengguna terhadap
sistem pada modul yang diuji.
2.2.6 Laravel
Laravel adalah kerangka kerja aplikasi web berbasis PHP yang bersifat
terbuka dan banyak digunakan dalam pengembangan aplikasi web modern. Laravel
dirancang untuk mendukung pengembangan aplikasi berbasis pola arsitektur model
```
view controller (MVC). Kerangka kerja ini menyediakan beragam fitur seperti
```
sistem routing, manajemen basis data melalui migrasi, sistem autentikasi bawaan,
dan dukungan middleware [14].
Keunggulan Laravel antara lain sintaks yang ekspresif dan mudah dibaca,
struktur kode yang terorganisasi, serta ekosistem yang lengkap. Pada tugas akhir
ini, Laravel digunakan sebagai kerangka utama untuk membangun sistem
manajemen operasional kafe karena mendukung integrasi antara logika bisnis, basis
data, dan antarmuka pengguna secara terstruktur.
```
2.2.7 Model View Controller (MVC)
```
```
Model view controller (MVC) merupakan pola arsitektur perangkat lunak
```
yang memisahkan aplikasi menjadi tiga komponen utama, yaitu model, view, dan
controller [15]. Model merepresentasikan data dan logika bisnis aplikasi, termasuk
aturan bisnis dan interaksi dengan basis data. View bertanggung jawab
18
menampilkan antarmuka pengguna, sedangkan controller bertugas mengatur alur
aplikasi dengan menerima permintaan dari pengguna, memanggil fungsi pada
model, dan menentukan tampilan yang akan dikembalikan kepada pengguna.
Penerapan pola MVC pada pengembangan sistem manajemen operasional
kafe membantu menjaga struktur aplikasi agar tetap terpisah antara logika bisnis
dan tampilan. Hal ini memudahkan proses pemeliharaan maupun pengembangan
fitur tambahan di kemudian hari.
2.2.8 Eloquent ORM
Eloquent ORM adalah sistem object relational mapping bawaan Laravel
yang memungkinkan pengembang berinteraksi dengan basis data menggunakan
pendekatan berorientasi objek. Setiap tabel dalam basis data direpresentasikan
sebagai kelas model Eloquent, dan setiap baris pada tabel dipetakan menjadi objek
dari kelas tersebut [16].
Eloquent mendukung pendefinisian relasi antar tabel, seperti relasi satu ke
satu, satu ke banyak, dan banyak ke banyak, melalui metode yang deklaratif. Selain
itu, Eloquent menyediakan antarmuka kueri builder yang ekspresif sehingga
pengembang dapat menulis kueri dalam bentuk pemanggilan metode berantai . Pada
tugas akhir ini, Laravel Eloquent digunakan untuk mengelola data pesanan, item
pesanan, meja, pengguna, dan entitas lain yang berkaitan dengan modul POS, KDS,
dan pelayan.
2.2.9 Livewire
Laravel Livewire adalah kerangka kerja yang memungkinkan
pengembangan antarmuka pengguna dinamis pada aplikasi Laravel tanpa perlu
menulis JavaScript secara eksplisit [17]. Livewire bekerja dengan mengirim
permintaan asinkron ke server setiap kali terjadi interaksi pada komponen
antarmuka. Server kemudian memproses logika menggunakan PHP dan
mengembalikan potongan HTML yang telah diperbarui. Potongan HTML tersebut
disisipkan kembali ke halaman tanpa perlu memuat ulang seluruh halaman.
Pendekatan ini memudahkan pengembang untuk membangun fitur
antarmuka yang interaktif, seperti pembaruan daftar pesanan, perubahan status
19
pesanan, maupun pencarian dinamis, dengan tetap menggunakan bahasa PHP dan
Blade [12]. Dalam tugas akhir ini, Livewire dimanfaatkan untuk membangun
antarmuka POS, KDS, dan modul waiter yang responsif terhadap perubahan data.
2.2.10 Filament Components
Filament adalah pustaka antarmuka pengguna yang dibangun di atas
teknologi Tailwind CSS, Alpine.js, Laravel, dan Livewire. Filament menyediakan
komponen antarmuka siap pakai, seperti tabel data, formulir, dan berbagai elemen
tampilan lain yang terintegrasi dengan model Eloquent [18]. Penggunaan komponen
ini membantu mempercepat pembuatan antarmuka administrasi serta menjaga
konsistensi tampilan.
Dalam tugas akhir ini, Filament tidak digunakan sebagai panel admin secara
penuh, tetapi dimanfaatkan sebagai kumpulan komponen antarmuka pendukung,
terutama untuk menampilkan data yang berkaitan dengan modul POS dan KDS.
Dengan menggunakan komponen yang sudah tersedia, pengembang dapat lebih
memfokuskan perhatian pada perancangan alur bisnis pemrosesan pesanan .
2.2.11 MySQL
MySQL adalah sistem manajemen basis data relasional yang digunakan
secara luas dalam berbagai aplikasi berbasis web. MySQL menggunakan bahasa
```
Structured kueri Language (SQL) untuk mendefinisikan, mengelola, dan
```
memanipulasi data [19]. Sistem ini dikenal stabil, cepat, dan mampu menangani
volume data yang besar sehingga cocok digunakan untuk aplikasi skala kecil
maupun menengah .
Pada penelitian ini, MySQL digunakan sebagai basis data utama untuk
menyimpan informasi pesanan, detail item pesanan, data meja, data pengguna, dan
data pendukung lain yang dibutuhkan oleh sistem.
2.2.12 PHP
```
PHP (Hypertext Preprocessor) adalah bahasa pemrograman skrip yang
```
banyak digunakan untuk pengembangan aplikasi web dinamis [20]. PHP dapat
disisipkan langsung ke dalam HTML dan memiliki dukungan luas terhadap
berbagai basis data. Dalam praktiknya, penggunaan PHP tanpa kerangka kerja
20
tertentu dapat menyebabkan struktur kode yang kurang teratur karena logika bisnis,
akses data, dan tampilan bercampur dalam satu berkas.
Munculnya berbagai kerangka kerja berbasis PHP, seperti Laravel,
membantu pengembang mengatasi masalah tersebut dengan menyediakan struktur
standar dan pola pengembangan yang lebih terarah. Hal ini mengurangi
pengulangan kode dan memudahkan pemeliharaan aplikasi. Laravel sebagai
kerangka kerja PHP yang digunakan dalam tugas akhir ini memanfaatkan
kemampuan PHP untuk membangun aplikasi web yang dinamis dan terstruktur.
2.2.13 Containerization
Containerization merupakan sebuah fitur dari Docker yang dapat
menyimpan sebuah aplikasi dan semua dependensinya ke dalam sebuah kontainer
melalui image. Image dapat didefinisikan sebagai template atau cetak biru yang
digunakan untuk membuat sebuah container [21]. Dengan mekanisme ini, perbedaan
lingkungan sistem operasi, versi pustaka, maupun konfigurasi server tidak lagi
menjadi masalah karena seluruh kebutuhan aplikasi sudah didefinisikan di dalam
image. Ketika image dijalankan, Docker akan membuat container sebagai instans
aktif dari image tersebut, sehingga aplikasi dapat dijalankan secara konsisten di
berbagai lingkungan.
2.2.14 Docker
Docker merupakan sebuah platform containerization yang digunakan untuk
mengemas, mendistribusikan, dan menjalankan aplikasi secara konsisten pada
berbagai lingkungan komputasi [22]. Docker memungkinkan pengembang untuk
membungkus aplikasi beserta seluruh dependensi, library, dan konfigurasi sistem
ke dalam sebuah unit terisolasi yang disebut container. Dengan pendekatan ini,
aplikasi dapat dijalankan dengan perilaku yang sama baik pada lingkungan
pengembangan, pengujian, maupun produksi.
Dalam pengembangan aplikasi web, Docker banyak digunakan untuk
memastikan konsistensi lingkungan kerja antar pengembang serta mempermudah
proses deployment. Dengan menggunakan Docker, permasalahan yang sering
muncul akibat perbedaan konfigurasi sistem dapat diminimalkan. Oleh karena itu,
21
Docker menjadi salah satu teknologi penting dalam praktik DevOps dan
pengembangan perangkat lunak modern.
2.2.15 Full-Stack
Pengembangan aplikasi web full-stack adalah pendekatan di mana
pengembang atau tim bertanggung jawab terhadap dua sisi utama aplikasi
sekaligus, yaitu sisi server dan sisi klien. Pada sisi server, pengembang mengelola
logika bisnis, pemrosesan permintaan, dan interaksi dengan basis data. Pada sisi
klien, pengembang merancang antarmuka yang digunakan oleh pengguna untuk
berinteraksi dengan sistem.
Dalam konteks tugas akhir ini, pengembangan sistem manajemen
operasional kafe dilakukan dengan pendekatan full-stack menggunakan Laravel
pada sisi server, serta Blade, Livewire, dan komponen Filament pada sisi klien.
Modul POS, KDS, dan Waiter dirancang dengan mempertimbangkan aliran data
end-to-end, mulai dari masukan pesanan pada POS, pemrosesan di dapur melalui
KDS, hingga penandaan pesanan sebagai telah disajikan oleh pelayan. Pendekatan
ini mendukung integrasi yang kuat antara lapisan logika bisnis dan antarmuka
pengguna, sehingga sistem lebih mudah dipelihara dan dikembangkan.
22
BAB III
PERANCANGAN SISTEM
3.1 Gambaran Umum dan Ruang Lingkup Perancangan
Bab ini membahas analisis kebutuhan dan perancangan sistem manajemen
operasional kafe berbasis web dengan fokus pada tiga modul utama, yaitu point of
```
sales (POS), kitchen display system (KDS), dan Waiter. Analisis kebutuhan
```
dilakukan untuk mengidentifikasi proses bisnis pemrosesan pesanan, aktor yang
terlibat, serta kebutuhan fungsional dan nonfungsional dari sistem. Hasil analisis
tersebut menjadi dasar penyusunan rancangan sistem yang meliputi diagram use
case, diagram alur proses, perancangan struktur sistem, perancangan basis data,
rancangan arsitektur aplikasi, rancangan antarmuka, serta pemilihan teknologi dan
lingkungan pengembangan.
Pada penelitian ini, platform Qash menyediakan berbagai fitur pendukung
seperti pengelolaan tenant, produk, inventori, dan meja yang tetap digunakan agar
konteks operasional sistem dapat dipahami secara utuh. Namun demikian,
pembahasan mendalam difokuskan pada alur pemrosesan pesanan POS–KDS–
Waiter serta dukungan customer check order sebagai fitur pendamping pelacakan
```
status pesanan. Modul lain seperti human resources management (HRM), absensi
```
karyawan, laporan tertentu, dan notifikasi internal hanya disebutkan sebagai bagian
dari ekosistem sistem tanpa dianalisis secara rinci, karena tidak termasuk ruang
lingkup utama penelitian.
3.2 Analisis Kebutuhan Sistem
Analisis kebutuhan dilakukan untuk memastikan sistem yang dirancang
selaras dengan alur kerja operasional kafe, khususnya pada titik-titik koordinasi
```
antar peran (kasir–dapur–pelayan) yang berpengaruh terhadap ketepatan pesanan
```
dan kecepatan layanan. Penggalian kebutuhan dilakukan melalui wawancara semi-
terstruktur dan observasi alur pemesanan untuk mengidentifikasi permasalahan dan
kebutuhan pada proses lintas peran tersebut. Hasil analisis kemudian dirumuskan
23
menjadi proses bisnis inti POS–KDS–Waiter dan diturunkan menjadi kebutuhan
fungsional serta nonfungsional sebagai acuan perancangan dan implementasi
sistem. Pada bagian ini, kebutuhan dipaparkan dengan menekankan keterkaitan
antara fungsi sistem dan peran pengguna, sehingga setiap modul memiliki batas
tanggung jawab yang jelas dan terdefinisi.
3.2.1 Wawancara Semi-Terstruktur
Pengumpulan kebutuhan sistem pada penelitian ini dilakukan melalui
wawancara semi-terstruktur. Metode ini dipilih karena memungkinkan peneliti
menggunakan pertanyaan kunci sebagai panduan, sekaligus tetap memberikan
fleksibilitas untuk menggali informasi lebih mendalam ketika responden
menyampaikan temuan yang relevan dengan alur pemesanan kafe. Pendekatan ini
sejalan dengan tujuan penelitian, yaitu mengidentifikasi permasalahan operasional
pemesanan serta merumuskan kebutuhan sistem yang tepat sasaran pada modul
```
point of sales (POS), kitchen display system (KDS), dan waiter.
```
Stakeholder utama dalam penelitian ini adalah rekan peneliti yang terlibat
langsung dalam operasional Kasumba Coffee di Bandung serta sedang
mengembangkan platform sistem dengan nama Qash. Stakeholder memiliki
pengalaman bekerja di lingkungan coffee shop dan memahami proses layanan
harian, termasuk dinamika operasional pada jam sibuk. Pengalaman tersebut
menjadi dasar dalam merumuskan hipotesis awal mengenai potensi permasalahan
yang sering terjadi, seperti ketidaksinkronan informasi pesanan antar karyawan,
keterlambatan penyampaian status pesanan, serta keterbatasan visibilitas progress
```
pesanan dari sudut pandang aktor yang berbeda (kasir, dapur, dan pelayan).
```
Berdasarkan pengalaman tersebut, stakeholder kemudian bekerja sama dengan
peneliti dalam merancang solusi berbasis sistem informasi yang mengintegrasikan
alur pemesanan hingga penyajian makanan.
Untuk memastikan bahwa kebutuhan sistem tidak hanya bersumber dari
asumsi stakeholder, wawancara juga dilakukan kepada aktor-aktor yang terlibat
langsung dalam proses layanan di Kasumba Coffee. Responden ditentukan secara
purposif, yaitu pihak yang memiliki peran langsung pada alur pemesanan, meliputi
pemilik kafe atau penanggung jawab operasional, kasir sebagai pengguna modul
24
POS, serta pelanggan sebagai pihak yang menerima dampak langsung dari kualitas
layanan. Wawancara ini bertujuan menangkap permasalahan yang sering luput dari
pencatatan sistem, seperti ketidaksesuaian catatan pesanan dengan kondisi aktual,
keterlambatan konfirmasi pesanan, serta hambatan komunikasi informasi dari kasir
ke dapur dan dari dapur ke pelayan.
Wawancara dilaksanakan melalui sesi tanya jawab dengan daftar
pertanyaan kunci yang disusun sebelumnya. Pertanyaan tersebut dijabarkan sebagai
berikut.
1. Bagaimana alur pemesanan berjalan dari customer hingga pesanan
disajikan?
2. Apa kendala yang paling sering terjadi ketika volume pesanan meningkat?
3. Informasi apa saja yang wajib tersedia agar pesanan tidak tertukar?
4. Bagaimana mekanisme konfirmasi dan pembayaran yang berjalan saat ini?
5. Bagaimana proses pemantauan status pesanan dilakukan oleh kasir dan
pelanggan?
6. Informasi apa yang dibutuhkan pelayan untuk memastikan pesanan yang
diantarkan sesuai dan tepat waktu?
Hasil wawancara menunjukkan bahwa permasalahan utama dalam
pemesanan di coffee shop tidak hanya berada pada tahap pencatatan transaksi, tetapi
juga pada kontinuitas informasi antar peran dan visibilitas status pesanan secara
real-time. Oleh karena itu, sistem Qash dirancang untuk mengintegrasikan alur
pemesanan secara menyeluruh, mulai dari pembuatan pesanan dan konfirmasi
pembayaran melalui POS, pemrosesan pesanan di dapur melalui KDS, hingga
pengantaran oleh pelayan. Selain itu, sistem menyediakan laman customer check
order sebagai fitur pendamping agar pelanggan dapat memantau status pesanan
yang sedang berjalan, sehingga proses penyajian dapat dilakukan berdasarkan
status yang konsisten dan terdokumentasi.
Berdasarkan temuan tersebut, kebutuhan fungsional dan nonfungsional
pada penelitian ini difokuskan pada modul yang memiliki dampak langsung
terhadap kecepatan dan ketepatan layanan, yaitu POS, KDS, dan Waiter.
Pembatasan ruang lingkup ini dilakukan agar pembahasan penelitian tetap terarah
25
sesuai dengan judul tugas akhir, tanpa menghilangkan konteks bahwa platform
Qash secara keseluruhan dapat memiliki modul lain di luar alur pemesanan inti.
3.2.2 Proses Bisnis Sistem
Proses bisnis yang didukung oleh sistem terbagi menjadi dua lingkup, yaitu
proses bisnis pada tingkat platform Qash dan proses bisnis pada tingkat tenant
```
(kafe). Pada tingkat platform Qash, administrator melakukan autentikasi dan
```
mengelola daftar tenant, termasuk menambahkan tenant baru serta memodifikasi
informasi tenant yang sudah ada. Pengelolaan tenant tersebut menjadi pondasi agar
setiap kafe dapat menggunakan sistem dalam konteks operasional yang terpisah
melalui mekanisme multi-tenant.
Pada tingkat tenant, pemilik atau manajer kafe masuk melalui halaman login
tenant dan mengelola operasional harian melalui backoffice. Proses bisnis yang
menjadi fokus penelitian adalah alur pemrosesan pesanan yang melibatkan modul
POS, KDS, dan Waiter. Melalui POS, kasir mencatat pesanan pelanggan,
melakukan perhitungan total termasuk pajak dan diskon, serta memproses
pembayaran. Setelah pesanan tercatat, pesanan diteruskan ke KDS agar staf dapur
dapat memproses dan memperbarui status pesanan hingga dinyatakan siap. Ketika
pesanan berstatus siap, sistem menampilkan informasi tersebut pada modul Waiter
sehingga pelayan dapat mengantarkan pesanan ke pelanggan dan menandai pesanan
sebagai tersaji.
Sebagai dukungan transparansi layanan, sistem menyediakan laman
customer check order yang memungkinkan pelanggan memantau status pesanan
berdasarkan nomor referensi atau identitas yang digunakan saat pemesanan.
Dengan demikian, proses bisnis tidak berhenti pada pencatatan transaksi, tetapi
mencakup kontinuitas informasi dari pembuatan pesanan, pemrosesan dapur,
hingga penyajian dan pelacakan status.
3.2.3 Kebutuhan Fungsional
Kebutuhan fungsional merupakan uraian mengenai fungsi atau layanan
yang harus disediakan oleh sistem agar dapat mendukung proses bisnis pemesanan
pada kafe. Kebutuhan ini disusun berdasarkan alur pemesanan yang melibatkan
26
kasir, dapur, dan pelayan, serta pemilik atau manajer kafe sebagai pengelola. Fungsi
yang dirumuskan menekankan integrasi status pesanan antarmodul, sehingga
perubahan status yang dilakukan pada satu modul dapat tercermin pada modul lain
secara konsisten.
Secara umum, kebutuhan fungsional mencakup autentikasi pengguna sesuai
```
peran, pengelolaan data prasyarat transaksi (produk dan meja), pembuatan pesanan
```
melalui POS atau waiter take order, pemrosesan pembayaran dan konfirmasi
transaksi, pemrosesan pesanan pada KDS, serta penyajian pesanan oleh pelayan.
Selain itu, kebutuhan pendamping disertakan untuk mendukung pelacakan status
oleh pelanggan melalui nomor referensi maupun identitas pelanggan. Rincian
kebutuhan fungsional berikut dirangkum pada Tabel 3.1 dengan keterangan SW
merupakan software, MK merupakan manajemen kafe, dan F merupakan
fungsional.
Tabel 3.1 Kebutuhan fungsional sistem.
No Kode Deskripsi KategoriPengguna Prioritas
1 SW-MK-F01
Pengguna dapat melakukan
login ke aplikasi
menggunakan username dan
password yang sesuai dengan
hak akses masing-masing.
Kasir, Kitchen,
Waiter Tinggi
2 SW-MK-F02
Kasir dapat membuat
pesanan baru pada modul
POS dengan memilih meja
dan produk beserta
jumlahnya.
Kasir Tinggi
3 SW-MK-F03
Kasir dapat melakukan aksi
CRUD, yaitu mengubah atau
membatalkan pesanan
selama transaksi belum
dibayarkan.
Kasir Tinggi
4 SW-MK-F04
Kasir dapat memproses
pembayaran pesanan,
menghitung total harga
termasuk pajak dan diskon,
serta menyelesaikan
transaksi.
Kasir Tinggi
27
```
Tabel 3.1 Kebutuhan fungsional sistem (lanjutan).
```
No Kode Deskripsi KategoriPengguna Prioritas
5 SW-MK-F05
Setelah pembayaran berhasil,
sistem menyimpan pesanan
dan membuat data order
beserta order item dengan
status awal CONFIRMED
dan QUEUED, dan status
dibuat untuk alur KDS.
Kasir, Kitchen,
Waiter Tinggi
6 SW-MK-F06
Sistem menampilkan daftar
pesanan berstatus QUEUED
pada KDS sehingga dapat
dilihat oleh staf dapur.
Kitchen Tinggi
7 SW-MK-F07
Staf dapur dapat memilih
pesanan pada KDS dan
memperbarui statusnya
menjadi PREPARING pada
saat pesanan mulai diproses.
Kitchen Tinggi
8 SW-MK-F08
Staf dapur dapat menandai
pesanan yang telah selesai
disiapkan dengan mengubah
status menjadi READY pada
KDS.
Kitchen Tinggi
9 SW-MK-F09
Perubahan status pesanan
dan pemuatan ulang pesanan
bersifat real-time dan
otomatis
Kitchen Tinggi
10 SW-MK-F10
Waiter dapat melakukan aksi
CRUD untuk setiap item
pada keranjang serta validasi
pesanan sebelum diteruskan
ke sistem.
Waiter Tinggi
11 SW-MK-F11
Sistem menampilkan
pesanan berstatus READY,
PREPARING, dan
CONFIRMED pada halaman
waiter orders sehingga dapat
dipantau oleh waiter.
Waiter Tinggi
12 SW-MK-F12
Waiter dapat memilih
pesanan yang akan
diantarkan kepada pelanggan
dan melihat informasi meja
tujuan.
Waiter Tinggi
28
```
Tabel 3.1 Kebutuhan fungsional sistem (lanjutan).
```
No Kode Deskripsi KategoriPengguna Prioritas
13 SW-MK-F13
Setelah pesanan selesai
diantarkan, waiter dapat
mengubah status pesanan
menjadi SERVED sehingga
pesanan tidak lagi muncul
sebagai pesanan yang belum
tersaji.
Waiter Tinggi
14 SW-MK-F14
Sistem melakukan validasi
untuk perubahan status
pesanan yang dilakukan oleh
waiter.
Sistem Tinggi
15 SW-MK-F15
Perubahan status pesanan
dan pemuatan ulang pesanan
bersifat real-time dan
otomatis
Waiter Tinggi
16 SW-MK-F16
Waiter dapat melakukan
pencarian order berdasarkan
no_references atau nama dan
email.
Waiter Tinggi
17 SW-MK-F17
Waiter dapat membuka
halaman waiter take order
dan menampilkan seluruh
item dalam database dan
dikategorikan berdasarkan
CATEGORIES.
Waiter Tinggi
18 SW-MK-F18
Waiter dapat menambahkan
pesanan untuk pelanggan
dengan status pesanan
ORDERBY waiter.
Waiter Tinggi
19 SW-MK-F19
Waiter dapat menambahkan
informasi data meja dan data
pelanggan.
Waiter Tinggi
20 SW-MK-F20
Sistem mencatat waktu
perubahan status pesanan
```
(NEW, IN_PROGRESS,
```
```
READY, SERVED) untuk
```
keperluan pemantauan durasi
pemrosesan pesanan.
Sistem Tinggi
29
```
Tabel 3.1 Kebutuhan fungsional sistem (lanjutan).
```
No Kode Deskripsi KategoriPengguna Prioritas
21 SW-MK-F21
Pengguna yang telah selesai
menggunakan sistem dapat
melakukan logout untuk
mengakhiri sesi penggunaan.
Kasir, Kitchen,
Waiter Tinggi
22 SW-MK-F22
Halaman customer
melakukan validasi data
sebelum menampilkan detail
pesanan.
Customer Sedang
23 SW-MK-F23
Customer dapat melihat
status pesanan melalui laman
customer check order dengan
memasukkan nomor
referensi pesanan atau
identitas yang digunakan saat
```
pemesanan (misalnya nama
```
```
dan email).
```
Customer Sedang
24 SW-MK-F24
Pelacakan status pesanan dan
status kesiapan pesanan
bersifat real-time dan
otomatis.
Customer Sedang
Agar tidak menimbulkan ambiguitas pada tahap implementasi, kebutuhan
fungsional juga menetapkan bahwa sistem harus mencatat perubahan status pesanan
beserta waktu terjadinya perubahan. Pencatatan ini penting untuk dua tujuan:
memastikan integrasi alur antarmodul berjalan sesuai urutan status, serta
menyediakan data yang dapat digunakan untuk evaluasi durasi pemrosesan
pesanan.
3.2.4 Kebutuhan Non-Fungsional
Kebutuhan nonfungsional merupakan persyaratan kualitas sistem yang
mendukung penggunaan sistem secara efektif. Pada penelitian ini, sistem
```
dikembangkan dan diuji pada lingkungan pengembangan (bukan klaim produksi).
```
Oleh karena itu, kebutuhan nonfungsional dirumuskan sebagai target desain dan
batasan verifikasi pada skenario pengujian. Pemenuhannya diverifikasi melalui
```
pengujian fungsional (black box), pemeriksaan kontrol akses berbasis peran, serta
```
observasi perilaku sistem pada lingkungan pengembangan berbasis Docker.
30
Kebutuhan nonfungsional mencakup ketersediaan layanan aplikasi selama
sesi uji, keandalan konsistensi status pesanan antarmodul, portabilitas akses lintas
perangkat dan peramban, waktu respons yang memadai untuk modul operasional,
integritas data transaksi melalui validasi dan pembatasan hak akses, serta aspek
keamanan melalui autentikasi dan otorisasi berbasis peran. Rincian kebutuhan non-
fungsional dirangkum pada Tabel 3.2 dengan SW merupakan software, MK
merupakan manajemen kafe, F merupakan fungsional, dan NF merupakan non-
fungsional.
Tabel 3.2 Kebutuhan non-fungsional.
No Kode Parameter Target Desain Cara Verifikasi
1 SW-MK-NF01 Availability
Sistem dapat dijalankan
secara konsisten pada
lingkungan
pengembangan selama
sesi uji coba sesuai jam
simulasi operasional
kafe, dengan layanan
aplikasi dan basis data
berjalan stabil melalui
Docker.
Menjalankan sistem
pada Docker dan
melakukan akses
modul POS, KDS,
dan waiter selama
```
sesi uji;
```
memastikan
layanan dapat
diakses tanpa
kegagalan yang
menghentikan
proses uji.
2 SW-MK-NF02 Reliability
Sistem menjaga
konsistensi data pesanan
antarmodul POS, KDS,
dan waiter melalui
validasi masukan dan
alur perubahan status
yang terkontrol
```
(CONFIRMED→
```
IN_PROGRESS→
```
READY→SERVED).
```
Uji black-box
skenario perubahan
```
status; pemeriksaan
```
bahwa daftar
pesanan pada tiap
modul berubah
sesuai status dan
tidak terjadi
loncatan status
yang tidak valid.
3 SW-MK-NF03 Portability
Sistem berbasis web
dapat diakses melalui
peramban modern pada
sistem operasi umum
```
(Windows, macOS,
```
```
Linux), serta dapat
```
dijalankan lintas
lingkungan
menggunakan container
Docker.
Akses sistem dari
minimal dua
```
peramban berbeda;
```
menjalankan
aplikasi melalui
Docker pada mesin
pengembangan.
31
```
Tabel 3.2 Kebutuhan non-fungsional (lanjutan).
```
No Kode Parameter Target Desain Cara Verifikasi
4 SW-MK-NF04ResponseTime
Operasi utama seperti
menampilkan daftar
pesanan pada KDS dan
waiter dirancang
responsif pada
lingkungan
pengembangan, dengan
optimasi kueri dan
paginasi data.
Observasi waktu
muat pada halaman
daftar pesanan KDS
dan waiter saat uji
```
coba; memastikan
```
tidak terjadi jeda
yang menghambat
alur kerja pada
skenario pengujian.
5 SW-MK-NF05Data Safety/Integrity
Data transaksi dan data
pesanan terlindungi dari
kehilangan akibat
kesalahan operasi
pengguna melalui
validasi, pembatasan hak
akses, serta mekanisme
penyimpanan transaksi
yang konsisten.
Uji input tidak
```
valid; uji peran
```
```
pengguna;
```
memastikan sistem
menolak operasi
yang tidak sesuai
dan data pesanan
tidak berubah tanpa
aksi yang sah.
6 SW-MK-NF06 Security
Sistem hanya dapat
diakses oleh pengguna
terautentikasi dan
menerapkan otorisasi
berbasis peran agar
modul POS, KDS, dan
waiter hanya dapat
diakses sesuai
kewenangan.
Uji login dan
```
logout; uji akses
```
halaman dengan
```
role berbeda;
```
memastikan
halaman/aksi
terbatas sesuai
peran.
3.3 Perancangan Proses dan Alur Sistem
Perancangan proses dan alur sistem bertujuan memetakan bagaimana aktor
berinteraksi dengan sistem dari awal hingga akhir layanan. Bagian ini menyajikan
```
dua sudut pandang perancangan, yaitu (1) pemetaan fungsi inti yang disediakan
```
```
sistem melalui use case diagram dan (2) pemetaan urutan langkah operasional
```
melalui diagram alur proses. Dengan demikian, pembaca memperoleh gambaran
yang jelas mengenai kapan sebuah pesanan dibuat, diproses, dinyatakan siap,
hingga ditandai tersaji dalam alur POS–KDS–Waiter.
32
3.3.1 Use Case Diagram
Use case diagram digunakan untuk memetakan interaksi antara aktor dan
```
fungsi (use case) yang disediakan sistem. Pada penelitian ini, use case diagram
```
disusun untuk menegaskan ruang lingkup utama tugas akhir, yaitu alur pemrosesan
pesanan yang terintegrasi mulai dari pencatatan pesanan dan pembayaran pada
```
modul point of sale (POS), pemrosesan pesanan pada kitchen display system (KDS),
```
hingga penyajian pesanan oleh pelayan. Sebagai fitur pendamping, sistem juga
menyediakan customer check order agar pelanggan dapat memantau status pesanan
berdasarkan nomor referensi atau identitas yang digunakan saat pemesanan.
Aktor yang terlibat dalam alur utama pemrosesan pesanan terdiri atas kasir,
dapur, pelayan, pemilik/manajer, dan pelanggan. Kasir berperan mencatat pesanan
```
dan menyelesaikan pembayaran; dapur memproses pesanan dan memperbarui
```
```
status produksi; pelayan memantau pesanan siap antar dan menandai pesanan
```
```
sebagai tersaji; pemilik/manajer mengelola data pendukung seperti produk dan
```
```
meja serta memantau ringkasan operasional; sedangkan pelanggan memantau status
```
pesanan melalui laman pelacakan. Untuk menjaga keamanan akses, seluruh
```
pengguna internal (kasir, dapur, pelayan, pemilik/manajer) mengakses sistem
```
melalui mekanisme autentikasi dan otorisasi berbasis peran.
Agar pembahasan lebih terarah, use case diagram dibagi menjadi dua, yaitu
use case diagram utama yang merepresentasikan proses bisnis inti pemesanan pada
```
tingkat tenant (operasional kafe) dan use case diagram opsional yang
```
```
menggambarkan konteks pengelolaan tenant pada platform Qash (multi-tenant).
```
Diagram opsional ditampilkan sebagai konteks platform dan tidak menjadi fokus
implementasi inti POS–KDS–Waiter pada penelitian ini.
Berdasarkan Gambar 3.1, alur pemrosesan pesanan dimulai ketika kasir
membuat pesanan dan menyelesaikan pembayaran sehingga order tercatat dan
masuk antrean produksi pada KDS. Kitchen kemudian memproses pesanan dengan
```
memperbarui status hingga dinyatakan siap (ready).
```
33
Gambar 3.1 Use case POS-KDS-Waiter-Customer.
34
Pesanan yang berstatus ready selanjutnya ditampilkan pada modul Waiter untuk
diantarkan ke meja pelanggan, lalu pelayan menandai pesanan sebagai tersaji
```
(served). Di sisi pelanggan, sistem menyediakan laman customer check order agar
```
pelanggan dapat memantau status pesanan yang sedang berjalan. Untuk
memudahkan pembacaan, ringkasan use case utama tiap aktor disajikan pada Tabel
3.3 yang merangkum masukan dan keluaran utama dari setiap interaksi.
Sebagai konteks platform, Gambar 3.2 menampilkan use case diagram
tenant management pada Qash. Diagram ini menggambarkan peran admin platform
dalam melakukan pengelolaan tenant agar masing-masing kafe dapat memiliki
lingkungan operasional yang terpisah. Namun demikian, pembahasan implementasi
detail terkait tenant management tidak menjadi ruang lingkup utama tugas akhir ini.
Gambar 3.2 Use case tenant management Qash.
3.3.2 Skenario Use Case
Subbab ini memaparkan skenario use case sebagai uraian langkah
operasional dari setiap fungsi utama yang telah divisualisasikan pada use case
diagram. Skenario disusun untuk menunjukkan urutan aksi aktor dan respons
sistem, termasuk kondisi kesalahan yang umum terjadi. Pembahasan difokuskan
```
pada modul inti pemrosesan pesanan di tingkat tenant (POS–KDS–Waiter) serta
```
fitur pendamping customer check order. Skenario untuk tenant management pada
platform Qash disajikan secara terbatas sebagai konteks multi-tenant dan tidak
dibahas hingga detail implementasi.
35
```
A) Use Case Modul Utama
```
Skenario use case modul utama disusun untuk menggambarkan secara rinci
bagaimana aktor berinteraksi dengan sistem pada proses inti pemesanan di
tingkat tenant. Setiap skenario menjelaskan tahapan operasional yang dilakukan
```
aktor beserta respons sistem, mulai dari kondisi awal (pre-condition), alur utama
```
```
(primary flow), kemungkinan kesalahan (error flow), hingga kondisi akhir (post-
```
```
condition). Penyajian skenario ini bertujuan untuk memastikan bahwa fungsi-
```
fungsi utama pada modul POS, KDS, dan Waiter dapat berjalan secara
terintegrasi dan konsisten sesuai kebutuhan operasional kafe. Tabel 3.3 hingga
Tabel 3.8 menampilkan use case mulai dari login hingga customer check order.
Tabel 3.3 Use case login.
Use Case
Id Number UC-01
Use Case
Name Login
Use Case
Description
Proses autentikasi pengguna internal untuk mengakses sistem
pada level tenant.
Primary
Actor Cashier, Kitchen, Waiter, Owner/Manager
Pre-
Condition
Pengguna memiliki akun yang valid dan berada pada halaman
login tenant.
Primary
Flow of
Event
User Action System Response
1. Membuka halaman login
tenant
2. Menampilkan form login
3. Mengisi email/username dan
password
4. Membuat sesi login dan
memuat dashboard sesuai
role.
36
```
Tabel 3.3 Use case login (lanjutan).
```
Error
Flow of
Events
5a. Kredensial salah / tidak
valid
5b Menampilkan pesan gagal
login dan tetap di halaman
login.
Post-
Condition
```
Pengguna masuk ke dashboard sesuai peran (role) dan
```
mendapatkan akses modul yang berwenang.
Tabel 3.4 Use case membuat pesanan.
Use Case
Id Number UC-02
Use Case
```
Name Membuat Pesanan (POS)
```
Use Case
Description
Cashier membuat pesanan pelanggan melalui POS dengan
memilih produk, jumlah, dan Data meja tersedia jika tipe
pesanan dine-in.
Primary
Actor Cashier
Secondary
Actor -
Pre-
Condition
```
Cashier sudah login; data produk tersedia; dan data meja
```
tersedia untuk dine-in
Primary
Flow of
Event
User Action System Response
1. Membuka halaman POS
2. Menampilkan katalog
produk dan keranjang
3. Memilih tipe pesanan
```
(dine-in/takeaway) dan (jika
```
```
dine-in) memilih meja
```
4. Menyimpan pilihan tipe
pesanan/meja
5. Memilih produk dan jumlah
37
```
Tabel 3.4 Use case membuat pesanan (lanjutan).
```
Primary
Flow of
Event
6. Menambahkan item ke
keranjang dan menghitung
subtotal
7. Meninjau ringkasan pesanan
8. Menampilkan ringkasan
item, subtotal, pajak/diskon
```
(jika ada)
```
9. Mengonfirmasi pembuatan
pesanan
10. Membuat data order dan
order_items
Error
Flow of
Events
10a. Keranjang kosong
10b. Menolak proses dan
menampilkan validasi “item
belum dipilih”
11a. Meja tidak tersedia / tidak
valid
11b. Menolak proses dan
meminta pilih meja lain
Post-
Condition Order terbentuk dengan status = waiting_for_payment
Tabel 3.5 Use case memproses pembayaran pesanan.
Use Case
Id Number UC-03
Use Case
```
Name Memproses Pembayaran Pesanan (Checkout)
```
Use Case
Description
Cashier melakukan checkout untuk menyelesaikan transaksi
dan mengkonfirmasi pembayaran.
Primary
Actor Cashier
38
```
Tabel 3.5 Use case memproses pembayaran pesanan (lanjutan).
```
Pre-
Condition Pesanan sudah dibuat dan siap dibayar.
Primary
Flow of
Event
User Action System Response
1. Membuka ringkasan
transaksi
2. Menampilkan total, pajak,
diskon, grand total
3. Memilih metode
pembayaran dan
mengonfirmasi
4. Memvalidasi data
pembayaran
5. Menyelesaikan pembayaran
6. Menandai payment_status
sesuai hasil pembayaran
7. Menyimpan transaksi
8. Menyimpan order final dan
menampilkan bukti transaksi
9. Mengirim pesanan ke KDS
10. Menampilkan pesanan
pada antrean KDS
Error Flow
of Events
11a. Pembayaran gagal
11b. Menampilkan notifikasi
gagal dan transaksi tidak
difinalisasi
12a. Pembayaran dibatalkan
12b. Mengembalikan ke
halaman transaksi tanpa
finalisasi
Post-
Condition
Status pembayaran diperbarui dan pesanan masuk antrean
KDS.
39
Tabel 3.6 Use case memproses pesanan.
Use Case
Id Number UC-04
Use Case
```
Name Memproses Pesanan (KDS)
```
Use Case
Description
Kitchen memilih pesanan yang masuk dan memperbarui status
selama proses produksi.
Primary
Actor Kitchen
Secondary
Actor -
Pre-
```
Condition Kitchen sudah login; ada pesanan masuk di antrean KDS.
```
Primary
Flow of
Event
User Action System Response
1.Membuka halaman KDS
2. Menampilkan daftar
pesanan masuk
3. Memilih pesanan untuk
diproses
4. Menampilkan detail item
pesanan
5. Mengubah status menjadi
preparing/in progress
6. Menyimpan perubahan
status dan waktu
7. Menyelesaikan pesanan
8. Mengubah status menjadi
ready dan menyimpan waktu
selesai
40
```
Tabel 3.6 Use case memproses pesanan (lanjutan).
```
Error
Flow of
Events
9a. Bahan habis / pesanan
tidak dapat dipenuhi
9b. Menandai pesanan
ditolak/dibatalkan sesuai
```
kebijakan sistem (opsional)
```
Post-
Condition
Pesanan berstatus ready dan muncul pada modul waiter untuk
proses penyajian.
Tabel 3.7 Use case menyajikan pesanan.
Use Case
Id Number UC-05
Use Case
Name Menyajikan Pesanan
Use Case
Description
Waiter melihat daftar pesanan ready dan menandai pesanan
sebagai served setelah disajikan.
Primary
Actor Waiter
Secondary
Actor -
Pre-
```
Condition Waiter sudah login; terdapat pesanan berstatus ready.
```
Primary
Flow of
Event
User Action System Response
1.Membuka halaman waiter
orders
2. Menampilkan daftar
pesanan ready
3. Memilih pesanan
4. Menampilkan detail meja
dan item
5. Menyajikan pesanan ke
pelanggan
41
```
Tabel 3.7 Use case menyajikan pesanan (lanjutan).
```
Primary
Flow of
Event
6. Menyimpan status served
pada item
7. Menekan aksi “mark
served”
8. Menyimpan status served
dan Order dan menyimpan
waktu penyajian
Error
Flow of
Events
9a. Pesanan sudah berubah
```
status (mis. dibatalkan)
```
9b. Menampilkan notifikasi
dan menyegarkan daftar
Post-
Condition
Status pesanan berubah menjadi served dan tidak tampil lagi di
daftar ready.
Tabel 3.8 Use case customer check order.
Use Case Id
Number UC-06
Use Case
Name Customer Check Order
Use Case
Description
Customer memeriksa status pesanan berdasarkan nomor
```
referensi atau identitas (nama/email).
```
Primary
Actor Customer
Secondary
Actor -
Pre-
Condition
Customer memiliki nomor referensi atau identitas yang
digunakan saat pemesanan.
42
```
Tabel 3.8 Use case customer check order (lanjutan).
```
Primary
Flow of
Event
User Action System Response
1.Membuka
halaman check
order
2. Menampilkan form pencarian
3. Mengisi nomor
referensi/nama-
email
4. Memvalidasi input
5. Menekan
tombol cari
6. Mengambil data order dan
menampilkan status terkini
Error Flow
of Events
7a. Data tidak
ditemukan
7b. Menampilkan pesan “pesanan tidak
ditemukan”
Post-
Condition
Status pesanan ditampilkan
```
(confirmed/preparing/ready/served, dll.).
```
```
B) Use Case Tenant Management (Qash)
```
Use case tenant management pada platform Qash menggambarkan fungsi
administratif pada tingkat platform yang bertujuan mengelola tenant kafe yang
menggunakan sistem. Skenario ini berfokus pada proses autentikasi admin
platform serta pengelolaan data tenant, seperti penambahan, pembaruan, dan
pengaturan status tenant. Pembahasan skenario tenant management disajikan
secara terbatas dan tidak difokuskan pada detail implementasi, karena berada di
luar ruang lingkup utama penelitian yang menitikberatkan pada alur pemrosesan
pesanan POS–KDS–Waiter.
43
Tabel 3.9 Use case login admin platform.
Use Case
Id Number UCQ-01
Use Case
Name Login Admin Platform
Use Case
Description
Admin platform melakukan autentikasi untuk mengakses
dashboard Qash.
Primary
Actor Admin Platform
Secondary
Actor -
Pre-
Condition Admin memiliki akun platform yang valid.
Primary
Flow of
Event
User Action System Response
1. Membuka halaman login
platform
2. Menampilkan form login
3. Mengisi kredensial
4. Memvalidasi kredensial
5. Menekan login
6. Menampilkan dashboard
platform
Error Flow
of Events
7a. Kredensial salah / tidak
valid
7b Menampilkan pesan gagal
login dan tetap di halaman
login.
Post-
Condition Admin masuk ke dashboard platform.
44
Tabel 3.10 Use case mengelola tenant.
Use Case
Id Number
UCQ-02
Use Case
Name
Mengelola Tenant
Use Case
Description
Admin Qash menambahkan tenant baru, memperbarui
informasi tenant, atau menonaktifkan tenant.
Primary
Actor
Admin Qash
Secondary
Actor
-
Pre-
Condition
Admin sudah login.
Primary
Flow of
Event
User Action System Response
1.Membuka menu
tenant management
2. Menampilkan daftar tenant
3. Memilih aksi
tambah/ubah/nonaktif
4. Menampilkan form sesuai aksi
5. Mengisi/mengubah
data tenant
6. Memvalidasi data
7. Menyimpan
8. Menyimpan perubahan dan
memperbarui daftar tenant
Error Flow
of Events
9a. Data tidak valid /
belum lengkap
9b. Menampilkan validasi dan
meminta perbaikan
Post-
Condition
Data tenant tersimpan/terbarui dan dapat digunakan untuk
operasional pada level tenant.
45
3.3.3 Diagram Alur Proses
```
Diagram alur proses (flowchart) digunakan untuk menggambarkan urutan
```
langkah yang dilakukan aktor ketika berinteraksi dengan sistem, mulai dari awal
proses hingga proses tersebut selesai. Pada tugas akhir ini, diagram alur disusun
untuk menekankan alur utama pemrosesan pesanan pada kafe, yang dimulai dari
```
pencatatan pesanan di kasir melalui modul point of sales (POS), pemrosesan di
```
```
dapur melalui kitchen display system (KDS), hingga pengantaran pesanan oleh
```
pelayan kepada pelanggan.
Selain alur utama tersebut, diagram alur juga ditampilkan untuk proses yang
bersifat pendukung, yaitu pengelolaan tenant pada tingkat platform Qash dan proses
akses backoffice pada tingkat tenant. Penyajian diagram alur membantu pembaca
memahami bagaimana arus data dan keputusan mengalir di dalam sistem sebelum
masuk ke tahap implementasi teknis pada bab berikutnya.
```
A) Diagram Alur Tenant Management pada Platform Qash (Konteks)
```
Modul tenant management pada platform Qash digunakan oleh administrator
untuk menambahkan dan mengelola tenant yang akan menggunakan sistem
manajemen kafe. Modul ini menjadi pintu awal sebelum setiap tenant dapat
mengakses backoffice masing-masing.
Diagram alur proses tenant management pada platform Qash ditunjukkan pada
Gambar 3.3. Berdasarkan Gambar 3.3, alur proses dimulai ketika administrator
Qash membuka halaman login Qash dan memasukkan alamat surel serta kata
sandi ke dalam formulir autentikasi. Sistem kemudian melakukan validasi
terhadap kredensial yang dimasukkan. Apabila kombinasi alamat surel dan kata
sandi tidak sesuai dengan data yang tersimpan, sistem menampilkan pesan
kesalahan dan mengarahkan pengguna kembali ke halaman login sehingga
administrator dapat mencoba kembali dengan data yang benar. Apabila
kredensial valid, sistem mengizinkan administrator masuk dan menampilkan
halaman dashboard Qash.
46
Gambar 3.3 Proses tenant management
Setelah berhasil masuk, administrator berada pada dashboard yang
menampilkan daftar tenant yang telah terdaftar pada sistem. Dari dashboard ini,
administrator dapat memilih beberapa tindakan. Jika administrator memilih
untuk menambahkan tenant baru, sistem menampilkan formulir pendaftaran
tenant yang berisi data yang harus diisi, seperti nama tenant, alamat, dan
informasi pendukung lain yang diperlukan untuk inisialisasi tenant. Setelah
formulir diisi, sistem melakukan validasi terhadap data tersebut. Apabila
terdapat data yang tidak lengkap atau tidak sesuai, sistem menampilkan pesan
kesalahan dan meminta administrator untuk memperbaiki isian formulir.
47
Apabila seluruh data valid, sistem menyimpan informasi tenant baru ke dalam
basis data dan menambahkan tenant tersebut ke daftar yang terlihat pada
dashboard.
Selain menambah tenant baru, administrator juga dapat memilih untuk
mengubah data tenant yang sudah ada. Ketika salah satu tenant dipilih, sistem
menampilkan detail tenant tersebut sehingga administrator dapat memperbarui
informasi yang diperlukan, misalnya mengubah nama, alamat, atau status tenant.
Setelah perubahan disimpan, sistem memperbarui data tenant di basis data dan
mengembalikan administrator ke tampilan daftar tenant dengan data yang telah
diperbarui. Pada akhir sesi, administrator dapat melakukan logout untuk
mengakhiri sesi autentikasi, dan sistem akan mengarahkan kembali ke halaman
login. Dengan demikian, seluruh proses pengelolaan tenant melalui modul
tenant management terselesaikan sesuai alur yang digambarkan pada Gambar
3.3.
```
B) Diagram Alur Point of Sales (POS)
```
```
Modul point of sales (POS) merupakan titik awal alur pemrosesan pesanan pada
```
sistem manajemen operasional kafe. Melalui POS, kasir mencatat pesanan
pelanggan, menghitung total harga serta pajak dan diskon, memproses
pembayaran, serta memastikan pesanan tersimpan di dalam sistem sehingga
```
dapat diteruskan ke dapur melalui kitchen display system (KDS). Dengan
```
demikian, POS berperan sebagai penghubung antara interaksi pelanggan di kasir
dengan proses operasional di dapur dan pelayan. Diagram alur proses point of
sales ditunjukkan pada Gambar 3.4.
Berdasarkan diagram tersebut, alur dimulai ketika kasir membuka halaman POS
pada backoffice tenant. Kasir memilih meja atau pelanggan, kemudian memilih
produk dan jumlah pesanan. Sistem menghitung total pembayaran secara
otomatis, termasuk subtotal, pajak, dan diskon apabila diterapkan. Selanjutnya,
kasir memeriksa apakah pesanan sudah benar. Jika masih perlu diperbaiki, kasir
dapat kembali ke tahap pemilihan produk. Apabila pesanansudah sesuai, kasir
melanjutkan ke tahap pemilihan metode pembayaran. Sistem kemudian
memvalidasi proses pembayaran. Jika pembayaran gagal, sistem menampilkan
48
pesan kesalahan dan kasir dapat melakukan perbaikan atau mengulang proses
pembayaran. Jika pembayaran berhasil, sistem menyimpan pesanan ke basis data
dengan status awal NEW dan mengirimkannya ke KDS agar dapat segera
diproses oleh dapur. Setelah pesanan tersimpan, sistem menampilkan ringkasan
transaksi atau struk kepada kasir. Dengan demikian, proses pemesanan pada
modul POS selesai dan pesanan memasuki tahap pemrosesan berikutnya di
KDS.
Gambar 3.4 Proses point of sale.
```
C) Diagram Alur Kitchen Display System (KDS)
```
```
Kitchen display sytem (KDS) digunakan oleh staf dapur untuk memantau dan
```
memproses pesanan yang masuk dari kasir melalui modul POS. KDS
menampilkan daftar pesanan secara real-time sehingga dapur dapat mengetahui
urutan dan detail item yang harus disiapkan. Diagram alur proses kitchen display
system ditunjukkan pada Gambar 3.5.
49
Gambar 3.5 Proses Kitchen Display System.
Proses dimulai ketika pesanan baru dari POS disimpan dengan status NEW.
Sistem menampilkan pesanan tersebut pada daftar antrean KDS. Staf dapur
kemudian memilih pesanan yang ingin diproses, dan sistem mengubah status
pesanan menjadi preparing. Pada tahap ini, staf dapur menyiapkan makanan atau
minuman sesuai pesanan. Setelah pesanan selesai disiapkan, staf dapur
menandai pesanan sebagai ready. Sistem kemudian menyimpan perubahan
status beserta waktu penyelesaian. Pesanan yang telah berstatus ready akan
muncul pada modul Waiter sebagai pesanan yang siap diantarkan kepada
pelanggan. Dengan demikian, KDS berfungsi sebagai penghubung antara kasir
dan pelayan melalui pembaruan status pesanan yang dilakukan oleh dapur.
```
D) Diagram Alur Pengantaran Pesanan oleh Pelayan
```
Modul Waiter digunakan untuk membantu staf dalam memantau pesanan yang
masuk dan yang sudah selesai diproses oleh dapur dan mengantarkannya kepada
pelanggan. Proses ini terhubung langsung dengan status pesanan yang diubah
```
oleh dapur melalui kitchen display system (KDS). Diagram alur proses
```
pengantaran oleh pelayan ditunjukkan pada Gambar 3.6. Alur dimulai ketika
suatu pesanan telah berstatus READY pada KDS. Status tersebut otomatis
muncul pada halaman pesanan siap diantar yang dapat diakses oleh waiter.
Sistem menampilkan daftar pesanan lengkap dengan nomor meja sehingga
waiter dapat menentukan pesanan mana yang akan diantarkan terlebih dahulu.
50
Gambar 3.6 Proses Waiter.
Pelayan memilih pesanan yang ingin diantar dan membawa pesanan tersebut ke
meja pelanggan. Setelah pesanan diterima pelanggan, pelayan menandai pesanan
sebagai SERVED. Sistem kemudian menyimpan status baru tersebut beserta
waktu penyajian. Jika tenant menerapkan pengelolaan meja, sistem juga dapat
memperbarui status meja terkait. Dengan demikian, modul Waiter berfungsi
sebagai tahap akhir dalam alur pemrosesan pesanan, memastikan bahwa pesanan
yang telah disiapkan di dapur dapat disajikan kepada pelanggan secara tepat dan
terkoordinasi.
```
E) Diagram Alur Waiter Take Order
```
Selain alur pemesanan melalui kasir, sistem juga mendukung pemesanan
langsung oleh pelanggan melalui pelayan. Oleh karena itu, diagram alur Waiter
Take Order ditampilkan untuk menggambarkan perbedaan titik masuk data
pesanan serta integrasinya dengan KDS. Diagram alur proses Waiter Take Order
ditunjukkan pada Gambar 3.7.
51
Gambar 3.7 Proses customer check order.
```
F) Diagram Alur Customer Check Order
```
Sebagai fitur pendamping, sistem menyediakan halaman customer check order
untuk memungkinkan pelanggan memantau status pesanan. Diagram alur berikut
menggambarkan proses pencarian dan penampilan status pesanan tanpa
memerlukan autentikasi pengguna. Diagram alur proses customer check order
ditunjukkan pada Gambar 3.8.
Gambar 3.8 Proses customer check order.
52
3.4 Perancangan Struktur Sistem
Perancangan struktur sistem menjelaskan komponen inti yang membentuk
```
sistem dari sisi struktur statis, yaitu kelas/objek domain, layanan (service layer),
```
serta penyimpanan data pada basis data. Bagian ini ditempatkan setelah
perancangan alur proses agar rancangan kelas dan basis data dapat diturunkan
secara logis dari kebutuhan serta interaksi aktor yang telah dijelaskan sebelumnya.
Dengan pendekatan ini, struktur sistem yang dirancang tidak berdiri sendiri, tetapi
memiliki keterkaitan langsung dengan proses pemesanan POS–KDS–Waiter dan
kebutuhan pelacakan status oleh customer.
3.4.1 Desain Basis Data
Desain basis data pada sistem manajemen operasional kafe ini
menggunakan model relasional dengan DBMS MySQL. Entitas yang dirancang
mencerminkan kebutuhan utama sistem, khususnya alur pemesanan mulai dari
pencatatan pesanan pada POS, pemrosesan di dapur melalui kitchen display system
```
(KDS), hingga pengantaran oleh pelayan. Selain itu, perancangan basis data juga
```
mempertimbangkan kebutuhan multi-tenant, sehingga data setiap kafe yang
menggunakan sistem tetap terisolasi.
```
Entity relationship diagram (ERD) digunakan untuk memvisualisasikan
```
struktur data dan relasi antar entitas yang mendukung alur pemesanan terintegrasi
POS–KDS–Waiter. ERD menggambarkan keterkaitan data mulai dari pengelolaan
menu, pembentukan pesanan, perubahan status pesanan di dapur, hingga pesanan
disajikan. Pada penelitian ini, ERD difokuskan pada entitas inti yang langsung
terlibat dalam alur POS–KDS–Waiter, sedangkan entitas pendukung seperti diskon
dan pajak ditampilkan sebagai pelengkap apabila digunakan dalam perhitungan
transaksi.
3.4.2 Entity Relationship Diagram
```
Entity relationship diagram (ERD) digunakan untuk memvisualisasikan
```
struktur data dan relasi antar entitas yang mendukung alur pemesanan terintegrasi
```
kasir (POS), dapur (KDS), dan pelayan. ERD memberikan gambaran keterkaitan
```
data mulai dari pengelolaan menu, pembentukan pesanan, pemrosesan status
53
pesanan di dapur, hingga pesanan disajikan kepada pelanggan. Pada implementasi
sistem, basis data dirancang menggunakan pendekatan multi-tenant untuk menjaga
isolasi data antar kafe. Namun, untuk keperluan keterbacaan diagram pada laporan
tugas akhir, tabel tenants beserta atribut tenant_id pada setiap entitas tidak
ditampilkan pada ERD. Penghilangan ini dilakukan agar ERD lebih ringkas dan
fokus pada relasi inti modul pemrosesan pesanan. Secara fungsional, mekanisme
multi-tenant tetap diterapkan pada implementasi sistem seperti pada Gambar 3.9.
Gambar 3.9 ERD sistem pemrosesan pesanan POS–KDS–Waiter.
54
3.4.3 Diagram Kelas
Diagram kelas digunakan untuk memodelkan struktur kelas pada sistem,
termasuk atribut, operasi utama, serta relasi antarkelas yang terlibat dalam
pemrosesan pesanan. Pada penelitian ini, class diagram difokuskan pada domain
pemesanan dan pemenuhan pesanan, sehingga menampilkan kelas-kelas model inti
seperti Order, OrderItem, Product, CustomerDetail, DiningTable, serta entitas
pendukung transaksi seperti Tax, OrderTax, dan Discount. Selain itu, diagram juga
memuat komponen layanan, seperti pembuatan pesanan, pemrosesan pembayaran,
dan pembaruan status pemenuhan pesanan.
Struktur tersebut dirancang untuk mendukung pemisahan tanggung jawab
antar komponen. Kelas model bertugas merepresentasikan data dan relasi,
sedangkan layanan menangani aturan bisnis agar perubahan status pesanan
berlangsung terkontrol dan konsisten antarmodul. Misalnya, pembuatan pesanan
dari POS atau pelayan diarahkan melalui layanan pembuatan pesanan sehingga
pembentukan Order dan OrderItem terjadi melalui mekanisme yang seragam.
Selanjutnya, pembaruan status pada KDS dan Waiter dikelola oleh layanan
pemenuhan pesanan agar sistem dapat menyegarkan status pada level order
berdasarkan status item yang dikerjakan. Pada sisi pelanggan, layanan kueri
digunakan untuk menyediakan akses pencarian pesanan berdasarkan nomor
referensi atau identitas yang diberikan pelanggan. Diagram kelas sistem
pemrosesan pesanan ditampilkan pada Gambar 3.10. Diagram ini menjadi jembatan
antara rancangan proses dan rancangan basis data, karena kelas-kelas model yang
ditampilkan kemudian dipetakan menjadi tabel dan relasi pada perancangan basis
data. Pada diagram tersebut tabel Order merupakan inti dari alur pemrosesan
pesanan dimulai dari pembuatan pesanan melalui aktor waiter maupun aktor
kitchen, setelah pesanan terbuat pesanan tersebut akan dilakukan hidrasi kembali
melalui OrderTax dan OrderItem untuk pengecekan total diskon pesanan dan pajak
sudah sesuai dengan konfigurasi sistem atau belum.
55
Gambar 3.10 Class diagram sistem pemrosesan pesanan POS–KDS–Waiter.
56
3.4.4 Relasi Antar Entitas
Relasi antar entitas menggambarkan hubungan antara tabel-tabel yang
digunakan dalam basis data. Tabel 3.11 menunjukkan relasi utama antar entitas
yang relevan dengan proses pemesanan, di mana 1 : M merupakan one-to-many dan
1:1 merupakan one-to-one.
Tabel 3.11 Tabel relasi antar entitas
No Nama Entitas Relasi Nama Entitas Relasi Atribut Relasi
1 tenants 1 : M floors tenant_id
2 tenants 1 : M dining_tables tenant_id
3 tenants 1 : M customer_details tenant_id
4 tenants 1 : M categories tenant_id
5 tenants 1 : M products tenant_id
6 tenants 1 : M discounts tenant_id
7 tenants 1 : M taxes tenant_id
8 tenants 1 : M orders tenant_id
9 floors 1 : M dining_tables floor_id
10 categories 1 : M products category_id
11 products 1 : M product_options product_id
12 product_options 1 : M product_option_values product_option_id
13 customer_details 1 : M orders customer_detail_id
14 orders 1 : M order_items order_id
15 products 1 : M order_items product_id
16 discounts 1 : M order_items discount_id
17 orders 1 : M order_taxes order_id
18 taxes 1 : M order_taxes tax_id
19 dining_tables 1 : M orders dining_table_id
20 tenants 1 : 1 tenant_profiles tenant_id
Pemodelan basis data pada sistem ini didominasi oleh relasi satu ke banyak
```
(1:M) karena sebagian besar entitas bersifat hierarkis dan berhubungan dengan data
```
transaksi. Pada sistem operasional kafe, satu pesanan dapat memiliki banyak item,
57
satu kategori dapat memiliki banyak produk, dan satu tenant dapat memiliki banyak
```
entitas operasional. Relasi satu ke satu (1:1) hanya digunakan ketika entitas tersebut
```
secara logis memiliki satu pasangan tetap, seperti relasi antara tenants dan
tenant_profiles. Hal ini sesuai dengan praktik terbaik pemodelan basis data untuk
aplikasi transaksi seperti POS dan KDS.
3.4.5 Deskripsi Entitas
Pada subbab ini, deskripsi entitas disajikan berdasarkan tingkat
keterkaitannya dengan alur pemesanan POS–KDS–Waiter. Entitas inti transaksi
```
(orders, order_items, dan entitas pendukung transaksi) dijelaskan terlebih dahulu
```
karena menjadi pusat alur pemrosesan pesanan. Selanjutnya, entitas utama yang
digunakan pada pembentukan pesanan, seperti produk, opsi produk, meja, serta data
pelanggan pendukung, dijelaskan sebagai prasyarat data. Terakhir, entitas
konfigurasi dan konteks platform, seperti pengaturan pajak, diskon, serta multi-
tenant, disajikan sebagai komponen pendukung agar pembaca dapat memahami
ruang lingkup penelitian secara utuh.
```
A) Entitas Inti Transaksi
```
Pada bagian ini, akan ditampilkan deskripsi tiap entitas, mulai dari entitas orders
hingga customer_details, yang disajikan pada Tabel 3.12 hingga Tabel 3.16.
Tabel 3.12 Deskripsi entitas orders.
Nama Atribut TipeData Atribut Keterangan
id bigint Primary Key -
tenant_id varchar Foreign Key Foreign Key ke tenants
customer_detail_id bigint Foreign Key Foreign Key kecustomer_details
```
total decimal(10,2) - Total nilai pesanan sebelumpajak dan diskon
```
```
subtotal decimal(12,2) default(0) Subtotal harga item setelahdiskon
```
58
```
Tabel 3.12 Deskripsi entitas orders (lanjutan).
```
Nama Atribut TipeData Atribut Keterangan
```
total_tax decimal(12,2) default(0) Total Pajak yang dikenakanpada pesanan.
```
```
grand_total decimal(12,2) default(0) Nilai akhir pesanan yangharus dibayar pelanggan.
```
status enum
['waiting_for
_payment',
'confirmed',
'preparing',
'ready',
'served',
'cancelled'].
```
default(‘waiti
```
ng_for_paym
```
ent)
```
```
(waiting_for_payment,
```
confirmed, preparing,
```
ready, served, cancelled)
```
source enum
[‘pos’, ‘qr’,
‘waiter’].
```
default(pos)
```
Sumber pesanan, misalnya
dari POS atau QR
order_type enum
[‘dine_in’,
‘takeaway’].
```
default(dine_i
```
```
n)
```
```
Jenis pesanan (dine-in atau
```
```
takeaway)
```
payment_status enum
['pending','pa
id', 'failed',
'cancelled'].
```
default(‘pendi
```
```
ng’)
```
Status pembayaran
```
(pending, paid, failed,
```
```
cancelled)
```
payment_channel varchar nullable Melihat saluran pembayaran
reference_no varchar unique Nomor referensi pesananatau pembayatan.
confirmed_at timestamp nullable Waktu pesanan dikonfirmasi
preparing_at timestamp nullable Waktu pesanan mulaidisiapkan di dapur.
59
```
Tabel 3.12 Deskripsi entitas orders (lanjutan).
```
Nama Atribut TipeData Atribut Keterangan
ready_at timestamp nullable Waktu pesanan dinyatakansiap disajikan.
expected_seconds_t
```
otal int default(0)
```
Perkiraan total waktu
penyelesaian pesanan
```
queue_seconds int default(0) Waktu antre sebelumdiproses dapur
```
```
prep_seconds int default(0) Lama waktu persiapan didapur.
```
```
total_seconds int default(0) Total durasi pesanan daridibuat hingga selesai.
```
paid_at timestamp nullable Waktu pembayaran berhasildilakukan.
dining_table_id bigint Foreign Key Foreign Key terhadapdining_tables
Tabel 3.13 Deskripsi entitas order_items.
Nama Atribut Tipe Data Atribut Keterangan
id bigint PrimaryKey -
tenant_id varchar ForeignKey Foreign Key ke tenants
order_id bigint ForeignKey Foreign Key ke orders
product_id bigint ForeignKey Foreign Key ke products
product_name varchar - Nama produk pada saattransaksi.
```
unit_price decimal(10,2) - Harga satuan produk.
```
```
final_price decimal(10,2) default(0) Harga akhir per itemsetelah diskon.
```
60
```
Tabel 3.13 Deskripsi entitas order_items (lanjutan).
```
Nama Atribut Tipe Data Atribut Keterangan
```
discount_amount decimal(10,2) default(0) Nilai diskon yangditerapkan pada item.
```
```
discount_id bigint nullable Foreign key ke discounts(jika spesifik)
```
quantity int - Jumlah item yang dipesan
estimate_seconds int nullable Estimasi waktu penyajianitem
options json nullable Pilihan opsi produk yangdipilih pelangan.
special_instructions text nullable Catatan khusus pelanggan
Tabel 3.14 Deskripsi entitas order_taxes.
Nama
Atribut Tipe Data Atribut Keterangan
id bigint Primary Key -
order_id bigint Foreign Key Foreign Key ke order
tax_id bigint Foreign Key Foreign Key ke tax
name varchar - Nama pajak yangditerapkan
```
type enum [‘percentage’,‘fixed’]Jenis pajak (percentage ataufixed)
```
```
rate decimal(10,2) - Tarif pajak.
```
```
amount decimal(12,2) - Nilai nominal pajak yangdibebankan.
```
61
Tabel 3.15 Deskripsi entitas cart_items.
Nama
Atribut Tipe Data Atribut Keterangan
id bigint PrimaryKey -
name varchar - -
```
price decimal(10,2) - Harga satuan item pada keranjang.
```
```
quantity int default(1) Jumlah item pada keranjang.
```
options json nullable Opsi produk yang dipilih
item_id varchar - Id produk yang ada dikeranjang
```
session_id varchar nullable Identitas sesi pengguna anonimus(tanpa login)
```
user_id bigint nullable Identitas pengguna jika keranjangdikaitkan dengan user tertentu.
Tabel 3.16 Deskripsi entitas customer_details.
Nama
Atribut
Tipe
Data Atribut Keterangan
id bigint PrimaryKey -
tenant_id varchar Foreign Key Foreign Key ke tenants
name varchar - Nama pelanggan.
email varchar - Alamat email pelanggan
gender enum
['male',
'female',
'na']
```
Jenis kelamin pelanggan (male, female,
```
```
na) bila diisi, not available bila tidak
```
ingin menyebutkan.
62
```
B) Entitas Utama yang Langsung dipakai oleh Transaksi
```
Pada bagian ini, akan ditampilkan deskripsi tiap entitas, mulai dari entitas
product hingga dining_tables, yang disajikan pada Tabel 3.17 hingga Tabel 3.22.
Tabel 3.17 Deskripsi entitas product.
Nama Atribut Tipe Data Atribut Keterangan
id bigint PrimaryKey -
tenant_id varchar Foreign Key Foreign Key ke tenants
category_id bigint ForeignKeyForeign Key kecategories
product_image varchar nullable Lokasi gambar produk
name varchar Nama Produk
alternate_name varchar nullable Nama alternatif produk
description varchar nullable Deskripsi singkat produk
```
price decimal(10,2) - Harga jual produk
```
```
goods_price decimal(10,2) - Harga pokok barang
```
estimated_seconds int nullable Estimasi waktu penyajianproduk dalam detik
```
featured tinyint(1) default(0)
```
Penanda apakah produk
termasuk produk
unggulan
```
active tinyint(1) default(true) Status produk
```
```
stock_qty int default(1) Jumlah stok produk yangtersedia.
```
63
Tabel 3.18 Deskripsi entitas category.
Nama
Atribut Tipe Data Atribut Keterangan
id bigint Primary Key -
tenant_id varchar Foreign Key Foreign Key ke tenants
image_url varchar nullable Lokasi gambar kategori
name varchar - Nama kategori
Tabel 3.19 Deskripsi entitas product_options.
Nama
Atribut
Tipe
Data Atribut Keterangan
id bigint Primary Key
tenant_id varchar Foreign Key ke tenants
product_id bigint Foreign Key ke products
name varchar Nama opsi, misalnya“Size”, “Sugar Level”
```
is_required tinyint(1)
```
Menandakan apakah
opsi wajib dipilih saat
pemesanan.
Tabel 3.20 Deskripsi entitas product_option_values.
Nama Atribut Tipe Data Atribut Keterangan
id bigint PrimaryKey -
tenant_id varchar ForeignKey Foreign Key ke tenants
product_option_id bigint ForeignKeyForeign Key keproduct_options
value varchar - Nilai opsi seperti “Small”,“Medium”, “large”
```
price_adjustment decimal(8,2) default(0) Penyesuaian harga opsi
```
64
Tabel 3.21 Deskripsi entitas floors.
Nama
Atribut Tipe Data Atribut Keterangan
id bigint PrimaryKey -
tenant_id varchar ForeignKey Foreign Key ke tenants
name varchar - Nama atau label lantai.
```
area_type int nullable String untuk penamaan tipe area(indoor/outdoor)
```
```
order unsignedInteger default(0) Untuk urutan floor
```
Tabel 3.22 Deskripsi entitas dining_tables.
Nama
Atribut
Tipe
Data Atribut Keterangan
id bigint Primary Key -
tenant_id varchar Foreign Key Foreign Key ketenants
floor_id bigint Foreign Key Foreign Key ke floors
label varchar -
Nama atau nomor
meja yang
ditampilkan sistem
status enum
['available', 'occupied',
'oncleaning', 'archived'].
```
default(‘available’)
```
Status meja
```
(available, occupied,
```
```
oncleaning, archived).
```
```
shape enum ['circle', 'rectangle'].default(‘rectangle’)Bentuk meja (circleatau rectangle).
```
```
x,y,h,w integer default(0,0,2,2)
```
Koordinat dan ukuran
meja pada layout
table plan
```
capacity int default(2) Kapasitas jumlahkursi di meja
```
65
```
Tabel 3.22 Deskripsi entitas dining_tables (lanjutan).
```
Nama
Atribut
Tipe
Data Atribut Keterangan
```
qr_code varchar unique(qr_code) Kode unik untuk akses pemesananberbasis QR
```
color string nullable Untuk memasukan string kode hex
```
C) Entitas Pendukung/Konteks Platform
```
Pada bagian ini, akan ditampilkan deskripsi tiap entitas, mulai dari entitas
tenants, taxes, serta discount, yang disajikan pada Tabel 3.13 hingga Tabel 3.25.
Tabel 3.23 Deskripsi entitas tenants.
Nama
Atribut
Tipe
Data Atribut Keterangan
id varchar Primarykey -
data json nullable Menyimpan konfigurasi tambahan tenantdalam bentuk json
Tabel 3.24 Deskripsi entitas taxes.
Nama
Atribut Tipe Data Atribut Keterangan
id bigint Primary Key -
tenant_id varchar Foreign Key Foreign Key ketenants
name varchar - Nama pajak
```
type enum [‘percentage’, ‘fixed’].default(‘percentage’)
```
Jenis pajak
```
(persentase atau
```
```
fixed)
```
```
rate decimal(10,2) default(0) Besaran tarif pajak
```
```
is_active tinyint(1) default(1)
```
Menandakan
apakah aktif atau
tidak
66
Tabel 3.25 Deskripsi entitas discounts.
Nama
Atribut Tipe Data Atribut Keterangan
id bigint Primary Key -
tenant_id varchar Foreign Key Foreign Key ketenants
name varchar - Nama program diskon
```
discount_type enum [‘flat’, ‘percent’] Jenis diskon (flat ataupercent)
```
```
value decimal(10,2) - Nilai diskon
```
applicable_for enum [‘all’, ‘specific’]
Menandakan diskon
berlaku untuk semua
produk atau produk
```
tertentu (all products
```
```
atau specific product)
```
products json nullable Daftar produk yangdikenai diskon
valid_from date - Tanggal mulai berlakudiskon
valid_till date - Tanggal berakhirnyadiskon
days json wajib diisi Hari-hari tertentuketika diskon berlaku
quantity_type enum
[‘unlimited’,
‘decrement’].
```
default(‘unlimited’)
```
Jenis kuota diskon
```
(unlimited atau
```
```
decrement)
```
status enum
[‘active’,
‘inactive’].
```
default(‘active’)
```
Status diskon
67
```
3.5 Perancangan Arsitektur Aplikasi (MVC & Komponen Interaktif)
```
Sistem ini ini dirancang dengan menerapkan arsitektur model view
```
controller (MVC) yang merupakan bagian dari kerangka kerja Laravel. Pendekatan
```
ini digunakan untuk memisahkan antara logika program, tampilan antarmuka, dan
pengelolaan data ke dalam komponen yang berbeda, sehingga struktur sistem
menjadi lebih jelas dan memudahkan proses pengembangan. Pada bagian ini, akan
dijelaskan daftar model yang digunakan oleh sistem, pengendalian proses, serta
view dari sistem.
3.5.1 Model
Pada bagian ini, disajikan daftar model yang digunakan oleh sistem pada Tabel 3.26
serta daftar model pendukung pada Tabel 3.27.
Tabel 3.26 Daftar model yang digunakan oleh sistem.
Model Korelasi Fungsi
Category hasMany Product Pengelompokan menu untukmemudahkan pencarian
```
DiningTable belongsTo Floor; hasManyOrder
```
Identitas meja untuk pesanan
dine-in, termasuk status
ketersediaan dan penentuan
tujuan pengantaran.
Order
```
hasMany OrderItem;
```
```
belongsTo DiningTable;
```
belongsTo CustomerDetail
Menyimpan header pesanan dan
status proses
```
OrderItem belongsTo Order;belongsTo ProductMenyimpan item pesanan yangdiproses dapur dan disajikan
```
```
Product belongsTo Category;hasMany OrderItemData menu yang dipilih padaPOS/Waiter Ordering
```
ProductOption belongsTo Product
Menyimpan definisi opsi produk
```
(mis. ukuran, tingkat gula) yang
```
dapat dipilih saat pemesanan.
68
```
Tabel 3.26 Daftar model yang digunakan oleh sistem (lanjutan).
```
Model Korelasi Fungsi
ProductOptionValue
```
hasMany Order;
```
belongsTo
DiningTable
```
(opsional, bila ada)
```
```
Menyimpan nilai opsi (mis.
```
```
Small/Medium/Large) beserta
```
```
penyesuaian harga (jika ada).
```
CustomerDetail hasMany Order Data identitas pendukung untukcheck order
Floor hasManyDiningTablePembagian area/lantaipenempatan meja
Tabel 3.27 Model pendukung.
Model
Pendukung Korelasi Fungsi
```
OrderTax belongsTo Order;belongsTo Tax
```
Menyimpan rincian pajak yang
diterapkan pada transaksi agar
mudah ditelusuri
Tax hasMany OrderTax Menyimpan definisi pajak yangdapat diterapkan pada transaksi
Discount
hasMany OrderItem
```
(opsional, jika diskon
```
```
dipasang per item)
```
Menyimpan aturan diskon yang
digunakan dalam perhitungan
harga
3.5.2 Pengendalian Proses: MVC dan Livewire
Pada bagian ini, disajikan controller dari MVC dan Livewire pada Tabel 3.28.
Tabel 3.28 Controller MVC dan Livewire.
No Modul/Proses JenisPengendali Fungsi
1 POS - PembentukanPesanan Livewire Kelola keranjang, validasi input,simpan orders & order_items
2 POS - Pembayaran /KonfirmasiLivewire /ControllerFinalisasi transaksi, pembaruanstatus pembayaran & pesanan
3 KDS - Pemrosesandapur Livewire Menampilkan antrean, ubah statuspreparing/ready
69
```
Tabel 3.28 Controller MVC dan Livewire (lanjutan).
```
No Modul/Proses JenisPengendali Fungsi
4 Waiter Orders Livewire Menampilkan ready, ubahmenjadi served
5 Waiter Ordering -Input pesanan Livewire Input pesanan oleh waiter dankirim ke POS dan KDS
6 Customer CheckOrder Controller Validasi referensi, tampilkanringkasan & status
7 Product Create Controller Pembuatan produk, melihatkuantitas produk
3.5.3 View
Pada bagian ini, disajikan view inti modul, yaitu modul POS-KDS-Waiter pada
Tabel 3.29 serta view pendukung pada Tabel 3.30.
Tabel 3.29 View inti modul: POS-KDS-Waiter.
Folder
View aktor File yang dibuat Fungsi
pos/ Cashier Index.blade.phpreceipt-print.blade.phpMemilih item, membuatpesanan, dan checkout
kitchen/ Kitchen kitchen-order-index.blade.php
Melihat antrean dan
memperbarui status
pesanan
waiter/ Waiter
Take-
order.index.blade.php
orders.blade.php
Memantau pesanan ready
dan membuat pesanan.
customer/ Customer check.blade.php Melihat status pesananberdasarkan referensi
70
Tabel 3.30 View pendukung.
Folder
View aktor File yang dibuat Fungsi
product/ Owner/Manager
index.blade.php,
create.blade.php,
edit.blade.php
Menambah,
mengubah, dan
menonaktifkan
produk
category/ Owner/Manager index.blade.php
Mengelompokkan
produk untuk
kebutuhan menu
discount/ Owner/Manager index.blade.php,
Mengatur diskon
untuk transaksi
```
(jika digunakan)
```
tax/ Owner/Manager index.blade.php
Mengatur pajak
```
transaksi (jika
```
```
digunakan)
```
diningtables/ Owner/Manager
Index.blade.php,
information.blade.php,
plan.blade.php,
qr.blade.php
Mengatur meja dan
area untuk pesanan
dine-in
qash/ Qash Owner dashboard.blade.php
3.6 Perancangan Antarmuka Modul POS-KDS-Waiter
Pada bagian ini, akan dibahas mengenai perancangan antarmuka point of
```
sales (POS), perancangan antarmuka kitchen display system (KDS), perancangan
```
antarmuka Waiter Orders, serta perancangan antarmuka Waiter Take Orders.
3.6.1 Perancangan Antarmuka POS
Antarmuka POS dirancang agar kasir dapat melakukan pencarian menu,
memilih kategori, menambahkan item ke keranjang, menentukan tipe pesanan
```
(dine-in atau takeaway), memilih meja untuk pesanan dine-in, serta meninjau
```
ringkasan transaksi sebelum pesanan dibuat. Komponen utama pada halaman ini
```
meliputi area daftar menu (kartu produk), fitur pencarian dan filter kategori, serta
```
panel keranjang yang menampilkan item terpilih, jumlah, dan total transaksi.
Perancangan antarmuka modul POS ditunjukkan pada Gambar 3.11.
71
Gambar 3.11 Antarmuka POS.
3.6.2 Perancangan Antarmuka Kitchen Display System
```
Kitchen display system (KDS) dirancang sebagai antarmuka operasional
```
bagi staf dapur untuk memantau antrian pesanan dan memperbarui status
pemrosesan pesanan secara terstruktur. KDS berfungsi sebagai pengganti
komunikasi manual atau nota kertas dengan menampilkan daftar pesanan yang
masuk dari POS maupun pesanan yang dibuat oleh pelayan. Informasi pada KDS
dibuat ringkas, tetapi tetap memuat elemen inti yang dibutuhkan dapur untuk
menyiapkan pesanan secara akurat dan tepat waktu. Perancangan antarmuka
halaman KDS ditunjukkan pada Gambar 3.12.
72
Gambar 3.12 Antarmuka kitchen display system.
Pada perancangan ini, KDS menampilkan pesanan berdasarkan status
pemrosesan. Pesanan yang telah dikonfirmasi akan muncul pada daftar utama KDS
untuk segera diproses. Ketika dapur mulai mengerjakan pesanan, staf dapur dapat
mengubah status pesanan menjadi preparing. Setelah pesanan selesai disiapkan,
staf dapur dapat menandai pesanan menjadi ready. Perubahan status tersebut
menjadi dasar integrasi dengan modul Waiter, karena pesanan yang berstatus ready
akan tampil pada halaman Waiter Orders untuk diantarkan kepada pelanggan.
Elemen antarmuka yang dirancang pada halaman KDS mencakup identitas
pesanan dan informasi yang diperlukan dapur untuk mengeksekusi pekerjaan,
```
meliputi nomor meja (untuk pesanan dine-in), daftar item beserta kuantitas, serta
```
catatan khusus apabila tersedia. Selain itu, antarmuka menyediakan tombol aksi
untuk mengubah status pesanan secara langsung, sehingga pembaruan status dapat
dilakukan tanpa proses tambahan. Perancangan KDS menekankan keterbacaan dan
kecepatan interaksi karena digunakan pada area dapur yang memiliki ritme kerja
tinggi. Oleh karena itu, informasi ditampilkan secara ringkas dan terfokus pada
73
pemrosesan, serta aksi perubahan status dibuat jelas untuk meminimalkan
kesalahan operasional.
3.6.3 Perancangan Antarmuka Waiter Orders
Antarmuka Waiter Orders dirancang untuk membantu pelayan memantau
pesanan yang telah selesai disiapkan oleh dapur dan siap diantar ke meja pelanggan.
Modul ini berperan sebagai penghubung pada tahap akhir layanan setelah
pemrosesan di dapur, sehingga pelayan memperoleh informasi secara tepat waktu
tanpa perlu melakukan pengecekan manual ke area dapur. Dengan dukungan
pembaruan status dari KDS, alur pelayanan menjadi lebih terkoordinasi karena
pelayan dapat menentukan prioritas pengantaran berdasarkan status pesanan yang
telah diperbarui, termasuk mempertimbangkan waktu kesiapan pesanan agar
kualitas sajian tetap terjaga.
Pada perancangan ini, halaman Waiter Orders menampilkan daftar pesanan
dengan status ready. Informasi yang disajikan dibuat ringkas untuk mendukung
kecepatan kerja pelayan, meliputi nomor meja, ringkasan item serta waktu pesanan
dinyatakan siap. Penyajian informasi yang singkat namun informatif bertujuan
mengurangi beban kognitif saat pelayan menangani beberapa meja sekaligus,
sekaligus meminimalkan risiko pesanan tertukar. Sistem juga menyediakan aksi
untuk membuka detail pesanan agar pelayan dapat melihat rincian item, catatan
khusus, atau instruksi penyajian sebelum pesanan dibawa ke pelanggan.
Setelah pesanan berhasil diantar dan disajikan, pelayan melakukan
konfirmasi penyajian pada sistem dengan mengubah status pesanan dari ready
menjadi served. Mekanisme ini memastikan bahwa pesanan yang sudah disajikan
tidak lagi muncul pada daftar pesanan siap antar, sekaligus menjadi penanda bahwa
alur pemrosesan pesanan telah selesai hingga tahap pelayanan. Selain menjaga
kerapian antrian kerja, perubahan status ini juga membantu pencatatan operasional,
misalnya untuk pelacakan waktu layanan dan evaluasi kinerja proses dari dapur ke
meja. Perancangan antarmuka halaman Waiter Orders ditunjukkan pada Gambar
3.13.
74
Gambar 3.13 Antarmuka waiter orders.
3.6.4 Perancangan Antarmuka Waiter Take Order
Selain memantau pesanan siap antar, sistem juga menyediakan halaman
Waiter Take Order untuk mendukung skenario pemesanan yang dilakukan
langsung oleh pelanggan melalui pelayan. Fitur ini diperlukan ketika pelanggan
memilih memesan secara lisan kepada pelayan, sehingga pelayan dapat mencatat
pesanan tanpa harus bergantung pada kasir untuk input ulang. Dengan adanya
halaman ini, pesanan dapat langsung diteruskan ke dapur melalui KDS, sehingga
waktu tunggu input pesanan dapat dikurangi.
Pada perancangan antarmuka Waiter Ordering, pelayan dapat memilih tipe
```
pesanan (misalnya dine-in) dan menentukan meja tujuan. Selanjutnya pelayan
```
memilih produk beserta jumlahnya, serta dapat menambahkan catatan khusus
apabila pelanggan memiliki permintaan tertentu. Setelah data pesanan dinyatakan
75
lengkap, sistem menyediakan tombol untuk mengirim pesanan. Pesanan yang
dikirim akan tercatat pada basis data sebagai order dan item pesanan, kemudian
muncul pada KDS agar dapat segera diproses oleh staf dapur.
Antarmuka Waiter Ordering dirancang menyerupai alur input pesanan yang
sederhana, dengan fokus pada kecepatan dan meminimalkan kesalahan. Oleh
karena itu, perancangan menekankan kemudahan pencarian menu, kejelasan
ringkasan item yang dipilih, serta validasi agar pesanan tidak dikirim dalam kondisi
kosong. Perancangan antarmuka halaman waiter take order ditunjukkan pada
Gambar 3.14.
Gambar 3.14 Antarmuka waiter take order.
3.7 Dukungan Teknologi & Lingkungan Pengembangan
Sistem dibangun menggunakan Laravel sebagai kerangka kerja utama
dengan arsitektur MVC untuk menjaga pemisahan tanggung jawab komponen.
Interaksi antarmuka yang membutuhkan pembaruan cepat didukung oleh Livewire
v3 agar pembaruan data dapat terjadi tanpa pemuatan ulang halaman. Penyajian
tabel data tertentu memanfaatkan Filament v4 untuk mempercepat implementasi
komponen tabel dan tampilan administrasi. Basis data menggunakan MySQL,
76
sedangkan konsistensi lingkungan pengembangan dijaga menggunakan Docker
untuk menstandarkan layanan yang digunakan selama pengembangan dan
pengujian.
```
A) Kerangka dan Arsitektur Aplikasi
```
Sistem dibangun menggunakan Laravel 12 sebagai kerangka kerja utama.
Laravel digunakan untuk mengelola rute aplikasi, validasi input, autentikasi dan
otorisasi akses, serta interaksi dengan basis data melalui ORM. Penggunaan
Laravel membantu penerapan struktur pengembangan yang terorganisasi
sehingga implementasi alur POS–KDS–Waiter dapat dilakukan secara konsisten
dan mudah dipelihara.
```
B) Livewire v3 untuk Interaksi Antarmuka Dinamis
```
Untuk mendukung kebutuhan interaksi cepat pada modul operasional, sistem
memanfaatkan Livewire v3. Livewire digunakan untuk membangun komponen
antarmuka yang dapat memperbarui data pada halaman secara dinamis tanpa
memuat ulang keseluruhan halaman. Mekanisme ini relevan pada modul seperti
KDS dan Waiter, karena status pesanan dapat berubah dalam waktu singkat dan
perlu ditampilkan secara tepat waktu. Dengan demikian, Livewire mendukung
pengalaman penggunaan yang lebih responsif dalam alur pemesanan.
```
C) Filament v4 sebagai Komponen Tabel dan Penyajian Data
```
Sistem menggunakan Filament v4 untuk mendukung penyajian data secara
terstruktur, khususnya pada komponen tabel dan elemen antarmuka yang
menampilkan data operasional. Filament membantu mempercepat pembangunan
elemen yang memerlukan tampilan data yang konsisten, seperti daftar pesanan,
ringkasan transaksi, atau data pendukung yang terkait dengan proses pemesanan.
Pemanfaatan Filament pada penelitian ini ditempatkan sebagai pendukung
antarmuka, sedangkan pembahasan utama tetap difokuskan pada alur POS–
KDS–Waiter.
77
```
D) Stancl Tenancy sebagai Konteks Multi-Tenant
```
Aplikasi Qash dirancang sebagai platform yang mendukung penggunaan oleh
lebih dari satu kafe dengan isolasi data antar tenant. Untuk mendukung
kebutuhan tersebut, sistem memanfaatkan Stancl Tenancy sebagai mekanisme
pengelolaan konteks multi-tenant. Namun, penelitian ini membatasi pembahasan
pada implementasi alur pemrosesan pesanan POS–KDS–Waiter. Oleh karena
itu, aspek multi-tenant dijelaskan sebagai konteks arsitektur aplikasi dan tidak
dibahas secara rinci pada implementasi modul inti.
3.7.1 Basis Data MySQL
Sistem menggunakan MySQL sebagai DBMS dengan model basis data
relasional. MySQL digunakan untuk menyimpan data master dan data transaksi,
```
terutama data pesanan (orders) dan detail item pesanan (order_items). Struktur
```
basis data dirancang untuk mendukung pencatatan perubahan status pesanan yang
menjadi dasar integrasi antarmodul POS–KDS–Waiter, termasuk pencatatan waktu
perubahan status untuk kebutuhan pelacakan proses.
3.7.2 TablePlus sebagai Alat Inspeksi dan Validasi Basis Data
Untuk mendukung proses implementasi dan verifikasi skema basis data,
penelitian ini memanfaatkan TablePlus sebagai aplikasi klien basis data. TablePlus
digunakan untuk melakukan inspeksi struktur tabel, tipe data, indeks, constraint,
serta relasi foreign key pada DBMS MySQL yang digunakan oleh sistem.
Penggunaan TablePlus membantu memastikan bahwa hasil pembentukan tabel
melalui mekanisme migrasi Laravel telah sesuai dengan rancangan ERD, sekaligus
memudahkan proses pengecekan data uji selama pengembangan dan pengujian.
Pada penelitian ini, TablePlus digunakan sebagai alat bantu validasi dan
dokumentasi visual struktur basis data, sedangkan definisi skema tetap mengacu
pada hasil implementasi migration yang dirangkum pada Lampiran.
3.7.3 Lingkungan Pengembangan Berbasis Docker
Untuk menjaga konsistensi lingkungan pengembangan, sistem dijalankan
menggunakan Docker sebagai lingkungan berbasis container. Docker digunakan
untuk menstandarkan layanan yang dibutuhkan aplikasi, khususnya layanan basis
78
data, sehingga konfigurasi pengembangan tidak bergantung pada instalasi manual
pada setiap perangkat. Penggunaan Docker pada penelitian ini bertujuan untuk
kebutuhan pengembangan dan pengujian pada lingkungan lokal, bukan sebagai
indikator bahwa sistem telah dioperasikan pada lingkungan produksi.
3.8 Rancangan Pengujian
Rancangan pengujian pada penelitian ini disusun untuk memastikan bahwa
sistem manajemen operasional kafe yang dikembangkan telah memenuhi
kebutuhan fungsional serta dapat digunakan dengan baik oleh pengguna. Pengujian
dibagi menjadi dua pendekatan utama, yaitu black box testing untuk memverifikasi
```
fungsi sistem berdasarkan skenario uji, dan user acceptance test (UAT) untuk
```
mengukur tingkat penerimaan pengguna terhadap kemudahan penggunaan,
kejelasan informasi, dan kesesuaian fitur dengan kebutuhan operasional. Ruang
```
lingkup pengujian mencakup modul point of sales (POS), kitchen display system
```
```
(KDS), waiter orders, waiter take orders, serta customer check order.
```
3.8.1 Rancangan Pengujian Black Box
Pengujian black box dirancang untuk memastikan setiap fungsi pada sistem
berjalan sesuai kebutuhan tanpa meninjau struktur kode internal. Pengujian
dilakukan dengan memberikan input melalui antarmuka, menjalankan langkah uji
sesuai skenario, kemudian membandingkan keluaran sistem terhadap hasil yang
diharapkan. Setiap skenario juga diberi prioritas untuk menandai fungsi yang paling
kritis terhadap operasional.
```
Pada pelaksanaan pengujian, setiap skenario diuji berulang (misalnya 10
```
```
kali) untuk melihat konsistensi hasil, kemudian dihitung persentase keberhasilan.
```
Tabel 3.31 menunjukkan daftar pengujian fungsional berbasis skenario yang
digunakan sebagai acuan uji Black Box pada masing-masing modul.
79
```
Tabel 3.31 Pengujian fungsional (black box).
```
No KategoriPengguna Kode Skenario/Kebutuhanyang Diuji Hasil yang Diharapkan Prioritas
1 Kitchen KDS-01
Menampilkan daftar
pesanan aktif sesuai
filter
Pesanan tampil terkelompok
confirmed/preparing/ready Tinggi
2 Kasir POS-01 Menambah produk kekeranjang Item muncul di keranjang Tinggi
3 Waiter WTO-01 Mengirim order kekasir Order terbentuk Tinggi
4 Waiter WO-01 Menandai item“served”Item berubah served dari“Ready to Serve” Tinggi
5 Customer CCO-01 Customer melihatstatus pesananStatus terakhir tampil sesuaiorder Sedang
3.8.2 Rancangan Pengujian User Acceptance Testing
```
User acceptance test (UAT) dirancang untuk mengetahui tingkat
```
penerimaan pengguna terhadap sistem berdasarkan pengalaman penggunaan secara
langsung. Pengujian dilakukan dengan memberikan kuesioner kepada pengguna di
```
Kasumba Coffee Shop Bandung sesuai peran, yaitu Cashier (POS), Kitchen (KDS),
```
```
Waiter, dan Customer (Check Order). Responden diminta menilai pernyataan
```
terkait kemudahan penggunaan, kejelasan informasi, dan kesesuaian fitur sistem
dengan kebutuhan operasional menggunakan skala Likert 1–5. Tabel 3.32
```
menunjukkan penilaian skala Likert dengan interpretasi: 1 (Sangat Tidak Setuju), 2
```
```
(Tidak Setuju), 3 (Cukup Setuju), 4 (Setuju), dan 5 (Sangat Setuju). Nilai pada
```
Tabel 3.32 digunakan sebagai acuan penilaian setiap pernyataan pada kuesioner
UAT. Skor yang diberikan responden selanjutnya diolah untuk memperoleh total
skor, rata-rata, dan persentase penerimaan pada masing-masing peran.
80
Tabel 3.32 Penilaian skala Likert.
Angka Penilaian
1 Sangat Tidak Setuju
2 Tidak Setuju
3 Cukup Setuju
4 Setuju
5 Sangat Setuju
Untuk tiap jenis modul, disusun sepuluh pernyataan yang kemudian
disebarkan kepada responden untuk menilai kemudahan penggunaan, kejelasan
informasi, dan kesesuaian fitur sistem dengan kebutuhan operasional. Tabel 3.33
memuat pernyataan UAT untuk peran cashier yang berfokus pada kemudahan
pencatatan pesanan, kejelasan informasi produk/harga, dan proses pembayaran.
Responden pada peran cashier memberikan skor 1–5 untuk setiap pernyataan pada
Tabel 3.33.
Tabel 3.33 Pernyataan user acceptance test – Cashier.
No. Pernyataan UAT – Cashier.
1. Saya merasa proses pencatatan pesanan melalui POS mudah dipahami dan
digunakan.
2. Saya merasa informasi produk dan harga pada POS ditampilkan dengan
jelas.
3. Saya merasa proses pembayaran dan konfirmasi transaksi mudah
dilakukan.
4. Saya merasa sistem membantu mengurangi kesalahan pencatatan pesanan
5. Saya merasa tampilan POS mendukung kecepatan pelayanan kepada
pelanggan.
6. Saya merasa pemilihan tipe pesanan (dine-in/takeaway) pada POS mudah
dilakukan dan tidak membingungkan.
7. Saya merasa validasi sistem saat checkout (keranjang kosong, belum pilih
```
customer, dine-in wajib pilih meja) membantu mencegah kesalahan
```
transaksi.
81
```
Tabel 3.33 Pernyataan user acceptance test – Cashier (lanjutan).
```
No. Pernyataan UAT – Cashier.
8. Saya merasa ringkasan transaksi (subtotal, pajak/diskon bila ada, total
```
akhir) mudah dipahami sebelum konfirmasi pembayaran.
```
9. Saya merasa setelah transaksi selesai, status pesanan terbentuk dan dapat
diteruskan ke proses dapur tanpa langkah tambahan yang rumit.
10. Saya merasa pencarian/identifikasi pesanan (melalui reference_no)
membantu saat perlu melakukan pengecekan pesanan.
```
Tabel 3.34 memuat pernyataan UAT untuk Kitchen Staff (KDS) yang
```
menilai keterbacaan antrean, kemudahan perubahan status, dan dukungan
koordinasi lintas peran. Responden pada peran kitchen staff memberikan skor 1–5
untuk setiap pernyataan pada Tabel 3.34.
Tabel 3.34 Pernyataan user acceptance test – Kitchen Staff.
No. Pernyataan UAT – Kitchen Staff.
1. Saya merasa daftar pesanan pada KDS mudah dipahami
2. Saya merasa perubahan status pesanan mudah dilakukan
3. Saya merasa informasi item dan catatan pesanan ditampilkan dengan jelas
4. Saya merasa KDS membantu mengatur prioritas pesanan
5. Saya merasa KDS mempercepat koordinasi dengan kasir dan waiter
6. Saya merasa informasi waktu/urutan antrean pesanan pada KDS cukup
membantu untuk menentukan prioritas.
7. Saya merasa perubahan status pesanan (confirmed → preparing → ready)
pada KDS mudah dilakukan dan tidak menimbulkan kekeliruan.
8. Saya merasa KDS membantu mengurangi ketergantungan pada
komunikasi manual dengan kasir terkait pesanan masuk.
9. Saya merasa KDS memudahkan pemantauan pesanan yang masih dalam
proses dibandingkan pencatatan manual.
10. Saya merasa tampilan KDS tetap jelas dan mudah dibaca saat jumlah
pesanan meningkat.
82
Tabel 3.35 memuat pernyataan UAT untuk Waiter yang menilai kejelasan
informasi. Responden pada peran waiter memberikan skor 1–5 untuk setiap
pernyataan pada Tabel 3.35.
Tabel 3.35 Pernyataan user acceptance test – Waiter.
No. Pernyataan UAT – Waiter.
1. Saya merasa informasi pesanan siap antar mudah dipahami
2. Saya merasa informasi meja dan detail pesanan membantu proses
penyajian
3. Saya merasa proses penandaan pesanan sebagai served mudah dilakukan
4. Saya merasa sistem membantu mengurangi kesalahan penyajian
5. Saya merasa sistem meningkatkan koordinasi dengan dapur
6. Saya merasa daftar pesanan “ready” yang harus disajikan mudah dipantau
oleh waiter.
7. Saya merasa informasi meja dan detail item pada halaman waiter cukup
lengkap untuk menghindari salah antar.
8. Saya merasa proses penandaan pesanan sebagai served membantu
memastikan status pesanan konsisten dengan kondisi di lapangan.
9. Saya merasa sinkronisasi status antara KDS dan waiter berjalan baik
sehingga tidak terjadi miskomunikasi saat penyajian.
10. Saya merasa fitur pencarian/penyaringan pesanan pada halaman waiter
membantu mempercepat penyajian.
Tabel 3.36 memuat pernyataan UAT untuk customer yang menilai
kemudahan akses dan manfaat fitur check order dalam memantau progres pesanan
secara mandiri. Responden pada peran customer memberikan skor 1–5 untuk setiap
pernyataan pada Tabel 3.36.
83
Tabel 3.36 Pernyataan user acceptance test – Customer.
No. Pernyataan UAT – Customer.
1. Saya merasa fitur check order mudah digunakan
2. Saya merasa informasi status pesanan ditampilkan dengan jelas
3. Saya merasa fitur ini membantu mengetahui progres pesanan
4. Saya merasa tidak perlu bertanya ke kasir/waiter untuk mengetahui status
pesanan
5. Saya merasa fitur ini meningkatkan kenyamanan saat menunggu pesanan
6. Saya merasa tampilan status pesanan (misalnya
```
confirmed/preparing/ready/served) mudah dipahami.
```
7. Saya merasa informasi detail pesanan (item yang dipesan) membantu
memastikan pesanan saya benar.
8. Saya merasa pembaruan status pesanan ditampilkan tepat waktu sesuai
progres yang terjadi.
9. Saya merasa fitur ini mengurangi kebutuhan saya untuk bertanya ke
kasir/waiter saat menunggu pesanan.
10. Saya merasa fitur check order meningkatkan kenyamanan selama
menunggu pesanan.
84
BAB IV
IMPLEMENTASI SISTEM
Pada bagian ini akan dibahas mengenai gambaran umum implementasi sistem,
implementasi basis data, dan implementasi modul yang mencakup modul
```
pendukung, modul point of sales (POS), modul kitchen display system (KDS),
```
modul waiter orders, modul waiter take orders, serta modul customer check and
track orders.
4.1 Gambaran Umum Implementasi Sistem
Implementasi sistem dilakukan pada lingkungan pengembangan dan
```
pengujian (bukan klaim lingkungan produksi). Pengembangan aplikasi
```
menggunakan Laravel 12 dengan dukungan komponen interaktif Livewire v3 dan
komponen tabel menggunakan Filament v4. Sistem menggunakan MySQL sebagai
DBMS untuk menyimpan data transaksi dan data pendukung. Untuk menjaga
konsistensi lingkungan dan memudahkan penggunaan konfigurasi layanan, aplikasi
dan basis data dijalankan menggunakan Docker pada mesin pengembangan.
Secara fungsional, sistem terdiri dari beberapa modul berikut:
1. Modul Pendukung (prasyarat): konteks tenant (multi-tenant), autentikasi
```
dan otorisasi berbasis peran, serta pengisian data master (kategori, produk,
```
```
opsi produk, dan meja) sebagai masukan langsung transaksi.
```
2. Modul Point of Sales (POS): pencatatan transaksi, manajemen keranjang,
```
validasi, perhitungan nilai transaksi (subtotal/pajak/diskon), serta
```
pembentukan order yang akan diteruskan ke proses dapur.
3. Modul Kitchen Display System (KDS): menampilkan daftar order yang
perlu diproses dapur, mengelola perubahan status pemrosesan, dan menjaga
konsistensi alur produksi.
4. Modul Waiter Orders dan Waiter Take Order: Waiter Orders berfokus pada
pengantaran pesanan yang telah siap dan penandaan penyajian, sedangkan
Waiter Take Order memungkinkan pelayan membuat pesanan dari area
85
layanan dan mengirimkannya agar tercatat sebagai order baru sesuai
mekanisme sistem.
5. Modul Customer Check Order dan Track Order (read-only): pelanggan
memeriksa status pesanan menggunakan referensi/identitas order dan
melihat progres status.
Dalam proses implementasi sistem, spesifikasi lingkungan implementasi yang
digunakan adalah sebagai berikut:
```
• Sistem Operasi (host): Windows 11.
```
• Processor: Intel Core i7-10750H.
• RAM: 16 GB.
• Penyimpanan: SSD 512 GB.
• PHP: PHP 8.3.
• Kerangka Kerja: Laravel 12.
• Komponen UI/Interaktif: Livewire v3, Filament v4.
• DBMS: MySQL 8.4.
• Web server: built-in PHP server.
• Docker: digunakan sebagai environment pengembangan untuk menjalankan
layanan aplikasi dan basis data secara terstandar.
```
• Browser pengujian: Google Chrome versi 143.0.7499.193 (Official Build)
```
```
(64-bit).
```
```
• Konfigurasi Docker Compose (Laravel Sail).
```
Pada tahap implementasi sistem, lingkungan pengembangan dan pengujian
aplikasi manajemen operasional kafe dikonfigurasikan menggunakan Docker
dengan bantuan Laravel Sail. Pendekatan ini dipilih untuk memastikan konsistensi
lingkungan eksekusi aplikasi, sehingga perbedaan konfigurasi sistem operasi atau
dependensi perangkat lunak pada perangkat pengembang tidak memengaruhi hasil
implementasi dan pengujian. Laravel Sail menyediakan konfigurasi Docker
Compose yang telah terintegrasi dengan kerangka Laravel, sehingga memudahkan
pengelolaan layanan aplikasi secara terstandar.
86
Gambar 4.1 menampilkan kondisi container Docker pada saat server
dimatikan. Berdasarkan Gambar 4.1, sistem disusun menggunakan beberapa image
utama, yaitu image aplikasi Laravel berbasis PHP 8.3, image MySQL sebagai basis
data, serta image ngrok untuk kebutuhan tunnelling. Image tersebut digunakan
sebagai fondasi dalam membangun kontainer yang saling terhubung dalam satu
lingkungan pengembangan yang terisolasi.
Gambar 4.1 Docker container pada saat server dimatikan.
Konfigurasi layanan aplikasi dilakukan melalui berkas compose.yaml yang
```
dikelola oleh Laravel Sail. Berkas ini mendefinisikan beberapa layanan (services)
```
yang dijalankan secara bersamaan dalam arsitektur multi-container, meliputi
kontainer aplikasi Laravel, kontainer basis data MySQL, serta kontainer pendukung
untuk akses eksternal. Potongan konfigurasi layanan aplikasi ditunjukkan pada
Tabel 4.1.
Tabel 4.1 Konfigurasi compose.yaml untuk layanan laravel.test.
```
services:
```
laravel.test:
```
build:
```
```
context: './vendor/laravel/sail/runtimes/8.3'
```
```
dockerfile: Dockerfile
```
```
args:
```
```
WWWGROUP: '${WWWGROUP}'
```
```
image: 'sail-8.3/app'
```
```
extra_hosts:
```
- 'host.docker.internal:host-gateway'
87
```
ports:
```
- '${APP_PORT:-80}:80' # Expose port 80 for external
access
- '${VITE_PORT:-5173}:${VITE_PORT:-5173}' # Vite
```
environment:
```
```
WWWUSER: '${WWWUSER}'
```
```
LARAVEL_SAIL: 1
```
```
XDEBUG_MODE: '${SAIL_XDEBUG_MODE:-off}'
```
```
XDEBUG_CONFIG: '${SAIL_XDEBUG_CONFIG:-
```
```
client_host=host.docker.internal}'
```
```
IGNITION_LOCAL_SITES_PATH: '${PWD}'
```
```
volumes:
```
- '.:/var/www/html'
```
networks:
```
- sail
```
depends_on:
```
- mysql
Pada konfigurasi tersebut, layanan laravel.test berperan sebagai kontainer
utama aplikasi. Kontainer ini dibangun menggunakan runtime PHP 8.3 yang
disediakan oleh Laravel Sail. Source code aplikasi di-mount ke dalam kontainer
```
melalui mekanisme volume (.:/var/www/html), sehingga setiap perubahan kode
```
pada host dapat langsung diterapkan di dalam kontainer tanpa perlu proses rebuild.
Selain itu, port aplikasi dipetakan ke port host agar sistem dapat diakses melalui
peramban selama proses pengembangan dan pengujian.
Sebagai penyedia basis data, sistem menggunakan kontainer mysql dengan
image MySQL versi 8.4. Konfigurasi basis data dilakukan melalui variabel
lingkungan yang diambil dari berkas .env, sehingga informasi sensitif seperti nama
basis data, pengguna, dan kata sandi tidak dituliskan secara langsung di dalam
berkas konfigurasi Docker. Untuk menjaga keberlangsungan data selama proses
```
pengembangan, digunakan volume Docker (sail-mysql) yang menyimpan data basis
```
data secara persisten. Potongan konfigurasi kontainer basis data ditunjukkan pada
Tabel 4.2.
Tabel 4.2 Konfigurasi compose.yaml untuk layanan mysql.
```
mysql:
```
```
image: 'mysql:8.4'
```
```
ports:
```
- '${FORWARD_DB_PORT:-3306}:3306'
```
environment:
```
```
MYSQL_ROOT_PASSWORD: '${DB_PASSWORD}'
```
```
MYSQL_ROOT_HOST: '%'
```
```
MYSQL_DATABASE: '${DB_DATABASE}'
```
88
```
MYSQL_USER: '${DB_USERNAME}'
```
```
MYSQL_PASSWORD: '${DB_PASSWORD}'
```
```
MYSQL_ALLOW_EMPTY_PASSWORD: 1
```
```
MYSQL_EXTRA_OPTIONS: '${MYSQL_EXTRA_OPTIONS:-}'
```
```
volumes:
```
- 'sail-mysql:/var/lib/mysql'
-
Gambar 4.2 menunjukkan kondisi Docker kontainer yang berjalan pada
server lokal. Berdasarkan Gambar 4.2, seluruh kontainer berjalan dalam satu
jaringan Docker bertipe bridge dengan nama sail. Jaringan ini memungkinkan
komunikasi antar layanan secara internal tanpa perlu mengekspos seluruh port ke
host. Selain itu, sistem memanfaatkan kontainer ngrok sebagai tunnelling service
untuk menyediakan akses eksternal sementara ke aplikasi yang berjalan pada
lingkungan lokal, misalnya untuk keperluan uji coba dan demonstrasi sistem.
Gambar 4.2 Docker kontainer berjalan pada server lokal.
Setelah seluruh kontainer berhasil dijalankan, lingkungan pengembangan
dinyatakan siap digunakan untuk tahap implementasi fitur sistem, pengujian
```
fungsional, serta pelaksanaan user acceptance test (UAT). Penggunaan Docker dan
```
Laravel Sail pada penelitian ini difokuskan sebagai sarana pengembangan dan
pengujian lokal, bukan sebagai representasi langsung dari lingkungan produksi.
89
4.2 Implementasi Basis Data
Perancangan basis data pada Bab 3.4.2 diimplementasikan menggunakan
DBMS MySQL dengan model relasional. Implementasi skema dilakukan melalui
mekanisme migrasi pada Laravel, sehingga proses pembentukan tabel, relasi,
constraint, dan indeks dapat dikelola secara terstruktur, terdokumentasi, dan
konsisten pada setiap lingkungan pengembangan. Basis data ini difokuskan untuk
mendukung alur pemesanan terintegrasi POS–KDS–Waiter, mencakup data master
```
(produk, kategori, serta meja), data transaksi (pesanan dan item pesanan), serta data
```
```
pendukung transaksi (pajak dan diskon).
```
Secara konseptual, data transaksi dibagi menjadi dua tingkat. Tabel orders
berperan sebagai header pesanan yang menyimpan informasi umum seperti
```
identitas tenant, tipe pesanan (dine-in/takeaway), sumber pesanan
```
```
(POS/QR/Waiter), status pembayaran, serta ringkasan nilai transaksi (subtotal,
```
```
pajak, dan total akhir). Sementara itu, tabel order_items menyimpan rincian item
```
yang dipesan per pesanan, termasuk kuantitas, harga final, catatan khusus, serta
status pemenuhan pada level item. Untuk kebutuhan komponen pajak, digunakan
tabel order_taxes yang menyimpan rincian pajak yang diterapkan pada suatu
pesanan sehingga kalkulasi pajak tetap dapat ditelusuri dan diaudit.
Untuk mendukung pelacakan proses operasional, perubahan status
pemenuhan pesanan disimpan pada atribut status dan dilengkapi atribut waktu
```
(misalnya waktu konfirmasi, mulai diproses, hingga siap disajikan). Atribut waktu
```
ini digunakan untuk membantu penelusuran alur, memastikan konsistensi
```
sinkronisasi status antarmodul (POS-KDS-Waiter), dan menjadi dasar
```
pengembangan analisis durasi layanan apabila diperlukan.
Selain tabel transaksi, sistem menggunakan tabel master products dan
categories untuk mendukung pengelolaan menu, serta tabel floors dan
dining_tables untuk mendukung pemetaan meja pada layanan dine-in. Data
pelanggan yang digunakan pada transaksi disimpan pada tabel customer_details.
Pada sisi antarmuka pemesanan, terdapat tabel cart_items yang digunakan untuk
menyimpan item sementara pada konteks sesi pengguna sebelum pesanan terbentuk
menjadi transaksi permanen.
90
```
A) Dukungan Multi-Tenant dan Integritas Data
```
Untuk menjaga isolasi data antarkafe, setiap data yang relevan terhadap
operasional tenant disimpan dengan identitas tenant_id. Penerapan foreign key
```
constraint serta kebijakan penghapusan (misalnya cascade atau set null)
```
digunakan sesuai kebutuhan agar integritas data tetap terjaga, terutama pada
relasi transaksi seperti orders–order_items dan referensi ke data master.
```
B) Visualisasi Struktur Basis Data Menggunakan TablePlus
```
Validasi implementasi skema basis data dilakukan melalui inspeksi struktur
tabel dan relasi menggunakan aplikasi TablePlus. Visualisasi ini digunakan
untuk memverifikasi bahwa skema yang terbentuk pada DBMS MySQL telah
sesuai dengan rancangan ERD pada Bab 3.4.3, khususnya pada entitas inti
transaksi sebagaimana dijelaskan pada Bab 3.4.5. Verifikasi difokuskan pada
beberapa aspek, yaitu kesesuaian nama kolom dan tipe data, terbentuknya
constraint dan indeks sesuai kebutuhan akses, serta konsistensi relasi foreign key
pada alur transaksi pemesanan.
Tangkapan layar hasil visualisasi skema basis data disajikan pada Lampiran
B. Daftar tabel pada basis data ditunjukkan pada Gambar B.1. Struktur tabel inti
```
transaksi ditunjukkan pada Gambar B.2 (orders), Gambar B.3 (order_items),
```
```
dan Gambar B.4 (customer_details). Struktur tabel data master yang menjadi
```
```
prasyarat transaksi ditunjukkan pada Gambar B.5 (products) dan Gambar B.6
```
```
(categories). Contoh relasi foreign key pada alur transaksi, yaitu orders–
```
order_items, ditunjukkan pada Gambar B.7 sebagai verifikasi integritas
referensial.
```
C) Rujukan Definisi Skema pada Lampiran
```
Definisi DDL pada Lampiran A merupakan representasi skema tabel yang
terbentuk setelah proses migration Laravel dijalankan pada DBMS MySQL.
Untuk seluruh tabel yang digunakan pada penelitian ini disajikan pada Lampiran
A. Sementara itu, tangkapan layar struktur tabel dan relasi hasil visualisasi pada
TablePlus disajikan pada Lampiran B.
91
4.3 Implementasi Modul Pendukung
Subbab ini menjelaskan komponen prasyarat yang diperlukan agar modul
inti POS–KDS–Waiter dapat dijalankan, meliputi akses tenant, autentikasi
```
pengguna, serta pengisian data master (produk dan meja). Pembahasan dibatasi
```
pada aspek yang berkaitan langsung dengan proses pemesanan dan tidak mengulas
fitur manajemen lain di luar ruang lingkup penelitian.
4.3.1 Multi-Tenant dan Isolasi Data
Aplikasi dirancang multi-tenant sehingga satu platform dapat melayani
lebih dari satu kafe dengan ruang data terpisah. Setiap data operasional yang relevan
menyertakan tenant_id, dan akses data dibatasi pada tenant pengguna yang sedang
aktif. Mekanisme ini mencegah kebocoran data antar tenant dan menjaga
konsistensi operasional per kafe.
Pada Gambar 4.3 menunjukkan proses pembuatan tenant yang berfungsi
sebagai “ruang kerja” untuk satu kafe. Dengan konsep ini, seluruh data operasional
seperti produk, meja, pesanan, dan transaksi akan terikat pada tenant yang dipilih
sehingga pemisahan data antar kafe dapat terjaga.
Gambar 4.3 Pembuatan tenant sebagai ruang kerja kafe.
92
```
4.3.2 Login dan Otorisasi Berbasis Peran (RBAC)
```
Autentikasi diterapkan untuk memastikan hanya pengguna terdaftar yang
dapat mengakses sistem. Otorisasi berbasis peran membatasi fitur sesuai tanggung
jawab, misalnya staff kasir mengakses POS, staff dapur mengakses KDS, dan staff
pelayan mengakses halaman pelayan. Pembatasan ini mencegah perubahan status
oleh pihak yang tidak berwenang.
Pada Gambar 4.4 menampilkan antarmuka login pada konteks tenant.
Proses login menjadi pintu masuk utama untuk validasi identitas pengguna sebelum
sistem memberikan akses sesuai peran yang dimiliki.
Gambar 4.4 Halaman login pada lingkungan tenant.
93
```
(A) (B) (C)
```
Gambar 4.5
```
Tampilan sidebar berdasarkan peran pengguna: A) cashier, B) kitchen, C) waiter
```
```
Pada Gambar 4.5 memperlihatkan bahwa menu navigasi (sidebar)
```
menyesuaikan peran pengguna. Perbedaan menu ini bertujuan mengarahkan
pengguna hanya pada fitur yang relevan dengan tugasnya, sekaligus mengurangi
risiko akses ke fungsi yang tidak semestinya.
Gambar 4.6 Halaman admin memberikan batasan akses kepada karyawan.
94
Pada Gambar 4.6 menunjukkan mekanisme otorisasi pemilik kafe yang
dapat membatasi akses fitur tertentu hanya untuk role tertentu. Dengan pembatasan
```
tersebut, sistem memastikan tindakan operasional (misalnya memproses pesanan di
```
```
KDS atau melakukan transaksi di POS) hanya dapat dilakukan oleh pihak yang
```
berwenang.
Gambar 4.7 Halaman 403.
Pada Gambar 4.7 menggambarkan kondisi ketika pengguna mencoba
mengakses halaman yang tidak sesuai dengan perannya. Sistem akan menolak
```
akses, dan memberikan kode 403 (403 Forbidden) adalah kode status HTTP yang
```
berarti server mengerti permintaan Anda tetapi menolak memberikan akses ke
sumber daya yang diminta, sehingga kontrol keamanan dan konsistensi alur kerja
tetap terjaga.
4.3.3 Data Master Pemesanan
Data master berupa kategori, produk, dan meja disiapkan agar pemesanan
dapat dilakukan secara konsisten. Kategori digunakan untuk pengelompokan menu,
produk menyimpan informasi item yang dapat dipesan, dan meja digunakan untuk
skenario dine-in.
95
Gambar 4.8 Manajemen kategori produk sebagai pengelompokan menu.
Berdasarkan Gambar 4.8, sistem menyediakan fitur manajemen kategori
untuk mengelompokkan produk ke dalam kelompok menu tertentu, sehingga proses
pencarian dan penyajian menu pada modul POS maupun Waiter menjadi lebih
terstruktur. Setiap kategori dapat dikelola melalui operasi tambah, ubah, dan hapus,
sehingga perubahan pengelompokan menu dapat dilakukan tanpa memengaruhi
alur transaksi yang sedang berjalan.
Gambar 4.9 Manajemen produk sebagai data master pemesanan.
Selanjutnya, pada Gambar 4.9 menunjukkan manajemen produk sebagai
data master pemesanan. Pada modul ini, pengguna dapat mengelola informasi
produk yang akan digunakan dalam transaksi, seperti nama produk, kategori, harga,
status aktif/nonaktif, serta informasi pendukung lainnya sesuai kebutuhan sistem.
96
Data produk yang telah tersimpan kemudian menjadi referensi utama saat kasir atau
pelayan melakukan pemesanan, sehingga nilai transaksi dan item yang dikirim ke
proses dapur dapat terbentuk secara konsisten berdasarkan data master yang sama.
4.3.4 Pengaturan Meja untuk Pesanan Dine-In
Pada skenario dine-in, sistem menggunakan data meja untuk mengaitkan
pesanan dengan lokasi pelanggan. Oleh karena itu, sebelum transaksi dilakukan,
data meja disiapkan pada sistem, termasuk identitas meja dan status meja apabila
ditampilkan. Data ini digunakan pada POS maupun Waiter Orders untuk
memastikan pesanan dine-in memiliki informasi tujuan pengantaran.
Gambar 4.10 Manajemen meja untuk mendukung pesanan dine-in.
4.3.5 Diskon dan Pajak
Diskon dan pajak diterapkan sebagai komponen perhitungan nilai transaksi.
Konfigurasi ini digunakan pada perhitungan ringkasan transaksi di modul POS agar
total akhir yang ditampilkan sesuai aturan yang ditetapkan tenant.
97
Gambar 4.11 Konfigurasi pajak sebagai komponen pendukung transaksi.
Gambar 4.12 Konfigurasi diskon sebagai komponen pendukung transaksi.
```
4.4 Implementasi Modul Point of Sale (POS)
```
Modul POS bertanggung jawab mencatat pesanan, mengelola keranjang,
```
menentukan konteks layanan (dine-in/takeaway), mengisi data pelanggan, serta
```
menyelesaikan transaksi pembayaran. Setelah transaksi tersimpan, pesanan
diteruskan untuk diproses dapur melalui status awal yang disepakati sistem,
sehingga pesanan menjadi input bagi KDS.
Struktur implementasi POS dijelaskan melalui antarmuka POS dan logika
```
bisnis POS yang meliputi: (1) manajemen keranjang, (2) perhitungan nilai
```
```
transaksi, (3) checkout dan penyimpanan transaksi, (4) transisi status pesanan untuk
```
```
integrasi KDS, dan (5) validasi input sebagai pencegahan transaksi tidak valid.
```
98
4.4.1 Antarmuka POS
Antarmuka POS dirancang untuk mendukung proses pencatatan pesanan
oleh kasir secara cepat dan terstruktur. Halaman POS terdiri atas dua area utama.
Area pertama menampilkan daftar menu dalam bentuk kartu produk yang
dilengkapi informasi nama produk, harga, dan ketersediaan stok. Area kedua
merupakan panel transaksi yang digunakan untuk memilih tipe pesanan,
menentukan meja untuk pesanan dine-in, menampilkan item pada keranjang, serta
menampilkan ringkasan nilai transaksi.
Pada area daftar menu, kasir dapat melakukan pencarian menu melalui
kolom pencarian dan menyaring menu berdasarkan kategori. Setiap kartu produk
menyediakan aksi penambahan item ke keranjang. Produk yang tidak tersedia
ditandai pada antarmuka sehingga kasir tidak dapat menambahkannya ke dalam
keranjang.
```
Pada panel transaksi, kasir dapat memilih tipe pesanan (misalnya dine-in
```
```
atau takeaway). Untuk pesanan dine-in, kasir memilih nomor meja dari daftar meja
```
yang tersedia, termasuk informasi status meja. Setelah item dipilih, keranjang
menampilkan daftar item beserta jumlah dan harga. Bagian ringkasan transaksi
```
menampilkan subtotal, diskon (apabila diterapkan), dan total akhir. Tombol aksi
```
pemesanan digunakan untuk mengirim data pesanan agar tersimpan pada basis data
dan diteruskan ke proses berikutnya.
```
Sistem juga menyediakan dukungan tema tampilan (light mode dan dark
```
```
mode) untuk meningkatkan kenyamanan penggunaan pada kondisi pencahayaan
```
berbeda. Perubahan tema hanya memengaruhi tampilan visual tanpa mengubah
fungsi utama proses transaksi pada POS.
99
Gambar 4.13 Halaman POS.
Pada Gambar 4.13 menampilkan tampilan utama POS yang
memperlihatkan pembagian area daftar menu dan panel transaksi, sehingga kasir
dapat memilih produk sekaligus memantau isi keranjang dan total transaksi dalam
satu halaman.
Gambar 4.14 Halaman POS bagian pemesanan item.
Pada Gambar 4.14 menunjukkan proses kasir menambahkan item dari kartu
produk ke keranjang. Setiap penambahan item akan memperbarui daftar item dan
nilai transaksi secara otomatis pada panel keranjang.
100
Gambar 4.15 Halamn POS pengisian data pelanggan.
Pada Gambar 4.15 memperlihatkan form input data pelanggan yang
digunakan untuk mengaitkan pesanan dengan identitas pelanggan, sehingga data
transaksi dapat terdokumentasi lebih lengkap dan dapat digunakan untuk kebutuhan
laporan maupun riwayat pesanan.
Gambar 4.16 Halaman POS mencari data pelanggan yang sudah terdaftar.
Pada Gambar 4.16 menunjukkan fitur pencarian pelanggan yang
memungkinkan kasir memilih data pelanggan yang sudah ada di basis data,
sehingga mempercepat proses input dan menghindari duplikasi data pelanggan.
101
Gambar 4.17 Informasi keranjang POS.
Pada Gambar 4.17 menampilkan keranjang pesanan berisi daftar item,
jumlah, dan harga. Pada bagian ini kasir dapat memverifikasi item sebelum
melanjutkan transaksi, termasuk memastikan kuantitas dan total sementara sudah
sesuai.
Gambar 4.18 Proses pembayaran pesanan.
102
Pada Gambar 4.18 menunjukkan tahap pembayaran, di mana sistem
menampilkan total yang harus dibayar dan elemen yang diperlukan untuk
menyelesaikan transaksi. Setelah pembayaran berhasil, status pesanan diteruskan
ke tahap berikutnya untuk diproses di KDS.
Gambar 4.19 Halaman ringkasan pesanan.
Pada Gambar 4.19 memperlihatkan ringkasan akhir transaksi yang
```
mencakup subtotal, diskon (jika ada), dan total akhir. Ringkasan ini berfungsi
```
sebagai validasi terakhir sebelum transaksi dikonfirmasi dan disimpan sebagai data
pesanan.
4.4.2 Logika Bisnis POS
```
Logika bisnis pada modul point of sales (POS) diimplementasikan
```
menggunakan komponen berbasis Livewire untuk menangani interaksi pengguna
pada antarmuka secara responsif tanpa pemuatan ulang halaman penuh. Seluruh
perhitungan dan proses transaksi dipusatkan melalui helper dan service agar nilai
transaksi yang ditampilkan pada antarmuka selaras dengan data yang disimpan pada
103
basis data. Pendekatan ini mengurangi duplikasi logika, meningkatkan konsistensi
angka transaksi, serta memastikan alur operasional kasir berjalan dan dapat
```
terintegrasi langsung dengan modul kitchen display system (KDS).
```
```
A) Manajemen Keranjang
```
Manajemen keranjang dimulai ketika kasir memilih produk pada
ProductComponent. Pada saat penambahan item, sistem menghitung harga item
dengan mempertimbangkan penyesuaian harga dari opsi produk
```
(price_adjustment). Apabila kasir sedang mengubah item yang sudah ada (mode
```
```
edit), item lama dihapus terlebih dahulu agar data yang tersimpan pada keranjang
```
merepresentasikan pilihan terbaru. Setelah itu, sistem membangun payload item
```
keranjang menggunakan CartHelper::buildPayload() untuk menempelkan atribut
```
```
penting seperti opsi, catatan, diskon, harga dasar, harga mentah (sebelum diskon),
```
```
dan harga akhir (setelah diskon). Payload yang terbentuk kemudian disimpan ke
```
```
keranjang melalui Cart::add() dan komponen antarmuka diperbarui melalui event
```
cart-updated.
Tabel 4.3 ProductComponent.php.
```
public function addSelectedProductToCart(): void
```
```
{
```
```
if (! $this->selectedProduct) {
```
```
return;
```
```
}
```
```
$price = (float) ($this->selectedProduct->price ?? 0);
```
```
$options = [];
```
```
foreach ($this->selectedOptions as $optionId => $valueId) {
```
```
if ($valueId) {
```
```
$value = ProductOptionValue::find($valueId);
```
```
if ($value) {
```
$options[$optionId] = [
'id' => $value->id,
'value' => $value->value,
```
'price_adjustment' => (float) $value-
```
>price_adjustment,
```
];
```
```
$price += (float) $value->price_adjustment;
```
```
}
```
```
}
```
```
}
```
104
```
if ($this->editingItemId) {
```
```
Cart::remove($this->editingItemId);
```
```
}
```
```
$payload = $this->cartHelper->buildPayload(
```
$this->selectedProduct,
$options,
$this->quantity,
$this->note,
```
$this->resolveTenantId(),
```
$price,
```
(float) ($this->selectedProduct->price ?? 0),
```
$this->discountFetcher
```
);
```
```
Cart::add($payload);
```
```
$this->showOptionModal = false;
```
```
$this->dispatch('unlock-scroll');
```
```
$this->dispatch('pos-close-product-modal');
```
```
$this->reset(['selectedProduct', 'selectedOptions',
```
```
'quantity', 'note', 'editingItemId']);
```
```
$this->dispatch('cart-updated');
```
```
}
```
Dengan mekanisme tersebut, setiap item keranjang memiliki atribut yang
lengkap untuk kebutuhan perhitungan diskon, estimasi waktu, dan penyimpanan ke
order_items pada tahap checkout. Perubahan isi keranjang seperti menaikkan
kuantitas, menurunkan kuantitas, dan menghapus item ditangani oleh
```
CartComponent melalui Cart::update() dan Cart::remove(). Sistem menjaga agar
```
```
kuantitas tidak berada di bawah satu; jika kuantitas diturunkan ketika bernilai satu,
```
item akan dihapus. Setiap perubahan selalu memicu event cart-updated agar
ringkasan transaksi pada UI diperbarui secara real-time. Tabel 4.4 dan Tabel 4.5
memperlihatkan operasi perubahan kuantitas dan penghapusan item pada keranjang
sekaligus mekanisme penguncian kuantitas minimum melalui penghapusan item
ketika kuantitas mencapai satu. Pola ini memastikan perubahan keranjang selalu
konsisten, serta memicu pembaruan ringkasan transaksi pada antarmuka tanpa
memerlukan reload halaman.
105
Tabel 4.4 CartComponent.php.
```
public function increaseItem(int|string $id): void
```
```
{
```
```
Cart::update($id, [
```
'quantity' => ['relative' => true, 'value' => 1],
```
]);
```
```
$this->dispatch('cart-updated');
```
```
}
```
```
public function decreaseItem(int|string $id): void
```
```
{
```
```
$item = Cart::get($id);
```
```
if (! $item) {
```
```
return;
```
```
}
```
```
if ((int) $item->quantity <= 1) {
```
```
Cart::remove($id);
```
```
} else {
```
```
Cart::update($id, [
```
'quantity' => ['relative' => true, 'value' => -1],
```
]);
```
```
}
```
```
$this->dispatch('cart-updated');
```
```
}
```
```
public function removeItem(int|string $id): void
```
```
{
```
```
Cart::remove($id);
```
```
$this->dispatch('cart-updated');
```
```
}
```
Tabel 4.5 CartHelper.php.
```
public function buildPayload(
```
Product $product,
array $options,
int $quantity,
string $note,
?string $tenantId,
?float $rawPrice = null,
?float $basePrice = null,
?DiscountFetcher $discountFetcher = null,
array $extraAttributes = []
```
): array {
```
```
$base = $basePrice ?? (float) ($product->price ?? 0);
```
```
$raw = $rawPrice ?? $this->calculateRawPrice($product,
```
```
$options, $base);
```
```
$pricing = PriceAfterDiscount::calculate($product,
```
```
$tenantId, $raw, $discountFetcher);
```
106
```
$attributes = array_merge([
```
'product_id' => $product->id,
'options' => $options,
'base_price' => $base,
'raw_price' => $raw,
```
'estimated_seconds' => (int) ($product-
```
```
>estimated_seconds ?? 0),
```
'note' => $note,
'discount_id' => $pricing['discount_id'] ?? null,
```
'discount_amount' => (float)
```
```
($pricing['discount_amount'] ?? 0),
```
'discount_name' => $pricing['discount_name'] ?? null,
'discount_badge' => $pricing['badge'] ?? null,
```
'final_price' => (float) ($pricing['final_price'] ??
```
```
$raw),
```
```
], $extraAttributes);
```
return [
```
'id' => CartItemIdentifier::make($product->id,
```
```
$options),
```
```
'name' => (string) $product->name,
```
```
'price' => (float) ($pricing['final_price'] ?? $raw),
```
'quantity' => $quantity,
'attributes' => $attributes,
```
];
```
```
}
```
```
B) Perhitungan Nilai Transaksi
```
Perhitungan nilai transaksi dipusatkan pada helper/service agar angka yang
tampil pada antarmuka konsisten dengan angka yang digunakan saat penyimpanan
```
transaksi. Payload keranjang memuat dua nilai harga, yaitu raw_price (harga
```
```
sebelum diskon) dan final_price (harga setelah diskon). Ringkasan item dihitung
```
```
dengan CartHelper::summarizeItems() untuk menghasilkan subtotal dan total
```
diskon berdasarkan atribut item, kemudian nilai akhir ditetapkan sebagai
```
max(subtotal - discount, 0) agar tidak bernilai negatif. Perhitungan ini menjaga agar
```
komponen antarmuka memperoleh ringkasan nilai transaksi dari satu sumber logika
yang konsisten. Tabel 4.6 menampilkan operasi ringkasan subtotal dan diskon pada
CartHelper.
Tabel 4.6 Ringkasan subtotal dan diskon pada CartHelper.
```
public function summarizeItems(iterable $items): array
```
```
{
```
```
$subtotal = 0.0;
```
107
```
$discount = 0.0;
```
```
foreach ($items as $item) {
```
```
$raw = (float) ($item->attributes['raw_price'] ??
```
```
$item->price);
```
```
$subtotal += $raw * (int) $item->quantity;
```
```
$lineDiscount = (float) ($item-
```
```
>attributes['discount_amount'] ?? 0);
```
```
$discount += $lineDiscount * (int) $item->quantity;
```
```
}
```
```
$final = max($subtotal - $discount, 0);
```
return [
'subtotal' => $subtotal,
'discount' => $discount,
'final' => $final,
```
Penentuan diskon dilakukan melalui PriceAfterDiscount::calculate() dengan
```
memilih diskon terbesar yang berlaku pada produk. Hasilnya disimpan dalam
```
atribut item (discount_id, discount_amount, dan final_price) sehingga harga final
```
pada keranjang merepresentasikan nilai setelah diskon. Setelah subtotal transaksi
ditentukan, sistem menghitung pajak dan grand total menggunakan
```
OrderTaxCalculator::calculate() dengan mengambil daftar pajak aktif tenant dan
```
menghasilkan rincian pajak per baris serta total pajak.
Dalam implementasi checkout, nilai yang digunakan sebagai dasar total transaksi
dihitung dari cartItem->price * quantity, di mana cartItem->price merupakan
```
harga final (setelah diskon). Dengan demikian, pada alur ini pajak dihitung
```
berdasarkan nilai total harga final item pada keranjang. Tabel 4.7 menampilkan
perhitungan harga setelah diskon serta Tabel 4.8 menampikan perhitungan pajak.
Tabel 4.7 PriceAfterDiscount.php.
```
public static function calculate(Product $product, ?string
```
$tenantId, ?float $priceOverride = null, ?DiscountFetcher
```
$discountFetcher = null): array
```
```
{
```
```
$basePrice = $priceOverride ?? (float) $product->price;
```
$result = [
'has_discount' => false,
'discount_id' => null,
'discount_name' => null,
'discount_type' => null,
'discount_amount' => 0.0,
108
```
'final_price' => round($basePrice, 2),
```
'badge' => null,
```
];
```
```
if (! $tenantId || $basePrice <= 0) {
```
```
return $result;
```
```
}
```
$fetcher = $discountFetcher ??
```
app(DiscountFetcher::class);
```
```
$discounts = $fetcher->forTenant($tenantId);
```
```
if ($discounts->isEmpty()) {
```
```
return $result;
```
```
}
```
```
$applicable = $discounts->filter(fn ($discount) =>
```
```
$discount->appliesToProduct($product->id));
```
```
if ($applicable->isEmpty()) {
```
```
return $result;
```
```
}
```
```
$bestDiscount = null;
```
```
$bestAmount = 0.0;
```
```
foreach ($applicable as $discount) {
```
```
$amount = $discount->discountAmountFor($basePrice);
```
```
if ($amount > $bestAmount) {
```
```
$bestAmount = $amount;
```
```
$bestDiscount = $discount;
```
```
}
```
```
}
```
```
if (! $bestDiscount || $bestAmount <= 0) {
```
```
return $result;
```
```
}
```
```
$finalPrice = max($basePrice - $bestAmount, 0);
```
$badge = $bestDiscount->discount_type === 'percent'
```
? '-' . rtrim(rtrim(number_format((float)
```
```
$bestDiscount->value, 2, '.', ''), '0'), '.') . '%'
```
```
: '-Rp' . number_format($bestAmount, 0, ',', '.');
```
return [
'has_discount' => true,
'discount_id' => $bestDiscount->id,
'discount_name' => $bestDiscount->name,
'discount_type' => $bestDiscount->discount_type,
```
'discount_amount' => round($bestAmount, 2),
```
```
'final_price' => round($finalPrice, 2),
```
'badge' => $badge,
```
];
```
```
}}
```
109
Tabel 4.8 OrderTaxCalculator.php.
```
public function calculate(?string $tenantId, float $subtotal):
```
TaxCalculationResult
```
{
```
```
$subtotal = max(0, round($subtotal, 2));
```
```
if (! $tenantId) {
```
```
return new TaxCalculationResult(
```
$subtotal,
0.0,
$subtotal,
```
);
```
```
collect()
```
```
}
```
```
$taxes = Tax::query()
```
```
->where('tenant_id', $tenantId)
```
```
->where('is_active', true)
```
```
->orderBy('id')
```
```
->get();
```
```
$lines = collect();
```
```
$totalTax = 0.0;
```
```
foreach ($taxes as $tax) {
```
```
$rate = (float) $tax->rate;
```
$amount = $tax->type === 'percentage'
```
? round(($rate / 100) * $subtotal, 2)
```
```
: round($rate, 2);
```
```
$lines->push([
```
'tax_id' => $tax->id,
'name' => $tax->name,
'type' => $tax->type,
'rate' => $rate,
'amount' => $amount,
```
]);
```
```
$totalTax += $amount;
```
```
}
```
```
$totalTax = round($totalTax, 2);
```
```
return new TaxCalculationResult(
```
$subtotal,
$totalTax,
```
round($subtotal + $totalTax, 2),
```
$lines
```
);
```
```
}
```
110
```
C) Proses Akhir dan Penyimpanan Transaksi
```
Proses akhir dimulai pada OrderComponent ketika kasir menekan tombol
checkout. Sistem mencegah pemrosesan ulang apabila order yang sedang dibuka
```
sudah berstatus lunas. Selanjutnya sistem menjalankan validatePosOrder() sebagai
```
guard untuk memastikan prasyarat transaksi terpenuhi. Jika transaksi valid, sistem
membuka modal pembayaran dengan melempar event pos-open-payment agar
PaymentComponent memfasilitasi pemilihan metode pembayaran.
```
PaymentComponent kemudian memvalidasi input (khususnya pada metode tunai)
```
dan melempar event pos-process-order yang berisi paymentChannel dan data
pembayaran yang diperlukan. Tabel 4.9 memperlihatkan bahwa checkout selalu
```
melewati guard validatePosOrder() sebelum membuka modal pembayaran, serta
```
memiliki jalur khusus untuk pembaruan order pay_later pada kondisi tertentu serta
Tabel 4.10 menampilkan mekanisme pembayaran dengan uang tunai.
Tabel 4.9 OrderComponent.php
```
public function checkout(): void
```
```
{
```
// Only block re-processing when viewing an existing paid
order
```
if ($this->orderPaid && $this->orderId) {
```
```
$this->dispatch('pos-flash', type: 'warning', message:
```
```
'Order already paid.');
```
```
return;
```
```
}
```
```
if (! $this->validatePosOrder()) {
```
```
return;
```
```
}
```
```
if ($this->isEditingUnpaid && $this->orderId && $this-
```
```
>orderPaymentStatus === 'pay_later') {
```
// Re-validate stock before updating
```
if (! $this->validateStockForCart()) {
```
```
return;
```
```
}
```
```
$this->updatePayLaterOrder();
```
```
return;
```
```
}
```
```
$this->dispatch('pos-open-payment');
```
```
}
```
111
Tabel 4.10 PaymentComponent.php.
```
public function confirmCashPaymentClient(string $amountInput):
```
void
```
{
```
```
$numericString = preg_replace('/\D/', '', $amountInput) ??
```
```
'';
```
```
$amount = $numericString === '' ? 0.0 : (float)
```
```
$numericString;
```
```
$roundedTotal = roundToIndoRupiahTotal($this->totalAmount);
```
```
$this->cashReceivedNumeric = $amount;
```
$this->cashReceivedFormatted = $numericString === ''
? ''
```
: number_format((int) $amount, 0, ',', '.');
```
```
if ($amount < $roundedTotal) {
```
```
$this->dispatch('pos-flash', type: 'warning', message:
```
```
'Received amount is less than total.');
```
```
return;
```
```
}
```
```
$this->dispatch('pos-process-order', paymentChannel:
```
'cash', receivedAmount: $this->cashReceivedNumeric, change:
```
max(0, $this->cashReceivedNumeric - $roundedTotal));
```
```
}
```
Dengan mekanisme ini, sistem memastikan checkout hanya dapat dilanjutkan
ketika prasyarat transaksi telah terpenuhi dan alur pembayaran diarahkan secara
eksplisit ke komponen pembayaran. Pada tahap pembayaran cash, sistem menolak
pembayaran apabila nominal yang diterima lebih kecil dari total yang harus dibayar.
Jika valid, sistem meneruskan proses dengan melempar event pos-process-order
beserta receivedAmount dan change. Untuk metode card dan QRIS, event yang
sama dikirim tanpa nominal tunai.
```
Setelah event diterima, OrderComponent::processOrder() menjalankan
```
```
penyimpanan transaksi di dalam DB::transaction() untuk menjamin konsistensi.
```
Sistem menghitung total transaksi dari keranjang, menghitung pajak melalui
OrderTaxCalculator, lalu membuat atau memperbarui record orders. Selanjutnya
sistem menyimpan item ke order_items dengan menyimpan unit_price sebagai
```
harga mentah (raw_price) dan final_price sebagai harga setelah diskon
```
```
(final_price), serta menyertakan opsi dan catatan. Pada tahap ini sistem juga
```
```
menangani penyesuaian stok, baik untuk order baru maupun edit order (berbasis
```
112
```
selisih kuantitas). Rincian pajak disimpan melalui relasi taxLines, status meja dine-
```
```
in diperbarui menjadi occupied, dan sistem memanggil refreshFulfillmentStatus()
```
agar status pemenuhan order tersinkron. Setelah transaksi berhasil, sistem
menyimpan informasi tunai bila diperlukan, membersihkan keranjang, menyiapkan
```
data struk, mengirim notifikasi operasional (tanpa memblok transaksi bila notifikasi
```
```
gagal), dan me-reset konteks POS melalui event agar antarmuka siap untuk
```
transaksi berikutnya.
```
D) Transisi Status untuk Integrasi KDS
```
Integrasi POS dengan KDS dilakukan melalui status pemenuhan order
```
(fulfillment_status) yang bersifat turunan dari status item. Setelah order berhasil
```
```
diproses, sistem memanggil Order::refreshFulfillmentStatus() untuk menghitung
```
status turunan berdasarkan status item yang terkait. Jika terjadi perubahan status
atau perbedaan timestamp siklus hidup, pesanan akan diperbarui sehingga status
pemenuhan yang tersimpan merepresentasikan kondisi terbaru. Tabel 4.11
menunjukkan mekanisme penyegaran status pemenuhan pesanan, mulai dari
pengambilan item, perhitungan status turunan, hingga pembaruan jika terdapat
perubahan.
Tabel 4.11 Penyegaran status pemenuhan pada Order.php.
```
public function refreshFulfillmentStatus(): string
```
```
{
```
```
$items = $this->relationLoaded('items')
```
? $this->items
```
: $this->items()->select('id', 'status', 'ready_at',
```
```
'served_at', 'created_at', 'updated_at')->get();
```
```
$derived = self::deriveFulfillmentStatusFromItems($items);
```
```
$updates = $this->buildFulfillmentTimestamps($derived,
```
```
$items);
```
```
if ($this->fulfillment_status !== $derived || $this-
```
```
>hasLifecycleDrift($updates)) {
```
```
$this->forceFill($updates)->save();
```
```
}
```
```
return $derived;
```
```
}
```
113
Dengan pola status turunan ini, KDS dapat menampilkan pesanan yang aktif
diproses berdasarkan status pemenuhan tanpa memerlukan masukan ulang dari
kasir. Pada modul KDS, daftar pesanan dapat ditampilkan berdasarkan status
```
pembayaran (misalnya paid atau pay_later) serta status pemenuhan aktif.
```
Perubahan status dari sisi dapur pada level item akan memengaruhi status turunan
order setelah mekanisme penyegaran status dijalankan, sehingga status yang terlihat
pada KDS dan status yang tersimpan pada basis data tetap konsisten.
```
E) Validasi Input Pesanan
```
Validasi diterapkan secara berlapis untuk mencegah transaksi tidak valid
sebelum data disimpan. Guard utama pada tahap checkout adalah
```
validatePosOrder(), yang menolak proses apabila keranjang kosong, customer
```
belum dipilih, atau meja dine-in belum ditentukan. Penolakan dilakukan dengan
menampilkan pesan peringatan dan menghentikan proses checkout. Tabel 4.12
menampilkan aturan validasi minimal yang harus terpenuhi sebelum transaksi dapat
dilanjutkan ke tahap pembayaran.
Tabel 4.12 Guard validasi checkout pada OrderComponent.php.
```
private function validatePosOrder(): bool
```
```
{
```
```
if ($this->cartIsEmpty()) {
```
```
$this->dispatch('pos-flash', type: 'warning', message:
```
```
'Cart is empty.');
```
```
return false;
```
```
}
```
```
if (! $this->customerId) {
```
```
$this->dispatch('pos-flash', type: 'warning', message:
```
```
'Select a customer first.');
```
```
return false;
```
```
}
```
```
if ($this->orderType === 'dine-in' && ! $this->tableId) {
```
```
$this->dispatch('pos-flash', type: 'warning', message:
```
```
'Please pick a table for dine-in.');
```
```
return false;
```
```
}
```
```
return true;
```
```
}
```
114
Validasi ini memastikan transaksi tidak dapat diproses apabila informasi dasar
transaksi belum lengkap. Validasi ketersediaan stok dilakukan melalui
```
validateStockForCart(). Fungsi ini membandingkan kuantitas item pada keranjang
```
dengan stok aktual. Pada mode edit order, sistem memperhitungkan kuantitas awal
order sebagai stok yang “tersedia” agar penyesuaian tidak menyebabkan
perhitungan ganda. Jika stok tidak mencukupi atau produk tidak ditemukan, sistem
menampilkan peringatan dan membatalkan proses. Tabel 4.13 menunjukkan proses
validasi stok yang membandingkan kuantitas diminta pada keranjang dengan stok
produk, serta mekanisme penyesuaian stok tersedia saat mode edit order.
Tabel 4.13 Validasi stok keranjang pada OrderComponent.php
```
private function validateStockForCart(): bool
```
```
{
```
```
$cartItems = Cart::getContent();
```
```
$tenantId = $this->resolveTenantId();
```
```
$insufficient = [];
```
```
foreach ($cartItems as $item) {
```
```
$productId = CartItemIdentifier::extractProductId($item)
```
```
?? $item->id;
```
```
$product = Product::when($tenantId, fn ($q) => $q-
```
```
>where('tenant_id', $tenantId))->find($productId);
```
```
if (! $product) {
```
```
$insufficient[] = "{$item->name} is unavailable.";
```
```
continue;
```
```
}
```
```
$requested = (int) $item->quantity;
```
```
$available = (int) $product->stock_qty;
```
```
if ($this->orderId && isset($this-
```
```
>originalProductQuantities[$productId])) {
```
$available += $this-
```
>originalProductQuantities[$productId];
```
```
}
```
```
if ($requested > $available) {
```
```
$insufficient[] = "{$item->name} only has
```
```
{$available} left in stock.";
```
```
}
```
```
}
```
```
if (! empty($insufficient)) {
```
```
$this->dispatch('pos-flash', type: 'warning', message:
```
```
implode(' ', $insufficient));
```
```
return false;
```
```
}
```
```
return true;}
```
115
Dengan validasi stok ini, sistem mencegah terjadinya transaksi yang melampaui
stok tersedia serta menjaga konsistensi stok ketika order sedang diperbarui. Selain
validasi checkout dan stok, PaymentComponent juga melakukan validasi untuk
pembayaran cash dengan memastikan nominal yang diterima tidak lebih kecil dari
total transaksi. Dengan validasi berlapis pada level keranjang, checkout, stok, dan
pembayaran, modul POS menjaga integritas transaksi sebelum data disimpan ke
basis data dan diteruskan ke modul lain seperti KDS.
```
4.5 Implementasi Modul Kitchen Display System (KDS)
```
KDS menyediakan tampilan daftar pesanan yang perlu diproses dapur dan
memungkinkan dapur memperbarui status pesanan selama proses produksi. Data
yang ditampilkan pada KDS di-filter agar hanya menampilkan pesanan yang
relevan untuk diproses, serta dilindungi oleh otorisasi peran kitchen. Perubahan
status pada KDS menjadi dasar sinkronisasi status yang akan dikonsumsi oleh
waiter dan customer tracking. Pada bagian ini, akan dibahas mengenai antarmuka
KDS dan logika bisnis KDS yang mencakup kondisi awal pesanan dari POS,
pemilihan data yang ditampilkan pada KDS, aksi perubahan status pada KDS dan
kontrol akses, peran service pemenuhan dan sinkronisasi pesanan, serta items
boards dan ringkasan kinerja harian.
4.5.1 Antarmuka KDS
```
Halaman antarmuka dari kitchen display system (KDS) disajikan pada
```
Gambar 4.20. Pada Gambar 4.20, ditampilkan kondisi awal KDS ketika belum ada
pesanan yang masuk atau seluruh pesanan telah selesai diproses. Tampilan ini
menjadi indikator bahwa dapur sedang tidak memiliki antrian produksi aktif pada
tenant yang sedang digunakan.
116
```
Gambar 4.20 Halaman KDS – kondisi awal (tanpa pesanan aktif).
```
Pada Gambar 4.21, ditunjukkan pesanan yang baru diterima dan masuk ke tahap
confirmed. Pada tahap ini pesanan sudah tercatat dan siap untuk mulai diproses oleh
```
dapur, namun belum masuk tahap produksi (preparing).
```
Gambar 4.21 Halaman KDS saat pesanan masuk.
Pada Gambar 4.22 memperlihatkan aksi dapur untuk memulai pengerjaan
pesanan dengan memindahkan status dari confirmed ke preparing. Perubahan status
ini menandakan bahwa pesanan telah masuk proses produksi dan statusnya akan
tersinkron ke modul lain.
117
Gambar 4.22 Halaman KDS menampilkan order dengan status preparing.
Pada Gambar 4.23 menunjukkan pembaruan status pada level item, yaitu
ketika item tertentu pada pesanan sudah selesai dibuat dan ditandai sebagai ready.
Pembaruan per item membantu dapur dan pelayan mengetahui progres penyelesaian
pesanan secara lebih detail.
Gambar 4.23 Halaman KDS ketika status item berubah menjadi ready.
Pada Gambar 4.24 menampilkan aksi untuk menandai seluruh item dalam
satu pesanan sebagai ready sekaligus. Fitur ini mempercepat operasional ketika
semua item pada pesanan selesai bersamaan, serta memastikan status pesanan siap
diserahkan ke pelayan untuk tahap penyajian.
118
Gambar 4.24 Notifikasi mark order ready untuk seluruh item pada satu pesanan.
Pada Gambar 4.25 memperlihatkan items board sebagai ringkasan agregasi
item yang masih aktif diproses, yaitu gabungan item pada status confirmed dan
preparing. Tampilan rekap ini membantu dapur memantau beban kerja dan prioritas
produksi tanpa perlu membuka detail masing-masing pesanan.
Gambar 4.25 Halaman items board.
Pada Gambar 4.26 menampilkan ringkasan performa kitchen pada hari
berjalan, seperti jumlah pesanan yang telah selesai diproses serta indikator
ketepatan waktu penyelesaian. Informasi ini dapat digunakan sebagai evaluasi
operasional untuk melihat konsistensi layanan dapur selama jam operasional.
119
Gambar 4.26 Ringkasan kinerja kitchen hari ini.
4.5.2 Logika Bisnis KDS
Logika bisnis pada KDS untuk perubahan status menerapkan pola Livewire
```
server-side. Data pesanan ditarik dari basis data melalui kueri di metode render(),
```
```
sementara perubahan status dilakukan melalui metode aksi (misalnya Start
```
```
Preparing dan Mark Ready) yang memanggil service pemenuhan pesanan.
```
Pendekatan ini menjaga agar status yang tampil di KDS merupakan kondisi aktual
dari basis data dan perubahan status tercatat konsisten.
```
A) Kondisi Awal Pesanan dari POS
```
Pesanan dibuat melalui POS dan item disimpan pada order_items dengan status
```
awal queued (default migrasi). Setelah pembayaran berhasil, POS memanggil
```
mekanisme penyegaran status pemenuhan pada model Order sehingga level
```
order memiliki fulfillment_status awal (misalnya confirmed) sebagai status
```
turunan dari status item. Dengan demikian, KDS cukup membaca order yang
memenuhi kriteria tanpa membuat data baru.
```
B) Pemilihan Data yang ditampilkan pada KDS
```
KDS hanya memuat pesanan yang relevan untuk operasional dapur, yaitu
```
pesanan dengan order_type aktif (dine-in/takeaway), payment_status
```
paid/pay_later, serta status pemenuhan pada fase aktif KDS
```
(confirmed/preparing/ready). Selain itu tersedia fitur pencarian berdasarkan id
```
atau reference_no, dan filter tenant agar data yang muncul sesuai konteks tenant
yang sedang digunakan. Tabel 4.14 menunjukkan kueri utama yang digunakan
KDS untuk memuat order, menerapkan filter pencarian, lalu membagi hasil
menjadi tiga kelompok tampilan: confirmed, preparing, dan ready.
120
```
Tabel 4.14 Kueri dan pemetaan tampilan KDS pada method render().
```
```
public function render()
```
```
{
```
```
$activeOrderTypes = ['dine-in', 'takeaway'];
```
```
$tenantId = function_exists('tenant') ? tenant('id') : null;
```
```
$term = trim($this->search);
```
```
$base = Order::with(['items.product.options',
```
```
'customerDetail', 'diningTable'])
```
```
->latest()
```
```
->whereIn('order_type', $activeOrderTypes)
```
```
->whereIn('payment_status', ['paid', 'pay_later'])
```
```
->when($tenantId, fn ($q) => $q->where('tenant_id',
```
```
$tenantId));
```
```
if ($term !== '') {
```
```
$like = "%{$term}%";
```
```
$base->where(function ($q) use ($like) {
```
```
$q->where('id', 'like', $like)
```
```
->orWhere('reference_no', 'like', $like);
```
```
});
```
```
}
```
```
$confirmedOrders = (clone $base)
```
```
->where('fulfillment_status',
```
```
Order::FULFILLMENT_CONFIRMED)
```
```
->get();
```
```
$preparingOrders = (clone $base)
```
```
->where('fulfillment_status',
```
```
Order::FULFILLMENT_PREPARING)
```
```
->get();
```
```
$doneOrders = (clone $base)
```
```
->where('fulfillment_status', Order::FULFILLMENT_READY)
```
```
->orderByDesc('ready_at')
```
```
->orderByDesc('created_at')
```
```
->get();
```
```
return view('livewire.backoffice.kitchen-orders', [
```
'confirmedOrders' => $confirmedOrders,
'preparingOrders' => $preparingOrders,
'doneOrders' => $doneOrders,
```
]);
```
```
}
```
Pemisahan hasil kueri menjadi tiga kelompok status ini membuat KDS
bekerja sebagai papan antrean terstruktur, di mana staf dapur dapat fokus pada
pesanan yang “menunggu dimulai”, “sedang dikerjakan”, dan “siap”.
121
```
C) Aksi Perubahan Status pada KDS dan Kontrol Akses
```
KDS menyediakan aksi perubahan status yang dijalankan di server-side melalui
metode pada komponen Livewire KitchenOrders. Aksi-aksi ini dibatasi
menggunakan permission agar hanya staf dapur yang berwenang dapat
```
mengubah status, yaitu kitchen_kds_update_order untuk memulai proses (Start
```
```
Preparing) dan kitchen_kds_confirm_order untuk menandai siap (Mark Ready).
```
```
Setiap aksi memuat ulang data terbaru dengan memanggil dispatch('$refresh')
```
sehingga tampilan KDS langsung mencerminkan status terbaru setelah operasi
selesai. Tabel 4.15 memperlihatkan contoh aksi “mulai mengerjakan” untuk item
tertentu, mulai dari pengecekan hak akses, pengambilan item beserta relasi order
```
(dengan filter tenant), pemanggilan service pemenuhan, hingga pemuatan ulang
```
tampilan.
Tabel 4.15 Aksi start preparing item pada KitchenOrders.
```
public function startPreparingItem(int $itemId): void
```
```
{
```
```
if (! auth()->user()?->can('kitchen_kds_update_order')) {
```
```
return;
```
```
}
```
```
$tenantId = function_exists('tenant') ? tenant('id') : null;
```
```
$item = OrderItem::with('order')
```
```
->when($tenantId, fn ($q) => $q->where('tenant_id',
```
```
$tenantId))
```
```
->find($itemId);
```
```
if (! $item || ! $item->order) {
```
```
return;
```
```
}
```
```
$this->fulfillment->startPreparing($item->order, [$item-
```
```
>id]);
```
```
$this->dispatch('$refresh');
```
```
}
```
```
Dengan pola ini, perubahan status dilakukan secara aman (berdasarkan hak
```
```
akses), konsisten (melalui service), dan segera tercermin di antarmuka (melalui
```
```
refresh Livewire). Selain per item, KDS juga menyediakan aksi pada level order
```
```
(misalnya memulai seluruh order atau menandai seluruh item dalam order
```
```
menjadi ready). Hal ini mengurangi pekerjaan manual ketika satu pesanan berisi
```
122
banyak item. Tabel 4.16 menunjukkan aksi untuk level order, yaitu memulai
proses order dan menandai seluruh item dalam order menjadi ready, termasuk
pemuatan relasi items dan pemuatan tampilan. Aksi pada level order
mempercepat operasional dapur, terutama saat seluruh item dalam satu pesanan
diproses bersamaan dan siap diselesaikan secara kolektif.
Tabel 4.16 Aksi preparing/ready pada level order.
```
public function startOrderPreparing(int $orderId): void
```
```
{
```
```
if (! auth()->user()?->can('kitchen_kds_update_order')) {
```
```
return;
```
```
}
```
```
$tenantId = function_exists('tenant') ? tenant('id') : null;
```
```
$order = Order::with('items')
```
```
->when($tenantId, fn ($q) => $q->where('tenant_id',
```
```
$tenantId))
```
```
->find($orderId);
```
```
if (! $order) {
```
```
return;
```
```
}
```
```
$this->fulfillment->startPreparing($order, null);
```
```
$this->dispatch('$refresh');
```
```
}
```
```
public function markOrderReady(int $orderId): void
```
```
{
```
```
if (! auth()->user()?->can('kitchen_kds_confirm_order')) {
```
```
return;
```
```
}
```
```
$tenantId = function_exists('tenant') ? tenant('id') : null;
```
```
$order = Order::with('items')
```
```
->when($tenantId, fn ($q) => $q->where('tenant_id',
```
```
$tenantId))
```
```
->find($orderId);
```
```
if (! $order) {
```
```
return;
```
```
}
```
```
$this->fulfillment->markAllReady($order);
```
```
$this->dispatch('$refresh');
```
```
}
```
123
```
D) Peran Service Pemenuhan dan Sinkronisasi Pesanan
```
Untuk menjaga konsistensi, KDS tidak mengubah status item secara langsung di
komponen UI, melainkan meneruskannya ke OrderFulfillmentService. Service
ini bertanggung jawab mengubah status order_items.status mengikuti aturan
```
transisi (misalnya queued → preparing → ready), menyetel timestamp relevan
```
```
(misalnya ready_at saat ready), dan memanggil refreshFulfillmentStatus() agar
```
orders.fulfillment_status selalu mengikuti agregasi status item. Dengan
demikian, status pada level order tidak menjadi sumber kebenaran utama,
melainkan proyeksi yang diturunkan dari status item.
Ringkasan transisi status pada KDS:
• POS membuat item: order_items.status = queued, kemudian order
diturunkan menjadi fulfillment_status = confirmed.
• Dapur menekan Start Preparing: item berubah ke preparing → order
turunannya menjadi preparing.
```
• Dapur menekan Mark Ready (per item/per order): item menjadi ready →
```
order turunannya menjadi ready.
```
• Jika modul lain melakukan aksi Serve (misalnya waiter/runner), item
```
menjadi served dan order menjadi served ketika seluruh item telah served.
```
E) Items Board dan Ringkasan Kinerja Harian
```
```
Items board, yaitu agregasi item dari pesanan aktif (gabungan Confirmed +
```
```
Preparing). Agregasi dilakukan berdasarkan kombinasi nama produk + opsi
```
```
terpilih, sehingga variasi item tetap terlihat (misalnya level gula atau ukuran).
```
Item dengan status cancelled atau served tidak ditampilkan pada papan ini. Hasil
agregasi diurutkan dari kuantitas terbesar agar dapur dapat mengenali item
paling dominan yang sedang berjalan.
Ringkasan kinerja hari ini, dihitung dari pesanan yang berstatus ready pada
tanggal berjalan. Sistem membandingkan expected_seconds_total dengan waktu
```
proses aktual (total_seconds atau fallback computed_total_seconds) untuk
```
```
menandai kategori tepat waktu, terlambat peringatan (lebih dari +300 detik atau
```
```
5 menit), dan terlambat tinggi (lebih dari +600 detik atau 10 menit). Ringkasan
```
124
ini membantu evaluasi kinerja operasional dapur. Tabel 4.17 menunjukkan cara
KDS membangun items board dengan agregasi berdasarkan produk dan opsi,
serta mengabaikan item yang sudah served atau cancelled. Kode ini
menghasilkan papan ringkas “apa yang sedang dimasak” dan membantu dapur
menyusun prioritas produksi berdasarkan volume item.
Tabel 4.17 Kode items board pada KitchenOrders.
```
protected function buildItemsBoard($orders): array
```
```
{
```
```
$acc = [];
```
```
foreach ($orders as $order) {
```
```
foreach ($order->items as $it) {
```
```
if (in_array($it->status,
```
```
[OrderItem::STATUS_CANCELLED, OrderItem::STATUS_SERVED], true))
```
```
{
```
```
continue;
```
```
}
```
```
$pairs = $this->extractOptionPairs($it);
```
```
$optionsDisplay = implode(', ', $pairs);
```
```
$key = $it->product_name . '|' . $optionsDisplay;
```
```
if (!isset($acc[$key])) {
```
$acc[$key] = [
```
'name' => (string) $it->product_name,
```
'options' => $optionsDisplay,
'qty' => 0,
```
];
```
```
}
```
```
$acc[$key]['qty'] += (int) $it->quantity;
```
```
}
```
```
}
```
```
$result = array_values($acc);
```
```
usort($result, fn($a, $b) => $b['qty'] <=> $a['qty']);
```
```
return $result;
```
```
}
```
4.6 Implementasi Modul Waiter Orders
Modul Waiter Orders digunakan oleh pelayan untuk memantau pesanan
yang telah selesai disiapkan oleh dapur dan siap diantarkan ke pelanggan. Berbeda
dari KDS yang berfokus pada proses produksi, Waiter Orders berfokus pada tahap
```
layanan akhir (serving), yaitu memindahkan status item/pesanan dari ready menjadi
```
served setelah pesanan diterima pelanggan. Dengan demikian, modul ini menjadi
```
penghubung antara keluaran dapur (ready) dan penyelesaian layanan pelanggan
```
```
(served), sekaligus menjaga status pemenuhan pada sistem tetap konsisten.
```
125
4.6.1 Antarmuka Halaman Waiter Orders
Halaman Waiter Orders menampilkan daftar pesanan yang siap diantar
dengan status ready. Informasi pada halaman ini dirancang ringkas agar pelayan
dapat mengambil keputusan secara cepat ketika volume pesanan meningkat. Daftar
pesanan umumnya disajikan dalam bentuk tabel/kartu yang menampilkan informasi
```
inti seperti nomor meja (untuk dine-in), ringkasan item, dan waktu pesanan
```
```
dinyatakan siap (ready time).
```
Selain daftar “siap antar”, halaman ini juga dapat menampilkan bagian
```
“dalam proses” (misalnya pesanan yang masih confirmed atau preparing) untuk
```
memberi konteks bagi pelayan tentang antrean yang akan segera menyusul. Pada
```
tiap pesanan tersedia aksi untuk membuka detail dan melakukan penyajian (serve)
```
```
per item maupun sekaligus (serve all) sesuai kebutuhan operasional. Pada Gambar
```
4.27 menunjukkan tampilan utama waiter orders yang mem-filter dan
memprioritaskan pesanan dengan status ready. Tampilan ringkas ini membantu
pelayan menentukan urutan pengantaran berdasarkan informasi meja, ringkasan
item, serta waktu pesanan siap.
Gambar 4.27 Halaman waiter orders menampilkan pesanan ready to serve.
Pada Gambar 4.28 menampilkan detail satu pesanan, termasuk daftar item
dan statusnya. Pada tahap ini pelayan dapat melakukan verifikasi item yang siap
disajikan serta memastikan pesanan diantar ke meja yang benar sebelum melakukan
aksi penyajian.
126
Gambar 4.28 Detail pesanan pada waiter orders.
Pada Gambar 4.29 memperlihatkan aksi Serve All yang digunakan untuk menandai
seluruh item berstatus ready dalam satu pesanan sebagai telah disajikan. Fitur ini
mempercepat operasional ketika pesanan diantar sekaligus dan membantu
sinkronisasi status penyajian pada sistem.
Gambar 4.29 Aksi serve all ready items.
Pada Gambar 4.30 menampilkan rekap kinerja pelayan pada hari berjalan, seperti
jumlah pesanan/item yang telah disajikan serta indikator aktivitas pelayanan.
Informasi ini berguna untuk pemantauan operasional dan evaluasi performa layanan
pada jam operasional tertentu.
127
Gambar 4.30 Detail rekap harian kinerja waiter.
4.6.2 Logika Bisnis Waiter Orders
Logika Waiter Orders diimplementasikan menggunakan komponen
Livewire WaiterOrdersPage. Data pesanan dimuat dari basis data dan dipetakan
menjadi struktur array yang lebih mudah ditampilkan di antarmuka. Aksi penyajian
oleh pelayan dijalankan di server-side dengan otorisasi Gate, kemudian diteruskan
ke OrderFulfillmentService untuk mengubah status item menjadi served, mencatat
waktu layanan, dan menyegarkan fulfillment_status pada level order sebagai status
turunan dari agregasi item.
```
A) Pemuatan Status Order
```
```
Metode refreshOrders() membangun kueri dasar order dengan kriteria
```
```
operasional: order_type dine-in/takeaway dan payment_status paid/pay_later.
```
Kueri kemudian ditambah filter tenant bila ada, serta filter pencarian yang
mencakup reference_no, id, dan data pelanggan melalui relasi customerDetail.
```
Setelah itu data dipisahkan menjadi dua kelompok: Ready to Serve (order yang
```
```
memiliki item status ready) dan queued (order dengan fulfillment_status
```
```
confirmed). Hasil kueri dipetakan menjadi array menggunakan mapOrder().
```
Untuk daftar Ready to Serve, pemetaan menggunakan onlyReadyItems = true
agar item yang ditampilkan hanya item ready sehingga tampilan lebih ringkas.
Tabel 4.18 memperlihatkan pembentukan kueri, filter tenant dan pencarian,
pemisahan ready dan preparing, serta pemetaan hasil ke struktur tampilan.
```
Tabel 4.18 Pemuatan dan pemetaan daftar pesanan pada refreshOrders().
```
```
public function refreshOrders(): void
```
```
{
```
```
$tenantId = $this->resolveTenantId();
```
```
$term = trim($this->orderSearch);
```
```
$basekueri = Order::kueri()
```
```
->with([
```
'items:id,order_id,product_name,quantity,options,
special_instructions,status',
128
'customerDetail:id,name,email',
'diningTable:id,label',
```
])
```
```
->whereIn('order_type', ['dine-in', 'takeaway'])
```
```
->whereIn('payment_status', ['paid', 'pay_later']);
```
```
if ($tenantId !== null) {
```
```
$basekueri->where('tenant_id', $tenantId);
```
```
}
```
```
if ($term !== '') {
```
```
$like = '%' . $term . '%';
```
```
$basekueri->where(function ($q) use ($like) {
```
```
$q->where('reference_no', 'like', $like)
```
```
->orWhere('id', 'like', $like)
```
```
->orWhereHas('customerDetail', function ($cq) use
```
```
($like) {
```
```
$cq->where('name', 'like', $like)
```
```
->orWhere('email', 'like', $like);
```
```
});
```
```
});
```
```
}
```
```
$ready = (clone $basekueri)
```
```
->whereHas('items', fn ($q) => $q->where('status',
```
```
OrderItem::STATUS_READY))
```
```
->orderByDesc('ready_at')
```
```
->orderByDesc('created_at')
```
```
->limit(50)
```
```
->get();
```
```
$inProgress = (clone $basekueri)
```
```
->whereIn('fulfillment_status',
```
```
[Order::FULFILLMENT_CONFIRMED, Order::FULFILLMENT_PREPARING])
```
```
->orderByDesc('created_at')
```
```
->limit(50)
```
```
->get();
```
```
$this->readyOrders = $ready->map(fn (Order $order) => $this-
```
```
>mapOrder($order, true))->all();
```
```
$this->inProgressOrders = $inProgress->map(fn (Order $order)
```
```
=> $this->mapOrder($order))->all();
```
... // perhitungan stats
```
}
```
Daftar Ready to Serve yang ditentukan oleh keberadaan item ready, item atau
order akan membuat otomatis keluar dari daftar setelah statusnya diubah
menjadi served oleh waiter pada refresh berikutnya.
129
```
B) Statistik Operasional pada Waiter Orders
```
Komponen juga menghitung statistik ringkas untuk membantu pemantauan
```
operasional: jumlah item ready, jumlah order preparing, jumlah order dalam
```
```
antrean (confirmed), dan jumlah item served pada hari berjalan. Statistik dihitung
```
dengan filter order_type dan payment_status yang sama, serta
mempertimbangkan tenant. Tabel 4.19 menunjukkan cara sistem menghitung
```
ready (jumlah item siap disaji), served (jumlah item disaji hari ini), serta
```
preparing dan queue berdasarkan fulfillment_status pada order. Validasi
pendingCount mencegah penyajian dilakukan ketika masih ada item yang belum
siap, sehingga alur layanan tetap sesuai urutan produksi di dapur.
Tabel 4.19 Perhitungan statistik waiter orders.
```
$statsBase = Order::kueri()
```
```
->whereIn('order_type', ['dine-in', 'takeaway'])
```
```
->whereIn('payment_status', ['paid', 'pay_later'])
```
```
->when($tenantId, fn ($q) => $q->where('tenant_id',
```
```
$tenantId));
```
```
$readyItemsCount = OrderItem::kueri()
```
```
->where('status', OrderItem::STATUS_READY)
```
```
->when($tenantId, fn ($q) => $q->where('tenant_id',
```
```
$tenantId))
```
```
->whereHas('order', fn ($q) => $q
```
```
->whereIn('order_type', ['dine-in', 'takeaway'])
```
```
->whereIn('payment_status', ['paid', 'pay_later'])
```
```
)
```
```
->count();
```
```
$servedToday = OrderItem::kueri()
```
```
->where('status', OrderItem::STATUS_SERVED)
```
```
->when($tenantId, fn ($q) => $q->where('tenant_id',
```
```
$tenantId))
```
```
->whereDate('served_at', now()->toDateString())
```
```
->whereHas('order', fn ($q) => $q
```
```
->whereIn('order_type', ['dine-in', 'takeaway'])
```
```
->whereIn('payment_status', ['paid', 'pay_later'])
```
```
)
```
```
->count();
```
$this->stats = [
'ready' => $readyItemsCount,
```
'preparing' => (clone $statsBase)-
```
```
>where('fulfillment_status', Order::FULFILLMENT_PREPARING)-
```
```
>count(),
```
```
'queue' => (clone $statsBase)->where('fulfillment_status',
```
```
Order::FULFILLMENT_CONFIRMED)->count(),
```
130
'served' => $servedToday,
```
];
```
```
C) Aksi Serve All Ready Items pada Satu Order
```
```
Aksi markAsServed(orderId) digunakan untuk menyajikan seluruh item yang
```
ready dalam satu order. Sistem menerapkan
```
Gate::authorize(waiter_serve_order) agar hanya pengguna berwenang yang
```
dapat melakukan aksi. Setelah order dimuat, sistem menghitung daftar item
```
ready (readyIds) dan menghitung pendingCount, yaitu jumlah item yang
```
statusnya belum termasuk ready/served/cancelled. Jika pendingCount masih ada
atau tidak ada item ready, sistem menolak aksi dan menampilkan pesan bahwa
semua item harus ready sebelum order disajikan. Jika valid, sistem memanggil
```
OrderFulfillmentService::markServed(order, readyIds), menampilkan pesan
```
```
sukses, lalu memanggil refreshOrders() agar daftar langsung diperbarui. Tabel
```
4.20 menunjukkan implementasi markAsServed beserta validasi item pending
sebelum melakukan penyajian kolektif. Aksi penyajian tidak dapat dilakukan
pada item yang belum siap karena item diambil dengan filter status ready,
sehingga menjaga integritas proses layanan.
Tabel 4.20 Aksi markAsServed pada halaman waiter order.
```
public function markAsServed(int $orderId): void
```
```
{
```
```
Gate::authorize('waiter_serve_order');
```
```
$tenantId = $this->resolveTenantId();
```
```
$order = Order::with('items')
```
```
->when($tenantId, fn ($q) => $q->where('tenant_id',
```
```
$tenantId))
```
```
->find($orderId);
```
```
if (! $order) {
```
```
return;
```
```
}
```
```
$readyIds = $order->items->where('status',
```
```
OrderItem::STATUS_READY)->pluck('id')->all();
```
$pendingCount = $order->items
```
->filter(fn ($item) => ! in_array($item->status,
```
[OrderItem::STATUS_READY, OrderItem::STATUS_SERVED,
```
OrderItem::STATUS_CANCELLED], true))
```
131
```
->count();
```
```
if ($pendingCount > 0
```
```
D) Aksi Serve per Item
```
```
Aksi serveItem(itemId) digunakan untuk menyajikan satu item tertentu. Aksi ini
```
```
juga melewati Gate::authorize(waiter_serve_order). Item diambil dengan syarat
```
```
status = ready agar aksi tidak bisa diterapkan pada item yang belum siap. Jika
```
item dan order valid, sistem memanggil
```
OrderFulfillmentService::markServed(order, [itemId]), menampilkan pesan
```
```
sukses, dan memanggil refreshOrders() agar daftar selalu bersih dari item yang
```
sudah served. Tabel 4.21 menunjukkan implementasi serveItem yang hanya
menerima item ready, lalu meneruskan perubahan status ke service. Aksi
penyajian tidak dapat dilakukan pada item yang belum siap karena item diambil
dengan filter status ready, sehingga menjaga integritas proses layanan.
Tabel 4.21 Aksi serveItem pada WaiterOrdersPage.
```
public function serveItem(int $itemId): void
```
```
{
```
```
Gate::authorize('waiter_serve_order');
```
```
$tenantId = $this->resolveTenantId();
```
```
$item = OrderItem::with('order')
```
```
->where('status', OrderItem::STATUS_READY)
```
```
->when($tenantId, fn ($q) => $q->where('tenant_id',
```
```
$tenantId))
```
```
->find($itemId);
```
```
if (! $item || ! $item->order) {
```
```
return;
```
```
}
```
```
$this->fulfillment->markServed($item->order, [$item->id]);
```
```
session()->flash('message', 'Item served.');
```
```
$this->refreshOrders();
```
```
}
```
```
E) Perubahan Status Served pada Service Layer
```
Perubahan status pada Waiter Orders dipusatkan pada OrderFulfillmentService.
Ketika pelayan melakukan serve, metode markServed memperbarui
132
order_items.status dari ready menjadi served, mengisi served_at dengan waktu
saat ini, dan memastikan ready_at terisi menggunakan COALESCE. Setelah
perubahan item disimpan, service memanggil Order::refreshFulfillmentStatus
agar fulfillment_status pada level order selalu mengikuti agregasi status item.
Dengan pendekatan ini, status order bukan sumber kebenaran yang diubah
manual, melainkan proyeksi dari status item. Tabel 4.22 menunjukkan
mekanisme markServed yang digunakan pelayan, serta keterkaitannya dengan
startPreparing dan markReady sebagai sumber status ready dari modul dapur.
Tabel 4.22 Perubahan status pada OrderFulfillmentService.
```
public function markServed(Order $order, array $itemIds): void
```
```
{
```
```
if (empty($itemIds)) {
```
```
return;
```
```
}
```
```
$this->itemsQuery($order, $itemIds)
```
```
->where('status', OrderItem::STATUS_READY)
```
```
->where('status', '!=', OrderItem::STATUS_CANCELLED)
```
```
->update([
```
'status' => OrderItem::STATUS_SERVED,
```
'served_at' => now(),
```
```
'ready_at' => DB::raw('COALESCE(ready_at,
```
```
CURRENT_TIMESTAMP)'),
```
```
]);
```
```
$order->refreshFulfillmentStatus();
```
```
}
```
Dengan pemusatan perubahan status pada service dan penyegaran status order
setelah update, konsistensi status pemenuhan terjaga untuk seluruh modul yang
membaca data order dan item.
```
F) Integrasi KDS → Waiter → Customer
```
Integrasi antarmodul terjadi melalui status item pada order_items dan status
turunan pada level orders.fulfillment_status. Setelah POS membuat pesanan dan
pesanan masuk ke alur produksi, KDS memperbarui item dari queued/preparing
```
menjadi ready melalui OrderFulfillmentService::markReady(). Perubahan ini
```
```
akan terbaca pada Waiter Orders ketika komponen memanggil refreshOrders(),
```
133
karena daftar Ready to Serve dibentuk dari order yang memiliki minimal satu
item berstatus ready.
Pada tahap layanan, pelayan melakukan penyajian melalui aksi serveItem atau
markAsServed. Kedua aksi tersebut memanggil
```
OrderFulfillmentService::markServed() untuk mengubah item ready → served
```
serta mencatat served_at. Setelah itu sistem memanggil
```
Order::refreshFulfillmentStatus() sehingga status pemenuhan pesanan ikut
```
tersinkron mengikuti agregasi status item. Karena mem-filter daftar “Ready to
Serve” berdasarkan item ready, maka item yang telah memiliki status served
otomatis keluar dari daftar pada pemuatan ulang berikutnya.
Tahap terakhir dalam siklus layanan adalah pelanggan sebagai penerima
keluaran layanan. Dalam konteks ini, pelanggan bukan aktor yang mengubah
status, tetapi menjadi pihak yang menerima hasil akhir proses: pesanan telah
disajikan dan status item/pesanan berubah menjadi served. Dengan adanya
pemisahan daftar “In Progress” dan “Ready to Serve”, pelayan dapat
```
memastikan bahwa hanya item yang sudah siap (ready) yang disajikan kepada
```
```
pelanggan, dan validasi pada markAsServed() mencegah penyajian dilakukan
```
ketika masih ada item yang belum ready. Hal ini memastikan pesanan diterima
customer dalam kondisi lengkap sesuai aturan operasional yang diterapkan.
Dengan demikian, alur layanan terbentuk secara end-to-end sebagai berikut:
POS membuat pesanan → KDS memproses hingga ready → waiter menyajikan
dan menandai served → customer menerima pesanan sebagai keluaran akhir
proses layanan, dan seluruh perubahan status tercatat konsisten pada basis data
untuk kebutuhan monitoring dan pelacakan kinerja layanan.
4.7 Implementasi Modul Waiter Take Order
Modul Waiter Take Orders dikembangkan untuk mendukung proses
pemesanan di area layanan ketika pelanggan memilih memesan melalui pelayan,
bukan langsung melalui kasir/POS. Pada modul ini, pelayan dapat menelusuri
```
katalog produk, memilih opsi (misalnya varian atau topping), menambahkan
```
catatan, menentukan jumlah, memilih meja untuk dine-in atau menandai sebagai
takeaway, mengisi identitas pelanggan, lalu mengirim pesanan agar tercatat sebagai
134
order baru dengan source = waiter. Setelah order berhasil dibuat, order masuk ke
alur produksi dapur melalui KDS sesuai mekanisme status item dan status turunan
pada level order.
Dari sisi arsitektur, modul ini memisahkan tanggung jawab agar logika tetap
rapi dan konsisten. Layanan data ditempatkan pada service layer untuk
menyediakan kategori, daftar produk, dan detail produk berikut perhitungan diskon.
Antarmuka dibangun menggunakan komponen Livewire yang saling
berkomunikasi melalui event. Perhitungan diskon dan pajak mengikuti mekanisme
yang sama dengan POS, yaitu melalui PriceAfterDiscount, DiscountFetcher,
CartHelper, dan OrderTaxCalculator, sehingga hasil perhitungan tidak berbeda
antara kanal waiter dan kanal POS.
4.7.1 Antarmuka Waiter Take Order
Halaman Waiter Take Orders terdiri atas tiga area utama, yaitu katalog
```
menu, keranjang (cart drawer), dan pemilihan meja (table modal). Katalog
```
menampilkan daftar produk yang dapat dipilih. Ketika pelayan memilih suatu
produk, sistem membuka modal detail untuk memilih opsi, mengisi catatan, dan
menentukan jumlah. Item yang dipilih kemudian dikirim ke keranjang melalui
event. Pada keranjang, pelayan dapat meninjau dan memodifikasi item, melihat
```
ringkasan transaksi (subtotal, diskon, pajak, grand total), memilih meja atau
```
takeaway, mengisi identitas pelanggan, serta menentukan opsi pembayaran
sebelum mengirim order ke sistem.
Pada Gambar 4.31 menampilkan tampilan utama waiter take orders berupa
katalog produk yang dapat dipilih oleh pelayan. Pada halaman ini tersedia akses
cepat menuju keranjang untuk memantau item yang sudah dipilih dan melanjutkan
proses pembuatan pesanan.
135
Gambar 4.31 Tampilan katalog produk pada halaman waiter take orders.
Pada Gambar 4.32 menunjukkan modal detail produk yang muncul ketika
pelayan memilih item dari katalog. Pada modal ini, pelayan dapat menentukan
```
varian/opsi produk (jika ada), menambahkan catatan khusus (misalnya tanpa saus,
```
```
tingkat pedas), serta mengatur kuantitas sebelum item dimasukkan ke keranjang.
```
Gambar 4.32 Modal detail produk pada waiter take orders.
Pada Gambar 4.33 memperlihatkan cart drawer yang menampilkan daftar
item yang dipilih beserta jumlah dan harga. Pada bagian ini, pelayan dapat
mengubah kuantitas, menghapus item, serta melihat ringkasan transaksi seperti
subtotal, diskon, pajak, dan total akhir sebelum pesanan diproses lebih lanjut.
136
```
Gambar 4.33 Keranjang (cart drawer).
```
Pada Gambar 4.34 menampilkan table modal yang digunakan untuk
memilih meja pada pesanan dine-in. Daftar meja umumnya disertai informasi status
ketersediaan, sehingga pelayan dapat memastikan pesanan ditempatkan pada meja
yang valid dan sesuai kondisi operasional.
Gambar 4.34 Pemilihan meja pada waiter take order.
Pada Gambar 4.35 menunjukkan notifikasi ketika sistem mendeteksi stok
item tidak mencukupi untuk diproses menjadi pesanan. Mekanisme ini berfungsi
sebagai validasi sebelum transaksi dibuat, sehingga mencegah pesanan terkirim
dengan item yang tidak dapat dipenuhi.
137
Gambar 4.35 Notifikasi stok tidak mencukupi sebelum order dikirim.
Pada Gambar 4.36 menampilkan notifikasi keberhasilan setelah pelayan
mengirim pesanan dan sistem berhasil membuat transaksi. Notifikasi ini menjadi
umpan balik bahwa data pesanan sudah tercatat dan diteruskan ke alur berikutnya
```
(kasir/POS) sesuai rancangan proses bisnis.
```
Gambar 4.36 Notifikasi setelah transaksi berhasil dibuat.
4.7.2 Logika Bisnis Waiter Take Order
Logika bisnis pada modul ini mencakup enam rangkaian utama. Pertama,
penyediaan data menu dan diskon. Kedua, pemilihan produk beserta opsi. Ketiga,
manajemen keranjang dan perhitungan ringkasan transaksi. Keempat, pemilihan
meja untuk dine-in atau penentuan takeaway. Kelima, validasi serta pembuatan
order saat pengiriman ke sistem. Keenam, integrasi order waiter ke alur POS–
KDS–Waiter–Customer.
```
A) Layanan Data Menu dan Diskon
```
Pengambilan data menu dipusatkan pada TakeOrderService yang menyediakan
fungsi pengambilan kategori, daftar produk ringkas, dan detail produk termasuk
opsi. Pada tahap daftar produk dan detail produk, service ini sekaligus
138
menempelkan informasi diskon dengan mekanisme pricing yang sama seperti
POS melalui PriceAfterDiscount dan DiscountFetcher. Tabel 4.23 menunjukkan
bagaimana service memuat kategori, daftar produk, dan detail produk sekaligus
menghitung diskon menggunakan mekanisme pricing yang konsisten dengan
POS. Melalui TakeOrderService, UI waiter tidak menghitung diskon secara
mandiri. Seluruh diskon dan harga final diberikan oleh mekanisme pricing yang
sama dengan POS. Pendekatan ini mengurangi duplikasi logika serta mencegah
perbedaan hasil perhitungan antar kanal pemesanan.
Tabel 4.23 TakeOrderService pada sumber data menu, opsi, dan diskon.
```
public function categories(string|int|null $tenantId):
```
Collection
```
{
```
```
return Category::query()
```
```
->when($tenantId, fn ($q) => $q->where('tenant_id',
```
```
$tenantId))
```
```
->orderBy('name')
```
```
->get(['id', 'name']);
```
```
}
```
```
public function products(string|int|null $tenantId, ?int
```
```
$categoryId, string $search, int $limit = 80): Collection
```
```
{
```
```
$term = trim($search);
```
```
return Product::query()
```
```
->when($tenantId, fn ($q) => $q->where('tenant_id',
```
```
$tenantId))
```
```
->when($categoryId, fn ($q) => $q->where('category_id',
```
```
$categoryId))
```
```
->when($term !== '', function ($q) use ($term) {
```
```
$like = '%' . $term . '%';
```
```
$q->where(function ($qq) use ($like) {
```
```
$qq->where('name', 'like', $like)
```
```
->orWhere('description', 'like', $like);
```
```
});
```
```
})
```
```
->orderBy('name')
```
```
->limit($limit)
```
```
->get(['id', 'name', 'price', 'stock_qty', 'description',
```
```
'category_id'])
```
```
->map(function (Product $product) use ($tenantId) {
```
```
$payload = $this->presentProduct($product, $tenantId);
```
```
$payload['category_id'] = $product->category_id;
```
```
return $payload;
```
```
});
```
```
}
```
```
public function productDetail(int $productId, string|int|null
```
```
$tenantId): ?array
```
```
{
```
```
$product = Product::query()
```
139
```
->with(['options.values'])
```
```
->when($tenantId, fn ($q) => $q->where('tenant_id',
```
```
$tenantId))
```
```
->find($productId);
```
```
if (! $product) {
```
```
return null;
```
```
}
```
```
$pricing = $this->pricing($product, $tenantId);
```
return [
'product' => [
'id' => $product->id,
'name' => $product->name,
'description' => $product->description,
```
'price' => (float) ($product->price ?? 0),
```
```
'stock_qty' => (int) ($product->stock_qty ?? 0),
```
```
'options' => $product->options->map(function ($option)
```
```
{
```
return [
'id' => $option->id,
'name' => $option->name,
```
'values' => $option->values->map(function ($value)
```
```
{
```
return [
'id' => $value->id,
'label' => $value->value,
```
'price_adjustment' => (float) ($value-
```
```
>price_adjustment ?? 0),
```
```
];
```
```
})->all(),
```
```
];
```
```
})->all(),
```
],
'pricing' => $pricing,
```
];
```
```
}
```
```
private function pricing(Product $product, string|int|null
```
```
$tenantId): array
```
```
{
```
```
return PriceAfterDiscount::calculate(
```
```
product: $product,
```
```
tenantId: $tenantId ? (string) $tenantId : null,
```
```
priceOverride: (float) ($product->price ?? 0),
```
```
discountFetcher: $this->discountFetcher
```
```
);
```
```
}
```
```
B) Browser Produk dan Modal Opsi
```
Komponen ProductBrowser mengelola katalog produk dan modal opsi. Katalog
```
dimuat melalui loadProducts() yang mengambil daftar produk dari
```
TakeOrderService, lalu menyimpannya pada state allProducts. Penyaringan
berdasarkan kategori dan kata kunci dilakukan pada sisi komponen melalui
140
```
getProductsProperty(). Ketika pelayan memilih produk,
```
```
openProduct(productId) memanggil productDetail() untuk mengambil detail
```
```
produk, opsi, dan pricing final. Saat tombol “Add” ditekan, addSelection()
```
membangun payload pilihan opsi dan mengirim event waiter-item-selected ke
```
komponen keranjang (CartDrawer). Tabel 4.24 merangkum mekanisme
```
pemuatan katalog, pembukaan modal detail, serta pengiriman item terpilih ke
keranjang melalui event. Penggunaan event membuat komponen lebih modular.
ProductBrowser berfokus pada pengalaman pemilihan produk dan opsi,
sedangkan CartDrawer menangani keranjang serta perhitungan ringkasan
transaksi.
Tabel 4.24 ProductBrowser untuk katalog menu, modal opsi, dan event pemilihan item.
```
public function loadProducts(): void
```
```
{
```
$this->allProducts = $this->takeOrderService
```
->products(
```
```
tenantId: $this->resolveTenantId(),
```
```
categoryId: null,
```
```
search: '',
```
```
limit: 150
```
```
)
```
```
&nbsp->values()
```
```
&nbsp->all();
```
```
$this->productsLoaded = true;
```
```
}
```
```
public function getProductsProperty(): Collection
```
```
{
```
```
if (! $this->productsLoaded) {
```
```
return collect();
```
```
}
```
```
$term = trim($this->search);
```
```
$catId = $this->categoryId;
```
```
return collect($this->allProducts)
```
```
->filter(function ($product) use ($term, $catId) {
```
```
if ($catId && (int) ($product['category_id'] ?? 0) !==
```
```
(int) $catId) {
```
```
return false;
```
```
}
```
```
if ($term === '') {
```
```
return true;
```
```
}
```
$haystacks = [
```
(string) ($product['name'] ?? ''),
```
```
(string) ($product['description'] ?? ''),
```
```
&nbsp];
```
```
foreach ($haystacks as $h) {
```
```
if ($h !== '' && stripos($h, $term) !== false) {
```
141
```
return true;
```
```
}
```
```
&nbsp}
```
```
return false;
```
```
})
```
```
&nbsp->values();
```
```
}
```
```
public function openProduct(int $productId): void
```
```
{
```
$detail = $this->takeOrderService-
```
>productDetail($productId, $this->resolveTenantId());
```
```
if (! $detail) {
```
```
return;
```
```
}
```
```
$this->activeProduct = array_merge(
```
$detail['product'],
['discount' => $detail['pricing']]
```
);
```
```
$this->selectedOptions = [];
```
```
foreach (($detail['product']['options'] ?? []) as $option)
```
```
{
```
```
$this->selectedOptions[$option['id']] = null;
```
```
}
```
```
$this->quantity = 1;
```
```
$this->note = '';
```
```
$this->showModal = true;
```
```
$this->dispatch('lock-scroll');
```
```
}
```
```
public function addSelection(): void
```
```
{
```
```
if (! $this->activeProduct) {
```
```
return;
```
```
}
```
```
$optionPayload = [];
```
```
foreach (($this->activeProduct['options'] ?? []) as
```
```
$option) {
```
$selected = $this->selectedOptions[$option['id']] ??
```
null;
```
```
if (! $selected) {
```
```
continue;
```
```
}
```
```
$value = collect($option['values'] ?? [])-
```
```
>firstWhere('id', $selected);
```
```
if ($value) {
```
$optionPayload[$option['id']] = [
'id' => $value['id'],
'value' => $value['label'],
```
'price_adjustment' => (float)
```
```
($value['price_adjustment'] ?? 0),
```
```
&nbsp];
```
```
}
```
```
}
```
```
$this->dispatch(
```
'waiter-item-selected',
```
productId: $this->activeProduct['id'],
```
142
```
quantity: $this->quantity,
```
```
options: $optionPayload,
```
```
note: $this->note,
```
```
editingItemId: $this->editingItemId
```
```
);
```
```
$this->closeModal();
```
```
C) Keranjang, Ringkasan Transaksi, dan Pengiriman Order
```
Komponen CartDrawer menjadi pusat pengelolaan keranjang pelayan. Item
yang dipilih dari ProductBrowser diterima melalui event waiter-item-selected.
Item kemudian dibentuk menjadi payload terstruktur menggunakan
```
CartHelper::buildPayload() sehingga informasi opsi, catatan, harga dasar,
```
diskon, dan harga final tersimpan konsisten. Payload dimasukkan ke cart
```
(Darryldecode Cart), lalu refreshCart() menghitung ulang ringkasan transaksi.
```
```
Ringkasan transaksi mengikuti mekanisme POS. CartHelper::summarizeItems()
```
menghitung subtotal, total diskon, serta nilai final.
```
OrderTaxCalculator::calculate() menghitung total pajak dan grand total
```
berdasarkan nilai final tersebut. Pemilihan meja dilakukan melalui event waiter-
table-selected dan disimpan ke session untuk menjaga konsistensi ketika
halaman di-refresh atau berpindah navigasi. Tabel 4.25 menunjukkan alur
penerimaan item, pembentukan payload, perhitungan ringkasan, dan
penyimpanan pilihan meja melalui session. Dengan struktur payload yang
seragam dan mekanisme ringkasan yang sama dengan POS, angka pada
keranjang pelayan tetap konsisten ketika order dilanjutkan ke tahap berikutnya.
Tabel 4.25 CartDrawer.
```
#[On('waiter-item-selected')]
```
```
public function addSelection(int $productId, int $quantity =
```
1, array $options = [], string $note = '', string|int|null
```
$editingItemId = null): void
```
```
{
```
```
$product = Product::when($this->tenantId, fn ($q) => $q-
```
```
>where('tenant_id', $this->tenantId))->find($productId);
```
```
if (! $product) {
```
```
return;
```
```
}
```
```
if ($editingItemId) {
```
```
Cart::remove($editingItemId);
```
```
}
```
143
```
$payload = $this->cartHelper->buildPayload(
```
$product,
$options,
```
max(1, $quantity),
```
$note,
```
$this->resolveTenantId(),
```
null,
null,
$this->discountFetcher
```
);
```
```
Cart::add($payload);
```
```
$this->refreshCart();
```
```
}
```
```
#[On('waiter-table-selected')]
```
```
public function setTable(?int $tableId = null, string $label
```
```
= 'Select Table', string $orderType = 'dine-in'): void
```
```
{
```
```
$this->tableId = $tableId;
```
```
$this->tableLabel = $label;
```
```
$this->orderType = $orderType;
```
```
session()->put('waiter_table_id', $tableId);
```
```
session()->put('waiter_table_label', $label);
```
```
}
```
```
public function refreshCart(): void
```
```
{
```
```
$content = Cart::getContent();
```
```
$summary = $this->cartHelper->summarizeItems($content);
```
```
$taxCalc = $this->taxCalculator->calculate($this-
```
```
>resolveTenantId(), $summary['final']);
```
```
$this->items = $content->values()->map(function ($item) {
```
return [
```
'id' => (string) $item->id,
```
```
'name' => (string) $item->name,
```
```
'quantity' => (int) $item->quantity,
```
```
'price' => (float) $item->price,
```
'attributes' => $item->attributes ? $item->attributes-
```
>toArray() : [],
```
```
];
```
```
})->all();
```
```
$this->subtotal = $summary['subtotal'];
```
```
$this->discountTotal = $summary['discount'];
```
```
$this->taxTotal = $taxCalc->totalTax;
```
```
$this->grandTotal = $taxCalc->grandTotal;
```
```
$this->dispatch('waiter-cart-count', count: $content-
```
```
>count());
```
```
}
```
```
D) Validasi dan Pembuatan Order saat “Send to Cashier”
```
```
Fungsi utama pada modul ini adalah sendToCashier(). Aksi dijalankan di server-
```
```
side dan dilindungi oleh Gate::authorize('waiter_take_order'). Sebelum
```
membuat order, sistem menjalankan validasi berlapis yang mencakup validasi
keranjang, validasi identitas pelanggan, penentuan tipe order, serta validasi stok.
144
Identitas pelanggan divalidasi dengan customerName wajib diisi dan
customerEmail wajib diisi serta berformat email. Jenis order ditentukan
berdasarkan pilihan meja. Jika meja dipilih maka order bertipe dine-in,
sedangkan jika tidak ada meja maka bertipe takeaway. Validasi stok dilakukan
```
melalui CartHelper::validateStock(). Jika stok tidak mencukupi, sistem
```
menampilkan modal stok dan proses dihentikan. Jika seluruh validasi lolos,
sistem membuat atau memperbarui CustomerDetail berdasarkan email,
menghitung ringkasan dan pajak menggunakan CartHelper dan
OrderTaxCalculator, serta menghitung estimasi waktu produksi melalui
```
CartHelper::expectedSeconds(). Pembuatan order dijalankan dalam transaksi
```
```
basis data (DB::transaction) untuk menjamin konsistensi antara orders,
```
order_items, dan order_taxes. Order yang dibuat diberi source = waiter dan
nomor referensi dengan format W-<TEN>-<RAND>.
Pada implementasi ini, payment_status diisi sebagai pending ketika payNow =
true, sedangkan pay_later ketika payNow = false. Pemisahan ini memungkinkan
sistem membedakan order yang masih menunggu penyelesaian pembayaran oleh
kasir dari order yang diperbolehkan dibayar belakangan. Tabel 4.26
menunjukkan urutan validasi serta proses pembuatan order yang dibungkus
transaksi basis data. Penggunaan transaksi basis data menjamin order, item, dan
pajak tersimpan dalam keadaan konsisten. Validasi stok sebelum pembuatan
order mencegah terbentuknya transaksi tidak valid yang berpotensi mengganggu
proses pada tahap dapur maupun kasir.
Tabel 4.26 CartDrawer – sendToCashier: validasi, cek stok, dan create order dalam transaksi.
```
public function sendToCashier(): void
```
```
{
```
```
Gate::authorize('waiter_take_order');
```
```
$this->refreshCart();
```
```
$this->validate([
```
'customerName' => ['required', 'string', 'max:120'],
'customerEmail' => ['required', 'email', 'max:160'],
```
]);
```
```
$tenantId = $this->resolveTenantId();
```
```
if (count($this->items) === 0) {
```
```
$this->dispatch('notify', type: 'warning', message:
```
```
'Please add at least one item.');
```
145
```
return;
```
```
}
```
$resolvedOrderType = $this->tableId ? 'dine-in' :
```
'takeaway';
```
$insufficient = $this->cartHelper-
```
>validateStock(Cart::getContent(), $tenantId);
```
```
if (! empty($insufficient)) {
```
```
$this->stockIssues = $insufficient;
```
```
$this->stockIssueModal = true;
```
```
$this->showDrawer = false;
```
```
return;
```
```
}
```
```
$customer = CustomerDetail::firstOrCreate(
```
['tenant_id' => $tenantId, 'email' => $this-
>customerEmail],
['name' => $this->customerName]
```
);
```
```
if ($customer->name !== $this->customerName) {
```
```
$customer->name = $this->customerName;
```
```
$customer->save();
```
```
}
```
$summary = $this->cartHelper-
```
>summarizeItems(Cart::getContent());
```
```
$taxCalc = $this->taxCalculator->calculate($tenantId,
```
```
$summary['final']);
```
$expectedSeconds = $this->cartHelper-
```
>expectedSeconds(Cart::getContent());
```
```
$isPayNow = (bool) $this->payNow;
```
```
$order = DB::transaction(function () use ($tenantId,
```
$customer, $taxCalc, $expectedSeconds, $resolvedOrderType,
```
$isPayNow) {
```
```
$reference = $this->generateReference($tenantId);
```
```
$order = Order::create([
```
'reference_no' => $reference,
'customer_detail_id' => $customer->id,
'dining_table_id' => $resolvedOrderType === 'dine-in' ?
$this->tableId : null,
'total' => $taxCalc->grandTotal,
'subtotal' => $taxCalc->subtotal,
'total_tax' => $taxCalc->totalTax,
'grand_total' => $taxCalc->grandTotal,
'payment_status' => $isPayNow ? 'pending' :
'pay_later',
'payment_channel' => 'cash',
'tenant_id' => $tenantId,
'expected_seconds_total' => $expectedSeconds,
'source' => 'waiter',
'order_type' => $resolvedOrderType,
```
]);
```
```
foreach (Cart::getContent() as $item) {
```
$attributes = $item->attributes ? $item->attributes-
```
>toArray() : [];
```
```
$options = $attributes['options'] ?? [];
```
$productId = $attributes['product_id'] ??
```
CartItemIdentifier::extractProductId($item) ?? $item->id;
```
146
```
$unitPrice = (float) ($attributes['raw_price'] ??
```
```
$item->price);
```
```
$finalPrice = (float) ($attributes['final_price'] ??
```
```
$item->price);
```
```
$discountAmount = (float)
```
```
($attributes['discount_amount'] ?? max($unitPrice -
```
```
$finalPrice, 0));
```
```
$discountId = $attributes['discount_id'] ?? null;
```
```
OrderItem::create([
```
'order_id' => $order->id,
'product_id' => $productId,
'product_name' => $item->name,
'unit_price' => $unitPrice,
'final_price' => $finalPrice,
'discount_amount' => $discountAmount,
'discount_id' => $discountId,
'quantity' => $item->quantity,
'options' => $options,
```
'estimate_seconds' => (int)
```
```
($attributes['estimated_seconds'] ?? 0),
```
'special_instructions' => $attributes['note'] ??
null,
'tenant_id' => $tenantId ?? $this->tenantId,
```
]);
```
```
}
```
```
foreach ($taxCalc->lines as $line) {
```
```
$order->taxLines()->create([
```
'tax_id' => $line['tax_id'],
'name' => $line['name'],
'type' => $line['type'],
'rate' => $line['rate'],
'amount' => $line['amount'],
```
]);
```
```
}
```
```
if ($resolvedOrderType === 'dine-in' && $this->tableId) {
```
```
DiningTable::where('tenant_id', $tenantId)->where('id',
```
```
$this->tableId)
```
```
->update(['status' => 'occupied']);
```
```
}
```
```
$order->refreshFulfillmentStatus();
```
```
return $order;
```
```
});
```
```
Cart::clear();
```
```
$this->refreshCart();
```
```
$this->dispatch('waiter-table-selected', tableId: $this-
```
>tableId, label: $this->tableLabel, orderType:
```
$resolvedOrderType);
```
```
$this->dispatch('notify', type: 'success', message: 'Order
```
```
sent to cashier.');
```
```
}
```
147
```
E) Pemilihan Meja menggunakan TableModal
```
Pemilihan meja dipisahkan pada komponen TableModal agar prosesnya
terstruktur dan dapat digunakan ulang. Saat pemilih meja dibuka, TableModal
memuat floor beserta daftar meja melalui TableLookupService. Ketika pelayan
memilih meja, komponen mengirim event waiter-table-selected yang berisi
```
tableId, label, dan orderType. Event ini ditangkap oleh CartDrawer::setTable()
```
dan disimpan ke session agar pilihan meja tetap tersimpan. Tabel 4.27
menunjukkan mekanisme pembukaan modal pemilih meja, pemilihan floor, dan
pengiriman hasil pilihan meja ke CartDrawer melalui event. Dengan pemisahan
ini, logika pemilihan meja tidak menumpuk pada keranjang, sementara hasil
pilihan tetap konsisten karena disimpan ke session oleh CartDrawer.
Tabel 4.27 Table modal pemilihan meja.
```
#[On('waiter-open-table-picker')]
```
```
public function open(): void
```
```
{
```
```
$this->loadTables();
```
```
$this->showModal = true;
```
```
}
```
```
public function selectFloor(?int $floorId = null): void
```
```
{
```
```
$this->activeFloorId = $floorId;
```
```
$this->loadTables();
```
```
}
```
```
public function selectTable(?int $tableId, ?string $label =
```
```
null): void
```
```
{
```
```
$tableLabel = $label ?: ($tableId ? 'Table ' . $tableId :
```
```
'Takeaway');
```
```
$this->dispatch(
```
'waiter-table-selected',
```
tableId: $tableId,
```
```
label: $tableLabel,
```
```
orderType: $tableId ? 'dine-in' : 'takeaway'
```
```
);
```
```
$this->close();
```
4.8 Implementasi Customer Check Order dan Track Order
Modul customer check and track order disediakan untuk memberi akses
kepada pelanggan dalam melakukan pengecekan status pesanan secara mandiri.
```
Modul ini bersifat read-only (tidak mengubah status), dan berfungsi sebagai titik
```
```
akhir (end-user) dari siklus layanan, yaitu memastikan pelanggan dapat melihat
```
148
progres pesanan dari fase produksi sampai pesanan disajikan. Status pesanan yang
ditampilkan pada sisi pelanggan bersumber dari data yang sama dengan POS, KDS,
dan Waiter, sehingga perubahan status pada dapur maupun pelayan akan langsung
tercermin pada halaman pelanggan. Pada implementasinya, modul customer terdiri
dari dua bagian utama:
• Customer check order, yaitu halaman pencarian/pemilihan pesanan
```
berdasarkan identitas pelanggan (nama dan email) atau nomor referensi.
```
• Customer track order, yaitu halaman pelacakan progres pesanan
berdasarkan reference_no, termasuk indikator status, pembaruan otomatis
```
(polling), dan ringkasan nilai transaksi.
```
4.8.1 Antarmuka Customer Check Order
Halaman customer check order menampilkan form pencarian yang
mendukung dua skenario. Skenario pertama adalah pencarian menggunakan nama
dan email untuk menampilkan daftar pesanan yang berkaitan dengan identitas
pelanggan. Skenario kedua adalah pencarian menggunakan nomor referensi untuk
menemukan satu pesanan tertentu secara spesifik. Hasil pencarian identitas
pelanggan ditampilkan sebagai daftar pesanan terbaru dan menggunakan
pagination agar tetap ringan ketika jumlah riwayat pesanan bertambah.
Pada Gambar 4.37 menunjukkan form pencarian yang digunakan pelanggan
untuk memasukkan nama dan email sebagai identitas pencarian. Masukan ini
digunakan sistem untuk mengambil dan menampilkan riwayat pesanan yang sesuai
dengan data pelanggan pada tenant terkait. Penggunaan dua atribut identitas
tersebut membantu mempersempit hasil pencarian dan mengurangi kemungkinan
riwayat pesanan tertukar dengan pelanggan lain yang memiliki nama serupa.
Setelah data dimasukkan dan dikirim, sistem melakukan validasi dasar terhadap
format input, lalu menampilkan daftar pesanan yang relevan beserta informasi
ringkasnya, sehingga pelanggan dapat meninjau kembali detail transaksi dan status
pesanan secara lebih mudah.
149
Gambar 4.37 Halaman customer check order.
Pada Gambar 4.38 menampilkan hasil pencarian dalam bentuk daftar
pesanan terbaru milik pelanggan. Daftar disajikan ringkas dan menggunakan
pagination agar tampilan tetap responsif ketika jumlah pesanan yang ditemukan
cukup banyak.
Gambar 4.38 Halaman customer check order menggunakan reference_no.
Pada Gambar 4.39 menunjukkan skenario pencarian menggunakan nomor
```
referensi (reference_no) untuk menemukan satu pesanan secara spesifik.
```
Mekanisme ini mendukung pelanggan yang ingin memeriksa pesanan tertentu tanpa
harus menampilkan seluruh riwayat pesanan.
150
Gambar 4.39 Form pencarian berdasarkan reference_no.
4.8.2 Logika Bisnis Customer Check Order
Logika bisnis customer check order diimplementasikan pada
CustomerOrderLookupController. Controller ini bertanggung jawab memusatkan
validasi input, membaca parameter pencarian dari request, serta mengambil data
order yang telah dibatasi oleh tenant agar tidak terjadi kebocoran data lintas tenant.
Pencarian dapat dilakukan melalui kombinasi nama dan email, maupun melalui
reference_no. Tabel 4.28 menampilkan struktur utama implementasi
```
CustomerOrderLookupController, khususnya metode showForm() sebagai handler
```
```
halaman pencarian berbasis kueri string, metode search() sebagai handler submit
```
form yang melakukan redirect dengan kueri string, serta dua metode kueri internal
yang memuat data pesanan berdasarkan identitas pelanggan atau berdasarkan
reference_no.
151
Tabel 4.28 CustomerOrderLookupController.
```
public function showForm(Request $request): View
```
```
{
```
```
$orders = null;
```
```
$name = $request->query('name');
```
```
$email = $request->query('email');
```
```
$reference = $request->query('reference');
```
```
$referenceOrder = null;
```
```
$referenceSearched = false;
```
```
if (! is_null($name) && ! is_null($email)) {
```
```
$validated = validator(
```
['name' => $name, 'email' => $email],
[
'name' => ['required', 'string', 'max:255'],
'email' => ['required', 'email', 'max:255'],
]
```
)->validate();
```
```
$name = trim($validated['name']);
```
```
$email = strtolower($validated['email']);
```
```
$orders = $this->fetchOrders($name, $email);
```
```
}
```
```
if (! is_null($reference)) {
```
```
$referenceSearched = true;
```
```
$validated = validator(
```
['reference' => $reference],
['reference' => ['required', 'string', 'max:120']]
```
)->validate();
```
$referenceOrder = $this-
```
>fetchOrderByReference(trim($validated['reference']));
```
```
}
```
```
return view('customer.orders.check', [
```
'orders' => $orders,
'name' => $name,
'email' => $email,
'reference' => $reference,
'referenceOrder' => $referenceOrder,
'referenceSearched' => $referenceSearched,
```
]);
```
```
}
```
```
public function search(Request $request): RedirectResponse
```
```
{
```
```
$validated = $request->validate([
```
'name' => ['required', 'string', 'max:255'],
'email' => ['required', 'email', 'max:255'],
```
]);
```
```
return redirect()->route('customer.orders.check', [
```
152
'name' => $validated['name'],
'email' => $validated['email'],
```
]);
```
```
}
```
Pada Tabel 4.28 menunjukkan dua metode utama yang mengelola alur
```
pencarian pesanan dari sisi pelanggan. Metode showForm() membaca parameter
```
```
pencarian dari kueri string (nama+email dan/atau reference) lalu menjalankan
```
```
validasi sebelum memanggil fungsi pengambilan data. Metode search()
```
memvalidasi input dari form dan melakukan redirect ke halaman yang sama sambil
membawa parameter pencarian melalui kueri string agar hasil pencarian dapat
ditampilkan secara konsisten dan mudah dibagikan. Kemudian, pada Tabel 4.29
ditampilkan implementasi kueri customer order.
Tabel 4.29 Implementasi kueri customer order.
```
private function fetchOrders(string $name, string $email)
```
```
{
```
```
$tenantId = tenant('id');
```
```
return Order::query()
```
```
->with('customerDetail')
```
```
->where('tenant_id', $tenantId)
```
```
->whereHas('customerDetail', function ($query) use
```
```
($name, $email) {
```
```
$query->whereRaw('LOWER(email) = ?', [$email])
```
```
->where('name', 'like', '%' . $name . '%');
```
```
})
```
```
->latest()
```
```
->paginate(10)
```
```
->withqueryString();
```
```
}
```
```
private function fetchOrderByReference(string $reference):
```
?Order
```
{
```
```
$tenantId = tenant('id');
```
```
return Order::query()
```
```
->with(['customerDetail'])
```
```
->where('tenant_id', $tenantId)
```
```
->where('reference_no', $reference)
```
```
->first();
```
```
}
```
Tabel 4.29 memperlihatkan dua fungsi kueri internal yang menjadi inti
```
pembatasan data berbasis tenant. Metode fetchOrders() mengambil daftar pesanan
```
153
```
berdasarkan identitas pelanggan (nama dan email) dengan menyertakan relasi
```
customerDetail, membatasi data menggunakan tenant_id, lalu mengembalikan
```
hasil dalam bentuk pagination. Sementara itu, fetchOrderByReference() digunakan
```
untuk pencarian spesifik satu pesanan menggunakan reference_no pada tenant yang
sama, sehingga pelanggan tidak dapat mengakses pesanan lintas tenant.
Berdasarkan implementasi tersebut, proses pencarian dilakukan melalui
validasi input terlebih dahulu untuk memastikan format nama, email, dan reference
sesuai kebutuhan sistem. Setelah validasi, sistem menerapkan pembatasan tenant
melalui tenant_id pada setiap kueri, sehingga pelanggan hanya dapat melihat
pesanan yang berada dalam tenant yang sama. Untuk pencarian berdasarkan
identitas, sistem melakukan pencocokan email secara case-insensitive
```
menggunakan LOWER(email), sedangkan nama dicocokkan menggunakan pola
```
like agar lebih toleran terhadap variasi penulisan. Hasil pencarian ditampilkan
dalam bentuk pagination sehingga performa tetap terjaga ketika data bertambah.
```
4.8.3 Antarmuka Order Tracking (Customer Track Order)
```
Halaman customer track order menampilkan status pesanan secara ringkas
dan mudah dipahami. Antarmuka menampilkan status terkini dalam bentuk badge,
```
progres status dalam bentuk stepper (confirmed, preparing, ready, served), nomor
```
referensi sebagai identitas pesanan, serta ringkasan nominal transaksi. Halaman ini
juga mendukung pembaruan otomatis melalui mekanisme polling pada komponen
Livewire, sehingga pelanggan tidak perlu melakukan refresh manual. Perancangan
antarmuka customer track order ditunjukkan pada Gambar 4.40 hingga Gambar
4.42.
Pada Gambar 4.40 menunjukkan tampilan utama customer track order yang
menampilkan status pesanan paling baru dalam bentuk badge serta urutan progres
melalui stepper. Dengan tampilan ini, pelanggan dapat memahami posisi pesanan
pada alur pemrosesan tanpa perlu melihat detail teknis pada modul internal
```
(POS/KDS/Waiter).
```
154
Gambar 4.40 Halaman customer track order.
Pada Gambar 4.41 menampilkan halaman yang menunjukkan status
pemesanan yang melakukan pembaruan otomatis melalui polling pada Livewire.
Mekanisme ini memungkinkan sistem mengambil status terbaru secara berkala,
```
sehingga perubahan status (misalnya dari preparing menjadi ready) dapat tampil ke
```
pelanggan tanpa tindakan manual.
Gambar 4.41 Perubahan status pesanan pada halaman order tracking.
Pada Gambar 4.42 memperlihatkan ringkasan nominal transaksi pada
halaman tracking. Ringkasan ini memberikan informasi finansial utama yang
terkait dengan pesanan, seperti total nilai item pesanan, total biaya yang dihitung
sistem, serta nilai bersih yang menjadi acuan akhir pelanggan.
155
Gambar 4.42 Ringkasan pesanan.
4.8.4 Logika Bisnis Order Tracking dan Sinkronisasi Status
Logika Order Tracking diimplementasikan pada OrderTrackingController.
Controller ini bertugas memvalidasi akses berdasarkan tenant dan reference_no,
memastikan order yang ditampilkan benar-benar berada pada tenant yang sesuai.
Selain itu, sistem menerapkan aturan bahwa halaman tracking ditampilkan setelah
pembayaran selesai. Jika status pembayaran belum paid, pelanggan diarahkan
terlebih dahulu ke halaman status pembayaran. Setelah order valid, sistem memuat
item dan pajak, menghidrasi opsi item agar tampil siap presentasi.
Tabel 4.30 OrderTrackingController.
```
public function show(Request $request, string $reference): View
```
```
{
```
```
$order = $this->resolveByReferenceOrFail($request,
```
```
$reference);
```
```
if ($order->payment_status !== 'paid') {
```
```
return redirect()->route('payment.status', [
```
```
'tenant' => $request->route('tenant'),
```
'reference' => $order->reference_no,
```
]);
```
```
}
```
```
$order = OrderItemOptionHydrator::hydrate($order-
```
```
>loadMissing(['items', 'taxLines']));
```
```
$summary = $this->summaryService->build($order);
```
```
return view('payment.order-tracking', [
```
'order' => $order,
'summary' => $summary,
156
```
]);
```
```
}
```
Tabel 4.31 memperlihatkan alur utama pada OrderTrackingController.
Alur dimulai dari resolusi order berdasarkan reference_no dan tenant_id. Jika order
belum dibayar, sistem melakukan redirect ke halaman status pembayaran. Jika
sudah dibayar, sistem memuat relasi items dan taxLines, lalu membangun summary
yang digunakan pada tampilan pelacakan pesanan.
Tabel 4.31 OrderTrackingController.
```
private function resolveByReferenceOrFail(Request $request,
```
```
string $reference): Order
```
```
{
```
```
$tenantId = (string) ($request->route('tenant') ??
```
```
tenant('id'));
```
```
$order = Order::where('reference_no', $reference)
```
```
->where('tenant_id', $tenantId)
```
```
->first();
```
```
if (! $order) {
```
```
abort(404);
```
```
}
```
```
return $order;
```
```
}
```
Berdasarkan potongan kode tersebut, akses halaman tracking dikunci
melalui kombinasi reference_no dan tenant_id. Apabila order tidak ditemukan,
sistem mengembalikan respons 404 sehingga reference yang tidak valid tidak dapat
menampilkan data apa pun. Selanjutnya, apabila payment_status belum paid,
sistem mengarahkan pelanggan ke halaman status pembayaran agar alur bisnis tetap
konsisten. Setelah pembayaran valid, sistem memuat item dan taxLines, lalu
melakukan proses hydrator untuk menyiapkan struktur opsi item agar mudah
ditampilkan. Ringkasan nominal dibentuk menggunakan PaymentSummaryService,
sehingga nilai yang ditampilkan konsisten dengan perhitungan sistem pembayaran.
4.8.5 Integrasi POS-KDS-Waiter-Customer
Modul customer menjadi titik akhir dari siklus layanan. Customer tidak
mengubah data, namun menerima hasil perubahan status dari modul operasional:
157
1. POS membuat pesanan dan menyimpan item pada order_items (status awal
```
queued), lalu order membentuk fulfillment_status awal sebagai status
```
turunan.
2. KDS memproses item dari queued/preparing hingga ready menggunakan
```
OrderFulfillmentService::markReady, sehingga item menjadi ready dan
```
ready_at tercatat.
3. Waiter Orders menyajikan item ready menjadi served menggunakan
```
OrderFulfillmentService::markServed, sehingga served_at tercatat dan
```
pesanan ikut tersinkron saat seluruh item served.
4. Customer melihat status terbaru melalui Order Tracking. Mekanisme
polling memastikan perubahan dari KDS atau Waiter tercermin otomatis
tanpa refresh manual.
Dengan demikian, customer berperan sebagai penerima output layanan
```
terakhir: pesanan diterima saat status mencapai ready dan layanan selesai saat status
```
mencapai served. Seluruh perubahan tercatat konsisten pada basis data sehingga
dapat digunakan untuk monitoring operasional dan evaluasi performa layanan.
Konteks multi-tenant dan isolasi data dijelaskan pada Subbab 4.3.1 sebagai
prasyarat operasional, sedangkan pembahasan integrasi pada subbab ini difokuskan
pada alur status dan pertukaran data antarmodul inti.
158
BAB V
PENGUJIAN DAN EVALUASI
Pada bagian ini, akan dibahas metode pengujian serta hasil pengujian black box dan
```
user acceptance test (UAT). Pengujian black box serta user acceptance test
```
dilakukan pada modul POS, KDS, Waiter Orders, Waiter Take Orders, serta
Customer Check/Track Order.
5.1 Metode Pengujian
Pengujian pada penelitian ini menggunakan metode black box testing untuk
memastikan setiap fungsi pada sistem berjalan sesuai kebutuhan tanpa meninjau
struktur kode internal. Pengujian dilakukan dengan memberikan masukan pada
antarmuka, menjalankan langkah uji sesuai skenario, kemudian membandingkan
keluaran sistem terhadap hasil yang diharapkan. Ruang lingkup pengujian meliputi
```
modul point of sales (POS), kitchen display system (KDS), Waiter Orders, Waiter
```
Take Orders, serta Customer Check/Track Order
Selain itu, penelitian ini juga melakukan user acceptance testing untuk
menilai kemampuan pengguna dalam mengoperasikan sistem secara langsung,
khususnya terkait kemudahan mempelajari alur, kecepatan menyelesaikan tugas,
serta potensi kesalahan penggunaan saat menjalankan fitur-fitur utama. Pengujian
```
ini dilakukan dengan meminta pengguna pada setiap peran (cashier, kitchen, waiter,
```
```
dan customer) menjalankan sejumlah tugas representatif (misalnya mencatat
```
```
pesanan, mengubah status pesanan, hingga melakukan pengecekan order),
```
kemudian mencatat hasil penyelesaian tugas, hambatan yang muncul, serta
masukan pengguna sebagai bahan evaluasi perbaikan antarmuka dan alur sistem.
5.2 Pengujian Black Box
Pengujian black box dilakukan untuk memverifikasi kesesuaian fungsi
sistem berdasarkan skenario penggunaan tanpa meninjau struktur kode program.
Pengujian difokuskan pada validasi masukan, proses yang dijalankan sistem, dan
keluaran yang dihasilkan pada setiap modul yang diuji. Pada penelitian ini, setiap
159
skenario pengujian dijalankan sebanyak 10 kali untuk memastikan konsistensi hasil
dan meminimalkan kemungkinan kegagalan yang bersifat insidental. Persentase
```
pada kolom “Persentase (10x uji)” menunjukkan tingkat keberhasilan dari total 10
```
kali pengujian pada skenario tersebut. Hasil pengujian disajikan per modul,
```
meliputi point of sales (POS), kitchen display system (KDS), Waiter Orders, Waiter
```
Take Orders, serta Customer Check/Track Order
```
5.2.1 Pengujian Black Box – Point of Sales (POS)
```
```
Berdasarkan pengujian pada modul point of sales (POS) yang telah
```
dilakukan, hasil dan persentase dari 10 kali pengujian disajikan pada Tabel 5.1.
Pada Tabel 5.1, ditampikan juga scenario pengujian black box pada modul point of
```
sales (POS) untuk memverifikasi fungsi inti pemesanan, pengelolaan keranjang,
```
validasi checkout, proses pembayaran, serta integrasi order ke modul kitchen
```
display system (KDS).
```
```
Tabel 5.1 Pengujian black box point of sales (POS).
```
```
Hasil Pengujian Black Box – Point of Sales (POS)
```
Nama
Pengujian Bentuk Pengujian
Hasil Yang
Diharapkan
Hasil
Pengujian
Persentase
```
(10x uji)
```
Login dan
pembatasan
hak akses
Aktor kasir melakukan
login serta navigasi
halaman dan hak akses
dibatasi berdasarkan
peran
Aktor kasir berhasil
login dan hak akses
seusai dengan peran
yang diberikan
Berhasil 100%
Menambah
produk ke
keranjang
Pilih produk pada katalog
lalu tambah ke cart
Item masuk ke
keranjang dengan
quantity awal sesuai
input
Berhasil 100%
Menambah
produk
dengan opsi
Pilih produk yang
memiliki opsi, pilih opsi,
lalu tambah
Opsi tersimpan pada
```
item (membedakan
```
```
varian) dan harga
```
mengikuti
penyesuaian opsi
Berhasil 100%
Edit item
keranjang
Klik edit item, ubah
opsi/qty/catatan, lalu
simpan
Item lama tergantikan
dengan data baru
sesuai perubahan
Berhasil 100%
160
```
Tabel 5.1 Pengujian black box point of sales (POS) (lanjutan).
```
```
Hasil Pengujian Black Box – Point of Sales (POS)
```
Nama
Pengujian
Bentuk
Pengujian Hasil Yang Diharapkan
Hasil
Pengujian
Persentase
```
(10x uji)
```
Menambah item
Klik tombol
tambah
quantity pada
cart
Quantity bertambah dan
ringkasan transaksi ikut
berubah
Berhasil 100%
Mengurangi
```
item (qty > 1)
```
Klik tombol
kurang
quantity saat
qty > 1
Quantity berkurang dan
ringkasan ikut berubah Berhasil 100%
Mengurangi
```
item (qty = 1)
```
Klik tombol
kurang
quantity saat
```
qty = 1
```
Item dihapus dari keranjang Berhasil 100%
Hapus item
keranjang
Klik remove
pada item
Item hilang dari keranjang dan
ringkasan diperbarui Berhasil 100%
Validasi
```
checkout: cart
```
kosong
Klik checkout
saat cart
kosong
Sistem menolak proses dan
menampilkan peringatan “Cart
is empty”
Berhasil 100%
Validasi
```
checkout:
```
customer belum
dipilih
Isi keranjang,
tidak pilih
customer, klik
checkout
Sistem menolak proses dan
menampilkan peringatan
“Select a customer first”
Berhasil 100%
Validasi
```
checkout: dine-
```
in wajib pilih
meja
Set orderType
dine-in tanpa
table, klik
checkout
Sistem menolak proses dan
menampilkan peringatan pilih
meja dine-in
Berhasil 100%
Pembayaran
tunai kurang
dari total
Checkout, pilih
cash, input
nominal < total
Sistem menolak dan
menampilkan peringatan
“Received amount is less than
total”
Berhasil 100%
Pembayaran
tunai berhasil +
kembalian
Input cash ≥
total
Sistem memproses order,
menghitung kembalian, dan
menampilkan notifikasi sukses
Berhasil 100%
Pembayaran
kartu berhasil
Pilih card lalu
konfirmasi
Sistem memproses order
dengan
```
payment_channel=card dan
```
status paid
Berhasil 100%
Pembayaran
qris berhasil
Pilih qris lalu
konfirmasi
Sistem memproses order
dengan payment_channel=qris
dan status paid
Berhasil 100%
161
```
Tabel 5.1 Pengujian black box point of sales (POS) (lanjutan).
```
```
Hasil Pengujian Black Box – Point of Sales (POS)
```
Nama
Pengujian
Bentuk
Pengujian Hasil Yang Diharapkan
Hasil
Pengujian
Persentase
```
(10x uji)
```
Penyimpanan
order, item, dan
pajak
Selesaikan
transaksi
Data tersimpan konsisten
pada orders, order_items,
dan order_taxes
Berhasil 100%
Mengubah stok
saat pembuatan
pesanan
Buat order baru
dengan produk
stok terbatas
stock_qty berkurang sesuai
quantity yang dibeli Berhasil 100%
Mengubah stok
saat edit order
existing
Edit order existing
```
(tambah/kurangi
```
```
item)
```
Stok menyesuaikan data
perubahan item
```
(berkurang/bertambah)
```
Berhasil 100%
Integrasi ke
```
KDS: order
```
muncul
Selesaikan
transaksi POS lalu
buka KDS
Order muncul pada KDS
sesuai status pemenuhan
```
awal (confirmed)
```
Berhasil 100%
```
5.2.2 Pengujian Black Box – Kitchen Display System (KDS)
```
```
Berdasarkan pengujian terhadap modul kitchen display system (KDS) yang
```
telah dilakukan, hasil dan persentase dari 10 kali pengujian disajikan pada Tabel
5.2. Pada Tabel 5.2, ditampilkan juga skenario pengujian black box pada modul
```
kitchen display system (KDS) untuk memverifikasi kemampuan sistem
```
menampilkan antrean pesanan, melakukan perubahan status pemrosesan,
menerapkan pembatasan hak akses, serta memastikan sinkronisasi tampilan setelah
aksi dilakukan.
```
Tabel 5.2 Pengujian black box kitchen display system (KDS).
```
```
Hasil Pengujian Black Box – Kitchen Display System (KDS)
```
Nama
Pengujian Bentuk Pengujian
Hasil Yang
Diharapkan
Hasil
Pengujian
Persentase
```
(10x uji)
```
Login dan
pembatasan
hak akses
Aktor kitchen staff
melakukan login serta
navigasi halaman dan hak
akses dibatasi berdasarkan
peran
Aktor kasir berhasil
login dan hak akses
seusai dengan peran
yang diberikan
Berhasil 100%
162
```
Tabel 5.2 Pengujian black box kitchen display system (KDS) (lanjutan).
```
```
Hasil Pengujian Black Box – Kitchen Display System (KDS)
```
Nama
Pengujian Bentuk Pengujian
Hasil Yang
Diharapkan
Hasil
Pengujian
Persentase
```
(10x uji)
```
Menampilkan
pesanan aktif
sesuai kriteria
KDS
Membuka halaman KDS
dan memeriksa data yang
tampil
Sistem hanya
menampilkan
pesanan dengan
order_type dine-
in/takeaway dan
payment_status
paid/pay_later, lalu
mengelompokkan
berdasarkan
fulfillment_status
```
(confirmed,
```
```
preparing, ready)
```
Berhasil 100%
Pencarian
order
berdasarkan
id/reference
Mengisi kolom pencarian
dengan id atau reference_no
Sistem menampilkan
order yang sesuai
kata kunci pencarian
dan tetap mengikuti
filter tenant
Berhasil 100%
Mulai
menyiapkan
pesanan per
item
Klik aksi “Start Preparing”
pada item berstatus queued
Status item berubah
queued → preparing
dan tampilan KDS
terbaru
Berhasil 100%
Menandakan
Ready per
item
Klik aksi “Mark Ready”
pada item berstatus queued
atau preparing
Status item berubah
menjadi ready,
ready_at tercatat, dan
tampilan KDS terbaru
Berhasil 100%
Mulai
menyiapkan
level order
Klik aksi “Start Preparing”
pada level order
Semua item aktif
dalam order masuk
status preparing dan
order ikut tersinkron
Berhasil 100%
Menandakan
Ready level
order
Klik aksi “Mark Order
Ready” pada level order
Semua item aktif
dalam order menjadi
ready dan order ikut
tersinkron
Berhasil 100%
Refresh data
setelah aksi
```
(sinkronisasi
```
```
tampilan)
```
Melakukan aksi status, lalu
amati perubahan daftar
tanpa reload manual
Setelah aksi berhasil,
daftar KDS otomatis
menampilkan kondisi
```
terbaru (refresh
```
```
Livewire)
```
Berhasil 100%
163
5.2.3 Pengujian Black Box – Waiter Orders
Berdasarkan pengujian terhadap modul Waiter Orders yang telah
dilakukan, hasil dan persentase dari 10 kali pengujian disajikan pada Tabel 5.3.
Pada Tabel 5.3, ditampilkan juga skenario pengujian black box pada modul Waiter
Orders untuk memverifikasi tampilan daftar pesanan berdasarkan status, proses
```
penyajian (serve) per item maupun secara kolektif, fungsi pencarian, pembatasan
```
otorisasi, serta pembaruan data tanpa pemuatan ulang halaman.
Tabel 5.3 Pengujian black box Waiter Orders.
Hasil Pengujian Black Box – Waiter Orders
```
Nama Pengujian Bentuk Pengujian Hasil YangDiharapkanHasilPengujianPersentase(10x uji)
```
Login dan
pembatasan hak
akses
Aktor pelayan
melakukan login serta
navigasi halaman dan
hak akses dibatasi
berdasarkan peran
Aktor kasir
berhasil login dan
hak akses seusai
dengan peran yang
diberikan
Berhasil 100%
Menampilkan
“Ready to Serve”
Pastikan ada item
berstatus ready
Order dengan
minimal satu item
ready tampil pada
daftar Ready to
Serve
Berhasil 100%
Menampilkan
“PREPARING”
Pastikan ada order
confirmed/preparing
Order tampil pada
daftar In Progress
sesuai
fulfillment_status
Berhasil 100%
Polling refresh
data
Diamkan halaman
beberapa detik / trigger
refreshOrders
Daftar berubah
mengikuti status
terbaru tanpa
reload manual
Berhasil 100%
Serve per item
```
(hanya ready)
```
Klik Serve pada item
ready
Item berubah
ready → served
dan hilang dari
daftar ready pada
refresh berikutnya
Berhasil 100%
Serve All Ready
```
Items (valid)
```
Pastikan semua item
non-cancelled sudah
ready, klik Serve All
Semua item ready
menjadi served
dan order keluar
dari Ready to
Serve
Berhasil 100%
164
```
Tabel 5.3 Pengujian black box Waiter Orders (lanjutan).
```
Hasil Pengujian Black Box – Waiter Orders
```
Nama Pengujian Bentuk Pengujian Hasil YangDiharapkanHasilPengujianPersentase(10x uji)
```
Serve All ditolak
saat ada item
pending
Sisakan item
queued/preparing lalu
klik Serve All
Sistem menolak
dan menampilkan
pesan “All items
must be ready…”
Berhasil 100%
Otorisasi
waiter_serve_order
Login tanpa permission
lalu coba Serve
Aksi tidak
```
dijalankan (status
```
```
tidak berubah)
```
Berhasil 100%
```
Pencarian order Isi orderSearch(id/reference/customer)Daftar terfiltersesuai kata kunci Berhasil 100%
```
Statistik
operasional tampil
Buka halaman Waiter
Orders
Statistik
ready/preparing/
queue/served
terhitung dan
tampil
Berhasil 100%
5.2.4 Pengujian Black Box – Waiter Take Order
Berdasarkan pengujian terhadap modul Waiter Take Orders yang telah
dilakukan, hasil dan persentase dari 10 kali pengujian disajikan pada Tabel 5.4.
Pada Tabel 5.4, ditampilkan juga skenario pengujian black box pada modul Waiter
Take Order untuk memverifikasi proses pembuatan pesanan oleh pelayan, mulai
dari pemuatan katalog, filter produk/kategori, pengelolaan keranjang, validasi data
pelanggan dan meja, validasi stok, hingga pembentukan order dengan sumber
Waiter serta integrasi ke alur POS/KDS.
Tabel 5.4 Pengujian black box Waiter Take Order.
Hasil Pengujian Black Box – Waiter Take Orders
Nama
Pengujian
Bentuk
Pengujian Hasil Yang Diharapkan
Hasil
Pengujian
Persentase
```
(10x uji)
```
Memuat
katalog
produk
Buka halaman
Waiter Take
Order
```
Produk tampil (maksimum
```
```
sesuai limit) dan dapat
```
diakses tanpa error
Berhasil 100%
165
```
Tabel 5.4 Pengujian black box Waiter Take Order (lanjutan).
```
Hasil Pengujian Black Box – Waiter Take Orders
```
Nama Pengujian Bentuk Pengujian Hasil YangDiharapkanHasilPengujianPersentase(10x uji)
```
Filter pencarian produk Isi search term
Produk terfilter
sesuai
nama/deskripsi
Berhasil 100%
Filter kategori produk Pilih kategori Produk terfiltersesuai kategori Berhasil 100%
Buka modal detail
produk Klik salah satu produk
Modal
menampilkan
detail, opsi, dan
```
pricing (diskon
```
```
bila ada)
```
Berhasil 100%
Menambah item dengan
opsi & catatan
Pilih opsi, isi note,
pilih qty, klik Add
Item masuk
keranjang
dengan opsi &
catatan
tersimpan
Berhasil 100%
Mengubah item
keranjang dari waiter
Klik edit item pada
cart
Modal memuat
data item lama
dan perubahan
tersimpan
setelah Add
Berhasil 100%
Mengurangi/Menambah
item
Klik +/− pada item
cart
Quantity
berubah dan
ringkasan
transaksi ikut
berubah
Berhasil 100%
Pemilihan meja dine-in Buka table picker lalupilih meja
tableId, label,
dan orderType
terset dan
tersimpan pada
session
Berhasil 100%
Kirim ke kasir tanpa
item
Klik Send to Cashier
saat cart kosong
Sistem menolak
dan
menampilkan
peringatan
minimal 1 item
Berhasil 100%
Validasi customer wajib
Kosongkan
customerName/email
lalu klik “Send to
Cashier”
Sistem menolak
dan
menampilkan
error validasi
Berhasil 100%
166
```
Tabel 5.4 Pengujian black box Waiter Take Order (lanjutan).
```
Hasil Pengujian Black Box – Waiter Take Orders
```
Nama Pengujian Bentuk Pengujian Hasil YangDiharapkanHasilPengujianPersentase(10x uji)
```
Validasi stok
sebelum create
order
Buat qty melebihi
stok lalu klik “Send
to Cashier”
Sistem menampilkan
modal stockIssues dan
tidak membuat order
Berhasil 100%
Create order
sukses
```
(source=waiter)
```
Isi lengkap, stok
cukup, klik “Send
to Cashier”
Order terbentuk dengan
reference W-...,
```
source=waiter, items &
```
taxes tersimpan
Berhasil 100%
Merubah status
meja saat dine-in
Kirim order dine-
in dengan tableId
Status meja berubah
menjadi occupied Berhasil 100%
Payment status
sesuai payNow
Uji payNow=true
dan payNow=false
```
payNow=true →
```
pending, payNow=false
→ pay_later
Berhasil 100%
Integrasi ke
POS/KDS
Setelah order
terkirim
Order terlihat di kasir
dan masuk alur
```
pemrosesan (KDS)
```
Berhasil 100%
5.2.5 Pengujian Black Box – Customer Check Order
Berdasarkan pengujian terhadap modul customer check order yang telah
dilakukan, hasil dan persentase dari 10 kali pengujian disajikan pada Tabel 5.5.
Pada Tabel 5.5, ditampilkan juga skenario pengujian black box pada fitur customer
check order untuk memverifikasi akses halaman, validasi input reference number,
keterbaruan status pesanan sesuai perubahan di KDS/Waiter, penampilan detail
```
item (termasuk opsi dan catatan), serta konsistensi perhitungan total dan pajak.
```
Tabel 5.5 Pengujian black box customer check order.
Hasil Pengujian Black Box – Customer Check Order
Nama
Pengujian
Bentuk
Pengujian Hasil Yang Diharapkan
Hasil
Pengujian
Persentase
```
(10x uji)
```
Akses halaman
cek status
pesanan
Buka halaman
Customer Check
Order
Halaman dapat diakses
dan menampilkan
form/komponen pencarian
order
Berhasil 100%
Input
reference_no
valid
Masukkan
reference_no
yang ada
Sistem menampilkan
ringkasan order dan status
pemenuhan terbaru
Berhasil 100%
167
```
Tabel 5.5 Pengujian black box customer check order (lanjutan).
```
Hasil Pengujian Black Box – Customer Check Order
Nama
Pengujian
Bentuk
Pengujian Hasil Yang Diharapkan
Hasil
Pengujian
Persentase
```
(10x uji)
```
Input
reference_no
tidak valid
Masukkan
reference_no
yang tidak
ada
Sistem menampilkan pesan order
tidak ditemukan Berhasil 100%
Perubahan
status
tercermin
Ubah status
item di
KDS/Waiter
lalu cek
ulang
Status di customer ikut berubah
```
(confirmed/preparing/ready/served) Berhasil 100%
```
Menampilkan
detail item &
catatan
Buka order
yang punya
opsi/catatan
Detail item, opsi, dan catatan tampil
sesuai data tersimpan Berhasil 100%
Konsistensi
total & pajak
Bandingkan
dengan data
order
Total, pajak, dan grand total
konsisten dengan data di basis data Berhasil 100%
5.3 User Acceptance Test
```
User acceptance test (UAT) dilaksanakan berdasarkan rancangan pada Bab
```
3.8 untuk mengukur tingkat penerimaan pengguna terhadap sistem. Bagian ini
menyajikan hasil penilaian responden pada tiap peran dalam bentuk skor, rata-rata,
dan persentase penerimaan. Pengujian user acceptance test dilakukan dengan
memberikan survei berupa 10 pernyataan kepada karyawan Kasumba Coffee.
Pernyataan disusun untuk mengevaluasi kemudahan pencatatan pesanan, kejelasan
informasi produk dan harga, proses pembayaran, serta dukungan sistem terhadap
kecepatan pelayanan. Skenario pengujian dilakukan pada empat role, yaitu cashier
untuk modul POS, kitchen staff untuk modul KDS, waiter untuk modul Waiter,
dan customer untuk modul customer check order.
5.3.1 Skenario Pengujian pada Role Cashier
Pengujian pada role cashier bertujuan menilai sejauh mana sistem
membantu proses menambahkan pesanan dan koordinasi dengan kitchen, waiter
sampai dengan customer. Pernyataan difokuskan pada kejelasan navigasi pesanan,
168
kemudahan pembuatan pesanan, kecepatan pembuatan pesanan, serta pengurangan
kesalahan pembuatan pesanan. Pada Tabel 5.6, ditunjukkan hasil pengujian
terhadap role cashier berupa skor pertanyaan, rata-rata skor, dan persentase skor.
Tabel 5.6 Hasil pengujian terhadap role cashier.
Hasil UAT – Role Cashier
No. Pernyataan Nilai
1. Saya merasa proses pencatatan pesanan melalui POS mudah
dipahami dan digunakan
4
2. Saya merasa informasi produk dan harga pada POS ditampilkan
dengan jelas
5
3. Saya merasa proses pembayaran dan konfirmasi transaksi mudah
dilakukan
5
4. Saya merasa sistem membantu mengurangi kesalahan pencatatan
pesanan
4
5. Saya merasa tampilan POS mendukung kecepatan pelayanan kepada
pelanggan
5
6. Saya merasa pemilihan tipe pesanan (dine-in/takeaway) pada POS
mudah dilakukan dan tidak membingungkan.
5
7. Saya merasa validasi sistem saat checkout (misalnya cart kosong,
```
belum pilih customer, dine-in wajib pilih meja) membantu
```
mencegah kesalahan transaksi.
5
8. Saya merasa ringkasan transaksi (subtotal, pajak/diskon bila ada,
```
total akhir) mudah dipahami sebelum konfirmasi pembayaran.
```
4
9. Saya merasa setelah transaksi selesai, status pesanan terbentuk dan
dapat diteruskan ke proses dapur tanpa langkah tambahan yang
rumit.
5
10. Saya merasa pencarian/identifikasi pesanan (misalnya melalui
```
reference_no) membantu saat perlu melakukan pengecekan pesanan.
```
3
```
Skor Pertanyaan (S) 45
```
```
Rata-rata Skor (R) 4,5
```
```
Persentase Skor (P) 90%
```
169
```
Tabel 5.6 menunjukkan bahwa hasil user acceptance test (UAT) modul POS
```
```
oleh role cashier. Berdasarkan Persamaan (2.1), Persamaan (2.2), serta Persamaan
```
```
(2.3), diperoleh skor total 45 dengan rata-rata 4,5 dan persentase penerimaan 90%,
```
yang mengindikasikan modul POS dinilai mudah digunakan, informatif, dan
mendukung kecepatan serta akurasi pelayanan.
5.3.2 Skenario Pengujian pada Role Kitchen Staff
Pengujian pada role kitchen staff dilakukan untuk mengevaluasi kemudahan
```
penggunaan kitchen display system (KDS) dalam memantau antrean pesanan,
```
memperbarui status pemrosesan, serta mengatur prioritas produksi. Pernyataan
difokuskan pada keterbacaan informasi dan dukungan sistem terhadap koordinasi
lintas peran. Pada Tabel 5.7, ditunjukkan hasil pengujian terhadap role kitchen staff
berupa skor pertanyaan, rata-rata skor, dan persentase skor.
Tabel 5.7 Hasil pengujian terhadap role kitchen staff.
Hasil UAT – Role Kitchen Staff
No. Pernyataan Nilai
1. Saya merasa daftar pesanan pada KDS mudah dipahami 5
2. Saya merasa perubahan status pesanan mudah dilakukan 3
3. Saya merasa informasi item dan catatan pesanan ditampilkan
dengan jelas
5
4. Saya merasa KDS membantu mengatur prioritas pesanan 4
5. Saya merasa KDS mempercepat koordinasi dengan kasir dan
waiter
5
6. Saya merasa informasi waktu/urutan antrean pesanan pada KDS
cukup membantu untuk menentukan prioritas.
4
7. Saya merasa perubahan status pesanan (confirmed → preparing →
```
ready) pada KDS mudah dilakukan dan tidak menimbulkan
```
kekeliruan.
5
8. Saya merasa KDS membantu mengurangi ketergantungan pada
komunikasi manual dengan kasir terkait pesanan masuk.
4
170
```
Tabel 5.7 Hasil pengujian terhadap role kitchen staff (lanjutan).
```
Hasil UAT – Role Kitchen Staff
No. Pernyataan Nilai
9. Saya merasa KDS memudahkan pemantauan pesanan yang masih
dalam proses dibandingkan pencatatan manual.
4
10. Saya merasa tampilan KDS tetap jelas dan mudah dibaca saat
jumlah pesanan meningkat.
4
```
Skor Pertanyaan (S) 43
```
```
Rata-rata Skor (R) 4,3
```
```
Persentase Skor (P) 86%
```
```
Tabel 5.7 menunjukkan bahwa hasil user acceptance test (UAT) pada
```
```
modul KDS oleh role kitchen staff. Berdasarkan Persamaan (2.1), Persamaan (2.2),
```
```
serta Persamaan (2.3), diperoleh skor total 43 dengan rata-rata 4,3 dan persentase
```
penerimaan 86%, yang mengindikasikan modul KDS dinilai mudah digunakan,
informatif, dan mendukung kecepatan serta akurasi pelayanan.
3.5.3 Skenario Pengujian pada Role Waiter
```
Pengujian pada role waiter bertujuan menilai sejauh mana sistem (modul
```
```
Waiter) membantu proses penyajian pesanan dan koordinasi dengan dapur.
```
Pernyataan difokuskan pada kejelasan informasi pesanan siap antar, kemudahan
penandaan status served, serta pengurangan kesalahan penyajian. Pada Tabel 5.8,
ditunjukkan hasil pengujian terhadap role waiter berupa skor pertanyaan, rata-rata
skor, dan persentase skor.
Tabel 5.8 Hasil pengujian terhadap role waiter.
Hasil UAT – Role Waiter
No. Pernyataan Nilai
1. Saya merasa informasi pesanan siap antar mudah dipahami 5
2. Saya merasa informasi meja dan detail pesanan membantu proses
penyajian
5
171
Hasil UAT – Role Waiter
No. Pernyataan Nilai
3. Saya merasa proses penandaan pesanan sebagai served mudah
dilakukan
5
4. Saya merasa sistem membantu mengurangi kesalahan penyajian 4
5. Saya merasa sistem meningkatkan koordinasi dengan dapur 5
6. Saya merasa daftar pesanan “ready” yang harus disajikan mudah
dipantau oleh waiter.
5
7. Saya merasa informasi meja dan detail item pada halaman waiter
cukup lengkap untuk menghindari salah antar.
3
8. Saya merasa proses penandaan pesanan sebagai served membantu
memastikan status pesanan konsisten dengan kondisi di lapangan.
5
9. Saya merasa sinkronisasi status antara KDS dan waiter berjalan
baik sehingga tidak terjadi miskomunikasi saat penyajian.
5
10. Saya merasa fitur pencarian/penyaringan pesanan pada halaman
waiter membantu mempercepat penyajian.
4
```
Skor Pertanyaan (S) 46
```
```
Rata-rata Skor (R) 4,6
```
```
Persentase Skor (P) 92%
```
```
Tabel 5.8 menunjukkan bahwa hasil user acceptance test (UAT) modul
```
```
Waiter (Waiter Orders dan Waiter Take Orders) oleh role waiter. Berdasarkan
```
```
Persamaan (2.1), Persamaan (2.2), serta Persamaan (2.3), diperoleh skor total 46
```
dengan rata-rata 4,6 dan persentase penerimaan 92%, yang mengindikasikan modul
Waiter dinilai mudah digunakan, informatif, dan mendukung kecepatan serta
akurasi pelayanan.
3.5.4 Skenario Pengujian pada Role Customer
Pengujian pada modul customer check order dilakukan untuk mengetahui
persepsi pelanggan terhadap kemudahan akses dan manfaat fitur pelacakan
pesanan. Pernyataan diarahkan pada kejelasan informasi status, kenyamanan
menunggu, serta pengurangan ketergantungan pelanggan pada kasir atau waiter
172
untuk menanyakan progres pesanan. Pada Tabel 5.9, ditunjukkan hasil pengujian
terhadap role customer berupa skor pertanyaan, rata-rata skor, dan persentase skor.
Tabel 5.9 Hasil pengujian terhadap role customer.
Hasil UAT – Role Waiter
No Pernyataan NilaiR1 R2 R3
1. Saya merasa fitur check order mudah
digunakan 3 4 4
2. Saya merasa informasi status pesanan
ditampilkan dengan jelas 4 5 4
3. Saya merasa fitur ini membantu
mengetahui progres pesanan 5 5 4
4. Saya merasa tidak perlu bertanya ke
kasir/waiter untuk mengetahui status
pesanan
5 4 4
5. Saya merasa fitur ini meningkatkan
kenyamanan saat menunggu pesanan 5 4 5
6. Saya merasa tampilan status pesanan
```
(misalnya
```
```
confirmed/preparing/ready/served)
```
mudah dipahami.
5 5 5
7. Saya merasa informasi detail pesanan
```
(item yang dipesan) membantu
```
memastikan pesanan saya benar.
4 4 5
8. Saya merasa pembaruan status pesanan
ditampilkan tepat waktu sesuai progres
yang terjadi.
4 3 4
9. Saya merasa fitur ini mengurangi
kebutuhan saya untuk bertanya ke
kasir/waiter saat menunggu pesanan.
4 5 5
10. Saya merasa fitur check order
meningkatkan kenyamanan selama
menunggu pesanan.
5 4 4
```
Skor Pertanyaan (S) 44 43 44
```
```
Rata-rata Skor (R) 43,6
```
```
Persentase Skor (P) 87,2%
```
```
Tabel 5.9 menunjukkan bahwa hasil user acceptance test (UAT) modul
```
```
customer check/track order oleh role customer. Berdasarkan Persamaan (2.1),
```
173
```
Persamaan (2.2), serta Persamaan (2.3), diperoleh skor total 44 untuk responden
```
pertama, skor total 43 untuk responden kedua, serta skor total 44 untuk responden
ketiga dengan rata-rata 4,36 dan persentase penerimaan 87,2%, yang
mengindikasikan modul customer check/track order dinilai mudah digunakan,
informatif, dan mendukung kecepatan serta akurasi pelayanan.
3.5.5 Kesimpulan Pengujian UAT
```
Berdasarkan hasil user acceptance test (UAT) di Kasumba Coffee Shop
```
Bandung, seluruh role menunjukkan tingkat penerimaan yang tinggi. Role Cashier
memberikan hasil persentase penerimaan sebesar 90% yang menunjukkan modul
POS mudah digunakan, informatif, dan mendukung proses pelayanan. Role Kitchen
Staff memberikan hasil persentase penerimaan sebesar 86% yang menunjukkan
modul KDS dinilai jelas dan bermanfaat. Role Waiter memberikan hasil persentase
penerimaan sebesar 92% yang menandakan sistem sangat membantu koordinasi
penyajian dan meminimalkan kesalahan penandaan pesanan. Role Customer juga
memberikan hasil persentase penerimaan yang tinggi sebesar 87,2%, menunjukkan
pelanggan terbantu memantau status pesanan secara mandiri sehingga mengurangi
kebutuhan bertanya kepada kasir atau pelayan. Hal ini memperkuat temuan bahwa
sistem tidak hanya memberikan manfaat bagi karyawan internal, tetapi juga
meningkatkan pengalaman pelanggan.
Secara keseluruhan, hasil UAT menunjukkan bahwa sistem manajemen
operasional kafe yang dikembangkan telah memenuhi sebagian besar kebutuhan
pengguna di Kasumba Coffee dan dapat diterima dengan baik untuk digunakan
dalam mendukung proses operasional kafe. Dengan tingkat penerimaan berkisar di
86% – 92% pada seluruh peran utama, sistem dinilai layak sebagai solusi
pendukung alur pemesanan terintegrasi POS–KDS–Waiter serta fitur pelacakan
pesanan oleh customer.
5.4 Evaluasi, Temuan, dan Keterbatasan
Evaluasi dilakukan untuk menilai kesesuaian implementasi sistem terhadap
kebutuhan yang telah dirancang pada Bab III. Berdasarkan hasil pengujian
```
fungsional (black box) dan user acceptance test (UAT), sistem menunjukkan bahwa
```
174
alur pemrosesan pesanan terintegrasi POS–KDS–Waiter dapat berjalan sesuai
skenario operasional kafe, mulai dari pencatatan dan pembayaran pesanan pada
POS, pemrosesan status di KDS, hingga penyajian pesanan oleh pelayan. Selain itu,
fitur customer check/track order mampu memberikan akses informasi read-only
bagi pelanggan untuk memantau status pesanan secara mandiri. Namun demikian,
beberapa keterbatasan yang masih ditemukan dalam penelitian ini dirincikan
sebagai berikut.
1. Sistem belum memiliki mekanisme notifikasi otomatis antar peran
```
(misalnya notifikasi real-time ke pelayan ketika pesanan siap).
```
2. Pengujian dilakukan dalam skala terbatas pada lingkungan pengembangan
berbasis Docker, sehingga performa pada server produksi belum
sepenuhnya diuji.
3. Integrasi multi-tenant belum dievaluasi secara menyeluruh untuk skenario
penggunaan paralel oleh banyak tenant.
4. Fitur pelaporan dan dashboard performa belum mencakup analisis waktu
```
rata-rata penyelesaian pesanan (service time per item/order).
```
Meskipun terdapat keterbatasan tersebut, hasil uji fungsional dan UAT
menunjukkan bahwa sistem telah memenuhi kebutuhan utama pengguna dalam
pemrosesan pesanan dan koordinasi antar peran pada lingkungan kafe. Hasil UAT
menunjukkan tingkat penerimaan pengguna dengan persentase modul POS sebesar
90%, modul KDS sebesar 86%, modul Waiter sebesar 92%, dan modul customer
check/track order sebesar 87,2%. Nilai ini mengindikasikan bahwa sistem dinilai
mudah digunakan serta membantu operasional pemrosesan pesanan. Persentase
yang lebih rendah pada modul KDS menunjukkan adanya ruang perbaikan,
terutama terkait kenyamanan dan kemudahan interaksi ketika melakukan
perubahan status pesanan.
Dengan demikian, sistem pemrosesan pesanan kafe berbasis web dengan
integrasi POS–KDS–Waiter dapat disimpulkan berhasil diimplementasikan sesuai
rancangan dan mampu berjalan stabil pada lingkungan pengembangan. Hasil
evaluasi pada bagian ini dapat digunakan sebagai dasar untuk pengembangan
175
berikutnya, terutama pada penambahan notifikasi real-time antar peran, pengujian
performa di lingkungan produksi, evaluasi multi-tenant pada skala penggunaan
paralel, serta pengembangan metrik operasional seperti service time untuk
mendukung monitoring dan pengambilan keputusan berbasis data.
176
BAB VI
PENUTUP
6.1 Kesimpulan
Berdasarkan hasil perancangan, implementasi, dan pengujian fungsional
yang telah dilakukan, beberapa kesimpulan yang dapat diambil:
1. Sistem pemrosesan pesanan kafe berbasis web berhasil diimplementasikan
```
dengan mengintegrasikan modul point of sales (POS), kitchen display system
```
```
(KDS), serta Waiter. Integrasi ini memungkinkan alur pemesanan berjalan
```
lebih terstruktur, mulai dari pencatatan pesanan dan penyelesaian transaksi
di POS, pemrosesan dan pembaruan status produksi di KDS, hingga
penyajian pesanan oleh pelayan.
2. Fitur customer check/track order yang bersifat read-only berhasil
memberikan dukungan informasi kepada pelanggan. Fitur ini
memungkinkan pelanggan melakukan pengecekan dan pelacakan status
pesanan berdasarkan data yang tersinkron dari proses POS, KDS, dan Waiter.
3. Fungsi-fungsi utama pada setiap modul dapat berjalan sesuai skenario yang
dirancang, serta status pesanan dapat tersinkron dengan baik antarmodul
dalam batasan pengujian yang dilakukan, dengan tingkat keberhasilan
mencapai 100% untuk tiap modul, berdasarkan black box testing.
4. Sistem yang dirancang dapat diterima dan digunakan sesuai kebutuhan
operasional kafe, dengan tingkat penerimaan berkisar di 86 – 92%
berdasarkan user acceptance test. Tingkat penerimaan tersebut menunjukkan
alur kerja pemesanan dinilai mudah dipahami, fungsi-fungsi utama berjalan
konsisten, dan informasi status pesanan yang ditampilkan pada masing-
masing modul sesuai dengan ekspektasi pengguna.
177
6.2 Saran Pengembangan
Untuk pengembangan selanjutnya, beberapa hal yang disarankan adalah:
1. Menambahkan pengujian nonfungsional, seperti load test untuk
```
memastikan kestabilan pada jam sibuk, serta pengujian keamanan (misalnya
```
```
uji kontrol akses dan validasi masukan).
```
2. Memperluas dukungan operasional, misalnya pemisahan alur produksi
```
untuk kategori item tertentu (minuman/makanan) atau peningkatan fitur
```
antrian produksi sesuai kebutuhan kafe.
3. Memisahkan alur produksi makanan dan minuman menjadi dua
stasiun/modul berbeda, yaitu kitchen untuk makanan dan bar untuk
```
minuman. Produk diberi atribut “station” (kitchen/bar) sehingga item
```
```
pesanan otomatis diteruskan ke layar/antrian yang sesuai (kitchen display
```
```
untuk makanan dan bar display untuk minuman), namun tetap berada dalam
```
satu order yang sama.
4. Memperkuat aspek operasional dan audit, seperti pencatatan timestamp dan
```
aktor pada setiap perubahan status (siapa mengubah, kapan, dari status apa
```
```
ke status apa), serta ringkasan metrik durasi proses (waktu tunggu–waktu
```
```
produksi–waktu tersaji) untuk evaluasi layanan.
```
178
DAFTAR PUSTAKA
[1] Badan Pusat Statistik, Statistik Penyediaan Makanan dan Minuman 2023, 7th
ed., Badan Pusat Statistik, Jakarta, 2024.
[2] A. E.S Saputra, E. Rusdianto, Z. Ernaningsih , “Pembangunan Sistem Informasi
Manajemen Inventaris Toko dan Gudang Berbasis Website”, Jurnal
Informatika Atma Jogja, vol. 5, no. 1, pp. 11–18, 2025.
[3] I. P. A. Dharmaadi dan G. M. Arya Sasmitha, “Perancangan Sistem Informasi
Restoran Terintegrasi Berbasis Java Web Socket Online”, Jurnal Penelitian
Pos dan Informatika, vol. 8, no. 1, pp. 51–62, 2018.
[4] P. Garbarz dan M. Plechawska-Wójcik, “Comparative analysis of PHP
frameworks on the example of Laravel and Symfony”, Journal of Computer
Sciences Institute, vol. 22, 2022.
```
[5] A. Susila, “Aplikasi Point Of Sales (POS) Berbasis Website Dengan
```
```
Menggunakan Laravel (Studi Kasus: Bakmi Djowo)”, Jurnal Ilmu
```
Komputer dan Pendidikan, vol. 2, no. 1, pp. 160–167, 2023.
[6] E. Astriyani, A. Rahmani, A. Elvina, dan F. Noviantika, “Web-Based Food
Ordering Information System at Naonaru’s Kitchen”, Collabits Journal,
vol. 2, no. 1, pp. 32–35, 2025.
[7] M. F. Khandwani, P. Lanke, P. Harne, A. Sapkal, dan A. Adhao, “Restaurant
Management System”, International Journal for Research in Applied
Science and Engineering Technology, vol. 11, no. 4, pp. 4129–4133, 2023.
```
[8] --, “Understanding Point of Sale (POS) Systems: Features and Benefits”,
```
Investopedia, https://www.investopedia.com/terms/p/point-of-sale.asp, 11
Oktober 2025.
```
[9] --, “Kitchen Display System (KDS): why your restaurant kitchen needs it”,
```
LS Retail, n.d., https://www.lsretail.com/resources/what-is-restaurant-
kitchen-display-system, 11 Oktober 2025.
[10] --, “How does the waiter ordering system work?”, Upmenu, n.d.,
```
https://www.upmenu.com/blog/how-does-the-waiter-ordering-system-
```
work/, 15 Oktober 2025.
[11] F. Viktor, "Software Development Models: Iterative and Incremental
Development", Technology Conversations,
```
http://technologyconversations.com/2014/01/21/software-development-
```
models-iterative-and-incremental-development/, 31 Januari 2026.
179
[12] Larman, C. dan Basili, V., Iterative and Incremental Development: A Brief
History, Computer, vol. 36, no. 6, pp. 47–56, 2003.
```
[13] Priyatna, B., Hananto, A. L., dan Nova, M, “Application of UAT (User
```
```
Acceptance Test) Evaluation Model in Minggon E-Meeting Software
```
Development”, Systematics, vol. 2, no. 3, pp. 110–117, 2022.
```
[14] --, “Laravel Documentation (v11.x)”, Laravel, n.d., https://laravel.com/docs,
```
21 Oktober 2025.
[15] Aniche, M., Bavota, G., Treude, C., Gerosa, M. A., dan Van Deursen, A, “Code
smells for model-view-controller architectures”, Empirical Software
Engineering, vol. 23, no. 4, pp. 2121–2157, 2018.
[16] --, “Eloquent ORM”, Laravel, n.d., https://laravel.com/docs/5.0/eloquent, 1
November 2025.
[17] --, “Livewire Documentation”, Laravel Livewire, n.d.,
```
https://livewire.laravel.com/docs , 1 November 2025.
```
[18] --, “Filament Components”, Filament Docs, n.d.,
```
https://filamentphp.com/docs/4.x/components/overview, 3 November
```
2025.
[19] --, “MySQL: Understanding What It Is and How It's Used”, Oracle,
```
https://www.oracle.com/asean/mysql/what-is-mysql/, 10 November 2025.
```
[20] --, “What is PHP and what can it do?”, PHP Docs, n.d.,
```
https://www.php.net/manual/en/introduction.php, 10 November 2025.
```
[21] --, “What is a Container?”, Docker Docs, n.d., https://docs.docker.com/get-
started/overview/, 11 November 2025.
[22] --, “What is Docker?”, Docker Docs, n.d., https://docs.docker.com/get-
started/docker-overview/, 11 November 2025.
180
BIODATA MAHASISWA
Nama Mahasiswa : Yuda Nadhika
```
NIM : 2112012114008
```
```
Konsentrasi : Full-stack Web Developer
```
Tempat/Tgl. Lahir : Bekasi, 24 Mei 2003
Alamat Sekarang : Villa Taman Kartini, Jl. Graha
Permai V Blok E5/11, Margahayu,
Bekasi, Jawa Barat
No. Telepon/HP : 081281381395
Nama Orang Tua : Nana Dharmana
Alamat Orang Tua : Villa Taman Kartini, Jl. Graha
Permai V Blok E5/11, Margahayu,
Bekasi, Jawa Barat
No Telepon/HP : 081311971120
IP Kumulatif :
Tanggal Lulus :
Masa Studi :
Pengalaman dan Prestasi yang pernah diraih:
Penulis memulai pengalaman kerja menjadi mahasiswa magang dengan jabatan
```
full-stack web developer di UPTTIK (Unit Pelaksana Teknis Teknologi Informasi
```
```
dan Komunikasi) pada Juli 2023 – September 2023. Kemudian, penulis menjadi
```
DevOps Engineer Intern di PT Sigma Cipta Caraka pada Jul 2024 – Aug 2024. Pada
tahun 2024, penulis juga menjabat sebagai front-end web developer di Perusahaan
Webdologi. Saat ini, penulis sedang mengembangkan aplikasi sistem pintar kafe
Bernama Qash di bawah PT. Rayu Inovasia.
Semarang, 19 Januari 2026
Yuda Nadhika
181
LAMPIRAN A
```
KODE PROGRAM (DDL BASIS DATA)
```
Lampiran ini memuat definisi skema basis data dalam bentuk DDL MySQL
yang digunakan pada sistem, meliputi pembentukan tabel, relasi foreign key,
indeks, dan constraint. DDL berikut merupakan hasil skema setelah proses
pembentukan tabel dilakukan melalui mekanisme migration pada Laravel.
Kode A.1: DDL MySQL Tabel orders
```
CREATE TABLE `orders` (
```
`id` bigint unsigned NOT NULL AUTO_INCREMENT,
```
`tenant_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
```
`customer_detail_id` bigint unsigned DEFAULT NULL,
`dining_table_id` bigint unsigned DEFAULT NULL,
```
`total` decimal(10,2) NOT NULL,
```
```
`subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
```
```
`total_tax` decimal(12,2) NOT NULL DEFAULT '0.00',
```
```
`grand_total` decimal(12,2) NOT NULL DEFAULT '0.00',
```
`fulfillment_status`
```
enum('confirmed','preparing','ready','served') COLLATE
```
utf8mb4_unicode_ci NOT NULL DEFAULT 'confirmed',
```
`source` enum('pos','qr','waiter') COLLATE utf8mb4_unicode_ci
```
NOT NULL DEFAULT 'pos',
```
`order_type` enum('dine-in','takeaway') COLLATE
```
utf8mb4_unicode_ci NOT NULL DEFAULT 'dine-in',
`payment_status`
```
enum('pending','paid','failed','cancelled','pay_later') COLLATE
```
utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
```
`payment_channel` varchar(120) COLLATE utf8mb4_unicode_ci
```
DEFAULT NULL,
```
`xendit_invoice_id` varchar(255) COLLATE utf8mb4_unicode_ci
```
DEFAULT NULL,
```
`xendit_invoice_url` varchar(255) COLLATE utf8mb4_unicode_ci
```
DEFAULT NULL,
```
`reference_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT
```
NULL,
`confirmed_at` timestamp NULL DEFAULT NULL,
`preparing_at` timestamp NULL DEFAULT NULL,
`ready_at` timestamp NULL DEFAULT NULL,
`expected_seconds_total` int NOT NULL DEFAULT '0',
`queue_seconds` int NOT NULL DEFAULT '0',
`prep_seconds` int NOT NULL DEFAULT '0',
`total_seconds` int NOT NULL DEFAULT '0',
`paid_at` timestamp NULL DEFAULT NULL,
`created_at` timestamp NULL DEFAULT NULL,
`updated_at` timestamp NULL DEFAULT NULL,
```
PRIMARY KEY (`id`),
```
182
UNIQUE KEY `orders_tenant_id_reference_no_unique`
```
(`tenant_id`,`reference_no`),
```
KEY `orders_customer_detail_id_foreign`
```
(`customer_detail_id`),
```
```
KEY `orders_dining_table_id_foreign` (`dining_table_id`),
```
KEY `orders_tenant_id_reference_no_index`
```
(`tenant_id`,`reference_no`),
```
```
KEY `orders_tenant_id_source_index` (`tenant_id`,`source`),
```
KEY `orders_tenant_id_order_type_index`
```
(`tenant_id`,`order_type`),
```
KEY `orders_tenant_id_fulfillment_status_index`
```
(`tenant_id`,`fulfillment_status`),
```
CONSTRAINT `orders_customer_detail_id_foreign` FOREIGN KEY
```
(`customer_detail_id`) REFERENCES `customer_details` (`id`) ON
```
DELETE SET NULL,
CONSTRAINT `orders_dining_table_id_foreign` FOREIGN KEY
```
(`dining_table_id`) REFERENCES `dining_tables` (`id`) ON DELETE
```
SET NULL,
CONSTRAINT `orders_tenant_id_foreign` FOREIGN KEY
```
(`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
```
```
)
```
Kode A.2: DDL MySQL Tabel order_items
```
CREATE TABLE `order_items` (
```
`id` bigint unsigned NOT NULL AUTO_INCREMENT,
```
`tenant_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
```
`order_id` bigint unsigned NOT NULL,
`product_id` bigint unsigned DEFAULT NULL,
```
`product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT
```
NULL,
```
`unit_price` decimal(10,2) NOT NULL,
```
```
`final_price` decimal(10,2) NOT NULL DEFAULT '0.00',
```
```
`discount_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
```
`discount_id` bigint unsigned DEFAULT NULL,
`quantity` int NOT NULL,
`estimate_seconds` int DEFAULT NULL,
`options` json DEFAULT NULL,
`special_instructions` text COLLATE utf8mb4_unicode_ci,
`status`
```
enum('queued','preparing','ready','served','cancelled') COLLATE
```
utf8mb4_unicode_ci NOT NULL DEFAULT 'queued',
`ready_at` timestamp NULL DEFAULT NULL,
`served_at` timestamp NULL DEFAULT NULL,
`created_at` timestamp NULL DEFAULT NULL,
`updated_at` timestamp NULL DEFAULT NULL,
```
PRIMARY KEY (`id`),
```
```
KEY `order_items_order_id_foreign` (`order_id`),
```
KEY `order_items_tenant_id_status_index`
```
(`tenant_id`,`status`),
```
```
KEY `order_items_product_id_index` (`product_id`),
```
```
KEY `order_items_discount_id_index` (`discount_id`),
```
CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY
```
(`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
```
183
CONSTRAINT `order_items_tenant_id_foreign` FOREIGN KEY
```
(`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
```
Kode A.3: DDL MySQL Tabel order_taxes
```
CREATE TABLE `order_taxes` (
```
`id` bigint unsigned NOT NULL AUTO_INCREMENT,
`order_id` bigint unsigned NOT NULL,
`tax_id` bigint unsigned DEFAULT NULL,
```
`name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
```
```
`type` enum('percentage','fixed') COLLATE utf8mb4_unicode_ci
```
NOT NULL,
```
`rate` decimal(10,2) NOT NULL,
```
```
`amount` decimal(12,2) NOT NULL,
```
`created_at` timestamp NULL DEFAULT NULL,
`updated_at` timestamp NULL DEFAULT NULL,
```
PRIMARY KEY (`id`),
```
```
KEY `order_taxes_tax_id_foreign` (`tax_id`),
```
```
KEY `order_taxes_order_id_index` (`order_id`),
```
CONSTRAINT `order_taxes_order_id_foreign` FOREIGN KEY
```
(`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
```
```
CONSTRAINT `order_taxes_tax_id_foreign` FOREIGN KEY (`tax_id`)
```
```
REFERENCES `taxes` (`id`) ON DELETE SET NULL
```
```
)
```
Kode A.4: DDL MySQL Tabel cart_items
```
CREATE TABLE `cart_items` (
```
`id` bigint unsigned NOT NULL AUTO_INCREMENT,
```
`name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
```
```
`price` decimal(10,2) NOT NULL,
```
`quantity` int NOT NULL DEFAULT '1',
`options` json DEFAULT NULL,
```
`item_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
```
```
`session_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT
```
NULL,
`user_id` bigint unsigned DEFAULT NULL,
`created_at` timestamp NULL DEFAULT NULL,
`updated_at` timestamp NULL DEFAULT NULL,
```
PRIMARY KEY (`id`),
```
```
KEY `cart_items_session_id_index` (`session_id`),
```
```
KEY `cart_items_user_id_index` (`user_id`)
```
```
)
```
Kode A.5: DDL MySQL Tabel customer_details
```
CREATE TABLE `customer_details` (
```
`id` bigint unsigned NOT NULL AUTO_INCREMENT,
```
`tenant_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
```
```
`name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
```
```
`email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
```
```
`gender` enum('male','female','na') COLLATE utf8mb4_unicode_ci
```
NOT NULL DEFAULT 'na',
`created_at` timestamp NULL DEFAULT NULL,
184
`updated_at` timestamp NULL DEFAULT NULL,
```
PRIMARY KEY (`id`),
```
UNIQUE KEY `customer_details_tenant_id_email_unique`
```
(`tenant_id`,`email`),
```
CONSTRAINT `customer_details_tenant_id_foreign` FOREIGN KEY
```
(`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
```
```
)
```
Kode A.6: DDL MySQL Tabel products
```
CREATE TABLE `products` (
```
`id` bigint unsigned NOT NULL AUTO_INCREMENT,
```
`tenant_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
```
`category_id` bigint unsigned NOT NULL,
```
`product_image` varchar(255) COLLATE utf8mb4_unicode_ci
```
DEFAULT NULL,
```
`name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
```
```
`alternate_name` varchar(255) COLLATE utf8mb4_unicode_ci
```
DEFAULT NULL,
`description` text COLLATE utf8mb4_unicode_ci,
```
`price` decimal(10,2) NOT NULL,
```
```
`goods_price` decimal(10,2) DEFAULT NULL,
```
`estimated_seconds` int unsigned DEFAULT NULL,
```
`featured` tinyint(1) NOT NULL DEFAULT '0',
```
```
`active` tinyint(1) NOT NULL DEFAULT '1',
```
`stock_qty` int unsigned NOT NULL DEFAULT '1',
`created_at` timestamp NULL DEFAULT NULL,
`updated_at` timestamp NULL DEFAULT NULL,
```
PRIMARY KEY (`id`),
```
UNIQUE KEY `products_tenant_id_name_unique`
```
(`tenant_id`,`name`),
```
```
KEY `products_category_id_foreign` (`category_id`),
```
```
KEY `products_tenant_id_active_index` (`tenant_id`,`active`),
```
CONSTRAINT `products_category_id_foreign` FOREIGN KEY
```
(`category_id`) REFERENCES `categories` (`id`) ON DELETE
```
RESTRICT,
CONSTRAINT `products_tenant_id_foreign` FOREIGN KEY
```
(`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
```
```
)
```
Kode A.7: DDL MySQL Tabel categories
```
CREATE TABLE `categories` (
```
`id` bigint unsigned NOT NULL AUTO_INCREMENT,
```
`tenant_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
```
```
`image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT
```
NULL,
```
`name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
```
`created_at` timestamp NULL DEFAULT NULL,
`updated_at` timestamp NULL DEFAULT NULL,
```
PRIMARY KEY (`id`),
```
UNIQUE KEY `categories_tenant_id_name_unique`
```
(`tenant_id`,`name`),
```
185
CONSTRAINT `categories_tenant_id_foreign` FOREIGN KEY
```
(`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
```
```
)
```
Kode A.8: DDL MySQL Tabel product_option
```
CREATE TABLE `product_options` (
```
`id` bigint unsigned NOT NULL AUTO_INCREMENT,
```
`tenant_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
```
`product_id` bigint unsigned NOT NULL,
```
`name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
```
```
`is_required` tinyint(1) NOT NULL DEFAULT '0',
```
`created_at` timestamp NULL DEFAULT NULL,
`updated_at` timestamp NULL DEFAULT NULL,
```
PRIMARY KEY (`id`),
```
```
KEY `product_options_tenant_id_foreign` (`tenant_id`),
```
```
KEY `product_options_product_id_foreign` (`product_id`),
```
CONSTRAINT `product_options_product_id_foreign` FOREIGN KEY
```
(`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
```
CONSTRAINT `product_options_tenant_id_foreign` FOREIGN KEY
```
(`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
```
```
)
```
Kode A.9: DDL MySQL Tabel product_option_values
```
CREATE TABLE `product_option_values` (
```
`id` bigint unsigned NOT NULL AUTO_INCREMENT,
```
`tenant_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
```
`product_option_id` bigint unsigned NOT NULL,
```
`value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
```
```
`price_adjustment` decimal(8,2) NOT NULL DEFAULT '0.00',
```
`created_at` timestamp NULL DEFAULT NULL,
`updated_at` timestamp NULL DEFAULT NULL,
```
PRIMARY KEY (`id`),
```
```
KEY `product_option_values_tenant_id_foreign` (`tenant_id`),
```
KEY `product_option_values_product_option_id_foreign`
```
(`product_option_id`),
```
CONSTRAINT `product_option_values_product_option_id_foreign`
```
FOREIGN KEY (`product_option_id`) REFERENCES `product_options`
```
```
(`id`) ON DELETE CASCADE,
```
CONSTRAINT `product_option_values_tenant_id_foreign` FOREIGN
```
KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
```
```
)
```
Kode A.10: DDL MySQL Tabel floors
```
CREATE TABLE `floors` (
```
`id` bigint unsigned NOT NULL AUTO_INCREMENT,
`created_at` timestamp NULL DEFAULT NULL,
`updated_at` timestamp NULL DEFAULT NULL,
```
`tenant_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
```
```
`name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
```
```
`area_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT
```
NULL,
186
`order` int unsigned NOT NULL DEFAULT '0',
```
PRIMARY KEY (`id`),
```
```
KEY `floors_tenant_id_order_index` (`tenant_id`,`order`),
```
CONSTRAINT `floors_tenant_id_foreign` FOREIGN KEY
```
(`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
```
```
)
```
Kode A.11: DDL MySQL Tabel dining_tables
```
CREATE TABLE `dining_tables` (
```
`id` bigint unsigned NOT NULL AUTO_INCREMENT,
`created_at` timestamp NULL DEFAULT NULL,
`updated_at` timestamp NULL DEFAULT NULL,
```
`tenant_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
```
`floor_id` bigint unsigned DEFAULT NULL,
```
`label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
```
```
`status` enum('available','occupied','oncleaning','archived')
```
COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available',
```
`shape` enum('circle','rectangle') COLLATE utf8mb4_unicode_ci
```
NOT NULL DEFAULT 'rectangle',
`x` smallint unsigned NOT NULL DEFAULT '0',
`y` smallint unsigned NOT NULL DEFAULT '0',
`h` tinyint unsigned NOT NULL DEFAULT '2',
`w` tinyint unsigned NOT NULL DEFAULT '2',
`capacity` int NOT NULL DEFAULT '2',
```
`color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
```
```
`qr_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT
```
NULL,
```
PRIMARY KEY (`id`),
```
```
UNIQUE KEY `qr_code` (`qr_code`),
```
```
KEY `dining_tables_tenant_id_index` (`tenant_id`),
```
```
KEY `dining_tables_floor_id_foreign` (`floor_id`),
```
KEY `dining_tables_tenant_id_floor_id_index`
```
(`tenant_id`,`floor_id`),
```
CONSTRAINT `dining_tables_floor_id_foreign` FOREIGN KEY
```
(`floor_id`) REFERENCES `floors` (`id`) ON DELETE CASCADE,
```
CONSTRAINT `dining_tables_tenant_id_foreign` FOREIGN KEY
```
(`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
```
```
)
```
Kode A.12: DDL MySQL Tabel tenants
```
CREATE TABLE `tenants` (
```
```
`id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
```
`created_at` timestamp NULL DEFAULT NULL,
`updated_at` timestamp NULL DEFAULT NULL,
`data` json DEFAULT NULL,
```
PRIMARY KEY (`id`)
```
```
)
```
187
Kode A.13: DDL MySQL Tabel taxes
```
CREATE TABLE `taxes` (
```
`id` bigint unsigned NOT NULL AUTO_INCREMENT,
```
`tenant_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
```
```
`name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
```
```
`type` enum('percentage','fixed') COLLATE utf8mb4_unicode_ci
```
NOT NULL DEFAULT 'percentage',
```
`rate` decimal(10,2) NOT NULL DEFAULT '0.00',
```
```
`is_active` tinyint(1) NOT NULL DEFAULT '1',
```
`created_at` timestamp NULL DEFAULT NULL,
`updated_at` timestamp NULL DEFAULT NULL,
```
PRIMARY KEY (`id`),
```
KEY `taxes_tenant_id_is_active_index`
```
(`tenant_id`,`is_active`),
```
```
CONSTRAINT `taxes_tenant_id_foreign` FOREIGN KEY (`tenant_id`)
```
```
REFERENCES `tenants` (`id`) ON DELETE CASCADE
```
```
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
```
```
COLLATE=utf8mb4_unicode_ci
```
Kode A.14: DDL MySQL Tabel discounts
```
CREATE TABLE `discounts` (
```
`id` bigint unsigned NOT NULL AUTO_INCREMENT,
```
`tenant_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
```
```
`name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
```
```
`discount_type` enum('flat','percent') COLLATE
```
utf8mb4_unicode_ci NOT NULL,
```
`value` decimal(10,2) NOT NULL,
```
```
`applicable_for` enum('all','specific') COLLATE
```
utf8mb4_unicode_ci NOT NULL DEFAULT 'all',
`products` json DEFAULT NULL,
`valid_from` date NOT NULL,
`valid_till` date NOT NULL,
`days` json NOT NULL,
```
`quantity_type` enum('unlimited','decrement') COLLATE
```
utf8mb4_unicode_ci NOT NULL DEFAULT 'unlimited',
`quantity` int DEFAULT NULL,
```
`status` enum('active','inactive') COLLATE utf8mb4_unicode_ci
```
NOT NULL DEFAULT 'active',
`created_at` timestamp NULL DEFAULT NULL,
`updated_at` timestamp NULL DEFAULT NULL,
```
PRIMARY KEY (`id`),
```
```
KEY `discounts_tenant_id_status_index` (`tenant_id`,`status`),
```
KEY `discounts_tenant_id_valid_from_index`
```
(`tenant_id`,`valid_from`),
```
CONSTRAINT `discounts_tenant_id_foreign` FOREIGN KEY
```
(`tenant_id`) REFERENCES `tenants` (`id`)
```
```
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
```
```
COLLATE=utf8mb4_unicode_ci
```
188
LAMPIRAN B
VISUALISASI STRUKTUR DAN RELASI BASIS
DATA MENGGUNAKAN TABLEPLUS
Lampiran ini menampilkan tangkapan layar hasil implementasi skema basis
data pada DBMS MySQL yang divisualisasikan menggunakan TablePlus untuk
memverifikasi struktur tabel, constraint, indeks, dan relasi sesuai rancangan ERD.
```
Gambar B.1 Daftar tabel pada basis data (TablePlus – schema overview).
```
189
Gambar B.2 Struktur tabel orders.
Gambar B.3 Struktur tabel order_items.
190
Gambar B.4 Struktur tabel customer_details.
Gambar B.5 Struktur tabel products.
Gambar B.6 Struktur tabel categories.
##### Gambar B.7 Relasi foreign key orders–order_items.