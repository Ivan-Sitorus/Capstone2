## FinancialReport Redesign — Key Learnings

### Filament v3.1.2 Architecture Notes
- Page class uses `InteractsWithHeaderActions` trait for header action buttons
- Actions can have `modal()` + `schema()` for modal forms
- Custom pages with route parameters override `routes()` to register additional routes
- Must match parent `routes(Panel, ?PageConfiguration)` signature exactly
- SPA mode requires `spaUrlExceptions` for non-Livewire routes (downloads)
- `getUrl()` passes array to `route()`, so parameters must match route definition

### Schema vs Forms Components
- `Filament\Schemas\Components` (from filament/schemas) is the new API
- `Filament\Forms\Components` (from filament/forms) still works for backward compat
- Both can be used in Action `schema()` and Page `form()`

### Route Patterns for Custom Pages
- `$slug = 'view-report/{id}'` creates `/admin/view-report/{id}`
- Override `getRelativeRouteName()` to control route name
- Additional routes (downloads) registered via `routes()` using closures
- Cannot use `ClassName@method` syntax with Livewire components

### Template Load Redesign
- Old `loadTemplate` filled a form that no longer exists on main page
- Changed to directly generate report from template config → cleaner UX
- Template save moved to ViewReport header action → better workflow

## F4 Scope Fidelity Check Results (2026-05-10)

### Per-Task Compliance

| Task | Status | Issues |
|------|--------|--------|
| T1 (Cleanup) | ✅ COMPLIANT | Dead files deleted, ReportExport kept, no refs remain |
| T2 (Tailwind v4) | ✅ COMPLIANT | vite.config.js + AdminPanelProvider + theme.css all correct |
| T3 (DTO) | ✅ COMPLIANT | ReportData, ReportRow, SummaryItem all present, fromArray + fromGeneratedReport |
| T4 (Formatter) | ✅ COMPLIANT | All methods present, no business logic |
| T5 (Service) | ✅ COMPLIANT | Unified FinancialReportService, old services @deprecated, GeneratedReport->toReportData() |
| T6 (FilamentRenderer) | ✅ COMPLIANT | No sort/search/filter, recordClasses, indent, dark mode |
| T7 (DomPdfRenderer) | ✅ COMPLIANT | generate/download/raw, print CSS via style tag, no Browsershot |
| T8 (ExcelRenderer) | ⚠️ PARTIAL | ReportExport.php still exists (dead code, not deleted per spec) |
| T9 (CsvRenderer) | ✅ COMPLIANT | UTF-8 BOM, plain CSV, download() |
| T10 (CSS + Dark) | ✅ COMPLIANT | financial-table.css registered, dark: variants, compact styling |
| T11 (FinancialReport) | ❌ VIOLATION | loadTemplate() still calls OLD services (Simple/Rigid/Custom) instead of unified FinancialReportService |
| T12 (ViewReport) | ✅ COMPLIANT | Uses FilamentTableRenderer::configure(), all 3 renderers, routes maintained |
| T13 (Print Template) | ✅ COMPLIANT | Letterhead, repeating headers, page breaks, no flexbox/grid, no Tailwind, no Google Fonts |

### Must NOT Do — All Clean (17/17)
- No sorting/searching/filtering on report tables → VERIFIED (FilamentTableRenderer: searchable(false), filters([]), defaultSort(null))
- No inline editing → VERIFIED
- No custom Blade HTML for report body → VERIFIED (uses {{ $this->table }})
- No new report types → VERIFIED (simple/rigid/custom only)
- No breaking JSON structure changes → VERIFIED (fromGeneratedReport backward compat)
- No Browsershot/Chromium → VERIFIED (grep returns nothing)
- No external PDF services → VERIFIED (DomPDF only)
- Dead code deleted → VERIFIED (FinancialReportExport, FinancialReportPdf gone)
- Old services deprecated, not deleted → VERIFIED (@deprecated tags present)
- DTO plain PHP → VERIFIED (no Spatie Data)
- No hardcoded formats → VERIFIED (uses AccountingFormatter)
- No flexbox/grid in print CSS → VERIFIED
- No Google Fonts CDN in print → VERIFIED
- No position fixed/sticky in print → VERIFIED
- No Tailwind classes in print Blade → VERIFIED
- Route structure unchanged → VERIFIED (SPA exceptions preserved)

### Unaccounted Files (3)
1. `app/Models/Expense.php` — NOT in any plan task
2. `database/migrations/2026_05_10_060713_create_expenses_table.php` — NOT in any plan task
3. `database/migrations/2026_05_10_060510_add_invoice_date_to_receivables_table.php` — NOT in any plan task

### Violations
1. **Task 11**: FinancialReport.php@loadTemplate() line 49-53 calls `app(SimpleReportService::class)`, `app(RigidReportService::class)`, `app(CustomReportService::class)` instead of using `FinancialReportService::generate()`. Plan spec states "Generate report action tetap menggunakan FinancialReportService yang baru".
2. **Task 8**: `app/Exports/ReportExport.php` still exists as dead code. Plan spec says "Hapus app/Exports/ReportExport.php jika sudah tidak dipakai".
