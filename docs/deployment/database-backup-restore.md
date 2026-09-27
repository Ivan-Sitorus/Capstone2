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
docker exec capstone2-pgsql-1 pg_dump -U postgres -d pos_cafe -Fc \
  > "storage/backups/pos_cafe-$TS.dump"

# Database testing
docker exec capstone2-pgsql-1 pg_dump -U postgres -d pos_cafe_testing -Fc \
  > "storage/backups/pos_cafe_testing-$TS.dump"

ls -lh storage/backups/
```

Verifikasi isi dump (pastikan TOC terbaca):

```bash
docker exec -i capstone2-pgsql-1 pg_restore -l \
  < storage/backups/pos_cafe-<TS>.dump | head
```

## Restore

Restore ke database baru, aman karena tidak menimpa database yang ada:

```bash
docker exec capstone2-pgsql-1 createdb -U postgres pos_cafe_restore
docker exec -i capstone2-pgsql-1 pg_restore -U postgres -d pos_cafe_restore --no-owner \
  < storage/backups/pos_cafe-<TS>.dump
```

Menimpa database yang ada (hati-hati, data saat ini akan hilang):

```bash
docker exec capstone2-pgsql-1 dropdb -U postgres pos_cafe
docker exec capstone2-pgsql-1 createdb -U postgres pos_cafe
docker exec -i capstone2-pgsql-1 pg_restore -U postgres -d pos_cafe --no-owner \
  < storage/backups/pos_cafe-<TS>.dump
```

Setelah restore, sinkronkan migration bila perlu:

```bash
php artisan migrate --force
```

## Catatan

- Simpan salinan dump di luar repo (drive eksternal atau cloud) sebagai lapisan kedua.
- Nama container PostgreSQL adalah `capstone2-pgsql-1`; sesuaikan bila berubah.
