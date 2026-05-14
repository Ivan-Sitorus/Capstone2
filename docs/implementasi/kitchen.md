# Bagian 3.2 — Kitchen Display System (KDS) & Stock Report

## Kitchen Display System (KDS)

### Gambaran Umum

Kitchen Display System adalah tampilan dapur yang menampilkan pesanan masuk secara real-time dalam format kanban 3 kolom. Koki bisa melihat pesanan, mengubah statusnya dengan satu tap, dan mendapat notifikasi suara saat pesanan baru masuk.

Target device: **Tablet/Laptop di dapur**, readable dari jarak ~2 meter. Dark theme default agar nyaman di lingkungan dapur yang terang.

---

### Layout: Kanban 3 Kolom

```
┌─────────────────┐  ┌──────────────────────┐  ┌──────────────────┐
│   PENDING (3)   │  │   PREPARING (2)      │  │    READY (5)     │
│   #1A2332 bg    │  │   #1E293B bg         │  │   #1E293B bg     │
│                 │  │                      │  │                  │
│ ┌─────────────┐ │  │ ┌──────────────────┐ │  │ ┌──────────────┐ │
│ │ #ORD-051    │ │  │ │ #ORD-049         │ │  │ │ #ORD-045     │ │
│ │ 2x Kopi     │ │  │ │ 1x Roti Bakar    │ │  │ │ 3x Teh Tarik │ │
│ │ 1x Roti     │ │  │ │ ● 3 min ago      │ │  │ │ ✓ Selesai    │ │
│ │ ● Baru!     │ │  │ │                  │ │  │ │              │ │
│ │ [Mulai]     │ │  │ │ [Siap]           │ │  │ │ [Ambil]      │ │
│ └─────────────┘ │  │ └──────────────────┘ │  │ └──────────────┘ │
│                 │  │                      │  │                  │
│ ┌─────────────┐ │  │ ┌──────────────────┐ │  │                  │
│ │ #ORD-052    │ │  │ │ #ORD-048         │ │  │                  │
│ │ ...         │ │  │ │ ...              │ │  │                  │
│ └─────────────┘ │  │ └──────────────────┘ │  │                  │
└─────────────────┘  └──────────────────────┘  └──────────────────┘
```

**3 Kolom:**
1. **Pending** — Pesanan baru, belum dimulai. Warna latar kolom lebih gelap (`#0F172A`) untuk kontras.
2. **Preparing** — Sedang dibuat. Timer mulai berjalan.
3. **Ready** — Siap diambil kasir. Auto-clear setelah kasir konfirmasi.

**Per kolom:**
- Header: nama kolom + jumlah item (badge angka)
- Scrollable card list
- Card menampilkan: kode order, item-item, timer, tombol aksi

---

### Interaksi: Tap to Bump

Sistem memakai **tap** (klik/touch), bukan drag-and-drop. Alasan: touch interface di tablet dapur jauh lebih reliable dengan tap daripada drag.

**Alur bump:**
1. Order baru muncul di kolom Pending
2. Koki tap tombol **"Mulai"** → pindah ke Preparing, timer mulai
3. Koki tap tombol **"Siap"** → pindah ke Ready
4. Kasir mengambil pesanan → status jadi Completed, card hilang dari KDS

**Controller endpoint:**
```
PATCH /api/kds/orders/{id}/bump
Body: { status: "preparing" | "ready" }
```

Validasi bump:
- Hanya boleh ke status berikutnya (Pending → Preparing → Ready)
- Tidak bisa skip status
- Tidak bisa bump mundur

---

### Timer & Urgensi Warna

Setiap order di kolom Preparing punya timer sejak pertama kali di-bump. Warna card berubah berdasarkan durasi:

| Durasi | Warna Card | Status |
|---|---|---|
| 0-5 menit | Normal (putih/slate-50 di dark theme) | Aman |
| 5-10 menit | Kuning (`#EAB308` border kiri) | Perhatian |
| >10 menit | Merah (`#EF4444` border kiri + pulsing animation) | Urgent |

**Implementasi timer:**
```jsx
const [elapsed, setElapsed] = useState(0);

useEffect(() => {
  if (order.status !== 'preparing') return;
  const start = new Date(order.preparing_at).getTime();
  const interval = setInterval(() => {
    setElapsed(Math.floor((Date.now() - start) / 1000));
  }, 1000);
  return () => clearInterval(interval);
}, [order.preparing_at, order.status]);
```

**Kelas urgensi:**
```css
.kds-card-urgent {
  border-left: 4px solid #EF4444;
  animation: pulse-urgent 2s infinite;
}

@keyframes pulse-urgent {
  0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
  50%      { box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
}
```

---

### Notifikasi Suara

Saat pesanan baru masuk ke kolom Pending, KDS memutar suara notifikasi menggunakan **Web Audio API** — tidak perlu file audio eksternal.

```js
// resources/js/Hooks/useKitchenSound.js
const audioContext = new (window.AudioContext || window.webkitAudioContext)();

export function playNewOrderChime() {
  const osc = audioContext.createOscillator();
  const gain = audioContext.createGain();

  osc.connect(gain);
  gain.connect(audioContext.destination);

  osc.frequency.setValueAtTime(880, audioContext.currentTime);     // A5
  osc.frequency.setValueAtTime(1100, audioContext.currentTime + 0.1); // C#6
  gain.gain.setValueAtTime(0.3, audioContext.currentTime);
  gain.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.4);

  osc.start(audioContext.currentTime);
  osc.stop(audioContext.currentTime + 0.4);
}
```

**Toggle mute:** Tombol speaker di pojok kanan atas KDS. State disimpan di `localStorage` agar persisten.

```jsx
const [muted, setMuted] = useState(
  () => localStorage.getItem('kds-muted') === 'true'
);

const toggleMute = () => {
  const next = !muted;
  setMuted(next);
  localStorage.setItem('kds-muted', String(next));
};
```

**Catatan:** Browser mewajibkan user interaction sebelum AudioContext bisa dipakai. Solusi: inisialisasi `audioContext` setelah user pertama kali tap/klik di halaman.

---

### Polling Data Real-time

KDS menggunakan **polling 5 detik** melalui Inertia partial reload:

```jsx
useEffect(() => {
  const interval = setInterval(() => {
    router.reload({
      only: ['orders'],
      preserveState: true,
      preserveScroll: true,
    });
  }, 5000);

  return () => clearInterval(interval);
}, []);
```

**Kenapa polling, bukan WebSocket?**
- Fase 1: polling sudah cukup untuk KDS. Dapur tidak perlu latency sub-detik.
- **Laravel Reverb** (WebSocket) didefer ke fase berikutnya. Saat Reverb sudah aktif di modul Kasir, KDS akan di-upgrade ke push-based.

**Optimasi polling:**
- `preserveState: true` — tidak reset scroll position
- `preserveScroll: true` — kolom tidak lompat saat data refresh
- `only: ['orders']` — hanya fetch data yang berubah, tidak reload seluruh halaman

### Full-screen Mode

Toggle full-screen untuk pengalaman imersif di dapur:

```jsx
function toggleFullscreen() {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen();
  } else {
    document.exitFullscreen();
  }
}
```

- Tombol full-screen di header KDS
- Shortcut keyboard: F11
- Saat full-screen: sidebar kasir collapse, hanya KDS yang tampil

### Dark Theme

KDS default ke dark theme karena:
- Dapur biasanya terang (lampu neon), layar gelap kurang silau
- Kontras tinggi untuk readability dari jarak 2 meter
- Warna urgensi (kuning/merah) lebih menonjol di background gelap

```css
[data-interface="kitchen"] {
  --background: #0F172A;
  --foreground: #F8FAFC;
  --card: #1E293B;
  --card-foreground: #F8FAFC;
  --muted: #334155;
  --muted-foreground: #94A3B8;
  --accent: #1E293B;
  --border: #334155;
}
```

### Filter Tabs

Tiga tab filter di atas kanban:

```
[Semua (10)]  [Minuman (6)]  [Makanan (4)]
```

Filter berdasarkan kategori menu di dalam order:
- **Semua:** semua order
- **Minuman:** order yang mengandung item dari kategori "Minuman" (Kopi, Teh, Coklat, dll.)
- **Makanan:** order yang mengandung item dari kategori "Makanan" (Snack, Roti, dll.)

Implementasi: filter di backend. Query Eloquent mengecek kategori dari `orderItems → menu → category`.

### Halaman dan Route

```
GET /kitchen
```

**Controller:** `KitchenController@index`

**Layout:** `KitchenLayout.jsx` — full-screen, dark theme, tanpa sidebar kasir.

**Page:** `resources/js/Pages/Kitchen/Index.jsx`

**Middleware:** `auth` + role check `cashier` atau `kitchen` (jika role kitchen ditambahkan nanti).

---

## Kitchen Stock Report

### Gambaran Umum

Fitur ini memungkinkan staf dapur melaporkan penyesuaian stok bahan baku — misalnya susu tumpah, roti kadaluarsa, atau stok tambahan yang baru datang. Admin kemudian me-review dan menyetujui/menolak laporan.

### Model: StockAdjustment

Model `StockAdjustment` sudah ada di codebase (`app/Models/StockAdjustment.php`). Struktur:

```php
// app/Models/StockAdjustment.php
class StockAdjustment extends Model
{
    protected $fillable = [
        'ingredient_id',
        'reported_by',        // user_id yang membuat laporan
        'report_type',        // 'increase' atau 'decrease'
        'quantity',           // jumlah perubahan
        'reason',             // alasan (teks bebas)
        'status',             // 'pending', 'approved', 'rejected'
        'reviewed_by',        // user_id admin yang review (nullable)
        'rejection_note',     // catatan penolakan (nullable)
        'reviewed_at',        // timestamp review (nullable)
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'reviewed_at' => 'datetime',
        ];
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
```

### Alur Bisnis

#### 1. Kitchen Submit Adjustment

Staf dapur membuka halaman **"Lapor Stok"**, mengisi form:

- **Bahan baku** (dropdown ingredient)
- **Jenis laporan** (increase/decrease)
- **Jumlah** (dalam satuan ingredient, misal: 100 ml)
- **Alasan** (teks bebas, misal: "Susu UHT tumpah saat menuang")

**Efek langsung:** Begitu form disubmit, stok ingredient langsung berubah. Tidak menunggu approval admin. Filosofinya: dapur harus selalu punya angka stok yang akurat secara real-time.

```php
// KitchenStockController@store
public function store(StoreStockAdjustmentRequest $request)
{
    $adjustment = StockAdjustment::create([
        'ingredient_id' => $request->ingredient_id,
        'reported_by'   => auth()->id(),
        'report_type'   => $request->report_type,
        'quantity'      => $request->quantity,
        'reason'        => $request->reason,
        'status'        => 'pending',
    ]);

    // Langsung kurangi/tambah stok
    $ingredient = Ingredient::find($request->ingredient_id);
    if ($request->report_type === 'decrease') {
        $ingredient->decrement('current_stock', $request->quantity);
    } else {
        $ingredient->increment('current_stock', $request->quantity);
    }

    // Catat di stock_movements
    StockMovement::create([
        'ingredient_id'     => $request->ingredient_id,
        'adjustment_id'     => $adjustment->id,
        'type'              => $request->report_type,
        'quantity'          => $request->quantity,
        'stock_before'      => $ingredient->current_stock,  // setelah adjustment
        'stock_after'       => $ingredient->current_stock,  // sama, karena langsung berubah
        'reference_type'    => 'stock_adjustment',
        'reference_id'      => $adjustment->id,
        'notes'             => $request->reason,
    ]);

    return redirect()->back()->with('success', 'Laporan stok terkirim.');
}
```

#### 2. Admin Review (di Panel Filament)

Admin melihat laporan di panel Filament → tab **"Pending"**.

**Approve:**
- Status laporan jadi `approved`
- `reviewed_by` = admin ID
- `reviewed_at` = timestamp sekarang
- Stok tidak berubah (sudah berubah saat submit)
- `StockMovement` tetap tidak berubah

**Reject:**
- Status laporan jadi `rejected`
- `reviewed_by` = admin ID
- `reviewed_at` = timestamp sekarang
- `rejection_note` = alasan penolakan
- **Stok dikembalikan** ke posisi sebelum adjustment

```php
// Reject logic
public function reject(StockAdjustment $adjustment)
{
    $adjustment->update([
        'status'         => 'rejected',
        'reviewed_by'    => auth()->id(),
        'reviewed_at'    => now(),
        'rejection_note' => request('rejection_note'),
    ]);

    // Kembalikan stok
    $ingredient = $adjustment->ingredient;
    if ($adjustment->report_type === 'decrease') {
        $ingredient->increment('current_stock', $adjustment->quantity);
    } else {
        $ingredient->decrement('current_stock', $adjustment->quantity);
    }

    // Catat reversal di stock_movements
    StockMovement::create([
        'ingredient_id'  => $ingredient->id,
        'type'           => $adjustment->report_type === 'decrease' ? 'increase' : 'decrease',
        'quantity'       => $adjustment->quantity,
        'stock_before'   => $ingredient->current_stock,
        'stock_after'    => $ingredient->current_stock,
        'reference_type' => 'stock_adjustment_reversal',
        'reference_id'   => $adjustment->id,
        'notes'          => 'Reversal: Adjustment ditolak. ' . $adjustment->rejection_note,
    ]);
}
```

#### 3. Admin Buat Adjustment Langsung

Admin juga bisa membuat adjustment langsung dari panel Filament. Adjustment yang dibuat admin otomatis **auto-approved** (`status = 'approved'`). Tidak perlu review.

### Dua Tab di Admin Panel

**Tab "Disetujui" (Approved):**
- Semua adjustment yang sudah berlaku (approved + admin-created)
- Menampilkan: tanggal, bahan, jumlah, jenis, pelapor, status
- Tidak ada aksi (read-only)

**Tab "Pending":**
- Hanya adjustment dari kitchen yang belum di-review
- Menampilkan: tanggal, bahan, jumlah, jenis, pelapor, alasan
- Aksi: tombol **Setujui** (hijau) dan **Tolak** (merah)
- Saat menolak: modal dialog minta alasan penolakan

### Edge Cases

#### Simultaneous Reports (Concurrent Access)

**Masalah:** Dua staf dapur submit adjustment untuk ingredient yang sama pada waktu hampir bersamaan. Jika tidak ditangani, stok bisa inkonsisten.

**Solusi: Optimistic Locking dengan version check.**

```php
// Di model Ingredient
protected function casts(): array
{
    return [
        'version' => 'integer',
    ];
}

// Di controller, sebelum update stok:
$ingredient = Ingredient::find($id);
$affected = Ingredient::where('id', $id)
    ->where('version', $ingredient->version)
    ->update([
        'current_stock' => $newStock,
        'version'       => $ingredient->version + 1,
    ]);

if ($affected === 0) {
    // Gagal: ingredient sudah diubah proses lain
    throw new \App\Exceptions\ConcurrentStockUpdateException(
        'Stok baru saja diubah. Silakan refresh dan coba lagi.'
    );
}
```

#### Report During Active Orders

**Prinsip:** Adjustment langsung berlaku saat dibuat. Stok berubah real-time. Order yang sedang aktif menggunakan stok yang tersedia saat itu.

Contoh skenario:
1. Stok susu: 1000ml
2. Order A masuk: pakai 200ml → stok: 800ml
3. Kitchen lapor susu tumpah 100ml → stok: 700ml
4. Order B masuk: pakai 150ml → stok: 550ml

Sederhana dan tidak ada konflik.

#### Rejection dengan Order yang Terjadi Selama Pending

**Skenario:**
1. Stok susu: 1000ml
2. Kitchen lapor tumpah 100ml → stok jadi 900ml (pending)
3. Order masuk, pakai 200ml → stok jadi 700ml
4. Admin tolak laporan → stok harus kembali ke posisi SEBELUM adjustment

**Naif:** `$ingredient->increment('current_stock', 100)` → stok jadi 800ml (SALAH)

**Benar:** Stok setelah reversal = 700ml + 100ml = 800ml. Karena order yang terjadi selama pending mengurangi 200ml dari stok yang sudah teradjustment (900ml), maka reversal menambah 100ml ke stok saat ini.

```php
// Reject logic yang benar
$currentStock = $ingredient->current_stock; // 700ml
$adjustmentAmount = $adjustment->quantity;  // 100ml

if ($adjustment->report_type === 'decrease') {
    // Tadinya dikurangi 100ml, sekarang kembalikan 100ml
    $ingredient->current_stock = $currentStock + $adjustmentAmount;
} else {
    // Tadinya ditambah 100ml, sekarang kurangi 100ml
    $ingredient->current_stock = $currentStock - $adjustmentAmount;
}
$ingredient->save();
```

Perhitungan ini sederhana karena kita hanya membalik adjustment, tidak menghitung ulang dari nol.

---

## Rute dan File

### KDS

| Method | Route | Controller | File |
|---|---|---|---|
| GET | `/kitchen` | `KitchenController@index` | `Kitchen/Index.jsx` |
| PATCH | `/api/kds/orders/{id}/bump` | `KitchenController@bump` | API only |

### Stock Report (Kitchen Side)

| Method | Route | Controller | File |
|---|---|---|---|
| GET | `/kitchen/stok` | `KitchenStockController@index` | `Kitchen/Stok/Index.jsx` |
| GET | `/kitchen/stok/create` | `KitchenStockController@create` | `Kitchen/Stok/Create.jsx` |
| POST | `/kitchen/stok` | `KitchenStockController@store` | Form POST |

### Stock Report (Admin Side — Filament)

Sudah ada resource Filament untuk StockAdjustment. Hanya perlu memastikan dua tab view (Approved dan Pending) berfungsi dengan baik.

---

## Checklist Implementasi

### KDS
- [ ] Layout Kitchen (dark theme, full-screen capable)
- [ ] Kanban 3 kolom dengan card
- [ ] Tap-to-bump interaction
- [ ] Timer per order di kolom Preparing
- [ ] Urgensi warna (normal → kuning → merah + pulse)
- [ ] Suara notifikasi Web Audio API
- [ ] Mute toggle
- [ ] Polling 5 detik
- [ ] Full-screen toggle
- [ ] Filter tabs (Semua / Minuman / Makanan)
- [ ] Responsive untuk tablet landscape

### Stock Report
- [ ] Form lapor stok (kitchen)
- [ ] Submit langsung kurangi/tambah stok
- [ ] Admin view: tab Approved + Pending
- [ ] Approve flow (tidak ubah stok)
- [ ] Reject flow (kembalikan stok + alasan wajib)
- [ ] Admin auto-approved adjustment
- [ ] Optimistic locking untuk concurrent reports
- [ ] StockMovement logging untuk semua perubahan
- [ ] Unit test untuk semua skenario (approve, reject, concurrent)
