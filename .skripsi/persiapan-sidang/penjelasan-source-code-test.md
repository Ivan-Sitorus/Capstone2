# Penjelasan Source Code: White Box & Gray Box Testing
## Untuk Sidang Skripsi — Muhammad Nio Hastungkoro

---

## A. WHITE BOX — FEFO Test

**File:** `tests/Unit/Inventory/InventoryServiceFefoTest.php`
**Method:** `test_fefo_deducts_soonest_expiry_first()`

### Baris Per Baris

```php
$category = Category::create([
    'name' => 'Minuman Test FEFO', 'slug' => 'minuman-test-fefo', 'is_active' => true
]);
$ingredient = Ingredient::create([
    'name' => 'Susu Test FEFO', 'unit' => 'ml', 'is_active' => true
]);
```

> "Saya membuat satu kategori 'Minuman Test FEFO' dan satu bahan baku 'Susu Test FEFO' dengan satuan mililiter. Saya sengaja memilih 'ml' karena bahan cair seperti susu memang diukur dalam mililiter di cafe — ini realistis dengan operasional daily."

```php
$nearExpiry = IngredientBatch::create([
    'ingredient_id' => $ingredient->id, 'quantity' => 80,
    'expiry_date' => now()->addDays(3), 'received_at' => now()->subDays(3),
]);
$farExpiry = IngredientBatch::create([
    'ingredient_id' => $ingredient->id, 'quantity' => 80,
    'expiry_date' => now()->addDays(30), 'received_at' => now()->subDays(1),
]);
```

> **Kenapa angka 80 dan 80?** "Saya sengaja membuat dua batch dengan **jumlah yang sama (80 ml)** tetapi **expiry_date berbeda** — satu expired 3 hari lagi, satu 30 hari lagi. Dengan jumlah yang sama, saya bisa dengan mudah melihat batch mana yang dipilih oleh algoritma: batch yang expired 3 hari harus habis duluan (0), batch yang expired 30 hari harus berkurang sesuai kebutuhan. Jika jumlahnya tidak sama, saya tidak bisa dengan yakin mengatakan bahwa algoritma memilih berdasarkan expiry — bisa jadi karena jumlahnya lebih kecil. **Ini adalah validasi murni untuk prioritas FEFO.**"

```php
$menu = Menu::create([
    'category_id' => $category->id, 'name' => 'Menu Test FEFO',
    'slug' => 'menu-test-fefo', 'price' => 10000
]);
MenuIngredient::create([
    'menu_id' => $menu->id, 'ingredient_id' => $ingredient->id, 'quantity_used' => 30
]);
```

> **Kenapa quantity_used = 30?** "Angka 30 dipilih agar kebutuhan total untuk 3 porsi = 90 ml. Dengan 80 ml + 80 ml total 160 ml, kebutuhan 90 ml akan menghabiskan batch pertama (80 → 0) dan mengambil 10 ml dari batch kedua (80 → 70). Ini menghasilkan assert yang mudah diverifikasi."

```php
app(InventoryService::class)->decreaseStockForOrder([
    ['menu_id' => $menu->id, 'quantity' => 3],
]);
```

> "Saya memanggil `decreaseStockForOrder` dengan 3 porsi — artinya total kebutuhan susu = 30 ml × 3 = 90 ml."

```php
$this->assertSame(0.0, (float) $nearExpiry->fresh()->quantity);
$this->assertSame(70.0, (float) $farExpiry->fresh()->quantity);
```

> **Kenapa assert 0.0 dan 70.0?** "Karena FEFO harus memprioritaskan batch yang expired duluan. Batch `nearExpiry` (expired 3 hari) harus habis duluan — dari 80 menjadi 0 (habis 80 ml). Batch `farExpiry` (expired 30 hari) hanya berkurang 10 ml — dari 80 menjadi 70. **Jika assert ini gagal — misalnya batch yang expired 30 hari yang habis — berarti algoritma FEFO saya rusak.**"

> `assertSame` bukan `assertEquals`: "Saya menggunakan `assertSame`, bukan `assertEquals`, karena `assertSame` membandingkan **tipe dan value** — float 0.0 bukan integer 0. Jika ada konversi tipe yang salah, `assertSame` akan menangkapnya."

---

### Kedua: `test_decrease_stock_for_order_uses_fefo_batches_first()`

```php
$oldBatch = IngredientBatch::create([
    'ingredient_id' => $ingredient->id, 'quantity' => 100,
    'expiry_date' => now()->addDays(3), 'received_at' => now()->subDays(3),
    'cost_per_unit' => 1,
]);
$newBatch = IngredientBatch::create([
    'ingredient_id' => $ingredient->id, 'quantity' => 200,
    'expiry_date' => now()->addDays(30), 'received_at' => now()->subDays(1),
    'cost_per_unit' => 1,
]);
```

> **Kenapa 100 vs 200?** "Saya sengaja membuat jumlah berbeda: batch lama 100 gram, batch baru 200 gram. Ini untuk memverifikasi bahwa algoritma tetap memilih batch lama duluan meskipun jumlahnya lebih kecil. Kebutuhan untuk 2 porsi dengan resep 30g/porsi = 60 gram. Batch lama (100g, expired 3 hari) harus berkurang menjadi 40g. Batch baru (200g, expired 30 hari) harus tetap 200g. Jika batch baru yang berkurang — berarti algoritma mengambil batch dengan jumlah lebih besar terlebih dahulu, bukan berdasarkan expiry — dan test akan gagal."

```php
$this->assertTrue($result['success']);
$this->assertSame(40.0, (float) $oldBatch->quantity);
$this->assertSame(200.0, (float) $newBatch->quantity);
$this->assertDatabaseHas('stock_movements', [
    'ingredient_id' => $ingredient->id,
    'ingredient_batch_id' => $oldBatch->id,
    'movement_type' => 'sale',
]);
```

> "Saya memverifikasi: (1) return sukses, (2) batch lama 100 → 40 (berkurang 60), (3) batch baru tetap 200, (4) ada stock_movement dengan batch_id mengacu ke batch lama — **bukti bahwa batch lamelah yang terpilih.**"

---

## B. WHITE BOX — FIFO Test

**File:** `tests/Unit/Inventory/InventoryServiceFifoTest.php`
**Method:** `test_decrease_stock_for_order_deducts_ingredients_by_recipe()`

```php
$category = Category::create(['name' => 'Minuman', 'slug' => 'minuman', 'is_active' => true]);
$menu = Menu::create(['category_id' => $category->id, 'name' => 'Kopi Susu Test', ...]);
$ingredient = Ingredient::create(['name' => 'Kopi Test', 'unit' => 'gram', 'is_active' => true]);
IngredientBatch::create([
    'ingredient_id' => $ingredient->id, 'quantity' => 100,
    'expiry_date' => now()->addYear(), 'received_at' => now(),
]);
MenuIngredient::create([
    'menu_id' => $menu->id, 'ingredient_id' => $ingredient->id, 'quantity_used' => 30,
]);
```

> "Test ini memvalidasi **skenario paling dasar**: apakah deduksi stok berdasarkan resep berjalan benar? Saya membuat 1 batch dengan 100 gram kopi, resep 30 gram per porsi. Saya pesan 2 porsi → kebutuhan 60 gram."

```php
$result = app(InventoryService::class)->decreaseStockForOrder([
    ['menu_id' => $menu->id, 'quantity' => 2],
]);
$this->assertTrue($result['success']);
$this->assertDatabaseHas('stock_movements', [
    'ingredient_id' => $ingredient->id,
    'movement_type' => 'sale',
]);
```

> "Saya assert bahwa return sukses dan ada stock_movement tercatat. **Ini adalah smoke test untuk seluruh alur deduksi — jika test ini gagal, berarti ada yang rusak di level fundamental.**"

---

### Kedua: `test_fifo_deducts_oldest_batch_first()`

```php
$ingredient = Ingredient::create([
    'name' => 'Kopi Test FIFO', 'unit' => 'gram', 'is_active' => true,
    'batch_mode' => 'fifo'
]);
$oldBatch = IngredientBatch::create([
    'ingredient_id' => $ingredient->id, 'quantity' => 100,
    'received_at' => now()->subDays(5), 'expiry_date' => now()->addYear(),
]);
$newBatch = IngredientBatch::create([
    'ingredient_id' => $ingredient->id, 'quantity' => 200,
    'received_at' => now()->subDays(1), 'expiry_date' => now()->addDays(3),
]);
```

> **Kenapa angka ini?** "Batch A: received 5 hari lalu, quantity 100, expired 1 tahun lagi. Batch B: received 1 hari lalu, quantity 200, expired 3 hari lagi. **Perhatikan:** Batch B memiliki expiry lebih dekat (3 hari) — jadi jika algoritma FEFO, batch B akan dipilih duluan. Tapi karena mode batch = FIFO, batch A—yang lebih lama received—harus dipilih duluan. Ini adalah **validasi bahwa FIFO mengalahkan FEFO** ketika mode batch diset ke FIFO."

```php
app(InventoryService::class)->decreaseStockForOrder([
    ['menu_id' => $menu->id, 'quantity' => 2], // kebutuhan 60 gram
]);
$this->assertSame(40.0, (float) $oldBatch->fresh()->quantity);
$this->assertSame(200.0, (float) $newBatch->fresh()->quantity);
```

> **Kenapa batch lama harus 40?** "Kebutuhan 60 gram diambil dari batch A (100 gram) — sisa 40. Batch B tidak tersentuh — tetap 200. **Jika batch B yang berkurang, itu berarti algoritma saya menggunakan FEFO meskipun batch_mode=FI**FO — bug."

---

## C. WHITE BOX — Rollback Test (Insufficient Stock)

**File:** `tests/Feature/Inventory/InventoryRollbackTest.php` — sebenarnya GRAY BOX karena memverifikasi efek samping di database

```php
$batch = IngredientBatch::create([
    'ingredient_id' => $ingredient->id, 'quantity' => 20,
    'expiry_date' => now()->addDays(10), 'received_at' => now(),
]);
MenuIngredient::create([
    'menu_id' => $menu->id, 'ingredient_id' => $ingredient->id, 'quantity_used' => 15,
]);
```

> **Kenapa quantity_used 15 dan batch cuma 20?** "Kebutuhan 2 porsi = 15 × 2 = 30 gram. Stok hanya 20 gram. Saya sengaja membuat **defisit 10 gram** untuk memicu exception. Jika kebutuhan = stok, test tidak akan pernah gagal — dan saya tidak bisa memvalidasi rollback."

```php
try {
    $service->decreaseStockForOrder([
        ['menu_id' => $menu->id, 'quantity' => 2],
    ]);
    $this->fail('Expected insufficient stock exception was not thrown.');
    // ^ Baris ini penting: jika exception tidak dilempar, test FAIL
}
catch (Exception $exception) {
    $this->assertStringContainsString('Stok tidak mencukupi', $exception->getMessage());
}
```

> "Saya wrap pemanggilan dalam try-catch. Jika `decreaseStockForOrder` TIDAK melempar exception — padahal stok jelas kurang — maka `$this->fail()` dieksekusi dan test gagal. Jika exception dilempar, saya verifikasi bahwa pesan error-nya mengandung 'Stok tidak mencukupi'."

```php
$batch->refresh();
$this->assertSame(20.0, (float) $batch->quantity);
$this->assertDatabaseCount('stock_movements', 0);
```

> **Kenapa assert batch tetap 20 dan stock_movements = 0?** "Ini adalah **validasi rollback yang paling penting**. Jika exception terjadi, seluruh perubahan harus dikembalikan ke kondisi awal. Batch harus tetap 20 — tidak berkurang. Tidak ada stock_movement yang tercatat — karena transaksi di-rollback. **Jika assert ini gagal — misalnya batch menjadi 19 — berarti ada stock yang terlanjur terpakai sebelum rollback — itu bug serius.**"

---

## D. GRAY BOX — Order → Stock Deduction → Daily Usage

**File:** `tests/Feature/Inventory/DailyIngredientUsageAggregationTest.php`
**Method:** `test_cashier_order_store_immediately_records_daily_usage()`

```php
$cashier = User::factory()->create(['role' => 'cashier']);
```

> "Saya membuat user kasir — karena route `/kasir/pesanan-baru` di-protect oleh middleware `auth:web` dan `role:cashier,admin`. Tanpa ini, request akan di-redirect ke login dan tidak pernah mencapai controller."

```php
$ingredient = $this->createIngredientWithBatch(120);
// ...
MenuIngredient::create([
    'menu_id' => $menu->id, 'ingredient_id' => $ingredient->id, 'quantity_used' => 12,
]);
```

> **Kenapa batch 120 dan quantity_used 12?** "3 porsi × 12 gram = 36 gram kebutuhan. Batch 120 gram — stok cukup. Angka 12 dipilih karena 12 × 3 = 36 — mudah diverifikasi. 120 batch cukup besar sehingga tidak perlu khawatir stok habis."

```php
$this->actingAs($cashier)
    ->postJson('/kasir/pesanan-baru', [
        'uuid' => (string) \Illuminate\Support\Str::uuid7(),
        'payment_method' => 'cash',
        'customer_name' => 'Walk In Test',
        'items' => [
            ['menu_id' => $menu->id, 'quantity' => 3],
        ],
    ])
    ->assertRedirect();
```

> **Saya mengirim HTTP POST request ke route `/kasir/pesanan-baru` — persis seperti yang dilakukan kasir di halaman POS.** Saya menyertakan UUID unik (untuk mencegah duplikasi), metode bayar cash, dan 3 porsi menu. Saya assert bahwa response redirect — bukan error — yang menandakan bahwa pesanan berhasil diproses."

```php
$dailyUsage = DailyIngredientUsage::query()
    ->where('ingredient_id', $ingredient->id)
    ->whereDate('usage_date', now()->toDateString())
    ->first();

$this->assertNotNull($dailyUsage);
$this->assertSame(36.0, (float) $dailyUsage->jumlah_digunakan);
```

> **Kenapa assert 36 gram?** "Resep 12 gram per porsi × 3 porsi = 36 gram. Saya verifikasi bahwa setelah request HTTP, tabel `daily_ingredient_usages` mencatat 36 gram pemakaian untuk bahan tersebut. **Ini membuktikan bahwa seluruh layer bekerja: HTTP request → controller → service → database — secara terintegrasi.** Jika assert ini gagal, berarti ada yang putus di rantai integrasi."

---

## E. GRAY BOX — Idempotent Test

**Method:** `test_processing_same_order_sale_twice_is_idempotent()`

```php
$firstRun = $service->processSaleForOrder($order, $cashier->id);
$secondRun = $service->processSaleForOrder($order, $cashier->id);

$this->assertTrue($firstRun['success']);
$this->assertTrue($secondRun['success']);
$this->assertTrue((bool) ($secondRun['skipped'] ?? false));

$this->assertDatabaseCount('daily_ingredient_usages', 1);
$this->assertDatabaseCount('stock_movements', 1);
```

> **Kenapa saya memproses order yang SAMA dua kali?** "Saya ingin memvalidasi bahwa method `processSaleForOrder` bersifat **idempotent** — jika order yang sama diproses lagi (misalnya karena kasir double-click atau refresh), sistem tidak boleh mengurangi stok dua kali. Setelah run kedua, jumlah `daily_ingredient_usages` tetap 1 dan `stock_movements` tetap 1 — bukan 2. Flag `skipped` menandakan bahwa run kedua di-skip karena stok sudah diproses sebelumnya."
