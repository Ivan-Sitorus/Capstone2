# Bagian 3.1 — Migrasi Bootstrap 5 ke Tailwind CSS v4 + shadcn/ui

## Gambaran Umum

Proyek W9 Cafe POS saat ini memakai Bootstrap 5.3 yang hanya diimport di satu file (`CashierLayout.jsx`), sementara **seluruh 26 komponen React menggunakan 100% inline styles** — tanpa satupun pemakaian `className`. Ini adalah hasil dari pendekatan *fast iteration* di fase awal. Fase migrasi ini akan mengganti Bootstrap dengan Tailwind CSS v4 (yang sudah terinstal untuk panel Filament admin) dan menambahkan shadcn/ui untuk komponen UI yang konsisten.

Anggaran: **1 sprint (2 minggu)** untuk full migration + verifikasi visual.

---

## Kondisi Saat Ini

| Aspek | Status |
|---|---|
| Bootstrap 5.3 | Terinstall, tapi hanya 1 import di `CashierLayout.jsx` baris 3 |
| Penggunaan `className` | Nol di semua komponen React |
| Semua styling | 100% inline `style={{ }}` objects |
| Tailwind CSS v4 | Sudah terinstall via `@tailwindcss/vite` dan `tailwindcss` v4 |
| shadcn/ui | Belum terinstall |
| Filament Admin | Tetap, tidak terpengaruh migrasi ini |
| CSS Variables | Ada di `resources/css/app.css` dengan custom properties (`--orange-primary`, `--navy-sidebar`, dll) |

### Daftar Komponen yang Perlu Dimigrasi

**Layouts (2 file):**
- `resources/js/Layouts/CashierLayout.jsx` — Sidebar navy, toast, main content wrapper
- `resources/js/Layouts/CustomerLayout.jsx` — Mobile wrapper, bottom nav

**Cashier Pages (6 file):**
- `Dashboard.jsx`
- `PesananBaru.jsx`
- `PesananAktif.jsx`
- `RiwayatPesanan.jsx`
- `Order/Show.jsx`
- `Profil.jsx`

**Customer Pages (8 file):**
- `Menu/Index.jsx`
- `Cart/Index.jsx`
- `Riwayat/Index.jsx`
- `Payment/Choose.jsx`
- `Payment/QrisUpload.jsx`
- `Payment/QrisStatus.jsx`
- `Payment/CashStatus.jsx`
- `Order/Status.jsx`

**Cashier Components (4 file):**
- `StatBar.jsx`
- `MenuGridItem.jsx`
- `KeranjangItem.jsx`
- `OrderCard.jsx`

**Customer Components (5 file):**
- `BottomNav.jsx`
- `CategoryChip.jsx`
- `MenuCard.jsx`
- `CartItem.jsx`
- `RiwayatCard.jsx`

**Common (1 file):**
- `StatusBadge.jsx` — *Sebagian besar tetap inline* karena warnanya dinamis dari `statusMap`

**Auth (1 file):**
- `Auth/Login.jsx`

**Total: 26 file React + 1 file CSS**

---

## Langkah Migrasi

### Tahap 1: Setup (1 hari)

#### 1.1 Install shadcn/ui

```bash
npx shadcn@latest init
```

Pilih konfigurasi:
- `rsc: false` (React Server Components tidak dipakai — kita pakai Inertia client-side)
- `cssVariables: true` (pakai CSS custom properties untuk theming)
- Style: `default`
- Base color: `slate`
- Path alias: `@/` → `resources/js/`
- CSS file: `resources/css/app.css`
- Output dir: `resources/js/components/ui`

#### 1.2 Install komponen shadcn yang dibutuhkan

```bash
npx shadcn@latest add button card badge table tabs dialog sheet form input select
```

Komponen ini akan muncul di `resources/js/components/ui/` dan bisa diimport langsung:
```tsx
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
// dst.
```

#### 1.3 Buat utility `cn()`

Bikin file `resources/js/lib/utils.ts`:

```ts
import { type ClassValue, clsx } from "clsx";
import { twMerge } from "tailwind-merge";

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}
```

Fungsi `cn()` dipakai untuk merge class Tailwind secara aman, khususnya saat class bisa konflik.

#### 1.4 Siapkan Tailwind di `app.css`

Timpa `resources/css/app.css` dengan:

```css
@import "tailwindcss";

[data-interface="cashier"] {
  --primary: #3B6FD4;
  --primary-foreground: #FFFFFF;
  --sidebar: #1A2332;
}

[data-interface="customer"] {
  --primary: #E8692A;
  --primary-foreground: #FFFFFF;
  --nav-height: 60px;
}

[data-interface="kitchen"] {
  --primary: #28A745;
  --primary-foreground: #FFFFFF;
  --card-bg: #F8F9FA;
}
```

Setiap layout akan men-set `data-interface` attribute di root element-nya, sehingga CSS variables otomatis berganti per interface.

#### 1.5 Hapus Bootstrap

```bash
npm uninstall bootstrap @popperjs/core
```

Lalu hapus baris 3 dari `CashierLayout.jsx`:
```jsx
import 'bootstrap/dist/css/bootstrap.min.css';  // HAPUS baris ini
```

---

### Tahap 2: Konversi Per File (8 hari — paralel 2-3 orang)

#### Pendekatan Konversi

Satu file per commit. Setiap commit punya **Playwright screenshot comparison** sebelum dan sesudah untuk memastikan tidak ada regresi visual.

**Pattern konversi utama:**

| Inline Style | Tailwind Class |
|---|---|
| `style={{ padding: '24px' }}` | `className="p-6"` |
| `style={{ background: '#FFFFFF' }}` | `className="bg-white"` |
| `style={{ borderRadius: 12 }}` | `className="rounded-xl"` (12px = rounded-xl) |
| `style={{ fontSize: 14 }}` | `className="text-sm"` |
| `style={{ fontWeight: 700 }}` | `className="font-bold"` |
| `style={{ display: 'flex' }}` | `className="flex"` |
| `style={{ gap: 12 }}` | `className="gap-3"` |
| `style={{ color: '#64748B' }}` | `className="text-slate-500"` |
| `style={{ border: '1px solid #E2E8F0' }}` | `className="border border-slate-200"` |
| `style={{ boxShadow: '0 2px 8px rgba(15,23,42,0.03)' }}` | `className="shadow-sm"` |

**Yang TETAP inline:**
- Warna dinamis dari `statusMap` (seperti di `StatusBadge.jsx`) — karena nilainya datang dari object, bukan static
- Nilai computed yang tidak bisa jadi static class (misal: `width: sidebarWidth` yang dari state)

**Contoh hasil konversi — Dashboard header:**

Sebelum:
```jsx
<div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 28 }}>
    <div>
        <h1 style={{ fontSize: 26, fontWeight: 700, color: '#0F172A', margin: '0 0 4px' }}>
            Dashboard
        </h1>
        <p style={{ fontSize: 14, color: '#64748B', margin: 0 }}>
            Selamat datang, Kasir! Berikut ringkasan hari ini.
        </p>
    </div>
</div>
```

Sesudah:
```jsx
<div className="flex justify-between items-start mb-7">
    <div>
        <h1 className="text-2xl font-bold text-slate-900 mb-1">
            Dashboard
        </h1>
        <p className="text-sm text-slate-500">
            Selamat datang, Kasir! Berikut ringkasan hari ini.
        </p>
    </div>
</div>
```

#### Urutan Konversi yang Direkomendasikan

1. **Common components dulu** (`StatusBadge`, helpers) — karena dipakai di banyak tempat
2. **Layouts** (`CashierLayout`, `CustomerLayout`) — fondasi semua halaman
3. **Cashier components** (`StatBar`, `MenuGridItem`, etc.)
4. **Customer components** (`BottomNav`, `MenuCard`, etc.)
5. **Cashier pages** mulai dari yang paling sederhana (`Profil` → `Dashboard` → `RiwayatPesanan` → `PesananAktif` → `PesananBaru` → `Order/Show`)
6. **Customer pages** (`Menu` → `Cart` → `Riwayat` → `Payment/*` → `Order/Status`)
7. **Auth page** (`Login`)

---

### Tahap 3: Verifikasi Visual (2 hari)

#### Screenshot Comparison

Setiap halaman di-screenshot dengan Playwright **sebelum dan sesudah migrasi**:

```typescript
// e2e/visual/regression.spec.ts
test('Cashier Dashboard visual regression', async ({ page }) => {
  await page.goto('/cashier/dashboard');
  await expect(page).toHaveScreenshot('dashboard.png', {
    threshold: 0.01, // toleransi 1% perbedaan pixel
  });
});
```

#### Checklist Verifikasi Per Halaman

- [ ] Cashier Login — split screen kiri/kanan, form, error state
- [ ] Cashier Dashboard — header, stat bar, quick actions, tabel transaksi
- [ ] Cashier Pesanan Baru — 3 panel, grid menu, keranjang
- [ ] Cashier Pesanan Aktif — filter tabs, order cards grid
- [ ] Cashier Riwayat — filter bar, tabel, status badges
- [ ] Cashier Order Detail — 2 kolom, item list, info card
- [ ] Cashier Profil — avatar, info fields
- [ ] Customer Menu — search, chips, menu grid
- [ ] Customer Cart — item list, footer summary
- [ ] Customer Riwayat — filter tabs, card list
- [ ] Customer Login — logo, info box, form
- [ ] Customer Payment pages — upload, status

---

## CSS Variable Theming

Tiga interface (Cashier, Customer, Kitchen) memakai sistem `data-interface` attribute:

```css
/* Default (fallback) */
:root {
  --primary: #3B6FD4;
  --radius: 0.5rem;
}

/* Cashier — biru navy */
[data-interface="cashier"] {
  --primary: #3B6FD4;
  --primary-foreground: #FFFFFF;
  --sidebar: #1A2332;
}

/* Customer — oranye */
[data-interface="customer"] {
  --primary: #E8692A;
  --primary-foreground: #FFFFFF;
  --nav-height: 60px;
}

/* Kitchen — hijau, dark theme default */
[data-interface="kitchen"] {
  --primary: #28A745;
  --primary-foreground: #FFFFFF;
  --card-bg: #1E293B;
  --background: #0F172A;
  --foreground: #F8FAFC;
}
```

Setiap layout wrapper men-set attribute ini:
- `CashierLayout` → `<div data-interface="cashier">`
- `CustomerLayout` → `<div data-interface="customer">`
- `KitchenLayout` (baru) → `<div data-interface="kitchen">`

shadcn/ui akan otomatis menggunakan variable `--primary` dari theme yang aktif karena diinisialisasi dengan `cssVariables: true`.

---

## Responsive Design

### Breakpoints Tailwind yang Dipakai

| Breakpoint | Min Width | Digunakan Untuk |
|---|---|---|
| `sm` | 640px | Tablet kecil |
| `md` | 768px | Tablet |
| `lg` | 1024px | Desktop kecil / Kitchen |
| `xl` | 1280px | Desktop besar / Cashier sidebar |

### Interface Cashier (Desktop)

- Min width: 1024px (`lg:`)
- Sidebar: fixed 210px (`w-[210px]`)
- Content: `flex-1` dengan padding `p-8`
- Tidak ada mobile layout untuk kasir

### Interface Kitchen (Desktop/Tablet)

- Card grid: `grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4`
- Font ukuran besar (readable dari jarak 2 meter di dapur): `text-lg` minimum
- Touch targets: min 44px untuk semua tombol

### Interface Customer (Mobile-first)

- Maksimum lebar konten: `max-w-[430px] mx-auto`
- Di desktop: tampil centered dengan background abu-abu
- Bottom nav: `fixed bottom-0 w-full max-w-[430px]` height 60px
- Touch targets: min 44px (standar WCAG)

### Aturan Touch Target

Semua elemen interaktif di mobile wajib punya touch target minimum 44×44px:
```html
<!-- Tombol di mobile -->
<button className="min-h-[44px] min-w-[44px]">...</button>

<!-- Link di bottom nav -->
<a className="min-h-[44px] flex items-center justify-center">...</a>
```

---

## Integrasi dengan shadcn/ui

### Komponen yang Dipakai dari shadcn

| Komponen shadcn | Menggantikan | Lokasi |
|---|---|---|
| `Button` | Tombol inline style | Semua halaman |
| `Card` | Div dengan border + shadow | Card konten, menu card |
| `Badge` | StatusBadge (sebagian) | Status order |
| `Table` | Tabel Bootstrap/inline | Dashboard, riwayat |
| `Tabs` | Pill tabs manual | Pesanan Aktif, Verifikasi |
| `Dialog` | Modal custom / Inertia modal | Modal bayar, modal struk |
| `Sheet` | Panel kanan keranjang (mobile) | POS interface |
| `Form` + `Input` | Form inline | Login, profil, search |
| `Select` | Dropdown custom | Filter metode bayar |

### Cara Pakai

```tsx
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

// Tombol utama kasir
<Button className="bg-[var(--primary)] hover:bg-blue-700">
  Pesanan Baru
</Button>

// Card menu
<Card>
  <CardHeader>
    <CardTitle>Kopi Robusta</CardTitle>
  </CardHeader>
  <CardContent>
    <p className="text-[var(--primary)] font-semibold">Rp 12.000</p>
  </CardContent>
</Card>
```

### Variant Button yang Dipakai

```tsx
// Primary (default)
<Button>Simpan</Button>

// Secondary (outline)
<Button variant="outline">Batal</Button>

// Destructive (hapus/logout)
<Button variant="destructive">Hapus</Button>

// Ghost (link-style, untuk aksi tabel)
<Button variant="ghost">Setujui</Button>

// Custom size
<Button size="sm">Detail</Button>
<Button size="lg">BAYAR Rp 45.000</Button>
```

---

## Checklist Migrasi

### Sebelum Migrasi
- [ ] Screenshot semua 16 halaman sebagai baseline
- [ ] Catat semua warna dan spacing yang dipakai di inline styles
- [ ] Pastikan test suite passing (`php artisan test --parallel`)

### Setup
- [ ] `npx shadcn@latest init` dengan konfigurasi di atas
- [ ] Install semua komponen shadcn yang dibutuhkan
- [ ] Buat `resources/js/lib/utils.ts`
- [ ] Update `app.css` dengan Tailwind import dan theme variables
- [ ] Hapus Bootstrap dari `package.json` dan `CashierLayout.jsx`

### Per File (26 file)
- [ ] Konversi 1 file
- [ ] Screenshot comparison untuk halaman yang terdampak
- [ ] Commit atomic: `feat(ui): convert [ComponentName] to Tailwind + shadcn`
- [ ] Lanjut ke file berikutnya

### Setelah Semua File
- [ ] `npm run build` — zero warnings
- [ ] `php artisan test --parallel` — semua passing
- [ ] `npx playwright test e2e/visual/` — semua screenshot match
- [ ] Test manual semua flow: login → POS → bayar → struk
- [ ] Test responsive: resize browser, test di device fisik (HP)

### Rollback Plan
Jika ada masalah besar, revert bisa dilakukan per-komponen karena setiap file dikonversi dalam commit terpisah. CSS variables dan Tailwind config bisa tetap aktif karena tidak ada yang bentrok dengan inline styles.

---

## Catatan Tambahan

1. **shadcn/ui hanya untuk komponen baru dan refactor bertahap.** Tidak wajib semua halaman langsung pakai shadcn. Halaman yang sudah berfungsi baik dengan Tailwind murni bisa tetap seperti itu.

2. **Custom component vs shadcn.** Beberapa komponen custom kita (seperti `MenuGridItem` dan `KeranjangItem`) punya logika bisnis spesifik yang tidak cocok dengan komponen shadcn generik. Ini tetap sebagai komponen custom — hanya styling-nya yang dikonversi ke Tailwind.

3. **Fokus utama Tailwind, shadcn bonus.** Prioritas adalah menghilangkan inline styles dan menggantinya dengan class Tailwind. Integrasi shadcn adalah bonus yang meningkatkan konsistensi dan aksesibilitas.

4. **Tidak mengganggu Filament.** Panel admin Filament menggunakan Tailwind secara internal melalui Filament build pipeline. Tidak ada perubahan pada file atau konfigurasi Filament.
