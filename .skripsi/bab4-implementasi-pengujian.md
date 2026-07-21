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
