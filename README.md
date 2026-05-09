# W9 Cafe POS — Sistem Point of Sale

Sistem POS berbasis web PWA untuk W9 Cafe STIE Totalwin Semarang.  
**Fase aktif:** Modul Transaksi (Kasir + Pelanggan)

---

## Tech Stack

| Layer | Teknologi |
|---|---|
| Backend | Laravel 13, PHP 8.5.6 |
| Runtime | Node.js 24.x |
| Database | PostgreSQL 18 |
| Frontend | React 19 + Inertia.js v2 |
| CSS | Bootstrap 5.3 + Tailwind 4 |
| Build | Vite 7 |
| Auth | Laravel Sanctum (session-based) |
| Payment | Manual (Cash / QRIS) |
| State | Zustand (cart) + IndexedDB (offline) |
| Docker | NGINX + PHP-FPM + Supervisor |

---

## Cara Install

```bash
# 1. Clone & install dependencies
composer install
npm install

# 2. Environment
cp .env.example .env
php artisan key:generate

# Isi DB_* di .env

# 3. Database
php artisan migrate:fresh --seed

# 4. Jalankan
npm run build          # production
# atau
npm run dev &          # development (hot reload)
php artisan serve
```

---

## Akun Default (setelah seeding)

| Role | Email | Password |
|---|---|---|
| Kasir | `kasir@w9cafe.com` | `password` |
| Admin | `admin@w9cafe.com` | `password` |
| Customer | `budi@student.com` | `password` |

> Login pelanggan menggunakan **Nama Lengkap** sebagai username dan **NIM** sebagai password.  
> NIM Budi: `21120122140001`

---

## Variabel Environment Penting

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=pos_cafe
DB_USERNAME=postgres
DB_PASSWORD=

# No payment gateway configured (cash/qris only)
```

---

## Using Docker (Production)

```bash
docker compose --profile prod up -d
```

App runs on **http://localhost:8081**. The `prod` profile starts:

- **app** — single-container NGINX + PHP-FPM + Supervisor (port 8081)
- **pgsql** — PostgreSQL 18 (port 5432)
- **redis** — Redis cache (port 6379)

```bash
# Run migrations and seed on first launch
docker compose exec -T app php artisan migrate:fresh --seed
```

---

## Halaman Kasir (Desktop)

| Route | Halaman |
|---|---|
| `/login` | Login kasir |
| `/cashier/dashboard` | Dashboard + stat harian |
| `/cashier/pesanan-baru` | POS interface (grid menu + keranjang) |
| `/cashier/pesanan-aktif` | Kanban pesanan aktif |
| `/cashier/riwayat` | Riwayat transaksi |
| `/cashier/order/{id}` | Detail pesanan |
| `/cashier/verifikasi` | Verifikasi akun mahasiswa |
| `/cashier/profil` | Profil kasir |

## Halaman Pelanggan (Mobile PWA)

| Route | Halaman |
|---|---|
| `/customer/login` | Login mahasiswa |
| `/customer/menu` | Menu + kategori chips |
| `/customer/cart` | Keranjang belanja |
| `/customer/riwayat` | Riwayat pesanan |
| `/customer/order/{id}/status` | Status pembayaran |

---

## Struktur Tim

- **Ivan** — Fullstack Transaction (modul ini)
- **Nio** — Fullstack Inventory (fase berikutnya)
- **Ruben** — Data Mining & FastAPI (fase terakhir)
