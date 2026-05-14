# Arsitektur Umum — POS Cafe W9

## Tech Stack Final

| Lapisan | Teknologi | Versi | Keterangan |
|---|---|---|---|
| **Bahasa** | PHP | 8.5.6 | Fitur: property hooks, asymmetric visibility, match expression |
| **Framework Backend** | Laravel | 13.8 | Inertia.js sebagai bridge tunggal ke frontend |
| **Runtime JS** | Node.js | 24.x | Dibutuhkan untuk build Vite |
| **Frontend Framework** | React | 18 | Komponen fungsional + hooks |
| **SPA Bridge** | Inertia.js | v2 | Tidak ada REST API terpisah; controller return `Inertia::render()` |
| **CSS Framework** | Tailwind CSS | v4 | Utility-first dengan CSS variable theming |
| **UI Components** | shadcn/ui | new-york style | Komponen aksesibel, disalin langsung ke proyek |
| **Admin Panel** | Filament | 5.x | Native Laravel, auth guard terpisah |
| **Database** | PostgreSQL | 18 | ACID, JSONB, full-text search |
| **ORM** | Eloquent | (Laravel) | Semua query melalui model, tanpa raw SQL kecuali report kompleks |
| **State Management** | Zustand | 5.x | Ringan, tanpa boilerplate Redux |
| **Offline Storage** | IndexedDB | (browser) | Via library `idb`; persistensi keranjang pelanggan |
| **Auth Operasional** | Laravel Sanctum | session-based | Device Identity + Staff Context |
| **Auth Admin** | Laravel Sanctum | standard guard | Filament pakai auth Laravel bawaan |
| **Build Tool** | Vite | 7.x | Hot module replacement + production build |
| **Web Server** | NGINX | stable | Reverse proxy + static files |
| **Process Manager** | PHP-FPM | 8.5 | FastCGI process manager |
| **Queue Worker** | Supervisor | 4.x | Menjaga queue worker tetap berjalan |
| **Container** | Docker | 27+ | Multi-service: app + pgsql + redis |
| **Testing** | PHPUnit + Playwright | 11.x / 1.x | Unit/feature test + end-to-end browser test |

---

## Unified UI Architecture

Sistem memiliki tiga antarmuka berbeda dalam **satu codebase**:

1. **Kasir** — Desktop web (lebar minimum 1024px), layout sidebar + konten
2. **Dapur** — Desktop/tablet, tampilan antrean pesanan (kitchen display)
3. **Pelanggan** — Mobile PWA (lebar maksimum 430px), bottom navigation

### Strategi Theming

Ketiga antarmuka berbagi komponen yang sama, dibedakan melalui **CSS variable** yang di-scope berdasarkan attribute `data-interface` pada elemen root (`<html>` atau wrapper):

```css
/* resources/css/themes.css */

/* Default: antarmuka kasir (desktop navy) */
:root {
  --color-primary: #3B6FD4;
  --color-primary-hover: #2F5BBF;
  --color-sidebar: #1A2332;
  --color-sidebar-logo: #0F1621;
  --color-sidebar-text: #9AA3AF;
  --color-sidebar-active: #3B6FD4;
  --color-accent: #3B6FD4;
  --color-card-bg: #FFFFFF;
  --color-page-bg: #F8F9FA;
  --color-border: #E9ECEF;
  --color-text-primary: #1A1A2E;
  --color-text-secondary: #6C757D;
  --color-success: #28A745;
  --color-warning: #FFC107;
  --color-danger: #DC3545;
  --radius-card: 12px;
  --radius-button: 8px;
  --radius-chip: 50px;
  --font-family: 'Inter', system-ui, sans-serif;
}

/* Antarmuka pelanggan (mobile orange) */
[data-interface="customer"] {
  --color-primary: #E8692A;
  --color-primary-hover: #D15A1E;
  --color-accent: #E8692A;
  --color-page-bg: #FAFAFA;
  --radius-card: 12px;
  --radius-button: 50px;
  --radius-chip: 50px;
}

/* Antarmuka dapur (industrial green) */
[data-interface="kitchen"] {
  --color-primary: #2D6A4F;
  --color-primary-hover: #1B4332;
  --color-accent: #D4A017;
  --color-page-bg: #1E1E1E;
  --color-card-bg: #2A2A2A;
  --color-text-primary: #E8E8E8;
  --color-text-secondary: #AAAAAA;
  --radius-card: 8px;
  --radius-button: 4px;
  --radius-chip: 4px;
  --font-family: 'JetBrains Mono', monospace;
}
```

### Struktur Komponen

```
resources/js/
├── Components/
│   ├── Common/              ← Komponen yang dipakai semua interface
│   │   ├── StatusBadge.jsx
│   │   ├── LoadingSpinner.jsx
│   │   ├── EmptyState.jsx
│   │   ├── ConfirmDialog.jsx
│   │   └── Toast.jsx
│   ├── Cashier/             ← Komponen khusus antarmuka kasir
│   │   ├── StatBar.jsx
│   │   ├── MenuGridItem.jsx
│   │   ├── KeranjangItem.jsx
│   │   └── OrderCard.jsx
│   ├── Customer/            ← Komponen khusus antarmuka pelanggan
│   │   ├── BottomNav.jsx
│   │   ├── MenuCard.jsx
│   │   ├── CartItem.jsx
│   │   └── RiwayatCard.jsx
│   └── Kitchen/             ← Komponen khusus antarmuka dapur
│       ├── OrderTicket.jsx
│       └── TimerBadge.jsx
├── Layouts/
│   ├── CashierLayout.jsx    ← Sidebar navy + white card content area
│   ├── CustomerLayout.jsx   ← Mobile wrapper + bottom nav
│   └── KitchenLayout.jsx    ← Dark fullscreen + column layout
└── Pages/
    ├── Auth/
    ├── Cashier/
    ├── Customer/
    └── Kitchen/
```

💡 **Prinsip**: Komponen di `Common/` tidak boleh mengimpor dari folder interface lain. Interface-specific component boleh mengimpor dari `Common/`.

---

## Multi-Login Architecture

### Konsep: Device Identity + Staff Context

Sistem operasional (kasir, dapur) **tidak menggunakan autentikasi global** seperti aplikasi web pada umumnya. Sebagai gantinya, sistem menggunakan dua lapis identitas:

```
┌──────────────────────────────────┐
│           DEVICE (perangkat)      │
│  device_sessions                  │
│  ├── device_uuid                  │
│  ├── device_name                  │
│  └── last_seen_at                 │
│                                   │
│  ┌────────────────────────────┐  │
│  │   STAFF CONTEXT (individu) │  │
│  │   active_staff_sessions    │  │
│  │   ├── device_session_id    │  │
│  │   ├── user_id              │  │
│  │   ├── pin_verified_at      │  │
│  │   └── active_context       │  │
│  └────────────────────────────┘  │
│                                   │
│  ┌────────────────────────────┐  │
│  │   STAFF CONTEXT (shift 2)  │  │
│  │   active_staff_sessions    │  │
│  │   ├── device_session_id    │  │
│  │   ├── user_id (staff lain) │  │
│  │   ├── pin_verified_at      │  │
│  │   └── active_context       │  │
│  └────────────────────────────┘  │
└──────────────────────────────────┘
```

**Mengapa tidak pakai `auth()->user()` global?**

Satu perangkat fisik (misalnya laptop kasir) bisa digunakan bergantian oleh beberapa staff dalam satu hari. Auth global Laravel hanya mengenali satu user per session, sehingga tidak cocok untuk skenario multi-staff pada shared workstation.

### Tabel Baru

#### `device_sessions`

```sql
CREATE TABLE device_sessions (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    device_uuid VARCHAR(64) UNIQUE NOT NULL,
    device_name VARCHAR(255),
    last_seen_at TIMESTAMP DEFAULT now(),
    created_at TIMESTAMP DEFAULT now(),
    updated_at TIMESTAMP DEFAULT now()
);
```

#### `active_staff_sessions`

```sql
CREATE TABLE active_staff_sessions (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    device_session_id UUID NOT NULL REFERENCES device_sessions(id) ON DELETE CASCADE,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    pin_verified_at TIMESTAMP DEFAULT now(),
    active_context VARCHAR(50) DEFAULT 'pos',
    created_at TIMESTAMP DEFAULT now(),
    updated_at TIMESTAMP DEFAULT now()
);
```

### Alur Login Operasional

1. Pengguna membuka halaman login di perangkat
2. Sistem mendeteksi atau membuat `device_sessions` berdasarkan device UUID
3. Pengguna memasukkan email + password
4. Sistem memverifikasi kredensial via Laravel auth
5. Jika berhasil: buat record di `active_staff_sessions` untuk user tersebut di device ini
6. Session Laravel menyimpan `device_session_id` (BUKAN `user_id`)
7. Setiap request, middleware membaca `device_session_id` dari session → lookup `active_staff_sessions` → dapatkan `user_id` → set sebagai actor

### Alur Logout

**Logout individu** (staff tertentu keluar, device tetap aktif):

```
DELETE FROM active_staff_sessions WHERE id = ? 
```

**Logout semua** (seluruh staff di device ini keluar):

```
DELETE FROM active_staff_sessions WHERE device_session_id = ?
DELETE FROM device_sessions WHERE id = ?
```

### Filament Admin: Sistem Terpisah

Panel admin Filament **tetap menggunakan Laravel auth bawaan** (`auth()->user()`). Tidak ada device session. Hanya user dengan role `admin` yang bisa mengakses Filament panel. Kedua sistem auth berjalan secara independen.

---

## Aturan Satu Akun Satu Device

Setiap akun staff (kasir/kitchen) hanya bisa login di **satu perangkat pada satu waktu**. Jika staff yang sama mencoba login di perangkat atau tab browser lain, sistem akan menolak.

### Implementasi

Saat login, sistem memeriksa:

```php
// Di DeviceAuthController::login()

// Cek apakah user ini sudah punya active_staff_session di device manapun
$exists = ActiveStaffSession::where('user_id', $user->id)->exists();

if ($exists) {
    return back()->withErrors([
        'email' => 'Akun ini sudah digunakan di perangkat lain. Silakan logout terlebih dahulu.',
    ]);
}
```

Tidak diperlukan BroadcastChannel API atau WebSocket untuk sinkronisasi antar tab karena aturannya sederhana: **satu akun = satu device = satu tab**.

---

## Session Security

### Konfigurasi Cookie

```env
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=strict
SESSION_LIFETIME=480        # 8 jam (satu shift kerja)
```

### Regenerasi Session (Cegah Session Fixation)

Setiap kali login, session ID diregenerasi:

```php
$request->session()->regenerate();
```

### Explicit Actor Context

Setiap aksi dalam sistem yang membutuhkan identitas staff mengirimkan `active_staff_session_id` secara eksplisit. Ini mencegah kebingungan identitas saat multiple staff login di satu device.

```php
// Contoh: membuat pesanan baru
Order::create([
    // ...
    'actor_staff_session_id' => session('active_staff_session_id'),
    'cashier_id' => $actor->user_id,  // lookup dari staff session
]);
```

### Server-Side Revocation List

Saat logout, session dihapus dari tabel `active_staff_sessions` di database. Middleware memeriksa setiap request untuk memastikan session masih valid.

### Yang Tidak Dilakukan

- **Tidak ada token rotation per request** — terlalu kompleks dan tidak perlu untuk shared workstation
- **Tidak ada hard IP binding** — dapat menyebabkan false positive pada mobile hotspot atau jaringan kampus yang sering berganti IP
- **Tidak ada admin terminate manual** — forced logout tidak diperlukan; logout individu sudah cukup

---

## Database

### Model Income (`Income.php`)

Tabel `incomes` sudah ada di database (migrasi `2026_04_11_000008_create_incomes_table`) tetapi belum memiliki model Eloquent. Model akan dibuat dengan struktur:

```php
// app/Models/Income.php
class Income extends Model
{
    protected $fillable = [
        'source',
        'category', 
        'amount',
        'date',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => 'decimal:2',
        ];
    }
}
```

### Kitchen Session Table

Belum ada tabel untuk sesi staff dapur (kitchen). Akan dibuat menggunakan `active_staff_sessions` yang sama karena mekanismenya identik dengan sesi kasir — hanya `active_context` yang berbeda (nilai `kitchen` bukan `pos`).

### FIFO Inventory (FEFO-first)

Sistem inventori sudah mengimplementasikan strategi **FEFO** (First Expired First Out) di `InventoryService`, bukan FIFO murni. FEFO memprioritaskan bahan dengan tanggal kedaluwarsa terdekat:

- Batch dengan `expiry_date` paling awal dipakai terlebih dahulu
- FIFO digunakan sebagai tiebreaker ketika dua batch memiliki tanggal kedaluwarsa yang sama
- Implementasi di `app/Services/InventoryService.php`

### Skema Database Lengkap

Lihat file migrasi di `database/migrations/` untuk definisi lengkap setiap tabel. Tabel-tabel inti:

| Tabel | Deskripsi | Migrasi |
|---|---|---|
| `users` | Semua user (admin, kasir, pelanggan, dapur) | `0001_01_01_000000` |
| `categories` | Kategori menu | `2025_01_01_000011` |
| `menus` | Item menu | `2025_01_01_000012` |
| `cafe_tables` | Meja kafe + QR code | `2025_01_01_000013` |
| `orders` | Pesanan | `2025_01_01_000014` |
| `order_items` | Item dalam pesanan | `2025_01_01_000015` |
| `payments` | Pembayaran | `2025_01_01_000016` |
| `ingredients` | Bahan baku | `2026_04_11_000001` |
| `ingredient_batches` | Batch bahan (dengan expiry) | `2026_04_11_000002` |
| `menu_ingredients` | Relasi menu-bahan | `2026_04_11_000003` |
| `stock_adjustments` | Penyesuaian stok | `2026_04_11_000004` |
| `stock_movements` | Riwayat pergerakan stok | `2026_04_11_000005` |
| `incomes` | Pendapatan non-penjualan | `2026_04_11_000008` |
| `cashier_sessions` | Sesi shift kasir (legacy) | `2026_04_11_000011` |
| `promotions` | Promo/diskon | `2026_04_11_000012` |
| `settings` | Konfigurasi global | `2026_04_01_120401` |
| `daily_ingredient_usages` | Pemakaian harian | `2026_04_15_000001` |

> ⚠️ Tabel `cashier_sessions` yang ada saat ini (dengan kolom `shift_start`, `shift_end`, `total_sales`) akan digantikan oleh `WorkSession` untuk jadwal kerja dan `active_staff_sessions` untuk sesi login. Model `CashierSession.php` akan dipertahankan untuk backward compatibility reporting.

---

## Diagram Alur Request

```
Browser (React + Inertia)
  │
  │  1. User klik navigasi atau submit form
  │
  ▼
Inertia.js Router
  │
  │  2. XHR request ke Laravel route
  │
  ▼
Laravel Route (web.php)
  │
  │  3. Middleware: auth, role, CheckWorkSession
  │
  ▼
Controller
  │
  │  4. Business logic → Eloquent queries
  │
  ▼
PostgreSQL
  │
  │  5. Return data
  │
  ▼
Inertia::render('PageName', $props)
  │
  │  6. JSON response dengan component name + props
  │
  ▼
Inertia.js Client (di React)
  │
  │  7. Mount komponen React yang sesuai
  │
  ▼
React Component (render)
  │
  │  8. Tampil di browser
  │
  ▼
Browser UI
```
