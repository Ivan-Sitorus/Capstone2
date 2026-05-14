# Dokumentasi Implementasi POS Cafe W9 STIE Totalwin

## Tentang Dokumentasi Ini

Dokumentasi ini menjelaskan implementasi teknis sistem Point of Sale (POS) W9 Cafe STIE Totalwin Semarang secara menyeluruh. Setiap dokumen membahas satu aspek spesifik: arsitektur, basis data, alur autentikasi, antarmuka, hingga deployment.

Dokumentasi ditulis dalam bahasa Indonesia dan ditargetkan untuk pengembang yang akan meneruskan atau memelihara sistem. Seluruh contoh kode menggunakan stack aktual proyek: Laravel 13, React 18, Inertia.js v2, PostgreSQL 18, dan Tailwind CSS v4.

### Konvensi Penulisan

| Elemen | Makna |
|---|---|
| `Kode` | Nama file, kelas, fungsi, perintah terminal, atau kode program |
| **Tebal** | Istilah penting atau penekanan |
| `⚠️` | Peringatan / gotcha yang perlu diperhatikan |
| `💡` | Tips atau best practice |
| Blok kode | Contoh implementasi nyata dari codebase |

---

## Daftar Isi

### Bagian 1 — Arsitektur & Autentikasi

| No | Dokumen | Deskripsi |
|---|---|---|
| 1 | [README.md](./README.md) | Pengantar, daftar isi, ringkasan keputusan arsitektur, dan glosarium |
| 2 | [arsitektur.md](./arsitektur.md) | Arsitektur umum: tech stack, unified UI, multi-login, session security |
| 3 | [auth-multi-login.md](./auth-multi-login.md) | Detail autentikasi: Device Auth Controller, edge cases, siklus login/logout |
| 4 | [sesi-kerja.md](./sesi-kerja.md) | Sesi kerja kasir & kitchen: WorkSession, middleware, popup warning |

### Bagian 2 — Modul Transaksi

| No | Dokumen | Deskripsi |
|---|---|---|
| 5 | transaksi-kasir.md | Alur POS kasir: grid menu, keranjang, konfirmasi pembayaran, status pesanan |
| 6 | transaksi-pelanggan.md | Alur pelanggan: menu, cart offline, QR scan, pembayaran mandiri |
| 7 | transaksi-pembayaran.md | Metode pembayaran: tunai, QRIS, struk digital, verifikasi upload |

### Bagian 3 — Tampilan Antarmuka

| No | Dokumen | Deskripsi |
|---|---|---|
| 8 | [ui-tailwind.md](./ui-tailwind.md) | Migrasi Bootstrap 5 ke Tailwind CSS v4 + shadcn/ui: strategi, CSS variables, komponen |
| 9 | [kitchen.md](./kitchen.md) | Kitchen Display System (KDS): tampilan antrean, update status, laporan stok habis |
| 10 | [receipt-qris.md](./receipt-qris.md) | Struk digital: format, tampilan mobile, perbaikan upload bukti QRIS |

### Bagian 4 — Admin & Keuangan

| No | Dokumen | Deskripsi |
|---|---|---|
| 11 | [cash-flow.md](./cash-flow.md) | Arus kas: pencatatan pemasukan non-penjualan, pengeluaran, piutang |
| 12 | [laporan-keuangan.md](./laporan-keuangan.md) | Laporan keuangan: laporan harian, laporan shift, histori transaksi |
| 13 | filament-admin.md | Panel admin Filament: resource CRUD, manajemen menu, inventori |
| 14 | deployment.md | Docker, NGINX, Supervisor, environment variables, CI/CD |

---

## Ringkasan Keputusan Arsitektur

| Keputusan | Pilihan | Alasan |
|---|---|---|
| **Bahasa back-end** | PHP 8.5.6 / Laravel 13.8 | Ekosistem matang, tim familiar, ORM Eloquent produktif |
| **Front-end framework** | React 18 + Inertia.js v2 | SPA-like UX tanpa perlu REST API terpisah; satu codebase Laravel |
| **CSS framework** | Tailwind CSS v4 + shadcn/ui | Utility-first untuk produktivitas; shadcn/ui untuk komponen aksesibel |
| **Admin panel** | Filament 5 (native) | CRUD generator cepat, native Laravel, auth guard terpisah |
| **Database** | PostgreSQL 18 | ACID compliance, JSONB support, cocok untuk inventory & laporan |
| **Auth operasional** | Device Identity + Staff Context | Multi-staff per device tanpa auth user global; aman untuk shared workstation |
| **Auth admin** | Laravel Sanctum (standard guard) | Filament panel tetap pakai auth Laravel bawaan |
| **State cart pelanggan** | Zustand + IndexedDB | Offline-capable, sinkronisasi otomatis saat online |
| **Deployment** | Docker (NGINX + PHP-FPM + Supervisor) | Satu container produksi, mudah di-deploy di VPS manapun |

### Arsitektur Multi-Login (Keputusan Khusus)

Sistem operasional (kasir, dapur) **tidak menggunakan `auth()->user()` global**. Sebagai gantinya, digunakan konsep **Device Identity + Staff Context**:

```
Device → device_sessions (UUID device) → active_staff_sessions (staff login)
```

Satu perangkat bisa dipakai bergantian oleh beberapa staff. Setiap staff login sebagai sesi terpisah. Logout hanya menghapus sesi staff yang bersangkutan, bukan seluruh sesi perangkat. Ini memungkinkan skenario:

- Pagi: staff A login, bekerja, lalu logout individu
- Siang: staff B login di perangkat yang sama tanpa perlu menutup browser

Detail lengkap di [auth-multi-login.md](./auth-multi-login.md).

---

## Glosarium

| Istilah | Definisi |
|---|---|
| **Device Identity** | Identitas perangkat yang dikenali melalui device UUID unik, disimpan di `device_sessions` |
| **Staff Context** | Sesi kerja staff tertentu dalam satu device; direpresentasikan oleh `active_staff_sessions` |
| **Actor** | Staff yang sedang aktif melakukan aksi; diidentifikasi via `actor_staff_session_id` |
| **Inertia.js** | Bridge antara Laravel (back-end) dan React (front-end) tanpa REST API |
| **Filament** | Admin panel framework native Laravel; digunakan untuk CRUD back-office |
| **shadcn/ui** | Koleksi komponen React yang bisa disalin langsung ke proyek, bukan npm package |
| **Zustand** | State management library ringan untuk React; digunakan untuk keranjang belanja |
| **IndexedDB** | Database browser untuk penyimpanan offline; digunakan untuk persistensi cart |
| **WorkSession** | Jadwal kerja staff (hari, jam mulai, jam selesai); digunakan untuk popup peringatan |
| **QRIS** | Quick Response Code Indonesian Standard; metode pembayaran QR yang didukung |
| **PWA** | Progressive Web App; aplikasi web yang bisa di-install dan bekerja offline |
| **FEFO** | First Expired First Out; strategi pengelolaan stok berdasarkan tanggal kedaluwarsa |
| **NIM** | Nomor Induk Mahasiswa; digunakan sebagai password login pelanggan |
| **KTM** | Kartu Tanda Mahasiswa; harus ditunjukkan ke kasir untuk verifikasi akun |
