# Input Helpers + Menu Fixes

## TL;DR
> Apply TextInputHelper/NumberInputHelper to ALL text/number inputs across Filament resources, fix FileUpload delay in MenuResource, and add toggle-disabled logic for cashback.

---

## Tasks

- [x] 1. **MenuResource Fixes** — FileUpload delay + toggle-disable cashback + helpers
- [x] 2. **PromotionResource Helpers**
- [x] 3. **CategoryResource Helpers**
- [x] 4. **UserResource Helpers**
- [x] 5. **CashierSessionResource Helpers**
- [x] 6. **IncomeResource Helpers**
- [x] 7. **ExpenseResource Helpers**
- [x] 8. **ReceivableResource Helpers**

  **What to do**:
  - Add imports: `TextInputHelper`, `NumberInputHelper`
  - `customer_name` → `->extraInputAttributes(TextInputHelper::string())`
  - `amount` → `->extraInputAttributes(NumberInputHelper::decimal())` + format pattern
  - `paid_amount` → `->extraInputAttributes(NumberInputHelper::decimal())` + format pattern
  
  **Files**: `app/Filament/Resources/ReceivableResource.php`
  **Category**: `quick`

---

## Reference Pattern (from IngredientResource)

```php
use App\Filament\Helpers\NumberInputHelper;
use App\Filament\Helpers\TextInputHelper;

// Text input pattern:
TextInput::make('name')
    ->label('Label')
    ->required()
    ->maxLength(255)
    ->extraInputAttributes(TextInputHelper::string()),

// Integer number pattern:
TextInput::make('amount')
    ->label('Amount')
    ->required()
    ->numeric()
    ->minValue(0)
    ->type('text')
    ->stripCharacters('.')
    ->extraInputAttributes(NumberInputHelper::integer(99999999))
    ->dehydrateStateUsing(fn ($state) => is_string($state) ? (float) str_replace(',', '.', $state) : $state)
    ->formatStateUsing(fn ($state) => $state !== null && $state !== '' ? number_format((float) $state, 2, ',', '.') : ''),
```

## Notes
- Semua perubahan hanya di `app/Filament/Resources/` directory
- Menambahkan import + `->extraInputAttributes()` untuk tiap TextInput
- Untuk numeric fields: tambah `->type('text')`, `->stripCharacters('.')`, format/dehydrate pattern
- FileUpload delay fix khusus `MenuResource.php` saja
- Toggle-disable behavior khusus `MenuResource.php` saja
