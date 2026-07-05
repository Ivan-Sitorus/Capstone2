





## UNIVERSITAS DIPONEGORO

## PENGEMBANGAN APLIKASI PEMANTAUAN STOK
## DENGAN METODE MIN-MAX DAN INTEGRASI
## API WHATSAPP DI KAFE BDIM

## TUGAS AKHIR
Diajukan sebagai salah satu syarat untuk memperoleh gelar
## Sarjana Teknik

## RENDY HARTONO PUTRA
## 21120121130071

## DEPARTEMEN TEKNIK KOMPUTER
## FAKULTAS TEKNIK
## SEMARANG
## 2025



ii

## HALAMAN PENGESAHAN
Tugas Akhir ini diajukan oleh
## Nama : Rendy Hartono Putra
## NIM : 21120121130071
Jurusan/Program Studi : Teknik Komputer
Judul Tugas Akhir : Pengembangan Aplikasi Pemantauan Stok dengan
Metode Min-Max dan Integrasi API WhatsApp di
Kafe B.di.M

Telah berhasil dipertahankan di hadapan Tim Penguji dan diterima sebagai bagian
dari persyaratan yang diperlukan untuk memperoleh gelar Sarjana Teknik pada
## Departemen Teknik Komputer, Fakultas Teknik, Universitas Diponegoro.

## TIM PENGUJI
Pembimbing I  : Rinta Kridalukmana, S.Kom., M.T., Ph.D.   (  )
Pembimbing II  : Ilmam Fauzi Hashbil Alim, S.T., M.Kom.    (  )
Ketua Penguji  : Prof. Dr. Adian Fatchur Rochim, S.T., M.T. (  )
Anggota Penguji : Prof. Dr. Ir. R. Rizal Isnanto, S.T., M.M., M.T., IPU,
ASEAN Eng.            (  )
## Semarang, 16 Juni 2025
## Ketua Departemen Teknik Komputer



Dr. Oky Dwi Nurhayati S.T., M.T.
## NIP. 197910022009122001



iii



## HALAMAN PERNYATAAN ORISINALITAS















Tugas Akhir ini adalah hasil karya saya sendiri,
dan semua sumber baik yang dikutip maupun yang dirujuk
telah saya nyatakan dengan benar.











## Nama : Rendy Hartono Putra
## NIM : 21120121130071
## Tanda Tangan :


## Tanggal : Semarang, 23 Februari 2025




iv

## HALAMAN PERNYATAAN PERSETUJUAN PUBLIKASI
## TUGAS AKHIR UNTUK KEPENTINGAN AKADEMIS
Sebagai sivitas akademika Universitas Diponegoro, saya yang bertanda tangan di
bawah ini:

## Nama    : Rendy Hartono Putra
## NIM    : 21120121130071
Jurusan/Program Studi  : Teknik Komputer
## Fakultas    : Teknik
## Jenis Karya   : Tugas Akhir

demi  pengembangan  ilmu  pengetahuan,  menyetujui  untuk  memberikan  kepada
Universitas   Diponegoro Hak   Bebas   Royalti   Noneksklusif (None-exclusive
Royalty Free Right) atas ilmiah saya yang berjudul:

Pengembangan Aplikasi Pemantauan Stok dengan Metode Min-Max dan
Integrasi API WhatsApp di Kafe BdiM


beserta     perangkat     yang     ada     (jika     diperlukan).     Dengan     Hak     Bebas
Royalti/Noneksklusif ini Universitas berhak menyimpan,
mengalihmedia/formatkan,  mengelola  dalam  bentuk  pangkalan  data  (database),
merawat dan memublikasikan tugas akhir saya selama tetap mencantumkan nama
saya sebagai penulis/pencipta dan sebagai pemilik Hak Cipta.

Demikian pernyataan ini saya buat dengan sebenarnya.


Dibuat di : Semarang
## Pada Tanggal : 10 Februari 2025


Yang menyatakan,




## Rendy Hartono Putra




v

## KATA PENGANTAR
Dengan  mengucapkan  puji  dan  syukur  kepada  Tuhan  Yang  Maha  Esa,
karena berkat rahmat dan hidayah-Nya, penulis dapat menyelesaikan laporan Tugas
Akhir ini yang berjudul “Pengembangan  Aplikasi  Pemantauan  Stok  dengan
Metode Min-Max dan Integrasi API WhatsApp di Kafe BdiM” dengan baik.
Tugas  Akhir  merupakan  suatu  kewajiban  yang  harus  dilaksanakan  untuk
memenuhi  salah  satu  syarat  kelulusan  dalam  mencapai  Sarjana  Teknik  pada
## Program Studi S1 Teknik Komputer, Fakultas Teknik, Universitas Diponegoro.
Dalam   penyusunan   Tugas   Akhir   ini   penulis   senantiasa   mendapatkan
dukungan, bimbingan, doa, bantuan, serta arahan dari berbagai pihak. Oleh karena
itu, penulis bermaksud ingin menyampaikan rasa terima kasih kepada:
- Bapak Rinta Kridalukmana, S.Kom., M.T., Ph.D. selaku dosen pembimbing
I  atas  arahan  dan  bimbingannya  dalam pembuatan  aplikasi dan  penulisan
laporan Tugas Akhir.
- Bapak Ilmam Fauzi Hashbil Alim, S.T., M.Kom. selaku dosen pembimbing
II  yang  telah  memberikan  saran  serta  bimbingan  dalam  pengerjaan  dan
penulisan Tugas Akhir.
- Dr.  Oky  Dwi  Nurhayati,  S.T.,  M.T.  selaku  Ketua  Departemen  Teknik
## Komputer Universitas Diponegoro.
- Seluruh    jajaran    dosen    Departemen    Teknik    Komputer    Universitas
Diponegoro yang telah memberikan ilmunya kepada seluruh mahasiswa.
- Bibi Suryani dan paman  Musarodin  atas  dukungan  tempat  tinggal  dan
material selama menjalani perkuliahan dan bimbingan Tugas Akhir.
- Ayah,  ibu,  Hardi,  Nindy,  dan  seluruh  keluarga  atas  segala  doa  dan
dukungannya yang tidak terhitung.
- Kelompok 3 Capstone Siklus 2 2024 yaitu Abida Amalia Syifa dan Maritza
Septiarini  yang  telah  membantu  dan  bekerja  sama  dalam  menyelesaikan
proyek Tugas Akhir.
- Seluruh   keluarga   besar   Al-Muharrik   Teknik   Komputer   yang   telah
membersamai perjalanan penulis selama di Universitas Diponegoro.



vi

- Seluruh  keluarga  besar  Izzati  FT  Universitas  Diponegoro  yang  telah
membersamai perjalanan penulis selama di Universitas Diponegoro.
- Keluarga  Teknik  Komputer,  khususnya  Angkatan  2021  yang  senantiasa
memberikan dukungannya.
- Staf  Tata  Usaha  Departemen  Teknik  Komputer  yang  telah  menjalankan
seluruh tugasnya dengan baik.
- Serta semua pihak yang tidak dapat penulis sebutkan satu persatu yang telah
membantu hingga terselesaikannya Tugas Akhir ini.
Penulis    menuangkan    pemahaman    dan    pengetahuan    penulis    dalam
pembuatan  Laporan  Tugas  Akhir  ini.  Namun,  penulis  sangat  menyadari  bahwa
segala  kemampuan  dan  ilmu  pengetahuan  yang  dimiliki  masih  sangat  kurang,
sehingga Laporan Tugas Akhir ini masih jauh dari kata sempurna. Kritik dan saran
sangat  diharapkan  demi  sempurnanya  Laporan  Tugas  Akhir  ini.  Penulis  juga
berharap  dengan  adanya  Tugas  Akhir  yang  telah  diselesaikan  ini  bisa  dijadikan
suatu media pembelajaran atau minimal dijadikan bahan referensi untuk karya tulis
lainnya.   Melalui   kesadaran,   penulis   memohon   maaf   jika   terdapat   banyak
kekurangan pada Tugas Akhir ini. Akhir kata penulis mengucapkan terima kasih.

## Semarang, 20 Februari 2025


## Penulis




vii

## DAFTAR ISI

COVER  ................................................................................................................... i
HALAMAN PENGESAHAN ................................................................................. ii
HALAMAN PERNYATAAN ORISINALITAS ................................................... iii
## HALAMAN PERNYATAAN PERSETUJUAN PUBLIKASI TUGAS AKHIR
UNTUK KEPENTINGAN AKADEMIS .............................................................. iv
KATA PENGANTAR ............................................................................................ v
DAFTAR ISI ......................................................................................................... vii
DAFTAR GAMBAR ............................................................................................. xi
DAFTAR TABEL ................................................................................................ xiii
DAFTAR RUMUS .............................................................................................. xiv
ABSTRAK ............................................................................................................ xv
ABSTRACT ......................................................................................................... xvi
BAB I PENDAHULUAN ....................................................................................... 1
1.1 Latar Belakang ........................................................................................ 1
1.2 Rumusan Masalah ................................................................................... 3
1.3 Batasan Masalah...................................................................................... 3
1.4 Tujuan Penelitian .................................................................................... 3
1.5 Manfaat Penelitian .................................................................................. 4
1.6 Metodologi Penelitian ............................................................................. 4
1.7 Sistematika Penelitian ............................................................................. 6
BAB II KAJIAN PUSTAKA .................................................................................. 7
2.1 Penelitian Terdahulu ............................................................................... 7
2.2 Landasan Teori ...................................................................................... 11
2.2.1 Metode Agile ..................................................................................... 11
2.2.2 Stock Opname.................................................................................... 12
2.2.3 Sistem Pemantauan ........................................................................... 13



viii

2.2.4 Fonnte ................................................................................................ 13
2.2.5 Reorder Point .................................................................................... 14
2.2.6 Metode Min-Max ............................................................................... 14
2.2.7 Visual Studio Code ........................................................................... 15
2.2.8 Figma ................................................................................................ 15
2.2.9 XAMPP ............................................................................................. 15
BAB III PERANCANGAN SISTEM ................................................................... 16
3.1 Gambaran Umum Sistem ...................................................................... 16
3.1.1 Fungsi Utama Produk ........................................................................ 17
3.1.2 Batasan Sistem .................................................................................. 18
3.2 Lingkungan Pengembangan Sistem ...................................................... 18
3.2.1 Lingkungan Pengembangan .............................................................. 18
3.2.2 Lingkungan Operasional ................................................................... 19
3.3 Kebutuhan ............................................................................................. 20
3.3.1 Antarmuka Eksternal ......................................................................... 20
3.3.1.1 Antarmuka Perangkat Keras ..................................................... 20
3.3.1.2 Antarmuka Perangkat Lunak..................................................... 21
3.3.1.3 Antarmuka Komunikasi ............................................................ 21
3.3.2 Deskripsi Fungsional ......................................................................... 22
3.3.2.1 Diagram Use Case ..................................................................... 22
3.3.2.2 Diagram Kelas MVC................................................................. 23
3.3.2.3 Sequence Digram ...................................................................... 24
3.3.2.4 Activity Diagram ....................................................................... 28
3.3.2.5 State Diagram ............................................................................ 29
3.3.2.6 Deployment Diagram ................................................................ 31



ix

3.3.3 Kebutuhan Fungsional ...................................................................... 31
3.3.4 Kebutuhan Non-fungsional ............................................................... 33
3.4 Desain Produk ....................................................................................... 34
3.4.1 Arsitektur Sistem ............................................................................... 34
3.4.2 Desain Detail Sistem ......................................................................... 37
3.4.2.1 Deskripsi Data ........................................................................... 37
3.4.3 Standar-standar yang Dipergunakan ................................................. 38
3.5 Perhitungan Metode Min-Max .............................................................. 39
3.6 Metode Pengujian.................................................................................. 42
3.6.1 Pengujian Penerimaan Pengguna ...................................................... 42
3.6.2 Pengujian Blackbox .......................................................................... 42
BAB IV HASIL DAN PEMBAHASAN .............................................................. 43
4.1 Implementasi ......................................................................................... 43
4.1.1 Implementasi Produk ........................................................................ 43
4.1.2 Tampilan Produk ............................................................................... 43
4.1.2.1 Halaman Home.......................................................................... 45
4.1.2.2 Halaman Login .......................................................................... 45
4.1.2.3 Halaman Dasbor Staf ................................................................ 46
4.1.2.4 Halaman Setting Profil .............................................................. 48
4.1.2.5 Halaman Setting API Notifikasi ............................................... 49
4.1.2.6 Halaman Ubah Password .......................................................... 51
4.2 Pengujian ............................................................................................... 52
4.2.1 Pengujian Penerimaan Pengguna ...................................................... 52
4.2.2 Pengujian Black Box ......................................................................... 54
4.3 Pembahasan Ketercapaian Tujuan Penelitian ....................................... 62



x

BAB V PENUTUP ................................................................................................ 67
5.1 Kesimpulan ........................................................................................... 67
5.2 Saran ...................................................................................................... 68
DAFTAR PUSTAKA ........................................................................................... 69
BIODATA MAHASISWA ................................................................................... 71




xi

## DAFTAR GAMBAR
Gambar 2. 1 Tahapan metodologi agile ................................................................ 11

Gambar 3. 1 Use case diagram dari sisi integrasi dengan API WhatsApp Fonnte 22
Gambar 3. 2 Diagram Kelas MVC ........................................................................ 23
Gambar 3. 3 Sequence diagram LoginUser .......................................................... 24
Gambar 3. 4 Sequence diagram BuatPelaporan .................................................... 25
Gambar 3. 5 Sequence diagram UbahProfil .......................................................... 26
Gambar 3. 6 Sequence diagram SetAPIToken ...................................................... 26
Gambar 3. 7 Sequence diagram HubungkanAPI .................................................. 27
Gambar 3. 8 Sequence diagram PutuskanHubunganAPI ...................................... 27
Gambar 3. 9 Activity diagram ............................................................................... 28
Gambar 3. 10 Kondisi pelaporan oleh staf ............................................................ 29
Gambar 3. 11 Kondisi stok menipis oleh sistem ................................................... 30
Gambar 3. 12 Deployment Diagram ..................................................................... 31
Gambar 3. 13 Arsitektur Sistem ............................................................................ 34

Gambar 4. 1 Halaman Home ................................................................................. 45
Gambar 4. 2 Halaman Login ................................................................................. 45
Gambar 4. 3 Halaman Dasbor (staf) ..................................................................... 46
Gambar 4. 4 Modal ”report” kepada atasan .......................................................... 46
Gambar 4. 5 Modal “report” ketika memilih ”lainnya” ........................................ 47
Gambar 4. 6 Isi pesan notifikasi yang dikirim ke owner atau manajer yang sudah
terhubung API notifikasi .................................................................. 47
Gambar 4. 7 Halaman Setting → Profile .............................................................. 48
Gambar 4. 8 Modal edit profil............................................................................... 48
Gambar 4. 9 Halaman atur token API Fonnte WhatsApp ..................................... 49
Gambar 4. 10 Modal edit token API ..................................................................... 49
Gambar 4. 11 Halaman pengambilan token API dari Fonnte ............................... 50
Gambar 4. 12 Halaman Setting → API Notifikasi ................................................ 50



xii

Gambar 4. 13 Hasil ketika tombol ”Hubungkan kembali” ditekan ...................... 51
Gambar 4. 14 Halaman Setting → Ubah Password .............................................. 51
Gambar 4. 15 Mengambil data kebutuhan (terpakai di aplikasi) .......................... 62
Gambar 4. 16 Memasukkan nilai ambang batas minimum ke sistem oleh manajer
......................................................................................................... 66
Gambar 4. 17 Pesan notifikasi stok menipis di WhatsApp ................................... 66





xiii

## DAFTAR TABEL

Tabel 2. 1 Penelitian terdahulu................................................................................ 9

Tabel 3. 1 Lingkungan pengembangan sistem ...................................................... 18
Tabel 3. 2 Spesifikasi perangkat lunak ................................................................. 19
Tabel 3. 3 Antarmuka perangkat keras ................................................................. 20
Tabel 3. 4 Tabel kebutuhan fungsional ................................................................. 32
Tabel 3. 5 Kebutuhan non-fungsional ................................................................... 33
Tabel 3. 6 Alur koneksi antar komponen .............................................................. 36
Tabel 3. 7 Tabel User ............................................................................................ 37
Tabel 3. 8 Tabel SetApiToken .............................................................................. 38
Tabel 3. 9 Tabel UserProfile ................................................................................. 38

Tabel 4. 1 Perbedaan halaman yang dapat diakses untuk masing-masing role .... 43
Tabel 4. 2 Perbedaan fitur utama dari masing-masing role .................................. 44
Tabel 4. 3 Skala penilaian Likert .......................................................................... 52
Tabel 4. 4 Daftar pertanyaan dan hasil pengujian UAT........................................ 52
Tabel 4. 5 Pengujian fungsional melalui metode black box ................................. 54
Tabel 4. 6 Hasil pengujian halaman login ............................................................. 56
Tabel 4. 7 Hasil pengujian halaman dasbor .......................................................... 57
Tabel 4. 8 Hasil pengujian halaman setting profil ................................................ 58
Tabel 4. 9 Hasil pengujian halaman setting token API Fonnte ............................. 59
Tabel 4. 10 Hasil pengujian halaman sambung atau putuskan API notifikasi ...... 60
Tabel 4. 11 Hasil pengujian halaman ubah password ........................................... 61
Tabel 4. 12 Kebutuhan data .................................................................................. 63




xiv

## DAFTAR RUMUS

Persamaan (3.1) ..................................................................................................... 40
Persamaan (3.2) ..................................................................................................... 40
Persamaan (3.3) ..................................................................................................... 40
Persamaan (3.4) ..................................................................................................... 41
Persamaan (3.5) ..................................................................................................... 41

Persamaan (4.1) ..................................................................................................... 64
Persamaan (4.2) ..................................................................................................... 64
Persamaan (4.3) ..................................................................................................... 64
Persamaan (4.4) ..................................................................................................... 64
Persamaan (4.5) ..................................................................................................... 64
Persamaan (4.6) ..................................................................................................... 65
Persamaan (4.7) ..................................................................................................... 65
Persamaan (4.8) ..................................................................................................... 65





xv


## ABSTRAK
Sektor Food & Beverage (F&B), terutama kafe dan restoran, sering menghadapi tantangan
dalam pengelolaan stok barang dan bahan baku. Metode konvensional seperti pencatatan manual
dapat  menyebabkan  masalah kesulitan  pelacakan  stok  menipis,  kesalahan  perhitungan  transaksi,
serta kesulitan dalam melihat riwayat pembelian dan pemakaian. Kafe B.di.M mengalami masalah
serupa.  Pencatatan  masih dilakukan  secara  manual  dengan  menggunakan  formulir  kertas  dalam
kegiatan stock opname. Berdasarkan masalah tersebut, dikembangkanlah aplikasi pemantauan stok
di  kafe  B.di.M  sebagai  solusi  untuk  mempermudah  pengelolaan  stok  barang  dan  bahan  serta
peringatan dini terjadinya stok menipis dengan metode min-max dan integrasi API WhatsApp.
Metode  penelitian dilakukan  dengan  mengunjungi  Kafe  B.di.M  kemudian  bertanya
mengenai proses bisnis kafe. Setelah diketahui proses bisnis kafe kemudian dikembangkanlah sistem
yang mampu membantu pengelolaan kafe. Evaluasi dan pengembangan selalu dilakukan sebagai
tindakan pengoptimalan sistem.
Sistem  pemantauan  stok  di  Kafe  B.di.M  berhasil  dirancan  untuk  pengelolaan  stok  yang
efektif,  termasuk  peringatan  dini  melalui  WhatsApp.  Pengguna  menunjukkan  penerimaan  100%,
meskipun pelatihan awal diperlukan. Perhitungan min-max membantu menentukan ambang batas
stok dan  kebutuhan  pembelian.  Sistem  ini  berhasil  mendigitalkan  proses  inventaris  konvensional
dan meningkat aksesibilitas dan efisiensi bagi staf, manajer, dan pemilik kafe.

Kata kunci: pemantauan stok; notifikasi; metode min-max; website.




xvi

## ABSTRACT
The Food & Beverage (F&B)  sector,  especially  cafes  and  restaurants,  often  faces
challenges in managing inventory and materials. Conventional methods such as manual recording
can  cause  problems  such  as  difficulty  in  tracking  low  stock  levels,  human  error  in  calculate
transactions, and difficulty in viewing purchase and usage history. Café B.di.M experienced similar
problems. Inventory counts are still conducted manually using paper forms. Based on these issues,
an  inventory  monitoring  application  was  developed  for  Café  B.di.M  as  a  solution  to  simplify
inventory management and provide early warnings of low stock levels using the min-max method
and WhatsApp API integration.
The research method involved visiting Café B.di.M and inquiring about the café’s business
processes. After understanding the café’s business processes, a system was developed to assist in
managing  the  café.  Evaluation  and  development  are  continuously  carried  out  as  part  of  system
optimization efforts.
A  stock  monitoring  system  at  Café  B.di.M  was  successfully  designed  for  effective  stock
management,  including  early  warning  via  WhatsApp.  Users  showed  100%  acceptance,  although
initial training was required. Min-max calculation helps determine stock thresholds and purchasing
needs.   The   system   successfully   digitized   conventional   inventory   processes   and   increased
accessibility and efficiency for staff, manager, and café owner.

Keywords: stock monitoring; notifications; min-max method; website.


## 1

## BAB I
## PENDAHULUAN

## 1.1 Latar Belakang
Sektor Food & Beverage (F&B),  terutama  kafe  dan  restoran,  sering
menghadapi  tantangan  dalam  pengelolaan persediaan barang  dan  bahan  baku.
Metode  konvensional  seperti  pencatatan  manual  dapat  menyebabkan  berbagai
masalah  seperti  kesulitan  pelacakan persediaan menipis,  kesalahan  perhitungan
bahan terpakai dari transaksi, serta kesulitan dalam melihat riwayat pembelian dan
pemakaian barang  dan bahan.  Dampak  dari  masalah  ini  bisa  berupa  kehilangan
pendapatan  karena  ketidaktersediaan  menu,  ketidaksesuaian  data  stok  riil  dengan
catatan,  hingga  penurunan  kepuasan  pelanggan  dan  daya  saing  bisnis. Selain  itu,
pengelolaan manual juga memakan waktu dan rentan terhadap kesalahan manusia,
yang dapat mengganggu operasional harian.
Kafe   B.di.M   di   Tembalang,   Semarang,   mengalami   masalah   serupa.
Dengan  volume  pelanggan  yang  terus  meningkat,  kafe  ini  masih  mengandalkan
pencatatan stok bahan di bar dan dapur secara manual menggunakan formulir kertas
setiap harinya setelah jam operasional kafe sebagai kegiatan stock opname. Proses
ini  dilakukan  oleh  karyawan  kafe  dan  menyulitkan  pemilik  serta  pengelola
inventaris  untuk  mendapatkan  laporan  stok  yang  akurat  dan  tepat  waktu.  Hal  ini
tidak  hanya  meningkatkan  risiko  kesalahan input dan  perhitungan,  tetapi  juga
menghambat  kemampuan  pemilik  dan  pengelola  inventaris  dalam  mengambil
keputusan  cepat  mengenai  penambahan  stok,  sehingga  berpotensi  menyebabkan
ketidaktersediaan menu dan bahan terbuang yang tidak terlacak.


## 2


Perkembangan  teknologi  informasi  menawarkan  solusi  efektif  untuk
mengatasi  masalah  manajemen  stok  ini.  Sistem  pemantauan  berbasis  situs web
memungkinkan  otomasi,  kemudahan  akses  informasi,  dan  pemantauan  secara
waktu-nyata. Pemantauan  secara  umum  berarti  memeriksa,  melacak,  maupun
pengontrolan suatu proses [1]. Situs web merupakan sebuah laman yang tersedia di
server yang  kemudian  dapat  diakses  melalui  jaringan  internet  untuk  memperoleh
informasi atau konten tertentu [2].
Berdasarkan  kebutuhan  ini,  dikembangkanlah  aplikasi pemantauan  stok
bernama ”BdiM’s Stock”. Aplikasi  adalah  perangkat  lunak  yang  bersifat  spesifik
dan  biasanya  digunakan  yang  bersifat  spesifik,  dan  biasanya  digunakan  untuk
membantu   pekerjaan   di   berbagai   bidang [3]. Aplikasi   ini   dirancang   untuk
memantau  inventaris  barang  dan  bahan  secara  waktu-nyata,  menyimpan  riwayat
pembelian  dan  transaksi  harian,  mendeteksi  ketidaksesuaian  data  antara  sistem
dengan kondisi riil, dan memudahkan karyawan melaporkan kerusakan barang atau
informasi penting lainnya.
Solusi  yang  dikembangkan  adalah  integrasi  dengan  API  WhatsApp
(Fonnte).  Sistem  ini  menggunakan  metode min-max sehingga  memungkinkan
pengaturan ambang batas minimum stok untuk setiap barang dan bahan. Ketika stok
telah jatuh di bawah ambang batas ini, notifikasi peringatan akan dikirimkan secara
otomatis  melalui  aplikasi  whatsapp  kepada  pemilik  dan  pengelola  inventaris.
Dengan begitu, informasi krusial dapat diterima dengan cepat dan memungkinkan
tindakan korektif segera.
Aplikasi     pemantauan     kafe     yang     dibuat menggunakan     bahasa
pemrograman PHP dan kerangka kerja Laravel dan Bootstrap. Proses kerja aplikasi
dimulai  dengan  pengguna  berinteraksi  dengan  halaman  untuk  mengelola  stok
barang, stok bahan, daftar menu, dan transaksi. Dengan adanya solusi di atas, Kafe
B.di.M   diharapkan   mampu   meningkatkan   operasional   kafe   serta   mampu
mengambil keputusan terkait harga menu dari laporan inventaris.



## 3


## 1.2 Rumusan Masalah
Berdasarkan latar belakang di atas, masalah yang dapat dirumuskan adalah
sebagai berikut:
- Bagaimana  merancang  dan  mengembangkan  sistem  pemantauan  sehingga
proses pemantauan stok barang dan bahan kafe dapat berjalan dengan efektif.
- Bagaimana  cara  pemilik  (owner)  dan  manajer  kafe dapat segera  mengetahui
jika terjadi barang rusak atau stok menipis.

## 1.3 Batasan Masalah
Dalam penulisan Tugas Akhir ini, agar pembahasan hanya berfokus pada
permasalahan  utama  dan  tidak  melebar  ke  topik-topik  lainnya,  maka  dibatasi
masalah sebagai berikut:
- Penelitian ini hanya berfokus pada pengembangan aplikasi pemantauan stok di
Kafe B.di.M yang terkait dengan integrasi API whatsapp dan metode min-max.
- Sistem pemantauan ini hanya mampu memberikan data secara waktu-nyata di
stok  bahan  jika  staf  atau  manajer telah menambahkan  data  transaksi melalui
fitur impor excel dari POS (Point of Sales).
- Integrasi sistem pemantauan stok dengan API  WhatsApp hanya  mencakup
informasi pelaporan barang rusak dan notifikasi stok menipis.
- Parameter yang diukur oleh sistem adalah stok barang (mencakup barang awal,
masuk, dan keluar), stok bahan (mencakup bahan awal, masuk, terpakai, sisa,
dan  sisa  sebenarnya),  menu beserta komposisinya,  dan  data  transaksi  kafe
untuk menghitung bahan terpakai.

## 1.4 Tujuan Penelitian
Berdasarkan   rumusan   masalah   yang   ada,   dapat   dijabarkan   tujuan
penelitian  ini  adalah  Pengembangan  Aplikasi  Pemantauan  Stok  di  Kafe  B.di.M
sebagai  solusi  untuk  mempermudah  pengelolaan stok barang  dan  bahan serta
peringatan dini terjadinya stok menipis dengan metode min-max dan integrasi API
WhatsApp.

## 4


## 1.5 Manfaat Penelitian
Manfaat penelitian bagi Penulis adalah sebagai berikut:
- Dapat menerapkan ilmu pengetahuan yang sudah dipelajari selama perkuliahan
di Departemen Teknik Komputer Universitas Diponegoro.
- Dapat  mengembangkan  ilmu  pengetahuan  yang  sudah  dipelajari  sehingga
dapat menghasilkan sesuatu yang bermanfaat.
- Dapat   menambah   pengalaman   penulis   dalam   mengembangkan   aplikasi
berbasis web dan pengintegrasian dengan API WhatsApp.
- Dapat   menambah   pengalaman   penulis   dalam   melakukan   pengujian   dan
evaluasi sistem yang dikembangkan.

Sementara  itu,  manfaat  penelitian  untuk  pengelola  kafe  di  kafe  B.di.M
adalah sebagai berikut:
- Memberikan    solusi untuk pemantauan    stok menggunakan metode
konvensional yang dilakukan oleh pengelola sebelumnya.
- Memberikan  solusi  dalam  meningkatkan  operasional  kafe  dengan  proses
pemantauan stok barang dan bahan secara waktu-nyata.
- Memberikan kemudahan dalam membandingkan stok pada sistem dengan stok
yang ada di gudang.

## 1.6 Metodologi Penelitian
Terdapat  beberapa  tahapan  dalam  pelaksanaan  tugas  akhir  ini.  Berikut
merupakan tahap-tahap dalam pembuatan tugas akhir.
## 1. Studi Literatur
Tahap  studi  literatur  merupakan  tahap  pengumpulan  informasi  yang
diperlukan  untuk  pengerjaan  tugas  akhir  sekaligus  mempelajarinya.  Tahapan  ini
dimulai  dari  pengumpulan  literatur,  diskusi,  serta  pemahaman  topik  tugas  akhir
antara lain memahami proses bisnis kafe, mengolah proses bisnis saat ini menjadi
kebutuhan sistem, perancangan basis data, desain antarmuka yang mudah dan baik,
dan integrasi dengan API WhatsApp.

## 5


- Perancangan dan Desain Sistem
Tahap  perancangan  sistem  dilakukan  berdasarkan  data-data  yang  telah
diperoleh dari tahap studi literatur. Pada tahap ini digambarkan dan dideskripsikan
secara jelas sistem yang  diusulkan dan dilakukan perancangan proses bisnis baru
pada sistem yang dikembangkan, desain sistem, diagram, dan kebutuhan sistem.

## 3. Implementasi
Pada tahap ini dilakukan implementasi rancangan sistem yang sebelumnya
telah dibuat. Tahapan ini merupakan tahap realisasi dari tahapan sebelumnya untuk
menjadi aplikasi yang siap disimulasikan dan sudah sesuai dengan perancangan.

- Uji Coba dan Evaluasi
Pada tahap ini sistem pemantauan stok yang telah selesai dikembangkan
dan siap untuk di uji. Pengujian dan evaluasi dilakukan dengan melihat kesesuaian
dan ketepatan sistem dalam mengelola data yang di-input-kan pengguna, mengolah
data  stok,  notifikasi  yang  didapat  serta pelaporan  barang  rusak,  keluaran  laporan
stok bahan dalam  excel,  dan  keluaran  laporan  stok  barang  dalam  pdf  dan  word
(.docx).  Selain  itu, dilakukan juga  pengujian  fungsional  aplikasi  yang  dilakukan
oleh  pemilik,  pengelola  inventaris,  dan karyawan kafe  untuk  memastikan  fungsi
dari masing-masing fitur berfungsi sesuai dengan kebutuhan.

## 5. Penyusunan Laporan Tugas Akhir
Pada tahap ini disusun laporan tugas akhir sebagai dokumen pelaksanaan
tugas  akhir  yang  mencakup  seluruh  konsep,  teori,  implementasi,  dan  hasil  yang
dikerjakan.


## 6


## 1.7 Sistematika Penelitian
Untuk memberikan gambaran mengenai isi laporan tugas akhir ini, secara
singkat dapat diuraikan melalui sistematika penulisan. Sistematika penulisan tugas
akhir ini terdiri atas lima bab dengan susunan sebagai berikut.
## BAB I PENDAHULUAN
Bab  ini  berisikan  latar  belakang,  rumusan  masalah,  batasan  masalah,  tujuan
penelitian, manfaat penelitian, metodologi penelitian, dan sistematika penelitian.

## BAB II KAJIAN PUSTAKA
Bab ini berisikan teori yang menjadi dasar peneliti dalam merancang sistem. Kajian
pustaka terdiri dari penelitian terdahulu serta landasan teori yang membantu dalam
mengembangkan Aplikasi Pemantauan Stok di Kafe B.di.M berbasis laman web.

## BAB III PERANCANGAN SISTEM
Bab   perancangan   sistem   berisikan   tentang   bagaimana   proses   kerja   aplikasi
pemantauan stok menggunakan proses design thinking yaitu mencari tahu masalah
serta   kebutuhan   pengguna,   menganalisis   dan   merancang   kebutuhan   sistem,
mengembangkan  aplikasi,  dan  pengujian.  Bab  ini  berisi  gambaran  umum  sistem,
lingkungan   pengembangan   sistem,   kebutuhan,   desain   produk,   dan   metode
pengujian.

## BAB IV IMPLEMENTASI DAN PENGUJIAN
Bab ini menjelaskan tentang bagaimana Aplikasi Pemantauan Stok di Kafe B.di.M
diimplementasikan.  Pada  bab  ini  juga  berisikan  pengujian  dari  sistem  dengan
metode pengujian  penerimaan  pengguna,  pengujian min-max,  dan pengujian
blackbox.

## BAB V PENUTUP
Bab  ini  berisikan  kesimpulan  dari  hasil  implementasi  serta  pengujian  yang  telah
dilakukan. Selain itu, terdapat saran-saran yang dapat digunakan untuk penelitian
dan pengembangan sistem selanjutnya.


## 7

## BAB II
## KAJIAN PUSTAKA

## 2.1 Penelitian Terdahulu
Penelitian  yang  dilakukan  tidak  dapat  dipisahkan  dari  kajian  penelitian
sebelumnya yang telah dilaksanakan sebagai dasar acuan dan pembanding dalam
penelitian.  Studi  penelitian  sebelumnya  merujuk  pada  penelitian-penelitian  yang
sudah  dilakukan  dan  digunakan  sebagai  referensi  untuk  menerapkan  sistem
pemantauan stok di Kafe B.di.M.
Syafaat  &  Fitrani dkk [4] meneliti penambahan fitur  notifikasi  yang
terintegrasi   dengan chatbot telegram   pada   aplikasi   pencatatan   stok   barang.
Penelitian  ini  dilakukan dengan mencoba  pelaporan  stok  barang  masuk  lewat
aplikasi  Telegram.  Saat  admin  menekan  tombol Button  Stok  Level atau Stok
Habis akan  memunculkan  data  saat  ini  atau  apabila  stok  habis  akan  memberi
notifikasi stok barang habis di Telegram.
Afriansyah & Annisa [5] mengintegrasikan  antara  admin  dan  kasir  pada
aplikasi  “Sistem  Inventory  Askha  Jaya”.   Penelitian   ini   membuat   aplikasi
pemantauan  dengan  2 role yaitu  admin  dan  kasir. Admin  dapat  menambah  dan
mengubah   data,   sedangkan   kasir   hanya   menambah   dan   mengubah   data
penjualannya.  Aplikasi  ini  memiliki sidebar dan  tampilan  pilih  menu  di  dasbor
yang dapat digunakan untuk berpindah halaman. Sistem id barang yang digunakan
yaitu AJ-XXX (AJ untuk kode awal Askha Jaya). Selain menghitung barang masuk
atau keluar, terdapat pula barang terjual untuk menghitung penjualan usaha Askha
## Jaya.


## 8


Saputra dkk [6] meneliti aplikasi pemantauan stok yang mampu memantau
stok  secara  waktu-nyata.  Terdapat  2 role yaitu  admin  dan  pegawai.  Admin  dapat
melakukan semua fungsi, sedangkan pegawai hanya terbatas melihat kecuali fungsi
untuk mengelola transaksi keluar atau masuk barang, serta dapat mencetak laporan
penjualan  keseluruhan. Halaman  dasbor  memiliki  pesan  selamat  datang  dan user
yang login, lalu terdapat informasi berapa jumlah supplier, barang, total stok barang,
dan total barang terjual. Untuk desain basis data, dari user terhubung ke penjualan
dan pembelian, kemudian masing-masing penjualan dan pembelian terdapat detail.
Putra dkk [7] meneliti  sistem pemantauan  stok  untuk  PT  Sucofindo
Samarinda  Branch.  Penelitian  ini  menerapkan Add  Category misalnya  untuk
pengkategorian  barang   yang  dapat  dikonsumsi  dan  tidak  dapat  dikonsumsi.
Aplikasi juga sudah menerapkan halaman edit profil pribadi yang sedang login serta
ganti password.
Fitriana dkk [8] mengembangkan aplikasi pengelolaan stok gudang di Raja
Vapor  Gebog. Aplikasi digunakan untuk menghitung barang misalnya “GRAPE
OKINAWA 15ML” yang berarti barang sudah dalam satu kemasan, tidak terbagi
menjadi  bahan.  Terdapat  grafik  pada  dasbor  kemudian  terdapat  laporan  analisis
ABC  (Activity-Based  Costing)  yaitu  pemberian  nilai  persentase  pada  barang
menjadi 3 kategori.
Rachmawati  &  Lentari [9] meneliti penerapan  metode min-max untuk
pengendalian persediaan bahan baku di sebuah perusahaan manufaktur spare parts
LPG.  Tujuan  utamanya  adalah  meminimalkan  terjadinya overstock (kelebihan
persediaan).  Metode  ini  melibatkan  penentuan  beberapa  parameter  kunci,  yaitu
safety  stock,  stok  minimum  (titik  pemesanan  ulang),  stok  maksimum,  jumlah
pemesanan, dan reorder level. Hasil penelitian menunjukkan bahwa metode min-
max efektif dalam mengelola persediaan.
Dari  penelitian  terdahulu  dapat  menjadi  referensi  untuk  pengembangan
pada aplikasi pemantauan stok di kafe B.di.M. Perbandingan tujuan, metode, dan
kesimpulan  antara keenam jurnal  yang  menjadi  referensi  dan  dasar  penelitian
dirangkum kemudian disajikan pada Tabel 2.1.



## 9


Tabel 2. 1 Penelitian terdahulu
## Penulis
(Tahun)
## Tujuan Metodologi Penelitian Kesimpulan
## Dany Syafaat,
## Arif Senja Fitrani
## (2024)
Mengembangkan sistem
pemantauan   stok   barang   yang
terintegrasi dengan chatbot
telegram. Penelitian ini dilakukan
karena  belum  ada  di  penelitian
sebelumnya, aplikasi
pemantauan   stok   yang   sudah
terintegrasi dengan Telegram.
Pengembangan   sistem   pemantauan   stok   ini
dikembangkan untuk mengelola stok barang dan
laporan  barang.  Terdapat  notifikasi  yang  masuk
ke   akun   telegram   terdaftar   ketika   pengguna
melakukan klik tombol Level Stok atau Barang
## Habis.
Hasil  dari  penelitian  ini,  dalam  pengembangan
aplikasi  pengelolaan  stok  untuk  memudahkan
pengelolaan inventaris perusahaan dapat
digunakan fitur notifikasi. Adanya fitur notifikasi
stok   menipis   membantu   mengatasi   habisnya
stok.
## Aidil Afriansyah,
## Resty Annisa
## (2022)
Mengembangkan sistem
pemantauan  stok  barang  keluar
masuk    sebagai    solusi    untuk
pencatatan  konvensional  dengan
kertas.
Pengembangan    sistem    data    penjualan    dan
monitoring stok  menggunakan  pengembangan
scrum. Menggunakan scrum termasuk
metodologi agile dimana  penulis  menghubungi
klien, analisis kebutuhan sistem, desain prototype
sistem, membangun sistem, lalu testing. Pertama,
penulis  merancang wireframe hingga prototype
menggunakan figma, lalu implementasi menjadi
aplikasi pemantauan stok. Aplikasi jadinya dapat
menghitung    barang    keluar    masuk    beserta
penjualan   yang   berlangsung.   Terdapat   pula
laporan barang masuk yang dapat diunduh dalam
format pdf.
Hasil  dari  penelitian  ini,  dalam  pengembangan
aplikasi akan menghasilkan tampilan dan fungsi
yang   lebih   jelas   dengan   membuat   prototype
terlebih dahulu. Aplikasi dapat menghitung stok
akhir  dengan  masukan  stok  barang  masuk  dan
keluaran  stok  barang  keluar,  kemudian  terdapat
ekspor    laporan   barang    masuk    yang    dapat
digunakan untuk  menentukan  kapan  dan berapa
pembelian selanjutnya.
## Rizqi Saputra, Sri
## Sumarlinda,
## Wijiyanto (2024)
Mengembangkan sistem
pengelolaan barang berbasis web
bagi Toko Mebel Sidarta sebagai
penjual  barang  jenis  mebel  dan
barang  elektronik  rumah  tangga.
Sebelumnya, pengelolaan
dilakukan dengan metode
konvensional yaitu buku.
Metode   pengembangan   menggunakan   metode
waterfall dan hasilnya aplikasi pemantauan stok
yang    menyimpan    data    penjualan    barang,
pembelian barang. Terdapat  informasi total stok
dan terjual di dasbor.
Hasil  dari  penelitian  ini,  hasil  pengembangan
aplikasi  pemantauan  stok  dibuat  lebih  rapi  dan
menampilkan informasi yang mungkin
dibutuhkan pengguna di dasbor.



## 10

Tabel 2. 1 Penelitian terdahulu (lanjutan)
## Penulis
(Tahun)
## Tujuan Metodologi Penelitian Kesimpulan
## Chitta   Rihesvar,
## Krishna Putra,
## Muhammad
## Zainul   Rohman,
## Bambang
## Cahyono (2024)
Mengembangkan sistem
pemantauan stok di PT Sucofindo
Samarinda Branch sebagai solusi
untuk mempermudah
dokumentasi peminjaman
barang,  serta  memberikan  akses
peminjaman  kepada  staf  melalui
situs web.
Pengembangan  sistem  pemantauan  stok  barang
sebagai dokumentasi peminjaman oleh staf dibuat
dengan    menerapkan addCategory misalnya
barang  konsumsi  atau  bukan  konsumsi.  Terdapat
halaman   profil   dan   ubah password sebagai
keamanan data pengguna.
Hasil  dari  penelitian  ini,  dalam  pengembangan
menerapkan pengkategorian barang, lalu terdapat
halaman   pengaturan   seperti   profil   dan   ubah
password.
## Fitriana, Arif
## Setiawan, R.
## Rhoedy Setiawan
## (2025)
Mengembangkan sistem
pengelolaan    stok    barang    di
## Gudang Raja Vapor
menggunakan metode ABC
(Activity-Based Costing)  sebagai
solusi pengelolaan konvensional.
Pengembangan    sistem    menggunakan    laporan
analisis   ABC   dimana   barang   dikelompokkan
menjadi 3 kategori A, B, dan C berdasarkan nilai
dan kontribusinya terhadap stok barang.
Hasil  dari  penelitian  ini,  dalam  pengembangan
aplikasi pengelolaan stok, perlunya
pengelompokan  barang  yang urgent (laku)  dan
barang biasa.
## Nur Layli
## Rachmawati,
## Mutiara    Lentari
## (2022)
Menerapkan    metode min-max
untuk   pengendalian   persediaan
bahan baku di sebuah perusahaan
manufaktur spare   parts LPG
dengan tujuan utamanya
meminimalkan  terjadi overstock
(kelebihan persediaan).
Metode  yang  digunakan  adalah min-max,  yang
dilakukan dengan  menentukan safety  stock untuk
mencegah risiko kekurangan persediaan,
menentukan   kapan   pemesanan   kembali   harus
dilakukan,  dan  menentukan  jumlah  maksimum
persediaan  yang  diperbolehkan  untuk  disimpan.
Penelitian ini mempertimbangkan biaya
pembelian,  penyimpanan,  dan  pemesanan  dalam
menentukan total biaya persediaan.
Hasil dari penelitian menunjukkan bahwa metode
min-max efektif dalam mengelola persediaan dan
dapat  membantu  perusahaan  meminimasi  biaya
kekurangan  persediaan.  Penerapan  metode  ini
mampu    memperbaiki    kebijakan    persediaan,
mengurangi biaya persediaan, serta menurunkan
stockout dan overstock.

Secara keseluruhan, pengembangan aplikasi pemantauan stok di Kafe B.di.M harus memadukan keenam penelitian. Hal yang
perlu diterapkan dalam pengembangan yaitu digitalisasi pencatatan, notifikasi waktu-nyata melalui WhatsApp, desain antarmuka yang
informatif, fitur keamanan dan kategorisasi, serta implementasi metode min-max untuk mengoptimalkan pengendalian persediaan yang
dapat meminimalkan  persediaan habis atau kelebihan.

## 11



## 2.2 Landasan Teori
Landasan  teori  adalah  fondasi  konseptual  dari  setiap  penelitian  atau
pengembangan  sistem.  Bagian  ini  membahas  konsep,  metode,  dan  model  yang
relevan sebagai dasar pemikiran dalam pengembangan sistem.
## 2.2.1 Metode Agile
Metode agile merupakan  kumpulan  beberapa  metode  pengembangan
perangkat  lunak  yang  dilakukan  secara  berulang  dan  bertahap  sesuai  kebutuhan.
Agile  Software  Development merupakan  metodologi  pengembangan  perangkat
lunak yang membutuhkan adaptasi cepat dari sisi pengembang melalui kolaborasi
antar  tim  yang  terorganisir agar  mampu  melakukan  perubahan-perubahan  jangka
pendek  ketika  mengembangkan  sistem untuk  memenuhi  keinginan client [10].
Pendapat lain mengatakan bahwa metode agile merupakan metode pengembangan
incremental dan  berfokus  pada  perkembangan  yang  cepat,  perangkat  lunak  yang
dirilis   bertahap,   mengurangi   proses overhead,   sehingga   menghasilkan   kode
berkualitas  tinggi  dan  ketika  proses  pengembangannya  melibatkan  pelanggan
secara langsung [11]. Gambar 2.1 memperlihatkan tahapan metode agile.

Gambar 2. 1 Tahapan metodologi agile


## 12


Ada  beberapa  model  pengembangan  perangkat  lunak  yang  termasuk
dalam metode agile  software  development. Contoh metode agile seperti pada
Gambar 2.1 yaitu 1) Scrum Methodology, 2) Scaled Agile Framework (SAFe), 3)
Lean  Software  Development (LSD),  4) Crystal  Methodology,  5) Feature  Driven
Development (FDD), 6) Extreme Programming (XP), 7) Rational Unified Process
(RUP), dan 8) Dynamic System Development Method (DSDM).
Dalam    penelitian    ini,    model    yang    digunakan adalah Extreme
Programming (XP). XP  berfokus  pada kerja  sama  tim,  komunikasi  efektif,  dan
kecepatan adaptasi terhadap perubahan kebutuhan pengguna. Sistem ini dilakukan
dengan cara programmer mendengar kebutuhan   pelanggan terlebih   dahulu
kemudian dilanjutkan dengan menulis kode, setelah itu melakukan testing berulang
untuk     memastikan     tidak     ada kesalahan, lalu dilakukan refactoring
(menyempurnakan   kode   agar   tetap   bersih   dan   mudah   dipelihara). XP   juga
menerima  perubahan  di  bagian kode manapun  dari  kode program sesuai coding
standart yang telah   disepakati   tim.   Namun, kekurangan metode extreme
programming adalah perlunya perwakilan pengguna akhir atau client di tim yang
bisa langsung memberikan masukan [12].

## 2.2.2 Stock Opname
Stock  Opname adalah  kegiatan  perhitungan  jumlah  persediaan  fisik  stok
barang  di  gudang  yang  dilakukan  setiap  awal  atau  akhir  bulan [13]. Kegiatan  ini
merupakan   langkah   pengendalian   dari   internal   kafe   atau   perusahaan   untuk
meminimalisir  risiko  terjadinya  selisih  antara  stok  fisik  dengan  pencatatan  stok
yang dilakukan. Melalui stock opname dapat diketahui keakuratan pembukuan stok
persediaan.  Jika  terjadi  selisih  antara stock  opname dengan  catatan  pembukuan,
maka  terdapat  kemungkinan  terjadi  transaksi  yang  belum  tercatat  atau  terjadi
kesalahan  perhitungan.  Terdapat  beberapa  perusahaan  melakukan stock  opname
untuk persediaan barang dan kas, kemudian ada pula stock opname di perusahaan
manufaktur  menjadikan stock  opname sebagai  alat  untuk memantau  persediaan
bahan baku [14].


## 13


## 2.2.3 Sistem Pemantauan
Pemantauan adalah menentukan apa yang telah dilaksanakan, maksudnya
mengevaluasi  prestasi  kerja  dan  apabila  perlu  menerapkannya  melalui  tindakan-
tindakan  korektif  sehingga  hasil  pekerjaan  sesuai  dengan  rencana  yang  telah
ditetapkan [15]. Pemantauan  akan  memberikan  informasi  tentang  status  dan
kecenderungan  bahwa  pengukuran  dan  evaluasi telah  diselesaikan  berulang  dari
waktu  ke  waktu. Umumnya, hal ini  dilakukan  untuk  tujuan  tertentu  seperti
memeriksa,  mengevaluasi,  dan  mempertahankan  terhadap  objek  yang  sedang
berjalan.
Pemantauan menyediakan data mentah yang digunakan untuk menjawab
pertanyaan,  sedangkan  evaluasi  adalah  meletakkan  data-data  tersebut  agar  dapat
digunakan  dan  dengan  demikian  memberikan  nilai  tambah. Evaluasi  merupakan
tempat belajar atas kejadian masa lalu yang memunculkan pertanyaan yang perlu
dijawab  guna  perbaikan  di  masa  depan [16]. Di  penelitian  ini, sistem  pemantuan
digunakan  untuk  memantau  stok  bahan  dan  barang  yang  nantinya  digunakan
sebagai evaluasi pembelian stok berikutnya.

## 2.2.4 Fonnte
Fonnte  merupakan  sebuah  layanan  yang  berjalan  diatas  official  API
WhatsApp.  Fonnte  menggunakan  WhatsApp web untuk  melakukan  otomatisasi
pengiriman  dan  membalas  pesan WhatsApp  baik  menggunakan  API  maupun
webhook.
Membuat  integrasi  WhatsApp  dengan project Laravel  dapat  dilakukan
melalui API Fonnte. Untuk menghubungkan aplikasi dengan API Fonnte kita perlu
mendaftar akun kemudian menghubungkannya dengan akun  whatsapp yang akan
digunakan untuk mengirim pesan bot. Setelah mendaftar, selanjutnya perlu meng-
copy token  yang  diberikan  oleh  situs web Fonnte  kemudian  token  tersebut  dapat
dipakai di aplikasi laravel sebagai syarat user dapat menerima pesan.


## 14


Menautkan  token  Fonnte  ke  laravel  agar  terhubung  dengan  Whatsapp
dapat dilakukan dengan  membuat 2 halaman. Halaman pertama yaitu store token
yang  didapat  dari  Fonnte  ke  sebuah  basis  data  dan  pastikan bisa update apabila
misalnya admin ingin melakukan perubahan akun bot. Lalu di halaman kedua yaitu
membuat halaman untuk connect user dengan memasukan wa_token_api di basis
data  untuk  setiap user yang  ingin  menerima  pesan  notifikasi  barang  atau  bahan
menipis  ataupun  menerima  pesan report dari user staf  sehingga mereka  bisa
memilih untuk menerima pesan notifikasi atau tidak.

## 2.2.5 Reorder Point
Reorder Point (ROP) adalah batas pemesanan barang kembali, dalam hal
ini ketika jumlah barang pada persediaan sudah mencapai batas maka pemesanan
barang harus segera dilakukan [17]. Hal ini berarti ROP adalah titik minimum atau
ambang  batas  minimum  dari  persediaan  barang  atau  bahan,  jika  persediaan
melewati ambang batas, maka sebaiknya segera dilakukan pemesanan kembali.

2.2.6 Metode Min-Max
Metode min-max bekerja   dengan   cara   memberi   batas minimal dan
maksimal pada suatu barang yang ada pada gudang, setelah itu nilai tersebut akan
digunakan dalam aktivitas pemesanan barang kembali [18]. Metode ini digunakan
untuk  mengetahui  rekomendasi  ambang  batas  minimum  yang  digunakan  pada
sistem pemantauan stok kafe serta mengetahui rekomendasi persediaan maksimum
agar tidak terjadi pembelian barang atau bahan yang berlebihan. Melalui metode ini,
jumlah  barang  yang  harus  dibeli  ketika  persediaan  melewati  ambang  batas
minimum dapat diketahui.


## 15


## 2.2.7 Visual Studio Code
Visual  Studio Code merupakan text  editor yang  dapat  ditambahkan
ekstensi pemrograman serta beberapa kerangka kerja yang diperlukan. Aplikasi ini
digunakan   untuk   membuat Model   View   Controller (MVC)   yang   mengatur
bagaimana   model,   tampilan front-end,   dan   kode back-end untuk   mengelola
bagaimana  tampilan  situs web dan  bagaimana  sistem  berperilaku. Versi  yang
digunakan dalam pengembangan sistem adalah versi 1.100.3.

## 2.2.8 Figma
Figma adalah perangkat lunak berbasis web yang dapat digunakan untuk
membuat desain wireframe hingga prototyping antarmuka aplikasi web yang akan
dibuat. Versi yang digunakan dalam pengembangan sistem adalah versi 116.12.2.

## 2.2.9 XAMPP
XAMPP adalah perangkat lunak atau situs web lokal yang memungkinkan
pengguna  untuk  menjalankan server Apache di  komputer  tanpa  koneksi  internet.
Dengan  XAMPP,  pengguna  dapat  membuat,  mengembangkan,  serta  menguji
aplikasi web di komputer sebelum dipublikasikan melalui server yang sebenarnya.
Aplikasi ini sudah memiliki basis data bawaan yaitu MySQL. Versi yang digunakan
dalam pengembangan adalah versi 8.3.0.



## 16

## BAB III
## PERANCANGAN SISTEM

Penelitian   Tugas   Akhir   ini   menggunakan   metode Agile, Extreme
Programming (XP) untuk pengembangan aplikasi pemantauan stok di Kafe B.di.M
yang  bernama  “B.di.M’s  Stock”.  Tahapan-tahapan   yang   dilakukan   meliputi
perencanaan dan pembuatan spesifikasi, perancangan diagram dan basis data yang
diperlukan,  perancangan  UI/UX,  pengembangan  basis  data  dan  perangkat  lunak,
implementasi sistem, dan yang terakhir yaitu fitur notifikasi WhatsApp.

## 3.1 Gambaran Umum Sistem
Saat  ini  proses  pemantauan  stok  di  Kafe  B.di.M  masih  dilakukan  secara
manual  dengan  cara  staf  dan  pengelola  inventaris  kafe  melakukan  pengecekan
barang setiap  malam  ketika  kafe  tutup kemudian  dicatat  di  kertas formulir. Lalu
pemilik  kafe  dan  pengelola  inventaris  kafe  dapat  melihat  laporan  pemakaian  dan
stok  sisa  kafe  hanya  di  periode  waktu  tertentu  setelah datang ke  Kafe  lalu
menanyakan  laporan  pemakaian  bulan  ini atau  setiap  tanggal  tertentu. Hal  ini
membuat   pemilik   dan   pengelola   inventaris   kesulitan   memantau   stok   dan
memungkinkan  terjadinya  stok  habis  karena  sulit  menentukan  kapan  dan  berapa
jumlah stok yang perlu di beli.
Stok yang dipantau di kafe ini memiliki dua tipe bahan (bar dan kitchen)
dimana jika terjadi transaksi, maka karyawan (staf) akan mengisi kertas yang berisi
informasi nama bahan, stok awal, masuk, keluar, dan sisa. Selain informasi tersebut,
dilampirkan pula printout dari POS untuk informasi transaksi per harinya. Hal ini
menyebabkan penggunaan  kertas  untuk cetak menjadi  banyak  karena  setiap  hari
perlu  dilakukan stock  opname.  Hal  ini juga memungkinkan  terjadinya kesalahan
hitung untuk  menentukan  sisa  hari  itu  atau kesalahan penulisan stok  bahan  awal
untuk stok besok yang diambil dari sisa kemarin.


## 17


Untuk  mengatasi  masalah ini,  sistem  pemantauan  stok  di  Kafe  B.di.M
dikembangkan. Sistem ini dapat menyimpan informasi barang (inventaris kafe) dan
bahan (di bar dan kitchen). Informasi yang tersimpan termasuk stok awal, masuk,
keluar atau terpakai, dan sisa yang disimpan pula tanggal dilakukan pembelian stok
maupun  terpakai  sebagai  pemantauan. Pada  setiap  barang  dan  bahan  juga  dapat
diatur  di  titik  minimum  berapa  akan  muncul  notifikasi  pada smartphone pemilik
(owner) dan  pengelola inventaris  kafe  (manajer). Selain  itu, karyawan  (staf)  juga
dapat  menginformasikan  apabila  terjadi  kesalahan input atau  hal  lainnya  dengan
adanya fitur report. Melalui aplikasi web ini, owner dan manajer dapat memantau
penggunaan  dan  sisa  stok  kapanpun  dan  dimanapun,  kemudian  staf juga dapat
memasukkan data transaksi dan stock opname tanpa khawatir salah hitung.

## 3.1.1 Fungsi Utama Produk
Fungsi  utama  dari  sistem  yang  dikembangkan adalah  untuk  membantu
owner,  manajer,  dan  staf  kafe  dalam  memantau  dan  mengelola  stok barang  dan
bahan  baku  secara  akurat,  sehingga  dapat  mengurangi  kesalahan  manusia  dalam
pencatatan stok. Selain itu, aplikasi ini memiliki fungsi utama lainnya, yaitu:
- Menampilkan informasi mengenai ketersediaan stok bahan baku secara waktu-
nyata,  lengkap  dengan  peringatan  notifikasi  stok menipis melalui WhatsApp
untuk bahan yang melewati ambang batas minimum yang sudah ditetapkan.
- Memfasilitasi owner dan manajer agar dapat melihat dan mengekspor laporan
stok barang dan bahan secara detail.
- Perhitungan pemakaian  bahan baku secara  singkat,  melalui  fitur impor  excel
untuk semua transaksi yang didapat dari aplikasi Point of Sales (POS).


## 18


## 3.1.2 Batasan Sistem
Sistem pemantauan stok ini memiliki beberapa batasan. Batasan tersebut
diantaranya sebagai berikut:
- Sistem hanya tersedia pada platform berbasis situs web.
- Aplikasi  berbentuk  situs web hanya  dapat  diakses  jika  pengguna  memiliki
akses internet.
- Situs web memerlukan web browser agar bisa diakses oleh pengguna.

## 3.2 Lingkungan Pengembangan Sistem
Lingkungan     pengembangan     sistem     menguraikan     perangkatkeras
(hardware)   dan   perangkat   lunak   (software)   yang   digunakan   dalam   proses
perancangan,  pengembangan,  dan  pengujian  sistem  pemantauan  stok  di  Kafe
B.di.M.  Memahami  lingkungan  ini  penting  untuk  mereplikasi  atau  memelihara
sistem di masa mendatang.
## 3.2.1 Lingkungan Pengembangan
Sistem  yang  kami  kembangkan  menggunakan  beberapa  perangkat  keras
saat pengembangan aplikasi “B.di.M’s Stock”. Spesifikasi  perangkat  keras  yang
digunakan disajikan pada Tabel 3.1.

Tabel 3. 1 Lingkungan pengembangan sistem
## Perangkat Keras
## Nama Perangkat Spesifikasi
Macbook Pro M3 (Fullstack 1) 1. Sistem Operasi: MacOS Sequoia
## 2. Prosesor: Apple M3 Chip
- RAM: 8 GB Unified Memory
- Penyimpanan: 512 GB SSD
Dell Inspiron 3442 (Fullstack 2) 1. Sistem  Operasi:  Windows  10  Pro,  Version
## 22H2
- Prosesor: Intel(R) Pentium(R) 3558U
## 3. RAM: 8 GB
- Penyimpanan: 512 GB HDD

## 19


Tabel 3. 1 Lingkungan pengembangan sistem (lanjutan)
## Perangkat Keras
## Nama Perangkat Spesifikasi
Dell XPS 15 Touch (Fullstack 3) 1. Sistem  Operasi:  Windows  11  Pro,  Version
## 23H2
- Prosesor: Intel(R) Core(R) i7-8750H
## 3. RAM: 16 GB
Penyimpanan: 512 GB SSD

Selain  perangkat  keras, terdapat  pula  beberapa  perangkat  lunak  yang
digunakan dalam proses pengembangan aplikasi. Spesifikasi perangkat lunak yang
digunakan terdapat dalam Tabel 3.2.

Tabel 3. 2 Spesifikasi perangkat lunak
## Perangkat Lunak
## Nama Perangkat Spesifikasi
## Laravel Versi 10.10
PHP Versi 8.3
## Visual Studio Code Versi 1.100.3
## Figma Versi 116.12.2
XAMPP Versi 8.3.0

## 3.2.2 Lingkungan Operasional
Perangkat  keras  minimal  yang  diperlukan  untuk  menjalankan  aplikasi
“B.di.M’s Stock” yaitu komputer atau laptop yang memiliki prosesor Intel Core I3
atau AMD Ryzen 3 dengan RAM sebesar 4GB, penyimpanan data sebesar 256 GB
SATA serta perangkat monitor, mouse, dan keyboard. Sementara, perangkat lunak
yang diperlukan untuk mengoperasikannya adalah sebagai berikut.
- Sistem  operasi  Windows  10  64-bit  atau  system  operasi  lainnya  yang  dapat
menjalankan perangkat lunak browser untuk menampilkan aplikasi.
- Google  Chrome  atau web browser lainnya  yang  dapat  digunakan  untuk
menjalankan serta menampilkan aplikasi web.


## 20


## 3.3 Kebutuhan
Kebutuhan  merinci  spesifikasi  dan  fungsionalitas  yang  harus  dipenuhi
oleh  sistem.  Bagian  ini  berisi  seluruh  proses  desain  dan  pengembangan  untuk
memastikan  bahwa  sistem  yang  dibangun  dapat  secara  efektif  menyelesaikan
masalah dan memenuhi ekspektasi pengguna.

## 3.3.1 Antarmuka Eksternal
Ada  beberapa  perangkat  antarmuka  eksternal  yang  dipakai  pada sistem
yang  dibuat  seperti  antarmuka  perangkat  keras,  antarmuka  perangkat  lunak,  dan
antarmuka  komunikasi. Antarmuka  eksternal  adalah  titik  di  mana  batas sistem
informasi berakhir dan sistem terhubung ke sistem lain. Perangkat keras eksternal
yang terhubung dengan sistem pada titik ini harus dicantumkan [19].

## 3.3.1.1 Antarmuka Perangkat Keras
Antarmuka  perangkat  keras  adalah  penghubung  antara  perangkat  keras.
Sistem  yang  dibuat  menggunakan  antarmuka  perangkat  keras yang terdapat  di
## Tabel 3.3.

Tabel 3. 3 Antarmuka perangkat keras
## No.
## Antarmuka
## Pengguna
## Fungsi
- Keyboard Antarmuka keyboard atau papan ketik digunakan
untuk memasukkan data ke dalam aplikasi
- Mouse Antarmuka mouse digunakan untuk mengontrol
kursor pada komputer.
- Monitor Antarmuka monitor digunakan untuk melihat
tampilan dari aplikasi



## 21


## 3.3.1.2 Antarmuka Perangkat Lunak
Aplikasi pemantauan stok di Kafe B.di.M “BdiM’s Stock” menggunakan
antarmuka perangkat lunak web browser yang mendukung PHP 8, JavaScript, dan
HTML5.  Pengguna  dapat  memakai web browser Google  Chrome  atau  Microsoft
Edge  untuk  mengakses  aplikasi.  Selain  itu,  pengguna  (owner dan  manajer  saja)
dapat  menghubungkan  aplikasi  dengan API WhatsApp  untuk  menerima  pesan
notifikasi stok menipis ataupun menerima pelaporan oleh staf kafe.

## 3.3.1.3 Antarmuka Komunikasi
Antarmuka    Komunikasi    berarti    antarmuka    dan    protokol    yang
memungkinkan  perangkat  lunak  yang  di-install pada  komputer  lain  (termasuk
server dan perangkat genggam) untuk beroperasi [20]. Aplikasi “B.di.M’s Stock”
ini memerlukan akses ke jaringan internet untuk bisa dijalankan. Jaringan internet
digunakan  untuk  menghubungkan  perangkat  pengguna  dengan web browser dan
aplikasi WhatsApp. Setelah  memasuki web browser,  pengguna  dapat  meminta
respon  HTTP  kepada  aplikasi  untuk  melakukan create, read, update,  dan delete
(CRUD)  terhadap  data  di  basis  data  aplikasi. Selain  itu,  ada  API  token  untuk
menghubungkan user dengan  basis  data  agar  mereka  yang  terdaftar  (memiliki
token) dapat menerima pesan notifikasi di WhatsApp masing-masing.


## 22


## 3.3.2 Deskripsi Fungsional
Deskripsi  Fungsional  menguraikan  secara  rinci  apa  saja  yang  dapat
dilakukan sistem pemantauan stok dari sudut pandang pengguna. Bagian ini berisi
fitur-fitur dan kemampuan yang dimiliki sistem secara menyeluruh.
## 3.3.2.1 Diagram Use Case
Diagram use  case merupakan  model  diagram  UML (Unified  Modelling
Language) yang digunakan  untuk  menjelaskan  bagaimana  perilaku  pengguna
dalam menggunakan aplikasi. Di dalamnya terdapat aktor dan perilaku yang dapat
dilakukan untuk  mengetahui  semua  kebutuhan  sistem  ini dari sudut  pandang
pengguna. Diagram use  case aplikasi  dari  sisi  integrasi  dengan  API WhatsApp
Fonnte terdapat pada Gambar 3.1.

Gambar 3. 1 Use case diagram dari sisi integrasi dengan API WhatsApp Fonnte
Terdapat  dua  metode  utama  bagi owner dan  manajer  dalam  penerimaan
notifikasi. Pertama, staf membuat pelaporan (report) menggunakan tombol Report
yang berisi informasi kesalahan input, barang/bahan rusak, kemudian ditambahkan
keterangan. Proses  ini  memerlukan  cek  sesi  untuk  memastikan  bahwa  staf  sudah
login sebelum membuat pelaporan. Setelah form report di isi, sistem akan request
ke basis data untuk mengirimkan informasi tersebut ke WhatsApp.

## 23


Sebelum  diteruskan  ke  API  WhatsApp  Fonnte,  basis  data  akan  selalu
memeriksa wa_token_api untuk  memastikan  penerima  pesan  telah  terdaftar
sebagau subscriber (melalui aksi ”Terima atau putuskan token”). Owner dapat
mengubah   token   untuk   mengatur   nomor   whatsapp   yang   digunakan   sebagai
pengirim pesan notifikasi (bot). Metode kedua yaitu ketika stok melewati ambang
batas minimum, maka akan terkirim notifikasi secara otomatis ke semua subscriber.
3.3.2.2 Diagram Kelas MVC
Pada pengembangan sistem yang menggunakan konsep berorientasi objek,
digram   kelas   merupakan   suatu   diagram   berstruktur   statis   dalam   melakukan
penjelasan  pada  struktur  sistem  dengan  melihat  dari  kelas,  metode,  atribut,  dan
hubungan  antar  kelas [21]. Diagram  kelas  juga  menampilkan  hierarki  sistem  dan
fungsional terstruktur pada suatu sistem.

Gambar 3. 2 Diagram Kelas MVC
Model View Controller (MVC) adalah sebuah arsitektur perangkat lunak
yang  membagi  aplikasi  menjadi  tiga  komponen  utama,  yaitu  model, view,  dan
controller.  Hal  ini  bertujuan  untuk  memisahkan  logika  aplikasi  dari  antarmuka
pengguna,  sehingga  kode  menjadi  lebih  terstruktur,  mudah  dipahami,  dan  mudah
dikelola. Diagram kelas MVC adalah representasi grafis dari struktur kelas dalam
arsitektur MVC.

## 24


Gambar  3.2 memperlihatkan diagram  kelas MVC  yang menggambarkan
struktur sistem dari sisi pengintegrasian dengan API WhatsApp. Semua pengguna
dapat  mengubah  profil  melalui  halaman setting,  lalu  pengguna  dengan role lebih
tinggi dapat mengatur token atau menautkan akun ke token tersebut.

## 3.3.2.3 Sequence Digram
Sequence diagram merupakan   salah   satu   jenis   diagram   UML yang
digunakan untuk    mendefinisikan perilaku    atau    interaksi antar obyek.
Penggambaran sequence diagram  menggunakaan  alur  komunikasi  dan  interaksi
antar objek dengan urutan waktu pemanggilan metode.

Gambar 3. 3 Sequence diagram LoginUser
Gambar  3.3  memperlihatkan sequence diagram ketika  pengguna  ingin
masuk ke halaman login. Apabila pengguna telah memiliki sesi login, mereka akan
redirect ke halaman dasbor, kemudian apabila belum memiliki sesi dapat mengisi
formulir login. Halaman Login berisi formulir email dan password yang dapat di isi
kemudian tekan tombol Login (dengan fungsi postLogin()). Email dan password
yang terkirim akan dilakukan pengecekan validasi melalui sistem kemudian menuju
basis data, jika data sesuai, akan diarahkan ke Halaman Dasbor, dan jika salah satu
data terdapat kesalahan, akan memunculkan pesan “Email atau Password Salah”.
Setelah  masuk  ke  Halaman  Dasbor,  akan  memunculkan  pesan  bahwa  nama
pengguna berhasil login.

## 25



Gambar 3. 4 Sequence diagram BuatPelaporan
Gambar  3.4  memperlihatkan sequence diagram  mengenai  pembuatan
laporan oleh staf. Pertama-tama, staf memasuki aplikasi baik melalui dasbor atau
halaman  lainnya, staf dapat  menekan  tombol Report.  Setelah  menekan  tombol
Report maka akan muncul formulir modal yang berisi informasi salah input, barang
rusak, atau  kadaluarsa  kemudian  dapat  menekan  tombol Kirim.  Pesan  pelaporan
sebelum    dikirim    akan dilakukan    pengecekan    pengguna    yang    memiliki
wa_api_token.  Pengguna  yang  memiliki wa_api_token akan  diteruskan  ke  API
Fonnte lalu pesan tersebut masuk ke WhatsApp pengguna terdaftar. Dari sisi staf
pembuat  pelaporan,  akan  menerima  pesan  bahwa  pelaporan  berhasil  terkirim  ke
atasan.


## 26



Gambar 3. 5 Sequence diagram UbahProfil
Gambar  3.5  memperlihatkan sequence diagram  mengenai  pengubahan
profil pengguna. Semua role dapat melakukan perubahan profil pengguna dan data
tersimpan untuk pengguna tersebut.

Gambar 3. 6 Sequence diagram SetAPIToken
Gambar  3.6 memperlihatkan sequence diagram  mengenai  pengubahan
token API Fonnte yang  dilakukan oleh role owner dan berfungsi untuk mengatur
WhatsApp  pengirim  sebagai bot. Token  yang  diisi owner akan  digunakan  di
wa_api_token untuk pengguna  yang ingin menghubungkan  (subscribe) ke  fitur
notifikasi WhatsApp.


## 27



Gambar 3. 7 Sequence diagram HubungkanAPI
Gambar 3.7 memperlihatkan sequence diagram bagaimana
menghubungkan pengguna owner atau manajer dengan nomor WhatsApp mereka
pada fitur notifikasi. Pengguna dapat menekan tombol HubungkanKembali agar
wa_api_token tersimpan  di  basis  data  pengguna. Setelah  token  tersimpan, akan
muncul pesan bahwa API telah terhubung dengan nomor yang di isi oleh pengguna.

Gambar 3. 8 Sequence diagram PutuskanHubunganAPI
Gambar  3.8 memperlihatkan sequence diagram bagaimana memutuskan
pengguna owner atau  manajer  dengan  fitur  notifikasi.  Pengguna  dapat  menekan
tombol PutuskanHubungan agar wa_api_token dikosongkan di  basis  data
pengguna.  Setelah  token kosong, akan  muncul  pesan  bahwa  API  telah terputus
dengan nomor yang di isi oleh pengguna.


## 28


## 3.3.2.4 Activity Diagram
Activity Diagram   merupakan   diagram   UML   yang   digunakan   untuk
memodelkan alur kerja (workflow) atau proses bisnis dalam suatu sistem. Gambar
3.9 memperlihatkan activity diagram   sistem   dari   sisi integrasi dengan   API
WhatsApp.

Gambar 3. 9 Activity diagram
Gambar  3.9  memperlihatkan  bagaimana  aktivitas  staf  dalam  membuat
pelaporan  dan  bagaimana  sistem  pemantauan  stok  dan  WhatsApp  melakukan
aktivitasnya. Staf  setelah  masuk  aplikasi  atau sistem,  dapat  membuat  permintaan
pelaporan  kemudian  dapat  mengisi  pelaporan. Sistem  akan  memproses  pesan
tersebut  lalu  memeriksa  pengguna  yang  memiliki wa_api_token. Bagi  pengguna
dengan wa_api_token, pesan  akan  dikirim  dan  diterima  ke  WhatsApp  masing-
masing  pengguna. Apabila  pengguna  tidak  memiliki wa_api_token, pesan  tidak
terkirim ke pengguna tersebut, lalu staf mendapat informasi pesan telah terkirim,
terlepas apakah terdapat pengguna yang memiliki wa_api_token maupun tidak.

## 29


## 3.3.2.5 State Diagram
State Diagram   merupakan   diagram   UML   yang   digunakan   untuk
memodelkan  perilaku  dinamis  sebuah  objek  dengan  menunjukkan  keadaan  yang
bisa  dimiliki  objek  tersebut.  Kondisi  pengiriman  pesan  notifikasi  ke  WhatsApp
memiliki 2 kondisi yang terdapat pada Gambar 3.10 dan Gambar 3.11.

Gambar 3. 10 Kondisi pelaporan oleh staf
Gambar  3.10  memperlihatkan state atau  kondisi  pelaporan  oleh  staf.
Pertama-tama idle berarti pengguna staf telah masuk ke dalam sistem tetapi tidak
ingin membuat pelaporan. Ketika staf ingin membuat pelaporan maka akan mengisi
formulir modal  kemudian sistem membacanya,  dan  saat  menekan  tombol kirim
maka  pesan  akan  berhasil  dikirimkan. Setelah  pengiriman  berhasil  staf  dapat
memilih apakah ingin membuat pelaporan lagi atau tidak.

## 30



Gambar 3. 11 Kondisi stok menipis oleh sistem
Gambar 3.11 memperlihatkan state atau kondisi ketika stok menipis oleh
sistem. State pertama yaitu idle, ketika tidak ada perubahan pada basis data sistem.
State kedua yaitu kondisi ketika pengguna menambah transaksi atau mengisi stock
opname, maka sistem akan membaca sisa dan minimum stok dengan perhitungan
푠푖푠푎 = (푎푤푎푙+푚푎푠푢푘)−푡푒푟푝푎푘푎푖. Ketika sisa lebih dari minimum kemudian
setelah dilakukan transaksi atau stock opname menghasilkan nilai sisa kurang dari
minimum, sistem akan membuat pesan notifikasi bahan menipis yang dikirimkan
ke semua pengguna yang memiliki wa_api_token. Kondisi kedua atau state kedua
yaitu ketika stok sisa barang lebih dari minimum kemudian dilakukan pengurangan
barang  hingga  melewati  ambang  batas  minimum,  maka  pesan  notifikasi  akan
terkirim  ke  WhatsApp  milik  semua  pengguna  yang  memiliki wa_api_token.
Perhitungan sisa untuk bahan yaitu 푠푖푠푎=(푎푤푎푙+푚푎푠푢푘)−푘푒푙푢푎푟.

## 31


## 3.3.2.6 Deployment Diagram
Deployment Diagram   adalah   diagram   UML   yang   digunakan   untuk
memodelkan  topologi  fisik  sistem  perangkat  keras  (hardware)  dan  penyebaran
(deployment) komponen perangkat lunak pada node-node perangkat keras tersebut.
Diagram  ini  menunjukkan  di  mana  komponen-komponen  sistem  akan  dijalankan
dan bagaimana mereka saling terhubung. Gambar 3.12 memperlihatkan deployment
diagram dari sistem pemantauan stok yang dikembangkan.

## Gambar 3. 12 Deployment Diagram
Deployment   diagram   yang   terlihat   pada   Gambar   3.12   menjelaskan
bagaimana sistem  di-deploy ke  layanan hosting. Pada Web  Client menampilkan
blade Laravel  berupa  HTML5,  kemudian  terhubung  dengan  HTTPS web  server
yang terdiri dari Model, View, dan Controller (MVC) yang terhubung melalui DRM
connection yaitu basis data MySQL.

## 3.3.3 Kebutuhan Fungsional
Dari seluuh diagram yang telah dipaparkan pada sub-bab 3.3.2, didapatkan
kebutuhan fungsional dari tiap stakeholder. Terdapat kode yang digunakan untuk
merepresentasikan kebutuhan (requirement) pada Sistem Pemantauan Stok di Kafe
B.di.M  dengan  SPSB  sebagai  inisial  dari  Sistem  Pemantauan  Stok  B.di.M  dan
XXXX merupakan   digit   atau nomor   kebutuhan   (requirement). Kebutuhan
fungsional sistem pemantauan stok di kafe B.di.M terdapat pada Tabel 3.4.

## 32


Tabel 3. 4 Tabel kebutuhan fungsional
## No. Kode Deskripsi
## Kebutuhan
## Owner Manajer Staf Prioritas
## 1. SPSB-
## 0001
Melakukan login. Owner,   manajer,   dan   staf   dapat   masuk
sebagai  akun  terdaftar  ketika  melakukan
login akun.
## Tinggi
## 2. SPSB-
## 0002
Membuat form
report.
- - Staf  dapat
membuat
formulir
untuk
report
kesalahan
input,
barang
atau bahan
rusak, atau
informasi
penting
lainnya
## Tinggi
## 3. SPSB-
## 0004
Membuat atau
mengubah
wa_api_token
Owner dapat
membuat
atau
mengubah
token.
## - - Tinggi
## 4..  SPSB-
## 0004
## Menghubungkan
ke
wa_api_token
Owner dan manajer   dapat
menghubungkan  akunnya  ke
token dengan menekan
tombol   “Hubungkan”   di
pengaturan.
## - Tinggi
## 5. SPSB-
## 0004
Database dapat
mengirimkan
informasi ke
whatsapp   melalui
API Fonnte
Owner dan manajer
menerima  pesan  notifikasi  di
whatsapp mereka.

## - Tinggi



## 33


## 3.3.4 Kebutuhan Non-fungsional
Kebutuhan  non-fungsional  digunakan  untuk  memastikan  apakah  sistem
sudah sesuai dengan kebutuhan pengguna. Kebutuhan non-fungsional dalam sistem
pemantauan stok di Kafe B.di.M terdapat pada Tabel 3.5.

Tabel 3. 5 Kebutuhan non-fungsional
SRS-Id Parameter Requirement
## SW-SPSB-
## NF01
Availability Aplikasi   ini   dapat   beroperasi   7   hari   dalam
seminggu dan 24 jam dalam satu hari.
## SW-SPSB-
## NF02
Reliability Sistem   akan   menjamin   minimalisasi   tingkat
kegagalan dalam pengoperasian.
## SW-SPSB-
## NF03
Portability Sistem  dapat  dioperasikan  pada  komputer  yang
memiliki sistem operasi windows dan perangkat
lunak web browser.
## SW-SPSB-
## NF04
Response Time Memberikan  waktu  respon  maksimal  5  detik
untuk setiap permintaan data dari dasbor.
## SW-SPSB-
## NF05
Safety Semua data pada sistem dijamin keamanannya.
## SW-SPSB-
## NF06
Security Aplikasi  menggunakan autentikasi  dua  faktor
dan enkripsi untuk melindungi data sensitif.

Penjelasan kode:
SW  : Software
SPSB : Sistem Pemantauan Stok B.di.M
NF : Non-Fungsional


## 34


## 3.4 Desain Produk
Desain   produk   berisi   konsep   abstak yang   diterjemahkan   menjadi
spesifikasi konkret yang akan memandu implementasi. Bagian ini membahas detail
bagaimana sistem pemantauan stok di Kafe B.di.M dirancang, yaitu arsitekur sistem
dan alur koneksinya
## 3.4.1 Arsitektur Sistem
Dalam  perancangan  perangkat  lunak, arsitektur  sistem  diperlukan  untuk
mendefinisikan   keseluruhan   dari   sistem   yang   melibatkan   hubungan   antar
komponen  utama.  Arsitektur  sistem  dari  sistem  pemantauan  stok  di  Kafe  B.di.M
terdapat pada Gambar 3.2.

## Gambar 3. 13 Arsitektur Sistem
Arsitektur  situs web sistem  pemantauan  stok  di  Kafe  B.di.M  terdiri  dari
beberapa komponen yang menyatakan struktur dan desain dari sistem komputer dan
perangkat  lunak.  Arsitektur  ini  menunjukkan  interaksi  antara  komponen  utama
yang mendukung aplikasi. Komponen-komponen tersebut meliputi Database, PHP
Web  Server, web browser,  API  Fonnte,  whatsapp, internet, dan client. Setiap
komponen   memiliki   peran   spesifik   yang   saling   terhubung   melalui   koneksi
komunikasi   (C1,   C2,   C3,   C4,   C5, C6, dan   C7). Penjelasan   untuk   setiap
komponennya sebagai berikut:

## 1. Database
Database berfungsi sebagai  penyimpanan  utama  untuk  data  stok  (bahan  dan
barang), transaksi, komposisi menu, dan token API. Menyediakan data waktu-
nyata dan memfasilitasi akses data untuk komponen lain.


## 35


- PHP Web Server
PHP Web Server bertindak sebagai perantara yang menghubungkan basis data
dengan pengguna. Aplikasi pemantauan stok berjalan di komponen ini.

- API Fonnte
Antarmuka  percakapan  yang  memungkinkan  sistem  mengirimkan  informasi
persediaan melewati  ambang  batas  minimum dan pelaporan  dari  staf  ke
Whatsapp. Berperan sebagai jembatan antara sistem dan WhatsApp.

- WhatsApp
Platform komunikasi yang memungkinkan pengguna menerima informasi stok
bahan dan barang dari sistem melalui notifikasi pesan instan. Berperan sebagai
keluaran dari sistem.

## 5. Web Browser
Perantara  utama  bagi  pengguna  untuk  mengakses  aplikasi  pemantauan  stok
melalui  antarmuka web.  Mengambil  data  dari Database melalui  PHP Web
Server dan mendukung operasi CRUD melalui HTTP Request.

## 6. Client
Perangkat    yang    digunakan    pengguna    (komputer,    laptop,    tablet,    dan
smartphone)  untuk  melihat  dan  memperbarui  data  stok,  serta  menerima
notifikasi     melalui     aplikasi WhatsApp. Area     dimana     stakeholder
mengoperasikan perangkat.

## 7. Internet
Media transmisi utama yang menghubungkan hampir semua komponen sistem.
Pengguna memerlukan jaringan internet untuk mengakses sistem ini.


## 36


Untuk  memudahkan  penjelasan  alur  koneksi  antar  komponen,  maka
diringkas dalam tabel. Alur koneksi antar komponen terdapat pada Tabel 3.6.

Tabel 3. 6 Alur koneksi antar komponen
## Kode
## Komponen
## Pengirim
## Komponen
## Penerima
## Media
## Transmisi
## Metode
## Transmisi
## C1 Database
PHP Web
## Server
## Internet
HTTP Post
atau HTTP
## Get
## C2
PHP Web
## Server
API Fonnte Internet
HTTP Post
C3 API Fonnte WhatsApp Internet API
C4 Internet WhatsApp Internet API
## C5
PHP Web
## Server
## Web Browser Internet
HTTP Post,
Put, Get, atau
## Delete.
## C6 Internet Web Browser Internet
HTTP Post
atau HTTP
## Get
## C7 Internet Client Internet
## Beragan
(Aplikasi dan
## Web)

Pada  C1, database mengirimkan  data  ke  PHP web server ketika  ada
permintaan. Lalu  C2  terjadi ketika  PHP web server menerima  informasi  laporan
atau persediaan   melewati   ambang   batas   minimum, kemudian melakukan
pengecekan  token  pengguna  yang  terdaftar,  lalu  mengirim  permintaan  ke  API
Fonnte. Di C3, API Fonnte menghubungkan dan meneruskan permintaan dari PHP
web server ke WhatsApp  untuk  pengiriman  pesan. C4, whatsapp  menggunakan
koneksi internet untuk mengirim notifikasi ke client pengguna. C5, PHP web server
menampilkan  aplikasi web pemantauan  stok  di web browser. C6  merupakan
koneksi  antara internet dengan web browser yang  terjadi  ketika client ingin
mengakses aplikasi melalui web browser. Terakhir, untuk C7 yaitu client terhubung
ke  internet  untuk  mengakses  berbagai  layanan,  termasuk  menerima  notifikasi
WhatsApp dan mengakses web browser.


## 37


## 3.4.2 Desain Detail Sistem
Desain detail sistem memaparkan secara lebih dalam mengenai struktur
dan implementasi teknis dari sistem pemantauan stok di Kafe B.di.M. Bagian ini
berisi desain basis data dan standar-standar yang diperlukan sistem.
## 3.4.2.1 Deskripsi Data
Physical  Data  Model (DPM)  yang  digunakan  untuk  menggambarkan
struktur basis data secara rinci, termasuk tabel, kolom, dan tipe data yang digunakan
untuk membuat integrasi laravel dengan whatsapp. Data kolom dalam tabel dapat
berelasi dengan tabel lain menggunakan foreign key. Deskripsi data untuk membuat
integrasi  Laravel  dengan  whatsapp  di  sistem  pemantauan  stok  di  Kafe  B.di.M
terdapat pada Tabel 3. 7 sampai dengan Tabel 3.9.

## Tabel 3. 7 Tabel User
## Nama Kolom Tipe Data Deskripsi
id bigInt Primary Key
name varchar(50) -
email varchar(80) -
email_verified_at timestamp -
password varchar(76) varchar(76)    merupakan    ukuran
tertinggi pemakaian hashing
bcrypt  di  laravel  dengan  minimal
60   karakter   tergantung   jumlah
iterasi.
role enum('OWNER',
## 'MANAJER',
## 'STAF')
Membatasi role hanya
menerima ”OWNER”, ”Manajer”,
dan ”STAF”.
remember_token varchar(100) -
wa_api_token varchar(100) Membatasi user yang     dapat
menerima   notifikasi   hanya   jika
token   terisi   sesuai   dengan   API
yang didapat dari Fonnte.



## 38



Tabel 3. 8 Tabel SetApiToken
## Nama Kolom Tipe Data Deskripsi
id bigInt Primary Key
name varchar(50) -
token_name varchar(100) Menyimpan  nama  token  yang
didapatkan dari fonnte sebagai
pengirim pesan dari whatsapp,
yang  nantinya ini dimasukkan
ke wa_api_token.
phone varchar(18) -

Tabel 3. 9 Tabel UserProfile
## Nama Kolom Tipe Data Deskripsi
id bigInt Primary Key
user_id bigInt Foreign Key
phone varchar(18) Menyimpan    informasi    nomor
ponsel  pengguna  yang  nantinya
juga digunakan untuk
menghubungkan  ke  nomor  mana
pesan WhatsApp diterima.
address text -
birth_date date -
gender enum('L', 'P') ’L’   (Laki-laki)   dan   ’P’
(Perempuan).

3.4.3 Standar-standar yang Dipergunakan
Standar-standar  yang  dipergunakan  dalam  pengembangan  sistem  pemantauan
stok di Kafe B.di.M (“Bdim’s Stock”) terdapat beberapa macam. Untuk Standar platform,
aplikasi  berbasis web yang  dibuat menggunakan Visual  Studio  Code. Standar  bahasa
pemrograman berbasis web menggunakan PHP. Basis data yang digunakan adalah MySQL,
kemudian  mengandalkan  HTTP Request sebagai  protokol  komunikasi  data  antara client
dengan server. HTTP memungkinkan client untuk meminta informasi (request) ke server
dan server mengembalikan respon (response) atas permintaan tersebut.
Keamanan  sistem yang  digunakan seperti enkripsi hash bcrypt pada password,
lalu  menggunakan middleware dan  token  untuk  memberikan  keamanan  data  dengan
perbedaan akses aplikasi sesuai role masing-masing dan waktu token. Hal ini digunakan
untuk  memastikan  bahwa  aplikasi  yang  dibuat  sudah  memenuhi  standar  keamanan
pengguna.


## 39


3.5 Perhitungan Metode Min-Max
Perhitungan metode min-max adalah perhitungan untuk  menentukan
ambang batas minimum dan maksimum persediaan barang atau bahan yang dapat
disimpan  dalam  gudang.  Metode  ini  digunakan  untuk  mengetahui  rekomendasi
ambang  batas  minimum  yang  digunakan  pada  sistem  pemantauan  stok  kafe  serta
mengetahui  rekomendasi  persediaan maksimum  agar  tidak  terjadi  pembelian
barang atau bahan yang berlebihan. Berikut adalah rumus metode min-max menurut
Rachmawati & Lentari kemudian disesuaikan dengan proses bisnis Kafe B.di.M.
- Persediaan Minimum (Reorder Point)
Persediaan minimum merupakan nilai ambang batas minimum persediaan
barang   atau   bahan   dimana   ketika   melewati   titik   tersebut,   sebaiknya   segera
dilakukan  pemesanan  persediaan. Metode  ini  menggunakan  kebutuhan  rata-rata
data barang atau bahan dalam waktu 1 bulan (dihitung per hari). Terdapat beberapa
faktor yang memengaruhi batas ambang minimum yaitu sebagai berikut:
- Lead  Time,  waktu  tunggu  yang  diperlukan  ketika  barang  atau  bahan  dipesan
hingga datang di perusahaan atau kafe. Dalam hal ini setiap barang memiliki
nilai lead time yang berbeda antara barang atau bahan satu dengan yang lainnya
tergantung dari jarak, bahan, alat transportasi, dan sebagainya.
- Tingkat  pemakaian atau  kebutuhan  atau  terpakai rata-rata  barang  atau  bahan
setiap  periode waktu  tertentu.  Dapat  dihitung  berdasarkan pemakaian  barang
atau bahan setiap harinya untuk periode satu bulan dan pemakaian barang atau
bahan setiap bulannya untuk periode satu tahun.
- Safety  Stock adalah  nilai  persediaan  yang  harus  dimiliki  perusahaan untuk
mengatasi situasi pemakaian yang tidak terduga atau melebihi rata-rata. Hal ini
perlu untuk meminimalisirkan terjadinya keterlambatan datangnya barang atau
bahan. Persamaan safety  stock terdapat  pada  persamaan  3.1  dan  3.2  dengan
menghitung deviasi standar terlebih dulu.


## 40


## 퐷푒푣푖푎푠푖 푆푡푎푛푑푎푟=
## √
## ∑
## (푇−푇)
## 2
## 푗푢푚푙푎ℎ 푝푒푟푖표푑푒−1

## (3.1)
## 푆푎푓푒푡푦 푆푡표푐푘=푆푎푓푒푡푦 퐹푎푐푡표푟×퐷푒푣푖푎푠푖 푆푡푎푛푑푎푟
## (3.2)

## Keterangan:
푇 : Kebutuhan stok per hari.
푇  : Kebutuhan stok rata-rata dalam satu periode.

Untuk   perhitungan   di   kafe   B.di.M,   ukuran Safety   Factor bahan
disesuaikan dengan permintaan dari pemilik dan pengelola inventaris kafe. Ukuran
safety  factor berkisar antara 1.5  sampai  3.0  untuk  menentukan  ketidakpastian
barang atau bahan yang diukur dengan pertimbangan variabel penjualan tinggi atau
rendah  pada  setiap  barang  atau  bahan. Safety  factor di  kafe  B.di.M  belum
ditentukan sehingga pengujian dilakukan dengan ukuran 1.5. Setelah adanya sistem
yang  dibuat,  pihak  kafe  dapat  mulai  menentukan safety  factor untuk  mengatasi
kehabisan stok. Persamaan 3.3 merupakan persamaan untuk persediaan minimum.

## 푃푒푟푠푒푑푖푎푎푛 푀푖푛푖푚푢푚=
## (
## 푇×퐿푇
## )
## +푆푆
## (3.3)

## Keterangan:
푇 : Kebutuhan stok per hari.
LT : Waktu tunggu pemesanan (hari).
SS : Nilai safety stock.


## 41


## 2. Persediaan Maksimum
Persediaan maksimum merupakan nilai maksimum persediaan barang atau
bahan   yang diperbolehkan   disimpan   di   gudang. Nilai   ini   digunakan   untuk
menghindari  kafe menyimpan  stok  yang  berlebih  (overstock). Persamaan  3.4
merupakan persamaan untuk persediaan maksimum.

## 푃푒푟푠푒푑푖푎푎푛 푀푎푘푠푖푚푢푚=(푡
## 푏
## +1)×
## (
## 푇×퐿푇
## )
## +푆푆
## (3.4)

## Keterangan:
푡푏 : Rata-rata jarak waktu pemesanan stok dalam satu periode (hari).
푇 : Kebutuhan stok per hari.
LT : Waktu tunggu pemesanan (hari).
SS : Nilai safety stock.

## 3. Jumlah Pemesanan Kembali
Jumlah pemesanan kembali digunakan untuk menentukan berapa jumlah
barang atau bahan yang harus di pesan ketika ambang batas minimum telah tercapai.
Persamaan 3.5 merupakan persamaan untuk mengetahui jumlah barang atau bahan
yang harus di pesan.

## 푄=(푃푒푟푠푒푑푖푎푎푛 푀푎푘푠푖푚푢푚−푃푒푟푠푒푑푖푎푎푛 푀푖푛푖푚푢푚)
## (3.5)

## Keterangan:
## Q : Jumlah Pemesanan Kembali.



## 42


## 3.6 Metode Pengujian
Tahap pengujian dari pengembangan sistem pemantauan ini menggunakan
beberapa  metode  untuk  memastikan  sistem  telah  berjalan  dengan  baik. Metode
yang digunakan antara lain sebagai berikut.
## 3.6.1 Pengujian Penerimaan Pengguna
Pengujian adalah proses untuk memastikan pengembang sudah memenuhi
semua kebutuhan fungsional dan non-fungsional sistem yang dibuat [22]. Pengujian
perangkat  lunak  merupakan  proses  mengevaluasi  perangkat  lunak  atau  program
dengan   tujuan   untuk   menemukan bug [23].   Pengembang   perlu   memastikan
perangkat  lunak  dapat  berjalan  dengan  baik  di  semua  kondisi  dan  situasi  dari
pengguna bersangkutan.
Pengujian penerimaan  pengguna  (User  Acceptance  Test/UAT) adalah
tahap  pengujian  sistem  yang  dilakukan  oleh  pengembang  dengan  melibatkan
pengguna. Proses ini menghasilkan dokumen sebagai bukti bahwa pengguna sistem
telah  menerima  pengembangan  sistem  dan  menganggap  kebutuhan  mereka  dapat
terpenuhi  berdasarkan  hasil  pengujian [24]. Tujuan  UAT  untuk  menguji  sistem
dalam  lingkungan  dan  skenario  penggunaan  nyata  sebelum  sistem  diterima  dan
diimplementasikan. Pengujian ini biasa dilakukan sebelum fitur baru dalam sistem
diluncurkan [25].

## 3.6.2 Pengujian Blackbox
Pengujian black box adalah  pengujian  fungsionalitas  sistem  dimana
penguji tidak perlu tahu mengenai logika internal ataupun struktur program. Sesuai
namanya,  struktur  kode  program  tertutup  oleh  kotak  hitam  (black  box)  sehingga
penguji hanya melihat apa yang ada didepannya [26]. Dalam pengujian black box,
penguji berfokus pada input dan output program, bukan bagaimana proses tersebut
dilakukan di dalam [27].


## 43

## BAB IV
## HASIL DAN PEMBAHASAN

## 4.1 Implementasi
Implementasi   merupakan   proses   transformasi   desain   sistem   menjadi
produk yang berfungsi secara penuh. Bagian ini berisi bagaimana setiap komponen
yang telah dirancang diimplementasikan.
## 4.1.1 Implementasi Produk
Sebagai  platform  berbasis  situs web,  aplikasi  dapat  diakses  melalui
berbagai   perangkat   tanpa   perlu   instalasi   tambahan. Proses   implementasi
pengembangan sistem pemantauan ini dilakukan menggunakan bahasa PHP versi
8,3 dengan kerangka kerja Laravel 10.10 untuk bagian back-end, kemudian terdapat
HTML,   CSS,   dan JavaScript untuk   tampilan front-end-nya. Struktur   MVC
diterapkan  untuk  memisahkan  logika  bisnis,  tampilan,  dan  pengelolaan  data,
sehingga meningkatkan skalabilitas serta kemudahan pemeliharaan aplikasi.
## 4.1.2 Tampilan Produk
Tampilan  halaman  Pengembangan  Aplikasi  Pemantauan  Stok  di  Kafe
B.di.M secara  keseluruhan terdapat  perbedaan.  Perbedaan  untuk  halaman  yang
dapat diakses terdapat pada Tabel 4.1.

Tabel 4. 1 Perbedaan halaman yang dapat diakses untuk masing-masing role
## Halaman Owner Manajer Staf
Halaman Dasbor v v v
Halaman Buat Laporan v v x
Halaman Stok Barang v v v
Halaman Stok Bahan v v v
Halaman Daftar Menu v v v
Halaman Transaksi v v v
Halaman Temporary v v x
Halaman Data User v v x

## 44


Tabel 4. 1 Perbedaan halaman yang dapat diakses untuk masing-masing role (lanjutan)
## Halaman Owner Manajer Staf
Halaman Setting → Profile v v v
Halaman Setting → Ubah Token API v v x
Halaman Setting → Hubungkan API v v x
Halaman Setting → Ubah Password v v v

Lalu  terdapat  perbedaan  fitur  utama  dari  masing-masing role. Secara
keseluruhan perbedaan fitur terdapat pada Tabel 4.2.

Tabel 4. 2 Perbedaan fitur utama dari masing-masing role
## No. Owner Manajer Staff
- Owner dan manajer dapat melihat dan
melakukan ekspor excel maupun pdf untuk
laporan bulanan stok barang dan bahan
## -
- Owner memiliki
semua fitur yang
dimiliki manajer dan
staf.
Manajer memiliki
semua fitur yang
dimiliki staf.
Staf memiliki fitur yang
terbatas yaitu untuk
menghitung stok di kafe.
- Owner dan manajer dapat melihat notifikasi
apabila stok menipis atau menerima laporan
kerusakan/kehilangan barang oleh staf.
Staf dapat membuat
pelaporan (report)
informasi penting kepada
owner dan manajer.
4 Owner dapat
membuat dan
menghapus akun
Manajer dan Staf
Manajer hanya dapat
membuat dan
menghapus akun
## Staf
## -
- Owner dapat
mengatur
wa_api_token yang
digunakan sebagai
bot whatsapp.
Manajer hanya dapat
melakukan subscribe
agar menerima
notifikasi Whatsapp.
## -

## 45



## 4.1.2.1 Halaman Home
Halaman Home yang terdapat pada Gambar 4.1 memperlihatkan gambaran
umum  tentang  aplikasi.  Pada  bagian  atas  halaman,  terdapat tombol Login untuk
berpindah ke halaman login dengan  mudah.  Di  bagian  tengah  halaman,  terdapat
informasi aplikasi yang menjelaskan bahwa B.di.M stok merupakan aplikasi stock
opname yang dirancang untuk membantu pemantauan stok barang dan bahan baku
di Kafe B.di.M.

## Gambar 4. 1 Halaman Home

## 4.1.2.2 Halaman Login
Halaman Login berisi Login   Panel untuk   memasukkan email dan
password yang  dapat  diisi. Login  Panel juga  dilengkapi  dengan  tombol Lupa
password? untuk  me-reset password dengan  memasukkan email yang  terdaftar
kemudian  mengisi  formular  yang  dikirim  ke email. Gambar 4.2 memperlihatkan
halaman login dari sistem pemantauan stok di Kafe B.di.M.

## Gambar 4. 2 Halaman Login

## 46


## 4.1.2.3 Halaman Dasbor Staf
Halaman Dasbor Staf yang terdapat pada Gambar 4.3 merupakan halaman
awal yang dapat dilihat pada staf saat memasuki aplikasi. Memiliki tampilan hampir
sama  dengan  manajer dan owner, namun  datanya  disesuaikan  sesuai role-nya.
Perbedaan terlihat yaitu  di pojok kanan  atas terdapat tombol Report. Tombol ini
digunakan untuk melaporkan ke atasan apabila terjadi barang rusak atau informasi
penting lainnya.

## Gambar 4. 3 Halaman Dasbor (staf)

Gambar 4. 4 Modal ”report” kepada atasan
Gambar 4.4 memperlihatkan modal ketika tombol Report ditekan oleh
pengguna staf. Terdapat pilihan jenis lapor dan alasannya, lalu bisa ditambahkan
keterangan.

## 47



Gambar 4. 5 Modal “report” ketika memilih ”lainnya”
Gambar  4.5  memperlihatkan  isian  pengguna  staf  dan  ketika  memilih
lainnya, maka akan muncul input text yaitu ketika alasan pelaporan tidak terdapat
di pilihan. Lalu pada keterangan dapat di isi sesuai alasan dan jenis pelaporan.
Setelah itu, semua pengguna yang telah menghubungkan akunnya dengan
API  notifikasi  akan  menerima  pesan  ke  WhatsApp. Gambar  4.6  memperlihatkan
keluaran pesan oleh staf dan stok menipis oleh sistem di aplikasi WhatsApp. Pesan
hanya terkirim ke owner dan manajer yang sudah terhubung API notifikasi.

Gambar 4. 6 Isi pesan notifikasi yang dikirim ke owner atau manajer yang sudah terhubung API
notifikasi


## 48


## 4.1.2.4 Halaman Setting Profil
Gambar 4.7 memperlihatkan halaman Setting → Profil untuk  melihat
profil user yang saat ini login dan dapat melakukan edit profil. Terdapat informasi
nama pengguna, email, role, nomor ponsel aktif, tanggal lahir, jenis kelamin, dan
alamat dari masing-masing pengguna.

## Gambar 4. 7 Halaman Setting → Profile

Gambar 4. 8 Modal edit profil
Gambar  4.8  menampilkan  modal  untuk  melakukan  edit  profil ketika
tombol EditProfil ditekan. Selain  nama  pengguna,  kolom  lain  tidak  wajib  di  isi,
namun  untuk owner atau  manajer  yang  ingin  mengaktifkan  fitur  notifikasi,  maka
perlu mengisi nomor ponsel yang sesuai.

## 49


4.1.2.5 Halaman Setting API Notifikasi
Gambar 4.9 memperlihatkan halaman owner dapat mengatur token yang
didapatkan  dari Fonnte  yang  digunakan  untuk  menghubungkan  aplikasi  dengan
WhatsApp yang digunakan untuk mengirim pesan. Terdapat informasi status nama
pengirim,  nomor  WhatsApp  pengirim,  dan  token  yang  terhubung  dengan  API
Fonnte. Role staf dan manajer tidak memiliki akses ke halaman ini.

Gambar 4. 9 Halaman atur token API Fonnte WhatsApp

Gambar 4. 10 Modal edit token API
Gambar  4.10  memperlihatkan  modal  edit  token  API  Ketika  tombol
EditToken ditekan  oleh  pengguna owner.  Data  yang  perlu  dimasukkan  sesuai
dengan akun yang dibuat pada situs web Fonnte.

## 50



Gambar 4. 11 Halaman pengambilan token API dari Fonnte
Gambar 4.11 memperlihatkan halaman pengambilan token API dari situs
web Fonnte. Terdapat  informasi  nomor  WhatsApp,  nama  perangkat  tersambung,
dan  token  untuk  menghubungkan  aplikasi  dengan  WhatsApp  pengguna  sebagai
pengirim bot.
Selanjutnya pada Gambar 4.12 memperlihatkan halaman  Setting → API
Notifikasi untuk mengatur pengguna dengan role owner atau manajer apakah ingin
menerima pesan notifikasi jika barang/bahan menipis (dibawah minimum stok yang
diatur). Terdapat status dan tombol HubungkanKembali apabila pengguna belum
terhubung dengan token API Fonnte. Role staf tidak dapat mengakses halaman ini.

Gambar 4. 12 Halaman Setting → API Notifikasi


## 51



Gambar 4. 13 Hasil ketika tombol ”Hubungkan kembali” ditekan
Gambar 4.13 memperlihatkan hasil ketika Tombol Hubungkan Kembali
ditekan. Nomor ponsel yang dipakai untuk notifikasi WhatsApp sesuai dengan apa
yang dimasukkan di halaman Setting → Profil.

## 4.1.2.6 Halaman Ubah Password
Gambar 4.14 memperlihatkan Halaman Setting → Ubah Password untuk
mengubah password pengguna baru setelah didaftarkan oleh owner atau manajer.
Ketika  pengguna  ingin  melakukan  perubahan password lama,  pengguna  dapat
mengisi keseluruhan form input, kemudian menekan tombol
SimpanPasswordBaru.

## Gambar 4. 14 Halaman Setting → Ubah Password



## 52


## 4.2 Pengujian
Pengujian  dilakukan  untuk  memastikan  bahwa  sistem  pemantauan  stok
yang  telah  dikembangkan  sudah  berfungsi  dengan  baik. Pengujian  dilakukan
melalui  dua  tahapan,  yaitu  pengujian penerimaan  pengguna dan  pengujian black
box.
## 4.2.1 Pengujian Penerimaan Pengguna
Pengujian  ini  dilakukan  dengan  menyebar kuesioner kepada  stakeholder
dengan  penerapan  skala  penilaian  Likert, kemudian  data  tersebut  dimasukkan
dalam rumus perhitungan. Skala penilaian Likert terdapat pada Tabel 4.3.

Tabel 4. 3 Skala penilaian Likert
## Skala Keterangan Skor Persentase
SS Sangat Setuju 5 100% - 80%
## S Setuju 4 79% - 60%
## C Cukup 3 59% - 40%
TS Tidak Setuju 2 39% - 20%
STS Sangat Tidak Setuju 1 19% - 0%

Hasil    dari    pengujian penerimaan    pengguna    (User    Acceptance
Testing/UAT)  terdapat  pada  Tabel  4.4. Pengujian dilakukan  kepada seluruh
pengguna  aplikasi  pemantauan  stok  ini  yaitu  pemilik,  pengelola  inventaris,  dan
karyawan.

Tabel 4. 4 Daftar pertanyaan dan hasil pengujian UAT
## No. Pertanyaan
## Skala
## Persentase
## 1 2 3 4 5
- Apakah fitur pengaturan ambang
batas minimum sudah membantu
dalam    mengetahui    persediaan
barang dan bahan menipis?
## 8
## 8
## 8
## ×100%=100%
- Apakah  fitur  penambahan  data
barang    sudah    sesuai    dengan
kebutuhan?
## 8
## 8
## 8
## ×100%=100%



## 53


Tabel 4. 4 Daftar pertanyaan dan hasil pengujian UAT (lanjutan)
## No. Pertanyaan
## Skala
## Persentase
## 1 2 3 4 5
- Apakah   fitur   menambah   dan
kurangi   stok   di   tabel   barang
sudah sesuai dengan kebutuhan?
## 8
## 8
## 8
## ×100%=100%
- Apakah  fitur  penambahan  data
master     bahan     sudah     sesuai
dengan kebutuhan?
## 8
## 8
## 8
## ×100%=100%
- Apakah  tabel  manajemen  bahan
sudah menampilkan kolom yang
sesuai dengan kebutuhan?
## 8
## 8
## 8
## ×100%=100%
- Apakah  fitur  tambah  stok  bahan
sudah sesuai dengan kebutuhan?
## 8
## 8
## 8
## ×100%=100%
- Apakah  fitur stock  opname pada
bahan    sudah    sesuai    dengan
kebutuhan?


## 8
## 8
## 8
## ×100%=100%
- Apakah  fitur  penambahan  menu
untuk mengatur komposisi bahan
terpakai   sudah   sesuai   dengan
kebutuhan?
## 8
## 8
## 8
## ×100%=100%
- Apakah    fitur    transaksi    yaitu
impor  excel  dan  tambah  manual
sudah sesuai dengan kebutuhan?
## 8
## 8
## 8
## ×100%=100%
- Apakah    aplikasi    pemantauan
stok membantu dalam
pengelolaan inventaris kafe?
## 8
## 8
## 8
## ×100%=100%

Hasil  dari  pengujian pada Tabel  4.4 yang  disebarkan kepada seluruh
pengguna  aplikasi  pemantauan  stok,  dapat  disimpulkan  bahwa  pengguna  setuju
sebesar (100%). Dari pertanyaan “Apakah aplikasi pemantauan stok membantu
dalam pengelolaan inventaris kafe?” dapat disimpulkan semua pengguna sudah
menerima aplikasi ini dengan baik, kemudian sudah digunakan dalam kurun waktu
satu bulan.


## 54


## 4.2.2 Pengujian Black Box
Pengujian black box dilakukan  untuk  memastikan  bahwa  sistem  sudah
berjalan  sesuai  dengan  alur  yang  sudah  dirancang  beserta  fungsi  dalam  aplikasi
berjalan  sebagaimana  yang  dibutuhkan  oleh  pengguna. Pengujian  ini  dilakukan
tanpa  melihat  atau  memeriksa  struktur  kode  program. Pengujian  fungsional yang
dilakukan pada aplikasi terdapat pada Tabel 4.5.

Tabel 4. 5 Pengujian fungsional melalui metode black box
## No.
## Kategori
## Pengguna
## Kode Deskripsi Kebutuhan Prioritas
## 1. Owner,
manajer, dan
staf.
SPSB-0001 Melakukan login untuk
mendapat mengakses
sistem.
## Tinggi
## 2. Owner,
manajer, dan
staf.
SPSB-0002 Masuk dan melihat
halaman dasbor.
## Tinggi
## 3. Owner,
manajer, dan
staf.
SPSB-0003 Masuk ke  halaman  stok
barang lalu dapat
melakukan CRUD
terhadap data tersebut
## Tinggi





## 4. Owner,
manajer, dan
staf.
SPSB-0004 Masuk  ke  halaman  stok
bahan lalu dapat
melakukan CRUD
terhadap data tersebut
## Tinggi
## 5. Owner,
manajer, dan
staf.
SPSB-0005 Masuk ke halaman daftar
menu lalu dapat
melakukan CRUD
terhadap data tersebut
## Tinggi
## 6. Owner,
manajer, dan
staf.
SPSB-0006 Masuk ke halaman
transaksi lalu dapat
melakukan CRUD
terhadap data tersebut
## Tinggi
- Owner dan
manajer.
SPSB-0007 Masuk ke halaman
temporary    delete lalu
dapat  melakukan  CRUD
terhadap data tersebut
## Tinggi



## 55


Tabel 4. 5 Pengujian fungsional melalui metode black box (lanjutan)
## No.
## Kategori
## Pengguna
## Kode Deskripsi Kebutuhan Prioritas
- Owner dan
manajer.
SPSB-0008 Masuk  ke  halaman  data
user lalu dapat
melakukan CRUD
terhadap data tersebut
## Tinggi
## 9. Owner,
manajer, dan
staf.
SPSB-0009 Masuk ke halaman
setting profil  dan  dapat
mengubah    profil user
yang sedang login.
## Tinggi
- Owner. SPSB-0010 Masuk ke halaman
setting token   API   dan
mampu  mengubah  token
tersebut.
## Tinggi
- Owner dan
manajer.
SPSB-0011 Masuk ke halaman
sambung   dan   putuskan
API whatsapp agar dapat
menerima atau
mematikan koneksi
notifikasi   stok   menipis
dan report dari  staf  di
whatsapp.
## Tinggi
## 12. Owner,
manajer, dan
staf.
SPSB-0012 Masuk  ke  halaman  ubah
password untuk
mengganti password
setelah   dibuatkan   akun
baru   oleh owner atau
manajer.
## Tinggi

Tabel   4.5   memperlihatkan   keseluruhan   pengujian   fungsional   sistem
dengan metode black box. Dari sisi pengintegrasian sistem dengan API WhatsApp,
yang perlu dilakukan pengujian black box yaitu pengujian dengan kode SPSB-0001,
SPSB-0002, SPSB-0009, SPSB-0010, SPSB-0011, dan SPSB-0012.


## 56


- Pengujian Halaman Login (SPSB-0001)
Pengujian  ini  dilakukan  selama  10  kali  untuk  memastikan  bahwa  fitur
login berjalan  sesuai  dengan  kebutuhan  sistem.  Setiap  pengujian  dimulai  dengan
membuka  halaman home kemudian  klik  halaman login,  setelah  itu  mencoba
memasukkan   kombinasi   data   yang   berbeda   untuk   mensimulasikan   berbagai
kemungkinan  skenario  saat  pengguna  (owner,  manajer,  staf)  mencoba  masuk  ke
dalam aplikasi. Tabel 4.6 memperlihatkan hasil pengujian halaman login.

Tabel 4. 6 Hasil pengujian halaman login
## Nama
## Pengujian
## Bentuk
## Pengujian
Hasil yang
## Diharapkan
## Hasil
## Pengujian
## Persentase
## Pengujian
masuk ke
halaman
login.
## Klik Login
pada    halaman
home.
## Menampilkan
halaman
login.
## Berhasil
100%  selama
10 kali
pengujian.
## Pengujian
login dengan
form kosong.
## Mengosongkan
semua atau
sebagian form
input dan
menekan
tombol login.
Tampil  pesan
bahwa
terdapat email
atau password
harus di isi.
## Berhasil
100%  selama
10 kali
pengujian.
## Pengujian
login dengan
data salah.
## Memasukkan
email atau
password yang
salah lalu
menekan
tombol login.
Tampil  pesan
“Email  atau
password
salah!”
## Berhasil
100%  selama
10 kali
pengujian.
## Pengujian
login dengan
data benar.
## Memasukkan
email dan
password yang
sesuai
kemudian
menekan
tombol login.
Login berhasil
dan  pengguna
diarahkan   ke
halaman
dasbor.
## Berhasil
100%  selama
10 kali
pengujian.



## 57


- Pengujian Halaman Dasbor (SPSB-0002)
Pengujian ini dilakukan selama 10 kali untuk memastikan bahwa informasi
penting  mengenai  kondisi  stok  barang  dan  bahan  dapat  terlihat  langsung  oleh
pengguna saat pertama kali masuk ke sistem. Fokus utama dari pengujian ini yaitu
mengecek  apakah  sistem  berhasil  menampilkan  barang  dan  bahan  yang  stoknya
berada  di  bawah batas  minimum  sesuai  dengan  data  yang  sebenarnya. Tabel  4.7
memperlihatkan hasil pengujian halaman dasbor.

Tabel 4. 7 Hasil pengujian halaman dasbor
## Nama
## Pengujian
## Bentuk Pengujian
Hasil yang
## Diharapkan
## Hasil
## Pengujian
## Persentase
## Pengujian
akses
halaman
dasbor.
Menekan menu
“Dashboard”  pada
sidebar atau melalui
dropdown di  kanan
atas   saat   masih   di
halaman home.
## Halaman
dasbor
berhasil
ditampilkan
dengan   data
yang
lengkap.
## Berhasil
## 100%
selama     10
kali
pengujian.
## Memastikan
barang     dan
bahan    yang
kurang    dari
batas
minimum
terlihat di
dasbor.
Sistem akan
menampilkan  daftar
barang   dan   bahan
yang stoknya
melewati batas
minimum.
Pengujian dilakukan
dengan mencoba
mengurangi stok
barang   atau   bahan
hingga melewati
batas minimum,
kemudian    kembali
ke   halaman   dasbor
semula.
## Semua
barang    dan
bahan
dengan   stok
dibawah
batas
minimum
tampil
otomatis    di
halaman
dasbor.
## Berhasil
## 100%
selama     10
kali
pengujian.



## 58


Tabel 4. 7 Hasil pengujian halaman dasbor (lanjutan)
## Nama
## Pengujian
## Bentuk Pengujian
Hasil yang
## Diharapkan
## Hasil
## Pengujian
## Persentase
## Menekan
tombol
“Report”
hanya    untuk
akun staf
saja.
Menekan tombol
“Report” dan
membuat   pesan   lalu
klik  “Kirim”  hingga
masuk   ke   whatsapp
owner dan    manajer
yang  sudah  memiliki
wa_api_token.
Pesan   yang
ditulis   oleh
akun staf
masuk ke
WhatsApp
owner dan
manajer
terdaftar.
## Berhasil
## 100%
selama   10
kali
pengujian.
## Responsivitas
halaman.
Akses halaman dasbor
dari    berbagai    layar
## (laptop/desktop/tablet)
## Tampilan
tidak   pecah
atau
terpotong
## Berhasil
## 100%
selama   10
kali
pengujian.

- Pengujian Halaman Setting Profil (SPSB-0009)
Pengujian  ini  dilakukan  selama  10  kali  untuk  memastikan  bahwa semua
pengguna  dapat  mengubah  profil  individu  melalui  halaman setting (pengaturan).
Tabel 4.8 memperlihatkan hasil pengujian halaman setting profil.

Tabel 4. 8 Hasil pengujian halaman setting profil
## Nama
## Pengujian
## Bentuk
## Pengujian
Hasil yang
## Diharapkan
## Hasil
## Pengujian
## Persentase
## Pengujian
akses
halaman
sertting
profil.
Menekan tombol
Setting pada
dropdown di
kanan   atas   saat
masih di halaman
dasbor atau
admin.
Halaman setting
berhasil
ditampilkan
dengan data
yang lengkap.
## Berhasil
## 100%
selama     10
kali
pengujian.
## Mengubah
profil dengan
menekan
tombol Edit
profil
Sistem akan
memunculkan
modal yang
berisi form  input
profil   kemudian
menekan  tombol
## Simpan
Semua    barang
dan bahan
dengan stok
dibawah    batas
minimum
tampil  otomatis
di halaman
dasbor.
## Berhasil
## 100%
selama     10
kali
pengujian.

## 59



- Pengujian Halaman Setting Token API Fonnte (SPSB-0010)
Pengujian ini dilakukan selama 10 kali untuk memastikan bahwa hanya user
owner yang dapat mengubah token api melalui halaman setting (token api). Tabel
4.9 memperlihatkan hasil pengujian halaman seting token API Fonnte.

Tabel 4. 9 Hasil pengujian halaman setting token API Fonnte
## Nama
## Pengujian
## Bentuk
## Pengujian
Hasil yang
## Diharapkan
## Hasil
## Pengujian
## Persentase
## Pengujian
akses
halaman
sertting
token api
fonnte.
Menekan sidebar
Atur Token API
pada sidebar
halaman setting.
Halaman setting
token api
berhasil
ditampilkan
dengan data
yang lengkap.
## Berhasil
## 100%
selama     10
kali
pengujian.
## Mengubah
token dengan
menekan
tombol Edit
token
Sistem akan
memunculkan
modal yang
berisi form  input
profil   kemudian
menekan  tombol
## Simpan
Semua    barang
dan bahan
dengan stok
dibawah    batas
minimum
tampil  otomatis
di halaman
dasbor.
## Berhasil
## 100%
selama     10
kali
pengujian.



## 60


- Pengujian Halaman Sambung atau Putuskan API Notifikasi (SPSB-0011)
Pengujian  ini  dilakukan  selama  10  kali  untuk  memastikan  bahwa user
owner dan  manajer  dapat  masuk  ke  halaman yang berfungsi untuk  menyambung
atau putuskan API Notifikasi dengan menekan satu tombol “Hubungkan Kembali”
atau “Putuskan Hubungan”. Cara  kerja  dari  tombol  tersebut  yaitu  memasukkan
wa_api_token di  SPSB-0011  ke  basis  data user yang  menekan  “Hubungkan
Kembali”.  Lalu  token  tersebut  dibuat  NULL  jika  pengguna  menekan  tombol
“Putuskan  Hubungan”.  Pengguna owner atau   manajer   yang   tidak   memiliki
wa_api_token tidak akan menerima notifikasi apapun. Tabel 4.10 memperlihatkan
hasil pengujian halaman sambung atau putuskan API notifikasi.

Tabel 4. 10 Hasil pengujian halaman sambung atau putuskan API notifikasi
## Nama
## Pengujian
## Bentuk
## Pengujian
Hasil yang
## Diharapkan
## Hasil
## Pengujian
## Persentase
## Pengujian
akses
halaman
sambung atau
putuskan   api
notifikasi.
## Menekan
sidebar API
Notifikasi pada
sidebar  halaman
setting.
## Halaman
sambung atau
putuskan api
notifikasi
berhasil
ditampilkan
dengan data yang
lengkap.
## Berhasil
## 100%
selama    10
kali
pengujian.
## Menyambung
dan  putuskan
api  notifikasi
whatsapp
Menekan tombol
## Hubungkan
## Kembali
kemudian
menekan tombol
## Putuskan
## Hubungan.
## Berhasil
menyambungkan
api notifikasi
saat ditekan
tombol
“Hubungkan
## Kembali”
kemudian    akan
terputus
hubungan  ketika
menekan  tombol
“Putuskan
## Hubungan”
## Berhasil
## 100%
selama    10
kali
pengujian.



## 61


- Pengujian Halaman Ubah Password (SPSB-0012)
Pengujian  ini  dilakukan  selama  10  kali  untuk  memastikan  bahwa  semua
pengguna dapat mengubah password individu melalui halaman setting (pengaturan)
kemudian “Ubah Password”. Tabel 4.11 memperlihatkan hasil pengujian halaman
ubah password.

Tabel 4. 11 Hasil pengujian halaman ubah password
## Nama
## Pengujian
## Bentuk Pengujian
Hasil yang
## Diharapkan
## Hasil
## Pengujian
## Persentase
## Pengujian
akses
halaman
ubah
password.
Menekan sidebar
## Ubah Password
pada sidebar
halaman setting.
## Halaman
ubah
password
berhasil
ditampilkan
dengan   data
yang
lengkap.
## Berhasil
## 100%
selama     10
kali
pengujian.
## Mengubah
password
lama dengan
mengisi
formulir
“Password
## Lama”,
“Password
Baru”,  dan
“Konfirmasi
## Password
## Baru”
kemudian
menekan
tombol
## Simpan
## Password
## Baru
Di    halaman    ubah
password terdapat
formulir yang
langsung  bisa  di  isi
oleh pengguna,
setelah     itu     dapat
menekan tombol
## Simpan   Password
Baru jika ingin
mengubah password
## Password
lama     milik
pengguna
diganti
menjadi
password
baru.
## Berhasil
## 100%
selama     10
kali
pengujian.



## 62


## 4.3 Pembahasan Ketercapaian Tujuan Penelitian
Pada tahap ini dilakukan pembahasan terkait hasil ketercapaian tujuan dari
penelitian  yang  telah  dilakukan. Ketercapaian  tujuan  ini  dilihat  dari  keberhasilan
dalam  pengintegrasian  dengan  API  WhatsApp  yaitu  mengirim  pesan  notifikasi
apabila terdapat pelaporan staf dan kondisi stok menipis. Untuk mengetahui kondisi
stok menipis, maka perlu dilakukan perhitungan ambang batas minimum sehingga
pesan  tidak  menumpuk  dan  tidak  terjadi  kekosongan  stok.  Perhitungan  ambang
batas  minimum  menggunakan  titik  aman  persediaan  (safety  stock)  dan  titik
pemesanan kembali (reorder point) yang dihitung menggunakan metode min-max.
Setelah ini akan dibahas tahapan penerapan metode min-max.
- Kebutuhan data
Nilai kebutuhan data dalam satu bulan diketahui dengan mengambil data
transaksi  yang  ada  di  sistem  selama  satu  bulan.  Setelah  itu menghitung  deviasi
standar dengan  data  yang diperlukan yaitu total kebutuhan  dan  total  rata-rata
kebutuhan. Proses pengambilan data kebutuhan melalui rekap bulanan bahan yaitu
pada  kolom  terpakai seperti  pada  Gambar  4.15 yang  memperlihatkan data  bahan
espresso  di  bulan  Juni  2025. Contoh  kebutuhan  data  riil  yang  terdapat  di  kafe
B.di.M pada bulan Mei 2025 untuk bahan dada fillet terdapat pada Tabel 4.12.

Gambar 4. 15 Mengambil data kebutuhan (terpakai di aplikasi)


## 63


Tabel 4. 12 Kebutuhan data
Bahan Dada Fillet (dalam gram)
## Hari
## Kebutuhan
## (푇)
## Rata-rata
## Kebutuhan (푇)
## (푇−푇) (푇−푇)
## 2

## 1. 120
## 882,58
## -762,58 581.529,24
## 2. 480
## 882,58
## -402,58 162.071,18
## 3. 120 882,58
## -762,58
## 581.529,24
## 4. 480 882,58
## -402,58
## 162.071,18
## 5. 300
## 882,58
## -582,58
## 339.400,21
## 6. 360 882,58
## -522,58
## 273.090,53
## 7. 300 882,58
## -582,58
## 339.400,21
## 8. 120 882,58 -762,58 581.529,24
## 9. 660 882,58 -222,58 49.542,14
## 10 240 882,58 -642,58 412.909,89
## 11. 120 882,58 -762,58 581.529,24
## 12. 180 882,58 -702,58 493.619,56
## 13. 300 882,58 -582,58 339.400,21
## 14. 300 882,58 -582,58 339.400,21
## 15. 600 882,58 -282,58 79.851,82
## 16. 600 882,58 -282,58 79.851,82
## 17. 1560 882,58 677,42 458.896,98
## 18. 5640 882,58 4.757,42 22.633.038,92
## 19. 960 882,58 77,42 5.993,76
## 20. 300 882,58 -582,58 339.400,21
## 21. 60 882,58 -822,58 676.638,92
## 22. 480 882,58 -402,58 162.071,18
## 23. 2760 882,58 1.877,42 3.524.703,43
## 24. 120 882,58 -762,58 581.529,24
## 25. 2940 882,58 2.057,42 4.232.974,40
## 26. 960 882,58 77,42 5.993,76
## 27. 1500 882,58 617,42 381.206,66
## 28. 2100 882,58 1.217,42 1.482.109,89
## 29. 480 882,58 -402,58 162.071,18
## 30. 1980 882,58 1.097,42 1.204.329,24
## 31. 240 882,58 -642,58 412.909,89
## Jumlah
## 27.360
## 41.660.593,55

Telah  didapatkan  data  kebutuhan dada fillet. Hasil  perhitungan  di  Tabel
4.12 dan data yang ada di lapangan dapat disimpulkan data-data berikut:
a. Rata-rata kebutuhan (T) bahan ayam fillet per hari sebesar 882,58 gram
b. Safety factor sebesar 1.5.
c. Waktu tunggu pemesanan (LT) dada fillet selama 1 hari.
d. Rata-rata jarak pemesanan (tb) dada fillet yaitu 3,5 hari dengan data pembelian stok
setiap 3 atau 4 hari (9 kali dalam satu periode).

## 64


## 2. Perhitungan Deviasi Standar
Di tahap ini, perhitungan standar deviasi digunakan untuk menentukan nilai
safety  stock menggunakan  data pada  Tabel  4.12. Persamaan 4.1  merupakan
persamaan untuk menghitung deviasi standar.

## 퐷푒푣푖푎푠푖 푆푡푎푛푑푎푟=
## √
## ∑
## (푇−푇)
## 2
## 푗푢푚푙푎ℎ 푝푒푟푖표푑푒−1

## (4.1)
## =
## √
## 41.660.594
## 31−1

## =1.178,43 푔푟푎푚
## =1.178 푔푟푎푚
## (4.2)

## 3. Perhitungan Safety Stock
Setelah  diketahui  nilai  deviasi  standar, selanjutnya dilakukan  perhitungan
safety  stock dari  data  persamaan  4.2. Persamaan  4.3  merupakan formula untuk
mencari safety stock, kemudian persamaan 4.4 merupakan nilai safety factor yang
digunakan  pada  perhitungan  ini. Hasil  perhitungan safety  stock terdapat  pada
persamaan 4.5.

## 푆푎푓푒푡푦 푆푡표푐푘=푆푎푓푒푡푦 퐹푎푐푡표푟×퐷푒푣푖푎푠푖 푆푡푎푛푑푎푟
## (4.3)
## 푆푎푓푒푡푦 퐹푎푐푡표푟=1,5
## (4.4)
## 푆푎푓푒푡푦 푆푡표푐푘=1,5×1178,43
## =1.767,64
## =1.768 푔푟푎푚
## (4.5)



## 65


- Perhitungan Metode Min-Max
Pada  tahap  ini  dilakukan  perhitungan  ambang  batas  minimum  dan  batas
maksimum    yang    direkomendasikan    berdasarkan    kebutuhan    sebelumnya.
Perhitungan persediaan  minimum, maksimum,  dan  jumlah  pemesanan  kembali
adalah sebagai berikut:
Perhitungan persediaan minimum. Hasil perhitungan persediaan minimum
dapat dilihat melalui persamaan 4.6.

## 푃푒푟푠푒푑푖푎푎푛 푀푖푛푖푚푢푚=
## (
## 푇×퐿푇
## )
## +푆푆
## =
## (
## 882,58×1
## )
## +1768,64
## =2.650,22
## =2.650 푔푟푎푚
## (4.6)

Perhitungan persediaan maksimum. Hasil    perhitungan    persediaan
maksimum dapat dilihat melalui persamaan 4.7.

## 푃푒푟푠푒푑푖푎푎푛 푀푎푘푠푖푚푢푚=(푡푏+1)×
## (
## 푇×퐿푇
## )
## +푆푆
## =3,5×
## (
## 882,58×1
## )
## +1768,64
## =5.739,25
## =5.739 푔푟푎푚
## (4.7)

Jumlah pemesanan kembali. Hasil perhitungan jumlah pemesanan kembali
dapat dilihat melalui persamaan 4.8.

## 푄=(푃푒푟푠푒푑푖푎푎푛 푀푎푘푠푖푚푢푚−푃푒푟푠푒푑푖푎푎푛 푀푖푛푖푚푢푚)
## =(5.739−2.650)
## =3089 푔푟푎푚
## (4.8)


## 66


Dapat  dilihat  dari  hasil  perhitungan persamaan  4.1  hingga  4.8 bahwa  semua
perhitungan  menghasilkan  nilai  persediaan  minimum,  maksimum,  dan  jumlah
pemesanan  kembali. Nilai  persediaan  minimum  digunakan  untuk  menentukan
ambang batas minimum pada sistem. Apabila stok menipis atau berada di bawah
batas minimum, maka notifikasi akan dikirimkan ke WhatsApp Owner dan manajer
yang   sudah   memiliki wa_api_token. Gambar   4.16 memperlihatkan   cara
memasukkan perhitungan nilai ambang batas minimum ke sistem yang dilakukan
oleh manajer.

Gambar 4. 16 Memasukkan nilai ambang batas minimum ke sistem oleh manajer

Gambar 4. 17 Pesan notifikasi stok menipis di WhatsApp
Gambar  4.17  memperlihatkan  tampilan  pesan  notifikasi  stok  menipis  di
WhatsApp owner dan manajer terdaftar. Terdapat dua jenis pesan yaitu notifikasi
bahan menipis dan notifikasi barang menipis. Informasi yang ditampilkan yaitu stok
saat ini, pengaturan batas minimum, dan sisa sebelum dilakukan transaksi dan stock
opname (untuk  bahan) atau  pengurangan  barang (untuk  barang). Selain  itu,
notifikasi stok menipis juga akan dikirimkan setiap malam hari untuk keseluruhan
stok bahan dan barang menipis.


## 67

## BAB V
## PENUTUP

## 5.1 Kesimpulan
Berdasarkan hasil implementasi dan pengujian yang telah dilakukan dapat
disimpulkan beberapa hal berikut:
- Sistem  pemantauan  stok  di  Kafe  B.di.M  berhasil  dirancang  untuk  mengelola
stok   barang   dan   bahan   kafe   dengan   lebih   efektif   dan   dapat   menerima
peringatan stok menipis lebih dini di WhatsApp.
- Dari segi kualitatif, pengujian penerimaan pengguna menunjukkan nilai 100%
yang  berarti  pengguna  merasa bahwa  aplikasi  ini  dapat  dimanfaatkan  dan
membantu pekerjaan inventaris mereka, namun di awal masih perlu pelatihan
penggunaan untuk dapat mengoperasikan sistem dengan lancar.
- Berdasarkan   perhitungan min-max dapat   diketahui   nilai   ambang   batas
minimum  dan  maksimum  yang  dihitung  dari  penggunaan  barang  atau  bahan
dalam satu periode (bulan). Dari data ini, jumlah persediaan yang perlu di beli
atau di tambah dapat diketahui.
Penelitian ini telah sukses merancang, mengembangkan, mengimplementasi,
dan menguji sistem pemantauan stok di Kafe B.di.M untuk mengelola barang dan
bahan kafe dengan lebih efektif dan mampu memberikan peringatan notifikasi dini
jika  stok  menipis  atau  berada  di  bawah ambang batas  minimum  yang telah
ditentukan. Sistem  ini  mampu  menggantikan  proses  bisnis  inventaris  kafe  yang
sebelumnya  dilakukan  dengan  metode  konvensional,  sekarang  dapat  beralih  ke
bentuk  digital  dan  dapat  diakses  dari  manapun  dan  kapanpun  asalkan  pengguna
terhubung ke akses internet. Diharapkan implementasi sistem ini dapat membantu
pemilik, pengelola inventaris, serta staf dalam melakukan pemeriksaan stok barang
dan bahan kafe.


## 68


## 5.2 Saran
Berdasarkan  hasil  penelitian  dan  pengembangan  yang  sudah  dilakukan,
adapun saran pengembangan lebih lanjutnya yaitu sebagai berikut.
- Disarankan untuk mengembangkan aplikasi pemantauan stok berbasis Android
atau iOS agar pengguna dapat mengakses data stok barang dan bahan dengan
lebih fleksibel dan praktis, kapan saja dan di mana saja. Dengan adanya aplikasi
berbasis perangkat bergerak ini, pengguna tidak perlu bergantung pada laptop,
desktop, atau tablet.
- Adanya  kajian  lebih  lanjut  mengenai  cara  mengelola  stok  yang  lebih  baik
seperti   perhitungan   dengan   metode weighted   moving   average, double
exponential smoothing, atau vector machine.
- Pengembangan lebih lanjut untuk metode min-max yang lebih optimal dengan
penyesuaian terhadap toko,   perusahaan,   atau   kafe   yang   berbeda yang
berpengaruh terhadap perhitungan persamaan.


## 69


## DAFTAR PUSTAKA

[1]  R.  Kasamska dan R.  Tsvetkova,  “Monitoring,  Evaluation,  and  Quality
Assurance in Project Management,” VUZF Review, vol. 3, no. 1, pp. 100-109,
## 2018.
[2]  A. Herliana dan P. M. Rasyid, “Sistem Informasi Monitoring Pengembangan
Software pada Tahap Delevopment Berbasis Web,” Jurnal Informatika,, vol.
III, no. 1, pp. 41-50, April 2016.
[3]  A. Tandilintin, A. P. Candra dan G. S. Adji, “Perancangan Aplikasi Project
Monitoring pada PT Cyber Solution Berbasis Web,” vol. 5, no. 1, pp. 68-76,
2019, https://doi.org/10.33050/icit.v5i1.104.
[4]  D. Syafaat dan A. S. Fitrani, “Sistem Informasi Inventory dengan Notifikasi
Bot  Telegram  Berbasis  Website  (Studi  Kasus  PT.  Global  Data  Akses),”
Indonesian  Journal  of  Applied  Technology, vol.  1,  no.  2,  pp.  1-18,  2024,
https://doi.org/2010.47134/ijat.v1i2.3055.
[5]  A. Afriansyah dan R. Annisa, “Sistem Data Penjualan dan Monitoring Stok
Barang pada Toko Keripik Aiza,” Jurnal Komputer dan Informatika, vol. 4,
no. 2, pp. 88-97, November 2022, https://doi.org/10.53842/juki.v4i2.118.
[6]  R.  Saputra,  S.  Sumarlinda dan Wijiyanto,  “Sistem  Informasi  Persediaan
Barang dengan Metode Perpetual pada Toko Mebel Sidarta Berbasis Web,”
Jurnal Teknologi Informasi dan Multimedia, vol. 6, no. 2, pp. 147-160, 2024,
https://doi.org/10.35746/jtim.v6i2.544.
[7]  C.  R.  K.  Putra,  M.  Z.  Rohman dan B. Cahyono, “Implementation of Web
Inventory Tracking and Monitoring for PT Sucofindo Samarinda Branch,”
Jurnal Aplikasi Teknik dan Pengabdian Masyarakat, vol. 8, no. 4, pp. 123-
## 130, 2024.
[8]  Fitriana,  A.  Setiawan dan R. R. Setiawan, “Transformasi Pengelolaan Stok
Gudang  Raja  Vapor  Gebog  Melalui  Sistem  Informasi  Berbasis  Web  dan
Metode Activity Based Costing,” Jurnal Teknik Informatika, vol. 5, no. 1, pp.
290-300, 2025, https://doi.org/10.58794/jekin.v5i1.1322.
[9]  N. L. Rachmawati dan Lentari Mutiara, “Penerapan Metode Min-Max untuk
Minimasi Stockout dan Overstock Persediaan Bahan Baku,” Jurnal INTECH
Teknik  Industri  Universitas  Serang  Raya, vol.  8,  no.  2,  pp.  143-148,  2022,
https://doi.org/10.30656/intech.v8i2.4735.
[10]  R.  S.  Pressman,  Software  Engineering:  A  Practitioner's  Approach  (7th
Edition), McGraw - Hill, New York: McGraw Hill Higher Education, 2010.
[11]  S. Ian, Software Engineering (9th Edition), Boston, Massachusetts: Pearson
## Education, 2011.
[12]  A.  Shrivastava,  I.  Jaggi,  N.  Katoch,  D.  Gupta dan S. Gupta, “A Systematic
Review on Extreme Programming,” Journal of  Physics: Conference Series,
pp. 1-11, 2021, https://doi.org/10.32409/jikstik.23.3.3641.



## 70


[13]  G. Zahra dan I. Supriadi, “Evaluasi Pengendalian Persediaan terhadap Hasil
Stock Opname melalui Sistem Informasi Akuntansi pada Gota Minimarker,”
Jurnal  Akuntansi  Manajemen  Bisnis  dan  Teknologi, vol.  1,  no.  2,  pp.  220-
231, 2021, https://doi.org/10.56870/ambitek.v1i2.25.
[14]  S.  Aisyah dan O.  N.  Irama,  “Pengaruh  Metode  Pencatatan  Persediaan
terhadap  Stock  Opname  pada  PT. Sagami Indonesia,” Edu  Society:  Jurnal
Pendidikan, Ilmu Sosial, dan PengabdianKepada Masyarakat, vol. 5, no. 1,
pp. 30-36, 2025.
[15]  G.  R.  Terry dan D.  F.  J.  Smith,  Prinsip-Prinsip  Manajemen,  Jakarta:  Bumi
## Aksara, 2009.
[16]  A.   Jaenudin,   D.   Wahyuningtyas dan P.  D.  A.  Pamungkas,  “Sistem
Pemantauan dan Pemeliharaan Perangkat Teknologi Informasi Berbasis Web
pada Departemen IT PT Dengso Indonesia Bekasi,” Jurnal Mahasiswa Bina
Insani, vol. 1, no. 1, pp. 119-134, 2016.
[17]  A. V. Langke,  I. D. Palandeng dan M. M. Karuntu, “Analisis Pengendalian
Persediaan Bahan Baku Kelapa pada PT. Tropica Cocoprima menggunakan
Economic Order Quantity,” Jurnal EMBA, vol. 6, no. 3, pp. 1158-1167, 2018.
[18]  A.  C.  R.  Kussing,  A.  Ahistasari dan T. Tajuddin, “Analisis Pengendalian
Persediaan   Bahan   Baku   menggunakan   Metode   Min-Max,” Industrial
Engineering Journal, vol. 1, no. 1, pp. 33-42, 2022,
https://doi.org/10.33506/system.v1i1.1990.
[19]  R. F. Schmidt, “Chapter 10 - Formulating the Functional Architecture,” in
## Software  Engineering  Architecture-driven  Software  Development,  Elsevier
Inc., 2013, pp. 173-184.
[20]  I. Setyawibawa dan A. Goeritno, “Communication Interface Adapter Berbasis
## Mikrokontroler  Arduino  Terkendali  Sinyal  Dual  Tone  Multi  Frequency,”
Jurnal Teknik Elektro, vol. 11, no. 1, pp. 19-26, 2019.
[21]  B. Beizer, Software Testing Techniques, London: ITP Media Group, 1990.
[22]  G.  J.  Myers,  C.  Sandler dan T.  Badgett,  The  Art  of  Software  Testing  (3rd
## Edition), Canada: John Wiley & Sons, Inc, 2012.
[23]  N.  Aini,  S.  A.  Wicaksono dan I. Arwani, “Pembangunan Sistem Informasi
Perpustakaan   Berbasis   Web   menggunakan   Metode   Rapid   Application
Development  (RAD)  (Studi  pada  :  SMK  Negeri  11  Malang),” Jurnal
Pengembangan  Teknologi  Informasi  dan  Ilmu  Komputer, vol.  3,  no.  9,  pp.
## 8647-8655, 2019.
[24]  E. L. Hady, K. Haryono dan N. W. Rahayu, “User Acceptance Testing (UAT)
pada Purwarupa Sistem Tabungan Santri (Studi Kasus: Pondok Pesantren Al-
Mwaddah),” Jurnal Ilmiah Multimedia dan Komunikasi, vol. 5, no. 1, pp. 1-
10, 2020, https://doi.org/10.56873/jimk.v5i1.64.
[25]  P. M. Jacob dan M. Prasanna, “A Comparative Analysis on Black Box Testing
Strategies,” International Conference on Information Science (ICIS), 2016.
[26]  R. Mall, Fundamentals of Software Engineering (3rd Edition), Prentice Hall
of India, 2009.

## 71







## BIODATA MAHASISWA
## Nama Mahasiswa : Rendy Hartono Putra
## NIM : 21120121130071
## Konsentrasi : Software
Alamat Sekarang : Puri Delta Asri 5 blok C2 no. 28 Jalan
Pinus III, Desa Kalongan, Ungaran
## Timur, Kab. Semarang.
No. Telepon/HP : 081226077106
Nama orang tua : Subur Hartono
Alamat orang tua : RT 01/ RW 02 Desa Cokroyasan,
## Kec. Ngombol, Kab. Purworejo
Telp./HP: 082325226558
IP Kumulatif : 3.77
## Tanggal Lulus
## : 30 Juni 2025
## Masa Studi : 4 Tahun

Pengalaman dan Prestasi yang pernah diraih:
- Kepala Bidang HRD, Al-Muharrik
- Asisten praktikum Praktikum Pemrograman Perangkat Bergerak,
Switching Routing and Wireless Essentials, dan Sistem Digital Lanjut.

## Semarang, 27 Mei 2025



## Rendy Hartono Putra
