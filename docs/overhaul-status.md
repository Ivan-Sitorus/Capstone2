# Status Overhaul minePOS

Dokumen ini merangkum sampai mana pekerjaan overhaul di branch `MinePOS`, apa yang berubah, apa yang belum, dan hal yang perlu diperhatikan.

- Branch kerja: `MinePOS` (semua commit sudah di-push ke GitHub).
- Titik aman: tag `v0.9-pre-overhaul` dan branch `backup/pre-overhaul`.
- Hotfix sisi pelanggan di-commit lebih dulu ke `integrasi-datamining-frontend` (commit `c6cedb4`).

## Sudah selesai

**FASE A (fungsional):**
- Guard admin: tidak bisa menonaktifkan/menghapus diri sendiri atau admin aktif terakhir.
- Flicker kasir diperbaiki: layout persisten, pending-count dari shared props, tanpa side-effect saat mount, tanpa polling 1 detik.
- Responsif kasir: sidebar jadi off-canvas drawer dan cart jadi bottom sheet (snap 50 sampai 90 persen) di layar ponsel.
- Background pelanggan: wallpaper raster diganti SVG motif bunga responsif.
- Halaman login kasir: kembali ke versi kartu responsif dari branch `dcd-integrasi`.
- Bug QR `?table=1` (500 SQLSTATE 22P02) diperbaiki menjadi 404.

**FASE B (data/schema):**
- Tabel `notifications` dihapus.
- `order_code` aman konkurensi dengan retry terarah (maks 5 percobaan).
- File sampah (1190/1208/1210), dead code, dan relasi rusak `User::orders()` dihapus.
- Em dash diganti di sumber aplikasi (data mining dikecualikan).
- Migration dibersihkan dan `app_settings` di-rename menjadi `receipt_and_whatsapp_settings`.
- Seeder memakai konstanta di atas file; `config/seeding.php` dihapus.
- Semua file migration dinamai ulang `2026_06_28_000001..000017` berurutan.

**FASE B2 (edge case konkurensi):**
- Status menu dicek ulang di dalam transaksi (kasir dan pelanggan).
- Stok kurang -> 409, bukan 500.
- Transisi status memakai conditional update (tidak bisa diproses ganda).
- QRIS upload menyetel `qris_status` dan menegakkan batas resubmit.
- Deref `menu?->name` diamankan, parameter tanggal divalidasi.

**FASE C/D/E/F:**
- Reusable: `App\Support\Formatter::rupiah`, `resources/js/helpers.js` memakai Intl, komponen `Money`.
- TypeScript dihapus dari frontend (shadcn `.tsx` -> `.jsx`).
- Rebranding `POSMine` -> `minePOS`; database `pos_cafe` -> `minepos`, `pos_cafe_testing` -> `minepos_testing`.
- Tooling: ESLint + Prettier + Pint.
- Inertia dinaikkan ke v3 (JS dan adapter server), polling diganti `usePoll`.
- Keamanan backend: kepemilikan pesanan lewat sesi (IDOR ditutup), CSRF kasir diaktifkan, XSS template di-escape, props kasir diminimalkan.

**Verifikasi:** build Vite hijau, PHPUnit 107 test lolos, lint 0 error, uji runtime browser (login, navigasi, drawer, sheet, background) lolos.

## Belum selesai / ditunda

- **C1** refactor besar React (struktur per fitur, penghapusan inline style besar-besaran). Belum dikerjakan.
- **Ditunda atas permintaan user:** E1 observability/monitoring, B1 guard hapus-menghapus, B6 pisah pcs/buah, dan seluruh item data mining (termasuk tooltip, benchmark ukuran, SQL vs JSONB).
- **Rename database produksi (Neon/Vercel)** harus manual: ubah nama DB dan set `DB_DATABASE=minepos`.

## Keterbatasan dan penyimpangan dari plan

- Layout persisten hanya untuk kasir; flicker sisi pelanggan belum ditangani.
- Format rupiah belum seragam sepenuhnya: sebagian admin memakai `Rp100.000` (tanpa spasi) karena test mengunci format itu, sisi pelanggan memakai `Rp 45.000`.
- Escape drawer perlu handler eksplisit.
- `usePoll` `keepAlive:false` hanya men-throttle saat tab tidak aktif, bukan berhenti total.
- Hanya 1 dari 6 kandidat `nullable` migration yang aman dihapus.
- FK `cashier_histories.user_id` belum diperbaiki (menyatu dengan B1 yang ditunda).
- Nama sequence/index Postgres lama masih `app_settings_*`.
- Seeder belum dijalankan ulang; DB dev masih berisi email lama `@posmine.com`, sehingga login dengan `@minepos.com` gagal sampai `php artisan db:seed` dijalankan.
- File raster lama (`wallpaper-menu.jpg`, `wallpaper-identitas.png`) masih ada di disk (tidak direferensikan).
- Aset `qris/qris-minepos.png` (dari seeder) belum ada; yang nyata `public/images/logo-qris.png`.

## Cara menjalankan dan menguji

```bash
# build frontend
npm run build

# lint
npm run lint

# test backend (wajib lewat Docker)
NET=$(docker network ls --format '{{.Name}}' | grep -i capstone | head -1)
docker run --rm --network "$NET" -v "$PWD":/var/www/html -w /var/www/html posmine-pos:latest php artisan test
```

- Database dev: `minepos`; database test: `minepos_testing` (container `capstone2-pgsql-1`).
- Backup dan restore: `docs/deployment/database-backup-restore.md`. Backup pre-overhaul ada di `storage/backups/pos_cafe-*.dump`.

## Catatan saat kembali ke `integrasi-datamining-frontend`

- Database dev sudah berada di skema overhaul (`app_settings` sudah di-rename, `notifications` dihapus). Versi lama mengharapkan skema `pos_cafe` yang lama.
- Untuk menjalankan versi lama, restore backup pre-overhaul menjadi database `pos_cafe` lalu set `DB_DATABASE=pos_cafe`:
  ```bash
  docker exec capstone2-pgsql-1 createdb -U postgres pos_cafe
  docker exec -i capstone2-pgsql-1 pg_restore -U postgres -d pos_cafe --no-owner < storage/backups/pos_cafe-<TS>.dump
  ```
- Perubahan pada `.env` (gitignored) tetap berlaku lintas branch, jadi periksa `DB_DATABASE` sesuai branch yang dijalankan.
