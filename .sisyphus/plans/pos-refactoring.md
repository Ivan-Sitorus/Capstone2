# POS Cafe Refactoring — UI + Feature + Database Changes

## TL;DR

> **Quick Summary**: 15 changes to Filament 5 admin panel: fix FinancialReport crash, integer-only prices globally, remove expenses/waste-records/customer-role, collapsible sidebar, auto-slug, QR code tables, and multiple form/UI improvements.
>
> **Deliverables**:
> - FinancialReport page fixed (no more ERR_EMPTY_RESPONSE)
> - All prices converted to integer (monetary fields only, ingredient quantities stay decimal)
> - Expenses page removed, Waste Records page removed, Customer role removed
> - Collapsible sidebar + SPA mode enabled
> - Slug auto-generated from name (kebab-case, create only)
> - CafeTable CRUD resource + QR code generation
> - Receivables improved, stock adjustment reason to varchar(255)
>
> **Estimated Effort**: Large
> **Parallel Execution**: YES — 4 waves
> **Critical Path**: Fix FinancialReport → Integer prices → Remove pages → UI improvements

---

## Context

### Original Request
User wants to refactor the POS cafe Filament 5 admin panel with 15 changes: fix bugs (FinancialReport crash, image upload delay), database changes (integer prices, remove customer role), page removals (expenses, waste records), UI improvements (sidebar, slug, labels), and new features (QR code tables).

### Interview Summary
**Key Decisions**:
- FinancialReport: page returns ERR_EMPTY_RESPONSE (Livewire crash) — the page is a stub
- Input gambar delay: menu image upload in Filament — slight delay in modal
- Remove customer role: customer has no account, remove 'customer' from user role enum
- Cashflow: **DEFERRED** — don't touch
- Integer prices: apply globally to ALL monetary fields (not ingredient quantities)
- Migration rule: only CREATE operations (new migration files for schema changes)

**Metis Review – Critical Findings**:
- ⚠️ FinancialReport references Expense model — must fix BEFORE removing expenses
- ⚠️ ReceivableResource has broken `$order->customer` reference — Order uses `customer_name`, not `customer` relation
- ⚠️ Waste record removal needs stock_movements FK cleanup
- ⚠️ Integer prices: ingredient quantities MUST stay decimal (0.5 kg, 250 ml)
- ⚠️ Customer role removal: PostgreSQL ENUM can't be modified — convert to VARCHAR
- ⚠️ Auto-slug: only on CREATE, not EDIT

---

## Work Objectives

### Core Objective
Refactor the Filament 5 admin panel to fix bugs, simplify database schema (integer prices, remove dead features), improve UX (sidebar, slug, labels), and add cafe table management with QR codes.

### Definition of Done
- [ ] `curl -s http://localhost/admin/financial-report` returns 200
- [ ] All monetary fields store as integer in DB
- [ ] Expenses page removed from sidebar
- [ ] Waste records page removed from sidebar
- [ ] Customer role removed from users table
- [ ] Receivables page functional with correct customer name reference
- [ ] Stock adjustment reason is varchar(255)
- [ ] Sidebar is collapsible on desktop
- [ ] Slug auto-generated from name on create
- [ ] Category creation available in menu form
- [ ] CafeTable CRUD working with QR code generation
- [ ] Cashback input has no default value (empty/null)

### Must Have
- Integer prices: menus, orders, payments, receivables, expenses
- FinancialReport page loads without error
- No customer role in database or UI
- QR code generation for cafe tables

### Must NOT Have
- Do NOT change ingredient quantities to integer
- Do NOT change cost_per_unit to integer (small costs lose precision)
- Do NOT change cashflow page (deferred)
- Do NOT auto-generate slugs on EDIT
- Do NOT have migration files with ALTER/ADD/DROP operations

---

## Verification Strategy

### Test Decision
- **Infrastructure exists**: YES (25 PHPUnit tests + Playwright QA)
- **Automated tests**: Tests-after
- **Playwright QA**: Verify admin pages load without errors

### QA Policy
Every task includes agent-executed verification:
- **API/Backend**: Bash (curl) — check HTTP status codes, verify DB changes
- **UI/Frontend**: Playwright — load admin pages, verify elements
- Evidence saved to `.sisyphus/evidence/`

---

## Execution Strategy

### Parallel Execution Waves

```
Wave I (Start Immediately — bug fixes + code cleanup):
├── Task 1: Fix FinancialReport page crash [deep]
├── Task 2: Fix ReceivableResource broken customer reference [quick]
├── Task 3: Remove IncomeResource dead code [quick]
└── Task 4: Fix ReceivableResource sortable computed column [quick]

Wave II (After Wave I — database changes):
├── Task 5: Integer prices migration (monetary fields only) [deep]
├── Task 6: Remove customer role from database [deep]
├── Task 7: Remove waste_records + handle FK cascade [quick]
├── Task 8: Stock adjustment reason to varchar(255) [quick]
└── Task 9: Cashback field nullable (no default) [quick]

Wave III (After Wave II — page + UI changes):
├── Task 10: Remove ExpensesResource [quick]
├── Task 11: Collapsible sidebar + SPA mode config [quick]
├── Task 12: Auto-generate slug from name [quick]
├── Task 13: Category creation in menu form [quick]
├── Task 14: Repeater labels based on ingredient unit [quick]
├── Task 15: Menu image upload optimization [quick]
└── Task 16: UserResource — remove customer role option [quick]

Wave IV (After Wave III — new features):
├── Task 17: Install QR code library [quick]
├── Task 18: Create CafeTableResource (CRUD) [deep]
├── Task 19: QR code generation service + display [deep]
├── Task 20: Run all QA verification [unspecified-high]
└── Task 21: Final cleanup + Playwright admin smoke tests [unspecified-high]
```

### Critical Path
```
Task 1 (FinancialReport fix) → Task 2 (receivable fix) → Task 5 (integer prices)
  → Task 6 (remove customer) → Task 10 (remove expenses) → Task 11-16 (UI)
  → Task 17-19 (QR+tables) → Task 20-21 (QA)
```

---

## TODOs

- [x] 1. **Fix FinancialReport Page Crash**

  **What to do**:
  - `curl -s http://localhost/admin/financial-report` returns ERR_EMPTY_RESPONSE (Livewire crash)
  - Read `app/Filament/Pages/FinancialReport.php` — it's a STUB
  - The stub has `generateReport()` with `// stub - Task 17-19 will replace` comment
  - It references `Expense` model (line 96-99) and various report services
  - Fix: replace the stub with working report logic OR make it gracefully show "coming soon" until services are ready
  - Check `resources/views/filament/pages/financial-report.blade.php` for any PHP errors
  - Verify: `curl -s -o /dev/null -w "%{http_code}" http://localhost/admin/financial-report` returns 200
  - Verify via Playwright: navigate to `/admin/financial-report`, no console errors

  **Must NOT do**: Do NOT remove the page — just fix the crash

  **Recommended Agent Profile**: `deep`
  **Parallelization**: Wave I, **Blocked By**: None, **Blocks**: Task 10

  **References**: 
  - `app/Filament/Pages/FinancialReport.php` — has `Expense::distinct()` which will break after expense removal
  - `resources/views/filament/pages/financial-report.blade.php`
  - `app/Services/SimpleReportService.php`, `RigidReportService.php`, `CustomReportService.php`

  **Acceptance Criteria**:
  - [ ] `curl -s -o /dev/null -w "%{http_code}" http://localhost/admin/financial-report` → 200
  - [ ] Playwright: page loads without ERR_EMPTY_RESPONSE
  - [ ] No reference to `Expense` model in FinancialReport.php

  **QA Scenarios**:
  ```
  Scenario: FinancialReport page loads
    Tool: Bash (curl)
    Steps: curl -s -o /dev/null -w "%{http_code}" http://localhost/admin/financial-report → 200
    Evidence: .sisyphus/evidence/task-1-financial-report.txt
  ```

- [x] 2. **Fix ReceivableResource Broken customer Reference**

  **What to do**:
  - Read `app/Filament/Resources/ReceivableResource.php` line 60
  - Current: `$order->customer` — but Order model has NO `customer` relationship
  - Order tracks customers via `customer_name` + `customer_phone` strings
  - Fix: change to `$order->customer_name` or just remove the auto-fill logic since `customer_name` is a direct field on Order, not a relationship
  - Also fix line 238: `remaining_amount` is sortable but is a computed accessor — add `->sortable(false)` or remove sortable

  **Must NOT do**: Do NOT change Order model

  **Recommended Agent Profile**: `quick`
  **Parallelization**: Wave I, **Blocked By**: None

  **References**:
  - `app/Filament/Resources/ReceivableResource.php:60` — broken `$order->customer`
  - `app/Filament/Resources/ReceivableResource.php:238` — sortable computed column
  - `app/Models/Order.php` — has `customer_name`, `customer_phone`, NO `customer()` relation

  **Acceptance Criteria**:
  - [ ] ReceivableResource form loads without error when selecting an order
  - [ ] `remaining_amount` column not sortable

  **Commit**: `fix: receivable resource broken customer reference + sortable`

- [x] 3. **Remove IncomeResource Dead Code**

  **What to do**:
  - `IncomeResource` has `$shouldRegisterNavigation = false` and empty `getPages()` — income table was DROPPED by migration `2026_05_06_170043`
  - Delete `app/Filament/Resources/IncomeResource.php` and its Pages directory
  - Delete `app/Models/Income.php` if it exists
  - Verify no other code references `Income` model

  **Must NOT do**: Do NOT delete the migration file (it's history)

  **Recommended Agent Profile**: `quick`
  **Parallelization**: Wave I, **Blocked By**: None

  **Acceptance Criteria**:
  - [ ] `IncomeResource.php` deleted
  - [ ] `grep -r "IncomeResource\|Income::class" app/` returns no results

  **Commit**: `chore: remove dead IncomeResource code`

- [x] 4. **Fix ReceivableResource Sortable Column**

  **What to do**:
  - `ReceivableResource.php` line 238: `remaining_amount` is `->sortable()` but it's a computed accessor
  - Computed accessors can't be sorted by SQL — will throw error
  - Fix: remove `->sortable()` or make it `->sortable(false)`
  - If remaining amount must be sortable, add a DB column and compute on save

  **Recommended Agent Profile**: `quick`
  **Parallelization**: Wave I (with Tasks 2-3)

  **Acceptance Criteria**:
  - [ ] `remaining_amount` column does not throw SQL error on sort click

  **Commit**: `fix: receivable remaining_amount sortable column`

- [x] 5. **Integer Prices Migration — Monetary Fields Only**

  **What to do**:
  - Convert ALL monetary price columns from DECIMAL to BIGINT (store in Rupiah, no decimals)
  - **ONLY monetary fields** — do NOT touch ingredient quantities (stay decimal)
  - Tables/columns to convert:
    - `menus`: `price`, `student_price`, `cashback`
    - `orders`: `total_amount`
    - `order_items`: `unit_price`, `subtotal`
    - `payments`: `amount`
    - `expenses`: `amount`
    - `receivables`: `amount`, `paid_amount`
  - Create NEW migration files (CREATE operations): drop old tables → create new tables with BIGINT columns → migrate data (multiply by 1, verify whole numbers)
  - Update all Model casts: `'decimal:2'` → `'integer'` for monetary fields
  - Update all Filament form fields: `NumberInputHelper::decimal()` → `NumberInputHelper::integer()`, or just `TextInput::numeric()->integer()`
  - Update all Filament table columns: `->money('IDR')` format handles BIGINT correctly
  - ⚠️ Verify ALL existing prices are whole numbers: `SELECT price FROM menus WHERE price != FLOOR(price)` — must return 0 rows
  - ⚠️ Do NOT convert: `ingredients.low_stock_threshold`, `ingredient_batches.quantity`, `ingredient_batches.cost_per_unit`, `menu_ingredients.quantity_used`, `stock_movements.*`, `stock_adjustments.*`, `waste_records.quantity`, `daily_ingredient_usages.jumlah_digunakan`

  **Must NOT do**: Do NOT touch ingredient/inventory quantity columns

  **Recommended Agent Profile**: `deep`
  **Parallelization**: Wave II, **Blocked By**: Task 1, **Blocks**: Task 10

  **References**:
  - Migration files in `database/migrations/` — identify all create_*_table files containing price/amount columns
  - Model casts in `app/Models/Menu.php`, `Order.php`, `OrderItem.php`, `Payment.php`, `Expense.php`, `Receivable.php`
  - `app/Filament/Helpers/NumberInputHelper.php` — add `integer()` method

  **Acceptance Criteria**:
  - [ ] All monetary columns are BIGINT in DB
  - [ ] No DECIMAL price columns remaining
  - [ ] Ingredient quantities are STILL DECIMAL
  - [ ] `php artisan migrate:fresh` succeeds
  - [ ] Form inputs accept only whole numbers
  - [ ] Table displays format correctly (e.g., "Rp 12.500" from integer 12500)

  **QA Scenarios**:
  ```
  Scenario: Menu price stored as integer
    Tool: Bash (psql via Docker)
    Steps:
      1. INSERT INTO menus (name, price) VALUES ('Test', 12500)
      2. SELECT price, pg_typeof(price) FROM menus WHERE name='Test' → 12500 | bigint
    Evidence: .sisyphus/evidence/task-5-integer-price.txt
  ```

  **Commit**: `refactor: integer prices for all monetary fields`

- [ ] 6. **Remove Customer Role From Database**

  **What to do**:
  - PostgreSQL ENUM doesn't support removing values → convert `role` column to VARCHAR
  - Create new migration: drop old `users` table → recreate with `role` VARCHAR(20) default 'cashier'
  - Migrate existing admin/cashier users (exclude 'customer' role)
  - Update `User` model: remove role ENUM references
  - Update `UserSeeder`: remove Budi Pelanggan entry, change all roles to 'admin'/'cashier' only
  - Update `UserResource` form: remove 'customer' from select options
  - ⚠️ Check: if any LIVE users have role='customer', handle them (delete or change to 'cashier')

  **Must NOT do**: Do NOT break existing admin/cashier login

  **Recommended Agent Profile**: `deep`
  **Parallelization**: Wave II, **Blocked By**: Task 5

  **Acceptance Criteria**:
  - [ ] `role` column is VARCHAR, not ENUM
  - [ ] No 'customer' value in role column
  - [ ] Admin/kasir login works
  - [ ] UserResource form shows only 'admin' + 'cashier'

  **QA Scenarios**:
  ```
  Scenario: Customer role removed
    Tool: Bash (psql)
    Steps: SELECT DISTINCT role FROM users → only 'admin' and 'cashier'
    Evidence: .sisyphus/evidence/task-6-remove-customer.txt
  ```

  **Commit**: `refactor: remove customer role from users table`

- [ ] 7. **Remove Waste Records — Handle FK Cascade**

  **What to do**:
  - Migration `2026_04_11_000006` added `waste_record_id` FK to `stock_movements`
  - Create NEW migration: drop `stock_movements` → recreate without `waste_record_id` column → drop `waste_records` table
  - Delete `WasteRecordResource` and its Pages
  - Delete `WasteRecord` model
  - Delete `WasteRecordObserver` (if exists)
  - Delete `tests/Feature/Admin/WasteRecordFlowTest.php`
  - Remove WasteRecord from navigation

  **Must NOT do**: Do NOT delete the original migration file (history)

  **Recommended Agent Profile**: `quick`
  **Parallelization**: Wave II

  **Acceptance Criteria**:
  - [ ] Waste record removed from admin sidebar
  - [ ] `stock_movements` table has no `waste_record_id` column
  - [ ] `waste_records` table dropped
  - [ ] No WasteRecord references in code

  **Commit**: `refactor: remove waste records + FK cleanup`

- [ ] 8. **Stock Adjustment Reason — text → varchar(255)**

  **What to do**:
  - ⚠️ First: check `SELECT MAX(LENGTH(reason)) FROM stock_adjustments` — must be ≤ 255
  - Create new migration: drop `stock_adjustments` → recreate with `reason` as `varchar(255)`
  - Update form: `Textarea` → `TextInput` with `maxLength(255)`
  - If any reason > 255 chars: truncate with warning during migration

  **Must NOT do**: Do NOT lose existing reason data

  **Recommended Agent Profile**: `quick`
  **Parallelization**: Wave II, **Blocked By**: Task 5

  **Acceptance Criteria**:
  - [ ] `reason` column is `varchar(255)`
  - [ ] Form input is TextInput with max 255 chars

  **Commit**: `refactor: stock adjustment reason varchar 255`

- [ ] 9. **Cashback Field Nullable (No Default)**

  **What to do**:
  - Current: `unsignedInteger default 0` in migration, `->default(0)` in form
  - Create new migration (within the menus table recreation from Task 5): make `cashback` nullable, no default
  - Update MenuResource form: remove `->default(0)`, leave empty
  - Model cast: `'integer'` (no change needed, just verify nullable)

  **Must NOT do**: Do NOT break existing menu data

  **Recommended Agent Profile**: `quick`
  **Parallelization**: Wave II

  **Acceptance Criteria**:
  - [ ] `cashback` column allows NULL
  - [ ] Form input shows empty (not "0")

  **Commit**: `refactor: cashback nullable no default`

- [ ] 10. **Remove ExpensesResource**

  **What to do**:
  - Delete `app/Filament/Resources/ExpenseResource.php` and its Pages
  - Delete `app/Models/Expense.php`
  - Delete `database/migrations/*create_expenses_table.php` (original migration)
  - ⚠️ Task 1 must be complete first (FinancialReport no longer references Expense)
  - Remove expense table from DB schema

  **Must NOT do**: Do NOT delete if FinancialReport still references Expense

  **Recommended Agent Profile**: `quick`
  **Parallelization**: Wave III, **Blocked By**: Task 1, Task 5

  **Acceptance Criteria**:
  - [ ] Expenses not in admin sidebar
  - [ ] `expenses` table dropped
  - [ ] No Expense references in code

  **Commit**: `refactor: remove expenses resource`

- [ ] 11. **Collapsible Sidebar + SPA Mode Config**

  **What to do**:
  - Edit `app/Providers/Filament/AdminPanelProvider.php`
  - Add `->sidebarCollapsibleOnDesktop()` to the Panel config
  - SPA mode already enabled (`->spa()`) — verify it works
  - Optional: add `->collapsedSidebarWidth('4rem')` for compact collapsed state
  - Test: sidebar collapses/expands on desktop

  **Must NOT do**: Do NOT change sidebar width or nav groups

  **Recommended Agent Profile**: `quick`
  **Parallelization**: Wave III

  **Acceptance Criteria**:
  - [ ] Sidebar has collapse toggle button on desktop
  - [ ] Collapsed state shows icons only

  **Commit**: `feat: collapsible sidebar`

- [ ] 12. **Auto-Generate Slug From Name (Create Only)**

  **What to do**:
  - In MenuResource and CategoryResource forms:
  - Add `->live(onBlur: true)` to `name` field
  - Add `->afterStateUpdated(fn (Set $set, $state) => $set('slug', Str::slug($state)))`
  - Hide slug input on create form (auto-generated)
  - On edit: show slug as read-only, do NOT regenerate
  - In table view: hide slug column (`->hidden()`)
  - ⚠️ Duplicate slug handling: if slug already exists, append `-2`, `-3`, etc.

  **Must NOT do**: Do NOT regenerate slug on EDIT

  **Recommended Agent Profile**: `quick`
  **Parallelization**: Wave III

  **Acceptance Criteria**:
  - [ ] Typing name auto-fills slug on create
  - [ ] Slug not editable on edit
  - [ ] Slug hidden from table
  - [ ] Duplicate slugs handled with suffix

  **Commit**: `feat: auto-generate slug from name`

- [ ] 13. **Category Creation in Menu Form (Optional)**

  **What to do**:
  - In MenuResource form, add `->createOptionForm()` to category Select
  - The modal form includes: name (TextInput), slug (auto-generated, hidden)
  - Category creation is optional — user can select existing or create new
  - Modal action label: "+ Kategori Baru"

  **Must NOT do**: Do NOT make category required for menu creation

  **Recommended Agent Profile**: `quick`
  **Parallelization**: Wave III

  **Acceptance Criteria**:
  - [ ] "+" button appears next to category select
  - [ ] Clicking opens modal to create category
  - [ ] New category auto-selected after creation

  **Commit**: `feat: inline category creation in menu form`

- [ ] 14. **Repeater Labels Based on Ingredient Unit**

  **What to do**:
  - In MenuResource's ingredients repeater:
  - Change `itemLabel` to show: "Nama Bahan (Unit)" 
  - Read ingredient unit from the selected ingredient relationship
  - Current: already has a suffix showing unit — keep it, just improve label

  **Must NOT do**: Do NOT change repeater structure

  **Recommended Agent Profile**: `quick`
  **Parallelization**: Wave III

  **Acceptance Criteria**:
  - [ ] Repeater labels show ingredient name + unit

  **Commit**: `refactor: repeater labels based on ingredient unit`

- [ ] 15. **Menu Image Upload Optimization**

  **What to do**:
  - The FileUpload component has a slight delay — modal appears first, image input renders a moment later
  - This is a Filament Livewire re-render behavior, not a real bug
  - Optimize: add `->placeholder('Pilih gambar menu...')`, ensure spinner shows during upload
  - If delay is unacceptable: use `->loadingIndicator()` or `->extraAttributes(['x-cloak' => ''])` for smoother UX

  **Must NOT do**: Do NOT change the image upload service (WebP conversion etc.)

  **Recommended Agent Profile**: `quick`
  **Parallelization**: Wave III

  **Acceptance Criteria**:
  - [ ] Image input appears without visible delay
  - [ ] Upload works with WebP conversion

  **Commit**: `fix: menu image upload loading state`

- [ ] 16. **UserResource — Remove Customer Role Option**

  **What to do**:
  - Already partially handled by Task 6 (DB change)
  - In `UserResource.php` form: remove `'customer' => 'Pelanggan'` from role select options
  - In table: remove `'customer' => 'warning'` from role badge colors
  - Any `->default('customer')` in form → change to `->default('cashier')`

  **Must NOT do**: Do NOT break admin user CRUD

  **Recommended Agent Profile**: `quick`
  **Parallelization**: Wave III, **Blocked By**: Task 6

  **Acceptance Criteria**:
  - [ ] Role select shows only 'admin' and 'cashier'
  - [ ] No 'customer' in badge color map

  **Commit**: `refactor: remove customer role from user resource`

- [ ] 17. **Install QR Code Library**

  **What to do**:
  - Install via composer: `composer require linkxtr/laravel-qrcode`
  - Verify: `QrCode::size(100)->generate('test')` produces valid SVG
  - No DB changes needed — cafe_tables.qr_code stores the URL, QR is rendered on-the-fly

  **Must NOT do**: Do NOT modify existing cafe_tables schema (add later in Task 18)

  **Recommended Agent Profile**: `quick`
  **Parallelization**: Wave IV

  **Acceptance Criteria**:
  - [ ] `composer show linkxtr/laravel-qrcode` shows version
  - [ ] QR code generation test works

  **Commit**: `feat: add QR code library`

- [ ] 18. **Create CafeTableResource (CRUD)**

  **What to do**:
  - Create `php artisan make:filament-resource CafeTable`
  - Form: `table_number` (integer, unique), `is_available` (toggle)
  - Table columns: `table_number`, `is_available` (icon), `qr_code` (generated QR SVG)
  - Navigation group: "Data Master" or new "Cafe" group
  - Create new migration: CREATE `cafe_tables` with correct schema
  - Seeder: create 10 tables with sequential numbers

  **Must NOT do**: Do NOT modify existing cafe_tables schema if it already exists

  **Recommended Agent Profile**: `deep`
  **Parallelization**: Wave IV, **Blocked By**: Task 17

  **Acceptance Criteria**:
  - [ ] CafeTableResource in admin sidebar
  - [ ] Create/Edit/Delete tables works
  - [ ] QR code displays on table row

  **Commit**: `feat: cafe table crud resource`

- [ ] 19. **QR Code Generation Service + Display**

  **What to do**:
  - Create `app/Services/QrCodeService.php`:
    - `generateForTable(CafeTable $table): string` — returns SVG
    - URL: `route('customer.identitas', ['table' => $table->table_number])`
  - In CafeTableResource: use `ImageColumn` or custom column to render QR
  - Add download button: "Unduh QR" downloads PNG version
  - QR size: 200×200px

  **Must NOT do**: Do NOT store QR images on disk (render dynamically)

  **Recommended Agent Profile**: `deep`
  **Parallelization**: Wave IV, **Blocked By**: Task 18

  **Acceptance Criteria**:
  - [ ] QR code visible in cafe table view
  - [ ] Download QR works

  **Commit**: `feat: QR code generation for cafe tables`

- [ ] 20. **Run All QA Verification**

  **What to do**:
  - Run `php artisan migrate:fresh --seed` — verify no errors
  - Run `php artisan test` — verify tests pass
  - Run Playwright admin smoke tests: login, navigate key pages
  - Test FinancialReport: `curl http://localhost/admin/financial-report` → 200
  - Test all CRUD operations on CafeTableResource
  - Test user role: no customer option in form
  - Test slug auto-generate: create menu, verify slug filled

  **Must NOT do**: Do NOT skip any verification step

  **Recommended Agent Profile**: `unspecified-high` + `playwright`
  **Parallelization**: Wave IV, **Blocked By**: Tasks 1-19

  **Commit**: `test: qa verification for all refactored features`

- [ ] 21. **Final Cleanup + Playwright Admin Smoke Tests**

  **What to do**:
  - Run git diff to verify changes are intentional
  - Run `composer dump-autoload` to ensure no stale class references
  - Run Playwright admin smoke: `/admin/login`, `/admin`, `/admin/menus`, `/admin/cafe-tables`
  - Update `README.md` with new features list
  - Verify no stale references: `grep -r "Expense\|Income\|WasteRecord\|customer.*role" app/` → clean

  **Must NOT do**: Do NOT commit `.env` changes

  **Recommended Agent Profile**: `unspecified-high` + `playwright`
  **Parallelization**: Wave IV final

  **Commit**: `chore: final cleanup + admin smoke tests`

---

## Final Verification Wave

- [ ] F1. **Plan Compliance Audit** — `oracle`
- [ ] F2. **Code Quality Review** — `unspecified-high`
- [ ] F3. **Real Manual QA** — `unspecified-high` + `playwright`
- [ ] F4. **Scope Fidelity Check** — `deep`

---

## Commit Strategy

- **Wave I**: `fix: financial report, receivable customer, income cleanup`
- **Wave II**: `refactor: integer prices, remove customer role, waste records, cashback`
- **Wave III**: `refactor: remove expenses, sidebar, slug, category, labels, image`
- **Wave IV**: `feat: cafe table crud + qr codes`
