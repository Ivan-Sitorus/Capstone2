# Learnings — menu-image-upload

## Key Decisions
- Intervention v3 for WebP conversion
- Storage::disk('public') for image storage
- Cache keys to invalidate: `customer_menu_v1` AND `menu_categories_active`
- Model events for delete cleanup (not event/listener combo)
- Register MenuImageService in AppServiceProvider (not separate provider)
- TDD approach for Model task (Task 2)

## Conventions
- Use `Storage::disk('public')->url()` for absolute URLs in accessor
- Tests use `Storage::fake('public')` + `UploadedFile::fake()->image()`
- Follow `MenuRecipeModelTest.php` pattern for test structure
- Follow `IngredientCrudTest.php` pattern for Filament admin tests
- FileUpload uses `->saveUploadedFileUsing()` for custom conversion

## Gotchas
- `public` disk url config: `rtrim(env('APP_URL'), '/').'/storage'` - accessor must use this
- Filament FileUpload: `->image()` sets `image/*` accepted types, add explicit `->acceptedFileTypes()` too
- Cache invalidation in 3 places: CreateMenu::afterCreate(), EditMenu::afterSave(), FileUpload::afterStateUpdated()
- MenuImageService must be registered as singleton in AppServiceProvider
- `image` column already in `$fillable` - just need accessor + model event

## File Paths (for reference)
- Menu model: `app/Models/Menu.php`
- MenuResource: `app/Filament/Resources/MenuResource.php`
- CreateMenu: `app/Filament/Resources/MenuResource/Pages/CreateMenu.php`
- EditMenu: `app/Filament/Resources/MenuResource/Pages/EditMenu.php`
- MenuImageService: `app/Services/MenuImageService.php`
- Customer menu: `resources/js/Pages/Customer/Menu/Index.jsx`
- Customer MenuCard: `resources/js/Components/Customer/MenuCard.jsx`## Menu Image Upload Implementation

### Date: 2026-05-06

### Files Modified
- `app/Services/MenuImageService.php` (created)
- `app/Filament/Resources/MenuResource.php`
- `app/Filament/Resources/MenuResource/Pages/CreateMenu.php`
- `app/Filament/Resources/MenuResource/Pages/EditMenu.php`

### Key Learnings

#### 1. Filament FileUpload Component
- Use `Filament\Forms\Components\FileUpload` for image uploads
- Chain methods: `->image()`, `->imagePreviewHeight('200')`, `->directory('menus/')`, `->disk('public')`
- Make optional with `->nullable()` (no `->required()`)
- Accepted file types: `['image/jpeg', 'image/png', 'image/webp']`
- Max size in KB: `->maxSize(5120)` for 5MB

#### 2. Cache Invalidation Pattern
- Two cache keys must be flushed when menu changes:
  - `customer_menu_v1` (CustomerMenuController)
  - `menu_categories_active` (CashierPesananBaruController)
- Use `Cache::forget()` in `afterCreate()` and `afterSave()` hooks
- Import: `use Illuminate\Support\Facades\Cache;`

#### 3. Filament Resource Page Hooks
- `CreateRecord` pages support `afterCreate()` method
- `EditRecord` pages support `afterSave()` method
- These hooks run automatically after the record is saved

#### 4. Image Service Pattern
- Use Intervention Image v3+ for image manipulation
- Convert to WebP format for better compression
- Scale images to max 800px width (maintains aspect ratio)
- Store in `public` disk under `menus/` directory
- Delete old image before saving new one

### Code Pattern

```php
// MenuResource.php - FileUpload field
FileUpload::make('image')
    ->label('Gambar Menu')
    ->directory('menus/')
    ->disk('public')
    ->image()
    ->imagePreviewHeight('200')
    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
    ->maxSize(5120)
    ->nullable(),

// CreateMenu.php - afterCreate hook
protected function afterCreate(): void
{
    Cache::forget('customer_menu_v1');
    Cache::forget('menu_categories_active');
}

// EditMenu.php - afterSave hook
protected function afterSave(): void
{
    Cache::forget('customer_menu_v1');
    Cache::forget('menu_categories_active');
}
```

### Dependencies Required
- `intervention/image` package (for image manipulation)
- GD or Imagick PHP extension

---

## Task 3: Add saveUploadedFileUsing + CreateMenu

### Date: 2026-05-06

### Files Modified
- `app/Filament/Resources/MenuResource.php` - Added saveUploadedFileUsing to FileUpload
- `app/Filament/Resources/MenuResource/Pages/CreateMenu.php` - Created with afterCreate()
- `app/Filament/Resources/MenuResource/Pages/EditMenu.php` - Already had afterSave()

### Implementation Details

#### 1. Added saveUploadedFileUsing to FileUpload
```php
FileUpload::make('image')
    ->label('Gambar Menu')
    ->directory('menus/')
    ->disk('public')
    ->image()
    ->imagePreviewHeight('200')
    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
    ->maxSize(5120)
    ->nullable()
    ->saveUploadedFileUsing(function ($file) {
        $oldPath = $this->record?->image;
        return app(MenuImageService::class)->convertAndStore($file, $oldPath);
    }),
```

#### 2. Created CreateMenu.php
```php
<?php

namespace App\Filament\Resources\MenuResource\Pages;

use App\Filament\Resources\MenuResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Cache;

class CreateMenu extends CreateRecord
{
    protected static string $resource = MenuResource::class;

    protected function afterCreate(): void
    {
        Cache::forget('customer_menu_v1');
        Cache::forget('menu_categories_active');
    }
}
```

### Key Findings
- FileUpload already existed in MenuResource.php - just needed saveUploadedFileUsing
- EditMenu.php already had afterSave() method with cache flush
- CreateMenu.php did not exist - created from scratch
- MenuImageService must be imported in MenuResource.php
- All 3 files pass lsp_diagnostics with no errors

---

## Task 1: MenuImageService Implementation

### Date: 2026-05-06

### Files Modified
- `app/Services/MenuImageService.php` - Updated WebP quality to 70, renamed deleteImage() to delete()
- `app/Providers/AppServiceProvider.php` - Added singleton registration

### Implementation Details

#### 1. MenuImageService Structure
```php
class MenuImageService
{
    public function convertAndStore(UploadedFile $file, ?string $oldPath = null): ?string
    {
        // Delete old file if exists
        // Scale to 800px width
        // Convert to WebP with quality 70
        // Save to storage/app/public/menus/
        // Return relative path
    }

    public function delete(?string $path): void
    {
        // Null-safe, empty-safe, nonexistent-safe deletion
    }
}
```

#### 2. Key Specifications
- **Driver**: Intervention Image v3 with GD Driver
- **Max Width**: 800px (maintains aspect ratio via scale())
- **Format**: WebP
- **Quality**: 70
- **Storage**: Storage::disk('public')
- **Directory**: menus/
- **Filename**: menu_{timestamp}_{uniqid}.webp

#### 3. Null-Safe Delete Pattern
The delete() method safely handles:
- `null` - returns immediately (no-op)
- `''` (empty string) - returns immediately (no-op)
- Non-existent path - checks exists() before delete (no-op)
- Valid path - deletes file from storage

#### 4. Singleton Registration
```php
// AppServiceProvider.php
public function register(): void
{
    $this->app->singleton(\App\Services\MenuImageService::class);
}
```

### QA Test Results
All tests pass via `php artisan tinker`:
1. ✅ Singleton registration confirmed
2. ✅ Null-safe delete handles null/empty/nonexistent
3. ✅ Required methods exist (convertAndStore, delete)
4. ✅ WebP quality is 70

### Dependencies
- `intervention/image-laravel` package (v3)
- GD PHP extension

---

## Task 4: MenuImageUploadTest (Integration Test)

### Date: 2026-05-06

### File Created
- `tests/Feature/Admin/MenuImageUploadTest.php` — 8 test cases

### Test Cases

1. **test_image_url_returns_null_when_no_image** — `image_url` accessor returns null when `image` column is null
2. **test_image_url_returns_absolute_url_when_image_set** — `image_url` returns absolute URL via `Storage::disk('public')->url()`
3. **test_deleting_menu_with_image_removes_file_from_storage** — `Menu::deleting` event calls `MenuImageService::delete()` which removes file from `Storage::fake('public')`
4. **test_replacing_image_deletes_old_file_and_stores_new** — `convertAndStore($file, $oldPath)` deletes old file before storing new
5. **test_webp_conversion_produces_valid_webp_file** — JPEG upload produces `.webp` file with "RIFF" header and valid `IMAGETYPE_WEBP` via `getimagesizefromstring()`
6. **test_cache_is_invalidated_after_menu_save** — Verifies BOTH `customer_menu_v1` AND `menu_categories_active` cache keys are null after invalidation
7. **test_upload_validation_rejects_file_over_5mb** — Laravel `Validator` with `max:5120` rule rejects 6000KB file
8. **test_upload_validation_rejects_invalid_mime_type** — Laravel `Validator` with `mimes:jpeg,png,webp` rejects `image/gif`

### Key Decisions
- Did NOT mock `MenuImageService` — tests use the real service with `Storage::fake('public')`
- Used `createMenu()` helper (same pattern as `MenuImageAccessorTest`) instead of a factory
- Cache test documents the contract (both keys must be null after invalidation) since Filament Livewire testing infrastructure is not available in this project
- Validation tests use Laravel's `Validator` facade directly with rules matching Filament's `FileUpload` component configuration
- WebP validation uses both magic bytes check (`"RIFF"`) and `getimagesizefromstring()` → `IMAGETYPE_WEBP`

### Gotchas
- PostgreSQL not running in dev environment; tests cannot be executed locally, only syntax-checked
- `UploadedFile::fake()->image()` creates real GD images that pass `isValid()` — critical for testing the real service
- `UploadedFile::fake()->create($name, $kb, $mimeType)` creates text files with specified size/mime — used for validation rejection tests


## Task 6: Storage Symlink Verification (2026-05-06)

### What Worked
- `php artisan storage:link` creates symlink successfully
- Symlink resolves correctly: `public/storage` → `storage/app/public`
- Files in `storage/app/public/menus/` are accessible via the symlink
- Configuration in `config/filesystems.php` correctly sets public disk URL to `APP_URL/storage`

### Key Learning
The symlink is a **filesystem-level** feature. In production (Linux server with Apache/Nginx), 
the web server serves files from `public/storage` which resolves to `storage/app/public`.

In WSL + Laragon environment, HTTP tests may fail (404) because:
- Apache runs on Windows side
- Files are on WSL filesystem
- Windows Apache cannot serve WSL paths directly

This is **not a code issue** - the symlink works correctly at filesystem level.

### Production URL Format
```
Base: http://localhost/storage/menus/
Full: http://localhost/storage/menus/{filename}.webp
```

### Verification Steps (Production)
1. Run `php artisan storage:link` once after deployment
2. Upload image via Filament form
3. Access via browser: `curl -I http://your-domain.com/storage/menus/filename.webp`
4. Should return HTTP 200 with correct Content-Type
