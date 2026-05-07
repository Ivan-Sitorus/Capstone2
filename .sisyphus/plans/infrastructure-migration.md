# Infrastructure Migration & Automation — WSL2 + Docker + MCP + QA

## TL;DR

> **Quick Summary**: Migrate POS project from Windows/Laragon to WSL2 Ubuntu 24.04, upgrade seluruh stack ke versi LTS terbaru (Laravel 13, PHP 8.5, Filament 5, React 19.2, PostgreSQL 18, Python 3.14), Dockerize dengan Laravel Sail, install 4 MCP servers, automate QA ~36 halaman via Playwright (12 cashier/customer + 24 admin panel), dan siapkan deployment CLI.
>
> **Deliverables**:
> - WSL2 Ubuntu 24.04 dev environment
> - Upgrade: Laravel 13.8 + PHP 8.5.5 + Filament 5.6 + React 19.2 + PostgreSQL 18.3 + Python 3.14.4
> - Laravel Sail Docker Compose (PHP 8.5, PostgreSQL 18, Reverb, FastAPI)
> - 4 MCP servers configured (PostgreSQL, Laravel, Playwright, Artisan)
> - Playwright QA scripts untuk ~36 halaman (8 kasir + 4 customer + 24 admin panel)
> - Vercel CLI, TiDB CLI, Cloudinary CLI terinstall
>
> **Estimated Effort**: Extra Large
> **Parallel Execution**: YES — 7 waves
> **Critical Path**: WSL2 migration → Stack upgrade → Docker Compose → MCP → Playwright QA

---

## Context

### Original Request
User wants to migrate the POS project from Windows Laragon to WSL2, upgrade all stack to latest LTS versions for sustainability, Dockerize it, install MCP servers for LLM-controlled development, automate QA via browser testing for ALL pages (cashier + customer + admin panel), and install deployment CLIs.

### Interview Summary
**Key Discussions**:
- **All latest LTS versions**: Laravel 13.8, PHP 8.5.5, Filament 5.6, React 19.2, PostgreSQL 18.3, Python 3.14.4
- **Docker**: Laravel Sail
- **Database**: PostgreSQL 18.3 for development, TiDB untuk production nanti
- **QA**: EVERYTHING — 12 halaman cashier/customer + 24 halaman admin panel (Filament 5), termasuk modals, buttons, all interactions
- **WSL**: Ubuntu 24.04
- **MCP**: PostgreSQL, Laravel, Playwright, Artisan

**Version Reality Check** (ALL verified on web, May 2026):
- Laravel 13.8.0 — Released Mar 17, 2026. Min PHP 8.3. Supports PHP 8.5.
- PHP 8.5.5 — Released Apr 9, 2026. Active support until Dec 2027, security until Dec 2029.
- Python 3.14.4 — Released Apr 7, 2026. Bugfix release.
- React 19.2.5 (npm latest) / 19.2.6 (GitHub, May 6, 2026).
- Filament 5.6.2 — Released May 2, 2026. Active feature development.
- PostgreSQL 18.3 — Released Feb 26, 2026.

** ⚠️ BREAKING CHANGES**: Laravel 12→13 + Filament 4→5 = perlu code migration. PHP 8.5 new features + deprecations.

**Research Findings**:
- Project Laravel 12, `laravel/sail` sudah di `composer.json` require-dev
- Laravel Reverb (WebSocket) perlu port 8080
- FastAPI TIDAK punya `requirements.txt` — blocking Dockerization, harus dibuat dulu
- WSL2 CRITICAL: project HARUS di Linux ext4 (~/projects), BUKAN /mnt/c
- PostgreSQL port collision risk dengan existing Laragon (5432)
- Vite HMR butuh `usePolling: true` di WSL2
- CRLF→LF line ending issue saat pindah Windows→WSL
- PHPUnit tests pakai SQLite :memory:, aman di Docker
- Filament 4.0 admin panel ADA tapi OUT of scope QA

### Metis Review
**Identified Gaps** (addressed):
- **requirements.txt missing**: Dibuat dari parsing `api.py` imports → Task 6
- **PHP version**: Sail default PHP 8.4, project butuh PHP 8.2 → override di compose → Task 9
- **CRLF issue**: `.gitattributes` + `git add --renormalize .` → Task 2
- **Port collision**: `FORWARD_DB_PORT=5433` default → Task 9
- **Filament OUT of QA scope**: Explicit exclusion → Guardrails
- **Two strict phases**: Phase 1 (Docker+MCP+QA) dulu, Phase 2 (Vercel+TiDB+Cloudinary) setelah stabil → Execution Strategy
- **Rollback validation**: Stop Docker → verify Laragon still works → Task IV-F

---

## Work Objectives

### Core Objective
Meng-upgrade seluruh tech stack ke versi LTS terbaru 2026, membangun Docker environment identik untuk seluruh tim, mengaktifkan LLM-controlled development via MCP, dan mengotomatisasi QA browser testing untuk SEMUA halaman (36 halaman: cashier + customer + admin panel).

### Concrete Deliverables
- `~/projects/Capstone2/` — project di WSL2 native ext4, upgraded ke Laravel 13 + Filament 5
- `docker-compose.yml` — Laravel Sail dengan PHP 8.5, PostgreSQL 18, Redis, Reverb, FastAPI
- `.dockerignore`, `.env.sail`, `datamining/requirements.txt`
- `docker/python/Dockerfile` — FastAPI Python 3.14
- `tests/playwright/*.spec.ts` — ~36 file QA scenario (12 frontend + 24 admin panel)
- `~/.config/opencode/opencode.json` — 4 MCP server configs
- Upgrade documentation: migration notes dari Laravel 12→13, Filament 4→5

### Definition of Done
- [ ] `docker compose up -d` → semua service healthy dalam 30 detik
- [ ] `docker compose exec app php artisan migrate:fresh --seed` → sukses (dengan schema Filament 5 + Laravel 13)
- [ ] Semua PHPUnit tests PASS (di-update untuk compatibility Laravel 13)
- [ ] `curl http://localhost:8001/health` → `{"status":"ok"}`
- [ ] Playwright QA: 36 halaman PASS, ~200 screenshots captured, console errors tercapture
- [ ] MCP servers terdeteksi dan berfungsi via OpenCode

### Must Have
- PostgreSQL 18.3 di Docker
- PHP 8.5.5 di Docker (Sail custom runtime)
- Laravel 13.8 + Filament 5.6.2 berjalan penuh
- Python 3.14.4 untuk FastAPI
- React 19.2.5 di frontend
- QA mencakup SEMUA halaman admin panel (modals, buttons, form submissions, table interactions)
- Laravel Reverb (WebSocket) jalan
- `.dockerignore`, `.env.sail`, `.gitattributes`

### Must NOT Have (Guardrails)
- **MUST NOT** skip Filament admin panel QA — semua Resources, Pages, Widgets harus di-test
- **MUST NOT** deploy ke Vercel sebelum Docker + upgrade + QA stabil
- **MUST NOT** hapus/migrasi data Laragon PostgreSQL existing
- **MUST NOT** commit `.env.sail`, `.env.tidb`, MCP secrets ke git
- **MUST NOT** skip rollback validation
- **MUST NOT** skip upgrade docs (Laravel 12→13, Filament 4→5 migration notes)

---

## Verification Strategy

> **ZERO HUMAN INTERVENTION** — ALL verification is agent-executed. No exceptions.

### Test Decision
- **Infrastructure exists**: YES (25 PHPUnit tests)
- **Automated tests**: Tests-after (PHPUnit existing tests verify Docker env; Playwright untuk QA)
- **Framework**: PHPUnit 11.5 (existing) + Playwright (new)
- **Baseline**: 25 PHPUnit tests must PASS di Docker container

### QA Policy
Every implementation task includes Agent-Executed QA Scenarios:
- **Frontend/UI**: Playwright — navigate, interact, assert DOM, screenshot, capture console errors
- **API/Backend**: Bash (curl) — send requests, assert status + response fields
- **Docker/Infra**: Bash — docker compose ps, health checks, logs
- Evidence saved to `.sisyphus/evidence/task-{N}-{scenario-slug}.{ext}`

---

## Execution Strategy

### ⚠️ DUA FASE KETAT — JANGAN CAMPUR

```
PHASE 1 (Wave I-VI): Stack Upgrade + Docker + MCP + ALL QA (36 halaman)
  ↓ ALL VERIFIED & STABLE ↓
PHASE 2 (Wave VII): Deployment CLI + Vercel/TiDB/Cloudinary Prep
```

**PHASE 2 hanya dimulai setelah PHASE 1 verified: Docker healthy, 25 PHPUnit PASS, upgrade stack sukses, Playwright QA 36 halaman PASS (including Filament admin).**

### Parallel Execution Waves

```
PHASE 1 — Wave I (Start Immediately — WSL2 + prerequisites):
├── Task 1: WSL2 environment setup + project copy [quick]
├── Task 2: Git line-ending fix + .gitattributes [quick]
├── Task 3: .dockerignore creation [quick]
├── Task 4: .env.sail — Docker-specific env [quick]
└── Task 5: Generate datamining/requirements.txt [quick]

Wave II (After Wave I — Stack Upgrade):
├── Task 6: PHP 8.5.5 upgrade + composer.json update [deep]
├── Task 7: Laravel 12 → 13 upgrade [deep]
├── Task 8: Filament 4 → 5 upgrade [deep]
├── Task 9: React 19.2.5 upgrade + Vite config update [quick]
└── Task 10: PostgreSQL 18.3 migration (schema compatibility check) [unspecified-high]

Wave III (After Wave II — Laravel Sail core):
├── Task 11: php artisan sail:install --with=pgsql,redis [quick]
├── Task 12: Customize Sail for PHP 8.5 + PostgreSQL 18 [deep]
├── Task 13: docker-compose.yml — add Reverb, FastAPI (Python 3.14), queue [deep]
├── Task 14: WSL2 Vite HMR config [quick]
└── Task 15: Docker environment smoke test [quick]

Wave IV (After Wave III — MCP servers, MAX PARALLEL):
├── Task 16: PostgreSQL MCP server setup [quick]
├── Task 17: Laravel MCP server setup [deep]
├── Task 18: Laravel Artisan MCP setup [quick]
├── Task 19: Playwright MCP setup + browser install [unspecified-high]
└── Task 20: OpenCode MCP config integration [quick]

PHASE 1 — Wave V (After Wave IV — Cashier + Customer QA, MAX PARALLEL):
├── Task 21: Playwright QA setup + auth state management [unspecified-high]
├── Task 22-33: 12 halaman QA (K1-K8 + C1-C4) [unspecified-high × 12]
└── Task 34: Full order flow E2E integration [unspecified-high]

Wave VI (After Wave V — Filament Admin QA, MAX PARALLEL):
├── Task 35-46: QA 12 Filament Resources (User, Menu, Order, Category, dll) [unspecified-high × 12]
├── Task 47-54: QA 8 Filament Pages (CashFlow, FinancialReport, DataMining, dll) [unspecified-high × 8]
├── Task 55-58: QA 4 Filament Widgets [unspecified-high × 4]
└── Task 59: Filament QA — modals, buttons, form validation [unspecified-high]

--- PHASE 1 MUST BE VERIFIED BEFORE PHASE 2 ---

PHASE 2 — Wave VII (After Phase 1 verified — Deployment CLIs + prep):
├── Task 60: Vercel CLI install + auth [quick]
├── Task 61: TiDB CLI install + auth [quick]
├── Task 62: Cloudinary CLI install + auth [quick]
├── Task 63: vercel.json for PHP runtime [deep]
├── Task 64: TiDB database provisioning [unspecified-high]
└── Task 65: Cloudinary upload script [quick]

Wave FINAL (After ALL tasks — 4 parallel reviews):
├── Task F1: Plan Compliance Audit (oracle)
├── Task F2: Code Quality Review (unspecified-high)
├── Task F3: Real Manual QA (unspecified-high + playwright)
└── Task F4: Scope Fidelity Check (deep)
```

### Phase 2 Gate Check (BEFORE Wave V begins)

```
□ docker compose ps → semua service healthy
□ docker compose exec app php artisan test → 25/25 PASS
□ curl localhost:8001/health → {"status":"ok"}
□ curl localhost:8080 → WebSocket response (HTTP 426/400, NOT connection refused)
□ Playwright QA reports → 12 pages PASS, screenshots captured
□ Rollback test: stop Docker → Laragon still serves app
```

### Critical Path
```
Task 1 (WSL2 env) → Task 7 (sail:install) → Task 8 (customize) → Task 11 (smoke test)
  → Task 12-16 (MCP) → Task 17-30 (Playwright QA) → Gate Check → Task 31-33 (CLIs)
  → Task 34-36 (deploy prep) → F1-F4 (reviews)
```

### Agent Dispatch Summary
- **Wave I**: 6 — T1-T6 → `quick`
- **Wave II**: 5 — T7 → `quick`, T8 → `deep`, T9 → `deep`, T10-T11 → `quick`
- **Wave III**: 5 — T12 → `quick`, T13 → `deep`, T14 → `quick`, T15 → `unspecified-high`, T16 → `quick`
- **Wave IV**: 14 — T17-T30 → `unspecified-high` (with `playwright` skill)
- **Wave V**: 3 — T31-T33 → `quick`
- **Wave VI**: 3 — T34 → `deep`, T35 → `unspecified-high`, T36 → `quick`
- **FINAL**: 4 — F1 → `oracle`, F2 → `unspecified-high`, F3 → `unspecified-high`+`playwright`, F4 → `deep`

---

## TODOs

- [x] 1. **WSL2 Environment Setup + Project Copy**

  **What to do**:
  - Verifikasi WSL2 Ubuntu 24.04 sudah terinstall (`wsl -l -v`)
  - Konfigurasi `.wslconfig` di Windows: memory=10GB, processors=4
  - Konfigurasi `/etc/wsl.conf` di WSL: `[automount] options="metadata"`, `[user] default=<username>`
  - Copy project dari `/mnt/c/laragon/www/point-of-sale/Capstone2` ke `~/projects/Capstone2`
  - ⚠️ CRITICAL: COPY (cp -r), jangan MOVE. Original di Windows tetap ada untuk rollback.
  - Jalankan `wsl --shutdown` lalu restart WSL untuk apply config

  **Must NOT do**:
  - JANGAN hapus project original di Windows
  - JANGAN `mv` — harus `cp -r`

  **Recommended Agent Profile**:
  - **Category**: `quick`
  - **Skills**: `[]`
  - **Reason**: File copy + config edit, no complex logic

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave I (with Tasks 2-6)
  - **Blocks**: Task 7
  - **Blocked By**: None

  **References**:
  - WSL config pattern: `docs/guide/installation.md` (from oh-my-openagent research)
  - Project root: `/mnt/c/laragon/www/point-of-sale/Capstone2/`

  **Acceptance Criteria**:
  - [ ] `~/projects/Capstone2/` exists with full project contents
  - [ ] `cat /etc/wsl.conf | grep metadata` → shows `options = "metadata"`
  - [ ] `wsl -l -v` (from PowerShell) shows Ubuntu-24.04 running v2
  - [ ] Original Windows project still untouched at `/mnt/c/laragon/www/point-of-sale/Capstone2`

  **QA Scenarios**:
  ```
  Scenario: Verify WSL2 environment is ready
    Tool: Bash
    Preconditions: WSL2 Ubuntu 24.04 running
    Steps:
      1. ls ~/projects/Capstone2/composer.json → file exists
      2. ls ~/projects/Capstone2/package.json → file exists
      3. cat /etc/wsl.conf → contains metadata option
      4. df -T ~/projects/ → shows ext4 filesystem type (NOT drvfs/9p)
    Expected Result: All checks pass. Project on ext4 filesystem, not on /mnt/c.
    Evidence: .sisyphus/evidence/task-1-wsl2-ready.txt
  ```

  **Commit**: YES (groups with Wave I)
  - Message: `infra: wsl2 env setup — metadata config + project copy to ext4`
  - Files: `/etc/wsl.conf`, `~/projects/Capstone2/`

- [x] 2. **Git Line-Ending Fix + .gitattributes**

  **What to do**:
  - Buat `.gitattributes` dengan `* text=auto`
  - Spesifik: `*.php text eol=lf`, `*.jsx text eol=lf`, `*.css text eol=lf`, `*.json text eol=lf`
  - Jalankan `git add --renormalize .` untuk convert CRLF→LF
  - Verifikasi tidak ada file dengan CRLF: `git ls-files --eol | grep crlf` → harus kosong
  - Commit perubahan ini

  **Must NOT do**:
  - JANGAN skip file binary (gambar, font) — harus didefine sebagai binary di `.gitattributes`

  **Recommended Agent Profile**:
  - **Category**: `quick`
  - **Skills**: `[]`
  - **Reason**: Config file + git renormalize, straightforward

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave I (with Tasks 1,3-6)
  - **Blocks**: None (independent)
  - **Blocked By**: Task 1 (need project on WSL)

  **References**:
  - `.gitattributes` pattern: project root
  - Git renormalize: `git add --renormalize .`

  **Acceptance Criteria**:
  - [ ] `.gitattributes` file exists dengan `* text=auto`
  - [ ] `git ls-files --eol | grep crlf` → empty
  - [ ] `.editorconfig` already exists with `end_of_line = lf` — verify

  **QA Scenarios**:
  ```
  Scenario: No CRLF line endings remain
    Tool: Bash
    Preconditions: Project copied to WSL2
    Steps:
      1. git add --renormalize .
      2. git diff --cached --name-only → shows renormalized files (if any)
      3. git ls-files --eol | grep crlf → empty output
    Expected Result: No crlf in tracked files. All LF.
    Evidence: .sisyphus/evidence/task-2-eol-check.txt
  ```

  **Commit**: YES (groups with Wave I)
  - Message: `infra: add .gitattributes + renormalize line endings to LF`
  - Files: `.gitattributes`

- [x] 3. **.dockerignore Creation**

  **What to do**:
  - Buat `.dockerignore` di project root
  - Exclude: `vendor/`, `node_modules/`, `.env*`, `.git/`, `storage/logs/*`, `storage/framework/cache/*`, `storage/framework/sessions/*`, `storage/framework/views/*`, `tests/`, `phpunit.xml`, `*.md`, `k6/`, `.opencode/`, `.editorconfig`
  - Exclude Docker files sendiri: `Dockerfile`, `docker-compose*.yml`, `.dockerignore`
  - Include: `!docker/nginx/`, `!docker/python/`

  **Must NOT do**:
  - JANGAN exclude `storage/app/` (bisa berisi uploaded images)
  - JANGAN exclude `public/build/` — perlu di-copy dari build stage

  **Recommended Agent Profile**:
  - **Category**: `quick`
  - **Skills**: `[]`
  - **Reason**: Single config file, straightforward pattern matching

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave I (with Tasks 1-2,4-6)
  - **Blocks**: Task 7 (Docker build context)
  - **Blocked By**: None

  **References**:
  - Docker docs production guide: aggressive `.dockerignore` pattern
  - `composer.json` for vendor location
  - `vite.config.js` for build output location

  **Acceptance Criteria**:
  - [ ] `.dockerignore` exists di project root
  - [ ] `ls -la vendor/ | wc -l` > 1 (vendor still exists locally)
  - [ ] `grep "vendor/" .dockerignore` → match found

  **QA Scenarios**:
  ```
  Scenario: Docker build context is minimal
    Tool: Bash
    Preconditions: .dockerignore created
    Steps:
      1. docker build -t test-context -f - . <<< 'FROM alpine' 2>&1 | grep "sending build context"
      2. Check reported size is < 50MB (should exclude vendor, node_modules, .git)
    Expected Result: Context size < 50MB. Without .dockerignore it would be 300MB+.
    Evidence: .sisyphus/evidence/task-3-build-context.txt
  ```

  **Commit**: YES (groups with Wave I)
  - Message: `infra: add .dockerignore`
  - Files: `.dockerignore`

- [x] 4. **.env.sail — Docker-Specific Environment**

  **What to do**:
  - Buat `.env.sail` (Docker-specific env, TIDAK mengganti `.env`)
  - Key overrides:
    - `DB_HOST=pgsql` (service name, BUKAN 127.0.0.1)
    - `DB_PASSWORD=password` (Sail default untuk postgres)
    - `DB_USERNAME=sail` (Sail default)
    - `REDIS_HOST=redis`
    - `REVERB_HOST=0.0.0.0`
    - `APP_URL=http://localhost`
    - `SESSION_DRIVER=database` (jangan file — Docker restart = hilang)
    - `FORWARD_DB_PORT=5433` (hindari collision dengan Laragon 5432)
    - `WWWUSER=1000`, `WWWGROUP=1000`
  - Copy dari `.env` lalu override values
  - Tambah `.env.sail` ke `.gitignore`

  **Must NOT do**:
  - JANGAN modifikasi `.env` original
  - JANGAN commit `.env.sail` — tambahkan ke `.gitignore`

  **Recommended Agent Profile**:
  - **Category**: `quick`
  - **Skills**: `[]`
  - **Reason**: Config file creation, straightforward

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave I (with Tasks 1-3,5-6)
  - **Blocks**: Task 7
  - **Blocked By**: Task 1 (need project)

  **References**:
  - Existing `.env`: `DB_HOST=127.0.0.1`, `DB_CONNECTION=pgsql`
  - Sail docs: `DB_HOST` must match service name
  - Sail default PostgreSQL credentials: user=sail, password=password

  **Acceptance Criteria**:
  - [ ] `.env.sail` exists dengan `DB_HOST=pgsql`
  - [ ] `.env.sail` punya `FORWARD_DB_PORT=5433`
  - [ ] `.env.sail` punya `SESSION_DRIVER=database`
  - [ ] `.gitignore` contains `.env.sail`
  - [ ] `.env` original UNCHANGED

  **QA Scenarios**:
  ```
  Scenario: .env.sail has Docker-compatible values
    Tool: Bash
    Preconditions: .env.sail created
    Steps:
      1. grep DB_HOST .env.sail → shows "DB_HOST=pgsql"
      2. grep DB_HOST .env → shows "DB_HOST=127.0.0.1" (original unchanged)
      3. grep SESSION_DRIVER .env.sail → shows "database"
      4. grep ".env.sail" .gitignore → found (not committed)
    Expected Result: .env.sail has Docker values, .env unchanged
    Evidence: .sisyphus/evidence/task-4-env-sail.txt
  ```

  **Commit**: YES (groups with Wave I)
  - Message: `infra: add .env.sail with Docker-specific config`
  - Files: `.env.sail`, `.gitignore`

- [x] 5. **Generate datamining/requirements.txt from api.py**

  **What to do**:
  - Baca `datamining/api.py` — parse semua `import` statements
  - Identifikasi external dependencies: `fastapi`, `uvicorn`, `psycopg2-binary`, `scikit-learn`, `pandas`, `matplotlib`, `seaborn`, `prophet`, `python-dotenv`, `numpy`
  - Versi: cek di pip atau asumsikan latest stable
  - Buat `datamining/requirements.txt`:
    ```
    fastapi>=0.115.0
    uvicorn>=0.34.0
    psycopg2-binary>=2.9.10
    scikit-learn>=1.6.0
    pandas>=2.2.0
    matplotlib>=3.10.0
    seaborn>=0.13.0
    prophet>=1.1.0
    python-dotenv>=1.0.0
    numpy>=2.2.0
    ```
  - Verifikasi dengan `pip install --dry-run -r datamining/requirements.txt`

  **Must NOT do**:
  - JANGAN hardcode versi exact (gunakan `>=`)
  - JANGAN include built-in modules (os, json, typing, datetime, abc)

  **Recommended Agent Profile**:
  - **Category**: `quick`
  - **Skills**: `[]`
  - **Reason**: Read imports → generate requirements, straightforward

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave I (with Tasks 1-4,6)
  - **Blocks**: Task 6 (FastAPI Dockerfile)
  - **Blocked By**: Task 1 (need project files)

  **References**:
  - `datamining/api.py` — imports at top of file (fastapi, uvicorn, psycopg2, sklearn, pandas, matplotlib, seaborn, prophet, dotenv, numpy)
  - `datamining/__init__.py` — additional imports
  - `datamining/association.py`, `prediction.py`, `bahanbaku.py`, `prediksibaku.py` — more imports

  **Acceptance Criteria**:
  - [ ] `datamining/requirements.txt` exists
  - [ ] Contains all 10+ external packages
  - [ ] Prophet included (used in prediction.py)
  - [ ] psycopg2-binary included (used in api.py for PostgreSQL connection)

  **QA Scenarios**:
  ```
  Scenario: requirements.txt covers all imports
    Tool: Bash
    Preconditions: requirements.txt created
    Steps:
      1. grep "^fastapi" datamining/requirements.txt → match
      2. grep "^psycopg2" datamining/requirements.txt → match
      3. grep "^prophet" datamining/requirements.txt → match
      4. grep "^scikit-learn" datamining/requirements.txt → match
      5. wc -l datamining/requirements.txt → >= 10 lines
    Expected Result: All major imports represented in requirements.txt
    Evidence: .sisyphus/evidence/task-5-requirements.txt
  ```

  **Commit**: YES (groups with Wave I)
  - Message: `infra: generate datamining/requirements.txt from api.py imports`
  - Files: `datamining/requirements.txt`

- [x] 6. **PHP 8.5.5 Upgrade + composer.json Update**

  **What to do**:
  - Update `composer.json`: `"php": "^8.5"`, `"laravel/framework": "^13.0"`
  - Cek semua dependencies untuk PHP 8.5 compatibility: `composer check-platform-reqs`
  - Update composer dependencies: `composer update`
  - Jika ada conflict, resolve pakai `composer update --with-all-dependencies`
  - Verifikasi: `php artisan --version` → Laravel 13.x
  - Catat semua breaking changes dari PHP 8.5 (deprecated features, removed extensions)
  - Update Sail runtime reference ke PHP 8.5

  **Must NOT do**:
  - JANGAN commit sebelum semua test pass

  **Recommended Agent Profile**:
  - **Category**: `deep`
  - **Skills**: `[]`
  - **Reason**: PHP major upgrade + dependency resolution, breaking changes possible

  **Parallelization**:
  - **Can Run In Parallel**: NO (sequential — blocks Laravel + Filament upgrade)
  - **Parallel Group**: Wave II (with Tasks 7-10)
  - **Blocks**: Task 7, Task 8
  - **Blocked By**: Task 1

  **References**:
  - `composer.json` — current `"php": "^8.2"`, `"laravel/framework": "^12.0"`
  - PHP 8.5 migration guide: https://php.net/manual/en/migration85.php
  - PHP 8.5 changelog: https://php.net/ChangeLog-8.php

  **Acceptance Criteria**:
  - [ ] `composer.json` shows `"php": "^8.5"`
  - [ ] `composer.json` shows `"laravel/framework": "^13.0"`
  - [ ] `composer update` completes without errors
  - [ ] `php artisan --version` → Laravel Framework 13.x

  **QA Scenarios**:
  ```
  Scenario: PHP 8.5 + Laravel 13 compatibility
    Tool: Bash
    Preconditions: composer.json updated
    Steps:
      1. composer validate → no errors
      2. composer check-platform-reqs → no missing extensions
      3. php artisan --version → "Laravel Framework 13.x"
      4. php artisan inspire → works without errors
    Expected Result: Dependencies resolve, Laravel 13 boots
    Negative: Platform reqs missing → install missing PHP extensions
    Evidence: .sisyphus/evidence/task-6-php85-upgrade.txt
  ```

  **Commit**: YES (groups with Wave II)
  - Message: `upgrade: php 8.5 + laravel 13 — composer dependency update`
  - Files: `composer.json`, `composer.lock`

- [x] 7. **Laravel 12 → 13 Code Migration**

  **What to do**:
  - Baca official Laravel 13 upgrade guide: https://laravel.com/docs/13.x/upgrade
  - Breaking changes utama Laravel 13:
    - Min PHP 8.3 (sudah resolved via Task 6 — kita di 8.5)
    - AI primitives built-in (Laravel AI)
    - JSON:API resources
    - Perubahan di queue, cache, security
    - Semantic/vector search capabilities
  - Cek semua file project untuk deprecated method/class usage
  - Update config files: `config/*.php` jika ada perubahan format
  - Update `app/Exceptions/Handler.php` jika format berubah
  - Update middleware jika ada signature changes
  - Jalankan: `php artisan migrate` (schema check — apakah ada migration yang perlu di-update)
  - Jalankan: `php artisan test` — semua 25 tests harus tetap PASS

  **Must NOT do**:
  - JANGAN skip test run — regression testing wajib

  **Recommended Agent Profile**:
  - **Category**: `deep`
  - **Skills**: `[]`
  - **Reason**: Major framework upgrade, code migration, potential breaking changes across entire codebase

  **Parallelization**:
  - **Can Run In Parallel**: NO (sequential — depends on Task 6)
  - **Parallel Group**: Wave II
  - **Blocks**: Task 8, Task 11
  - **Blocked By**: Task 6

  **References**:
  - `config/` — semua config files
  - `app/Http/` — controllers, middleware
  - `app/Models/` — Eloquent models
  - Laravel 13 upgrade guide: https://laravel.com/docs/13.x/upgrade
  - Laravel 13 release notes: https://laravel.com/docs/13.x/releases

  **Acceptance Criteria**:
  - [ ] `php artisan migrate:status` → semua migration up-to-date
  - [ ] `php artisan test` → 25/25 PASS (0 failures)
  - [ ] `php artisan route:list` → semua routes terdaftar
  - [ ] Tidak ada deprecated method calls

  **QA Scenarios**:
  ```
  Scenario: Laravel 13 app boots and all tests pass
    Tool: Bash
    Preconditions: Task 6 done (Laravel 13 installed)
    Steps:
      1. php artisan migrate:status → all migrations "Ran"
      2. php artisan test --compact → 25 tests PASS
      3. php artisan route:list --compact → routes registered
      4. php artisan config:cache → no errors (config valid)
    Expected Result: All 25 tests pass on Laravel 13
    Negative: Test failures → check deprecated APIs, update test assertions
    Evidence: .sisyphus/evidence/task-7-laravel13-migration.txt
  ```

  **Commit**: YES (groups with Wave II)
  - Message: `upgrade: laravel 12 → 13 code migration`
  - Files: `config/`, `app/`, `tests/`

- [x] 8. **Filament 4 → 5 Upgrade**

  **What to do**:
  - Update `composer.json`: `"filament/filament": "^5.0"`
  - Run: `composer update filament/filament --with-all-dependencies`
  - Run upgrade command: `php artisan filament:upgrade`
  - Baca Filament 5 upgrade guide: https://filamentphp.com/docs/5.x/introduction/upgrade
  - Breaking changes di Filament 5:
    - PHP 8.5 compatibility fixes (Task 6 sudah resolve)
    - New Resource/Panel API changes
    - Widget registration changes
    - Page component restructure
  - Update semua Filament Resources (12): `app/Filament/Resources/*.php`
  - Update semua Filament Pages (8): `app/Filament/Pages/*.php`
  - Update semua Filament Widgets (4): `app/Filament/Widgets/*.php`
  - Update Filament config: `config/filament.php`
  - Jalankan Filament test: `php artisan test --filter=Filament`

  **Must NOT do**:
  - JANGAN skip Filament upgrade command (`filament:upgrade`)
  - JANGAN hapus custom Filament code sebelum verifikasi

  **Recommended Agent Profile**:
  - **Category**: `deep`
  - **Skills**: `[]`
  - **Reason**: Major admin panel framework upgrade with breaking API changes across 12 Resources + 8 Pages + 4 Widgets

  **Parallelization**:
  - **Can Run In Parallel**: NO (depends on Task 7)
  - **Parallel Group**: Wave II
  - **Blocks**: Task 11
  - **Blocked By**: Task 7

  **References**:
  - `composer.json` — `"filament/filament": "^4.0"` → `"^5.0"`
  - `app/Filament/Resources/` — 12 Resource files
  - `app/Filament/Pages/` — 8 Page files
  - `app/Filament/Widgets/` — 4 Widget files
  - `config/filament.php`
  - Filament 5 upgrade: https://filamentphp.com/docs/5.x/introduction/upgrade

  **Acceptance Criteria**:
  - [ ] `filament/filament` version 5.x in composer.lock
  - [ ] Admin panel dapat diakses di `/admin`
  - [ ] 12 Resources tampil di sidebar
  - [ ] 8 Pages dapat diakses
  - [ ] 4 Widgets render di dashboard
  - [ ] Tidak ada PHP errors / exceptions

  **QA Scenarios**:
  ```
  Scenario: Filament 5 admin panel loads correctly
    Tool: Bash
    Preconditions: Task 7 done, Filament upgraded
    Steps:
      1. php artisan filament:upgrade → no errors
      2. php artisan route:list | grep filament → routes registered
      3. php artisan migrate:status → filament migrations OK
      4. php artisan test --filter=Filament → tests pass (if any)
    Expected Result: Filament 5 fully functional
    Negative: Missing pages → check PanelProvider registration
    Evidence: .sisyphus/evidence/task-8-filament5-upgrade.txt
  ```

  **Commit**: YES (groups with Wave II)
  - Message: `upgrade: filament 4 → 5 — resources, pages, widgets updated`
  - Files: `composer.json`, `composer.lock`, `config/filament.php`, `app/Filament/`

- [x] 9. **React 19.2.5 Upgrade + Vite Config**

  **What to do**:
  - Update `package.json`: `"react": "^19.2.5"`, `"react-dom": "^19.2.5"`
  - Cek Inertia.js compatibility dengan React 19.2: `@inertiajs/react` harus >= 2.x
  - Run: `npm install`
  - Update jika ada breaking changes dari React 19.2 minor
  - Build: `npm run build`
  - Catat: React 19.2.5 vs 19.2.4 — minor patch, risiko rendah

  **Must NOT do**:
  - JANGAN update ke React 19.3 canary (experimental)

  **Recommended Agent Profile**:
  - **Category**: `quick`
  - **Skills**: `[]`
  - **Reason**: Minor React patch upgrade, low risk

  **Parallelization**:
  - **Can Run In Parallel**: YES (independent of Task 7-8 after Task 6)
  - **Parallel Group**: Wave II (with Task 10)
  - **Blocks**: None
  - **Blocked By**: Task 1

  **References**:
  - `package.json` — current `"react": "^19.2.4"`
  - React 19.2.5 changelog: https://github.com/facebook/react/releases/tag/v19.2.5

  **Acceptance Criteria**:
  - [ ] `package.json` shows `"react": "^19.2.5"`
  - [ ] `npm list react` → 19.2.5
  - [ ] `npm run build` → success
  - [ ] Vite build output tidak ada warnings

  **QA Scenarios**:
  ```
  Scenario: React 19.2.5 builds and renders
    Tool: Bash
    Preconditions: npm install completed
    Steps:
      1. npm list react → 19.2.5
      2. npm run build → exit 0, no errors
      3. ls public/build/assets/ → build output exists
    Expected Result: React 19.2.5 integrated, Vite build succeeds
    Evidence: .sisyphus/evidence/task-9-react-upgrade.txt
  ```

  **Commit**: YES (groups with Wave II)
  - Message: `upgrade: react 19.2.4 → 19.2.5`
  - Files: `package.json`, `package-lock.json`

- [x] 10. **PostgreSQL 18.3 Compatibility Check**

  **What to do**:
  - Verifikasi schema compatibility: cek migrations untuk pgsql-specific syntax
  - Cek apakah ada query yang menggunakan deprecated PostgreSQL features
  - PostgreSQL 18 breaking changes dari 16:
    - Perubahan di JSON functions
    - Perubahan di security/integrity checks
  - Update `config/database.php` jika ada driver changes
  - Catat: PostgreSQL 18.3 release notes → tidak ada breaking schema changes besar
  - Test connection: sementara masih pakai PostgreSQL 16 existing

  **Must NOT do**:
  - JANGAN paksa migrate existing data ke PostgreSQL 18 — hanya cek kompatibilitas

  **Recommended Agent Profile**:
  - **Category**: `unspecified-high`
  - **Skills**: `[]`
  - **Reason**: Database version upgrade compatibility analysis

  **Parallelization**:
  - **Can Run In Parallel**: YES (with Task 9)
  - **Parallel Group**: Wave II
  - **Blocks**: Task 12
  - **Blocked By**: Task 1

  **References**:
  - `database/migrations/` — 29 migration files
  - `config/database.php` — pgsql driver config
  - PostgreSQL 18 release notes: https://www.postgresql.org/docs/18/release-18.html

  **Acceptance Criteria**:
  - [ ] Semua 29 migrations kompatibel dengan PostgreSQL 18 syntax
  - [ ] Tidak ada deprecated features yang digunakan
  - [ ] Catatan: jika ada, dokumentasikan migration path

  **QA Scenarios**:
  ```
  Scenario: PostgreSQL 18 compatibility verified
    Tool: Bash
    Preconditions: Project running
    Steps:
      1. grep -r "::jsonb" database/migrations/ → check JSONB usage
      2. php artisan db:show → current PostgreSQL version
      3. Review PostgreSQL 18 release notes for breaking changes
    Expected Result: No blocking issues for PostgreSQL 18 upgrade
    Evidence: .sisyphus/evidence/task-10-pg18-compat.txt
  ```

  **Commit**: YES (groups with Wave II)
  - Message: `upgrade: postgresql 16 → 18 compatibility verification`
  - Files: (documentation only)

- [ ] 11. **FastAPI Dockerfile (Python 3.14)**

  **What to do**:
  - Buat `docker/python/Dockerfile` untuk FastAPI datamining
  - Base: `python:3.14-slim`
  - Install system deps untuk psycopg2: `libpq-dev gcc`
  - Copy `datamining/` ke `/app/`
  - Install requirements: `pip install -r requirements.txt`
  - Expose port 8001
  - CMD: `uvicorn api:app --host 0.0.0.0 --port 8001`

  **Must NOT do**:
  - JANGAN pakai `python:3.11` (full image) — gunakan `-slim`
  - JANGAN hardcode database credentials di Dockerfile

  **Recommended Agent Profile**:
  - **Category**: `quick`
  - **Skills**: `[]`
  - **Reason**: Simple multi-stage-adjacent Dockerfile, standard Python pattern

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave I (with Tasks 1-5)
  - **Blocks**: Task 9 (compose integration)
  - **Blocked By**: Task 5 (requirements.txt)

  **References**:
  - `datamining/api.py:13` — FastAPI app = FastAPI()
  - `datamining/api.py:437-439` — uvicorn.run(app, host="0.0.0.0", port=8001)
  - Official Python Docker guide: slim image + libpq-dev for psycopg2

  **Acceptance Criteria**:
  - [ ] `docker/python/Dockerfile` exists
  - [ ] Uses `python:3.11-slim` base
  - [ ] Installs libpq-dev before pip install
  - [ ] Exposes port 8001
  - [ ] CMD runs uvicorn on 0.0.0.0:8001

  **QA Scenarios**:
  ```
  Scenario: Dockerfile builds successfully
    Tool: Bash
    Preconditions: requirements.txt exists
    Steps:
      1. cd ~/projects/Capstone2
      2. docker build -f docker/python/Dockerfile -t fastapi-test ./datamining/
      3. docker run --rm fastapi-test python -c "import fastapi; print('OK')"
    Expected Result: Build succeeds, fastapi module importable
    Evidence: .sisyphus/evidence/task-6-fastapi-build.txt
  ```

  **Commit**: YES (groups with Wave I)
  - Message: `infra: add FastAPI Dockerfile for datamining module`
  - Files: `docker/python/Dockerfile`

- [ ] 7. **php artisan sail:install — Laravel Sail Scaffold**

  **What to do**:
  - Jalankan `php artisan sail:install --with=pgsql,redis` di WSL2 project
  - Ini menghasilkan `docker-compose.yml` standar Laravel Sail
  - Verifikasi file terbuat: `docker-compose.yml` exists
  - Verifikasi service names: `laravel.test`, `pgsql`, `redis`
  - Periksa `.env` sudah otomatis di-update Sail

  **Must NOT do**:
  - JANGAN edit `docker-compose.yml` dulu — Task 8 yang handle kustomisasi
  - JANGAN jalankan `sail up` sebelum Task 8 selesai (PHP 8.2 override)

  **Recommended Agent Profile**:
  - **Category**: `quick`
  - **Skills**: `[]`
  - **Reason**: Single artisan command, zero logic

  **Parallelization**:
  - **Can Run In Parallel**: NO (sequential)
  - **Parallel Group**: Wave II (after Tasks 1-6)
  - **Blocks**: Task 8, Task 9
  - **Blocked By**: Task 1, Task 4

  **References**:
  - `composer.json:require-dev` — `"laravel/sail": "^1.41"`
  - Laravel Sail docs: `sail:install` command
  - File: `docker-compose.yml` (to be generated)

  **Acceptance Criteria**:
  - [ ] `docker-compose.yml` exists di project root
  - [ ] Contains `laravel.test` service
  - [ ] Contains `pgsql` service (not mysql)
  - [ ] Contains `redis` service

  **QA Scenarios**:
  ```
  Scenario: Sail scaffold has correct services
    Tool: Bash
    Preconditions: composer dependencies installed
    Steps:
      1. php artisan sail:install --with=pgsql,redis
      2. grep "pgsql:" docker-compose.yml → match
      3. grep "redis:" docker-compose.yml → match
      4. grep "mysql:" docker-compose.yml → no match (using pgsql)
    Expected Result: docker-compose.yml with pgsql + redis, no mysql
    Evidence: .sisyphus/evidence/task-7-sail-scaffold.txt
  ```

  **Commit**: YES (groups with Wave II)
  - Message: `infra: laravel sail scaffold with pgsql + redis`
  - Files: `docker-compose.yml`

- [ ] 8. **Customize Sail for PHP 8.2 + PostgreSQL 16**

  **What to do**:
  - Edit `docker-compose.yml` — override PHP version di `laravel.test` build:
    - `build.context` → `./vendor/laravel/sail/runtimes/8.2` (bukan 8.4 default)
  - PostgreSQL image: `postgres:16-alpine` (bukan 18)
  - Set Docker env: `WWWUSER`, `WWWGROUP`, `FORWARD_DB_PORT=5433`
  - Pastikan `DB_HOST=pgsql` (service name), `DB_USERNAME=sail`, `DB_PASSWORD=password`
  - Konfigurasi volumes: bind mount project root + named volumes untuk DB/Redis data
  - Pastikan `.env.sail` values match docker-compose environment

  **Must NOT do**:
  - JANGAN ganti service names (sail package expects `laravel.test`, `pgsql`, `redis`)
  - JANGAN hapus healthcheck dari pgsql service (dibutuhkan untuk depends_on)

  **Recommended Agent Profile**:
  - **Category**: `deep`
  - **Skills**: `[]`
  - **Reason**: Needs careful editing of Docker Compose, understanding Sail internals, PHP version matching, PostgreSQL version pinning

  **Parallelization**:
  - **Can Run In Parallel**: YES (with Task 9 after Task 7 done)
  - **Parallel Group**: Wave II
  - **Blocks**: Task 11
  - **Blocked By**: Task 7

  **References**:
  - `vendor/laravel/sail/runtimes/` — Sail runtime directories (check for 8.2)
  - `composer.json:require.php` — `"^8.2"`
  - `docker-compose.yml` (generated by Task 7)
  - `.env.sail` (created in Task 4)
  - Sail official: https://laravel.com/docs/12.x/sail#choosing-your-php-version

  **Acceptance Criteria**:
  - [ ] `docker-compose.yml` build.context points to `runtimes/8.2`
  - [ ] PostgreSQL image is `postgres:16-alpine`
  - [ ] `FORWARD_DB_PORT=5433` configured
  - [ ] `DB_HOST=pgsql` in environment

  **QA Scenarios**:
  ```
  Scenario: PHP version in container is 8.2
    Tool: Bash (after docker compose up)
    Preconditions: docker-compose.yml customized
    Steps:
      1. grep "runtimes/8.2" docker-compose.yml → match
      2. grep "postgres:16" docker-compose.yml → match
      3. docker compose config → validates without errors
    Expected Result: Config targets PHP 8.2 and PostgreSQL 16
    Evidence: .sisyphus/evidence/task-8-sail-custom.txt
  ```

  **Commit**: YES (groups with Wave II)
  - Message: `infra: customize sail to php 8.2 + postgres 16`
  - Files: `docker-compose.yml`

- [ ] 9. **docker-compose.yml — Add Reverb, FastAPI, Queue Worker**

  **What to do**:
  - Tambahkan service `reverb`:
    - Build dari Sail app image (same PHP container)
    - Port: `${REVERB_PORT:-8080}:8080`
    - Command: `php artisan reverb:start --host=0.0.0.0 --port=8080`
    - Depends on: pgsql, redis
  - Tambahkan service `fastapi`:
    - Build dari `docker/python/Dockerfile`
    - Port: `8001:8001`
    - Environment: `DB_HOST=pgsql`, `DB_PORT=5432`
    - Networks: share `sail` network
  - Tambahkan service `queue`:
    - Build dari Sail app image
    - Command: `php artisan queue:work --sleep=3 --tries=3`
    - Depends on: pgsql, redis
    - Restart: unless-stopped
  - Semua service harus join network `sail`

  **Must NOT do**:
  - JANGAN hardcode ports — gunakan `${VARIABLE:-default}` syntax
  - JANGAN expose Reverb atau FastAPI ke 0.0.0.0 tanpa perlu (dev-only)

  **Recommended Agent Profile**:
  - **Category**: `deep`
  - **Skills**: `[]`
  - **Reason**: Multi-service Docker Compose with networking, dependency chains, health checks — complex orchestration

  **Parallelization**:
  - **Can Run In Parallel**: YES (with Task 8 after Task 7 done)
  - **Parallel Group**: Wave II
  - **Blocks**: Task 11
  - **Blocked By**: Task 6, Task 7

  **References**:
  - `config/reverb.php` — Reverb config (host, port, apps)
  - `docker/python/Dockerfile` (Task 6)
  - `datamining/api.py` — FastAPI entrypoint
  - `config/queue.php` — queue connection set to `database`

  **Acceptance Criteria**:
  - [ ] `reverb` service defined in docker-compose.yml
  - [ ] `fastapi` service defined in docker-compose.yml
  - [ ] `queue` service defined in docker-compose.yml
  - [ ] All services on same `sail` network
  - [ ] FastAPI has `DB_HOST=pgsql` env

  **QA Scenarios**:
  ```
  Scenario: docker compose config is valid
    Tool: Bash
    Preconditions: docker-compose.yml updated
    Steps:
      1. docker compose config --quiet → exit code 0
      2. docker compose config --services → shows: laravel.test, pgsql, redis, reverb, fastapi, queue
    Expected Result: All 6 services registered, config valid
    Evidence: .sisyphus/evidence/task-9-compose-config.txt
  ```

  **Commit**: YES (groups with Wave II)
  - Message: `infra: add reverb + fastapi + queue worker to docker compose`
  - Files: `docker-compose.yml`

- [x] 10. **WSL2 Vite HMR Configuration**

  **What to do**:
  - Edit `vite.config.js`:
    - `server.host = '0.0.0.0'`
    - `server.port = 5173`
    - `server.hmr.host = 'localhost'`
    - `server.watch.usePolling = true`
    - `server.watch.interval = 100`
  - Pastikan docker-compose.yml maps `${VITE_PORT:-5173}:5173`
  - Docs: tambahkan catatan di README bahwa `npm run dev` berjalan via `sail npm run dev`

  **Must NOT do**:
  - JANGAN disable React plugin
  - JANGAN ganti Vite port ke selain 5173 (Sail expects it)

  **Recommended Agent Profile**:
  - **Category**: `quick`
  - **Skills**: `[]`
  - **Reason**: Single config file edit, well-documented WSL2 pattern

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave II
  - **Blocks**: None (independent)
  - **Blocked By**: Task 1 (need project in WSL)

  **References**:
  - `vite.config.js` — current config (alias, React plugin, manual chunks)
  - WSL2 Vite docs: usePolling required for cross-filesystem watch

  **Acceptance Criteria**:
  - [ ] `vite.config.js` has `server.watch.usePolling: true`
  - [ ] `vite.config.js` has `server.host: '0.0.0.0'`
  - [ ] Docker compose maps port 5173

  **QA Scenarios**:
  ```
  Scenario: Vite config has WSL2-compatible settings
    Tool: Bash
    Preconditions: vite.config.js edited
    Steps:
      1. grep "usePolling" vite.config.js → "usePolling: true"
      2. grep "host.*0.0.0.0" vite.config.js → match
      3. node -e "import('./vite.config.js').then(c => console.log(c.default.server.watch.usePolling))" → true
    Expected Result: Vite configured for WSL2 polling
    Evidence: .sisyphus/evidence/task-10-vite-hmr.txt
  ```

  **Commit**: YES (groups with Wave II)
  - Message: `infra: configure vite hmr for wsl2 (polling + host 0.0.0.0)`
  - Files: `vite.config.js`

- [ ] 11. **Docker Environment Smoke Test**

  **What to do**:
  - Copy `.env.sail` → `.env` (sementara, untuk Docker)
  - Jalankan `docker compose build --no-cache`
  - Jalankan `docker compose up -d`
  - Tunggu semua service healthy: `docker compose ps`
  - Jalankan: `docker compose exec app php artisan migrate:fresh --seed`
  - Jalankan: `docker compose exec app php artisan test`
  - Test FastAPI: `curl -s http://localhost:8001/health`
  - Test Reverb: `curl -s http://localhost:8080/apps/test -H "Upgrade: websocket" -H "Connection: Upgrade"`
  - Ambil screenshot: `docker compose logs --tail=50`

  **Must NOT do**:
  - JANGAN skip health check wait — services mungkin belum ready
  - JANGAN lanjut ke Wave III jika ada FAIL

  **Recommended Agent Profile**:
  - **Category**: `quick`
  - **Skills**: `[]`
  - **Reason**: Command execution + verification, straightforward but critical gate

  **Parallelization**:
  - **Can Run In Parallel**: NO (sequential — depends on Tasks 7-10)
  - **Parallel Group**: Wave II (final gate)
  - **Blocks**: Phase 2 Gate, Wave III
  - **Blocked By**: Task 8, Task 9, Task 10

  **References**:
  - `phpunit.xml` — 25 tests in Unit + Feature suites
  - `database/seeders/DatabaseSeeder.php` — test data seeder
  - `datamining/api.py:13` — FastAPI app
  - `config/reverb.php` — apps config

  **Acceptance Criteria**:
  - [ ] `docker compose ps` → semua service "healthy" atau "Up"
  - [ ] `php artisan migrate:fresh --seed` → exit 0
  - [ ] `php artisan test` → 25/25 PASS (0 failures)
  - [ ] `curl localhost:8001/health` → 200 OK
  - [ ] `curl localhost:8080` → HTTP response (bukan connection refused)

  **QA Scenarios**:
  ```
  Scenario: Docker dev environment fully operational
    Tool: Bash (+ tmux for interactive wait)
    Preconditions: docker compose up -d
    Steps:
      1. sleep 15 (wait for services to initialize)
      2. docker compose ps --format json | grep "healthy" → all services
      3. docker compose exec app php artisan migrate:fresh --seed --force
      4. docker compose exec app php artisan test --compact
      5. curl -s http://localhost:8001/health | jq .status → "ok"
      6. curl -s -o /dev/null -w "%{http_code}" http://localhost:8080 → any response (not 000)
    Expected Result: All 6 checks pass. Database seeded, 25 tests pass, FastAPI responds, Reverb responds.
    Negative: If any step fails, capture docker compose logs --tail=100
    Evidence: .sisyphus/evidence/task-11-smoke-test.txt
  ```

  **Commit**: YES (groups with Wave II)
  - Message: `infra: docker smoke test — all services healthy`
  - Files: (no code changes, evidence only)

- [x] 12. **PostgreSQL MCP Server Setup**

  **What to do**:
  - Install: `npm install -g @sarmadparvez/postgresql-mcp`
  - Test koneksi: `postgresql-mcp postgresql://sail:password@localhost:5433/pos_cafe?mode=readonly`
  - Config di `~/.config/opencode/opencode.json`:
    ```json
    {
      "mcp": {
        "postgresql": {
          "type": "local",
          "command": ["npx", "-y", "@sarmadparvez/postgresql-mcp", "postgresql://sail:password@localhost:5433/pos_cafe"],
          "enabled": true,
          "timeout": 30000
        }
      }
    }
    ```
  - Catatan: port 5433 (FORWARD_DB_PORT), bukan 5432
  - Verifikasi: LLM bisa query `SELECT * FROM menus LIMIT 5`

  **Must NOT do**:
  - JANGAN gunakan read-write mode untuk production safety — gunakan `?mode=readonly` atau biarkan default

  **Recommended Agent Profile**:
  - **Category**: `quick`
  - **Skills**: `[]`
  - **Reason**: npm install + config file, straightforward

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave III (with Tasks 13-16)
  - **Blocks**: None (independent)
  - **Blocked By**: Task 11 (smoke test — DB must be running)

  **References**:
  - GitHub: https://github.com/mmcco/postgresql-mcp
  - npm: `@sarmadparvez/postgresql-mcp`
  - Tools: `query`, `execute`, `schema`, `list_tables`, `transaction`
  - `.env.sail`: `DB_USERNAME=sail`, `DB_PASSWORD=password`, `FORWARD_DB_PORT=5433`

  **Acceptance Criteria**:
  - [ ] `which postgresql-mcp` atau `npx @sarmadparvez/postgresql-mcp --help` → works
  - [ ] MCP config added to `opencode.json`
  - [ ] Connection string uses correct port (5433 for host, or 5432 for Docker internal)

  **QA Scenarios**:
  ```
  Scenario: PostgreSQL MCP can list tables
    Tool: Bash
    Preconditions: Docker running, sail DB accessible
    Steps:
      1. PGPASSWORD=password psql -h localhost -p 5433 -U sail -d pos_cafe -c "\dt"
      2. Check output includes: menus, orders, order_items, users, categories
    Expected Result: Tables listed. MCP server can connect to Docker PostgreSQL.
    Negative: Connection refused → check Docker port mapping, FORWARD_DB_PORT
    Evidence: .sisyphus/evidence/task-12-pgsql-mcp.txt
  ```

  **Commit**: YES (groups with Wave III)
  - Message: `infra: add postgresql mcp server config`
  - Files: `~/.config/opencode/opencode.json`

- [x] 13. **Laravel MCP Server Setup (laravel/mcp official)**

  **What to do**:
  - Install: `composer require laravel/mcp`
  - Publish routes: `php artisan vendor:publish --tag=ai-routes`
  - Generate server: `php artisan make:mcp-server PosServer`
  - Generate tools untuk POS domain:
    - `php artisan make:mcp-tool GetMenuItemsTool`
    - `php artisan make:mcp-tool CreateOrderTool`
    - `php artisan make:mcp-tool GetOrderStatusTool`
    - `php artisan make:mcp-tool VerifyStudentTool`
  - Register di `routes/ai.php`: `Mcp::local('pos', PosServer::class);`
  - Config MCP client di `opencode.json`:
    ```json
    {
      "mcp": {
        "laravel-pos": {
          "type": "local",
          "command": ["php", "artisan", "mcp:serve", "--server=pos"],
          "enabled": true
        }
      }
    }
    ```

  **Must NOT do**:
  - JANGAN expose Mcp::web() tanpa auth (security risk)
  - JANGAN generate tools untuk Filament domain (out of scope)

  **Recommended Agent Profile**:
  - **Category**: `deep`
  - **Skills**: `[]`
  - **Reason**: Requires understanding of MCP protocol, Laravel service container, route registration, and tool design patterns

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave III (with Tasks 12,14-16)
  - **Blocks**: None (independent)
  - **Blocked By**: Task 11 (Docker must be running)

  **References**:
  - `laravel/mcp` docs: https://laravel.com/docs/master/mcp
  - GitHub: https://github.com/laravel/mcp
  - `app/Models/Menu.php` — data structure for GetMenuItemsTool
  - `app/Models/Order.php` — data structure for GetOrderStatusTool
  - `routes/ai.php` — MCP route registration

  **Acceptance Criteria**:
  - [ ] `laravel/mcp` terinstall (check `composer.json`)
  - [ ] `routes/ai.php` exists
  - [ ] `Mcp::local('pos', PosServer::class)` registered
  - [ ] At least 4 MCP tools generated

  **QA Scenarios**:
  ```
  Scenario: Laravel MCP server starts without errors
    Tool: Bash (interactive via tmux)
    Preconditions: Docker running, MCP tools generated
    Steps:
      1. docker compose exec app php artisan mcp:serve --server=pos &
      2. sleep 2
      3. Check process is running: jobs -l
      4. Kill process: kill %1
    Expected Result: MCP server starts without PHP errors
    Negative: Class not found → check namespace in ai.php matches tool class
    Evidence: .sisyphus/evidence/task-13-laravel-mcp.txt
  ```

  **Commit**: YES (groups with Wave III)
  - Message: `infra: add laravel mcp server with pos tools`
  - Files: `routes/ai.php`, `app/Mcp/`, `composer.json`

- [x] 14. **Laravel Artisan MCP Setup**

  **What to do**:
  - Clone: `git clone https://github.com/entanglr/laravel-artisan-mcp /opt/laravel-artisan-mcp`
  - Install: `cd /opt/laravel-artisan-mcp && uv sync`
  - Whitelist commands: `migrate, migrate:fresh, migrate:rollback, route:list, make:controller, make:model, make:migration, cache:clear, config:clear, queue:work, db:seed, test`
  - Config di `opencode.json`:
    ```json
    {
      "mcp": {
        "laravel-artisan": {
          "type": "local",
          "command": ["uv", "--directory", "/opt/laravel-artisan-mcp", "run", "artisan_mcp_server.py"],
          "environment": {
            "ARTISAN_DIRECTORY": "/home/nioha/projects/Capstone2",
            "WHITELISTED_COMMANDS": "migrate,migrate:fresh,migrate:rollback,route:list,make:controller,make:model,make:migration,cache:clear,config:clear,queue:work,db:seed,test"
          },
          "enabled": true
        }
      }
    }
    ```

  **Must NOT do**:
  - JANGAN whitelist `migrate:fresh` di production (dev-only)
  - JANGAN whitelist `db:wipe` atau destructive commands

  **Recommended Agent Profile**:
  - **Category**: `quick`
  - **Skills**: `[]`
  - **Reason**: Git clone + config file + whitelist, straightforward

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave III (with Tasks 12-13,15-16)
  - **Blocks**: None (independent)
  - **Blocked By**: Task 1 (need project path)

  **References**:
  - GitHub: https://github.com/entanglr/laravel-artisan-mcp
  - Project path: `~/projects/Capstone2` (WSL2)
  - `artisan` — standard Laravel commands

  **Acceptance Criteria**:
  - [ ] `/opt/laravel-artisan-mcp` exists
  - [ ] `uv sync` completed successfully
  - [ ] Whitelist includes `migrate, route:list, make:controller, test`
  - [ ] MCP config added to opencode.json

  **QA Scenarios**:
  ```
  Scenario: Artisan MCP server can list commands
    Tool: Bash (interactive via tmux)
    Preconditions: Git cloned + uv sync done
    Steps:
      1. cd /opt/laravel-artisan-mcp
      2. ARTISAN_DIRECTORY=~/projects/Capstone2 WHITELISTED_COMMANDS="route:list,test" uv run artisan_mcp_server.py &
      3. sleep 2 && kill %1
    Expected Result: Server starts without Python errors
    Negative: ModuleNotFoundError → run `uv sync` again
    Evidence: .sisyphus/evidence/task-14-artisan-mcp.txt
  ```

  **Commit**: NO (system-wide install, not project files)

- [x] 15. **Playwright MCP Setup + Browser Install**

  **What to do**:
  - Install Playwright MCP: `npx @playwright/mcp@latest --help`
  - Install browser binaries: `npx playwright install --with-deps chromium`
  - Config di `opencode.json`:
    ```json
    {
      "mcp": {
        "playwright": {
          "type": "local",
          "command": ["npx", "@playwright/mcp@latest", "--console-level=error", "--headless"],
          "enabled": true,
          "timeout": 60000
        }
      }
    }
  ```
  - Install system deps untuk WSL2 headless: `sudo apt install -y libnss3 libnspr4 libatk-bridge2.0-0 libdrm2 libxkbcommon0 libgbm1 libasound2`
  - Verifikasi: `npx playwright test --browser chromium` bisa jalan

  **Must NOT do**:
  - JANGAN install full Playwright test runner (`@playwright/test`) di sini — Task 17-30 yang handle
  - JANGAN skip `--headless` — WSL2 tidak punya display

  **Recommended Agent Profile**:
  - **Category**: `unspecified-high`
  - **Skills**: `["playwright"]`
  - **Reason**: Playwright MCP installation + browser deps + WSL headless config — moderately complex

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave III (with Tasks 12-14,16)
  - **Blocks**: Task 17 (Playwright QA)
  - **Blocked By**: Task 11 (Docker running, app accessible)

  **References**:
  - GitHub: https://github.com/microsoft/Playwright-MCP
  - npm: `@playwright/mcp@latest`
  - Tool: `browser_console_messages` — for JS error detection
  - WSL2: `--headless` required (no X11 display)

  **Acceptance Criteria**:
  - [ ] `npx playwright --version` → shows version
  - [ ] Chromium browser installed
  - [ ] `browser_console_messages` tool available in MCP
  - [ ] WSL headless mode confirmed working

  **QA Scenarios**:
  ```
  Scenario: Playwright can launch headless Chromium
    Tool: Bash
    Preconditions: Playwright + Chromium installed
    Steps:
      1. npx playwright test --browser chromium --headed=false -g "smoke" 2>&1 || true
      2. npx playwright --version → shows version number
      3. ls ~/.cache/ms-playwright/ → shows chromium directory
    Expected Result: Playwright installed, browser binaries present
    Negative: Missing deps → install libnss3, libgbm1, etc.
    Evidence: .sisyphus/evidence/task-15-playwright-install.txt
  ```

  **Commit**: NO (system install)

- [x] 16. **OpenCode MCP Config Integration**

  **What to do**:
  - Baca existing `~/.config/opencode/opencode.json`
  - Merge semua 4 MCP server config ke dalam file tersebut
  - Format:
    ```json
    {
      "mcp": {
        "postgresql": { "type": "local", "command": [...], "enabled": true },
        "laravel-pos": { "type": "local", "command": [...], "enabled": true },
        "laravel-artisan": { "type": "local", "command": [...], "environment": {...}, "enabled": true },
        "playwright": { "type": "local", "command": [...], "enabled": true }
      }
    }
    ```
  - Validasi JSON: `python3 -m json.tool opencode.json > /dev/null`
  - Restart OpenCode untuk load MCP servers
  - Verifikasi: `/mcp` di OpenCode TUI → list semua 4 servers

  **Must NOT do**:
  - JANGAN overwrite existing config tanpa merge
  - JANGAN duplicate MCP entries

  **Recommended Agent Profile**:
  - **Category**: `quick`
  - **Skills**: `[]`
  - **Reason**: JSON merge + validation, straightforward

  **Parallelization**:
  - **Can Run In Parallel**: NO (depends on Tasks 12-15)
  - **Parallel Group**: Wave III (final task)
  - **Blocks**: None
  - **Blocked By**: Task 12, 13, 14, 15

  **References**:
  - `~/.config/opencode/opencode.json` — existing OpenCode config
  - `~/.config/opencode/oh-my-openagent.json` — Oh My OpenAgent config (Task 4 from earlier session)
  - Tasks 12-15 MCP configs

  **Acceptance Criteria**:
  - [ ] `opencode.json` valid JSON
  - [ ] 4 MCP servers configured: postgresql, laravel-pos, laravel-artisan, playwright
  - [ ] OpenCode restart → `/mcp` shows all servers
  - [ ] No duplicate entries

  **QA Scenarios**:
  ```
  Scenario: OpenCode config has all 4 MCP servers
    Tool: Bash
    Preconditions: All 4 MCP configured
    Steps:
      1. python3 -m json.tool ~/.config/opencode/opencode.json → no errors
      2. grep -c '"type": "local"' ~/.config/opencode/opencode.json → >= 4
      3. grep "postgresql" ~/.config/opencode/opencode.json → match
      4. grep "laravel-pos" ~/.config/opencode/opencode.json → match
      5. grep "laravel-artisan" ~/.config/opencode/opencode.json → match
      6. grep "playwright" ~/.config/opencode/opencode.json → match
    Expected Result: Config valid, all 4 MCP servers present
    Evidence: .sisyphus/evidence/task-16-opencode-mcp.json
  ```

  **Commit**: NO (system config)

- [ ] 17. **Playwright QA — Auth State Management + Setup**

  **What to do**:
  - Install Playwright test runner: `npm install -D @playwright/test`
  - Init: `npx playwright install --with-deps chromium`
  - Buat `playwright.config.ts`:
    - 2 projects: `cashier` (viewport: 1280x800) dan `customer` (viewport: 430x932)
    - Base URL: `http://localhost` (Docker Sail)
    - Reporter: html + json
    - Screenshot: `only-on-failure`
    - Console error capture: enabled
  - Buat `tests/playwright/auth.setup.ts`:
    - Login sebagai kasir: `kasir@w9cafe.com` / `password` → save storageState ke `auth/cashier.json`
    - Login sebagai customer: `Budi` / `21120122140001` → save storageState ke `auth/customer.json`
  - Buat `tests/playwright/helpers.ts`:
    - `formatRupiah(n)` — verifikasi format IDR di UI
    - `getStatusBadge(status)` — cari status badge element dengan teks tertentu
    - `captureConsoleErrors(page)` — capture dan log console errors
  - Pastikan DB seeded: `docker compose exec app php artisan migrate:fresh --seed`

  **Must NOT do**:
  - JANGAN hardcode auth credentials di test files → gunakan env atau test fixtures
  - JANGAN skip console error listener — semua error harus tercapture

  **Recommended Agent Profile**:
  - **Category**: `unspecified-high`
  - **Skills**: `["playwright"]`
  - **Reason**: Playwright project initialization, auth state management, test infrastructure

  **Parallelization**:
  - **Can Run In Parallel**: NO (sequential — blocks all page tests)
  - **Parallel Group**: Wave IV (first task)
  - **Blocks**: Task 18-30
  - **Blocked By**: Task 11 (Docker running), Task 15 (Playwright MCP)

  **References**:
  - `README.md` — default accounts: `kasir@w9cafe.com / password`, `budi@student.com / password`
  - Playwright auth docs: storageState pattern for multi-role testing
  - CLAUDE.md K1-K8 + C1-C4 page specs
  - `resources/js/helpers.js` — formatRupiah, formatDate, formatTime, summarizeItems

  **Acceptance Criteria**:
  - [ ] `playwright.config.ts` exists with 2 projects (cashier + customer)
  - [ ] `tests/playwright/auth.setup.ts` saves both storageStates
  - [ ] `tests/playwright/helpers.ts` has utilities
  - [ ] `npx playwright test auth.setup` → PASS

  **QA Scenarios**:
  ```
  Scenario: Auth setup authenticates both roles
    Tool: Playwright (via bash)
    Preconditions: Docker running, DB seeded
    Steps:
      1. npx playwright test auth.setup --project=cashier
      2. Check auth/cashier.json exists and contains cookies
      3. npx playwright test auth.setup --project=customer
      4. Check auth/customer.json exists and contains cookies
    Expected Result: Both storageState files created with valid auth
    Negative: Login failed → check DB seeded, APP_URL matches
    Evidence: .sisyphus/evidence/task-17-auth-setup.json
  ```

  **Commit**: YES (groups with Wave IV)
  - Message: `infra: playwright setup — auth state management + helpers`
  - Files: `playwright.config.ts`, `tests/playwright/auth.setup.ts`, `tests/playwright/helpers.ts`, `package.json`

- [ ] 18. **Playwright QA — Kasir Dashboard (K2)**

  **What to do**:
  - Buat `tests/playwright/cashier/dashboard.spec.ts`
  - Precondition: login as cashier (storageState: auth/cashier.json)
  - Navigate ke `/cashier/dashboard`
  - Assertions:
    - Heading "Dashboard" (24px bold) visible
    - Stat bar: 3 columns — "Total Penjualan Hari Ini", "Jumlah Transaksi", "Pesanan Aktif"
    - Quick action buttons: "+ Pesanan Baru", "Lihat Pesanan", "Riwayat"
    - Tabel "Transaksi Terbaru" exists dengan header: ID Pesanan, Item, Total, Pembayaran, Status
  - Console: capture errors via `page.on('console', ...)`
  Screenshot: `tests/playwright/screenshots/k2-dashboard.png`

  **Must NOT do**:
  - JANGAN test dengan data kosong (DB harus di-seed dengan orders)

  **Recommended Agent Profile**:
  - **Category**: `unspecified-high`
  - **Skills**: `["playwright"]`
  - **Reason**: Playwright page testing with DOM assertions

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave IV (with Tasks 19-30)
  - **Blocks**: None
  - **Blocked By**: Task 17

  **References**:
  - CLAUDE.md K2 — Dashboard spec (stat bar, quick action, table)
  - `app/Http/Controllers/Cashier/CashierDashboardController.php`
  - `resources/js/Pages/Cashier/Dashboard.jsx`

  **Acceptance Criteria**:
  - [ ] Test file exists with at least 5 assertions
  - [ ] Stat bar values rendered (formatRupiah visible)
  - [ ] Quick action buttons clickable
  - [ ] Tabel transaksi populated (if seed data exists)

  **QA Scenarios**:
  ```
  Scenario: Dashboard renders with stats and table
    Tool: Playwright
    Preconditions: Auth as kasir, DB seeded with orders
    Steps:
      1. page.goto('/cashier/dashboard')
      2. expect(page.locator('h1')).toContainText('Dashboard')
      3. expect(page.locator('text=Total Penjualan')).toBeVisible()
      4. expect(page.locator('text=Pesanan Aktif')).toBeVisible()
      5. page.on('console', msg => { if (msg.type() === 'error') throw new Error(msg.text()) })
    Expected Result: Dashboard fully rendered, no console errors
    Negative: Empty table → seed data not loaded, check migrate:fresh --seed
    Screenshot: .sisyphus/evidence/task-18-k2-dashboard.png
  ```

  **Commit**: YES (groups with Wave IV)
  - Message: `infra: playwright qa — kasir dashboard (k2)`
  - Files: `tests/playwright/cashier/dashboard.spec.ts`

- [ ] 19. **Playwright QA — Kasir POS/PesananBaru (K3)**

  **What to do**:
  - Buat `tests/playwright/cashier/pesanan-baru.spec.ts`
  - Ini halaman paling kompleks — 3 panel: sidebar, menu grid, keranjang
  - Assertions:
    - Panel kiri: sidebar nav visible
    - Panel tengah: search bar ada, category chips (Kopi, Teh, dll), menu grid 4-column
    - Panel kanan: "Keranjang Pesanan" header, empty cart state, "BAYAR" button disabled
  - Interaksi:
    - Klik category chip → filter menu grid
    - Search menu → result filtered
    - Klik menu card → item muncul di keranjang
    - Klik + di keranjang → quantity bertambah, subtotal update
    - Klik - di keranjang → quantity berkurang
    - Klik − sampe 0 → item hilang dari keranjang
    - Total update realtime
  - Console: capture errors

  **Must NOT do**:
  - JANGAN test payment flow di task ini (ada di Task 30 — integration)

  **Recommended Agent Profile**:
  - **Category**: `unspecified-high`
  - **Skills**: `["playwright"]`
  - **Reason**: Most complex page — 3 panels, category filtering, cart state management via Zustand + IndexedDB

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave IV (with Tasks 18,20-30)
  - **Blocks**: None
  - **Blocked By**: Task 17

  **References**:
  - CLAUDE.md K3 — POS interface spec (3 panels, menu grid, keranjang)
  - `resources/js/Pages/Cashier/PesananBaru.jsx`
  - `resources/js/Components/Cashier/MenuGridItem.jsx`
  - `resources/js/Components/Cashier/KeranjangItem.jsx`
  - `resources/js/Store/cartStore.js` — Zustand cart store
  - Framework: Zustand + IndexedDB (offline persistence)

  **Acceptance Criteria**:
  - [ ] Search bar functional (filter hasil)
  - [ ] Category chips filter menu correctly
  - [ ] Click menu card → item added to cart
  - [ ] Quantity +/- works (Zustand state update)
  - [ ] Subtotal dan Total kalkulasi benar
  - [ ] Empty cart → BAYAR button disabled (opacity 0.5)
  - [ ] Zero console errors

  **QA Scenarios**:
  ```
  Scenario: Full POS interaction — search, add, adjust, remove
    Tool: Playwright
    Preconditions: Auth as kasir, menu items seeded
    Steps:
      1. page.goto('/cashier/pesanan-baru')
      2. expect(page.locator('input[placeholder*="Cari menu"]')).toBeVisible()
      3. page.locator('.category-chip').first().click() → menu grid filters
      4. page.locator('.menu-card').first().click() → item appears in cart
      5. expect(page.locator('.cart-total')).toContainText('Rp')
      6. page.locator('.cart-qty-plus').first().click() → quantity ++
      7. expect(page.locator('.cart-item-count')).toContainText('2')
      8. page.locator('.cart-qty-minus').first().click() → quantity --
      9. Remove last item → cart empty, BAYAR disabled
    Expected Result: Cart interactions work correctly across Zustand/IndexedDB
    Negative: Quantity not updating → Zustand store not syncing
    Screenshot: .sisyphus/evidence/task-19-k3-pos.png
  ```

  **Commit**: YES (groups with Wave IV)
  - Message: `infra: playwright qa — kasir pos/pesanan baru (k3)`
  - Files: `tests/playwright/cashier/pesanan-baru.spec.ts`

- [ ] 20. **Playwright QA — Kasir PesananAktif (K4)**

  **What to do**:
  - Buat `tests/playwright/cashier/pesanan-aktif.spec.ts`
  - Assertions:
    - Heading "Pesanan Aktif" + subtitle
    - Filter tabs: Semua, Pending, Dibayar, Selesai (pill style)
    - Grid 3-column order cards
  - Interaksi:
    - Klik tab "Pending" → filter cards
    - Klik tab "Dibayar" → filter cards
    - Klik tab "Selesai" → filter cards
    - Klik tab "Semua" → all cards shown
  - Per card assertion:
    - `#ORD-XXX` order code visible
    - StatusBadge rendered (dot + label)
    - Items summary (e.g., "2x Kopi Robusta, 1x Roti Bakar")
    - Total harga (formatRupiah)
    - "Detail" button exists
  - Console: capture errors

  **Must NOT do**:
  - JANGAN test WebSocket/polling (di luar scope)

  **Recommended Agent Profile**:
  - **Category**: `unspecified-high`
  - **Skills**: `["playwright"]`
  - **Reason**: Order card grid + filter tab interaction

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave IV (with Tasks 18-19,21-30)
  - **Blocks**: None
  - **Blocked By**: Task 17

  **References**:
  - CLAUDE.md K4 — Pesanan Aktif spec (filter tabs, order cards)
  - `resources/js/Pages/Cashier/PesananAktif.jsx`
  - `resources/js/Components/Cashier/OrderCard.jsx`
  - `resources/js/Components/Common/StatusBadge.jsx`

  **Acceptance Criteria**:
  - [ ] 4 filter tabs functional (Semua, Pending, Dibayar, Selesai)
  - [ ] Order cards rendered with order_code, status, items summary, total
  - [ ] StatusBadge colors correct (e.g., Pending=orange, Selesai=hijau)
  - [ ] Zero console errors

  **QA Scenarios**:
  ```
  Scenario: Filter tabs filter order cards correctly
    Tool: Playwright
    Preconditions: Auth as kasir, multiple orders with different statuses seeded
    Steps:
      1. page.goto('/cashier/pesanan-aktif')
      2. expect(page.locator('h1')).toContainText('Pesanan Aktif')
      3. Filter tabs visible: page.locator('text=Pending'), 'text=Selesai'
      4. page.locator('text=Pending').click() → cards update
      5. expect(page.locator('.order-card')).not.toHaveCount(0)
      6. page.locator('text=Selesai').click()
      7. Each card shows: order_code (#ORD-XXX), StatusBadge, formatRupiah(total)
    Expected Result: Tab filtering works, cards correctly displayed
    Screenshot: .sisyphus/evidence/task-20-k4-aktif.png
  ```

  **Commit**: YES (groups with Wave IV)
  - Message: `infra: playwright qa — kasir pesanan aktif (k4)`
  - Files: `tests/playwright/cashier/pesanan-aktif.spec.ts`

- [ ] 21. **Playwright QA — Kasir RiwayatPesanan (K5)**

  **What to do**:
  - Buat `tests/playwright/cashier/riwayat.spec.ts`
  - Assertions:
    - Heading "Riwayat Pesanan" + subtitle
    - Filter bar: search input, date input, dropdown "Semua Metode"
    - Tabel dengan kolom: ID Pesanan, Tanggal, Waktu, Total, Pembayaran, Kasir, Status, Aksi
  - Interaksi:
    - Search: ketik order code → tabel filter
    - Date picker: pilih tanggal → tabel filter
    - Dropdown: pilih "QRIS" / "Tunai" → tabel filter
  - Verifikasi: semua status di tabel = "Selesai" (hijau)
  - Console: capture errors

  **Recommended Agent Profile**:
  - **Category**: `unspecified-high`
  - **Skills**: `["playwright"]`
  - **Reason**: Table filtering + search with date/dropdown interactions

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave IV (with Tasks 18-20,22-30)
  - **Blocks**: None
  - **Blocked By**: Task 17

  **References**:
  - CLAUDE.md K5 — Riwayat Pesanan spec (filter bar, table)
  - `resources/js/Pages/Cashier/RiwayatPesanan.jsx`

  **Acceptance Criteria**:
  - [ ] Search input filters table rows
  - [ ] Date picker filters by date
  - [ ] Payment method dropdown filters correctly
  - [ ] All statuses = "Selesai" (green badge)
  - [ ] Zero console errors

  **QA Scenarios**:
  ```
  Scenario: Search and filter riwayat transactions
    Tool: Playwright
    Preconditions: Auth as kasir, multiple completed orders
    Steps:
      1. page.goto('/cashier/riwayat')
      2. Fill search: page.fill('input[placeholder*="Cari"]', '#ORD-001')
      3. expect(page.locator('table tbody tr')).toHaveCount(1)  → filtered to 1 row
      4. Clear search → all rows visible again
      5. Select dropdown option "QRIS" → table updates
      6. Each status badge shows "Selesai" (hijau)
    Expected Result: All filter mechanisms functional
    Negative: No results → seed more orders
    Screenshot: .sisyphus/evidence/task-21-k5-riwayat.png
  ```

  **Commit**: YES (groups with Wave IV)
  - Message: `infra: playwright qa — kasir riwayat pesanan (k5)`
  - Files: `tests/playwright/cashier/riwayat.spec.ts`

- [ ] 22. **Playwright QA — Kasir DetailPesanan (K6)**

  **What to do**:
  - Buat `tests/playwright/cashier/detail.spec.ts`
  - Mulai dari `/cashier/order/{id}` (id valid dari seeded data)
  - Assertions:
    - Back arrow `←` + heading "Detail Pesanan #ORD-XXX"
    - Subtitle: tanggal + waktu + "WIB"
    - StatusBadge kanan (warna sesuai status)
    - 2-column layout: "Daftar Item Pesanan" (kiri), "Informasi Pesanan" (kanan)
  - Kiri — tabel item:
    - Kolom: Nama Item, Harga, Jumlah, Subtotal
    - Footer: "Total Pembayaran" bold + formatRupiah
  - Kanan — info card:
    - ID Pesanan, Tanggal, Waktu, Metode Pembayaran, Kasir, Status
  - Console: capture errors

  **Recommended Agent Profile**:
  - **Category**: `unspecified-high`
  - **Skills**: `["playwright"]`
  - **Reason**: Detail page with 2-column layout verification

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave IV
  - **Blocks**: None
  - **Blocked By**: Task 17

  **References**:
  - CLAUDE.md K6 — Detail Pesanan spec (back arrow, 2 columns, table)
  - `resources/js/Pages/Cashier/Order/Show.jsx`

  **Acceptance Criteria**:
  - [ ] Order items table rendered with correct columns
  - [ ] Total matches items × qty
  - [ ] Info card semua field populated
  - [ ] StatusBadge color correct
  - [ ] Back arrow clickable

  **QA Scenarios**:
  ```
  Scenario: Detail pesanan shows complete order info
    Tool: Playwright
    Preconditions: Auth as kasir, at least one order with items
    Steps:
      1. page.goto('/cashier/order/1')
      2. expect(page.locator('h1')).toContainText('Detail Pesanan')
      3. expect(page.locator('text=Daftar Item Pesanan')).toBeVisible()
      4. expect(page.locator('text=Informasi Pesanan')).toBeVisible()
      5. Table items: expect(page.locator('table')).toBeVisible()
      6. Total row: expect(page.locator('text=Total Pembayaran')).toBeVisible()
    Expected Result: Full order detail displayed correctly
    Negative: 404 → invalid order ID, check seed data
    Screenshot: .sisyphus/evidence/task-22-k6-detail.png
  ```

  **Commit**: YES (groups with Wave IV)
  - Message: `infra: playwright qa — kasir detail pesanan (k6)`
  - Files: `tests/playwright/cashier/detail.spec.ts`

- [ ] 23. **Playwright QA — Kasir VerifikasiAkun (K7)**

  **What to do**:
  - Buat `tests/playwright/cashier/verifikasi.spec.ts`
  - Assertions:
    - Heading "Verifikasi Akun Mahasiswa" + subtitle
    - Info bar: "🕐 5 Menunggu" (kuning), "✓ 12 Disetujui" (hijau)
    - Search input "Cari nama atau NIM..."
    - Filter tabs: Semua, Menunggu, Disetujui, Ditolak
    - Tabel: No, Nama, NIM, Tgl Daftar, Status, Aksi
  - Interaksi:
    - Klik tab "Menunggu" → filter pending verification
    - Search NIM → filter specific student
  - Verifikasi: status badges warna benar (Menunggu=kuning, Disetujui=hijau, Ditolak=merah)
  - Aksi untuk status "Menunggu": tombol "Setujui" (hijau) + "Tolak" (merah) muncul
  - Console: capture errors

  **Must NOT do**:
  - JANGAN klik "Setujui" / "Tolak" (mutasi data) — hanya verifikasi UI

  **Recommended Agent Profile**:
  - **Category**: `unspecified-high`
  - **Skills**: `["playwright"]`
  - **Reason**: Verification table with status badges and conditional action buttons

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave IV
  - **Blocks**: None
  - **Blocked By**: Task 17

  **References**:
  - CLAUDE.md K7 — Verifikasi Akun spec (info bar, filter tabs, status badges)
  - `resources/js/Pages/Cashier/VerifikasiAkun.jsx`

  **Acceptance Criteria**:
  - [ ] Info bar shows pending + approved counts
  - [ ] Filter tabs switch table content
  - [ ] Status badges warna sesuai
  - [ ] "Menunggu" rows show "Setujui" + "Tolak" buttons
  - [ ] "Disetujui" / "Ditolak" rows show "Detail" link
  - [ ] Zero console errors

  **QA Scenarios**:
  ```
  Scenario: Verifikasi page filters and shows correct badges
    Tool: Playwright
    Preconditions: Auth as kasir, students with different verification statuses
    Steps:
      1. page.goto('/cashier/verifikasi')
      2. expect(page.locator('text=Menunggu')).toBeVisible()
      3. page.locator('text=Menunggu').click() → filtered
      4. All visible rows: StatusBadge "Menunggu" (kuning)
      5. Each row has "Setujui" and "Tolak" buttons
      6. page.locator('text=Disetujui').click()
      7. All visible rows: StatusBadge "Disetujui" (hijau), only "Detail" link
    Expected Result: Badge colors correct, action buttons conditional
    Screenshot: .sisyphus/evidence/task-23-k7-verifikasi.png
  ```

  **Commit**: YES (groups with Wave IV)
  - Message: `infra: playwright qa — kasir verifikasi akun (k7)`
  - Files: `tests/playwright/cashier/verifikasi.spec.ts`

- [ ] 24. **Playwright QA — Kasir Profil (K8)**

  **What to do**:
  - Buat `tests/playwright/cashier/profil.spec.ts`
  - Assertions:
    - Heading "Profil Saya" + subtitle
    - 2-column layout: Card Profil (kiri) + Informasi Akun (kanan)
  - Kiri — Card Profil:
    - Avatar lingkaran 80px (bg biru, icon user)
    - Nama kasir (18px bold)
    - Badge role "Kasir" (pill style)
    - Tombol "Keluar dari Akun" (merah, full-width)
  - Kanan — Informasi Akun:
    - 4 field groups: Nama Lengkap, Email, Peran/Role, Terdaftar Sejak
    - Semua input disabled/read-only (bg #F8F9FA)
  - Console: capture errors

  **Must NOT do**:
  - JANGAN klik "Keluar dari Akun" (logout mutation) — hanya verifikasi UI

  **Recommended Agent Profile**:
  - **Category**: `unspecified-high`
  - **Skills**: `["playwright"]`
  - **Reason**: Profile page with read-only form fields + logout button

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave IV
  - **Blocks**: None
  - **Blocked By**: Task 17

  **References**:
  - CLAUDE.md K8 — Profil Kasir spec (avatar, badge, read-only fields)
  - `resources/js/Pages/Cashier/Profil.jsx`

  **Acceptance Criteria**:
  - [ ] Avatar + nama + role badge rendered
  - [ ] 4 field groups displayed dengan label dan value
  - [ ] Semua input dalam keadaan disabled
  - [ ] Tombol "Keluar dari Akun" merah, terlihat
  - [ ] Zero console errors

  **QA Scenarios**:
  ```
  Scenario: Profil kasir displays account info correctly
    Tool: Playwright
    Preconditions: Auth as kasir
    Steps:
      1. page.goto('/cashier/profil')
      2. expect(page.locator('h1')).toContainText('Profil Saya')
      3. Avatar visible: page.locator('.avatar-circle') exists
      4. Badge "Kasir" visible
      5. All 4 input fields: disabled attribute present
      6. Logout button: page.locator('text=Keluar dari Akun').toBeVisible()
    Expected Result: All profile info displayed, fields read-only
    Screenshot: .sisyphus/evidence/task-24-k8-profil.png
  ```

  **Commit**: YES (groups with Wave IV)
  - Message: `infra: playwright qa — kasir profil (k8)`
  - Files: `tests/playwright/cashier/profil.spec.ts`

- [ ] 25. **Playwright QA — Kasir Login (K1)**

  **What to do**:
  - Buat `tests/playwright/cashier/login.spec.ts`
  - Viewport: desktop (1280x800)
  - Assertions:
    - Split screen layout: kiri navy (#1A2332) + kanan putih
    - Kiri: logo "W9 Cafe" + "Sistem Point of Sale" subtitle
    - Kanan: "Masuk ke Akun Anda" heading + subtitle gray
    - Input Email dengan icon Mail (kiri)
    - Input Password dengan icon Lock (kiri)
    - Tombol "Masuk" (full-width, bg #3B6FD4)
  - Validasi:
    - Submit empty → error message
    - Submit invalid credentials → error box merah (bg #FEF2F2, border #FCA5A5)
    - Console: capture errors (CLIENT-SIDE only)

  **Must NOT do**:
  - JANGAN test successful login — sudah di-handle oleh auth.setup.ts (Task 17)

  **Recommended Agent Profile**:
  - **Category**: `unspecified-high`
  - **Skills**: `["playwright"]`
  - **Reason**: Login form validation with error state verification

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave IV
  - **Blocks**: None
  - **Blocked By**: Task 17

  **References**:
  - CLAUDE.md K1 — Login Kasir spec (split screen, form, error state)
  - `resources/js/Pages/Auth/Login.jsx`

  **Acceptance Criteria**:
  - [ ] Split screen layout verified
  - [ ] Logo + form rendered
  - [ ] Invalid login shows error box
  - [ ] Error box matches spec (bg #FEF2F2, border #FCA5A5)
  - [ ] Zero client-side console errors

  **QA Scenarios**:
  ```
  Scenario: Login form shows error for invalid credentials
    Tool: Playwright
    Preconditions: Not authenticated (no storageState)
    Steps:
      1. page.goto('/login')
      2. expect(page.locator('text=W9 Cafe')).toBeVisible()
      3. expect(page.locator('text=Masuk ke Akun Anda')).toBeVisible()
      4. page.fill('input[type="email"]', 'wrong@email.com')
      5. page.fill('input[type="password"]', 'wrongpassword')
      6. page.click('button[type="submit"]')
      7. expect(page.locator('text=Email atau kata sandi salah')).toBeVisible() (wait 2s)
    Expected Result: Error alert visible with correct styling
    Screenshot: .sisyphus/evidence/task-25-k1-login.png
  ```

  **Commit**: YES (groups with Wave IV)
  - Message: `infra: playwright qa — kasir login (k1)`
  - Files: `tests/playwright/cashier/login.spec.ts`

- [ ] 26. **Playwright QA — Customer Menu (C1)**

  **What to do**:
  - Buat `tests/playwright/customer/menu.spec.ts`
  - Viewport: mobile (430x932)
  - Precondition: login as customer (storageState: auth/customer.json)
  - Assertions:
    - Header: avatar circle + "Hello Guest" / "selamat Datang"
    - Search bar: "Cari kopi, teh, snack..." (border-radius 50px)
    - Section "Kategori": horizontal scroll chips (Kopi, Teh, Coklat, Snack)
    - Active chip: bg #E8692A (orange)
    - Inactive chip: white + border #E9ECEF
    - Section "Menu Populer": grid 2-column
    - Menu card: image placeholder, name (14px semibold), price (color #E8692A), "+ Tambah" button (orange pill)
  - Interaksi:
    - Klik category chip → menu grid filter
    - Klik "+ Tambah" → verifikasi UI feedback (cart count di bottom nav?)
  - Bottom nav: 4 tabs (Menu, Keranjang, Riwayat, Akun)
  - Console: capture errors

  **Recommended Agent Profile**:
  - **Category**: `unspecified-high`
  - **Skills**: `["playwright"]`
  - **Reason**: Mobile viewport, category filtering, card grid

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave IV
  - **Blocks**: None
  - **Blocked By**: Task 17

  **References**:
  - CLAUDE.md C1 — Menu Pelanggan spec (header, category chips, 2-col grid)
  - `resources/js/Pages/Customer/Menu/Index.jsx`
  - `resources/js/Components/Customer/BottomNav.jsx`
  - `resources/js/Components/Customer/MenuCard.jsx`
  - `resources/js/Components/Customer/CategoryChip.jsx`

  **Acceptance Criteria**:
  - [ ] Mobile viewport (430x932) applied
  - [ ] Category chips render and filter
  - [ ] Menu cards: 2-column grid, harga color orange (#E8692A)
  - [ ] "+ Tambah" button visible on each card
  - [ ] Bottom nav 4 tabs visible
  - [ ] Zero console errors

  **QA Scenarios**:
  ```
  Scenario: Customer browses menu with category filter
    Tool: Playwright
    Preconditions: Auth as customer (Budi), menu items seeded
    Steps:
      1. page.setViewportSize({ width: 430, height: 932 })
      2. page.goto('/customer/menu')
      3. expect(page.locator('text=selamat Datang')).toBeVisible()
      4. Category chips visible: page.locator('.category-chip')
      5. page.locator('text=Kopi').click() → menu grid filters
      6. Menu cards: expect(page.locator('.menu-card')).not.toHaveCount(0)
      7. Price color check: orange (#E8692A) on card
      8. Bottom nav: expect(page.locator('text=Keranjang')).toBeVisible()
    Expected Result: Menu page fully functional on mobile viewport
    Negative: Empty menu → seed data not loaded
    Screenshot: .sisyphus/evidence/task-26-c1-menu.png
  ```

  **Commit**: YES (groups with Wave IV)
  - Message: `infra: playwright qa — customer menu (c1)`
  - Files: `tests/playwright/customer/menu.spec.ts`

- [ ] 27. **Playwright QA — Customer Keranjang (C2)**

  **What to do**:
  - Buat `tests/playwright/customer/cart.spec.ts`
  - Viewport: mobile (430x932)
  - Precondition: customer authenticated, add some items to cart via IndexedDB/Zustand
  - Assertions:
    - Header "Keranjang" (18px bold, center)
    - Sub-info: "N item" + total harga (orange)
    - Item list: per item — nama (14px bold), harga satuan (gray 12px), [-] quantity [+]
    - Quantity buttons: lingkaran 32px, bg orange
    - Footer: Subtotal, Diskon (orange), divider, Total (bold orange)
    - Tombol "Bayar Sekarang" (pill 50px, orange, full-width)
  - Console: capture errors

  **Recommended Agent Profile**:
  - **Category**: `unspecified-high`
  - **Skills**: `["playwright"]`
  - **Reason**: Cart with Zustand/IndexedDB, quantity interaction, mobile viewport

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave IV
  - **Blocks**: None
  - **Blocked By**: Task 17

  **References**:
  - CLAUDE.md C2 — Keranjang spec (item list, footer summary, pay button)
  - `resources/js/Pages/Customer/Cart/Index.jsx`
  - `resources/js/Components/Customer/CartItem.jsx`
  - `resources/js/Store/cartStore.js` — Zustand

  **Acceptance Criteria**:
  - [ ] Cart shows added items
  - [ ] Quantity +/- works
  - [ ] Subtotal, Diskon, Total displayed correctly
  - [ ] "Bayar Sekarang" button visible (full-width, orange pill)
  - [ ] Zero console errors

  **QA Scenarios**:
  ```
  Scenario: Cart displays items and updates quantities
    Tool: Playwright
    Preconditions: Auth as customer, items in cart (via IndexedDB/Zustand preset)
    Steps:
      1. page.setViewportSize({ width: 430, height: 932 })
      2. page.goto('/customer/cart')
      3. expect(page.locator('text=Keranjang')).toBeVisible()
      4. Cart has items: expect(page.locator('.cart-item')).not.toHaveCount(0)
      5. Click + on item → quantity increments
      6. Click - on item → quantity decrements
      7. Total updates: expect(page.locator('text=Total')).toBeVisible()
      8. "Bayar Sekarang" button: expect(page.locator('text=Bayar')).toBeVisible()
    Expected Result: Cart fully interactive on mobile
    Screenshot: .sisyphus/evidence/task-27-c2-cart.png
  ```

  **Commit**: YES (groups with Wave IV)
  - Message: `infra: playwright qa — customer keranjang (c2)`
  - Files: `tests/playwright/customer/cart.spec.ts`

- [ ] 28. **Playwright QA — Customer Riwayat (C3)**

  **What to do**:
  - Buat `tests/playwright/customer/riwayat.spec.ts`
  - Viewport: mobile (430x932)
  - Precondition: customer dengan riwayat orders
  - Assertions:
    - Header "Riwayat Pesanan" (18px bold, center)
    - Filter tabs (pill style, bg #F0F0F0): Semua, Diproses, Selesai
    - Active tab: bg white + shadow
    - Order cards:
      - "Pesanan N" (bold) + status badge (Diproses: orange bg; Selesai: hijau bg)
      - Tanggal (gray 12px)
      - Items summary (gray 13px)
      - Harga (bold) + "Detail" button (orange)
  - Interaksi:
    - Klik tab "Diproses" → filter
    - Klik tab "Selesai" → filter
    - Klik "Detail" button → verifikasi navigation
  - Console: capture errors

  **Recommended Agent Profile**:
  - **Category**: `unspecified-high`
  - **Skills**: `["playwright"]`
  - **Reason**: Order history with filter tabs, mobile viewport

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave IV
  - **Blocks**: None
  - **Blocked By**: Task 17

  **References**:
  - CLAUDE.md C3 — Riwayat spec (filter tabs, order cards)
  - `resources/js/Pages/Customer/Riwayat/Index.jsx`
  - `resources/js/Components/Customer/RiwayatCard.jsx`

  **Acceptance Criteria**:
  - [ ] 3 filter tabs functional
  - [ ] Order cards rendered with correct status badges
  - [ ] "Diproses" badge: bg orange (#E8692A)
  - [ ] "Selesai" badge: bg green (#E8F5E9)
  - [ ] "Detail" button navigates
  - [ ] Zero console errors

  **QA Scenarios**:
  ```
  Scenario: Customer views order history with status filters
    Tool: Playwright
    Preconditions: Auth as customer, orders with different statuses
    Steps:
      1. page.setViewportSize({ width: 430, height: 932 })
      2. page.goto('/customer/riwayat')
      3. expect(page.locator('text=Riwayat Pesanan')).toBeVisible()
      4. Filter tabs visible: Semua, Diproses, Selesai
      5. page.locator('text=Semua').click() → all cards shown
      6. page.locator('text=Selesai').click() → only completed
      7. Status badge: expect(page.locator('text=Selesai')).toBeVisible()
    Expected Result: Filter tabs and order cards functional
    Screenshot: .sisyphus/evidence/task-28-c3-riwayat.png
  ```

  **Commit**: YES (groups with Wave IV)
  - Message: `infra: playwright qa — customer riwayat (c3)`
  - Files: `tests/playwright/customer/riwayat.spec.ts`

- [ ] 29. **Playwright QA — Customer Login (C4)**

  **What to do**:
  - Buat `tests/playwright/customer/login.spec.ts`
  - Viewport: mobile (430x932)
  - Precondition: NOT authenticated
  - Assertions:
    - Logo: kotak rounded 16px (80×80px), bg navy (#1A2332), teks "w9"
    - "W9 Cafe" heading (22px bold)
    - "Pemesanan Online" subtitle (14px gray)
    - Info box: bg #FFF0E8, "Login sebagai Mahasiswa" (orange), "Dapatkan diskon 10%!" (gray)
    - Input "Username (Nama Lengkap)": icon User, placeholder "Masukkan nama lengkap..."
    - Input "Password (NIM)": icon Lock, placeholder "Masukkan NIM..."
    - Tombol "→ Masuk": full-width, bg #E8692A, height 50px, border-radius 8px
    - "Cara Login" section: 3 bullet points (12px gray)
  - Console: capture errors

  **Must NOT do**:
  - JANGAN test successful login — sudah di-handle auth.setup.ts

  **Recommended Agent Profile**:
  - **Category**: `unspecified-high`
  - **Skills**: `["playwright"]`
  - **Reason**: Mobile login form with info box

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave IV
  - **Blocks**: None
  - **Blocked By**: Task 17

  **References**:
  - CLAUDE.md C4 — Login Pelanggan spec (logo, info box, cara login)
  - `resources/js/Pages/Customer/Auth/Login.jsx`

  **Acceptance Criteria**:
  - [ ] Logo + info box rendered
  - [ ] Input fields have correct icons and placeholders
  - [ ] "Cara Login" instructions visible
  - [ ] Color theme orange (#E8692A) consistent
  - [ ] Zero client-side console errors

  **QA Scenarios**:
  ```
  Scenario: Customer login page renders correctly on mobile
    Tool: Playwright
    Preconditions: Not authenticated
    Steps:
      1. page.setViewportSize({ width: 430, height: 932 })
      2. page.goto('/customer/login')
      3. expect(page.locator('text=W9 Cafe')).toBeVisible()
      4. Info box: expect(page.locator('text=Login sebagai Mahasiswa')).toBeVisible()
      5. expect(page.locator('text=Dapatkan diskon 10%')).toBeVisible()
      6. Cara Login section visible with NIM instructions
    Expected Result: Mobile login page fully rendered
    Screenshot: .sisyphus/evidence/task-29-c4-login.png
  ```

  **Commit**: YES (groups with Wave IV)
  - Message: `infra: playwright qa — customer login (c4)`
  - Files: `tests/playwright/customer/login.spec.ts`

- [ ] 30. **Playwright QA — Integration: Full Order Flow End-to-End**

  **What to do**:
  - Buat `tests/playwright/integration/full-order-flow.spec.ts`
  - Flow:
    1. Login as cashier (desktop 1280x800)
    2. Navigate ke `/cashier/pesanan-baru`
    3. Pilih category → klik 3 menu items (misal: 2x Kopi Robusta, 1x Roti Bakar)
    4. Verifikasi keranjang: quantity benar, subtotal benar
    5. Klik "BAYAR" → pilih metode pembayaran → konfirmasi
    6. Verifikasi order created (muncul di `/cashier/pesanan-aktif`)
    7. Navigate ke `/cashier/order/{newId}` → verifikasi detail
    8. Capture screenshots di setiap step
  - Console: capture errors di setiap step
  - Cross-browser: hanya chromium (dev phase)

  **Must NOT do**:
  - JANGAN test payment gateway actual (tidak ada di project)

  **Recommended Agent Profile**:
  - **Category**: `unspecified-high`
  - **Skills**: `["playwright"]`
  - **Reason**: Complex multi-page end-to-end flow, the most challenging QA scenario

  **Parallelization**:
  - **Can Run In Parallel**: NO (sequential — depends on all prior QA tasks passing)
  - **Parallel Group**: Wave IV (last)
  - **Blocks**: None
  - **Blocked By**: Task 18-29 (all page tests must pass first)

  **References**:
  - All CLAUDE.md K1-K8, C1-C4 specs
  - `app/Http/Controllers/Cashier/CashierPesananBaruController.php`
  - `resources/js/Store/cartStore.js`
  - Flow: Menu grid → Keranjang → Payment → Order Created

  **Acceptance Criteria**:
  - [ ] Auth as cashier → success
  - [ ] Add 3 items to cart → cart populated
  - [ ] Submit order → redirect/response success
  - [ ] Order appears in pesanan-aktif
  - [ ] Order detail page accessible
  - [ ] Screenshots captured at each step
  - [ ] Zero console errors throughout flow

  **QA Scenarios**:
  ```
  Scenario: Complete order flow from POS to order tracking
    Tool: Playwright
    Preconditions: Docker running, DB seeded, auth as kasir
    Steps:
      1. page.goto('/cashier/pesanan-baru')
      2. Click category "Kopi" chip
      3. Click first menu card → item added
      4. Click second menu card → item added
      5. Cart shows 2 items, total > 0
      6. Click "BAYAR" button → payment screen or modal
      7. Select payment method (Cash/QRIS)
      8. Confirm → success message or redirect
      9. Navigate to '/cashier/pesanan-aktif'
      10. New order card visible with correct items
      11. Navigate to order detail → items match cart input
    Expected Result: Complete flow from POS to order active
    Negative: Order not appearing → check controller, broadcast, polling
    Screenshot: .sisyphus/evidence/task-30-e2e-flow.png (multiple)
  ```

  **Commit**: YES (groups with Wave IV)
  - Message: `infra: playwright qa — full order flow e2e integration`
  - Files: `tests/playwright/integration/full-order-flow.spec.ts`

---

### Wave VI — Filament Admin Panel QA (24 halaman)

> **Policy**: QA untuk SEMUA halaman Filament 5 admin panel — Resources, Pages, Widgets, modals, buttons, form submissions, table interactions, filters, exports.
> Viewport: 1280×800 (desktop).
> Precondition: Auth as admin user.

- [ ] A1. **Playwright QA — Filament Resources (12 files)**

  **What to do**: Buat `tests/playwright/admin/resources/` dengan 12 spec files:
  - `user-resource.spec.ts` — list, create, edit, delete user. Form fields: name, email, password, role, NIM.
  - `menu-resource.spec.ts` — list, create, edit menu. Fields: name, price, category, image upload.
  - `category-resource.spec.ts` — CRUD kategori.
  - `order-resource.spec.ts` — order table dengan filters (status, date range). View action.
  - `payment-resource.spec.ts` — payment list. View details.
  - `promotion-resource.spec.ts` — CRUD promosi, promotion rules.
  - `ingredient-resource.spec.ts` — CRUD bahan baku. Stock management.
  - `ingredient-batch-resource.spec.ts` — batch tracking, FEFO.
  - `stock-adjustment-resource.spec.ts` — stock adjustment form.
  - `cashier-session-resource.spec.ts` — session tracking.
  - `receivable-resource.spec.ts` — piutang management.
  - `expense-resource.spec.ts` + `income-resource.spec.ts` — finance entries.

  **Per resource assertions**: List table renders, create button opens form modal, form fields exist, submit creates record, edit works, delete confirmation modal, filters work, table sorting.

  **Recommended Agent Profile**: `unspecified-high` + `playwright`
  **Parallelization**: YES — all 12 can run parallel. Wave VI. **Blocked By**: Task 21 (auth setup), Task 8 (Filament 5 upgrade).
  **Commit**: `infra: playwright qa — filament 12 resources`
  **Evidence**: `.sisyphus/evidence/task-a1-{resource}.png`

- [ ] A2. **Playwright QA — Filament Pages (8 files)**

  **What to do**: Buat `tests/playwright/admin/pages/`:
  - `cash-flow.spec.ts` — CashFlow page, chart renders, date filter
  - `financial-report.spec.ts` — FinancialReport, export buttons (PDF/Excel)
  - `data-mining.spec.ts` — DataMining page, tabs/sections
  - `asosiatif-menu.spec.ts` — Association rules display
  - `klasterisasi-menu.spec.ts` — Clustering visualization
  - `klasterisasi-bahan-baku.spec.ts` — Ingredient clustering
  - `prediksi-menu.spec.ts` — Menu prediction charts
  - `prediksi-bahan-baku.spec.ts` — Ingredient prediction

  **Per page assertions**: Heading visible, content renders (charts/widgets/tables), any date filters work, export/download buttons exist.

  **Recommended Agent Profile**: `unspecified-high` + `playwright`
  **Parallelization**: YES — all 8 parallel. Wave VI. **Blocked By**: A1, Task 8.
  **Commit**: `infra: playwright qa — filament 8 pages`
  **Evidence**: `.sisyphus/evidence/task-a2-{page}.png`

- [ ] A3. **Playwright QA — Filament Widgets (4 files)**

  **What to do**: Buat `tests/playwright/admin/widgets/`:
  - `stats-overview.spec.ts` — Dashboard stats widget (angka, label)
  - `cash-flow-chart.spec.ts` — CashFlow chart widget
  - `cash-flow-stats.spec.ts` — CashFlow stat cards
  - `unexpected-transaction.spec.ts` — Unexpected transaction widget

  **Per widget assertions**: Widget renders di dashboard/posisinya, data values displayed, chart visible (jika ada), refresh/polling works.

  **Recommended Agent Profile**: `unspecified-high` + `playwright`
  **Parallelization**: YES — all 4 parallel. Wave VI.
  **Commit**: `infra: playwright qa — filament 4 widgets`
  **Evidence**: `.sisyphus/evidence/task-a3-{widget}.png`

- [ ] A4. **Playwright QA — Filament Modals, Buttons, Form Validation**

  **What to do**: Buat `tests/playwright/admin/modals-buttons.spec.ts`:
  - **Modals**: Create modal opens/closes, edit modal pre-fills data, delete confirmation modal, action confirmation modals
  - **Buttons**: Primary actions (Create, Save), secondary (Cancel, Delete), icon buttons (edit, delete, view), bulk action buttons
  - **Form Validation**: Required field validation, unique field validation (email/NIM), numeric-only validation, max length, file type validation (image)
  - **Table Interactions**: Checkbox select-all, bulk actions dropdown, column sorting, pagination (per-page selector), search/filter bar
  - **Notifications**: Success toast setelah CRUD, error toast setelah validasi gagal

  **Console**: Capture errors via `page.on('console')`

  **Recommended Agent Profile**: `unspecified-high` + `playwright`
  **Parallelization**: NO (sequential — runs after A1-A3 as integration layer)
  **Blocked By**: A1, A2, A3
  **Commit**: `infra: playwright qa — filament modals, buttons, forms`
  **Evidence**: `.sisyphus/evidence/task-a4-modals.png`

---

- [x] 31. **Vercel CLI Install + Auth**

  **What to do**:
  - Install: `npm install -g vercel`
  - Auth: `vercel login` (OAuth flow via browser)
  - Verifikasi: `vercel whoami` → show account
  - Test: `vercel list` → list existing projects

  **Must NOT do**:
  - JANGAN deploy project apa pun — hanya install + auth
  - JANGAN link project ke Vercel dulu (`vercel link`)

  **Recommended Agent Profile**:
  - **Category**: `quick`
  - **Skills**: `[]`
  - **Reason**: npm install + auth, straightforward

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave V (with Tasks 32-33)
  - **Blocks**: Task 34
  - **Blocked By**: Phase 1 Gate Check PASS

  **References**:
  - Vercel CLI docs: https://vercel.com/docs/cli
  - Install: `npm install -g vercel`

  **Acceptance Criteria**:
  - [ ] `vercel --version` → version number
  - [ ] `vercel whoami` → authenticated username

  **QA Scenarios**:
  ```
  Scenario: Vercel CLI authenticated
    Tool: Bash
    Preconditions: Node.js installed
    Steps:
      1. vercel --version → outputs version
      2. vercel whoami → outputs username (NOT "not authenticated")
    Expected Result: CLI installed and authenticated
    Evidence: .sisyphus/evidence/task-31-vercel.txt
  ```

  **Commit**: NO (system install)

- [x] 32. **TiDB CLI Install + Auth**

  **What to do**:
  - Install: `curl https://raw.githubusercontent.com/tidbcloud/tidbcloud-cli/main/install.sh | sh`
  - Auth: `ticloud auth login` (OAuth via browser)
  - Verifikasi: `ticloud config list` → show profiles
  - Test: `ticloud serverless list` → list clusters (may be empty)

  **Must NOT do**:
  - JANGAN buat cluster TiDB — hanya install + auth
  - JANGAN modifikasi database project

  **Recommended Agent Profile**:
  - **Category**: `quick`
  - **Skills**: `[]`
  - **Reason**: Install script + OAuth auth, straightforward

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave V (with Tasks 31,33)
  - **Blocks**: Task 35
  - **Blocked By**: Phase 1 Gate Check PASS

  **References**:
  - TiDB CLI docs: https://docs.pingcap.com/tidbcloud/get-started-with-cli/
  - GitHub: https://github.com/tidbcloud/tidbcloud-cli

  **Acceptance Criteria**:
  - [ ] `ticloud version` → version number
  - [ ] `ticloud auth whoami` → authenticated

  **QA Scenarios**:
  ```
  Scenario: TiDB CLI authenticated
    Tool: Bash
    Preconditions: curl + shell available
    Steps:
      1. ticloud version → outputs version
      2. ticloud config list → shows at least "default" profile
    Expected Result: CLI installed and authenticated
    Evidence: .sisyphus/evidence/task-32-tidb.txt
  ```

  **Commit**: NO (system install)

- [x] 33. **Cloudinary CLI Install + Auth**

  **What to do**:
  - Install: `pip3 install cloudinary-cli`
  - Verifikasi: `cld --version`
  - Set env: `export CLOUDINARY_URL="cloudinary://API_KEY:API_SECRET@cloud_name"`
  - Simpan ke `~/.bashrc` atau `.env` untuk persist
  - Test: `cld config` → show cloud_name

  **Must NOT do**:
  - JANGAN upload file — hanya install + auth
  - JANGAN commit CLOUDINARY_URL ke version control

  **Recommended Agent Profile**:
  - **Category**: `quick`
  - **Skills**: `[]`
  - **Reason**: pip install + env var config, straightforward

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave V (with Tasks 31-32)
  - **Blocks**: Task 36
  - **Blocked By**: Phase 1 Gate Check PASS

  **References**:
  - Cloudinary CLI docs: https://cloudinary.com/documentation/cloudinary_cli
  - Install: `pip3 install cloudinary-cli`

  **Acceptance Criteria**:
  - [ ] `cld --version` → version number
  - [ ] `cld config` → shows cloud_name (not error)

  **QA Scenarios**:
  ```
  Scenario: Cloudinary CLI authenticated
    Tool: Bash
    Preconditions: Python 3 + pip3 available
    Steps:
      1. cld --version → outputs version
      2. cld config → shows cloud_name (no auth error)
    Expected Result: CLI installed, CLOUDINARY_URL configured
    Evidence: .sisyphus/evidence/task-33-cloudinary.txt
  ```

  **Commit**: NO (system install)

- [x] 34. **vercel.json for PHP Runtime**

  **What to do**:
  - Buat `vercel.json` di project root:
    ```json
    {
      "version": 2,
      "functions": {
        "api/index.php": {
          "runtime": "vercel-php@0.9.0"
        }
      },
      "routes": [
        { "src": "/build/(.*)", "dest": "/build/$1" },
        { "src": "/(.*)", "dest": "/api/index.php" }
      ]
    }
    ```
  - Buat `api/index.php` (entry point shim):
    ```php
    <?php
    require __DIR__ . '/../public/index.php';
    ```
  - ⚠️ Catat batasan Vercel di README: no queue workers, no cron, no writable storage, cold starts, 250MB size limit

  **Must NOT do**:
  - JANGAN deploy ke Vercel — hanya konfigurasi persiapan
  - JANGAN commit `.vercel` folder

  **Recommended Agent Profile**:
  - **Category**: `deep`
  - **Skills**: `[]`
  - **Reason**: Vercel PHP routing + handling Laravel public path. Butuh pemahaman Vercel serverless architecture.

  **Parallelization**:
  - **Can Run In Parallel**: YES (with Tasks 35-36 after Wave V)
  - **Parallel Group**: Wave VI
  - **Blocks**: None
  - **Blocked By**: Task 31

  **References**:
  - Vercel PHP runtime docs: vercel-php
  - `public/index.php` — Laravel entry point
  - Guides: Vercel PHP deployment patterns

  **Acceptance Criteria**:
  - [ ] `vercel.json` exists dengan PHP runtime config
  - [ ] `api/index.php` shim exists
  - [ ] README updated dengan Vercel deployment notes + batasan

  **QA Scenarios**:
  ```
  Scenario: vercel.json is valid and points to correct entry
    Tool: Bash
    Preconditions: vercel.json + api/index.php created
    Steps:
      1. python3 -m json.tool vercel.json → no parse errors
      2. grep "vercel-php" vercel.json → match
      3. php -l api/index.php → "No syntax errors"
    Expected Result: Config valid, PHP shim syntactically correct
    Evidence: .sisyphus/evidence/task-34-vercel-config.txt
  ```

  **Commit**: YES (groups with Wave VI)
  - Message: `infra: add vercel.json + php runtime shim for future deployment`
  - Files: `vercel.json`, `api/index.php`, `README.md`

- [ ] 35. **TiDB Database Provisioning + Connection Test**

  **What to do**:
  - Buat TiDB Serverless cluster: `ticloud serverless create --display-name "pos-cafe-db" --region ap-southeast-1`
  - Get connection info: `ticloud serverless describe`
  - Catat: TIDB_HOST, TIDB_PORT, TIDB_USER, TIDB_PASSWORD
  - Simpan credentials di `.env.tidb` (jangan commit)
  - Test koneksi: `mysql -h $TIDB_HOST -P $TIDB_PORT -u $TIDB_USER -p$TIDB_PASSWORD -e "SELECT 1"`
  - ⚠️ Catatan: TiDB = MySQL-compatible, BUKAN PostgreSQL. Migrasi schema + data dilakukan terpisah saat production ready.

  **Must NOT do**:
  - JANGAN jalankan migration ke TiDB — hanya provisioning + connection test
  - JANGAN commit `.env.tidb` atau credentials ke git

  **Recommended Agent Profile**:
  - **Category**: `unspecified-high`
  - **Skills**: `[]`
  - **Reason**: TiDB Cloud provisioning + MySQL connectivity test

  **Parallelization**:
  - **Can Run In Parallel**: YES (with Tasks 34,36)
  - **Parallel Group**: Wave VI
  - **Blocks**: None
  - **Blocked By**: Task 32

  **References**:
  - TiDB CLI: `ticloud serverless create`
  - MySQL client: `mysql` CLI
  - TiDB Serverless free tier includes 1 cluster

  **Acceptance Criteria**:
  - [ ] TiDB cluster created and ACTIVE
  - [ ] Connection string works (mysql client can connect)
  - [ ] `.env.tidb` created with credentials
  - [ ] `.gitignore` contains `.env.tidb`

  **QA Scenarios**:
  ```
  Scenario: TiDB cluster accessible and queryable
    Tool: Bash
    Preconditions: ticloud authenticated, cluster created
    Steps:
      1. ticloud serverless list → shows pos-cafe-db ACTIVE
      2. mysql -h $TIDB_HOST -P $TIDB_PORT -u $TIDB_USER -p"$TIDB_PASSWORD" -e "SELECT VERSION()" → shows TiDB version
    Expected Result: MySQL connection to TiDB successful
    Negative: Connection refused → check cluster status, firewall rules
    Evidence: .sisyphus/evidence/task-35-tidb-connect.txt
  ```

  **Commit**: NO (credentials not committed)

- [x] 36. **Cloudinary Upload Script for Menu Images**

  **What to do**:
  - Buat `scripts/upload-menu-images.sh`:
    ```bash
    #!/usr/bin/env bash
    # Upload menu images ke Cloudinary
    for img in public/images/menu/*.jpg; do
      filename=$(basename "$img" .jpg)
      cld uploader upload "$img" public_id="menu/$filename" folder="pos-cafe-menu"
    done
    ```
  - Buat `scripts/upload-menu-images.py` (Python version, lebih robust):
    - Walk directory `public/images/menu/`
    - Upload tiap file via Cloudinary SDK
    - Track uploaded files di JSON manifest
  - ⚠️ Catatan: saat production, storage switch dari local `public/images/` ke Cloudinary CDN URLs

  **Must NOT do**:
  - JANGAN jalankan upload script — hanya buat script
  - JANGAN modifikasi kode Laravel untuk pakai Cloudinary URLs (itu production phase)

  **Recommended Agent Profile**:
  - **Category**: `quick`
  - **Skills**: `[]`
  - **Reason**: Shell + Python scripts for batch upload

  **Parallelization**:
  - **Can Run In Parallel**: YES (with Tasks 34-35)
  - **Parallel Group**: Wave VI
  - **Blocks**: None
  - **Blocked By**: Task 33

  **References**:
  - Cloudinary CLI: `cld uploader upload`
  - Menu images directory: `public/images/menu/` (if exists)
  - Cloudinary Python SDK: `cloudinary` package

  **Acceptance Criteria**:
  - [ ] `scripts/upload-menu-images.sh` executable
  - [ ] `scripts/upload-menu-images.py` exists (optional)
  - [ ] Script uses `CLOUDINARY_URL` env var for auth

  **QA Scenarios**:
  ```
  Scenario: Upload script is syntactically valid
    Tool: Bash
    Preconditions: Scripts created
    Steps:
      1. ls -la scripts/upload-menu-images.sh → exists
      2. bash -n scripts/upload-menu-images.sh → no syntax errors
      3. python3 -m py_compile scripts/upload-menu-images.py → no errors (optional)
    Expected Result: Scripts valid, ready for production use
    Evidence: .sisyphus/evidence/task-36-cloudinary-script.txt
  ```

  **Commit**: YES (groups with Wave VI)
  - Message: `infra: add cloudinary upload script for menu images`
  - Files: `scripts/upload-menu-images.sh`, `scripts/upload-menu-images.py`

---

## Final Verification Wave

> 4 review agents run in PARALLEL. ALL must APPROVE. Present consolidated results to user and get explicit "okay" before completing.

- [ ] F1. **Plan Compliance Audit** — `oracle`
  Read the plan end-to-end. For each "Must Have": verify implementation exists (read file, curl endpoint, run command). For each "Must NOT Have": search codebase for forbidden patterns — reject with file:line if found. Check evidence files exist in `.sisyphus/evidence/`. Compare deliverables against plan.
  Output: `Must Have [N/N] | Must NOT Have [N/N] | Tasks [N/N] | VERDICT: APPROVE/REJECT`

- [ ] F2. **Code Quality Review** — `unspecified-high`
  Run PHPCS/Pint + `php artisan test` in Docker. Review all changed files for: hardcoded credentials, `.env` committed, missing `.dockerignore` entries, unprotected MCP ports. Check AI slop: excessive comments, over-abstraction.
  Output: `Lint [PASS/FAIL] | Tests [N pass/N fail] | Files [N clean/N issues] | VERDICT`

- [ ] F3. **Real Manual QA** — `unspecified-high` (+ `playwright` skill)
  Start from clean Docker state. Execute EVERY QA scenario from EVERY task — follow exact steps, capture evidence. Test cross-task integration (Docker networking, MCP connectivity, Playwright auth flows). Test edge cases: Docker restart, port conflict, empty DB state.
  Output: `Scenarios [N/N pass] | Integration [N/N] | Edge Cases [N tested] | VERDICT`

- [ ] F4. **Scope Fidelity Check** — `deep`
  For each task: read "What to do", read actual diff (git log/diff). Verify 1:1 — everything in spec was built (no missing), nothing beyond spec was built (no creep). Check "Must NOT do" compliance. Detect cross-task contamination. Flag unaccounted changes (esp. Filament files touched).
  Output: `Tasks [N/N compliant] | Contamination [CLEAN/N issues] | Unaccounted [CLEAN/N files] | VERDICT`

---

## Commit Strategy

- **Wave I**: `infra: wsl2 env setup + dockerignore + requirements.txt` — `.gitattributes`, `.dockerignore`, `.env.sail`, `datamining/requirements.txt`, `docker/`
- **Wave II**: `infra: laravel sail with pgsql + reverb + fastapi + vite hmr` — `docker-compose.yml`, `vite.config.js`, `docker/`
- **Wave III**: `infra: mcp servers (postgresql, laravel, playwright, artisan)` — `opencode.json`, MCP configs
- **Wave IV**: `infra: playwright qa — 12 halaman pos cafe` — `tests/playwright/*.spec.ts`
- **Wave V**: `infra: deployment cli install (vercel, tidb, cloudinary)` — CLI install only
- **Wave VI**: `infra: deployment prep (vercel.json, tidb provision, cloudinary script)` — `vercel.json`, scripts/

---

## Success Criteria

### Verification Commands
```bash
# Docker health
docker compose ps --format "table {{.Name}}\t{{.Status}}"

# Laravel functional
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app php artisan test

# FastAPI health
curl -s http://localhost:8001/health

# Reverb WebSocket
curl -s -I -H "Upgrade: websocket" -H "Connection: Upgrade" http://localhost:8080/apps/test

# Playwright QA
npx playwright test --reporter=html
```

### Final Checklist
- [ ] Semua service Docker healthy (pgsql, redis, reverb, app, fastapi)
- [ ] `php artisan test` — 25 tests PASS
- [ ] `localhost:8001/health` — FastAPI OK
- [ ] Semua 12 halaman Playwright QA PASS dengan screenshots
- [ ] Console errors terdeteksi dan tercapture
- [ ] 4 MCP servers terhubung ke OpenCode
- [ ] Vercel CLI, TiDB CLI, Cloudinary CLI terinstall
- [ ] Rollback test: Laragon masih bisa jalan
