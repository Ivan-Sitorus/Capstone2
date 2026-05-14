# Piutang (Receivables)

> **Modul:** Admin & Finance  
> **Fase:** Fitur Admin — Manajemen Piutang  
> **Penulis:** Tim Capstone W9 Cafe POS

---

## Daftar Isi

1. [Arsitektur Piutang](#arsitektur-piutang)
2. [Alur Piutang (Auto-create & Manual)](#alur-piutang-auto-create--manual)
3. [Partial Payment](#partial-payment)
4. [Manual Receivable (Tanpa Order)](#manual-receivable-tanpa-order)
5. [Tampilan Progress Bar & Detail](#tampilan-progress-bar--detail)
6. [Filament Resource untuk Piutang](#filament-resource-untuk-piutang)
7. [Overdue Handling (Visual Highlight)](#overdue-handling-visual-highlight)
8. [Edge Cases](#edge-cases)

---

## Arsitektur Piutang

### Model Receivable

**File:** `app/Models/Receivable.php` (✅ SUDAH ADA)

```php
class Receivable extends Model
{
    // Status constants
    public const STATUS_PENDING  = 'pending';   // Belum dibayar
    public const STATUS_PARTIAL  = 'partial';   // Dibayar sebagian
    public const STATUS_PAID     = 'paid';      // Lunas
    public const STATUS_OVERDUE  = 'overdue';   // Jatuh tempo

    protected $fillable = [
        'customer_name',     // Nama pelanggan
        'amount',            // Total piutang (Rp)
        'invoice_date',      // Tanggal invoice
        'due_date',          // Jatuh tempo (MANUAL — tidak auto)
        'status',            // pending | partial | paid | overdue
        'paid_amount',       // Sudah dibayar (Rp)
        'notes',             // Catatan
        'order_id',          // FK ke orders (nullable — untuk auto-create)
    ];

    // Accessor: sisa yang harus dibayar
    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->amount - (float) $this->paid_amount);
    }
}
```

### Schema Tabel `receivables`

```sql
-- database/migrations/2026_04_11_000010_create_receivables_table.php
-- + migration: 2026_05_10_060510_add_invoice_date_to_receivables_table.php

CREATE TABLE receivables (
    id              BIGSERIAL PRIMARY KEY,
    customer_name   VARCHAR(100) NOT NULL,
    invoice_date    DATE NULL,
    amount          BIGINT NOT NULL,             -- Total piutang
    paid_amount     BIGINT DEFAULT 0,            -- Sudah dibayar
    status          VARCHAR(20) DEFAULT 'pending', -- pending|partial|paid|overdue
    due_date        DATE NULL,                   -- Jatuh tempo (MANUAL)
    order_id        BIGINT NULL REFERENCES orders(id) ON DELETE SET NULL,
    notes           TEXT NULL,
    created_at      TIMESTAMP,
    updated_at      TIMESTAMP
);

CREATE INDEX idx_receivables_status ON receivables(status);
CREATE INDEX idx_receivables_due_date ON receivables(due_date);
CREATE INDEX idx_receivables_order_id ON receivables(order_id);
```

### Relasi

```
Receivable ──belongsTo──> Order (nullable)
```

---

## Alur Piutang (Auto-create & Manual)

### 1. Auto-create dari Order "Bayar Nanti"

**Trigger:** Kasir membuat order dengan `payment_method = 'bayar_nanti'`

```
┌──────────────┐     ┌───────────────────┐     ┌──────────────────┐
│ Order dibuat │────>│ Payment status:   │────>│ Receivable       │
│ oleh Kasir   │     │ 'unpaid'          │     │ auto-create      │
│ bayar_nanti  │     │ payment_method:   │     │ amount = total   │
└──────────────┘     │ 'bayar_nanti'     │     │ order_id = FK    │
                     └───────────────────┘     │ status = pending │
                                               │ customer_name    │
                                               │ dari order       │
                                               └──────────────────┘
```

```php
// Listener/Observer: auto-create receivable saat order dibuat
class OrderObserver
{
    public function created(Order $order): void
    {
        // Hanya auto-create jika payment_method = bayar_nanti
        if ($order->payment_method !== 'bayar_nanti') {
            return;
        }

        Receivable::create([
            'customer_name' => $order->customer->name ?? 'Guest',
            'amount' => $order->total_amount,
            'invoice_date' => now()->toDateString(),
            'due_date' => null,  // ← selalu manual, tidak auto-set
            'status' => Receivable::STATUS_PENDING,
            'paid_amount' => 0,
            'order_id' => $order->id,
            'notes' => "Auto-generated dari Order #{$order->order_code}",
        ]);
    }
}
```

### 2. Manual Receivable (Tanpa Order)

**Trigger:** Admin membuat piutang manual (misal: pinjaman karyawan, catering event).

```
┌───────────────────────────────────────────────────┐
│  Admin buka Filament → Create Receivable          │
│                                                    │
│  Form:                                             │
│  • Nama Pelanggan: "Bapak Agus"                   │
│  • Jumlah: Rp 500.000                             │
│  • Tanggal Invoice: 12 Mei 2026                   │
│  • Jatuh Tempo: 19 Mei 2026                       │
│  • Catatan: "Catering meeting 15 orang"           │
│  • order_id: NULL (tidak terkait order)           │
│                                                    │
│  Flag created_by: admin                            │
└──────────────────────────────────────────────────┘
```

---

## Partial Payment

### Alur Pembayaran Sebagian

```
Piutang: Rp 500.000
─────────────────────────────────────────
│ Bayar Rp 200.000 → paid_amount = 200K │
│ Status: partial                       │
│ Sisa: Rp 300.000                      │
│───────────────────────────────────────│
│ Bayar Rp 300.000 → paid_amount = 500K │
│ Status: paid ✅                        │
│ Sisa: Rp 0                            │
└───────────────────────────────────────┘
```

### Method `recordPayment()`

**File:** `app/Models/Receivable.php` (✅ SUDAH ADA)

```php
/**
 * Record a payment for this receivable.
 * Automatically updates status based on paid_amount.
 *
 * @param float $amount Payment amount to add
 * @throws \InvalidArgumentException If receivable is already paid
 */
public function recordPayment(float $amount): void
{
    if ($this->status === self::STATUS_PAID) {
        throw new \InvalidArgumentException('Receivable is already fully paid');
    }

    if ($amount <= 0) {
        throw new \InvalidArgumentException('Payment amount must be positive');
    }

    $currentPaid = (float) $this->paid_amount;
    $totalAmount = (float) $this->amount;
    $newPaid = min($currentPaid + $amount, $totalAmount); // jangan lebih dari total

    $this->paid_amount = $newPaid;

    // Auto-update status
    if ($newPaid >= $totalAmount) {
        $this->status = self::STATUS_PAID;
        $this->paid_amount = $totalAmount; // pastikan tepat
    } elseif ($newPaid > 0) {
        $this->status = self::STATUS_PARTIAL;
    }

    $this->save();
}
```

### Form Partial Payment di Filament

```php
// Form input pembayaran di halaman View/Edit Receivable
Forms\Components\Section::make('Catat Pembayaran')
    ->schema([
        TextInput::make('payment_amount')
            ->label('Jumlah Pembayaran')
            ->numeric()
            ->required()
            ->minValue(1)
            ->maxValue(fn ($record) => $record->remaining_amount)
            ->prefix('Rp')
            ->helperText(fn ($record) =>
                "Sisa piutang: Rp " . number_format($record->remaining_amount, 0, ',', '.')
            ),

        DatePicker::make('payment_date')
            ->label('Tanggal Pembayaran')
            ->default(now())
            ->required(),

        Textarea::make('payment_notes')
            ->label('Catatan Pembayaran'),
    ]),
```

---

## Manual Receivable (Tanpa Order)

### Perbedaan Auto vs Manual

| Aspek | Auto-create (dari Order) | Manual (oleh Admin) |
|-------|--------------------------|---------------------|
| **Trigger** | Order `payment_method = bayar_nanti` | Admin input manual |
| **`order_id`** | Terisi (FK ke order) | NULL |
| **`created_by`** | System | Admin user ID |
| **Data order** | Dari order items | N/A (deskripsi manual) |
| **Stok** | Dikurangi saat order | Tidak ada pengurangan stok |
| **Invoice** | Auto-generate | Manual input |

### Form Manual Receivable (Filament)

```php
// app/Filament/Resources/ReceivableResource.php
public static function form(Form $form): Form
{
    return $form->schema([
        Forms\Components\TextInput::make('customer_name')
            ->label('Nama Pelanggan / Peminjam')
            ->required()
            ->maxLength(100),

        Forms\Components\TextInput::make('amount')
            ->label('Jumlah Piutang')
            ->numeric()
            ->required()
            ->minValue(1)
            ->prefix('Rp'),

        Forms\Components\DatePicker::make('invoice_date')
            ->label('Tanggal Invoice')
            ->default(now()),

        Forms\Components\DatePicker::make('due_date')
            ->label('Jatuh Tempo')
            ->helperText('Kosongkan jika tidak ada jatuh tempo'),

        Forms\Components\Textarea::make('notes')
            ->label('Catatan / Deskripsi')
            ->helperText('Jelaskan tujuan piutang (contoh: catering event, pinjaman)')
            ->maxLength(500),

        Forms\Components\Hidden::make('created_by')
            ->default(fn () => auth()->id()),
    ]);
}
```

### View: Detail Receivable

Menampilkan:
- Progress bar pembayaran
- Menu items dari order (jika `order_id` tidak null)
- Promosi yang diterapkan (jika `order_id` tidak null)
- Riwayat pembayaran (partial payments log)

---

## Tampilan Progress Bar & Detail

### Progress Bar

```
┌────────────────────────────────────────────────┐
│  Status: Partial                                │
│                                                  │
│  ████████████░░░░░░░░░░░░░░░  60%               │
│  Rp 300.000 / Rp 500.000                        │
│  Sisa: Rp 200.000                               │
│  Jatuh tempo: 19 Mei 2026 (3 hari lagi)         │
└────────────────────────────────────────────────┘
```

### Implementasi di Filament View Page

```php
// app/Filament/Resources/ReceivableResource/Pages/ViewReceivable.php

use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;

public function infolist(Infolist $infolist): Infolist
{
    return $infolist->schema([
        Section::make('Status Pembayaran')
            ->schema([
                ViewEntry::make('progress')
                    ->view('filament.components.receivable-progress', [
                        'paid' => $this->record->paid_amount,
                        'total' => $this->record->amount,
                        'percentage' => $this->record->amount > 0
                            ? round(($this->record->paid_amount / $this->record->amount) * 100)
                            : 0,
                    ]),
                TextEntry::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'pending' => 'warning',
                        'partial' => 'info',
                        'paid' => 'success',
                        'overdue' => 'danger',
                    }),
                TextEntry::make('due_date')
                    ->date('d M Y'),
            ]),

        Section::make('Detail Order')
            ->visible(fn () => $this->record->order_id !== null)
            ->schema([
                TextEntry::make('order.order_code')
                    ->label('Kode Order'),
                TextEntry::make('order.total_amount')
                    ->label('Total Order')
                    ->money('IDR'),
                // Menu items dari order
                ViewEntry::make('order_items')
                    ->view('filament.components.order-items-list', [
                        'items' => $this->record->order?->items ?? [],
                    ]),
            ]),

        Section::make('Riwayat Pembayaran')
            ->schema([
                // Tabel riwayat partial payments
            ]),
    ]);
}
```

### Blade Component: Progress Bar

```blade
{{-- resources/views/filament/components/receivable-progress.blade.php --}}
<div class="space-y-2">
    <div class="flex justify-between text-sm">
        <span class="text-gray-600">Terbayar</span>
        <span class="font-semibold">
            Rp {{ number_format($paid, 0, ',', '.') }} / Rp {{ number_format($total, 0, ',', '.') }}
        </span>
    </div>

    {{-- Progress bar --}}
    <div class="w-full bg-gray-200 rounded-full h-3">
        <div class="h-3 rounded-full transition-all duration-300
            {{ $percentage >= 100 ? 'bg-green-500' : ($percentage > 0 ? 'bg-blue-500' : 'bg-gray-300') }}"
            style="width: {{ $percentage }}%">
        </div>
    </div>

    <div class="flex justify-between text-xs text-gray-500">
        <span>{{ $percentage }}% terbayar</span>
        <span>Sisa: Rp {{ number_format(max(0, $total - $paid), 0, ',', '.') }}</span>
    </div>
</div>
```

---

## Filament Resource untuk Piutang

### Tabel ListReceivables

```php
// app/Filament/Resources/ReceivableResource/Pages/ListReceivables.php

public function table(Table $table): Table
{
    return $table
        ->query(Receivable::query()->with('order'))
        ->columns([
            TextColumn::make('customer_name')
                ->label('Pelanggan')
                ->searchable()
                ->sortable(),

            TextColumn::make('amount')
                ->label('Total')
                ->money('IDR')
                ->sortable(),

            TextColumn::make('paid_amount')
                ->label('Terbayar')
                ->money('IDR'),

            // Progress bar inline
            TextColumn::make('progress')
                ->label('Progress')
                ->view('filament.tables.columns.receivable-progress'),

            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->color(fn ($state) => match ($state) {
                    'pending' => 'warning',
                    'partial' => 'info',
                    'paid' => 'success',
                    'overdue' => 'danger',
                }),

            TextColumn::make('due_date')
                ->label('Jatuh Tempo')
                ->date('d M Y')
                ->sortable(),

            TextColumn::make('days_overdue')
                ->label('Overdue')
                ->state(function (Receivable $record): ?string {
                    if ($record->status === 'paid' || !$record->due_date) {
                        return null;
                    }
                    $days = now()->startOfDay()->diffInDays($record->due_date, false);
                    if ($days <= 0) return null;
                    return $days . ' hari';
                })
                ->badge()
                ->color(fn ($state) => match (true) {
                    $state === null => null,
                    str_contains($state, '7') => 'warning',
                    str_contains($state, '14') => 'danger',
                    default => 'warning',
                }),
        ])
        ->filters([
            SelectFilter::make('status')
                ->options([
                    'pending' => 'Pending',
                    'partial' => 'Partial',
                    'paid' => 'Lunas',
                    'overdue' => 'Overdue',
                ]),
        ]);
}
```

### Filter Dashboard Khusus

```php
// Filter: "Piutang > 7 Hari"
// Preset filter yang langsung menampilkan piutang overdue lebih dari 7 hari

public function getTableFilters(): array
{
    return [
        SelectFilter::make('status')
            ->options(Receivable::STATUSES),

        // Preset filter: overdue > 7 hari
        Filter::make('overdue_gt_7')
            ->label('Piutang > 7 Hari')
            ->query(fn ($query) => $query
                ->where('status', '!=', Receivable::STATUS_PAID)
                ->whereNotNull('due_date')
                ->where('due_date', '<', now()->subDays(7))
            ),
    ];
}
```

---

## Overdue Handling (Visual Highlight)

### Klasifikasi Overdue

| Rentang Hari Overdue | Warna | Badge |
|---------------------|-------|-------|
| 1 - 7 hari | **Kuning** (`#FFC107`) | `● Overdue` |
| 8 - 14 hari | **Oranye** (`#FF9800`) | `● Overdue 10hr` |
| > 14 hari | **Merah** (`#DC3545`) | `● Overdue 21hr` |

### CSS untuk Visual Highlight

```css
/* Row highlight berdasarkan overdue */
.receivable-overdue-warning {
    background-color: #FFF8E1 !important; /* kuning muda */
}
.receivable-overdue-orange {
    background-color: #FFF3E0 !important; /* oranye muda */
}
.receivable-overdue-danger {
    background-color: #FFEBEE !important; /* merah muda */
}
```

### Implementasi di Filament Table Row

```php
// Di ListReceivables table
TextColumn::make('due_date')
    ->label('Jatuh Tempo')
    ->date('d M Y')
    ->color(fn (Receivable $record) => match (true) {
        $record->status === 'paid' => null,
        $record->isOverdue() && $record->due_date->diffInDays(now()) > 14 => 'danger',
        $record->isOverdue() && $record->due_date->diffInDays(now()) > 7 => 'warning',
        $record->isOverdue() => 'warning',
        default => null,
    });
```

### Tidak Ada Blokir atau Denda

**Penting:** Sistem hanya memberikan **visual highlight** (warna baris, badge hari overdue, filter). Tidak ada:
- ❌ Blokir customer (tetap bisa order meskipun punya piutang)
- ❌ Denda otomatis (tidak ada perhitungan denda keterlambatan)
- ❌ Auto-suspend akun

Keputusan penagihan diserahkan ke admin/kasir secara manual.

---

## Edge Cases

### 1. Order "Bayar Nanti" Dicancel

**Kasus:** Kasir membatalkan order yang dibuat dengan `payment_method = bayar_nanti`.

**Penanganan — Auto-void receivable:**

```php
// Observer: saat order di-cancel
class OrderObserver
{
    public function updated(Order $order): void
    {
        if ($order->isDirty('status') && $order->status === 'cancelled') {
            // Cari receivable terkait
            $receivable = Receivable::where('order_id', $order->id)->first();

            if ($receivable && $receivable->status !== Receivable::STATUS_PAID) {
                // Auto-void: status jadi cancelled, amount jadi 0
                $receivable->status = 'void';
                $receivable->notes = ($receivable->notes ?? '') .
                    "\n[DIBATALKAN] Order #{$order->order_code} dicancel pada " .
                    now()->toDateTimeString();
                $receivable->save();

                // Kembalikan stok (jika sebelumnya sudah dikurangi)
                // InventoryService::revertStockForOrder($order);
            }
        }
    }
}
```

### 2. Overdue — Visual Highlight Saja

**Kasus:** Piutang sudah lewat jatuh tempo.

**Penanganan — Badge + warna row:**

```
Tabel Receivable:

┌──────────┬──────────┬────────────┬──────────┬─────────────┐
│ Pelanggan│ Total    │ Jatuh Tempo│ Status   │ Overdue     │
├──────────┼──────────┼────────────┼──────────┼─────────────┤
│ Budi     │ 50.000   │ 10 Mei     │ Pending  │ ⚠ 2 hari    │ ← kuning
│ Ani      │ 200.000  │ 28 Apr     │ Partial  │ ⚠ 14 hari   │ ← oranye
│ Cici     │ 500.000  │ 10 Apr     │ Overdue  │ ⚠ 32 hari   │ ← merah
│ Dodi     │ 100.000  │ 15 Mei     │ Paid     │ -           │ ← normal
└──────────┴──────────┴────────────┴──────────┴─────────────┘
```

### 3. Manual Receivable Tanpa Order

**Kasus:** Admin memasukkan piutang manual, tidak terkait order manapun.

**Penanganan — Seperti order normal, tapi dengan flag:**

```php
// Tidak ada perbedaan fungsional — hanya metadata
// Flag pembeda: order_id = null

// Perilaku:
// - Order items: tidak ada (karena bukan dari order)
// - Stok: tidak berkurang (karena bukan penjualan menu)
// - Invoice: deskripsi manual dari admin
// - Pembayaran: sama seperti receivable biasa (partial payment)
```

### 4. Overdue Auto-Status Update

**Kasus:** Piutang yang `status = pending` tapi `due_date` sudah lewat.

**Penanganan — Scheduled task atau query scope:**

```php
// Opsi 1: Scheduled task (setiap malam)
// app/Console/Kernel.php
$schedule->call(function () {
    Receivable::where('status', Receivable::STATUS_PENDING)
        ->whereNotNull('due_date')
        ->where('due_date', '<', now())
        ->update(['status' => Receivable::STATUS_OVERDUE]);
})->daily();

// Opsi 2: Dynamic via accessor (tidak update DB, hanya tampilan)
public function getDynamicStatusAttribute(): string
{
    if ($this->status === self::STATUS_PAID) {
        return self::STATUS_PAID;
    }
    if ($this->isOverdue()) {
        return self::STATUS_OVERDUE;
    }
    return $this->status;
}
```

### 5. Pembayaran Melebihi Total Piutang

**Kasus:** Admin/sistem mencoba input pembayaran lebih besar dari sisa piutang.

**Penanganan — Guard di `recordPayment()`:**

```php
// Sudah dihandle di Receivable::recordPayment()
$newPaid = min($currentPaid + $amount, $totalAmount);
// ↑ tidak akan pernah melebihi totalAmount

// Throw exception jika sudah lunas
if ($this->status === self::STATUS_PAID) {
    throw new \InvalidArgumentException('Receivable is already fully paid');
}
```

### 6. Order dengan Receivable Dihapus (Soft Delete)

**Kasus:** Order yang memiliki receivable dihapus.

**Penanganan — nullOnDelete:**

```sql
-- Migration sudah menangani:
order_id BIGINT NULL REFERENCES orders(id) ON DELETE SET NULL
-- ↑ Saat order dihapus, order_id di receivable jadi NULL
--   Receivable tetap ada, hanya tidak terhubung ke order
```

---

## Ringkasan File yang Dibuat/Diubah

| File | Aksi | Deskripsi |
|------|------|-----------|
| `app/Models/Receivable.php` | ✅ SUDAH ADA | Model + `recordPayment()` + scopes |
| `app/Filament/Resources/ReceivableResource.php` | ✅ SUDAH ADA | CRUD resource |
| `app/Filament/Resources/ReceivableResource/Pages/ListReceivables.php` | **UBAH** | Tambah filter overdue, badge days, progress bar |
| `app/Filament/Resources/ReceivableResource/Pages/ViewReceivable.php` | **UBAH** | Progress bar, order items, riwayat pembayaran |
| `app/Filament/Resources/ReceivableResource/Pages/CreateReceivable.php` | **UBAH** | Form manual receivable (tanpa order) |
| `app/Observers/OrderObserver.php` | **BUAT** | Auto-create receivable + auto-void saat cancel |
| `resources/views/filament/components/receivable-progress.blade.php` | **BUAT** | Blade component progress bar |
| `resources/views/filament/tables/columns/receivable-progress.blade.php` | **BUAT** | Table column progress bar |
| `app/Console/Kernel.php` | **UBAH** | Scheduled task untuk auto-update overdue |
