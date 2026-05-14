# Bagian 3.3 — Struk Digital & QRIS Upload Fix

## Struk Digital

### Gambaran Umum

Setiap transaksi yang selesai dibayar menghasilkan struk digital yang bisa diakses melalui URL publik. Pelanggan bisa melihat struk tanpa perlu login — cukup scan QR code yang ditampilkan di layar kasir atau dikirim via link.

Struk dirancang bersih, informatif, dan mudah difoto/disimpan pelanggan sebagai bukti pembayaran.

---

### Route Publik

```
GET /receipt/{order_code}
```

Route ini bersifat **publik** — tidak memerlukan autentikasi. Siapa pun yang punya link bisa mengaksesnya, seperti struk fisik yang bisa dipegang siapa saja.

**Controller:** `ReceiptController@show`

```php
// routes/web.php
Route::get('/receipt/{order_code}', [ReceiptController::class, 'show'])
    ->name('receipt.show');
```

**Validasi akses:** Tidak ada. Route publik. Order code berfungsi sebagai "password" alami — cukup panjang dan random sehingga tidak bisa ditebak.

---

### Data yang Ditampilkan

Struk menampilkan informasi lengkap transaksi dari model `Order`:

| Bagian | Data | Sumber |
|---|---|---|
| Header | Logo W9 Cafe, nama cafe, alamat | `Setting` model |
| Info Pesanan | Order code (#ORD-048), tanggal, waktu, kasir | `Order` model |
| Item | Nama menu, qty, harga satuan, subtotal | `OrderItem` via relasi `items.menu` |
| Promosi | Nama promosi, jumlah diskon (jika ada) | `AppliedPromotion` via relasi |
| Total | Subtotal, diskon, total bayar | `Order.total_amount` |
| Pembayaran | Metode (QRIS/Tunai), status | `Payment` via relasi |
| Footer | "Terima kasih telah berkunjung", "Simpan struk ini sebagai bukti pembayaran" | Statis |

**Query Eager Loading:**

```php
$order = Order::with([
    'items.menu',
    'payment',
    'cashier',
    'appliedPromotions.promotion',
])->where('order_code', $orderCode)->firstOrFail();
```

---

### Desain Halaman

Struk didesain dengan estetika **thermal receipt** — putih bersih, font jelas, layout satu kolom. Bisa di-print atau difoto.

```
┌──────────────────────────────┐
│                              │
│         ☕ W9 CAFE           │
│     Jl. STIE Totalwin       │
│        Semarang             │
│                              │
│ ─────────────────────────── │
│                              │
│   STRUK PEMBAYARAN          │
│                              │
│   #ORD-048                  │
│   22 Feb 2026, 10:25 WIB    │
│   Kasir: Ivan               │
│                              │
│ ─────────────────────────── │
│                              │
│   Kopi Robusta              │
│   2x Rp 12.000    Rp 24.000 │
│                              │
│   Roti Bakar                │
│   1x Rp 21.000    Rp 21.000 │
│                              │
│ ─────────────────────────── │
│                              │
│   Subtotal         Rp 45.000│
│   Diskon (10%)    -Rp  4.500│
│   Total            Rp 40.500│
│                              │
│ ─────────────────────────── │
│                              │
│   Metode: QRIS              │
│   Status: LUNAS             │
│                              │
│ ─────────────────────────── │
│                              │
│   Terima kasih telah        │
│   berkunjung ke W9 Cafe!    │
│                              │
│   Simpan struk ini sebagai  │
│   bukti pembayaran.         │
│                              │
└──────────────────────────────┘
```

**Page file:** `resources/js/Pages/Receipt/Show.jsx`

---

### QR Code pada Struk

Setiap struk dilengkapi QR code yang meng-encode URL `/receipt/{order_code}`. QR code memudahkan pelanggan mengakses ulang struk mereka di kemudian hari.

**Library:** `qrcode` (npm package)

```bash
npm install qrcode
```

**Implementasi:**

```jsx
import { useEffect, useRef } from 'react';
import QRCode from 'qrcode';

function ReceiptQR({ orderCode }) {
  const canvasRef = useRef(null);
  const url = `${window.location.origin}/receipt/${orderCode}`;

  useEffect(() => {
    if (canvasRef.current) {
      QRCode.toCanvas(canvasRef.current, url, {
        width: 150,
        margin: 2,
        color: { dark: '#1A1A2E', light: '#FFFFFF' },
      });
    }
  }, [url]);

  return (
    <div className="flex flex-col items-center mt-4 pt-4 border-t border-gray-200">
      <p className="text-xs text-gray-500 mb-2">Scan untuk melihat struk</p>
      <canvas ref={canvasRef} />
    </div>
  );
}
```

---

### Integrasi dengan Flow Pembayaran

#### Flow Kasir (K3 → Struk)

1. Kasir tap **"BAYAR"** di keranjang
2. Modal dialog muncul: pilih metode bayar (QRIS / Tunai)
3. Konfirmasi pembayaran
4. Order dibuat, status jadi `completed`
5. **Modal struk muncul** — menampilkan ringkasan order + QR code
6. Kasir bisa cetak atau kasir bisa kasih pelanggan scan QR

```php
// CashierOrderController@store
public function store(StoreOrderRequest $request)
{
    $order = $this->createOrder($request);
    $order->createPayment($request->payment_method);

    return Inertia::render('Cashier/Order/Receipt', [
        'order' => $order->load(['items.menu', 'payment']),
    ]);
}
```

#### Flow Pelanggan (C2 → Struk)

1. Pelanggan tap **"Bayar Sekarang"** di keranjang
2. Pilih metode: QRIS (upload bukti) atau Tunai (konfirmasi di kasir)
3. Upload/konfirmasi selesai
4. Redirect ke halaman struk: `/receipt/{order_code}`

```php
// CustomerOrderController@store
$order = $this->createOrder($request);

if ($request->payment_method === 'qris') {
    return redirect()->route('customer.payment.qris', $order);
}

// Cash: redirect ke halaman status
return redirect()->route('receipt.show', $order->order_code);
```

---

### Informasi Cafe dari Setting Model

Data cafe (nama, alamat, nomor telepon) tidak di-hardcode — diambil dari model `Setting`:

```php
// app/Models/Setting.php
class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set($key, $value)
    {
        return static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
```

Setting yang dipakai di struk:

| Key | Default | Deskripsi |
|---|---|---|
| `cafe_name` | W9 Cafe | Nama cafe di header |
| `cafe_address` | Jl. STIE Totalwin, Semarang | Alamat |
| `cafe_phone` | — | Nomor telepon (opsional) |
| `receipt_footer` | Terima kasih telah berkunjung ke W9 Cafe! | Teks footer |

---

## QRIS Upload Fix

### Masalah Saat Ini

Sistem pembayaran QRIS meminta pelanggan meng-upload screenshot bukti transfer. Namun implementasi saat ini belum menggunakan Laravel filesystem storage dengan benar. Gambar hasil upload tidak tersimpan di lokasi standar dan tidak bisa diakses kembali.

### Solusi: Gunakan Laravel Storage

#### Upload Gambar

Semua upload gambar — baik bukti QRIS, foto menu, maupun avatar — harus melalui Laravel `Storage` facade:

```php
use Illuminate\Support\Facades\Storage;

// Di CustomerPaymentController@uploadQris
public function uploadQris(Request $request, Order $order)
{
    $request->validate([
        'payment_proof' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
    ]);

    // Hapus bukti lama jika ada
    if ($order->payment_proof) {
        Storage::disk('public')->delete($order->payment_proof);
    }

    // Upload ke storage/public/qris/
    $path = $request->file('payment_proof')->store('qris', 'public');

    $order->update(['payment_proof' => $path]);

    return redirect()->back()->with('success', 'Bukti pembayaran berhasil diupload.');
}
```

#### Path Konvensi

| Jenis File | Path | Disk |
|---|---|---|
| Bukti QRIS | `qris/{filename}` | `public` |
| Foto menu | `menus/{filename}` | `public` |
| Avatar user | `avatars/{filename}` | `public` |

#### Storage Link

Pastikan symlink `public/storage` → `storage/app/public` sudah dibuat:

```bash
php artisan storage:link
```

Cek keberadaan symlink:

```bash
ls -la public/storage
# Seharusnya: public/storage -> ../storage/app/public
```

#### Akses Gambar

Di view, pakai `Storage::url()`:

```blade
<img src="{{ Storage::url($order->payment_proof) }}" alt="Bukti QRIS">
```

Di React/Inertia, path gambar sudah dikirim via prop atau bisa diakses via URL helper:

```jsx
<img src={`/storage/${order.payment_proof}`} alt="Bukti QRIS" />
```

Lebih baik lagi, tambahkan accessor di model:

```php
// app/Models/Order.php
public function getPaymentProofUrlAttribute(): ?string
{
    if (!$this->payment_proof) {
        return null;
    }
    return Storage::disk('public')->url($this->payment_proof);
}
```

### Flow Persetujuan QRIS

#### Kasir Approve

1. Kasir buka halaman pesanan aktif, lihat order dengan pembayaran QRIS
2. Kasir tap **"Lihat Detail"** → lihat bukti transfer
3. Kasir verifikasi nominal dan tujuan transfer
4. Kasir tap **"Konfirmasi Pembayaran"**

```php
// CashierOrderController@confirmPayment
public function confirmPayment(Order $order)
{
    $order->payment->update([
        'status'  => 'success',
        'paid_at' => now(),
    ]);

    $order->update([
        'payment_status' => 'paid',
        'status'         => 'completed',
    ]);

    // Hapus gambar bukti setelah dikonfirmasi
    if ($order->payment_proof) {
        Storage::disk('public')->delete($order->payment_proof);
        $order->update(['payment_proof' => null]);
    }

    return redirect()->back()->with('success', 'Pembayaran dikonfirmasi.');
}
```

**Kenapa hapus gambar setelah approve?**
- Struk sudah jadi bukti sah, gambar screenshot tidak diperlukan lagi
- Menghemat storage
- Privasi: gambar bukti transfer mungkin memuat informasi sensitif pelanggan

#### Kasir Reject

1. Kasir lihat bukti, ternyata tidak valid (nominal salah, rekening beda)
2. Kasir tap **"Tolak"**

```php
// CashierOrderController@rejectPayment
public function rejectPayment(Order $order)
{
    $order->payment->update([
        'status' => 'failed',
    ]);

    $order->update([
        'payment_status' => 'unpaid',
    ]);

    // Gambar TETAP disimpan sebagai bukti
    // Tidak dihapus, untuk audit trail

    return redirect()->back()->with('error', 'Pembayaran ditolak.');
}
```

**Kenapa gambar tetap disimpan setelah reject?**
- Sebagai bukti audit: siapa yang upload, kapan, kenapa ditolak
- Jika terjadi dispute, bukti gambar masih tersedia
- Gambar hanya dihapus saat approve (transaksi sah)

---

### Flow Halaman QRIS Pelanggan

#### Halaman Upload (QrisUpload.jsx)

```
┌──────────────────────────────┐
│         Pembayaran QRIS      │
│                              │
│   Total: Rp 40.500           │
│                              │
│   Silakan transfer ke:       │
│   QRIS W9 Cafe               │
│   [QR code statis / info]    │
│                              │
│   ┌──────────────────────┐   │
│   │ Upload Bukti Transfer │   │
│   │ [choose file]        │   │
│   └──────────────────────┘   │
│                              │
│   [Kirim Bukti Pembayaran]   │
└──────────────────────────────┘
```

**File:** `resources/js/Pages/Customer/Payment/QrisUpload.jsx`

**Form validation:**
- File wajib
- Format: JPEG, PNG
- Maksimal: 5MB
- Preview sebelum submit

#### Halaman Status (QrisStatus.jsx)

```
┌──────────────────────────────┐
│         Status Pembayaran    │
│                              │
│         ⏳ (ikon)            │
│                              │
│   Menunggu Konfirmasi        │
│   Kasir sedang memverifikasi │
│   bukti pembayaran Anda.     │
│                              │
│   Order: #ORD-048            │
│   Total: Rp 40.500           │
│                              │
│   [Lihat Struk]              │
└──────────────────────────────┘
```

**File:** `resources/js/Pages/Customer/Payment/QrisStatus.jsx`

**Fitur:**
- Polling setiap 10 detik untuk cek update status
- Tampil status: pending → dibayar → selesai
- Jika dibayar, tampil tombol **"Lihat Struk"** → redirect ke `/receipt/{order_code}`
- Jika ditolak, muncul pesan error + instruksi upload ulang

---

## Checklist Implementasi

### Struk Digital
- [ ] Route publik `GET /receipt/{order_code}`
- [ ] `ReceiptController@show`
- [ ] Page `Receipt/Show.jsx` dengan desain thermal receipt
- [ ] QR code di struk via library `qrcode`
- [ ] Data cafe dari `Setting` model
- [ ] Eager loading items, payment, cashier, promotions
- [ ] Integrasi: modal struk setelah bayar di kasir
- [ ] Integrasi: redirect ke struk setelah bayar di pelanggan
- [ ] Print-friendly CSS (`@media print`)
- [ ] Responsive: tampil baik di mobile maupun desktop

### QRIS Upload Fix
- [ ] Semua upload gambar pakai `Storage::disk('public')`
- [ ] Path standar: `qris/`, `menus/`, `avatars/`
- [ ] Symlink `php artisan storage:link` sudah dibuat
- [ ] Accessor `payment_proof_url` di model Order
- [ ] Form upload dengan preview, validasi format & ukuran
- [ ] Flow approve: hapus gambar setelah konfirmasi
- [ ] Flow reject: gambar tetap disimpan
- [ ] Konfirmasi ada error handling (gambar corrupt, disk penuh)
- [ ] Test upload + approve + reject di Playwright
