# Bagian 3.6 — Edge Cases: Daftar & Keputusan

## Daftar 16 Edge Cases

Berikut adalah daftar lengkap edge cases yang teridentifikasi di sistem W9 Cafe POS beserta keputusan penanganannya per 12 Mei 2026.

| # | Modul | Edge Case | Deskripsi | Keputusan | Penanganan |
|---|---|---|---|---|---|
| 1 | **Kitchen / Stock** | Concurrent stock adjustment | Dua staf dapur melaporkan adjustment pada ingredient yang sama dalam waktu hampir bersamaan. Jika tidak ditangani, stok bisa salah hitung (race condition). | Optimistic locking dengan version check di model `Ingredient`. Update hanya berhasil jika version di DB masih sama dengan yang dibaca. Jika gagal, throw `ConcurrentStockUpdateException` dan minta user refresh. | `Ingredient::where('id', $id)->where('version', $v)->update([...])`. Cek affected rows = 0 → retry atau error. |
| 2 | **Kitchen / Stock** | Rejection saat ada order aktif | Kitchen lapor susu tumpah 100ml (pending) → stok berkurang. Lalu order masuk pakai stok yang sudah berkurang. Admin kemudian menolak laporan. Stok harus dikembalikan secara akurat. | Reversal tidak menghitung ulang dari awal, tapi menambah/mengurangi dari stok saat ini sebesar jumlah adjustment semula. `$currentStock + $adjustmentAmount` (bukan set ke nilai lama yang sudah basi). | Simpan `stock_before` di `StockMovement` sebagai referensi, tapi gunakan arithmetic sederhana untuk reversal. |
| 3 | **Customer / Auth** | Nama pelanggan duplikat | Dua mahasiswa dengan nama yang persis sama (misal: dua "Budi Santoso") ingin login. Sistem saat ini mencari user berdasarkan `name` — akan menemukan lebih dari satu. | Cari berdasarkan `name` + `nim` sekaligus. Jika ada dua "Budi Santoso", mereka pasti punya NIM berbeda. Query: `User::where('name', $name)->where('nim', $nim)->where('role', 'customer')->first()`. | Ubah query di `CustomerAuthController@login` dari `->first()` menjadi query yang menyertakan `nim` sebagai filter, bukan hanya sebagai verifikasi. |
| 4 | **POS / Cart** | Quantity nol atau negatif di keranjang | UI memungkinkan user menekan tombol `-` sampai quantity = 0. Atau bug memungkinkan quantity negatif. Ini akan menghasilkan total order Rp 0 atau error perhitungan. | Jika quantity <= 0, item otomatis dihapus dari keranjang (bukan di-set ke 0). Validasi di `cartStore.js`: `updateQty` langsung filter out jika qty <= 0. Validasi server: `min:1` di `StoreOrderRequest`. | Frontend: `qty <= 0 ? items.filter(i => i.menuId !== menuId)`. Backend: `'items.*.quantity' => 'required|integer|min:1'`. |
| 5 | **POS / Order** | Memesan menu yang sudah unavailable | Menu di-set `is_available = false` oleh admin, tapi masih muncul di cache atau halaman POS yang tidak direfresh. Pelanggan atau kasir bisa memesannya. | Cek ketersediaan menu di backend saat order dibuat. Jika menu tidak available, tolak dengan error jelas: "Menu [nama] saat ini tidak tersedia." Frontend: sembunyikan menu unavailable dari grid via query `where('is_available', true)`. | Validasi di `StoreOrderRequest`: setiap `menu_id` dicek `Menu::find($id)->is_available`. Halaman POS hanya me-load menu dengan `->where('is_available', true)`. |
| 6 | **Payment / QRIS** | Bukti QRIS > 5MB | Pelanggan upload foto bukti transfer resolusi tinggi (hasil screenshot HP flagship) yang melebihi batas 5MB. Upload gagal tanpa pesan yang jelas. | Batas 5MB (5120 KB) di-enforce di validasi Laravel. Frontend menampilkan preview + ukuran file sebelum submit. Jika > 5MB, tampilkan pesan "Ukuran file maksimal 5MB. Silakan kompres gambar Anda." Jangan biarkan gagal di server tanpa feedback. | `'payment_proof' => 'required|image|mimes:jpeg,png|max:5120'`. Frontend: baca `file.size` sebelum upload, tampilkan warning jika mendekati batas. |
| 7 | **Kitchen / KDS** | Bump mundur (Ready → Preparing) | Koki tidak sengaja atau sengaja ingin mengembalikan order dari Ready ke Preparing. Secara bisnis, ini bisa terjadi jika makanan yang "sudah siap" ternyata kurang. | Tidak diizinkan via UI. Bump hanya maju: Pending → Preparing → Ready. Jika benar-benar perlu rollback, hanya admin yang bisa via panel Filament. Ini mencegah kebingungan di dapur. | Validasi di `KitchenController@bump`: `if ($newStatus !== $nextStatus) return 422`. Daftar transisi valid: `['pending' => 'preparing', 'preparing' => 'ready']`. |
| 8 | **Customer / Promotion** | Customer belum diverifikasi dapat diskon | Bug memungkinkan customer yang `is_student_verified = false` tetap mendapat diskon 10% karena logika diskon tidak mengecek flag verifikasi. | Setiap perhitungan diskon mahasiswa WAJIB mengecek `$user->is_student_verified`. Tidak cukup hanya `$user->role === 'customer'`. Flag `is_student_verified` adalah gate satu-satunya untuk diskon mahasiswa. | Di `OrderService` atau helper: `if ($user->is_student_verified) { applyDiscount(10); }`. Test: customer_unverified_cannot_get_student_discount. |
| 9 | **Dashboard / UI** | Dashboard dengan 0 transaksi | Kasir login pertama kali di hari itu, belum ada transaksi. Dashboard harus menampilkan state kosong yang informatif, bukan error "Cannot read property 'total_amount' of null". | Tabel transaksi terbaru: tampilkan pesan "Belum ada transaksi hari ini." Stat bar: Rp 0, 0 transaksi, 0 pesanan aktif. Quick actions tetap tampil agar kasir bisa mulai membuat pesanan. Semua nilai numerik fallback ke 0 jika data kosong. | `totalPenjualan ?? 0`, `jumlahTransaksi ?? 0`, `pesananAktif ?? 0`. Tabel: conditional render dengan pesan "Belum ada transaksi hari ini" (sudah diimplementasi di `Dashboard.jsx` baris 153). |
| 10 | **POS / Browser** | Browser refresh saat transaksi aktif | Kasir sedang mengisi keranjang, browser tidak sengaja ke-refresh. Cart di Zustand (in-memory) hilang. Kerugian: harus input ulang semua item. | Cart disimpan di **IndexedDB** via library `idb` setiap kali berubah. Saat halaman dimuat ulang, `cartStore` membaca dari IndexedDB terlebih dahulu. Jika tidak ada, baru pakai initial state kosong. | Pakai `zustand` middleware `persist` dengan storage IndexedDB, atau manual sync di `useEffect` setiap cart berubah. Library `idb` sudah terinstal. |
| 11 | **Customer / Order** | Koneksi terputus saat checkout | Pelanggan di HP, jaringan kampus tidak stabil. Saat tap "Bayar Sekarang", request POST gagal karena timeout. Order bisa jadi terbuat di server tapi responsenya tidak sampai ke client. | Backend: operasi pembuatan order harus idempoten. Gunakan `idempotency_key` (UUID generated by client) untuk mencegah duplicate order jika client retry. Frontend: tampilkan spinner + pesan "Memproses..." dengan timeout handling. Jika timeout, tampilkan dialog "Cek status pesanan Anda" + link ke halaman riwayat. | Idempotency key di-header request: `X-Idempotency-Key: <uuid>`. Backend cek apakah order dengan key ini sudah ada → return existing order. Frontend: `fetch` dengan timeout 15 detik, fallback UI jika gagal. |
| 12 | **POS / Multitab** | Dua tab kasir terbuka bersamaan | Kasir membuka dua tab browser (atau dua monitor) dengan halaman POS. Cart di satu tab tidak sinkron dengan tab satunya. Jika kasir membuat order di tab A, tab B masih menampilkan cart lama. | Cart disimpan per-tab (tidak dishare antar tab). Tidak ada sync lintas tab untuk cart. Untuk data global (seperti daftar pesanan aktif), gunakan polling atau WebSocket (Laravel Echo) yang otomatis update di semua tab. | Cart: per-tab, no cross-tab sync (disengaja — setiap tab adalah sesi POS independen). Pesanan aktif: WebSocket broadcast ke semua listener. Tab B akan otomatis mendapat order baru dari tab A via Echo channel. |
| 13 | **Dashboard / Waktu** | Transaksi tepat di tengah malam | Order dibuat jam 23:59:59, selesai jam 00:00:01. Masuk ke statistik "hari ini" yang mana? Bagaimana dengan cashier session yang berganti hari? | Statistik dashboard harian berdasarkan `DATE(created_at)`, bukan session. Order yang `created_at` di hari Senin tetap masuk statistik Senin meskipun selesai di hari Selasa. Cashier session: shift-change manual di akhir hari oleh kasir (ada tombol "Tutup Shift"). | Query: `whereDate('created_at', today())`. `CashierSession` model punya `opened_at` dan `closed_at` untuk tracking shift. Tidak ada auto-close — kasir harus menutup shift secara manual. |
| 14 | **Customer / QR Meja** | Meja sudah dipakai pelanggan lain | Pelanggan A scan QR meja 3 dan mulai memesan. Pelanggan B (beda HP) juga scan QR meja 3. Sistem harus mencegah meja yang sama dipakai oleh dua pelanggan berbeda secara bersamaan. | Saat pelanggan pertama kali scan QR dan memulai sesi, meja di-lock untuk customer tersebut. Lock dilepas saat: (1) order completed, (2) pelanggan logout, (3) timeout 30 menit tanpa aktivitas. Pelanggan B yang scan meja yang sama akan melihat pesan "Meja sedang digunakan. Silakan pilih meja lain atau tunggu beberapa saat." | Field `locked_by_customer_id` di `cafe_tables` + `locked_at` timestamp. Cek lock sebelum mengizinkan pemesanan. Background job: lepas lock yang expired (>30 menit). |
| 15 | **Struk / Akses** | Order dihapus, struk masih bisa diakses | Admin menghapus order karena suatu alasan (misal: transaksi uji coba). Pelanggan yang sudah menyimpan link struk akan melihat 404. Ini membingungkan. | Order tidak pernah dihapus secara fisik (hard delete). Gunakan soft delete (`SoftDeletes` trait) sehingga data tetap ada. Jika order benar-benar harus dihapus permanen, admin harus memahami konsekuensi: struk tidak bisa diakses lagi. | `use SoftDeletes` di model `Order`. `ReceiptController` query pakai `withTrashed()` agar tetap bisa mengakses struk order yang di-soft-delete. |
| 16 | **Auth / Role** | Customer mencoba akses halaman kasir | Mahasiswa iseng mengubah URL browser ke `/cashier/dashboard`. Tanpa pengecekan role yang ketat, dia bisa melihat data transaksi semua pelanggan — pelanggaran privasi serius. | Middleware role memeriksa `auth()->user()->role` di setiap request ke route kasir. Jika role bukan `cashier` atau `admin`, return 403 atau redirect ke halaman sesuai role. Semua route kasir wajib pakai middleware `role:cashier,admin`. | Middleware `App\Http\Middleware\RoleMiddleware`: cek `in_array($user->role, $roles)`. Route grouping: `Route::middleware(['auth', 'role:cashier'])->group(...)`. Test: customer_cannot_access_cashier_dashboard. |

---

## Ringkasan Prioritas Penanganan

| Prioritas | # | Edge Case | Alasan |
|---|---|---|---|
| 🔴 **Kritis** | 16 | Customer akses halaman kasir | Privasi & keamanan — harus difix sebelum production |
| 🔴 **Kritis** | 5 | Pesan menu unavailable | Bisa menyebabkan order tidak bisa dipenuhi |
| 🔴 **Kritis** | 8 | Diskon tanpa verifikasi | Kerugian finansial cafe |
| 🟡 **Tinggi** | 4 | Quantity nol/negatif | Bisa corrupt data order |
| 🟡 **Tinggi** | 13 | Transaksi tengah malam | Memengaruhi akurasi laporan keuangan |
| 🟡 **Tinggi** | 14 | Meja dipakai bersamaan | Pengalaman pelanggan buruk |
| 🟡 **Tinggi** | 11 | Koneksi putus saat checkout | Bisa menyebabkan double order |
| 🟡 **Tinggi** | 1 | Concurrent stock adjustment | Akurasi inventori |
| 🟢 **Medium** | 3 | Nama pelanggan duplikat | Perlu fix sebelum banyak mahasiswa daftar |
| 🟢 **Medium** | 10 | Browser refresh saat POS | UX kasir — sering terjadi |
| 🟢 **Medium** | 6 | Bukti QRIS > 5MB | UX pelanggan |
| 🟢 **Medium** | 12 | Dua tab kasir | Skenario yang mungkin terjadi |
| 🟢 **Medium** | 2 | Rejection saat order aktif | Skenario jarang tapi perlu penanganan benar |
| 🔵 **Rendah** | 9 | Dashboard kosong | Sudah diimplementasi di Dashboard.jsx |
| 🔵 **Rendah** | 7 | Bump mundur KDS | Dicegah oleh validasi |
| 🔵 **Rendah** | 15 | Order dihapus, struk 404 | Gunakan soft delete |

---

## Status Implementasi (per 12 Mei 2026)

| # | Edge Case | Status | Keterangan |
|---|---|---|---|
| 1 | Concurrent stock adjustment | ❌ Belum | Perlu version field + optimistic locking |
| 2 | Rejection saat order aktif | ❌ Belum | Logic reversal perlu diimplementasi |
| 3 | Nama pelanggan duplikat | ❌ Belum | Query perlu diupdate |
| 4 | Quantity nol/negatif | ✅ Sudah | `cartStore.js` baris 33: `qty <= 0 ? filter` |
| 5 | Menu unavailable | ⚠️ Sebagian | Backend: sudah `where('is_available', true)`. Validasi order: belum. |
| 6 | QRIS > 5MB | ❌ Belum | Perlu validasi frontend + backend |
| 7 | Bump mundur KDS | ❌ Belum | KDS belum diimplementasi |
| 8 | Diskon tanpa verifikasi | ❌ Belum | Promosi mahasiswa belum diimplementasi |
| 9 | Dashboard kosong | ✅ Sudah | `Dashboard.jsx` baris 153: state kosong |
| 10 | Browser refresh | ⚠️ Sebagian | IndexedDB terinstal (`idb`), tapi persist cart belum diimplementasi |
| 11 | Koneksi putus checkout | ❌ Belum | Perlu idempotency key |
| 12 | Dua tab kasir | ⚠️ Sebagian | WebSocket sudah ada (Echo), cart per-tab by design |
| 13 | Transaksi tengah malam | ⚠️ Sebagian | `whereDate('created_at', today())` dipakai. Cashier session close: manual. |
| 14 | Meja dipakai bersamaan | ❌ Belum | Perlu locking mechanism |
| 15 | Struk after delete | ❌ Belum | Soft delete belum diterapkan |
| 16 | Customer akses kasir | ⚠️ Sebagian | Middleware role ada, tapi perlu dipastikan semua route kasir ter-cover |

**Legenda:** ✅ Sudah diimplementasi | ⚠️ Sebagian | ❌ Belum diimplementasi

---

## Rencana Penyelesaian

Semua edge cases di atas harus diselesaikan dalam sprint yang relevan:

| Sprint | Modul | Edge Cases |
|---|---|---|
| Sprint 4 (saat ini) | Auth + Verifikasi | #3, #8, #16 |
| Sprint 5 | POS + Cart | #4, #5, #9, #10, #12 |
| Sprint 6 | Payment + QRIS | #6, #11 |
| Sprint 7 | Kitchen + Stock | #1, #2, #7 |
| Sprint 8 | Meja + Receipt | #13, #14, #15 |
