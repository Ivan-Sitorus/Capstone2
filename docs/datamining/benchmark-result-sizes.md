# Ukuran Hasil Data Mining (Cache Blob) — Pengukuran Nyata

> **Status: TERUKUR.** Semua angka pada §5–§6 diukur langsung dari tabel `cache` PostgreSQL
> (database `pos_cafe`, container `capstone2-pgsql-1`) pada 2026-09-27. Tidak ada angka rekaan.
> Angka yang **diestimasi** selalu ditandai `ESTIMASI` beserta dasar perhitungannya (§7).
> Dokumen ini melengkapi `benchmark-two-seeders.md` (metodologi & gaya mengikuti dokumen itu).

- Tanggal ukur: 2026-09-27
- Branch: `MinePOS`
- Penulis: (Nio / Data-Mining Storage Analysis)
- Ruang lingkup: **pertanyaan #19** — berapa ukuran hasil data mining yang tersimpan, dan bagaimana
  skalanya terhadap jumlah order.
- Tanpa perubahan kode/skema/data. Hanya query read-only + pembacaan berkas.

---

## 1. Executive Summary

1. Total hasil data mining yang tersimpan di cache = **23,08 MB** (23.075.577 byte) untuk 6 kunci,
   dan **98,7%** di antaranya adalah **gambar chart base64** (22.774.912 byte). Baris data
   (prediksi/ringkasan/rules) hanya **≈0,30 MB (1,3%)**.
2. **Satu kunci mendominasi: `prediksi_menu_last_result` = 18,53 MiB (84,2% dari total).**
   Kunci ini **hanya ditulis, tidak pernah dibaca** oleh kode mana pun (lihat §4) — jadi 84% burden
   DB saat ini adalah data mati ("dead value").
3. Ukuran hasil **ditentukan oleh katalog (jumlah menu/bahan) dan horizon prediksi**, bukan oleh
   jumlah order. Bukti terukur: dua run klasterisasi menu dengan rentang **160 hari vs 584 hari
   (3,65×)** menghasilkan ukuran chart **hampir identik** (828.142 vs 821.958 byte, selisih −0,7%).
   Jumlah order hanya memengaruhi ukuran bila ia mengubah **rentang hari (jumlah titik data)**
   atau katalog (§7).
4. Tabel `cache` berukuran **24 MB** (TOAST) ≈ **96%**-nya adalah hasil DM ini; terhadap total DB
   `pos_cafe` (247 MB) ≈ **9,3%**. Belum kritis hari ini, **tetapi** (a) angka mati 18,5 MiB,
   (b) tidak ada prune terjadwal, dan (c) blob DM terduplikasi byte-identik di DB `minepos`
   → pertumbuhan tidak terbatas (§8).
5. Perbaikan berdampak tertinggi (tanpa ubah skema): **hapus penulisan kunci mati** (−84% byte DM),
   **pisahkan chart dari baris** (sisa ≈0,30 MB, −98,7%), **tegakkan cap 3 entri**, dan
   **prune kunci kedaluwarsa/yatim**. Detail di `storage-analysis.md`.

---

## 2. Pertanyaan yang Dijawab (#19)

> *"Berapa ukuran hasil data mining yang tersimpan per algoritma, dan bagaimana ukurannya berskala
> terhadap jumlah order (mis. 1000 vs 5000 order)?"*

Jawaban: hasil disimpan sebagai **satu nilai serialized PHP per kunci cache** (bukan tabel hasil).
Ukuran nyata per kunci diukur pada §5; ukurannya didorong oleh **chart base64** (§6) dan
**jumlah menu/bahan + horizon**, bukan volume transaksi (§7).

---

## 3. Metodologi

### 3.1 Environment

- DB PostgreSQL 18, container `capstone2-pgsql-1`; database aktif per `.env` = **`pos_cafe`**
  (`DB_CONNECTION=pgsql`, `DB_DATABASE=pos_cafe`).
- `CACHE_STORE=database` (`.env`), `config/cache.php` → `driver: database`, `table: cache`,
  prefix default `Str::slug(APP_NAME).'-cache-'`. `APP_NAME=minePOS` → prefix runtime
  **`minepos-cache-`**.
- **Catatan prefix (terukur):** seluruh blob DM di DB tersimpan dengan prefix **`posmine-cache-`**
  (bukan `minepos-`). Artinya blob ini ditulis saat `APP_NAME` masih bernilai brand lama
  (`posMine`, lihat jejak `https://posmine.com` di view ter-cache). Karena kunci runtime sekarang
  `minepos-cache-…` dan DB `pos_cafe` **tidak memiliki** baris `minepos-cache-prediksi*`, blob DM
  tersebut **tidak dapat dibaca** aplikasi berkonfigurasi saat ini (lihat §8.3).
- Karena `Cache::get()` pada database store **menghapus entri hanya saat kunci itu dibaca dan
  ternyata kedaluwarsa**, baris dengan prefix lama **tidak pernah dibaca → tidak pernah dihapus**,
  bahkan setelah TTL lewat.

### 3.2 Cara mengukur

**a. Ukuran kolom & metadata (query langsung):**

```bash
docker exec capstone2-pgsql-1 psql -U postgres -d pos_cafe -c \
  "SELECT key,
          pg_size_pretty(pg_column_size(value)::bigint) AS val_size,
          pg_column_size(value) AS bytes,
          to_timestamp(expiration) AS expires_at
     FROM cache ORDER BY pg_column_size(value) DESC;"

docker exec capstone2-pgsql-1 psql -U postgres -d pos_cafe -c \
  "SELECT pg_size_pretty(pg_total_relation_size('cache')) AS cache_table,
          pg_size_pretty(pg_database_size('pos_cafe'))       AS db_total;"
```

**b. Dekomposisi payload (chart vs baris):** nilai serialized di-dump per kunci, lalu
di-`unserialize()` dengan PHP 8.5 (host) dan ditelusuri rekursif; setiap string base64
(penanda PNG `iVBORw0KGgo`) dijumlahkan panjangnya.

```bash
docker exec capstone2-pgsql-1 psql -U postgres -d pos_cafe -Atc \
  "SELECT value FROM cache WHERE key='posmine-cache-prediksi_menu_last_result';" \
  > /tmp/dmcache/prediksi_menu_last_result.ser
php /tmp/dmcache/analyze.php     # unserialize + hitung byte per sub-pohon & total base64
```

- **Ukuran kunci** = `pg_column_size(value)` (byte tersimpan; untuk data ini = `octet_length`,
  artinya TOAST **tidak** mengompres efektif karena PNG sudah terkompresi).
- **Ukuran baris** = `strlen(serialize(<sub-array>))` per bagian (predictions/summary_table/logs).
- **Ukuran chart** = jumlah `strlen()` semua string base64 di pohon payload.

### 3.3 Batasan

- Angka diukur pada **satu snapshot** DB saat ini (6 kunci DM, 2 run per kunci history, 1 run
  untuk `prediksi_menu_last_result`), bukan rata-rata banyak run.
- Ukuran bagan PNG bervariasi antar-run (matplotlib) seperti dicatat di `benchmark-two-seeders.md` §11.
- Tidak ada algoritma DM berat yang dijalankan untuk dokumen ini (RAM mesin terbatas) — lihat §10.

---

## 4. Struktur Penyimpanan (bukti kode)

`CACHE_STORE=database`, tanpa Redis/Memcached. Hasil ditulis oleh halaman Filament, **bukan**
tabel hasil tersendiri. Kunci, TTL, dan pola tulis:

| Kunci cache (prefix runtime) | Penulis (write) | Pembaca (read) | TTL | Cap entri | Chart disimpan |
|---|---|---|---:|---|---|
| `prediksi_menu_last_result` | `PrediksiMenu.php:166-170` | **tidak ada** | 7 hari | — | **Seluruh 5 grup chart** |
| `prediksi_menu_results_history` | `PrediksiMenu.php:136-163` | `PrediksiRingMenu.php:48` | 30 hari | klaim "max 3" (tak diimplementasi) | 1 chart (`feature_importance`) |
| `prediksi_bahan_baku_results_history` | `PrediksiBahanBaku.php:138-165` | `PredictionRingBahanBaku.php:47` | 30 hari | klaim "max 3" (tak diimplementasi) | 1 chart (`feature_importance`) |
| `klasterisasi_menu_results` | `KlasterisasiMenu.php:186-204` | `RingkasanMenu.php:47,78` | 30 hari | komentar "tanpa batas" | 5 chart |
| `klasterisasi_bahan_baku_results_history` | `KlasterisasiBahanBaku.php:155-187` | `RingkasanClusteringBahanBaku.php:44,57` | 30 hari | klaim "maks 3" (tak diimplementasi) | 4 chart |
| `asosiatif_menu_results` | `AsosiatifMenu.php:138-156` | `RingkasanAsosiatif.php:47,78` | 30 hari | klaim "list 3 terbaru" (tak diimplementasi) | 3 chart |

Temuan penting dari pembacaan kode:

1. **`prediksi_menu_last_result` adalah data mati.** `grep -rn "prediksi_menu_last_result" app/ resources/`
   hanya menemukan **satu kemunculan: `Cache::put`**. Tidak ada `Cache::get`. Kunci ditulis demi
   "backward-compatible" (`PrediksiMenu.php:165`) tetapi tidak dikonsumsi halaman mana pun.
2. **Pola tulis = read–modify–write seluruh blob.** Setiap run: `Cache::get` seluruh list → filter
   rentang tanggal sama → `array_unshift` → `Cache::put` seluruh list. Biaya tulis O(ukuran blob).
3. **Cap "max 3" tidak ada di kode.** Tidak ditemukan `array_slice`/truncate di halaman mana pun;
   komentar `max 3`/`maks 3` menyesatkan. History bertambah **satu entri per rentang tanggal unik**
   sampai TTL 30 hari.
4. **Pola baca = whole-blob.** `Cache::get('…', [])` mengembalikan seluruh array lalu dirender Blade.
   Tidak ada query per-field, agregasi lintas-run, atau pembaruan parsial.
5. **Entri tidak dihapus saat kedaluwarsa oleh Laravel** (database store hanya menghapus saat
   `get()` kunci yang kedaluwarsa); tidak ada `schedule`/perintah prune di `routes/console.php`.
   `Cache::flush()` justru dipanggil di `ReceiptSettingsPage.php:101` (menyimpan pengaturan struk
   akan **menghapus seluruh cache**, termasuk hasil DM).

### 4.1 Bentuk payload (dari FastAPI)

- Prediksi menu (`prediction.py:573`): `predictions` (n menu), `summary_table`, `preprocessing_logs`,
  `charts = { forecast_all, feature_importance, evaluation, all_items, per_menu[ ] }`.
- Prediksi bahan baku (`prediksibaku.py:432`): sama strukturnya, `charts.per_ingredient[ ]`.
- Klasterisasi menu/bahan (`api.py`): `table_rows`, `cluster_summary`, `preprocessing_logs`,
  `charts = { bar, bar_jumlah, bar_keuntungan, kategorisasi, elbow, silhouette }` (subset per tipe).
- Asosiasi (`association.py:277`): `rules` (Top-8), `freq_1_itemsets`, `freq_2_itemsets`,
  `preprocessing_logs`, `charts = { top_rules, sup_conf, freq_item }`.

Semua `charts` adalah **PNG base64** (`api.py:115`, `prediction.py:44`, `prediksibaku.py:71`),
disimpan inline di dalam array yang sama.

---

## 5. Hasil Pengukuran per Kunci / Algoritma — **TERUKUR**

Diukur dari DB `pos_cafe`, 2026-09-27. Satuan: byte; MB = 10⁶ byte, MiB = 2²⁰ byte.

| Kunci | Algoritma | Byte tersimpan | Setara | Chart (byte) | Baris+log (byte) | % chart |
|---|---|---:|---:|---:|---:|---:|
| `prediksi_menu_last_result` | Prediksi menu (blob penuh) | 19.434.113 | 19,43 MB (18,53 MiB) | 19.361.148 | 69.653 | **99,60%** |
| `klasterisasi_menu_results` (2 run) | Klasterisasi menu | 1.679.961 | 1,68 MB | 1.649.752 | 29.847 | 98,20% |
| `klasterisasi_bahan_baku_results_history` (2 run) | Klasterisasi bahan baku | 893.496 | 0,89 MB | 877.044 | 16.216 | 98,16% |
| `asosiatif_menu_results` (2 run) | Asosiasi menu | 433.344 | 0,43 MB | 412.260 | 20.898 | 95,13% |
| `prediksi_bahan_baku_results_history` (2 run) | Prediksi bahan baku | 415.581 | 0,42 MB | 322.884 | 92.659 | 77,69% |
| `prediksi_menu_results_history` (1 run) | Prediksi menu (history) | 219.076 | 0,22 MB | 151.824 | 67.230 | 69,30% |
| **TOTAL** | | **23.075.577** | **23,08 MB (22,01 MiB)** | **22.774.912** | **296.503** | **98,70%** |

**Ukuran per satu run** (entri history, agar adil antar-algoritma):

| Algoritma | Byte / run | Chart / run | Baris+log / run | #PNG / run (blob penuh) |
|---|---:|---:|---:|---:|
| Klasterisasi menu | 843.011 / 836.936 | 828.142 / 821.958 | 14.869 / 14.978 | 5 |
| Klasterisasi bahan baku | 454.621 / 438.861 | 446.335 / 430.931 | 8.286 / 7.930 | 4 |
| Prediksi bahan baku | 207.788 / 207.779 | 161.496 / 161.412 | 46.292 / 46.367 | 33 (history simpan 1) |
| Prediksi menu | 219.066 | 151.836 | 67.230 | 48 (history simpan 1) |
| Asosiasi menu | 196.382 / 236.948 | 187.922 / 224.510 | 8.460 / 12.438 | 3 |

**Konteks tabel cache & DB:**

| Metrik | Nilai terukur |
|---|---|
| `pg_total_relation_size('cache')` (`pos_cafe`) | **24 MB** (didominasi TOAST dari blob DM) |
| Jumlah baris tabel `cache` (`pos_cafe`) | 27 |
| Total hasil DM / ukuran tabel cache | ≈ **96%** |
| `pg_database_size('pos_cafe')` | **247 MB** |
| Total hasil DM / total DB | ≈ **9,3%** |
| DB `minepos` — tabel `cache` | **24 MB**, blob DM **byte-identik** (md5 sama) dengan `pos_cafe` |

> **Duplikasi:** keenam md5 blob DM di `pos_cafe` **sama persis** dengan di `minepos`
> (`fa00b467…`, `4987f979…`, `e3ec0197…`, `8e120097…`, `a5f9fe05…`, `8f49f7a2…`). Bila kedua DB
> dianggap hidup, ada **≈46 MB** blob DM identik tersimpan dua kali.

---

## 6. Dekomposisi: Chart Mendominasi (bukti langsung)

### 6.1 `prediksi_menu_last_result` (19,43 MB) — rincian byte

| Bagian | Byte | % payload |
|---|---:|---:|
| `charts.all_items` (1 PNG, 44 subplot ditumpuk, `figsize=(14, 5×44)`) | 9.492.528 | **48,8%** |
| `charts.per_menu[0..43]` (44 PNG, masing-masing 14×5 inci) | 9.285.376 | **47,8%** |
| `charts.evaluation` | 332.644 | 1,7% |
| `charts.feature_importance` | 151.824 | 0,8% |
| `charts.forecast_all` | 98.776 | 0,5% |
| **Total charts** | **19.364.214** | **99,60%** |
| `predictions` (44 menu × 7 hari forecast) | 58.033 | 0,30% |
| `summary_table` (44 baris) | 8.799 | 0,05% |
| `preprocessing_logs` + rentang tanggal | ≈2.821 | 0,01% |
| **Total baris+log (tanpa chart)** | **69.653** | **0,36%** |

> **Satu gambar** (`all_items`, lihat `prediction.py:369-388`) menyumbang **9,05 MiB** — sendirian
> sekitar **49%** dari seluruh hasil DM yang tersimpan.

### 6.2 Kunci lain (chart share)

| Kunci | Chart | Baris+log | % chart |
|---|---:|---:|---:|
| `klasterisasi_menu_results` | 1.649.752 | 29.847 | 98,2% |
| `klasterisasi_bahan_baku_results_history` | 877.044 | 16.216 | 98,2% |
| `asosiatif_menu_results` | 412.260 | 20.898 | 95,1% |
| `prediksi_bahan_baku_results_history` | 322.884 | 92.659 | 77,7% |
| `prediksi_menu_results_history` | 151.824 | 67.230 | 69,3% |

**Kesimpulan §6:** menyimpan "hasil" pada praktiknya berarti menyimpan **PNG**, bukan angka.

---

## 7. Skalabilitas terhadap Jumlah Order (1000 vs 5000)

### 7.1 Prinsip: output = katalog × horizon, bukan jumlah transaksi

Pipeline mengagregasi transaksi menjadi **deret harian per menu/bahan** sebelum model berjalan
(`prediction.py` SQL agregasi harian; `benchmark-two-seeders.md` §5.2). Karena itu:

- Jumlah **baris output**: `predictions` = jumlah menu/bahan × `forecast_days`;
  `summary_table` = jumlah menu/bahan; `rules` = Top-8; `table_rows` klasterisasi = jumlah menu/bahan.
- Jumlah **chart output**: 1 chart per menu/bahan + sejumlah chart ringkasan tetap.
- Jumlah order hanya masuk melalui: (a) **rentang hari** (banyak titik data di grafik), dan
  (b) katalog (jumlah menu/bahan yang punya penjualan).

### 7.2 Bukti terukur bahwa jumlah order/hari tidak mengubah ukuran

| Perbandingan | Rentang hari | Transaksi | Ukuran chart | Delta |
|---|---:|---:|---:|---:|
| Klasterisasi menu run [0] | 160 hari | — | 828.142 B | — |
| Klasterisasi menu run [1] | **584 hari (3,65×)** | — | 821.958 B | **−0,74%** |
| Klasterisasi bahan baku [0] (K=4) | 212 hari | — | 446.335 B | — |
| Klasterisasi bahan baku [1] (K=2) | 181 hari | — | 430.931 B | −3,4% |
| Asosiasi [0] | 181 hari | 20.261 | 187.922 B | — |
| Asosiasi [1] | 117 hari | 11.731 (**0,58×**) | 224.510 B (rules 4→**8**) | +19% (didorong rules, bukan transaksi) |

Dua run klasterisasi menu berbeda 3,65× panjang rentang menghasilkan ukuran chart hampir sama —
bukti langsung bahwa ukuran hasil **tidak** berskala dengan jumlah order/transaksi.

### 7.3 Angka 1000 vs 5000 order

- **Skenario A — katalog & rentang hari sama** (mis. 1000 order dalam 3 bulan vs 5000 order dalam
  3 bulan): **ukuran byte identik**. Baris output sama (`44 menu × 7 hari`), chart sama.
  Ini kasus paling umum bila yang bertambah hanya kepadatan transaksi, bukan panjang periode.
- **Skenario B — 5000 order memperpanjang rentang hari** (mis. 1000 order ≈ 3 bulan vs
  5000 order ≈ 12–15 bulan): hanya **prediksi menu/bahan** yang berubah (grafik memuat lebih banyak
  titik harian); klasterisasi & asosiasi tetap (terbukti §7.2).

**ESTIMASI rentang ukuran (dasar: §5–§6 + geometri gambar):**

| Kunci | ~3 bulan (1000 order) | ~12 bulan (5000 order) | Terukur (rentang 19 bulan) |
|---|---:|---:|---:|
| `prediksi_menu_last_result` (blob penuh) | **ESTIMASI ≈10–15 MB** | **ESTIMASI ≈15–18 MB** | **19,43 MB** |
| `prediksi_menu_results_history` (1 chart fitur) | ≈0,20–0,23 MB | ≈0,20–0,23 MB | 0,219 MB |
| `prediksi_bahan_baku_results_history` | ≈0,20–0,23 MB | ≈0,20–0,23 MB | 0,416 MB (2 run) |
| `klasterisasi_menu_results` (per run) | ≈0,80–0,85 MB | ≈0,80–0,85 MB | 0,84 MB/run |
| `klasterisasi_bahan_baku_results_history` (per run) | ≈0,43–0,46 MB | ≈0,43–0,46 MB | 0,45 MB/run |
| `asosiatif_menu_results` (per run) | ≈0,19–0,24 MB | ≈0,19–0,24 MB | 0,20–0,24 MB/run |

**Dasar ESTIMASI kolom prediksi menu:** `all_items` = 44 subplot `figsize=(14, 5×44)` inci; tinggi
kanvas tetap, sehingga ukuran PNG tumbuh **sub-linear** terhadap jumlah titik (garis hari yang
panjang memampatkan lebih baik). `all_items` terukur 9,49 MB pada 577 hari; memperpendek ke ~90 hari
diperkirakan ~4–7 MB, memperpanjang ke ~365 hari ~7–9 MB, dengan `per_menu` (48%) ikut menurun/menaik
lebih ringan. Angka ini **belum diukur langsung** karena memerlukan run DM (lihat §10).

> **Kesimpulan §7:** untuk pertanyaan "1000 vs 5000 order", jawaban singkatnya adalah **hampir tidak
> berpengaruh** selama katalog dan rentang tanggal sama. Yang membuat hasil besar adalah
> **jumlah menu (44) dan panjang rentang hari**, bukan volume order.

---

## 8. TTL, Retensi, dan Dampak Database

### 8.1 TTL aktual

| Kunci | TTL | Kolom `expiration` terukur |
|---|---|---|
| `prediksi_menu_last_result` | 7 hari | 2026-10-03 (run 2026-09-26) |
| seluruh kunci history lain | 30 hari | 2026-10-26 |

### 8.2 Yang terjadi setelah kedaluwarsa

- Laravel database cache **tidak** menjalankan penghapusan latar. `DatabaseStore::get()` menghapus
  entri hanya jika kunci itu **dibaca** dan sudah lewat `expiration`.
- Tidak ada `schedule()`/perintah prune di `routes/console.php` (hanya `inspire`).
- Akibatnya: entri kedaluwarsa **tetap menempati TOAST** sampai (a) kunci dibaca lagi, atau
  (b) `php artisan cache:clear`/`Cache::flush()` dijalankan.
- `Cache::flush()` ada di `ReceiptSettingsPage.php:101` — menyimpan pengaturan struk akan
  menghapus **seluruh** cache termasuk hasil DM (fragile, bukan mekanisme retensi yang disengaja).

### 8.3 Kunci yatim (prefix)

- Blob DM berprefix `posmine-cache-`, sedangkan prefix runtime sekarang `minepos-cache-`.
- DB `pos_cafe` **tidak** punya `minepos-cache-prediksi*`, jadi aplikasi saat ini tidak menemukan
  dan tidak pernah `get()` blob lama → **tidak pernah dihapus** meski TTL lewat.
- Ini memperkuat kekhawatiran tim: hasil bisa "tersimpan selamanya" bukan karena TTL panjang,
  tetapi karena **key never read → never expired-deleted**.

### 8.4 Dampak DB terukur

| Item | Nilai |
|---|---|
| Ukuran blob DM (6 kunci) | 23,08 MB |
| Di antaranya kunci mati `prediksi_menu_last_result` | 18,53 MiB (**84,2%** dari total DM) |
| Ukuran baris+log saja (tanpa chart, semua kunci) | **≈0,30 MB** |
| Tabel `cache` (`pos_cafe`) | 24 MB → DM ≈96% |
| Tabel `cache` (`minepos`) | 24 MB (duplikat byte-identik) |
| Total DB `pos_cafe` | 247 MB → DM ≈9,3% |

**Kesimpulan §8:** burden saat ini ≈23 MB/DB (≈46 MB bila dua DB hidup), **bukan** dari jumlah
baris (hanya 27 baris) melainkan dari **nilai TOAST raksasa berisi PNG base64**. Tanpa perbaikan,
pertumbuhan = (jumlah rentang tanggal unik yang dijalankan × ukuran/run) tanpa batas, dan kunci
kedaluwarsa/yatim tidak dibersihkan otomatis.

---

## 9. Kesimpulan & Dampak Database (untuk #19)

1. Hasil DM disimpan sebagai **blob cache tunggal per kunci**, 98,7% isinya chart base64.
2. Ukuran per algoritma (1 run): klasterisasi menu ~0,84 MB, klasterisasi bahan baku ~0,45 MB,
   prediksi bahan baku ~0,21 MB, prediksi menu (history) ~0,22 MB, asosiasi ~0,20–0,24 MB;
   `prediksi_menu_last_result` yang **mati** = 19,43 MB.
3. Skala terhadap order: **tidak** berskala dengan jumlah order; berskala dengan **jumlah menu/bahan
   × jumlah hari × horizon**. 1000 vs 5000 order dalam rentang yang sama = ukuran identik.
4. Retensi: TTL 7/30 hari **tetapi** tidak ada pembersihan otomatis; entri yatim/kedaluwarsa
   bertahan. Tidak ada cap 3 entri di kode.
5. Rekomendasi perbaikan prioritas (detail di `storage-analysis.md`):
   1. Hapus penulisan `prediksi_menu_last_result` → **−84%** byte DM.
   2. Pisahkan chart (berkas/objek storage) dari baris → sisa **≈0,30 MB** (**−98,7%**).
   3. Tegakkan cap 3 entri; prune terjadwal; bersihkan kunci prefix lama.
   4. Perkecil `all_items` (49% payload) dan chart per-menu.

---

## 10. Reproduce

```bash
# 1. Ukuran & TTL per kunci
docker exec capstone2-pgsql-1 psql -U postgres -d pos_cafe -c \
  "SELECT key, pg_size_pretty(pg_column_size(value)::bigint) s,
          pg_column_size(value) b, to_timestamp(expiration) exp
     FROM cache ORDER BY b DESC;"

# 2. Ukuran tabel cache & DB
docker exec capstone2-pgsql-1 psql -U postgres -d pos_cafe -c \
  "SELECT pg_size_pretty(pg_total_relation_size('cache')),
          pg_size_pretty(pg_database_size('pos_cafe'));"

# 3. Dump + dekomposisi chart vs baris
for k in prediksi_menu_last_result prediksi_menu_results_history \
         prediksi_bahan_baku_results_history klasterisasi_menu_results \
         klasterisasi_bahan_baku_results_history asosiatif_menu_results; do
  docker exec capstone2-pgsql-1 psql -U postgres -d pos_cafe -Atc \
    "SELECT value FROM cache WHERE key='posmine-cache-$k';" > /tmp/dmcache/$k.ser
done
php /tmp/dmcache/analyze.php     # unserialize rekursif + jumlah byte base64
```

### Follow-up yang memerlukan run DM (belum dijalankan; RAM mesin terbatas)

Untuk mengukur (bukan mengestimasi) kolom `ESTIMASI` §7.3: jalankan pipeline prediksi menu pada
tiga rentang (~3 bulan, ~12 bulan, ~19 bulan) dengan katalog tetap, dengan cache dihangatkan, lalu
catat `pg_column_size` kunci hasil. Jalankan **saat mesin idle, satu pipeline per waktu**, sesuai
peringatan di `benchmark-two-seeders.md` §10.

---

## 11. Threats to Validity / Ketidakpastian

1. **Snapshot tunggal.** Ukuran PNG matplotlib bervariasi antar-run (dokumentasi `benchmark-two-seeders.md`
   §11); angka per-run di sini dari run yang tersimpan, bukan median banyak run.
2. **Satu DB ≠ seluruh deployment.** `.env` menunjuk `pos_cafe`; DB `minepos` menyimpan salinan
   byte-identik. Kebenaran "DB mana yang live" harus dikonfirmasi operator sebelum klaim duplikasi
   dijadikan dasar keputusan.
3. **Prefix `posmine-`.** Kesimpulan §8.3 (kunci yatim) bergantung pada `APP_NAME`/`CACHE_PREFIX`
   saat ini dan tidak adanya config ter-cache (`bootstrap/cache/config.php` tidak ada saat pengukuran).
   Verifikasi ulang bila `APP_NAME`/`CACHE_PREFIX` berubah.
4. **Angka ESTIMASI §7.3** belum diukur; hanya didasarkan pada geometri gambar + ukuran terukur.
   Jangan kutip sebagai angka terukur.
5. **TOAST.** `pg_column_size` = `octet_length` menunjukkan tidak ada kompresi efektif untuk data ini;
   bila konfigurasi TOAST berubah, ukuran disk bisa berbeda dari angka logis.
