# Argumen Sidang — Pemilihan Metode Pengujian

---

## 1. Kenapa Memilih Tiga Metode Ini?

> **"Saya memilih Black Box, White Box, dan Gray Box karena ketiganya saling melengkapi dan tidak bisa digantikan satu sama lain.**
>
> **Black Box** menguji dari perspektif pengguna — apakah admin bisa login, tambah bahan baku, melakukan penyesuaian stok. Saya tidak perlu tahu bagaimana kode di dalamnya. Ini penting karena pengguna akhir hanya peduli pada 'apakah fitur berfungsi', bukan bagaimana implementasinya.
>
> **White Box** menguji dari perspektif developer — apakah algoritma FEFO benar menghabiskan batch yang paling cepat expired? Apakah perhitungan deduksi stok akurat? Ini hanya bisa dijawab dengan melihat langsung ke kode dan data.
>
> **Gray Box** menjembatani keduanya — saya tahu struktur database dan routing, tapi saya menguji melalui HTTP seperti pengguna sungguhan. Ini memvalidasi bahwa semua layer — dari request, autentikasi, controller, service, hingga database — bekerja bersama secara benar.
>
> Tiga metode ini memastikan kualitas dari tiga sisi berbeda: UI (Black Box), logika internal (White Box), dan integrasi antar modul (Gray Box)."

---

## 2. Kenapa Tidak Memasukkan Metode Lain?

### Performance / Load Testing

> **"Performance testing tidak saya masukkan karena skala penggunaan sistem ini tidak membutuhkannya. W9 Cafe melayani sekitar 50-200 transaksi per hari dengan maksimal 3 kasir dan 2 admin secara bersamaan. Beban ini sangat ringan untuk arsitektur Laravel + PostgreSQL yang saya gunakan.**
>
> Benchmark eksternal menunjukkan Laravel mampu menangani ribuan request per detik pada hardware modern. Untuk skenario cafe ini, performance testing tidak akan memberikan insight yang berarti karena bottleneck pasti bukan di kode, melainkan di kecepatan internet pengguna.
>
> Namun demikian, saya tetap melakukan profiling menggunakan Laravel Debugbar selama pengembangan untuk memastikan tidak ada N+1 query atau slow query yang tidak perlu. Ini saya lakukan sebagai bagian dari praktik pengembangan, bukan sebagai metode pengujian formal."

### Usability Testing

> **"Usability testing idealnya melibatkan pengguna akhir yang merepresentasikan target user sesungguhnya. Untuk konteks tugas akhir ini, keterbatasan akses ke pengguna nyata — yaitu admin dan kasir W9 Cafe — menjadi kendala utama.**
>
> Selain itu, antarmuka admin dibangun menggunakan Filament yang sudah mengikuti standar UI/UX Bootstrap dan diuji oleh ribuan pengembang di ekosistem Laravel. Dengan demikian, risiko usability issue pada level fundamental sudah diminimalkan oleh framework itu sendiri."

### Security Testing (Penetration Testing)

> **"Security testing menyeluruh membutuhkan tools dan keahlian spesifik yang berada di luar fokus capstone ini. Yang saya lakukan adalah audit keamanan dasar meliputi:**
>
> - **SQL Injection:** seluruh query menggunakan Eloquent parameter binding atau explicit ? binding — aman
> - **Mass Assignment:** semua model memiliki properti $fillable — aman
> - **Autentikasi:** route protection via middleware `auth:web` dan `role:cashier,admin`
> - **CSRF:** Laravel menyediakan CSRF protection secara default
>
> Audit ini saya dokumentasikan dan bisa dipertanggungjawabkan. Untuk penetration testing menyeluruh, itu bisa menjadi pekerjaan lanjutan."

### Regression Testing

> **"Regression testing sebenarnya sudah tercakup secara otomatis oleh rangkaian PHPUnit yang saya miliki. Setiap kali saya melakukan perubahan kode, saya menjalankan seluruh test suite — 146 assertions di 33 file test. Jika ada perubahan yang memecah fungsionalitas yang sudah ada, test akan langsung gagal.**
>
> Dengan kata lain, test suite saya sudah berfungsi sebagai regression test suite."

### Alpha / Beta Testing

> **"Alpha dan beta testing membutuhkan rilis sistem ke lingkungan pengguna sebenarnya untuk periode tertentu. Untuk tugas akhir dengan jadwal yang ketat, ini sulit dilakukan.**
>
> Sebagai gantinya, simulasi black box yang saya lakukan mencakup 15 skenario yang mewakili seluruh fungsionalitas utama sistem. Meskipun tidak melibatkan user nyata, cakupan skenarionya sudah mencakup semua fitur kritis."

---

## 3. Ringkasan — Siap untuk Sidang

```
┌──────────────────────────────────────────────────────────────────┐
│  STRATEGI PENGUJIAN — SISTEM MANAJEMEN INVENTORI                  │
│                                                                   │
│  DIPILIH:  │ ALASAN                           │ COCOK UNTUK      │
│  ──────────┼──────────────────────────────────┼────────────────── │
│  Black Box │ Verifikasi fitur dari sisi user  │ Admin non-teknis  │
│  White Box │ Validasi algoritma + logika       │ Developer         │
│  Gray Box  │ Verifikasi integrasi lintas modul │ End-to-end flow   │
│                                                                   │
│  TIDAK DIPILIH:  │ PENGGANTI / ALASAN                             │
│  ────────────────┼────────────────────────────────────────────     │
│  Performance     │ Beban cafe ringan (<200 trx/hari)              │
│  Usability       │ Filament sudah terstandarisasi UI              │
│  Security        │ Audit manual SQL injection + mass assignment   │
│  Regression      │ PHPUnit test suite sudah mencakup              │
│  Alpha/Beta      │ Simulasi black box 15 skenario                 │
│                                                                   │
│  HASIL: 20 skenario berhasil (100%)                               │
│  - 15 Black Box (Admin UI)                                        │
│  - 5 White Box (PHPUnit Unit)                                     │
│  - (Gray Box terintegrasi di Feature Test)                        │
└──────────────────────────────────────────────────────────────────┘
```
