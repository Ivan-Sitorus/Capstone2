# Bagian 3.4 — Login Pelanggan & Verifikasi Akun Mahasiswa

## Customer Login (C4)

### Gambaran Umum

Pelanggan W9 Cafe — yang merupakan mahasiswa STIE Totalwin — login menggunakan **nama lengkap sebagai username** dan **NIM sebagai password**. Ini adalah skema autentikasi non-standar yang disesuaikan dengan kebutuhan cafe kampus: mahasiswa tidak perlu mengingat password terpisah karena NIM sudah unik dan selalu mereka ingat.

Setelah login, mahasiswa bisa memesan menu, mendapat diskon 10% (jika akun sudah diverifikasi kasir), dan melihat riwayat pesanan.

---

### Routes

| Method | Route | Controller | Middleware |
|---|---|---|---|
| GET | `/customer/login` | `CustomerAuthController@showLogin` | `guest` |
| POST | `/customer/login` | `CustomerAuthController@login` | `guest` |
| POST | `/customer/logout` | `CustomerAuthController@logout` | `auth` |

### Controller: CustomerAuthController

```php
// app/Http/Controllers/Customer/CustomerAuthController.php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerLoginRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CustomerAuthController extends Controller
{
    public function showLogin()
    {
        return Inertia::render('Customer/Auth/Login');
    }

    public function login(CustomerLoginRequest $request)
    {
        // Autentikasi dengan name (username) + NIM (password)
        $credentials = [
            'name' => $request->username,
            'role' => 'customer',
        ];

        $user = \App\Models\User::where($credentials)->first();

        if (!$user || $user->nim !== $request->password) {
            return back()->withErrors([
                'username' => 'Nama atau NIM tidak cocok.',
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('customer.menu'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('customer.login');
    }
}
```

### Form Request: CustomerLoginRequest

```php
// app/Http/Requests/CustomerLoginRequest.php

class CustomerLoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:20'],  // NIM max 20 char
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'Nama lengkap wajib diisi.',
            'password.required' => 'NIM wajib diisi.',
        ];
    }
}
```

### Logic Autentikasi

Berbeda dari login kasir yang pakai `email` + `password` standar Laravel, login pelanggan punya logika khusus:

1. Cari user dengan `name = username` DAN `role = 'customer'`
2. Cocokkan field `nim` user dengan value `password` dari form
3. Jika cocok → `Auth::login($user)` → redirect ke menu
4. Jika tidak cocok → error "Nama atau NIM tidak cocok."

**Kenapa tidak pakai Hash::check()?**
Karena password adalah NIM — bukan hash. NIM disimpan sebagai teks biasa di field `nim` (bukan di field `password`). Field `password` di tabel users dipakai oleh Admin dan Kasir (yang login dengan email + password biasa).

**User model untuk customer:**

```php
// Saat seeding atau registrasi customer
User::create([
    'name'     => 'Budi Santoso',
    'email'    => 'budi@student.com',
    'nim'      => '21120122140001',
    'password' => bcrypt('password'),   // tidak dipakai untuk login customer
    'role'     => 'customer',
    'is_student_verified' => false,      // belum diverifikasi
]);
```

---

### Halaman Login (C4)

**File:** `resources/js/Pages/Customer/Auth/Login.jsx`

**Layout:** Mobile-first, max-width 430px, centered di desktop. Tema oranye (`#E8692A`).

**Struktur halaman:**

```
┌──────────────────────────────┐
│                              │
│       ┌──────────┐           │
│       │   w9     │  (logo)   │
│       └──────────┘           │
│       W9 Cafe                │
│       Pemesanan Online       │
│                              │
│ ┌──────────────────────────┐ │
│ │ Login sebagai Mahasiswa  │ │
│ │ Dapatkan diskon 10% untuk│ │
│ │ semua menu!              │ │
│ └──────────────────────────┘ │
│                              │
│   Nama Lengkap               │
│   ┌──────────────────────┐   │
│   │ 👤 Masukkan nama...  │   │
│   └──────────────────────┘   │
│                              │
│   NIM                        │
│   ┌──────────────────────┐   │
│   │ 🔒 Masukkan NIM...   │   │
│   └──────────────────────┘   │
│                              │
│   ┌──────────────────────┐   │
│   │     → Masuk          │   │
│   └──────────────────────┘   │
│                              │
│   Cara Login:                │
│   • Username: nama lengkap   │
│   • Password: NIM Anda       │
│   • Tunjukkan KTM pada       │
│     Kasir untuk verifikasi   │
│     akun.                    │
└──────────────────────────────┘
```

**Desain sesuai CLAUDE.md C4:**
- Background halaman: `#FAFAFA`
- Logo area: kotak rounded 16px (80x80), bg navy `#1A2332`, teks "w9" putih
- Info box mahasiswa: background `#FFF0E8` (orange light), border-radius 12px
- Input fields: ikon User dan Lock dari lucide-react
- Tombol: full-width, bg `#E8692A`, border-radius 8px, height 50px
- Cara Login info: teks 12px abu-abu, highlight pada baris terakhir

**Error state:**
```
┌──────────────────────────────┐
│ ⚠ Nama atau NIM tidak cocok  │
└──────────────────────────────┘
```
Background `#FEF2F2`, border `#FCA5A5`, teks merah.

### Middleware

Customer yang sudah login di-redirect ke menu jika mengakses `/customer/login`. Customer yang belum login di-redirect ke login jika mengakses halaman customer manapun.

```php
// app/Http/Middleware/RedirectIfAuthenticated.php (modifikasi)
public function handle(Request $request, Closure $next, string ...$guards)
{
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->role === 'customer') {
            return redirect()->route('customer.menu');
        }
        if ($user->role === 'cashier') {
            return redirect()->route('cashier.dashboard');
        }
    }

    return $next($request);
}
```

---

## Verifikasi Akun Mahasiswa (K7)

### Gambaran Umum

Mahasiswa yang baru registrasi harus diverifikasi oleh kasir secara manual. Kasir memeriksa KTM (Kartu Tanda Mahasiswa) fisik pelanggan sebelum mengaktifkan akun dan memberikan akses diskon 10%. Halaman verifikasi menampilkan semua akun mahasiswa dalam tabel dengan filter status.

### Routes

| Method | Route | Controller | Middleware |
|---|---|---|---|
| GET | `/cashier/verifikasi` | `CashierVerifikasiController@index` | `auth` + `role:cashier` |
| POST | `/cashier/verifikasi/{id}/approve` | `CashierVerifikasiController@approve` | `auth` + `role:cashier` |
| POST | `/cashier/verifikasi/{id}/reject` | `CashierVerifikasiController@reject` | `auth` + `role:cashier` |

### Controller: CashierVerifikasiController

```php
// app/Http/Controllers/Cashier/CashierVerifikasiController.php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;

class CashierVerifikasiController extends Controller
{
    public function index()
    {
        $customers = User::where('role', 'customer')
            ->when(request('status'), fn($q, $status) => $q->where('is_student_verified', $status === 'disetujui'))
            ->when(request('search'), fn($q, $search) =>
                $q->where(function($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                      ->orWhere('nim', 'ilike', "%{$search}%");
                })
            )
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'semua'     => User::where('role', 'customer')->count(),
            'menunggu'  => User::where('role', 'customer')->where('is_student_verified', false)->count(),
            'disetujui' => User::where('role', 'customer')->where('is_student_verified', true)->count(),
            'ditolak'   => User::where('role', 'customer')->where('is_student_verified', false)->count(), // Same as menunggu for now
        ];

        return Inertia::render('Cashier/VerifikasiAkun', compact('customers', 'counts'));
    }

    public function approve(User $user)
    {
        $user->update(['is_student_verified' => true]);

        return back()->with('success', "Akun {$user->name} berhasil disetujui.");
    }

    public function reject(User $user)
    {
        // Rejection: tetap keep akun, hanya catat sebagai ditolak
        // Untuk saat ini, tidak ada field rejected_at — cukup tetap false
        // Bisa tambahkan notes di log atau field notes nanti

        return back()->with('success', "Akun {$user->name} ditolak.");
    }
}
```

### Halaman Verifikasi (K7)

**File:** `resources/js/Pages/Cashier/VerifikasiAkun.jsx`

**Layout:** Dalam CashierLayout (sidebar navy + white card).

**Struktur:**

```
┌──────────────────────────────────────────────────────┐
│  Verifikasi Akun Mahasiswa                          │
│  Kelola dan verifikasi pendaftaran akun pelanggan    │
│                                                      │
│  [🕐 5 Menunggu]  [✓ 12 Disetujui]                  │
│                                                      │
│  [🔍 Cari nama atau NIM...]  [Semua|Menunggu|Disetujui|Ditolak] │
│                                                      │
│  ┌────────────────────────────────────────────────┐  │
│  │ No │ Nama         │ NIM          │ Tgl Daftar │ Status │ Aksi │
│  ├────────────────────────────────────────────────┤  │
│  │ 1  │ Budi Santoso │ 21120122140001 │ 22 Feb    │ 🟡 Menunggu │ Setujui Tolak │
│  │ 2  │ Ani Rahmawati│ 21120122140002 │ 21 Feb    │ 🟢 Disetujui │ Detail │
│  │ 3  │ Cici Indah   │ 21120122140003 │ 20 Feb    │ 🔴 Ditolak   │ Detail │
│  └────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────┘
```

### Tabel Kolom

| Kolom | Data | Style |
|---|---|---|
| No | Nomor urut | 14px, abu-abu |
| Nama | `user.name` | 14px, bold, `#1A1A2E` |
| NIM | `user.nim` | 14px, monospace |
| Tgl Daftar | `user.created_at` | 14px, abu-abu |
| Status | `<StatusBadge>` | `menunggu` / `disetujui` / `ditolak` |
| Aksi | `Setujui` + `Tolak` (menunggu) atau `Detail` | Teks, no border |

### Status Badges

Menggunakan komponen `StatusBadge` yang sudah ada:

```jsx
<StatusBadge status="menunggu" />   // Kuning, dot #D4A64A
<StatusBadge status="disetujui" />  // Hijau, dot #4D9B6A
<StatusBadge status="ditolak" />    // Merah, dot #C95D4A
```

Aksi untuk status "Menunggu":
- **Setujui** — teks hijau (`#28A745`), cursor pointer. Klik → POST approve, reload halaman.
- **Tolak** — teks merah (`#DC3545`), cursor pointer. Klik → POST reject, reload halaman.

Aksi untuk status lainnya:
- **Detail** — teks biru (`#3B6FD4`), link ke halaman detail user (atau saat ini hanya info minimal).

### Filter Bar

**Search input:**
- Placeholder: "Cari nama atau NIM..."
- Lebar: 300px
- Input debounce → query server-side via Inertia reload

**Status tabs (pill):**
```
[Semua (5)]  [Menunggu (2)]  [Disetujui (2)]  [Ditolak (1)]
```

Setiap tab menerapkan query param `status`:
- Semua → no filter
- Menunggu → `is_student_verified = false`
- Disetujui → `is_student_verified = true`
- Ditolak → (untuk saat ini mapping ke `is_student_verified = false` juga, atau butuh field tambahan `verified_status` enum)

**Implementasi tab filter:**

```jsx
const tabs = [
  { key: '',         label: 'Semua',      count: counts.semua },
  { key: 'menunggu',  label: 'Menunggu',   count: counts.menunggu },
  { key: 'disetujui', label: 'Disetujui',  count: counts.disetujui },
  { key: 'ditolak',   label: 'Ditolak',    count: counts.ditolak },
];

function FilterTabs({ current }) {
  return (
    <div className="flex gap-2">
      {tabs.map(tab => (
        <Link
          key={tab.key}
          href={`/cashier/verifikasi?status=${tab.key}`}
          preserveState
          className={`rounded-full px-4 py-1.5 text-sm font-medium transition
            ${current === tab.key
              ? 'bg-[#3B6FD4] text-white'
              : 'bg-gray-100 text-gray-500 hover:bg-gray-200'
            }`}
        >
          {tab.label} ({tab.count})
        </Link>
      ))}
    </div>
  );
}
```

---

### Flow Verifikasi End-to-End

```
Mahasiswa            Kasir                  Sistem
    │                  │                      │
    │  Daftar/Login    │                      │
    │───────────────→  │                      │
    │                  │                      │   is_student_verified = false
    │                  │                      │   Tidak dapat diskon 10%
    │                  │                      │
    │  Tunjukkan KTM   │                      │
    │───────────────→  │                      │
    │                  │                      │
    │                  │  Buka /cashier/      │
    │                  │  verifikasi          │
    │                  │──────────→           │
    │                  │           ←──────────│  Tampilkan daftar
    │                  │                      │  status "Menunggu"
    │                  │                      │
    │                  │  "Setujui"           │
    │                  │──────────→           │
    │                  │           ←──────────│  is_student_verified = true
    │                  │                      │  Diskon 10% aktif
    │                  │                      │
    │  Sekarang dapat  │                      │
    │  diskon 10%      │                      │
    │←─────────────────│                      │
```

---

### Dependency dengan Fitur Promosi

Fitur verifikasi mahasiswa HARUS selesai sebelum fitur **diskon mahasiswa 10%** bisa berfungsi. 

Logika diskon di order:

```php
// Di OrderService atau CustomerMenuController
if ($user->is_student_verified) {
    $discountPercent = 10; // 10%
    $discountAmount = $total * 0.10;
    $totalAfterDiscount = $total - $discountAmount;
}
```

Field `is_student_verified` di model User adalah gate untuk seluruh fitur diskon mahasiswa.

---

### Status "Ditolak" — Catatan

Saat ini model User hanya punya boolean `is_student_verified`. Untuk membedakan antara "Menunggu" (baru daftar, belum dicek) dan "Ditolak" (sudah dicek, KTM tidak valid), perlu tambahan:

**Opsi 1: Enum field baru**

```php
// Migration
$table->string('verification_status')->default('menunggu');
// ENUM('menunggu', 'disetujui', 'ditolak')
```

**Opsi 2: Reuse boolean + timestamps**

```php
is_student_verified: false + verified_at: null → Menunggu
is_student_verified: true  + verified_at: not null → Disetujui
is_student_verified: false + verified_at: not null → Ditolak
```

**Opsi 2 lebih ringan** karena tidak perlu migration baru — cukup tambahkan field `verified_at` yang sudah direncanakan.

---

## Checklist Implementasi

### C4 — Customer Login
- [ ] Route `GET /customer/login` dan `POST /customer/login`
- [ ] `CustomerAuthController` dengan `showLogin()` dan `login()`
- [ ] `CustomerLoginRequest` form validation
- [ ] Logic autentikasi: name + NIM (bukan email + password)
- [ ] Middleware redirect jika sudah login
- [ ] Page `Customer/Auth/Login.jsx` sesuai desain CLAUDE.md C4
- [ ] Tema oranye, logo, info box, form dengan icons
- [ ] Error state: "Nama atau NIM tidak cocok."
- [ ] Info "Cara Login" di bagian bawah
- [ ] Test: login berhasil, login gagal (nama salah, NIM salah)
- [ ] Test: redirect jika sudah login

### K7 — Verifikasi Akun Mahasiswa
- [ ] Route `GET /cashier/verifikasi`
- [ ] Route `POST /cashier/verifikasi/{id}/approve`
- [ ] Route `POST /cashier/verifikasi/{id}/reject`
- [ ] `CashierVerifikasiController`
- [ ] Page `Cashier/VerifikasiAkun.jsx`
- [ ] Tabel: No, Nama, NIM, Tgl Daftar, Status, Aksi
- [ ] StatusBadge: menunggu (kuning), disetujui (hijau), ditolak (merah)
- [ ] Filter tabs: Semua | Menunggu | Disetujui | Ditolak
- [ ] Search by nama atau NIM
- [ ] Aksi "Setujui" dan "Tolak" untuk status menunggu
- [ ] Aksi "Detail" untuk status lainnya
- [ ] Konfirmasi sebelum approve/reject (opsional)
- [ ] Field `verified_at` untuk membedakan menunggu vs ditolak
- [ ] Test: approve, reject, filter, search, pagination

### Cross-cutting
- [ ] Fitur diskon mahasiswa 10% depend pada `is_student_verified`
- [ ] Verifikasi selesai SEBELUM implementasi promosi `is_student_only`
- [ ] Dokumentasi flow verifikasi untuk training kasir
