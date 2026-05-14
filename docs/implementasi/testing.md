# Bagian 3.5 — Testing Strategy: TDD, Factories & E2E

## Filosofi Testing

Proyek W9 Cafe POS menerapkan **Test-Driven Development (TDD)** secara ketat untuk seluruh fitur baru di fase modul transaksi. Setiap fitur mengikuti siklus **RED → GREEN → REFACTOR**:

1. **RED** — Tulis test dulu. Test harus gagal karena fitur belum ada.
2. **GREEN** — Implementasi kode seminimal mungkin agar test passing.
3. **REFACTOR** — Rapikan kode tanpa mengubah behavior. Test tetap hijau.

Aturan mutlak: **tulis test SEBELUM implementasi.** Tidak ada kode produksi yang ditulis tanpa test yang gagal terlebih dahulu.

### Lapisan Testing

| Lapisan | Scope | Tool | Lokasi |
|---|---|---|---|
| **Unit** | Function, method, single class | PHPUnit | `tests/Unit/` |
| **Feature** | HTTP request, controller flow, database | PHPUnit | `tests/Feature/` |
| **E2E** | Full browser flow (multi-page) | Playwright | `e2e/` |

---

## Unit & Feature Tests (PHPUnit)

### Struktur Test Saat Ini

Proyek sudah punya 32 file test yang mencakup modul Inventory, Finance, Admin, dan Promotion. Berikut gambaran cakupan:

| Modul | File Test | Status |
|---|---|---|
| Inventory | `InventoryServiceFefoTest`, `InventoryRollbackTest`, `DailyIngredientUsageAggregationTest`, `InventoryFoundationMigrationTest` | ✅ Ada |
| Promotion | `PromotionServiceTest`, `PromotionCrudTest` | ✅ Ada |
| Finance | `FinanceModuleTest`, `FinancialReportServiceTest` | ✅ Ada |
| Admin | `AdminDashboardSmokeTest`, `AdminAuthSmokeTest`, `AdminCurrentResourcesSmokeTest` | ✅ Ada |
| Order | `OrderPromotionIntegrationTest` | ✅ Ada |
| Menu | `MenuRecipeParityTest`, `MenuRecipeModelTest`, `MenuImageAccessorTest`, `MenuImageUploadTest` | ✅ Ada |
| Stock | `StockAdjustmentFlowTest` | ✅ Ada |
| **Transaksi (POS)** | — | ❌ Belum ada |
| **Kitchen (KDS)** | — | ❌ Belum ada |
| **Customer (Auth)** | — | ❌ Belum ada |
| **Verifikasi** | — | ❌ Belum ada |
| **Struk** | — | ❌ Belum ada |
| **QRIS** | — | ❌ Belum ada |

**Yang perlu ditambahkan untuk fase ini:** minimal 12-15 test file baru.

### Test yang Perlu Dibuat

#### Auth Tests

**`tests/Feature/Auth/CustomerLoginTest.php`**
```php
class CustomerLoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function customer_can_login_with_name_and_nim()
    {
        User::factory()->create([
            'name' => 'Budi Santoso',
            'nim'  => '21120122140001',
            'role' => 'customer',
        ]);

        $response = $this->post('/customer/login', [
            'username' => 'Budi Santoso',
            'password' => '21120122140001',
        ]);

        $response->assertRedirect('/customer/menu');
        $this->assertAuthenticated();
    }

    /** @test */
    public function customer_cannot_login_with_wrong_nim()
    {
        User::factory()->create([
            'name' => 'Budi Santoso',
            'nim'  => '21120122140001',
            'role' => 'customer',
        ]);

        $response = $this->post('/customer/login', [
            'username' => 'Budi Santoso',
            'password' => '99999999999999',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    /** @test */
    public function cashier_cannot_login_via_customer_login()
    {
        User::factory()->create([
            'name' => 'Kasir Satu',
            'role' => 'cashier',
        ]);

        $response = $this->post('/customer/login', [
            'username' => 'Kasir Satu',
            'password' => 'anypassword',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }
}
```

#### Order Tests

**`tests/Feature/Order/PosFlowTest.php`**
```php
class PosFlowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function cashier_can_create_order_via_pos()
    {
        $cashier = User::factory()->cashier()->create();
        $menu    = Menu::factory()->create(['price' => 15000]);

        $this->actingAs($cashier)
            ->post('/cashier/pesanan-baru', [
                'items' => [
                    ['menu_id' => $menu->id, 'quantity' => 2],
                ],
                'payment_method' => 'cash',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'total_amount'  => 30000,
            'payment_status' => 'paid',
            'status'         => 'completed',
        ]);
    }

    /** @test */
    public function order_requires_at_least_one_item()
    {
        $cashier = User::factory()->cashier()->create();

        $this->actingAs($cashier)
            ->post('/cashier/pesanan-baru', [
                'items'          => [],
                'payment_method' => 'cash',
            ])
            ->assertSessionHasErrors('items');
    }
}
```

#### Kitchen Tests

**`tests/Feature/Kitchen/KdsBumpTest.php`**
```php
class KdsBumpTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function order_can_be_bumped_from_pending_to_preparing()
    {
        $order = Order::factory()->pending()->create();

        $this->actingAs(User::factory()->cashier()->create())
            ->patch("/api/kds/orders/{$order->id}/bump", [
                'status' => 'preparing',
            ])
            ->assertOk();

        $this->assertEquals('preparing', $order->fresh()->status);
    }

    /** @test */
    public function order_cannot_be_bumped_backward()
    {
        $order = Order::factory()->preparing()->create();

        $this->actingAs(User::factory()->cashier()->create())
            ->patch("/api/kds/orders/{$order->id}/bump", [
                'status' => 'pending',
            ])
            ->assertStatus(422);
    }
}
```

#### Verifikasi Tests

**`tests/Feature/Cashier/VerifikasiAkunTest.php`**
```php
class VerifikasiAkunTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function cashier_can_approve_student_account()
    {
        $customer = User::factory()->customer()->unverified()->create();
        $cashier  = User::factory()->cashier()->create();

        $this->actingAs($cashier)
            ->post("/cashier/verifikasi/{$customer->id}/approve")
            ->assertRedirect();

        $this->assertTrue($customer->fresh()->is_student_verified);
    }

    /** @test */
    public function cashier_can_reject_student_account()
    {
        $customer = User::factory()->customer()->unverified()->create();
        $cashier  = User::factory()->cashier()->create();

        $this->actingAs($cashier)
            ->post("/cashier/verifikasi/{$customer->id}/reject")
            ->assertRedirect();

        $this->assertFalse($customer->fresh()->is_student_verified);
    }
}
```

#### Struk Tests

**`tests/Feature/Receipt/ReceiptPublicAccessTest.php`**
```php
class ReceiptPublicAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function receipt_is_publicly_accessible_without_login()
    {
        $order = Order::factory()->completed()->create([
            'order_code' => 'ORD-TEST-001',
        ]);

        $response = $this->get('/receipt/ORD-TEST-001');

        $response->assertOk();
        $response->assertSee('ORD-TEST-001');
    }

    /** @test */
    public function receipt_shows_404_for_nonexistent_order()
    {
        $this->get('/receipt/NONEXISTENT')->assertNotFound();
    }
}
```

#### QRIS Tests

**`tests/Feature/Payment/QrisUploadTest.php`**
```php
class QrisUploadTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function customer_can_upload_qris_proof()
    {
        Storage::fake('public');
        $customer = User::factory()->customer()->create();
        $order    = Order::factory()->unpaid()->create([
            'customer_id' => $customer->id,
        ]);

        $this->actingAs($customer)
            ->post("/customer/order/{$order->id}/upload-qris", [
                'payment_proof' => UploadedFile::fake()->image('bukti.jpg'),
            ])
            ->assertRedirect();

        Storage::disk('public')->assertExists($order->fresh()->payment_proof);
    }

    /** @test */
    public function qris_proof_must_be_image()
    {
        $customer = User::factory()->customer()->create();
        $order    = Order::factory()->unpaid()->create(['customer_id' => $customer->id]);

        $this->actingAs($customer)
            ->post("/customer/order/{$order->id}/upload-qris", [
                'payment_proof' => UploadedFile::fake()->create('bukan_gambar.pdf'),
            ])
            ->assertSessionHasErrors('payment_proof');
    }
}
```

### Menjalankan Test

```bash
# Semua test
php artisan test --parallel

# Hanya unit test
php artisan test --testsuite=Unit --parallel

# Hanya feature test
php artisan test --testsuite=Feature --parallel

# Filter spesifik
php artisan test --filter=PosFlowTest --parallel
```

---

## Factories

### Kondisi Saat Ini

Hanya **satu factory** yang tersedia: `UserFactory`. Semua model lain belum punya factory. Ini harus segera diperbaiki karena factory adalah fondasi dari TDD.

### Daftar Factory yang Harus Dibuat

| Model | Factory File | Prioritas |
|---|---|---|
| `User` | `database/factories/UserFactory.php` | ✅ Sudah ada (perlu update definisi state) |
| `Category` | `database/factories/CategoryFactory.php` | 🔴 Tinggi |
| `Menu` | `database/factories/MenuFactory.php` | 🔴 Tinggi |
| `Order` | `database/factories/OrderFactory.php` | 🔴 Tinggi |
| `OrderItem` | `database/factories/OrderItemFactory.php` | 🔴 Tinggi |
| `Payment` | `database/factories/PaymentFactory.php` | 🔴 Tinggi |
| `CafeTable` | `database/factories/CafeTableFactory.php` | 🟡 Medium |
| `Ingredient` | `database/factories/IngredientFactory.php` | 🟡 Medium |
| `IngredientBatch` | `database/factories/IngredientBatchFactory.php` | 🟡 Medium |
| `MenuIngredient` | `database/factories/MenuIngredientFactory.php` | 🟡 Medium |
| `StockAdjustment` | `database/factories/StockAdjustmentFactory.php` | 🟢 Rendah |
| `StockMovement` | `database/factories/StockMovementFactory.php` | 🟢 Rendah |
| `Promotion` | `database/factories/PromotionFactory.php` | 🟡 Medium |
| `PromotionRule` | `database/factories/PromotionRuleFactory.php` | 🟡 Medium |
| `AppliedPromotion` | `database/factories/AppliedPromotionFactory.php` | 🟡 Medium |
| `Receivable` | `database/factories/ReceivableFactory.php` | 🟢 Rendah |
| `Expense` | `database/factories/ExpenseFactory.php` | 🟢 Rendah |
| `CashierSession` | `database/factories/CashierSessionFactory.php` | 🟢 Rendah |
| `UnexpectedTransaction` | `database/factories/UnexpectedTransactionFactory.php` | 🟢 Rendah |

### UserFactory (Update)

Factory yang sudah ada perlu ditambahkan state methods:

```php
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name'              => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => static::$password ??= bcrypt('password'),
            'remember_token'    => Str::random(10),
            'role'              => 'customer',
            'is_student_verified' => false,
        ];
    }

    public function cashier(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'cashier',
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }

    public function customer(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'customer',
            'nim'  => fake()->numerify('21120122######'),
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_student_verified' => true,
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_student_verified' => false,
        ]);
    }
}
```

### Contoh Factory Baru

#### CategoryFactory

```php
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Kopi', 'Teh', 'Coklat', 'Snack', 'Roti', 'Makanan Berat',
        ]);

        return [
            'name'  => $name,
            'slug'  => Str::slug($name),
            'is_active' => true,
        ];
    }
}
```

#### MenuFactory

```php
class MenuFactory extends Factory
{
    public function definition(): array
    {
        $names = [
            'Kopi Robusta', 'Kopi Latte', 'Teh Tarik', 'Coklat Panas',
            'Roti Bakar', 'Pisang Goreng', 'Nasi Goreng', 'Mie Instan',
        ];

        return [
            'category_id'   => Category::factory(),
            'name'          => fake()->unique()->randomElement($names),
            'slug'          => fn (array $attrs) => Str::slug($attrs['name']),
            'description'   => fake()->sentence(),
            'price'         => fake()->numberBetween(5000, 35000),
            'is_available'  => true,
            'is_student_discount' => false,
            'student_price'  => null,
        ];
    }

    public function withStudentDiscount(): static
    {
        return $this->state(fn (array $attr) => [
            'is_student_discount' => true,
            'student_price'       => (int) ($attr['price'] * 0.9),
        ]);
    }

    public function unavailable(): static
    {
        return $this->state(fn (array $attr) => [
            'is_available' => false,
        ]);
    }
}
```

#### OrderFactory

```php
class OrderFactory extends Factory
{
    private static int $counter = 0;

    public function definition(): array
    {
        static::$counter++;

        return [
            'order_code'     => sprintf('ORD-%06d', static::$counter),
            'table_id'       => CafeTable::factory(),
            'customer_id'    => User::factory()->customer(),
            'cashier_id'     => User::factory()->cashier(),
            'status'         => 'pending',
            'order_type'     => 'cashier',
            'payment_status' => 'unpaid',
            'total_amount'   => 0, // Akan dihitung dari OrderItems
            'notes'          => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => 'pending']);
    }

    public function preparing(): static
    {
        return $this->state(fn () => [
            'status'       => 'preparing',
            'preparing_at' => now(),
        ]);
    }

    public function ready(): static
    {
        return $this->state(fn () => ['status' => 'ready']);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status'         => 'completed',
            'payment_status' => 'paid',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => ['status' => 'cancelled']);
    }

    public function unpaid(): static
    {
        return $this->state(fn () => ['payment_status' => 'unpaid']);
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Order $order) {
            if ($order->items()->count() === 0) {
                OrderItem::factory()->count(2)->create([
                    'order_id' => $order->id,
                ]);
            }
            // Hitung total dari items
            $total = $order->items->sum('subtotal');
            if ($total > 0) {
                $order->update(['total_amount' => $total]);
            }
        });
    }
}
```

#### OrderItemFactory

```php
class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        $price = fake()->numberBetween(5000, 35000);
        $qty   = fake()->numberBetween(1, 5);

        return [
            'order_id'   => Order::factory(),
            'menu_id'    => Menu::factory(),
            'quantity'   => $qty,
            'unit_price' => $price,
            'subtotal'   => $price * $qty,
            'notes'      => null,
        ];
    }
}
```

#### PaymentFactory

```php
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id'        => Order::factory(),
            'payment_method'  => fake()->randomElement(['qris', 'cash', 'transfer']),
            'payment_gateway' => 'manual',
            'transaction_id'  => 'TXN-' . Str::upper(Str::random(10)),
            'amount'          => fn (array $attrs) => Order::find($attrs['order_id'])->total_amount ?? 0,
            'status'          => 'pending',
            'paid_at'         => null,
        ];
    }

    public function success(): static
    {
        return $this->state(fn () => [
            'status'  => 'success',
            'paid_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status' => 'failed',
        ]);
    }
}
```

---

## Playwright E2E Tests

### Setup

Playwright sudah terinstal (`@playwright/test` v1.59, config: `playwright.config.ts`).

### Lokasi Test

```
e2e/
├── auth/
│   └── multi-login.spec.ts
├── cashier/
│   └── pos-flow.spec.ts
├── kitchen/
│   └── kds-flow.spec.ts
├── customer/
│   └── order-flow.spec.ts
├── admin/
│   ├── financial-report.spec.ts
│   └── verifikasi.spec.ts
└── visual/
    └── regression.spec.ts
```

### Spesifikasi E2E

#### auth/multi-login.spec.ts — Autentikasi Multi-Role

```
Test 1: Kasir login dengan email & password
  - Buka /login
  - Isi email: kasir@w9cafe.com
  - Isi password: password
  - Klik Masuk
  - Assert: redirect ke /cashier/dashboard
  - Assert: sidebar menampilkan menu kasir

Test 2: Kasir login gagal (password salah)
  - Buka /login
  - Isi email valid, password salah
  - Assert: error "Email atau kata sandi salah"

Test 3: Customer login dengan nama & NIM
  - Buka /customer/login
  - Isi nama: "Budi Santoso"
  - Isi NIM: "21120122140001"
  - Klik Masuk
  - Assert: redirect ke /customer/menu

Test 4: Logout
  - Login sebagai kasir
  - Klik "Keluar" di sidebar
  - Assert: redirect ke /login
  - Assert: akses /cashier/dashboard → redirect ke /login

Test 5: Switch antar akun
  - Login kasir → logout → login customer
  - Assert: tidak ada session mixing
```

#### cashier/pos-flow.spec.ts — Flow POS Kasir

```
Test 1: Pilih menu → cart → bayar → struk
  - Buka /cashier/pesanan-baru
  - Klik menu "Kopi Robusta" → muncul di keranjang
  - Tambah quantity jadi 2x
  - Klik menu "Roti Bakar"
  - Assert: keranjang menampilkan 2 item, total benar
  - Klik "BAYAR Rp ..."
  - Pilih metode "Tunai"
  - Klik "Konfirmasi"
  - Assert: muncul modal struk
  - Assert: ada QR code di struk

Test 2: Cart kosong tidak bisa bayar
  - Buka /cashier/pesanan-baru
  - Assert: tombol BAYAR disabled

Test 3: Search menu
  - Buka /cashier/pesanan-baru
  - Ketik "kopi" di search
  - Assert: hanya menu "Kopi" yang tampil

Test 4: Filter kategori
  - Klik chip "Kopi"
  - Assert: hanya menu kategori Kopi yang tampil
```

#### kitchen/kds-flow.spec.ts — Kitchen Display System

```
Test 1: Order masuk ke Pending
  - Sebagai kasir, buat order baru
  - Sebagai kitchen, buka /kitchen
  - Assert: order baru muncul di kolom Pending

Test 2: Bump Pending → Preparing
  - Tap tombol "Mulai" di order card
  - Assert: card pindah ke kolom Preparing
  - Assert: timer mulai berjalan

Test 3: Bump Preparing → Ready
  - Tap tombol "Siap"
  - Assert: card pindah ke kolom Ready

Test 4: Timer urgensi
  - Order di Preparing selama 6 menit
  - Assert: card berubah warna kuning
  - Order di Preparing selama 11 menit
  - Assert: card berubah warna merah + animasi pulse

Test 5: Filter Minuman/Makanan
  - Klik tab "Minuman"
  - Assert: hanya order dengan item minuman yang tampil
```

#### customer/order-flow.spec.ts — Flow Order Pelanggan

```
Test 1: Customer login → pilih menu → checkout → QRIS
  - Login sebagai customer terverifikasi
  - Buka /customer/menu
  - Klik "Tambah" pada Kopi Robusta
  - Klik "Tambah" pada Roti Bakar
  - Buka /customer/cart
  - Assert: 2 item di keranjang
  - Assert: harga diskon 10% diterapkan (student discount)
  - Klik "Bayar Sekarang"
  - Pilih "QRIS"
  - Upload gambar bukti
  - Assert: redirect ke halaman status
  - Assert: status "Menunggu Konfirmasi"

Test 2: Customer tanpa verifikasi tidak dapat diskon
  - Login sebagai customer belum terverifikasi
  - Tambah menu ke keranjang
  - Assert: total TANPA diskon 10%

Test 3: Riwayat pesanan
  - Login customer
  - Buka /customer/riwayat
  - Assert: daftar pesanan tampil
  - Tap tab "Diproses" → filter
  - Tap "Detail" → lihat detail pesanan
```

#### admin/verifikasi.spec.ts — Verifikasi Akun Mahasiswa

```
Test 1: Kasir menyetujui akun mahasiswa
  - Buka /cashier/verifikasi
  - Cari "Budi Santoso"
  - Klik "Setujui"
  - Assert: status berubah jadi "Disetujui" (hijau)
  - Assert: toast "Akun Budi Santoso berhasil disetujui."

Test 2: Kasir menolak akun mahasiswa
  - Buka /cashier/verifikasi
  - Klik tab "Menunggu"
  - Klik "Tolak" pada salah satu akun
  - Assert: status tetap "Menunggu" (tidak berubah)
  - Assert: toast penolakan

Test 3: Filter tab
  - Klik tab "Disetujui"
  - Assert: hanya akun terverifikasi yang tampil
  - Klik tab "Semua"
  - Assert: semua akun tampil
```

#### admin/financial-report.spec.ts — Laporan Keuangan

```
Test 1: Generate laporan harian
  - Login sebagai admin
  - Buka /admin/reports
  - Pilih periode "Hari Ini"
  - Klik "Generate Report"
  - Assert: AG Grid menampilkan data transaksi
  - Assert: total pendapatan sesuai

Test 2: Export Excel
  - Generate laporan
  - Klik "Export Excel"
  - Assert: file .xlsx terdownload
```

### Menjalankan Playwright

```bash
# Semua test
npx playwright test

# Hanya test spesifik
npx playwright test cashier/pos-flow.spec.ts

# Dengan UI mode
npx playwright test --ui

# Dengan browser tampil
npx playwright test --headed

# Generate screenshot untuk visual regression
npx playwright test --update-snapshots
```

### Environment untuk Playwright

```typescript
// playwright.config.ts
export default defineConfig({
  testDir: './e2e',
  fullyParallel: true,
  retries: 1,
  workers: 2,
  reporter: [['html'], ['list']],
  use: {
    baseURL: 'http://localhost:8080',
    trace: 'on-first-retry',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
    {
      name: 'mobile',
      use: { ...devices['iPhone 13'] },
    },
  ],
  webServer: {
    command: 'php artisan serve --port=8080',
    url: 'http://localhost:8080',
    reuseExistingServer: true,
  },
});
```

---

## Verification Commands

Setelah semua test diimplementasikan, jalankan perintah ini untuk verifikasi:

```bash
# 1. Build frontend — zero warnings
bun run build

# 2. Database migration + seed — sukses
php artisan migrate:fresh --seed

# 3. Semua PHP test — ALL passing
php artisan test --parallel

# 4. Semua Playwright test — ALL passing
npx playwright test

# 5. Pastikan tidak ada regression
git diff --name-only main...HEAD
```

**Target:** Semua command di atas menghasilkan exit code 0 dengan zero failures.

---

## Catatan Penting

1. **TDD ketat.** Setiap PR harus menyertakan test. PR tanpa test akan ditolak saat code review.

2. **Database refresh.** Semua test memakai trait `RefreshDatabase`. Tidak boleh ada test yang bergantung pada data dari test sebelumnya.

3. **Factory > Seeder.** Gunakan factory di test, bukan database seeder. Seeder hanya untuk development dan demo.

4. **Parallel testing.** `php artisan test --parallel` harus selalu berfungsi. Jangan ada test yang konflik satu sama lain karena race condition database.

5. **Playwright: seed sebelum run.** Playwright test memerlukan database yang sudah di-seed. Pastikan `php artisan migrate:fresh --seed` sudah dijalankan sebelum `npx playwright test`.

6. **Visual regression: threshold 1%.** Screenshot comparison di Playwright pakai threshold 1% — toleransi kecil untuk perbedaan rendering antar environment. Jika gagal, periksa apakah perubahan memang disengaja.
