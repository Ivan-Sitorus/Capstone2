# Financial Report — Redesign + Full Implementation

## TL;DR

> **Quick Summary**: Redesign FinancialReport page with tab-switching UI (Generated Reports | Saved Templates), modal-based report creation, full report service implementation (fix Expense dependency), generated_reports table, and PDF/Excel export.

---

## Work Objectives

### Core Objective
Implement actual financial report generation (remove stub), create `generated_reports` table, redesign FinancialReport page with dual-tab UI and modal creation flow.

### Deliverables
- `generated_reports` table + Eloquent model
- FinancialReport page: two tabs (Generated Reports | Saved Templates)
- Modal form for creating new reports
- Full report generation using SimpleReportService, RigidReportService, CustomReportService
- Report view page with data tables
- PDF/Excel export action
- Fix Expense dependency in report services

---

## TODOs

- [x] 1. **Create generated_reports migration + model**

  **What to do**:
  - Create migration `create_generated_reports_table` with: id, user_id FK, name, type, date_start, date_end, aggregation, categories (json), result (json), timestamps
  - Create `GeneratedReport` Eloquent model with casts + relationships
  - Run migration

- [x] 2. **Fix report services — remove Expense dependency**

  **What to do**:
  - In SimpleReportService, RigidReportService, CustomReportService: replace `Expense::` calls with `UnexpectedTransaction::` or remove the expense section
  - `calcuateTotalExpense()` method currently uses `Expense::sum('amount')` — replace with `UnexpectedTransaction::where('jenis', 'expense')->sum('nominal')` or similar
  - Verify all service methods work without Expense model

- [x] 3. **Redesign FinancialReport page — tabs + table + modal**
- [x] 4. **Create Report View page**
- [x] 5. **Implement PDF/Excel export**

  **What to do**:
  - Use existing `barryvdh/laravel-dompdf` for PDF export
  - Use existing `maatwebsite/excel` for Excel export
  - Add download actions to the report table rows
  - Generated PDF/Excel based on report result data

- [x] 6. **Delete obsolete Expense import from services**

  **What to do**:
  - Remove all `use App\Models\Expense;` from report services
  - Replace with `UnexpectedTransaction` or appropriate alternative
  - Run `php artisan test` to ensure no breaking changes

- [x] 7. **Final QA verification** (Playwright skipped per request — verified via curl)

  **What to do**:
  - Test: generate a report → save → view → download PDF/Excel
  - Test: load template → generate from template → save
  - Test: delete report → confirm deletion
  - Verify no SQL errors or class-not-found errors
