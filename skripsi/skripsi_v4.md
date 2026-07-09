






## IMPLEMENTASI SISTEM MANAJEMEN INVENTORI
## PADA POINT OF SALE W9 CAFE STIE TOTALWIN
## MENGGUNAKAN LARAVEL DAN FILAMENT


## TUGAS AKHIR

Diajukan sebagai salah satu syarat untuk memperoleh  gelar
## Sarjana Teknik

## MUHAMMAD NIO HASTUNGKORO
## 21120122140155

## DEPARTEMEN TEKNIK KOMPUTER
## FAKULTAS TEKNIK
## UNIVERSITAS DIPONEGORO
## SEMARANG
## 2026


i
## HALAMAN PENGESAHAN
## Tugas Akhir

## IMPLEMENTASI SISTEM MANAJEMEN INVENTORI PADA
## POINT OF SALE W9 CAFE MENGGUNAKAN
## LARAVEL DAN FILAMENT

Tugas Akhir ini diajukan oleh:
## Muhammad Nio Hastungkoro
## 21120122140155

## Kepada
## Departemen Teknik  Komputer
## Universitas Diponegoro

Telah disetujui Oleh:


## Pembimbing  I
Yudi Eko Windarto, S.T., M.Kom.

Pembimbing  II
Rinta Kridalukmana, S.Kom., M.T., Ph.D.


ii
## HALAMAN PERNYATAAN ORISINALITAS
Tugas  Akhir  ini  adalah  hasil  karya  saya  sendiri,  dan  semua  sumber  baik  yang
dikutip maupun yang dirujuk telah saya nyatakan dengan benar.

## Nama : Muhammad Nio Hastungkoro
## NIM : 21120122140155
## Tanda Tangan :
## Tanggal : 23 Mei 2026


iii
## HALAMAN PERNYATAAN PERSETUJUAN PUBLIKASI
## TUGAS AKHIR UNTUK KEPENTINGAN AKADEMIS
Sebagai sivitas akademika Universitas Diponegoro,  saya yang bertanda tangan di
bawah ini:

Nama : MUHAMMAD NIO HASTUNGKORO
## NIM : 21120122140155
Departemen : TEKNIK KOMPUTER
Fakultas : TEKNIK
Jenis Karya : TUGAS AKHIR

Demi  pengembangan  ilmu  pengetahuan,  menyetujui  untuk  memberikan  kepada
Universitas   Diponegoro   Hak   Bebas   Royalti   Non   Eksklusif   (Non-exclusive
Royalty Free Right) atas karya ilmiah  saya berjudul:

## IMPLEMENTASI  SISTEM  MANAJEMEN  INVENTORI  PADA  POINT  OF
## SALE W9 CAFE MENGGUNAKAN LARAVEL DAN FILAMENT

beserta  perangkat  yang  ada  (jika  diperlukan).  Dengan  Hak  Bebas  Royalti/Non
Eksklusif ini Universitas Diponegoro berhak menyimpan, mengalih
media/formatkan,  mengelola  dalam  bentuk  pangkalan  data  (database),  merawat
dan  memublikasikan  Tugas  Akhir  saya  selama  tetap  mencantumkan  nama  saya
sebagai penulis/pencipta dan sebagai pemilik Hak Cipta. Demikian pernyataan ini
saya buat dengan sebenarnya.

Dibuat di: Semarang
Pada tanggal: 23 Mei 2026
Yang menyatakan,

(Muhammad Nio Hastungkoro)


iv


v
## KATA PENGANTAR

Puji syukur penulis panjatkan ke hadirat Allah SWT atas limpahan rahmat
dan hidayah-Nya sehingga penulis dapat menyelesaikan tugas akhir yang berjudul
"Implementasi  Sistem  Manajemen  Inventori  pada Point  of  Sale W9 Cafe STIE
Totalwin menggunakan  Laravel  dan  Filament"  dengan  baik.  Tugas  akhir  ini
disusun  sebagai  salah  satu  syarat  untuk  memperoleh  gelar  Sarjana  Teknik  pada
## Departemen Teknik  Komputer, Fakultas Teknik, Universitas Diponegoro.
Penulisan   tugas   akhir   ini   tidak   lepas   dari   bantuan,   bimbingan,   dan
dukungan  dari  berbagai  pihak.  Oleh  karena  itu,  pada  kesempatan  ini  penulis
menyampaikan  rasa terima kasih yang sebesar-besarnya  kepada:
- Bapak  Yudi  Eko  Windarto,  S.T.,  M.Kom.,  selaku  Dosen  Pembimbing  I
yang telah memberikan arahan, bimbingan, dan masukan berharga selama
proses penelitian dan penulisan tugas akhir ini.
- Bapak    Rinta    Kridalukmana,    S.Kom.,    M.T.,    Ph.D.,    selaku    Dosen
Pembimbing   II   yang   telah    meluangkan   waktu   untuk   memberikan
bimbingan,  koreksi,  dan  saran  perbaikan  yang  sangat  membantu  dalam
penyelesaian  tugas akhir ini.
- Bapak  dan  Ibu  Dosen  Departemen  Teknik  Komputer,  Fakultas  Teknik,
Universitas  Diponegoro  yang  telah  memberikan  ilmu  dan  pengetahuan
selama masa perkuliahan.
- Orang   tua   dan   keluarga   penulis   yang   senantiasa   memberikan   doa,
dukungan moral, dan motivasi yang tiada henti.
- Rekan-rekan  mahasiswa  Departemen  Teknik  Komputer,  khususnya  Ivan
dan  Ruben,  yang  telah  berkolaborasi  dalam  pengembangan  sistem  POS
W9 Cafe secara keseluruhan.
- Seluruh  pihak  yang  telah  membantu  dalam  penyelesaian  tugas  akhir  ini
yang tidak dapat disebutkan satu per satu.
Penulis  menyadari bahwa tugas akhir ini  masih memiliki kekurangan dan
keterbatasan. Oleh karena itu, penulis sangat mengharapkan kritik dan saran yang
membangun  untuk  perbaikan  di  masa  mendatang.  Semoga  tugas  akhir  ini  dapat

vi
bermanfaat  bagi  pengembangan  ilmu  pengetahuan,  khususnya  di  bidang  sistem
informasi dan manajemen inventori.
## Semarang, Mei 2026
## Muhammad Nio Hastungkoro


vii
## DAFTAR ISI

(Klik kanan → Update Field untuk menampilkan Daftar Isi)


viii
## DAFTAR GAMBAR

(Klik kanan → Update Field untuk menampilkan Daftar Gambar)


ix
## DAFTAR TABEL

(Klik kanan → Update Field untuk menampilkan Daftar Tabel)


x

## ABSTRAK
Mana jemen inventori merupa kan a spek kritis da la m opera siona l kafe ya ng mempenga ruhi
ketersediaa n baha n baku, kelanca ra n produksi, da n kepua sa n pela ngga n. Pada  pra ktiknya , ba nyak
kafe ma sih mela kuka n penca tata n stok seca ra  ma nua l menggunaka n nota  kerta s a tau spreadsheet,
yang renta n terha da p kesa laha n pencata ta n, keterlamba tan informa si stok, se rta  sulitnya  mela cak
riwa ya t  pergera kan  stok.  Penelitia n  ini  bertujua n  mera nca ng  da n  mengimplementa sikan  sistem
ma na jemen  inventori  pa da  Point  of  Sale W9 Cafe mengguna kan  La ravel  da n  Fila ment  ya ng
ma mpu mengelola  stok ba ha n ba ku berba sis resep.
Sistem  dikembangkan  mengguna ka n  kera ngka   kerja   La ra vel  denga n  pa nel  administra si
da ri  pusrta ka  Fila ment  yang  menyedia ka n  a nta rmuka   pengelola a n  data   da n  ma najemen batch
seca ra  intuitif. Sistem mengimplementa sika n dua  mode deduksi batch ya itu FEFO (First-Expiry-
First-Out)  da n  FIFO  (First-In-First-Out),  denga n  meka nisme  penguncia n  da ta   untuk  mencegah
konflik  pa da   transa ksi  bersamaa n.  Integra si  dengan  modul  tra nsa ksi  ka sir  dila kukan  seca ra
otoma tis di ma na  deduksi stok terja di seba ga i ba gia n da ri pemrosesa n pesa na n.
Pengujia n dila kukan denga n metode black box, white box, dan integration testing.
Ha sil pengujia n black box terhada p seluruh modul menunjukka n skena rio berha sil. Pengujia n
white box memva lida si kebena ra n a lgoritma FIFO da n FEFO. Pengujia n integrasi memva lida si
konsistensi a lira n da ta a nta r modul. Seluruh meka nisme deduksi stok da n penca tata n
pergera ka n berja la n sesua i pera nca nga n.
Kata kunci: Sistem ma na jemen inventori, Point of Sale, La ra vel, Fila ment, FEFO, FIFO.


xi
## ABSTRACT
Inventory  management  is  a  critical aspect  of  cafe  operations  that  affects  raw  material
availability, production continuity, and customer satisfaction. In practice, many cafes still record
stock manually using paper notes or spreadsheets, which are prone to recording errors, delayed
stock information, and difficulty in tracking stock movement history. This study aims to design and
implement  an  inventory  management  system  for  the  W9   Cafe  Point  of  Sale  using  Laravel  and
Filament that can manage recipe-based raw material stock.
The   system   was   developed   using   the   Laravel   13   framework   with   the   Filament
administration   panel,   which   provides   intuitive   data   management   interfaces   and   batch
management. The system implements two batch deduction modes: FEFO (First-Expiry-First-Out)
and  FIFO  (First-In-First-Out),  with  data locking  mechanisms  to  prevent  conflicts  in  concurrent
transactions.  Integration  with  the  cashier  transaction  module  is  achieved  automatically  where
stock deduction occurs as part of order processing.
Testing was conducted using black box, white box, and integration testing methods.
Black box testing across all modules showed scenarios passed. White box testing validated
FIFO and FEFO algorithms. Integration testing validated data consistency across modules.
All stock deduction mechanisms and movement recording functioned as designed.
Keywords:  Inventory  management  system,  Point  of  Sale,  Laravel,  Filament,  FEFO,
## FIFO.


## BAB I
## PENDAHULUAN

## 1.1 Latar Belakang
Manajemen inventori atau pengelolaan stok bahan baku merupakan proses
inti  dalam  operasional  kafe  yang  menentukan  kelancaran  produksi  dan  kepuasan
pelanggan.  Namun  pada  praktiknya,  banyak  kafe  masih  mencatat  stok  secara
manual  menggunakan  buku  atau spreadsheet, yang  rentan  terhadap  kesalahan
pencatatan,   keterlambatan   informasi   stok,   serta   sulitnya   melacak    riwayat
pergerakan stok secara akurat [1].
Salah  satu  contohnya  adalah  W9 Cafe di  Semarang  yang  dikelola  oleh
STIE  Totalwin.  Dalam  operasional  sehari-hari,  W9 Cafe masih  menggunakan
buku stok dan spreadsheet sehingga sering terjadi ketidaksesuaian antara catatan
dengan  kondisi  aktual,  serta  sulitnya  melacak  riwayat  pemakaian  bahan  baku
secara akurat. Kondisi ini mendorong perlunya pengembangan sistem manajemen
inventori  yang  terintegrasi  dengan  sistem  POS  yang  akan  digunakan  oleh  W9
Cafe untuk mengatasi masalah operasional  ini.
Sistem POS yang  dikembangkan mencakup sistem  manajemen  inventori
yang  mencakup fitur  pengelolaan  data  menu  dan  bahan  baku,  pengaturan  stok
bahan  baku  dengan  mode  deduksi  FEFO  (First-Expiry-First-Out)  dan  FIFO
(First-In-F irst-Out),    penyesuaian    stok    manual,    serta    pencatatan    riwayat
pemakaian  bahan  baku.  Sistem  dibangun  menggunakan  kerangka  kerja  Laravel
dengan   panel   administrasi   Filament   untuk   memudahkan   pengelolaan   data
inventori.

## 1.2 Rumusan Masalah
Berdasarkan latar belakang, dirumuskan permasalahan  sebagai berikut:
- Bagaimana merancang dan membangun  sistem  manajemen  inventori pada
POS W9 Cafe menggunakan Laravel dan Filament?


- Bagaimana  sistem  dapat menggunakan stok  berdasarkan prioritas masa
kedaluwarsa (FEFO) dan urutan penerimaan (FIFO) untuk meminimalkan
pemborosan bahan baku?
- Bagaimana  sistem dapat mencatat pemakaian bahan baku secara otomatis
dan akurat setiap terjadi transaksi penjualan?
- Bagaimana hasil pengujian sistem dalam meningkatkan akurasi pencatatan
stok dan efisiensi operasional?
- Bagaimana hasil pengujian integrasi antara modul inventori dan modul
transaksi dalam memastikan konsistensi data stok?

## 1.3 Batasan Masalah
- Sistem   berfokus   pada   manajemen   inventori   bahan   baku   dan   fitur
pendukung  (autentikasi, dashboard, kategori,   menu).  Penelitian  tidak
mencakup laporan keuangan dan modul di luar konteks inventori.
- Mode deduksi batch stok terbatas pada FEFO dan FIFO.
- Sistem memerlukan  koneksi internet untuk diakses.
- Pengujian terbatas pada black box, white box, dan integration test yang
mencakup verifikasi algoritma deduksi, konsistensi data antar modul, dan
validasi fungsionalitas sistem.

## 1.4 Tujuan Penelitian
- Mengimplementasikan  sistem  manajemen  inventori  pada  POS  W9 Cafe
menggunakan Laravel dan Filament.
- Menerapkan  prioritas  penggunaan  stok  berdasarkan  masa  kedaluwarsa
(FEFO) dan urutan penerimaan (FIFO) untuk meminimalkan pemborosan
bahan baku.
- Mengintegrasikan   pencatatan   pemakaian   bahan   baku   secara   otomatis
dengan  setiap  transaksi  penjualan  serta  menyediakan  riwayat  perubahan
stok yang dapat dilacak.
- Melakukan  pengujian  sistem  untuk  memvalidasi  akurasi  pencatatan  stok
dan efisiensi operasional.
- Melakukan pengujian integrasi untuk memvalidasi konsistensi aliran data
antar modul inventori dan modul transaksi.



## 1.5 Manfaat Penelitian
- Manfaat bagi Penulis:  Mendapatkan pengalaman dalam merancang sistem
manajemen  inventori yang terstruktur menggunakan Laravel dan Filament.
- Manfaat  bagi  Kafe:  Mendapatkan  sistem  manajemen  inventori  dengan
deduksi stok otomatis dan riwayat pergerakan stok yang dapat dilacak.
- Manfaat bagi Peneliti Selanjutnya:  Menjadi referensi implementasi sistem
manajemen  inventori dengan deduksi stok otomatis.

## 1.6 Metodologi Penelitian
Penelitian ini menggunakan tahapan sebagai berikut:
- Requirements:  Identifikasi  kebutuhan  melalui  observasi  di  W9 Cafe dan
studi literatur.
- Design: Merancang basis  data,  arsitektur  aplikasi,  algoritma  deduksi, dan
antarmuka panel Filament.
- Development: Implementasi  secara  bertahap  dalam  dua  iterasi,  yaitu  inti
inventori dan panel administrasi.
- Testing:  Pengujian black box, white box, dan integration testing untuk
memvalidasi seluruh fungsionalitas dan integrasi sistem.
- Review:  Evaluasi  hasil  pengujian  dan  penyusunan  dokumentasi sistem
sebagai bagian dari laporan tugas akhir.
- Penyusunan  Laporan: Menyusun laporan  Tugas  Akhir  sebagai  bentuk
dokumentasi  dari  seluruh  kegiatan  yang  meliputi  konsep,  landasan  teori,
proses implementasi,  dan hasil yang diperoleh.

## 1.7 Sistematika  Penulisan
Tugas akhir ini terdiri atas lima bab dengan susunan sebagai berikut.
## BAB I PENDAHULUAN
Berisi    latar    belakang,    rumusan    masalah,    batasan    masalah,    tujuan
penelitian, manfaat penelitian, metodologi penelitian, dan sistematika penulisan.
## BAB II KAJIAN PUSTAKA


Membahas  penelitian  terdahulu,  metode  penelitian  yang  digunakan,  serta
landasan teori yang meliputi konsep sistem manajemen inventori, algoritma FEFO
dan FIFO, Laravel, Filament, dan metode pengujian.
## BAB III PERANCANGAN SISTEM
Berisi   gambaran   umum   sistem,   lingkungan   pengembangan,   analisis
kebutuhan  fungsional  dan  non-fungsional,  perancangan  proses  dan  alur  sistem,
perancangan   basis   data,   perancangan   arsitektur   aplikasi,   serta   perancangan
antarmuka.
## BAB IV IMPLEMENTASI DAN PENGUJIAN
Menyajikan  implementasi  panel  administrasi,  implementasi  algoritma  inti,
serta hasil pengujian black box, white box, dan integration.
## BAB V PENUTUP
Bab ini berisi kesimpulan dari perancangan, implementasi, dan pengujian
yang telah dilakukan, serta saran pengembangan dan penelitian lebih  lanjut pada
masa mendatang.


## BAB II
## KAJIAN PUSTAKA

## 2.1 Penelitian Terdahulu
Kajian   penelitian   ini   membahas   berbagai   penelitian   terdahulu   yang
memiliki  keterkaitan  dengan  topik  pengembangan  sistem  manajemen  inventori
dan  POS  berbasis  web.  Penelitian-penelitian  tersebut  menjadi  referensi  penting
dalam  memahami  konsep  sistem  informasi  manajemen  stok,  algoritma  deduksi
batch, serta penerapan teknologi pendukung seperti Laravel dan Filament. Kajian
ini   juga   memperkuat   landasan   teoritis   dan   memberikan   gambaran   tentang
efektivitas sistem digital dalam meningkatkan akurasi dan efisiensi operasional di
bidang inventori serta transaksi penjualan.
Penelitian  oleh  Supron  dan A. Susila  berjudul  "Aplikasi Point  of  Sales
(POS)  Berbasis Website Dengan  Menggunakan  Laravel  (Studi  Kasus:  Bakmi
Djowo)"  mengembangkan  sistem  POS  berbasis  web  yang  mencakup  transaksi
penjualan  dan  pengelolaan  stok  menu.  Persamaan  dengan  penelitian  ini  adalah
penggunaan  Laravel  sebagai  kerangka  kerja  utama  dan  fokus  pada  pengelolaan
stok, sedangkan perbedaannya terletak pada konteks: penelitian Supron dan Susila
pada   restoran   Bakmi   Djowo  tanpa  dukungan   manajemen batch, sementara
penelitian ini pada kafe dengan implementasi mode deduksi FEFO dan FIFO [2].
Selanjutnya, penelitian oleh Y. Darmayunata, Y. Yuhelmi, dan M. Devega
dalam  "Pembangunan  Sistem  Inventori  Apotek  Menggunakan  Metode  FIFO dan
FEFO"   mengimplementasikan   kedua   algoritma   deduksi batch pada   sistem
inventori  apotek.  Persamaan  dengan  penelitian  ini  adalah  fokus  pada  algoritma
FIFO  dan   FEFO,   sedangkan   perbedaannya   adalah   penelitian   Devega,   dkk.
diterapkan pada lingkungan apotek dengan pengelolaan obat, sementara penelitian
ini  menerapkan  kedua  algoritma  pada  sistem  inventori  bahan  baku  kafe  yang
terintegrasi dengan POS [3].
Penelitian oleh R. A. Farisi, A. R. Zayn, B. A. Nugroho, dan A. Heriadi,
dalam  "Implementasi  Sistem  Informasi  Akademik  Pengelolaan   Tugas  Akhir
Berbasis  Laravel  dan  Filament"  membahas  implementasi  Filament  sebagai  panel


administrasi   Laravel.   Persamaan   dengan   penelitian   ini   adalah   penggunaan
Filament    untuk    antarmuka    administrasi,    sedangkan    perbedaannya    adalah
penelitian  Al  Farisi,  dkk.  berfokus  pada  sistem  informasi  akademik,  sementara
penelitian ini menerapkannya secara spesifik untuk manajemen inventori kafe [4].
Terakhir, Garbarz dan Plechawska-Wójcik melakukan analisis komparatif
kerangka  kerja  PHP  Laravel  dan  Symfony.  Persamaan  dengan  penelitian  ini
adalah pemilihan Laravel sebagai kerangka kerja utama, sedangkan perbedaannya
adalah penelitian tersebut bersifat komparatif umum tanpa studi kasus spesifik [5].
1Ta bel 2.1 Ka jia n penelitia n terda hulu.
## No Peneliti,  Tahun Tujuan Hasil
1 Supron  dan  A.  Susila
## (2023)
Sistem    POS    berbasis
web
Transaksi     dan     stok,
tanpa batch
management
2 M. Devega, dkk.
## (2024)
FIFO dan FEFO
inventory
FIFO dan  FEFO  untuk
manajemen  stok
3 R.     Al     Farisi,     dkk.
## (2025)
Laravel Filament Filament   untuk   panel
administrasi
4 P.    Garbarz    dan    M.
Plechawska-Wójcik
## (2022)
Analisis    Laravel    dan
## Symfony
Laravel sebagai
framework utama
Sistem  yang  dikembangkan  memiliki  keunggulan  dibanding  penelitian
terdahulu  berupa  deduksi  stok  otomatis  berbasis   resep  bahan  baku  dengan
modulasi  perubahan,  mode  deduksi  FEFO  dan  FIFO  yang  dapat  dikonfigurasi,
pencatatan   riwayat   pergerakan   stok   yang   tidak   dapat   diubah,   mekanisme
pengamanan  transaksi  untuk  mencegah  konflik  data,  serta  panel  admin  Filament
untuk pengelolaan data inventori.

## 2.2 Metode Penelitian
Metode  penelitian  menggunakan Agile yang  menekankan  kolaborasi  erat
antara  pengembang  dan  pengguna,  serta  pengembangan  sistem  yang  dilakukan


secara  iteratif,  bertahap,  dan  fleksibel  agar  dapat  menyesuaikan  diri  dengan
perubahan kebutuhan selama proses berlangsung.

1 Ga mba r 2.1 Metode Agile
Berikut  adalah  tahapan  dalam  pengembangan  perangkat  lunak  dengan
menggunakan metode Agile:
- Requirements (Pengumpulan   Kebutuhan): Tim   bekerja   sama   dengan
pemilik   kafe untuk   mengidentifikasi   dan   memprioritaskan   kebutuhan
sistem dalam catatan yang jelas dan terukur.
- Design (Perancangan): Tim  membuat  rancangan  solusi  sederhana  namun
efektif untuk fitur-fitur yang dipilih di iterasi ini, seperti sketsa antarmuka,
alur sistem, atau arsitektur teknis awal.
- Development (Pengembangan): Developer mulai menulis     kode,
membangun fitur secara nyata, dan melakukan pengujian (unit testing).
- Testing (Pengujian): Tim  memverifikasi  bahwa  fitur  yang  sudah  dibuat
berfungsi dengan baik melalui  berbagai jenis  tes.
- Deployment (Penyebaran): Hasil   kerja   yang   sudah   lolos   pengujian
dikeluarkan ke lingkungan staging atau produksi.
- Review (Tinjauan): Mencakup Sprint  Review untuk  mendemonstrasikan
hasil ke pemilik kafe.
Setelah tahap review selesai,  siklus kembali ke tahap Requirements untuk
memulai iterasi baru dengan memasukkan umpan balik, perubahan prioritas, atau
kebutuhan tambahan. Pendekatan siklus berulang ini memungkinkan proyek terus
disempurnakan secara adaptif [6].



## 2.3 Landasan Teori
## 2.3.1 Sistem Manajemen Inventori
Sistem  manajemen  inventori  adalah  serangkaian  proses  yang  digunakan
untuk  mengelola,  memantau,  dan  mengendalikan  persediaan  barang  dalam  suatu
organisasi.  Tujuan  utamanya  adalah  memastikan  ketersediaan  stok  yang  cukup
untuk  memenuhi  permintaan  tanpa  menimbulkan  kelebihan  stok  yang  dapat
meningkatkan  biaya  penyimpanan  [7].  Dalam penelitian  ini,  inventori  mencakup
bahan  baku  dan  barang  jadi  yang  memerlukan  pengelolaan  khusus  terkait  masa
kedaluwarsa.

2.3.2 PostgreSQL
PostgreSQL  adalah  sistem  manajemen  basis  data  relasional  objek  open
source dengan  reputasi  stabil  dan  kaya  fitur.  PostgreSQL  mendukung row-level
locking melalui SELECT  ...  FOR  UPDATE, transaction  isolation  levels,  dan
foreign  key  constraints yang  diperlukan  untuk  menjaga  integritas  data  pada
aplikasi  berbasis web [8].

2.3.3 Algoritma Deduksi Batch (FEFO dan FIFO)
Algoritma deduksi batch menentukan urutan penggunaan stok berdasarkan
karakteristik masing-masing batch. FEFO (First-Expiry-First-Out)
memprioritaskan batch dengan  tanggal  kedaluwarsa  terdekat,  sangat  penting
untuk  bahan  baku  dengan  masa  simpan  terbatas  seperti  bahan  segar  dan  produk
dairy [9]. Dalam penelitian  ini, implementasi FEFO menggunakan batch dengan
tanggal  kedaluwarsa  terdekat  terlebih  dahulu,  sedangkan FIFO  menggunakan
batch yang diterima lebih awal terlebih dahulu.

2.3.4 Point of Sale (POS)
Point of Sale (POS)  merupakan sistem yang digunakan untuk  memproses
transaksi  penjualan  dan  mengelola  data  penjualan,  produk,  serta  stok  secara
terintegrasi [10].



## 2.3.5 PHP
PHP  (Hypertext  Preprocessor)  adalah  bahasa  pemrograman  skrip  yang
banyak   digunakan   untuk   pengembangan   aplikasi   web   dinamis.   PHP   dapat
disisipkan  langsung  ke  dalam  H TML  dan  memiliki  dukungan  luas  terhadap
berbagai  basis  data.  Laravel  sebagai  kerangka  kerja  PHP  yang  digunakan dalam
tugas  akhir  ini  memanfaatkan  kemampuan  PHP  untuk  membangun  aplikasi  web
yang terstruktur [5].

## 2.3.6 Laravel
Laravel  adalah  kerangka  kerja  aplikasi  web  berbasis  PHP  yang  bersifat
terbuka  dan  menyediakan  berbagai  fitur  seperti  sistem routing,  manajemen  basis
data   melalui migration, dan   sistem   autentikasi   bawaan   yang   memudahkan
pengembangan aplikasi  web modern [11].

## 2.3.7 Filament
Filament  adalah  pustaka  antarmuka  pengguna  yang  dibangun  di  atas
Laravel dan menyediakan komponen antarmuka siap pakai seperti tabel data dan
formulir  yang  terintegrasi  dengan  basis  data,  sehingga  mempercepat  pembuatan
antarmuka administrasi [12].

## 2.3.8 Entity Relationship  Diagram
Entity  Relationship  Diagram (ERD)  merupakan  model  pemodelan  data
yang  disusun  berdasarkan  objek-objek  yang  ada di dunia  nyata.  ERD digunakan
untuk menggambarkan hubungan antar data dalam sebuah basis data secara  logis
sehingga  mudah  dipahami  oleh  pengembang  dan  pengguna.  ERD  terdiri  dari
entitas, atribut (kolom-kolom dalam tabel), dan relasi antar entitas (seperti one-to-
many, many-to-many) [13].  Pada pengembangan sistem  inventori ini,  ERD
digunakan  untuk  memodelkan  hubungan  antara  bahan  baku  dengan batch stok,
menu dengan resep, serta keterkaitan antara stok dengan pergerakannya.



## 2.3.9 Activity Diagram
Activity  diagram merupakan  salah  satu  diagram  dalam Unified  Modeling
Language (UML)  yang  digunakan  untuk  menggambarkan  alur  aktivitas  atau
proses  dalam  suatu  sistem.  Diagram  ini  menampilkan  urutan  kegiatan  dari  awal,
keputusan-keputusan  yang  terjadi, hingga  akhir  melalui  aliran  kontrol  antar
aktivitas [14].

## 2.3.10  Flowchart
Flowchart atau     diagram     alir     merupakan     jenis     diagram     yang
merepresentasikan   algoritma,   alur   kerja,   atau   proses   dengan   menampilkan
langkah-langkah  dalam  bentuk  simbol-simbol  grafis  yang  dihubungkan  dengan
panah. Flowchart digunakan untuk menganalisis, mendesain, dan
mendokumentasikan sebuah proses atau program secara sistematis [15].

## 2.3.11 Pengujian Black Box
Pengujian black  box merupakan  teknik  pengujian  perangkat  lunak  yang
berfokus  pada  spesifikasi  fungsional  tanpa  memerlukan  pengetahuan  tentang
struktur  internal  kode  program.  Pengujian  ini  dilakukan  dengan  mendefinisikan
kondisi  masukan  dan  memverifikasi  keluaran  yang  dihasilkan,  sehingga  lebih
menitikberatkan pada kesesuaian fungsi sistem dengan kebutuhan pengguna [16].

## 2.3.12 Pengujian White Box
Pengujian white  box merupakan  teknik  pengujian  perangkat  lunak  yang
berfokus pada struktur internal dan logika kode program. Pengujian ini dirancang
dari  perspektif  pengembang  dengan  menguji  seluruh  bagian  kode  yang  dapat
diuji,   bertujuan   untuk   menemukan   kesalahan   logis   pada source   code dan
memastikan  bahwa  setiap fitur  berfungsi sesuai  dengan  yang  diharapkan [17].

## 2.3.13 Pengujian Integration
Pengujian integration merupakan level pengujian perangkat lunak yang berfokus pada interaksi antar modul atau komponen dalam sistem. Berbeda dengan pengujian unit yang menguji fungsi secara terisolasi, pengujian integration memvalidasi bahwa modul-modul yang telah diuji secara individual dapat bekerja sama dengan benar ketika diintegrasikan. Tujuannya adalah mendeteksi kesalahan pada antarmuka antar modul, aliran data, dan konsistensi state ketika terjadi pertukaran informasi antar komponen [18].

Pengujian integration dapat dilakukan dengan dua pendekatan. Pendekatan white box integration testing memverifikasi kebenaran aliran data antar modul melalui pengujian berbasis kode dengan memeriksa keadaan database sebelum dan sesudah transaksi. Pendekatan black box integration testing memverifikasi interaksi antar modul dari sisi pengguna melalui antarmuka sistem. Kombinasi kedua pendekatan ini memberikan keyakinan bahwa integrasi antar modul berjalan dengan benar baik dari sisi teknis maupun fungsional.

## BAB III
## PERANCANGAN SISTEM

## 3.1 Gambaran Proses Bisnis
## 3.1.1 Proses Bisnis Saat Ini
Pada  sistem  yang  berjalan  saat  ini,  pencatatan  inventori  di  kafe  masih
dilakukan secara manual. Admin  mencatat penerimaan bahan baku di buku stok,
kasir  menulis  pesanan  pada  nota  kertas,  dan stock opname dilakukan  secara
periodik. Hal ini menyebabkan kesalahan pencatatan dan keterlambatan informasi
stok.

3.1.2 Proses Bisnis yang Dikembangkan
Sistem  yang  dikembangkan  mengotomatiskan  pencatatan  dan  deduksi
stok. Admin mengelola data bahan baku dan resep melalui panel Filament. Ketika
kasir  memproses  pesanan,  sistem  secara  otomatis  mendeduksi  stok  berdasarkan
resep   menu,   lalu   hasilnya   akan   dicatat   dan   ditampilkan   sebagai   riwayat
penggunaan bahan baku.



3.2 Perancangan Proses dan Alur Sistem
## 3.2.1 Use Case Diagram

2 Ga mba r 3.1 Use case diagram sistem ma na jemen inventori.
Use  case  diagram pada  Gambar  3.1  menggambarkan  interaksi yang  bisa
dilakukan   admin   terhadap   keseluruhan   sistem.   Admin   bertanggung   jawab
mengelola  data  inventori  (login,  kelola  bahan  baku, batch stok,  resep  menu, dan
penyesuaian  stok), sedangkan sistem  secara  otomatis  menjalankan  deduksi  stok
ketika pesanan diproses.


## 3.2.2 Flowchart Proses Deduksi Stok

Ga mba r 3.2 Flowchart proses deduksi stok
Proses deduksi stok dimulai ketika pesanan masuk melalui modul transaksi
kasir.   Sistem   mendekomposisi   setiap   menu   berdasarkan   resep   pada   tabel
menu_ingredients,  kemudian mulai  mengurangi stok  dari IngredientBatch
sesuai mode FEFO atau FIFO dalam satu proses yang konsisten.



## 3.2.3 Activity Diagram
Activity  diagram merupakan  salah  satu  jenis  diagram  dalam Unified
Modeling Language (UML) yang digunakan untuk menggambarkan alur aktivitas
atau  proses  dalam  suatu  sistem.  Diagram  ini  menampilkan  urutan  kegiatan  dari
awal hingga akhir melalui  aliran kontrol antar aktivitas [16].


Ga mba r 3.3 Activity diagram ta mba h ba ha n ba ku.
Gambar   3.3 memperlihatkan activity   diagram ketika   admin    ingin
menambah  bahan  baku  baru  ke  dalam  sistem.  Pertama-tama,  admin  membuka
halaman daftar bahan baku dan menekan tombol "Buat Bahan Baku". Sistem akan


menampilkan  formulir  yang  berisi input nama  bahan  baku,  pilihan  unit  satuan,
dan  mode batch (FEFO  atau  FIFO).  Setelah  admin  mengisi  data  dan  menekan
tombol  "Buat",  sistem  memvalidasi input dan  menyimpan  data  ke  dalam  tabel
ingredients  di database. Database mengembalikan  respons  sukses,  dan  sistem
menampilkan pesan bahwa data bahan baku berhasil ditambahkan.

5 Ga mba r 3.4 Activity diagram ta mba h batch stok.
6 Gambar   3.4 memperlihatkan activity   diagram ketika   admin    ingin
menambah batch stok  untuk  suatu  bahan  baku.  Admin  memilih  bahan  baku  dari
daftar,  lalu  menekan  tombol  "Batch Stok" dan  kemudian  "Buat Batch". Sistem
menampilkan  formulir batch yang  berisi input jumlah  stok,  tanggal  kedaluwarsa


(untuk mode FEFO) atau tanggal diterima (untuk mode FIFO), dan harga per unit.
Setelah  admin  mengisi  data  dan  menekan  "Buat",  sistem  memvalidasi input dan
menyimpan data ke     tabel ingredient_batches dengan relasi ke
ingredient_id. Database mengembalikan    respons    sukses,    dan    sistem
menampilkan pesan bahwa batch baru berhasil  ditambahkan.

7Ga mba r 3.5 Activity diagram penyesua ia n stok
8 Gambar  3.5 memperlihatkan activity  diagram ketika  admin  melakukan
penyesuaian   stok  manual.  Admin  membuka  halaman  penyesuaian   stok  dan
memilih bahan baku yang akan disesuaikan, kemudian memilih tipe penyesuaian
(penambahan   atau   pengurangan)   serta   mengisi   jumlah   dan   alasan.   Setelah
menekan   "Buat",   sistem   memvalidasi   bahwa   jumlah   lebih   besar   dari   nol,


kemudian  mencatat  penyesuaian  ke  tabel stock_adjustments dan  pergerakan
stok  ke  tabel stock_movements,  serta  memperbarui  kuantitas  pada batch terkait
di database. Database melakukan commit untuk  menjaga  konsistensi  data,  dan
sistem menampilkan pesan bahwa penyesuaian stok berhasil.

9Ga mba r 3.6 Activity diagram bua t menu ba ru
10 Gambar 3.6 memperlihatkan activity diagram ketika admin ingin membuat
menu  baru  beserta  resep  (komposisi  bahan  baku).  Admin  membuka  halaman
daftar  menu  dan  menekan  tombol  "Buat  Menu",  kemudian  mengisi  data  menu
berupa nama, kategori, foto menu, harga, dan diskon. Selanjutnya admin memilih
bahan  baku  dari repeater dan  mengisi  jumlah  pemakaian  per  unit  menu,  lalu
menekan  "Buat". Setiap  menu  wajib  punya  minimal  satu  bahan  baku. Sistem
memvalidasi data menu. Jika data tidak valid, admin akan melihat pesan error dan
memperbaiki input. Jika valid, sistem menyimpan data menu ke tabel  menus dan


relasi resep ke tabel menu_ingredients. Database mengembalikan respons sukses,
dan sistem menampilkan pesan bahwa menu beserta resep berhasil  disimpan.

## 3.3 Kebutuhan Sistem
## 3.3.1 Kebutuhan Fungsional
Kebutuhan fungsional  sistem  manajemen  inventori  diidentifikasi  dari use
case  diagram dan diberi  kode  unik  berawalan  INV,  sebagaimana  disajikan  pada
## Tabel 3.1.
2Ta bel 3.1 Kebutuha n fungsiona l sistem
## No Kode Deskripsi Aktor Prioritas
1 INV-F01 Admin   dapat   mengelola   data   bahan
baku
## Admin Tinggi
2 INV-F02 Admin dapat mengelola batch stok Admin Tinggi
3 INV-F03 Sistem mendukung deduksi batch FEFO
dan FIFO
## Sistem Tinggi
4 INV-F04 Sistem mencatat pergerakan stok secara
permanen
## Sistem Tinggi
5 INV-F05 Admin  dapat  melakukan  penyesuaian
stok manual
## Admin Tinggi
6 INV-F06 Admin dapat mengelola resep menu Admin Tinggi
7 INV-F07 Sistem melakukan deduksi stok
otomatis saat pesanan diproses
## Sistem Tinggi
8 INV-F08 Sistem menjaga konsistensi data stok Sistem Tinggi
9 INV-F09 Admin dapat login dan logout Admin Tinggi
10 INV-F11 Admin dapat mengelola data kategori Admin Sedang
11 INV-F12 Admin dapat mengelola data menu Admin Sedang

3.3.2 Kebutuhan Non-Fungsional
Kebutuhan  non-fungsional  berkaitan  dengan  kualitas  sistem  sebagaimana
disajikan pada Tabel 3.2.
3Ta bel 3.2 Kebutuha n non-fungsiona l


## No Kode Parameter Target Verifikasi
1 INV-NF01 Akurasi
## Deduksi
Deduksi  stok  sesuai   resep
menu
Uji deduksi
berbasis resep
2 INV-NF02 Prioritas
## Batch Stok
Mode   deduksi   FIFO   dan
## FEFO
Uji algoritma
FIFO dan FEFO
3 INV-NF03 Validitas
## Penyesuaian
Penyesuaian stok
penambahan dan
pengurangan
Uji  penyesuaian
stok
4 INV-NF04 Pemulihan
## Stok
Pembatalan penyesuaian
dan mengembalikan    stok
awal
Uji   pembatalan
penyesuaian
5 INV-NF05 Konsistensi
## Data
Transaksi dalam satu proses
yang konsisten
Uji    konsistensi
batch

## 3.4 Perancangan Arsitektur Sistem
## 3.4.1 Arsitektur Umum
Gambar 3.3 memperlihatkan arsitektur umum sistem yang terbagi menjadi
tiga subsistem utama, yaitu Sistem Transaksi, Sistem Inventori, dan Sistem
Data Mining.  Sistem
Transaksi menangani proses pemesanan yang dilakukan oleh kasir dan pelanggan
melalui antarmuka masing-masing,   yang   kemudian   diproses   oleh   Modul
Transaksi.  Sistem  Inventori  mencakup  Panel  Admin  (Filament)  yang  digunakan
oleh  admin  untuk  mengelola  data  inventori,  serta business  logic inventori  yang
menangani seluruh logika pencatatan dan perubahan stok. Seluruh data disimpan
dan dikelola pada PostgreSQL sebagai basis data utama.



11Ga mba r 3.7 Arsitektur umum keseluruha n sistem
Admin  mengakses  Panel  Admin  melalui  web browser untuk  mengelola
data inventori seperti bahan baku, batch stok, resep  menu, dan penyesuaian stok.
Kasir dan pelanggan masing-masing mengakses antarmuka melalui web browser
untuk terhubung ke antarmuka masing-masing, yang kemudian akan terhubung ke
modul transaksi untuk memproses  pesanan.  Ketika  transaksi  diproses, modul
transaksi secara otomatis  memicu deduksi stok ke sistem  inventori. Panel Admin
juga  terhubung  ke sistem  inventori  untuk  seluruh  operasi  pengelolaan  data.
Business  logic sistem  inventori kemudian  membaca  dan  menyimpan  data  ke
PostgreSQL.
Penelitian   ini   berfokus   pada   pengembangan sistem inventori yang
mencakup  panel  admin  dan business  logic sistem  inventori,  sedangkan sistem
transaksi merupakan modul yang sudah dikembangkan dalam penelitian terpisah. Seluruh data disimpan dan dikelola pada PostgreSQL sebagai basis data utama, yang juga digunakan oleh modul Data Mining (pengembangan terpisah) untuk analisis pola penjualan dan prediksi bahan baku.

## 3.4.2 Arsitektur Detail Sistem Inventori
Arsitektur detail sistem inventori menggambarkan komponen-komponen yang membentuk subsistem inventori serta hubungannya dengan subsistem transaksi. Sistem inventori terdiri dari tiga lapisan inti: Lapisan Service, Lapisan Model, dan Database.

**Lapisan Service (Business Logic)** merupakan inti dari sistem inventori. Lapisan ini terdiri dari InventoryService yang mengimplementasikan seluruh logika deduksi batch dengan algoritma FEFO/FIFO, termasuk pencatatan pergerakan stok ke dalam StockMovement; StockReconciliationService yang menangani logika penyesuaian stok manual (penambahan dan pengurangan) dan mekanisme pembatalan penyesuaian yang mengembalikan stok ke kondisi semula (reversal); UnitConversionService yang menangani konversi satuan antara unit resep dan unit penyimpanan bahan baku; serta MenuImageService yang menangani unggah dan penghapusan gambar menu.

**Lapisan Model (Data Access)** terdiri dari model Eloquent yang mewakili entitas inventori: Category (pengelompokan menu), Menu (beserta resep bahan baku melalui MenuIngredient), Ingredient (master data bahan baku), IngredientBatch (stok per batch dengan informasi kedaluwarsa dan harga), StockMovement (catatan immutable setiap perubahan stok yang dapat dilihat sebagai riwayat pemakaian), dan StockAdjustment (penyesuaian stok manual). Seluruh model ini menggunakan Eloquent ORM untuk membaca dan menulis data ke database.

**Database PostgreSQL** menyimpan seluruh data inventori.

Sistem transaksi berinteraksi dengan sistem inventori melalui dua jalur. Pertama, Panel Admin Filament melakukan operasi CRUD ke model inventori (Category, Menu, Ingredient, IngredientBatch, StockAdjustment) serta membaca data riwayat pemakaian dari StockMovement. Kedua, controller transaksi (CashierPesananBaruController dan CashierOrderController) memanggil InventoryService::processSaleForOrder() ketika pesanan diproses, yang kemudian menjalankan algoritma deduksi batch, mencatat perubahan ke StockMovement, dan memperbarui stok pada IngredientBatch yang sesuai.

## 3.5 Perancangan Basis Data
## 3.5.1 Entity Relationship Diagram

12Ga mba r 3.8 ERD sistem inventori
Terdapat   lima   tabel   utama   penyusun   sistem   manajemen   inventori.
Kelimanya terdiri    dari ingredients sebagai    master    data    bahan    baku,
ingredient_batches untuk  menyimpan  stok  per  batch, menu_ingredients
sebagai  tabel  yang  menghubungkan  menu  dengan  bahan  baku  penyusunnya
beserta  jumlah  pemakaian, stock_movements sebagai  catatan immutable setiap
perubahan    stok    yang    terjadi,    dan stock_adjustments untuk    mencatat
penyesuaian stok manual beserta riwayat pembatalannya.

## 3.5.2 Deskripsi Entitas
Pada  implementasi  sistem  manajemen  inventori, perencanaan database
dijelaskan   secara   rinci   melalui   tabel-tabel   berikut   yang   mencakup   seluruh
spesifikasi  teknis  penyimpanan  data  berdasarkan Entity  Relationship  Diagram
## (ERD).


4Ta bel 3.3 Struktur ta bel ingredients
## Kolom Tipe Keterangan
id BIGINT Primary key, auto-
increment
name VARCHAR Nama bahan baku
unit ENUM Satuan unit
low_stock_threshold DECIMAL Ambang batas stok
minimum
batch_mode ENUM Mode     deduksi batch
## (FIFO/FEFO)
is_active BOOLEAN Status aktif
deleted_at TIMESTAMP Soft delete
created_at TIMESTAMP Waktu dibuat
updated_at TIMESTAMP Waktu diperbarui
Ta bel 3.4 Struktur ta bel ingredient_ba tches
## Kolom Tipe Keterangan
id BIGINT PK Primary key, auto-
increment
ingredient_id BIGINT FK Foreign key ke
ingredients
quantity DECIMAL Jumlah stok
expiry_date DATE Kedaluwarsa (FEFO)
received_at TIMESTAMP Penerimaan  (FIFO)
 cost_per_unit INTEGER Harga per unit
 
 6Ta bel 3.5 Struktur ta bel menu_ingredients
## Kolom Tipe Keterangan
id BIGINT PK Primary key, auto-
increment
menu_id BIGINT FK
Foreign key ke menus


ingredient_id BIGINT FK Foreign key ke
ingredients
quantity_used DECIMAL Jumlah   bahan   per   unit
menu

7Ta bel 3.6 Struktur ta bel stock_a djustments
## Kolom Tipe Keterangan
id BIGINT PK Primary key, auto-
increment
code VARCHAR Kode penyesuaian (ADJ-
## DDMMYY-N)
adjustable_type VARCHAR Tipe (ingredient/menu)
ingredient_id BIGINT FK Foreign key ke
ingredients
menu_id BIGINT FK
Foreign key ke menus
adjustment_type ENUM Tipe (increase/decrease)
category VARCHAR Kategori penyesuaian
quantity DECIMAL Jumlah penyesuaian
quantity_before DECIMAL Stok sebelum
quantity_after DECIMAL Stok sesudah
reason TEXT Alasan penyesuaian
reported_by BIGINT FK
Foreign key ke users
adjusted_at TIMESTAMP Waktu kejadian
status VARCHAR Status (active/cancelled)
cancel_reason TEXT Alasan pembatalan
created_at TIMESTAMP Waktu dibuat
updated_at TIMESTAMP Waktu diperbarui










8Ta bel 3.7 Struktur ta bel stock_movements
## Kolom Tipe Keterangan
id BIGINT PK Primary key, auto-
increment
ingredient_id BIGINT FK Foreign key ke
ingredients
ingredient_batch_id BIGINT FK Foreign key ke
ingredient_batches
order_id BIGINT FK
Foreign key ke orders
order_item_id BIGINT FK Foreign key ke
order_items
stock_adjustment_id BIGINT FK Foreign key ke
stock_adjustments
movement_type ENUM Jenis   pergerakan   (sale,
purchase, adjustment
increase, adjustment
decrease, waste,
correction)
source_type VARCHAR Tipe sumber (order,
adjustment, waste)
source_id VARCHAR ID sumber
quantity_before DECIMAL(12,2) Stok sebelum
quantity_change DECIMAL(12,2) Jumlah perubahan
quantity_after DECIMAL(12,2) Stok sesudah
unit_cost DECIMAL(12,2) Harga per unit
notes TEXT Catatan
recorded_by BIGINT FK
Foreign key ke users
created_at TIMESTAMP Waktu dicatat


## BAB IV
## IMPLEMENTASI DAN PENGUJIAN

## 4.1 Implementasi  Panel Admin
## 4.1.1 Halaman Login
Halaman login merupakan  halaman  pertama  yang  muncul  ketika  admin
mengakses  panel  admin.  Halaman  ini menampilkan formulir  autentikasi  dengan
input email dan password untuk   memverifikasi   identitas   admin   sebelum
mengakses sistem.

13 Ga mba r 4.1 Ha la ma n login a dmin

## 4.1.2 Halaman Kategori Menu
Halaman kategori menu menampilkan daftar kategori menu beserta jumlah
menu   yang   termasuk   ke   dalam   kategori   tersebut.   Admin   dapat   melihat,
menambah, mengedit, dan menghapus kategori sesuai kebutuhan.


14 Ga mba r 4.2 Hala ma n da fta r ka tegori

Ga mba r 4.3 Form bua t da fta r ka tegori ba ru
Form  tambah  kategori  berisi  input  nama  kategori  yang  harus  diisi  oleh
admin. Setelah disimpan, kategori baru akan muncul di tabel daftar kategori.

## 4.1.3 Halaman Menu
Halaman   menu   menampilkan   daftar   seluruh   menu   beserta   informasi
kategori,  harga, diskon khusus mahasiswa STIE  Totalwin, jumlah prediksi sisa
jual, dan status ketersediaan. Admin dapat melihat dan mengelola data menu dari
halaman ini.



Ga mba r 4.4 Ha la ma n da fta r menu

Ga mba r 4.5 Ha la ma n form bua t menu
Form  buat  menu  baru  terdiri  dari  input  nama  menu,  pemilihan  kategori,
foto  menu  yang  akan ditampilkan di web self-order pelanggan (opsional), harga
menu, diskon khusus mahasiswa STIE Totalwin (opsional), toggle status tersedia,
serta pengelolaan resep bahan baku penyusun menu. Admin dapat memilih bahan
baku  dari dropdown dan  menentukan  jumlah  pemakaian  per  unit  menu. Setiap
menu  wajib  memiliki  minimal  satu  bahan  baku. Jika  status  tersedia  aktif,  maka
menu akan  muncul di web POS kasir dan web self-order pelanggan. Sebaliknya,
jika  status  tersedia  dimatikan,  maka  menu  tidak  akan  muncul  di  web  POS  kasir
dan web self-order pelanggan.15



## 4.1.4 Halaman Bahan Baku
Halaman  bahan  baku   menampilkan  daftar  seluruh  bahan   baku  yang
terdaftar  dalam  sistem  beserta  informasi seperti unit,  total  stok (dihitung  dari
seluruh batch stok  yang  ada), kedaluwarsa  terdekat, mode prioritas batch stok
bahan baku (FEFO/FIFO). Admin dapat mengunjungi halaman batch stok bahan
baku dan riwayat pemakaian bahan baku yang dipilih dari halaman ini.

Ga mba r 4.6 Ha la ma n ba ha n ba ku

Ga mba r 4.7 Form bua t ba ha n ba ku ba ru
Form  tambah  bahan  baku  berisi  input  nama  bahan  baku,  pemilihan  unit
satuan,  dan  mode prioritas batch stok.  Mode  batch  menentukan  urutan priotitas
deduksi  stok ketika  stok  berkurang  karena  pesanan.  Terdapat dua  mode  prioritas
batch stok, yaitu  FEFO yang  memprioritaskan masa  kedaluwarsa, serta FIFO
yang memprioritaskan waktu penerimaan batch stok. Khusus mode FEFO, batch


stok yang kedaluwarsa secara default tidak akan dipakai lagi oleh  sistem. Namun
perilaku  sistem  ini  bisa  diubah  admin  untuk  setiap batch yang  akan  diinput
melalui  form input batch stok baru.

## 4.1.5 Halaman Batch Stok Bahan Baku
Halaman batch stok  bahan  baku  menampilkan  daftar batch untuk  suatu
bahan baku. Setiap batch menampilkan  informasi kode batch, jumlah stok untuk
masing-masing batch, tanggal  kedaluwarsa,  waktu  diterima,  dan harga  satuan.
Batch stok  yang  habis  akan  otomatis  disembunyikan  saat  pertama  kali  admin
mengunjungu  halaman  ini,  namun  bisa  ditampilkan  jika  admin  mengklik  tombol
"Tampilkan Batch Habis".

Ga mba r 4.8 Ha la ma n batch stok
Batch stok  yang  kedaluwarsa  secara default tidak  akan  dipakai  lagi  oleh
sistem. Namun admin harus secara manual menandai bahwa batch stok tersbebut
sudah kedaluwarsa agar sesuai  keadaan  nyata  di  lapangan.  Jika  sudah  ditandai
kedaluwarsa,  maka batch stok  akan  dianggap  habis  dan  akan  disembunyikan
ketika  admin  pertama  kali  mengunjungi  halaman batch stok  untuk  bahan  baku
tersebut.



Ga mba r 4.9 Admin mena nda i batch keda luwa rsa
Form buat batch  stok berfungsi  untuk  menambah batch stok  baru  yang
berisi  input  jumlah  stok,  tanggal  kedaluwarsa (wajib  jika  bahan  baku  memakai
mode  FEFO,  opsional  jika  mode  FIFO), waktu  diterima, dan  harga satuannya.
Terakhir terdapat toggle yang jika diaktifkan, maka batch stok tersebut tetap dapat
terpakai untuk pesanan walaupun batch tersebut sudah kedaluwarsa dan memakai
prioritas batch FEFO. Hal  ini  dapat  berguna  untuk  memberikan  fleksibilitas
kepada admin  untuk  bahan  baku  yang sudah  kedaluwarsa  secara  sistem  namun
dalam kondisi nyata masih layak pakai.

Ga mba r 4.10 Form bua t batch stok ba ru



## 4.1.6 Halaman Penyesuaian Stok
Halaman penyesuaian  stok menampilkan daftar seluruh penyesuaian yang
telah dilakukan, mencakup informasi kode, waktu, tipe
(penambahan/pengurangan),  jenis   (bahan  baku/menu),  nama,  kategori,  status
(aktif/dibatalkan), serta jumlah stok sebelum dan sesudah. Setiap baris dilengkapi
tombol  "Detail"  untuk  melihat  informasi  lengkap  dan  tombol  "Batalkan"  untuk
membatalkan penyesuaian yang masih  aktif.

Ga mba r 4.11 Ha la ma n penyesua ia n stok
Detail   penyesuaian   stok   menampilkan   informasi   lengkap   mengenai
penyesuaian stok. Informasi tambahan yang hanya ditampilkan detail penyesuaian
meliputi catatan penyesuaian serta alasan pembatalan (jika dibatalkan).

Ga mba r 4.12 Deta il penyesua ia n stok


Form penyesuaian  stok baru terdiri dari pemilihan jenis  (bahan baku atau
menu), pemilihan bahan baku atau menu yang akan disesuaikan, tipe penyesuaian
(Penambahan  atau  Pengurangan),  kategori  penyesuaian  (seperti  Kedaluwarsa,
Rusak,  Tumpah,  Koreksi  Stok,  atau  Lainnya),  input  jumlah,  catatan,  petugas
pelapor,   dan   tanggal   kejadian.   Setiap   penyesuaian   dicatat   oleh   sistem  dan
memperbarui  stok pada batch terkait.

Ga mba r 4.13 Form bua t penyesua ia n stok ba ru
Penyesuaian  stok  yang  masih  berstatus  aktif  dapat  dibatalkan  melalui
tombol  "Batalkan".  Admin  wajib  mengisi  alasan  pembatalan.  Ketika  dibatalkan,
sistem  secara  otomatis  mengembalikan  stok  ke  kondisi  sebelum  penyesuaian
dilakukan dengan membalikkan setiap perubahan pada batch terkait dan mencatat
pergerakan  stok  baru  sebagai reversal.  Status  penyesuaian  berubah  menjadi
"Dibatalkan" dan stok kembali seperti semula.



Ga mba r 4.14 Admin memba ta lka n penyesua ia n stok

## 4.1.7 Halaman Riwayat Penggunaan Bahan Baku
Halaman  riwayat  penggunaan  bahan  baku  menampilkan  data  pemakaian
bahan  baku  berdasarkan  transaksi  penjualan  yang  telah  terjadi.  Informasi  ini
membantu admin dalam memantau tren penggunaan bahan baku.

16Ga mba r 4.15 Ha la ma n riwa ya t stok




17Ga mba r 4.16 Ha la ma n deta il riwa ya t stok versi penyesua ia n

18Ga mba r 4.17 Ha la ma n deta il riwa ya t stok versi penjua la n
Tombol    detail    pada    halaman    riwayat    penggunaan    bahan    baku
menampilkan informasi  lebih lanjut mengenai pemakaian yang terjadi.  Tampilan
detail bersifat fleksibel tergantung pada jenis pergerakan stok yang mendasarinya.
Ada  dua  jenis  pemakaian,  yaitu  penjualan  dan  penyesuaian. Jika pemakaian
berasal  dari  transaksi  penjualan,  detail  akan  menampilkan  pesanan  terkait  dan
item  menu  yang  diproses. Jika pemakaian  berasal  dari  penyesuaian  stok,  detail
akan menampilkan  kode penyesuaian dan alasan dilakukannya penyesuaian.


## 4.1.8 Implementasi Algoritma Deduksi Stok
Algoritma deduksi stok merupakan inti dari sistem manajemen inventori
yang menentukan urutan konsumsi batch ketika terjadi pemakaian bahan baku.
Sistem mengimplementasikan dua mode deduksi utama, yaitu FEFO (First-Expiry-
First-Out) untuk bahan dengan masa kedaluwarsa terbatas dan FIFO (First-In-
First-Out) untuk bahan non-perishable. Mekanisme penguncian data (row-level
locking) diterapkan untuk mencegah konflik pada transaksi bersamaan.

Penentuan urutan batch dilakukan melalui perintah match yang
menerjemahkan mode batch bahan baku menjadi urutan query SQL. Batch
dengan quantity lebih besar dari nol diambil, kemudian diurutkan berdasarkan
mode yang dikonfigurasi pada setiap bahan baku. Batch yang memiliki nilai
relevan kosong (NULL) ditempatkan di akhir urutan agar tidak mengganggu
prioritas.

$query = IngredientBatch::where('ingredient_id',
$ingredientId)
##     ->where('quantity', '>', 0)
##     ->where(function ($q) {
##         $q->whereNull('expiry_date')
##           ->orWhereDate('expiry_date', '>',
## now())
##           ->orWhere('allow_expired_usage', true);
##     })
##     ->lockForUpdate();

match ($ingredient->batch_mode) {
##     Ingredient::BATCH_MODE_FIFO => $query
##         ->orderByRaw('CASE WHEN received_at IS NULL
## THEN 1 ELSE 0 END')
##         ->orderBy('received_at', 'asc')
##         ->orderBy('expiry_date', 'asc')
##         ->orderBy('id', 'asc'),
##     default => $query  // FEFO
##         ->orderByRaw('CASE WHEN expiry_date IS NULL
## THEN 1 ELSE 0 END')
##         ->orderBy('expiry_date', 'asc')
##         ->orderBy('received_at', 'asc')
##         ->orderBy('id', 'asc'),
};

Pada kode di atas, baris match menentukan urutan batch berdasarkan
mode. Pada mode FIFO, batch diurutkan berdasarkan received_at terlama
(ascending). Pada mode default (FEFO), batch diurutkan berdasarkan
expiry_date terdekat (ascending). Klausa orderByRaw('CASE WHEN ... IS
NULL THEN 1 ELSE 0 END') memastikan bahwa batch yang memiliki nilai
relevan kosong ditempatkan paling akhir sehingga tidak dikonsumsi lebih dahulu.
Klausa lockForUpdate() mengunci baris-baris batch yang terpilih untuk mencegah
transaksi bersamaan mengakses data yang sama sebelum transaksi saat ini selesai.

Setelah batch diurutkan sesuai prioritas, sistem melakukan iterasi deduksi
dari batch pertama hingga kebutuhan kuantitas terpenuhi. Setiap iterasi mencatat
pergerakan stok melalui model StockMovement yang merekam quantity_before,
quantity_change, dan quantity_after untuk keperluan audit.

foreach ($batches as $batch) {
##     if ($remainingToDeduct <= 0) break;
##     $before = (float) $batch->quantity;
##     $deductFromThisBatch = min($before,
## $remainingToDeduct);
##     $after = $before - $deductFromThisBatch;
##     $batch->quantity = $after;
##     $batch->save();
##     $remainingToDeduct -= $deductFromThisBatch;
##     StockMovement::create([
##         'ingredient_id' => $ingredientId,
##         'ingredient_batch_id' => $batch->id,
##         'order_id' => $context['order_id'] ?? null,
##         'movement_type' => $context['movement_type']
## ?? 'sale',
##         'quantity_before' => $before,
##         'quantity_change' => -
## $deductFromThisBatch,
##         'quantity_after' => $after,
##         'unit_cost' => $batch->cost_per_unit,
##         'notes' => $context['notes'] ?? null,
##     ]);
}

Pada kode di atas, setiap batch diproses secara berurutan. Variabel before
menyimpan nilai stok sebelum deduksi, deductFromThisBatch menghitung
jumlah yang diambil dari batch saat ini menggunakan fungsi min(), dan after
menyimpan nilai stok setelah deduksi. Setelah penyimpanan batch, sistem
mencatat StockMovement dengan quantity_change bernilai negatif karena
merupakan pengurangan stok. Proses berlanjut hingga remainingToDeduct habis
atau seluruh batch telah diproses.

## 4.1.9 Implementasi Penyesuaian Stok dan Perhitungan Stok
Penyesuaian stok (stock adjustment) merupakan fitur yang memungkinkan
admin melakukan perubahan stok secara manual di luar transaksi penjualan, baik
berupa penambahan (increase) maupun pengurangan (decrease, waste, damage).
Fitur pembatalan penyesuaian (cancel) juga diimplementasikan untuk
mengembalikan stok ke kondisi sebelum penyesuaian dilakukan.

Mekanisme pembatalan penyesuaian bekerja dengan cara membalikkan
(reverse) setiap pergerakan stok yang tercatat pada penyesuaian yang akan
dibatalkan. Untuk setiap StockMovement yang terkait, sistem menghitung nilai
perubahan kebalikan (reversalChange = -originalChange), mengembalikan stok
batch ke nilai semula, dan mencatat StockMovement baru sebagai jejak audit.

foreach ($record->stockMovements as $movement) {
##     $batch = IngredientBatch::find(
##         $movement->ingredient_batch_id);
##     if (! $batch) continue;
##     $originalChange = (float)
## $movement->quantity_change;
##     $reversalChange = -$originalChange;
##     $batchBefore = (float) $batch->quantity;
##     $batch->increment('quantity',
## $reversalChange);
##     $batchAfter = (float) $batch->quantity;
##     StockMovement::create([
##         'ingredient_id' =>
## $movement->ingredient_id,
##         'ingredient_batch_id' => $batch->id,
##         'stock_adjustment_id' => $record->id,
##         'movement_type' =>
## $movement->movement_type,
##         'source_type' =>
## 'stock_adjustment_reversal',
##         'source_id' => (string) $record->id,
##         'quantity_before' => $batchBefore,
##         'quantity_change' => $reversalChange,
##         'quantity_after' => $batchAfter,
##         'unit_cost' => $batch->cost_per_unit,
##         'notes' => 'Pembatalan: ' . $reason,
##     ]);
}

Selain penyesuaian stok, sistem juga menyediakan perhitungan stok yang
dikonversi menjadi jumlah porsi (servings) yang dapat diproduksi dari bahan baku
yang tersedia. Atribut stock pada model Menu menghitung ketersediaan stok
untuk setiap menu berdasarkan resep bahan baku penyusunnya. Perhitungan
dilakukan dengan membagi total stok setiap bahan baku dengan kebutuhan per
porsi (quantity_used), kemudian mengambil nilai minimum di antara seluruh
bahan baku penyusun. Pendekatan ini memastikan bahwa jumlah porsi yang
dilaporkan sesuai dengan bahan baku yang paling terbatas.

public function getStockAttribute(): ?float
## {
##     $ingredients = $this->menuIngredients()
##         ->with('ingredient')->get();
##     if ($ingredients->isEmpty()) return null;
##     $minServings = null;
##     foreach ($ingredients as $mi) {
##         if (! $mi->ingredient) continue;
##         $totalStock = (float)
## $mi->ingredient->batches()
##             ->where('quantity', '>', 0)
##             ->where(function ($q) {
##                 $q->whereNull('expiry_date')
##                   ->orWhereDate('expiry_date', '>',
## now())
##                   ->orWhere('allow_expired_usage',
## true);
##             })
##             ->sum('quantity') ?: 0;
##         $needed = (float)
## $mi->quantity_used;
##         $servings = $needed > 0
##             ? (int) ($totalStock / $needed)
##             : 0;
##         if ($minServings === null
##             || $servings < $minServings) {
##             $minServings = $servings;
##         }
##     }
##     return $minServings ?? 0;
## }

Pada kode di atas, setiap bahan baku penyusun menu diperiksa stoknya
melalui relasi batches. Hanya batch dengan quantity lebih dari nol dan belum
kedaluwarsa (atau diizinkan penggunaan kedaluwarsa) yang dihitung. Total stok
dibagi dengan quantity_used (kebutuhan per porsi) untuk mendapatkan jumlah
porsi yang dapat dibuat dari bahan tersebut. Nilai minimum (minServings) di
antara seluruh bahan kemudian menjadi nilai akhir atribut stock. Jika menu tidak
memiliki resep (ingredients kosong), fungsi mengembalikan null.


## 4.2 Pengujian
## 4.2.1 Pengujian Black Box
Pengujian black box berfokus pada validasi fungsionalitas  sistem dari sisi
antarmuka  pengguna,  memastikan  bahwa  setiap  fitur  yang  tersedia  pada  panel
administrasi  berjalan  sesuai  dengan  kebutuhan  fungsional  yang  telah  dirancang.
Skenario  pengujian black  box mencakup  autentikasi  admin,  manajemen  bahan
baku, penyesuaian stok, dan manajemen menu.
a. Pengujian Autentikasi Admin
Pengujian  autentikasi  admin  dilakukan  untuk  memastikan  bahwa  hanya
admin  yang  terdaftar  yang  dapat  mengakses  panel  admin.  Skenario  pengujian
mencakup  login  dengan  kredensial  valid,  login  dengan password salah,  dan
logout.
9Ta bel 4.1 Pengujia n black box a utentika si a dmin
## Skenario Langkah Hasil
## Diharapkan
## Status
Login dengan
kredensial valid
Isi email dan
password benar,
klik Masuk
Berhasil masuk ke
panel admin
## Berhasil
Login dengan
password salah
Isi password salah Tampil pesan
error
## Berhasil
Logout Klik tombol
## Logout
Kembali ke
halaman login
## Berhasil
b. Pengujian Manajemen  Bahan Baku
Pengujian  manajemen  bahan  baku  dilakukan  untuk  memvalidasi  bahwa
admin  dapat  melakukan  operasi  CRUD  pada  data  bahan  baku  serta  menambah
batch  stok.  Skenario  pengujian  mencakup  tambah,  ubah,  dan  hapus  bahan  baku,
serta tambah batch stok.






10Ta bel 4.2 Pengujia n black box ma na jemen ba ha n ba ku
## Skenario Langkah Hasil
## Diharapkan
## Status
Tambah bahan
baku
Isi form, simpan Data    muncul   di
tabel
## Berhasil
Ubah bahan baku Ubah    nama/unit,
simpan
Data berubah Berhasil
Hapus bahan
baku
Klik hapus Data  hilang  (soft
delete)
## Berhasil
Tambah batch
stok
Isi jumlah,
tanggal, simpan
Batch muncul  di
daftar
## Berhasil
c. Pengujian Penyesuaian Stok
Pengujian  penyesuaian  stok  dilakukan  untuk  memvalidasi  bahwa  admin
dapat melakukan penambahan dan pengurangan stok secara  manual, serta  sistem
menolak  input  yang  tidak  valid.  Skenario  pengujian  mencakup  penyesuaian
penambahan, penyesuaian pengurangan, dan input jumlah tidak valid.
11Ta bel 4.3 Pengujia n black box penyesua ia n stok
## Skenario Langkah Hasil
## Diharapkan
## Status
## Penyesuaian
## Penambahan
Isi  jumlah  positif,
pilih tipe
penambahan
Stok bertambah Berhasil
## Penyesuaian
## Pengurangan
Isi  jumlah  positif,
pilih tipe
pengurangan
Stok berkurang Berhasil
Penyesuaian    qty
## <= 0
Isi 0 Ditolak sistem Berhasil
d. Pengujian Manajemen  Menu
Pengujian  manajemen  menu  dilakukan  untuk  memvalidasi  bahwa  admin
dapat  mengelola  data  menu  beserta  resep  bahan  baku  penyusunnya.  Skenario


pengujian  mencakup  tambah,  ubah,  dan  hapus  menu,  serta  penambahan  dan
penghapusan resep menu.12
Ta bel 4.4 Pengujia n black box ma na jemen resep menu
## Skenario Langkah Hasil
## Diharapkan
## Status
Tambah menu
baru
Isi form, simpan Data    muncul   di
tabel
## Berhasil
Ubah Menu  Ubah    nama/unit,
simpan
Data berubah Berhasil
Hapus Menu Klik hapus Data  hilang  (soft
delete)
## Berhasil
Tambah resep
menu
Pilih ingredient
dan quantity
Menu mempunyai
resep yang
ditambahkan
## Berhasil
Hapus resep
menu
Klik hapus Muncul pesan
peringatan
resepmenu   wajib
diisi
## Berhasil
Seluruh  skenario  pengujian black  box pada  kelima  modul  menunjukkan
status  Berhasil.  Hasil  ini  menandakan  bahwa  seluruh  fungsionalitas  antarmuka
panel  administrasi  telah  berjalan  sesuai  harapan,  mulai  dari  autentikasi  admin,
pengelolaan  bahan  baku  dan  batch  stok,  penyesuaian  stok, hingga manajemen
resep menu.

## 4.2.2 Pengujian White  Box
Pengujian white  box dilakukan  untuk  memverifikasi  kebenaran  logika
internal sistem menggunakan PHPUnit. Pengujian mencakup lima skenario utama
yang merepresentasikan  fitur inti manajemen  inventori.
## 1. Pengujian Deduksi Stok Berdasarkan Resep Menu
Pengujian  ini  memvalidasi bahwa ketika  suatu menu yang  memiliki  resep
(komposisi bahan baku) diproses, sistem secara otomatis  mengurangi stok bahan


baku sesuai dengan jumlah yang terdaftar pada tabel menu_ingredients. Dalam
skenario  pengujian, langkah  pertama  adalah  menentukan sebuah  menu  "Kopi
Susu" dengan resep 30 gram kopi per porsi dipesan sebanyak 2 porsi. Lalu sistem
harus mengurangi stok kopi sebesar 60 gram dari batch yang tersedia.
public function
test_decrease_stock_for_order_deducts_ingredients_by_recipe():
void
## {
## $menu = Menu::factory()->create();
## $ingredient = Ingredient::factory()->create(['unit' =>
## 'gram']);
IngredientBatch::factory()->create([
## 'ingredient_id' => $ingredient->id,
## 'quantity' => 100,
## ]);
MenuIngredient::create([
## 'menu_id' => $menu->id,
## 'ingredient_id' => $ingredient->id,
## 'quantity_used' => 30,
## ]);

$result = app(InventoryService::class)-
>decreaseStockForOrder([
## ['menu_id' => $menu->id, 'quantity' => 2],
## ]);

$this->assertTrue($result['success']);
$this->assertDatabaseHas('stock_movements', [
## 'ingredient_id' => $ingredient->id,
## 'movement_type' => 'sale',
## ]);
## }

Ga mba r 4.18 Pengujia n deduksi stok berda sa rka n resep menu

- Pengujian Algoritma FIFO
Pengujian  ini  memvalidasi  bahwa  algoritma  FIFO  (First-In-F irst-Out)
mengonsumsi batch dengan received_at paling awal terlebih dahulu. Dua batch
bahan  baku  dengan  mode  FIFO dibuat,  yaitu Batch A  (diterima  5  hari  lalu,  qty:
100)  dan Batch B  (diterima  1  hari  lalu,  qty:  200).  Setelah  dilakukan  deduksi
sebesar  60  gram, Batch A  berkurang  menjadi  40  gram  dan batch B  tetap  200
gram.  Hasil  pengujian  sesuai  dengan  prinsip  FIFO  karena batch yang  diterima
lebih awal diproses terlebih dahulu.



public function test_fifo_deducts_oldest_batch_first(): void
## {
$oldBatch = IngredientBatch::factory()->create([
'quantity' => 100, 'received_at' => now()->subDays(5),
## ]);
$newBatch = IngredientBatch::factory()->create([
'quantity' => 200, 'received_at' => now()->subDays(1),
## ]);

app(InventoryService::class)->decreaseStockForOrder([...]);

$this->assertSame(40.0, (float) $oldBatch->fresh()-
## >quantity);
$this->assertSame(200.0, (float) $newBatch->fresh()-
## >quantity);
## }

Ga mba r 4.19 Pengujia n a lgoritma  FIFO

- Pengujian Algoritma FEFO
Pengujian  ini  memvalidasi  bahwa  algoritma  FEFO  (First-Expiry-First-
Out) menggunakan batch dengan expiry_date terdekat  terlebih  dahulu.  Dua
batch bahan baku dengan mode default FEFO dibuat, yaitu Batch A (kedaluwarsa
3  hari  lagi,  qty:  80)  dan Batch B  (kedaluwarsa  30  hari  lagi,  qty:  80).  Setelah
dilakukan deduksi sebesar 100 unit, Batch A habis terpakai (80 unit) dan Batch B
tersisa  60  unit.  Prioritas  terhadap batch yang  mendekati  kedaluwarsa  ini  penting
untuk bahan baku segar yang memiliki  masa simpan  terbatas.
public function test_fefo_deducts_soonest_expiry_first(): void
## {
$nearExpiry = IngredientBatch::factory()->create([
'quantity' => 80, 'expiry_date' => now()->addDays(3),
## ]);
$farExpiry = IngredientBatch::factory()->create([
'quantity' => 80, 'expiry_date' => now()->addDays(30),
## ]);

app(InventoryService::class)->decreaseStockForOrder([...]);

$this->assertSame(0.0, (float) $nearExpiry->fresh()-
## >quantity);
$this->assertSame(60.0, (float) $farExpiry->fresh()-
## >quantity);
## }



Ga mba r 4.20 Pengujia n a lgoritma  FEFO

## 4. Pengujian Penyesuaian Stok
Pengujian  ini  memvalidasi  bahwa  admin  dapat  melakukan  penyesuaian
stok secara manual. Penyesuaian tipe increase menambah stok pada batch terbaru,
sedangkan tipe decrease mengurangi stok dari batch yang ada. Setiap penyesuaian
dicatat melalui entri StockAdjustment dan StockMovement.
public function
test_increase_adjustment_adds_quantity_to_latest_batch(): void
## {
## $ingredient = Ingredient::factory()->create();
$batch = IngredientBatch::factory()->create([
## 'ingredient_id' => $ingredient->id,
## 'quantity' => 50,
## ]);

$adjustment = app(StockReconciliationService::class)
->createManualAdjustment(
ingredientId: $ingredient->id,
quantity: 20,
adjustmentType: 'increase',
reason: 'Restock',
reportedBy: $admin->id,
## );

$this->assertSame(70.0, (float) $batch->fresh()->quantity);
$this->assertSame('increase', $adjustment->adjustment_type);
## }

Ga mba r 4.21 Pengujia n penyesua ia n stok

## 5. Pengujian Pembatalan Penyesuaian Stok
Pengujian    ini    memvalidasi    bahwa    ketika    suatu    penyesuaian    stok
dibatalkan,   sistem    mengembalikan   stok   ke   kondisi    sebelum   penyesuaian
dilakukan.   Pembatalan   penyesuaian increase akan    mengurangi   stok,   dan
pembatalan  penyesuaian decrease akan  menambah  stok  kembali.  Seluruh  proses
dicatat dalam StockMovement baru.
public function test_cancelling_adjustment_restores_stock():
void
## {


// Setup: buat adjustment increase 20 unit
$adjustment = $this->createAdjustment('increase', 20);
$stockBeforeCancel = (float) $adjustment->ingredient-
>getTotalStock();

// Batalkan adjustment
$adjustment->cancel('Wrong quantity');
$stockAfterCancel = (float) $adjustment->ingredient-
>fresh()->getTotalStock();

// Stok harus kembali ke jumlah awal
$this->assertSame($stockBeforeCancel - 20,
$stockAfterCancel);
$this->assertDatabaseCount('stock_movements', 2);
## }

Ga mba r 4.22 Pengujia n pemba ta la n penyesua ia n stok
Seluruh pengujian white box menunjukkan hasil sesuai dengan spesifikasi
yang  dirancang.  Algoritma  FIFO  dan  FEFO  bekerja  dengan  benar,  penyesuaian
stok  berjalan  akurat,  serta  pembatalan  penyesuaian  berhasil  mengembalikan  stok
ke kondisi awal.

## 4.2.3 Pengujian Integration
Pengujian integrasi merupakan level pengujian yang melengkapi pengujian
black box dan white box. Jika black box menguji fungsionalitas fitur secara
individual dan white box menguji kebenaran logika internal, maka pengujian
integrasi memvalidasi aliran data antar modul serta konsistensi state ketika terjadi
pertukaran informasi antar komponen sistem [18].

a. Pengujian Penambahan Batch
Pengujian penambahan batch dilakukan untuk memverifikasi bahwa
penambahan stok bahan baku melalui fitur batch management menghasilkan
perubahan total stok yang akurat dan tidak menghasilkan pencatatan
stock_movements yang tidak semestinya.

## Skenario Langkah Hasil Diharapkan Status
Tambah batch Tambah batch
stok dengan
kuantitas 50
unit
Total stok
bertambah 50
sesuai batch
## Berhasil
Verifikasi
## stock_movements
Cek tabel
## stock_movements
Tidak ada
pergerakan baru
(penambahan
batch bukan
transaksi stok)
## Berhasil

b. Pengujian Penyesuaian Stok
Pengujian penyesuaian stok dilakukan untuk memverifikasi bahwa
penyesuaian stok tipe increase dan decrease berfungsi dengan benar, serta
pembatalan penyesuaian mengembalikan stok ke kondisi semula dan mencatat
reversal movement.

## Skenario Langkah Hasil Diharapkan Status
## Adjustment
## increase
Buat adjustment
dengan kuantitas
## +30
Batch stok
bertambah 30
## Berhasil
Verifikasi batch
naik
Cek kuantitas
batch terkait
Kuantitas batch
bertambah sesuai
adjustment
## Berhasil
## Batalkan
## adjustment
Klik batalkan
pada adjustment
Stok kembali ke
jumlah semula
## Berhasil
Verifikasi
reversal
Cek
## stock_movements
Movement reversal
tercatat dengan
quantity_change
berlawanan
## Berhasil

c. Pengujian Deduksi FEFO
Pengujian deduksi FEFO dilakukan untuk memverifikasi bahwa batch
dengan expiry_date terdekat dikonsumsi terlebih dahulu ketika terjadi pemakaian
stok.

## Skenario Langkah Hasil Diharapkan Status
Buat 2 batch Batch A: qty 80,
expiry 3 hari
Batch B: qty 80,
expiry 30 hari
Kedua batch
terbuat
## Berhasil
Deduksi 100
unit
Jalankan fungsi
deduksi stok
Batch A habis (80
unit), Batch B
sisa 60 unit
## Berhasil
Verifikasi
prioritas
## FEFO
Cek urutan
deduksi
Batch expiry 3
hari terpakai
duluan
## Berhasil

d. Pengujian Konsistensi Riwayat
Pengujian konsistensi riwayat dilakukan untuk memverifikasi bahwa setiap
pergerakan stok mencatat quantity_before, quantity_change, dan quantity_after
secara akurat sehingga riwayat dapat dilacak dengan tepat.

## Skenario Langkah Hasil Diharapkan Status
Proses order Buat order
dengan 2 menu
beresep
Order diproses Berhasil
Cek konsistensi
## stock_movements
Periksa
quantity_before,
quantity_change,
quantity_after
quantity_after =
quantity_before +
quantity_change
## Berhasil
Verifikasi
penjumlahan
Hitung total
quantity_change
Total sesuai
dengan jumlah
bahan baku yang
terpakai
## Berhasil

e. Pengujian Integrasi Lintas Modul — Order ke Stok
Pengujian integrasi lintas modul dilakukan untuk memverifikasi bahwa
ketika pesanan diproses melalui modul transaksi (POS), stok bahan baku pada
modul inventori berkurang sesuai resep menu dan daily_ingredient_usage tercatat
dengan benar.

## Skenario Langkah Hasil Diharapkan Status
Order POS Buat pesanan
melalui sistem
## POS
Stok bahan baku
berkurang sesuai
resep
## Berhasil
Verifikasi
deduksi resep
Cek total stok
bahan baku
penyusun
Stok berkurang
tepat sesuai
quantity_used kali
kuantitas order
## Berhasil
Verifikasi
## daily_usage
Cek tabel
## daily_ingredient
## _usage
Pemakaian harian
tercatat dengan
tanggal dan
kuantitas yang
benar
## Berhasil

Seluruh skenario pengujian integration menunjukkan status Berhasil. Hasil
ini membuktikan bahwa aliran data antar modul inventori dan modul transaksi
berjalan konsisten, pencatatan pergerakan stok akurat, serta mekanisme deduksi
batch dan reversal berfungsi sesuai perancangan.


## BAB V
## PENUTUP

## 5.1 Kesimpulan
Berdasarkan   hasil   perancangan,   implementasi,   dan   pengujian   sistem
manajemen  inventori  pada Point  of  Sale W9 Cafe menggunakan  Laravel  dan
Filament, dapat ditarik kesimpulan sebagai berikut:
- Sistem  berhasil  menerapkan  manajemen  inventori  berbasis  bahan  baku
dengan  resep  terintegrasi  yang  memungkinkan  setiap  menu  yang  terjual
memberikan  dampak langsung terhadap stok bahan baku terkait.
- Sistem  berhasil  menerapkan  prioritas  penggunaan  stok  berdasarkan  masa
kedaluwarsa  (FEFO)  dan  urutan  penerimaan  (FIFO)  yang  meminimalkan
pemborosan bahan baku serta menjaga konsistensi data stok.
- Sistem  berhasil  mencatat  pemakaian  bahan  baku  secara  otomatis  setiap
kali  terjadi  transaksi  penjualan  dan  menyediakan  riwayat  perubahan  stok
yang dapat dilacak secara lengkap.
- Pengujian black   box terhadap   seluruh   modul   menunjukkan   skenario
berhasil.   Pengujian   white   box   memvalidasi   kebenaran   deduksi   stok
berbasis   resep,   algoritma   FIFO   dan   FEFO,   penyesuaian   stok,   serta
pembatalan penyesuaian stok. Pengujian integrasi memvalidasi konsistensi
aliran data antar modul inventori dan modul transaksi.

## 5.2 Saran
Berdasarkan hasil penelitian, terdapat beberapa saran untuk pengembangan
lebih lanjut:
- Sistem  manajemen  inventori  ini  dapat  dikembangkan  dengan  aplikasi
mobile agar  sistem  dapat diakses  dengan  mudah  ketika  admin  atau  kasir
sedang tidak berada di dekat laptop atau desktop PC.
- Sistem manajemen inventori ini dapat dikembangkan dengan menerapkan
notifikasi  Stok  Menipis  dan  Kedaluwarsa  melalui  aplikasi  perpesanan
seperti  WhatsApp  ketika  stok  bahan  baku  berada di  bawah  ambang  batas


atau mendekati tanggal kedaluwarsa agar  admin dapat mengetahui segera
bahan baku mana yang stoknya perlu diperbarui.
- Integrasi Barcode Scanner untuk  mempercepat dan mempermudah proses
pencatatan dan identifikasi batch stok bahan baku.


## DAFTAR PUSTAKA

[1] Sisilia  Anyel  Faridawati,  Henrikus  Herdi,  and  Paulus  Libu  Lamawitak,
“Analisis  Penerapan  Sistem  Informasi  Akuntansi  untuk  Meningkatkan
Efisiensi dan Keamanan Keuangan UMKM (Cafe Rindu Lokaria),” Jurnal
Ekonomi,  Akuntansi,  dan  Perpajakan,  vol.  1,  no.  4,  pp.  189–215,  Aug.
2024, doi: https://doi.org/10.61132/jeap.v1i4.443.
[2] Supron  and  Atang  Susila,  “Aplikasi  Point  Of  Sales  (POS)  Berbasis
## Website    Dengan    Menggunakan    Laravel    :    Studi    Kasus:    Bakmi
Djowo,” LOGIC : Jurnal Ilmu Komputer dan Pendidikan, vol. 2, no. 1, pp.
## 160–167, 2023, Available:
https://journal.mediapublikasi.id/index.php/logic/article/view/2902
[3] M. Devega, Yuhelmi Yuhelmi, and Yuvi Darmayunata,
## “PEMBANGUNAN SISTEM INVENTORI APOTEK
MENGGUNAKAN METODE FIFO DAN FEFO,” Zonasi,  vol.  6,  no.  1,
pp. 159–172, Feb. 2024, doi: https://doi.org/10.31849/zn.v6i1.17318.
[4] R.  Al  Farisi,  A.  Ramadhan  Zayn,  B.  Agung  Nugroho,  and  A.  Heriadi,
“Implementasi  Sistem  Informasi  Akademik  Pengelolaan  Tugas  Akhir
Berbasis Laravel dan Filament,” Jurnal Sistem Informasi Triguna Dharma
(JURSI    TGD),    vol.    4,    no.    3,    pp.    486–496,    May    2025,    doi:
https://doi.org/10.53513/jursi.v4i3.10989.
[5] P.  Garbarz  and  M.  Plechawska-Wójcik,  “Comparative  analysis  of  PHP
frameworks  on  the  example  of  Laravel  and  Symfony,” Journal   of
Computer   Sciences   Institute,   vol.   22,   pp.   18–25,   Mar.   2022,   doi:
https://doi.org/10.35784/jcsi.2781.
[6] D.  T.  Haniva,  J.  A.  Ramadhan,  and  A. Suharso,  “Systematic  Literature
## Review    Penggunaan    Metodologi    Pengembangan    Sistem    Informasi
Waterfall, Agile, dan Hybrid,” JIEET (Journal of Information Engineering
and  Educational  Technology),  vol.  7,  no.  1,  pp.  36–42,  Jun.  2023,  doi:
https://doi.org/10.26740/jieet.v7n1.p36-42.


[7] E. Kurniawati and A. Ikhwan, “Perancangan Sistem Informasi Manajemen
## Inventaris Kontrol Stok Barang Berbasis Web,” Jurnal  Teknologi  Sistem
Informasi  dan  Aplikasi,  vol.  6,  no.  3,  pp.  408–415,  Jul.  2023,  doi:
https://doi.org/10.32493/jtsi.v6i3.30881.
[8] PostgreSQL Global Development Group, "PostgreSQL 18.4
## Documentation," May 14, 2026. Available:
https://www.postgresql.org/docs/18/
[9] M. Devega, Yuhelmi Yuhelmi, and Yuvi Darmayunata,
## “PEMBANGUNAN SISTEM INVENTORI APOTEK
MENGGUNAKAN METODE FIFO DAN FEFO,” Zonasi,  vol.  6,  no.  1,
pp. 159–172, Feb. 2024, doi: https://doi.org/10.31849/zn.v6i1.17318.
[10] Sudirman  Sudirman  and  Ika  Agustina,  “PENGEMBANGAN  SISTEM
## POINT OF SALE (POS) BERBASIS WEB DALAM MENINGKATKAN
COSTUMER  RELATIONSHIP  MANAGEMENT,” Indonesian  Journal
of  Economy,  Business,  Entrepreneurship  and  Finance,  vol.  4,  no.  1,  pp.
108–119, 2024, doi: https://doi.org/10.53067/ijebef.v4i1.142.
[11] Fried   Sinlae,   Eko   Irwanda,   Zaky   Maulana,   and   V.   E.   Syahputra,
“Penggunaan  Framework  Laravel  dalam  Membangun  Aplikasi  Website
Berbasis PHP,” Jurnal  Siber  Multi  Disiplin ,  vol.  2,  no.  2,  pp.  119–132,
2024, doi: https://doi.org/10.38035/jsmd.v2i2.186.
[12] B. S. R. Adawiyah, S. I. Murpratiwi, and A. Manan, “Development of the
SI-FARA  (Facility  and  Meeting  Reservation Information  System) Admin
Back-End  Using  the  Laravel  Filament  Framework  at  the  Diskominfo
Mataram City,” Jurnal Begawe Teknologi Informasi (JBegaTI), vol. 6, no.
2, Sep. 2025, doi: https://doi.org/10.29303/jbegati.v6i2.1348.
[13] S.  M.  Pulungan,  R.  Febrianti,  T.  Lestari,  N.  Gurning,  and  N.  Fitriana,
“Analisis  Teknik  Entity-Relationship    Diagram    Dalam    Perancangan
Database,” Jurnal Ekonomi Manajemen dan Bisnis (JEMB), vol. 1, no. 2,
pp. 143–147, Feb. 2023, doi: https://doi.org/10.47233/jemb.v1i2.533.
[14] Siska  Narulita,  Ahmad  Nugroho,  and  M.  Zakki  Abdillah,  “Diagram
Unified Modelling Language (UML) untuk Perancangan Sistem Informasi


Manajemen Penelitian dan Pengabdian Masyarakat
(SIMLITABMAS),” Bridge  :  Jurnal  publikasi  Sistem  Informasi  dan
Telekomunikasi,    vol.    2,    no.    3,    pp.    244–256,    Aug.    2024,    doi:
https://doi.org/10.62951/bridge.v2i3.174.
[15] Lailani  Fitria,  A.  Patricia,  and  Raudhatul  Jannah,  “ANALISA
## PROSEDUR PENERAPAN KARTU RENCANA STUDI
## MENGGUNAKAN FLOWCHART PADA STIE TUAH NEGERI KOTA
DUMAI,” Jurnal  Administrasi  Sosial  dan  Humaniora,  vol.  7,  no.  2,  pp.
143–143, Jan. 2024, doi: https://doi.org/10.56957/jsr.v7i2.265.
[16] N. M. Jibril, None Zulrahmadi, and N. 3Muhammad Amin, “PENGUJIAN
## SISTEM   INFORMASI   E-MODUL   PADA   SMPN   1   TEMPULING
## MENGGUNAKAN  BLACK  BOX  TESTING,” JURNAL  PERANGKAT
LUNAK, vol. 6, no. 2, pp. 327–332, Jun. 2024, doi:
https://doi.org/10.32520/jupel.v6i2.3326.
[17] M. Helmi and S. Fedianto, “Pengujian Sistem Jaringan Dokumentasi Dan
Informasi Menggunakan Black Box Testing Dan White Box Testing” vol.
3, no. 1, 2024. Available: https://repository.upnjatim.ac.id/id/eprint/20155
[18] Susanti Kurmilasari, "PENGUJIAN PERANGKAT LUNAK PADA WEBSITE
KA'CAKE: IMPLEMENTASI UNIT TESTING, INTEGRATION TESTING, SYSTEM
TESTING, DAN VALIDATION TESTING UNTUK MENJAMIN KUALITAS DAN
KEANDALAN SISTEM," JATI (Jurnal Mahasiswa Teknik Informatika), vol. 8,
no. 3, pp. 1–10, 2024.