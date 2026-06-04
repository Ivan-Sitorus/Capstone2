# Konteks Skripsi: Implementasi Sistem Manajemen Inventori pada POS W9 Cafe

## Data Diri

| | |
|---|---|
| **Nama** | Muhammad Nio Hastungkoro |
| **NIM** | 21120122140155 |
| **Prodi** | Teknik Komputer, Universitas Diponegoro |
| **Judul Skripsi** | Implementasi Sistem Manajemen Inventori pada Point of Sale W9 Cafe menggunakan Laravel dan Filament |
| **Pembimbing** | Yudi Eko Windarto, S.T., M.Kom. & Rinta Kridalukmana, S.Kom., M.T., Ph.D. |
| **Template Format** | `skripsi/contoh-skripsi.md` (Laporan TA Yuda Nadhika, 6 bab) |

---

## Struktur Bab (6 Bab, mengikuti template UNDIP)

```
BAB I   PENDAHULUAN
        1.1 Latar Belakang
        1.2 Rumusan Masalah
        1.3 Tujuan Penelitian
        1.4 Batasan Masalah
        1.5 Manfaat Penelitian
        1.6 Metodologi Penelitian
        1.7 Sistematika Penulisan

BAB II  TINJAUAN PUSTAKA
        2.1 Penelitian Terdahulu
        2.2 Landasan Teori

BAB III PERANCANGAN SISTEM
        3.1 Gambaran Umum dan Ruang Lingkup
        3.2 Analisis Kebutuhan Sistem
        3.3 Pemodelan Proses
        3.4 Perancangan Basis Data
        3.5 Perancangan Arsitektur Aplikasi
        3.6 Perancangan Antarmuka
        3.7 Rancangan Pengujian

BAB IV  IMPLEMENTASI SISTEM
        4.1 Lingkungan Implementasi
        4.2 Implementasi Basis Data
        4.3 Implementasi Service Layer
        4.4 Implementasi Admin Panel (Filament)
        4.5 Implementasi Integrasi

BAB V   PENGUJIAN DAN EVALUASI
        5.1 Pengujian White Box
        5.2 Pengujian Black Box
        5.3 Pengujian Integrasi
        5.4 Analisis Hasil Pengujian

BAB VI  PENUTUP
        6.1 Kesimpulan
        6.2 Saran
```

**Catatan:** Pemisahan BAB IV (Implementasi) dan BAB V (Pengujian) menjadi 2 bab terpisah adalah variasi yang wajar untuk topik implementasi software dengan pengujian ekstensif. Struktur 6 bab ini sudah terbukti lolos di UNDIP Teknik Komputer (referensi: Yuda Nadhika, 2026).

---

## Gambaran Proyek

Sistem POS Cafe berbasis web PWA untuk **W9 Cafe STIE Totalwin Semarang**.

**Tech Stack:**

| Layer | Teknologi |
|---|---|
| Framework Backend | Laravel 13 (PHP 8.5.6) |
| Database | PostgreSQL 18 |
| ORM | Eloquent |
| Admin Panel | Filament (Livewire-based) |
| Frontend Kasir/Pelanggan | React 18 + Inertia.js v2 |
| CSS | Bootstrap 5 |
| State Management | Zustand (cart) + IndexedDB (offline) |
| Build Tool | Vite 7 |
| Auth | Laravel Sanctum (session-based, multi-role) |

---

## Arsitektur Sistem Inventori

Sistem inventori W9 Cafe memiliki **dua jalur pelacakan stok paralel** dengan pola desain yang identik:

```
                    SISTEM INVENTORI W9 CAFE
                    ==========================

  ┌──────────────────────────────────────────────────────────────┐
  │                                                              │
  │  1. BAHAN BAKU (Recipe-Based)                               │
  │                                                              │
  │     Ingredient ──1:N──▶ IngredientBatch                      │
  │       │                     (quantity, expiry, received_at,  │
  │       │                      cost_per_unit, custom_order)    │
  │       │                                                     │
  │       ├──1:N──▶ StockMovement (IMMUTABLE, audit trail)     │
  │       ├──1:N──▶ StockAdjustment (manual correction)          │
  │       ├──1:N──▶ DailyIngredientUsage (agregasi harian)      │
  │       └──N:M──▶ Menu (via menu_ingredients pivot, resep)   │
  │                                                              │
  │  2. STOK MENU (Finished Product, tanpa resep)               │
  │                                                              │
  │     MenuStock ──1:1──▶ Menu                                  │
  │       │              (is_stock_calculated = false)           │
  │       ├──1:N──▶ MenuStockBatch                               │
  │       ├──1:N──▶ MenuStockMovement (IMMUTABLE)               │
  │       └──1:N──▶ MenuStockAdjustment                          │
  │                                                              │
  └──────────────────────────────────────────────────────────────┘
```

### Jalur 1: Bahan Baku (dengan resep)

Menu yang memiliki resep (misal: "Kopi Susu" = kopi + susu) akan mendebet stok **IngredientBatch** saat ada pesanan. Resep didefinisikan melalui tabel pivot `menu_ingredients` yang menghubungkan Menu ↔ Ingredient dengan kolom `quantity_used`.

### Jalur 2: Stok Menu (produk jadi tanpa resep)

Menu yang tidak memiliki resep (misal: "Teh Botol", produk kemasan) dilacak melalui **MenuStock** yang terhubung 1:1 dengan Menu. Auto-dibuat oleh `MenuObserver` ketika resep dihapus dari suatu menu.

### Strategi Deduksi Batch

Setiap Ingredient dan MenuStock memiliki kolom `batch_mode`:

| Mode | Deskripsi | Sort Order |
|------|-----------|------------|
| **FEFO** (default) | *First-Expiry-First-Out*, batch dengan expiry_date terdekat dikonsumsi duluan | `expiry_date ASC` |
| **FIFO** | *First-In-First-Out*, batch dengan received_at terlama dikonsumsi duluan | `received_at ASC` |
| **Custom** | Urutan manual | `custom_order ASC` |

Semua deduksi menggunakan **pessimistic locking** (`lockForUpdate()` dengan `ORDER BY id ASC`) untuk mencegah deadlock pada transaksi konkuren.

---

## Model Data (11 tabel)

### A. Sub-Sistem Bahan Baku

#### 1. `ingredients`, Master Bahan Baku

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | BIGINT PK | Auto-increment |
| name | VARCHAR(255) | Nama bahan |
| unit | ENUM | 'gram','kg','ml','liter','pcs','sachet','sdm','sdt' |
| low_stock_threshold | DECIMAL(12,2) | Default 0 |
| is_active | BOOLEAN | Default true |
| batch_mode | VARCHAR(255) | 'fefo' (default), 'fifo', atau 'custom', CHECK constraint |
| deleted_at | TIMESTAMP | Soft deletes |
| created_at, updated_at | TIMESTAMP | Laravel timestamps |

**Method penting:** `getTotalStock(): float`, sum quantity seluruh batch.

#### 2. `ingredient_batches`, Stok per Batch

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | BIGINT PK | |
| ingredient_id | BIGINT FK | → ingredients.id CASCADE DELETE |
| quantity | DECIMAL(12,2) | Qty saat ini di batch ini |
| expiry_date | DATE | Nullable, untuk FEFO |
| received_at | TIMESTAMP | Nullable, untuk FIFO |
| cost_per_unit | DECIMAL(12,2) | Default 0 |
| custom_order | INTEGER | Nullable, untuk custom mode |

**Index:** `(ingredient_id, expiry_date)`, `(received_at)`.  
**Note:** `$timestamps = false`.

#### 3. `menu_ingredients`, Pivot Resep

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | BIGINT PK | |
| menu_id | BIGINT FK | → menus.id CASCADE DELETE |
| ingredient_id | BIGINT FK | → ingredients.id CASCADE DELETE |
| quantity_used | DECIMAL(12,2) | Jumlah bahan per 1 unit menu |

**Unique:** `UNIQUE(menu_id, ingredient_id)`.  
**Note:** `$timestamps = false`.

#### 4. `stock_movements`, Audit Trail Bahan Baku (IMMUTABLE)

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | BIGINT PK | |
| ingredient_id | BIGINT FK | |
| ingredient_batch_id | BIGINT FK | Nullable |
| order_id, order_item_id | BIGINT FK | Nullable, sumber transaksi |
| stock_adjustment_id | BIGINT FK | Nullable |
| movement_type | ENUM | 'purchase','sale','waste','adjustment_increase','adjustment_decrease','correction' |
| source_type, source_id | VARCHAR | Polymorphic origin |
| quantity_before, quantity_change, quantity_after | DECIMAL(12,2) | Snapshot sebelum/sesudah |
| unit_cost | DECIMAL(12,2) | Nullable |
| recorded_by | BIGINT FK | → users.id NULL ON DELETE |
| created_at, updated_at | TIMESTAMP | |

**Business rule:** `booted()` throws `LogicException` pada `updating` dan `deleting`. **Append-only.**

#### 5. `stock_adjustments`, Penyesuaian Stok Manual

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | BIGINT PK | |
| ingredient_id | BIGINT FK | |
| adjustment_type | ENUM('increase','decrease') | |
| quantity | DECIMAL(12,2) | |
| quantity_before, quantity_after | DECIMAL(12,2) | Snapshot |
| reason | VARCHAR(255) | Nullable |
| reported_by | BIGINT FK | → users.id NULL ON DELETE |
| adjusted_at | TIMESTAMP | Default CURRENT_TIMESTAMP |

#### 6. `daily_ingredient_usages`, Agregasi Pemakaian Harian

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | BIGINT PK | |
| usage_date | DATE | |
| ingredient_id | BIGINT FK | → ingredients.id CASCADE |
| ingredient_name | VARCHAR(255) | **Denormalized** |
| unit | VARCHAR(20) | **Denormalized** |
| jumlah_digunakan | DECIMAL(12,2) | Default 0 |

**Unique:** `UNIQUE(usage_date, ingredient_id)`, satu baris per ingredient per hari.

#### 7. `stock_reports`, Workflow Approval Perubahan Stok

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | BIGINT PK | |
| ingredient_id | BIGINT FK | |
| reported_by | BIGINT FK | → users.id CASCADE DELETE |
| report_type | ENUM('increase','decrease') | |
| quantity | DECIMAL(12,2) | |
| quantity_before, quantity_after | DECIMAL(12,2) | Nullable |
| reason | TEXT | |
| status | ENUM('pending','approved','rejected') | Default 'pending' |
| reviewed_by | BIGINT FK | Nullable |
| rejection_note | TEXT | Nullable |
| reviewed_at | TIMESTAMP | Nullable |

### B. Sub-Sistem Stok Menu

#### 8. `menu_stocks`, Master Stok Menu (1:1 dengan Menu)

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | BIGINT PK | |
| menu_id | BIGINT FK | → menus.id CASCADE DELETE, UNIQUE |
| unit | ENUM | Sama seperti ingredients |
| low_stock_threshold | DECIMAL(12,2) | Default 0 |
| is_active | BOOLEAN | Default true |
| batch_mode | VARCHAR(255) | 'fefo' / 'fifo' / 'custom' |
| deleted_at | TIMESTAMP | Soft deletes |

#### 9. `menu_stock_batches`, Batch Stok Menu

Sama struktur dengan `ingredient_batches` (FK ke `menu_stocks.id`). `$timestamps = false`.

#### 10. `menu_stock_adjustments`, Penyesuaian Stok Menu

Sama struktur dengan `stock_adjustments` (FK ke `menu_stocks.id`).

#### 11. `menu_stock_movements`, Audit Trail Stok Menu (IMMUTABLE)

Sama struktur dengan `stock_movements` (FK ke `menu_stocks.id` dan `menu_stock_batches.id`). **Append-only.**

---

### Relasi Antar Tabel (ERD Ringkas)

```
menus ──1:1──▶ menu_stocks ──1:N──▶ menu_stock_batches
  │                                  │
  │                                  ▼
  │                            menu_stock_adjustments
  │                                  │
  │                                  ▼
  │                            menu_stock_movements ◀── orders
  │
  ├──N:M──▶ menu_ingredients ──N:1──▶ ingredients ──1:N──▶ ingredient_batches
  │                                                          │
  │                     daily_ingredient_usages ──N:1──▶      │
  │                                                          ▼
  │                                                    stock_adjustments
  │                                                          │
  │                                                          ▼
  │                                                    stock_movements ◀── orders
  │                                                          │
  │                                                    stock_reports (approval)
```

---

## Service Layer (4 services)

### InventoryService (`app/Services/InventoryService.php`, ~453 baris)

Mesin utama deduksi stok bahan baku.

| Method | Akses | Parameter | Return |
|--------|-------|-----------|--------|
| `processSaleForOrder()` | public | `Order $order, ?int $recordedBy = null, bool $skipStockValidation = false` | `array` |
| `decreaseStockForOrder()` | public | `array $items, bool $skipStockValidation = false` | `array` |
| `decreaseStockForIngredient()` | public | `int $ingredientId, float $quantity, array $context = []` | `array` |
| `canFulfillOrder()` | public | `array $items` | `['can_fulfill' => bool, 'insufficient_ingredients' => [...]]` |
| `deductIngredientStock()` | private | `int $ingredientId, float $requiredQuantity, array $context = [], bool $skipStockValidation = false` | `array` |
| `recordDailyIngredientUsage()` | private | `Ingredient $ingredient, float $usedQuantity, ?string $usageDate = null` | `void` |

**Key behaviors:**
- Dual-path: menu dengan resep → deduksi IngredientBatch; menu tanpa resep → delegasi ke MenuStockService
- FIFO/FEFO/Custom batch ordering berdasarkan `Ingredient::batch_mode`
- Pessimistic locking: `lockForUpdate()` pada batch rows
- Skip-validation: `skipStockValidation=true` memungkinkan stok negatif (offline sync)
- Idempotent: skip jika StockMovement dengan `movement_type='sale'` dan `order_id` sama sudah ada
- Broadcast: fire `StockUpdated` event setelah transaksi commit

### MenuStockService (`app/Services/MenuStockService.php`, ~254 baris)

Deduksi stok untuk produk jadi (menu tanpa resep).

| Method | Akses | Parameter | Return |
|--------|-------|-----------|--------|
| `deductMenuStockBatch()` | public | `int $menuStockId, float $requiredQuantity, array $context = [], bool $skipStockValidation = false` | `array` |
| `processSaleForOrderMenuStock()` | public | `Order $order, ?int $recordedBy = null` | `array` |
| `canFulfillOrderMenuStock()` | public | `array $items` | `array` |

### StockReconciliationService (`app/Services/StockReconciliationService.php`)

Penyesuaian manual stok bahan baku.

| Method | Parameter | Return |
|--------|-----------|--------|
| `createManualAdjustment()` | `int $ingredientId, float $quantity, string $adjustmentType, string $reason, ?int $reportedBy = null, ?string $adjustedAt = null` | `StockAdjustment` |

### MenuStockReconciliationService (`app/Services/MenuStockReconciliationService.php`)

Penyesuaian manual stok menu.

| Method | Parameter | Return |
|--------|-----------|--------|
| `createManualAdjustment()` | `int $menuStockId, float $quantity, string $adjustmentType, string $reason, ?int $reportedBy = null, ?string $adjustedAt = null` | `MenuStockAdjustment` |

---

## Observers (2 file)

### `MenuObserver`
- `created()`: Auto-create MenuStock untuk menu non-recipe (`is_stock_calculated = false`)
- `saved()`: Handle toggle `is_stock_calculated`, create MenuStock idempotently
- `deleting()`: Cascade soft-delete MenuStock
- `restored()`: Restore soft-deleted MenuStock

### `MenuIngredientObserver`
- `created()`, `updated()`, `deleted()`: Panggil `Menu::refreshStockCalculatedFlag()` untuk sinkronisasi flag

---

## Filament Admin Panel (grup Inventori)

Panel admin dikonfigurasi di `AdminPanelProvider.php`:
- **Panel ID:** `admin`, **Path:** `/admin`, **Auth guard:** `admin`
- **SPA Mode:** Enabled
- **Navigation Group Inventori:** Berisi 2 tabbed pages + 1 resource visible + 5 hidden resources

### Tabbed Pages

| Page | Route | Konten Tab |
|------|-------|------------|
| **StokPage** | `/admin/stok` | Tab "Bahan Baku" (ListStocks) + Tab "Menu" (ListMenuStocks) |
| **AdjustmentsPage** | `/admin/penyesuaian-stok` | Tab "Bahan Baku" (ListStockAdjustments) + Tab "Menu" (ListMenuStockAdjustments) |

### Resources

| Resource | Model | Fungsi |
|----------|-------|--------|
| **StockResource** | `Ingredient` | CRUD bahan baku + batch management (ManageBatches page) |
| **StockAdjustmentResource** | `StockAdjustment` | Penyesuaian stok bahan → via `StockReconciliationService` |
| **MenuStockResource** | `MenuStock` | CRUD stok menu (filter: `is_stock_calculated=false`) |
| **MenuStockAdjustmentResource** | `MenuStockAdjustment` | Penyesuaian stok menu → via `MenuStockReconciliationService` |
| **DailyIngredientUsageResource** | `DailyIngredientUsage` | **Read-only**, laporan pemakaian harian |

Semua resource di atas memiliki `shouldRegisterNavigation = false` (kecuali DailyIngredientUsageResource) karena diakses melalui tabbed pages.

### Resource Tambahan (terkait inventori)

| Resource | File | Fungsi Inventori |
|----------|------|------------------|
| **MenuResource** | `MenuResource.php` | Atur resep via `IngredientsRelationManager` (manage `menuIngredient` pivot dengan ingredient select + quantity) |
| **CategoryResource** | `CategoryResource.php` | Kategori menu |

### Helper Classes

| Helper | File | Fungsi |
|--------|------|--------|
| **NumberInputHelper** | `Filament/Helpers/NumberInputHelper.php` | Format angka desimal (koma → titik), batasi digit integer, cegah karakter `-eE+` |
| **TextInputHelper** | `Filament/Helpers/TextInputHelper.php` | Enforce max length dengan visual indicator |

---

## Integrasi dengan Modul Lain (6 entry points)

Semua deduksi stok terjadi sebagai **side effect** dari pemrosesan pesanan:

| Controller | Method | Pemicu | Inventory Action |
|-----------|--------|--------|-----------------|
| `CashierPesananBaruController::store()` | POST `/kasir/pesanan-baru` | Kasir buat pesanan baru | `processSaleForOrder()` |
| `CashierOrderController::updateStatus()` | PATCH `/kasir/pesanan/{id}/status` | Update status → diproses | `canFulfillOrder()` + `processSaleForOrder()` |
| `CashierOrderController::confirmCash()` | PATCH `.../konfirmasi-tunai` | Bayar tunai | `canFulfillOrder()` + `processSaleForOrder()` |
| `CashierOrderController::confirmQris()` | PATCH `.../konfirmasi-qris` | Bayar QRIS | `canFulfillOrder()` + `processSaleForOrder()` |
| `CashierOrderController::acceptQrisProof()` | POST `.../qris/accept` | Approve bukti QRIS | `canFulfillOrder()` + `processSaleForOrder()` |
| `OrderSyncController::store()` | POST `/sync-orders` | Sinkronisasi offline PWA | `processSaleForOrder(skipStockValidation: true)` |

Pre-validasi juga dilakukan di `StoreOrderRequest::withValidator()` via `canFulfillOrder()` sebelum order dibuat.

Alur data secara umum:

```
StoreOrderRequest
  └── canFulfillOrder(), pre-validasi stok
       │
       ▼
CashierController::store() / confirm*()
  └── DB::transaction()
       ├── canFulfillOrder(), double check
       └── InventoryService::processSaleForOrder()
            ├── IF menu has recipe (menu_ingredients exists)
            │    └── deductIngredientStock()
            │         ├── lockForUpdate() batch rows
            │         ├── sort by batch_mode (FIFO/FEFO/Custom)
            │         ├── decrement IngredientBatch.quantity
            │         ├── create StockMovement (immutable)
            │         └── upsert DailyIngredientUsage
            │
            └── IF menu has MenuStock (no recipe)
                 └── MenuStockService::deductMenuStockBatch()
                      ├── lockForUpdate() MenuStockBatch rows
                      ├── sort by batch_mode
                      ├── decrement MenuStockBatch.quantity
                      └── create MenuStockMovement (immutable)
```

---

## Database Migrations (13 file)

| # | File | Tabel |
|---|------|-------|
| 1 | `2026_04_11_000001_create_ingredients_table.php` | `ingredients` |
| 2 | `2026_04_11_000002_create_ingredient_batches_table.php` | `ingredient_batches` |
| 3 | `2026_04_11_000003_create_menu_ingredients_table.php` | `menu_ingredients` |
| 4 | `2026_04_11_000004_create_stock_adjustments_table.php` | `stock_adjustments` |
| 5 | `2026_04_11_000005_create_stock_movements_table.php` | `stock_movements` |
| 6 | `2026_04_15_000001_create_daily_ingredient_usages_table.php` | `daily_ingredient_usages` |
| 7 | `2026_05_05_000001_change_low_stock_threshold_to_decimal_in_ingredients_table.php` | `ingredients` (alter) |
| 8 | `2026_05_12_000001_add_batch_mode_and_custom_order.php` | `ingredients` + `ingredient_batches` (alter) |
| 9 | `2026_05_12_101344_create_stock_reports_table.php` | `stock_reports` |
| 10 | `2026_05_17_000001_create_menu_stocks_table.php` | `menu_stocks` |
| 11 | `2026_05_17_000002_create_menu_stock_batches_table.php` | `menu_stock_batches` |
| 12 | `2026_05_17_000003_create_menu_stock_adjustments_table.php` | `menu_stock_adjustments` |
| 13 | `2026_05_17_000004_create_menu_stock_movements_table.php` | `menu_stock_movements` |

---

## Pengujian (17 test files, 54 test methods, ALL ACTIVE)

**Konfigurasi:** phpunit.xml, PostgreSQL database `testing`, cache/session `array` driver, Laravel Telescope & Pulse disabled. Tidak ada group/skip/todo.

### A. Unit Tests, `tests/Unit/Inventory/` (4 file, 7 tests)

| File | Test Methods | Cakupan |
|------|-------------|---------|
| `InventoryServiceFifoTest.php` | 1 | FIFO: older `received_at` batch consumed first |
| `InventoryServiceFefoTest.php` | 1 | FEFO: nearer `expiry_date` batch consumed first |
| `InventoryServiceSkipValidationTest.php` | 3 | `skipStockValidation=true` allows negative stock; tetap record movement; default throw exception |
| `IngredientModelTest.php` | 2 | `getTotalStock()` sum semua batch; `scopeActive()` filter |

### B. Feature Tests, `tests/Feature/Inventory/` (3 file, 6 tests)

| File | Test Methods | Cakupan |
|------|-------------|---------|
| `DailyIngredientUsageAggregationTest.php` | 3 | 2 order → 1 row aggregated; idempotent (2nd call skipped); cashier order langsung record usage |
| `InventoryRollbackTest.php` | 1 | Insufficient stock → exception → batch unchanged, 0 movements |
| `InventoryFoundationMigrationTest.php` | 2 | 5 core tables exist; `menu_ingredients` has expected columns |

### C. MenuStock Feature Tests, `tests/Feature/Admin/MenuStock*.php` (6 file, 27 tests)

| File | Tests | Cakupan |
|------|-------|---------|
| `MenuStockEdgeCasesTest.php` | 7 | Auto-create MenuStock saat resep dihapus; soft delete cascade; dual-deduction prevention; mixed order rollback; idempotent save; batch exhaustion to zero; negative qty rejection |
| `MenuStockServiceTest.php` | 5 | Single/multi-batch FEFO; insufficient stock exception; idempotent processSale; FIFO ordering |
| `MenuStockInventoryIntegrationTest.php` | 4 | No-recipe menu deducts MenuStock; menu tanpa resep & tanpa stock di-skip; mixed order (recipe + stock); rollback jika stock insufficient |
| `MenuStockAdjustmentResourceTest.php` | 3 | Increase adjustment; decrease adjustment; data integrity on edit |
| `MenuStockReconciliationTest.php` | 4 | Increase + movement; decrease with signed negative; reject invalid qty (0, -5) |
| `MenuStockResourceTest.php` | 4 | List menu stocks; create with initial batch; filter to no-recipe only; batches hidden on edit |

### D. StockAdjustment & Ingredient Tests, `tests/Feature/Admin/` (3 file, 8 tests)

| File | Tests | Cakupan |
|------|-------|---------|
| `StockAdjustmentFlowTest.php` | 4 | Increase adjustment; decrease adjustment; non-admin cannot access; reject negative qty |
| `IngredientCrudTest.php` | 2 | Full CRUD lifecycle + soft delete; non-admin cannot access |
| `IngredientBatchCrudTest.php` | 2 | Create + update batch; sorting by expiry then received |

### E. Kitchen StockReport Tests, `tests/Feature/Kitchen/StockReportTest.php` (6 tests)

| Test | Cakupan |
|------|---------|
| Approve report → status approved + StockAdjustment created | Workflow approval |
| Reject report → status rejected + rejection_note | Workflow rejection |
| Kitchen cannot approve own report | (documented, not enforced) |
| Initial status is pending | Status lifecycle |
| Report belongs to ingredient | Relationship |
| Report belongs to reported_by user | Relationship |

---

## Ringkasan Kuantitatif

| Metrik | Value |
|--------|-------|
| **Total models** | 11 |
| **Total services** | 4 (InventoryService ~453 baris, MenuStockService ~254 baris, 2 reconciliation) |
| **Total observers** | 2 |
| **Total migrations** | 13 |
| **Total Filament resource/page files** | ~25+ (6 resources, 2 tabbed pages, 6 sub-pages, 3 relation managers, helpers, panel provider) |
| **Total test files** | 17 |
| **Total test methods** | 54 |
| **Integration entry points** | 6 (5 cashier + 1 sync) |
| **Batch modes** | 3 (FEFO, FIFO, Custom) |
| **Key business rules** | Immutable movements (append-only), pessimistic locking, idempotent processing, skip-validation for offline sync |

---

---

> **Catatan Format:** Jangan gunakan em dash (—) di mana pun di file skripsi. Gunakan koma (,) sebagai pengganti jika perlu pemisah antar klausa.

## Alur Pengerjaan Skripsi (Step by Step)

1. **Baca `konteks-skripsi.md`**, pahami struktur bab, formatting, arsitektur inventori
2. **Baca codebase**, pahami kode inventori di `app/Services/`, `app/Models/`, `app/Filament/Resources/`
3. **Baca `contoh-skripsi.md`**, lihat bagaimana template penulisan skripsi UNDIP Teknik Komputer
4. **Tulis konten** per bab di markdown: `bab1-pendahuluan.md`, `bab2-tinjauan-pustaka.md`, ..., `bab6-penutup.md` + `abstrak.md`, `abstract.md`, `kata-pengantar.md`, `daftar-pustaka.md`
5. **Jalankan `create_template.py`**, generate `template-skripsi.docx` (jika belum ada atau ingin reset formatting)
6. **Jalankan `build_skripsi.py`**, baca markdown → generate `skripsi-lengkap.docx`
7. **Buka `skripsi-lengkap.docx` di Word** → `Ctrl+A → F9` untuk update TOC/page number

**Format output final:**
- `skripsial.md`, satu file markdown berisi seluruh skripsi (gabungan semua bab)
- `skripsial.docx`, satu file DOCX final siap sidang

Script `build_skripsi.py` akan menghasilkan kedua file tersebut. File `skripsial.md` berguna untuk version control dan review cepat, sedangkan `skripsial.docx` untuk submit ke pembimbing.

---

## Workflow Penulisan Skripsi

### Format Output
- Setiap bab/isi skripsi ditulis dalam **2 format**: `*.md` (markdown) dan `*.docx` (Word)
- Konversi MD → DOCX menggunakan **python-docx**

### Diagram
- Semua diagram ditulis dalam kode **Mermaid**
- Source code Mermaid **TIDAK** dilampirkan langsung ke file MD/DOCX skripsi
- Setiap diagram disimpan sebagai file terpisah di folder **`skripsi/diagram/`**
- Format penamaan: `skripsi/diagram/<bab>-<urutan>-<nama-diagram>.mmd`
 , Contoh: `skripsi/diagram/bab3-01-arsitektur-sistem.mmd`
 , Contoh: `skripsi/diagram/bab3-02-er-diagram.mmd`
 , Contoh: `skripsi/diagram/bab4-01-class-diagram-service.mmd`

### Struktur File per Bab
```
skripsi/
├── konteks-skripsi.md              ← file ini
├── contoh-skripsi.md               ← template referensi
├── contoh-skripsi.md.backup        ← backup template asli
├── diagram/
│   ├── bab3-01-arsitektur-sistem.mmd
│   ├── bab3-02-er-diagram.mmd
│   ├── bab3-03-use-case-diagram.mmd
│   ├── bab3-04-sequence-diagram.mmd
│   ├── bab4-01-class-diagram-service.mmd
│   └── ...
├── gambar/                         ← screenshot/export diagram untuk docx
├── bab1-pendahuluan.md
├── bab1-pendahuluan.docx
├── bab2-tinjauan-pustaka.md
├── bab2-tinjauan-pustaka.docx
├── bab3-perancangan-sistem.md
├── bab3-perancangan-sistem.docx
├── bab4-implementasi-sistem.md
├── bab4-implementasi-sistem.docx
├── bab5-pengujian-dan-evaluasi.md
├── bab5-pengujian-dan-evaluasi.docx
├── bab6-penutup.md
├── bab6-penutup.docx
└── skripsi-lengkap.md
└── skripsi-lengkap.docx
```

### Toolchain Konversi
```bash
# Install python-docx
pip install python-docx

# Script konversi akan dibuat di skripsi/convert.py
# Usage: python skripsi/convert.py skripsi/bab1-pendahuluan.md
```

---

## File Referensi Penting

### Services
- `app/Services/InventoryService.php`, Engine utama deduksi bahan baku
- `app/Services/MenuStockService.php`, Engine deduksi stok menu
- `app/Services/StockReconciliationService.php`, Penyesuaian manual bahan
- `app/Services/MenuStockReconciliationService.php`, Penyesuaian manual stok menu

### Models
- `app/Models/Ingredient.php`, `IngredientBatch.php`, `MenuIngredient.php`
- `app/Models/MenuStock.php`, `MenuStockBatch.php`, `MenuStockMovement.php`, `MenuStockAdjustment.php`
- `app/Models/StockMovement.php`, `StockAdjustment.php`, `StockReport.php`
- `app/Models/DailyIngredientUsage.php`

### Filament
- `app/Filament/Resources/StockResource.php` + `*/Pages/ManageBatches.php`
- `app/Filament/Resources/StockAdjustmentResource.php` + `*/Pages/CreateStockAdjustment.php`
- `app/Filament/Resources/MenuStockResource.php` + `*/Pages/ManageMenuStockBatches.php`
- `app/Filament/Resources/MenuStockAdjustmentResource.php`
- `app/Filament/Resources/DailyIngredientUsageResource.php`
- `app/Filament/Resources/MenuResource/RelationManagers/IngredientsRelationManager.php`
- `app/Filament/Pages/StokPage.php`
- `app/Filament/Pages/AdjustmentsPage.php`
- `app/Providers/Filament/AdminPanelProvider.php`

### Observers
- `app/Observers/MenuObserver.php`
- `app/Observers/MenuIngredientObserver.php`

### Integrasi
- `app/Http/Controllers/Cashier/CashierPesananBaruController.php`
- `app/Http/Controllers/Cashier/CashierOrderController.php`
- `app/Http/Controllers/Api/OrderSyncController.php`
- `app/Http/Requests/StoreOrderRequest.php`
- `app/Events/StockUpdated.php`

### Database
- `database/migrations/2026_04_11_*` sampai `2026_05_17_*` (11 migration files)
- `database/seeders/CashFlowIngredientSeeder.php`
- `database/seeders/IngredientUsageSeeder.php`

### Tests
- `tests/Unit/Inventory/` (3 file)
- `tests/Feature/Inventory/` (3 file)
- `tests/Feature/Admin/MenuStock*.php` (6 file)
- `tests/Feature/Admin/StockAdjustmentFlowTest.php`
- `tests/Feature/Admin/IngredientCrudTest.php`
- `tests/Feature/Admin/IngredientBatchCrudTest.php`
- `tests/Feature/Kitchen/StockReportTest.php`

---

## Alur Pembuatan Skripsi (Format DOCX)

### Strategi Otomatisasi

**Prinsip: Hybrid, manual 1 kali, otomatis seterusnya.**

| Fase | Dikerjakan Oleh | Waktu |
|------|----------------|-------|
| **Template** (margin, styles, numbering, section break) | **Manual di Word, 15 menit** | Sekali di awal |
| **Isi konten** (semua teks skripsi termasuk tabel, gambar) | **AI nulis di Markdown** | Setiap bab |
| **Generate DOCX dari template** | **Python script otomatis** | Setiap selesai nulis |
| **Formatting detail** (italic asing, source code, abstrak, daftar isi) | **Python script otomatis** | Setelah generate |
| **Final polish** (update TOC, cek halaman) | **Buka di Word, klik Update Field** | Final |

Jangan khawatir, AI + Python tetap mengerjakan **~90% pekerjaan**. Tidak perlu setup manual Word sama sekali.

---

### Langkah 0: Template DOCX via Python (Sekali Buat, Otomatis)

Template `template-format-skripsi.docx` yang sudah ada **tidak bisa diekstrak stylenya** karena formatting-nya dilakukan secara manual (direct formatting tiap teks), bukan melalui proper Word styles.

**Solusi:** Python script yang membuat template DOCX baru dari nol, mendefinisikan semua formatting dengan benar. Script ini dijalankan SEKALI dan hasilnya dipakai terus.

Script: `skripsi/create_template.py`

```python
# create_template.py, Generate template dengan proper Word styles
from docx import Document
from docx.shared import Pt, Cm, Emu, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.oxml.ns import qn, nsdecls
from docx.oxml import parse_xml

doc = Document()

# ── Ukuran Kertas & Margin ──
section = doc.sections[0]
section.page_width  = Cm(21)
section.page_height = Cm(29.7)
section.top_margin    = Cm(4)
section.bottom_margin = Cm(3)
section.left_margin   = Cm(4)
section.right_margin  = Cm(3)

# ── Default Font ──
style = doc.styles['Normal']
style.font.name = 'Times New Roman'
style.font.size = Pt(12)
style.paragraph_format.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY
style.paragraph_format.line_spacing = 1.5
style.paragraph_format.first_line_indent = Cm(1.25)

# ── Heading 1 (BAB I PENDAHULUAN) ──
h1 = doc.styles['Heading 1']
h1.font.name = 'Times New Roman'
h1.font.size = Pt(14)
h1.font.bold = True
h1.font.color.rgb = RGBColor(0, 0, 0)
h1.paragraph_format.alignment = WD_ALIGN_PARAGRAPH.CENTER
h1.paragraph_format.space_before = Pt(0)
h1.paragraph_format.space_after = Pt(24)
h1.paragraph_format.keep_with_next = True
h1.paragraph_format.keep_together = True

# ── Heading 2 (1.1 Latar Belakang) ──
h2 = doc.styles['Heading 2']
h2.font.name = 'Times New Roman'
h2.font.size = Pt(12)
h2.font.bold = True
h2.font.color.rgb = RGBColor(0, 0, 0)
h2.paragraph_format.alignment = WD_ALIGN_PARAGRAPH.LEFT
h2.paragraph_format.keep_with_next = True
h2.paragraph_format.keep_together = True

# ── Heading 3 (1.1.1 Sub-bab) ──
h3 = doc.styles['Heading 3']
h3.font.name = 'Times New Roman'
h3.font.size = Pt(12)
h3.font.bold = True
h3.font.color.rgb = RGBColor(0, 0, 0)
h3.paragraph_format.alignment = WD_ALIGN_PARAGRAPH.LEFT
h3.paragraph_format.keep_with_next = True
h3.paragraph_format.keep_together = True

# ── Caption ──
caption = doc.styles['Caption']
caption.font.name = 'Times New Roman'
caption.font.size = Pt(11)
caption.paragraph_format.alignment = WD_ALIGN_PARAGRAPH.CENTER
caption.paragraph_format.space_after = Pt(12)

doc.save('skripsi/template-skripsi.docx')
```

#### Spesifikasi Format yang Akan Diterapkan oleh Python

| Elemen | Spesifikasi |
|--------|------------|
| **Kertas** | A4 (21 cm × 29,7 cm) |
| **Margin** | Top 4 cm, Left 4 cm, Bottom 3 cm, Right 3 cm |
| **Font default** | Times New Roman 12pt |
| **Spasi** | 1.5 (1,5 spasi) untuk body text |
| **Alignment** | Justify untuk semua (kecuali source code) |
| **Indentasi paragraf** | First line 1,25 cm |
| **Heading 1 (BAB)** | TNR 14 Bold, Center, 2 baris terpisah (BAB I + PENDAHULUAN) |
| **Heading 2 (sub-bab)** | TNR 12 Bold, Left |
| **Heading 3 (sub-sub-bab)** | TNR 12 Bold, Left |
| **Numbering** | Multilevel native Word via numbering.xml |
| **Caption (TableCaption / FigureCaption)** | TNR 10, Justify, spasi 1.0, tidak italic (kecuali kata asing otomatis) |
| **Source code** | Courier New 10pt, spasi 1.0, Left, di tabel 1×1 |
| **Abstrak** | TNR 10pt, spasi 1.0 |
| **Abstract** | TNR 10pt, spasi 1.0, **semua italic** |
| **Kata asing** | Auto-italic via deteksi kamus |
| **Page number (awal)** | Romawi i, ii, iii, bawah tengah |
| **Page number (isi)** | Arab 1, 2, 3, kanan atas atau bawah tengah |
| **Section break** | Antara halaman awal dan isi (Next Page) |
| **Link to Previous** | Dinonaktifkan di section 2 |
| **Bab baru** | Page break otomatis |
| **Bold** | Semua bab, sub-bab, sub-sub-bab |

---

### Langkah 1: Persiapan Skrip Konversi

Dua skrip Python yang akan dibuat:

| Skrip | Fungsi |
|-------|--------|
| `skripsi/create_template.py` | Generate template DOCX dari nol (dijalankan SEKALI) |
| `skripsi/build_skripsi.py` | Baca Markdown → inject ke template → generate DOCX final (dijalankan SETIAP NULIS BAB) |

```bash
# Install dependencies
pip install python-docx
```

---

### Langkah 2: Menulis Konten di Markdown

Tiap bab ditulis di file markdown terpisah:

```
skripsi/
├── bab1-pendahuluan.md
├── bab2-tinjauan-pustaka.md
├── bab3-perancangan-sistem.md
├── bab4-implementasi-sistem.md
├── bab5-pengujian-dan-evaluasi.md
├── bab6-penutup.md
├── abstrak.md
├── abstract.md
├── kata-pengantar.md
├── daftar-pustaka.md
```

---

### Langkah 3: Generate DOCX via Python

Script Python akan melakukan:

1. **Generate template** `template-skripsi.docx` dari nol (via `create_template.py`), semua style, margin, numbering sudah di-set
2. **Baca Markdown** setiap bab → parse heading, paragraf, tabel, kode, gambar
3. **Inject ke template** dengan formatting:
  , Bab baru → Page Break
  , Judul bab di tengah, dua baris:
     ```
     BAB I
     PENDAHULUAN
     ```
  , Bold untuk bab, sub-bab, sub-sub-bab
  , Justify untuk semua teks (kecuali source code)
  , Indentasi 1,25 cm untuk paragraf baru
  , Numbering dengan indentasi 1,25 cm antara nomor dan teks
4. **Source code**: taruh di tabel 1×1, Courier New 10pt, spacing 1.0, left alignment, pertahankan indentasi kode
5. **Kata asing**: italic otomatis via deteksi kamus
6. **Abstrak**: TNR 10pt, spacing 1.0
7. **Abstract**: TNR 10pt, spacing 1.0, **semua italic**
8. **Inject TOC field** (Daftar Isi)
9. **Inject List of Figures field** (Daftar Gambar)
10. **Inject List of Tables field** (Daftar Tabel)
11. **Gabung semua bab** jadi satu file `skripsi-lengkap.docx`

---

### Langkah 4: Finalisasi

1. Buka file `.docx` hasil generate di Word
2. **Update Daftar Isi**: klik kanan → Update Field
3. **Update Daftar Gambar/Tabel**: klik kanan → Update Field
4. Periksa:
  , Nomor halaman konsisten
  , Header/footer benar
  , Tidak ada orphan (judul bab di akhir halaman)
5. Export ke PDF untuk pengecekan akhir

---

### Detail Formatting Spesifik

#### A. Penulisan Bab Baru
```
[Page Break]
[Baris 1, tengah, TNR 14 Bold]: BAB I
[Baris 2, tengah, TNR 14 Bold]: PENDAHULUAN
[spasi 2]
[Isi bab...]
```

#### B. Heading Hierarchy
```
Heading 1 → "BAB I PENDAHULUAN" (TNR 14 Bold, Center)
Heading 2 → "1.1 Latar Belakang" (TNR 12 Bold, Left, indent 1,25)
Heading 3 → "1.1.1 Sub Pokok Bahasan" (TNR 12 Bold, Left, indent 1,25)
Normal    → Teks paragraf (TNR 12, Justify, first line indent 1,25)
```

#### C. Kata Asing (Italic)
Semua kata asing (bahasa Inggris, Latin, istilah teknis asing) harus di-italic.

**Deteksi otomatis (via Python):**
1. Tokenisasi teks per kata
2. Cocokkan dengan kamus kata bahasa Indonesia (gunakan `kamus-kata-baku` atau `sastrawi`)
3. Kata yang tidak ada di kamus dan bukan nama orang/tempat → italic
4. Atau: daftar istilah teknis asing yang umum di bidang komputer (`database`, `framework`, `algorithm`, `batch`, `fifo`, `fefo`, `queue`, `server`, `client`, `cache`, `middleware`, `endpoint`, dll.)
5. Semua kata dalam `<i>` atau `*kata*` di markdown → italic

**Fallback manual:** Setelah konversi, jalankan skrip deteksi tambahan yang menandai kata potensial asing untuk review manual.

#### D. Abstrak & Abstract
| Elemen | Font | Size | Spacing | Alignment | Style |
|--------|------|------|---------|-----------|-------|
| **Abstrak** (Indonesia) | Times New Roman | 10pt | 1.0 (single) | Justify | AbstractText (custom style) |
| **Abstract** (Inggris) | Times New Roman | 10pt | 1.0 (single) | Justify | AbstractTextItalic (custom style, **semua italic**) |

#### E. Source Code / Kutipan Kode (Blok Kode)
```
┌────────────────────────────────────────────┐
│  Tabel 1×1 (tanpa border, atau border halus) │
│                                              │
│  def fifo_deduction(batches, qty):          │
│      for batch in sorted(batches,           │
│          key=lambda b: b.received_at):      │
│          if qty <= 0: break                 │
│          deduct = min(qty, batch.quantity)  │
│          batch.quantity -= deduct           │
│          qty -= deduct                      │
│                                              │
│  Font: Courier New 10pt                     │
│  Spacing: 1.0 (single)                      │
│  Alignment: Left (bukan justify)            │
│  Indentasi kode dipertahankan               │
└────────────────────────────────────────────┘
```

#### E2. Inline Code (Kutipan dalam Paragraf)
Kutipan `source code` atau nama method/variable di dalam paragraf (ditandai dengan backtick `` ` `` di markdown) akan diformat dengan aturan berikut:

| Elemen | Aturan |
|--------|--------|
| **Font** | Courier New |
| **Size** | 10pt |
| **Spacing** | Sama dengan paragraf induk (1.5 untuk body, 1.0 untuk abstrak) |
| **Backtick** | Dihapus, tidak terlihat di output |
| **Contoh markdown** | `pada model `StockMovement` dan `MenuStockMovement`` |
| **Hasil di DOCX** | pada model **StockMovement** dan **MenuStockMovement** (Courier New 10pt) |

Formatting lain seperti *italic* bisa tetap digunakan bersamaan dalam satu paragraf.

#### E3. Caption Tabel dan Gambar

Caption tabel dan gambar menggunakan **dua style terpisah** agar bisa terdeteksi otomatis oleh Daftar Tabel dan Daftar Gambar:

| Elemen | Style | Fungsi | Format |
|--------|-------|--------|--------|
| **Caption Tabel** | `TableCaption` (custom) | Auto-masuk Daftar Tabel (`TOC \t "TableCaption,1"`) | TNR 10pt, Justify, spasi 1.0, **tidak italic** |
| **Caption Gambar** | `FigureCaption` (custom) | Auto-masuk Daftar Gambar (`TOC \t "FigureCaption,1"`) | TNR 10pt, Justify, spasi 1.0, **tidak italic** |

**Aturan:**
- Hanya kata asing/istilah teknis yang di-italic (via deteksi `FOREIGN_TERMS` + `*...*` di markdown)
- Caption **TIDAK** di-italic seluruhnya
- Alignment Justify (rata kiri-kanan), bukan Center
- Spasi 1.0 (single spacing)
- Ditulis dalam markdown sebagai teks biasa diawali `Tabel X.Y ...` atau `Gambar X.Y ...`

**Contoh markdown:**
```markdown
Tabel 3.1 Kebutuhan fungsional sistem.
```
**Contoh markdown dengan kata asing:**
```markdown
Tabel 3.2 Kebutuhan non-fungsional dengan mode *batch* FEFO.
```

#### F. Daftar Isi, Daftar Gambar, Daftar Tabel
Menggunakan field TOC/TOF/TOT bawaan Word via python-docx:

```python
# Inject TOC
from docx.oxml.ns import qn
from docx.oxml import OxmlElement

def insert_toc(doc, levels="1-3"):
    """Insert TOC field into document."""
    # OOXML field: TOC \o "1-3" \h \z \u
    # ...

def insert_lof(doc):
    """Insert List of Figures field."""
    # OOXML field: TOC \t "FigureCaption,1" \h \z \u
    # (menggunakan style FigureCaption, bukan SEQ Figure)
    # ...

def insert_lot(doc):
    """Insert List of Tables field."""
    # OOXML field: TOC \t "TableCaption,1" \h \z \u
    # (menggunakan style TableCaption, bukan SEQ Table)
    # ...
```

Field-field ini perlu di-refresh setelah dibuka di Word (klik kanan → Update Field). Atau otomatis via LibreOffice unoserver:

```bash
# Via unoserver (Docker)
docker pull chanmo/unoserver
docker run -p 5000:5000 chanmo/unoserver
http -f POST :5000/convert/docx file@skripsi.docx -o skripsi-final.docx
```

#### G. Format Referensi (IEEE)

Menggunakan **IEEE citation style** dengan format `[1]`, `[2]`, `[3]`, dst.

**Aturan umum:**
- Semua referensi berasal dari **paper jurnal ilmiah** (jurnal nasional terakreditasi atau jurnal internasional)
- **Diprioritaskan jurnal berbahasa Indonesia** terlebih dahulu
- Baru setelahnya jurnal internasional, prosiding konferensi, atau buku referensi
- Hindari referensi dari blog, website komersial, atau sumber tidak terverifikasi
- Minimal 20 referensi untuk skripsi S1

**Format IEEE di Daftar Pustaka:**

```
[1] N. Depan. N. Belakang, "Judul artikel," Nama Jurnal, vol. X, no. X, pp. xx–xx, Bulan Tahun, doi: xx.xxxx/xxxxx.
```

Setiap elemen format:

| Elemen | Format | Contoh |
|--------|--------|--------|
| **Penulis** | Inisial. Nama Belakang | `A. B. Setiawan` atau `Setiawan` untuk 1 penulis |
| **Banyak penulis** | Pisah dengan koma, sebelum penulis terakhir pakai `, dan` | `A. Setiawan, B. Pratama, dan C. Wijaya` |
| **Judul artikel** | Dalam tanda petik, kapitalisasi kalimat | `"Implementasi sistem inventori berbasis web menggunakan Laravel"` |
| **Nama jurnal** | Huruf miring (*italic*), kapitalisasi judul | `Jurnal Teknik Komputer` |
| **Volume** | `vol. X` | `vol. 15` |
| **Nomor** | `no. X` | `no. 2` |
| **Halaman** | `pp. xx–xx` (En dash, bukan hyphen) | `pp. 45–58` |
| **Tahun** | Dalam kurung setelah halaman | `(2025)` |
| **DOI** | Jika tersedia | `doi: 10.xxxxx/xxxxx` |

**Contoh lengkap:**

```
[1] A. B. Setiawan, "Sistem manajemen inventori menggunakan metode FIFO," Jurnal Teknik Informatika, vol. 12, no. 3, pp. 100–110, 2024.
[2] C. D. Pratama dan E. Wijaya, "Analisis perbandingan algoritma FEFO dan FIFO pada sistem inventory," Jurnal Sistem Informasi, vol. 8, no. 1, pp. 22–35, 2023, doi: 10.xxxxx/xxxxx.
[3] R. K. Sari, D. Lestari, dan M. H. Nasution, "Pengembangan sistem POS berbasis web untuk usaha kecil menengah," Jurnal Teknologi Informasi dan Ilmu Komputer, vol. 10, no. 4, pp. 301–310, 2024.
[4] S. Nugroho, "Implementasi Filament admin panel pada aplikasi berbasis Laravel," Jurnal Rekayasa Perangkat Lunak, vol. 6, no. 2, pp. 55–63, 2025.
[5] J. Smith, "A systematic review of batch tracking algorithms in inventory management systems," International Journal of Supply Chain Management, vol. 18, no. 3, pp. 210–225, 2024.
```

#### Otomatisasi Referensi via CrossRef (habanero)

Referensi bisa ditulis manual di `daftar-pustaka.md`, atau di-generate otomatis dari DOI menggunakan library Python **habanero** + **citeproc-py**.

CrossRef API **tidak memerlukan API key**, gratis dan terbuka untuk umum.

**Cara 1: Manual, tulis langsung IEEE di daftar-pustaka.md**

```markdown
[1] A. B. Setiawan, "Sistem manajemen inventori menggunakan metode FIFO," *Jurnal Teknik Informatika*, vol. 12, no. 3, pp. 100–110, 2024.
[2] C. D. Pratama dan E. Wijaya, "Analisis perbandingan algoritma FEFO dan FIFO pada sistem inventory," *Jurnal Sistem Informasi*, vol. 8, no. 1, pp. 22–35, 2023, doi: 10.xxxxx/xxxxx.
```

**Cara 2: Otomatis, cukup tulis DOInya, script akan fetch IEEE**

```markdown
[1] doi:10.1109/access.2024.1234567
[2] doi:10.xxxxx/xxxxx
```

Script `build_skripsi.py` akan menggunakan **habanero** untuk me-request IEEE dari CrossRef:

```python
from habanero import cn

def fetch_ieee_citation(doi):
    """Fetch IEEE citation from CrossRef via content negotiation."""
    try:
        return cn.content_negotiation(
            ids=doi.strip(),
            format="text",
            style="ieee"
        )
    except Exception as e:
        return f"[Gagal fetch DOI: {doi}, {e}]"
```

**Cara 3: Semi-otomatis, generate BibTeX dulu, lalu format via citeproc-py**

```python
from habanero import cn
from citeproc import CitationStylesStyle, CitationStylesBibliography
from citeproc import Citation, CitationItem
from citeproc.source.bibtex import BibTeX

# Step 1: Ambil BibTeX dari DOI
bibtex_str = cn.content_negotiation(ids="10.xxxxx/xxxxx", format="bibtex")

# Step 2: Simpan ke file .bib
with open("refs.bib", "w") as f:
    f.write(bibtex_str)

# Step 3: Load CSL style IEEE 
bib_source = BibTeX("refs.bib")
style = CitationStylesStyle("ieee")
bibliography = CitationStylesBibliography(style, bib_source, {})

# Step 4: Generate bibliography entries
for item in bib_source:
    citation = Citation([CitationItem(item.key)])
    bibliography.register(citation)

bibliography.bibliography()  # → daftar IEEE
```

**Instalasi:**

```bash
pip install habanero citeproc-py
```

**Cara sitasi dalam teks (side note):**

Setiap kalimat atau paragraf yang membutuhkan referensi, tulis nomor referensi di akhir kalimat menggunakan format `[n]`.

```markdown
Sistem manajemen inventori sangat penting dalam operasional kafe untuk menghindari kehabisan stok bahan baku [1]. Metode FIFO (First-In-First-Out) dan FEFO (First-Expiry-First-Out) merupakan dua pendekatan umum yang digunakan dalam sistem inventory untuk mengoptimalkan penggunaan bahan berdasarkan waktu penerimaan dan masa kadaluarsa [1], [2]. Penelitian sebelumnya menunjukkan bahwa implementasi sistem inventori berbasis web dapat mengurangi kesalahan pencatatan stok hingga 85% dibandingkan metode manual [3].
```

**Penulisan di file daftar-pustaka.md:**

```
[1] doi:10.1109/access.2024.1234567
[2] doi:10.xxxxx/xxxxx
```

Atau jika ingin kontrol penuh atas format:

```
[1] A. B. Setiawan, "Judul," *Jurnal*, vol. X, no. Y, pp. Z–Z, 2024.
```

Script `build_skripsi.py` akan mendeteksi otomatis: jika baris diawali `doi:` → fetch dari CrossRef. Jika tidak → pakai teks mentah apa adanya.

---

| No | Item | Status |
|:--:|------|--------|
| 1 | Kertas A4, margin 4-4-3-3 | ☐ |
| 2 | Heading 1: TNR 14 Bold Center ("BAB I") | ☐ |
| 3 | Heading 2: TNR 12 Bold Left ("1.1") | ☐ |
| 4 | Heading 3: TNR 12 Bold Left ("1.1.1") | ☐ |
| 5 | Normal: TNR 12 Justify, indent 1,25 cm, spasi 1.5 | ☐ |
| 6 | Section break: romawi (i,ii) ↔ arab (1,2) | ☐ |
| 7 | Link to Previous dinonaktifkan | ☐ |
| 8 | Page number: romawi di awal, arab di isi | ☐ |
| 9 | Different First Page per bab (jika perlu) | ☐ |
| 10 | Kata asing di-italic semua | ☐ |
| 11 | Abstrak: TNR 10, spasi 1.0 | ☐ |
| 12 | Abstract: TNR 10, spasi 1.0, semua italic | ☐ |
| 13 | Bab baru → page break, judul 2 baris tengah | ☐ |
| 14 | Bold untuk semua bab, sub-bab, sub-sub-bab | ☐ |
| 15 | Indentasi paragraf 1,25 cm | ☐ |
| 16 | Indentasi numbering 1,25 cm | ☐ |
| 17 | Source code di tabel 1×1, Courier New 10, spasi 1.0, left | ☐ |
| 18 | Semua teks justify (kecuali source code) | ☐ |
| 19 | Daftar Isi otomatis | ☐ |
| 20 | Daftar Gambar otomatis | ☐ |
| 21 | Daftar Tabel otomatis | ☐ |
| 22 | Page break antar bab | ☐ |
| 23 | PDF final di-cek halaman per halaman | ☐ |
