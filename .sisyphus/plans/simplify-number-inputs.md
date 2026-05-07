# Plan: Simplify Number Input Fields & Fix Decimal Validation

**Status**: ✅ COMPLETED  
**Created**: 2026-05-06  
**Files affected**: 3

---

## Summary

Remove `->numeric()` from all decimal-input fields that use Indonesian number formatting (`1.500,55`) because PHP's `is_numeric()` doesn't recognize comma as decimal separator. Simplify `low_stock_threshold` from 34 lines (with 25-line anonymous ValidationRule) to 8 lines matching the `quantity` field pattern. Add decimal place warning to `NumberInputHelper::decimal()`.

---

## Root Cause

```
Browser: stripCharacters('.') → "1500,55" submitted
Server:  ->numeric() validation runs → is_numeric("1500,55") = FALSE  ❌
         dehydrateStateUsing doesn't run until AFTER validation fails
```

PHP `is_numeric()` rejects comma decimal separator. All client-side formatting via `NumberInputHelper::decimal()` & `stripCharacters('.')` is correct, but server-side `->numeric()` blocks valid Indonesian-format numbers.

---

## Task 1: Enhance `NumberInputHelper::decimal()` — Decimal Place Warning

**File**: `app/Filament/Helpers/NumberInputHelper.php`  
**Lines**: 22-28  
**Priority**: HIGH (dependency for all other tasks)

### Current behavior
`p[1].slice(0,2)` silently truncates decimals >2 digits. No user feedback.

### Change
Add warning when user types >2 decimal digits, BEFORE truncation. Same pattern as existing "batas digit maksimal" warning.

**Before (lines 22-28)**:
```php
public static function decimal(int $maxInt = 10): array
{
    return [
        'onkeydown' => "return !['-','e','E','+'].includes(event.key)",
        'oninput' => "let prev=this.dataset.p||'';let v=this.value.replace(/[^0-9,]/g,'');if(v.startsWith(','))v=v.slice(1);let fc=v.indexOf(',');if(fc!==-1){v=v.slice(0,fc+1)+v.slice(fc+1).replace(/,/g,'');}let p=v.split(',');let i=p[0];let d=p.length>1?p[1].slice(0,2):'';if(i.length>1){i=i.replace(/^0+/,'')||'0';}let mv='9'.repeat({$maxInt});let ex=i.length>mv.length||(i.length==mv.length&&i>mv);let wrp=this.closest('.fi-input-wrp');let el=this.closest('.fi-fo-field')||this.parentElement;let err=el.querySelector('.fi-fo-invalid');if(ex){this.value=prev;if(wrp)wrp.style.boxShadow='0 0 0 2px #DC3545';if(!err){err=document.createElement('p');err.className='fi-fo-invalid';err.style.cssText='color:#DC3545;font-size:0.875rem;margin-top:0.25rem;';el.appendChild(err);}err.textContent='Ini adalah batas digit maksimal';}else{i=i.replace(/\\B(?=(\\d{3})+(?!\\d))/g,'.');this.dataset.p=p.length>1?i+','+d:i;this.value=this.dataset.p;if(wrp)wrp.style.boxShadow='';if(err)err.remove();}",
    ];
}
```

**After**:
```php
public static function decimal(int $maxInt = 10): array
{
    return [
        'onkeydown' => "return !['-','e','E','+'].includes(event.key)",
        'oninput' => "let prev=this.dataset.p||'';let v=this.value.replace(/[^0-9,]/g,'');if(v.startsWith(','))v=v.slice(1);let fc=v.indexOf(',');if(fc!==-1){v=v.slice(0,fc+1)+v.slice(fc+1).replace(/,/g,'');}let p=v.split(',');let i=p[0];let wrp=this.closest('.fi-input-wrp');let el=this.closest('.fi-fo-field')||this.parentElement;let err=el.querySelector('.fi-fo-invalid');if(p.length>1&&p[1].length>2){this.value=prev;if(wrp)wrp.style.boxShadow='0 0 0 2px #DC3545';if(!err){err=document.createElement('p');err.className='fi-fo-invalid';err.style.cssText='color:#DC3545;font-size:0.875rem;margin-top:0.25rem;';el.appendChild(err);}err.textContent='Maksimal 2 angka di belakang koma';}else{let d=p.length>1?p[1].slice(0,2):'';if(i.length>1){i=i.replace(/^0+/,'')||'0';}let mv='9'.repeat({$maxInt});let ex=i.length>mv.length||(i.length==mv.length&&i>mv);if(ex){this.value=prev;if(wrp)wrp.style.boxShadow='0 0 0 2px #DC3545';if(!err){err=document.createElement('p');err.className='fi-fo-invalid';err.style.cssText='color:#DC3545;font-size:0.875rem;margin-top:0.25rem;';el.appendChild(err);}err.textContent='Ini adalah batas digit maksimal';}else{i=i.replace(/\\B(?=(\\d{3})+(?!\\d))/g,'.');this.dataset.p=p.length>1?i+','+d:i;this.value=this.dataset.p;if(wrp)wrp.style.boxShadow='';if(err)err.remove();}}",
    ];
}
```

**What changed**: Moved `let d=...` declaration AFTER decimal check. Added `if(p.length>1&&p[1].length>2)` block that reverts to previous value and shows "Maksimal 2 angka di belakang koma" warning with red outline. Moved `wrp`/`el`/`err` declarations before the check so both error blocks can use them.

### QA
- Type `10,123` → value stays at `10,12`, red outline + warning "Maksimal 2 angka di belakang koma"
- Type `10,12` → accepted normally, no warning
- Paste `1.500,789` → reverts to previous, warning appears

---

## Task 2: Simplify `low_stock_threshold` in IngredientResource.php

**File**: `app/Filament/Resources/IngredientResource.php`  
**Lines**: 59-92  
**Priority**: HIGH

### Change
Replace 34-line block (including 25-line anonymous ValidationRule class) with simplified 8-line pattern matching `quantity` field.

**Before (lines 59-92)**:
```php
            TextInput::make('low_stock_threshold')
                ->label('Low Stock Threshold')
                ->required()
                ->type('text')
                ->extraInputAttributes(NumberInputHelper::decimal())
                ->formatStateUsing(fn ($state) => $state !== null && $state !== '' ? number_format((float) $state, 2, ',', '.') : '')
                ->dehydrateStateUsing(fn ($state) => is_string($state) ? (float) str_replace(',', '.', str_replace('.', '', $state)) : $state)
                ->rules([
                    new class implements \Illuminate\Contracts\Validation\ValidationRule
                    {
                        public function validate(string $attribute, mixed $value, \Closure $fail): void
                        {
                            if ($value === null || $value === '') {
                                return;
                            }
                            $normalized = is_string($value)
                                ? str_replace(',', '.', str_replace('.', '', $value))
                                : (string) $value;
                            if (! is_numeric($normalized)) {
                                $fail('The :attribute field must be a number.');

                                return;
                            }
                            $num = (float) $normalized;
                            if ($num < 0) {
                                $fail('The :attribute field must be at least 0.');
                            }
                            if ($num > 9999999999.99) {
                                $fail('The :attribute field must not be greater than 9.999.999.999,99.');
                            }
                        }
                    },
                ])
                ->suffix(fn ($get) => $get('unit') ? ' ' . $get('unit') : ''),
```

**After**:
```php
            TextInput::make('low_stock_threshold')
                ->label('Low Stock Threshold')
                ->required()
                ->type('text')
                ->extraInputAttributes(NumberInputHelper::decimal())
                ->formatStateUsing(fn ($state) => $state !== null && $state !== '' ? number_format((float) $state, 2, ',', '.') : '')
                ->stripCharacters('.')
                ->dehydrateStateUsing(fn ($state) => is_string($state) ? (float) str_replace(',', '.', $state) : $state)
                ->suffix(fn ($get) => $get('unit') ? ' ' . $get('unit') : ''),
```

**What changed**:
1. Added `->stripCharacters('.')` — strips dot thousand separators client-side before submission (same as `quantity`)
2. Simplified `dehydrateStateUsing` — no longer does `str_replace('.', '', ...)` since `stripCharacters('.')` handles dots
3. Removed entire `->rules([...])` block — client-side JS + DB `decimal(12,2)` enforce constraints

**Number flow for input `1.500,55`**:
```
Display: 1.500,55
stripCharacters('.') → "1500,55" (submitted to server)
dehydrateStateUsing → (float) str_replace(',', '.', "1500,55") → 1500.55
Saved to DB decimal(12,2) → 1500.55 ✓
```

### QA
- Open create ingredient modal → `low_stock_threshold` field is EMPTY (not pre-filled with `0,00`)  
- Type `1.500,75` → displays as `1.500,75` in input → saves as `1500.75` in DB
- Type `0,00` → saves as `0.00` → table column shows `0,00 gram`
- No "must be a number" validation error for any valid Indonesian-format number

---

## Task 3: Remove `->numeric()` from `quantity` in IngredientResource.php

**File**: `app/Filament/Resources/IngredientResource.php`  
**Lines**: 105-115 (inside Repeater > batches > schema)  
**Priority**: HIGH

### Change
Remove `->numeric()` only. Keep `->minValue(0)` (harmless annotation) and `->step(0.1)` (HTML attribute).

**Before (line 108)**:
```php
                    TextInput::make('quantity')
                        ->label('Jumlah')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->step(0.1)
                        ...
```

**After**:
```php
                    TextInput::make('quantity')
                        ->label('Jumlah')
                        ->required()
                        ->minValue(0)
                        ->step(0.1)
                        ...
```

### QA
- Type `10,5` in quantity field → no "must be a number" error → saves as `10.5`
- Type `100` → saves as `100.0`
- Type `0,5` → saves as `0.5`

---

## Task 4: Remove `->numeric()` from `quantity` in ManageBatches.php

**File**: `app/Filament/Resources/IngredientResource/Pages/ManageBatches.php`  
**Priority**: HIGH

### Change
Same fix as Task 3 — remove `->numeric()` from both `quantity` fields:
- CreateAction (approx line 80)
- EditAction (approx line 117)

### QA
- Edit batch → type `5,5` in quantity → no "must be a number" error → saves correctly
- Create new batch → type `10,25` → saves correctly

---

## Task 5: Verify No Remaining `->numeric()` on Decimal Fields

**Priority**: MEDIUM

### Change
Search codebase for `->numeric()` on fields that use `NumberInputHelper::decimal()` to ensure none remain.

```bash
grep -rn "NumberInputHelper::decimal" app/ --include="*.php" -l
# Then check each file for ->numeric() on those fields
```

### QA
- No field using `NumberInputHelper::decimal()` should have `->numeric()`
- `cost_per_unit` should still have `->numeric()` (uses `NumberInputHelper::integer()`, no comma issue)

---

## Edge Cases

| Input | Expected Display | Submitted Value | Saved Value | Notes |
|-------|-----------------|-----------------|-------------|-------|
| `0,00` | `0,00` | `0,00` | `0.00` | Valid |
| `1500` | `1.500` | `1500` | `1500.00` | No decimal, auto-formats |
| `1.500,55` | `1.500,55` | `1500,55` | `1500.55` | Normal case |
| `10,123` | `10,12` | `10,12` | `10.12` | Truncated + warning |
| `abc` | blocked by JS | N/A | N/A | JS onkeydown blocks |
| `-100` | blocked by JS | N/A | N/A | JS onkeydown blocks `-` |
| Paste `10000000000` | reverted + warning | previous | unchanged | Max integer digits |
| Empty | empty | empty | error via `required` | Validation catches |

---

## Verification Steps

1. `php -l` syntax check on all 3 files
2. Run `php artisan migrate` (if migration not yet run)
3. Open create ingredient modal → verify field is empty
4. Type `1.500,55` → verify displays correctly → save → verify DB has `1500.55`
5. Type `10,123` → verify warning "Maksimal 2 angka di belakang koma"
6. Type `10,5` in quantity → verify no "must be a number" error
7. Run `php artisan test` → verify no regressions
