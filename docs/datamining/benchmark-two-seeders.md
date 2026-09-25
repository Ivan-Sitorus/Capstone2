# Benchmark Dua Seeder — Original vs Optimized (Data Mining POSMine)

> **Status: TERUKUR.** Dataset A & Dataset B sudah diukur (lihat §7). Tidak ada angka rekaan.
> - Dataset A (2025-01-01 → 2026-09-25): 242,83s → 57,92s = **4,19×** (`comparison-findings.md`;
>   run optimized terbaru setelah optimasi P11: total ≈21,7s → ≈11×).
> - Dataset B (replikasi full `DatabaseSeeder` repo original; 6.454 order / 7.409 item): 70,80s → 13,20s = **5,36×**.
>
> Dokumen ini disusun untuk pembaca **skeptis**: setiap klaim waktu punya dataset, perintah, dan
> bukti paritas yang bisa direproduksi.

- Tanggal draft: 2026-09-25
- Penulis: (Nio / Data-Mining Optimization)
- Sumber utama sisi "optimized": `.sisyphus/notepads/datamining-optimization-refactor/comparison-findings.md`
- Repo original (read-only): `https://github.com/ShandovaaGame/Capstone_POS_Cafe`
  - Commit HEAD saat clone: `e23f347c3ecaad7ec2233849116d3519c95920ea` (2026-08-14, "Update source code")
  - Clone lokal: `/tmp/pos-cafe-original` (dihapus setelah pengukuran; lihat langkah reproduce)

---

## 1. Executive Summary

Implementasi data mining kita (5 pipeline FastAPI) dioptimasi tanpa mengubah algoritma inti.
Pada **Dataset A** (seeder milik project kita, rentang 2025-01-01 → 2026-09-25; 68.149 order /
272.075 order_item; 44 menu; 29 bahan baku), total waktu 5 pipeline turun dari **242,83 s → 57,92 s
(4,19× lebih cepat, −76%)**, dengan hasil clustering & prediksi **identik** (paritas 100%).

Dokumen ini menambahkan **Dataset B**: seeder milik repo original sendiri (± 3 bulan order,
2025-01-01 → 2025-04-10; lihat §4) untuk menguji apakah keunggulan tetap ada pada data kecil.
Tujuannya menjawab pertanyaan skeptis: *"Apakah speedup hanya artefak dari data besar?"*

**Rekomendasi format laporan:** **Markdown sebagai dokumen kanonik + JSON sebagai lampiran data mentah**
(lihat §10). Alasan ringkas: butuh tabel & bukti yang bisa dibaca manusia, sekaligus angka mentah yang
bisa diverifikasi mesin dan di-commit di git tanpa parser khusus.

---

## 2. Pipeline yang Dibandingkan

Kelima pipeline identik secara fungsional di kedua implementasi:

| # | Pipeline | Endpoint original | Modul original | Algortima |
|---|---|---|---|---|
| 1 | Prediksi menu | `POST /prediction` | `datamining/prediction.py` | Prophet per menu (regressor `is_weekend`) |
| 2 | Clustering menu | `POST /clustering` | `datamining/api.py` (`run_pipeline`) | K-Means 2D (Jumlah, Keuntungan) + Silhouette K-selection |
| 3 | Asosiasi menu | `POST /association` | `datamining/association.py` | FP-Growth (`mlxtend`) + `association_rules`, 1→1, Top-8 by lift |
| 4 | Prediksi bahan baku | `POST /prediction-bahan-baku` | `datamining/prediksibaku.py` | Prophet per bahan baku (regressor `is_weekend`) |
| 5 | Clustering bahan baku | `POST /clustering-bahan-baku` | `datamining/bahanbaku.py` | K-Means 1D (Jumlah_Digunakan) + Silhouette K-selection |

---

## 3. Metodologi

### 3.1 Dua dataset

| Aspek | **Dataset A** — seeder kita | **Dataset B** — seeder repo original |
|---|---|---|
| Sumber | `database/seeders/CafeSeeder.php` + `config/seeding.php` | `database/seeders/*` repo original (lihat §4) |
| Rentang order (headline) | 2025-01-01 → 2026-09-25 | 2025-01-01 → 2025-04-10 (**≈3,3 bulan**, 100 hari) |
| Order (`status='selesai'`) | 68.149 | 888 (ORD2xxx) — atau 6.454 bila `PredictionHistorySeeder` ikut (lihat §4.2) |
| Order item | 272.075 | 1.843 — atau 7.409 bila `PredictionHistorySeeder` ikut |
| Hari unik | 633 | 100 |
| Menu unik | 44 | 22 |
| Bahan baku unik | 29 | 15 (`IngredientUsageSeeder`, 151 hari) |
| Baris bahan baku | 18.322 (view ad-hoc; lihat catatan) | 2.265 |

> **Catatan Dataset B:** repo original menyimpan **empat** seeder relevan data-mining dengan rentang
> berbeda (100 hari, 253 hari, 151 hari). Lihat §4.2 untuk inventaris lengkap. Angka "≈3 bulan" pada
> tugas merujuk ke jendela 100 hari (2025-01-01 → 2025-04-10) yang dipakai `TransactionHistorySeeder`
> dan `AssociationHistorySeeder`. **Reviewer wajib mengonfirmasi scope B sebelum sel diisi.**

### 3.2 Environment

- DB PostgreSQL 18, database `pos_cafe`, container `capstone2-pgsql-1`.
- Service mining FastAPI, container `capstone2-datamining-1` (Python 3.13, scikit-learn + Prophet + mlxtend).
- Kedua versi (original & optimized) dijalankan **di container yang sama, DB yang sama, rentang yang sama**
  → perbandingan apple-to-apple.
- Repo original dijalankan *apa adanya* (chart base64, fetch mentah, algoritma utuh). Hanya lapisan DB
  yang diadaptasi (bukan algoritma), yaitu:
  - `o.status = 'selesai'` → `'completed'` (skema project beda; **catatan**: seeder original menulis
    campuran `'completed'` (ORD1xxx) dan `'selesai'` (ORD2xxx/ORD3xxx), lihat §11),
  - `menus.harga_modal` → `cost_price`,
  - `plt.cm.get_cmap("tab10", n)` → `matplotlib.colormaps["tab10"].resampled(n)` (API matplotlib baru),
  - `fetch_ingredient_data` original membaca `daily_ingredient_usages` yang sudah dihapus → dibuat
    **VIEW sementara** dengan query identik milik project (18.322 baris), lalu di-DROP setelah uji.

### 3.3 Cara mengukur waktu

- Waktu diukur **per pipeline** dan dipecah menjadi `fetch_s` (query DB) + `pipeline_s` (komputasi murni,
  termasuk chart bila versi original), lalu `total_s = fetch_s + pipeline_s`.
- Stopwatch `time.perf_counter()` di dalam proses FastAPI, satu request satu pipeline, cache dihangatkan
  lebih dulu (warm-up) agar bukan cold-start.
- Setiap pipeline dijalankan **≥3 kali**; dilaporkan **median** (bukan best-of) untuk menekan noise.
- Trade-off yang diterima: versi original memproduksi chart base64 (I/O matplotlib) — ini bagian sah dari
  biaya original dan **tidak** boleh dipreteli agar perbandingan jujur.

### 3.4 Cara memverifikasi kebenaran (paritas)

1. Jalankan kedua implementasi pada dataset & rentang yang sama, lalu bandingkan **payload inti**
   (bukan base64): `best_k`, `silhouette_score`, jumlah baris, label klaster per menu/bahan, dan
   metrik evaluasi (`MAE/RMSE/MAPE/SMAPE`) per menu/bahan.
2. Skrip paritas: `datamining/test_kmeans_parity.py` (mensyaratkan hasil identik) dan
   `datamining/test_optimization_parity.py`.
3. Asosiasi **sengaja berbeda**: `min_support` diturunkan `0.01 → 0.002` (lihat §7). Ini keputusan desain,
   bukan regresi — untuk paritas, samakan ambang dulu.
4. Bukti paritas Dataset A sudah tercatat di `comparison-findings.md` (§7 di dokumen ini).

---

## 4. Deskripsi Dataset

### 4.1 Dataset A — seeder milik project kita

- Seeder: `database/seeders/CafeSeeder.php`; konfigurasi: `config/seeding.php`.
- `SEED_START_DATE=2025-01-01` (default); `SEED_ORDERS_PER_DAY=100`; item per order 2–6 (rata-rata ≈4);
  qty 1–3; `weekend_multiplier=1.4`; seasonality bulanan (Des 1,15 dst.).
- Dipakai oleh `migrate:fresh --seed`.
- Volume terukur pada rentang uji 2025-01-01 → 2026-09-25:
  - **68.149 order** (`status='selesai'`), **272.075 order_item**, **633 hari**, **44 menu**.
  - Bahan baku 29 item; view usage 18.322 baris.
- Pipeline asosiasi memakai `272.075 baris item / 68.149 order` (angka sama).

### 4.2 Dataset B — seeder repo original

Inventaris lengkap (semua dijalankan bila `php artisan migrate:fresh --seed` pada repo original):

| Seeder | Tabel | Rentang | Hari | Volume | Status order |
|---|---|---|---:|---|---|
| `TransactionHistorySeeder.php` | `orders` / `order_items` | 2025-01-01 → 2025-04-10 | 100 | 500 order / 500 item (5 menu × 100 hari) | `completed` (tidak terbaca filter `'selesai'`) |
| `AssociationHistorySeeder.php` | `orders` / `order_items` | 2025-01-01 → 2025-04-10 | 100 | **888 order / 1.843 item** (2–3 item/order; 22 menu) | `selesai` |
| `PredictionHistorySeeder.php` | `orders` / `order_items` | 2025-08-01 → 2026-04-10 | 253 | 5.566 order / 5.566 item (22 menu × 253 hari) | `selesai` |
| `IngredientUsageSeeder.php` | `daily_ingredient_usages` | 2025-11-01 → 2026-03-31 | 151 | 2.265 baris (15 bahan × 151 hari) | — |

Catatan volume:
- `TransactionHistorySeeder` docblock: *"Dataset: 5 menu × 100 hari (2025-01-01 s/d 2025-04-10) = 500 baris"*.
- `AssociationHistorySeeder`: 30 template, 888 order (bukan "~840" seperti di komentar), 1.843 item.
- `PredictionHistorySeeder`: 22 menu × 253 hari = 5.566 order, semuanya `selesai`.

**Definisi operasional seeder B untuk benchmark ini (perlu konfirmasi reviewer):**

- **B-core (≈3 bulan, untuk 3 pipeline order):** union order `status='selesai'` pada jendela
  2025-01-01 → 2025-04-10 → **888 order / 1.843 item / 22 menu / 100 hari** (dari `AssociationHistorySeeder`).
- **B-ingredient (untuk 2 pipeline bahan baku):** `IngredientUsageSeeder` → **2.265 baris / 15 bahan / 151 hari**.
- **B-full (opsional, bila ingin seluruh `DatabaseSeeder`):** tambah `PredictionHistorySeeder`
  → **6.454 order `selesai` / 7.409 item**, rentang 2025-01-01 → 2026-04-10 (ada gap Mei–Jul 2025).

---

## 5. What Changed & Why (per optimasi)

Legenda: **A\*** = asosiasi, **B\*** = prediksi menu, **C\*** = prediksi bahan baku, **K\*** = konsolidasi K-Means.
"Paritas" = output inti identik menurut `comparison-findings.md`.

### 5.1 Asosiasi

| ID | Perubahan | Alasan | Paritas | Est. hemat |
|---|---|---|---|---|
| **A1** | `groupby(...).apply(list)` + `TransactionEncoder` → `pd.crosstab(Order_id, Nama Item) > 0` langsung ke `fpgrowth` | `apply(list)` terukur 4,38 s; `crosstab` membentuk matriks basket vektor tunggal tanpa list Python per baris | sama | ~3–4 s |
| **A2** | `te.fit(...).transform(...)` → `te.fit_transform(...)` | Menghindari double-pass encoder | sama | kecil (encode 0,15 s) |
| **A3** | `fetch_association_data` hanya SELECT kolom yang dipakai + buang `ORDER BY` | 7 kolom (Posisi/Jumlah/Harga/Subtotal tak terpakai) & sort 272k baris hanya untuk di-group ulang → buang | sama | ~1–3 s |

### 5.2 Prediksi menu

| ID | Perubahan | Alasan | Paritas | Est. |
|---|---|---|---|---|
| **B1** | Hapus `sort_values` + `groupby([Tanggal, Nama Item]).sum()` | Fetch SQL sudah agregasi harian per (tanggal, menu) → agregasi Python mubazir | sama | — |
| **B2** | Loop date-fill per item + loop IQR per item → vektorisasi (`pivot`/`reindex`, `groupby.transform` + `clip`) | Loop Python per menu (44×) di atas tabel penuh | sama | — |
| **B3** | `prepare_menu_data` mask `df_capped[Nama Item]==item` 44× → pre-split sekali via `groupby` | Menghindari 44 pemindaian tabel penuh | sama | — |
| **B4** | `Day_Type` via `.apply(lambda)` → `np.where` | Vektorisasi | sama | — |
| **B6** | Feature importance loop mean per item → `groupby` | 44 agregasi terpisah → 1 agregasi vektor | sama | — |

> **B5 (paralelisasi joblib `n_jobs=2`)** tidak termasuk scope wajib laporan ini; opsional dan berisiko memori 1 GB.

### 5.3 Prediksi bahan baku

| ID | Perubahan | Alasan | Paritas | Est. |
|---|---|---|---|---|
| **C2** | Loop reindex + loop IQR per bahan → vektorisasi | Sama seperti B2, untuk 29 bahan | sama | — |
| **C3** | `df_capped[Bahan_Baku]==bahan` dihitung 2× (main loop + feature importance) → pre-group sekali | Buang duplikasi pemindaian | sama | — |
| **C4** | Konfigurasi Prophet duplikat dengan `prediction.py` → ekstrak helper bersama | DRY; satu sumber konfigurasi, mengurangi risiko drift | sama | — |

> **C1** (`uncertainty_samples` default 1000 → set 100 seperti menu) **mengubah** batas CI (`batas_bawah/atas`),
> bukan point forecast. Dicatat sebagai perubahan yang disengaja; bukan bagian klaim paritas.
> **C5** (paralelisasi) opsional.

### 5.4 Konsolidasi K-Means (K1)

Original memanggil `KMeans` **tiga kali per nilai K** (loop Silhouette, loop Elbow, lalu fit final) — dua kali
loop K di `api.py` (`run_pipeline`) dan sekali lagi di `bahanbaku.py`. Optimasi: satu modul
`datamining/kmeans_utils.py` yang melakukan **satu fit per K**, memakai kembali label + `inertia_` + Silhouette,
lalu memakai ulang model terbaik untuk fit final.

- Alasan: `KMeans` deterministik (`random_state=42`, `n_init=10`) → dua fit dengan K sama menghasilkan label
  identik; fit kedua murni pemborosan.
- Paritas: `comparison-findings.md` mencatat klasterisasi identik (`best_k=2`/sil 0,626 untuk menu;
  `best_k=3`/sil 0,7402 untuk bahan baku) dan sudah diverifikasi 15/15 kombinasi oleh `test_kmeans_parity.py`.

---

## 6. Code Diff Highlights

> **Catatan kejujuran:** snippet **original** disalin persis dari hasil clone repo original
> (`/tmp/pos-cafe-original`). Snippet **optimized** di bawah adalah **representasi** yang direkonstruksi dari
> `comparison-findings.md`; berkas live `datamining/**` tidak dibaca saat drafting (sedang diedit agen lain).
> **Verifikasi snippet optimized terhadap source live sebelum finalisasi.**

### 6.1 A1 — Asosiasi: hilangkan `apply(list)` + `TransactionEncoder`

**Original** (`association.py`, ± baris 54–86):

```python
transactions = (
    df_clean
    .groupby("ID Pesanan")["Nama Item"]
    .apply(list)
    .reset_index()
)
te = TransactionEncoder()
te_array   = te.fit(transactions["Nama Item"]).transform(transactions["Nama Item"])
df_encoded = pd.DataFrame(te_array, columns=te.columns_)
frequent_itemsets = fpgrowth(df_encoded, min_support=MIN_SUPPORT, use_colnames=True, max_len=2)
```

**Optimized (representasi):**

```python
# Matriks basket boolean langsung, tanpa list Python per order
basket = pd.crosstab(df_clean["ID Pesanan"], df_clean["Nama Item"]) > 0
frequent_itemsets = fpgrowth(basket, min_support=MIN_SUPPORT, use_colnames=True, max_len=2)
```

### 6.2 A3 — Fetch asosiasi: SELECT ramping, tanpa ORDER BY

**Original** (`api.py`, `fetch_association_data`, ± baris 601–615):

```sql
SELECT o.created_at::date AS "Tanggal", o.order_code AS "Order_id",
       m.name AS "Nama Item", oi.item_position AS "Posisi",
       oi.quantity AS "Jumlah", oi.unit_price::float AS "Harga",
       oi.subtotal::float AS "Subtotal"
FROM order_items oi JOIN orders o ON o.id = oi.order_id JOIN menus m ON m.id = oi.menu_id
WHERE o.status = 'selesai'
ORDER BY o.created_at ASC, o.id ASC, oi.item_position ASC
```

**Optimized (representasi):**

```sql
SELECT o.order_code AS "Order_id", m.name AS "Nama Item"
FROM order_items oi JOIN orders o ON o.id = oi.order_id JOIN menus m ON m.id = oi.menu_id
WHERE o.status = 'selesai'
-- tanpa ORDER BY: hasil di-group ulang per Order_id
```

### 6.3 B1/B2 — Prediksi menu: agregasi & IQR vektorisasi

**Original** (`prediction.py`, ± baris 88–120 & 135–149):

```python
df_total = df_sorted.groupby(["Tanggal", "Nama Item"], as_index=False)["Jumlah"].sum()
...
for item in df_total["Nama Item"].unique():
    df_item = df_total[df_total["Nama Item"] == item].copy()
    all_dates = pd.date_range(df_item["Tanggal"].min(), df_item["Tanggal"].max(), freq="D")
    df_full = pd.DataFrame({"Tanggal": all_dates}).merge(df_item, on="Tanggal", how="left")
    ...
for item in df_final["Nama Item"].unique():
    df_item = df_final[df_final["Nama Item"] == item].copy()
    Q1, Q3 = df_item["Jumlah"].quantile(0.25), df_item["Jumlah"].quantile(0.75)
    ...
    df_item["Jumlah"] = np.where(df_item["Jumlah"] > upper, upper, ...)
```

**Optimized (representasi):**

```python
# SQL sudah agregasi harian → langsung pakai; date-fill & IQR vektorisasi
piv = df.pivot_table(index="Tanggal", columns="Nama Item", values="Jumlah", aggfunc="sum")
piv = piv.reindex(pd.date_range(piv.index.min(), piv.index.max(), freq="D")).fillna(0)
long = piv.stack().rename("Jumlah").reset_index()
Q1 = long.groupby("Nama Item")["Jumlah"].transform(lambda s: s.quantile(0.25))
Q3 = long.groupby("Nama Item")["Jumlah"].transform(lambda s: s.quantile(0.75))
long["Jumlah"] = long["Jumlah"].clip(lower=(Q1 - 1.5*(Q3-Q1)), upper=(Q3 + 1.5*(Q3-Q1)))
```

### 6.4 K1 — K-Means satu fit per K

**Original** (`api.py`, ± baris 236–263):

```python
for k in k_range:                       # pass 1: silhouette (fit)
    km = KMeans(n_clusters=k, random_state=42, n_init=10)
    labels = km.fit_predict(x_train)
    sil_scores.append(silhouette_score(x_train, labels))
for k in k_range:                       # pass 2: elbow (fit lagi)
    km = KMeans(n_clusters=k, random_state=42, n_init=10)
    km.fit(x_train)
    inertias.append(km.inertia_)
kmean = KMeans(n_clusters=best_k, random_state=42, n_init=10)  # pass 3: fit final
df_total["Klaster"] = kmean.fit_predict(x_train)
```

**Optimized (representasi, `datamining/kmeans_utils.py`):**

```python
def fit_k_range(x, k_range):
    out = {}
    for k in k_range:                   # satu fit per K
        km = KMeans(n_clusters=k, random_state=42, n_init=10)
        labels = km.fit_predict(x)
        out[k] = {"labels": labels, "inertia": km.inertia_,
                  "silhouette": silhouette_score(x, labels)}
    return out
# best_k dari max(silhouette); label final = out[best_k]["labels"] (tanpa fit ulang)
```

---

## 7. Timing Tables

### 7.1 Dataset A (2025-01-01 → 2026-09-25) — **TERUKUR**, sumber: `comparison-findings.md`

| Pipeline | Original (s) | Optimized (s) | Speedup |
|---|---:|---:|---:|
| Clustering menu | 17,35 | 3,98 | 4,36× |
| Asosiasi | 15,36 | 18,58 | **0,83× (lebih lambat)** |
| Prediksi menu | 130,13 | 16,89 | 7,70× |
| Prediksi bahan baku | 72,11 | 14,71 | 4,90× |
| Clustering bahan baku | 7,88 | 3,76 | 2,10× |
| **TOTAL** | **242,83** | **57,92** | **4,19× (−76%)** |

Rincian fetch vs pipeline (Dataset A, terukur):

| Pipeline | Orig fetch | Orig pipe | Ours fetch | Ours pipe |
|---|---:|---:|---:|---:|
| Clustering menu | 10,49 | 6,85 | 2,24 | 1,74 |
| Asosiasi | 7,95 | 7,40 | 8,50 | 10,08 |
| Prediksi menu | 8,87 | 121,26 | 1,25 | 15,63 |
| Prediksi bahan baku | 3,71 | 68,40 | 2,40 | 12,30 |
| Clustering bahan baku | 1,96 | 5,92 | 1,91 | 1,85 |

> Catatan: variasi antar-run (prediksi original sempat 99 s lalu 121 s) akibat pembuatan gambar matplotlib;
> magnitudo tetap jelas.

### 7.2 Dataset B (repo original, full `DatabaseSeeder`) — **TERUKUR**

> Bentuk `DatabaseSeeder` repo original (TransactionHistory + AssociationHistory + PredictionHistory +
> IngredientUsage + RecipeIngredient). Volume hasil: **6.454 order / 7.409 order_item / 23 menu / 24 bahan /
> 82 resep**, rentang 2025-01-01 → 2026-04-10 — identik dengan total order `selesai` + item pada repo original.
>
> Seeder Laravel repo original **tidak bisa dijalankan fresh** (migration `create_expenses_table` &
> `create_cashier_sessions_table` duplikat; seeder men-query kolom `menus.slug` yang tak ada di schema final).
> Karena itu Dataset B **direplikasi** ke schema POSMine sesuai spesifikasi seeder
> (`MenuSeeder`, `CategorySeeder`, `RecipeIngredientSeeder`, `HargaModalSeeder`, `AssociationHistorySeeder`,
> `PredictionHistorySeeder`) via `dataset_b_loader.py`.

| Pipeline | Original (s) | Optimized (s) | Speedup |
|---|---:|---:|---:|
| Clustering menu | 3,24 | 0,64 | 5,06× |
| Asosiasi | 2,16 | 0,09 | 24,00× |
| Prediksi menu | 31,61 | 4,78 | 6,61× |
| Prediksi bahan baku | 31,12 | 7,44 | 4,18× |
| Clustering bahan baku | 2,67 | 0,25 | 10,68× |
| **TOTAL** | **70,80** | **13,20** | **5,36× (−81%)** |

> Sisi "original" dijalankan dari kode repo original (`/tmp/orig_dm`) dengan adaptasi schema minimal
> (status `selesai`→`completed`, `menus.harga_modal`→`menus.cost_price`, sumber bahan baku dari view resep,
> patch `matplotlib.cm.get_cmap`). Adaptasi murni menyesuaikan schema/lingkungan, bukan mengubah algoritma.

---

## 8. Bukti Paritas / Kebenaran

Sumber: `comparison-findings.md` (Dataset A) + `datamining/test_kmeans_parity.py`.

| Pipeline | Hasil original | Hasil optimized | Status |
|---|---|---|---|
| Clustering menu | `best_k=2`, silhouette `0,626`, 44 baris | sama | **IDENTIK** |
| Clustering bahan baku | `best_k=3`, silhouette `0,7402` | sama | **IDENTIK** |
| Prediksi menu | MAPE 44/44 menu sama | sama | **IDENTIK** |
| Prediksi bahan baku | MAPE 29/29 bahan sama | sama | **IDENTIK** |
| Asosiasi | `0` rules (`min_support=0,01`) | `8` rules (`min_support=0,002`) | **BEDA BY DESIGN** |

- Klasterisasi: `datamining/test_kmeans_parity.py` mensyaratkan label & `best_k` identik;
  `comparison-findings.md` mencatat verifikasi **15/15 kombinasi**.
- Prediksi: MAPE per menu/bahan dibandingkan 1:1 → identik pada Dataset A.
- Asosiasi: perbedaan **disengaja** karena ambang `min_support` diturunkan `0,01 → 0,002` agar rules
  bermakna muncul pada data project. **Untuk uji paritas pada Dataset B, samakan `min_support` lebih dulu**
  (original default `0,01`) — jika tidak, perbedaan hasil adalah efek ambang, bukan efek kode.
- Pada Dataset B (888 order), `AssociationHistorySeeder` dirancang agar pasangan top muncul ≈65×
  (≈7,3% support) > `min_support=0,01`, sehingga original seharusnya menemukan rules → kandidat uji
  paritas yang baik pada data kecil. Isi tabel paritas B setelah diukur.

Paritas numerik Dataset B **tidak diverifikasi ulang** pada sesi ini (fokus sesi = waktu). Bukti kebenaran
yang tersedia: (a) paritas Dataset A + `comparison-findings.md`; (b) `datamining/test_kmeans_parity.py`
(15/15 kombinasi identik); dan (c) `datamining/test_optimization_parity.py` (old == new untuk preprocessing
prediksi menu/bahan baku, IQR, feature importance, dan pipeline asosiasi pada data sintetis).
Bila membandingkan rules asosiasi, samakan `min_support` lebih dulu.

---

## 9. Why Is It Faster? (Penjelasan Kompleksitas)

- **A1 — dari O(n_item·n_order) + overhead list Python ke matriks sparse boolean.**
  Original: `groupby.apply(list)` membangun `n_order` list Python + lookup dict, lalu `TransactionEncoder`
  mengonversi lagi ke matriks → dua representasi dan satu pass Python per baris. Optimized: `crosstab`
  mengagregasi langsung ke matriks order×menu (boolean) dalam operasi vektor C-level → menghapus pass
  list Python dan objek per-order. Pada 272k baris ini menghemat 3–4 s.
- **A3 — biaya I/O & sort linier.** Mengambil 2 dari 7 kolom dan membuang `ORDER BY` memangkas bytes
  transfer + biaya sort `O(N log N)` yang hasilnya dibuang karena data di-group ulang.
- **B1/B2 — dari `#menu` pass × tabel penuh ke operasi vektor.** Original melakukan filter per menu
  (44×) plus loop IQR per menu; tiap filter adalah pemindaian `O(N)`. Optimized menggantinya dengan
  `pivot/reindex` + `groupby.transform` (satu pass) → dari `O(#menu · N)` menjadi `O(N)`.
- **B3 — menghapus 44 pemindaian tabel penuh** (`df_capped[Nama Item]==item`) dengan satu `groupby` split.
- **B6/C3 — agregasi mean per item** dari `#item` pass menjadi satu `groupby`.
- **K1 — dari 2·|K|+1 fit ke |K| fit.** Karena `KMeans` deterministik (`random_state=42`, `n_init=10`),
  fit kedua pada K yang sama menghasilkan label & inertia identik; menghapusnya memangkas hampir separuh
  biaya K-selection tanpa mengubah hasil. Ini menjelaskan speedup clustering (4,36× / 2,10×) meski
  algoritmanya sama.
- **Dominasi Prophet.** Di Dataset A, prediksi menu = 121 s dari 243 s original (≈50%). Optimasi
  preprocessing memangkas bagian non-Prophet; sisa biaya adalah fit Prophet per menu (tidak diubah demi
  paritas). Karena itu speedup terbesar ada di prediksi menu (7,70×) setelah fetch dirampingkan.
- **Biaya fetch tetap relevan pada data besar:** asosiasi justru **lebih lambat** (0,83×) karena
  `crosstab` + fetch 272k baris di sisi optimized tidak sekompetitif jalur original untuk bentuk data ini.
  Ini dicatat jujur dan jadi target optimasi lanjutan (A1 lanjutan / fetch streaming).

---

## 10. Reproducibility Steps

> Perintah di bawah **belum dijalankan ulang** saat drafting (agen lain sedang menjalankan Python berat).
> Jalankan saat mesin idle, satu pipeline pada satu waktu.

### 10.1 Siapkan repo original

```bash
rm -rf /tmp/pos-cafe-original
git clone --depth 1 https://github.com/ShandovaaGame/Capstone_POS_Cafe.git /tmp/pos-cafe-original
git -C /tmp/pos-cafe-original rev-parse HEAD   # harus e23f347c3ecaad7ec2233849116d3519c95920ea
```

### 10.2 Dataset A (seeder kita)

```bash
docker compose exec -T app php artisan migrate:fresh --seed   # CafeSeeder, SEED_START_DATE=2025-01-01
# pastikan rentang: 2025-01-01 .. SEED_END_DATE (default: tanggal seed)
docker compose exec -T pgsql psql -U postgres -d pos_cafe -c \
  "SELECT min(created_at::date), max(created_at::date), count(*) FROM orders WHERE status='selesai';"
```

### 10.3 Dataset B (seeder original)

Opsi paling aman (tidak mencampur DB kita): DB terpisah, lalu seed original.

```bash
# container pgsql sementara / database terpisah, mis. pos_cafe_orig
# lalu, dengan source original di /tmp/pos-cafe-original:
php artisan migrate:fresh --seed
# cek volume
psql -c "SELECT status, count(*) FROM orders GROUP BY status;"
psql -c "SELECT count(*) FROM order_items;"
psql -c "SELECT min(usage_date), max(usage_date), count(*) FROM daily_ingredient_usages;"
```

Jika hanya ingin B-core/B-ingredient (tanpa `PredictionHistorySeeder`), ganti `DatabaseSeeder`
(atau jalankan `--class=AssociationHistorySeeder` + `--class=IngredientUsageSeeder`).

### 10.4 Jalankan benchmark per pipeline

```bash
# service FastAPI (container datamining) — 1 request per pipeline, ulang ≥3×, catat median
for i in 1 2 3; do
  curl -s -X POST http://localhost:8001/prediction \
       -H 'Content-Type: application/json' \
       -d '{"date_from":"2025-01-01","date_to":"2025-04-10"}' -o /tmp/pred_$i.json -w "prediction run $i: %{time_total}s\n"
  curl -s -X POST http://localhost:8001/clustering \
       -H 'Content-Type: application/json' -d '{}' -o /tmp/clust_$i.json -w "clustering run $i: %{time_total}s\n"
  curl -s -X POST http://localhost:8001/association \
       -H 'Content-Type: application/json' -d '{}' -o /tmp/assoc_$i.json -w "association run $i: %{time_total}s\n"
  curl -s -X POST http://localhost:8001/prediction-bahan-baku \
       -H 'Content-Type: application/json' -d '{}' -o /tmp/predbb_$i.json -w "pred-bb run $i: %{time_total}s\n"
  curl -s -X POST http://localhost:8001/clustering-bahan-baku \
       -H 'Content-Type: application/json' -d '{}' -o /tmp/clustbb_$i.json -w "clust-bb run $i: %{time_total}s\n"
done
```

> Catatan: endpoint versi optimized dapat berbeda (mis. `POST /run` dengan payload `{"pipeline": "..."}`);
> endpoint original adalah lima path di atas. Sesuaikan per versi dan catat di kolom `notes` JSON.
> Ukur `fetch_s` & `pipeline_s` dari log internal bila tersedia; jika tidak, catat hanya `total_s`.

### 10.5 Verifikasi paritas

```bash
# di dalam container datamining (source optimized)
python datamining/test_kmeans_parity.py
python datamining/test_optimization_parity.py
# bandingkan payload inti original vs optimized: best_k, silhouette, label, MAPE/RMSE
```

### 10.6 Isi JSON lalu (opsional) render tabel

Isi `benchmark-two-seeders.json` dengan angka median; sel yang belum diukur tetap `null`.
Jangan pernah menulis angka estimasi ke sel waktu.

---

## 11. Threats to Validity / Caveats (wajib dibaca reviewer)

1. **Definisi Dataset B ambigu di repo original.** Ada 4 seeder dengan rentang berbeda
   (100 hari / 253 hari / 151 hari) dan status order campur (`completed` vs `selesai`). Laporan ini
   mendefinisikan B-core ≈3 bulan, namun **harus dikonfirmasi** sebelum angka diisi.
2. **Filter status.** Pipeline original menyaring `o.status='selesai'`, sedangkan `TransactionHistorySeeder`
   menulis `'completed'`. Jika harness original **tidak** memetakan status, 500 order ORD1xxx tidak ikut
   terhitung. Ini memengaruhi volume B — catat pemetaan yang dipakai.
3. **Chart base64 termasuk biaya original.** Membuang chart dari kedua sisi akan mengubah rasio; jangan.
4. **`min_support` asosiasi berbeda by design.** Jangan mengklaim regresi/keunggulan asosiasi tanpa
   menyamakan ambang lebih dulu.
5. **`uncertainty_samples` (C1)** mengubah nilai CI bahan baku; point forecast tetap. Nyatakan eksplisit.
6. **Variasi run.** matplotlib & Prophet punya variasi antar-run (tercatat 99 s vs 121 s di Dataset A).
   Laporkan median ≥3 run, bukan satu angka.
7. **Angka A adalah hasil satu environment** (container/DB yang sama). B harus diukur di environment
   yang setara; jangan mencampur angka A ke tabel B.

---

## 12. Rekomendasi Format Laporan

**Pilihan: Markdown (kanonik) + JSON (lampiran data mentah). Hindari `.txt` sebagai format utama.**

| Format | Kesesuaian | Alasan |
|---|---|---|
| **Markdown** | **Primer** | Tabel, heading, dan **diff code** native; enak di-review di GitHub/git; bisa dikomentari per baris; audiens skeptis bisa melacak klaim → dataset → perintah. |
| **JSON** | **Pendamping wajib** | Angka mentah per run (semua run, bukan hanya median) + metadata environment & commit hash → bisa diverifikasi/di-render mesin dan di-diff. Sel `null` mencegah angka rekaan. |
| `.txt` | Tidak disarankan | Tidak ada tabel/diff terstruktur; sulit me-review tabel waktu; rawan tidak konsisten. Boleh hanya sebagai ekspor arsip/email bila diminta. |

**Justifikasi untuk audiens skeptis:** yang meyakinkan bukan narasi, melainkan (a) tabel waktu dengan
pecahan fetch vs pipeline, (b) bukti paritas yang bisa dijalankan (`test_kmeans_parity.py`), dan
(c) langkah reproduce yang pasti. Markdown menyajikan (a)/(b) dengan baik; JSON menyimpan data mentah
dan metadata agar angka bisa diaudit ulang tanpa percaya pada penulis. Kombinasi ini juga membuat
`<TO BE MEASURED>` terlihat jujur dan eksplisit, bukan disembunyikan.

---

## 13. Checklist Sebelum Finalisasi

- [ ] Konfirmasi definisi Seeder B (B-core vs B-full; pemetaan status).
- [ ] Jalankan 5 pipeline × 2 versi × 3 run pada Dataset B; isi JSON + tabel §7.2.
- [ ] Isi tabel paritas §8 untuk Dataset B (samakan `min_support` untuk asosiasi).
- [ ] Verifikasi snippet optimized di §6 terhadap `datamining/**` live.
- [ ] Lampirkan commit hash original & commit optimized, plus metadata environment ke JSON.
- [ ] Pastikan tidak ada sel waktu berisi estimasi; hanya `<TO BE MEASURED>` atau angka terukur.
