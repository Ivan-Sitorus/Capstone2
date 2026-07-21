# Pembahasan Skripsi & Daftar Pertanyaan — Bimbingan Dosen

> **Profil Dosen**: Business-oriented
> **Fokus**: Alur bisnis, keputusan workflow, justifikasi penulisan

---

## 1. Alur Menjelaskan Skripsi

### 1.1 Bab I — Pendahuluan (2 menit)

**Yang disampaikan:**
1. **Latar Belakang**: W9 Cafe masih catat stok manual (buku/spreadsheet) → rentan salah, lambat, tidak real-time
2. **Masalah**: Sistem POS ada tapi tidak terintegrasi dengan inventori
3. **Solusi**: Bangun sistem manajemen inventori yang terintegrasi dengan POS
4. **Rumusan Masalah**: 
   - Gimana merancang sistemnya?
   - Gimana meminimalkan pemborosan bahan baku via FEFO/FIFO?
   - Gimana mencatat pemakaian otomatis?
   - Gimana hasil pengujiannya?

**Yang perlu ditekankan:**
- "Fokus saya bukan membuat POS, tapi modul INVENTORI yang terintegrasi dengan POS yang sudah ada"
- "Kata kuncinya: otomatisasi deduksi stok berbasis resep"

### 1.2 Bab II — Kajian Pustaka (1 menit)

**Yang disampaikan:**
1. Teori yang langsung relevan: Manajemen Inventori, FEFO/FIFO, Laravel, Filament, PostgreSQL
2. **Tidak ada** teori teknis berlebihan (MVC, Eloquent ORM, Service Layer, Observer, Pessimistic Locking)

**Jika ditanya kenapa teori tertentu tidak ada:**
> "Saya merujuk pada tiga skripsi sebelumnya dari dosen pembimbing yang sama — semuanya hanya mencantumkan teori yang langsung relevan dengan kontribusi penelitian. Saya mengikuti pola yang sama."

### 1.3 Bab III — Perancangan Sistem (3 menit) ⭐

**Yang disampaikan:**
1. **Use Case**: Admin mengelola inventori, Sistem otomatis deduksi stok
2. **Flowchart**: Alur deduksi — dari pesanan masuk → cek idempotensi → loop menu → loop bahan baku → urut batch FEFO/FIFO → kurangi stok → catat pergerakan
3. **Activity Diagram**: 4 diagram — Kelola Bahan Baku, Batch, Penyesuaian Stok, Resep Menu
4. **Rumus Sisa Jual**: `MIN(floor(s(i)/u(i)))`
5. **Arsitektur**: Browser → Laravel → PostgreSQL (plus Sistem Transaksi sebagai eksternal)
6. **Lingkungan Pengembangan**: Spesifikasi laptop + software

**Jika ditanya tentang arsitektur:**
> "Ini arsitektur monolitik sederhana — admin akses via Filament, kasir via Inertia React. Semua data tersimpan di satu database PostgreSQL. Saya pisahkan Sistem Transaksi (modul milik teman) dan Sistem Inventori (bagian saya) untuk memperjelas scope skripsi."

### 1.4 Bab IV — Implementasi & Pengujian (2 menit)

**Yang disampaikan:**
1. **Tampilan Sistem**: Screenshot halaman-halaman utama
2. **Pengujian Black Box**: 12 skenario — semua berhasil ✅
3. **Pengujian White Box**: 5 skenario — FIFO, FEFO, deduksi resep, penyesuaian stok, pembatalan
4. **Tidak ada pengujian performa** — jelaskan kenapa

### 1.5 Bab V — Penutup (30 detik)

**Yang disampaikan:**
1. Sistem berhasil: deduksi otomatis, FEFO/FIFO, pencatatan permanen
2. Saran: mobile app, notifikasi stok, barcode

---

## 2. Daftar Pertanyaan yang Mungkin Ditanyakan

### 2.1 Pertanyaan Bisnis & Alur Sistem

| No | Pertanyaan | Jawaban |
|:--:|:-----------|:--------|
| 1 | **"Kenapa menu harus punya resep? Kenapa nggak bisa jual produk jadi tanpa resep?"** | "Karena inti sistem ini adalah deduksi stok otomatis berbasis resep. Kalau menu tidak punya resep, sistem tidak tahu bahan apa yang harus dikurangi. Semua menu di W9 Cafe memiliki resep — mulai dari kopi hingga makanan berat. Untuk produk non-resep (misal minuman kemasan), saya pakai stok satuan yang dikelola via penyesuaian stok manual." |
| 2 | **"Apa bedanya FEFO dan FIFO? Kapan pake yang mana?"** | "FEFO memprioritaskan batch yang paling cepat kedaluwarsa — cocok untuk susu, sayuran, bahan segar. FIFO memprioritaskan batch yang diterima lebih awal — cocok untuk kopi bubuk, sirup, bahan kering. Admin bisa pilih mode per bahan baku, tergantung karakteristiknya." |
| 3 | **"Kalau stok habis, apa yang terjadi? Apakah pesanan ditolak?"** | "Sebelum pesanan diproses, sistem mengecek ketersediaan stok via `canFulfillOrder()`. Jika stok tidak cukup, pesanan ditolak. Ini mencegah utang stok (overselling). Di sisi kasir, menu yang stoknya habis akan otomatis terurut ke bawah — jadi kasir bisa lihat mana yang tersedia." |
| 4 | **"Apakah batch yang sudah kedaluwarsa bisa terpakai?"** | "Secara default tidak — sistem otomatis melewati batch yang sudah lewat tanggal kedaluwarsa. Tapi ada toggle `allow_expired_usage` per batch yang bisa diaktifkan admin untuk bahan yang masih layak pakai meski sudah lewat 'best before' (misal sirup botolan)." |
| 5 | **"Penyesuaian stok bisa dibatalkan? Kenapa?"** | "Bisa dibatalkan dengan alasan. Ini penting untuk menjaga audit trail — misalnya admin salah input jumlah, atau ada koreksi. Saat dibatalkan, stok otomatis kembali ke kondisi semula dan semua perubahan tercatat di stock movement." |
| 6 | **"Apa yang membedakan sistem ini dengan catatan stok di Excel?"** | "Dua hal utama: (1) Deduksi stok otomatis saat transaksi — kasir tidak perlu berpikir 'saya harus kurangi stok kopi 30 gram', sistem melakukannya otomatis berdasarkan resep. (2) Riwayat pergerakan stok yang immutable — tidak bisa diubah atau dihapus, sehingga cocok untuk audit." |
| 7 | **"Siapa saja yang menggunakan sistem ini?"** | "Tiga role: Admin (mengelola data inventori via panel Filament), Kasir (memproses pesanan yang memicu deduksi stok), dan Pelanggan (memesan menu — turut memengaruhi stok via transaksi). Tapi dalam skripsi saya fokus ke Admin dan Sistem Inventori." |

### 2.2 Pertanyaan Keputusan Penulisan Skripsi

| No | Pertanyaan | Jawaban |
|:--:|:-----------|:--------|
| 8 | **"Kenapa tidak ada pengujian performa?"** | "Karena skala W9 Cafe kecil — rata-rata 50 transaksi per hari. Pengujian performa untuk sistem dengan beban seperti ini tidak memberikan insight yang berarti. Saya fokus ke black box (validasi fungsionalitas) dan white box (verifikasi algoritma). Ini juga sesuai dengan pola skripsi sebelumnya dari Pak Rinta." |
| 9 | **"Kenapa tidak ada pembahasan MVC, Eloquent, Service Layer di Bab II?"** | "Saya merujuk pada tiga skripsi sebelumnya: Wahyu, Djie, dan Rendy — semuanya dari dosbing yang sama. Mereka hanya mencantumkan teori yang langsung relevan dengan kontribusi. MVC, Eloquent, Service Layer adalah detail implementasi yang lebih tepat dibahas di Bab III sebagai perancangan arsitektur." |
| 10 | **"Kenapa pengujian white box cuma 5 skenario?"** | "5 skenario mewakili 5 fitur inti inventori: deduksi berbasis resep, algoritma FIFO, algoritma FEFO, penyesuaian stok, dan pembatalan penyesuaian. Saya tidak menguji hal-hal yang sudah di-cover oleh framework (seperti validasi form standar Laravel)." |
| 11 | **"Apa kontribusi penelitianmu?"** | "Sistem manajemen inventori yang terintegrasi dengan POS dengan deduksi stok otomatis berbasis resep. Kebaruan dibanding penelitian terdahulu adalah: (1) Setiap menu wajib punya resep — tidak ada dual-path, (2) Mode deduksi FEFO/FIFO bisa dikonfigurasi per bahan baku, (3) Batch kedaluwarsa bisa dikecualikan via toggle, (4) Pencatatan immutable untuk audit trail." |
| 12 | **"Kenapa tidak pakai REST API? Kenapa harus Inertia?"** | "Karena ini monolitik — satu aplikasi Laravel yang handle frontend dan backend. Inertia memungkinkan saya pakai React untuk UI tanpa perlu build API terpisah. Untuk skala kafe kecil, arsitektur monolitik lebih sederhana dan cepat dikembangkan." |
| 13 | **"Apa rencana pengembangan selanjutnya?"** | "Di skripsi saya sebutkan tiga saran: (1) Aplikasi mobile agar akses lebih mudah, (2) Notifikasi stok menipis via WhatsApp, (3) Integrasi barcode untuk mempercepat pencatatan batch." |
| 14 | **"Apakah sistem ini sudah dipakai di W9 Cafe?"** | "Sistem sudah diimplementasikan dan bisa diakses di server W9 Cafe. Namun karena ini proyek Capstone bersama, modul transaksi masih dalam tahap integrasi akhir oleh anggota tim lain." |

### 2.3 Pertanyaan Technical (Jika Muncul)

| No | Pertanyaan | Jawaban Singkat |
|:--:|:-----------|:----------------|
| 15 | **"Berapa total biaya development?"** | "Nol rupiah — semua tools open source (Laravel, Filament, PostgreSQL). Hanya bayar domain & hosting ~Rp 200-300rb/tahun." |
| 16 | **"Database-nya pakai apa?"** | "PostgreSQL 18 — karena support row-level locking (`SELECT ... FOR UPDATE`) untuk pessimistic locking, fitur yang saya butuhkan untuk transaksi konkuren." |
| 17 | **"Berapa lama waktu pengembangan?"** | "Sekitar 4-5 bulan — mulai dari analisis, perancangan, implementasi, hingga pengujian." |
| 18 | **"Kenapa pilih Filament dibanding Laravel Nova?"** | "Filament gratis dan open source. Nova berbayar ($199/site). Selain itu Filament punya ekosistem yang aktif dan dokumentasi yang lengkap." |

---

## 3. Tips Menghadapi Dosen Business-Oriented

1. **Bicara dalam istilah bisnis, bukan teknis**
   - ❌ "Saya pakai `lockForUpdate()` untuk mencegah race condition..."
   - ✅ "Stok tidak akan pernah menjadi minus meskipun dua kasir memproses pesanan di waktu yang sama."

2. **Hubungkan setiap fitur dengan nilai bisnis**
   - FEFO → "Mencegah bahan baku kedaluwarsa terbuang"
   - Sisa Jual → "Kasir langsung tahu menu mana yang bisa dijual"
   - Immutable movement → "Owner bisa audit kapan saja"

3. **Jika ditanya hal teknis, jawab singkat lalu arahkan ke bisnis**
   - "Secara teknis saya pakai pessimistic locking. Tapi yang lebih penting, ini memastikan data stok tetap akurat meskipun ada banyak transaksi."

4. **Jika tidak tahu jawabannya, jangan mengada-ada**
   - "Saya belum memikirkan aspek itu, Pak. Tapi menurut saya..."
   - "Itu masukan yang bagus, Pak. Mungkin bisa jadi saran pengembangan ke depan."
