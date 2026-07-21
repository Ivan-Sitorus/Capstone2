






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
## KATA PENGANTAR

Puji syukur  penulis  panjatkan ke hadirat Allah SWT  atas limpahan  rahmat
dan hidayah-Nya sehingga penulis dapat menyelesaikan Tugas Akhir yang berjudul
"Implementasi  Sistem  Manajemen  Inventori  pada Point  of  Sale W9 Cafe STIE
Totalwin menggunakan Laravel dan Filament" dengan baik. Tugas akhir ini disusun
sebagai salah satu syarat untuk memperoleh  gelar Sarjana Teknik pada Departemen
## Teknik Komputer, Fakultas Teknik, Universitas Diponegoro.
Penulisan tugas akhir ini tidak lepas dari bantuan, bimbingan, dan dukungan
dari berbagai  pihak. Oleh karena  itu, pada kesempatan  ini  penulis  menyampaikan
rasa terima kasih yang sebesar-besarnya  kepada:
- Bapak Dr. Maman  Somantri, S.T.,  M.T. selaku  Ketua Departemen Teknik
## Komputer Universitas Diponegoro.
- Bapak  Yudi Eko  Windarto,  S.T.,  M.Kom.,  selaku  Dosen  Pembimbing  I
yang telah memberikan  arahan,  bimbingan,  dan masukan  berharga  selama
proses penelitian dan penulisan Tugas Akhir ini.
- Bapak Ir. Rinta  Kridalukmana,   S.Kom.,  M.T.,   Ph.D.,  selaku   Dosen
Pembimbing    II   yang   telah    meluangkan    waktu   untuk   memberikan
bimbingan,  koreksi,  dan saran  perbaikan  dalam masa penyelesaian Tugas
Akhir ini.
- Seluruh  jajaran  dosen  Departemen  Teknik  Komputer,  Fakultas  Teknik,
Universitas  Diponegoro  yang  telah  memberikan  ilmu  dan  pengetahuan
selama masa perkuliahan.
- Orang  tua  dan  keluarga  penulis  yang  senantiasa  memberikan  doa dan
dukungan baik   materi    maupun    non-materi    sehingga    penulis    dapat
menyelesaikan  studi dan Tugas Akhir ini.
- Bapak   Andy selaku   admin W9 Cafe yang   telah   memberikan   izin,
kesempatan, serta  informasi  yang  dibutuhkan  selama  proses pengerjaan
proyek capstone dan penyusunan Tugas Akhir ini.

ii
- Anggota kelompok Capstone yaitu Ruben dan Ivan yang telah bekerja sama
dan membantu  dalam menyelesaikan  proyek capstone sebagai  bagian dari
Tugas Akhir ini.
- Teman-teman  penulis  semasa  SMA, khususnya  Hafizh dan Luvena, yang
telah  menemani dan  memberikan   dukungan sejak   masa   SMA,  masa
perkuliahan,  hingga penyelesaian  Tugas Akhir ini.
- Teman-teman  dari "Gladius",  khususnya  Fattah  dan  Rasya yang  telah
menemani serta memberikan  bantuan selama  masa  perkuliahan  hingga
hingga tahap akhir penyelesaian  studi.
- Rekan-rekan    mahasiswa    Departemen    Teknik    Komputer,    khususnya
angkatan  2022 "Compinero" yang  telah  berbagi  pengalaman,  dukungan,
serta kebersamaan  selama masa perkuliahan.
- Seluruh  pihak  yang  telah membantu  dalam penyelesaian Tugas Akhir  ini
baik  secara  langsung  maupun  tidak langsung yang  tidak dapat disebutkan
satu per satu.
Penulis  menyadari  bahwa  tugas akhir  ini  masih  memiliki  kekurangan  dan
keterbatasan. Oleh karena  itu, penulis  sangat mengharapkan  kritik dan saran yang
membangun untuk perbaikan di masa mendatang. Semoga laporan Tugas Akhir ini
dapat memberikan manfaat bagi pembaca dan pihak yang membutuhkan.
## Semarang, 23 Juni 2026



## Muhammad Nio Hastungkoro


iii
## DAFTAR ISI

KATA PENGANTAR.............................................................................................. i
DAFTAR ISI .......................................................................................................... iii
DAFTAR GAMBAR ............................................................................................. vi
DAFTAR TABEL ................................................................................................ viii
ABSTRAK ............................................................................................................. ix
ABSTRACT ............................................................................................................ x
BAB I PENDAHULUAN ....................................................................................... 1
1.1. Latar Belakang ........................................................................................ 1
1.2. Rumusan Masalah................................................................................... 1
1.3. Batasan Masalah ..................................................................................... 2
1.4. Tujuan Penelitian .................................................................................... 2
1.5. Manfaat Penelitian .................................................................................. 3
1.6. Metodologi Penelitian............................................................................. 3
1.7. Sistematika Penulisan ............................................................................. 3
BAB II KAJIAN PUSTAKA .................................................................................. 5
2.1. Penelitian Terdahulu ............................................................................... 5
2.2. Metode Penelitian ................................................................................... 6
2.3. Landasan Teori ....................................................................................... 8
2.3.1. Sistem Manajemen Inventori........................................................... 8
2.3.2. PostgreSQL...................................................................................... 8
2.3.3. Algoritma Deduksi Batch (FEFO dan FIFO) .................................. 8
2.3.4. Point of Sale (POS) ......................................................................... 8
2.3.5. PHP .................................................................................................. 8

iv
2.3.6. Laravel ............................................................................................. 9
2.3.7. Filament ........................................................................................... 9
2.3.8. Entity Relationship Diagram ........................................................... 9
2.3.9. Activity Diagram ............................................................................. 9
2.3.10. Flowchart ...................................................................................... 10
2.3.11. Pengujian Black Box ...................................................................... 10
2.3.12. Pengujian White Box...................................................................... 10
2.3.13. Pengujian Gray Box....................................................................... 10
BAB III PERANCANGAN SISTEM ................................................................... 12
3.1. Gambaran Proses B isnis ....................................................................... 12
3.1.1. Proses Bisnis Saat Ini .................................................................... 12
3.1.2. Proses Bisnis yang Dikembangkan ............................................... 12
3.2. Perancangan Arsitektur dan Alur Sistem.............................................. 12
3.2.1. Arsitektur Sistem Inventori ........................................................... 12
3.2.2. Use Case Diagram ........................................................................ 14
3.2.3. Flowchart Proses Deduksi Stok .................................................... 15
3.2.4. Activity Diagram ........................................................................... 17
3.3. Kebutuhan Sistem ................................................................................. 21
3.3.1. Kebutuhan Fungsional ................................................................... 21
3.3.2. Kebutuhan Non-Fungsional........................................................... 21
3.4. Perancangan Basis Data........................................................................ 22
3.2. Entity Relationship Diagram ......................................................... 22
3.5. Deskripsi Entitas............................................................................ 23
3.6. Perencanaan Pengujian ......................................................................... 26
3.7. Lingkungan Pengembangan.................................................................. 29

v
BAB IV IMPLEMENTASI DAN PENGUJIAN .................................................. 31
4.1.1. Implementasi Algoritma Deduksi Stok ......................................... 31
4.1.2. Implementasi Penyesuaian Stok dan Perhitungan Stok................. 33
4.2. Implementasi Panel Admin................................................................... 34
4.2.1. Halaman Login .............................................................................. 34
4.2.2. Halaman Kategori Menu ............................................................... 35
4.2.3. Halaman Menu .............................................................................. 36
4.2.4. Halaman Bahan Baku .................................................................... 37
4.2.5. Halaman Batch Stok Bahan Baku ................................................. 39
4.2.6. Halaman Penyesuaian Stok ........................................................... 40
4.2.7. Halaman Riwayat Penggunaan Bahan Baku ................................. 43
4.3. Pengujian .............................................................................................. 44
4.3.1. Pengujian Black Box ...................................................................... 44
4.3.2. Pengujian White Box...................................................................... 47
BAB V PENUTUP................................................................................................ 57
5.1. Kesimpulan ........................................................................................... 57
5.2. Saran ..................................................................................................... 57
DAFTAR PUSTAKA ........................................................................................... 59




vi
## DAFTAR GAMBAR

Gambar 2.1 Metode Agile ....................................................................................... 7
Gambar 3.2 Use case diagram sistem manajemen inventori ................................ 14
Gambar 3.3 Flowchart proses deduksi stok .......................................................... 15
Gambar 3.4 Activity diagram tambah bahan baku ................................................ 17
Gambar 3.5 Activity diagram tambah batch stok .................................................. 18
Gambar 3.6 Activity diagram penyesuaian stok ................................................... 19
Gambar 3.7 Activity diagram buat menu baru ...................................................... 20
Gambar 3.8 ERD sistem inventori ........................................................................ 22
Gambar 4.1 Halaman login admin ........................................................................ 35
Gambar 4.2 Halaman daftar kategori .................................................................... 35
Gambar 4.3 Form buat daftar kategori baru .......................................................... 36
Gambar 4.4 Halaman daftar menu ........................................................................ 36
Gambar 4.5 Halaman form buat menu .................................................................. 37
Gambar 4.6 Halaman bahan baku ......................................................................... 38
Gambar 4.7 Form buat bahan baku baru ............................................................... 38
Gambar 4.8 Halaman batch stok ........................................................................... 39
Gambar 4.9 Admin menandai batch kedaluwarsa ................................................ 40
Gambar 4.10 Form buat batch stok baru............................................................... 40
Gambar 4.11 Halaman penyesuaian stok .............................................................. 41
Gambar 4.12 Detail penyesuaian stok ................................................................... 41
Gambar 4.13 Form buat penyesuaian stok baru .................................................... 42
Gambar 4.14 Admin membatalkan penyesuaian stok ........................................... 42
Gambar 4.15 Halaman riwayat stok ...................................................................... 43
Gambar 4.16 Halaman detail riwayat stok versi penyesuaian .............................. 43
Gambar 4.17 Halaman detail riwayat stok versi penjualanss................................ 44
Gambar 4.18 Pengujian deduksi stok berdasarkan resep menu ............................ 48
Gambar 4.19 Pengujian algoritma FIFO ............................................................... 48
Gambar 4.20 Pengujian algoritma FEFO .............................................................. 49
Gambar 4.21 Pengujian penyesuaian stok............................................................. 50

vii
Gambar 4.22 Pengujian pembatalan penyesuaian stok ......................................... 51
Gambar 4.23 Pengujian Deduksi Stok dan Pencatatan Riwayat ........................... 53
Gambar 4.24 Pengujian Order Gagal karena Stok Tidak Mencukupi................... 54
Gambar 4.25 Alur Pelanggan ke Konfirmasi Kasir .............................................. 55



viii
## DAFTAR TABEL

Tabel 3.1 Kebutuhan fungsional sistem ................................................................ 21
Tabel 3.2 Kebutuhan non-fungsional .................................................................... 21
Tabel 3.3 Struktur tabel ingredients ...................................................................... 23
Tabel 3.4 Struktur tabel ingredient_batches.......................................................... 23
Tabel 3.5 Struktur tabel menu_ingredients ........................................................... 24
Tabel 3.6 Struktur tabel stock_adjustments .......................................................... 24
Tabel 3.7 Struktur tabel stock_movements ........................................................... 25
Tabel 3.8 Rencana skenario pengujian black box ................................................. 26
Tabel 3. 9 Rencana skenario pengujian white box ................................................ 28
Tabel 3.10 Rencana skenario pengujian gray box................................................. 29
Tabel 3.11 Perangkat keras pengembangan .......................................................... 29
Tabel 3.12 Perangkat lunak pengembangan.......................................................... 29
Tabel 4.1 Pengujian black box autentikasi admin ................................................. 45
Tabel 4.2 Pengujian black box manajemen bahan baku........................................ 45
Tabel 4.3 Pengujian black box penyesuaian stok .................................................. 46
Tabel 4.4 Pengujian black box manajemen resep menu........................................ 46



ix
## ABSTRAK
Ma na jemen inventori merupa ka n a spek kritis da la m opera siona l ka fe ya ng mempenga ruhi
ketersedia a n ba han baku, kela ncaran produksi, da n kepua san pela ngga n. Pa da pra ktiknya, banyak
ka fe ma sih mela kukan pencatatan stok seca ra  manual mengguna kan nota kerta s a tau spreadsheet,
ya ng renta n terha da p kesa la han penca tatan, keterla mbatan informasi stok, serta  sulitnya  melacak
riwa ya t  pergera ka n stok.  Penelitia n  ini  bertujua n mera nca ng da n mengimplementa sika n sistem
ma na jemen inventori pa da Point of Sale W9 Cafe mengguna ka n La ravel da n Fila ment ya ng mampu
mengelola  stok ba ha n ba ku berba sis resep.
Sistem dikemba ngka n menggunakan kera ngka  kerja  La ravel 13 denga n pa nel a dministra si
Fila ment  ya ng  menyedia ka n a ntarmuka pengelola a n da ta  da n ma najemen batch seca ra  intuitif.
Sistem  mengimplementa sika n dua  mode deduksi batch ya itu  FEFO  (First-Expiry-First-Out)  dan
FIFO  (First-In-First-Out),   denga n  meka nisme  penguncia n  da ta  untuk  mencega h  konflik  pada
tra nsa ksi bersa ma an. Integra si denga n modul tra nsa ksi ka sir  dila kuka n seca ra  otoma tis di  mana
deduksi stok terja di seba ga i ba gia n da ri pemrosesa n pesa na n.
Pengujia n  dila kuka n denga n metode black box, white box,  da n gray  box  testing. Ha sil
pengujia n black box terha da p seluruh modul menunjukkan skenario berha sil. Pengujia n white box
memva lida si kebena ra n a lgoritma  FIFO da n FEFO.  Pengujia n gray box memva lida si konsistensi
a lira n da ta  a ntar modul. Seluruh meka nisme deduksi stok da n penca tatan pergera kan berja lan sesuai
pera nca nga n.
Ka ta  kunci: Sistem ma na jemen inventori, Point of Sale, La ra vel, Fila ment, FEFO, FIFO.


x
## ABSTRACT
Inventory management  is  a  critical aspect of  cafe operations that affects raw  material
availability, production continuity, and customer satisfaction. In practice, many cafes still record
stock manually using paper notes or spreadsheets, which are prone to recording errors,  delayed
stock information, and difficulty in tracking stock movement history. This study aims to design and
implement  an  inventory management  system  for  the  W9  Cafe  Point of  Sale  using Laravel  and
Filament that can manage recipe-based raw material stock.
The   system   was   developed  using   the   Laravel   13   framework   with   the   Filament
administration panel, which provides intuitive data management interfaces and batch management.
The  system  implements  two  batch  deduction  modes:  FEFO  (First-Expiry-First-Out)  and  FIFO
(First-In-First-Out),  with data locking mechanisms to prevent conflicts in concurrent transactions.
Integration with the cashier transaction module is achieved automatically where stock deduction
occurs as part of order processing.
Testing was conducted using black box, white box, and gray box testing methods.  Black
box testing across  all modules showed scenarios passed.  White box testing validated FIFO  and
FEFO algorithms. Gray box testing validated data consistency across modules. All stock deduction
mechanisms and movement recording functioned as designed.
Keywords: Inventory management system, Point of Sale, Laravel, Filament, FEFO, FIFO.


## 1

## BAB I
## PENDAHULUAN

## 1.1. Latar Belakang
Manajemen  inventori  atau pengelolaan  stok bahan baku merupakan  proses
inti  dalam  operasional  kafe yang  menentukan  kelancaran  produksi dan kepuasan
pelanggan.  Namun  pada  praktiknya,  banyak  kafe  masih  mencatat  stok  secara
manual  menggunakan  buku  atau spreadsheet, yang  rentan  terhadap  kesalahan
pencatatan,   keterlambatan   informasi    stok,   serta   sulitnya    melacak    riwayat
pergerakan stok secara sistematis [1].
Salah satu contohnya adalah W9 Cafe di Semarang yang dikelola oleh STIE
Totalwin. Dalam operasional  sehari-hari,  W9 Cafe masih menggunakan buku stok
dan spreadsheet sehingga  sering  terjadi  ketidaksesuaian  antara  catatan  dengan
kondisi aktual, serta sulitnya melacak riwayat pemakaian bahan baku secara sistematis.
Kondisi ini mendorong perlunya pengembangan sistem manajemen inventori yang
terintegrasi   dengan  sistem   POS  yang  akan  digunakan  oleh  W9 Cafe untuk
mengatasi masalah operasional  ini.
Sistem POS yang  dikembangkan mencakup sistem  manajemen  inventori
yang  mencakup fitur  pengelolaan  data  menu  dan  bahan  baku,  pengaturan  stok
bahan baku dengan mode deduksi FEFO (First-Expiry-First-Out) dan FIFO (First-
In-First-Out),  penyesuaian stok manual, serta pencatatan riwayat pemakaian bahan
baku.  Sistem   dibangun  menggunakan   kerangka  kerja   Laravel   dengan  panel
administrasi Filament untuk memudahkan pengelolaan data inventori. Sistem ini diuji menggunakan tiga metode pengujian, yaitu black box testing untuk memvalidasi fungsionalitas fitur dari sisi pengguna, white box testing untuk memverifikasi kebenaran algoritma deduksi stok, dan gray box testing untuk memastikan konsistensi aliran data antar modul inventori dan modul transaksi.

## 1.2. Rumusan Masalah
Berdasarkan latar belakang, dirumuskan permasalahan  sebagai berikut:
- Bagaimana  merancang  dan membangun  sistem  manajemen  inventori  pada
POS W9 Cafe menggunakan Laravel dan Filament?
- Bagaimana  sistem  dapat menggunakan stok  berdasarkan prioritas masa
kedaluwarsa (FEFO) dan urutan penerimaan  (FIFO) untuk meminimalkan
pemborosan bahan baku?
- Bagaimana  sistem  dapat mencatat pemakaian  bahan baku  secara  otomatis
setiap terjadi transaksi penjualan?
- Bagaimana hasil pengujian sistem dapat memverifikasi bahwa seluruh fitur
sistem manajemen inventori telah berfungsi sesuai dengan yang diharapkan?

## 1.3. Batasan Masalah
Agar penulisan  Tugas  Akhir  ini berfokus  pada  permasalahan  utama  dan
menghindari pembahasan yang meluas ke topik lain, pembahasan dibatasi pada hal-
hal berikut:
- Sistem berfokus pada manajemen inventori bahan baku dan fitur pendukung
(autentikasi, dashboard, kategori,  menu).  Penelitian  tidak mencakup fitur
POS di luar konteks inventori.
- Mode deduksi batch stok terbatas pada FEFO dan FIFO.
- Sistem memerlukan  koneksi internet untuk diakses.
- Pengujian terbatas pada black box, white box, dan gray box yang mencakup
verifikasi  algoritma  deduksi, konsistensi  data antar  modul,  serta  validasi
fungsionalitas sistem.

## 1.4. Tujuan Penelitian
Tujuan yang ingin dicapai dari penelitian ini adalah sebagai berikut:
- Merancang dan membangun sistem manajemen inventori pada Point of Sale
W9 Cafe yang  mencakup  pengelolaan  menu,  bahan  baku, batch stok bahan
baku, penyesuaian stok, dan riwayat penggunaan stok.
- Menerapkan  algoritma  deduksi batch FEFO dan  FIFO pada pengelolaan
stok batch bahan baku untuk meminimalkan  pemborosan.
- Menyediakan    pencatatan    pemakaian    bahan    baku    secara    otomatis,
penyesuaian stok manual, serta riwayat perubahan stok yang dapat dilacak.
- Melakukan pengujian  sistem menggunakan black box, white box, dan gray
box  testing untuk memvalidasi  fungsionalitas  fitur,  kebenaran  algoritma,
serta konsistensi aliran  data antar modul.

## 3


## 1.5. Manfaat Penelitian
Penelitian ini bermanfaat bagi beberapa pihak yang dapat dijelaskan sebagai
berikut:
- Manfaat bagi Penulis:  Mendapatkan pengalaman  dalam merancang  sistem
manajemen  inventori yang terstruktur menggunakan Laravel dan Filament.
- Manfaat  bagi  Kafe:  Mendapatkan  sistem  manajemen  inventori  dengan
deduksi stok otomatis dan riwayat pergerakan stok yang dapat dilacak.
- Manfaat bagi  Peneliti  Selanjutnya:  Menjadi  referensi  implementasi  sistem
manajemen  inventori dengan deduksi stok otomatis.

## 1.6. Metodologi Penelitian
Penelitian ini menggunakan tahapan sebagai berikut:
- Requirements:  Identifikasi  kebutuhan  melalui  observasi  di  W9 Cafe dan
studi literatur.
- Design: Merancang basis  data, arsitektur  aplikasi,  algoritma  deduksi, dan
antarmuka panel Filament.
- Development: Implementasi  secara  bertahap  dalam  dua  iterasi,  yaitu  inti
inventori dan panel administrasi.
- Testing:  Pengujian black  box, white  box, dan gray  box untuk  memvalidasi
fungsionalitas  fitur,  kebenaran  algoritma, serta konsistensi  aliran  data
antar modul inventori dan transaksi.
- Review:  Evaluasi  hasil  pengujian  dan  penyusunan  dokumentasi sistem
sebagai bagian dari laporan tugas akhir.
- Documentation: Menyusun laporan    Tugas    Akhir    sebagai    bentuk
dokumentasi  dari  seluruh  kegiatan  yang  meliputi  konsep,  landasan  teori,
proses implementasi,  dan hasil yang diperoleh.

## 1.7. Sistematika  Penulisan
Tugas akhir ini terdiri atas lima bab dengan susunan sebagai berikut.
## BAB I PENDAHULUAN

## 4

Berisi latar belakang, rumusan masalah, batasan masalah, tujuan penelitian,
manfaat penelitian, metodologi penelitian, dan sistematika penulisan.
## BAB II KAJIAN PUSTAKA
Membahas  penelitian  terdahulu,  metode penelitian  yang  digunakan,  serta
landasan teori yang meliputi konsep sistem manajemen  inventori, algoritma FEFO
dan FIFO, Laravel, Filament, dan metode pengujian.
## BAB III PERANCANGAN SISTEM
Berisi    gambaran   umum   sistem,   lingkungan    pengembangan,   analisis
kebutuhan  fungsional  dan  non-fungsional,  perancangan  proses  dan  alur  sistem,
perancangan   basis   data,  perancangan   arsitektur   aplikasi,   serta   perancangan
antarmuka.
## BAB IV IMPLEMENTASI DAN PENGUJIAN
Menyajikan implementasi panel administrasi serta hasil pengujian black box, white box, dan gray box.
## BAB V PENUTUP
Bab ini  berisi  kesimpulan  dari perancangan,  implementasi,  dan pengujian
yang  telah dilakukan,  serta saran  pengembangan  dan penelitian  lebih  lanjut  pada
masa mendatang.


## 5
## BAB II
## KAJIAN PUSTAKA

## 2.1. Penelitian Terdahulu
Kajian   penelitian   ini   membahas    berbagai   penelitian   terdahulu   yang
memiliki  keterkaitan dengan topik pengembangan sistem manajemen  inventori dan
POS berbasis  web. Penelitian-penelitian  tersebut menjadi  referensi penting dalam
memahami  konsep  sistem  informasi  manajemen  stok,  algoritma  deduksi batch,
serta penerapan teknologi pendukung seperti Laravel dan Filament. Kajian ini juga
memperkuat landasan teoritis dan memberikan gambaran tentang efektivitas sistem
digital dalam  meningkatkan  akurasi  dan efisiensi  operasional  di bidang inventori
serta transaksi penjualan.
Penelitian  oleh  Supron  dan A. Susila  berjudul  "Aplikasi Point  of  Sales
(POS)  Berbasis Website Dengan  Menggunakan  Laravel  (Studi  Kasus:  Bakmi
Djowo)"  mengembangkan  sistem  POS  berbasis  web  yang  mencakup  transaksi
penjualan  dan  pengelolaan  stok  menu.  Persamaan  dengan  penelitian  ini  adalah
penggunaan  Laravel  sebagai  kerangka  kerja  utama  dan  fokus  pada pengelolaan
stok, sedangkan perbedaannya terletak pada konteks: penelitian Supron dan Susila
pada  restoran   Bakmi   Djowo   tanpa   dukungan   manajemen batch, sementara
penelitian ini pada kafe dengan implementasi mode deduksi FEFO dan FIFO [2].
Selanjutnya, penelitian  oleh Y. Darmayunata, Y. Yuhelmi,  dan M. Devega
dalam  "Pembangunan  Sistem  Inventori Apotek Menggunakan  Metode FIFO dan
FEFO" mengimplementasikan  kedua algoritma deduksi batch pada sistem inventori
apotek.  Persamaan  dengan penelitian  ini  adalah  fokus  pada algoritma  FIFO dan
FEFO, sedangkan perbedaannya  adalah penelitian  Devega,  dkk. diterapkan pada
lingkungan  apotek dengan pengelolaan obat, sementara penelitian  ini menerapkan
kedua algoritma  pada sistem  inventori  bahan  baku kafe yang  terintegrasi  dengan
## POS [3].
Penelitian  oleh  R.  A. Farisi,  A. R. Zayn, B. A. Nugroho,  dan A. Heriadi,
dalam  "Implementasi   Sistem  Informasi  Akademik  Pengelolaan   Tugas   Akhir
Berbasis  Laravel  dan Filament"  membahas  implementasi  Filament  sebagai  panel

## 6

administrasi Laravel. Persamaan dengan penelitian ini adalah penggunaan Filament
untuk antarmuka administrasi, sedangkan perbedaannya adalah penelitian Al Farisi,
dkk.   berfokus   pada   sistem    informasi    akademik,   sementara    penelitian   ini
menerapkannya secara spesifik untuk manajemen inventori kafe [4].
Terakhir,  Garbarz  dan Plechawska-Wójcik  melakukan  analisis  komparatif
kerangka kerja PHP Laravel dan Symfony. Persamaan dengan penelitian ini adalah
pemilihan  Laravel sebagai  kerangka kerja utama, sedangkan perbedaannya adalah
penelitian tersebut bersifat komparatif umum tanpa studi kasus spesifik [5].
1Ta bel 1.1 Ka jia n penelitia n terda hulu
## No Peneliti,  Tahun Tujuan Hasil
1 Supron  dan  A.  Susila
## (2023)
Sistem   POS   berbasis
web
Transaksi dan    stok,
tanpa batch
management
2 M. Devega, dkk.
## (2024)
FIFO dan FEFO
inventory
FIFO dan FEFO untuk
manajemen  stok
3 R.    Al    Farisi,     dkk.
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
Sistem  yang  dikembangkan  memiliki   keunggulan  dibanding  penelitian
terdahulu berupa deduksi stok otomatis berbasis resep bahan baku dengan modulasi
perubahan,  mode deduksi FEFO dan FIFO yang  dapat dikonfigurasi,  pencatatan
riwayat pergerakan stok yang tidak dapat diubah, mekanisme pengamanan transaksi
untuk mencegah  konflik data, serta panel admin Filament  untuk pengelolaan  data
inventori.

## 2.2. Metode Penelitian
Metode penelitian  menggunakan Agile yang  menekankan  kolaborasi  erat
antara  pengembang  dan  pengguna,  serta  pengembangan  sistem  yang  dilakukan

## 7

secara  iteratif,  bertahap,  dan  fleksibel  agar  dapat  menyesuaikan   diri  dengan
perubahan kebutuhan selama proses berlangsung.

Ga mba r 2.1 Metode Agile
Berikut  adalah  tahapan  dalam  pengembangan  perangkat  lunak  dengan
menggunakan metode Agile:
- Requirements (Pengumpulan Kebutuhan): Tim  bekerja sama dengan admin
kafe untuk mengidentifikasi dan memprioritaskan kebutuhan sistem dalam
catatan yang jelas dan terukur.
- Design (Perancangan): Tim  membuat  rancangan  solusi  sederhana  namun
efektif untuk fitur-fitur yang dipilih di iterasi ini,  seperti sketsa antarmuka,
alur sistem, atau arsitektur teknis awal.
- Development (Pengembangan): Developer mulai menulis kode,
membangun fitur secara nyata, dan melakukan pengujian (unit testing).
- Testing (Pengujian): Tim  memverifikasi  bahwa  fitur  yang  sudah  dibuat
berfungsi dengan baik melalui beberapa  jenis  pengujian  — black  box untuk
validasi fungsionalitas  fitur, white  box untuk  verifikasi  logika  internal,
serta gray  box untuk  memastikan  integrasi  antar  modul  inventori  dan
transaksi.
- Deployment (Penyebaran): Hasil   kerja   yang   sudah   lolos    pengujian
dikeluarkan ke lingkungan staging atau produksi.
- Review (Tinjauan): Mencakup Sprint  Review untuk  mendemonstrasikan
hasil ke admin kafe.
Setelah tahap review selesai,  siklus  kembali  ke tahap Requirements untuk
memulai  iterasi  baru  dengan memasukkan  umpan  balik,  perubahan prioritas,  atau
kebutuhan tambahan. Pendekatan siklus  berulang ini  memungkinkan  proyek terus
disempurnakan secara adaptif [6].


## 8

## 2.3. Landasan Teori
## 2.3.1. Sistem Manajemen Inventori
Sistem  manajemen  inventori  adalah  serangkaian  proses  yang  digunakan
untuk mengelola,  memantau,  dan mengendalikan  persediaan  barang  dalam  suatu
organisasi.  Tujuan  utamanya  adalah  memastikan  ketersediaan  stok  yang  cukup
untuk  memenuhi   permintaan   tanpa  menimbulkan   kelebihan   stok  yang  dapat
meningkatkan  biaya  penyimpanan  [7].  Dalam penelitian  ini,  inventori  mencakup
bahan  baku  dan barang  jadi  yang  memerlukan  pengelolaan  khusus  terkait  masa
kedaluwarsa.

2.3.2. PostgreSQL
PostgreSQL  adalah  sistem  manajemen  basis  data  relasional  objek  open
source dengan reputasi  stabil  dan kaya  fitur.  PostgreSQL mendukung row-level
locking,, transaction  isolation  levels,  dan foreign  key constraints yang diperlukan
untuk menjaga integritas data pada aplikasi berbasis web [8].

2.3.3. Algoritma Deduksi Batch (FEFO dan FIFO)
Dalam manajemen inventori, batch stok adalah sejumlah bahan baku yang diterima pada waktu yang sama dan memiliki karakteristik identik — seperti tanggal kedaluwarsa, harga beli, dan nomor lot. Pencatatan stok secara batch memungkinkan sistem melacak bahan baku berdasarkan kapan diterima dan kapan kedaluwarsa, sehingga pengambilan keputusan tentang batch mana yang harus dipakai lebih dahulu dapat dilakukan secara otomatis.

Algoritma deduksi batch menentukan urutan penggunaan stok berdasarkan karakteristik masing-masing batch. FEFO (First-Expiry-First-Out) memprioritaskan batch dengan tanggal kedaluwarsa terdekat, sangat penting untuk bahan baku dengan masa simpan terbatas seperti susu, sayuran, dan produk dairy [9]. Sebaliknya, FIFO (First-In-First-Out) memprioritaskan batch yang diterima paling awal, cocok untuk bahan baku yang tidak memiliki tanggal kedaluwarsa seperti gula, kopi bubuk, dan kemasan. Penerapan kedua algoritma ini secara terpisah per bahan baku memungkinkan kafe meminimalkan pemborosan akibat bahan kedaluwarsa sekaligus menjaga rotasi stok yang sehat.

## 2.3.4. Point of Sale (POS)
Point of Sale (POS) merupakan sistem yang digunakan untuk memproses transaksi penjualan dan mengelola data penjualan, produk, serta stok secara terintegrasi [10]. POS terdiri dari perangkat keras (terminal, printer struk, barcode scanner) dan perangkat lunak (aplikasi kasir, database, laporan). Dalam konteks kafe dan restoran, POS tidak hanya berfungsi mencatat pesanan tetapi juga mengelola inventori bahan baku, resep menu, dan data pelanggan. Sistem POS yang umum digunakan di Indonesia antara lain Moka POS, Pawoon, IPOS, dan Loyverse. Sistem POS yang baik harus mampu mencatat transaksi secara real-time, menyediakan laporan penjualan, serta terintegrasi dengan manajemen stok untuk menjaga ketersediaan bahan baku.

## 2.3.5. PHP
PHP  (Hypertext  Preprocessor)  adalah  bahasa  pemrograman  skrip  yang
banyak   digunakan   untuk  pengembangan   aplikasi   web   dinamis.   PHP   dapat
disisipkan  langsung  ke  dalam  HTML  dan  memiliki   dukungan  luas  terhadap

## 9

berbagai  basis data. Laravel  sebagai framework PHP yang digunakan dalam tugas
akhir  ini  memanfaatkan  kemampuan  PHP untuk membangun  aplikasi  web yang
terstruktur [5].

## 2.3.6. Laravel
Laravel  adalah framework aplikasi  web berbasis  PHP yang  bersifat open
source dan menyediakan  berbagai  fitur seperti  sistem routing,  manajemen  basis
data  melalui migration, dan  sistem   autentikasi   bawaan   yang   memudahkan
pengembangan aplikasi  web modern [11].

## 2.3.7. Filament
Filament adalah framework antarmuka pengguna open-source yang dibangun di atas Laravel dan menyediakan komponen siap pakai seperti tabel data, formulir, filter, dan panel administrasi yang terintegrasi dengan basis data [12]. Filament menggunakan konsep "Resource" yang memungkinkan pengembang mendefinisikan CRUD (Create, Read, Update, Delete) secara deklaratif — pengembang cukup mendefinisikan skema tabel dan formulir, maka Filament secara otomatis menghasilkan halaman daftar, halaman buat, halaman edit, serta tombol aksi. Filament mendukung pemisahan kode melalui pola separated resource, di mana definisi tabel, formulir, dan aksi ditempatkan di direktori terpisah untuk menjaga maintainability. Dengan pendekatan ini, pembuatan panel administrasi yang kompleks dapat dilakukan dengan cepat tanpa menulis kode JavaScript atau HTML secara manual.

## 2.3.8. Entity Relationship  Diagram
Entity  Relationship  Diagram (ERD)  merupakan  model  pemodelan  data
yang  disusun berdasarkan  objek-objek  yang ada di dunia  nyata. ERD digunakan
untuk menggambarkan  hubungan  antar data dalam  sebuah basis  data secara  logis
sehingga  mudah  dipahami  oleh  pengembang  dan  pengguna.  ERD  terdiri dari
entitas, atribut (kolom-kolom  dalam tabel), dan relasi  antar entitas (seperti one-to-
many, many-to-many) [13].   Pada pengembangan sistem   inventori ini,   ERD
digunakan  untuk  memodelkan  hubungan  antara  bahan  baku  dengan batch stok,
menu dengan resep, serta keterkaitan antara stok dengan pergerakannya.

## 2.3.9. Activity  Diagram
Activity  diagram merupakan  salah  satu  diagram  dalam Unified  Modeling
Language (UML) yang digunakan untuk menggambarkan alur aktivitas atau proses
dalam   suatu   sistem.   Diagram   ini   menampilkan   urutan  kegiatan   dari   awal,

## 10

keputusan-keputusan  yang  terjadi, hingga  akhir   melalui   aliran   kontrol  antar
aktivitas [14].

## 2.3.10. Flowchart
Flowchart atau     diagram     alir merupakan jenis     diagram     yang
merepresentasikan  algoritma, alur kerja, atau proses dengan menampilkan langkah-
langkah  dalam  bentuk  simbol-simbol   grafis  yang  dihubungkan  dengan  panah.
Flowchart digunakan  untuk  menganalisis,  mendesain,  dan  mendokumentasikan
sebuah proses atau program secara sistematis [15].

## 2.3.11. Pengujian Black Box
Pengujian black box merupakan teknik pengujian perangkat lunak yang berfokus pada spesifikasi fungsional tanpa memerlukan pengetahuan tentang struktur internal kode program. Pengujian ini dilakukan dengan mendefinisikan kondisi masukan dan memverifikasi keluaran yang dihasilkan, sehingga lebih menitikberatkan pada kesesuaian fungsi sistem dengan kebutuhan pengguna [16]. Teknik yang digunakan dalam pengujian black box pada penelitian ini adalah equivalence partitioning, di mana input data dikelompokkan ke dalam kelas-kelas yang setara — misalnya input valid dan input tidak valid — dan setiap kelas diuji dengan satu perwakilan. Tools: pengujian dilakukan secara manual melalui antarmuka panel Filament.

## 2.3.12. Pengujian White Box
Pengujian white box merupakan teknik pengujian perangkat lunak yang berfokus pada struktur internal dan logika kode program. Pengujian ini dirancang dari perspektif pengembang dengan menguji seluruh bagian kode yang dapat diuji, bertujuan untuk menemukan kesalahan logis pada source code dan memastikan bahwa setiap fitur berfungsi sesuai dengan yang diharapkan [17]. Dalam penelitian ini, white box testing menerapkan statement coverage — setiap baris kode yang mengandung logika bisnis harus dieksekusi minimal satu kali — dan branch coverage — setiap cabang keputusan (if/else, switch) harus diuji dengan kedua kondisinya. Tools: PHPUnit (framework pengujian PHP), dijalankan melalui command `php artisan test`.

## 2.3.13. Pengujian Gray Box
Pengujian gray box merupakan metode pengujian perangkat lunak yang menggabungkan aspek black box dan white box dengan pengetahuan parsial terhadap struktur internal sistem. Pengujian ini dilakukan dengan memanfaatkan pengetahuan terbatas seperti skema database atau arsitektur sistem untuk merancang kasus uji yang lebih terarah, namun pengujian tetap dilakukan melalui antarmuka eksternal seperti request HTTP, sehingga lebih menitikberatkan pada verifikasi integrasi antar modul atau subsistem tanpa harus memanggil kode sumber secara langsung [18]. Dalam penelitian ini, gray box testing digunakan untuk memverifikasi bahwa aliran data antara modul inventori dan modul transaksi berjalan konsisten. Tools: PHPUnit Feature Tests (`tests/Feature/`), Laravel HTTP Test Helpers (`actingAs`, `post`, `assertDatabaseHas`).

Tabel 2.1 Perbandingan Metode Pengujian

| Aspek | Black Box | White Box | Gray Box |
|-------|-----------|-----------|----------|
| Pengetahuan sistem | Tidak ada | Penuh (source code) | Sebagian (skema DB, arsitektur) |
| Fokus pengujian | Fungsionalitas fitur | Logika internal & algoritma | Integrasi antar modul |
| Contoh kasus uji | Login, CRUD data | FEFO/FIFO deduction | Order → stock movement |
| Tools | Manual UI (Filament) | PHPUnit (Unit Test) | PHPUnit (Feature Test) |
| Akses ke kode | Tidak | Ya | Tidak langsung |
| Sumber kebenaran | Kebutuhan pengguna | Algoritma | Konsistensi data |


## 12

## BAB III
## PERANCANGAN SISTEM

## 3.1. Gambaran Proses Bisnis
## 3.1.1. Proses Bisnis Saat Ini
Pada  sistem  yang  berjalan  saat  ini,  pencatatan  inventori  di  kafe  masih
dilakukan  secara  manual.  Admin mencatat penerimaan  bahan  baku di buku  stok,
kasir  menulis  pesanan  pada  nota  kertas,  dan penyesuaian  stok  (stock opname)
dilakukan   secara   periodik.   Hal   ini menyebabkan   kesalahan   pencatatan  dan
keterlambatan informasi stok.

3.1.2. Proses Bisnis yang Dikembangkan
Sistem yang dikembangkan mengotomatiskan pencatatan dan deduksi stok.
Admin mengelola  data bahan baku dan resep  melalui  panel Filament. Ketika kasir
memproses  pesanan,  sistem  secara  otomatis  mendeduksi stok  berdasarkan  resep
menu,  lalu  hasilnya  akan  dicatat  dan  ditampilkan  sebagai  riwayat  penggunaan
bahan baku.

3.2. Perancangan Arsitektur dan Alur Sistem
## 3.2.1. Arsitektur Sistem Inventori
Arsitektur  detail  sistem  inventori  menggambarkan  komponen-komponen
yang   membentuk   subsistem   inventori   serta   hubungannya   dengan  subsistem
transaksi.  Sistem  inventori  terdiri dari antarmuka  Filament  ditambah tiga lapisan
inti, yaitu Lapisan Service, Lapisan Model, dan Database.

## 13


Admin mengakses Panel Admin melalui web browser untuk mengelola data
inventori  seperti  bahan  baku, batch stok,  resep  menu,  penyesuaian  stok,  serta
melihat riwayat penggunaan stok bahan baku.
Lapisan Service (Business  Logic)  merupakan  inti  dari  sistem  inventori.
Bagian InventoryService mengimplementasikan  seluruh  logika  deduksi batch
dengan  algoritma  FEFO/FIFO,  termasuk  pencatatan  pergerakan  stok  ke  dalam
StockMovement.   Bagian StockAdjustmentService yang   menangani   logika
penyesuaian  stok  manual  serta  mekanisme  pembatalan  penyesuaian  (reversal).
## Bagian.
Lapisan  Model  (Data  Access)  terdiri  dari model  Eloquent  yang  mewakili
entitas  inventori. Category (pengelompokan  menu), Menu (beserta  resep  bahan
baku    melalui MenuIngredient), Ingredient (master    data   bahan    baku),
IngredientBatch  (stok   per batch dengan  informasi   kadaluwarsa  dan  harga),
StockMovement (catatan immutable setiap perubahan stok), dan StockAdjustment
(penyesuaian stok manual). Seluruh model ini menggunakan Eloquent ORM untuk

## 14

membaca  dan menulis  data ke database. Lalu Database PostgreSQL menyimpan
seluruh data inventori.

## 3.2.2. Use Case Diagram

Gambar 3.1 Use case diagram sistem manajemen inventori

Use case diagram pada Gambar 3.1 menggambarkan interaksi yang bisa dilakukan admin terhadap sistem. Admin bertanggung jawab mengelola data inventori yang mencakup login, kelola bahan baku, kelola batch stok, kelola resep menu, penyesuaian stok, dan kelola kategori, sedangkan sistem secara otomatis menjalankan deduksi stok ketika pesanan diproses.

Tabel 3.2 Skenario Use Case

| Use Case | Aktor | Deskripsi | Kondisi Awal | Alur Normal | Alur Alternatif/Error |
|----------|-------|-----------|-------------|-------------|----------------------|
| Login | Admin | Admin masuk ke sistem | Admin belum login | 1. Admin mengisi email & password. 2. Sistem validasi. 3. Sistem redirect ke dashboard. | Jika email/password salah, tampilkan pesan error dan kembali ke form login. |
| Kelola Bahan Baku | Admin | Mengelola data bahan baku (input, ubah, nonaktifkan) | Admin sudah login | 1. Admin akses halaman bahan baku. 2. Sistem menampilkan daftar bahan baku. 3. Admin pilih tambah/ubah/nonaktifkan. | Jika input tidak valid, sistem tolak dan tampilkan pesan error. |
| Kelola Batch Stok | Admin | Mengelola batch stok bahan baku | Admin sudah login, bahan baku tersedia | 1. Admin akses halaman batch. 2. Admin input batch baru (jumlah, tanggal expired). 3. Sistem simpan. | Jika batch dengan data sama sudah ada, sistem tolak. |
| Penyesuaian Stok | Admin | Melakukan penyesuaian stok (tambah/kurang/batal) | Admin sudah login | 1. Admin akses halaman penyesuaian. 2. Admin pilih bahan, tipe, jumlah. 3. Sistem simpan dan update stok. | Jika pengurangan melebihi stok, sistem tolak. |
| Deduksi Stok (Otomatis) | Sistem | Mengurangi stok bahan baku saat pesanan diproses | Ada pesanan masuk | 1. Sistem ambil resep menu. 2. Sistem pilih batch (FEFO/FIFO). 3. Sistem kurangi stok. | Jika stok tidak cukup, transaksi di-rollback dan pesanan ditolak. |
## 3.2.3. Flowchart Proses Deduksi Stok

Ga mba r 3.2 Flowchart proses deduksi stok
Flowchart pada  Gambar  3.3 menggambarkan  alur  deduksi  stok  ketika
pesanan diproses. Sistem terlebih dahulu memuat seluruh  data menu beserta resep

## 16

bahan  bakunya. Untuk setiap  menu  pesanan,  sistem  memilih  satu per  satu bahan
baku penyusunnya dan menghitung kebutuhan stok berdasarkan jumlah bahan baku
yang  dipakai  menu  tersebut (quantity_used) dikalikan jumlah  pesanan  menu
tersebut.
Selanjutnya,  sistem  menentukan  urutan  pengambilan batch berdasarkan
mode yang telah dikonfigurasi.  Mode FIFO mengurutkan batch berdasarkan data
received_at secara ASC (ascending, dari nilai terkecil ke terbesar) sehingga batch
yang  diterima  lebih  awal  didahulukan. Jika terdapat  beberapa batch dengan
received_at yang  sama,  urutan  ditentukan oleh  expiry_date ASC dan id ASC
secara  berurutan sebagai tiebreaker.  Mode FEFO mengurutkan batch berdasarkan
kolom expiry_date ASC  sehingga batch yang  kedaluwarsanya  paling  dekat
digunakan terlebih dahulu, dengan tiebreaker received_at ASC dan id ASC.
Stok  dikurangi  dari  setiap batch secara  berurutan  menggunakan  fungsi
min(quantity  batch,  sisa  kebutuhan), yaitu mengambil  nilai  terkecil  antara jumlah
stok yang tersedia di batch dengan jumlah stok yang masih diperlukan. Misalnya,
jika batch memiliki  stok 100 gram dan sisa  kebutuhan 60 gram,  maka batch akan
dikurangi 60 gram dan proses selesai.  Sebaliknya,  jika batch hanya memiliki  stok
60 gram dan sisa kebutuhan 100 gram, maka batch akan dikurangi 60 gram hingga
habis,  dan sisanya  diambil  dari batch berikutnya.  Fungsi  ini  memastikan  bahwa
pengambilan stok tidak melebihi  jumlah yang tersedia di batch, sehingga mencegah
stok dalam suatu batch menjadi negatif.
Setiap  perubahan  stok  kemudian  dicatat sebagai  pergerakan  stok. Proses
pengurangan batch diulang  hingga  kebutuhan  bahan  baku  terpenuhi,  kemudian
dilanjutkan  ke  bahan  baku  berikutnya  dalam  resep  yang  sama jika  satu  menu
memiliki  resep lebih dari satu bahan baku. Setelah seluruh bahan baku dalam suatu
menu selesai  diproses, sistem beralih  ke menu pesanan berikutnya dan mengulang
seluruh  proses  yang  telah  dijelaskan  sebelumnya.  Proses  berakhir  ketika  seluruh
item dalam pesanan telah diproses.


## 17

## 3.2.4. Activity Diagram
Activity   diagram merupakan   salah   satu  jenis   diagram  dalam Unified
Modeling  Language (UML) yang digunakan untuk menggambarkan  alur aktivitas
atau proses dalam suatu sistem. Diagram ini menampilkan urutan kegiatan dari awal
hingga akhir melalui  aliran  kontrol antar aktivitas [14].
## 2

Ga mba r 3.3 Activity diagram ta mba h ba ha n ba ku
Gambar    3.3 memperlihatkan activity    diagram ketika   admin   ingin
menambah  bahan  baku  baru  ke  dalam  sistem.  Pertama-tama,  admin  membuka
halaman daftar bahan baku dan menekan tombol "Buat Bahan Baku". Sistem akan
menampilkan  formulir yang berisi input nama bahan baku, pilihan unit satuan, dan
mode batch (FEFO atau FIFO). Setelah admin mengisi  data dan menekan  tombol
"Buat", sistem memvalidasi input dan menyimpan  data ke dalam tabel ingredients
di database. Database mengembalikan  respons  sukses,  dan sistem  menampilkan
pesan bahwa data bahan baku berhasil ditambahkan.

## 18


Ga mba r 3.4 Activity diagram ta mba h batch stok
Gambar    3.4 memperlihatkan activity    diagram ketika    admin   ingin
menambah batch stok untuk suatu  bahan baku.  Admin  memilih  bahan  baku  dari
daftar, lalu  menekan  tombol  "Batch Stok" dan  kemudian  "Buat Batch". Sistem
menampilkan  formulir batch yang  berisi input jumlah  stok,  tanggal kedaluwarsa
(untuk mode FEFO) atau tanggal diterima (untuk mode FIFO), dan harga per unit.
Setelah  admin  mengisi  data dan menekan  "Buat",  sistem  memvalidasi input dan
menyimpan data ke tabel ingredient_batches dengan relasi ke ingredient_id.
Database mengembalikan  respons  sukses,  dan sistem  menampilkan pesan bahwa
batch baru berhasil  ditambahkan.

## 19


Ga mba r 3.5 Activity dia gra m penyesua ia n stok
Gambar  3.5 memperlihatkan activity  diagram ketika  admin  melakukan
penyesuaian stok manual. Admin membuka halaman penyesuaian stok dan memilih
bahan   baku   yang   akan   disesuaikan,   kemudian   memilih    tipe   penyesuaian
(penambahan atau pengurangan) serta mengisi jumlah dan alasan. Setelah menekan
"Buat", sistem memvalidasi bahwa jumlah lebih besar dari nol, kemudian mencatat
penyesuaian    ke   tabel stock_adjustments dan   pergerakan   stok   ke   tabel
stock_movements,  serta  memperbarui  kuantitas  pada batch terkait  di database.
Database melakukan commit untuk   menjaga   konsistensi   data,   dan  sistem
menampilkan pesan bahwa penyesuaian stok berhasil.

## 20


Ga mba r 3.6 Activity diagram bua t menu ba ru
Gambar 3.6 memperlihatkan activity  diagram ketika admin ingin membuat
menu baru beserta resep (komposisi  bahan baku). Admin membuka halaman daftar
menu  dan menekan  tombol  "Buat  Menu",  kemudian  mengisi  data menu  berupa
nama,  kategori, foto menu, harga, dan diskon. Selanjutnya admin  memilih  bahan
baku  dari repeater dan mengisi  jumlah  pemakaian  per  unit  menu,  lalu  menekan
"Buat". Setiap menu  wajib  punya  minimal  satu bahan  baku. Sistem  memvalidasi
data menu. Jika data tidak valid, admin akan melihat pesan error dan memperbaiki
input. Jika  valid, sistem menyimpan  data menu ke tabel menus  dan relasi  resep ke
tabel menu_ingredients. Database mengembalikan  respons  sukses,  dan sistem
menampilkan pesan bahwa menu beserta resep berhasil  disimpan.


## 21

## 3.3. Kebutuhan Sistem
## 3.3.1. Kebutuhan Fungsional
Kebutuhan fungsional  sistem  manajemen  inventori  diidentifikasi dari use
case  diagram dan diberi  kode unik  berawalan  INV, sebagaimana  disajikan  pada
## Tabel 3.1.
Ta bel 3.1 Kebutuha n fungsiona l sistem
## No Kode Deskripsi Aktor Prioritas
1 INV-F01 Admin dapat mengelola data bahan baku Admin Tinggi
2 INV-F02 Admin   dapat   mengelola batch stok
bahan baku
## Admin Tinggi
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
7 INV-F09 Admin dapat login dan logout Admin Tinggi
8 INV-F11 Admin dapat mengelola data kategori Admin Sedang
9 INV-F12 Admin dapat mengelola data menu Admin Sedang

3.3.2. Kebutuhan Non-Fungsional
Kebutuhan non-fungsional  berkaitan  dengan kualitas  sistem  sebagaimana
disajikan pada Tabel 3.2.
Ta bel 3.2 Kebutuha n non-fungsiona l
## No Kode Parameter Target Verifikasi
1 INV-NF01 Akurasi
## Deduksi
Deduksi  stok  sesuai  resep
menu
Uji deduksi
berbasis resep
2 INV-NF02 Prioritas
## Batch Stok
Mode  deduksi  FIFO  dan
## FEFO
Uji algoritma
FIFO dan FEFO

## 22

3 INV-NF03 Validitas
## Penyesuaian
Penyesuaian stok
penambahan dan
pengurangan
Uji penyesuaian
stok
4 INV-NF04 Pemulihan
## Stok
Pembatalan penyesuaian
dan mengembalikan    stok
awal
Uji  pembatalan
penyesuaian
5 INV-NF05 Konsistensi
## Data
Transaksi dalam satu
proses yang konsisten
Uji   konsistensi
batch

## 3.4. Perancangan Basis Data
## 3.2. Entity Relationship  Diagram

Ga mba r 3.7 ERD sistem inventori
Terdapat   lima    tabel   utama   penyusun   sistem    manajemen    inventori.
Kelimanya terdiri    dari ingredients sebagai    master    data   bahan    baku,
ingredient_batches untuk  menyimpan  stok  per batch, menu_ingredients
sebagai tabel yang menghubungkan menu dengan bahan baku penyusunnya beserta

## 23

jumlah pemakaian, stock_movements sebagai catatan immutable setiap perubahan
stok  yang  terjadi,  dan stock_adjustments untuk  mencatat  penyesuaian  stok
manual beserta riwayat pembatalannya.

## 3.5. Deskripsi Entitas
Pada  implementasi  sistem  manajemen  inventori, perencanaan database
dijelaskan   secara   rinci   melalui   tabel-tabel   berikut   yang   mencakup   seluruh
spesifikasi  teknis  penyimpanan  data  berdasarkan Entity  Relationship  Diagram
## (ERD).
Ta bel 3.3 Struktur ta bel ingredients
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
## 2
Ta bel 3.4 Struktur ta bel ingredient_ba tches
## Kolom Tipe Keterangan
id BIGINT PK Primary key, auto-
increment
ingredient_id BIGINT FK Foreign key ke
ingredients
quantity DECIMAL Jumlah stok

## 24

expiry_date DATE Kedaluwarsa (FEFO)
received_at TIMESTAMP Penerimaan  (FIFO)
cost_per_unit INTEGER Harga per unit
custom_order INTEGER Urutan kustom batch

Ta bel 3.5 Struktur ta bel menu_ingredients
## Kolom Tipe Keterangan
id BIGINT PK Primary key, auto-
increment
menu_id BIGINT FK
Foreign key ke menus
ingredient_id BIGINT FK Foreign key ke
ingredients
quantity_used DECIMAL Jumlah   bahan   per   unit
menu

Ta bel 3.6 Struktur ta bel stock_a djustments
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

## 25

reported_by BIGINT FK
Foreign key ke users
adjusted_at TIMESTAMP Waktu kejadian
status VARCHAR Status (active/cancelled)
cancel_reason TEXT Alasan pembatalan
created_at TIMESTAMP Waktu dibuat
updated_at TIMESTAMP Waktu diperbarui

Ta bel 3.7 Struktur ta bel stock_movements
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

## 26

unit_cost DECIMAL(12,2) Harga per unit
notes TEXT Catatan
recorded_by BIGINT FK
Foreign key ke users
created_at TIMESTAMP Waktu dicatat


## 3.6. Perencanaan Pengujian

Perlu dibedakan antara metodologi penelitian dan metode penelitian yang digunakan dalam tugas akhir ini. Metodologi penelitian adalah pendekatan pengembangan sistem secara keseluruhan, yaitu Agile Development dengan tahapan requirements, design, development, testing, deployment, dan review yang dijalankan secara iteratif. Sedangkan metode penelitian adalah teknik spesifik yang digunakan untuk mengumpulkan data, memvalidasi sistem, dan menganalisis hasil — dalam hal ini mencakup black box testing untuk validasi fungsionalitas fitur dari sisi pengguna, white box testing untuk verifikasi kebenaran logika internal dan algoritma, serta gray box testing untuk memastikan integrasi antar modul berjalan konsisten.
Pengujian   dilakukan untuk  memvalidasi   sistem   manajemen   inventori
dengan  tiga  metode yang  saling  melengkapi,  yaitu black  box  testing, white  box
testing,  dan gray box testing.

## 3.6.1 Pengujian Black Box
Pengujian black  box bertujuan  untuk  memvalidasi   fungsionalitas  fitur
sistem  dari  sisi  pengguna tanpa  mengetahui  struktur internal  kode, dengan fokus
pada  kesesuaian input dan output terhadap  spesifikasi  kebutuhan  fungsional.
Skenario  pengujian  mencakup  autentikasi  admin  (login dan logout),  manajemen
bahan   baku   (create,   read,   update,   delete),   penyesuaian   stok  (penambahan,
pengurangan, dan input tidak valid), serta manajemen  menu  (tambah, ubah, hapus
menu, serta tambah dan hapus resep).
Pengujian   dilakukan   secara   manual   melalui   antarmuka   panel   admin
Filament  menggunakan  web browser. Seluruh  skenario  pengujian  berjalan  sesuai
dengan hasil yang diharapkan tanpa ditemukan kesalahan fungsional.3
Ta bel 3.8 Renca na  skena rio pengujia n black box
## No Kategori
## Pengujian
Skenario Pengujian Hasil  yang Diharapkan
1 Autentikasi Login dengan   kredensial
valid
Berhasil   masuk   ke  panel
admin
2 Autentikasi Login dengan password
salah
Muncul pesan error
3 Autentikasi Logout Kembali ke halaman login
4 Bahan Baku Tambah bahan baku Data muncul di tabel

## 27

5 Bahan Baku Ubah bahan baku Data berubah
6 Bahan Baku Hapus bahan baku Data hilang (soft  delete)
7 Bahan Baku Tambah batch stok Batch muncul di daftar
## 8 Penyesuaian
## Stok
Penyesuaian penambahan Stok     bertambah sesuai
input
## 9 Penyesuaian
## Stok
Penyesuaian pengurangan Stok berkurang sesuai input
## 10 Penyesuaian
## Stok
Input jumlah tidak valid Ditolak sistem
11 Menu Tambah menu baru Data muncul di tabel
12 Menu Ubah menu Data berubah sesuai input
13 Menu Hapus menu Data hilang (soft  delete)
14 Menu Tambah resep menu Menu  memiliki  resep  baru
sesuai input
15 Menu Hapus resep menu Muncul   peringatan   resep
wajib diisi

## 3.6.2 Pengujian White  Box
Pengujian white box bertujuan untuk memvalidasi kebenaran logika internal
dan algoritma sistem dengan mengakses source code secara langsung, dengan fokus
pada kebenaran algoritma  deduksi batch FEFO dan FIFO, mekanisme penyesuaian
stok,  serta  pembatalan  penyesuaian.  Skenario  pengujian  mencakup  deduksi stok
berdasarkan  resep  menu,  algoritma  FIFO (batch  dengan received_at paling  awal
dikonsumsi  terlebih  dahulu), algoritma  FEFO (batch dengan expiry_date terdekat
dikonsumsi  terlebih  dahulu),  penyesuaian  stok  tipe  increase,  serta  pembatalan
penyesuaian stok yang mengembalikan  stok ke kondisi awal.
Pengujian  dilakukan menggunakan PHPUnit dengan database PostgreSQL
untuk memverifikasi  perubahan  data secara  langsung.  Kriteria  keberhasilan  yang
ditetapkan   adalah    seluruh    skenario    pengujian    berjalan    tanpa error dan
menghasilkan  keluaran yang sesuai dengan yang diharapkan.4

## 28

Ta bel 3.9 Renca na  skena rio pengujia n white box
## No Kategori
## Pengujian
Skenario Pengujian Hasil  yang Diharapkan
1 Deduksi Resep Deduksi  stok  berdasarkan
resep menu
Stok bahan baku berkurang
sesuai quantity_used
## 2 Algoritma
## FIFO
Batch dengan received_at
berbeda
Batch paling  awal  terpakai
duluan
## 3 Algoritma
## FEFO
Batch dengan expiry_date
berbeda
Batch terdekat kedaluwarsa
terpakai duluan
## 4 Penyesuaian
## Stok
Penyesuaian tipe increase Kuantitas batch bertambah
5 Pembatalan Pembatalan penyesuaian
stok
Stok  kembali   ke   kondisi
awal

## 3.6.3 Pengujian Gray Box
Pengujian gray  box bertujuan  untuk  memvalidasi  interaksi  antara  sistem
transaksi dan sistem inventori dengan pengetahuan parsial terhadap struktur internal
sistem,  yaitu  dengan  mengirimkan   request  HTTP  ke controller transaksi  dan
memverifikasi   efek  sampingnya  pada database inventori.  Skenario  pengujian
mencakup    keberhasilan    pesanan   kasir,    yaitu memastikan    stok   berkurang,
pergerakan  stok  tercatat,  dan  pemakaian  harian  direkam, mengirimkan  error  ke
sistem transaksi ketika stok tidak mencukupi (memastikan pesanan ditolak dan stok
tidak  berubah),  serta  alur  pemesanan  pelanggan  yang  dikonfirmasi  oleh  kasir
(memastikan  stok  baru  berkurang  setelah pesanan  dari  pelanggan  dikonfirmasi
kasir).
Pengujian  dilakukan  menggunakan  PHPUnit  dengan  HTTP testing dan
database assertion untuk  memverifikasi  konsistensi  data.  Kriteria  keberhasilan
yang  ditetapkan  adalah  seluruh  skenario   pengujian  berjalan   tanpa error dan
menghasilkan output serta perubahan data yang sesuai dengan yang diharapkan.


## 29

Ta bel 3.10 Renca na  skena rio pengujia n gray box
## No Kategori
## Pengujian
Skenario Pengujian Hasil  yang Diharapkan
## 1 Pesanan Kasir
## Sukses
Kasir   membuat   pesanan
melalui  POS
Stok berkurang,
pergerakan tercatat,
pemakaian harian direkam
## 2 Pesanan Kasir
## Gagal
Kasir   membuat   pesanan
dengan stok tidak
mencukupi
Pesanan ditolak,     stok
tidak berubah
3 Alur Pelanggan Pelanggan pesan lalu
kasir konfirmasi
Stok baru berkurang
setelah    konfirmasi dari
kasir

## 3.7. Lingkungan Pengembangan
Dalam  proses  pengembangan  sistem  manajemen  inventori  berbasis  web,
digunakan  beberapa  perangkat  keras  dan  perangkat  lunak  pendukung.  Rincian
spesifikasi  perangkat  keras  yang  digunakan  selama  proses  pengembangan  dapat
dilihat pada Tabel 3.11.
Ta bel 3.11 Pera ngka t kera s pengemba nga n
## Perangkat Keras Nama Spesifikasi
Laptop Pengembangan Lenovo IdeaPad Slim 1 Prosesor:  AMD Ryzen 3
## 3250U,    RAM:    8    GB
DDR4, Storage:  256  GB
SSD, Layar:  14 inci  HD,
OS: Windows 11
Selain  perangkat  keras,  beberapa  perangkat  lunak  juga  digunakan  untuk
mendukung proses pengembangan sistem.  Detail spesifikasi  perangkat lunak yang
digunakan dapat dilihat pada Tabel 3.12.
Ta bel 3.12 Pera ngka t luna k pengemba nga n
## Perangkat Lunak Nama Perangkat
## Kerangka Kerja Laravel Framework

## 30

Bahasa Pemrograman PHP
Basis Data PostgreSQL
## Admin Panel Filament
## Container Docker
Code Editor VS Code
## Version Control Git
## Web Browser Microsoft Edge


## 31
## BAB IV
## IMPLEMENTASI DAN PENGUJIAN

## 4.0. Proses Pengembangan (Agile Iterations)

Sistem dikembangkan menggunakan pendekatan Agile dengan dua iterasi utama. Setiap iterasi mencakup tahap perencanaan (requirements & design), pengembangan, pengujian internal, dan review dengan stakeholder.

**Iterasi 1 — Inti Inventori dan Integrasi Transaksi** dilakukan pada minggu pertama hingga ketiga. Pada iterasi ini, fitur yang dikembangkan meliputi manajemen bahan baku, batch stok, dan resep menu. Algoritma FEFO dan FIFO diimplementasikan dan diuji coba dengan data simulasi. Integrasi awal dengan modul transaksi dilakukan untuk memvalidasi bahwa deduksi stok berjalan otomatis saat pesanan diproses. Setelah review iterasi 1, ditemukan kebutuhan untuk menambahkan fitur pembatalan penyesuaian stok yang mengembalikan stok ke kondisi awal — fitur ini kemudian dimasukkan ke dalam perencanaan iterasi 2.

**Iterasi 2 — Panel Administrasi dan Penyesuaian Stok** dilakukan pada minggu keempat hingga keenam. Fokus iterasi ini adalah pengembangan panel administrasi menggunakan Filament untuk seluruh entitas inventori (bahan baku, batch, menu, resep, penyesuaian stok, riwayat). Fitur pembatalan penyesuaian stok ditambahkan berdasarkan umpan balik iterasi 1. Selain itu, pengujian white box dan gray box ditulis untuk memvalidasi skenario-skenario kritis. Setelah iterasi 2 selesai, dilakukan pengujian black box terhadap seluruh fitur dan penyusunan dokumentasi tugas akhir.

4.1. Implementasi  Algoritma  pada Lapisan Service
## 4.1.1. Implementasi Algoritma  Deduksi Stok
Algoritma  deduksi stok  merupakan  inti  dari  sistem  manajemen  inventori
yang  menentukan  urutan  konsumsi batch ketika  terjadi  pemakaian  bahan  baku.
Sistem mengimplementasikan  dua mode deduksi utama, yaitu FEFO (First-Expiry-
First-Out)  dan FIFO (First-In-First-Out).  Mekanisme  penguncian  data (row-level
locking) diterapkan untuk mencegah konflik pada transaksi bersamaan.
Penentuan    urutan batch dilakukan    melalui    perintah    match    yang
menerjemahkan  mode batch bahan baku menjadi urutan query SQL. Batch dengan
quantity lebih besar  dari nol diambil, kemudian diurutkan berdasarkan mode yang
dikonfigurasi  pada setiap bahan  baku. Batch yang memiliki  nilai  relevan  kosong
(null)  ditempatkan di akhir urutan agar tidak mengganggu prioritas.
$query = IngredientBatch::where('ingredient_id', $ingredientId)
## ->where('quantity', '>', 0)
## ->where(function ($q) {
$q->whereNull('expiry_date')
->orWhereDate('expiry_date', '>', now())
->orWhere('allow_expired_usage', true);
## })
->lockForUpdate();

match ($ingredient->batch_mode) {
Ingredient::BATCH_MODE_FIFO => $query
->orderByRaw('CASE WHEN received_at IS NULL THEN 1 ELSE
## 0 END')
->orderBy('received_at', 'asc')
->orderBy('expiry_date', 'asc')
->orderBy('id', 'asc'),
default => $query  // FEFO
->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE
## 0 END')
->orderBy('expiry_date', 'asc')
->orderBy('received_at', 'asc')
->orderBy('id', 'asc'),
## };

## 32

Pada kode di atas, baris match menentukan urutan batch berdasarkan mode.
Pada mode  FIFO, batch diurutkan  berdasarkan  received_at  terlama  (ascending).
Pada mode default (FEFO), batch diurutkan berdasarkan expiry_date terdekat
(ascending). Kode orderByRaw('CASE WHEN ... IS NULL THEN 1 ELSE 0 END')
memastikan  bahwa batch yang memiliki  nilai  relevan  kosong ditempatkan paling
akhir sehingga  tidak dikonsumsi  lebih dahulu. Kode lockForUpdate() mengunci
baris-baris batch yang  terpilih  untuk  mencegah  transaksi  bersamaan  mengakses
data yang sama sebelum transaksi saat ini selesai.
Setelah batch diurutkan sesuai  prioritas,  sistem  melakukan  iterasi  deduksi
dari batch pertama  hingga  kebutuhan kuantitas terpenuhi.  Setiap iterasi  mencatat
pergerakan stok melalui model StockMovement yang merekam quantity_before,
quantity_change,  dan  quantity_after untuk merekam  riwayat  penggunaan
stok.
foreach ($batches as $batch) {
if ($remainingToDeduct <= 0) break;
## $before = (float) $batch->quantity;
$deductFromThisBatch = min($before, $remainingToDeduct);
$after = $before - $deductFromThisBatch;
## $batch->quantity = $after;
## $batch->save();
$remainingToDeduct -= $deductFromThisBatch;
StockMovement::create([
'ingredient_id' => $ingredientId,
## 'ingredient_batch_id' => $batch->id,
'order_id' => $context['order_id'] ?? null,
## 'movement_type' => $context['movement_type'] ?? 'sale',
## 'quantity_before' => $before,
'quantity_change' => -$deductFromThisBatch,
## 'quantity_after' => $after,
## 'unit_cost' => $batch->cost_per_unit,
'notes' => $context['notes'] ?? null,
## ]);
## }
Pada kode di atas, setiap batch diproses secara  berurutan. Variabel  before
menyimpan   nilai   stok  sebelum   deduksi, deductFromThisBatch menghitung
jumlah  yang  diambil  dari batch saat  ini  menggunakan  fungsi min(),  dan  after
menyimpan nilai stok setelah deduksi. Setelah penyimpanan batch, sistem mencatat
StockMovement dengan quantity_change bernilai   negatif  karena  merupakan

## 33

pengurangan stok. Proses berlanjut hingga remainingToDeduct habis atau seluruh
batch telah diproses.

4.1.2. Implementasi  Penyesuaian Stok dan Perhitungan Stok
Penyesuaian  stok (stock adjustment)  merupakan  fitur yang memungkinkan
admin melakukan  perubahan  stok secara  manual  di luar  transaksi  penjualan,  baik
berupa  penambahan  (increase)  maupun  pengurangan  (decrease, waste, damage).
Fitur     pembatalan     penyesuaian     (cancel)     juga     diimplementasikan untuk
mengembalikan  stok ke kondisi sebelum  penyesuaian dilakukan.
Mekanisme  pembatalan  penyesuaian  bekerja  dengan  cara  membalikkan
(reverse)   setiap  pergerakan  stok  yang  tercatat  pada  penyesuaian   yang  akan
dibatalkan.  Untuk  setiap StockMovement yang  terkait,  sistem  menghitung  nilai
perubahan  kebalikan (reversalChange  = -originalChange), mengembalikan
stok batch ke nilai  semula, dan mencatat StockMovement baru sebagai jejak audit.
foreach ($record->stockMovements as $movement) {
$batch = IngredientBatch::find($movement-
## >ingredient_batch_id);
if (! $batch) continue;
$originalChange = (float) $movement->quantity_change;
$reversalChange = -$originalChange;
$batchBefore = (float) $batch->quantity;
$batch->increment('quantity', $reversalChange);
$batchAfter = (float) $batch->quantity;
StockMovement::create([
## 'ingredient_id' => $movement->ingredient_id,
## 'ingredient_batch_id' => $batch->id,
## 'stock_adjustment_id' => $record->id,
## 'movement_type' => $movement->movement_type,
## 'source_type' => 'stock_adjustment_reversal',
## 'source_id' => (string) $record->id,
'quantity_before' => $batchBefore,
'quantity_change' => $reversalChange,
'quantity_after' => $batchAfter,
## 'unit_cost' => $batch->cost_per_unit,
'notes' => 'Pembatalan: ' . $reason,
## ]);
## }
Selain  penyesuaian  stok, sistem  juga  menyediakan  perhitungan  stok yang
dikonversi menjadi jumlah  porsi (servings)  yang dapat diproduksi dari bahan baku
yang tersedia. Atribut stock pada model Menu menghitung ketersediaan stok untuk
setiap  menu  berdasarkan  resep  bahan  baku  penyusunnya.  Perhitungan  dilakukan

## 34

dengan  membagi   total  stok  setiap  bahan  baku  dengan  kebutuhan  per  porsi
(quantity_used),  kemudian  mengambil  nilai  minimum  di antara  seluruh  bahan
baku penyusun.  Pendekatan ini  memastikan  bahwa jumlah  porsi  yang dilaporkan
sesuai dengan bahan baku yang paling terbatas.
public function getStockAttribute(): ?float
## {
$ingredients = $this->menuIngredients()->with('ingredient')-
## >get();
if ($ingredients->isEmpty()) return null;
$minServings = null;
foreach ($ingredients as $mi) {
if (! $mi->ingredient) continue;
$totalStock = (float) $mi->ingredient->batches()
## ->where('quantity', '>', 0)
## ->where(function ($q) {
$q->whereNull('expiry_date')
->orWhereDate('expiry_date', '>', now())
->orWhere('allow_expired_usage', true);
## })
## ->sum('quantity') ?: 0;
## $needed = (float) $mi->quantity_used;
$servings = $needed > 0 ? (int) ($totalStock / $needed)
## : 0;
if ($minServings === null || $servings < $minServings) {
$minServings = $servings;
## }
## }
return $minServings ?? 0;
## }
Pada  kode di  atas,  setiap  bahan  baku  penyusun  menu  diperiksa  stoknya
melalui  relasi batches.  Hanya batch dengan quantity lebih  dari  nol  dan belum
kedaluwarsa  (atau  diizinkan  penggunaan  kedaluwarsa)  yang  dihitung. Total  stok
dibagi dengan quantity_used (kebutuhan  per  porsi)  untuk mendapatkan jumlah
porsi  yang  dapat dibuat  dari  bahan  tersebut.  Nilai  minimum  (minServings)  di
antara seluruh  bahan  kemudian menjadi  nilai  akhir  atribut stock. Jika  menu tidak
memiliki  resep (ingredients kosong), fungsi mengembalikan nulsl.

## 4.2. Implementasi  Panel Admin
## 4.2.1. Halaman Login
Halaman login merupakan  halaman  pertama  yang  muncul  ketika  admin
mengakses  panel  admin.  Halaman  ini menampilkan formulir  autentikasi  dengan

## 35

input email dan password untuk memverifikasi  identitas admin sebelum mengakses
sistem.

Ga mba r 4.1 Ha la ma n login a dmin

## 4.2.2. Halaman Kategori Menu
Halaman kategori menu menampilkan  daftar kategori menu beserta jumlah
menu yang termasuk ke dalam kategori tersebut. Admin dapat melihat, menambah,
mengedit, dan menghapus kategori sesuai kebutuhan.

Ga mba r 4.2 Ha la ma n da fta r ka tegori


## 36


Ga mba r 4.3 Form bua t da fta r ka tegori ba ru
Form  tambah  kategori  berisi  input  nama  kategori  yang  harus  diisi  oleh
admin. Setelah disimpan, kategori baru akan muncul di tabel daftar kategori.

## 4.2.3. Halaman Menu
Halaman   menu   menampilkan   daftar  seluruh   menu   beserta   informasi
kategori, harga, diskon khusus mahasiswa STIE Totalwin, jumlah prediksi sisa jual,
dan  status  ketersediaan.  Admin  dapat  melihat  dan  mengelola  data  menu  dari
halaman ini.

Ga mba r 4.4 Ha la ma n da fta r menu


## 37


Ga mba r 4.5 Ha la ma n form bua t menu
Form buat menu baru terdiri dari input nama menu, pemilihan kategori, foto
menu yang akan ditampilkan di web self-order pelanggan (opsional), harga menu,
diskon khusus  mahasiswa  STIE  Totalwin  (opsional), toggle status tersedia, serta
pengelolaan  resep bahan baku penyusun menu. Admin dapat memilih  bahan baku
dari dropdown dan menentukan  jumlah  pemakaian  per  unit  menu. Setiap  menu
wajib memiliki  minimal satu bahan baku. Jika status tersedia aktif, maka menu akan
muncul  di web POS  kasir  dan web self-order pelanggan. Sebaliknya,  jika  status
tersedia dimatikan, maka menu tidak akan muncul di web POS kasir  dan web self-
order pelanggan.

## 4.2.4. Halaman Bahan Baku
Halaman bahan baku menampilkan daftar seluruh bahan baku yang terdaftar
dalam sistem beserta informasi seperti unit, total stok (dihitung dari seluruh batch
stok  yang ada), kedaluwarsa  terdekat, mode prioritas batch stok  bahan  baku
(FEFO/FIFO). Admin  dapat mengunjungi  halaman batch stok  bahan  baku  dan
riwayat pemakaian bahan baku yang dipilih dari halaman ini.

## 38


Ga mba r 4.6 Ha la ma n ba ha n ba ku


Ga mba r 4.7 Form bua t ba ha n ba ku ba ru
Form  tambah  bahan  baku  berisi  input  nama  bahan  baku,  pemilihan  unit
satuan,  dan mode prioritas batch stok.  Mode batch menentukan  urutan priotitas
deduksi stok ketika stok berkurang  karena  pesanan.  Terdapat dua mode  prioritas
batch stok, yaitu FEFO yang memprioritaskan masa kedaluwarsa, serta FIFO yang
memprioritaskan  waktu penerimaan batch stok. Khusus  mode FEFO, batch stok
yang  kedaluwarsa secara default tidak  akan  dipakai  lagi  oleh  sistem.  Namun
perilaku sistem ini bisa diubah admin untuk setiap batch yang akan diinput melalui
form input batch stok baru.


## 39

## 4.2.5. Halaman Batch Stok Bahan Baku
Halaman batch stok  bahan  baku  menampilkan  daftar batch untuk  suatu
bahan  baku. Setiap  batch menampilkan  informasi kode batch,  jumlah  stok untuk
masing-masing batch, tanggal  kedaluwarsa,  waktu  diterima,  dan harga  satuan.
Batch stok  yang  habis  akan  otomatis  disembunyikan  saat  pertama  kali  admin
mengunjungu  halaman  ini,  namun  bisa  ditampilkan  jika  admin  mengklik  tombol
"Tampilkan Batch Habis".

Ga mba r 4.8 Ha la ma n batch stok
Batch stok yang  kedaluwarsa  secara default tidak akan  dipakai  lagi  oleh
sistem. Namun admin harus  secara  manual menandai bahwa batch stok tersbebut
sudah kedaluwarsa agar sesuai  keadaan  nyata  di  lapangan.  Jika  sudah  ditandai
kedaluwarsa, maka batch stok akan dianggap habis dan akan disembunyikan ketika
admin pertama kali mengunjungi halaman batch stok untuk bahan baku tersebut.

## 40


Ga mba r 4.9 Admin mena nda i batch keda luwa rsa
Form buat batch stok berfungsi untuk menambah batch stok baru yang berisi
input  jumlah  stok,  tanggal  kedaluwarsa (wajib  jika  bahan  baku  memakai  mode
FEFO, opsional  jika  mode FIFO), waktu diterima, dan harga satuannya. Terakhir
terdapat toggle yang jika diaktifkan, maka batch stok tersebut tetap dapat terpakai
untuk pesanan walaupun batch tersebut sudah kedaluwarsa dan memakai  prioritas
batch FEFO. Hal ini  dapat berguna  untuk memberikan  fleksibilitas  kepada admin
untuk  bahan  baku  yang sudah kedaluwarsa  secara  sistem  namun dalam  kondisi
nyata masih layak pakai.

Ga mba r 4.10 Form bua t batch stok ba ru

## 4.2.6. Halaman Penyesuaian Stok
Penyesuaian stok dilakukan secara manual oleh admin melalui form penyesuaian di panel Filament. Berbeda dengan deduksi stok yang berjalan otomatis ketika pesanan diproses, penyesuaian stok adalah tindakan manual yang dilakukan admin ketika menemukan selisih antara catatan sistem dengan stok fisik, atau ketika ada bahan yang terbuang (waste). Halaman penyesuaian stok menampilkan daftar seluruh penyesuaian yang telah dilakukan, mencakup informasi kode, waktu, tipe

## 41

(penambahan/pengurangan),   jenis   (bahan   baku/menu),   nama,  kategori,   status
(aktif/dibatalkan), serta jumlah  stok sebelum  dan sesudah. Setiap baris  dilengkapi
tombol  "Detail"  untuk  melihat  informasi  lengkap  dan  tombol  "Batalkan"  untuk
membatalkan penyesuaian yang masih  aktif.

Ga mba r 4.11 Ha la ma n penyesua ia n stok
Detail   penyesuaian   stok   menampilkan    informasi    lengkap   mengenai
penyesuaian stok. Informasi tambahan yang hanya ditampilkan detail penyesuaian
meliputi catatan penyesuaian serta alasan pembatalan (jika dibatalkan).

Ga mba r 4.12 Deta il penyesua ia n stok
Form  penyesuaian  stok baru  terdiri dari  pemilihan  jenis  (bahan  baku  atau
menu), pemilihan  bahan baku atau menu yang akan disesuaikan,  tipe penyesuaian
(Penambahan  atau  Pengurangan),  kategori  penyesuaian   (seperti  Kedaluwarsa,
Rusak,  Tumpah,  Koreksi  Stok,  atau  Lainnya),  input  jumlah,  catatan,  petugas

## 42

pelapor,   dan  tanggal   kejadian.   Setiap   penyesuaian   dicatat  oleh   sistem   dan
memperbarui  stok pada batch terkait.

Ga mba r 4.13 Form bua t penyesua ia n stok ba ru
Penyesuaian  stok  yang  masih  berstatus  aktif  dapat  dibatalkan  melalui
tombol  "Batalkan".  Admin  wajib  mengisi  alasan  pembatalan.  Ketika  dibatalkan,
sistem  secara  otomatis  mengembalikan   stok  ke  kondisi  sebelum  penyesuaian
dilakukan dengan membalikkan  setiap perubahan pada batch terkait dan mencatat
pergerakan   stok  baru  sebagai reversal.   Status  penyesuaian   berubah  menjadi
"Dibatalkan" dan stok kembali seperti semula.

Ga mba r 4.14 Admin memba ta lka n penyesua ia n stok


## 43

## 4.2.7. Halaman Riwayat Penggunaan Bahan Baku
Halaman  riwayat  penggunaan  bahan  baku  menampilkan  data pemakaian
bahan  baku  berdasarkan  transaksi  penjualan  yang  telah  terjadi.  Informasi  ini
membantu admin dalam memantau tren penggunaan bahan baku.

Ga mba r 4.15 Ha la ma n riwa ya t stok


Ga mba r 4.16 Ha la ma n deta il riwa ya t stok versi penyesua ia n

## 44


Ga mba r 4.17 Ha la ma n deta il riwa ya t stok versi penjua la nss
Tombol detail pada halaman riwayat penggunaan bahan baku menampilkan
informasi  lebih  lanjut  mengenai  pemakaian  yang terjadi. Tampilan  detail bersifat
fleksibel tergantung pada jenis pergerakan  stok yang mendasarinya. Ada dua jenis
pemakaian, yaitu penjualan dan penyesuaian. Jika pemakaian berasal dari transaksi
penjualan, detail akan menampilkan  pesanan terkait dan item menu yang diproses.
Jika pemakaian  berasal  dari  penyesuaian  stok,  detail  akan  menampilkan  kode
penyesuaian dan alasan dilakukannya penyesuaian.

## 4.3. Pengujian
Pengujian sistem dilakukan menggunakan tiga metode: black box testing dilakukan secara manual melalui antarmuka panel Filament untuk memvalidasi fungsionalitas fitur dari sisi pengguna; white box testing menggunakan PHPUnit (dijalankan melalui perintah `php artisan test`) untuk memverifikasi kebenaran logika internal dan algoritma; gray box testing menggunakan PHPUnit Feature Tests dengan HTTP test helpers (`actingAs`, `post`, `assertDatabaseHas`) untuk memverifikasi integrasi antar modul melalui request HTTP.
## 4.3.1. Pengujian Black Box
Pengujian black  box berfokus pada validasi  fungsionalitas  sistem  dari sisi
antarmuka  pengguna,  memastikan  bahwa  setiap  fitur  yang  tersedia  pada  panel
administrasi  berjalan  sesuai  dengan kebutuhan  fungsional  yang  telah  dirancang.
Skenario  pengujian black  box mencakup  autentikasi  admin,  manajemen  bahan
baku, penyesuaian stok, dan manajemen menu.
a. Pengujian Autentikasi Admin
Pengujian  autentikasi  admin  dilakukan  untuk  memastikan  bahwa  hanya
admin  yang  terdaftar yang  dapat  mengakses  panel  admin.  Skenario  pengujian
mencakup login dengan kredensial valid, login dengan password salah, dan logout.


## 45

Ta bel 4.1 Pengujia n black box a utentika si a dmin
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
admin  dapat melakukan  operasi  CRUD pada  data bahan  baku  serta  menambah
batch stok.  Skenario  pengujian  mencakup  tambah,  ubah,  dan hapus  bahan  baku,
serta tambah batch stok.
Ta bel 4.2 Pengujia n black box ma na jemen ba ha n ba ku
## Skenario Langkah Hasil
## Diharapkan
## Status
Tambah bahan
baku
Isi form, simpan Data   muncul    di
tabel
## Berhasil
Ubah bahan baku Ubah    nama/unit,
simpan
Data berubah Berhasil
Hapus bahan baku Klik hapus Data  hilang  (soft
delete)
## Berhasil
Tambah batch
stok
Isi jumlah,
tanggal, simpan
Batch muncul   di
daftar
## Berhasil
c. Pengujian Penyesuaian Stok
Pengujian  penyesuaian  stok  dilakukan  untuk  memvalidasi  bahwa  admin
dapat melakukan  penambahan  dan pengurangan  stok secara  manual,  serta sistem
menolak   input  yang  tidak  valid.  Skenario   pengujian   mencakup  penyesuaian
penambahan, penyesuaian pengurangan, dan input jumlah tidak valid.

## 46

Ta bel 4.3 Pengujia n black box penyesua ia n stok
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
Penyesuaian     qty
## <= 0
Isi 0 Ditolak sistem Berhasil
d. Pengujian Manajemen  Menu
Pengujian  manajemen  menu  dilakukan  untuk memvalidasi  bahwa  admin
dapat  mengelola  data  menu  beserta  resep  bahan  baku  penyusunnya.  Skenario
pengujian  mencakup  tambah,  ubah,  dan  hapus  menu,  serta  penambahan  dan
penghapusan resep menu.5
Ta bel 4.4 Pengujia n black box ma na jemen resep menu
## Skenario Langkah Hasil
## Diharapkan
## Status
Tambah menu
baru
Isi form, simpan Data   muncul    di
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
Hapus resep menu Klik hapus Muncul pesan
peringatan
## Berhasil

## 47

resepmenu    wajib
diisi
Seluruh  skenario  pengujian black  box pada  kelima  modul  menunjukkan
status  Berhasil.  Hasil  ini  menandakan  bahwa  seluruh  fungsionalitas  antarmuka
panel  administrasi  telah  berjalan  sesuai  harapan,  mulai  dari  autentikasi  admin,
pengelolaan bahan baku dan batch stok, penyesuaian stok, hingga manajemen resep
menu.

## 4.3.2. Pengujian White  Box
Pengujian white  box dilakukan  untuk  memverifikasi   kebenaran  logika
internal sistem  menggunakan PHPUnit. Pengujian mencakup  lima  skenario utama
yang merepresentasikan  fitur inti manajemen  inventori.
## 1. Pengujian Deduksi Stok Berdasarkan Resep Menu
Pengujian  ini  memvalidasi  bahwa ketika suatu menu  yang memiliki  resep
(komposisi  bahan baku)  diproses,  sistem  secara  otomatis mengurangi  stok bahan
baku sesuai  dengan jumlah  yang terdaftar pada tabel menu_ingredients. Dalam
skenario pengujian, langkah pertama adalah menentukan sebuah menu "Kopi Susu"
dengan resep  30 gram  kopi per porsi  dipesan sebanyak 2 porsi. Lalu sistem  harus
mengurangi stok kopi sebesar 60 gram dari batch yang tersedia.
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

## 48


$this->assertTrue($result['success']);
$this->assertDatabaseHas('stock_movements', [
## 'ingredient_id' => $ingredient->id,
## 'movement_type' => 'sale',
## ]);
## }

Ga mba r 4.18 Pengujia n deduksi stok berda sa rka n resep menu
Skenario  ini  berhasil  memvalidasi  bahwa sistem  mampu  mendeduksi stok
bahan  baku  secara  tepat berdasarkan  resep  menu,  dengan pencatatan pergerakan
stok yang akurat pada setiap transaksi.

- Pengujian Algoritma FIFO
Pengujian  ini  memvalidasi   bahwa  algoritma   FIFO  (First-In-First-O ut)
mengonsumsi batch dengan received_at paling  awal terlebih dahulu. Dua batch
bahan baku dengan mode FIFO dibuat, yaitu Batch A (diterima 5 hari lalu, qty: 100)
dan Batch B (diterima 1 hari lalu,  qty: 200). Setelah dilakukan deduksi sebesar 60
gram, Batch A  berkurang  menjadi  40  gram  dan batch B  tetap  200  gram.  Hasil
pengujian  sesuai  dengan  prinsip  FIFO  karena batch yang  diterima  lebih  awal
diproses terlebih dahulu.
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

## 49

Hasil pengujian  ini membuktikan  bahwa algoritma  FIFO berfungsi dengan
benar,  yaitu  batch  yang  lebih  dahulu diterima  akan  dikonsumsi  terlebih  dahulu,
sehingga sesuai dengan prinsip First-In-First-Out.

- Pengujian Algoritma FEFO
Pengujian ini memvalidasi bahwa algoritma FEFO (First-Expiry-First-O ut)
menggunakan batch dengan expiry_date terdekat  terlebih  dahulu.  Dua batch
bahan baku dengan mode default FEFO dibuat, yaitu Batch A (kedaluwarsa 3 hari
lagi,  qty:  80) dan Batch B (kedaluwarsa  30 hari  lagi,  qty: 80).  Setelah dilakukan
deduksi sebesar  100 unit, Batch A habis terpakai  (80 unit) dan Batch B tersisa 60
unit. Prioritas terhadap batch yang mendekati kedaluwarsa ini penting untuk bahan
baku segar yang memiliki  masa simpan terbatas.
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
Hasil  pengujian  membuktikan  bahwa  algoritma  FEFO  berfungsi  dengan
benar, yaitu batch dengan masa kedaluwarsa terdekat dipakai terlebih dahulu.

## 4. Pengujian Penyesuaian Stok
Pengujian ini memvalidasi bahwa admin dapat melakukan penyesuaian stok
secara manual.  Penyesuaian  tipe increase menambah  stok  pada batch terbaru,

## 50

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
Hasil  pengujian  membuktikan  bahwa  mekanisme  penyesuaian  stok  tipe
increase berjalan  akurat sesuai dengan jumlah yang dimasukkan.

## 5. Pengujian Pembatalan Penyesuaian Stok
Pengujian ini memvalidasi bahwa ketika suatu penyesuaian stok dibatalkan,
sistem    mengembalikan    stok    ke    kondisi    sebelum    penyesuaian    dilakukan.
Pembatalan   penyesuaian increase akan   mengurangi    stok,   dan   pembatalan
penyesuaian decrease akan menambah stok kembali.  Seluruh proses dicatat dalam
StockMovement baru.
public function test_cancelling_adjustment_restores_stock():
void
## {
// Setup: buat adjustment increase 20 unit
$adjustment = $this->createAdjustment('increase', 20);
$stockBeforeCancel = (float) $adjustment->ingredient-
>getTotalStock();

// Batalkan adjustment
$adjustment->cancel('Wrong quantity');

## 51

$stockAfterCancel = (float) $adjustment->ingredient-
>fresh()->getTotalStock();

// Stok harus kembali ke jumlah awal
$this->assertSame($stockBeforeCancel - 20,
$stockAfterCancel);
$this->assertDatabaseCount('stock_movements', 2);
## }

Ga mba r 4.22 Pengujia n pemba ta la n penyesua ia n stok
Hasil pengujian membuktikan bahwa pembatalan penyesuaian stok berhasil
mengembalikan  stok ke kondisi semula dengan dicatatnya pergerakan baru sebagai
jejak audit.

Seluruh pengujian white box menunjukkan hasil sesuai dengan spesifikasi yang dirancang. Algoritma FIFO dan FEFO bekerja dengan benar, penyesuaian stok berjalan sesuai input, serta pembatalan penyesuaian berhasil mengembalikan stok ke kondisi awal. Tools: PHPUnit dengan konfigurasi database PostgreSQL, dijalankan melalui perintah `php artisan test`. Setiap skenario menguji method pada lapisan service (InventoryService, StockReconciliationService) secara langsung melalui dependency injection — memastikan bahwa setiap baris kode yang mengandung logika algoritma (statement coverage) dan setiap cabang keputusan (branch coverage) telah terverifikasi kebenarannya tanpa melalui HTTP request.

Cakupan white box testing meliputi:
- InventoryService::decreaseStockForOrder() — validasi input, pemilihan batch berdasarkan mode FEFO atau FIFO
- InventoryService::deductIngredientStock() — konversi satuan, filter batch valid, row-level locking, deduksi batch, pencatatan StockMovement
- StockReconciliationService::createManualAdjustment() — validasi tipe penyesuaian, penambahan/pengurangan stok
- Pembatalan penyesuaian — logika reversal untuk mengembalikan stok ke kondisi awal

## 4.3.3. Pengujian Gray Box
Pengujian gray box dilakukan untuk memverifikasi  bahwa aliran data antara
sistem  transaksi  (web kasir  dan pelanggan)  dan sistem  inventori  berjalan  dengan
benar.  Berbeda  dengan  pengujian white  box yang  memanggil service secara
langsung untuk memvalidasi  kebenaran algoritma, pengujian integrasi lintas modul
mengirimkan request HTTP  ke controller transaksi  dan memverifikasi  efeknya di
database inventori.
Pendekatan yang digunakan dalam pengujian  ini  termasuk  dalam kategori
gray  box  testing,  yaitu  metode pengujian  yang menggabungkan  aspek black  box
dan white  box dengan pengetahuan  parsial  terhadap struktur internal  sistem  [23].
Dalam konteks ini, penguji memiliki  pengetahuan tentang skema database dan alur
sistem  (seperti  nama  tabel stock_movements dan  kolom quantity_change),
namun pengujian tetap dilakukan melalui antarmuka HTTP tanpa memanggil  kode
secara  langsung.  Dengan  demikian,  pengujian  ini  memvalidasi  bahwa  seluruh
lapisan sistem mulai dari request pengguna, autentikasi, validasi, controller,  hingga
service inventori bekerja secara sesuai dengan rencana yang diinginkan.

## 52

Tiga  skenario  utama  diuji meliputi skenario  keberhasilan  pesanan  kasir,
skenario   gagal  ketika  stok  tidak  mencukupi,   serta  skenario   alur  pemesanan
pelanggan yang dikonfirmasi oleh kasir.

-  Pengujian Deduksi Stok dan Pencatatan Riwayat
Pengujian ini memverifikasi  bahwa ketika kasir berhasil  membuat pesanan
melalui  POS,  sistem  secara  otomatis  mendeduksi  stok  bahan  baku  sesuai  resep
menu dan mencatat pergerakan stok beserta pemakaian harian. Pengujian dilakukan
dengan  mengirimkan request HTTP POST ke  rute  /kasir/pesanan-baru  sebagai
pengguna yang telah diautentikasi.
public function
test_cashier_order_deducts_stock_and_creates_movement(): void
## {
## $cashier = User::factory()->create(['role' => 'cashier']);
$category = Category::create(['name' => 'Minuman']);
## $menu = Menu::create([
'name' => 'Kopi Susu',
## 'price' => 12000,
## 'category_id' => $category->id,
## ]);
## $ingredient = Ingredient::create([
'name' => 'Kopi Bubuk',
## 'unit' => 'gram',
## 'low_stock_threshold' => 10,
## ]);
$batch = IngredientBatch::create([
## 'ingredient_id' => $ingredient->id,
## 'quantity' => 100,
'expiry_date' => now()->addDays(30),
'received_at' => now(),
## 'cost_per_unit' => 1000,
## ]);
MenuIngredient::create([
## 'menu_id' => $menu->id,
## 'ingredient_id' => $ingredient->id,
## 'quantity_used' => 10,
## ]);

$this->actingAs($cashier);
## $response = $this->post(route('kasir.pesanan-baru.simpan'),
## [
## 'items' => [['menu_id' => $menu->id, 'quantity' => 2]],
## 'payment_method' => 'cash',
## ]);

$response->assertSessionHas('success');

$this->assertSame(80.0, (float) $batch->fresh()->quantity);

## 53

$this->assertDatabaseHas('stock_movements', [
## 'ingredient_id' => $ingredient->id,
## 'quantity_before' => 100,
## 'quantity_change' => -20,
## 'quantity_after' => 80,
## ]);
$this->assertDatabaseHas('daily_ingredient_usages', [
## 'ingredient_id' => $ingredient->id,
## ]);
## }

Ga mba r 4.23 Pengujia n Deduksi Stok da n Penca ta ta n Riwa ya t
Hasil  pengujian  membuktikan  bahwa  sistem  berhasil  mendeduksi  stok,
mencatat  pergerakan  stok,  dan  merekam  pemakaian  harian  secara  akurat  ketika
kasir membuat pesanan melalui  POS.

- Pengujian Order Gagal karena Stok Tidak Mencukupi
Pengujian ini memverifikasi  bahwa ketika stok bahan baku tidak mencukupi
untuk memenuhi  pesanan,  sistem  menolak  pesanan  tersebut dan tidak melakukan
perubahan   stok.  Validasi   dilakukan   oleh StoreOrderRequest melalui after
validation  hook yang memeriksa  kecukupan stok sebelum pesanan diproses.
public function
test_cashier_order_fails_when_stock_insufficient(): void
## {
## $cashier = User::factory()->create(['role' => 'cashier']);
$category = Category::create(['name' => 'Minuman']);
## $menu = Menu::create([
'name' => 'Kopi Susu',
## 'price' => 12000,
## 'category_id' => $category->id,
## ]);
## $ingredient = Ingredient::create([
'name' => 'Kopi Bubuk',
## 'unit' => 'gram',
## 'low_stock_threshold' => 10,
## ]);
IngredientBatch::create([
## 'ingredient_id' => $ingredient->id,
## 'quantity' => 15,
'expiry_date' => now()->addDays(30),
'received_at' => now(),
## 'cost_per_unit' => 1000,
## ]);
MenuIngredient::create([

## 54

## 'menu_id' => $menu->id,
## 'ingredient_id' => $ingredient->id,
## 'quantity_used' => 10,
## ]);

$this->actingAs($cashier);
## $response = $this->from('/kasir/pesanan-baru')-
## >post(route('kasir.pesanan-baru.simpan'), [
## 'items' => [['menu_id' => $menu->id, 'quantity' => 2]],
## 'payment_method' => 'cash',
## ]);

$response->assertSessionHasErrors('items');
## }

Ga mba r 4.24 Pengujia n Order Ga ga l ka rena  Stok Tida k Mencukupi
Hasil pengujian membuktikan bahwa sistem berhasil  menolak  pesanan dan
tidak  mengubah  data  stok  ketika  bahan  baku  tidak  mencukupi,  sesuai  dengan
mekanisme  validasi yang diterapkan.

- Pengujian Alur Pelanggan ke Konfirmasi Kasir
Pengujian  ini  memverifikasi  alur  lengkap  dari pemesanan  oleh  pelanggan
melalui   web self-order hingga  konfirmasi  pembayaran  oleh  kasir.  Pelanggan
membuat  pesanan, kemudian  kasir  mengonfirmasi  pembayaran.  Pengujian  ini
memvalidasi bahwa stok bahan baku tidak berubah saat pelanggan memesan karena
pesanan pelanggan harus menunggu konfirmasi kasir, lalu baru berkurang  setelah
kasir mengonfirmasi  pembayaran.
Public function
test_customer_order_flow_to_cashier_confirmation_deducts_stock():
void
## {
## $customer = User::factory()->create(['role' => 'customer']);
## $cashier = User::factory()->create(['role' => 'cashier']);
$category = Category::create(['name' => 'Minuman']);
## $menu = Menu::create([
'name' => 'Es Kopi',
## 'price' => 15000,
## 'category_id' => $category->id,
'is_available' => true,
## ]);
## $ingredient = Ingredient::create([
'name' => 'Kopi Bubuk',

## 55

## 'unit' => 'gram',
## 'low_stock_threshold' => 10,
## ]);
$batch = IngredientBatch::create([
## 'ingredient_id' => $ingredient->id,
## 'quantity' => 100,
'expiry_date' => now()->addDays(30),
'received_at' => now(),
## 'cost_per_unit' => 1000,
## ]);
MenuIngredient::create([
## 'menu_id' => $menu->id,
## 'ingredient_id' => $ingredient->id,
## 'quantity_used' => 10,
## ]);
$table = CafeTable::create([
## 'table_number' => 1,
## 'qr_code' => 'table-1',
## ]);

$this->actingAs($customer);
$response = $this->postJson(route('customer.order.store'), [
'customer_name' => 'Budi',
## 'customer_phone' => '081234567890',
## 'table_id' => $table->id,
## 'items' => [['menu_id' => $menu->id, 'quantity' => 2]],
## ]);

$response->assertStatus(201);
$this->assertSame(100.0, (float) $batch->fresh()->quantity);

$this->actingAs($cashier);
$order = \App\Models\Order::find($response->json('order_id'));
## $order->update(['payment_method' => 'cash']);
$confirmResponse                    =                    $this-
## >patch(route('kasir.pesanan.konfirmasi-tunai', [
## 'order' => $order->id,
## ]));

$confirmResponse->assertStatus(200);
$this->assertSame(80.0, (float) $batch->fresh()->quantity);
$this->assertDatabaseHas('stock_movements', [
## 'ingredient_id' => $ingredient->id,
## 'quantity_change' => -20,
## ]);
## }

Ga mba r 4.25 Alur Pela ngga n ke Konfirma si Ka sir

## 56

Hasil  pengujian  membuktikan  bahwa stok  bahan  baku tidak berubah  saat
pelanggan memesan dan baru berkurang setelah kasir mengonfirmasi  pembayaran,
sesuai dengan alur bisnis  yang dirancang.
Seluruh skenario pengujian gray box menunjukkan status Berhasil. Hasil ini
membuktikan bahwa aliran data dari modul transaksi (POS kasir dan web self-order
pelanggan)  ke modul inventori  berjalan  konsisten.  Ketika stok mencukupi, sistem
berhasil  mendeduksi stok, mencatat pergerakan,  dan merekam  pemakaian  harian.
Ketika stok  tidak mencukupi,  sistem  menolak  pesanan  dan tidak mengubah  data
stok.  Pada  alur  pelanggan,  stok  baru  berkurang  setelah  kasir  mengonfirmasi
pembayaran,  yang  menunjukkan  bahwa  integrasi  antar subsistem  telah  berfungsi
sesuai perancangan.




## 57
## BAB V
## PENUTUP

## 5.1 Kesimpulan
Berdasarkan   hasil   perancangan,   implementasi,    dan  pengujian   sistem
manajemen  inventori  pada Point  of  Sale W9 Cafe menggunakan  Laravel  dan
Filament, dapat ditarik kesimpulan sebagai berikut:
- Sistem  berhasil  menerapkan  manajemen  inventori  berbasis  bahan  baku
dengan  resep  terintegrasi  yang  memungkinkan  setiap  menu  yang  terjual
memberikan  dampak langsung terhadap stok bahan baku terkait.
- Sistem  berhasil  menerapkan  prioritas  penggunaan  stok  berdasarkan  masa
kedaluwarsa  (FEFO)  dan urutan penerimaan  (FIFO) yang  meminimalkan
pemborosan bahan baku serta menjaga konsistensi data stok.
- Sistem berhasil  mencatat pemakaian bahan baku secara otomatis setiap kali
terjadi transaksi  penjualan  dan menyediakan riwayat perubahan  stok yang
dapat dilacak secara lengkap.
- Pengujian black   box terhadap  seluruh   modul   menunjukkan   skenario
berhasil. Pengujian white box memvalidasi kebenaran deduksi stok berbasis
resep,  algoritma  FIFO  dan  FEFO,  penyesuaian  stok,  serta  pembatalan
penyesuaian  stok. Pengujian gray box memvalidasi  konsistensi  aliran  data
antar modul inventori dan modul transaksi.

## 5.2 Saran
Berdasarkan hasil penelitian, terdapat beberapa saran untuk pengembangan
lebih lanjut:
- Sistem  manajemen   inventori  ini  dapat  dikembangkan  dengan  aplikasi
mobile agar  sistem  dapat diakses  dengan mudah  ketika  admin  atau  kasir
sedang tidak berada di dekat laptop atau desktop PC.
- Sistem  manajemen  inventori  ini  dapat dikembangkan dengan menerapkan
notifikasi  Stok  Menipis  dan  Kedaluwarsa  melalui   aplikasi   perpesanan
seperti  WhatsApp ketika  stok bahan  baku  berada di bawah ambang  batas

## 58

atau mendekati tanggal  kedaluwarsa agar  admin  dapat mengetahui  segera
bahan baku mana yang stoknya perlu diperbarui.
- Integrasi Barcode  Scanner untuk mempercepat dan mempermudah proses
pencatatan dan identifikasi batch stok bahan baku.

## 59

## DAFTAR PUSTAKA

[1] Sisilia  Anyel  Faridawati,  Henrikus  Herdi,  and  Paulus  Libu  Lamawitak,
“Analisis  Penerapan  Sistem  Informasi  Akuntansi  untuk  Meningkatkan
Efisiensi dan Keamanan Keuangan UMKM (Cafe Rindu Lokaria),” Jurnal
Ekonomi, Akuntansi, dan Perpajakan, vol. 1, no. 4, pp. 189–215, Aug. 2024,
doi: https://doi.org/10.61132/jeap.v1i4.443.
[2] Supron and Atang Susila, “Aplikasi Point Of Sales (POS) Berbasis Website
Dengan  Menggunakan  Laravel  :  Studi Kasus:  Bakmi  Djowo,” LOGIC  :
Jurnal  Ilmu  Komputer  dan Pendidikan,  vol.  2, no.  1, pp.  160–167, 2023,
## Available:
https://journal.mediapublikasi.id/index.php/logic/article/view/2902
[3] M. Devega, Yuhelmi Yuhelmi, and Yuvi Darmayunata,
## “PEMBANGUNAN SISTEM INVENTORI APOTEK MENGGUNAKAN
METODE FIFO DAN FEFO,” Zonasi,  vol.  6,  no.  1,  pp.  159–172,  Feb.
2024, doi: https://doi.org/10.31849/zn.v6i1.17318.
[4] R.  Al  Farisi,  A.  Ramadhan  Zayn,  B.  Agung  Nugroho,  and  A.  Heriadi,
“Implementasi  Sistem  Informasi  Akademik  Pengelolaan  Tugas  Akhir
Berbasis Laravel dan Filament,” Jurnal Sistem Informasi  Triguna Dharma
(JURSI    TGD),    vol.    4,    no.    3,    pp.    486–496,    May    2025,    doi:
https://doi.org/10.53513/jursi.v4i3.10989.
[5] P.  Garbarz  and  M.  Plechawska-Wójcik,  “Comparative  analysis  of  PHP
frameworks on the example of Laravel and Symfony,” Journal of Computer
Sciences Institute, vol. 22, pp. 18–25, Mar. 2022, doi:
https://doi.org/10.35784/jcsi.2781.
[6] D. T.  Haniva,  J.  A.  Ramadhan, and A.  Suharso,  “Systematic  Literature
## Review    Penggunaan    Metodologi    Pengembangan    Sistem    Informasi
Waterfall, Agile, dan Hybrid,” JIEET (Journal  of Information Engineering
and  Educational  Technology),  vol.  7,  no.  1,  pp.  36–42,  Jun.  2023,  doi:
https://doi.org/10.26740/jieet.v7n1.p36-42.

## 60

[7] E. Kurniawati and A. Ikhwan, “Perancangan Sistem Informasi Manajemen
## Inventaris Kontrol Stok Barang Berbasis  Web,” Jurnal  Teknologi  Sistem
Informasi   dan  Aplikasi,   vol.  6,  no.  3,  pp.  408–415,  Jul.   2023,  doi:
https://doi.org/10.32493/jtsi.v6i3.30881.
[8] PostgreSQL Global Development Group, "PostgreSQL 18.4
## Documentation," May 14, 2026. Available:
https://www.postgresql.org/docs/18/
[9] M. Devega, Yuhelmi Yuhelmi, and Yuvi Darmayunata,
## “PEMBANGUNAN SISTEM INVENTORI APOTEK MENGGUNAKAN
METODE FIFO DAN FEFO,” Zonasi,  vol.  6,  no.  1,  pp.  159–172,  Feb.
2024, doi: https://doi.org/10.31849/zn.v6i1.17318.
[10] Sudirman  Sudirman  and  Ika  Agustina,  “PENGEMBANGAN SISTEM
## POINT OF SALE (POS) BERBASIS WEB DALAM MENINGKATKAN
COSTUMER RELATIONSHIP MANAGEMENT,” Indonesian Journal of
Economy, Business, Entrepreneurship  and Finance, vol. 4, no. 1, pp. 108–
119, 2024, doi: https://doi.org/10.53067/ijebef.v4i1.142.
[11] Fried   Sinlae,   Eko   Irwanda,   Zaky   Maulana,   and   V.   E.   Syahputra,
“Penggunaan  Framework  Laravel  dalam  Membangun  Aplikasi  Website
Berbasis  PHP,” Jurnal  Siber  Multi  Disiplin ,  vol.  2,  no.  2,  pp.  119–132,
2024, doi: https://doi.org/10.38035/jsmd.v2i2.186.
[12] B. S. R. Adawiyah, S. I. Murpratiwi, and A. Manan, “Development of the
SI-FARA (Facility  and Meeting  Reservation  Information System)  Admin
Back-End  Using  the  Laravel  Filament  Framework  at  the  Diskominfo
Mataram City,” Jurnal Begawe Teknologi Informasi  (JBegaTI),  vol. 6, no.
2, Sep. 2025, doi: https://doi.org/10.29303/jbegati.v6i2.1348.
[13] S.  M.  Pulungan,  R.  Febrianti,  T.  Lestari,  N.  Gurning,  and  N.  Fitriana,
“Analisis  Teknik  Entity-Relationship    Diagram    Dalam    Perancangan
Database,” Jurnal  Ekonomi Manajemen  dan Bisnis  (JEMB),  vol.  1, no. 2,
pp. 143–147, Feb. 2023, doi: https://doi.org/10.47233/jemb.v1i2.533.
[14] Siska Narulita, Ahmad Nugroho, and M. Zakki Abdillah, “Diagram Unified
Modelling    Language    (UML)    untuk   Perancangan    Sistem    Informasi

## 61

Manajemen Penelitian dan Pengabdian Masyarakat
(SIMLITABMAS),” Bridge   :   Jurnal   publikasi   Sistem   Informasi   dan
Telekomunikasi,    vol.    2,    no.    3,    pp.    244–256,    Aug.    2024,    doi:
https://doi.org/10.62951/bridge.v2i3.174.
[15] Lailani Fitria, A. Patricia, and Raudhatul Jannah, “ANALISA PROSEDUR
## PENERAPAN    KARTU     RENCANA    STUDI     MENGGUNAKAN
FLOWCHART PADA STIE  TUAH  NEGERI KOTA  DUMAI,” Jurnal
Administrasi  Sosial  dan Humaniora, vol. 7, no. 2, pp. 143–143, Jan. 2024,
doi: https://doi.org/10.56957/jsr.v7i2.265.
[16] N. M. Jibril, None Zulrahmadi, and N. 3Muhammad Amin, “PENGUJIAN
## SISTEM   INFORMASI  E-MODUL   PADA   SMPN   1   TEMPULING
## MENGGUNAKAN BLACK  BOX  TESTING,” JURNAL  PERANGKAT
LUNAK, vol. 6, no. 2, pp. 327–332, Jun. 2024, doi:
https://doi.org/10.32520/jupel.v6i2.3326.
[17] M. Helmi and S. Fedianto, “Pengujian Sistem Jaringan Dokumentasi Dan
Informasi Menggunakan Black Box Testing Dan White Box Testing” vol.
3, no. 1, 2024. Available: https://repository.upnjatim.ac.id/id/eprint/20155
[18] [1]V. Ang, S. Rahman, and Hasniati, “Implementation of Grey Box Testing
Technique in Testing the F1Math Application,” KHARISMA Tech, vol. 20,
no. 2, pp. 15–24, Oct. 2025, doi: 10.55645/kharismatech.v20i2.575.