# Cash Flow (Arus Kas)

> **Modul:** Admin & Finance  
> **Fase:** Migrasi Blade ke Native Filament  
> **Penulis:** Tim Capstone W9 Cafe POS

---

## Daftar Isi

1. [Status Saat Ini](#status-saat-ini)
2. [Perubahan dari Blade ke Filament Native](#perubahan-dari-blade-ke-filament-native)
3. [CashFlow Page (Native Filament)](#cashflow-page-native-filament)
4. [Income Model](#income-model)
5. [Expense Model](#expense-model)
6. [Widgets dan Chart](#widgets-dan-chart)
7. [Diagram Alur Data](#diagram-alur-data)

---

## Status Saat Ini

### Yang Sudah Ada

| Komponen | Status | Path |
|----------|--------|------|
| `CashFlow.php` (Filament Page) | ✅ ADA | `app/Filament/Pages/CashFlow.php` |
| `CashFlowChartWidget.php` | ✅ ADA | `app/Filament/Widgets/CashFlowChartWidget.php` |
| `CashFlowStatsWidget.php` | ✅ ADA | `app/Filament/Widgets/CashFlowStatsWidget.php` |
| Migration `incomes` | ✅ ADA | `database/migrations/2026_04_11_000008_create_incomes_table.php` |
| Migration `expenses` | ✅ ADA | `database/migrations/2026_05_10_060713_create_expenses_table.php` |
| Model `Expense.php` | ✅ ADA | `app/Models/Expense.php` |
| Blade view cash-flow | ✅ ADA | `resources/views/filament/pages/cash-flow.blade.php` |

### Yang Belum Ada / Perlu Dibuat

| Komponen | Status | Keterangan |
|----------|--------|------------|
| Model `Income.php` | ❌ BELUM ADA | Migration ada, model belum dibuat |
| Native Filament widgets untuk CashFlow page | ⚠️ PARTIAL | Widget ada tapi page masih pakai Blade view |
| Export CSV/Excel (Filament bulk action) | ❌ BELUM ADA | |

---

## Perubahan dari Blade ke Filament Native

### Masalah Existing

Halaman Cash Flow saat ini menggunakan **Blade view** sebagai renderer meskipun sudah didefinisikan sebagai Filament Page:

```php
// app/Filament/Pages/CashFlow.php (SAAT INI)
class CashFlow extends Page
{
    // ❌ Masih menggunakan Blade view — BUKAN native Filament
    protected string $view = 'filament.pages.cash-flow';

    public string $period = 'day';
    public ?string $date_start = null;
    public ?string $date_end = null;

    public function mount(): void
    {
        $this->period = 'day';
    }

    public function updatedPeriod(): void
    {
        // Dispatch ke Livewire widgets
        $this->dispatch('cashflow-period-changed', period: $this->period);
    }
}
```

**Masalah dengan pendekatan Blade:**
1. Filament Page seharusnya menggunakan native Filament widgets, bukan Blade view
2. Tidak konsisten dengan halaman Filament lainnya
3. Sulit di-maintain karena campur Blade + Livewire + Filament

### Target Implementasi

```php
// app/Filament/Pages/CashFlow.php (TARGET — Native Filament)
use Filament\Pages\Page;

class CashFlow extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Finance Details';
    protected static ?string $navigationLabel = 'Cash Flow';
    protected static ?string $title = 'Cash Flow';
    protected static ?int $navigationSort = 10;

    // ✅ HAPUS: protected string $view = 'filament.pages.cash-flow';
    // ✅ Gunakan native Filament page widgets

    public string $period = 'today'; // 'today'|'week'|'month'|'year'|'all'

    protected function getHeaderWidgets(): array
    {
        return [
            CashFlowStatsWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            CashFlowChartWidget::class,
        ];
    }

    protected function getViewData(): array
    {
        return [
            'period' => $this->period,
        ];
    }
}
```

### Period Tabs (Native Filament)

Menggantikan filter period Blade dengan Filament native tabs:

```
[Hari Ini] [Minggu Ini] [Bulan Ini] [Tahun Ini] [Semua]
```

```php
// Period state management
public string $period = 'today';

public function setPeriod(string $period): void
{
    $this->period = $period;
    $this->dispatch('cashflow-period-changed', period: $period);
}
```

---

## CashFlow Page (Native Filament)

### Hapus Dependency Blade View

**File yang berubah:** `app/Filament/Pages/CashFlow.php`

```diff
- protected string $view = 'filament.pages.cash-flow';
+ // HAPUS — gunakan native Filament rendering
+ // Widgets didefinisikan via getHeaderWidgets() dan getFooterWidgets()
```

**File Blade yang dihapus/diabaikan:** `resources/views/filament/pages/cash-flow.blade.php`

### Widgets pada CashFlow Page

| Posisi | Widget | Deskripsi |
|--------|--------|-----------|
| **Header** | `CashFlowStatsWidget` | 4 card: Pemasukan, Pengeluaran, Nett, Margin |
| **Content** | `CashFlowChartWidget` | Line chart pemasukan vs pengeluaran per periode |
| **Filter** | Period Tabs | Hari Ini, Minggu Ini, Bulan Ini, Tahun Ini, Semua |

### Stats Cards (CashFlowStatsWidget)

```php
// app/Filament/Widgets/CashFlowStatsWidget.php
class CashFlowStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Pemasukan', $this->getTotalIncome())
                ->description('Total pemasukan periode ini')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Pengeluaran', $this->getTotalExpense())
                ->description('Total pengeluaran periode ini')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),

            Stat::make('Nett', $this->getNetCashFlow())
                ->description($this->getNetDescription())
                ->color($this->getNetCashFlow() >= 0 ? 'success' : 'danger'),

            Stat::make('Margin', $this->getMarginPercentage() . '%')
                ->description('Margin keuntungan')
                ->color('info'),
        ];
    }
}
```

### Line Chart (CashFlowChartWidget)

```php
// app/Filament/Widgets/CashFlowChartWidget.php
class CashFlowChartWidget extends ChartWidget
{
    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Pemasukan',
                    'data' => $this->getIncomeData(),
                    'borderColor' => '#28A745',
                    'backgroundColor' => 'rgba(40, 167, 69, 0.1)',
                ],
                [
                    'label' => 'Pengeluaran',
                    'data' => $this->getExpenseData(),
                    'borderColor' => '#DC3545',
                    'backgroundColor' => 'rgba(220, 53, 69, 0.1)',
                ],
            ],
            'labels' => $this->getPeriodLabels(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
```

---

## Income Model

> **Status:** Migration `create_incomes_table` sudah ada, tetapi **model `Income.php` belum dibuat**.

### Schema Tabel `incomes`

```sql
-- database/migrations/2026_04_11_000008_create_incomes_table.php
CREATE TABLE incomes (
    id          BIGSERIAL PRIMARY KEY,
    source      VARCHAR(255) NOT NULL,      -- Sumber pemasukan
    category    VARCHAR(100) NOT NULL,        -- Kategori (penjualan, lainnya)
    amount      DECIMAL(12, 2) NOT NULL,     -- Jumlah (Rp)
    date        DATE NOT NULL,               -- Tanggal transaksi
    description TEXT NULL,                   -- Deskripsi (opsional)
    created_at  TIMESTAMP,
    updated_at  TIMESTAMP
);

CREATE INDEX idx_incomes_date ON incomes(date);
CREATE INDEX idx_incomes_category ON incomes(category);
```

### Model Income.php (yang akan dibuat)

**File:** `app/Models/Income.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Income extends Model
{
    protected $fillable = [
        'source',
        'category',
        'amount',
        'date',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    /**
     * Scope: filter by date range.
     */
    public function scopeBetweenDates(Builder $query, string $start, string $end): void
    {
        $query->whereBetween('date', [$start, $end]);
    }

    /**
     * Scope: filter by category.
     */
    public function scopeOfCategory(Builder $query, string $category): void
    {
        $query->where('category', $category);
    }
}
```

---

## Expense Model

### Schema Tabel `expenses`

```sql
-- database/migrations/2026_05_10_060713_create_expenses_table.php
CREATE TABLE expenses (
    id              BIGSERIAL PRIMARY KEY,
    vendor          VARCHAR(100) NOT NULL,       -- Vendor/pemasok
    category        VARCHAR(50) NOT NULL,         -- Kategori (bahan_baku, operasional, dll)
    amount          BIGINT NOT NULL,              -- Jumlah (Rp, integer)
    date            DATE NOT NULL,                -- Tanggal transaksi
    description     VARCHAR(255) NULL,            -- Deskripsi (opsional)
    payment_method  VARCHAR(20) NULL,             -- Metode bayar (cash, transfer, qris)
    created_at      TIMESTAMP,
    updated_at      TIMESTAMP
);
```

### Model Expense.php (existing)

**File:** `app/Models/Expense.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Expense extends Model
{
    protected $fillable = [
        'vendor',
        'category',
        'amount',
        'date',
        'description',
        'payment_method',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => 'integer',
        ];
    }

    /**
     * Scope: filter by date range.
     */
    public function scopeBetweenDates(Builder $query, string $start, string $end): void
    {
        $query->whereBetween('date', [$start, $end]);
    }

    /**
     * Scope: filter by category.
     */
    public function scopeOfCategory(Builder $query, string $category): void
    {
        $query->where('category', $category);
    }

    /**
     * Scope: filter by payment method.
     */
    public function scopeWithPaymentMethod(Builder $query, string $method): void
    {
        $query->where('payment_method', $method);
    }
}
```

---

## Widgets dan Chart

### CashFlowChartWidget

Menggunakan Filament ChartWidget (Chart.js) untuk line chart:

```php
// app/Filament/Widgets/CashFlowChartWidget.php (detail)
use Filament\Widgets\ChartWidget;

class CashFlowChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Arus Kas';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';

    // Listen untuk period change event
    protected $listeners = ['cashflow-period-changed' => '$refresh'];

    protected function getData(): array
    {
        $period = $this->getPageProperty('period', 'today');

        $incomeData = $this->fetchIncomeData($period);
        $expenseData = $this->fetchExpenseData($period);

        return [
            'datasets' => [
                [
                    'label' => 'Pemasukan',
                    'data' => $incomeData,
                    'borderColor' => '#28A745',
                    'backgroundColor' => 'rgba(40, 167, 69, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Pengeluaran',
                    'data' => $expenseData,
                    'borderColor' => '#DC3545',
                    'backgroundColor' => 'rgba(220, 53, 69, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $this->getPeriodLabels($period),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    private function fetchIncomeData(string $period): array
    {
        return match ($period) {
            'today' => [Income::whereDate('date', today())->sum('amount')],
            'week' => $this->fetchWeeklyData('income'),
            'month' => $this->fetchMonthlyData('income'),
            'year' => $this->fetchYearlyData('income'),
            default => $this->fetchYearlyData('income'),
        };
    }

    private function fetchExpenseData(string $period): array
    {
        return match ($period) {
            'today' => [Expense::whereDate('date', today())->sum('amount')],
            'week' => $this->fetchWeeklyData('expense'),
            'month' => $this->fetchMonthlyData('expense'),
            'year' => $this->fetchYearlyData('expense'),
            default => $this->fetchYearlyData('expense'),
        };
    }
}
```

### CashFlowStatsWidget

```php
// app/Filament/Widgets/CashFlowStatsWidget.php
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CashFlowStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $period = $this->getPageProperty('period', 'today');

        $income = $this->getTotalIncome($period);
        $expense = $this->getTotalExpense($period);
        $net = $income - $expense;
        $margin = $income > 0 ? round(($net / $income) * 100, 1) : 0;

        return [
            Stat::make('Total Pemasukan', 'Rp ' . number_format($income, 0, ',', '.'))
                ->description('Periode: ' . ucfirst($period))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart($this->getIncomeSparkline($period)),

            Stat::make('Total Pengeluaran', 'Rp ' . number_format($expense, 0, ',', '.'))
                ->description('Periode: ' . ucfirst($period))
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger')
                ->chart($this->getExpenseSparkline($period)),

            Stat::make('Arus Kas Bersih', 'Rp ' . number_format($net, 0, ',', '.'))
                ->description($net >= 0 ? 'Surplus' : 'Defisit')
                ->color($net >= 0 ? 'success' : 'danger'),

            Stat::make('Margin', $margin . '%')
                ->description('Margin keuntungan')
                ->color($margin >= 20 ? 'success' : ($margin >= 0 ? 'warning' : 'danger')),
        ];
    }
}
```

### Event Communication antar Widget

```php
// CashFlow Page ↔ Widgets
// Page dispatch event saat period berubah
public function updatedPeriod(): void
{
    $this->dispatch('cashflow-period-changed', period: $this->period);
}

// Widget listen event
class CashFlowChartWidget extends ChartWidget
{
    protected $listeners = [
        'cashflow-period-changed' => 'refreshData',
    ];

    public function refreshData(string $period): void
    {
        // Refresh chart dengan period baru
    }
}
```

---

## Diagram Alur Data

```
┌──────────────────────────────────────────────────────────┐
│  CashFlow Page (Filament)                                │
│                                                          │
│  ┌──────────────────────────────────────────────────┐   │
│  │ Period Tabs: Hari Ini | Minggu | Bulan | Tahun   │   │
│  └──────────────────────────────────────────────────┘   │
│                        │                                 │
│         ┌──────────────┼──────────────┐                  │
│         ▼              ▼              ▼                  │
│  ┌───────────┐  ┌───────────┐                           │
│  │ Stats     │  │ Chart     │                           │
│  │ Widget    │  │ Widget    │                           │
│  │           │  │           │                           │
│  │ Income    │  │ Line      │                           │
│  │ Expense   │  │ Chart     │                           │
│  │ Nett      │  │ Pemasukan │                           │
│  │ Margin    │  │ vs        │                           │
│  │           │  │ Pengel.   │                           │
│  └─────┬─────┘  └─────┬─────┘                           │
│        │              │                                  │
│        ▼              ▼                                  │
│  ┌──────────────────────────────────────────┐          │
│  │            Database                       │          │
│  │  ┌──────────┐    ┌──────────┐            │          │
│  │  │ incomes  │    │ expenses │            │          │
│  │  └──────────┘    └──────────┘            │          │
│  └──────────────────────────────────────────┘          │
└──────────────────────────────────────────────────────────┘
```

### Ringkasan File yang Dibuat/Diubah

| File | Aksi | Deskripsi |
|------|------|-----------|
| `app/Models/Income.php` | **BUAT** | Model untuk tabel `incomes` |
| `app/Filament/Pages/CashFlow.php` | **UBAH** | Hapus Blade view → native Filament |
| `app/Filament/Widgets/CashFlowStatsWidget.php` | **UBAH** | Tambah margin stat, period handler |
| `app/Filament/Widgets/CashFlowChartWidget.php` | **UBAH** | Tambah period-aware data fetching |
| `resources/views/filament/pages/cash-flow.blade.php` | **HAPUS** | Tidak digunakan lagi |
