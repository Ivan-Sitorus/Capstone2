# Backup dan Restore Database minePOS

Prosedur ini wajib dijalankan sebelum setiap perubahan schema (migration, rename tabel/kolom/database) di branch `MinePOS`.

Git hanya mengembalikan kode, bukan data. Backup database inilah yang melindungi data.

## Kapan dipakai

- Sebelum menjalankan migration baru.
- Sebelum rename tabel, kolom, atau nama database.
- Sebelum operasi berisiko lain di panel admin.

## Backup

Lokasi default: `storage/backups/` (diabaikan git, tidak akan ter-commit).

```bash
mkdir -p storage/backups
TS=$(date +%Y%m%d-%H%M%S)

# Database utama
docker exec capstone2-pgsql-1 pg_dump -U postgres -d minepos -Fc \
  > "storage/backups/minepos-$TS.dump"

# Database testing
docker exec capstone2-pgsql-1 pg_dump -U postgres -d minepos_testing -Fc \
  > "storage/backups/minepos_testing-$TS.dump"

ls -lh storage/backups/
```

Verifikasi isi dump (pastikan TOC terbaca):

```bash
docker exec -i capstone2-pgsql-1 pg_restore -l \
  < storage/backups/minepos-<TS>.dump | head
```

## Restore

Restore ke database baru, aman karena tidak menimpa database yang ada:

```bash
docker exec capstone2-pgsql-1 createdb -U postgres minepos_restore
docker exec -i capstone2-pgsql-1 pg_restore -U postgres -d minepos_restore --no-owner \
  < storage/backups/minepos-<TS>.dump
```

Menimpa database yang ada (hati-hati, data saat ini akan hilang):

```bash
docker exec capstone2-pgsql-1 dropdb -U postgres minepos
docker exec capstone2-pgsql-1 createdb -U postgres minepos
docker exec -i capstone2-pgsql-1 pg_restore -U postgres -d minepos --no-owner \
  < storage/backups/minepos-<TS>.dump
```

Setelah restore, sinkronkan migration bila perlu:

```bash
php artisan migrate --force
```

## Catatan

- Simpan salinan dump di luar repo (drive eksternal atau cloud) sebagai lapisan kedua.
- Nama container PostgreSQL adalah `capstone2-pgsql-1`; sesuaikan bila berubah.
