# TODO — Selesai

## ✅ Validasi Quantity Pesanan (Selesai 27 Mei 2026)

**Perubahan:**
1. `app/Services/InventoryService.php`
   - `decreaseStockForOrder()`: `continue` → `throw new \InvalidArgumentException(...)` (qty <= 0)
   - `canFulfillOrder()`: `continue` → `throw new \InvalidArgumentException(...)` (qty <= 0)
2. `app/Http/Controllers/Cashier/CashierPesananBaruController.php`
   - Tambah `catch (\Exception $e)` → redirect with `->with('error', ...)`
3. `app/Http/Controllers/Cashier/CashierOrderController.php`
   - try-catch di `updateStatus()`, `confirmCash()`, `confirmQris()`, `acceptQrisProof()`
   - Return JSON 500 dengan pesan error
4. `resources/js/Layouts/CustomerLayout.jsx`
   - Tambah `flash.error` handling (toast merah, 5 detik auto-dismiss)
