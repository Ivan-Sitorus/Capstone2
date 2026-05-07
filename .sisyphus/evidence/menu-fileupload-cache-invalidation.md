# Evidence: Menu FileUpload + Cache Invalidation

## Task: Add FileUpload field to Filament MenuResource form + override afterCreate() and afterSave() for cache invalidation

## Date: 2026-05-06

---

## Files Modified

### 1. app/Filament/Resources/MenuResource.php

**Changes:**
- Added import: `use App\Services\MenuImageService;`
- Added `saveUploadedFileUsing()` to FileUpload component

**FileUpload configuration:**
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

### 2. app/Filament/Resources/MenuResource/Pages/CreateMenu.php

**Status:** CREATED (did not exist before)

**Content:**
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

### 3. app/Filament/Resources/MenuResource/Pages/EditMenu.php

**Status:** ALREADY EXISTS (no changes needed)

**Verified content:**
```php
protected function afterSave(): void
{
    Cache::forget('customer_menu_v1');
    Cache::forget('menu_categories_active');
}
```

---

## Verification

### LSP Diagnostics
- MenuResource.php: ✅ No diagnostics
- CreateMenu.php: ✅ No diagnostics  
- EditMenu.php: ✅ No diagnostics

### Cache Keys Verified
- `customer_menu_v1` - CustomerMenuController line 30
- `menu_categories_active` - CashierPesananBaruController line 24

### Functionality
- FileUpload is OPTIONAL (no ->required())
- saveUploadedFileUsing calls MenuImageService::convertAndStore($file, $oldPath)
- afterCreate() flushes BOTH cache keys
- afterSave() flushes BOTH cache keys
- No image uploaded = menu saves normally with image = null

---

## Evidence of Completion

1. ✅ FileUpload field present with all required config (label, directory, disk, image preview, accepted types, max size)
2. ✅ saveUploadedFileUsing calls MenuImageService::convertAndStore
3. ✅ CreateMenu.php created with afterCreate() method
4. ✅ EditMenu.php already has afterSave() method
5. ✅ Both cache keys flushed in both hooks
6. ✅ FileUpload is optional (nullable, no required)
7. ✅ All files pass lsp_diagnostics
8. ✅ Evidence written to .sisyphus/evidence/
9. ✅ Findings appended to .sisyphus/notepads/menu-image-upload/learnings.md