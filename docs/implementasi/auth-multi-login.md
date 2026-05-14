# Autentikasi Multi-Login — POS Cafe W9

## Konsep Dasar

Sistem operasional POS Cafe W9 menggunakan arsitektur autentikasi **Device Identity + Staff Context** yang berbeda dari autentikasi web pada umumnya. Satu perangkat fisik (laptop, tablet) bisa digunakan oleh beberapa staff secara bergantian tanpa harus logout dari browser.

Komponen utama:

| Komponen | Tabel | Fungsi |
|---|---|---|
| **Device Identity** | `device_sessions` | Mengenali perangkat fisik melalui UUID |
| **Staff Context** | `active_staff_sessions` | Merekam staff mana yang sedang login di device tersebut |

Laravel session hanya menyimpan `device_session_id`, **bukan** `user_id`. Identitas staff di-resolve secara eksplisit setiap request melalui middleware.

---

## DeviceAuthController

File: `app/Http/Controllers/Auth/DeviceAuthController.php`

### Dependensi

```php
use App\Models\ActiveStaffSession;
use App\Models\DeviceSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Inertia\Inertia;
```

### `login()` — Verifikasi Kredensial & Buat Staff Session

```php
public function login(Request $request)
{
    $request->validate([
        'email'    => 'required|email',
        'password' => 'required',
    ]);

    // Rate limiting per email + IP
    $key = Str::lower($request->email) . '|' . $request->ip();

    if (RateLimiter::tooManyAttempts($key, 5)) {
        $seconds = RateLimiter::availableIn($key);
        return back()->withErrors([
            'email' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
        ]);
    }

    // Verifikasi kredensial
    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        RateLimiter::hit($key, 60);
        return back()->withErrors(['email' => 'Email atau kata sandi salah.']);
    }

    // Hanya staff (kasir, kitchen) yang bisa login ke sistem operasional
    if (!in_array($user->role, ['cashier', 'kitchen'])) {
        return back()->withErrors(['email' => 'Akun ini tidak memiliki akses ke sistem operasional.']);
    }

    // Cek aturan satu akun satu device
    $existingSession = ActiveStaffSession::where('user_id', $user->id)->first();

    if ($existingSession) {
        return back()->withErrors([
            'email' => 'Akun ini sudah aktif di perangkat lain. Silakan logout dari perangkat sebelumnya terlebih dahulu.',
        ]);
    }

    // Dapatkan atau buat device session
    $deviceUuid = $request->cookie('device_uuid') ?? (string) Str::uuid();
    $deviceSession = DeviceSession::firstOrCreate(
        ['device_uuid' => $deviceUuid],
        ['device_name' => $request->header('User-Agent')]
    );

    // Update last_seen
    $deviceSession->touch();

    // Buat staff session
    $staffSession = ActiveStaffSession::create([
        'device_session_id' => $deviceSession->id,
        'user_id'           => $user->id,
        'pin_verified_at'   => now(),
        'active_context'    => $user->role === 'kitchen' ? 'kitchen' : 'pos',
    ]);

    // Regenerasi session Laravel (cegah fixation)
    $request->session()->regenerate();

    // Simpan hanya device_session_id di Laravel session
    $request->session()->put('device_session_id', $deviceSession->id);
    $request->session()->put('active_staff_session_id', $staffSession->id);

    // Set cookie device_uuid (tahan 1 tahun)
    RateLimiter::clear($key);

    // Redirect sesuai role
    return $user->role === 'kitchen'
        ? Inertia::location(route('kitchen.display'))
        : Inertia::location(route('cashier.dashboard'));
}
```

### `logout()` — Hapus Sesi Staff

Mendukung dua mode: logout individu (satu staff) atau logout semua (seluruh staff di device).

```php
public function logout(Request $request)
{
    $staffSessionId = $request->input('staff_session_id');

    if ($staffSessionId) {
        // Logout individu: hapus satu active_staff_session
        ActiveStaffSession::where('id', $staffSessionId)->delete();

        // Cek apakah masih ada staff lain yang login di device ini
        $deviceSessionId = $request->session()->get('device_session_id');
        $remaining = ActiveStaffSession::where('device_session_id', $deviceSessionId)->count();

        if ($remaining > 0) {
            // Masih ada staff lain, arahkan ke halaman pilih staff
            return redirect()->route('auth.select-staff');
        }
    }

    // Logout semua: hapus seluruh staff session + device session
    $deviceSessionId = $request->session()->get('device_session_id');

    if ($deviceSessionId) {
        ActiveStaffSession::where('device_session_id', $deviceSessionId)->delete();
        DeviceSession::where('id', $deviceSessionId)->delete();
    }

    // Hapus session Laravel
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
}
```

### `switchStaff()` — Ganti Konteks Staff Aktif

Digunakan ketika device memiliki beberapa staff yang login dan perlu berganti konteks (misalnya dari mode POS ke mode kitchen, atau dari satu staff ke staff lain).

```php
public function switchStaff(Request $request)
{
    $request->validate([
        'staff_session_id' => 'required|exists:active_staff_sessions,id',
    ]);

    $deviceSessionId = $request->session()->get('device_session_id');

    // Pastikan session milik device ini
    $staffSession = ActiveStaffSession::where('id', $request->staff_session_id)
        ->where('device_session_id', $deviceSessionId)
        ->firstOrFail();

    // Update konteks aktif
    $request->session()->put('active_staff_session_id', $staffSession->id);

    $user = User::find($staffSession->user_id);

    return back()->with('success', "Sekarang aktif sebagai: {$user->name}");
}
```

### `activeStaff()` — Daftar Staff Aktif di Device Ini

```php
public function activeStaff(Request $request)
{
    $deviceSessionId = $request->session()->get('device_session_id');

    if (!$deviceSessionId) {
        return response()->json(['staff' => []]);
    }

    $staffSessions = ActiveStaffSession::with('user')
        ->where('device_session_id', $deviceSessionId)
        ->get()
        ->map(fn ($s) => [
            'id'              => $s->id,
            'name'            => $s->user->name,
            'role'            => $s->user->role,
            'active_context'  => $s->active_context,
            'is_current'      => $s->id === $request->session()->get('active_staff_session_id'),
            'pin_verified_at' => $s->pin_verified_at,
        ]);

    return response()->json(['staff' => $staffSessions]);
}
```

### `showSelectStaff()` — Halaman Pilih Staff

Ditampilkan saat ada beberapa staff login di satu device, untuk memilih staff mana yang aktif.

```php
public function showSelectStaff(Request $request)
{
    $deviceSessionId = $request->session()->get('device_session_id');

    $staff = ActiveStaffSession::with('user')
        ->where('device_session_id', $deviceSessionId)
        ->get()
        ->map(fn ($s) => [
            'id'   => $s->id,
            'name' => $s->user->name,
            'role' => $s->user->role,
        ]);

    return Inertia::render('Auth/SelectStaff', [
        'staff' => $staff,
    ]);
}
```

---

## Middleware: ResolveActor

Setiap request yang membutuhkan identitas staff diproses melalui middleware `ResolveActor`:

```php
// app/Http/Middleware/ResolveActor.php

namespace App\Http\Middleware;

use App\Models\ActiveStaffSession;
use Closure;
use Illuminate\Http\Request;

class ResolveActor
{
    public function handle(Request $request, Closure $next)
    {
        $staffSessionId = $request->session()->get('active_staff_session_id');

        if ($staffSessionId) {
            $staffSession = ActiveStaffSession::with('user')->find($staffSessionId);

            if ($staffSession) {
                // Injek identitas staff ke request
                $request->merge([
                    'actor_staff_session_id' => $staffSession->id,
                    'actor_user_id'          => $staffSession->user_id,
                ]);

                // Optional: share ke semua view Inertia
                \Inertia\Inertia::share('actor', [
                    'id'   => $staffSession->user->id,
                    'name' => $staffSession->user->name,
                    'role' => $staffSession->user->role,
                ]);
            }
        }

        return $next($request);
    }
}
```

### Registrasi Middleware

```php
// bootstrap/app.php atau app/Http/Kernel.php

->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'resolve.actor' => \App\Http\Middleware\ResolveActor::class,
    ]);
})
```

### Penggunaan di Route

```php
Route::prefix('cashier')->middleware(['auth', 'resolve.actor'])->group(function () {
    Route::get('/dashboard', [CashierDashboardController::class, 'index']);
    // ...
});
```

---

## Routing Auth

File: `routes/web.php` (tambahan untuk DeviceAuth)

```php
use App\Http\Controllers\Auth\DeviceAuthController;

// Halaman login operasional (tampilan baru)
Route::get('/login', [DeviceAuthController::class, 'showLogin'])->name('login');

// Proses login
Route::post('/login', [DeviceAuthController::class, 'login'])->name('login.attempt');

// Logout
Route::post('/logout', [DeviceAuthController::class, 'logout'])->name('logout');

// Halaman pilih staff (jika multi-staff login di satu device)
Route::get('/select-staff', [DeviceAuthController::class, 'showSelectStaff'])
    ->name('auth.select-staff')
    ->middleware('resolve.actor');

// Switch staff aktif
Route::post('/switch-staff', [DeviceAuthController::class, 'switchStaff'])
    ->name('auth.switch-staff')
    ->middleware('resolve.actor');

// API: daftar staff aktif (untuk polling UI)
Route::get('/api/active-staff', [DeviceAuthController::class, 'activeStaff'])
    ->name('api.active-staff')
    ->middleware('resolve.actor');
```

> ⚠️ `AuthController` yang lama (di `app/Http/Controllers/Auth/AuthController.php`) akan tetap dipertahankan untuk backward compatibility selama masa transisi. Setelah `DeviceAuthController` stabil, `AuthController` akan dihapus.

---

## Perbedaan dengan Auth Bawaan Laravel

| Aspek | Auth Bawaan Laravel | DeviceAuth (sistem ini) |
|---|---|---|
| **Yang disimpan di session** | `user_id` | `device_session_id` + `active_staff_session_id` |
| **Jumlah user per session** | 1 | Bisa lebih dari 1 (multi-staff) |
| **Logout** | Hapus semua session user | Bisa logout individu atau semua |
| **`auth()->user()`** | Tersedia | **Tidak digunakan**; pakai `actor_user_id` dari request |
| **Guard** | `web` (session) | `web` (session) — tapi tidak pakai guard user |
| **Rate limiting** | Default Laravel | Custom: email + IP, 5 percobaan, 60 detik cooldown |

---

## Edge Cases & Penanganan

### 1. Concurrent Tabs (Tab Ganda)

**Aturan**: Satu akun staff hanya bisa login di satu tab browser.

**Penanganan**:
- Saat login, `ActiveStaffSession::where('user_id', $user->id)->exists()` akan mengembalikan `true` jika staff sudah login di tab lain
- Sistem menolak dengan pesan error: *"Akun ini sudah aktif di perangkat lain."*
- Staff harus logout dari tab sebelumnya untuk bisa login di tab baru
- Tidak diperlukan WebSocket atau BroadcastChannel untuk deteksi

### 2. Session Hijack (Pembajakan Session)

**Penanganan berlapis**:

| Lapisan | Mekanisme |
|---|---|
| Cookie | `HttpOnly`, `Secure`, `SameSite=Strict` |
| Session ID | Diregenerasi setiap login (`$request->session()->regenerate()`) |
| Server-side check | Setiap request: middleware verifikasi `active_staff_session` masih ada di database |
| Revocation | Saat logout, record dihapus dari database → middleware akan menolak request berikutnya |

### 3. Offline PWA

**Status**: Didefer (ditunda untuk fase selanjutnya).

Saat ini sistem operasional membutuhkan koneksi internet. Offline support untuk antarmuka pelanggan (cart via IndexedDB) sudah diimplementasikan. Offline support untuk antarmuka kasir/kitchen akan dievaluasi di fase berikutnya dengan mempertimbangkan kompleksitas sinkronisasi data pesanan.

### 4. Forced Logout (Terminasi Paksa oleh Admin)

**Status**: Tidak diimplementasikan.

Tidak ada fitur admin untuk memaksa staff logout. Alasannya:
- Logout individu sudah cukup untuk rotasi staff
- Menambah tombol "paksa logout" di panel admin berpotensi disalahgunakan atau menyebabkan kebingungan
- Jika benar-benar diperlukan di masa depan, bisa ditambahkan endpoint admin yang menghapus `active_staff_sessions` untuk user tertentu

### 5. Session Expiry (Kedaluwarsa Sesi)

Session memiliki masa berlaku 8 jam (satu shift kerja). Jika session expired:
- Middleware Laravel akan me-redirect ke halaman login
- `active_staff_sessions` tetap ada di database (akan dibersihkan oleh scheduled job harian)
- Staff login ulang: sistem mendeteksi `active_staff_session` yang sudah expired → hapus → buat baru

### 6. Browser Refresh / Restart

Saat browser di-refresh atau direstart:
- Cookie `device_uuid` tetap ada (masa berlaku 1 tahun)
- Laravel session mungkin hilang (tergantung driver; file/database session bertahan)
- Jika session hilang, device dikenali ulang via `device_uuid` cookie → dibuatkan `device_session` baru
- Staff harus login kembali

---

## Model Eloquent

### DeviceSession

```php
// app/Models/DeviceSession.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class DeviceSession extends Model
{
    use HasUuids;

    protected $fillable = [
        'device_uuid',
        'device_name',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'last_seen_at' => 'datetime',
        ];
    }

    public function activeStaffSessions()
    {
        return $this->hasMany(ActiveStaffSession::class);
    }
}
```

### ActiveStaffSession

```php
// app/Models/ActiveStaffSession.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ActiveStaffSession extends Model
{
    use HasUuids;

    protected $fillable = [
        'device_session_id',
        'user_id',
        'pin_verified_at',
        'active_context',
    ];

    protected function casts(): array
    {
        return [
            'pin_verified_at' => 'datetime',
        ];
    }

    public function deviceSession()
    {
        return $this->belongsTo(DeviceSession::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('pin_verified_at', '>=', now()->subHours(8));
    }
}
```

---

## Migration

### `device_sessions`

```php
// database/migrations/xxxx_xx_xx_create_device_sessions_table.php

Schema::create('device_sessions', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('device_uuid', 64)->unique();
    $table->string('device_name', 255)->nullable();
    $table->timestamp('last_seen_at')->useCurrent();
    $table->timestamps();

    $table->index('device_uuid');
});
```

### `active_staff_sessions`

```php
// database/migrations/xxxx_xx_xx_create_active_staff_sessions_table.php

Schema::create('active_staff_sessions', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('device_session_id')
        ->constrained('device_sessions')
        ->cascadeOnDelete();
    $table->foreignId('user_id')
        ->constrained('users')
        ->cascadeOnDelete();
    $table->timestamp('pin_verified_at')->useCurrent();
    $table->string('active_context', 50)->default('pos');
    $table->timestamps();

    $table->unique(['device_session_id', 'user_id']);
    $table->index('user_id');
});
```

> 💡 Unique constraint pada `(device_session_id, user_id)` mencegah staff yang sama login dua kali di device yang sama.
