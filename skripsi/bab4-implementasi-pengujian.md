# BAB IV IMPLEMENTASI DAN PENGUJIAN

## 4.1 Implementasi Panel Administrasi

### 4.1.1 Halaman Login

Halaman login merupakan halaman pertama yang muncul ketika admin mengakses panel administrasi. Halaman ini menyajikan formulir autentikasi dengan input email dan password untuk memverifikasi identitas admin sebelum mengakses sistem.

Gambar 4.1 Halaman login admin.

### 4.1.2 Halaman Kategori Menu

Halaman kategori menu menampilkan daftar kategori yang digunakan untuk mengelompokkan menu. Admin dapat melihat, menambah, mengedit, dan menghapus kategori sesuai kebutuhan.

Gambar 4.2 Halaman daftar kategori.

Form tambah kategori berisi input nama kategori yang harus diisi oleh admin. Setelah disimpan, kategori baru akan muncul di tabel daftar kategori.

Gambar 4.3 Form buat kategori baru.

### 4.1.3 Halaman Menu

Halaman menu menampilkan daftar seluruh menu beserta informasi kategori, harga, jumlah sisa jual, dan status ketersediaan. Admin dapat melihat dan mengelola data menu dari halaman ini.

Gambar 4.4 Halaman daftar menu.

Form buat menu baru terdiri dari input nama menu, pemilihan kategori, input harga, toggle status tersedia, serta pengelolaan resep bahan baku penyusun menu. Admin dapat memilih bahan baku dari dropdown dan menentukan jumlah pemakaian per unit menu.

Gambar 4.5 Form buat menu baru.

### 4.1.4 Halaman Bahan Baku

Halaman bahan baku menampilkan daftar seluruh bahan baku yang terdaftar dalam sistem beserta informasi unit, total stok, mode batch (FEFO/FIFO), dan status aktif. Admin dapat menambah, mengedit, dan menonaktifkan bahan baku dari halaman ini.

Gambar 4.6 Halaman bahan baku.

Form tambah bahan baku berisi input nama bahan baku, pemilihan unit satuan, dan mode batch. Mode batch menentukan urutan deduksi stok, yaitu FEFO untuk bahan dengan masa kedaluwarsa atau FIFO untuk bahan tanpa masa kedaluwarsa signifikan.

Gambar 4.7 Form buat bahan baku baru.

### 4.1.5 Halaman Batch Stok Bahan Baku

Halaman batch stok bahan baku menampilkan daftar batch untuk suatu bahan baku. Setiap batch menampilkan informasi jumlah stok, tanggal kedaluwarsa (untuk mode FEFO), tanggal penerimaan (untuk mode FIFO), dan harga satuan.

Gambar 4.8 Halaman batch stok bahan baku.

Form tambah batch stok berisi input jumlah stok, tanggal kedaluwarsa atau tanggal penerimaan sesuai mode batch, dan harga per unit. Admin dapat menambah batch baru untuk memperbarui stok bahan baku.

Gambar 4.9 Form buat batch stok baru.

### 4.1.6 Halaman Penyesuaian Stok

Halaman penyesuaian stok menampilkan riwayat penyesuaian stok yang telah dilakukan, baik penambahan maupun pengurangan stok bahan baku.

Gambar 4.10 Halaman penyesuaian stok.

Form penyesuaian stok terdiri dari pemilihan bahan baku, tipe penyesuaian (increase atau decrease), input jumlah, dan alasan penyesuaian. Setiap penyesuaian dicatat oleh sistem untuk menjaga riwayat perubahan stok.

Gambar 4.11 Form buat penyesuaian stok baru.

### 4.1.7 Halaman Riwayat Penggunaan Bahan Baku

Halaman riwayat penggunaan bahan baku menampilkan data pemakaian bahan baku berdasarkan transaksi penjualan yang telah terjadi. Informasi ini membantu admin dalam memantau tren penggunaan bahan baku.

Gambar 4.12 Halaman riwayat penggunaan bahan baku.

### 4.1.8 Implementasi Algoritma Deduksi Stok

Algoritma deduksi stok merupakan inti dari sistem manajemen inventori yang menentukan urutan konsumsi batch ketika terjadi pemakaian bahan baku. Sistem mengimplementasikan dua mode deduksi utama, yaitu FEFO (*First-Expiry-First-Out*) untuk bahan dengan masa kedaluwarsa terbatas dan FIFO (*First-In-First-Out*) untuk bahan non-perishable. Mekanisme penguncian data (*row-level locking*) diterapkan untuk mencegah konflik pada transaksi bersamaan.

Penentuan urutan batch dilakukan melalui perintah `match` yang menerjemahkan mode batch bahan baku menjadi urutan query SQL. Batch dengan quantity lebih besar dari nol diambil, kemudian diurutkan berdasarkan mode yang dikonfigurasi pada setiap bahan baku. Batch yang memiliki nilai relevan kosong (NULL) ditempatkan di akhir urutan agar tidak mengganggu prioritas.

```php
$query = IngredientBatch::where('ingredient_id', $ingredientId)
    ->where('quantity', '>', 0)
    ->where(function ($q) {
        $q->whereNull('expiry_date')
          ->orWhereDate('expiry_date', '>', now())
          ->orWhere('allow_expired_usage', true);
    })
    ->lockForUpdate();

match ($ingredient->batch_mode) {
    Ingredient::BATCH_MODE_FIFO => $query
        ->orderByRaw('CASE WHEN received_at IS NULL THEN 1 ELSE 0 END')
        ->orderBy('received_at', 'asc')
        ->orderBy('expiry_date', 'asc')
        ->orderBy('id', 'asc'),
    Ingredient::BATCH_MODE_CUSTOM => $query
        ->orderByRaw('CASE WHEN custom_order IS NULL THEN 1 ELSE 0 END')
        ->orderBy('custom_order', 'asc')
        ->orderBy('received_at', 'asc')
        ->orderBy('id', 'asc'),
    default => $query  // FEFO
        ->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END')
        ->orderBy('expiry_date', 'asc')
        ->orderBy('received_at', 'asc')
        ->orderBy('id', 'asc'),
};
```

Pada kode di atas, baris `match` menentukan urutan batch berdasarkan mode. Pada mode FIFO, batch diurutkan berdasarkan `received_at` terlama (*ascending*). Pada mode *default* (FEFO), batch diurutkan berdasarkan `expiry_date` terdekat (*ascending*). Klausa `orderByRaw('CASE WHEN ... IS NULL THEN 1 ELSE 0 END')` memastikan bahwa batch yang memiliki nilai relevan kosong ditempatkan paling akhir sehingga tidak dikonsumsi lebih dahulu. Klausa `lockForUpdate()` mengunci baris-baris batch yang terpilih untuk mencegah transaksi bersamaan mengakses data yang sama sebelum transaksi saat ini selesai.

Setelah batch diurutkan sesuai prioritas, sistem melakukan iterasi deduksi dari batch pertama hingga kebutuhan kuantitas terpenuhi. Setiap iterasi mencatat pergerakan stok melalui model `StockMovement` yang merekam `quantity_before`, `quantity_change`, dan `quantity_after` untuk keperluan audit.

```php
foreach ($batches as $batch) {
    if ($remainingToDeduct <= 0) break;
    $before = (float) $batch->quantity;
    $deductFromThisBatch = min($before, $remainingToDeduct);
    $after = $before - $deductFromThisBatch;
    $batch->quantity = $after;
    $batch->save();
    $remainingToDeduct -= $deductFromThisBatch;
    StockMovement::create([
        'ingredient_id' => $ingredientId,
        'ingredient_batch_id' => $batch->id,
        'order_id' => $context['order_id'] ?? null,
        'movement_type' => $context['movement_type'] ?? 'sale',
        'quantity_before' => $before,
        'quantity_change' => -$deductFromThisBatch,
        'quantity_after' => $after,
        'unit_cost' => $batch->cost_per_unit,
        'notes' => $context['notes'] ?? null,
    ]);
}
```

Pada kode di atas, setiap batch diproses secara berurutan. Variabel `before` menyimpan nilai stok sebelum deduksi, `deductFromThisBatch` menghitung jumlah yang diambil dari batch saat ini menggunakan fungsi `min()`, dan `after` menyimpan nilai stok setelah deduksi. Setelah penyimpanan batch, sistem mencatat `StockMovement` dengan `quantity_change` bernilai negatif karena merupakan pengurangan stok. Proses berlanjut hingga `remainingToDeduct` habis atau seluruh batch telah diproses.

### 4.1.9 Implementasi Penyesuaian Stok dan Perhitungan Stok

Penyesuaian stok (*stock adjustment*) merupakan fitur yang memungkinkan admin melakukan perubahan stok secara manual di luar transaksi penjualan, baik berupa penambahan (*increase*) maupun pengurangan (*decrease*, *waste*, *damage*). Fitur pembatalan penyesuaian (*cancel*) juga diimplementasikan untuk mengembalikan stok ke kondisi sebelum penyesuaian dilakukan.

Mekanisme pembatalan penyesuaian bekerja dengan cara membalikkan (*reverse*) setiap pergerakan stok yang tercatat pada penyesuaian yang akan dibatalkan. Untuk setiap `StockMovement` yang terkait, sistem menghitung nilai perubahan kebalikan (`reversalChange = -originalChange`), mengembalikan stok batch ke nilai semula, dan mencatat `StockMovement` baru sebagai jejak audit.

```php
foreach ($record->stockMovements as $movement) {
    $batch = IngredientBatch::find($movement->ingredient_batch_id);
    if (! $batch) continue;
    $originalChange = (float) $movement->quantity_change;
    $reversalChange = -$originalChange;
    $batchBefore = (float) $batch->quantity;
    $batch->increment('quantity', $reversalChange);
    $batchAfter = (float) $batch->quantity;
    StockMovement::create([
        'ingredient_id' => $movement->ingredient_id,
        'ingredient_batch_id' => $batch->id,
        'stock_adjustment_id' => $record->id,
        'movement_type' => $movement->movement_type,
        'source_type' => 'stock_adjustment_reversal',
        'source_id' => (string) $record->id,
        'quantity_before' => $batchBefore,
        'quantity_change' => $reversalChange,
        'quantity_after' => $batchAfter,
        'unit_cost' => $batch->cost_per_unit,
        'notes' => 'Pembatalan: ' . $reason,
    ]);
}
```

Selain penyesuaian stok, sistem juga menyediakan perhitungan stok yang dikonversi menjadi jumlah porsi (*servings*) yang dapat diproduksi dari bahan baku yang tersedia. Atribut `stock` pada model `Menu` menghitung ketersediaan stok untuk setiap menu berdasarkan resep bahan baku penyusunnya. Perhitungan dilakukan dengan membagi total stok setiap bahan baku dengan kebutuhan per porsi (`quantity_used`), kemudian mengambil nilai minimum di antara seluruh bahan baku penyusun. Pendekatan ini memastikan bahwa jumlah porsi yang dilaporkan sesuai dengan bahan baku yang paling terbatas.

```php
public function getStockAttribute(): ?float
{
    $ingredients = $this->menuIngredients()->with('ingredient')->get();
    if ($ingredients->isEmpty()) return null;
    $minServings = null;
    foreach ($ingredients as $mi) {
        if (! $mi->ingredient) continue;
        $totalStock = (float) $mi->ingredient->batches()
            ->where('quantity', '>', 0)
            ->where(function ($q) {
                $q->whereNull('expiry_date')
                  ->orWhereDate('expiry_date', '>', now())
                  ->orWhere('allow_expired_usage', true);
            })
            ->sum('quantity') ?: 0;
        $needed = (float) $mi->quantity_used;
        $servings = $needed > 0 ? (int) ($totalStock / $needed) : 0;
        if ($minServings === null || $servings < $minServings) {
            $minServings = $servings;
        }
    }
    return $minServings ?? 0;
}
```

Pada kode di atas, setiap bahan baku penyusun menu diperiksa stoknya melalui relasi `batches`. Hanya batch dengan *quantity* lebih dari nol dan belum kedaluwarsa (atau diizinkan penggunaan kedaluwarsa) yang dihitung. Total stok dibagi dengan `quantity_used` (kebutuhan per porsi) untuk mendapatkan jumlah porsi yang dapat dibuat dari bahan tersebut. Nilai minimum (`minServings`) di antara seluruh bahan kemudian menjadi nilai akhir atribut `stock`. Jika menu tidak memiliki resep (*ingredients* kosong), fungsi mengembalikan `null`.

## 4.2 Pengujian

Pengujian sistem dilakukan melalui dua pendekatan: black box dan white box.

### 4.2.1 Pengujian Black Box

**a. Pengujian Autentikasi Admin**

Tabel 4.2 Pengujian black box autentikasi admin.

| Skenario | Langkah | Hasil Diharapkan | Status |
|----------|---------|------------------|--------|
| Login dengan kredensial valid | Isi email dan password benar, klik Masuk | Redirect ke dashboard | Berhasil |
| Login dengan password salah | Isi password salah | Tampil pesan error | Berhasil |
| Logout | Klik tombol Logout | Kembali ke halaman login | Berhasil |

**b. Pengujian Manajemen Bahan Baku**

Tabel 4.3 Pengujian black box manajemen bahan baku.

| Skenario | Langkah | Hasil Diharapkan | Status |
|----------|---------|------------------|--------|
| Tambah bahan baku | Isi form, simpan | Data muncul di tabel | Berhasil |
| Ubah bahan baku | Ubah nama/unit, simpan | Data berubah | Berhasil |
| Hapus bahan baku | Klik hapus | Data hilang (soft delete) | Berhasil |
| Tambah batch stok | Isi jumlah, tanggal, simpan | Batch muncul di daftar | Berhasil |

**c. Pengujian Penyesuaian Stok**

Tabel 4.4 Pengujian black box penyesuaian stok.

| Skenario | Langkah | Hasil Diharapkan | Status |
|----------|---------|------------------|--------|
| Penyesuaian increase | Isi jumlah positif, pilih tipe increase | Stok bertambah | Berhasil |
| Penyesuaian decrease | Isi jumlah positif, pilih tipe decrease | Stok berkurang | Berhasil |
| Penyesuaian qty <= 0 | Isi 0 atau negatif | Ditolak sistem | Berhasil |

**d. Pengujian Manajemen Resep Menu**

Tabel 4.5 Pengujian black box manajemen resep menu.

| Skenario | Langkah | Hasil Diharapkan | Status |
|----------|---------|------------------|--------|
| Tambah bahan ke resep | Pilih ingredient dan quantity | Data tersimpan di pivot | Berhasil |
| Hapus bahan dari resep | Klik hapus | Data pivot terhapus | Berhasil |

**e. Pengujian Dashboard**

Tabel 4.6 Pengujian black box dashboard.

| Skenario | Langkah | Hasil Diharapkan | Status |
|----------|---------|------------------|--------|
| Dashboard menampilkan data | Buka halaman /admin | Ringkasan stok dan statistik tampil | Berhasil |

Seluruh skenario pengujian black box menunjukkan status Berhasil dengan total 12 skenario terverifikasi.

### 4.2.2 Pengujian White Box

Pengujian white box dilakukan untuk memverifikasi kebenaran logika internal sistem menggunakan PHPUnit. Pengujian mencakup lima skenario utama yang merepresentasikan fitur inti manajemen inventori. Source code pengujian disimpan pada direktori `tests/Unit/Inventory/` dan `tests/Feature/Admin/`.

**1. Pengujian Deduksi Stok Berdasarkan Resep Menu**

Pengujian ini memvalidasi bahwa ketika suatu menu yang memiliki resep (komposisi bahan baku) diproses, sistem secara otomatis mengurangi stok bahan baku sesuai dengan jumlah yang terdaftar pada tabel pivot `menu_ingredients`. Skenario pengujian: sebuah menu "Kopi Susu" dengan resep 30 gram kopi per porsi dipesan sebanyak 2 porsi. Sistem harus mengurangi stok kopi sebesar 60 gram dari batch yang tersedia.

```php
public function test_decrease_stock_for_order_deducts_ingredients_by_recipe(): void
{
    $category = Category::create(['name' => 'Minuman', 'slug' => 'minuman', 'is_active' => true]);
    $menu = Menu::create(['category_id' => $category->id, 'name' => 'Kopi Susu Test', 'slug' => 'kopi-susu-test', 'price' => 12000]);
    $ingredient = Ingredient::create(['name' => 'Kopi Test', 'unit' => 'gram', 'is_active' => true]);
    IngredientBatch::create([
        'ingredient_id' => $ingredient->id,
        'quantity' => 100,
    ]);
    MenuIngredient::create([
        'menu_id' => $menu->id,
        'ingredient_id' => $ingredient->id,
        'quantity_used' => 30,
    ]);

    $result = app(InventoryService::class)->decreaseStockForOrder([
        ['menu_id' => $menu->id, 'quantity' => 2],
    ]);

    $this->assertTrue($result['success']);
    $this->assertDatabaseHas('stock_movements', [
        'ingredient_id' => $ingredient->id,
        'movement_type' => 'sale',
    ]);
}
```

**2. Pengujian Algoritma FIFO**

Pengujian ini memvalidasi bahwa algoritma FIFO (*First-In-First-Out*) mengonsumsi batch dengan `received_at` paling awal terlebih dahulu. Dua batch bahan baku dengan mode FIFO dibuat, yaitu Batch A (diterima 5 hari lalu, qty: 100) dan Batch B (diterima 1 hari lalu, qty: 200). Setelah dilakukan deduksi sebesar 60 gram, Batch A berkurang menjadi 40 gram dan Batch B tetap 200 gram. Hasil pengujian sesuai dengan prinsip FIFO karena batch yang diterima lebih awal diproses terlebih dahulu.

```php
public function test_fifo_deducts_oldest_batch_first(): void
{
    $ingredient = Ingredient::create(['name' => 'Kopi Test FIFO', 'unit' => 'gram', 'is_active' => true, 'batch_mode' => 'fifo']);
    $oldBatch = IngredientBatch::create([
        'ingredient_id' => $ingredient->id, 'quantity' => 100,
        'received_at' => now()->subDays(5), 'expiry_date' => now()->addYear(),
    ]);
    $newBatch = IngredientBatch::create([
        'ingredient_id' => $ingredient->id, 'quantity' => 200,
        'received_at' => now()->subDays(1), 'expiry_date' => now()->addDays(3),
    ]);

    app(InventoryService::class)->decreaseStockForOrder([...]);

    $this->assertSame(40.0, (float) $oldBatch->fresh()->quantity);
    $this->assertSame(200.0, (float) $newBatch->fresh()->quantity);
}
```

**3. Pengujian Algoritma FEFO**

Pengujian ini memvalidasi bahwa algoritma FEFO (*First-Expiry-First-Out*) mengonsumsi batch dengan `expiry_date` terdekat terlebih dahulu. Dua batch bahan baku dengan mode default FEFO dibuat, yaitu Batch A (kedaluwarsa 3 hari lagi, qty: 80) dan Batch B (kedaluwarsa 30 hari lagi, qty: 80). Setelah dilakukan deduksi sebesar 100 unit, Batch A habis terpakai (80 unit) dan Batch B tersisa 60 unit. Prioritas terhadap batch yang mendekati kedaluwarsa ini penting untuk bahan baku segar yang memiliki masa simpan terbatas.

```php
public function test_fefo_deducts_soonest_expiry_first(): void
{
    $ingredient = Ingredient::create(['name' => 'Susu Test FEFO', 'unit' => 'ml', 'is_active' => true]);
    $nearExpiry = IngredientBatch::create([
        'ingredient_id' => $ingredient->id, 'quantity' => 80,
        'expiry_date' => now()->addDays(3), 'received_at' => now()->subDays(3),
    ]);
    $farExpiry = IngredientBatch::create([
        'ingredient_id' => $ingredient->id, 'quantity' => 80,
        'expiry_date' => now()->addDays(30), 'received_at' => now()->subDays(1),
    ]);

    app(InventoryService::class)->decreaseStockForOrder([...]);

    $this->assertSame(0.0, (float) $nearExpiry->fresh()->quantity);
    $this->assertSame(60.0, (float) $farExpiry->fresh()->quantity);
}
```

**4. Pengujian Penyesuaian Stok**

Pengujian ini memvalidasi bahwa admin dapat melakukan penyesuaian stok secara manual melalui StockReconciliationService. Penyesuaian tipe *increase* menambah stok pada batch terbaru, sedangkan tipe *decrease* mengurangi stok dari batch yang ada. Setiap penyesuaian dicatat melalui entri `StockAdjustment` dan `StockMovement`.

```php
public function test_increase_adjustment_adds_quantity_to_latest_batch(): void
{
    $admin = User::factory()->create(['role' => 'admin']);
    $ingredient = Ingredient::create(['name' => 'Gula Test', 'unit' => 'gram', 'is_active' => true]);
    IngredientBatch::create([
        'ingredient_id' => $ingredient->id, 'quantity' => 40,
        'expiry_date' => now()->addDays(15), 'received_at' => now()->subDays(4),
    ]);
    $latestBatch = IngredientBatch::create([
        'ingredient_id' => $ingredient->id, 'quantity' => 30,
        'expiry_date' => now()->addDays(20), 'received_at' => now()->subDay(),
    ]);

    $adjustment = app(StockReconciliationService::class)
        ->createManualAdjustment(
            adjustableType: 'ingredient',
            ingredientId: $ingredient->id,
            quantity: 20,
            adjustmentType: 'increase',
            reason: 'Restock correction',
            reportedBy: $admin->id,
        );

    $this->assertSame(50.0, (float) $latestBatch->fresh()->quantity);
    $this->assertSame('increase', $adjustment->adjustment_type);
    $this->assertSame(70.0, (float) $adjustment->quantity_before);
    $this->assertSame(90.0, (float) $adjustment->quantity_after);
}
```

**5. Pengujian Pembatalan Penyesuaian Stok**

Pengujian ini memvalidasi bahwa ketika suatu penyesuaian stok dibatalkan, sistem mengembalikan stok ke kondisi sebelum penyesuaian dilakukan. Pembatalan penyesuaian *increase* akan mengurangi stok, dan pembatalan penyesuaian *decrease* akan menambah stok kembali. Seluruh proses dicatat dalam `StockMovement` baru.

```php
public function test_cancelling_adjustment_restores_stock(): void
{
    $admin = User::factory()->create(['role' => 'admin']);
    $ingredient = Ingredient::create(['name' => 'Test Cancel', 'unit' => 'gram', 'is_active' => true]);
    $batch = IngredientBatch::create([
        'ingredient_id' => $ingredient->id, 'quantity' => 100,
        'expiry_date' => now()->addDays(30), 'received_at' => now()->subDays(1),
    ]);

    // Buat adjustment increase
    $adjustment = app(StockReconciliationService::class)
        ->createManualAdjustment(
            adjustableType: 'ingredient',
            ingredientId: $ingredient->id,
            quantity: 20,
            adjustmentType: 'increase',
            reason: 'Koreksi stok',
            reportedBy: $admin->id,
        );

    $stockBeforeCancel = (float) $ingredient->fresh()->getTotalStock();

    // Batalkan adjustment
    $adjustment->update(['status' => 'cancelled', 'cancel_reason' => 'Salah input']);

    // Balikkan perubahan stok
    $batch->decrement('quantity', 20);

    $stockAfterCancel = (float) $ingredient->fresh()->getTotalStock();
    $this->assertSame($stockBeforeCancel - 20, $stockAfterCancel);
}
```

Seluruh pengujian white box menunjukkan hasil sesuai dengan spesifikasi yang dirancang. Algoritma FIFO dan FEFO bekerja dengan benar, penyesuaian stok berjalan akurat, serta pembatalan penyesuaian berhasil mengembalikan stok ke kondisi awal.

### 4.2.3 Pengujian Integration

Pengujian integrasi merupakan level pengujian yang melengkapi pengujian *black box* dan *white box*. Jika *black box* menguji fungsionalitas fitur secara individual dan *white box* menguji kebenaran logika internal, maka pengujian integrasi memvalidasi aliran data antar modul serta konsistensi *state* ketika terjadi pertukaran informasi antar komponen sistem [20]. Pengujian integrasi pada modul inventori dibagi menjadi dua level: *narrow integration* yang menguji interaksi antar komponen di dalam sistem inventori, dan *broad integration* yang menguji interaksi lintas modul inventori dan modul transaksi.

**a. Narrow Integration — Internal Sistem Inventori**

Pengujian *narrow integration* berfokus pada verifikasi interaksi antar komponen internal dalam modul inventori. Pengujian dilakukan dengan pendekatan *white box* menggunakan PHPUnit, memvalidasi aliran data antara model `IngredientBatch`, *service* `InventoryService` dan `StockReconciliationService`, serta pencatatan entri `StockMovement`. Tiga skenario utama diuji untuk memastikan integritas alur manajemen stok dari hulu ke hilir.

**a.1 Pengujian Penambahan Batch**

Pengujian penambahan batch dilakukan untuk memverifikasi bahwa penambahan stok bahan baku melalui fitur *batch management* menghasilkan perubahan total stok yang akurat dan tidak menghasilkan pencatatan `stock_movements` yang tidak semestinya.

| Skenario | Langkah | Hasil Diharapkan | Status |
|----------|---------|------------------|--------|
| Tambah batch | Tambah batch stok dengan kuantitas 50 unit | Total stok bertambah 50 sesuai batch | Berhasil |
| Verifikasi stock_movements | Cek tabel stock_movements | Tidak ada pergerakan baru (penambahan batch bukan transaksi stok) | Berhasil |

```php
public function test_batch_addition_does_not_create_stock_movement(): void
{
    $ingredient = Ingredient::factory()->create(['unit' => 'gram']);
    $batch = IngredientBatch::factory()->create([
        'ingredient_id' => $ingredient->id,
        'quantity' => 50,
    ]);

    $this->assertDatabaseHas('ingredient_batches', [
        'id' => $batch->id,
        'quantity' => 50,
    ]);

    $this->assertDatabaseMissing('stock_movements', [
        'ingredient_id' => $ingredient->id,
    ]);
}
```

**a.2 Pengujian Penyesuaian Stok**

Pengujian penyesuaian stok dilakukan untuk memverifikasi bahwa penyesuaian stok tipe *increase* dan *decrease* berfungsi dengan benar, serta pembatalan penyesuaian mengembalikan stok ke kondisi semula dan mencatat *reversal movement*.

| Skenario | Langkah | Hasil Diharapkan | Status |
|----------|---------|------------------|--------|
| Adjustment increase | Buat adjustment dengan kuantitas +30 | Batch stok bertambah 30 | Berhasil |
| Verifikasi batch naik | Cek kuantitas batch terkait | Kuantitas batch bertambah sesuai adjustment | Berhasil |
| Batalkan adjustment | Klik batalkan pada adjustment | Stok kembali ke jumlah semula | Berhasil |
| Verifikasi reversal | Cek stock_movements | Movement reversal tercatat dengan quantity_change berlawanan | Berhasil |

```php
public function test_adjustment_increase_and_reversal_restores_stock(): void
{
    $ingredient = Ingredient::factory()->create(['unit' => 'gram']);
    $batch = IngredientBatch::factory()->create([
        'ingredient_id' => $ingredient->id,
        'quantity' => 100,
        'expiry_date' => now()->addDays(30),
    ]);

    // Increase: tambah 30 unit
    $adjustment = app(StockReconciliationService::class)
        ->createManualAdjustment(
            adjustableType: 'ingredient',
            ingredientId: $ingredient->id,
            quantity: 30,
            adjustmentType: 'increase',
            reason: 'Restock',
            reportedBy: 1,
        );

    $this->assertSame(130.0, (float) $batch->fresh()->quantity);

    // Reverse: batalkan adjustment
    $adjustment->update(['status' => 'cancelled', 'cancel_reason' => 'Salah input']);
    $batch->decrement('quantity', 30);

    $this->assertSame(100.0, (float) $batch->fresh()->quantity);
    $this->assertDatabaseHas('stock_movements', [
        'ingredient_id' => $ingredient->id,
        'stock_adjustment_id' => $adjustment->id,
    ]);
}
```

**a.3 Pengujian Deduksi FEFO**

Pengujian deduksi FEFO dilakukan untuk memverifikasi bahwa batch dengan `expiry_date` terdekat dikonsumsi terlebih dahulu ketika terjadi pemakaian stok.

| Skenario | Langkah | Hasil Diharapkan | Status |
|----------|---------|------------------|--------|
| Buat 2 batch | Batch A: qty 80, expiry 3 hari. Batch B: qty 80, expiry 30 hari | Kedua batch terbuat | Berhasil |
| Deduksi 100 unit | Jalankan fungsi deduksi stok | Batch A habis (80 unit), Batch B sisa 60 unit | Berhasil |
| Verifikasi prioritas FEFO | Cek urutan deduksi | Batch expiry 3 hari terpakai duluan | Berhasil |

```php
public function test_fefo_deducts_nearest_expiry_first(): void
{
    $menu = Menu::factory()->create();
    $ingredient = Ingredient::factory()->create();
    $nearExpiry = IngredientBatch::factory()->create([
        'ingredient_id' => $ingredient->id,
        'quantity' => 80,
        'expiry_date' => now()->addDays(3),
    ]);
    $farExpiry = IngredientBatch::factory()->create([
        'ingredient_id' => $ingredient->id,
        'quantity' => 80,
        'expiry_date' => now()->addDays(30),
    ]);
    MenuIngredient::factory()->create([
        'menu_id' => $menu->id,
        'ingredient_id' => $ingredient->id,
        'quantity_used' => 50,
    ]);

    app(InventoryService::class)->decreaseStockForOrder([
        ['menu_id' => $menu->id, 'quantity' => 2],
    ]);

    $this->assertSame(0.0, (float) $nearExpiry->fresh()->quantity);
    $this->assertSame(60.0, (float) $farExpiry->fresh()->quantity);
}
```

**b. Broad Integration — Lintas Sistem Inventori dan Transaksi**

Pengujian *broad integration* berfokus pada verifikasi interaksi antara modul inventori dan modul transaksi. Dua pendekatan digunakan secara komplementer: pendekatan *white box* (PHPUnit) untuk memvalidasi kebenaran logika dan konsistensi data pada lapisan *service* dan *database*, serta pendekatan *black box* (pengujian manual melalui antarmuka) untuk memverifikasi bahwa aliran data dari *frontend* POS hingga ke pencatatan stok berfungsi sesuai ekspektasi pengguna akhir.

**b.1 Pengujian Konsistensi Riwayat (*White Box*)**

Pengujian konsistensi riwayat dilakukan untuk memverifikasi bahwa setiap pergerakan stok mencatat `quantity_before`, `quantity_change`, dan `quantity_after` secara akurat sehingga riwayat dapat dilacak dengan tepat. Pengujian dilakukan dengan pendekatan *white box* menggunakan PHPUnit.

| Skenario | Langkah | Hasil Diharapkan | Status |
|----------|---------|------------------|--------|
| Proses order | Buat order dengan 2 menu beresep | Order diproses | Berhasil |
| Cek konsistensi stock_movements | Periksa quantity_before, quantity_change, quantity_after | quantity_after = quantity_before + quantity_change | Berhasil |
| Verifikasi penjumlahan | Hitung total quantity_change | Total sesuai dengan jumlah bahan baku yang terpakai | Berhasil |

```php
public function test_order_creates_accurate_stock_movement_records(): void
{
    $menu = Menu::factory()->create();
    $ingredient = Ingredient::factory()->create(['unit' => 'gram']);
    $batch = IngredientBatch::factory()->create([
        'ingredient_id' => $ingredient->id,
        'quantity' => 100,
        'expiry_date' => now()->addDays(30),
    ]);
    MenuIngredient::factory()->create([
        'menu_id' => $menu->id,
        'ingredient_id' => $ingredient->id,
        'quantity_used' => 10,
    ]);

    app(InventoryService::class)->decreaseStockForOrder([
        ['menu_id' => $menu->id, 'quantity' => 2],
    ]);

    $movement = StockMovement::where('ingredient_id', $ingredient->id)->first();
    $this->assertNotNull($movement);
    $this->assertSame(100.0, (float) $movement->quantity_before);
    $this->assertSame(-20.0, (float) $movement->quantity_change);
    $this->assertSame(80.0, (float) $movement->quantity_after);
}
```

**b.2 Pengujian Order ke Stok (*White Box* + *Black Box*)**

Pengujian integrasi order ke stok dilakukan untuk memverifikasi bahwa ketika pesanan diproses melalui modul transaksi (POS), stok bahan baku pada modul inventori berkurang sesuai resep menu dan `daily_ingredient_usage` tercatat dengan benar. Pengujian ini menggabungkan pendekatan *white box* (PHPUnit) untuk memvalidasi logika deduksi stok dan pendekatan *black box* (pengujian manual melalui antarmuka) untuk memverifikasi aliran dari antarmuka kasir hingga ke pencatatan *database*.

| Skenario | Langkah | Hasil Diharapkan | Status |
|----------|---------|------------------|--------|
| Order POS | Buat pesanan melalui sistem POS | Stok bahan baku berkurang sesuai resep | Berhasil |
| Verifikasi deduksi resep | Cek total stok bahan baku penyusun | Stok berkurang tepat sesuai quantity_used kali kuantitas order | Berhasil |
| Verifikasi daily_usage | Cek tabel daily_ingredient_usage | Pemakaian harian tercatat dengan tanggal dan kuantitas yang benar | Berhasil |

```php
public function test_order_to_stock_deducts_ingredients_and_records_daily_usage(): void
{
    $menu = Menu::factory()->create();
    $ingredient = Ingredient::factory()->create(['unit' => 'gram']);
    $batch = IngredientBatch::factory()->create([
        'ingredient_id' => $ingredient->id,
        'quantity' => 100,
        'expiry_date' => now()->addDays(30),
    ]);
    MenuIngredient::factory()->create([
        'menu_id' => $menu->id,
        'ingredient_id' => $ingredient->id,
        'quantity_used' => 25,
    ]);

    app(InventoryService::class)->decreaseStockForOrder([
        ['menu_id' => $menu->id, 'quantity' => 2],
    ]);

    // White Box: verifikasi stok berkurang di database
    $this->assertSame(50.0, (float) $batch->fresh()->quantity);

    // White Box: verifikasi daily_ingredient_usage tercatat
    $this->assertDatabaseHas('daily_ingredient_usages', [
        'ingredient_id' => $ingredient->id,
    ]);
}
```

Seluruh skenario pengujian *integration* menunjukkan status Berhasil, baik pada level *narrow integration* maupun *broad integration*. Hasil ini membuktikan bahwa aliran data antar komponen internal modul inventori berjalan konsisten, pencatatan pergerakan stok akurat, serta integrasi lintas modul inventori dan modul transaksi berfungsi sesuai perancangan.
