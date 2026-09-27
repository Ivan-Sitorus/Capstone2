# Analisis Penyimpanan Hasil Data Mining — Cache Blob vs SQL Ternormalisasi vs JSONB

> **Status: ANALISIS.** Dokumen ini **tidak mengubah** kode, skema, atau data. Semua angka ukuran
> mengacu pada pengukuran nyata di `benchmark-result-sizes.md` (2026-09-27). Rekomendasi ditandai
> sebagai usulan, bukan perubahan yang sudah diterapkan.

- Tanggal: 2026-09-27
- Branch: `MinePOS`
- Penulis: (Nio / Data-Mining Storage Analysis)
- Ruang lingkup: **pertanyaan #14** — apakah hasil DM sebaiknya disimpan di cache blob
  (`CACHE_STORE=database`), tabel SQL ternormalisasi, atau JSONB?
- Dokumen terkait: `benchmark-result-sizes.md` (ukuran terukur), `benchmark-two-seeders.md` (metodologi).

---

## 1. Ringkasan Eksekutif

1. **Pola akses saat ini adalah whole-blob**: setiap halaman menulis dengan
   read–modify–write seluruh array (`Cache::get` → ubah → `Cache::put`) dan membacanya
   dengan `Cache::get('kunci', [])` lalu merender seluruh array. Tidak ada query per-field,
   agregasi lintas-run, prune, atau pembaruan parsial (bukti §2).
2. **Untuk pola akses itu, SQL ternormalisasi maupun JSONB TIDAK mempercepat baca.** Biaya baca
   didominasi **byte chart base64** (98,7% dari payload, `benchmark-result-sizes.md` §6); format
   penyimpanan apa pun tetap harus mengambil byte yang sama. Normalisasi tidak menghilangkan
   biaya TOAST dari PNG.
3. **Masalah sebenarnya bukan "cache vs tabel", melainkan tiga hal:** (a) kunci mati 19,43 MB
   yang hanya ditulis tidak dibaca, (b) chart base64 dicampur dengan baris data, (c) tidak ada
   prune/cap sehingga entri kedaluwarsa & yatim menumpuk.
4. **Rekomendasi:** tetap pakai cache-blob sebagai penyimpanan utama untuk sekarang, tetapi
   lakukan perbaikan murah berdampak tinggi: hapus kunci mati, **pindahkan chart ke berkas/objek
   storage**, tegakkan cap 3 entri, dan prune terjadwal. Adopsi tabel SQL ternormalisasi **hanya
   bila** kebutuhan lintas-run (audit/pencarian/pembaruan parsial) benar-benar muncul (§5–§6).
5. **Dampak angka** (dari 23,08 MB terukur): hapus kunci mati → **3,64 MB (−84,2%)**; lalu
   pindahkan chart ke berkas → **≈0,23 MB (−99,0%)** (§8).

---

## 2. Pola Akses Nyata (bukti kode)

### 2.1 Tulis — read–modify–write seluruh blob

Contoh `PrediksiMenu.php:136-170`:

```php
$history = Cache::get('prediksi_menu_results_history', []);      // baca SELURUH list
$history = array_values(array_filter(/* buang rentang tanggal sama */));
array_unshift($history, [/* satu entri baru */]);
Cache::put('prediksi_menu_results_history', $history, now()->addDays(30)); // tulis ULANG semua
```

Pola identik di `KlasterisasiMenu.php:186-204`, `KlasterisasiBahanBaku.php:155-187`,
`PrediksiBahanBaku.php:138-165`, `AsosiatifMenu.php:138-156`. Setiap penulisan menyalin ulang
seluruh blob (O(ukuran)); untuk `klasterisasi_menu_results` ~0,84 MB/run, ini berarti serialisasi
penuh tiap kali.

### 2.2 Baca — whole-blob, tanpa field query

Contoh `PrediksiRingMenu.php:48` dan `RingkasanAsosiatif.php:47,78`:

```php
$history = Cache::get('prediksi_menu_results_history', []);  // seluruh array
$this->results = $history;                                    // langsung dirender Blade
```

Tidak ada `WHERE`, `ORDER BY`, agregasi, atau pengambilan kolom tertentu. Grafik di-render dari
string base64 di dalam array yang sama.

### 2.3 Baca kunci mati

`prediksi_menu_last_result` (18,53 MiB) ditulis di `PrediksiMenu.php:166-170` tetapi **tidak
dibaca** di mana pun (`grep -rn` hanya menemukan `Cache::put`). Artinya biaya simpannya tidak
diimbangi manfaat baca apa pun.

### 2.4 Karakteristik yang relevan untuk pemilihan storage

| Karakteristik | Nilai (terukur/terlihat) |
|---|---|
| Frekuensi tulis | rendah: sekali per klik "Jalankan" per algoritma |
| Frekuensi baca | sedang: tiap halaman ring dibuka/di-refresh |
| Granularitas baca | seluruh blob (tidak pernah sebagian) |
| Ukuran dominan | chart base64 (98,7%) |
| Kebutuhan lintas-run | tidak ada di UI saat ini |
| Kebutuhan pembaruan parsial | tidak ada |
| Kebutuhan TTL/prune | ada (TTL 7/30 hari) tetapi tidak dijalankan otomatis |

---

## 3. Tiga Opsi Penyimpanan

### Opsi A — Cache blob (kondisi sekarang)

- Satu baris `cache` per kunci; nilai = PHP serialized array; `expiration` = Unix timestamp.
- **Kelebihan:** paling sederhana; tanpa skema/model; satu read/satu write; cocok dengan frekuensi
  tulis rendah.
- **Kekurangan:** tidak bisa di-query; baca selalu seluruh blob; tulis menyalin seluruh blob;
  tidak ada prune otomatis; mudah menyimpan data mati.

### Opsi B — SQL ternormalisasi

Usulan skema:

```
datamining_runs      (id, algorithm, run_at, input_from, input_to, date_from, date_to,
                      forecast_days, best_k, silhouette_score, total_*, created_at, updated_at)
datamining_run_items (id, run_id FK, item_type, item_name, cluster_label, quantity,
                      revenue, payload JSONB)          -- baris prediksi/klaster/rules
datamining_run_charts(run_id FK, chart_key, disk, path, mime, bytes)  -- chart DISIMPAN SEBAGAI BERKAS
```

- **Kelebihan:** query lintas-run (`WHERE best_k=3`, `ORDER BY run_at`), prune mudah
  (`DELETE WHERE run_at < …` dengan index), pembaruan parsial (catatan/label per run), enumerasi
  run tanpa memuat semua.
- **Kekurangan:** menambah migration/model/kode; tulis jadi multi-row insert; **tidak mengurangi
  byte chart** kecuali chart dipindah ke berkas (kolom BLOB tetap di-TOAST seperti sekarang).

### Opsi C — JSONB

- Menyimpan hasil (dan/atau chart) sebagai `jsonb`.
- **Kelebihan:** fleksibel (skema berubah tanpa migration), bisa di-index GIN untuk pencarian ke
  dalam JSON, `jsonb_set` untuk pembaruan parsial.
- **Kekurangan:** membaca satu run tetap mengambil seluruh nilai; base64 dalam JSONB bisa sedikit
  lebih besar (escaping) dan tetap di-TOAST; tidak ada percepatan baca untuk pola whole-blob.
  JSONB baru bernilai bila kita **benar-benar men-query ke dalamnya**.

---

## 4. Apakah SQL / JSONB Mempercepat Baca?

**Tidak, untuk pola akses saat ini.** Alasan:

1. Halaman ring membutuhkan **seluruh** run (baris **dan** chart) untuk dirender → SQL pun akan
   `SELECT *` setara; byte chart tetap harus dibaca.
2. Biaya baca = detoast nilai besar + transfer + (blob) `unserialize` PHP. SQL ternormalisasi
   menghindari `unserialize`, tetapi biaya itu **kecil** dibanding byte PNG. Terukur: blob aktif
   terbesar (klasterisasi menu) = 1,68 MB dengan chart 1,65 MB; barisnya 30 KB.
3. Kunci 19 MB praktis tidak pernah dibaca, jadi "mempercepat baca kunci itu" tidak relevan.

**Kapan SQL/JSONB baru memberi keuntungan** (dan itu bukan baca per-run):

| Kebutuhan | Cache blob | SQL ternormalisasi | JSONB |
|---|---|---|---|
| Render satu run utuh | ✅ memadai | ⚠️ setara (tambah join) | ⚠️ setara |
| Ambil hanya field tertentu (mis. daftar run, `best_k`) | ❌ baca semua | ✅ `SELECT id, run_at, best_k` | ✅ extract field |
| Cari lintas-run (`best_k=3`, rules lift>2) | ❌ | ✅ index/kolom | ✅ GIN |
| Prune by umur | ⚠️ manual | ✅ `DELETE … WHERE run_at <` | ⚠️ bisa tapi canggung |
| Pembaruan parsial (catatan/label) | ❌ tulis ulang blob | ✅ `UPDATE` kolom | ✅ `jsonb_set` |
| Chart 18 MB tetap di DB | ❌ | ❌ (BLOB tetap TOAST) | ❌ |

**Kesimpulan §4:** keuntungan SQL/JSONB bersifat **queryability & lifecycle**, bukan kecepatan
baca. Karena UI saat ini tidak men-query lintas-run, adopsi dini hanya menambah kompleksitas tanpa
manfaat nyata.

---

## 5. Kapan SQL/JSONB Layak Diadopsi

Adopsi Opsi B (SQL ternormalisasi; chart sebagai berkas) layak ketika salah satu muncul:

1. **Audit/riwayat lintas-run**: menampilkan "semua run 6 bulan terakhir dengan K optimal",
   membandingkan MAPE antar-run, atau mengekspor hasil.
2. **Prune berbasis kebijakan**: retensi per algoritma, hard cap jumlah run, atau pembersihan
   otomatis oleh `schedule`.
3. **Pembaruan parsial**: memberi catatan/approval/status per run tanpa menulis ulang blob.
4. **Kueri per-item**: "menu apa saja di klaster 2 sepanjang riwayat".
5. **Pemisahan chart dari DB** sudah dilakukan lebih dulu (kalau tidak, SQL tidak menyelesaikan
   masalah ukuran).

Selama kebutuhan di atas belum ada, **cache-blob tetap pilihan tepat**.

---

## 6. Matriks Keputusan

Skala: ✅ baik, ⚠️ terbatas, ❌ buruk.

| Dimensi | Cache blob (kini) | SQL ternormalisasi | JSONB |
|---|---|---|---|
| Kesederhanaan implementasi | ✅ | ❌ (migration+model) | ⚠️ |
| Kecepatan baca satu run (whole) | ✅ | ⚠️ | ⚠️ |
| Ambil field tertentu | ❌ | ✅ | ✅ |
| Pencarian lintas-run | ❌ | ✅ | ✅ |
| Prune/retensi | ❌ | ✅ | ⚠️ |
| Pembaruan parsial | ❌ | ✅ | ✅ |
| Ukuran byte chart | ❌ (98,7%) | ❌ (BLOB tetap) | ❌ |
| Ukuran baris data | ✅ | ✅ | ⚠️ (overhead) |
| Fleksibilitas skema | ✅ | ⚠️ | ✅ |

**Rekomendasi:** **Opsi A (cache blob) dipertahankan**, ditambah perbaikan §7. Opsi B diadopsi
hanya bila pemicu §5 muncul — dan saat itu **chart wajib dipindah ke berkas** agar normalisasi
benar-benar menurunkan ukuran DB. Opsi C (JSONB) hanya untuk sub-bagian **baris** yang butuh
query fleksibel (mis. `rules`), bukan untuk chart.

---

## 7. Rekomendasi Prioritas (highest-impact, tanpa ubah skema)

Urut menurun berdasarkan dampak ÷ risiko:

1. **Hapus penulisan kunci mati `prediksi_menu_last_result`.**
   Hanya ditulis, tidak pernah dibaca; 18,53 MiB = 84,2% total DM.
   → DM turun dari 23,08 MB ke **3,64 MB (−84,2%)**. (Perubahan 1 baris kode; di luar scope dokumen ini.)
2. **Pisahkan chart dari baris data.**
   Simpan PNG sebagai berkas (disk publik/S3) dan simpan hanya **path/URL** di cache/tabel;
   atau minimal kunci cache terpisah per chart. Baris + log hanya **≈0,23 MB** (aktif, tanpa kunci mati).
   → DM turun ke **≈0,23 MB (−99,0%)** dan baca halaman ring tidak lagi men-detoast PNG raksasa.
3. **Tegakkan cap 3 entri** yang selama ini hanya dikomentari
   (`array_slice($history, 0, 3)`) — mencegah pertumbuhan tak terbatas per rentang tanggal unik.
4. **Prune terjadwal + bersihkan kunci yatim.** Tambah perintah/scheduler yang
   `DELETE FROM cache WHERE expiration < now()` untuk kunci DM, dan hapus blob prefix lama
   (`posmine-cache-…`) yang tak terbaca. Ganti `Cache::flush()` di `ReceiptSettingsPage.php:101`
   dengan `Cache::forget()` bertarget agar tidak menghapus hasil DM.
5. **Perkecil chart terbesar.** `charts.all_items` (satu PNG 9,49 MB, 49% payload) dan 44 chart
   `per_menu` (~211 KB masing-masing): turunkan DPI/`figsize` atau agregasi; atau serve sebagai
   berkas ter-cache dengan kompresi.

Setelah 1–2 dilakukan, kebutuhan akan SQL/JSONB sebagian besar hilang.

---

## 8. Dampak Angka (basis: pengukuran nyata)

Basis: total DM terukur **23.075.577 B (23,08 MB)**; chart **22.774.912 B (98,7%)**;
kunci mati **19.434.113 B**; baris aktif (5 kunci history) **≈226.850 B (0,23 MB)**;
chart aktif **≈3.413.764 B (3,41 MB)**.

| Skenario | Total byte DM | Perubahan |
|---|---:|---:|
| Sekarang | 23.075.577 B (23,08 MB) | — |
| Hapus kunci mati (#1) | 3.641.464 B (3,64 MB) | **−84,2%** |
| + chart dipindah ke berkas (#2) | ≈226.850 B (0,23 MB) | **−99,0%** dari sekarang |
| Terhadap tabel `cache` 24 MB | 0,23 MB ≈ **1%** (dari 96%) | — |
| Terhadap DB `pos_cafe` 247 MB | ≈0,01% (dari 9,3%) | — |

> Bila DB `minepos` juga live dengan salinan byte-identik, semua angka di atas berlipat dua
> (≈46 MB → ≈0,5 MB). Lihat `benchmark-result-sizes.md` §11 untuk ketidakpastian ini.

---

## 9. Rencana Migrasi (hanya bila §5 terpenuhi)

1. Buat `datamining_runs`, `datamining_run_items` (+ kolom `payload jsonb` untuk metrik fleksibel),
   `datamining_run_charts` (path berkas, bukan byte).
2. Pindahkan chart ke `storage/app/datamining/charts/{run_id}/{key}.png` + URL; buat perintah
   backfill dari blob cache lama.
3. Ubah halaman Filament: tulis metadata+baris ke tabel, chart ke berkas, lalu isi cache-hot
   (opsional) dengan **baris saja** (tanpa chart) sebagai lapisan turunan ber-TTL pendek.
4. Tambah perintah prune + `schedule()`; hapus kunci lama setelah verifikasi paritas.
5. Verifikasi: bandingkan hasil render halaman ring sebelum/sesudah untuk satu run yang sama.

Rencana ini **belum dijalankan**; butuh perubahan kode + migration (di luar scope dokumen analisis).

---

## 10. Risiko & Ketidakpastian

1. **Tanpa perubahan kode** pada dokumen ini; rekomendasi §7–§9 bersifat usulan.
2. **Baca tidak dipercepat oleh SQL/JSONB** adalah kesimpulan struktural dari pola akses §2;
   bila UI kelak menambah query lintas-run, kesimpulan §4 berubah.
3. **Prefix & DB live** (`posmine-` vs `minepos-`, `pos_cafe` vs `minepos`) perlu dikonfirmasi
   operator sebelum keputusan prune dilakukan; jangan menghapus tanpa verifikasi.
4. **Angka variasi run**: ukuran PNG matplotlib bervariasi antar-run; angka §8 adalah satu snapshot.
5. **Pemisahan chart ke berkas** mengubah cara render (base64 → URL) dan perlu penanganan
   otorisasi akses berkas; ini konsekuensi implementasi, bukan alasan menunda pemisahan ukuran.
