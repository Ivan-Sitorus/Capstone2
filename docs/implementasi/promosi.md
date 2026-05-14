# Promosi (Promotions)

> **Modul:** Admin & Marketing  
> **Fase:** Fitur Admin — Manajemen Promosi  
> **Penulis:** Tim Capstone W9 Cafe POS

---

## Daftar Isi

1. [Arsitektur Promosi](#arsitektur-promosi)
2. [Tipe Promosi](#tipe-promosi)
3. [Field Existing vs Field Baru](#field-existing-vs-field-baru)
4. [Stacking Rules (Aturan Penggabungan)](#stacking-rules-aturan-penggabungan)
5. [Implementasi PromotionService](#implementasi-promotionservice)
6. [Filament Resource untuk Promosi](#filament-resource-untuk-promosi)
7. [Edge Cases](#edge-cases)

---

## Arsitektur Promosi

### Model yang Terlibat

```
Promotion (promosi)
├── id, name, type, discount_value, min_purchase
├── start_date, end_date, status
├── applicable_items (JSON), description
├── usage_limit, usage_count
├── is_student_only: BOOLEAN      ← DITAMBAHKAN
├── combinable: BOOLEAN           ← DITAMBAHKAN
├── priority: INTEGER             ← DITAMBAHKAN
│
├──> PromotionRule (aturan aplikasi)
│    └── promotion_id, applicable_type (menu/category), applicable_id
│
└──> AppliedPromotion (log pemakaian)
     └── promotion_id, order_id, discount_amount
```

### File yang Terlibat

| File | Deskripsi |
|------|-----------|
| `app/Models/Promotion.php` | Model promosi — ditambah 3 field baru |
| `app/Models/PromotionRule.php` | Aturan per menu/kategori |
| `app/Models/AppliedPromotion.php` | Log promosi yang sudah dipakai |
| `app/Services/PromotionService.php` | Service untuk validasi & kalkulasi promosi |
| `app/Services/OrderPromotionService.php` | Service khusus order-level promotions |
| `app/Filament/Resources/PromotionResource.php` | Filament CRUD promosi |

### Schema Existing

```sql
-- database/migrations/2026_04_11_000012_create_promotions_table.php
CREATE TABLE promotions (
    id              BIGSERIAL PRIMARY KEY,
    name            VARCHAR(255) NOT NULL,
    type            VARCHAR(50) NOT NULL,  -- percentage, fixed_amount, buy_x_get_y, bundle
    discount_value  DECIMAL(10,2) NOT NULL,
    min_purchase    DECIMAL(10,2) NULL,
    start_date      DATE NOT NULL,
    end_date        DATE NOT NULL,
    status          VARCHAR(20) DEFAULT 'scheduled', -- active, inactive, scheduled, expired
    applicable_items JSON NULL,
    description     TEXT NULL,
    usage_limit     INT UNSIGNED NULL,
    usage_count     INT UNSIGNED DEFAULT 0,
    created_at      TIMESTAMP,
    updated_at      TIMESTAMP,
    deleted_at      TIMESTAMP NULL  -- soft delete
);
```

---

## Tipe Promosi

| Tipe | Konstanta | Deskripsi | Contoh |
|------|-----------|-----------|--------|
| **Percentage** | `Promotion::TYPE_PERCENTAGE` | Diskon persentase | "Diskon 10% untuk mahasiswa" |
| **Fixed Amount** | `Promotion::TYPE_FIXED_AMOUNT` | Potongan nominal tetap | "Potongan Rp 5.000" |
| **Buy X Get Y** | `Promotion::TYPE_BUY_X_GET_Y` | Beli X gratis Y | "Beli 2 gratis 1" |
| **Bundle** | `Promotion::TYPE_BUNDLE` | Paket hemat | "Paket Hemat: Kopi + Roti = Rp 20.000" |

### Cara Kerja per Tipe

```php
// app/Services/PromotionService.php
public function calculateDiscountAmount(Promotion $promotion, float $unitPrice, int $quantity): float
{
    $lineBase = $unitPrice * $quantity;

    // Cek minimum pembelian
    if ($promotion->min_purchase !== null && $lineBase < (float) $promotion->min_purchase) {
        return 0;
    }

    return match ($promotion->type) {
        // Persentase: diskon % dari total
        Promotion::TYPE_PERCENTAGE => min(
            $lineBase,
            $lineBase * ((float) $promotion->discount_value / 100)
        ),

        // Fixed: potongan nominal, tidak boleh melebihi total
        Promotion::TYPE_FIXED_AMOUNT => min(
            $lineBase,
            (float) $promotion->discount_value
        ),

        // BOGO & Bundle: dihitung di OrderPromotionService
        default => 0,
    };
}
```

---

## Field Existing vs Field Baru

### Field yang Sudah Ada

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| `name` | VARCHAR | Nama promosi (contoh: "Diskon Mahasiswa 10%") |
| `type` | ENUM | `percentage`, `fixed_amount`, `buy_x_get_y`, `bundle` |
| `discount_value` | DECIMAL | Nilai diskon (% atau nominal) |
| `min_purchase` | DECIMAL | Minimal pembelian agar promosi berlaku |
| `start_date` | DATE | Tanggal mulai promosi |
| `end_date` | DATE | Tanggal berakhir promosi |
| `status` | ENUM | `active`, `inactive`, `scheduled`, `expired` |
| `applicable_items` | JSON | Daftar item yang berlaku (menu/category IDs) |
| `description` | TEXT | Deskripsi promosi |
| `usage_limit` | INT | Batas pemakaian (null = unlimited) |
| `usage_count` | INT | Jumlah sudah dipakai (auto-increment) |

### Field yang Akan Ditambahkan

```sql
-- Migration: tambah field promosi baru
ALTER TABLE promotions
    ADD COLUMN is_student_only BOOLEAN NOT NULL DEFAULT false,
    ADD COLUMN combinable BOOLEAN NOT NULL DEFAULT false,
    ADD COLUMN priority INTEGER NOT NULL DEFAULT 0;

CREATE INDEX idx_promotions_priority ON promotions(priority);
```

| Field Baru | Tipe | Default | Deskripsi |
|------------|------|---------|-----------|
| `is_student_only` | BOOLEAN | `false` | Hanya berlaku untuk mahasiswa terverifikasi |
| `combinable` | BOOLEAN | `false` | Bisa digabung dengan promosi lain |
| `priority` | INTEGER | `0` | Urutan prioritas (semakin kecil = semakin tinggi prioritas) |

### Model Promotion — Field Baru

```php
// app/Models/Promotion.php (tambahan)
protected $fillable = [
    // ...existing...
    'is_student_only',  // NEW
    'combinable',       // NEW
    'priority',         // NEW
];

protected function casts(): array
{
    return [
        // ...existing...
        'is_student_only' => 'boolean',  // NEW
        'combinable' => 'boolean',       // NEW
        'priority' => 'integer',         // NEW
    ];
}
```

---

## Stacking Rules (Aturan Penggabungan)

Sistem memungkinkan beberapa promosi diterapkan bersamaan pada satu order, dengan aturan yang dikonfigurasi manual oleh admin.

### Konsep: Class-based Stacking

```
Class: Item-Level            Class: Order-Level
─────────────────────       ─────────────────────
│ Percentage (item)  │       │ Student 10%       │
│ Fixed Amount (item)│       │ Diskon Total      │
│ BOGO (item)        │       │ Free Ongkir       │
│ Bundle (item)      │       │                   │
└────────────────────┘       └────────────────────┘
         ↓                          ↓
    Dihitung per item          Dihitung per order
         ↓                          ↓
         └──────────┬───────────────┘
                    ↓
              TOTAL DISKON
         (capped by max_discount)
```

### Aturan Prioritas

1. **Item-level promotions** dihitung duluan (mempengaruhi subtotal per item)
2. **Order-level promotions** dihitung setelahnya (dari subtotal setelah item discount)
3. Dalam class yang sama, prioritas diurut berdasarkan `priority ASC`
4. Jika prioritas sama: **combo → percentage → fixed**

```
Prioritas dalam satu class:
  1. Buy X Get Y / Bundle  (combo)
  2. Percentage             (%)
  3. Fixed Amount           (Rp)
```

### Flag `combinable`

Admin mengatur manual promosi mana yang bisa di-stack:

```php
// Admin memilih promosi mana yang bisa digabung
// Contoh: Diskon Mahasiswa 10% (order-level) + BOGO Kopi (item-level)

// Keduanya bisa stack jika:
// 1. combinable = true pada KEDUA promosi
// 2. Berada di class yang berbeda (item-level vs order-level)
```

### Cap: `max_discount_percentage`

Safeguard agar total diskon tidak melebihi batas wajar:

```php
// Config di .env atau settings
MAX_DISCOUNT_PERCENTAGE=50  // Maksimal 50% diskon total

// Saat kalkulasi:
$totalDiscount = $itemDiscounts + $orderDiscounts;
$maxAllowedDiscount = $orderSubtotal * (MAX_DISCOUNT_PERCENTAGE / 100);

if ($totalDiscount > $maxAllowedDiscount) {
    $totalDiscount = $maxAllowedDiscount;
}
```

### Contoh Stacking

**Skenario:** Mahasiswa membeli 2 kopi + 1 roti

| Promosi | Class | Tipe | Detail |
|---------|-------|------|--------|
| **Diskon Mahasiswa 10%** | Order-level | Percentage | `combinable=true`, `is_student_only=true` |
| **BOGO Kopi** | Item-level | Buy X Get Y | `combinable=true` |

**Kalkulasi:**

```
Harga Kopi Robusta:  Rp 12.000 × 2
Harga Roti Bakar:    Rp 10.000 × 1
Subtotal awal:       Rp 34.000

Step 1: Item-level (BOGO Kopi)
  Beli 2 kopi → gratis 1 kopi termurah
  Diskon item:        -Rp 12.000
  Subtotal setelah:   Rp 22.000

Step 2: Order-level (Diskon Mahasiswa 10%)
  10% × Rp 22.000:    -Rp 2.200
  Total akhir:         Rp 19.800

Total diskon:         Rp 14.200 (41.7% < 50% cap ✅)
```

---

## Implementasi PromotionService

### Validasi Promosi

```php
// app/Services/PromotionService.php (tambahan validasi baru)

public function getApplicablePromotionsForMenu(
    Menu $menu,
    ?User $customer = null
): Collection {
    return $this->loadActivePromotions()
        ->filter(function (Promotion $promotion) use ($menu, $customer) {
            // 1. Promosi masih bisa dipakai
            if (!$promotion->canBeUsed()) {
                return false;
            }

            // 2. Promosi berlaku untuk menu ini
            if (!$promotion->isApplicableTo((int) $menu->id, (int) $menu->category_id)) {
                return false;
            }

            // 3. [NEW] Student-only validation
            if ($promotion->is_student_only) {
                // Customer harus login DAN terverifikasi sebagai mahasiswa
                if (!$customer || !$customer->is_student_verified) {
                    return false;
                }
            }

            // 4. [NEW] Menu soft-deleted check
            // (ditangani di level query — menu yang di-soft-delete tidak muncul)

            return true;
        })
        ->values();
}
```

### Stacking Logic di OrderPromotionService

```php
// app/Services/OrderPromotionService.php (konseptual)

public function calculateOrderDiscounts(Collection $cartItems, ?User $customer): array
{
    // Step 1: Ambil semua promosi aktif yang combinable
    $allPromotions = $this->getActivePromotionsForCart($cartItems, $customer);

    // Step 2: Pisahkan per class
    $itemLevelPromos = $allPromotions->filter(
        fn ($p) => in_array($p->type, ['percentage', 'fixed_amount', 'buy_x_get_y', 'bundle'])
    );
    $orderLevelPromos = $allPromotions->filter(
        fn ($p) => $p->type === 'percentage' && $p->is_student_only
    );

    // Step 3: Hitung item-level duluan (sorted by priority ASC)
    $itemDiscounts = $this->calculateItemLevelDiscounts(
        $itemLevelPromos->sortBy('priority'),
        $cartItems
    );

    // Step 4: Hitung order-level (dari subtotal setelah item discount)
    $subtotalAfterItems = $cartItems->sum('subtotal') - $itemDiscounts['total'];
    $orderDiscounts = $this->calculateOrderLevelDiscounts(
        $orderLevelPromos->sortBy('priority'),
        $subtotalAfterItems
    );

    // Step 5: Terapkan max discount cap
    $totalDiscount = $itemDiscounts['total'] + $orderDiscounts['total'];
    $cap = config('promotions.max_discount_percentage', 50);
    $maxDiscount = $subtotalAfterItems * ($cap / 100);

    if ($totalDiscount > $maxDiscount) {
        // Proporsional scale down
        $scale = $maxDiscount / $totalDiscount;
        $totalDiscount = $maxDiscount;
        // ... scale individual discounts
    }

    return [
        'item_discounts' => $itemDiscounts,
        'order_discounts' => $orderDiscounts,
        'total_discount' => $totalDiscount,
    ];
}
```

---

## Filament Resource untuk Promosi

### Form Field Baru

```php
// app/Filament/Resources/PromotionResource.php (tambahan)

public static function form(Form $form): Form
{
    return $form->schema([
        // ...existing fields (name, type, discount_value, etc.)...

        // ─── NEW FIELDS ───
        Forms\Components\Section::make('Pengaturan Stacking')
            ->schema([
                Toggle::make('combinable')
                    ->label('Bisa Digabung (Combinable)')
                    ->helperText('Jika ON, promosi ini bisa digabung dengan promosi lain')
                    ->default(false),

                Toggle::make('is_student_only')
                    ->label('Khusus Mahasiswa')
                    ->helperText('Jika ON, hanya mahasiswa terverifikasi yang bisa pakai')
                    ->default(false),

                TextInput::make('priority')
                    ->label('Prioritas')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(999)
                    ->default(0)
                    ->helperText('Semakin kecil angka = semakin tinggi prioritas'),
            ])
            ->columns(3),
    ]);
}
```

### Tabel — Kolom Baru

```php
public static function table(Table $table): Table
{
    return $table
        ->columns([
            // ...existing columns...
            IconColumn::make('is_student_only')
                ->label('Mahasiswa')
                ->boolean()
                ->trueIcon('heroicon-o-academic-cap')
                ->falseIcon('heroicon-o-user-group'),
            IconColumn::make('combinable')
                ->label('Stackable')
                ->boolean(),
            TextColumn::make('priority')
                ->label('Prioritas')
                ->sortable(),
        ]);
}
```

---

## Edge Cases

### 1. Promosi Expired Mid-Order

**Kasus:** Pelanggan menambahkan item ke keranjang jam 23:50, promosi berakhir jam 00:00, checkout jam 00:05.

**Penanganan — Validasi ulang saat checkout:**

```php
// Saat checkout (bukan saat add to cart), validasi ulang semua promosi
public function validateAtCheckout(Cart $cart): void
{
    $now = now();

    foreach ($cart->appliedPromotions as $appliedPromo) {
        $promotion = Promotion::find($appliedPromo->promotion_id);

        // Cek ulang semua kondisi
        if (!$promotion) {
            throw new PromotionExpiredException("Promosi '{$appliedPromo->name}' sudah dihapus");
        }

        if ($promotion->end_date->lt($now)) {
            // Remove from cart, notify user
            $cart->removePromotion($appliedPromo);
            throw new PromotionExpiredException(
                "Promosi '{$promotion->name}' sudah berakhir pada " .
                $promotion->end_date->format('d M Y H:i')
            );
        }

        if (!$promotion->canBeUsed()) {
            throw new PromotionExpiredException(
                "Promosi '{$promotion->name}' sudah mencapai batas pemakaian"
            );
        }
    }
}
```

### 2. Multiple Overlap — Student + BOGO

**Kasus:** Mahasiswa terverifikasi, ada promosi Diskon Mahasiswa 10% (order-level) + BOGO Kopi (item-level).

**Penanganan — Stacking berdasarkan combinable + class:**

```
Cek combinable:
  Diskon Mahasiswa 10%  → combinable = true ✅
  BOGO Kopi             → combinable = true ✅

Cek class:
  Diskon Mahasiswa 10%  → order-level
  BOGO Kopi             → item-level

Beda class = BISA STACK ✅

Hasil: item-level dulu (BOGO), lalu order-level (10% dari sisa)
```

### 3. Soft-Deleted Menu

**Kasus:** Admin menghapus menu yang menjadi target promosi.

**Penanganan — Auto-deactivate:**

```php
// Observer di model Menu
class MenuObserver
{
    public function deleted(Menu $menu): void
    {
        // Cari promosi yang mereferensi menu ini
        $promotions = Promotion::whereJsonContains('applicable_items', $menu->id)->get();

        foreach ($promotions as $promotion) {
            // Auto-deactivate promosi
            $promotion->status = Promotion::STATUS_INACTIVE;
            $promotion->save();

            // Atau: hapus menu dari applicable_items
            $items = collect($promotion->applicable_items)
                ->reject(fn ($id) => $id == $menu->id)
                ->values()
                ->toArray();
            $promotion->applicable_items = $items;
            $promotion->save();
        }
    }
}
```

### 4. usage_count Mencapai usage_limit

**Kasus:** Promosi "Diskon Spesial" dengan `usage_limit = 100`, setelah 100 kali dipakai.

**Penanganan — Auto-increment + block:**

```php
// Saat order sukses, increment usage_count
public function incrementUsage(): void
{
    $this->usage_count = (int) $this->usage_count + 1;
    $this->save();

    // Jika sudah mencapai limit, auto-nonaktifkan
    if ($this->usage_limit !== null && $this->usage_count >= $this->usage_limit) {
        $this->status = self::STATUS_EXPIRED;
        $this->save();
    }
}

// Cek saat render dan saat checkout
public function canBeUsed(): bool
{
    return $this->isActive()
        && ($this->usage_limit === null || (int) $this->usage_count < (int) $this->usage_limit);
}
```

### 5. Unverified Student — is_student_only

**Kasus:** Pelanggan login tapi `is_student_verified = false`, mencoba pakai promosi `is_student_only`.

**Penanganan — Promosi TIDAK muncul:**

```php
// PromotionService — filter promosi
if ($promotion->is_student_only) {
    if (!$customer || !$customer->is_student_verified) {
        return false;  // ← Jangan tampilkan promosi ini
    }
}
```

**UX implication:**
- Pelanggan unverified TIDAK melihat promosi `is_student_only` sama sekali
- Tidak ada pesan "verifikasi dulu" — cukup tidak muncul
- Setelah diverifikasi kasir, promosi otomatis muncul di halaman menu

### 6. Promosi dengan min_purchase Tidak Tercapai

**Kasus:** Total order di bawah `min_purchase`.

**Penanganan:**

```php
public function calculateDiscountAmount(Promotion $promotion, float $unitPrice, int $quantity): float
{
    $lineBase = $unitPrice * $quantity;

    // Cek min_purchase
    if ($promotion->min_purchase !== null && $lineBase < (float) $promotion->min_purchase) {
        return 0;  // ← Tidak dapat diskon
    }

    // ... lanjut kalkulasi diskon
}
```

---

## Ringkasan File yang Dibuat/Diubah

| File | Aksi | Deskripsi |
|------|------|-----------|
| `database/migrations/xxxx_add_promo_fields.php` | **BUAT** | Tambah `is_student_only`, `combinable`, `priority` |
| `app/Models/Promotion.php` | **UBAH** | Tambah field baru + casts + validasi |
| `app/Services/PromotionService.php` | **UBAH** | Tambah validasi student-only, stacking |
| `app/Services/OrderPromotionService.php` | **UBAH** | Stacking logic (class-based, combinable, cap) |
| `app/Filament/Resources/PromotionResource.php` | **UBAH** | Form + table + filter untuk field baru |
| `app/Observers/MenuObserver.php` | **BUAT** | Auto-deactivate promosi saat menu dihapus |
| `config/promotions.php` | **BUAT** | Config max_discount_percentage |
