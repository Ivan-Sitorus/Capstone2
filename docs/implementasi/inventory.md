# Inventory — Manajemen Stok Bahan Baku

> **Modul:** Admin & Inventory  
> **Fase:** Fitur Admin dan Inventori  
> **Penulis:** Tim Capstone W9 Cafe POS

---

## Daftar Isi

1. [Arsitektur Inventori](#arsitektur-inventori)
2. [3 Batch Modes (FIFO / FEFO / Custom)](#3-batch-modes-fifo--fefo--custom)
3. [Implementasi Batch Modes](#implementasi-batch-modes)
4. [InventoryService — Logika Pengambilan Stok](#inventoryservice--logika-pengambilan-stok)
5. [ManageBatchOrder — Filament Page](#managebatchorder--filament-page)
6. [Edge Cases](#edge-cases)

---

## Arsitektur Inventori

### Model yang Terlibat

```
Ingredient (bahan baku)
├── id, name, unit, low_stock_threshold, is_active
├── batch_mode: ENUM('fifo', 'fefo', 'custom')  ← DITAMBAHKAN
│
└──> IngredientBatch (batch per bahan)
     ├── id, ingredient_id, quantity, expiry_date, received_at, cost_per_unit
     ├── custom_order: INTEGER NULL  ← DITAMBAHKAN
     │
     ├──> StockMovement (log setiap perubahan stok)
     │    └── order_id, ingredient_batch_id, quantity_before, quantity_change, quantity_after
     │
     └──> StockAdjustment (penyesuaian stok manual)
          └── adjustment_type, status, approved_at, approved_by
```

### File yang Terlibat

| File | Deskripsi |
|------|-----------|
| `app/Models/Ingredient.php` | Model bahan baku — ditambah field `batch_mode` |
| `app/Models/IngredientBatch.php` | Model batch — ditambah field `custom_order` |
| `app/Services/InventoryService.php` | Service inti — logika pengurangan stok |
| `app/Filament/Resources/IngredientResource.php` | Filament resource untuk CRUD ingredient |
| `app/Filament/Resources/IngredientResource/Pages/ManageBatches.php` | Halaman kelola batch |
| `app/Filament/Pages/ManageBatchOrder.php` | **NEW** — Halaman reorder batch custom |
| `app/Models/StockMovement.php` | Log pergerakan stok |
| `app/Models/StockAdjustment.php` | Penyesuaian stok |

---

## 3 Batch Modes (FIFO / FEFO / Custom)

Saat stok bahan baku dikurangi (misal saat order diproses), sistem harus memilih batch mana yang diambil duluan. Mode pengambilan batch ditentukan per bahan (`ingredients.batch_mode`):

| Mode | Singkatan | Urutan Pengambilan | Use Case |
|------|-----------|-------------------|----------|
| **FIFO** | First In First Out | `received_at ASC` (batch terlama duluan) | Bahan non-expiry: gula, beras, gelas |
| **FEFO** | First Expired First Out | `expiry_date ASC` (kadaluarsa terdekat duluan) | Bahan perishable: susu, daging, roti |
| **Custom** | Manual Order | `custom_order ASC` (urutan manual) | Admin ingin kontrol penuh urutan |

### Default Mode

**Default: FEFO** — karena mayoritas bahan baku cafe bersifat perishable (susu, roti, daging).

### Perbandingan Mode

| Aspek | FIFO | FEFO | Custom |
|-------|------|------|--------|
| Urutan | Berdasarkan `received_at` | Berdasarkan `expiry_date` | Berdasarkan `custom_order` |
| Null handling | Batch tanpa `received_at` di akhir | Batch tanpa `expiry_date` di akhir | Batch tanpa `custom_order` di akhir |
| Otomatis | ✅ Otomatis | ✅ Otomatis | ❌ Manual (admin atur) |
| Cocok untuk | Bahan non-perishable | Bahan perishable | Situasi khusus |

---

## Implementasi Batch Modes

### Migration: Tambah Field Baru

```sql
-- Migration: tambah batch_mode ke ingredients
ALTER TABLE ingredients
ADD COLUMN batch_mode VARCHAR(10) NOT NULL DEFAULT 'fefo'
CHECK (batch_mode IN ('fifo', 'fefo', 'custom'));

-- Migration: tambah custom_order ke ingredient_batches
ALTER TABLE ingredient_batches
ADD COLUMN custom_order INTEGER NULL;

CREATE INDEX idx_batches_custom_order ON ingredient_batches(ingredient_id, custom_order);
```

### Model Ingredient — Tambah Field

```php
// app/Models/Ingredient.php (field yang ditambahkan)
protected $fillable = [
    // ...existing fields...
    'batch_mode',  // NEW
];

protected function casts(): array
{
    return [
        'batch_mode' => 'string',
    ];
}

// Constants untuk batch mode
public const BATCH_MODE_FIFO = 'fifo';
public const BATCH_MODE_FEFO = 'fefo';
public const BATCH_MODE_CUSTOM = 'custom';

public static function batchModes(): array
{
    return [
        self::BATCH_MODE_FIFO => 'FIFO — First In First Out',
        self::BATCH_MODE_FEFO => 'FEFO — First Expired First Out',
        self::BATCH_MODE_CUSTOM => 'Custom — Manual Order',
    ];
}
```

### Model IngredientBatch — Tambah Field

```php
// app/Models/IngredientBatch.php (field yang ditambahkan)
protected $fillable = [
    // ...existing fields...
    'custom_order',  // NEW
];

protected function casts(): array
{
    return [
        // ...existing casts...
        'custom_order' => 'integer',
    ];
}
```

---

## InventoryService — Logika Pengambilan Stok

### Algoritma Saat Ini (FEFO only)

Saat ini, `InventoryService::deductIngredientStock()` menggunakan sorting **FEFO hardcoded**:

```php
// app/Services/InventoryService.php (SAAT INI — hardcoded FEFO)
private function deductIngredientStock(int $ingredientId, float $requiredQuantity, array $context = []): array
{
    $ingredient = Ingredient::findOrFail($ingredientId);

    // ❌ Hardcoded FEFO:
    $batches = IngredientBatch::where('ingredient_id', $ingredientId)
        ->where('quantity', '>', 0)
        ->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END')
        ->orderBy('expiry_date', 'asc')    // FEFO: kadaluarsa terdekat duluan
        ->orderBy('received_at', 'asc')    // fallback: FIFO jika expiry_date sama
        ->get();

    // ... deduction logic ...
}
```

### Algoritma Target (3 mode)

```php
// app/Services/InventoryService.php (TARGET — dynamic batch mode)
private function deductIngredientStock(int $ingredientId, float $requiredQuantity, array $context = []): array
{
    $ingredient = Ingredient::findOrFail($ingredientId);

    // ✅ Dynamic batch mode berdasarkan ingredient.batch_mode
    $batches = IngredientBatch::where('ingredient_id', $ingredientId)
        ->where('quantity', '>', 0)
        ->when($ingredient->batch_mode === Ingredient::BATCH_MODE_FIFO, function ($query) {
            // FIFO: batch terlama (received_at ASC)
            return $query
                ->orderByRaw('CASE WHEN received_at IS NULL THEN 1 ELSE 0 END')
                ->orderBy('received_at', 'asc');
        })
        ->when($ingredient->batch_mode === Ingredient::BATCH_MODE_FEFO, function ($query) {
            // FEFO: kadaluarsa terdekat (expiry_date ASC)
            return $query
                ->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END')
                ->orderBy('expiry_date', 'asc')
                ->orderBy('received_at', 'asc'); // fallback
        })
        ->when($ingredient->batch_mode === Ingredient::BATCH_MODE_CUSTOM, function ($query) {
            // Custom: urutan manual (custom_order ASC)
            return $query
                ->orderByRaw('CASE WHEN custom_order IS NULL THEN 1 ELSE 0 END')
                ->orderBy('custom_order', 'asc')
                ->orderBy('received_at', 'asc'); // fallback
        })
        ->get();

    // ... deduction logic (tidak berubah) ...
}
```

### Visualisasi Pengambilan Stok

```
Bahan: Susu Segar (batch_mode = FEFO)
Stok dibutuhkan: 15 liter

Batch tersedia:
┌────┬──────────┬──────────┬────────────┐
│ ID │ Quantity │ Expiry   │ Urutan     │
├────┼──────────┼──────────┼────────────┤
│ A  │ 10 L     │ 15 Mei   │ ← 1 (FEFO) │
│ B  │ 5 L      │ 20 Mei   │ ← 2        │
│ C  │ 8 L      │ 10 Mei   │ ← 0 (EXPIRED, skip) │
└────┴──────────┴──────────┴────────────┘
╔════════════════════════════════════════╗
║ C (expired) → skip                    ║
║ A (10 L) → ambil 10 L, sisa 5 L       ║
║ B (5 L) → ambil 5 L, sisa 0 L         ║
║ Total diambil: 15 L ✅                ║
╚════════════════════════════════════════╝
```

---

## ManageBatchOrder — Filament Page

### Halaman Reorderable untuk Custom Mode

Saat `batch_mode = custom`, admin bisa mengatur ulang urutan batch melalui halaman Filament.

**File:** `app/Filament/Pages/ManageBatchOrder.php`

```php
<?php

namespace App\Filament\Pages;

use App\Models\Ingredient;
use App\Models\IngredientBatch;
use Filament\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Tables;
use Filament\Tables\Table;

class ManageBatchOrder extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-queue-list';
    protected static ?string $navigationGroup = 'Inventory';
    protected static ?string $title = 'Atur Urutan Batch';
    protected static ?string $navigationLabel = 'Urutan Batch (Custom)';
    protected static ?int $navigationSort = 5;

    public ?int $ingredientId = null;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => IngredientBatch::where('ingredient_id', $this->ingredientId))
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Batch ID'),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Jumlah'),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->label('Kadaluarsa')
                    ->date('d M Y'),
                Tables\Columns\TextColumn::make('custom_order')
                    ->label('Urutan'),
            ])
            ->reorderable('custom_order')  // ← Filament built-in reorder
            ->defaultSort('custom_order', 'asc')
            ->headerActions([
                Tables\Actions\Action::make('save_order')
                    ->label('Simpan Urutan')
                    ->action(fn () => /* save to DB */),
            ]);
    }
}
```

### Cara Kerja Reorder

1. Admin memilih bahan (`ingredient_id`) dengan `batch_mode = custom`
2. Tabel menampilkan semua batch bahan tersebut, diurut berdasarkan `custom_order`
3. Admin bisa **drag-and-drop** baris untuk mengubah urutan
4. Filament secara otomatis memperbarui nilai `custom_order` di database
5. Sistem kemudian menggunakan urutan baru saat `deductIngredientStock()` dipanggil

### Filament Resource untuk Ingredient

Tambahkan pilihan batch mode di form ingredient:

```php
// app/Filament/Resources/IngredientResource.php
public static function form(Form $form): Form
{
    return $form->schema([
        // ...existing fields...
        Select::make('batch_mode')
            ->label('Mode Pengambilan Batch')
            ->options(Ingredient::batchModes())
            ->default('fefo')
            ->helperText('FEFO: kadaluarsa terdekat duluan. FIFO: batch terlama duluan. Custom: urutan manual.')
            ->required(),
    ]);
}
```

---

## Edge Cases

### 1. Simultaneous Stock Reports (Optimistic Locking)

**Kasus:** Dua admin menyetujui stock adjustment secara bersamaan.

**Penanganan — Optimistic Locking:**

```php
// app/Models/StockAdjustment.php
use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    // Gunakan version column untuk optimistic locking
    // Laravel otomatis cek version saat save()
    protected function casts(): array
    {
        return [
            'version' => 'integer',
        ];
    }

    public function approve(int $approvedBy): void
    {
        // Optimistic locking: jika version berubah sejak dibaca → gagal
        try {
            $this->status = 'approved';
            $this->approved_at = now();
            $this->approved_by = $approvedBy;
            $this->save(); // ← throws ModelNotFoundException jika version mismatch
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw new \RuntimeException(
                'Laporan stok telah diubah oleh admin lain. Silakan muat ulang dan coba lagi.'
            );
        }
    }
}
```

```sql
-- Tambah kolom version di stock_adjustments
ALTER TABLE stock_adjustments ADD COLUMN version INTEGER NOT NULL DEFAULT 1;
```

### 2. Report During Active Orders

**Kasus:** Admin membuat stock adjustment (laporan stok) saat order sedang diproses.

**Penanganan:**

```
┌──────────────────────────────────────────────────┐
│  Adjustment dibuat → stok langsung berubah       │
│  (TANPA menunggu approval)                        │
│                                                   │
│  ┌─────────────┐                                  │
│  │ DIAJUKAN    │ → Status: pending               │
│  │ Stok: -5 kg │   Stok berubah SAAT INI         │
│  └──────┬──────┘                                  │
│         │                                         │
│    ┌────▼────┐     ┌──────────────┐              │
│    │DISETUJUI│     │   DITOLAK    │              │
│    │Tetap    │     │ Revert stok  │ ← ROLLBACK   │
│    │tidak ada│     │ +5 kg lagi   │              │
│    │perubahan│     └──────────────┘              │
│    └─────────┘                                    │
└──────────────────────────────────────────────────┘
```

```php
// Saat adjustment ditolak → kembalikan stok
public function reject(int $rejectedBy): void
{
    DB::transaction(function () use ($rejectedBy) {
        // Revert semua stock movement dari adjustment ini
        foreach ($this->movements as $movement) {
            $batch = IngredientBatch::findOrFail($movement->ingredient_batch_id);
            $batch->quantity = $batch->quantity - $movement->quantity_change; // minus negatif = tambah
            $batch->save();

            // Log reversal movement
            StockMovement::create([
                'ingredient_id' => $movement->ingredient_id,
                'ingredient_batch_id' => $movement->ingredient_batch_id,
                'stock_adjustment_id' => $this->id,
                'movement_type' => 'adjustment_reversal',
                'quantity_before' => $batch->quantity + $movement->quantity_change,
                'quantity_change' => -$movement->quantity_change, // balikkan
                'quantity_after' => $batch->quantity,
                'notes' => "Adjustment #{$this->id} ditolak",
            ]);
        }

        $this->status = 'rejected';
        $this->rejected_at = now();
        $this->rejected_by = $rejectedBy;
        $this->save();
    });
}
```

### 3. Expired Batch dalam FEFO

**Kasus:** Ada batch expired yang masih memiliki quantity > 0.

**Penanganan:**

```php
// InventoryService — expired batch DIABAIKAN dalam FEFO
// Saat proses order, batch expired otomatis diskip

$batches = IngredientBatch::where('ingredient_id', $ingredientId)
    ->where('quantity', '>', 0)
    ->where(function ($q) {
        // Hanya ambil batch yang belum expired ATAU tidak punya expiry_date
        $q->where('expiry_date', '>=', now())
          ->orWhereNull('expiry_date');
    })
    ->orderBy('expiry_date', 'asc')
    ->get();
```

### 4. Semua Batch Habis

**Kasus:** Semua batch memiliki quantity = 0, tapi pesanan masuk.

**Penanganan:**

```php
// InventoryService::canFulfillOrder()
// Mengecek kecukupan stok SEBELUM memproses order

$totalAvailable = (float) $batches->sum('quantity');

if ($totalAvailable < $requiredQuantity) {
    throw new Exception(
        "Stok tidak mencukupi untuk bahan '{$ingredient->name}'. " .
        "Dibutuhkan: {$requiredQuantity} {$ingredient->unit}, " .
        "Tersedia: {$totalAvailable} {$ingredient->unit}"
    );
}
```

### 5. Null Handling per Mode

| Mode | Field NULL | Penanganan |
|------|-----------|------------|
| **FIFO** | `received_at` NULL | Batch tanpa `received_at` diurutkan **terakhir**. ORDER BY: `CASE WHEN received_at IS NULL THEN 1 ELSE 0 END, received_at ASC` |
| **FEFO** | `expiry_date` NULL | Batch tanpa `expiry_date` diurutkan **terakhir**. (Bahan non-perishable, tidak expired) |
| **Custom** | `custom_order` NULL | Batch tanpa `custom_order` diurutkan **terakhir**. ORDER BY: `CASE WHEN custom_order IS NULL THEN 1 ELSE 0 END, custom_order ASC, received_at ASC` |

---

## Ringkasan File yang Dibuat/Diubah

| File | Aksi | Deskripsi |
|------|------|-----------|
| `database/migrations/xxxx_add_batch_mode_to_ingredients.php` | **BUAT** | Tambah kolom `batch_mode` ke `ingredients` |
| `database/migrations/xxxx_add_custom_order_to_batches.php` | **BUAT** | Tambah kolom `custom_order` ke `ingredient_batches` |
| `app/Models/Ingredient.php` | **UBAH** | Tambah field `batch_mode` + constants |
| `app/Models/IngredientBatch.php` | **UBAH** | Tambah field `custom_order` + casts |
| `app/Services/InventoryService.php` | **UBAH** | Dynamic batch mode di `deductIngredientStock()` |
| `app/Filament/Resources/IngredientResource.php` | **UBAH** | Tambah Select `batch_mode` di form |
| `app/Filament/Pages/ManageBatchOrder.php` | **BUAT** | Halaman reorderable untuk custom mode |
| `app/Models/StockAdjustment.php` | **UBAH** | Tambah optimistic locking + reject logic |
