# POSMine — Sistem Point of Sale

Sistem POS berbasis web PWA untuk kafe (studi kasus POSMine STIE Totalwin Semarang).
**Fase aktif:** Modul Transaksi (Kasir + Pelanggan) + Panel Admin (Filament) + Data Mining.

---

## Tech Stack

| Layer | Teknologi |
|---|---|
| Backend | Laravel 13, PHP 8.5 |
| Admin Panel | Filament v5 (Livewire) |
| Frontend (Kasir & Pelanggan) | React 19 + Inertia.js v2 |
| CSS | Bootstrap 5.3 + Tailwind 4 |
| Build | Vite 7 |
| Database | PostgreSQL 18 |
| Auth | Session-based multi-guard (web/admin) |
| State (cart) | Zustand + IndexedDB (offline) |
| Data Mining | Python 3.13 + FastAPI + scikit-learn + Prophet |
| Payment | Manual (Cash / QRIS) |
| Deployment | Docker (FrankenPHP/NGINX) + Vercel (opsional) |

---

## Cara Install

```bash
# 1. Clone & install dependencies
composer install
npm install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Database
php artisan migrate:fresh --seed

# 4. Jalankan
npm run build          # production
# atau
npm run dev &          # development (hot reload)
php artisan serve
```

### Docker

```bash
docker compose --profile prod up -d
docker compose exec -T app php artisan migrate:fresh --seed
```

- **app** — FrankenPHP/NGINX + PHP-FPM (port 8081)
- **pgsql** — PostgreSQL 18 (port 5432)
- **datamining** — FastAPI data mining (port 8001)

---

## Akun Default (setelah seeding)

| Peran | Email | Password |
|---|---|---|
| Admin | `admin@posmine.com` | `password` |
| Kasir | `kasir@posmine.com` | `password` |

> Login pelanggan (mahasiswa) memakai **Nama Lengkap** sebagai username dan **NIM** sebagai password.

---

## Halaman Admin (Filament) — `/admin`

| URL | Halaman |
|---|---|
| `/admin` | Dashboard (statistik + filter rentang tanggal) |
| `/admin/menu` | Kelola menu (Harga & Biaya Modal) |
| `/admin/kategori-menu` | Kategori menu |
| `/admin/bahan-baku` | Bahan baku, batch, riwayat pemakaian & pembayaran |
| `/admin/penyesuaian-stok` | Penyesuaian stok |
| `/admin/pesanan` | Pesanan |
| `/admin/piutang` | Piutang & pembayaran |
| `/admin/qr-code-meja` | QR code meja (token opaque) |
| `/admin/riwayat-kasir` | Riwayat sesi kasir |
| `/admin/akun-staff` | Akun staff (status Aktif/Nonaktif) |
| `/admin/pengaturan-struk-dan-whatsapp` | Pengaturan struk & template WhatsApp |
| `/admin/prediksi-menu` | Data mining — Prediksi Menu |
| `/admin/klasterisasi-menu` | Data mining — Klasterisasi Menu |
| `/admin/asosiatif-menu` | Data mining — Asosiasi Menu |
| `/admin/prediksi-bahan-baku` | Data mining — Prediksi Bahan Baku |
| `/admin/klasterisasi-bahan-baku` | Data mining — Klasterisasi Bahan Baku |

---

## Halaman Kasir (Desktop)

| URL | Route name | Halaman |
|---|---|---|
| `/kasir/login` | `kasir.login` | Login kasir |
| `/kasir/dashboard` | `kasir.dashboard` | Dashboard |
| `/kasir/pesanan-baru` | `kasir.new-order` | POS interface |
| `/kasir/pesanan-aktif` | `kasir.active-orders` | Pesanan aktif |
| `/kasir/riwayat-pesanan` | `kasir.order-history` | Riwayat pesanan |
| `/kasir/pesanan/{order}` | `kasir.order.show` | Detail pesanan |
| `/kasir/profil` | `kasir.profile` | Profil kasir |

## Halaman Pelanggan (Mobile PWA)

| URL | Route name | Halaman |
|---|---|---|
| `/pelanggan/login` | `customer.login` | Login mahasiswa |
| `/pelanggan/menu` | `customer.menu` | Menu |
| `/pelanggan/keranjang` | `customer.cart` | Keranjang |
| `/pelanggan/riwayat` | `customer.history` | Riwayat pesanan |
| `/order?table={token}` | `customer.order.entry` | Entry QR meja |
| `/struk-pesanan/{uuid}` | `receipt.show-by-uuid` | Struk publik |

---

## Data Mining

Layanan FastAPI di `datamining/` (port 8001) menyediakan 5 pipeline via `POST /run`:

`prediction`, `clustering`, `association`, `prediction-bahan-baku`, `clustering-bahan-baku`.

Laravel memanggilnya fire-and-forget (`DataMiningRunner`) dan menyimpan hasil di tabel `datamining_runs`.

---

## Variabel Environment Penting

```env
APP_NAME=POSMine
APP_LOCALE=id

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=pos_cafe

DATAMINING_URL=http://datamining:8001
SEED_START_DATE=2025-01-01
SEED_END_DATE=
```

---

## Tim

- **Ivan** — Fullstack Transaction
- **Nio** — Fullstack Inventory
- **Ruben** — Data Mining & FastAPI
