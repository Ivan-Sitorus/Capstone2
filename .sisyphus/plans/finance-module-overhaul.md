# Finance Module Overhaul — Receivables, Cash Flow & Financial Reports

## TL;DR

> **Quick Summary**: Overhaul modul keuangan POS W9 Cafe — tambah integrasi piutang ke pesanan, optimasi CashFlow yang lambat, bangun laporan keuangan 3 tipe (Simple/Rigid/Custom) dengan export PDF/CSV/Excel, dan template laporan yang bisa disimpan admin.
>
> **Deliverables**:
> - Receivable terintegrasi dengan Order via FK `order_id`
> - Auto-create Receivable saat order pakai `bayar_nanti`
> - CashFlow page proper Filament dengan free date range + query optimization
> - Laporan Simple (ringkasan), Rigid (Laba Rugi + Arus Kas), Custom (pilih kategori + simpan template)
> - Export PDF (barryvdh/laravel-dompdf), CSV & Excel (maatwebsite/laravel-excel)
> - Data migration: incomes → unexpected_transactions, tambah FK receivables
>
> **Estimated Effort**: Large (25 tasks, 5 waves)
> **Parallel Execution**: YES — 5 waves
> **Critical Path**: Wave 1 (migration) → Wave 2 (CashFlow rebuild) → Wave 4 (reports) → Wave 5 (export)

---

## Context

### Original Request
Stakeholder pemilik cafe ingin:
1. Mencatat pemasukan, pengeluaran, dan piutang/receivables (cafe memberikan utang ke pelanggan event)
2. Piutang terintegrasi dengan data menu dan stok
3. Rekap laporan keuangan fleksibel: sederhana, rigid akuntansi, atau custom (pilih kategori + simpan template)
4. Free date range untuk semua laporan
5. Download laporan: PDF, CSV, Excel format rapi
6. Halaman cash flow terasa lambat — perlu optimasi
7. Gunakan Filament/Laravel built-in dulu, baru library PHP ecosystem, terakhir custom

### Interview Summary

**Key Discussions**:
- **Receivables**: FK `order_id` ke orders. Customer = event organizer (free text `customer_name`), bukan user sistem. Order dibuat normal via kasir.
- **Income unification**: Semua pemasukan dari Orders (`is_paid=true` + `UnexpectedTransaction::jenis='pemasukan'`). Tabel `incomes` di-drop setelah data dimigrasi.
- **Expenses**: Tiga sumber — `expenses` table (operasional), `IngredientBatch` costs (pembelian bahan baku), `UnexpectedTransaction::jenis='pengeluaran'`. Semua dijumlahkan.
- **Reports**: Simple (ringkasan in/out/net), Rigid (Income Statement + Cash Flow Statement), Custom (pilih kategori, simpan template). Semua free date range.
- **Testing**: Tests after implementation.

**Research Findings**:
- Widgets CashFlowStatsWidget & CashFlowChartWidget sudah benar query dari Orders + IngredientBatch + UnexpectedTransaction — blade page CashFlow.php yang tidak sinkron (pakai Income::)
- Tidak ada export package di composer.json — perlu install `maatwebsite/laravel-excel` + `barryvdh/laravel-dompdf`
- Order model punya `customer_name` free text, `payment_method`, `is_paid` — cocok untuk integrasi receivable
- `bayar_nanti` belum ada sebagai payment method — perlu ditambahkan
- `expenses` table dan `ExpenseResource` tersembunyi (`$shouldRegisterNavigation = false`) — perlu direstore navigasinya

### Metis Review

**Identified Gaps** (addressed):
1. **Dual income source conflict**: Widgets vs blade page. Solution: unify semua ke Orders + UnexpectedTransaction, hapus incomes table
2. **Historical incomes data**: Dimigrasi ke unexpected_transactions sebelum drop
3. **Three expense sources**: expenses + IngredientBatch + UnexpectedTransaction, semua berkontribusi
4. **bayar_nanti gap**: Auto-create Receivable saat order dibuat dengan payment_method `bayar_nanti`
5. **Template system scope creep**: Locked sebagai FILTER PRESETS (bukan drag-and-drop builder). Data model: `{name, user_id, config:json}` dengan config = `{date_start, date_end, categories[], aggregation}`
6. **FK constraint**: `nullable()->constrained('orders')->nullOnDelete()` — receivable tetap ada walau order dihapus
7. **Data migration pre-check**: Hitung jumlah row di incomes table, present ke admin sebelum drop
8. **No polling/websocket**: CashFlow refresh on period change only
9. **Existing Filament chart widget**: Pertahankan — enhance data pipeline, jangan ganti library chart

---

## Work Objectives

### Core Objective
Bangun modul keuangan terintegrasi: piutang tertaut ke pesanan, cash flow optimal & konsisten, laporan keuangan multi-format dengan template tersimpan dan export rapi.

### Concrete Deliverables
- Migration: `add_order_id_to_receivables`, `migrate_incomes_to_unexpected_transactions`
- Model: `Receivable` updated (FK + event listener), `ReportTemplate` baru
- Resources: `ReceivableResource` enhanced, `ExpenseResource` restored, `IncomeResource` retired
- Pages: `CashFlow` rebuilt as proper Filament, `FinancialReport` new page
- Widgets: `CashFlowStatsWidget` + `CashFlowChartWidget` enhanced (free date range)
- Exports: `ReportExporter` (Excel), `ReportPdfExport` (PDF), CSV via Excel package
- Packages: `maatwebsite/laravel-excel`, `barryvdh/laravel-dompdf`

### Definition of Done
- [ ] `php artisan migrate:fresh --seed` runs without errors
- [ ] CashFlow page loads in <3s with 10k+ orders
- [ ] Receivable list shows linked order_code when order_id present
- [ ] Creating order with `bayar_nanti` auto-creates Receivable record
- [ ] Simple report matches `SUM(total_amount) FROM orders WHERE is_paid=true` + unexpected income
- [ ] Rigid report sections (Income Statement + Cash Flow) totals match Simple report
- [ ] Custom report template saves & reloads correctly
- [ ] PDF/CSV/Excel export generates valid files with correct encoding

### Must Have
- FK `order_id` pada receivables (nullable)
- Auto-create Receivable pada order `bayar_nanti`
- CashFlow dengan free date range
- 3 tipe laporan (Simple/Rigid/Custom) + simpan template
- Export PDF, CSV, Excel
- Data migration yang preserve historical data

### Must NOT Have (Guardrails)
- **NO** drag-and-drop report builder — template = filter presets only
- **NO** polling/websocket/real-time pada CashFlow
- **NO** perubahan pada Order model, OrderResource, atau order statuses (hanya READ)
- **NO** auto-create Receivable untuk payment_method selain `bayar_nanti`
- **NO** delete data tanpa konfirmasi migration pre-check
- **NO** N+1 queries — semua report aggregation pakai database-level SUM/COUNT
- **NO** perubahan pada StatsOverview widget (dashboard) — hanya CashFlow widgets
- **NO** library charting baru — pakai Filament ChartWidget existing
- **NO** receivable aging report (30/60/90 hari) — out of scope
- **NO** tax calculation, multi-currency, budgeting/forecasting
- **NO** CashierSession reconciliation — out of scope

---

## Verification Strategy (MANDATORY)

> **ZERO HUMAN INTERVENTION** — ALL verification is agent-executed. No exceptions.

### Test Decision
- **Infrastructure exists**: YES (PHPUnit via Laravel)
- **Automated tests**: Tests-after
- **Framework**: PHPUnit (Laravel built-in)

### QA Policy
Every task MUST include agent-executed QA scenarios. Evidence saved to `.sisyphus/evidence/task-{N}-{scenario-slug}.{ext}`.

- **Backend/Migration**: Bash (`php artisan` commands, `psql` queries)
- **Filament UI**: Playwright (navigate, fill forms, assert table/display, screenshot)
- **Export/File**: Bash (`file` command, `head` for CSV, file size check)
- **API/Query**: Bash (curl, psql raw queries for data verification)

---

## Execution Strategy

### Parallel Execution Waves

```
Wave 1 (Start Immediately — migration + foundation):
├── Task 1: Migration: add order_id FK to receivables [quick]
├── Task 2: Migration: migrate incomes to unexpected_transactions [quick]
├── Task 3: Migration: create report_templates table [quick]
├── Task 4: Install export packages + config [quick]
├── Task 5: Add bayar_nanti to Order payment_method options [quick]
└── Task 6: ReportTemplate model + migration [quick]

Wave 2 (After Wave 1 — cash flow rebuild, MAX PARALLEL):
├── Task 7: CashFlow page rebuild — proper Filament page [unspecified-high]
├── Task 8: CashFlowStatsWidget — free date range enhancement [unspecified-high]
├── Task 9: CashFlowChartWidget — free date range enhancement [unspecified-high]
├── Task 10: ExpenseResource — restore navigation + enhance [quick]
├── Task 11: Retire IncomeResource — remove routes, mark deprecated [quick]
└── Task 12: Receivable auto-create listener (bayar_nanti → Receivable) [deep]

Wave 3 (After Wave 2 — receivable enhancements):
├── Task 13: ReceivableResource — enhanced table + form with order link [quick]
├── Task 14: Receivable detail page — order summary display [quick]
└── Task 15: Receivable payment tracking + status management [quick]

Wave 4 (After Wave 3 — financial reports):
├── Task 16: FinancialReport page — core structure + date range picker [deep]
├── Task 17: Simple report generator [quick]
├── Task 18: Rigid report generator (Income Statement + Cash Flow) [deep]
├── Task 19: Custom report builder (category selection + aggregation) [deep]
└── Task 20: Report template save/load system [quick]

Wave 5 (After Wave 4 — exports, tests, final verification):
├── Task 21: Excel/CSV export for all report types [quick]
├── Task 22: PDF export for all report types [quick]
├── Task 23: Integration tests (migration + cash flow + receivable + report) [unspecified-high]
├── Task 24: CashFlow performance verification [quick]
└── Task 25: Final cleanup — remove dead code, verify navigation [quick]

Wave FINAL (After ALL tasks):
├── Task F1: Plan Compliance Audit (oracle)
├── Task F2: Code Quality Review (unspecified-high)
├── Task F3: Real Manual QA (unspecified-high)
└── Task F4: Scope Fidelity Check (deep)
-> Present results -> Get explicit user okay

Critical Path: T1 → T7 → T13 → T16 → T21 → F1-F4
Parallel Speedup: ~65% faster than sequential
Max Concurrent: 6 (Waves 1 & 2)
```

### Dependency Matrix

| Task | Blocked By | Blocks | Wave |
|------|-----------|--------|------|
| 1-6 | - | 7-12 | 1 |
| 7 | 1, 2, 5 | 13, 16 | 2 |
| 8 | 1, 2 | 13, 24 | 2 |
| 9 | 1, 2 | 13, 24 | 2 |
| 10 | 1, 2 | 16 | 2 |
| 11 | 1, 2 | - | 2 |
| 12 | 5 | 13 | 2 |
| 13 | 7, 12 | 16 | 3 |
| 14 | 13 | - | 3 |
| 15 | 13 | - | 3 |
| 16 | 7, 10, 13 | 17-20 | 4 |
| 17 | 16 | 21, 22 | 4 |
| 18 | 16 | 21, 22 | 4 |
| 19 | 16 | 21, 22 | 4 |
| 20 | 16 | 21, 22 | 4 |
| 21 | 17, 18, 19 | F1-F4 | 5 |
| 22 | 17, 18, 19 | F1-F4 | 5 |
| 23 | 21, 22 | F1-F4 | 5 |
| 24 | 8, 9 | F1-F4 | 5 |
| 25 | 11, 22 | F1-F4 | 5 |

### Agent Dispatch Summary

- **Wave 1**: 6 tasks — T1-T6 → `quick`
- **Wave 2**: 6 tasks — T7-T9 → `unspecified-high`, T10-T11 → `quick`, T12 → `deep`
- **Wave 3**: 3 tasks — T13-T15 → `quick`
- **Wave 4**: 5 tasks — T16 → `deep`, T17 → `quick`, T18-T19 → `deep`, T20 → `quick`
- **Wave 5**: 5 tasks — T21-T22 → `quick`, T23 → `unspecified-high`, T24-T25 → `quick`
- **FINAL**: 4 tasks — F1 → `oracle`, F2-F3 → `unspecified-high`, F4 → `deep`

---

## TODOs

- [x] 1. Migration: add `order_id` FK to receivables table

  **What to do**:
  - Buat migration `add_order_id_to_receivables_table`
  - Tambah kolom `order_id` → `$table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete()`
  - Tambah index pada `order_id`
  - Pastikan migration bisa di-rollback (`php artisan migrate:rollback --step=1`)
  - Update model `Receivable.php`: tambah `order_id` ke `$fillable`, tambah relasi `order()`, update casts

  **Must NOT do**:
  - JANGAN buat FK not-nullable — receivable bisa dibuat tanpa order (event organizer manual entry)
  - JANGAN ubah kolom existing
  - JANGAN cascade delete (pakai `nullOnDelete`)

  **Recommended Agent Profile**:
  - **Category**: `quick`
    - Reason: Simple migration + model update, single concern
  - **Skills**: []
  - **Skills Evaluated but Omitted**: None relevant

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 1 (with Tasks 2, 3, 4, 5, 6)
  - **Blocks**: Task 7, 8, 9, 10, 13
  - **Blocked By**: None (can start immediately)

  **References**:
  - `database/migrations/2026_04_11_000010_create_receivables_table.php` — Existing receivables schema to extend
  - `app/Models/Receivable.php` — Model to update with FK + relation
  - `app/Models/Order.php:23-37` — Order fillable & relations pattern to follow

  **Acceptance Criteria**:
  - [ ] `php artisan migrate` runs without errors
  - [ ] `php artisan migrate:rollback --step=1` reverts cleanly
  - [ ] `psql -c "SELECT order_id FROM receivables LIMIT 1"` returns valid column (nullable OK)
  - [ ] `Receivable::with('order')->first()` returns Eloquent relation

  **QA Scenarios (MANDATORY)**:

  ```
  Scenario: Happy path — create receivable with order link
    Tool: Bash (curl to Filament endpoint or php artisan tinker)
    Preconditions: An Order record exists (id=1)
    Steps:
      1. php artisan tinker --execute="App\Models\Receivable::create(['customer_name'=>'Test Event','amount'=>500000,'invoice_date'=>now(),'due_date'=>now()->addDays(30),'status'=>'pending','paid_amount'=>0,'order_id'=>1])"
      2. php artisan tinker --execute="App\Models\Receivable::where('order_id',1)->first()->order->order_code"
    Expected Result: Returns the order_code of order id=1
    Evidence: .sisyphus/evidence/task-1-receivable-order-link.txt

  Scenario: Edge case — create receivable without order
    Tool: Bash
    Preconditions: None
    Steps:
      1. php artisan tinker --execute="App\Models\Receivable::create(['customer_name'=>'No Order Event','amount'=>300000,'invoice_date'=>now(),'due_date'=>now()->addDays(14),'status'=>'pending','paid_amount'=>0])"
      2. php artisan tinker --execute="App\Models\Receivable::where('customer_name','No Order Event')->first()->order_id"
    Expected Result: Returns null (no error)
    Evidence: .sisyphus/evidence/task-1-receivable-no-order.txt

  Scenario: Edge case — delete order, receivable remains
    Tool: Bash
    Preconditions: Receivable linked to Order id=1
    Steps:
      1. php artisan tinker --execute="App\Models\Order::find(1)->delete()"
      2. php artisan tinker --execute="App\Models\Receivable::where('order_id',1)->first()"
    Expected Result: Receivable still exists, order_id is null
    Evidence: .sisyphus/evidence/task-1-null-on-delete.txt
  ```

  **Commit**: YES
  - Message: `feat(migration): add order_id FK to receivables table`
  - Files: `database/migrations/*_add_order_id_to_receivables.php`, `app/Models/Receivable.php`

- [x] 2. Migration: migrate `incomes` data to `unexpected_transactions`

  **What to do**:
  - Buat migration class untuk memindahkan SEMUA data dari `incomes` ke `unexpected_transactions`
  - Mapping: `source` + `category` + `description` → `deskripsi`, `amount` → `nominal`, `jenis` = `'pemasukan'`
  - Format deskripsi: `"[{category}] {source}: {description}"`
  - Setelah data dipindah, DROP table `incomes`
  - Tambah safety check: hitung jumlah row di incomes sebelum migrasi, log ke migration output
  - Pastikan `down()` method restore: create incomes table + restore data

  **Must NOT do**:
  - JANGAN hapus data tanpa konfirmasi — log count sebelum migrasi
  - JANGAN skip record yang description-nya null
  - JANGAN lupa handle `down()` rollback

  **Recommended Agent Profile**:
  - **Category**: `quick`
    - Reason: Straightforward data migration, well-defined mapping
  - **Skills**: []
  - **Skills Evaluated but Omitted**: None relevant

  **Parallelization**:
  - **Can Run In Parallel**: YES (runs after T1 in same wave)
  - **Parallel Group**: Wave 1 (with Tasks 1, 3, 4, 5, 6)
  - **Blocks**: Task 7, 8, 9, 11
  - **Blocked By**: None (but logically runs within Wave 1)

  **References**:
  - `database/migrations/2026_04_11_000008_create_incomes_table.php` — source schema (incomes)
  - `database/migrations/2026_04_20_000001_create_unexpected_transactions_table.php` — target schema (unexpected_transactions)
  - `app/Models/Income.php` — source model columns
  - `app/Models/UnexpectedTransaction.php` — target model columns

  **Acceptance Criteria**:
  - [ ] `php artisan migrate` completes with info log showing row count
  - [ ] `psql -c "SELECT count(*) FROM incomes"` returns error (table dropped)
  - [ ] `psql -c "SELECT count(*) FROM unexpected_transactions WHERE jenis='pemasukan'"` ≥ original incomes count
  - [ ] `php artisan migrate:rollback --step=1` restores incomes table with data
  - [ ] No data loss verified

  **QA Scenarios (MANDATORY)**:

  ```
  Scenario: Happy path — migration succeeds with data preserved
    Tool: Bash
    Preconditions: incomes table has at least 3 records
    Steps:
      1. psql -c "SELECT count(*) as before_count FROM incomes" → record count
      2. php artisan migrate
      3. psql -c "SELECT count(*) as after_count FROM unexpected_transactions WHERE jenis='pemasukan'" → must match before_count
      4. psql -c "SELECT deskripsi FROM unexpected_transactions WHERE jenis='pemasukan' LIMIT 1" → verify formatted
    Expected Result: Row counts match, deskripsi contains source + category
    Evidence: .sisyphus/evidence/task-2-migration-success.txt

  Scenario: Rollback — restore incomes table
    Tool: Bash
    Preconditions: Migration has been applied
    Steps:
      1. php artisan migrate:rollback --step=1
      2. psql -c "SELECT count(*) FROM incomes" → same as before_count
      3. php artisan migrate
    Expected Result: incomes table restored with original data
    Evidence: .sisyphus/evidence/task-2-rollback.txt
  ```

  **Commit**: YES (groups with T1)
  - Message: `feat(migration): migrate incomes to unexpected_transactions and drop table`
  - Files: `database/migrations/*_migrate_incomes_to_unexpected.php`

- [x] 3. Migration: create `report_templates` table

  **What to do**:
  - Buat migration `create_report_templates_table`
  - Columns: `id`, `name` (string 100), `user_id` (FK to users, nullable), `config` (json), `type` (enum: simple/rigid/custom), `timestamps`
  - `config` JSON menyimpan: `{date_start, date_end, categories: [], aggregation: 'daily'|'monthly', report_type: 'simple'|'rigid'|'custom'}`
  - Index pada `user_id` + unique constraint pada `(name, user_id)`
  - Buat model `ReportTemplate` dengan casts `config` → `array`
  - Tambah relasi `user()` belongsTo

  **Must NOT do**:
  - JANGAN buat config nullable — harus ada default `{}`
  - JANGAN izinkan duplicate name per user

  **Recommended Agent Profile**:
  - **Category**: `quick`
    - Reason: Simple table creation + model
  - **Skills**: []
  - **Skills Evaluated but Omitted**: None relevant

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 1 (with Tasks 1, 2, 4, 5, 6)
  - **Blocks**: Task 16, 20
  - **Blocked By**: None

  **References**:
  - `database/migrations/2026_04_11_000008_create_incomes_table.php` — migration structure pattern
  - `app/Models/Setting.php` — JSON column model pattern
  - `app/Models/User.php` — User model for belongsTo relation

  **Acceptance Criteria**:
  - [ ] `php artisan migrate` creates `report_templates` table
  - [ ] `ReportTemplate::create(['name'=>'Test','user_id'=>1,'config'=>['date_start'=>'2026-01-01'],'type'=>'custom'])` succeeds
  - [ ] Second create with same name+user_id fails (unique constraint)

  **QA Scenarios (MANDATORY)**:

  ```
  Scenario: Happy path — create and retrieve template
    Tool: Bash (php artisan tinker)
    Preconditions: A user exists (id=1)
    Steps:
      1. php artisan tinker --execute="App\Models\ReportTemplate::create(['name'=>'Laporan Bulanan','user_id'=>1,'config'=>['date_start'=>'2026-05-01','date_end'=>'2026-05-31','categories'=>['sales'],'aggregation'=>'daily'],'type'=>'custom'])"
      2. php artisan tinker --execute="App\Models\ReportTemplate::where('name','Laporan Bulanan')->first()->config"
    Expected Result: Returns the config array as stored
    Evidence: .sisyphus/evidence/task-3-template-create.txt

  Scenario: Edge case — duplicate name per user rejected
    Tool: Bash
    Preconditions: Template "Laporan Bulanan" exists for user 1
    Steps:
      1. php artisan tinker --execute="App\Models\ReportTemplate::create(['name'=>'Laporan Bulanan','user_id'=>1,'config'=>[],'type'=>'simple'])"
    Expected Result: Exception thrown (unique constraint violation)
    Evidence: .sisyphus/evidence/task-3-duplicate-reject.txt
  ```

  **Commit**: YES (groups with T1)
  - Message: `feat(migration): create report_templates table`
  - Files: `database/migrations/*_create_report_templates.php`, `app/Models/ReportTemplate.php`

- [x] 4. Install export packages + configuration

  **What to do**:
  - `composer require maatwebsite/laravel-excel` — untuk Excel & CSV export
  - `composer require barryvdh/laravel-dompdf` — untuk PDF export
  - Publish config jika ada: `php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider"`
  - Verifikasi instalasi: buat test export class kosong, pastikan tidak ada error
  - Tidak perlu custom config — gunakan default

  **Must NOT do**:
  - JANGAN install `laravel-snappy` (perlu wkhtmltopdf binary) — pakai dompdf yang pure PHP
  - JANGAN publish config yang tidak diperlukan
  - JANGAN buat export class functional di task ini — hanya scaffolding

  **Recommended Agent Profile**:
  - **Category**: `quick`
    - Reason: Composer install + config check
  - **Skills**: []
  - **Skills Evaluated but Omitted**: None relevant

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 1 (with Tasks 1, 2, 3, 5, 6)
  - **Blocks**: Task 21, 22
  - **Blocked By**: None

  **References**:
  - `composer.json` — Check current dependencies
  - Official docs: `https://docs.laravel-excel.com/` — Maatwebsite Excel
  - Official docs: `https://github.com/barryvdh/laravel-dompdf` — DomPDF Laravel

  **Acceptance Criteria**:
  - [ ] `composer show maatwebsite/laravel-excel` returns version info
  - [ ] `composer show barryvdh/laravel-dompdf` returns version info
  - [ ] `php artisan` shows no error (no broken service providers)

  **QA Scenarios (MANDATORY)**:

  ```
  Scenario: Happy path — packages installed correctly
    Tool: Bash
    Preconditions: None
    Steps:
      1. composer require maatwebsite/laravel-excel barryvdh/laravel-dompdf
      2. php artisan | grep -i excel (verify provider registered)
      3. php artisan tinker --execute="echo class_exists('Maatwebsite\Excel\Excel') ? 'OK' : 'FAIL'"
    Expected Result: "OK" for both packages
    Evidence: .sisyphus/evidence/task-4-packages-installed.txt
  ```

  **Commit**: YES
  - Message: `chore(deps): add maatwebsite/laravel-excel and barryvdh/laravel-dompdf`
  - Files: `composer.json`, `composer.lock`

- [x] 5. Add `bayar_nanti` handling to order creation flow

  **What to do**:
  - `bayar_nanti` sudah ada di database enum — pastikan muncul sebagai opsi di UI order creation
  - Check `OrderResource.php` Filament form — tambah opsi `bayar_nanti` ke Select payment_method jika belum ada
  - Check kasir `PesananBaru.jsx` — tambah opsi "Bayar Nanti" di payment method selection
  - Saat order dibuat dengan `bayar_nanti`, SET `is_paid = false` (penting! default di DB adalah `true`)
  - JANGAN auto-create Receivable di task ini — itu di Task 12

  **Must NOT do**:
  - JANGAN ubah default `is_paid` di database migration
  - JANGAN auto-create receivable di sini
  - JANGAN hapus opsi payment method lain

  **Recommended Agent Profile**:
  - **Category**: `quick`
    - Reason: Adding enum option + default override
  - **Skills**: []
  - **Skills Evaluated but Omitted**: None relevant

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 1 (with Tasks 1, 2, 3, 4, 6)
  - **Blocks**: Task 12
  - **Blocked By**: None

  **References**:
  - `app/Filament/Resources/OrderResource.php` — Filament form where payment_method is selected
  - `resources/js/Pages/Cashier/PesananBaru.jsx` — Kasir UI where payment is selected
  - `app/Models/Order.php:21` — payment_method enum in migration
  - `database/migrations/2025_01_01_000014_create_orders_table.php:21` — Enum definition with bayar_nanti

  **Acceptance Criteria**:
  - [ ] `bayar_nanti` muncul sebagai opsi di Filament OrderResource form
  - [ ] `bayar_nanti` muncul sebagai opsi di kasir POS interface
  - [ ] Membuat order dengan `bayar_nanti` menghasilkan `is_paid = false` di database
  - [ ] `psql -c "SELECT is_paid FROM orders WHERE payment_method='bayar_nanti' LIMIT 1"` returns `f`

  **QA Scenarios (MANDATORY)**:

  ```
  Scenario: Happy path — create order with bayar_nanti via tinker
    Tool: Bash
    Preconditions: A menu exists (id=1)
    Steps:
      1. php artisan tinker --execute="$o=App\Models\Order::create(['order_code'=>'TEST-BN-001','customer_name'=>'Event Organizer','payment_method'=>'bayar_nanti','is_paid'=>false,'total_amount'=>150000,'status'=>'pending','order_type'=>'cashier']); echo 'Order ID: '.$o->id"
      2. psql -c "SELECT is_paid, payment_method FROM orders WHERE order_code='TEST-BN-001'"
    Expected Result: is_paid=false, payment_method=bayar_nanti
    Evidence: .sisyphus/evidence/task-5-bayar-nanti-order.txt

  Scenario: Edge case — bayar_nanti with is_paid incorrectly true
    Tool: Bash
    Preconditions: None
    Steps:
      1. Verify that when creating via Filament/kasir UI, is_paid is forced false for bayar_nanti
      2. Check controller/service logic ensures is_paid=false when payment_method=bayar_nanti
    Expected Result: Cannot have bayar_nanti + is_paid=true in same order
    Evidence: .sisyphus/evidence/task-5-bayar-nanti-constraint.txt
  ```

  **Commit**: YES
  - Message: `feat(order): add bayar_nanti payment option with is_paid handling`
  - Files: `app/Filament/Resources/OrderResource.php`, `resources/js/Pages/Cashier/PesananBaru.jsx`

- [x] 6. Create `ReportTemplate` model with relations

  **What to do**:
  - Buat model `app/Models/ReportTemplate.php`
  - Traits: `HasFactory`
  - `$fillable`: `['name', 'user_id', 'config', 'type']`
  - Casts: `config` → `array`
  - Relasi: `user()` belongsTo, `creator()` alias
  - Scope: `forUser($userId)`, `ofType($type)`
  - Pastikan model bisa digunakan di Filament

  **Must NOT do**:
  - JANGAN buat Filament Resource untuk ReportTemplate (cukup model saja)
  - JANGAN tambah validasi di model (validasi di Filament form nanti)

  **Recommended Agent Profile**:
  - **Category**: `quick`
    - Reason: Simple model creation
  - **Skills**: []
  - **Skills Evaluated but Omitted**: None relevant

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 1 (with Tasks 1, 2, 3, 4, 5)
  - **Blocks**: Task 20
  - **Blocked By**: Task 3 (needs table to exist)

  **References**:
  - `app/Models/Setting.php` — JSON cast pattern
  - `app/Models/User.php` — User model for belongsTo
  - `app/Models/Receivable.php` — Model structure pattern

  **Acceptance Criteria**:
  - [ ] `ReportTemplate::create([...])` works from tinker
  - [ ] `ReportTemplate::with('user')->first()` returns user relation
  - [ ] `ReportTemplate::forUser(1)->get()` returns only that user's templates
  - [ ] `ReportTemplate::ofType('simple')->get()` returns only simple templates

  **QA Scenarios (MANDATORY)**:

  ```
  Scenario: Happy path — model CRUD works
    Tool: Bash (php artisan tinker)
    Preconditions: Task 3 migration applied
    Steps:
      1. php artisan tinker --execute="$t=App\Models\ReportTemplate::create(['name'=>'Test','user_id'=>1,'config'=>['date_start'=>'2026-01-01'],'type'=>'simple']); echo $t->id"
      2. php artisan tinker --execute="App\Models\ReportTemplate::find(1)->config['date_start']"
      3. php artisan tinker --execute="App\Models\ReportTemplate::forUser(1)->ofType('simple')->count()"
    Expected Result: Create returns id, config access returns '2026-01-01', count ≥ 1
    Evidence: .sisyphus/evidence/task-6-model-crud.txt
  ```

  **Commit**: YES (groups with T3)
  - Message: `feat(model): create ReportTemplate model with relations and scopes`
  - Files: `app/Models/ReportTemplate.php`

---

- [x] 7. CashFlow page rebuild — proper Filament page with free date range

  **What to do**:
  - Rewrite `CashFlow.php` sebagai full Filament page (bukan custom blade)
  - Gunakan `DatePicker` Filament component untuk free date range (`date_start`, `date_end`)
  - Ganti semua query dari `Income::` ke `Order::where('is_paid', true)` + `UnexpectedTransaction::jenis='pemasukan'`
  - Hapus query `Income::` yang masih tersisa di `getSummary()`, `getCategoryBreakdown()`, `getTopSources()`, `getTransactions()`
  - Hapus method `getSvgChart()` — chart sudah di-handle oleh CashFlowChartWidget (Task 9)
  - Hapus method `getCategoryBreakdown()`, `getTopSources()`, `getTopVendors()` — tidak dirender di view
  - Simplifikasi: CashFlow page hanya render widgets via `@livewire` + period tabs
  - Ganti custom blade view ke Filament page view dengan `protected static string $view`
  - Tambah `#[On('cashflow-date-changed')]` listener untuk date range change

  **Must NOT do**:
  - JANGAN ubah widget query logic (widgets sudah benar query dari Orders)
  - JANGAN tambah polling/websocket
  - JANGAN hapus `getTransactions()` jika UnexpectedTransactionWidget membutuhkannya
  - JANGAN rename navigation group atau label

  **Recommended Agent Profile**:
  - **Category**: `unspecified-high`
    - Reason: Multi-file refactoring with query changes across page + view
  - **Skills**: []
  - **Skills Evaluated but Omitted**: None relevant

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 2 (with Tasks 8, 9, 10, 11, 12)
  - **Blocks**: Task 13, 16
  - **Blocked By**: Task 1, 2 (incomes table dropped, FK added)

  **References**:
  - `app/Filament/Pages/CashFlow.php` — Current page to rewrite
  - `resources/views/filament/pages/cash-flow.blade.php` — Current blade view to replace
  - `app/Filament/Widgets/CashFlowStatsWidget.php:53-59` — Correct income query pattern (Order + UnexpectedTransaction)
  - `app/Filament/Widgets/CashFlowChartWidget.php:33-36` — Correct income query builder pattern
  - `app/Filament/Pages/AsosiatifMenu.php` — Example of another Filament page in the project

  **Acceptance Criteria**:
  - [ ] CashFlow page loads without error after incomes table is dropped
  - [ ] All queries use `Order::` + `UnexpectedTransaction`, NOT `Income::`
  - [ ] Date range picker allows free date selection (not just presets)
  - [ ] Period tabs (Hari Ini/Bulan Ini/Tahun Ini/Semua Waktu) still work as quick presets
  - [ ] Changing date range dispatches event to widgets
  - [ ] `grep -r "Income::" app/Filament/Pages/CashFlow.php` returns NO matches

  **QA Scenarios (MANDATORY)**:

  ```
  Scenario: Happy path — CashFlow loads with correct data sources
    Tool: Bash + Playwright
    Preconditions: Orders exist with is_paid=true, incomes table dropped
    Steps:
      1. Playwright: Navigate to /admin/cash-flow
      2. Verify page title is "Cash Flow"
      3. Verify stats widgets render (4 KPI cards visible)
      4. Verify chart widget renders
      5. Verify no PHP errors in log
    Expected Result: Page renders fully with data from Orders
    Evidence: .sisyphus/evidence/task-7-cashflow-loads.png

  Scenario: Happy path — free date range works
    Tool: Playwright
    Preconditions: CashFlow page loaded
    Steps:
      1. Click date_start picker → select 2026-05-01
      2. Click date_end picker → select 2026-05-31
      3. Verify stats update to reflect new date range
    Expected Result: Stats numbers change, chart re-renders
    Evidence: .sisyphus/evidence/task-7-date-range.png

  Scenario: Edge case — zero data period
    Tool: Playwright
    Preconditions: CashFlow page loaded
    Steps:
      1. Select future date range (e.g., 2027-01-01 to 2027-01-31)
      2. Verify stats show Rp 0 or empty state
    Expected Result: No error, graceful zero state
    Evidence: .sisyphus/evidence/task-7-zero-data.png
  ```

  **Commit**: YES
  - Message: `refactor(cashflow): rebuild as proper Filament page with free date range`
  - Files: `app/Filament/Pages/CashFlow.php`, `resources/views/filament/pages/cash-flow.blade.php`

- [x] 8. CashFlowStatsWidget — free date range enhancement

  **What to do**:
  - Tambah properti `public string $dateStart`, `public string $dateEnd` ke widget
  - Update `dateRange()` method: jika `$dateStart` dan `$dateEnd` diset, gunakan itu; jika tidak, fallback ke period-based
  - Update `prevRange()` untuk menyesuaikan perbandingan periode sebelumnya
  - Tambah `#[On('cashflow-date-changed')]` listener untuk menerima date range dari parent page
  - Pastikan listener juga handle period-based events untuk backward compatibility
  - Optimasi query: gunakan `->toBase()` atau `DB::raw()` untuk sparkline queries agar lebih cepat
  - Cache query results menggunakan `Cache::remember()` dengan TTL 5 menit, invalidate on date change

  **Must NOT do**:
  - JANGAN hapus period-based behavior (Hari Ini/Bulan Ini/Tahun Ini harus tetap jalan)
  - JANGAN ubah tampilan stat cards
  - JANGAN tambah polling

  **Recommended Agent Profile**:
  - **Category**: `unspecified-high`
    - Reason: Query optimization + caching + dual-mode date handling
  - **Skills**: []
  - **Skills Evaluated but Omitted**: None relevant

  **Parallelization**:
  - **Can Run In Parallel**: YES (runs alongside T7, T9)
  - **Parallel Group**: Wave 2 (with Tasks 7, 9, 10, 11, 12)
  - **Blocks**: Task 24
  - **Blocked By**: Task 1, 2

  **References**:
  - `app/Filament/Widgets/CashFlowStatsWidget.php` — Full current implementation to enhance
  - `app/Filament/Widgets/CashFlowChartWidget.php:23-31` — Period listener pattern example
  - Laravel docs: `https://laravel.com/docs/11.x/cache` — Cache::remember() pattern

  **Acceptance Criteria**:
  - [ ] Widget accepts `dateStart` and `dateEnd` properties
  - [ ] When date range provided, stat numbers reflect that range
  - [ ] When date range NOT provided, falls back to period-based (day/month/year)
  - [ ] Sparkline queries execute in under 200ms each
  - [ ] Cache is invalidated when date range changes
  - [ ] `#[On('cashflow-date-changed')]` listener works

  **QA Scenarios (MANDATORY)**:

  ```
  Scenario: Happy path — stats update on date range change
    Tool: Bash + psql
    Preconditions: Orders exist across multiple months
    Steps:
      1. php artisan tinker --execute="echo App\Models\Order::where('is_paid',true)->whereBetween('created_at',['2026-05-01','2026-05-31'])->sum('total_amount')"
      2. Set dateStart=2026-05-01, dateEnd=2026-05-31 on widget
      3. php artisan tinker --execute="(new App\Filament\Widgets\CashFlowStatsWidget)->totalIncome('2026-05-01','2026-05-31')"
    Expected Result: Both values match
    Evidence: .sisyphus/evidence/task-8-date-range-match.txt

  Scenario: Performance — query timing under load
    Tool: Bash
    Preconditions: 10k+ orders in database
    Steps:
      1. time php artisan tinker --execute="App\Models\Order::where('is_paid',true)->whereBetween('created_at',['2026-01-01','2026-12-31'])->sum('total_amount')"
    Expected Result: Completes in under 500ms
    Evidence: .sisyphus/evidence/task-8-query-timing.txt
  ```

  **Commit**: YES
  - Message: `perf(cashflow): add free date range support and query caching to stats widget`
  - Files: `app/Filament/Widgets/CashFlowStatsWidget.php`

- [x] 9. CashFlowChartWidget — free date range enhancement

  **What to do**:
  - Tambah properti `public string $dateStart`, `public string $dateEnd`
  - Update `buildData()` untuk support custom date range sebagai alternatif period
  - Saat custom date range diset, gunakan dynamic bucketing:
    - ≤7 hari → by day (tiap hari)
    - 8-60 hari → by week (tiap minggu)
    - 61-365 hari → by month (tiap bulan)
    - >365 hari → by quarter (tiap 3 bulan)
  - Tambah `#[On('cashflow-date-changed')]` listener
  - Pertahankan period-based methods sebagai fallback
  - Cache hasil query chart dengan TTL 5 menit

  **Must NOT do**:
  - JANGAN ganti library chart — tetap Chart.js via Filament ChartWidget
  - JANGAN hapus period-based methods (`byHour`, `byEvenDay`, `byMonthOfYear`, `byMonth`)
  - JANGAN render >200 data points (gunakan aggregation)

  **Recommended Agent Profile**:
  - **Category**: `unspecified-high`
    - Reason: Complex dynamic bucketing logic + caching
  - **Skills**: []
  - **Skills Evaluated but Omitted**: None relevant

  **Parallelization**:
  - **Can Run In Parallel**: YES (runs alongside T7, T8)
  - **Parallel Group**: Wave 2 (with Tasks 7, 8, 10, 11, 12)
  - **Blocks**: Task 24
  - **Blocked By**: Task 1, 2

  **References**:
  - `app/Filament/Widgets/CashFlowChartWidget.php` — Full current implementation
  - `app/Filament/Widgets/CashFlowStatsWidget.php:29-37` — dateRange() pattern to replicate
  - Filament docs: ChartWidget `getData()` pattern

  **Acceptance Criteria**:
  - [ ] Chart renders with custom date range data
  - [ ] Dynamic bucketing correct: 5-day range shows 5 bars, 90-day shows ~12 bars
  - [ ] Chart labels format correctly (dates for daily, "Minggu 1" for weekly, "Jan 2026" for monthly)
  - [ ] Period-based presets still work
  - [ ] No more than 200 data points rendered

  **QA Scenarios (MANDATORY)**:

  ```
  Scenario: Happy path — chart reflects custom date range
    Tool: Playwright
    Preconditions: CashFlow page with custom date range
    Steps:
      1. Set dateStart=2026-05-01, dateEnd=2026-05-07 (7 days)
      2. Verify chart shows 7 x-axis labels (one per day)
      3. Set dateStart=2026-01-01, dateEnd=2026-12-31 (1 year)
      4. Verify chart shows ~12 x-axis labels (one per month)
    Expected Result: Chart dynamically adjusts bucketing
    Evidence: .sisyphus/evidence/task-9-chart-bucketing.png

  Scenario: Performance — chart renders under 1s
    Tool: Playwright (measure navigation timing)
    Preconditions: 10k+ orders
    Steps:
      1. Navigate to CashFlow page
      2. Check Performance API: load time < 1000ms
    Expected Result: Chart renders within 1 second
    Evidence: .sisyphus/evidence/task-9-chart-performance.txt
  ```

  **Commit**: YES
  - Message: `perf(cashflow): add free date range and dynamic bucketing to chart widget`
  - Files: `app/Filament/Widgets/CashFlowChartWidget.php`

- [x] 10. ExpenseResource — restore navigation + enhance

  **What to do**:
  - Set `protected static bool $shouldRegisterNavigation = true` pada ExpenseResource
  - Tambah kolom di table: tambah date range filter, total amount summary di header
  - Pastikan kategori expense mencakup yang diperlukan: `inventory`, `utilities`, `salary`, `rent`, `marketing`, `other`
  - Tambah metric/total di atas table (menggunakan `->headerActions()` atau widget embedded)
  - Verifikasi `navigationSort` tidak conflict dengan resource lain

  **Must NOT do**:
  - JANGAN ubah struktur tabel expenses
  - JANGAN hapus kategori existing
  - JANGAN ubah Expense model

  **Recommended Agent Profile**:
  - **Category**: `quick`
    - Reason: Toggle visibility + minor enhancements to existing resource
  - **Skills**: []
  - **Skills Evaluated but Omitted**: None relevant

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 2 (with Tasks 7, 8, 9, 11, 12)
  - **Blocks**: Task 16
  - **Blocked By**: Task 1, 2

  **References**:
  - `app/Filament/Resources/ExpenseResource.php` — Full resource to enhance
  - `app/Filament/Resources/ReceivableResource.php` — Navigation pattern example
  - `app/Models/Expense.php` — Model fields available

  **Acceptance Criteria**:
  - [ ] "Expenses" muncul di sidebar navigation "Finance Details" group
  - [ ] Expense list page loads with all columns
  - [ ] Date filter works (filter by date range)
  - [ ] Total amount summary visible di header

  **QA Scenarios (MANDATORY)**:

  ```
  Scenario: Happy path — Expenses visible in navigation
    Tool: Playwright
    Preconditions: Logged in as admin
    Steps:
      1. Navigate to /admin
      2. Look for "Finance Details" navigation group in sidebar
      3. Click to expand → verify "Expenses" item visible
      4. Click "Expenses" → verify list page loads
    Expected Result: Expenses page accessible from navigation
    Evidence: .sisyphus/evidence/task-10-expenses-nav.png

  Scenario: CRUD — create expense
    Tool: Playwright
    Preconditions: Expenses list page
    Steps:
      1. Click "New Expense" / "Tambah Pengeluaran"
      2. Fill vendor: "Test Vendor", category: "utilities", amount: 500000, date: today
      3. Submit
      4. Verify new expense appears in table
    Expected Result: Expense created and visible
    Evidence: .sisyphus/evidence/task-10-expense-create.png
  ```

  **Commit**: YES
  - Message: `feat(expense): restore navigation and enhance expense resource`
  - Files: `app/Filament/Resources/ExpenseResource.php`

- [x] 11. Retire IncomeResource — remove routes + mark deprecated

  **What to do**:
  - Set `protected static bool $shouldRegisterNavigation = false` (sudah false)
  - Hapus page classes: `ListIncomes`, `CreateIncome`, `EditIncome` — atau keep tapi kosongkan body
  - Remove route registrations di `getPages()`: return empty array atau comment out
  - Tambah docblock `@deprecated` pada class IncomeResource
  - Verifikasi tidak ada broken reference ke IncomeResource dari tempat lain
  - OPTIONAL: hapus file resource jika tidak ada reference (setelah verifikasi)

  **Must NOT do**:
  - JANGAN hapus model `Income.php` (mungkin masih direferensi di tes atau seeder)
  - JANGAN hapus migration file (historical record)
  - JANGAN ubah navigation items lain

  **Recommended Agent Profile**:
  - **Category**: `quick`
    - Reason: Simple route removal + deprecation
  - **Skills**: []
  - **Skills Evaluated but Omitted**: None relevant

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 2 (with Tasks 7, 8, 9, 10, 12)
  - **Blocks**: None (leaf task)
  - **Blocked By**: Task 1, 2

  **References**:
  - `app/Filament/Resources/IncomeResource.php` — Resource to retire
  - `app/Filament/Resources/IncomeResource/` — Subdirectory with page classes

  **Acceptance Criteria**:
  - [ ] `/admin/incomes` returns 404 or redirect
  - [ ] `/admin/incomes/create` returns 404 or redirect
  - [ ] No "Income" in sidebar navigation
  - [ ] No broken references in codebase (`grep -r "IncomeResource" app/` shows only the resource file itself)
  - [ ] `php artisan route:list | grep incomes` returns no results

  **QA Scenarios (MANDATORY)**:

  ```
  Scenario: Happy path — income routes inaccessible
    Tool: Bash (curl)
    Preconditions: App running
    Steps:
      1. curl -s -o /dev/null -w "%{http_code}" http://localhost/admin/incomes
      2. curl -s -o /dev/null -w "%{http_code}" http://localhost/admin/incomes/create
    Expected Result: Both return 404 or 302 (redirect)
    Evidence: .sisyphus/evidence/task-11-routes-gone.txt
  ```

  **Commit**: YES
  - Message: `refactor(income): retire IncomeResource after migration to Orders`
  - Files: `app/Filament/Resources/IncomeResource.php`, `app/Filament/Resources/IncomeResource/`

- [x] 12. Receivable auto-create event listener (bayar_nanti → Receivable)

  **What to do**:
  - Buat Eloquent event listener: saat Order `created`, jika `payment_method === 'bayar_nanti'` DAN `is_paid === false`, auto-create Receivable
  - Buat class `App\Listeners\CreateReceivableFromOrder` atau gunakan model `booted()` method di Order model
  - Receivable field mapping:
    - `customer_name` ← `$order->customer_name ?? 'Event Customer'`
    - `amount` ← `$order->total_amount`
    - `invoice_date` ← `$order->created_at`
    - `due_date` ← `$order->created_at->addDays(30)`
    - `status` ← `Receivable::STATUS_PENDING`
    - `paid_amount` ← `0`
    - `order_id` ← `$order->id`
    - `notes` ← `"Auto-generated from Order #{$order->order_code}"`
  - Register listener di `EventServiceProvider`

  **Must NOT do**:
  - JANGAN buat receivable jika payment_method BUKAN `bayar_nanti`
  - JANGAN buat receivable jika `is_paid === true`
  - JANGAN update Order dari listener (infinite loop risk)
  - JANGAN gunakan queue job (syncronous saja)

  **Recommended Agent Profile**:
  - **Category**: `deep`
    - Reason: Eloquent event system + data mapping + edge case handling
  - **Skills**: []
  - **Skills Evaluated but Omitted**: None relevant

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 2 (with Tasks 7, 8, 9, 10, 11)
  - **Blocks**: Task 13
  - **Blocked By**: Task 1 (FK must exist), Task 5 (bayar_nanti option must exist)

  **References**:
  - `app/Models/Order.php:13-21` — Model `boot()` method pattern
  - `app/Models/Receivable.php` — Receivable model with STATUS constants
  - Laravel docs: `https://laravel.com/docs/11.x/eloquent#events` — Eloquent events
  - `database/migrations/2025_01_01_000014_create_orders_table.php:21` — payment_method enum

  **Acceptance Criteria**:
  - [ ] Creating Order with `payment_method='bayar_nanti'` AND `is_paid=false` auto-creates Receivable
  - [ ] Creating Order with `payment_method='cash'` does NOT create Receivable
  - [ ] Creating Order with `payment_method='bayar_nanti'` AND `is_paid=true` does NOT create Receivable
  - [ ] Receivable has correct `customer_name`, `amount`, `order_id`
  - [ ] Receivable `due_date` = `created_at + 30 days`

  **QA Scenarios (MANDATORY)**:

  ```
  Scenario: Happy path — bayar_nanti order creates receivable
    Tool: Bash (php artisan tinker)
    Preconditions: Receivables table has order_id column (Task 1 done)
    Steps:
      1. php artisan tinker --execute="$o=App\Models\Order::create(['order_code'=>'TEST-AUTO-001','customer_name'=>'Acara Kampus','payment_method'=>'bayar_nanti','is_paid'=>false,'total_amount'=>750000,'status'=>'pending','order_type'=>'cashier']); echo 'Order: '.$o->id"
      2. php artisan tinker --execute="$r=App\Models\Receivable::where('order_id',{$o->id})->first(); echo $r ? 'Receivable: '.$r->id.' | Amount: '.$r->amount.' | Customer: '.$r->customer_name : 'NOT FOUND'"
    Expected Result: Receivable created with matching amount and customer_name
    Evidence: .sisyphus/evidence/task-12-auto-create.txt

  Scenario: Negative — cash order does NOT create receivable
    Tool: Bash
    Preconditions: None
    Steps:
      1. php artisan tinker --execute="$o=App\Models\Order::create(['order_code'=>'TEST-NO-AR-001','customer_name'=>'Walk-in','payment_method'=>'cash','is_paid'=>true,'total_amount'=>50000,'status'=>'selesai','order_type'=>'cashier'])"
      2. php artisan tinker --execute="echo App\Models\Receivable::where('order_id',{$o->id})->count()"
    Expected Result: Returns 0
    Evidence: .sisyphus/evidence/task-12-no-auto-create.txt

  Scenario: Edge case — duplicate prevention
    Tool: Bash
    Preconditions: Order with bayar_nanti already has receivable
    Steps:
      1. Manually trigger the event again or re-save the order
      2. php artisan tinker --execute="echo App\Models\Receivable::where('order_id',X)->count()"
    Expected Result: Still only 1 receivable (no duplicate)
    Evidence: .sisyphus/evidence/task-12-no-duplicate.txt
  ```

  **Commit**: YES
  - Message: `feat(receivable): auto-create receivable when order uses bayar_nanti`
  - Files: `app/Models/Order.php` (or new Listener), `app/Providers/EventServiceProvider.php`

---

- [x] 13. ReceivableResource — enhanced table + form with order link

  **What to do**:
  - Update `table()` method di `ReceivableResource.php`: tambah kolom `order_code` (dari relasi `order`) sebagai link, `remaining_amount`, filter due_date range, summary outstanding
  - Update `form()`: tambah `Select::make('order_id')->relationship('order', 'order_code')->searchable()`, auto-fill customer_name + amount saat order dipilih
  - `order_id` tetap nullable — receivable bisa dibuat manual tanpa order

  **Must NOT do**: JANGAN buat order_id required, JANGAN hapus kolom existing, JANGAN ubah model

  **Recommended Agent Profile**: **Category**: `quick` | **Skills**: []

  **Parallelization**: Wave 3 (with T14, T15) | **Blocks**: T16 | **Blocked By**: T7, T12

  **References**: `app/Filament/Resources/ReceivableResource.php` (enhance), `app/Filament/Resources/OrderResource.php` (link pattern)

  **Acceptance Criteria**:
  - [ ] Table shows `order_code` column as clickable link
  - [ ] Table shows `remaining_amount` with correct calculation
  - [ ] Form has searchable order_id select with auto-fill
  - [ ] Status filter works

  **QA Scenarios**:

  ```
  Scenario: Receivable table with order link
    Tool: Playwright → /admin/receivables
    Steps: 1. Verify columns include Order, Remaining 2. Click order link → navigates to order detail
    Evidence: .sisyphus/evidence/task-13-table-order-link.png

  Scenario: Create receivable with auto-fill
    Tool: Playwright → New Receivable
    Steps: 1. Select order from picker 2. Verify customer_name + amount auto-fill 3. Submit
    Evidence: .sisyphus/evidence/task-13-auto-fill.png
  ```

  **Commit**: YES — `feat(receivable): enhance table with order link and auto-fill form`

- [x] 14. Receivable detail page — order summary display

  **What to do**: Buat `ViewReceivable` page di `ReceivableResource\Pages\`. Tampilkan: customer_name, amount, paid, remaining, status, dates. Jika `order_id` ada: order_code (link), status, items list (menu name, qty, price, subtotal). Tambah "Record Payment" action → modal input amount → update paid_amount + status.

  **Must NOT do**: JANGAN tampilkan user data, JANGAN izinkan edit order

  **Recommended Agent Profile**: **Category**: `quick` | **Skills**: []

  **Parallelization**: Wave 3 (with T13, T15) | **Blocked By**: T13

  **References**: `app/Filament/Resources/ReceivableResource.php:150-157` (getPages), `app/Models/Order.php:57-60` (items relation)

  **Acceptance Criteria**:
  - [ ] `/admin/receivables/{id}` shows detail with order summary
  - [ ] "Record Payment" updates paid_amount and status
  - [ ] Graceful state when no linked order

  **QA Scenarios**:

  ```
  Scenario: View receivable with order
    Tool: Playwright → receivable detail
    Steps: 1. Verify customer, amounts, status 2. Verify order section: code, items list
    Evidence: .sisyphus/evidence/task-14-detail-with-order.png

  Scenario: Receivable without order — graceful
    Tool: Playwright → receivable without order_id
    Steps: Verify "No linked order" without error
    Evidence: .sisyphus/evidence/task-14-no-order.png
  ```

  **Commit**: YES — `feat(receivable): add detail page with order summary and payment recording`

- [x] 15. Receivable payment tracking + status auto-update

  **What to do**: Update paid_amount: `paid_amount += new_payment`. Auto-status: `paid_amount==0`→pending, `0<paid<amount`→partial, `paid>=amount`→paid. `isOverdue()`: `due_date<now() && status!=paid`. Color coding di table: overdue=red, partial=warning.

  **Must NOT do**: JANGAN izinkan paid_amount > amount, JANGAN payment pada status=paid, JANGAN cron job

  **Recommended Agent Profile**: **Category**: `quick` | **Skills**: []

  **Parallelization**: Wave 3 (with T13, T14) | **Blocked By**: T13

  **References**: `app/Models/Receivable.php:9-55` (STATUS constants, isOverdue), `app/Filament/Resources/ReceivableResource.php:116-126` (badge colors)

  **Acceptance Criteria**:
  - [ ] Status auto-transitions based on paid_amount
  - [ ] Overdue detection via model accessor
  - [ ] Cannot overpay or pay already-paid receivable

  **QA Scenarios**:

  ```
  Scenario: Status transitions
    Tool: Bash (tinker)
    Steps: 1. Set paid=200k on 500k receivable → partial 2. Set paid=500k → paid
    Evidence: .sisyphus/evidence/task-15-status-transition.txt

  Scenario: Overdue detection
    Tool: Bash → isOverdue() on past-due unpaid receivable → true
    Evidence: .sisyphus/evidence/task-15-overdue.txt
  ```

  **Commit**: YES (groups with T14) — `feat(receivable): add payment tracking with auto status updates`

---

- [x] 16. FinancialReport page — core structure + date range picker

  **What to do**: Buat `app/Filament/Pages/FinancialReport.php`. Navigation: icon `heroicon-o-document-chart-bar`, group `Finance Details`, sort 4. Layout: date_start + date_end picker, report type selector (Simple/Rigid/Custom). Custom mode: multi-select kategori. Aggregation selector. Action: Generate Report, Save as Template, Export. Report area di bawah form.

  **Must NOT do**: JANGAN hardcode kategori, JANGAN auto-generate (harus click), JANGAN izinkan date_end < date_start

  **Recommended Agent Profile**: **Category**: `deep` | **Skills**: []

  **Parallelization**: Wave 4 (first, unblocks T17-T20) | **Blocks**: T17, T18, T19, T20 | **Blocked By**: T7, T10, T13

  **References**: `app/Filament/Pages/CashFlow.php` (page pattern), `app/Models/ReportTemplate.php` (template model)

  **Acceptance Criteria**:
  - [ ] Page in "Finance Details" nav group
  - [ ] Date picker + type selector work
  - [ ] Custom type shows category multi-select
  - [ ] Generate renders report below form

  **QA Scenarios**:

  ```
  Scenario: Simple report generation
    Tool: Playwright → /admin/financial-reports
    Steps: 1. date_start=2026-05-01, date_end=2026-05-31 2. Type=Simple 3. Generate 4. Verify result area
    Evidence: .sisyphus/evidence/task-16-simple-generate.png
  ```

  **Commit**: YES — `feat(report): create FinancialReport page with date range and type selection`
  - Files: `app/Filament/Pages/FinancialReport.php`

- [x] 17. Simple report generator service

  **What to do**: `App\Services\SimpleReportService::generate($start, $end): array`. Output: total_income (Orders paid + Unexpected pemasukan), total_expense (Expenses + IngredientBatch + Unexpected pengeluaran), net, income_breakdown[], expense_breakdown[], receivables_outstanding. ALL queries pakai database aggregation (SUM, GROUP BY).

  **Must NOT do**: JANGAN PHP loop, JANGAN include unpaid orders, JANGAN render HTML

  **Recommended Agent Profile**: **Category**: `quick` | **Skills**: []

  **Parallelization**: Wave 4 (with T18, T19, T20) | **Blocks**: T21, T22 | **Blocked By**: T16

  **References**: `app/Filament/Widgets/CashFlowStatsWidget.php:53-68` (query pattern), `app/Models/Receivable.php:44-47` (remaining_amount)

  **Acceptance Criteria**:
  - [ ] total_income matches `SELECT SUM(total_amount) FROM orders WHERE is_paid=true AND created_at BETWEEN ? AND ?` + unexpected income
  - [ ] Empty date range returns zeros, not error

  **QA Scenarios**:

  ```
  Scenario: Report matches raw SQL
    Tool: Bash → compare service output vs psql query
    Evidence: .sisyphus/evidence/task-17-simple-match.txt
  ```

  **Commit**: YES — `feat(report): implement simple report generator service`
  - Files: `app/Services/SimpleReportService.php`

- [x] 18. Rigid report generator (Income Statement + Cash Flow)

  **What to do**: `App\Services\RigidReportService::generate($start, $end): array`. Income Statement: Pendapatan - HPP (IngredientBatch costs) = Laba Kotor. Laba Kotor - Beban Operasional (expenses) - Beban Tak Terduga = Laba/Rugi Bersih. Cash Flow: Arus Kas Masuk (paid orders + unexpected income + receivable payments) - Arus Kas Keluar = Arus Kas Bersih. Saldo Akhir = Saldo Awal(0) + Arus Kas Bersih. Cash basis accounting.

  **Must NOT do**: JANGAN balance sheet, JANGAN depreciation/tax, JANGAN accrual

  **Recommended Agent Profile**: **Category**: `deep` | **Skills**: []

  **Parallelization**: Wave 4 (with T17, T19, T20) | **Blocks**: T21, T22 | **Blocked By**: T16

  **References**: `app/Filament/Widgets/CashFlowStatsWidget.php:62-68` (IngredientBatch costs), `app/Filament/Widgets/CashFlowStatsWidget.php:53-59` (income from Orders)

  **Acceptance Criteria**:
  - [ ] Income Statement: Pendapatan - HPP = Laba Kotor = Laba Bersih + Beban
  - [ ] Cash Flow: Masuk - Keluar = Bersih
  - [ ] Laba Bersih = Simple report net (cash basis)

  **QA Scenarios**:

  ```
  Scenario: Income statement calculations correct
    Tool: Bash → verify all formulas chain
    Evidence: .sisyphus/evidence/task-18-income-statement.txt
  ```

  **Commit**: YES — `feat(report): implement rigid report with income statement and cash flow`
  - Files: `app/Services/RigidReportService.php`

- [x] 19. Custom report builder (category selection + aggregation)

  **What to do**: `App\Services\CustomReportService::generate(array $config): array`. Filter by selected categories only. Income categories from menu categories + unexpected. Expense from expenses.category + "Bahan Baku" (IngredientBatch) + unexpected. Aggregation: daily or monthly. Output: Date | Category | Type | Amount | Running Total.

  **Must NOT do**: JANGAN izinkan hide specific transactions, JANGAN hapus empty categories (tampilkan 0)

  **Recommended Agent Profile**: **Category**: `deep` | **Skills**: []

  **Parallelization**: Wave 4 (with T17, T18, T20) | **Blocks**: T21, T22 | **Blocked By**: T16

  **References**: `app/Filament/Pages/CashFlow.php:96-123` (category breakdown), `app/Models/Menu.php` (menu categories)

  **Acceptance Criteria**:
  - [ ] Only selected categories appear in output
  - [ ] Aggregation correct (daily/monthly grouping)
  - [ ] Running total accumulates correctly
  - [ ] Empty categories show 0, not hidden

  **QA Scenarios**:

  ```
  Scenario: Filtered custom report
    Tool: Bash → generate with specific categories, verify only those appear
    Evidence: .sisyphus/evidence/task-19-custom-filtered.txt
  ```

  **Commit**: YES — `feat(report): implement custom report builder with category filtering`
  - Files: `app/Services/CustomReportService.php`

- [x] 20. Report template save/load system

  **What to do**: Di FinancialReport page: tombol "Save as Template" (modal: name input, overwrite confirm). Template loader dropdown → auto-fill form. Delete template dengan konfirmasi. Scope: only current user's templates. Gunakan `ReportTemplate` model.

  **Must NOT do**: JANGAN izinkan lihat template user lain, JANGAN overwrite tanpa konfirmasi

  **Recommended Agent Profile**: **Category**: `quick` | **Skills**: []

  **Parallelization**: Wave 4 (with T17, T18, T19) | **Blocks**: T21, T22 | **Blocked By**: T3, T6, T16

  **References**: `app/Models/ReportTemplate.php` (template model), `app/Filament/Pages/FinancialReport.php` (host page)

  **Acceptance Criteria**:
  - [ ] Save/load/delete template works
  - [ ] Loading template restores date range, type, categories
  - [ ] Duplicate name per user rejected
  - [ ] Templates scoped to user

  **QA Scenarios**:

  ```
  Scenario: Save and load template
    Tool: Playwright → generate report → save template → reload → verify fields restored
    Evidence: .sisyphus/evidence/task-20-template-save-load.png
  ```

  **Commit**: YES — `feat(report): add template save/load/delete for financial reports`
  - Files: `app/Filament/Pages/FinancialReport.php`

---

- [x] 21. Excel/CSV export for all report types

  **What to do**: Buat `App\Exports\FinancialReportExport` menggunakan `maatwebsite/laravel-excel`. Implement `FromArray`, `WithHeadings`, `WithTitle`, `ShouldAutoSize`, `WithStyles`. Mapping data ke rows + heading per report type. CSV via `Excel::download(...)`. Pastikan encoding UTF-8 BOM.

  **Must NOT do**: JANGAN in-memory render semua data — gunakan chunking

  **Recommended Agent Profile**: **Category**: `quick` | **Skills**: []

  **Parallelization**: Wave 5 (with T22-T25) | **Blocked By**: T17, T18, T19

  **References**: `https://docs.laravel-excel.com/3.1/exports/` — Maatwebsite Excel docs

  **Acceptance Criteria**:
  - [ ] Excel (.xlsx) and CSV downloads produce valid files
  - [ ] File contains report title + date range + data table
  - [ ] Indonesian characters preserved

  **QA Scenarios**:
  ```
  Scenario: Excel export valid
    Tool: Bash → file report.xlsx → verify Microsoft Excel MIME
    Evidence: .sisyphus/evidence/task-21-excel-valid.txt
  ```

  **Commit**: YES — `feat(export): add Excel and CSV export for financial reports`
  - Files: `app/Exports/FinancialReportExport.php`

- [x] 22. PDF export for all report types

  **What to do**: Buat `App/Exports/FinancialReportPdf` dengan `barryvdh/laravel-dompdf`. Blade view `resources/views/exports/financial-report.blade.php` dengan styling rapi. Support Simple/Rigid/Custom via partial views.

  **Must NOT do**: JANGAN render PDF dari Filament page langsung — gunakan dedicated export class

  **Recommended Agent Profile**: **Category**: `quick` | **Skills**: []

  **Parallelization**: Wave 5 (with T21, T23-T25) | **Blocked By**: T17, T18, T19

  **References**: `https://github.com/barryvdh/laravel-dompdf`

  **Acceptance Criteria**:
  - [ ] Valid PDF (`file` command confirms "PDF document")
  - [ ] Multi-page support, proper page breaks
  - [ ] Indonesian characters render correctly

  **QA Scenarios**:
  ```
  Scenario: PDF valid
    Tool: Bash → file report.pdf → "PDF document, version 1.x"
    Evidence: .sisyphus/evidence/task-22-pdf-valid.txt
  ```

  **Commit**: YES — `feat(export): add PDF export for financial reports`
  - Files: `app/Exports/FinancialReportPdf.php`, `resources/views/exports/financial-report.blade.php`

- [x] 23. Integration tests

  **What to do**: `tests/Feature/FinanceModuleTest.php` — minimal 7 tests: migration column, bayar_nanti auto-create, cash flow queries source, simple report accuracy, receivable status transitions, custom report filtering, template save/load. Gunakan `RefreshDatabase`.

  **Must NOT do**: JANGAN test UI, JANGAN test third-party packages

  **Recommended Agent Profile**: **Category**: `unspecified-high` | **Skills**: []

  **Parallelization**: Wave 5 (with T21, T22, T24, T25) | **Blocked By**: T21, T22

  **Acceptance Criteria**: `php artisan test --filter=FinanceModuleTest` → all pass

  **QA Scenarios**: Bash → run test suite → verify all green
  **Evidence**: `.sisyphus/evidence/task-23-tests-pass.txt`

  **Commit**: YES — `test(finance): add integration tests for finance module`
  - Files: `tests/Feature/FinanceModuleTest.php`

- [x] 24. CashFlow performance verification

  **What to do**: Seed 10k+ orders via tinker. Measure load time via Playwright metrics. Verify <3s initial load, <1s period switch. Check query count ≤10 per widget. No N+1 queries. Add caching if needed.

  **Must NOT do**: JANGAN hapus fitur untuk perf, JANGAN tambah pagination

  **Recommended Agent Profile**: **Category**: `quick` | **Skills**: []

  **Parallelization**: Wave 5 (with T21-T23, T25) | **Blocked By**: T8, T9

  **Acceptance Criteria**: Load <3s, switch <1s, no N+1, <64MB memory

  **QA Scenarios**: Playwright → navigation timing <3000ms
  **Evidence**: `.sisyphus/evidence/task-24-load-time.txt`

  **Commit**: YES — `perf(cashflow): verify and document performance improvements`

- [x] 25. Final cleanup

  **What to do**: Hapus IncomeResource files. Verifikasi nav group "Finance Details": Cash Flow, Receivables, Expenses, Financial Reports. Hapus unused CSS di cash-flow blade. `php artisan optimize:clear`.

  **Must NOT do**: JANGAN hapus migration files atau model yang direferensi

  **Recommended Agent Profile**: **Category**: `quick` | **Skills**: []

  **Parallelization**: Wave 5 (with T21-T24) | **Blocked By**: T11, T22

  **Acceptance Criteria**: No IncomeResource references, clean nav, optimize:clear succeeds

  **QA Scenarios**: Playwright → sidebar nav verified, all links work
  **Evidence**: `.sisyphus/evidence/task-25-nav-clean.png`

  **Commit**: YES — `chore(finance): cleanup dead code and verify navigation`

---

## Final Verification Wave (MANDATORY — after ALL implementation tasks)

> 4 review agents run in PARALLEL. ALL must APPROVE. Present consolidated results to user and get explicit "okay" before completing.
> **Do NOT auto-proceed after verification. Wait for user's explicit approval.**

- [x] F1. **Plan Compliance Audit** — `oracle` — APPROVED (9/9 Must Have, 7/7 Must NOT Have)

- [x] F2. **Code Quality Review** — `unspecified-high` — PASS (16 clean, 4 minor issues noted)

- [x] F3. **Real Manual QA** — `unspecified-high` — PASS (7/7 scenarios)

- [x] F4. **Scope Fidelity Check** — `deep` — CONDITIONAL PASS (10/10 guardrails, 22/25 evidence)
  For each task: read "What to do", read actual diff. Verify 1:1 — nothing missing, nothing beyond spec. Check "Must NOT do" compliance. Detect cross-task contamination. Flag unaccounted changes.
  Output: `Tasks [N/N compliant] | Contamination [CLEAN/N issues] | Unaccounted [CLEAN/N files] | VERDICT`

---

## Commit Strategy

- **Wave 1**: `feat(migration): add order_id FK + migrate incomes + report_templates table` — T1, T2, T3 group
- **T4**: `chore(deps): add maatwebsite/laravel-excel and barryvdh/laravel-dompdf`
- **T5**: `feat(order): add bayar_nanti payment option with is_paid handling`
- **T6**: `feat(model): create ReportTemplate model` (with T3)
- **T7**: `refactor(cashflow): rebuild as proper Filament page with free date range`
- **T8**: `perf(cashflow): add free date range and caching to stats widget`
- **T9**: `perf(cashflow): add free date range and dynamic bucketing to chart widget`
- **T10**: `feat(expense): restore navigation and enhance expense resource`
- **T11**: `refactor(income): retire IncomeResource after migration`
- **T12**: `feat(receivable): auto-create receivable when order uses bayar_nanti`
- **T13**: `feat(receivable): enhance table with order link and auto-fill form`
- **T14+T15**: `feat(receivable): add detail page with order summary and payment tracking`
- **T16**: `feat(report): create FinancialReport page with date range and type selection`
- **T17**: `feat(report): implement simple report generator service`
- **T18**: `feat(report): implement rigid report with income statement and cash flow`
- **T19**: `feat(report): implement custom report builder with category filtering`
- **T20**: `feat(report): add template save/load/delete for financial reports`
- **T21**: `feat(export): add Excel and CSV export for financial reports`
- **T22**: `feat(export): add PDF export for financial reports`
- **T23**: `test(finance): add integration tests for finance module`
- **T24**: `perf(cashflow): verify and document performance improvements`
- **T25**: `chore(finance): cleanup dead code and verify navigation`

---

## Success Criteria

### Verification Commands
```bash
# Migration
php artisan migrate:fresh --seed          # Expected: no errors

# No Income references in active code
grep -r "Income::" app/Filament/          # Expected: 0 results

# CashFlow queries Orders, not incomes
grep -r "use App\\\\Models\\\\Income" app/Filament/Pages/CashFlow.php  # Expected: 0

# Tests pass
php artisan test --filter=FinanceModuleTest   # Expected: all pass

# Receivable auto-create
php artisan tinker --execute="App\\Models\\Order::create(['order_code'=>'TEST','payment_method'=>'bayar_nanti','is_paid'=>false,'total_amount'=>100000,'status'=>'pending','order_type'=>'cashier']); echo App\\Models\\Receivable::latest()->first()->amount;"
# Expected: 100000.00

# Simple report accuracy
php artisan tinker --execute="print_r(app(App\\Services\\SimpleReportService::class)->generate('2026-05-01','2026-05-31'));"
# Expected: structured array with totals
```

### Final Checklist
- [ ] All "Must Have" items present and functional
- [ ] All "Must NOT Have" guardrails respected
- [ ] 3 migration files created (order_id FK, incomes migrate, report_templates)
- [ ] CashFlow page loads < 3s with 10k+ orders
- [ ] Receivables linked to Orders via FK
- [ ] Bayar Nanti orders auto-create Receivables
- [ ] Simple report: totals match raw SQL
- [ ] Rigid report: Income Statement + Cash Flow with correct calculations
- [ ] Custom report: category filtering + template save/load
- [ ] Export: valid PDF, CSV, Excel files generated
- [ ] All QA evidence files present in `.sisyphus/evidence/`
- [ ] No `Income::` references in active code
- [ ] Navigation clean: 5 items in Finance Details group

