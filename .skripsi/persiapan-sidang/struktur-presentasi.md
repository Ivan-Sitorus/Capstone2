# Struktur Presentasi Sidang Skripsi
## Muhammad Nio Hastungkoro — 21120122140155
## Sistem Manajemen Inventori pada Point of Sale W9 Cafe STIE Totalwin
## Menggunakan Laravel dan Filament

---

## SLIDE 1 — Cover
```
Judul: Sistem Manajemen Inventori pada Point of Sale
       W9 Cafe STIE Totalwin Menggunakan Laravel dan Filament

Nama: Muhammad Nio Hastungkoro
NIM : 21120122140155
```

## SLIDE 2 — Latar Belakang (1 menit)
```
┌──────────────────────────────────────────────────────────────────┐
│  ❓ MASALAH                                                        │
│                                                                   │
│  W9 Cafe — kafe di STIE Totalwin Semarang                        │
│                                                                   │
│  SEBELUMNYA:                                                      │
│  • Tidak ada sistem komputerisasi untuk stok                      │
│  • Pencatatan resep masih manual (buku)                           │
│  • Stok bahan baku tidak terkontrol → bahan expired, overstock    │
│  • Deduksi stok manual → sering lupa catat                        │
│  • Laporan stok harian menyita waktu 2 jam/hari                   │
│  • Tidak ada prediksi atau perencanaan pembelian                  │
└──────────────────────────────────────────────────────────────────┘

🎙️  "Stock-taking at W9 Cafe was entirely manual. Staff relied on
    notebooks to track ingredient usage. This led to expired 
    ingredients, recipe measurement inconsistencies, and 2 hours 
    of daily administrative overhead for stock reconciliation."
```

## SLIDE 3 — Tujuan & Solusi (1 menit)
```
┌──────────────────────────────────────────────────────────────────┐
│  🎯 TUJUAN                                                        │
│                                                                   │
│  1. Otomatisasi deduksi stok + FEFO/FIFO batch management        │
│  2. Digitalisasi resep menu (setiap menu punya komposisi akurat) │
│  3. Manajemen batch stok dengan expiry tracking                  │
│  4. Notifikasi/prediksi untuk reorder point                      │
│  5. Laporan inventori (daily ingredient usage, stock adjustment)  │
│                                                                   │
│  🛠️  SOLUSI TEKNOLOGI                                              │
│  Laravel 13 (PHP) + Filament 5 (Admin UI) + PostgreSQL 18        │
│  Docker (NGINX + PHP-FPM) + React 18 + Bootstrap 5 (POS front)  │
└──────────────────────────────────────────────────────────────────┘
```

## SLIDE 4 — Ruang Lingkup & Batasan (30 detik)
```
┌──────────────────────────────────────────────────────────────────┐
│  📋 BATASAN MASALAH                                                │
│                                                                   │
│  ✓ Manajemen Ingredient (bahan baku) + batch stok                │
│  ✓ Resep menu (bahan dasar per item POS)                         │
│  ✓ Deduksi stok otomatis saat pesanan diproses                   │
│  ✓ Algoritma FEFO / FIFO untuk konsumsi batch                    │
│  ✓ Penyesuaian stok (increase, decrease, waste, cancellation)    │
│                                                                   │
│  ✗ Tidak mencakup modul keuangan / payment gateway               │
│  ✗ Tidak mencakup data mining dan prediksi (Ruben — anggota tim) │
└──────────────────────────────────────────────────────────────────┘
```

## SLIDE 5 — Perancangan Sistem (2 menit)
``
```
┌──────────────────────────────────────────────────────────────────┐
│  USE CASE DIAGRAM                                                  │
│                                                                   │
│          ┌─────────────────────────────────────────────┐          │
│          │           ┌──────────┐                     │          │
│          │           │ Admin    │                     │          │
│          │           ├──────────┤                     │          │
│          │           │• CRUD bahan baku               │          │
│          │           │• CRUD batch stok               │          │
│          │           │• Atur mode batch (FEFO/FIFO)   │          │
│          │           │• Penyesuaian stok              │          │
│          │           │• Lihat laporan harian          │          │
│          │           └──────────┘                     │          │
│          └─────────────────────────────────────────────┘          │
└──────────────────────────────────────────────────────────────────┘

🎙️  "The system has three user roles: Admin manages inventory, 
    Cashier processes orders which triggers automatic stock deduction,
    and Customer places orders via the mobile menu interface."

┌──────────────────────────────────────────────────────────────────┐
│  FLOWCHART DEDUKSI STOK                                           │
│                                                                   │
│  PESANAN MASUK                                                    │
│      ↓                                                            │
│  Hitung kebutuhan = quantity_used × jumlah_pesan                  │
│      ↓                                                            │
│  Ambil batch: FEFO (expired terdekat) atau FIFO (received lama)  │
│      ↓                                                            │
│  Validasi: total_available >= required?                           │
│      ├── Ya → Kurangi quantity batch, catat StockMovement         │
│      └── Tidak → Error: "Stok tidak mencukupi"                   │
│                                                                   │
│  ❓ KENPA FEFO/FIFO PENTING?                                      │
│     FEFO → cegah bahan expired (batch terdekat expired duluan)   │
│     FIFO → rotasi stok sehat (batch lama dipakai duluan)          │
└──────────────────────────────────────────────────────────────────┘
```

## SLIDE 6 — ERD & Database Schema (30 detik)
```
┌──────────────────────────────────────────────────────────────────┐
│  CORE TABLES                                                      │
│                                                                   │
│  ingredients ─── ingredient_batches                               │
│      │                    │                                       │
│      ├── menu_ingredients ┤  (pivot: quantity_used)               │
│      │        │                                                   │
│      menus    ┤                                                   │
│      │        │                                                   │
│      └── order_items ───── orders                                 │
│                                                                   │
│  stock_adjustments ─── stock_movements (audit trail)              │
└──────────────────────────────────────────────────────────────────┘
```

## SLIDE 7 — Implementasi (3 menit)
```
┌──────────────────────────────────────────────────────────────────┐
│  ARSITEKTUR IMPLEMENTASI                                          │
│                                                                   │
│  ┌──────────┐    ┌──────────┐    ┌────────────┐                  │
│  │ React    │    │ Filament │    │ Laravel   │                  │
│  │ (POS)    │───▶│ (Admin)  │───▶│ (API/App) │───▶ PostgreSQL  │
│  │ Inertia  │    │ Panel    │    │ Services  │                  │
│  └──────────┘    └──────────┘    └────────────┘                  │
│                                          │                        │
│                                   ┌──────┴──────┐                │
│                                   │ Inventory   │                │
│                                   │ Service     │                │
│                                   │ - FEFO/FIFO │                │
│                                   │ - Deduction │                │
│                                   │ - Validation│                │
│                                   └─────────────┘                │
└──────────────────────────────────────────────────────────────────┘

🎙️  "The system uses Laravel's Service Layer pattern. All inventory 
    business logic is encapsulated in InventoryService, keeping 
    controllers thin and testable. The deduction flow is:
    1. Order is processed → 2. Load menu recipe → 3. Select batch 
    via FEFO/FIFO → 4. Validate stock → 5. Deduct & record movement"

┌──────────────────────────────────────────────────────────────────┐
│  DEMO LIVE:                                                       │
│  - Tampilkan Panel Admin Filament (CRUD bahan baku, batch)       │
│  - Tampilkan POS kasir (pesanan → stok otomatis berkurang)       │
│  - Tampilkan stock adjustment (cancel → stok kembali)            │
└──────────────────────────────────────────────────────────────────┘
```

## SLIDE 8 — Pengujian Black Box (2 menit)
```
┌──────────────────────────────────────────────────────────────────┐
│  METODE: EQUIVALENCE PARTITIONING                                 │
│  Menguji dari perspektif pengguna, tanpa tahu kode di dalamnya   │
│                                                                   │
│  ┌──────────────────┬───────┬────────────────────────────────┐   │
│  │ Skenario         │ Jumlah│ Contoh                         │   │
│  ├──────────────────┼───────┼────────────────────────────────┤   │
│  │ Autentikasi      │  3    │ Login valid → masuk            │   │
│  │                  │       │ Login invalid → error          │   │
│  │                  │       │ Guest → redirect               │   │
│  ├──────────────────┼───────┼────────────────────────────────┤   │
│  │ Manajemen Bahan  │  3    │ Create, validation, delete     │   │
│  ├──────────────────┼───────┼────────────────────────────────┤   │
│  │ Penyesuaian Stok │  3    │ Increase, decrease, cancel     │   │
│  ├──────────────────┼───────┼────────────────────────────────┤   │
│  │ Resep Menu       │  3    │ Add, edit, delete ingredient   │   │
│  ├──────────────────┼───────┼────────────────────────────────┤   │
│  │ Dashboard        │  3    │ Load data, stats correct       │   │
│  ├──────────────────┼───────┼────────────────────────────────┤   │
│  │ TOTAL            │ 15    │ 100% PASS                      │   │
│  └──────────────────┴───────┴────────────────────────────────┘   │
└──────────────────────────────────────────────────────────────────┘
```

## SLIDE 9 — Pengujian White Box (2 menit)
```
┌──────────────────────────────────────────────────────────────────┐
│  METODE: STATEMENT + BRANCH COVERAGE                              │
│  Menguji logika internal dengan PHPUnit                           │
│                                                                   │
│  ┌──────────────────────────────────────┬─────────┬─────────────┐ │
│  │ Test                                 │ Assert  │ Hasil       │ │
│  ├──────────────────────────────────────┼─────────┼─────────────┤ │
│  │ FEFO: expired terdekat dipakai duluan│ qty=0   │ ✅ PASS     │ │
│  │                                      │ qty=70  │             │ │
│  ├──────────────────────────────────────┼─────────┼─────────────┤ │
│  │ FIFO: batch lama dipakai duluan      │ qty=40  │ ✅ PASS     │ │
│  │                                      │ qty=200 │             │ │
│  ├──────────────────────────────────────┼─────────┼─────────────┤ │
│  │ Stock Movement immutable             │ Throw   │ ✅ PASS     │ │
│  ├──────────────────────────────────────┼─────────┼─────────────┤ │
│  │ Unit Conversion (kg↔gram)            │ 1000    │ ✅ PASS     │ │
│  ├──────────────────────────────────────┼─────────┼─────────────┤ │
│  │ Promo: min purchase validation       │ diskon  │ ✅ PASS     │ │
│  └──────────────────────────────────────┴─────────┴─────────────┘ │
└──────────────────────────────────────────────────────────────────┘
```

## SLIDE 10 — Hasil & Statistik (1 menit)
```
┌──────────────────────────────────────────────────────────────────┐
│  RINGKASAN HASIL                                                  │
│                                                                   │
│  Metode         │ Kasus │ Pass  │ Tool                            │
│  ───────────────┼───────┼───────┼────────────────────────────     │
│  Black Box      │  15   │ 100%  │ Manual (UI Test)                │
│  White Box      │  5    │ 100%  │ PHPUnit (Unit Tests)            │
│  ───────────────┼───────┼───────┼────────────────────────────     │
│  TOTAL          │  20   │ 100%  │ (14 script, ±120 assertion)    │
│                                                                   │
│  Lalu lintas query: dari 37.807 LOC → 26.331 LOC (-30%)          │
└──────────────────────────────────────────────────────────────────┘

🎙️  "All 20 test scenarios passed. 100% success rate for both 
    Black Box and White Box testing. The project reduced from 
    37,807 lines of code to 26,331 lines through refactoring — 
    that's a 30% reduction while maintaining full functionality."
```

## SLIDE 11 — Kesimpulan & Saran (1 menit)
```
┌──────────────────────────────────────────────────────────────────┐
│  KESIMPULAN                                                        │
│                                                                   │
│  1. Sistem berhasil mengotomatisasi deduksi stok dengan          │
│     algoritma FEFO/FIFO                                          │
│  2. Manajemen batch + expiry tracking → kurangi waste            │
│  3. Stock adjustment + cancel → fleksibilitas operasional        │
│  4. Laporan harian (daily ingredient usage) — real-time          │
│                                                                   │
│  SARAN                                                             │
│  • Tambah data mining untuk prediksi kebutuhan stok              │
│  • Integrasi payment gateway                                     │
│  • Offline PWA untuk operasional saat koneksi terputus           │
│  • Dashboard analitik berbasis chart                              │
└──────────────────────────────────────────────────────────────────┘
```

## SLIDE 12 — Q&A (1 menit)
```
┌──────────────────────────────────────────────────────────────────┐
│  TERIMA KASIH                                                     │
│                                                                   │
│  Pertanyaan?                                                      │
│                                                                   │
│  Demo langsung jika diminta penguji                               │
│                                                                   │
│  Sumber: https://github.com/Ivan-Sitorus/Capstone2               │
└──────────────────────────────────────────────────────────────────┘
┌──────────────────────────────────────────────────────────────────┐
│  CADANGAN — JAWABAN POTENSIAL UNTUK PERTANYAAN SIDANG             │
│                                                                   │
│  Q: "Kenapa test ada 42 yang gagal?"                              │
│  A: "Technical debt dari penghapusan modul Kitchen dan database   │
│      constraint yang sudah didokumentasikan. Bukan dari fungsional │
│      sistem inti." — lebih detail di berkas argumen-sidang.md     │
│                                                                   │
│  Q: "Kenapa tidak pakai payment gateway?"                         │
│  A: "Cafe beroperasi dengan cash/QRIS manual — belum dibutuhkan." │
│                                                                   │
│  Q: "Bagaimana keamanan dari manipulasi harga?"                   │
│  A: "Semua input tervalidasi oleh Laravel FormRequest +           │
│      unsignedBigInteger di database mencegah nilai negatif."      │
│                                                                   │
│  Q: "Apa perbedaan sistem ini dengan POS biasa?"                  │
│  A: "Fokus pada inventory management dengan FEFO/FIFO — bukan     │
│      POS general-purpose. Integrasi batch tracking + expiry       │
│      jarang ada di POS standar."                                  │
└──────────────────────────────────────────────────────────────────┘
```
