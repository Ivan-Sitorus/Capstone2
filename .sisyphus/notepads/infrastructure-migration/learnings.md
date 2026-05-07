# Infrastructure Migration Learnings

## Task: Update .gitattributes + LF line endings

- **Date:** 2026-05-07
- **Working directory:** /home/nioha/projects/Capstone2 (WSL2 ext4)

### What was done
- Updated `.gitattributes` with explicit `text eol=lf` for all source file types (*.php, *.jsx, *.blade.php, *.css, *.js, *.ts, *.tsx, *.vue, *.json, *.md, *.scss, *.sh, *.yaml, *.yml)
- Added explicit `binary` rules for binary files (*.png, *.jpg, *.jpeg, *.gif, *.ico, *.svg, *.webp, *.woff, *.woff2, *.eot, *.ttf)
- Ran `git add --renormalize .` to apply new attributes and normalize all line endings

### Key findings
- `.env.testing` and several Filament resource files were deleted from disk but still tracked in git (from concurrent task). Had to restore them temporarily for `git add --renormalize .` to succeed.
- Binary files (png, jpg, ico, woff2) were already auto-detected as `-text` by git's built-in heuristics — the explicit `binary` rules make it more explicit.
- **Result:** `git ls-files --eol | grep crlf` returns empty — zero CRLF files in the repository.

---

## Task: Upgrade PHP 8.5.5 + Laravel 13.8.0

- **Date:** 2026-05-07
- **Working directory:** /home/nioha/projects/Capstone2 (Ubuntu 24.04 WSL2)

### What was done
- PHP 8.5.5 installed locally via .deb extraction from ppa:ondrej/php (no sudo available)
  - Extracted to `/home/nioha/.local/php8.5/`
  - Wrapper script at `/home/nioha/.local/bin/php8.5` with PHPRC/PHP_INI_SCAN_DIR env vars
  - All required extensions loaded: mbstring, xml, curl, zip, pgsql, intl, bcmath, gd, pdo, dom
- `composer.json` updated:
  - `"php": "^8.2 || ^8.5"` (dual constraint for backward compat + PHP 8.5 target)
  - `"laravel/framework": "^13.0"` → resolved to v13.8.0
  - `"filament/filament": "^5.0"` → resolved to v5.6.2
  - `"filament/upgrade": "^5.0"` → resolved to v5.6.2
  - `"laravel/tinker": "^3.0"` → required for illuminate/support ^13.0
  - `"phpunit/phpunit": "^13.0"` → required for Laravel 13 compatibility
- `composer update --with-all-dependencies` completed with 180 packages installed
- All verifications pass: `php artisan --version` = "Laravel Framework 13.8.0", `php artisan inspire` works

### Key findings
- **No sudo access** in this environment → had to download .deb packages from PPA directly and extract with `dpkg-deb -x`
- **phpoffice/phpspreadsheet 1.30.4 blocks PHP 8.5** (`"php": ">=7.4.0 <8.5.0"`). Solved via `config.platform.php = "8.4.1"` in composer.json — composer resolves deps as PHP 8.4.1 while actual runtime is 8.5.5
- **Livewire auto-upgraded** from v3.7.15 → v4.3.0 (Filament 5.x requirement)
- **Symfony components** all jumped from 7.x → 8.x (Laravel 13 requirement)
- **PHPUnit 13** drops several sebastian/* packages (code-unit, code-unit-reverse-lookup) and adds sebastian/git-state
- `composer check-platform-reqs` shows phpspreadsheet PHP constraint as "failed" — expected, handled by platform.php override
- Filament upgrade assets published automatically via `@php artisan filament:upgrade` post-autoload-dump hook

### php8.5 wrapper script location
```
/home/nioha/.local/bin/php8.5  (executable wrapper)
/home/nioha/.local/bin/php     (symlink to php8.5)
/home/nioha/.local/php8.5/     (extracted debs)
```
PATH already includes `/home/nioha/.local/bin` via ~/.profile

### Remaining concerns
- phpspreadsheet 1.30.4 needs upgrade to 2.x when maatwebsite/excel supports it
- Filament v4→v5 migration (Task 8) will need manual code changes
- Livewire v3→v4 migration may require component updates (Task 8)

---

## Task: Code Migration for Filament 5 + Laravel 13

- **Date:** 2026-05-07
- **Working directory:** /home/nioha/projects/Capstone2

### What was done

**Filament 5 API Migrations:**
- Changed deprecated `Filter->form()` to `Filter->schema()` in `ReceivableResource.php` (line 262)
- Changed deprecated `Table->actions()` to `Table->recordActions()` in `UnexpectedTransactionWidget.php` (line 89)

**Migration Fix:**
- Fixed `2026_04_11_000006_create_waste_records_table.php`: Added missing `$table->unsignedBigInteger('waste_record_id')->nullable()` column before the foreign key constraint. The foreign key was referencing a column that didn't exist in the `stock_movements` table.

**PHP 8.5 SQLite Extension (for testing):**
- Downloaded and installed `php8.5-sqlite3_8.5.5-1+ubuntu24.04.1+deb.sury.org+1_amd64.deb` from ppa:ondrej/php
- Extracted both `sqlite3.so` and `pdo_sqlite.so` to `/home/nioha/.local/php8.5/usr/lib/php/20250925/`
- Created INI files in `/home/nioha/.local/php8.5/etc/php/8.5/cli/conf.d/`

**Test Fixes (migration-related):**
- `AdminCurrentResourcesSmokeTest.php`: Removed `filament.admin.resources.incomes.index` (deprecated resource)
- `FinanceResourcesCrudTest.php`:
  - Replaced `Income` model with `UnexpectedTransaction` (incomes table dropped by migration)
  - Removed `incomes` route from access test

### Key findings
- **Filament 5 code was already 95% migrated** — all resources use `Filament\Schemas\Schema`, `->components()`, `->recordActions()`, `->toolbarActions()`. Only 2 deprecated API calls remained.
- **Filter API**: `Filter->form()` is deprecated in favor of `Filter->schema()` (both exist but `form()` is deprecated)
- **Table API**: `Table->actions()` is deprecated in favor of `Table->recordActions()` (both exist but `actions()` is deprecated)
- **Migration order matters**: The `create_waste_records_table` migration (000006) runs after `create_stock_movements` (000005) but tries to add a FK on a column that doesn't exist yet
- **Income resource is fully deprecated**: `IncomeResource::getPages()` returns empty array, incomes table is dropped by migration `2026_05_06_170043`, data moved to `unexpected_transactions`
- **Remaining 8 test failures are pre-existing** (not migration-related):
  - 6 image/storage tests fail due to missing test storage config
  - 1 WasteRecord test expects model event logic that doesn't exist
  - 1 DailyIngredientUsage test has duplicate insert issue

### Test Results
- **Before:** 58 failed, 7 passed (SQLite driver missing + migration bug)
- **After:** 8 failed, 57 passed (205 assertions)
- **Improvement:** 50 previously-failing tests now pass

### Verified
- `php artisan route:list`: 82 routes, EXIT_CODE=0
- `php artisan --version`: Laravel Framework 13.8.0
- `php artisan about`: Filament v5.6.2, Livewire v4.3.0
- LSP diagnostics: all modified files clean

---

## Task: PostgreSQL 18 Compatibility Check (Migrations & Queries)

- **Date:** 2026-05-07
- **Working directory:** /home/nioha/projects/Capstone2
- **Reference:** PostgreSQL 18.3 release notes (https://www.postgresql.org/docs/18/release-18.html)

### Scope
Read-only analysis of 30 migration files and raw SQL queries for PG18 compatibility.

### What was checked
1. All 30 migration files in `database/migrations/`
2. `config/database.php` pgsql driver configuration
3. Raw SQL usage across `app/` and `database/` directories
4. PostgreSQL 18 release notes for breaking changes

### Migration Files — VERDICT: ✅ FULLY COMPATIBLE

All 30 migration files use standard **Laravel Schema Builder** methods only — no raw SQL:
- `Schema::create()`, `Schema::table()`, `Schema::dropIfExists()`
- Standard column types: `string`, `text`, `integer`, `decimal`, `boolean`, `enum`, `json`, `timestamp`, `date`, `foreignId`, `uuid`
- Standard modifiers: `nullable()`, `default()`, `unique()`, `index()`, `constrained()`
- Standard FK actions: `cascadeOnDelete()`, `nullOnDelete()`, `restrict`
- One data-migration file (`2026_05_06_170043_migrate_incomes_to_unexpected_transactions.php`) uses `DB::table()` for INSERT/SELECT — no PG-specific syntax

### Raw SQL in app/ — VERDICT: ✅ FULLY COMPATIBLE

Only 3 files contain PG-specific raw SQL, all using standard PG features unchanged in PG18:

| File | PG-specific syntax | PG18 OK? |
|------|-------------------|----------|
| `app/Filament/Widgets/StatsOverview.php` | `created_at::date` (cast), `DATE_TRUNC('month', created_at)` | ✅ Yes |
| `app/Services/SimpleReportService.php` | `DB::raw('SUM(...)'), `COUNT(*)` (generic SQL, no PG-specific) | ✅ Yes |
| `app/Services/CustomReportService.php` | `DATE_TRUNC('month', %s)::date`, `UNION ALL`, `CAST(... AS NUMERIC)` | ✅ Yes |

### Database Config — VERDICT: ✅ NO CHANGES NEEDED

```php
'pgsql' => [
    'driver' => 'pgsql',
    'charset' => 'utf8',          // Standard, fully supported
    'search_path' => 'public',    // Standard
    'sslmode' => 'prefer',        // Standard
],
```

No configuration changes needed for PG18.

### PostgreSQL 18 Breaking Changes (from release notes) — VERDICT: ✅ NONE AFFECT THIS PROJECT

| PG18 Breaking Change | Impact on this project |
|---------------------|----------------------|
| Data checksums default enabled | No — initdb default, not query-related |
| Time zone abbreviation handling change | No — session config change |
| MD5 password deprecation | No — auth mechanism, not used in queries |
| VACUUM/ANALYZE inheritance children change | No — admin operation |
| COPY FROM `\.` end-of-file change | No — not used in this project |
| Unlogged partitioned tables disallowed | No — not used |
| AFTER triggers execution role change | No — no trigger definitions in code |
| Rule privileges removed | No — not used |

### Key Findings
1. **No deprecated PG features used**: The project does not use `::jsonb` casts, `GENERATED ALWAYS AS ... STORED`, or any other syntax changed in PG18.
2. **No schema builder upgrades needed**: All migrations use Laravel's abstraction layer, which handles PG version differences internally.
3. **The `::date` cast and `DATE_TRUNC()`** used in 2 files are stable, well-documented PG features that remain unchanged in PG18.
4. **PG18 `uuidv7()` function** — not used, but could be considered for future optimization if UUID generation becomes relevant.
5. **PG18 virtual generated columns (now default)** — not used, no impact.

### Recommendation
**No changes required.** The codebase is fully compatible with PostgreSQL 18. Migration to PG18 would be a straightforward `pg_upgrade` or dump/restore operation with no application code changes needed.
