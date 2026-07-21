# Alur Demo Sistem Manajemen Inventori — Bimbingan Dosen

> **Target Audiens**: Business-oriented dosen pembimbing
> **Durasi Demo**: ~15-20 menit
> **Flow**: Login → Master Data → Transaksi → Monitoring

---

## 1. Persiapan Sebelum Demo

### 1.1 Pastikan Container Nyala
```bash
cd ~/projects/Capstone2
docker compose --profile prod up -d
```

### 1.2 Pastikan Data Terisi
```bash
docker compose exec laravel.test php artisan migrate:fresh --seed --force
```

### 1.3 Login
- **URL**: `http://localhost:8080/admin/login`
- **Akun**: `admin@w9cafe.com` / `password`

---

## 2. Alur Demo — Sisi Admin (Panel Filament)

### 2.1 Dashboard (30 detik)
- Tampilkan ringkasan: total bahan baku, total batch stok, jumlah menu
- Jelaskan: "Dashboard ini memberikan gambaran cepat kondisi stok hari ini"

### 2.2 Manajemen Kategori (30 detik)
1. Buka **Menu → Kategori**
2. Tunjukkan daftar kategori yang sudah ada
3. Jelaskan: "Kategori digunakan untuk mengelompokkan menu agar lebih mudah dicari saat transaksi"

### 2.3 Manajemen Menu & Resep (3 menit) ⭐
1. Buka **Menu → Menu**
2. Tunjukkan daftar menu lengkap dengan kolom:
   - Nama, Kategori, Harga, **Sisa Jual** (jelaskan tooltip-nya), Tersedia
3. Klik tambah menu baru
4. Tunjukkan form:
   - Nama menu, kategori, harga
   - **Bagian Resep** — pilih bahan baku + jumlah pemakaian per porsi
5. Jelaskan: "Setiap menu WAJIB memiliki resep. Tanpa resep, stok tidak bisa terdeduksi otomatis"
6. Tunjukkan kolom **Sisa Jual** yang otomatis terhitung dari stok bahan baku
7. Selipkan: "Rumus Sisa Jual ini ada di Bab III skripsi — `MIN(floor(s(i)/u(i)))`"

### 2.4 Manajemen Bahan Baku (2 menit)
1. Buka **Inventori → Bahan Baku**
2. Tunjukkan daftar bahan baku:
   - Nama, unit, mode batch (FEFO/FIFO), total stok
3. Klik salah satu bahan baku → kelola batch
4. Tunjukkan form tambah batch:
   - Input jumlah, tanggal kedaluwarsa (wajib untuk FEFO, opsional untuk FIFO), harga satuan
   - **Toggle "allow_expired_usage"** — jelaskan fungsinya: "Kalau batch kedaluwarsa tapi masih layak pakai, toggle ini bisa diaktifkan agar batch tetap terpakai oleh sistem"

### 2.5 Penyesuaian Stok (2 menit) ⭐
1. Buka **Inventori → Penyesuaian Stok**
2. Tunjukkan daftar penyesuaian yang sudah ada
3. Klik tambah penyesuaian baru:
   - Pilih jenis (Bahan Baku), pilih bahan, tipe (increase/decrease), kategori (Kedaluwarsa, Rusak, dll), jumlah, catatan
4. Jelaskan: "Penyesuaian stok digunakan untuk koreksi stok manual — misalnya ada bahan yang rusak, tumpah, atau stok opname"
5. Tunjukkan tombol **Detail** — lihat info lengkap
6. Tunjukkan tombol **Batalkan** — "Penyesuaian bisa dibatalkan, stok akan kembali ke kondisi awal"

### 2.6 Riwayat Pemakaian Bahan Baku (30 detik)
1. Buka **Inventori → Pemakaian Bahan Harian**
2. Tunjukkan data pemakaian per tanggal
3. Jelaskan: "Ini mencatat otomatis setiap kali ada transaksi penjualan — admin bisa lihat tren pemakaian"

---

## 3. Alur Demo — Sisi Transaksi (Kasir)

### 3.1 Buka Halaman Kasir (30 detik)
1. Buka tab baru → `http://localhost:8080/kasir/login`
2. Login: `kasir@w9cafe.com` / `password`

### 3.2 Pesanan Baru (3 menit) ⭐
1. Tunjukkan halaman **Pesanan Baru**
2. Kategori di kiri, menu card di tengah, keranjang di kanan
3. Klik beberapa menu → tambah ke keranjang
4. Tunjukkan: "Stok akan otomatis terdeduksi saat pesanan diproses"
5. Klik **Bayar** → pilih metode pembayaran (Cash/QRIS)
6. Jelaskan: "Setelah pembayaran, sistem otomatis mengurangi stok bahan baku sesuai resep menu yang dipesan"

### 3.3 Pesanan Aktif & Riwayat (30 detik)
1. Buka **Pesanan Aktif** — tunjukkan status pesanan
2. Buka **Riwayat Pesanan** — tunjukkan histori

---

## 4. Poin-Poin Kunci yang Harus Ditekankan

| Poin | Di mana | Kenapa Penting |
|:-----|:--------|:---------------|
| **Sisa Jual** | Menu list | Menghubungkan stok dengan penjualan — nilai bisnis |
| **FEFO vs FIFO** | Bahan Baku → batch_mode | Mencegah pemborosan bahan baku — efisiensi biaya |
| **Deduksi otomatis** | Saat transaksi | Tidak perlu catat manual — hemat waktu, akurat |
| **Allow expired usage** | Batch → toggle | Fleksibilitas untuk bahan yang masih layak |
| **Pembatalan penyesuaian** | Penyesuaian Stok → Batalkan | Audit trail lengkap — keamanan data |
| **Immutable stock movement** | (dalam kode) | Riwayat tidak bisa dimanipulasi — kepercayaan |

---

## 5. Flow Presentasi (Urutan Bicara)

```
1. "Selamat siang, Pak. Saya akan mendemonstrasikan sistem manajemen inventori
   yang saya bangun untuk W9 Cafe."
   
2. "Secara garis besar, sistem ini memungkinkan admin mengelola stok bahan baku
   secara digital — dari penerimaan batch, pencatatan resep, hingga deduksi
   stok otomatis saat terjadi transaksi penjualan."
   
3. "Saya akan mulai dari panel admin dulu..." → Mulai demo 2.1

4. "...selanjutnya saya akan tunjukkan bagaimana sistem merespon ketika ada
   transaksi penjualan." → Pindah ke demo 3.2

5. "Jadi intinya, sistem ini mengotomatiskan proses yang tadinya manual:
   mencatat stok di buku, menghitung pemakaian, dan melacak riwayat."
   
6. "Saya persilakan jika ada yang ingin ditanyakan."
```
