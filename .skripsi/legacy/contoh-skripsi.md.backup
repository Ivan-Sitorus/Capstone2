### RANCANG BANGUN SISTEM PORTAL PARKIR PENDETEKSI

### KELENGKAPAN BERKENDARA DAN KETERSEDIAAN RUAS PARKIR

### MENGGUNAKAN INTEGRASI ARDUINO UNO DAN RASPBERRY PI

# TUGAS AKHIR

# Diajukan sebagai salah satu syarat untuk memperoleh gelar

# Sarjana Teknik

# AGUSTINUS ADVEN CHRISTO

# 21120121140114

# DEPARTEMEN TEKNIK KOMPUTER

# FAKULTAS TEKNIK

# UNIVERSITAS DIPONEGORO

# SEMARANG

# 2025


```
i
```
### HALAMAN PENGESAHAN

Tugas Ahkir ini diajukan oleh:
Nama : Agustinus Adven Christo
NIM : 21120121140114
Departemen : Teknik Komputer
Judul : Rancang Bangun Sistem Portal Parkir Pendeteksi
Kelengkapan Berkendara dan Ketersediaan Ruas Parkir
Menggunakan Integrasi Arduino Uno dan Raspberry Pi

Telah berhasil dipertahankan dihadapan Tim Penguji dan diterima sebagai bagian
persyaratan yang diperlukan untuk memperoleh gelar Sarjana Teknik pada
Jurusan/Program Studi Teknik Komputer, Fakultas Teknik, Universitas
Diponegoro.

### TIM PENGUJI

```
Pembimbing I : nama ( )
Pembimbing II : nama ( )
Ketua Penguji : Nama ( )
Anggota Penguji : Nama ( )
```
```
Semarang, 16 Januari 2025
Kepala Departemen Teknik Komputer
```
```
Dr. Oky Dwi Nurhayati, S.T., M.T.
NIP. 197910022009122001
```

```
ii
```
### HALAMAN PERNYATAAN ORISINALITAS

**Tugas Akhir ini adalah hasil karya saya sendiri,
dan semua sumber baik yang dikutip maupun yang dirujuk telah saya
nyatakan dengan benar.**

```
Nama : Agustinus Adven Christo
NIM : 21120121140114
Tanda Tangan :
```
```
Tanggal : Semarang, 16 Januari 2025
```

```
iii
```
### HALAMAN PERNYATAAN PERSETUJUAN PUBLIKASI TUGAS

### TUGAS AKHIR UNTUK KEPENTINGAN AKADEMIS

Sebagai sivitas akademika Universitas Diponegoro, saya yang bertanda tangan di
bawah ini :
Nama : Agustinus Adven Christo
Nim : 21120121140114
Departemen : Teknik Komputer
Fakultas : Teknik
Jenis Karya : Tugas Akhir
demi pengembangan ilmu pengetahuan, menyetujui untuk memberikan kepada
Universitas Diponegoro **Hak Bebas Royalti Noneksklusif** ( _Non-exclusive Royalty
Free Right_ ) atas karya ilmiah saya berjudul:
**Judul**
Beserta perangkat yang ada (jika diperlukan). Dengan Hak Bebas
Royalti/Noneksklusif ini Universitas Diponegoro berhak menyimpan,
mengalihmedia/formatkan, mengelola dalam bentuk pangkalan data ( _database_ ),
merawat dan memublikasikan Tugas Akhir saya selama tetap mencantumkan nama
saya sebagai penulis/pencipta dan sebagai pemilik Hak Cipta.
Demikian pernyataan ini saya buat dengan sebenarnya.

(^) Dibuat di : Semarang
Pada Tanggal : 16 Januari 2025
Yang Menyatakan
Agustinus Adven Christo


```
iv
```
### KATA PENGANTAR

Puji dan syukur dipanjatkan kepada Tuhan Yang Maha Esa karena atas
berkat dan karunia-Nya, penulis dapat menyelesaikan laporan Tugas Akhir ini yang
berjudul **“Rancang Bangun Sistem Portal Parkir Pendeteksi Kelengkapan
Berkendara dan Ketersediaan Ruas Parkir Menggunakan Integrasi Arduino
Uno dan Raspberry Pi”**.
Tugas Akhir merupakan salah satu syarat yang wajib dilakukan untuk
menyelesaikan studi di Departemen Teknik Komputer Fakultas Teknik Universitas
Diponegoro.
Dalam penyusunan Tugas Akhir ini, penulis senantiasa mendapatkan doa,
dukungan, bimbingan, arahan serta bantuan dari berbagai pihak. Oleh karena itu,
pada kesempatan ini penulis ingin menyampaikan rasa terima kasih kepada:

1. Ibu Dr. Oky Dwi Nurhayati, S.T., M.T. selaku Ketua Departemen Teknik
    Komputer Universitas Diponegoro.
2. Ibu Dania Eridani, S.T., M.Eng. selaku dosen pembimbing I yang telah
    memberikan arahan serta bimbingan dalam pengerjaan dan penulisan Tugas
    Akhir.
3. Ibu Patricia Evericho Mountaines, S.T., M.Cs. selaku dosen pembimbing II
    yang telah arahan serta bimbingan dalam pengerjaan dan penulisan Tugas
    Akhir.
4. Seluruh jajaran dosen Departemen Teknik Komputer Universitas Diponegoro
    yang senantiasa memberikan ilmu serta dukungan kepada seluruh mahasiswa
5. Kedua orang tua, saudara serta keluarga besar penulis atas segala doa dan
    dukungan yang tidak terhitung selama pengerjaan Tugas Akhir.
6. Anggota kelompok 20 Capstone Siklus 2 Tahun 2024 yaitu M. Bintang
    Prayoga Utama dan Yosia Aser Camme yang telah bekerja sama dan
    membantu dalam menyelesaikan proyek Tugas Akhir.
7. Sahabat dan teman-teman penulis yang memberikan dukungan serta doa
    kepada penulis.
8. Keluarga besar Teknik Komputer, khususnya angkatan 2021 “Syena” yang
    senantiasa memberikan dukungan.


```
v
```
9. Staf Tenaga Kependidikan Departemen Teknik Komputer Universitas
    Diponegoro yang telah bekerja dengan baik.
10. Serta semua pihak yang tidak dapat penulis sebutkan secara satu persatu yang
    telah membantu hingga terselesaikannya Tugas Akhir ini.

Penulis menuangkan pengetahuan dan pemahaman yang dimiliki dalam
pembuatan Tugas Akhir ini dan menyadari bahwa masih banyak kekurangan dalam
proses penyusunan hingga selesai, sehingga kritik dan saran yang membangun
sangat diharapkan guna menyempurnakan laporan Tugas Akhir ini yang jauh dari
kata sempurna. Semoga laporan Tugas Akhir ini dapat bermanfaat bagi pembaca.
Akhir kata, penulis ucapkan terima kasih.

Semarang,^16 Januari 2025^

```
Agustinus Adven Christo
```

```
vi
```
### DAFTAR ISI

HALAMAN PENGESAHAN ...................................................................................i
HALAMAN PERNYATAAN ORISINALITAS .................................................... ii
HALAMAN PERNYATAAN PERSETUJUAN PUBLIKASI TUGAS .............. iii
KATA PENGANTAR ............................................................................................iv
DAFTAR ISI ...........................................................................................................vi
DAFTAR GAMBAR ..............................................................................................ix
DAFTAR TABEL ...................................................................................................xi
ABSTRAK ............................................................................................................ xii
ABSTRACT ......................................................................................................... xiii
BAB I PENDAHULUAN ........................................................................................ 1
1.1 Latar Belakang ........................................................................................ 1
1.2 Rumusan Masalah ................................................................................... 3
1.3 Batasan Masalah ..................................................................................... 3
1.4 Tujuan Penelitian .................................................................................... 4
1.5 Manfaat Penelitian .................................................................................. 4
1.6 Metodologi Penelitian ............................................................................. 4
1.7 Sistematika Penulisan ............................................................................. 6
BAB II KAJIAN PUSTAKA ................................................................................... 7
2.1 Penelitian Terdahulu ............................................................................... 7
2.2 Landasan Teori ...................................................................................... 15
2.2.1 Raspberry Pi ....................................................................................... 15
2.2.2 Hailo AI Kit ....................................................................................... 16
2.2.3 Arduino .............................................................................................. 17
2.2.4 Linear Actuator Motor ....................................................................... 18
2.2.5 Motor Driver ...................................................................................... 19
2.2.6 Portal Parkir ( _Barrier Gate_ ) ............................................................... 19
2.2.7 Kamera ............................................................................................... 20
2.2.8 _Loop Vehicle Detector_ ....................................................................... 21


## vii



ix



xi

- BAB III PERANCANGAN SISTEM
   - 3.1 Gambaran Umum Sistem
      - 3.1.1 Fungsi Utama Produk
      - 3.1.2 Batasan Sistem
   - 3.2 Identifikasi Kebutuhan Sistem
      - 3.2.1 Kebutuhan Fungsional
      - 3.2.2 Kebutuhaan Non-Fungsional
   - 3.3 Perancangan Perangkat Keras
      - 3.3.1 Diagram Blok Sistem
      - 3.3.2 Diagram Alur Sistem
      - 3.3.3 Diagram Wiring Sistem
      - 3.3.4 Skematik
      - 3.3.5 Desain 3D Sistem
   - 3.4 Lingkungan Pengembangan Sistem
      - 3.4.1 Lingkungan Pengembangan
      - 3.4.2 Lingkungan Operasional
   - 3.5 Metode Pengujian
- BAB IV IMPLEMENTASI DAN PENGUJIAN
   - 4.1 Implementasi Produk
      - 4.1.1 Fabrikasi Desain 3D Wadah
      - 4.1.2 Fabrikasi Desain 3D Bingkai Wadah
      - 4.1.3 Fabrikasi Desain 3D Tiang Penyangga IP Kamera
      - 4.1.4 Pemasangan Linear Actuator Motor
      - 4.1.5 Pemasangan Arduino Uno
      - 4.1.6 Pemasangan Motor Driver L298N
      - 4.1.7 Pemasangan Power Supply
      - 4.1.8 Pemasangan Loop Vehicle Detector
      - 4.1.9 Pemasangan Raspberry Pi
      - 4.1.10 Konfigurasi Raspberry Pi
      - 4.1.11 Konfigurasi Arduino Uno
   - 4.2 Pengujian Produk
      - 4.2.1 Pengujian Fungsional viii
      - 4.2.2 Pengujian Non-Fungsional
- BAB V PENUTUP
   - 5.1 Kesimpulan
   - 5.2 Saran
- DAFTAR PUSTAKA
- LAMPIRAN I BIODATA MAHASISWA
- Gambar 3.1 Gambaran umum sistem DAFTAR GAMBAR
- Gambar 3.2 Diagram blok sistem
- Gambar 3.3 Diagram alur sistem
- Gambar 3.4 Diagram wiring sistem
- Gambar 3.5 Skematik
- Gambar 3.6 Desain 3D sistem
- Gambar 3.7 Denah peletakan IP kamera
- Gambar 3.8 Hollow Galvanis
- Gambar 3.9 ACP
- Gambar 4.1 Tampilan akhir sistem portal parkir
- Gambar 4.2 Tampilan akhir tiang penyangga IP kamera
- Gambar 4.3 Desain wadah tampak depan
- Gambar 4.4 Hasil fabrikasi wadah tampak depan
- Gambar 4.5 Desain wadah tampak belakang
- Gambar 4.6 Hasil fabrikasi wadah tampak belakang
- Gambar 4.7 Detail baut dan lubang.......................................................................
- Gambar 4.8 Detail baut dan lubang diperbesar
- Gambar 4.9 Desain bingkai wadah tampak depan
- Gambar 4.10 Desain fabrikasi bingkai wadah tampak depan
- Gambar 4.11 Desain tiang penyangga IP kamera
- Gambar 4.12 Hasil fabrikasi tiang penyangga IP kamera
- Gambar 4.13 Plat untuk tempat IP kamera
- Gambar 4.14 Linear Actuator Motor pada wadah sistem
- Gambar 4.15 Arduino Uno pada wadah sistem
- Gambar 4.16 Arduino Shield dari sebuah papan PCB
- Gambar 4.17 Motor Driver L298N pada wadah sistem
- Gambar 4.18 Power Supply pada wadah sistem
- Gambar 4.19 Loop Vehicle Detector pada wadah sistem
- Gambar 4.20 Raspberry Pi 5 pada wadah sistem
- Gambar 4.21 Keseluruhan komponen di dalam wadah x
- Gambar 4.22 Instalasi sistem operasi Raspberry Pi
- Gambar 4.23 Aktivasi VNC Server
- Gambar 4.24 Instalasi Visual Studio Code
- Gambar 4.25 Instalasi Hailo-8L berhasil
- Gambar 4.26 File Requirements.txt
- Gambar 4.27 File startprogram.desktop
- Gambar 4.28 Pengujian Loop Vehicle Detector
- Gambar 4.29 Pengujian IP kamera........................................................................
- Gambar 4.30 Pengujian USB kamera
- Gambar 4.31 Pengujian Beban Kerja CPU tanpa Hailo-8L
- Gambar 4.32 Pengujian Penggunaan Memori tanpa Hailo-8L
- Gambar 4.33 Pengujian Suhu CPU tanpa Hailo-8L
- Gambar 4.34 Pengujian Beban Kerja CPU dengan Hailo-8L
- Gambar 4.35 Pengujian Penggunaan Memori dengan Hailo-8L
- Gambar 4.36 Pengujian Suhu CPU dengan Hailo-8L
- Tabel 2.1 Penelitian terdahulu............................................................................... DAFTAR TABEL
- Tabel 3.1 Perincian diagram blok sistem
- Tabel 3.2 Perincian skematik
- Tabel 3.3 Perincian desain wadah
- Tabel 3.4 Perincian desain bingkai wadah
- Tabel 3.5 Perincian tiang penyangga IP kamera
- Tabel 3.6 Perhitungan ukuran wadah
- Tabel 3.7 Perhitungan ukuran bingkai wadah
- Tabel 4.1 Pengujian Fungsional
- Tabel 4.2 Pengujian Loop Vehicle Detector
- Tabel 4.3 Pengujian IP Kamera
- Tabel 4.4 Pengujian USB kamera
- Tabel 4.5 Pengujian waktu respons sistem
- Tabel 4.6 Pengujian Performa tanpa Integrasi Hailo-8L
- Tabel 4.7 Pengujian Performa dengan Integrasi Hailo-8L....................................


```
xii
```
### ABSTRAK

_Jumlah kendaraan bermotor di Indonesia terus meningkat, mencapai lebih dari 125 juta
unit pada tahun 2022. Namun, kepatuhan terhadap aturan lalu lintas, terutama penggunaan helm,
masih rendah. Di Universitas Diponegoro, mahasiswa wajib memakai helm dan mematuhi batas
kecepatan, tetapi pemeriksaan masih dilakukan manual, berisiko menimbulkan kesalahan. Selain
itu, pengendara kerap kesulitan menemukan ruang parkir, menyebabkan antrean.
Penelitian ini merancang sistem portal parkir berbasis pembelajaran mesin menggunakan
Raspberry Pi 5 untuk mendeteksi kelengkapan berkendara serta menyediakan informasi
ketersediaan ruas parkir, dan Arduino Uno untuk mengontrol portal. Raspberry Pi 5 memproses
visi komputer dari IP dan USB kamera, sedangkan Arduino Uno mengontrol linear actuator motor
melalui motor driver L298N untuk membuka portal, didukung loop vehicle detector guna mencegah
penutupan saat ada objek. Tombol emergency tersedia jika kamera USB gagal berfungsi. Pratinjau
helm dan informasi ruas parkir ditampilkan melalui aplikasi Python di monitor.
Sistem ini berhasil diimplementasikan dengan rata-rata beban CPU 90,35%, penggunaan
memori 20,92%, dan suhu 76,46°C tanpa Hailo-8L. Dengan Hailo-8L, beban CPU turun menjadi
16,19%, penggunaan memori 16,75%, dan suhu 57,49°C. Hasil ini menunjukkan peningkatan
efisiensi dan keandalan sistem dalam mendeteksi kelengkapan berkendara serta mengelola akses
parkir secara otomatis._

**_Kata kunci:_** _Raspberry Pi 5, Arduino Uno, portal parkir, visi komputer, Hailo- 8 L_


```
xiii
```
### ABSTRACT

_The number of motor vehicles in Indonesia continues to increase, reaching more than 125
million units in 2022. However, compliance with traffic regulations, particularly helmet use,
remains low. At Diponegoro University, students are required to wear helmets and adhere to speed
limits while riding on campus. However, inspections are still conducted manually, posing a risk of
errors. Furthermore, riders often struggle to find available parking spaces, leading to vehicle
congestion.
This study designs a machine learning-based parking gate system utilizing the Raspberry
Pi 5 to detect rider compliance and provide real-time parking availability information, while the
Arduino Uno controls the gate mechanism. The Raspberry Pi 5 processes computer vision data from
both IP and USB cameras, while the Arduino Uno operates a linear actuator motor via the L298N
motor driver to open the gate. A loop vehicle detector is implemented to prevent gate closure when
an object is present, and an emergency button is provided in case of USB camera failure. Helmet
detection and parking availability information are displayed through a Python-based application
on a monitor.
The system was successfully implemented with an average CPU load of 90.35%, memory
usage of 20.92%, and a temperature of 76.46°C without the Hailo-8L accelerator. With Hailo-8L,
CPU load was reduced to 16.19%, memory usage to 16.75%, and temperature to 57.49°C. These
results demonstrate improved efficiency and reliability in detecting rider compliance and managing
automated parking access._

**_Keywords:_** _Raspberry Pi 5, Arduino Uno, parking portal, computer vision, Hailo-8L_


### 1

### BAB I

### PENDAHULUAN

**1.1 Latar Belakang**
Keberadaan kendaraan bermotor khususnya sepeda motor menjadi salah satu
kebutuhan utama bagi seluruh kalangan masyarakat Indonesia, tak terkecuali
mahasiswa. Menurut Badan Pusat Statistik Nasional tahun 2022 mencatat jumlah
pengguna kendaraan bermotor jenis sepeda motor berjumlah 125.305.332 unit[1].
Jumlah pengguna kendaraan bermotor yang tinggi tidak sebanding dengan
kepatuhan pengendara terhadap peraturan lalu lintas, seperti kewajiban memakai
helm. Menurut Badan Pusat Statistik tahun 2022, jumlah kecelakaan yang terjadi di
Indonesia mencapai 139.258. Angka ini mengalami peningkatan signifikan
mencapai 35.613 lebih banyak dari data tahun 2021 sebanyak 103.645[2]. Melihat
fakta ini, Universitas Diponegoro memandang perlu adanya penekanan peraturan
terhadap pengendara, salah satunya kepada mahasiswa.
Mahasiswa pengguna sepeda motor di Universitas Diponegoro rutin
mendapat pengarahan tentang keamanan dan kelengkapan berkendara di tingkat
Program Studi hingga Universitas. Kewajiban penggunaan helm dan kecepatan
berkendara maksimal 20 km/jam telah diatur oleh Universitas Diponegoro,
khususnya Fakultas Teknik, dalam himbauan Surat Pengumuman Kelancaran
Ketertiban dan Keselamatan Civitas Akademik Fakultas Teknik[3]. Sebagai
langkah implementasi untuk memenuhi himbauan tersebut, Departemen di Fakultas
Teknik telah menerapkan pemeriksaan kelengkapan berkendara sebelum memasuki
area parkir, dengan tujuan membentuk kebiasaan berkendara yang aman bagi
seluruh civitas akademik. Namun, pemeriksaan tersebut masih dilakukan secara
manual, seperti yang diterapkan di Departemen Teknik Komputer.
Selain itu, berdasarkan SOP Parkir Kendaraan yang dikeluarkan oleh
Prosedur Sistem Manajemen Keselamatan dan Kesehatan Kerja ISO 45001:
Fakultas Teknik[4], terdapat sejumlah poin yang belum terpenuhi di area parkir
bersama Fakultas Teknik, seperti area parkir memiliki pintu masuk dan keluar
dengan pos satpam yang bertujuan untuk memudahkan satpam dalam mengawasi


dan menertibkan kendaraan di area parkir. Terdapat rambu-rambu serta marka yang
dapat memudahkan pengendara dalam memarkirkan kendaraannya dengan aman
dan tertib, dan Satpam mengarahkan kendaraan seperti mobil dan motor yang
masuk ke area parkir melalui pintu masuk yang sudah ditentukan. Di samping itu,
pengendara sering menghadapi kesulitan menemukan ruang parkir untuk
kendaraannya sehingga perlu bantuan dari satpam.
Berdasarkan permasalahan di atas, perlu adanya sebuah metode untuk
mengatasi kesulitan pengendara dalam menemukan ruang parkir yang tersedia,
serta meningkatkan pengawasan oleh satpam di area parkir. Salah satu metode yang
dapat digunakan adalah dengan memanfaatkan teknologi visi komputer. Visi
komputer merupakan bidang ilmu komputer yang memungkinkan komputer
mengidentifikasi dan memahami objek dan orang dalam gambar dan video. Seperti
tipe AI lainnya, visi komputer berupaya melakukan dan mengotomatiskan tugas
yang memerlukan kemampuan manusia. Dalam hal ini, visi komputer mereplikasi
cara manusia melihat dan cara manusia memahami apa yang dilihatnya[5].
Implementasi visi komputer yang berhasil direalisasikan yaitu mendeteksi
pengendara melalui kamera dan melihat hasil deteksi ruas parkir pada portal parkir
sebelum memasuki area parkir. Proses ini menggabungkan teknik pengolahan citra
dan pengenalan pola yang diolah dalam papan komputer tunggal Raspberry Pi 5
yang telah dilengkapi akselerator AI Hailo-8L untuk meningkatkan kemampuan
inferensi model pembelajaran mesin. Hasil visi komputer ini kemudian dikirim
menuju mikrokontroler Arduino Uno, yang berfungsi sebagai pengendali portal
parkir. Untuk mekanisme penggerak portal, sistem ini menggunakan _linear
actuator motor_ yang dikendalikan oleh motor driver L298N, memastikan gerakan
portal parkir yang presisi dan terkendali. Selain itu, sistem juga terintegrasi dengan
_loop vehicle detector_ untuk mendeteksi keberadaan kendaraan di bawah portal
parkir kendaran secara waktu nyata. Dengan kombinasi perangkat keras ini, sistem
portal parkir otomatis dapat beroperasi dengan handal dalam lingkungan
operasional dan pemeriksaan kelengkapan berkendara di Departemen Teknik
Komputer dapat dilakukan secara otomatis tanpa menimbulkan kesalahan manusia
dan memberikan posisi parkir yang tepat dengan menampilkan informasi ruas


parkir yang tersedia. Pada akhirnya, dapat tercipta otomatisasi sistem pemeriksaan
kelengkapan berkendara dan informasi ruas parkir di Departemen Teknik Komputer.

**1.2 Rumusan Masalah**
Berdasarkan latar belakang di atas, berikut adalah rumusan masalah yang
akan dibahas pada tugas akhir ini:

1. Bagaimana merancang sistem portal parkir otomatis pendeteksi kelengkapan
    berkendara dan ketersediaan ruas parkir menggunakan integrasi Arduino Uno
    dan Raspberry Pi 5?
2. Bagaimana mengintegrasikan perangkat lunak dengan perangkat keras agar
    sistem dapat berjalan dengan baik?
3. Bagaimana melakukan pengujian dan evaluasi performa pada sistem portal
    parkir untuk memastikan akurasi dan keandalannya?

**1.3 Batasan Masalah**
Tugas akhir ini memiliki batasan masalah untuk membatasi ruang lingkup
penelitian agar tetap terarah dan sesuai dengan tujuan yang ingin dicapai. Batasan
tersebut ditetapkan sebagai berikut:

1. Fokus utama penelitian ini adalah perancangan dan implementasi perangkat
    keras untuk Sistem Portal Parkir Pendeteksi Kelengkapan Berkendara dan
    Ketersediaan Ruas Parkir Berbasis Pembelajaran Mesin, dengan penekanan
    pada pengembangan Sistem Portal Parkir Pendeteksi Kelengkapan
    Berkendara dan Ketersediaan Ruas Parkir menggunakan Arduino Uno dan
    Raspberry Pi 5 , serta integrasi perangkat lunak secara terbatas.
2. Cakupan deteksi pada sistem portal parkir ini hanya terbatas pada model
    penggunaan helm pengendara bermotor dan model ruas parkir dalam area
    parkir bersama Fakultas Teknik.
3. Perangkat lunak yang diintegrasikan hanya sebatas mengirimkan sinyal antar
    perangkat keras melalui kode serial dan menampilkan hasil pembacaan
    kamera pada monitor.
4. Pengujian hanya dilakukan di area parkir bersama Fakultas Teknik.


5. Pengujian dan evaluasi sistem dilakukan dalam skala terbatas, tidak
    mencakup skenario lapangan yang luas atau kondisi ekstrem.

**1.4 Tujuan Penelitian**
Tujuan yang hendak dicapai pada penelitian tugas akhir ini adalah
membangun sistem portal parkir otomatis guna membantu dalam pemeriksaan
kelengkapan berkendara serta memberikan informasi ketersediaan ruang parkir
secara otomatis dengan memanfaatkan visi komputer. Sistem ini diharapkan
mampu mengelola ketertiban di area parkir, mengurangi kesalahan manusia dalam
proses pemeriksaan, serta memberikan pengalaman parkir yang lebih baik.

**1.5 Manfaat Penelitian**
Manfaat dari penelitian ini bagi penulis adalah:

1. Mengaplikasikan ilmu yang dipelajari selama berkuliah di Departemen
    Teknik Komputer untuk menyelesaikan masalah di dunia nyata.
2. Memberikan solusi modern untuk membantu pengelolaan sistem parkir di
    area parkir bersama Fakultas Teknik.
3. Menambah wawasan dan pengetahuan di bidang teknologi, serta menjadi
    dasar untuk penelitian lebih lanjut di masa depan.

**1.6 Metodologi Penelitian**
Penelitian Tugas Akhir ini dilaksanakan melalui tahapan-tahapan yang perlu
ditempuh. Berikut adalah penjabaran tahapan dalam pengerjaan penelitian Tugas
Akhir ini:

1. Studi Literatur
    Tahap ini meliputi proses mengumpulkan dan menganalisis informasi
    mengenai teori dan praktik dari berbagai sumber seperti penelitian terdahulu,
    buku, jurnal ilmiah, dan dokumen relevan lainnya. Studi literatur bertujuan
    untuk memahami konsep dasar, teknologi yang digunakan, serta pendekatan-
    pendekatan yang telah diterapkan dalam penelitian sejenis. Dengan demikian,


```
studi literatur dapat membantu merumuskan landasan teori yang kuat dan
mendukung pengembangan sistem yang akan dirancang.
```
2. Analisis Kebutuhan
    Tahap ini bertujuan untuk mengidentifikasi kebutuhan mendefinisikan
    kebutuhan fungsional dan non-fungsional dari sistem yang akan
    dikembangkan. Proses ini mencakup pemahaman tentang spesifikasi
    perangkat keras dan perangkat lunak yang dibutuhkan beserta eksplorasi
    sebelum memutuskan komponen yang tepat menjadi acuan dalam
    perancangan dan pengembangan sistem.
3. Perancangan
    Tahap ini dilakukan pembangunan sistem yang melibatkan pembuatan desain
    sistem secara menyeluruh berdasarkan data yang diperoleh dari tahap analisis
    kebutuhan. Desain sistem meliputi perencanaan alur kerja sistem, penentuan
    hubungan antar komponen, serta pembuatan diagram alir dan skema
    rangkaian. Hasil dari tahap perancangan ini akan menjadi dasar bagi tahap
    implementasi dan pengujian sistem.
4. Implementasi
    Tahap ini meliputi proses realisasi dari desain sistem yang telah dirancang
    sebelumnya. Implementasi mencakup pembuatan, pengembangan, dan
    integrasi berbagai komponen sistem, baik perangkat keras maupun perangkat
    lunak. Pada tahap ini, sistem mulai dijalankan untuk memastikan semua
    elemen berfungsi sesuai dengan tujuan yang telah ditetapkan dalam
    perancangan.
5. Pengujian dan Evaluasi
    Tahap ini meliputi proses menguji sistem yang telah diimplementasikan
    untuk memastikan kinerja dan fungsionalitasnya sesuai dengan hasil
    rancangan. Pengujian dilakukan menggunakan penyesuaian skenario ketika
    sistem beroperasi, di mana hasil uji tersebut akan dievaluasi hingga mencapai
    hasil terbaik untuk dapat beroperasi.


6. Penyusunan Laporan Tugas Akhir
    Tahap ini meliputi penyusunan laporan Tugas Akhir sebagai bentuk
    dokumentasi dari seluruh kegiatan yang telah dilaksanakan, meliputi konsep,
    landasan teori, proses implementasi, dan hasil yang diperoleh.

**1.7 Sistematika Penulisan**
Laporan Tugas Akhir ini tersusun dari lima bab dengan susunan sistematika
penulisan sebagai berikut:
**BAB I PENDAHULUAN**
Bab ini berisi tentang latar belakang, rumusan masalah, batasan masalah,
tujuan penelitian, manfaat penelitian, metodologi penelitian dan sistematika
penulisan.
**BAB II KAJIAN PUSTAKA**
Bab ini berisi uraian penelitian terdahulu dan landasan teori yang terkait
dengan penelitian ini. Kajian Pustaka ini berperan sebagai acuan dan referensi
penelitian dalam merancang sistem portal parkir pendeteksi kelengkapan
berkendara dan ketersediaan ruas parkir menggunakan integrasi Arduino Uno dan
Raspberry Pi 5.

## BAB III PERANCANGAN SISTEM

Bab ini berisi proses perancangan sistem portal parkir pendeteksi
kelengkapan berkendara dan ketersediaan ruas parkir menggunakan integrasi
Arduino Uno dan Raspberry Pi 5 berdasarkan pendeketan _design thinking_.
**BAB IV IMPLEMENTASI DAN PENGUJIAN**
Bab ini membahas implementasi sistem portal parkir pendeteksi kelengkapan
berkendara dan ketersediaan ruas parkir menggunakan Arduino Uno dan Raspberry
Pi 5 , serta menyajikan hasil pengujian untuk mengevaluasi kinerja dan efektivitas
sistem.
**BAB V PENUTUP**
Bab ini berisi kesimpulan dari perancangan, implementasi, dan pengujian
yang telah dilakukan, serta saran pengembangan dan penelitian lebih lanjut pada
masa mendatang.


### 7

### BAB II

### KAJIAN PUSTAKA

**2.1 Penelitian Terdahulu**
Kajian penelitian dalam penelitian ini membahas berbagai penelitian
terdahulu yang memiliki keterkaitan topik serupa dengan penelitian ini. Penelitian
sebelumnya dijadikan sebagai referensi dalam memahami konsep, metode, serta
hasil yang telah diperoleh dalam studi serupa. Selain itu, kajian ini berperan dalam
memberikan kontribusi pada pengembangan kerangka teoritis serta memberikan
gambaran yang lebih jelas mengenai pendekatan yang digunakan dalam penelitian
ini. Dengan adanya kajian pustaka, penelitian ini dapat dikembangkan secara lebih
sistematis serta didukung oleh temuan yang telah teruji sebelumnya.
Penelitian terdahulu oleh K. Kasym dkk. (2018) berjudul “Parking Gate
Control Based on Mobile Application”[6] merancang prototipe sistem kontrol
gerbang parkir berbasis aplikasi ponsel pintar untuk mengatasi keterbatasan sistem
gerbang parkir manual di daerah dengan cuaca ekstrem. Sistem ini dikendalikan
oleh Arduino Uno, yang berkomunikasi dengan ponsel pintar melalui teknologi
Bluetooth sebagai akses jarak jauh. Untuk meningkatkan keamanan, sistem
dilengkapi dengan sensor IR LM393, yang berfungsi sebagai sensor keselamatan
guna mencegah penutupan portal jika masih terdapat objek di bawahnya.
Mekanisme pergerakan portal parkir dalam penelitian ini menggunakan motor DC
12V yang dikendalikan oleh motor driver L298N, dengan mekanisme gir yang
dirancang khusus agar sesuai dengan kebutuhan operasional. Hasil yang ingin
dicapai dari sistem ini adalah menciptakan gerbang parkir otomatis yang tahan
terhadap cuaca ekstrem serta mampu menggerakkan beban portal secara andal.
Kesamaan penelitian ini dengan sistem yang dikembangkan dalam penelitian saat
ini terletak pada konsep otomatisasi gerbang portal parkir, pemanfaatan Arduino
Uno sebagai kontrol utama, serta penggunaan motor driver L298N untuk
mengendalikan motor DC. Namun, sistem yang dikembangkan dalam penelitian ini
memiliki perbedaan utama pada aspek teknologi akses kontrol dan metode


pengolahan data yang lebih canggih, serta integrasi sensor tambahan untuk
meningkatkan keandalan sistem.

Selanjutnya, penelitian yang dilakukan oleh K. M. Udofia (2020) berjudul
“Arduino Microcontroller-based Intelligent Car Parking System”[7] merancang
prototipe sistem parkir cerdas berbasis mikrokontroler Arduino Uno yang
mencakup berbagai aspek, seperti otomatisasi, kemudahan aktivitas parkir, dan
peningkatan keamanan kendaraan. Sistem ini dirancang dengan berbagai parameter
masukan untuk mencapai luaran yang optimal, di antaranya RFID dan Optical
Character Recognition (OCR) untuk mendata nomor kendaraan, menangkap
gambar plat nomor, serta mencocokkannya guna meningkatkan aspek keamanan
dan pencatatan biaya parkir. Selain itu, sistem ini juga menggunakan sensor IR dan
ultrasonik untuk mendeteksi keberadaan kendaraan serta menentukan posisi parkir
yang sesuai, motor stepper sebagai aktuator utama dalam menggerakkan portal
parkir secara otomatis, serta LCD untuk menampilkan informasi terkait aktivitas
parkir kepada pengendara. Luaran yang ingin dicapai dari sistem ini adalah
menciptakan kontrol gerbang parkir otomatis, alokasi slot parkir yang efisien,
peningkatan keamanan kendaraan, kemudahan dalam pembayaran, serta
pengelolaan parkir yang lebih teratur. Kesamaan antara penelitian ini dengan sistem
yang dikembangkan dalam penelitian saat ini terletak pada penggunaan Arduino
Uno sebagai mikrokontroler utama dalam mengendalikan komponen sistem parkir
otomatis, serta pemanfaatan teknologi visi komputer. Namun, perbedaannya
terletak pada jenis teknologi pengolahan citra yang digunakan, penelitian ini
menerapkan Optical Character Recognition (OCR), sedangkan sistem yang
dikembangkan dalam penelitian ini menggunakan pendekatan berbeda untuk
analisis kelengkapan berkendara sebelum masuk ke area parkir.

Penelitian terdahulu oleh A. Hasibuan dkk. (2021) berjudul “Design and
Development of An Automatic Door Gate Based on Internet of Things Using
Arduino Uno”[8] merancang prototipe gerbang otomatis berbentuk rolling door
berbasis Arduino Uno yang dikendalikan melalui aplikasi ponsel pintar
menggunakan modul Bluetooth HC-05. Sistem ini dirancang untuk meningkatkan
efisiensi dan kenyamanan dalam membuka serta menutup gerbang secara otomatis


dengan kendali jarak jauh. Pengujian dilakukan dengan variasi sumber daya 8-16V
DC, yang menunjukkan bahwa sistem dapat mengendalikan rolling door serta
menyesuaikan kecepatan motor sesuai dengan tegangan yang diberikan. Kesamaan
sistem ini dengan sistem yang dikembangkan terletak pada penggunaan Arduino
Uno sebagai mikrokontroler serta motor driver L298N sebagai pengendali motor
DC. Selain itu, penelitian ini juga memiliki relevansi dalam aspek pengujian variasi
tegangan sumber daya yang dapat disesuaikan untuk mengoptimalkan kinerja motor
DC sesuai kebutuhan sistem.

Penelitian terdahulu oleh Waheb A. Jabbar dkk (2021) berjudul “An IoT
Raspberry Pi-based parking management system for smart campus”[9]
mengusulkan sistem manajemen parkir berbasis IoT untuk membantu civitas
akademik dalam mencari ruas parkir kosong ketika memasuki area parkir, sistem
ini memanfaatkan perpaduan visi komputer dan sensor ultasonik dalam mendeteksi
objek yang berada di setiap ruas parkir dan tambahan modul GPS dalam memandu
pengemudi untuk menemukan area parkir, kemudian diintegrasikan dengan
Raspberry Pi 4 model B. Masukan kamera yang digunakan untuk visi komputer
pada sistem ini menggunakan Pi Camera yang memiliki port khusus dengan
Raspberry Pi. Dalam pemrosesannya sistem menggunakan 3 algoritma untuk
menspesifikasikan proses yang berjalan dan pemantauan hasilnya dapat dilihat
melalui GUI berupa aplikasi Blynk. Luaran yang ingin dicapai dari sistem Waheb
A. Jabbar adalah memudahkan pencarian informasi ketersediaan ruas parkir, hal ini
memiliki sedikit kesamaan dengan sistem yang dikembangkan, ditambah sama-
sama menggunakan visi komputer sebagai masukan dan papan tunggal Raspberry
Pi meskipun berbeda seri. Namun, dari kesamaan yang dimiliki tetap memiliki
perbedaan yaitu terletak pada jumlah masukan dan teknologi visi komputer, jumlah
sensor serta algoritma yang digunakan.

Selanjutnya, terdapat penelitian yang dilakukan oleh Ida Rachmaniar Sahali
dkk (2023) berjudul “Implementasi Otomasi Sistem Palang Parkir Berbasis
Teknologi RFID pada Lahan Parkir Rektorat Unhas”[10] melakukan implementasi
sistem palang parkir otomatis dengan integrasi Arduino Uno dan Raspberry Pi
berbasis RFID sebagai parameter masukan. Faktor keamanan kendaraan menjadi


fokus utama dengan proses kerja pengguna menempelkan RFID pada tag _reader_ di
mana hasil pengujian jarak pembacaan RFID _reader_ pada sistem ini maksimal 9
cm, lalu sistem akan mencari di basis data apakah pengguna tersebut terdaftar atau
tidak, jika tidak maka palang tidak akan terbuka. Setiap pengguna diwajibkan
mendaftarkan diri untuk pencatatan anggota di area parkir tersebut. Persamaan dari
sistem ini dengan sistem yang dikembangkan terletak pada penggunaan Arduino
Uno dan Raspberry Pi sebagai pusat kontrol dalam mengatur akses keluar masuk,
di mana jalur komunikasinya menggunakan serial. Di sisi lain, perbedaannya
terletak pada parameter masukan yang digunakan yaitu RFID dan tujuan
pengembangan sistem yaitu keamanan dan bukan ketersediaan ruas parkir.

Terakhir, penelitian terdahulu oleh Robi dkk (2023) berjudul “Implementasi
Sistem Pada Automasi Barrier Gate Palang Pintu Parkir Menggunakan ESP32 Dan
RFID”[11] mengembangkan sistem kendali gate dengan memanfaatkan ESP32 dan
RFID. Sistem ini berfokus pada pengelolaan area parkir dengan pembacaan
ketersediaan lahan parkir menggunakan data RFID, kemudian data tersebut
dikirimkan ke basis data oleh ESP32 sehingga dapat memberikan umpan balik
kepada pengguna jika kapasitas sudah penuh. Sistem ini juga memiliki GUI berupa
situs web untuk pengelola parkir melihat data lengkap pengguna beserta kendaraan
yang terparkir. Kesamaan sistem ini dengan sistem yang dikembangkan oleh
penulis terletak pada luaran utama yaitu untuk mengatur akses portal keluar dan
masuk ke area parkir. Namun, tetap terdapat perbedaan dari integrasi alat yang
digunakan, jika pada sistem ini menggunakan RFID sebagai sensor masukan dan
ESP32 sebagai mikrokontroler tunggal untuk pusat kontrol. Sementara itu, sistem
yang dikembangkan menggunakan visi komputer sebagai masukan dan Arduino
UNO serta Raspberry Pi 5 sebagai pusat kontrol terintegrasi.


Tabel 2. 1 Penelitian terdahulu
**No Nama Peneliti
(Tahun)**

```
Judul Tujuan Metode Hasil
```
```
1 K. Kasym, A.
Sarsenen, Z.
Segizbayev, D.
Junuskaliyeva, and
Md. Hazrat Ali
(2018)
```
```
Parking Gate
Control Based on
Mobile Application
```
```
Merancang prototipe
sistem kontrol
gerbang parkir
melalui aplikasi
ponsel pintar guna
meningkatkan
kenyamanan dan
efisiensi akses ke area
terbatas, terutama
dalam kondisi cuaca
ekstrem.
```
```
Sistem terintegrasi
dengan Arduino Uno
sebagai mikrokontroler,
motor DC dan motor
driver untuk
menggerakan portal,
Bluetooth module HC- 05
untuk menerima koneksi
Bluetooth, dan sensor IR
sensor LM393 sebagai
sensor keselamatan
```
```
Sistem kontrol portal ini
menunjukkan respons
cepat dengan portal dapat
mulai bergerak dalam
beberapa detik dan proses
pembukaan serta
penutupan berlangsung
kurang dari satu menit.
Prototipe ini memiliki
potensi untuk diterapkan
dalam kondisi nyata,
termasuk cuaca ekstrem.
2 K. M. Udofia (2020) Arduino
Microcontroller-
based Intelligent Car
Parking System
```
```
Merancang prototipe
sistem parkir cerdas
berbasis
mikrokontroler
Arduino yang
mengotomatisasi
kontrol gerbang,
```
```
Kendaraan masuk,
menempelkan RFID,
sensor IR memeriksa slot
parkir, portal terbuka jika
tersedia, lalu kamera
menangkap dan
mengekstrak plat nomor
```
```
Hasil penelitian
menunjukkan bahwa
sistem parkir cerdas
berbasis Arduino berhasil
mengotomatiskan kontrol
gerbang, deteksi slot,
keamanan kendaraan, dan
```

```
alokasi slot parkir,
meningkatkan
keamanan kendaraan,
mempermudah
pembayaran, serta
mengatasi masalah
parkir yang tidak
teratur.
```
```
dengan OCR untuk
dicocokkan dengan data
RFID. Terdapat sensor IR
pada portal untuk
keselamatan.
```
pembayaran. Dengan
sensor IR, ultrasonik,
RFID, serta kamera OCR,
sistem bekerja optimal
dalam memastikan
kendaraan terparkir
dengan baik dan
mengurangi interaksi
manusia.
3 A. Hasibuan,
Rosdiana, and D. S.
Tambunan (2021)

```
Design and
Development of An
Automatic Door
Gate Based on
Internet of Things
Using Arduino Uno
```
```
Merancang prototipe
gerbang otomatis
berbentuk rolling
door berbasis
Arduino dengan
motor DC, yang dapat
dikendalikan melalui
aplikasi di ponsel
pintar menggunakan
modul Bluetooth HC-
05, guna
meningkatkan
efisiensi dan
kenyamanan dalam
membuka serta
menutup gerbang
pada suatu sistem
secara otomatis
```
```
Arduino Uno sebagai
mikrokontroler dengan
mengendalikan motor DC
melalui motor driver
L298N. Di samping itu,
Arduino Uno juga
ditambahkan modul
Bluetooth HC- 05 untuk
menerima perintah dari
ponsel pintar melalui
Bluetooth
```
```
Sistem gerbang otomatis
berbasis Arduino dengan
motor DC mampu
menghasilkan daya dorong
lebih dari 50 N, cukup
untuk menggerakkan
gerbang geser dengan
diberikan suplai 5V.
Kontrol melalui aplikasi
ponsel pintar berjalan
efektif saat terhubung
dengan Bluetooth,
meskipun jarak
memengaruhi kecepatan
RPM dan respons sistem.
```

```
dengan interaksi jarak
jauh.
```
4 W. A. Jabbar, C. W.
Wei, N. A. Ainaa, and
N. A. Haironnazli
(2021)

```
An IoT Raspberry
Pi-based parking
management system
for smart campus
```
```
Mengembangkan
sistem manajemen
parkir berbasis IoT
menggunakan
Raspberry Pi 4, sensor
ultrasonik, Pi Camera,
dan GPS untuk
mendeteksi
ketersediaan ruang
parkir secara real-
time, dengan
pemantauan melalui
aplikasi Blynk.
```
```
Pembacaan ruas
menggunakan sensor
ultrasonik dan kamera
kemudian dengan modul
GPS lokasi parkir tujuan
dapat dilihat melalui
aplikasi Blynk.
```
```
Sistem dapat efektif
memantau status parkir
secara waktu nyata,
dengan integrasi data dari
sensor dan visi komputer
serta akses informasi
melalui aplikasi Blynk
yang dapat diakses kapan
saja.
```
5 I. R. Sahali, M.
Anshar, dan Z. Arfah
(2023)

```
Implementasi
Otomasi Sistem
Palang Parkir
Berbasis Teknologi
RFID pada Lahan
Parkir Rektorat
Unhas
```
```
Mengembangkan
sistem palang parkir
otomatis berbasis
RFID untuk
mengatasi
pengoperasian palang
parkir secara manual.
```
```
RFID yang terdaftar di
basis data sebagai
parameter masukan untuk
membuka palang parkir
dengan reader di 4 titik.
Portal parkir untuk motor
dan mobil dibedakan.
```
```
Rata-rata waktu
pembacaan kartu untuk
setiap node adalah: Node
1 : 0.324 detik, Node 2:
0.334 detik, Node 3: 0.408
detik dan Node 4: 0.426
detik. Jarak reader
maksimal 9 cm jika posisi
kartu sejajar dengan
reader. Sistem ini dapat
```

meningkatkan efisiensi
pengoperasian portal
parkir.
6 R. Robiyanto, W. P.
Putra, dan Raswa
(2023)

```
Implementasi
Sistem Pada
Automasi Barrier
Gate Palang Pintu
Parkir
Menggunakan
ESP32 Dan RFID
```
```
Membangun sistem
portal parkir otomatis
sebagai alat bantu
dalam mengontrol
jumlah kendaraan
yang masuk kedalam
area parkir.
```
```
Pengguna mendaftar agar
RFID tercatat pada
sistem. RFID
ditempelkan pada tag
reader , jika kapasitas
parkir penuh maka sistem
memberikan umpan
balik.
```
```
Sistem ini efektif
mengontrol akses
kendaraan, kemacetan,
serta keteraturan parkir.
Dengan penyimpanan data
kendaraan dalam basis
data, memberikan
informasi secara waktu
nyata mengenai
ketersediaan lahan parkir.
```

**2.2 Landasan Teori
2.2.1 Raspberry Pi**
Nama Raspberry Pi terkenal sebagai komputer papan tunggal (SBC)
berukuran kecil yang dikembangkan oleh Raspberry Pi Foundation di Britania Raya
untuk berbagai keperluan, mulai dari pendidikan hingga pengembangan teknologi.
Perangkat ini hadir dalam beberapa model dengan spesifikasi berbeda, tetapi
semuanya memiliki prosesor ARM, GPU, dan memori _onboard_ , serta
membutuhkan daya 5V untuk beroperasi. Dengan dukungan port GPIO, CSI, dan
berbagai antarmuka lainnya, Raspberry Pi dapat dihubungkan ke berbagai
perangkat seperti sensor, motor, dan modul komunikasi.
Raspberry Pi adalah perangkat prototipe kecil namun serbaguna yang telah
merevolusi penelitian ilmiah dan pendidikan. Biayanya yang rendah untuk sebuah
papan tunggal, ukurannya yang kecil, dan kemudahan penggunaannya
menjadikannya platform ideal untuk berbagai aplikasi ilmiah, termasuk akuisisi
data, sistem kontrol, dan pemodelan. Raspberry Pi juga merupakan alat yang kuat
untuk pemodelan dan simulasi ilmiah. Prosesornya yang berkinerja tinggi dan
memorinya yang melimpah memungkinkannya menjalankan perangkat lunak
ilmiah yang kompleks. Berikut adalah beberapa keunggulan Raspberry Pi[12].

- **Prosesor yang Cepat dan Kuat** – Memiliki performa lebih baik
    dibandingkan dengan papan prototipe lainnya seperti Arduino.
- **Dapat Berfungsi sebagai PC Desktop** – Selain digunakan untuk proyek
    sistem tertanam, Raspberry Pi juga bisa difungsikan sebagai komputer mini.
- **Mendukung Berbagai Bahasa Pemrograman** – Dapat diprogram
    menggunakan Java, Ruby, Python, dan C#, tidak terbatas pada C dan C++
    seperti Arduino.
- **Memiliki 40 Pin GPIO** – Memungkinkan integrasi dengan berbagai sensor
    digital untuk proyek sistem tertanam.
- **Mendukung Beragam IDE** – Tersedia banyak lingkungan pengembangan
    seperti Geany, Thonny, Code::Blocks, dan Adafruit.
- **Kemampuan Multimedia** – Bisa digunakan untuk pemrograman,
    pengeditan foto, serta pemrosesan audio/video.


- **Komunitas yang Besar dan Suportif** – Banyak forum, tutorial, dan
    dokumentasi yang memudahkan pengembangan proyek.
    Raspberry Pi kini menghadirkan seri terbarunya, Raspberry Pi 5, sebuah
komputer papan tunggal yang dirancang dengan spesifikasi terbaru untuk
mendukung berbagai kebutuhan komputasi, dengan spesifikasi sebagai berikut:
- **Processor** : Broadcom BCM2712, quad-core Arm Cortex-A76, clocked at 2.4
    GHz.
- **RAM Options** : 2GB, 4GB, 8GB, and 16GB.
- **GPU** : VideoCore VII with 800 MHz frequency.
- **Video Output** : Dual micro HDMI ports, supporting up to 4Kp60 resolution.
- **Connectivity** : Wi-Fi 802.11ac, Bluetooth 5.0, and Gigabit Ethernet
- **USB Ports** : Two USB 3.0 ports and one USB 2.0 port.
- **Storage** : MicroSD card slot for OS and data storage.
- **Expansion** : Single-lane PCI Express (PCIe) connector for additional
    peripherals.
- **Power Supply** : 5V/5A DC power via USB-C, with Power Delivery support.

**2.2.2 Hailo AI Kit**
Hailo merupakan perusahaan yang berfokus pada pengembangan teknologi
AI untuk perangkat _edge_ , menghadirkan prosesor dan akselerator yang dirancang
untuk mempercepat pemrosesan data secara efisien. Dengan arsitektur inovatif,
Hailo memungkinkan perangkat _edge_ menjalankan tugas kecerdasan buatan secara
mandiri tanpa bergantung pada komputasi awan, sehingga meningkatkan
responsivitas dan menghemat konsumsi daya. Teknologi ini membuka peluang baru
dalam berbagai bidang, termasuk otomatisasi industri, kendaraan otonom, serta
analisis data berbasis visual secara waktu nyata.
Hailo, sebagai produsen chip AI terkemuka, telah merevolusi lanskap
komputasi _edge_ dengan prosesor dan akselerator AI inovatifnya. Di pusat revolusi
ini terdapat Hailo- 8 ™, sebuah prosesor AI canggih yang dirancang khusus untuk
perangkat _edge_. Dirancang untuk mempercepat aplikasi _deep learning_ pada
perangkat _edge_ , produk Hailo menawarkan perpaduan sempurna antara kinerja


tinggi dan efisiensi daya. Edge AI, yang menjadi fokus utama inovasi Hailo,
merupakan kemajuan signifikan dalam cara data diproses dan dianalisis. Dengan
memungkinkan perangkat menjalankan algoritma _deep learning_ tingkat lanjut
secara lokal, prosesor Hailo meminimalkan latensi, yang merupakan aspek penting
dalam aplikasi yang memerlukan analisis _real-time_ , seperti analisis video dan
_computer vision_ [13].
Hailo kini menghadirkan Hailo-8L, lini prosesor AI yang lebih terjangkau
namun tetap menawarkan performa optimal untuk aplikasi edge AI. Meskipun lebih
hemat daya dan biaya dibandingkan dengan seri Hailo-8, Hailo-8L tetap
mempertahankan efisiensi komputasi yang tinggi, menjadikannya pilihan yang
ideal untuk sistem berbasis visi komputer, dengan spesifikasi sebagai berikut.

- **Neural Processing Unit (NPU)** : 13 TOPS.
- **Form Factor** : M.2 Key-M 2242/2280 atau PCIe Gen 3.0 x2.
- **Daya Maksimum:** <2,5W.

**2.2.3 Arduino**
Arduino adalah platform _open-source_ yang digunakan untuk
mengembangkan dan mengendalikan perangkat elektronik berbasis mikrokontroler.
Platform ini terdiri dari perangkat keras dalam bentuk papan sirkuit dengan
mikrokontroler serta perangkat lunak berupa lingkungan pemrograman (IDE) yang
digunakan untuk menulis dan mengunggah kode ke papan Arduino.
Papan Arduino memungkinkan pengguna untuk bereksperimen dengan ide-
ide inovatif yang ingin mereka wujudkan. Papan Arduino dapat diprogram
menggunakan Arduino IDE. Pada dasarnya, pemrograman dengan Arduino berarti
memprogram ulang mikrokontroler yang ada di dalamnya. Terdapat LED bawaan
pada papan, serta pin yang dapat diperluas untuk menghubungkan modul eksternal
seperti USB. Mikrokontroler bawaan inilah yang mengendalikan fungsi LED dan
sensor yang terhubung ke papan melalui pin-pin tersebut. Berikut adalah beberapa
keunggulan Arduino[14].


- **Mudah Digunakan** – Arduino dirancang dengan antarmuka yang sederhana
    dan mudah dipahami, sehingga cocok untuk pemula maupun profesional
    dalam pengembangan proyek elektronik.
- **Sumber Terbuka** – Baik perangkat keras maupun perangkat lunaknya
    bersifat sumber terbuka, memungkinkan komunitas global untuk
    berkontribusi dan mengembangkan berbagai inovasi.
- **Harga Terjangkau** – Dibandingkan dengan mikrokontroler lainnya,
    Arduino relatif murah dan mudah didapat.
- **Dapat Berjalan Secara Mandiri** – Program yang telah diunggah ke papan
    Arduino dapat berjalan tanpa harus selalu terhubung dengan komputer.
    Seri Arduino Uno yang sering dijumpai dalam kehidupan sehari-hari yaitu
Arduino Uno, memiliki spesifikasi sebagai berikut:
- **Mikrokontroler** : ATmega328P.
- **Tegangan Operasional** : 5V.
- **Frekuensi Clock:** 16 MHz.
- **Jumlah Pin Digital I/O** : 14 (termasuk 6 pin PWM).
- **Memori Flash** : 32 KB (termasuk 0,5 KB untuk bootloader).
- **Port Komunikasi** : UART, SPI, I2C.
- **Konektor USB** : Tipe B.
- **Konektor Daya** : Jack DC atau pin Vin.

**2.2.4 Linear Actuator Motor**
Di antara berbagai aktuator, motor DC adalah yang paling umum digunakan.
Hal ini disebabkan oleh efisiensinya yang tinggi, karakteristik torsi-kecepatan yang
lebih baik, serta ketersediaannya di pasaran. Selain itu, terdapat berbagai jenis
motor DC yang memungkinkan penggunaannya secara luas sesuai dengan
aplikasinya. Dalam motor DC, pengendalian kecepatan merupakan aspek yang
sangat penting. Terdapat berbagai teknik yang dapat digunakan untuk mengontrol
kecepatan motor DC[15].


_Linear motor_ adalah jenis perangkat mekanis yang mengubah energi listrik
secara langsung menjadi gerakan linear. _Linear motor_ ini memiliki karakteristik
seperti jumlah perantara yang lebih sedikit, struktur transmisi mekanis yang
sederhana, dan efisiensi konversi energi yang tinggi. Selain itu, linear motor
memiliki prospek aplikasi yang baik di bidang kontrol servo [16].

**2.2.5 Motor Driver**
Motor driver adalah perangkat elektronik yang mengontrol kecepatan, arah,
dan torsi dari motor listrik. Motor driver biasanya menggunakan mikrokontroler
atau rangkaian kontrol lainnya untuk mengirim sinyal ke motor. Motor driver
banyak digunakan di berbagai bidang industri, seperti otomasi, kendaraan listrik,
dan robotika[17].
Motor DC banyak digunakan karena memiliki spesifikasi torsi-kecepatan
yang baik, maka diperlukan metode yang efektif untuk mengontrol kecepatannya.
Salah satu cara yang umum digunakan adalah dengan modul motor driver seperti
L298N, sebuah motor driver berbasis IC H-Bridge yang dirancang untuk
mengendalikan motor DC. L298N mampu mengendalikan arah dan kecepatan
motor DC dengan mudah, bekerja dengan prinsip H-Bridge, yang memungkinkan
motor beroperasi dalam dua arah serta mendukung pengaturan kecepatan melalui
teknik PWM. Selain itu, L298N dapat menangani 2 motor dengan tegangan operasi
hingga 35V dan arus maksimum 2A per kanal.

**2.2.6 Portal Parkir (** **_Barrier Gate_** **)**
Sistem gerbang palang kendaraan adalah sebuah batang atau balok yang
dipasang pada poros untuk memungkinkan palang tersebut menghalangi kendaraan
atau pejalan kaki melewati titik yang dikendalikan. Biasanya, ujung palang akan
terangkat dalam lintasan melengkung hingga hampir mencapai posisi vertikal.
Gerbang palang ini sering diberi pemberat agar tiangnya dapat dengan mudah
diangkat. Gerbang palang kendaraan sering dipasangkan secara berlawanan ujung
ke ujung atau diatur secara _offset_ untuk menghalangi akses dari kedua arah[18].


**2.2.7 Kamera**
Kamera adalah perangkat optoelektronik yang menangkap cahaya dari suatu
objek dan mengubahnya menjadi sinyal listrik untuk menghasilkan gambar atau
video. Kamera bekerja berdasarkan prinsip optik dan pemrosesan sinyal digital,
mengandalkan lensa untuk memfokuskan cahaya ke sensor yang kemudian
menerjemahkannya menjadi representasi visual.
Kamera dapat dibagi menjadi beberapa jenis berdasarkan teknologi dan cara
transmisinya, seperti kamera analog yang menggunakan sinyal listrik kontinu untuk
mentransmisikan gambar, serta kamera digital yang mengubah cahaya menjadi data
digital. Kamera digital sendiri dapat dibagi berdasarkan metode transmisinya.

- Kamera USB bekerja berdasarkan prinsip konversi cahaya menjadi sinyal
    listrik melalui sensor gambar, seperti CMOS (Complementary Metal-Oxide-
    Semiconductor. Sinyal yang dihasilkan kemudian diproses oleh Image Signal
    Processor (ISP) untuk meningkatkan kualitas visual. Kamera USB memiliki
    kompatibilitas tinggi dengan berbagai perangkat dan mendukung panjang
    kabel hingga ∼8 meter, menjadikannya pilihan yang fleksibel dalam berbagai
    aplikasi. Selain itu, kamera USB relatif lebih murah dibandingkan dengan
    kamera MIPI CSI, meskipun memiliki penggunaan CPU yang lebih tinggi,
    sekitar 50%. Dengan kecepatan transfer data mencapai 350 Mbps, kamera ini
    cukup andal untuk kebutuhan umum, meskipun tidak secepat kamera MIPI
    CSI dalam pengiriman data[19].
- Kamera _Internet Protocol_ (IP) adalah jenis kamera video digital yang
    umumnya dipasang di berbagai lingkungan untuk tujuan pengawasan.
    Kamera ini dapat mengirimkan informasi melalui internet, memungkinkan
    akses jarak jauh. Dengan fitur ini, streaming video langsung dapat diakses
    secara bersamaan dari berbagai perangkat, seperti PC, smartphone, tablet,
    laptop, atau perangkat portabel lainnya[20].
- Kamera _Camera Serial Interface_ (CSI) merupakan protokol komunikasi
    serial berkecepatan tinggi yang dikembangkan oleh Mobile Industry
    Processor Interface (MIPI) untuk menghubungkan perangkat kamera dengan
    prosesor baseband pada perangkat ponsel pintar. Sesuai dengan standar MIPI


```
CSI, data piksel yang ditangkap oleh kamera dikirimkan secara unidireksional
melalui jalur berkecepatan tinggi (high-speed lanes) ke prosesor baseband.
Dibandingkan dengan kamera USB, kamera CSI memiliki keunggulan dalam
kecepatan transfer data yang lebih tinggi, yaitu berkisar antara 80 Mbps
hingga 2.5 Gbps per lane. Kamera ini juga lebih efisien dalam penggunaan
daya dan memori karena bekerja langsung dengan prosesor baseband tanpa
memerlukan konversi tambahan. Namun, kamera CSI dirancang untuk
koneksi jarak pendek, sehingga lebih cocok untuk aplikasi dalam perangkat
ponsel pintar atau sistem tertanam dengan keterbatasan ruang dan konsumsi
daya rendah[21].
```
## Gambar 4.28 Pengujian Loop Vehicle Detector

_Inductive loop_ adalah sebuah kumparan yang diberi eksitasi oleh osilator,
sehingga menciptakan medan magnet di sekitarnya yang beresonansi pada
frekuensi konstan (biasanya antara 10kHz hingga 200kHz), yang dipantau oleh
detektor elektronik. Frekuensi dasar ditetapkan saat tidak ada kendaraan di atas _loop_.
Ketika sebuah objek logam besar, seperti kendaraan, melewati _loop_ , arus eddy
diinduksi pada bagian logam kendaraan tersebut, yang pada gilirannya
menghasilkan medan magnet bolak-balik yang berlawanan dengan medan yang
dihasilkan oleh _loop_. Hal ini menyebabkan penurunan induktansi _loop_ , sehingga
frekuensi resonansi meningkat[22].
_Loop vehicle detector_ menggunakan metode _inductive loop_ , di mana
perubahan frekuensi akibat keberadaan kendaraan digunakan untuk mendeteksi
keberadaan dan pergerakan kendaraan di atas _loop_. Metode ini umum digunakan
dalam sistem deteksi lalu lintas dan pengaturan sinyal lampu lalu lintas.


### 22

### BAB III

### PERANCANGAN SISTEM

Pada perancangan sistem, akan dibahas mengenai deskripsi umum sistem
serta identifikasi kebutuhan sistem, yang mencakup kebutuhan fungsional dan
nonfungsional. Bab ini juga akan membahas perancangan perangkat keras alat,
yang mencakup perancangan sistem, mulai dari tahap desain 3D wadah sistem,
pemilihan material yang tepat, hingga proses fabrikasi dan integrasi komponen
elektronik, seperti mikrokontroler dengan sensor, aktuator, serta catu daya ke dalam
sistem.

## Gambar 3.1 Gambaran umum sistem DAFTAR GAMBAR

Gambar 3. 1 Gambaran umum sistem
Gambaran umum sistem dapat dilihat pada Gambar 3.1, sistem ini
menghadirkan solusi inovatif berupa digitalisasi dalam proses pengecekan
kelengkapan berkendara di area parkir bersama Fakultas Teknik. Dengan
mengintegrasikan teknologi visi komputer, sistem ini secara otomatis mendeteksi
penggunaan helm melalui kamera, menggantikan metode pengecekan manual yang
selama ini digunakan.
Selain itu, sistem ini juga dilengkapi dengan teknologi untuk memantau dan
menyajikan informasi ketersediaan ruas parkir secara waktu nyata, memungkinkan
pengendara mendapatkan akses cepat terhadap lokasi parkir yang tersedia. Dengan
penerapan teknologi ini, sistem tidak hanya meningkatkan efisiensi dan akurasi


proses pemeriksaan, tetapi juga mendukung terciptanya lingkungan parkir yang
lebih tertib, aman, dan modern.
Sistem ini menggunakan USB kamera sebagai komponen untuk mengambil
data kelengkapan pengendara sebagai masukan kepada model deteksi helm. Lalu,
menggunakan IP kamera sebagai komponen untuk mengambil data ruas parkir
sebagai masukan kepada model ruas parkir. Sehingga, sistem ini memiliki dua
proses pembelajaran mesin yang berjalan bersama dan akan diterima oleh pengguna
dalam satu waktu bersamaan.

**3.1.1 Fungsi Utama Produk**
Berikut ini merupakan fungsi utama dari sistem yang dikembangkan:

1. Sistem dapat membuka dan menutup portal parkir secara otomatis
    berdasarkan hasil deteksi kelengkapan berkendara pengendara sepeda motor.
2. Sistem dapat mendeteksi kelengkapan berkendara yaitu penggunaan helm
    pada pengendara sepeda motor.
3. Sistem dapat mendeteksi ketersediaan ruas parkir sepeda motor pada area
    parkir bersama Fakultas Teknik.
4. Sistem dapat menampilkan antarmuka pratinjau deteksi helm serta denah
    ketersediaan ruas parkir.

**3.1.2 Batasan Sistem**
Sistem ini memiliki batasan yang perlu diperhatikan, di antaranya sebagai
berikut:

1. Sistem ini membutuhkan sumber catu daya sebesar 220V AC.
2. Sistem ini tidak dapat dioperasikan ketika cuaca hujan.

**3.2 Identifikasi Kebutuhan Sistem**
Identifikasi kebutuhan sistem mencakup dua aspek utama, yaitu kebutuhan
fungsional dan nonfungsional. Kebutuhan fungsional menggambarkan fitur dan
kemampuan yang harus dimiliki oleh sistem agar dapat beroperasi sesuai dengan
tujuan yang telah ditetapkan, seperti deteksi penggunaan helm dan penyajian


informasi ketersediaan ruas parkir. Sementara itu, kebutuhan nonfungsional
berfokus pada aspek kualitas sistem, seperti keandalan, keamanan, kemudahan
penggunaan, dan menjelaskan batas kemampuan sistem.

**3.2.1 Kebutuhan Fungsional**
Kebutuhan fungsional menggambarkan cara kerja sistem dalam merespons
berbagai kondisi serta bagaimana sistem bereaksi terhadap masukan yang diberikan
sesuai dengan spesifikasi yang telah ditentukan. Pada sistem ini, kebutuhan
fungsional mencakup hal-hal berikut:

1. Sensor _loop vehicle detector_ diharapkan mampu mendeteksi objek motor.
2. Raspberry Pi 5 dan IP kamera diharapkan mampu terhubung pada jaringan
    Wi-Fi yang sama.
3. Sistem dapat diberi catu daya 220V AC sehingga keseluruhan komponen
    yang terhubung dapat menyala.
4. USB kamera diharapkan mampu terhubung dengan Raspberry Pi 5.
5. Komunikasi serial antara Raspberry Pi 5 dan Arduino Uno melalui USB
    diharapkan tidak ada delay.
6. Motor driver L298N dan _linear actuator motor_ diharapkan mampu
    terhubung dengan Arduino Uno.
7. _Loop vehicle detector_ dan sebuah LED diharapkan mampu terhubung
    dengan Arduino Uno
8. Aplikasi antarmuka grafis diharapkan mampu terhubung dengan Arduino
    Uno, IP kamera, dan USB kamera.

**3.2.2 Kebutuhaan Non-Fungsional**
Kebutuhan non-fungsional menjelaskan aspek kualitas yang harus dimiliki
sistem agar dapat beroperasi dengan optimal dan menguraikan batasan kemampuan
yang dimiliki oleh sistem. Berikut adalah kebutuhan non-fungsional pada sistem
ini:

1. Sistem diharapkan dapat beroperasi 7 hari dalam seminggu dan 12 jam
    dalam satu hari dengan kondisi tersambung dengan catu daya.


2. Sistem diharapkan dapat menjamin operasional dengan tingkat kegagalan
    minimal.
3. Komponen sistem diharapkan dapat dioperasikan pada sistem operasi
    Linux pada Raspberry Pi 5.
4. Sistem diharapkan dapat memberikan waktu respon dalam menjalankan
    seluruh komponen maksimal 15 detik.
5. Sistem diharapkan tidak membahayakan pengemudi atau kendaraan
    selama operasional.
6. Sistem diharapkan dapat memastikan hanya pengguna dengan hak akses
    khusus yang dapat mengontrol komponen pada sistem.

**3.3 Perancangan Perangkat Keras**
Perancangan perangkat keras terdiri dari perancangan komponen yang
digunakan seperti diagram blok sistem, diagram alur sistem, diagram _wiring_ ,
diagram skematik, 3D desain sistem, dan fabrikasi sistem. Raspberry Pi 5 dipilih
sebagai papan tunggal karena spesifikasi yang dimiliki cocok untuk menjalankan
proses pembelajaran mesin pada sistem dan arsitektur yang dimiliki dapat
mendukung penggunaan AI Kit untuk meningkatkan performa pemrosesan
sistem[23]. Arduino Uno dipilih sebagai mikrokontroler karena spesifikasi yang
dimiliki cukup dan mudah digunakan untuk otomatisasi komponen[24].

## Gambar 3.2 Diagram blok sistem

## Gambar 3.3 Diagram alur sistem


Pada Gambar 3.2 dapat dilihat bahwa sistem ini menggunakan dua sumber
daya listrik, yaitu 220V AC dan 12V DC, untuk memastikan seluruh komponen
dapat beroperasi sesuai dengan kebutuhannya. Sumber daya 220V AC digunakan
untuk Raspberry Pi 5, monitor, kipas pendingin, dan Hailo-8L, sementara 12V DC
digunakan untuk _linear actuator motor_ melalui motor driver L298N.
Sebagai pusat pemrosesan, Raspberry Pi 5 berperan dalam mengolah data
yang diperoleh dari berbagai perangkat, termasuk IP kamera dan USB kamera, yang
digunakan untuk mendeteksi kelengkapan berkendara dan ruas parkir. Data hasil
pemrosesan kemudian ditampilkan melalui monitor yang menjalankan aplikasi
desktop. Untuk mencegah panas berlebih, Raspberry Pi 5 dilengkapi dengan kipas
pendingin, sementara modul Hailo-8L membantu dalam akselerasi komputasi
berbasis pembelajaran mesin.
Selain itu, sistem ini juga dilengkapi dengan _loop vehicle detector_ , yang
berfungsi mendeteksi keberadaan ketika masih di bawah portal parkir. Perangkat
ini terhubung ke Arduino Uno, yang bertindak sebagai pengendali perantara antara
sensor dan aktuator. Arduino menerima sinyal dari Raspberry Pi 5 dan _loop vehicle
detector_ lalu meneruskannya ke motor driver L298N, yang mengendalikan _linear
actuator motor_ untuk membuka dan menutup portal parkir secara otomatis.
Tabel 3. 1 Perincian diagram blok sistem

```
Kode Komponen
Pengirim
```
```
Komponen
Penerima
```
```
Media
Komunikasi
```
```
Metode
Komunikasi
Data
C1
```
```
Sumber daya
220V AC
```
```
Raspberry Pi 5 Kabel listrik -
C2 Loop vehicle
detector
```
```
Kabel listrik -
```
```
C 3 IP kamera Kabel listrik -
C 4 Sumber Daya
12V DC
```
```
Kabel listrik -
```
```
C 5 Monitor Kabel listrik -
```

```
C 6 Sumber daya
12V DC
```
```
Motor driver
L298N
```
```
Kabel listrik -
```
```
C 7 Raspberry Pi 5 Arduino Uno Kabel serial Serial
Arduino Uno Raspberry Pi 5
C 8 Loop vehicle
detector
```
```
Arduino Uno Kabel jumper Sinyal digital
```
```
C 9 Arduino Uno Motor driver
L298N
```
```
Kabel jumper Sinyal digital
```
```
C 10 Motor driver
L298N
```
```
Linear actuator
motor
```
```
Kabel power
motor
```
```
PWM dan H-
Bridge kontrol
C 11 Linear
actuator
motor
```
```
Portal parkir Baut dan mur -
```
```
C 12 Raspberry Pi 5 Monitor HDMI TMDS
Aplikasi
Desktop
```
```
Internal Internal
```
```
C 13 Raspberry Pi 5 USB kamera USB UVC
C 14 Raspberry Pi 5 IP kamera Wi-Fi RTSP / ONVIF
C 15 Raspberry Pi 5 Kipas pendingin Kabel jumper -
C 16 Raspberry Pi 5 Hailo-8L PCIe Protokol PCIe
```
**3.3.2 Diagram Alur Sistem**
Sistem mulai bekerja setelah dihubungkan ke sumber daya, lalu IP kamera
memeriksa koneksi Wi-Fi. Jika belum terhubung, IP kamera akan mencoba
menyambungkan ke jaringan. Jika tetap gagal, IP kamera akan mengulangi untuk
menyambungkan ke jaringan hingga tersambung. Kemudian, Raspberry Pi 5 akan
melakukan koneksi Wi-Fi. Jika belum terhubung, Raspberry Pi 5 akan mengulangi
untuk melakukan koneksi Wi-Fi. Setelah koneksi IP kamera dan Raspberry Pi 5


berhasil, selanjutnya konfigurasi _autostart_ akan berjalan dan menandakan bahwa
sistem siap digunakan. IP kamera dan USB kamera mulai bekerja, melakukan
deteksi dan mengirimkan informasi ke monitor.
Sistem akan mendeteksi ruas parkir melalui IP kamera dan mengirimkan
informasi ruas parkir terbaru secara waktu nyata. Di samping itu, ketika ada objek
yang mendekati portal parkir, USB kamera akan mendeteksi apakah pengendara
menggunakan motor dan helm. Jika tidak, portal tetap tertutup. Namun, jika
pengendara memenuhi kriteria, sistem akan membuka portal dan menampilkan
informasi ruas parkir dan pratinjau deteksi helm pada monitor. Setelah itu, sensor
_loop vehicle detector_ memeriksa apakah objek masih berada di bawah portal. Jika
iya, portal akan tetap terbuka. Namun, jika tidak, portal akan tertutup dan sistem
akan melakukan deteksi kembali untuk proses baru selanjutnya. Gambar 3.3 adalah
diagram alur yang menggambarkan proses kerja sistem ini.

## Gambar 3.4 Diagram wiring sistem


**3.3.3 Diagram Wiring Sistem**

Gambar 3. 4 Diagram _wiring_ sistem
Diagram _wiring_ sistem adalah representasi visual dari hubungan koneksi dan
listrik antara berbagai komponen dalam suatu sistem elektronik. Diagram pada
Gambar 3.4 menunjukkan bagaimana komponen pada sistem terhubung satu sama
lain menggunakan kabel untuk membentuk suatu sistem yang berfungsi. Pin yang
digunakan di Arduino Uno berjumlah 7, terdiri dari pin 5V, GND, A0, 4, 5, 6, 10,
dan 11. Sedangkan pin pada Raspberry Pi 5 biasa disebut GPIO tidak digunakan
dikarenakan komponen yang terhubung yaitu IP kamera menggunakan koneksi Wi-
Fi, USB kamera menggunakan koneksi USB, monitor menggunakan koneksi
HDMI, dan Hailo-8L menggunakan koneksi PCIe pada _board_.
**3.3.4 Skematik**
Skematik merupakan perincian lebih lanjut dari diagram _wiring_ dan berguna
sebagai panduan ketika proses menghubungkan koneksi antar komponen yang
saling terhubung ke Raspberry Pi 5 dan Arduino Uno, sehingga memudahkan
pemahaman aliran koneksi dibandingkan dengan diagram _wiring_ yang menekankan
pada tata letak fisik kabel. Skematik pada Gambar 3.5 adalah representasi visual
rangkaian dari sistem yang digambarkan menggunakan simbol-simbol standar
untuk menunjukkan bagaimana komponen-komponen di dalamnya terhubung.


## Gambar 3.5 Skematik

Tabel 3. 2 Perincian skematik
**Pin
Komponen**

```
Pin Raspberry
Pi 5
```
```
Pin Arduino
Uno
```
```
Deskripsi
```
```
Hailo-8L
PCIe PCIe - Antarmuka
komunikasi
IP kamera
```
- - - Menggunakan
    Wi-Fi
**USB kamera**
USB USB - Antarmuka
komunikasi
**Monitor**
HDMI HDMI - Antarmuka
komunikasi
**Kipas pendingin**
5V 5V - Sumber daya
komponen


```
GND GND - Ground
PWM GPIO 18 - PWM
Raspberry Pi 5 dan Arduino Uno
```
- Serial Serial Antarmuka
    komunikasi

## Tabel 4.2 Pengujian Loop Vehicle Detector

```
3 - A0 Jalur koneksi
untuk membaca
besar tegangan
4 - D10 Jalur koneksi
untuk menyalakan
indikator lampu
Button
1 - D4 Jalur koneksi
untuk memberi
tegangan
2 - GND Ground
LED
Anoda - D10 Menyala ketika
loop vehicle
detector bekerja
Katoda - GND Ground melalui
resistor
Motor driver L298N
12V - - Terhubung ke
sumber daya
220V AC
GND - GND Ground
5V - 5V Sumber daya
cadangan Arduino
Uno
ENA - D11 PWM
IN1 - D5 Kontrol H-Bridge
IN2 - D6 Kontrol H-Bridge
```

```
OUT1 - - Terhubung ke
linear actuator
motor
OUT2 - - Terhubung ke
linear actuator
motor
```
## Gambar 3.6 Desain 3D sistem

Gambar 3. 6 Desain 3D sistem
Desain 3D sistem merupakan perancangan visual dari wadah sistem, bingkai
wadah, dan tiang penyangga IP kamera yang dibuat khusus untuk menyesuaikan
kebutuhan sistem ini. Pada Gambar 3. 6 merupakan desain keseluruhan yang telah
dibuat. Proses desain ini dilakukan untuk memastikan bahwa seluruh komponen
dapat terpasang dengan baik, terlindungi, serta memiliki tata letak yang sesuai
rancangan.
Tabel 3. 3 Perincian desain wadah

```
Wadah
Desain Keterangan
```
## Gambar 4.5 Desain wadah tampak belakang

```
yang digambarkan dengan posisi horizontal dan
memiliki 2 lubang sebagai akses tombol dan sirkulasi
udara. Kemudian, bentuk depan wadah yang
digambarkan dengan posisi vertikal.
```

```
Desain ini menggambarkan bagian dalam wadah yang
berada di sisi belakang wadah yang digambarkan
memiliki warna biru. Warna coklat muda digambarkan
sebagai papan kayu untuk menampung komponen
seperti Raspberry Pi 5 beserta koneksi USB kamera
dan monitor, Arduino Uno, linear actuator motor,
motor driver , loop vehicle detector, dan power supply.
Desain ini menggambarkan bagian depan dan belakang
wadah dari tampak atas.
```
Tabel 3. 4 Perincian desain bingkai wadah
**Bingkai Wadah
Desain Keterangan**

## Tabel 3.4 Perincian desain bingkai wadah

```
tampak samping kiri.
```
```
Desain ini menggambarkan bentuk bingkai wadah
dengan posisi vertikal dari tampak samping kiri.
```
```
Desain ini menggambarkan bentuk bingkai wadah
tampak samping kanan.
```

Tabel 3. 5 Perincian tiang penyangga IP kamera

## Gambar 4.2 Tampilan akhir tiang penyangga IP kamera

```
Desain Keterangan
Desain ini menggambarkan bentuk tiang penyangga
IP kamera dalam posisi tegak lurus dari tampak atas.
```
```
Desain ini menggambarkan bentuk tiang penyangga
IP kamera dalam posisi tegak lurus dari tampak
depan.
```
```
Desain ini menggambarkan bentuk tiang penyangga
IP kamera dalam posisi tegak lurus dari tampak kanan
atas.
```
Desain 3D sistem dibuat dengan Tinkercad dan menggunakan ukuran asli
yang didapat dari mengukur aspek yang dibutuhkan. Namun, ketika pembuatan
desain skalanya diubah menjadi 1:2 dari nilai asli.
Pengukuran pertama adalah menghitung total dimensi panjang, lebar dan
tinggi setiap komponen yaitu Raspberry Pi, Arduino Uno, _linear actuator motor,_
motor driver _, loop vehicle detector_ , dan _power supply_. Hasil yang didapat untuk
dimensi panjang, lebar, dan tinggi pada wadah dapat dilihat pada Tabel 3. 6 berikut.
Tabel 3. 6 Perhitungan ukuran wadah
**Wadah
Bagian Rincian Nilai (cm)**
Luar Panjang 67,5
Lebar 30
Tinggi 5,5
Dalam Panjang 60,9


```
Lebar 24,2
Tinggi 5
Lubang pemasangan
linear actuator motor
```
```
Panjang 3,6
Lebar 4
Tinggi 3,5
Lubang penahan baut
portal
```
Panjang 3
Lebar 2
Tinggi 3
Kemudian, menghitung tinggi pengendara motor ketika menaiki sebuah
motor, hasilnya mendapatkan ketinggian rata-rata 1,5 meter. Lalu mengukur total
dimensi wadah, sehingga didapatkan hasil yang dapat diliihat pada Tabel 3.7
berikut.
Tabel 3. 7 Perhitungan ukuran bingkai wadah
**Bingkai Wadah
Bagian Rincian Nilai (cm)**
Tiang penyangga (tanpa
kaki)

```
Panjang 135,2
Lebar 31
Tinggi 7,5
Kaki penyangga bagian
kiri (bulat)
```
```
Tinggi 4
Lebar 4
Kedalaman 4
Kaki penyangga bagian
kiri (kotak)
```
Panjang 27
Lebar 16
Tinggi 4
Terakhir, menghitung panjang dan lebar area parkir yang akan digunakan
sebagai area segmentasi pendeteksian ruas parkir. Hasil pengukuran area parkir
memiliki panjang 9,08 m dan lebar 18,8 m. Dalam pengukuran nilai untuk


pembuatan tiang penyangga IP kamera dilakukan dalam beberapa tahapan sebagai
berikut:

1. Parameter awal

## Gambar 3.7 Denah peletakan IP kamera

```
Berikut adalah rincian parameter yang digunakan dalam perhitungan:
```
- Tinggi tiang = 3 meter (asumsi perencanaan)
- Sudut pandang kamera (FOV Horizontal) = 90 ° per foto (asumsi)
- Jumlah kamera = 2 kamera, masing-masing menangkap 2 sisi
2. Perhitungan Cakupan Kamera
Asumsikan kamera dipasang pada ketinggian 3 meter, dan sudut pandang
kamera membentuk segitiga dengan lantai parkir. Untuk mencari panjang cakupan
area horizontal, dapat menggunakan Persamaan (3.1) yaitu trigonometri:

```
𝑥=ℎ×tan(𝜃)
Dimana:
```
- x = jarak horizontal yang bisa dicakup
- h = tinggi pemasangan kamera (3 meter)
- θ = setengah dari sudut FOV (karena sudut pandang
    terbagi dua ke kiri dan kanan dari arah tengah kamera)

### (3.1)

Jika sudut FOV 90 °, maka:

```
𝜃=^90
```
```
°
2 =^45
```
### °


𝑥= 3 ×tan( 45 °)= 3 × 1 = 3 𝑚𝑒𝑡𝑒𝑟
Sehingga satu kamera dapat mencakup lebar sekitar 6 meter (2x3 meter)
secara horizontal, karena setiap kamera menangkap dua sisi. Maka, total cakupan
horizontal dalam satu siklus adalah 12 meter dari satu tiang.
Jika area parkir yang ingin diawasi memiliki lebar 9,08 meter, maka dengan
dua kamera di satu tiang setinggi 3 meter, spesifikasi IP kamera dan tinggi tiang
sudah bisa mencakup seluruh area parkir.

Gambar 3. 8 Hollow Galvanis (^) Gambar 3. 9 ACP
Setelah proses 3D desain selesai, tahapan selanjutnya adalah pemilihan
material bahan untuk merealisasikan desain menjadi bentuk nyata. Bahan pertama
adalah hollow galvanis, bahan ini dipilih menjadi material dikarenakan sifatnya
yang ringan namun kuat, serta ketahanannya terhadap korosi dan karat. Selain itu,
hollow galvanis memiliki struktur yang stabil dan mudah dalam proses pemotongan
serta penyambungan, sehingga mempermudah proses fabrikasi[25]. Bahan kedua
adalah _Alumunium Composite Panel_ (ACP), bahan ini dipilih karena memiliki
bersifat ringan, tahan api, biaya yang rendah, serta daya tahan yang baik[26].
**3.4 Lingkungan Pengembangan Sistem
3.4.1 Lingkungan Pengembangan**
Spesifikasi perangkat keras dan perangkat lunak pada komputer papan
tunggal Raspberry Pi 5 yang digunakan dalam pengembangan sistem ini:

1. Raspbian Bookworm 64-bit.
2. Micro-SD 128 GB.
3. Hailo-8L Entry-Level AI Accelerator.
4. RealVNC Server 7.13.1.


5. Visual Studio Code.
6. Python 3.11.2 beserta pustaka tambahan hailo-apps-infra, hailort, ultralytics,
    customtkinter, cvzone, onnxruntime, onvif.
7. Koneksi internet.
8. Logitech C270 HD Webcam.
9. IP kamera PTZ Wi-Fi C18 beserta tiang penyangga.
10. Kerndy monitor HDMI 7 Inch.
11. Wadah mesin sistem yang terdiri dari Arduino Uno, Motor driver L298N,
    _linear actuator motor_ , _loop vehicle detector_ , _power supply_ , dan portal parkir.

Spesifikasi perangkat keras dan perangkat lunak pada laptop Lenovo Legion
5 yang digunakan dalam pengembangan sistem ini:

1. Windows 11 64-bit.
2. AMD Ryzen 7 5800H, memori 16GB, dan SSD 512GB.
3. RealVNC Viewer 7.13.1.
4. Visual Studio Code.
5. Python 3.11.2 beserta pustaka tambahan hailo-apps-infra, hailort, ultralytics,
    customtkinter, cvzone, onnxruntime, onvif.
6. Koneksi Internet.
7. Logitech C270 HD Webcam.
8. IP kamera PTZ Wi-Fi C18 beserta tiang penyangga.
9. Wadah mesin sistem yang terdiri dari Arduino Uno, Motor driver L298N,
    _linear actuator motor_ , _loop vehicle detector_ , _power supply_ , dan portal parkir.

**3.4.2 Lingkungan Operasional**
Spesifikasi perangkat keras dan perangkat lunak yang digunakan dalam
lingkungan operasional adalah sebagai berikut:

1. Sistem operasi Raspbian Bookworm 64-bit.
2. Hailo-8L.
3. Kartu memori Micro-SD 128GB.
4. Logitech C270 HD Webcam.


5. IP kamera PTZ Wi-Fi C18 beserta tiang penyangga.
6. Koneksi internet.
7. Kerndy monitor HDMI 7 Inch.
8. Program _Autostart._
9. Wadah mesin sistem yang terdiri dari komputer papan tunggal Raspberry Pi
    5 dengan kipas pendingin, Arduino Uno, Motor driver L298N, _linear actuator_
    _motor_ , _loop vehicle detector_ , _power supply_ , dan portal parkir.

**3.5 Metode Pengujian**
Sistem ini memiliki perancangan metode pengujian untuk menguji sistem.
Rincian metode pengujian yang dilakukan adalah sebagai berikut:

1. Pengujian Fungsional: Pengujian ini bertujuan untuk memastikan bahwa
    setiap fitur dalam sistem ini berfungsi sesuai spesifikasi yang telah
    ditentukan.
2. Pengujian Non-Fungsional: Pengujian ini bertujuan untuk mengetahui tingkat
    keandalan sistem dari berbagai aspek seperti kecepatan dan ketepatan
    pembacaan sensor, performa sistem seperti penggunaan CPU dan memori,
    serta suhu sistem. Pada pengujian penggunaan CPU dan memori, serta suhu
    sistem, lingkungan pengembangan dan lingkungan operasional dijadikan
    kondisi untuk mengambil pengukuran nilai, hal ini dikarenakan untuk
    mendapat hasil perbandingan dari 2 lingkungan berbeda dalam menguji
    performa sistem.


### 40

### BAB IV

## BAB IV IMPLEMENTASI DAN PENGUJIAN

```
Pada Bab IV Implementasi dan Pengujian, akan membahas proses
implementasi dari rancangan yang telah diuraikan pada Bab III Perancangan Sistem,
serta pengujian yang dilakukan untuk mengevaluasi kinerja sistem. Implementasi
sistem menghasilkan suatu produk yang kemudian diuji melalui dua metode utama
sesuai dengan perancangan yang telah disusun. Pertama, pengujian fungsional
dilakukan guna memastikan bahwa setiap fitur dalam sistem beroperasi sesuai
dengan spesifikasi yang telah ditetapkan. Kedua, pengujian non-fungsional
bertujuan untuk menilai keandalan sistem berdasarkan berbagai aspek, seperti
akurasi pembacaan sensor, suhu sistem, serta efisiensi penggunaan CPU dan
memori.
```
**4.1 Implementasi Produk**

## Gambar 4.1 Tampilan akhir sistem portal parkir

```
IP kamera
Gambar 4.1 dan Gambar 4.2 merupakan tampilan akhir dari hasil proses
implementasi sistem portal parkir untuk mendeteksi kelengkapan berkendara dan
ketersediaan ruang parkir yang terdiri dari tiga tahapan utama, yaitu implementasi
desain 3D sistem, implementasi perangkat keras, dan implementasi perangkat lunak.
```

Setelah masing-masing tahapan selesai, hasil dari ketiga tahapan tersebut
diintegrasikan guna memastikan keselarasan sistem sebelum tahap pengujian dapat
dilakukan.

**4.1.1 Fabrikasi Desain 3D Wadah**
Aluminium Composite Panel (ACP) digunakan sebagai bahan dasar dalam
pembuatan wadah sistem yang telah dirancang melalui Tinkercad. Desain 3D
tersebut kemudian direalisasikan melalui proses pemotongan, pengelasan, dan
pengecatan berwarna putih untuk memastikan kekuatan dan ketahanan dari struktur
yang dihasilkan.

```
Gambar 4. 3 Desain wadah tampak depan^ Gambar 4. 4 Hasil fabrikasi wadah^ tampak
depan
```
Gambar 4. 5 Desain wadah tampak belakang^ Gambar 4. 6 Hasil fabrikasi wadah^ tampak
belakang
Pada Gambar 4.5, desain wadah bagian belakang terdapat area berwarna biru,
pewarnaan di area tersebut digunakan sebagai tanda pemasangan papan kayu untuk
meletakkan komponen perangkat keras seperti Arduino Uno, motor driver L298N,
_linear actuator motor_ , _loop vehicle detector_ , dan _power supply_.


## Gambar 4.8 Detail baut dan lubang diperbesar

Wadah sistem memiliki lubang di bagian atas sebagai akses jalan keluar dari
pergerakan vertikal komponen _linear actuator motor._ Kemudian, di samping kanan
lubang tersebut terdapat lubang untuk tempat dipasangnya baut untuk memasang
portal parkir yang akan dihubungkan pada _linear actuator motor_. Berikut
merupakan spesifikasi _linear actuator motor_ yang digunakan pada sistem.

- **Tegangan Operasional:** 12V DC.
- **Daya:** 20W.
- **Dorongan atau Tarikan Maksimum:** Sekitar 200 kg / 2000 N.
- **Panjang Langkah:** 200 mm/20 cm.
- **Kecepatan** : 30mm/detik.
- **Rasio kecepatan dan tegangan:** 30 mm/detik (kg).
- **Sakelar Bawaan** : Ya.
    Kemudian, melakukan perhitungan waktu yang dibutuhkan untuk _linear
actuator motor_ mengangkat palang serta sudut akhir kemiringannya. Diketahui:
- **Massa palang =** 500 gram = 0,5 kg.
- **Panjang palang** = 37 cm = 0,37 m.
- **Posisi titik tumpu (baut penahan di kiri)** = 2 cm dari ujung kiri portal.
- **Posisi sambungan ke aktuator** = 12 cm dari ujung kiri portal.
- **Panjang Langkah:** 20 cm = 0,2 m.
- **Kecepatan** : 30mm/detik.
- **Dorongan atau Tarikan Maksimum:** Sekitar 200 kg / 2000 N.


1. Waktu Linear Actuator Motor Terangkat Maksimal

𝑡=𝑃𝑎𝑛𝑗𝑎𝑛𝑔𝐾𝑒𝑐𝑒𝑝𝑎𝑡𝑎𝑛^ 𝐿𝑎𝑛𝑔𝑘𝑎ℎ (4.1)^
Maka,
𝑡= 00 , 03 ,^2 ≈ 6 , 67 𝑑𝑒𝑡𝑖𝑘
Jadi, dengan menggunakan tegangan operasional yang disarankan _linear
actuator motor_ akan mencapai posisi maksimal dalam waktu sekitar 6,67 detik.
Sementara itu, dilakukan juga peningkatan _power supply_ menjadi 18V untuk
melihat perbandingan kecepatan[8], didapatkan hasil waktu untuk _linear actuator
motor_ akan mencapai posisi maksimal berkurang menjadi 5 detik. Namun, karena
ukuran _power supply_ 18V terlalu besar untuk ditempatkan dalam wadah sistem,
maka tetap digunakan _power supply_ 12V.

2. Sudut Kemiringan Maksimal
    _Linear actuator motor_ terhubung pada titik 12 cm dari ujung kiri portal. Oleh
karena itu, perubahan ketinggian di titik tersebut dapat dihitung. Ketika linear
actuator mencapai panjang langkah penuh sebesar 0,2 m, titik sambungan palang
akan terangkat sejauh 0,2 m. Sudut akhir kemiringan palang dapat ditentukan
menggunakan persamaan trigonometri.

𝜃=tan−^1 (𝑗𝑎𝑟𝑎𝑘 ℎ𝑜𝑟𝑖𝑧𝑜𝑛𝑡𝑎𝑙𝑘𝑒𝑛𝑎𝑖𝑘𝑎𝑛 𝑑𝑎𝑟𝑖 𝑏𝑎𝑢𝑡^ 𝑡𝑖𝑛𝑔𝑔𝑖 𝑘𝑒 𝑡𝑖𝑡𝑖𝑘^ 𝑎𝑘𝑡𝑢𝑎𝑡𝑜𝑟 𝑠𝑎𝑚𝑏𝑢𝑛𝑔𝑎𝑛 𝑎𝑘𝑡𝑢𝑎𝑡𝑜𝑟) (4.^2 )^
Jarak horizontal antara baut penahan (2 cm dari ujung) dan sambungan
aktuator (12 cm dari ujung) adalah:
𝑑= 12 − 2 = 10 𝑐𝑚= 0 , 1 𝑚
Sehingga,

𝜃=tan−^1 (^00 ,,^21 )
𝜃=tan−^1 ( 2 ) ≈ 63 , 43 °
Jadi, saat _linear actuator motor_ mencapai titik tertinggi, palang akan
membentuk sudut sekitar 63,43° dari posisi awalnya.


## Gambar 4.10 Desain fabrikasi bingkai wadah tampak depan

Sebagai material utama dalam pembuatan bingkai wadah sistem, hollow
galvanis digunakan untuk memberikan struktur yang kokoh dan tahan lama. Desain
3D yang telah dirancang melalui Tinkercad diwujudkan melalui serangkaian proses
pemotongan, pengelasan, serta pengecatan berwarna putih guna menjamin
kekuatan dan daya tahan.

## Gambar 4.3 Desain wadah tampak depan

```
depan
```
## Gambar 4.9 Desain bingkai wadah tampak depan

```
tampak depan
```
## Gambar 4.11 Desain tiang penyangga IP kamera

Dalam pembuatan tiang penyangga IP kamera, Aluminium Composite Panel
(ACP) dipilih sebagai material utama untuk memastikan struktur yang kuat dan
tahan lama. Desain 3D yang dikembangkan menggunakan Tinkercad direalisasikan
melalui tahapan pemotongan, pengelasan, serta pengecatan berwarna putih agar
menghasilkan konstruksi yang kokoh dan kuat.


```
Gambar 4. 11 Desain tiang penyangga IP
kamera
```
```
Gambar 4. 12 Hasil fabrikasi tiang penyangga
IP kamera
```
Pada tiang penyangga IP kamera setinggi 3 meter terdapat plat berbentuk
persegi yang terletak di ujung tiang, plat ini berfungsi untuk meletakkan 2 buah IP
kamera secara bertolak belakang dengan bantuan sekrup di kedua sisi.

## Gambar 4.13 Plat untuk tempat IP kamera

**4.1.4 Pemasangan Linear Actuator Motor**

## Gambar 4.14 Linear Actuator Motor pada wadah sistem

Komponen pertama yang dipasang pada wadah sistem adalah _linear
actuator motor_ , yang diposisikan dekat dengan lubang yang telah dibuat pada
wadah. Penempatan ini bertujuan untuk memudahkan pergerakan portal parkir
secara vertikal. Selain itu, komponen ini juga dipasang dengan kuat pada wadah
menggunakan siku-siku yang terpasang pada badan _linear actuator motor_.


**4.1.5 Pemasangan Arduino Uno**

## Gambar 4.15 Arduino Uno pada wadah sistem

Arduino Uno ditempatkan dekat _linear actuator motor_ untuk
meminimalkan jarak dengan Raspberry Pi 5 sehingga tidak mengganggu
pengaturan kabel antara _linear actuator motor_ dan motor driver L298N. Sebagai
pengganti _breadboard_ , digunakan papan PCB polos yang berfungsi sebagai media
koneksi komponen sebagaimana ditunjukkan pada Gambar 4.16. Papan PCB
tersebut dilengkapi terminal untuk _loop vehicle detector_ pada pin A0 dan 10, motor
driver L298N pada pin 5V, GND, 5, 6, dan 11, serta _emergency button_ pada pin 4
yang terhubung ke GND.

## Gambar 4.16 Arduino Shield dari sebuah papan PCB

**4.1.6 Pemasangan Motor Driver L298N**

```
Gambar 4. 17 Motor Driver L298N pada wadah sistem
```

Posisi pemasangan motor driver L298N harus berdekatan dengan _linear
actuator motor_ dan Arduino Uno untuk menghindari kendala keterbatasan panjang
kabel _jumper_ yang dapat menghambat koneksi antar komponen. Motor driver
L298N telah mulai dihubungkan ke Arduino Uno, dengan konfigurasi sebagai
berikut: pin IN1 terhubung ke pin 5, pin IN2 ke pin 6, pin ENA ke pin 11, pin 5V
ke pin 5V, dan pin GND ke pin GND pada Arduino Uno.

**4.1.7 Pemasangan Power Supply**

## Gambar 4.17 Motor Driver L298N pada wadah sistem

_Power supply_ berperan sebagai sumber daya utama untuk _linear actuator
motor_ , motor driver L298N, dan Arduino Uno. Pin V+ dan V- pada _power supply_
terhubung ke terminal 12V dan GND pada motor driver L298N. Selain itu, pin L
dan N pada _power supply_ dikoneksikan langsung ke jaringan listrik untuk
memastikan suplai daya yang stabil.

**4.1.8 Pemasangan Loop Vehicle Detector**

## Gambar 4.19 Loop Vehicle Detector pada wadah sistem

Komponen ini dipasang di dekat lubang untuk _linear actuator motor_ guna
menyediakan ruang yang cukup untuk penempatan kabel sensor serta


meminimalkan jarak kabel _jumper_ menuju Arduino Uno, sehingga instalasi lebih
rapi dan efisien.
**4.1.9 Pemasangan Raspberry Pi 5**

## Gambar 4.20 Raspberry Pi 5 pada wadah sistem

Raspberry Pi 5 dipasang berdekatan dengan Arduino Uno untuk
meminimalkan jarak kabel USB A to USB B agar tetap mencukupi, sekaligus
memberikan ruang yang cukup bagi koneksi USB kamera, USB C untuk sumber
daya, dan Micro HDMI pada Raspberry Pi 5. Keseluruhan rangkaian komponen
dapat dilihat pada Gambar 4.21

## Gambar 4.21 Keseluruhan komponen di dalam wadah x

**4.1.10 Konfigurasi Raspberry Pi 5**
Setelah Raspberry Pi 5 terpasang pada wadah, tahap selanjutnya adalah
melakukan konfigurasi pada perangkat tersebut. Konfigurasi ini mencakup
serangkaian prosedur yang harus dilakukan secara sistematis. Adapun tahapan-
tahapan konfigurasi tersebut adalah sebagai berikut:

1. Melakukan instalasi sistem operasi Raspberry Pi OS, konfigurasi dasar yang
    umumnya diterapkan mencakup sinkronisasi waktu sistem, manajemen
    kredensial pengguna seperti nama pengguna dan sandi, serta pengaturan
    protokol akses jarak jauh seperti Telnet dan SSH. Proses ini dilakukan dengan
    memanfaatkan aplikasi Raspberry Pi Imager melalui laptop. Sistem operasi


```
yang digunakan adalah Raspbian Bookworm 64-bit, sebagaimana
ditampilkan pada Gambar 4. 22.
```
## Gambar 4.22 Instalasi sistem operasi Raspberry Pi

2. Melakukan pengaturan VNC Server agar dapat diakses oleh VNC Viewer
    pada laptop, proses ini dilakukan agar Raspberry Pi 5 dapat dikendalikan dari
    jarak jauh menggunakan laptop melalui satu koneksi Wi-Fi yang sama.
    Masukkan berikut untuk mengaktifkan VNC Server.
    _Terminal:_
       sudo raspi-config
    Kemudian, pilih Interface Options → VNC → Enable. Tekan Enter, lalu
    keluar dari raspi-config. Hasilnya seperti pada Gambar 4. 23

## Gambar 4.23 Aktivasi VNC Server


3. Instalasi Visual Studio Code (VS Code) dilakukan pada Raspberry Pi 5 untuk
    proses pengembangan, penyuntingan kode secara langsung dan melakukan
    _clone_ folder berisi kode sistem yang didapat melalui tautan berikut, yaitu
    https://github.com/mbprayoga/parking-space-detection-with-helm-
    detection.git. Proses instalasi VS Code dilakukan dengan mengunduh paket
    yang sesuai dengan arsitektur sistem operasi yang digunakan melalui tautan
    berikut, yaitu https://code.visualstudio.com/. Setelah itu melakukan
    membuka _terminal_ dan memasukkan perintah berikut.
    _Terminal:_
       cd Downloads
       sudo dpkg -i file_name.deb
       cd ..
       git clone https://github.com/mbprayoga/parking-space-
       detection-with-helm-detection.git
       code.

## Gambar 4.24 Instalasi Visual Studio Code

4. Raspberry Pi 5 pada sistem portal parkir untuk mendeteksi kelengkapan
    berkendara dan ketersediaan ruang parkir memiliki sebuah akselerator AI
    yaitu Hailo-8L. Setelah akselerator ini dipasang melalui PCIe, diperlukan
    pengaturan tambahan agar dapat digunakan bersama Raspberry Pi 5.

## Gambar 4.25 Instalasi Hailo-8L berhasil


```
Berikut perintah yang perlu dilakukan untuk mengintegrasikan kedua hal ini.
Terminal:
sudo apt update
sudo apt full-upgrade
sudo raspi-config
sudo reboot
sudo apt install hailo-all
sudo reboot
hailortcli fw-control identify
Untuk mendapatkan performa optimal dari perangkat Hailo, PCIe perlu diatur
ke Gen3. Meskipun menggunakan Gen2 memungkinkan, namun akan
menghasilkan performa yang lebih rendah. Hailo AI HAT akan terdeteksi
secara otomatis sebagai Gen3, tetapi jika menggunakan M.2 HAT,
pengaturan harus dilakukan secara manual. Caranya adalah setelah
mengeksekusi sudo raspi-config, Pilih opsi 6 Advanced Options, lalu
pilih A8 PCIe Speed. Pilih Yes untuk mengaktifkan mode PCIe Gen 3. Klik
Finish untuk keluar.
```
5. Instalasi pustaka yang dibutuhkan untuk mengembangkan dan menjalankan
    sistem portal parkir untuk mendeteksi kelengkapan berkendara dan
    ketersediaan ruang parkir. Pustaka-pustaka yang digunakan adalah cvzone,
    ultralytics, customtkinter, pandas, onvif-zeep, natsort,
    opencv-python-headless, pillow, dan pyserial.

## Gambar 4.26 File Requirements.txt

```
Proses instalasi dilakukan dengan membuat sebuah virtual environment
Python dan sebuah file requirements.txt. Kemudian, file requirements.txt
diinstal di dalam virtual environment tersebut. Berikut adalah perintah
yang digunakan untuk melakukan instalasi.
```

```
Terminal:
sudo apt update
python3 -m venv venv
source venv/bin/activate
pip install -r requirements.txt
```
6. Terakhir, perlu adanya otomatisasi penggunaan Raspberry Pi 5 pada sistem
    portal parkir untuk mendeteksi kelengkapan berkendara dan ketersediaan
    ruang parkir agar mengurangi interaksi pengguna ketika ingin
    mengoperasikannya. Metode yang dilakukan adalah membuat sebuah
    program bernama autostart dalam bentuk file _bash shell_ , program ini
    dirancang agar ketika Raspberry Pi 5 pertama kali dinyalakan akan
    mengeksekusi _bash shell_ tersebut yang berisi tahapan dalam menjalankan
    semua proses sistem secara mandiri.
    Pertama, menuju folder direktori yang memiliki nama pengguna seperti pada
    sistem ini bernama kelompok 2 0 kemudian klik kanan pada mouse dan pilih
    Show Hidden untuk menampilkan folder sistem. Kemudian, masuk ke dalam
    folder .config dan membuat sebuah folder bernama autostart. Di dalam
    folder tersebut akan dibuat sebuah file baru berekstensi .desktop bernama
    startprogram.desktop.

## Gambar 4.27 File startprogram.desktop

Kemudian, masukkan perintah berikut di dalam file tersebut.
[Desktop Entry]
Exec=/home/kelompok20/Desktop/setup_auto.sh


Terminal=true
Selanjutnya, menuju Desktop dan membuat file berekstensi .sh bernama
setup_auto.sh. Masukkan perintah berikut di dalam file tersebut.
#!/bin/bash
sleep 30
cd ..
cd /home/kelompok20/parking-space-detection-with-helm-detection
# export QT_QPA_PLATFORM=xcb
source setup_env.sh
python3 src/gui.py >> program_log.txt 2>&1
deactivate

**4.1.11 Konfigurasi Arduino Uno**
Setelah melakukan pemasangan Arduino Uno, maka masih perlu dilakukan
konfigurasi seperti memasukkan kode program sesuai dengan rancangan sistem.
Langkah pertama yang perlu dilakukan adalah menghubungkan Arduino Uno
dengan laptop melalui kabel USB. Kemudian, membuka aplikasi Arduino IDE lalu
masukkan perintah berikut.
#define buttonpin 4
const int led = 10;
const int sensorPin = A0;
const int ENA_PIN = 11;
const int IN1_PIN = 5;
const int IN2_PIN = 6;
unsigned long previousMillis = 0;
unsigned long interval = 10000;
bool motorDirection = false;
bool motorRunning = false;
float voltageThreshold = 2.8;
String getSerialData() {
if (Serial.available() > 0) {
char incomingByte = Serial.read();
if (incomingByte == '1' || incomingByte == '0') {
String response = "";
response += incomingByte;
Serial.println("");
return response;
}
}
return "";
}
void setup() {
Serial.begin(115200);


Serial.println("Sistem Portal Parkir - Kontrol Linear
Actuator");
pinMode(ENA_PIN, OUTPUT);
pinMode(IN1_PIN, OUTPUT);
pinMode(IN2_PIN, OUTPUT);
pinMode(led, OUTPUT);
pinMode(buttonpin, INPUT_PULLUP);

digitalWrite(led, HIGH);
digitalWrite(IN1_PIN, LOW);
digitalWrite(IN2_PIN, LOW);
}

void loop() {
String command = getSerialData();

if ((command == "1" || digitalRead(buttonpin) == LOW) &&
motorRunning == false) {
motorRunning = true;
motorDirection = true;
previousMillis = millis();
Serial.println(previousMillis);
}
if (motorRunning) {
int rawValue = analogRead(sensorPin);
float voltage = (rawValue / 1023.0) * 5.0;
static bool baruNaik = false;
static bool sedangTurun = false;
if(voltage > voltageThreshold) {
motorDirection = true;
baruNaik = true;
sedangTurun = false;
}
else {
if(baruNaik) {
motorDirection = false;
baruNaik = false;
sedangTurun = true;
}
if(sedangTurun) {
motorDirection = false;
motorRunning = false;
sedangTurun = false;
}
else{
unsigned long currentMillis = millis();
if(currentMillis - previousMillis >= interval) {
previousMillis = currentMillis;
stopMotor();
}
}
}
if(motorDirection) {


analogWrite(ENA_PIN, 255);
digitalWrite(IN1_PIN, HIGH);
digitalWrite(IN2_PIN, LOW);
}
else{
analogWrite(ENA_PIN, 255);
digitalWrite(IN1_PIN, LOW);
digitalWrite(IN2_PIN, HIGH);
}
}
if(motorRunning == false){
int rawValue = analogRead(sensorPin);
float voltage = (rawValue / 1023.0) * 5.0;
if(voltage > voltageThreshold){
motorRunning = true;
motorDirection = true;
}
}
}
void stopMotor() {
motorDirection = !motorDirection;
analogWrite(ENA_PIN, 0);
digitalWrite(IN1_PIN, LOW);
digitalWrite(IN2_PIN, LOW);
motorRunning = false;
}
Pada program di atas, terdapat deklarasi beberapa variabel pada pin yang
digunakan sesuai perancangan seperti sensorPin = A0; untuk membaca tegangan
dari _loop vehicle detector_ , buttonpin 4 untuk _emergency button_ , IN1_PIN = 5;
untuk pin IN1 pada motor driver L298N, IN1_PIN = 6; untuk pin IN2 pada motor
driver L298N, led = 10; sebagai indikator lampu LED untuk menandakan adanya
tegangan dari _loop vehicle detector_. dan ENA_PIN = 11; untuk pin ENA pada motor
driver L298N. Alih-alih menggunakan delay untuk membagi tugas ketika kode
berjalan, program ini menggunakan millis untuk membagi tugas. Penggunaan
millis lebih disarankan dibandingkan delay karena memungkinkan eksekusi
beberapa tugas secara bersamaan tanpa menghentikan keseluruhan program.
Program diatur memiliki waktu 10 detik untuk melakukan 1 kali menjalankan tugas
ketika menerima masukan 1 dari Raspberry Pi 5 dan pembacaan ambang batas
tegangan di 2,8V untuk mengetahui ketika terdapat sinyal dari _loop vehicle detector._
Program akan memulai menjalankan tugas ketika masukan 1 dari Raspberry Pi 5
melalui serial atau ketika _emergency button_ ditekan. Setelah kondisi awal terpenuhi,
motor driver L298N akan menggerakan _linear actuator motor_ untuk menaikkan


```
portal parkir dan memiliki waktu tunggu selama 5 detik sebelum portal parkir
kembali ke posisi semula. Namun, ketika pengendara masih berada di bawah portal,
loop vehicle akan bekerja dan mengirim sinyal kepada Arduino Uno untuk
menaikkan portal kembali.
```
**4.2 Pengujian Produk**
Pengujian produk dilakukan untuk memastikan bahwa sistem portal parkir
untuk mendeteksi kelengkapan berkendara dan ketersediaan ruang parkir yang telah
dikembangkan berfungsi sesuai dengan spesifikasi yang telah dirancang. Proses ini
mencakup pengujian fungsional dan non-fungsional.

## Tabel 4.1 Pengujian Fungsional

```
Pengujian fungsional dilakukan untuk mengevaluasi kinerja seluruh
komponen perangkat keras yang telah dikembangkan, guna memastikan bahwa
setiap komponen dapat beroperasi, baik secara individu maupun dalam integrasi
keseluruhan sistem. Pengujian ini dilakukan pada 23 Februari 2025 di area parkir
bersama Fakultas Teknik disesuaikan dengan kebutuhan fungsional pada BAB III
Perancangan Sistem.
Tabel 4. 1 Pengujian Fungsional
No Pengujian Keluaran yang
diharapkan
```
```
Hasil
Pengujian
1 Raspberry Pi 5 dan
Arduino Uno diberi catu
daya 220 V AC
```
```
Raspbery Pi 5, Arduino Uno,
linear actuator, L298N dan
USB camera menyala.
```
```
Sesuai
```
```
2 Loop vehicle detector,
IP camera, monitor dan
exhaust fan diberi catu
daya 220 V AC
```
```
Loop vehicle detector, IP
camera, monitor dan exhaust
fan menyala.
```
```
Sesuai
```
```
3 Raspberry Pi 5 dan IP
camera dihubungkan ke
Wi-Fi
```
- Raspberry Pi 5 dan IP
    camera dapat terhubung
    dalam 1 jaringan bersama
    dengan Wi-Fi melalui
    SSID dan sandi yang telah
    disimpan.

```
Sesuai
```

- IP camera dapat
    mengambil foto atau
    video.
4 USB camera
dihubungkan ke
Raspberry Pi

```
USB camera dapat
mengambil foto atau video.
```
```
Sesuai
```
```
5 Komunikasi Serial
Raspberry Pi 5 dan
Arduino Uno melalui
USB
```
```
Arduino bisa menerima
pesan serial dari Raspberry
Pi begitupun sebaliknya.
```
```
Sesuai
```
```
6 L298N dan linear
actuator dihubungkan
ke Arduino Uno
```
- L298N menyala dan dapat
    mengendalikan linear
    actuator.
- Linear actuator bergerak
    ke atas dan bawah.

```
Sesuai
```
```
7 Loop vehicle detector
dan sebuah LED
dihubungkan ke
Arduino Uno
```
- Loop vehicle detector
    menyala.
- Loop vehicle detector
    dapat membaca
    pergerakan, dibuktikan
    dengan LED menyala.

```
Sesuai
```
```
8 Aplikasi antarmuka
grafis dapat terhubung
dengan Arduino Uno, IP
Camera dan USB
Camera.
```
- Aplikasi antarmuka grafis
    dapat mengakses informasi
    video atau foto yang
    diambil dari IP Camera
    dan USB Camera.
- Aplikasi antarmuka dapat
    mengirim sinyal ke
    Arduino Uno

```
Sesuai
```
**4.2.2 Pengujian Non-Fungsional**
Pengujian Non-Fungsional ini dilakukan untuk memastikan bahwa sistem
tidak hanya bekerja sesuai spesifikasi, tetapi juga memiliki tingkat kestabilan dan
efisiensi yang optimal dalam beroperasi.

1. Pengujian Loop Vehicle Detector


Gambar 4. 28 Pengujian _Loop Vehicle Detector_^
Pada Gambar 4.28, kabel sepanjang 10 meter _loop vehicle detector_ dipasang
dengan cara membentuk oval sebanyak 3 lilitan dalam sebuah papan kayu
berukuran 60 x 100 cm. Pengujian dilakukan sebanyak 3 iterasi dengan
menggunakan 10 sampel pengukuran pada setiap iterasi. Kemudian, hasil setiap
iterasi dihitung berdasarkan Persamaan (4.1), Persamaan (4.2), Persamaan (4.3),
dan Persamaan (4.4)[27].
Rumus menghitung nilai akurasi:
%𝐴𝑘𝑢𝑟𝑎𝑠𝑖=𝑞𝑞^×100%

### (4.1)

```
Rumus menghitung rata-rata nilai akurasi:
𝑅𝑎𝑡𝑎−𝑟𝑎𝑡𝑎 𝐴𝑘𝑢𝑟𝑎𝑠𝑖=∑𝐴𝑘𝑢𝑟𝑎𝑠𝑖^ 𝑠𝑒𝑡𝑖𝑎𝑝𝑛^ 𝑖𝑡𝑒𝑟𝑎𝑠𝑖
```
### (4.2)

```
Rumus menghitung nilai galat:
%𝐺𝑎𝑙𝑎𝑡=𝑇𝑦𝑝𝑒^ 𝐼^ +𝑞𝑇𝑦𝑝𝑒^ 𝐼𝐼×100%
```
### (4. 3 )

```
Rumus menghitung rata-rata nilai galat:
𝑅𝑎𝑡𝑎−𝑟𝑎𝑡𝑎 𝐺𝑎𝑙𝑎𝑡=∑𝐺𝑎𝑙𝑎𝑡^ 𝑠𝑒𝑡𝑖𝑎𝑝𝑛^ 𝑖𝑡𝑒𝑟𝑎𝑠𝑖
```
### (4.2)

Penjelasan:
q = Jumlah kendaraan sebenarnya
q^ = Jumlah kendaraan yang terdeteksi
Type I error = Kendaraan ada tetapi tidak terdeteksi
Type II error = Kendaraan tidak ada tetapi terdeteksi


Tabel 4. 2 Pengujian _Loop Vehicle Detector_
**Pengujian Jumlah
Motor
Sebenarnya**

```
Jumlah Motor
Terdeteksi
```
```
Type I
Error
```
```
Type II
Error Akurasi (%)
```
**Galat
(%)**
1 10 10 0 0 100 % 0 %
2 10 8 1 1 8 0% 2 0%
3 10 9 1 0 9 0% 1 0%
Maka, dapat dihitung:
Rata-rata nilai akurasi (%):
𝑅𝑎𝑡𝑎−𝑟𝑎𝑡𝑎 𝐴𝑘𝑢𝑟𝑎𝑠𝑖=^2703 =90%

Rata-rata nilai galat (%):

𝑅𝑎𝑡𝑎−𝑟𝑎𝑡𝑎 𝐺𝑎𝑙𝑎𝑡=^303 =10%
Berdasarkan hasil pengujian, rata-rata akurasi sensor loop detector dalam
mendeteksi kendaraan mencapai 90%, dengan rata-rata galat sebesar 10%. Hal ini
menunjukkan bahwa sensor cukup andal dalam mendeteksi kendaraan, meskipun
terdapat sedikit kesalahan deteksi. Kesalahan yang terjadi merupakan Type I Error,
yaitu ketika kendaraan tidak terdeteksi oleh sensor, sedangkan Type II Error tidak
ditemukan dalam pengujian ini. Hasil ini mengindikasikan bahwa sistem masih
dapat ditingkatkan untuk mengurangi galat dan meningkatkan akurasi deteksi
kendaraan.

## Tabel 4.3 Pengujian IP Kamera

```
Gambar 4. 29 Pengujian IP kamera
```

Pada Gambar 4.29 dilakukan pengujian IP kamera untuk memastikan
keandalan dari IP kamera yang telah dipasang pada tiang penyangga setinggi 3
meter dan terhubung dengan Raspberry Pi 5 dalam mengambil data gambar.
Tabel 4. 3 Pengujian IP Kamera
**Pengujian Jumlah
Motor
Sebenarnya**

```
Jumlah Motor
Terkirim
```
```
Type I
Error
```
```
Type II
Error Akurasi (%)
```
```
Galat
(%)
```
1 10 9 1 0 90 % 0 %
2 10 8 1 1 8 0% 10%
3 10 10 0 0 10 0% 0%
Maka, dapat dihitung:
Rata-rata nilai akurasi (%):

```
𝑅𝑎𝑡𝑎−𝑟𝑎𝑡𝑎 𝐴𝑘𝑢𝑟𝑎𝑠𝑖=^2703 =90%
```
Rata-rata nilai galat (%):

𝑅𝑎𝑡𝑎−𝑟𝑎𝑡𝑎 𝐺𝑎𝑙𝑎𝑡=^303 =10%
Dari hasil pengujian IP kamera dalam menangkap gambar untuk deteksi
ruas parkir, didapatkan rata-rata akurasi sebesar 90%, dengan rata-rata galat sebesar
10%. Jumlah motor terkirim yang dimaksud adalah ketika data ruas parkir yang
tampil pada aplikasi desktop.
Hasil ini menunjukkan IP kamera memiliki tingkat keandalan yang cukup
baik, tetapi masih dapat ditingkatkan lebih lanjut untuk mengurangi kesalahan
deteksi dan mencapai akurasi yang lebih optimal dengan meningkatkan tinggi tiang
penyangga untuk mendapatkan cakupan pandangan lebih luas.

## Gambar 4.30 Pengujian USB kamera

Pada Gambar 4. 30 dilakukan pengujian USB kamera untuk memastikan
keandalan dari USB kamera yang telah terhubung dengan Raspberry Pi 5 dalam
mengambil data gambar.


```
Gambar 4. 30 Pengujian USB kamera
```
Tabel 4. 4 Pengujian USB kamera
**Pengujian Jumlah Data
Sebenarnya**

```
Jumlah Data
Terkirim ke
Arduino Uno
```
```
Type I
Error
```
```
Type II
Error Akurasi (%)
```
**Galat
(%)**
1 10 10 0 0 10 0% 0%
2 10 9 1 0 9 0% 10%
3 10 9 1 0 9 0% 1 0%
Maka, dapat dihitung:
Rata-rata nilai akurasi (%):

```
𝑅𝑎𝑡𝑎−𝑟𝑎𝑡𝑎 𝐴𝑘𝑢𝑟𝑎𝑠𝑖=^2803 ≈92%
```
Rata-rata nilai galat (%):

𝑅𝑎𝑡𝑎−𝑟𝑎𝑡𝑎 𝐺𝑎𝑙𝑎𝑡=^203 = 6 ,6%
Dari hasil pengujian USB kamera dalam pengambilan gambar untuk deteksi
kelengkapan berkendara, diperoleh rata-rata akurasi sekitar 92% dan rata-rata galat
sebesar 6.67%. Jumlah data terkirim ke Arduino Uno yang dimaksud adalah ketika
data yang dikirim oleh Raspberry Pi 5 ke Arduino Uno ketika pendeteksian berhasil.
Hasil ini menunjukkan bahwa USB kamera memiliki performa deteksi yang
baik dalam mengidentifikasi kendaraan, tetapi masih ada potensi peningkatan untuk
mencapai akurasi yang lebih tinggi dengan pencahayaan cukup.

## Tabel 4.5 Pengujian waktu respons sistem

Pengujian waktu respons sistem dilakukan untuk memastikan keandalan
sistem portal parkir dalam mendeteksi kelengkapan berkendara serta ketersediaan


ruas parkir. Proses ini mencakup pendeteksian pengendara oleh USB kamera,
pengolahan data untuk menentukan kelengkapan berkendara, penyampaian
informasi mengenai ketersediaan ruas parkir, hingga aktivasi portal parkir agar
pengendara dapat melintas dengan lancar. Hasil pengujian dapat dilihat pada Tabel
4.5.
Tabel 4. 5 Pengujian waktu respons sistem
**Pengujian
ke-**

```
Waktu Respons Sistem (s)
```
1 6 , 58
2 7 ,55
3 7,50
4 7,37
5 7
6 7 ,22
7 6
8 6,10
9 7 ,80
10 7
**Rata-rata** 7,11
Berdasarkan hasil pengujian waktu respons sistem yang telah dilakukan
sebanyak 10 kali iterasi, diperoleh waktu respons berkisar antara 6 hingga 7,80
detik dengan rata-rata 7,11 detik. Hasil ini menunjukkan bahwa sistem mampu
menjalankan seluruh komponen dengan waktu respons di bawah batas maksimal
yang ditetapkan, yaitu 15 detik. Dengan demikian, dapat disimpulkan bahwa sistem
telah memenuhi kebutuhan non-fungsional terkait waktu respons.

## Tabel 4.6 Pengujian Performa tanpa Integrasi Hailo-8L

Pengujian performa dilakukan tanpa integrasi Hailo-8L untuk mengevaluasi
kinerja Raspberry Pi 5 dalam mengolah data pembelajaran mesin secara mandiri.
Fokus pengujian mencakup penggunaan CPU dan memori, serta suhu CPU saat
beroperasi dalam dua lingkungan berbeda, yaitu lingkungan pengembangan dan
lingkungan operasional di area parkir bersama Fakultas Teknik. Selain itu, analisis
juga dilakukan untuk mengidentifikasi potensi bottleneck dalam pemrosesan data,


terutama terkait keterbatasan arsitektur CPU dan kemampuan akselerasi inferensi
tanpa bantuan prosesor eksternal. Pengujian dilakukan selama 60 menit dengan
pencatatan data otomatis setiap 10 detik menggunakan baris kode yang dijalankan
bersamaan dengan sistem sejak pertama kali sistem dinyalakan. Data hasil
pengujian performa tanpa integrasi Hailo-8L secara lengkap dapat dilihat pada
Tabel 4. 6.
Tabel 4. 6 Pengujian Performa tanpa Integrasi Hailo-8L
**Waktu
Pengukuran
(s)**

```
Lingkungan Pengembangan Lingkungan Operasional
Beban
Kerja
CPU (%)
```
```
Memori
(%)
```
```
Suhu CPU
(°C)
```
```
Beban
Kerja
CPU (%)
```
```
Memori
(%)
```
```
Suhu CPU
(°C)
```
```
00:05 92 20,2 68,1 89,3 20,2 63,7
00:10 88,6 20,4 76,8 88,4 20,9 74,7
00:15 89,4 20,2 77,4 90,7 20,9 76,3
00:20 88,4 20,3 79 92,7 21 77,9
00:25 91,7 20,3 78,5 90,9 21,1 77,4
00:30 90,1 20,3 80,1 90,6 21 78,5
00:35 89,2 20,3 78,5 87,9 21 77,9
00:40 91,2 20,3 77,9 92,6 21,1 77,9
00:45 94,2 20,2 80,7 88,9 21 78,5
00:50 90,5 20,3 79,6 92,9 21,1 77,9
00:55 88,8 20,3 78,5 89,5 20,9 79
01:00 86,3 20,3 78,5 89,8 20,9 77,9
Rata-rata 90,0 3 20,28 77,8 90,35 20,92 76,46
```
## Gambar 4.31 Pengujian Beban Kerja CPU tanpa Hailo-8L

## Gambar 4.34 Pengujian Beban Kerja CPU dengan Hailo-8L

Raspberry Pi 5 dalam menangani proses komputasi tanpa integrasi Hailo-8L.


Pengujian ini dilakukan pada dua lingkungan yang berbeda, yaitu lingkungan
pengembangan di dalam ruangan dan lingkungan operasional di lapangan. Tujuan
utama pengujian ini adalah untuk memahami sejauh mana Raspberry Pi 5 dapat
mengelola beban kerja pembelajaran mesin secara mandiri serta mengidentifikasi
potensi keterbatasan dalam pemrosesan data waktu nyata.

## Gambar 4.33 Pengujian Suhu CPU tanpa Hailo-8L

Pada Gambar 4.31 dapat dilihat hasil pengujian menunjukkan bahwa beban
kerja CPU tanpa Hailo-8L, rata-rata penggunaan CPU di lingkungan
pengembangan adalah 90,03%, sedangkan di lingkungan operasional sedikit lebih
tinggi, yaitu 90,35%. Secara umum, kedua lingkungan memiliki pola penggunaan
CPU yang fluktuatif, dengan perbedaan nilai beban kerja pada setiap interval waktu.
Misalnya, pada menit ke-5, lingkungan pengembangan mencatat beban 92%, lebih
tinggi dibandingkan lingkungan operasional yang sebesar 89,3%. Namun, pada
menit ke-45, kondisi berbalik dengan lingkungan operasional mencapai beban
tertinggi 94,2%, sementara lingkungan pengembangan hanya 88,9%.
Beban CPU yang terus-menerus tinggi di kedua lingkungan ini berpotensi
meningkatkan suhu serta konsumsi daya, yang perlu diperhatikan agar tidak
menyebabkan panas berlebih atau degradasi performa.

```
82
```
```
84
```
```
86
```
```
88
```
```
90
```
```
92
```
```
94
```
```
96
```
```
0:05 0:10 0:15 0:20 0:25 0:30 0:35 0:40 0:45 0:50 0:55 1:00
```
```
Beban Kerja CPU (%)
```
```
Waktu Pengukuran (s)
```
# Komparasi Beban Kerja CPU

```
Lingkungan Pengembangan Lingkungan Operasional
```

**Pengujian Penggunaan Memori**
Pengujian ini bertujuan untuk mengevaluasi sejauh mana Raspberry Pi 5
dapat mengelola konsumsi memori secara mandiri pada kedua lingkungan uji serta
mengidentifikasi potensi keterbatasan dalam alokasi sumber daya saat menjalankan
proses pembelajaran mesin.

Gambar 4. 32 Pengujian Penggunaan Memori tanpa Hailo-8L
Pada Gambar 4.32 dapat dilihat hasil pengujian penggunaan memori,
terlihat bahwa lingkungan operasional memiliki rata-rata penggunaan memori
sebesar 20,93%, yang lebih tinggi dibandingkan lingkungan pengembangan dengan
rata-rata 20,28%. Pada awal pengujian, kedua lingkungan memiliki nilai yang sama,
yakni 20,2%, namun setelahnya lingkungan operasional mengalami peningkatan
yang cukup signifikan hingga mencapai puncak di 21,1% pada beberapa titik,
sedangkan lingkungan pengembangan cenderung lebih stabil dengan fluktuasi kecil
di sekitar 20,2% hingga 20,4%. Hal ini menunjukkan bahwa lingkungan
operasional cenderung menggunakan lebih banyak memori secara konsisten
dibandingkan lingkungan pengembangan.

```
19.6
```
```
19.8
```
```
20
```
```
20.2
```
```
20.4
```
```
20.6
```
```
20.8
```
```
21
```
```
21.2
```
```
0:05 0:10 0:15 0:20 0:25 0:30 0:35 0:40 0:45 0:50 0:55 1:00
```
```
Penggunaan Memori (%)
```
```
Waktu Pengukuran (s)
```
# Komparasi Penggunaan Memori

```
Lingkungan Pengembangan Lingkungan Operasional
```

**Pengujian Suhu CPU**
Pengujian ini dilakukan untuk mengamati bagaimana Raspberry Pi 5
menangani panas yang dihasilkan selama pemrosesan, serta membandingkan
perbedaan suhu antara lingkungan pengembangan dan operasional.

## Gambar 4.32 Pengujian Penggunaan Memori tanpa Hailo-8L

Pengujian suhu CPU pada Gambar 4.33, terlihat bahwa lingkungan pengembangan
memiliki rata-rata suhu sebesar 77,8°C, sedikit lebih tinggi dibandingkan
lingkungan operasional yang memiliki rata-rata 76,47°C. Pada awal pengujian,
suhu di lingkungan operasional lebih rendah dibandingkan lingkungan
pengembangan, namun seiring waktu keduanya mengalami peningkatan yang stabil
dan mendekati nilai yang serupa. Puncak suhu terjadi pada menit ke-45 di
lingkungan pengembangan dengan 80,7°C, sedangkan lingkungan operasional
relatif lebih stabil dengan variasi yang lebih kecil.

## Tabel 4.7 Pengujian Performa dengan Integrasi Hailo-8L....................................

Pengujian performa dilakukan dengan tambahan integrasi Hailo-8L untuk
menganalisis pengaruhnya terhadap kinerja Raspberry Pi 5 dalam mengolah data
pembelajaran mesin. Aspek yang diuji mencakup penggunaan CPU dan memori,
serta suhu CPU saat beroperasi dalam dua lingkungan berbeda, yaitu lingkungan
pengembangan dan lingkungan operasional di area parkir bersama Fakultas Teknik.
Pengujian dilakukan selama 6 0 menit dengan pencatatan data otomatis setiap 10

```
0
```
```
10
```
```
20
```
```
30
```
```
40
```
```
50
```
```
60
```
```
70
```
```
80
```
```
90
```
```
0:05 0:10 0:15 0:20 0:25 0:30 0:35 0:40 0:45 0:50 0:55 1:00
```
```
Suhu CPU (
```
```
°)
```
```
Waktu Pengukuran (s)
```
# Komparasi Suhu CPU

```
Lingkungan Pengembangan Lingkungan Operasional
```

detik menggunakan baris kode yang dijalankan bersamaan dengan sistem sejak
pertama kali sistem dinyalakan. Data hasil pengujian performa dengan integrasi
Hailo-8L secara lengkap dapat dilihat pada Tabel 4. 7.
Tabel 4. 7 Pengujian Performa dengan Integrasi Hailo-8L
**Waktu
Pengukuran
(s)**

```
Lingkungan Pengembangan Lingkungan Operasional
Beban
Kerja
CPU (%)
```
```
Memori
(%)
```
```
Suhu CPU
(°C)
```
```
Beban
Kerja
CPU (%)
```
```
Memori
(%)
```
```
Suhu CPU
(°C)
```
```
00:05 20 16 50 20 16,7 52,7
00:10 10,4 16 57,6 15 16,8 57,1
00:15 17,4 15,9 57,6 17,4 16,8 58,2
00:20 14,2 15,9 57,6 17 16,8 58,2
00:25 15,9 15,9 57,1 17 16,7 57,6
00:30 17 15,9 57,1 17,7 16,8 57,6
00:35 15,7 15,9 57,1 14,9 16,7 57,6
00:40 13,1 16 57,6 17,6 16,7 57,6
00:45 14,8 15,9 57,1 16,7 16,8 59,3
00:50 16,4 15,9 56,5 17,3 16,7 58,2
00:55 11 16 56,5 11,1 16,8 58,2
01:00 12 15,9 57,6 12,6 16,7 57,6
Rata-rata 14,82 15,93 56,61 16,19 16,75 57,49
```
**Pengujian Beban Kerja CPU**
Pengujian beban kerja CPU dilakukan untuk memahami dampak integrasi
Hailo-8L terhadap kinerja Raspberry Pi 5 pada dua lingkungan yang berbeda, yaitu
lingkungan pengembangan di dalam ruangan dan lingkungan operasional di
lapangan. Pengukuran dilakukan secara berkala untuk melihat bagaimana CPU
menangani proses selama sistem berjalan.


## Gambar 4.36 Pengujian Suhu CPU dengan Hailo-8L

Pada Gambar 4. 34 dapat dilihat hasil pengujian menunjukkan bahwa beban
kerja CPU rata-rata di lingkungan pengembangan adalah 1 4 , 8 2%, sedangkan di
lingkungan operasional meningkat menjadi 16, 19 %, dengan selisih 1, 37 % atau
9 , 25 % lebih tinggi. Kenaikan ini menunjukkan adanya beban kerja tambahan di
lingkungan operasional, pengolahan data sensor secara waktu nyata dan konstan.
Meskipun terjadi peningkatan, berat beban kerja masih berada dalam batas yang
aman dan tidak mengalami lonjakan ekstrem. Hal ini menandakan bahwa Raspberry
Pi 5 mampu menangani lingkungan operasional dengan baik tanpa mengalami
bottleneck atau penurunan performa yang signifikan.

**Pengujian Penggunaan Memori**
Penggunaan memori juga menjadi aspek penting dalam menilai keandalan
sistem. Pengujian ini bertujuan untuk mengetahui sejauh mana konsumsi memori
pada kedua lingkungan uji serta kemungkinan adanya perbedaan dalam alokasi
sumber daya. Pengukuran dilakukan dengan metode pencatatan berkala guna
memperoleh data penggunaan memori sepanjang pengujian.

```
0
```
```
5
```
```
10
```
```
15
```
```
20
```
```
25
```
```
0:05 0:10 0:15 0:20 0:25 0:30 0:35 0:40 0:45 0:50 0:55 1:00
```
```
Beban Kerja CPU (%)
```
```
Waktu Pengukuran (s)
```
# Komparasi Beban Kerja CPU

```
Lingkungan Pengembangan Lingkungan Operasional
```

## Gambar 4.35 Pengujian Penggunaan Memori dengan Hailo-8L

Pada Gambar 4. 35 dapat dilihat hasil pengujian penggunaan memori di
lingkungan pengembangan memiliki rata-rata 15, 93 %, sementara di lingkungan
operasional meningkat menjadi 16,75%, dengan selisih 0, 82 % atau peningkatan
sekitar 5, 15 %. Kenaikan ini mengindikasikan adanya kebutuhan memori tambahan
dalam skenario operasional, kemungkinan akibat pemrosesan sistem secara waktu
nyata dan konstan. Meskipun terjadi peningkatan, penggunaan memori tetap stabil
tanpa adanya lonjakan signifikan yang mengindikasikan _memory leak_. Oleh karena
itu, Raspberry Pi 5 masih mampu menjalankan sistem dengan baik di lingkungan
operasional,

**Pengujian Suhu CPU**
Suhu CPU juga menjadi salah satu indikator dalam mengevaluasi kestabilan
sistem saat beroperasi dalam jangka waktu tertentu. Pengujian ini dilakukan untuk
mengamati perbedaan suhu antara lingkungan pengembangan dan operasional serta
bagaimana faktor eksternal seperti sirkulasi udara dan suhu lingkungan dapat
mempengaruhi kinerja perangkat. Pengukuran suhu dilakukan secara berkala untuk
memperoleh gambaran perubahan suhu CPU selama sistem berjalan.

```
15.4
```
```
15.6
```
```
15.8
```
```
16
```
```
16.2
```
```
16.4
```
```
16.6
```
```
16.8
```
```
17
```
```
0:05 0:10 0:15 0:20 0:25 0:30 0:35 0:40 0:45 0:50 0:55 1:00
```
```
Penggunaan Memori (%)
```
```
Waktu Pengukuran (s)
```
# Komparasi Penggunaan Memori

```
Lingkungan Pengembangan Lingkungan Operasional
```

Gambar 4. 36 Pengujian Suhu CPU dengan Hailo-8L
Pengujian suhu CPU pada Gambar 4. 36 menunjukkan bahwa rata-rata suhu
di lingkungan pengembangan adalah 56, 61 °C, sedangkan di lingkungan
operasional meningkat menjadi 57, 49 °C, dengan selisih 0. 88 °C atau 1 , 56 % lebih
tinggi. Peningkatan ini dapat disebabkan oleh beban kerja tambahan, perbedaan
suhu lingkungan antara lingkungan pengembangan dan lingkungan operasional,
serta kondisi pendinginan yang mungkin kurang optimal di lapangan. Meskipun
suhu mengalami kenaikan, nilai yang tercatat masih berada dalam batas aman dan
tidak mencapai tingkat yang dapat menyebabkan _throttling_ atau penurunan
performa.

7. Analisis Komparatif Kinerja Raspberry Pi 5 dengan dan tanpa Hailo-8L
**Analisis Data**
Tanpa Hailo-8L:
- Beban kerja CPU rata-rata: 90,03% (pengembangan), 90,35% (operasional).
- Memori rata-rata: 20,28% (pengembangan), 20,92% (operasional).
- Suhu rata-rata CPU: 77,8°C (pengembangan), 76,46°C (operasional).
Dengan Hailo-8L:
- Beban kerja CPU rata-rata: 14,82% (pengembangan), 16,19% (operasional).
- Memori rata-rata: 15,93% (pengembangan), 16,75% (operasional).
- Suhu rata-rata CPU: 56,61°C (pengembangan), 57,49°C (operasional).

```
44
```
```
46
```
```
48
```
```
50
```
```
52
```
```
54
```
```
56
```
```
58
```
```
60
```
```
0:05 0:10 0:15 0:20 0:25 0:30 0:35 0:40 0:45 0:50 0:55 1:00
```
```
Suhu CPU (
```
```
°)
```
```
Waktu Pengukuran (s)
```
# Komparasi Suhu CPU

```
Lingkungan Pengembangan Lingkungan Operasional
```

Dari hasil ini, terlihat bahwa dengan menggunakan Hailo-8L, beban kerja
CPU turun drastis sekitar 83,5% (dari 90% menjadi sekitar 15%), dan suhu CPU
menurun sekitar 27,5% (dari 77,8°C menjadi 56,61°C). Hal ini menunjukkan bahwa
tugas-tugas inferensi yang sebelumnya dikerjakan CPU kini ditangani oleh Hailo-
8L, yang dirancang untuk pembelajaran mesin dibandingkan dengan CPU utama
Raspberry Pi 5.

**Perhitungan Efisiensi Penggunaan Hailo-8L**
Jika diasumsikan konsumsi daya CPU Raspberry Pi 5 dalam kondisi full load
sekitar 5W, maka:

- Daya CPU tanpa Hailo:

```
𝑃𝐶𝑃𝑈= 5 𝑊×^90100 ,^03 = 4 , 5 𝑊
```
- Daya CPU dengan Hailo:

𝑃𝐶𝑃𝑈= 5 𝑊×^14100 ,^82 = 0 , 74 𝑊
Sementara itu, Hailo-8L memiliki konsumsi daya maksimum sekitar 2.5W
dalam kondisi inferensi penuh. Dengan demikian, total daya saat menggunakan
Hailo menjadi:
𝑃𝑇𝑂𝑇𝐴𝐿=𝑃𝐶𝑃𝑈 +𝑃𝐻𝐴𝐼𝐿𝑂= 0 , 74 𝑊+ 2 , 5 𝑊= 3 , 24 𝑊
Penggunaan Hailo-8L di Raspberry Pi 5 secara signifikan mengurangi beban
kerja CPU dan memori, konsumsi daya, serta suhu sistem. Ini karena Hailo-8L
menangani tugas pembelajaran mesin secara efisien dengan arsitektur yang lebih
optimal dibandingkan CPU, yang tidak dirancang khusus untuk pembelajaran
mesin intensif. Akibatnya, sistem menjadi lebih hemat daya dan memiliki suhu
yang lebih baik, memungkinkan penggunaan dalam aplikasi pembelajaran mesin
tanpa risiko panas berlebih yang dapat secara signifikan menurunkan _throughput_
selama inferensi berkelanjutan jangka panjang, dengan penurunan lebih lanjut jika
suhu sekitar tinggi[28]. Dibandingkan dengan total daya tanpa Hailo-8L (sekitar
4 ,5W), penggunaan Hailo-8L memberikan penghematan daya sebesar 27,99%.


### 72

## BAB V PENUTUP

### PENUTUP

**5.1 Kesimpulan**
Berdasarkan hasil implementasi dan pengujian yang telah dilakukan,
penelitian berjudul “Rancang Bangun Sistem Portal Parkir Pendeteksi Kelengkapan
Berkendara dan Ketersediaan Ruas Parkir Menggunakan Integrasi Arduino Uno
dan Raspberry Pi” menghasilkan beberapa kesimpulan sebagai berikut:

1. Sistem portal parkir yang dikembangkan telah berhasil mengintegrasikan
    teknologi pembelajaran mesin dengan perangkat keras seperti Raspberry Pi,
    Arduino Uno, dan sensor terkait. Sistem ini berhasil memproses data
    kelengkapan berkendara secara otomatis serta pemantauan ketersediaan
    ruang parkir secara waktu nyata sesuai dengan kondisi aktual di lapangan.
2. Integrasi perangkat keras dan perangkat lunak dalam sistem portal parkir yang
    dikembangkan berhasil mengimplementasikan fungsi masukan data melalui
    IP kamera, USB kamera, serta mikrokontroler yang mendukung proses
    pembelajaran mesin dan kontrol portal. Sementara itu, fungsi keluaran
    mencakup antarmuka aplikasi desktop yang menampilkan hasil analisis pada
    monitor dan mengontrol mekanisme pembukaan portal parkir.
3. Sistem portal parkir yang dikembangkan jika dijalankan pada Raspberry Pi 5
    tanpa Hailo-8L menunjukkan rata-rata beban CPU 90,35%, konsumsi
    memori 20,92%, dan suhu operasional mencapai 76,46°C, menandakan
    pemrosesan pembelajaran mesin cukup membebani sistem.
4. Dengan integrasi Hailo-8L pada Raspberry Pi 5, beban CPU turun drastis
    hingga 82,07%, dengan rata-rata hanya 16,19%, konsumsi memori berkurang
    4,17% dari 20,92% menjadi 16,75%, dan suhu turun 18,97°C dari 76,46°C
    menjadi 57,49°C. Hal ini menunjukkan bahwa Hailo-8L secara signifikan
    meningkatkan efisiensi pemrosesan, mengurangi penggunaan sumber daya
    utama, serta meningkatkan stabilitas dan kinerja sistem.


**5.2 Saran**
Berdasarkan hasil penelitian berjudul “Rancang Bangun Sistem Portal Parkir
Pendeteksi Kelengkapan Berkendara dan Ketersediaan Ruas Parkir Menggunakan
Integrasi Arduino Uno dan Raspberry Pi”, terdapat beberapa saran untuk
pengembangan lebih lanjut, di antaranya sebagai berikut:

1. Merancang papan sirkuit kustom yang lebih ringkas untuk mengintegrasikan
    sistem dalam satu kesatuan untuk meminimalkan penggunaan kabel _jumper_.
2. Menggunakan _power supply_ 18V dengan penggunaan konverter _step-up_ dari
    12V ke 18V tanpa mengubah dimensi fisik sistem.


### 74

## DAFTAR PUSTAKA

[1] Badan Pusat Statistik, “Perkembangan Jumlah Kendaraan Bermotor
Menurut Jenis (Unit), 2021-2022,” Badan Pusat Statistik.
[2] Badan Pusat Statistik, “Jumlah Kecelakaan, Korban Mati, Luka Berat, Luka
Ringan, dan Kerugian Materi, 2022,” Badan Pusat Statistik.
[3] Fakultas Teknik Universitas Diponegoro, “Kelancaran, Ketertiban, dan
Keselamatan Civitas Akademika Fakultas Teknik Dalam Berkendara,” Surat
Pengumuman No. 436/UN7.F3.2/PENG/II/2023.
[4] Fakultas Teknik Universitas Diponegoro, “SOP Parkir Kendaraan,” Tersedia
di: https://k3.ft.undip.ac.id/wp-content/uploads/2024/02/PK-
A18_UN7.F3_K3_2024-SOP-Parkir-Kendaraan.pdf.
[5] Microsoft, “Apa itu visi komputer?,” Tersedia di:
https://azure.microsoft.com/id-id/resources/cloud-computing-
dictionary/what-is-computer-vision.
[6] K. Kasym, A. Sarsenen, Z. Segizbayev, D. Junuskaliyeva, dan Md. H. Ali,
“Parking Gate Control Based on Mobile Application,” dalam _2018 Joint 7th
International Conference on Informatics, Electronics & Vision (ICIEV) and
2018 2nd International Conference on Imaging, Vision & Pattern
Recognition (icIVPR)_ , IEEE, Jun 2018, hlm. 399–403. doi:
10.1109/ICIEV.2018.8640954.
[7] K. M. Udofia, “Arduino Microcontroller-based Intelligent Car Parking
System,” _Journal of Multidisciplinary Engineering Science and Technology_ ,
vol. 7, no. 3, hlm. 12336–12343, 2020.
[8] A. Hasibuan, Rosdiana, dan D. S. Tambunan, “Design and Development of
An Automatic Door Gate Based on Internet of Things Using Arduino Uno,”
_Bulletin of Computer Science and Electrical Engineering_ , vol. 2, no. 1, hlm.
17 – 27, 2021.
[9] W. A. Jabbar, C. W. Wei, N. A. A. M. Azmi, dan N. A. Haironnazli, “An
IoT Raspberry Pi-based parking management system for smart
campus[Formula presented],” Internet of Things (Netherlands), vol. 14, Jun
2021, doi: 10.1016/j.iot.2021.100387.


[10] I. R. Sahali, M. Anshar, dan Z. Arfah, “Implementasi Otomasi Sistem Palang
Parkir Berbasis Teknologi RFID pada Lahan Parkir Rektorat Unhas.”
[11] R. Robiyanto, W. P. Putra, dan Raswa, “Implementasi Sistem Pada Automasi
Barrier Gate Palang Pintu Parkir Menggunakan ESP32 Dan RFID,” _Coding :
Jurnal Komputer dan Aplikasi_ , vol. 11, no. 03, hlm. 457–466, 2023.
[12] S. E. Mathe, H. K. Kondaveeti, S. Vappangi, S. D. Vanambathina, dan N. K.
Kumaravelu, “A comprehensive review on applications of Raspberry Pi,” 1
Mei 2024, _Elsevier Ireland Ltd_. doi: 10.1016/j.cosrev.2024.100636.
[13] M. N. Achmadiah, N. Setyawan, A. A. Bryantono, C. C. Sun, dan W. K. Kuo,
“Fast Person Detection Using YOLOX with AI Accelerator for Train Station
Safety,” dalam _2024 International Electronics Symposium: Shaping the
Future: Society 5.0 and Beyond, IES 2024 - Proceeding_ , Institute of
Electrical and Electronics Engineers Inc., 2024, hlm. 504–509. doi:
10.1109/IES63037.2024.10665874.
[14] H. K. Kondaveeti, N. K. Kumaravelu, S. D. Vanambathina, S. E. Mathe, dan
S. Vappangi, “A systematic literature review on prototyping with Arduino:
Applications, challenges, advantages, and limitations,” 1 Mei 2021, _Elsevier
Ireland Ltd_. doi: 10.1016/j.cosrev.2021.100364.
[15] P. Peerzadaa, W. H. Larik, dan A. A. Mahar, “DC Motor Speed Control
Through Arduino andL298N Motor Driver Using PID Controller,” _IJEEET_ ,
vol. 4, no. 2, hlm. 21, Des 2021.
[16] S. Kejian, W. Feiming, D. Zikuo, Z. Bin, Z. Zhenyang, dan D. Yanqiang,
“Design and simulation analysis of permanent magnet linear motor actuator
used in circuit breaker,” dalam _2020 5th Asia Conference on Power and
Electrical Engineering (ACPEE)_ , IEEE, Jun 2020, hlm. 1903–1908. doi:
10.1109/ACPEE48638.2020.9136403.
[17] N. Suriyakan dan E. Wangkanklang, “Design and Invention of a Low-Cost
Motor Driver for the wheeled Robot,” dalam _2023 20th International
Conference on Electrical Engineering/Electronics, Computer,
Telecommunications and Information Technology, ECTI-CON 2023_ ,


Institute of Electrical and Electronics Engineers Inc., 2023. doi:
10.1109/ECTI-CON58255.2023.10153382.
[18] M. Jibril, M. Tadese, dan T. Shoga, “Design &amp; Control of Vehicle
Boom Barrier Gate System using Augmented H2 Optimal &amp; H infinity
Synthesis Controllers,” 24 Juli 2020. doi:
10.20944/preprints202007.0585.v1.
[19] H. Mewada, L. S. Sundar, P. Engineer, dan M. Desai, “A Comparative Study
Between External USB and MIPI CSI for Medical Imaging and a Device
Driver Implementation for Endoscope Camera Using USB,” dalam _2023 4th
International Conference on Communication, Computing and Industry 6.0
(C216)_ , IEEE, Des 2023, hlm. 1–5. doi: 10.1109/C2I659362.2023.10430494.
[20] P. A. Abdalla dan C. Varol, “Testing IoT Security: The Case Study of an IP
Camera,” dalam _2020 8th International Symposium on Digital Forensics and
Security (ISDFS)_ , IEEE, Jun 2020, hlm. 1 – 5. doi:
10.1109/ISDFS49300.2020.9116392.
[21] U. K. Malviya dan A. Swain, “Area Optimized TI2C Design for CSI’s
Camera Control Interface Protocol,” _International Journal on Emerging
Technologies_ , vol. 11, no. 4, hlm. 130–139, 2020.
[22] R. A. Gheorghiu, V. Iordache, dan V. A. Stan, “Urban traffic detectors –
comparison between inductive loop and magnetic sensors,” dalam _2021 13th
International Conference on Electronics, Computers and Artificial
Intelligence (ECAI)_ , IEEE, Jul 2021, hlm. 1 – 4. doi:
10.1109/ECAI52376.2021.9515014.
[23] A. H. M. Alaidi, Z. A. Ramadhan, J. S. Alrubaye, H. TH. S. ALRikabi, H.
A. Mutar, dan I. Svyd, “AI-based monkeypox detection model using
Raspberry Pi 5 AI Kit,” _Sustainable Engineering and Innovation_ , vol. 7, no.
1, hlm. 1–14, Jan 2025, doi: 10.37868/sei.v7i1.id393.
[24] S. Saravanan, A. Rishitha, M. Kalaiyarasi, K. V. Bhaskar Reddy, P.
Chandrababu, dan D. G. Kumar, “Identification of parking space availability
by using Arduino Uno for Smart City,” dalam _2022 3rd International_


_Conference on Communication, Computing and Industry 4.0 (C2I4)_ , IEEE,
Des 2022, hlm. 1–5. doi: 10.1109/C2I456876.2022.10051427.
[25] A. Wijaya dan H. Wijaya, “EVALUASI PENGGUNAAN RANGKA BAJA
HOLLOW SEBAGAI SISTEM PENYANGGA BEKISTING PLAT
LANTAI PADA PROYEK X,” _JMTS: Jurnal Mitra Teknik Sipil_ , hlm. 305–
314, Feb 2024, doi: 10.24912/jmts.v7i1.26666.
[26] P. Harmi Tjahjanti, A. Fahruddin, M. Mulyadi, S. Hardy Sujiatanti, M.
Eryandrie Wicaksono, dan R. Bamban Jakaria, “Core Material
Manufacturing Study On Aluminum Composite Panel (ACP),” _Jurnal
Improsci_ , vol. 2, no. 3, hlm. 186 – 196, Des 2024, doi:
10.62885/improsci.v2i3.579.
[27] N. K. Singh, A. K. Tangirala, dan L. D. Vanajakshi, “A Multivariate
Analysis Framework for Vehicle Detection From Loop Data Under
Heterogeneous and Less Lane Disciplined Traffic,” _IEEE Access_ , vol. 9, hlm.
143580 – 143591, 2021, doi: 10.1109/ACCESS.2021.3120470.
[28] T. Benoit-Cattin, D. Velasco-Montero, dan J. Fernández-Berni, “Impact of
Thermal Throttling on Long-Term Visual Inference in a CPU-Based Edge
Device,” _Electronics (Basel)_ , vol. 9, no. 12, hlm. 2106, Des 2020, doi:
10.3390/electronics9122106.


### 78

## LAMPIRAN I BIODATA MAHASISWA

### BIODATA MAHASISWA

```
Nama
Mahasiswa
```
```
: Agustinus Adven Christo
```
```
NIM : 21120121140114
Konsentrasi : Sistem Tertanam dan Robotika
Tempat/Tgl.
Lahir
```
```
: Jakarta, 9 Desember 2002
```
```
Alamat
Sekarang
```
```
: Jalan Deris No. 91 007/005,
Kelapa Dua, Kebon Jeruk,
Jakarta Barat 11550
No. Telepon : +62856 97771493
Alamat E-Mail : clvtito@gmail.com
Nama Orang
Tua
```
```
: Siswo Handoko
```
```
Alamat Orang
Tua
```
```
: Jalan Deris No. 91 007/005,
Kelapa Dua, Kebon Jeruk,
Jakarta Barat 11550
IP Kumulatif : 3, 90
```

