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
