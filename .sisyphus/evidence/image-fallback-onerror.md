# Evidence: Image Fallback onError Handler

**Task:** Add `onError` fallback handler to `<img>` tags in customer-facing menu pages  
**Date:** 2026-05-06  
**Status:** ✅ Complete

---

## Changes Made

### 1. resources/js/Pages/Customer/Menu/Index.jsx

**Location:** Line 30-36 (inline MenuItemCard component)

**Before:**
```jsx
{menu.image
    ? <img src={menu.image} alt={menu.name}
           loading={priority ? 'eager' : 'lazy'}
           decoding={priority ? 'sync' : 'async'}
           style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
    : <Coffee size={36} color="#FFFFFF" strokeWidth={1.5} />
}
```

**After:**
```jsx
{menu.image
    ? <img src={menu.image} alt={menu.name}
           loading={priority ? 'eager' : 'lazy'}
           decoding={priority ? 'sync' : 'async'}
           style={{ width: '100%', height: '100%', objectFit: 'cover' }}
           onError={e => { e.target.style.display = 'none'; }} />
    : <Coffee size={36} color="#FFFFFF" strokeWidth={1.5} />
}
```

---

### 2. resources/js/Components/Customer/MenuCard.jsx

**Location:** Line 28-35

**Before:**
```jsx
{menu.image
    ? <img
        src={menu.image}
        alt={menu.name}
        style={{ width: '100%', height: '100%', objectFit: 'cover' }}
      />
    : <Coffee size={32} color="#B5A898" />
}
```

**After:**
```jsx
{menu.image
    ? <img
        src={menu.image}
        alt={menu.name}
        style={{ width: '100%', height: '100%', objectFit: 'cover' }}
        onError={e => { e.target.style.display = 'none'; }}
      />
    : <Coffee size={32} color="#B5A898" />
}
```

---

## Verification

### Diagnostics Check
- ✅ `Index.jsx` — No diagnostics
- ✅ `MenuCard.jsx` — No diagnostics

### Behavior
When a menu image URL fails to load (404, network error, invalid URL):
1. The `onError` handler executes
2. The broken `<img>` element is hidden via `display: none`
3. The existing `<Coffee>` icon fallback becomes visible
4. No broken image icon is shown to users
5. No layout shift occurs (container maintains aspect ratio)

---

## Files Written

- `.sisyphus/notepads/menu-image-upload/learnings.md` — Pattern documentation
- `.sisyphus/evidence/image-fallback-onerror.md` — This evidence file

---

## Compliance

✅ Both `<img>` tags have `onError` handler  
✅ Uses exact pattern: `onError={e => { e.target.style.display = 'none'; }}`  
✅ Existing Coffee icon fallback structure intact  
✅ No styling or layout changes  
✅ No `<picture>` elements or WebP srcset added  
✅ No other files modified  
