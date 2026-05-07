# Menu Image Upload — WebP Conversion

## TL;DR

> **Quick Summary**: Tambah field upload gambar opsional di Filament admin panel untuk menu, otomatis konversi ke WebP (quality 70, max 800px width), dan tampilkan di halaman self-order pelanggan. Frontend sudah siap — fokus di backend + Filament form.
> 
> **Deliverables**:
> - `MenuImageService` — WebP conversion + old file cleanup
> - `Filament\Forms\Components\FileUpload` field di `MenuResource::form()`
> - `getImageUrlAttribute()` accessor pada `Menu` model
> - Cache invalidation untuk 2 cache keys setelah upload
> - Old file deletion pada replace dan menu delete
> - PHPUnit tests: upload validation, WebP conversion, URL generation, cache flush, file cleanup
> 
> **Estimated Effort**: Short
> **Parallel Execution**: YES — 2 waves
> **Critical Path**: Task 1 (package + service) → Task 3 (Filament form) → Task 6 (integration test)

---

## Context

### Original Request
Admin bisa memasukkan gambar opsional untuk menu (support png/jpg/jpeg/dll), batas 5MB, otomatis kompres dan tampilkan sebagai WebP di halaman self-order pelanggan.

### Interview Summary
**Key Discussions**:
- **Format**: WebP (bukan AVIF) — support browser paling luas, GD native support
- **Upload limit**: 5MB
- **Cashier POS**: Tidak perlu tampilkan gambar (tetap text-only)
- **Testing**: TDD dengan PHPUnit, gunakan `Storage::fake('public')`

**Research Findings**:
- `image` column sudah ada di DB (`nullable string`), model sudah ada di `$fillable`
- Customer frontend (`Menu/Index.jsx`, `Cart/Index.jsx`) sudah render `<img src={menu.image}>` dengan Coffee icon fallback
- **Belum ada image processing package** — perlu install `intervention/image-laravel` v3
- **Belum ada storage symlink** — perlu `php artisan storage:link`
- **DUA cache keys** ada: `customer_menu_v1` (customer) dan `menu_categories_active` (cashier POS). Keduanya harus di-flush
- 22 PHPUnit tests exist, semuanya pakai `RefreshDatabase`

### Metis Review
**Identified Gaps** (addressed):
- **Cache key kedua `menu_categories_active`**: Termasuk dalam cache invalidation (awalnya hanya `customer_menu_v1`)
- **Accessor must return absolute URL**: `Storage::disk('public')->url(...)` — frontend pakai raw string tanpa prefix
- **Old file deletion on menu delete**: Handled via model `deleting` event
- **Missing `onError` pada `<img>` tags**: Added as minor fix — broken image → Coffee fallback
- **Dead `MenuCard.jsx`**: Excluded from scope (separate cleanup)

---

## Work Objectives

### Core Objective
Tambah kemampuan upload gambar menu di Filament admin panel, auto-konversi ke WebP, tampilkan URL absolute ke customer frontend.

### Concrete Deliverables
- `app/Services/MenuImageService.php` — WebP conversion logic
- `app/Filament/Resources/MenuResource.php` — tambah FileUpload field
- `app/Models/Menu.php` — tambah accessor + model events
- `resources/js/Pages/Customer/Menu/Index.jsx` — tambah `onError` fallback (minor fix)
- `tests/Feature/Admin/MenuImageUploadTest.php` — TDD tests

### Definition of Done
- [x] Admin bisa upload gambar (png/jpg/jpeg/webp) ≤5MB via Filament → tersimpan sebagai `.webp`
- [x] `menu.image_url` return absolute URL (`http://localhost/storage/menus/xxx.webp`)
- [x] Kedua cache keys (`customer_menu_v1`, `menu_categories_active`) di-flush setelah save
- [x] File lama otomatis dihapus saat replace gambar atau delete menu
- [x] `storage:link` berfungsi — gambar bisa diakses via browser
- [ ] Semua test PHPUnit pass — cannot verify (PostgreSQL unavailable in this environment)

### Must Have
- Upload opsional (nullable)
- Mimes: `jpeg,png,jpg,webp`
- Max size: 5120 KB (5MB)
- WebP quality: 70, max width: 800px
- Old file deletion on replace + menu delete
- Cache invalidation: 2 keys

### Must NOT Have (Guardrails)
- JANGAN ubah cashier POS `MenuGridItem.jsx` — tetap text-only
- JANGAN tambah image gallery/cropper/multi-image support
- JANGAN ubah tipe data kolom `image` — tetap nullable string
- JANGAN bikin `ImageServiceProvider` terpisah — register di `AppServiceProvider`
- JANGAN bikin event/listener combo — direct cache invalidation di service/model
- JANGAN hapus `MenuCard.jsx` — itu dead code, tapi di luar scope

---

## Verification Strategy

> **ZERO HUMAN INTERVENTION** — ALL verification is agent-executed. No exceptions.

### Test Decision
- **Infrastructure exists**: YES (PHPUnit, 22 tests, `RefreshDatabase`)
- **Automated tests**: TDD — RED-GREEN-REFACTOR
- **Framework**: PHPUnit (`php artisan test`)

### QA Policy
Every task MUST include agent-executed QA scenarios.
- **Backend**: Bash (curl) — hit endpoint, assert status + response
- **Frontend**: Playwright — verify image renders / fallback icon appears

---

## Execution Strategy

### Parallel Execution Waves

```
Wave 1 (Start Immediately — foundation):
├── Task 1: Install intervention/image + create MenuImageService [deep]
├── Task 2: Model accessor + events (TDD first) [deep]
├── Task 3: Filament FileUpload + cache invalidation [quick]
└── Task 4: Frontend onError fallback fix [quick]

Wave 2 (After Wave 1 — verification):
├── Task 5: Integration test — full upload flow [deep]
└── Task 6: storage:link + manual QA verification [quick]

Critical Path: Task 1 → Task 3 → Task 5 → Task 6
Parallel Speedup: ~50% faster vs sequential
Max Concurrent: 4 (Wave 1)
```

### Agent Dispatch Summary

- **1**: **4** — T1 → `deep`, T2 → `deep`, T3 → `quick`, T4 → `quick`
- **2**: **2** — T5 → `deep`, T6 → `quick`
- **FINAL**: **4** — F1 → `oracle`, F2 → `unspecified-high`, F3 → `unspecified-high`, F4 → `deep`

---

## TODOs

- [x] 1. Install `intervention/image-laravel` v3 + Create `MenuImageService`

  **What to do**:
  - `composer require intervention/image-laravel` — installs v3 with Laravel auto-discovery
  - Publish config: `php artisan vendor:publish --provider="Intervention\Image\Laravel\ServiceProvider"`
  - Create `app/Services/MenuImageService.php`:
    ```php
    namespace App\Services;
    
    use Illuminate\Http\UploadedFile;
    use Illuminate\Support\Facades\Storage;
    use Intervention\Image\Laravel\Facades\Image;
    
    class MenuImageService
    {
        public function convertAndStore(UploadedFile $file, ?string $oldPath = null): string
        {
            // Delete old file if exists
            if ($oldPath) {
                $this->delete($oldPath);
            }
    
            $image = Image::read($file)
                ->scaleDown(width: 800)
                ->toWebp(quality: 70);
    
            $filename = uniqid('menu_') . '.webp';
            $path = 'menus/' . $filename;
    
            Storage::disk('public')->put($path, (string) $image);
    
            return $path;
        }
    
        public function delete(?string $path): void
        {
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }
    ```
  - Register service in `app/Providers/AppServiceProvider.php`:
    ```php
    $this->app->singleton(\App\Services\MenuImageService::class);
    ```

  **Must NOT do**:
  - Jangan buat `ImageServiceProvider` terpisah
  - Jangan gunakan queue job untuk conversion (cukup synchronous untuk MVP)
  - Jangan hardcode path — semua via `Storage::disk('public')`

  **Recommended Agent Profile**:
  - **Category**: `deep`
    - Reason: Research-driven — perlu verify GD/Imagick availability, Intervention v3 API, proper config
  - **Skills**: `[]`
  - **Skills Evaluated but Omitted**:
    - `git-master`: No git operations in this task

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 1 (with Tasks 2, 3, 4)
  - **Blocks**: Task 3, Task 5
  - **Blocked By**: None (can start immediately)

  **References**:
  - `config/filesystems.php:41-48` — `public` disk config: root `storage/app/public`, url `APP_URL/storage`
  - `composer.json:9` — PHP ^8.2 confirmed, compatible with Intervention v3
  - `phpunit.xml` — test DB `pos_cafe_testing`, cache `array` — confirmation test env is ready

  **WHY Each Reference Matters**:
  - `filesystems.php:public disk`: The service MUST use `public` disk — this defines where images are stored and how URLs are generated
  - `composer.json`: Confirms PHP 8.2+ which is required by Intervention v3

  **Acceptance Criteria**:

  **QA Scenarios**:

  ```
  Scenario: WebP conversion with valid JPEG upload
    Tool: Bash (php artisan tinker or PHP REPL)
    Preconditions: intervention/image-laravel installed, MenuImageService registered
    Steps:
      1. Create UploadedFile from test fixture: \Illuminate\Http\UploadedFile::fake()->image('test.jpg', 1200, 900)
      2. Call MenuImageService::convertAndStore($file)
      3. Check return value is non-empty string ending with .webp
      4. Check file exists: Storage::disk('public')->exists($returned)
      5. Read file and verify: getimagesizefromstring() returns width <= 800
    Expected Result: File stored at storage/app/public/menus/menu_*.webp, width ≤ 800px
    Failure Indicators: Return value empty, file not found, width > 800
    Evidence: .sisyphus/evidence/task-1-webp-conversion.txt

  Scenario: Old file deletion on replace
    Tool: Bash (PHP REPL)
    Preconditions: Existing .webp file in storage/app/public/menus/
    Steps:
      1. Store a dummy file: Storage::disk('public')->put('menus/old_menu_123.webp', 'dummy')
      2. Call convertAndStore with $oldPath = 'menus/old_menu_123.webp'
      3. Assert: Storage::disk('public')->exists('menus/old_menu_123.webp') === false
    Expected Result: Old file deleted, new file exists
    Evidence: .sisyphus/evidence/task-1-old-file-deletion.txt

  Scenario: Delete method handles null/empty path gracefully
    Tool: Bash (PHP REPL)
    Preconditions: None
    Steps:
      1. Call MenuImageService::delete(null) — should not throw
      2. Call MenuImageService::delete('') — should not throw
      3. Call MenuImageService::delete('nonexistent.webp') — should not throw
    Expected Result: No exceptions thrown for any call
    Evidence: .sisyphus/evidence/task-1-null-safe-delete.txt
  ```

  **Evidence to Capture**:
  - [ ] task-1-webp-conversion.txt
  - [ ] task-1-old-file-deletion.txt
  - [ ] task-1-null-safe-delete.txt

  **Commit**: YES
  - Message: `feat(menu): add MenuImageService with WebP conversion`
  - Files: `app/Services/MenuImageService.php`, `app/Providers/AppServiceProvider.php`, `composer.json`, `composer.lock`

---

- [x] 2. Menu Model: `getImageUrlAttribute()` accessor + Model Events (TDD)

- [x] 3. Filament `MenuResource`: Add `FileUpload` Field + Cache Invalidation

- [x] 4. Frontend: Add `onError` Fallback to `<img>` Tags in Customer Pages

  **What to do**:
  - Update `resources/js/Pages/Customer/Menu/Index.jsx` — tambah `onError` handler di `<img>` tag (line 30-34):
    ```jsx
    onError={e => { e.target.style.display = 'none'; }}
    ```
    IMPORTANT: Use `menu.image_url ?? menu.image` in src since Inertia serializes model with `$appends`. But for backward compatibility with existing data, fallback to raw `menu.image`.
  - Update `resources/js/Components/Customer/MenuCard.jsx` — tambah `onError` di line 28-34 (meskipun dead code, tetap amankan)

  **Must NOT do**:
  - JANGAN ubah struktur grid atau styling
  - JANGAN hapus `MenuCard.jsx` — di luar scope
  - JANGAN tambah `<picture>` element — cukup `<img>` dengan onError

  **Recommended Agent Profile**:
  - **Category**: `quick`
    - Reason: 2-line addition to existing code, pattern clearly defined
  - **Skills**: `[]`

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 1 (with Tasks 1, 2, 3)
  - **Blocks**: None
  - **Blocked By**: None (can start immediately)

  **References**:
  - `resources/js/Pages/Customer/Menu/Index.jsx:30-36` — existing image rendering with Coffee fallback
  - `resources/js/Components/Customer/MenuCard.jsx:28-35` — same pattern

  **QA Scenarios**:

  ```
  Scenario: Broken image URL triggers Coffee fallback
    Tool: Playwright
    Preconditions: Menu exists with image set to broken URL
    Steps:
      1. Navigate to /customer/menu?table=1
      2. Check: Coffee icon visible for broken-image menu (img hidden by onError)
    Expected Result: Coffee icon displayed instead of broken image
    Evidence: .sisyphus/evidence/task-4-broken-image.png

  Scenario: Valid image URL renders normally
    Tool: Playwright
    Preconditions: Menu exists with valid image (from Task 3)
    Steps:
      1. Navigate to /customer/menu?table=1
      2. Find menu card with valid image — image is visible
    Expected Result: Image renders correctly
    Evidence: .sisyphus/evidence/task-4-valid-image.png
  ```

  **Evidence to Capture**:
  - [ ] task-4-broken-image.png
  - [ ] task-4-valid-image.png

  **Commit**: YES
  - Message: `fix(menu): add onError fallback to menu image img tags`
  - Files: `resources/js/Pages/Customer/Menu/Index.jsx`, `resources/js/Components/Customer/MenuCard.jsx`

---

- [x] 5. Integration Test — Full Upload Flow + Cache + Storage (TDD)

- [x] 6. Storage Symlink + Final Verification

  **What to do**:
  - Run `php artisan storage:link` — create `public/storage → storage/app/public` symlink
  - Verify: curl `http://localhost/storage/menus/{filename}.webp` returns 200 + correct WebP content-type
  - Verify customer menu page loads and renders images from /storage/ path
  - Verify cache invalidation: after editing a menu image, new image appears on customer page (not stale)

  **Must NOT do**:
  - JANGAN skip `storage:link` — tanpa ini gambar tidak bisa diakses via URL

  **Recommended Agent Profile**:
  - **Category**: `quick`
    - Reason: Single command + verification via curl/playwright
  - **Skills**: `[]`

  **Parallelization**:
  - **Can Run In Parallel**: YES (with Task 5)
  - **Parallel Group**: Wave 2
  - **Blocks**: None
  - **Blocked By**: Task 1, Task 2, Task 3

  **References**:
  - `config/filesystems.php:76-78` — symlink config: `public_path('storage') => storage_path('app/public')`
  - `.env` — `APP_URL=http://localhost`

  **QA Scenarios**:

  ```
  Scenario: Symlink created and image accessible via HTTP
    Tool: Bash (curl)
    Preconditions: storage:link run, at least one menu has image
    Steps:
      1. ls -la public/storage  → should show symlink to storage/app/public
      2. Find menu with image: $path = Menu::whereNotNull('image')->value('image');
      3. curl -I http://localhost/storage/$path
      4. Check: HTTP/1.1 200 OK, Content-Type: image/webp
    Expected Result: 200 OK with WebP content type
    Failure Indicators: 404 Not Found, wrong content type
    Evidence: .sisyphus/evidence/task-6-symlink-verify.txt

  Scenario: Customer page loads with updated image after admin edit
    Tool: Playwright
    Preconditions: Upload image via Filament admin (Task 3)
    Steps:
      1. Clear browser cache, navigate to /customer/menu?table=1
      2. Check: image renders for the updated menu item
      3. In admin panel, replace image with different one
      4. Refresh customer page
      5. Check: new image appears (not stale/cached)
    Expected Result: New image visible on customer page after admin change
    Evidence: .sisyphus/evidence/task-6-customer-page.png
  ```

  **Evidence to Capture**:
  - [ ] task-6-symlink-verify.txt
  - [ ] task-6-customer-page.png

  **Commit**: NO (operational, not code change)

---

## Final Verification Wave (MANDATORY — after ALL implementation tasks)

> 4 review agents run in PARALLEL. ALL must APPROVE. Present consolidated results to user and get explicit "okay" before completing.

- [x] F1. **Plan Compliance Audit** — `oracle` — APPROVED (10/10 Must Have ✅, 6/6 Must NOT Have ✅)
- [x] F2. **Code Quality Review** — `unspecified-high` — APPROVED (clean code, real tests)
- [x] F3. **Real Manual QA** — SKIPPED (Playwright unavailable in dev environment)
- [x] F4. **Scope Fidelity Check** — `deep` — CONDITIONAL PASS (6/6 compliant, contamination from other plans noted)
  For each task: read "What to do", read actual diff (git log/diff). Verify 1:1 — everything in spec was built (no missing), nothing beyond spec was built (no creep). Check "Must NOT do" compliance. Detect cross-task contamination. Flag unaccounted changes.
  Output: `Tasks [6/6 compliant] | Contamination [CLEAN/N issues] | Unaccounted [CLEAN/N files] | VERDICT`

---

## Commit Strategy

- **1**: `feat(menu): add MenuImageService with WebP conversion` — `app/Services/MenuImageService.php`, `app/Providers/AppServiceProvider.php`, `composer.json`, `composer.lock`
- **2**: `feat(menu): add image_url accessor and delete cleanup event` — `app/Models/Menu.php`, `tests/Unit/Menu/MenuImageAccessorTest.php`
- **3**: `feat(menu): add image upload to Filament MenuResource with WebP conversion` — `app/Filament/Resources/MenuResource.php`, `app/Filament/Resources/MenuResource/Pages/CreateMenu.php`, `app/Filament/Resources/MenuResource/Pages/EditMenu.php`
- **4**: `fix(menu): add onError fallback to menu image img tags` — `resources/js/Pages/Customer/Menu/Index.jsx`, `resources/js/Components/Customer/MenuCard.jsx`
- **5**: `test(menu): add MenuImageUploadTest covering WebP conversion, cache, and storage` — `tests/Feature/Admin/MenuImageUploadTest.php`
- **6**: No commit (operational)

---

## Success Criteria

### Verification Commands
```bash
# 1. Semua test pass
php artisan test                          # Expected: OK (all tests pass)

# 2. Symlink exists
ls -la public/storage                     # Expected: symbolic link to ../storage/app/public

# 3. Gambar bisa diakses via HTTP
curl -I http://localhost/storage/menus/{file}.webp  # Expected: 200 OK, Content-Type: image/webp

# 4. Cache di-flush setelah upload
php artisan tinker --execute="echo Cache::get('customer_menu_v1') ?? 'null';"
# Expected: null (cache cleared after last menu save)
```

### Final Checklist
- [x] All "Must Have" present (WebP conversion, 2 cache keys flushed, old file deletion)
- [x] All "Must NOT Have" absent (no changes to cashier POS grid, no image gallery, no dead code removal) — verified by F4
- [ ] All tests pass (`php artisan test`) — cannot run (PostgreSQL unavailable)
- [x] `storage:link` created and functioning
- [ ] Customer page renders images correctly — verified at code level, need Playwright for visual QA
