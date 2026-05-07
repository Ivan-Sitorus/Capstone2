# Learnings — finance-module-overhaul

## Pre-flight Checks (2026-05-06)
- bayar_nanti already EXISTS in orders table migration (payment_method enum includes 'bayar_nanti')
- incomes table STILL EXISTS (not dropped yet) — Task 2 will migrate it
- ExpenseResource is hidden ($shouldRegisterNavigation = false)
- IncomeResource is hidden
- No export packages installed (maatwebsite, barryvdh)
- CashFlow.php had crash from dead route — FIXED (removed header actions)
- Receivable model has NO order_id FK yet
- ReportTemplate model doesn't exist yet
- Task 3: Created report_templates migration + verified model (already existed with correct structure)
- Task 12: Added booted() listener to Order model — auto-creates Receivable when order has payment_method='bayar_nanti' && is_paid=false
  - Uses static::created event (created_at already populated at that point)
  - Duplicate guard: checks receivable()->exists() before creating
  - receivable() hasOne relation added alongside other relations
  - Order.php already had a boot() method (for order_code generation); booted() works alongside it
- Task 8: CashFlowStatsWidget enhanced with dual-mode (date range OR period)
  - dateStart/dateEnd properties + #[On('cashflow-date-changed')] listener
  - Cache::remember() wrapping getStats(), key = md5(period + dateStart + dateEnd), TTL 5 min
  - Switching modes clears the other (period→clears date, date→clears period)
  - prevRange() auto-computes same-length prior window when date range active

## Task 7 — CashFlow Page Rebuild (2026-05-06)
- CashFlow.php reduced from 273 lines to 53 lines (80% reduction)
- All 10+ `Income::` references removed — queries replaced with widget-based approach
- 8 dead methods removed (getSummary, getCategoryBreakdown, getTopSources, getTopVendors, getTransactions, getSvgChart, chartByDay, chartByWeek)
- 3 private helpers removed (dateRange, prevRange, pctChange)
- Carbon and Expense imports no longer needed
- Blade view simplified from 142 lines to 34 lines — removed all custom dark-theme CSS
- Added date_start/date_end properties + #[On('cashflow-date-changed')] listener for future date range picker
- LSP diagnostics: clean, zero errors

## Task 9 — CashFlowChartWidget: Free Date Range (2026-05-06)
- Added `public string $dateStart = ''` and `public string $dateEnd = ''` properties
- Added `#[On('cashflow-date-changed')]` listener that sets dateStart/dateEnd
- Updated `onPeriodChanged()` to also reset date range when period changes
- `buildData()` now uses `Cache::remember()` — key = md5(serialize([period, dateStart, dateEnd])), TTL 300s
- Dynamic bucketing in `buildDynamicRange()`:
  - ≤7d → byDayRange() daily, label "D MMM"
  - 8-60d → byWeekRange() weekly, label "Minggu N"
  - 61-365d → byMonthRange() monthly, label "MMM YYYY"
  - >365d → byQuarterRange() quarterly, label "MMM YYYY – MMM YYYY", capped 200 points
- Period-based methods (byHour, byEvenDay, byMonthOfYear, byMonth) preserved as fallbacks
- Used `isoFormat()` for locale-aware Indonesian month names
- LSP: 0 errors, 0 warnings

## Task 11 — IncomeResource Retired (2026-05-06)
- Added `@deprecated` docblock to IncomeResource class
- getPages() now returns `[]` (empty array) — routes disabled
- Removed unused imports: ListIncomes, EditIncome, Pages
- $shouldRegisterNavigation already false (was already hidden)
- grep shows only internal references (within IncomeResource folder itself)
- Files kept for historical reference, not deleted

## Task 13 — ReceivableResource Enhanced (2026-05-06)
- Added order link column: `TextColumn::make('order.order_code')` with URL to Order resource view
- Added searchable order_id Select with `afterStateUpdated` callback for auto-fill
- Auto-fill populates: customer_name (from order->customer) + amount (from order->total_amount)
- order_id is nullable (optional) — admin can leave blank
- customer_name remains editable for manual override
- Added due_date range filter (from/until DatePickers)
- Preserved: status filter, all existing columns, defaultSort('due_date', 'asc'), all actions
- LSP diagnostics: clean

## Task 14 — ViewReceivable Page (2026-05-06)
- Created ViewReceivable.php extending Filament\Resources\Pages\ViewRecord
- In Filament 4, infolist is defined in Resource (not page) — pattern from OrderResource
- Added infolist() to ReceivableResource with sections:
  * Receivable Information (customer_name, invoice_date, amount, paid_amount, remaining_amount, due_date, status, notes)
  * Order Information (order_code as link, status, total) — visible when order exists
  * Order Items — visible when order exists and has items
  * "No Linked Order" message — visible when order is null
- Added "Record Payment" header action with modal:
  * TextInput for payment_amount with prefix 'Rp'
  * maxValue validation = remaining amount (prevents overpayment)
  * Updates paid_amount and auto-sets status (partial/paid)
- Added ViewAction to table recordActions for view button
- Registered in getPages(): 'view' => ViewReceivable::route('/{record}')
- LSP: only pre-existing issues (Filament\Forms\Set, deprecated form hint)

## Task 18 — RigidReportService (2026-05-06)
- Created `app/Services/RigidReportService.php` with `generate($dateStart, $dateEnd): array`
- Income Statement: Pendapatan (orders is_paid=true + UnexpectedTransaction pemasukan) - HPP (IngredientBatch) = Laba Kotor - Beban Operasional (Expense) - Beban Tak Terduga (UnexpectedTransaction pengeluaran) = Laba/Rugi Bersih
- Cash Flow: Arus Kas Masuk (Pendapatan + Receivable payments updated in period) - Arus Kas Keluar (Beban Operasional + HPP + Beban Tak Terduga) = Arus Kas Bersih
- All queries are database-level aggregation (sum/selectRaw)
- Cash basis accounting (is_paid = recognized)
- Receivable payments tracked via `updated_at` on Receivables with status paid/partial
- LSP diagnostics: clean

## Task 19 — CustomReportService (2026-05-06)
- Created `app/Services/CustomReportService.php` with `generate(array $config): array`
- Category prefix scheme: `menu:{id}`, `unexpected_income`, `expense:{name}`, `bahan_baku`, `unexpected_expense`
- 5 data sources queried via UNION ALL SQL:
  1. OrderItems→Menu→Category (paid orders, income)
  2. UnexpectedTransaction jenis='pemasukan' (income)
  3. Expenses table by category (expense)
  4. IngredientBatch purchases as "Bahan Baku" (expense)
  5. UnexpectedTransaction jenis='pengeluaran' (expense)
- `expenses.date` is a DATE column (not timestamp) — bindings use toDateString(), not full Carbon
- Aggregation: daily → `DATE(created_at)`, monthly → `DATE_TRUNC('month', created_at)::date`
- Zero-fill for missing combos done in PHP (structural, not summation) — ensures empty categories show 0
- Running total computed via PHP loop accumulator (income adds, expense subtracts)
- Summary totals computed via PHP loop (only non-SQL part — running total is inherently sequential)
- LSP diagnostics: clean, zero errors

## Task 16 — FinancialReport Page (2026-05-06)
- Created FinancialReport.php using Page + InteractsWithForms trait
- Form with InteractsWithForms renders `<form>` automatically via `{{ $this->form }}` — do NOT wrap in `<x-filament::form>` (would create nested forms)
- category_ids MultiSelect combines: Category::where('is_active', true) + Expense::distinct('category')
- For Menu categories → use `Category::pluck('name', 'id')`
- For Expense categories → use `Expense::distinct()->pluck('category', 'category')` — Expense has string `category` field, not FK
- DatePicker with `->afterOrEqual('date_start')` enforces end >= start validation
- `->native(false)` + `->displayFormat('Y-m-d')` for non-native Filament date picker
- Template loading via `wire:model.change` + `updatedSelectedTemplateId()` hook is cleaner than manual Load button
- Actions within form schema (`Forms\Components\Actions`) renders buttons inside the form tag
- `->live()` on Select enables reactive visibility for dependent fields (category_ids)

## Task 24 — CashFlow Performance Verification (2026-05-06)
- grep "Income::" app/Filament/ → ONLY found in IncomeResource.php (deprecated) — PASS
- CashFlow.php: 53 lines, no dead methods (getSummary, getCategoryBreakdown, etc. removed) — PASS
- CashFlowStatsWidget: Cache::remember() at line 119, key = md5(period+dateStart+dateEnd), TTL 300s — PASS
- CashFlowChartWidget: Cache::remember() at line 71, key = md5(serialize([period,dateStart,dateEnd])), TTL 300s — PASS
- Evidence written to: .sisyphus/evidence/task-24-cashflow-perf.txt

## Task 21 — Excel/CSV Export (2026-05-06)
- Created `app/Exports/FinancialReportExport.php` implementing FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithEvents
- Constructor accepts: type (simple|rigid|custom), data, dateRange
- Headers per type:
  * Simple: No | Kategori | Type | Amount
  * Rigid: Section | Subsection | Amount
  * Custom: Date | Category | Type | Amount | Running Total
- Number formatting: Rp currency (Indonesian Rupiah via number_format)
- Category/source mapping: cash→Tunai, qris→QRIS, unexpected_income→Pemasukan Tidak Terduga, etc.
- UTF-8 BOM handling via WithEvents (BeforeWriting event)
- LSP diagnostics: clean, zero errors

## Task 23 — Integration Tests (2026-05-06)
- Created `tests/Feature/FinanceModuleTest.php` with 6 tests covering all finance module changes
- Tests follow pattern from `tests/Feature/Admin/` — RefreshDatabase, assertDatabaseHas, direct model operations
- Order model booted() auto-creates Receivable when payment_method='bayar_nanti' && is_paid=false
- Receivable::recordPayment() transitions pending→partial→paid, throws on already-paid
- isOverdue() checks status !== paid AND due_date < now() (uses lessThan, not lte)
- scopeOverdue() filters status != paid AND due_date < now()
- SimpleReportService accuracy verified by comparing against raw DB::table() queries
- Test 6 uses Carbon::setTestNow() to freeze time for deterministic assertions
- No auth/filament dependencies — pure model + service testing
- SQLite :memory: used per phpunit.xml config
- LSP diagnostics: clean, zero errors
