# Auth Multi-Interface — Debugging Journey

> **Tanggal**: 15 Mei 2026  
> **Durasi**: ~4 jam debugging  
> **Final state**: ✅ Semua skenario berfungsi

---

## 1. Requirement

W9 Cafe POS punya **3 interface** yang berjalan di browser yang sama:

| Interface | URL | Guard | Cookie |
|-----------|-----|-------|--------|
| **Admin Panel** | `/admin` | `admin` | `w9-cafe-pos-session` |
| **Kasir (POS)** | `/kasir` | `web` | `w9-cafe-pos-session` |
| **Dapur (KDS)** | `/dapur` | `kitchen` | `w9-cafe-pos-session` |

**Kebutuhan**:

1. Admin bisa login ke ketiga interface bersamaan di tab berbeda
2. Logout dari satu interface TIDAK boleh mempengaruhi interface lain
3. Tidak boleh auto-login — buka `/kasir/login` harus tampil form, bukan langsung masuk
4. Login lintas-role harus kasih pesan error yang jelas (BUKAN 403 Forbidden)

---

## 2. Timeline Debugging

### Fase 1 — Cookie per Path (GAGAL) 🚫

**Pendekatan**: Middleware `SetSessionCookie` mengubah nama cookie session berdasarkan path URL:
- `/admin/*` → cookie: `admin_session`
- `/kasir/*` → cookie: `kasir_session`
- `/dapur/*` → cookie: `dapur_session`

**Kenapa gagal**: Middleware `web(prepend:)` tidak menjamin jalan SEBELUM `StartSession`. Session driver sudah terlanjur dibuat dengan nama cookie default. Selain itu, cookie `Path=/` menyebabkan semua cookie dikirim bersamaan — browser tetap bingung.

**Pelajaran**: Jangan manipulasi session cookie secara runtime. Gunakan mekanisme bawaan Laravel.

---

### Fase 2 — Multi-Guard (BERHASIL, tapi...) ✅⚠️

**Pendekatan**: Standard Laravel multi-guard. Setiap interface pakai guard berbeda:

```php
// config/auth.php
'guards' => [
    'web'     => ['driver' => 'session', 'provider' => 'users'],  // kasir
    'kitchen' => ['driver' => 'session', 'provider' => 'users'],  // dapur
    'admin'   => ['driver' => 'session', 'provider' => 'users'],  // admin
],
```

Setiap guard menyimpan auth state di **session key berbeda**:

| Guard | Session Key |
|-------|-------------|
| `web` | `login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d` |
| `kitchen` | `login_kitchen_59ba36addc2b2f9401580f014c7f58ea4e30989d` |
| `admin` | `login_admin_59ba36addc2b2f9401580f014c7f58ea4e30989d` |

Semua key di **satu cookie session** tapi **independen**. Login/logout di satu guard tidak mempengaruhi guard lain... **seharusnya**.

**Tapi masih ada 3 masalah tersembunyi** 👇

---

### Fase 3 — Debugging Root Cause 🐛🐛🐛

#### Root Cause #1: Filament default authGuard = `web`

**Lokasi**: `vendor/filament/filament/src/Panel/Concerns/HasAuth.php:94`

```php
protected string $authGuard = 'web';  // ← DEFAULT!
```

**Dampak**: Karena `AdminPanelProvider` tidak set `->authGuard('admin')`, Filament pakai guard `web` — **SAMA dengan kasir**. Akibatnya admin & kasir share auth state → auto-login, auto-logout.

**Kategori**: **SALAH KONFIGURASI KITA** — lupa set `->authGuard('admin')` secara eksplisit.

**Fix**:
```php
// AdminPanelProvider.php
->login()
->authGuard('admin')  // ← TAMBAH INI
```

---

#### Root Cause #2: Filament LogoutController — `session()->invalidate()`

**Lokasi**: `vendor/filament/filament/src/Auth/Http/Controllers/LogoutController.php:14-15`

```php
Filament::auth()->logout();
session()->invalidate();      // ← HAPUS SEMUA SESSION!
session()->regenerateToken();
```

**Dampak**: Meskipun `Filament::auth()->logout()` hanya logout admin guard, `session()->invalidate()` menghapus **seluruh session** — termasuk key guard `web` dan `kitchen`. Akibatnya logout dari admin = logout dari semua interface.

**Kategori**: **Limitation** — kerja normal untuk single-guard, tidak kompatibel dengan multi-guard.

**Fix**: Override dengan `FilamentLogoutController` yang cek guard lain dulu:
```php
// app/Http/Controllers/Auth/FilamentLogoutController.php
Filament::auth()->logout();

// Cek apakah guard lain masih aktif
$others = array_diff(['web', 'kitchen', 'admin'], ['admin']);
$stillActive = false;
foreach ($others as $g) {
    if (Auth::guard($g)->check()) {
        $stillActive = true;
        break;
    }
}
// Hanya invalidate kalau TIDAK ADA guard lain yang aktif
if (!$stillActive) {
    session()->invalidate();
    session()->regenerateToken();
}
```

---

#### Root Cause #3: Filament LoginResponse — `redirect()->intended()`

**Lokasi**: `vendor/filament/filament/src/Auth/Http/Responses/LoginResponse.php:14`

```php
return redirect()->intended(Filament::getUrl());
```

**Dampak**: `intended()` membaca URL "yang dituju" dari session. Kalau user sebelumnya buka `/dapur/login`, intended URL = `/dapur/login`. Ketika login di admin, redirect malah ke dapur!

**Kategori**: **Limitation** — behavior benar untuk single-guard, salah untuk multi-interface.

**Fix**: Ganti dengan redirect langsung tanpa `intended()`:
```php
// app/Providers/Filament/LoginResponse.php
return redirect(Filament::getUrl());
```

Register via `$this->app->bind()` di `AdminPanelProvider::register()`.

---

## 3. Arsitektur Final

```
┌─────────────────────────────────────────────────────┐
│                    BROWSER                          │
│                                                     │
│  Tab 1: /admin ─── cookie: w9-cafe-pos-session ─┐  │
│  Tab 2: /kasir ─── cookie: w9-cafe-pos-session ─┤  │
│  Tab 3: /dapur ─── cookie: w9-cafe-pos-session ─┘  │
│                                                     │
│  SATU cookie, tapi:                                 │
│    login_admin_59ba36...  ← admin guard            │
│    login_web_59ba36...    ← web guard (kasir)      │
│    login_kitchen_59ba36... ← kitchen guard (dapur) │
│                                                     │
│  Masing-masing INDEPENDEN!                          │
└─────────────────────────────────────────────────────┘
```

## 4. File yang Terlibat

### Dibuat
| File | Purpose |
|------|---------|
| `app/Http/Middleware/SetSessionCookie.php` | ❌ Dihapus (gagal) |
| `app/Http/Controllers/Auth/FilamentLogoutController.php` | ✅ Override logout Filament |
| `app/Providers/Filament/LoginResponse.php` | ✅ Override login redirect |

### Dimodifikasi
| File | Perubahan |
|------|-----------|
| `config/auth.php` | Tambah guard `kitchen` + `admin` |
| `routes/web.php` | `auth:web` di kasir, `auth:kitchen` di dapur |
| `AuthController.php` | Guard-specific login/logout + role validation |
| `AdminPanelProvider.php` | `authGuard('admin')` + routes + bind |
| `bootstrap/app.php` | Hapus `SetSessionCookie` prepend |

### Vendor File yang Di-override
| File | Alasan |
|------|--------|
| `vendor/filament/.../HasAuth.php:94` | `authGuard = 'web'` — fix via `->authGuard('admin')` |
| `vendor/filament/.../LogoutController.php:14` | `session()->invalidate()` — fix via custom controller |
| `vendor/filament/.../LoginResponse.php:14` | `redirect()->intended()` — fix via custom response |

---

## 5. Pelajaran

1. **Cookie-per-path middleware tidak reliable** di Laravel. `StartSession` sudah jalan sebelum middleware kita.
2. **Multi-guard adalah cara Laravel yang benar** untuk multi-auth. Session key per guard memberikan isolasi tanpa perlu manipulasi cookie.
3. **Filament tidak dirancang untuk multi-guard** dalam satu browser. Perlu override 2 controller + 1 response untuk membuatnya kompatibel.
4. **`redirect()->intended()` berbahaya** di multi-interface. Selalu redirect ke URL spesifik per interface.
5. **Selalu trace ke vendor source code** saat debugging. Bug bisa ada di kode yang bukan kita tulis.

---

## 6. Skenario Test

| # | Skenario | Expected |
|---|----------|----------|
| 1 | Admin login di `/admin` → buka `/kasir/login` di tab baru | Tampil login form (bukan auto-login) |
| 2 | Admin login di `/admin` → buka `/dapur/login` di tab baru | Tampil login form |
| 3 | Login `/kasir` dengan email admin | Tampil error: "Akun ini tidak memiliki akses ke Kasir" |
| 4 | Login `/dapur` dengan email kasir | Tampil error: "Akun ini tidak memiliki akses ke Dapur" |
| 5 | Login dengan email tidak terdaftar | Tampil error: "Email atau kata sandi salah" |
| 6 | Logout dari `/kasir` → cek tab `/admin` | Admin tetap login |
| 7 | Logout dari `/dapur` → cek tab `/kasir` | Kasir tetap login |
| 8 | Logout dari `/admin` → cek tab `/kasir` | Kasir tetap login |
| 9 | Admin login di `/admin`, `/kasir`, `/dapur` bersamaan | Ketiganya login, tiga session di DB |
| 10 | Login `/admin` setelah buka `/dapur` | Redirect ke `/admin`, BUKAN ke dapur |
