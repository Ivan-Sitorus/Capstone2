# Bank Pertanyaan Sidang — W9 Cafe POS
## Muhammad Nio Hastungkoro — 21120122140155

---

## KATEGORI A: KELAYAKAN BISNIS

### Q1: Kenapa buat POS sendiri? Kan sudah banyak yang gratis?

> **A:** "Yang gratis tidak memiliki fitur batch tracking FEFO/FIFO dan resep terintegrasi. PKasir memiliki FEFO tapi berbasis desktop Rust — tidak bisa diakses via browser. Kasapro memiliki expiry tracking tapi tidak bisa memilih mode per bahan. Sistem ini mengisi celah yang tidak terlayani."

### Q2: Bukankah ini overengineering untuk cafe kecil?

> **A:** "Justru FEFO mencegah kerugian harian. Satu kardus susu expired = Rp 50.000. Implementasinya hanya dua baris `orderBy`. Ini bukan overengineering — ini otomatisasi yang mencegah pemborosan rutin."

### Q3: Biaya deployment-nya berapa?

> **A:** "Nol rupiah untuk software. Cafe sudah punya laptop dan internet. Docker container jalan di hardware yang ada. Tidak ada biaya langganan bulanan."

### Q4: Siapa yang maintain setelah lulus?

> **A:** "Kode dibangun dengan Laravel + Filament — framework populer dengan dokumentasi lengkap. Separated resource pattern memudahkan developer baru memahami struktur dalam hitungan jam. Source code dan dokumentasi teknis diserahkan ke pemilik cafe."

---

## KATEGORI B: IMPLEMENTASI TEKNIS

### Q5: Kenapa pakai Inertia.js bukan REST API?

> **A:** "Semua user internal cafe — tidak ada pihak ketiga yang butuh API. Inertia menyederhanakan arsitektur: routing backend, props langsung dari controller, tidak perlu fetch/axios. Jika butuh API di masa depan, bisa ditambahkan sebagai layer terpisah."

### Q6: Kenapa pakai Filament bukan Laravel Nova atau panel sendiri?

> **A:** "Filament gratis, open source, dan menyediakan CRUD generator dengan separated pattern yang memudahkan maintainability. Fitur seperti Form Builder, Table Builder, dan RelationManager mempercepat development tanpa mengorbankan fleksibilitas."

### Q7: Kenapa PostgreSQL bukan MySQL?

> **A:** "PostgreSQL lebih unggul dalam window functions, CTE, dan row-level locking — fitur yang berguna untuk report inventori dan concurrent stock deduction. Sebagai syarat capstone, PostgreSQL digunakan sebagai database utama."

### Q8: `unsignedBigInteger` untuk harga — kenapa bukan `decimal`?

> **A:** "Menghindari floating point error pada perhitungan uang. BigInteger dalam satuan rupiah (bukan pecahan) memastikan akurasi 100% tanpa rounding error. Format Rupiah ditambahkan di view layer."

### Q9: Bagaimana mekanisme cegah overselling saat 2 kasir klik bayar bersamaan?

> **A:** "`lockForUpdate()` mengunci baris batch yang akan dikurangi. Jika transaksi A dan B terjadi bersamaan, B harus menunggu A selesai. Ini mencegah dua transaksi mengurangi batch yang sama secara simultan."

### Q10: Bagaimana cara kerja deduksi stok otomatis?

> **A:** "Ketika kasir klik Bayar, OrderProcessingService memanggil InventoryService. Sistem mengambil resep menu dari tabel menu_ingredients, menghitung total kebutuhan (quantity_used × jumlah_pesan), memilih batch berdasarkan mode FEFO/FIFO, memvalidasi stok, mengurangi batch, dan mencatat stock_movement — semua dalam satu database transaction."

### Q11: Kalau FEFO/FIFO di level query, apa bedanya dengan sorting manual di Excel?

> **A:** "Excel tidak bisa mencegah transaksi concurrent. Excel tidak bisa otomatis mendeduksi stok saat pesanan diproses. Excel tidak punya `lockForUpdate()`. Excel tidak mencatat immutable stock movement. Bedanya adalah integrasi dan atomicity — bukan algoritma sorting-nya."

---

## KATEGORI C: PENGUJIAN

### Q12: Kenapa ada 42 test gagal dari 146?

> **A:** "42 kegagalan berasal dari isu yang didokumentasikan: (1) penghapusan modul Kitchen yang tidak dihapus dari test, (2) constraint database yang berubah saat migrasi ulang. Tidak mempengaruhi fungsionalitas inti — semuanya pre-existing dan sudah dicatat."

### Q13: Kenapa tidak fix 42 test itu?

> **A:** "Mengubah test yang sudah ada berpotensi mengubah ekspektasi hasil — dan kami fokus pada validasi fungsionalitas baru. 104 test passing sudah mencakup seluruh skenario kritis (FEFO, FIFO, deduksi resep, penyesuaian stok)."

### Q14: Kenapa tidak pakai TDD (Test-Driven Development)?

> **A:** "Test ditulis setelah implementasi untuk memvalidasi kebenaran algoritma, bukan sebagai driver pengembangan. Pendekatan ini umum untuk proyek riset di mana eksplorasi desain lebih diutamakan daripada test-first."

### Q15: Kenapa tidak pakai UAT (User Acceptance Testing)?

> **A:** "UAT membutuhkan pengguna nyata yang menggunakan sistem dalam periode waktu tertentu. Batasan waktu tugas akhir dan kondisi cafe yang belum beroperasi dengan sistem ini menjadi kendala. Sebagai gantinya, black box testing mensimulasikan 15 skenario User Acceptance."

---

## KATEGORI D: KEAMANAN

### Q16: Apakah sistem rentan SQL Injection?

> **A:** "Tidak. 90% query menggunakan Eloquent yang otomatis parameterized. 10% sisanya adalah raw SQL dengan explicit `?` binding. Audit lengkap menunjukkan zero celah."

### Q17: Bagaimana keamanan autentikasi?

> **A:** "Laravel handle bcrypt hashing, CSRF token, session regeneration, dan rate limiting (5 percobaan sebelum lockout). Route di-protect oleh middleware `auth:web` dan `role:cashier,admin`."

### Q18: Bisa bypass login?

> **A:** "3 skenario black box memvalidasi: user valid → masuk, user invalid → ditolak, guest → redirect. Tidak ada celah yang ditemukan."

### Q19: Bagaimana proteksi mass assignment?

> **A:** "Semua model memiliki properti `$fillable`. Tidak ada `$request->all()` yang digunakan langsung di `create()` atau `update()`."

---

## KATEGORI E: KEPUTUSAN DESAIN

### Q20: Kenapa pakai service layer pattern? Bukankah controller langsung ke model lebih simpel?

> **A:** "Service layer memisahkan business logic dari presentation logic. InventoryService bisa di-test tanpa melalui HTTP. Jika logika deduksi stok berubah, cukup edit satu file — tidak perlu ubah 5 controller. Ini maintainability jangka panjang."

### Q21: Kenapa `canEdit()` default true (bebas edit)?

> **A:** "Admin = owner cafe. Tidak ada auditor eksternal. Kalau salah input, dia bisa langsung edit tanpa prosedur cancel + buat baru. KISS — zero kode tambahan untuk yang tidak dibutuhkan."

### Q22: Resep menu — kenapa tidak cukup pakai stok menu biasa?

> **A:** "Stok menu biasa mengurangi 1 dari total porsi. Resep menu memungkinkan deduksi berdasarkan komposisi aktual — 1 Kopi Susu = 10gr kopi + 5gr gula. Ini yang memungkinkan FEFO/FIFO bekerja secara akurat per bahan."

### Q23: Kenapa soft delete untuk Menu tapi hard delete untuk Category?

> **A:** "Menu — soft delete karena pesanan lama tetap perlu menampilkan nama menu. Category — hard delete dengan validasi (tidak bisa hapus jika masih ada menu di dalamnya). Setiap kebijakan dibuat berdasarkan kebutuhan bisnis spesifik."

### Q24: Kenapa stok menu dihapus dari `$appends`?

> **A:** "Accessor berjalan SETIAP kali model di-serialize, termasuk saat tidak butuh stok. Dengan melepas dari `$appends`, stok hanya dihitung di halaman yang memang membutuhkannya. Dampak: dari 200+ query per halaman menjadi 3 query."

---

## KATEGORI F: TECH STACK

### Q25: Kenapa PHP/Laravel bukan Go atau Node.js?

> **A:** "Laravel menyediakan ekosistem yang mature untuk POS: Eloquent ORM, Filament admin panel, Inertia.js, queue, caching, dan authentication. Untuk cafe skala kecil, overhead PHP tidak signifikan. Kecepatan development lebih penting daripada raw performance."

### Q26: Kenapa React bukan Livewire?

> **A:** "React memberikan kontrol lebih besar pada interaksi UI yang kompleks — seperti cart kasir yang responsif dan grid menu dengan filter real-time. Livewire lebih cocok untuk halaman yang didominasi form."

### Q27: Docker — bukankah ini berat untuk cafe?

> **A:** "Docker menyederhanakan deployment. Cukup `docker compose up -d` — semua service jalan. Cafe tidak perlu install PHP, PostgreSQL, atau Nginx manual. Resource overhead minimal untuk 1 container."

### Q28: Module data mining — bagaimana komunikasinya?

> **A:** "Python FastAPI sebagai service REST terpisah. Panel admin Filament mengirim request ke endpoint Python untuk menjalankan algoritma. Hasilnya disimpan di database PostgreSQL yang sama. Dua teknologi berbeda — satu database bersama."

---

## KATEGORI G: KONTRIBUSI AKADEMIS

### Q29: Apa kontribusi spesifik kamu sebagai individu di capstone tim?

> **A:** "Saya bertanggung jawab penuh atas modul manajemen inventori: perancangan database inventory, implementasi algoritma FEFO/FIFO, service layer (InventoryService, StockReconciliationService), integrasi dengan modul transaksi, panel admin inventori di Filament, serta pengujian white box dan gray box untuk skenario inventori."

### Q30: Apa perbedaan skripsi ini dengan skripsi POS lain yang sudah ada?

> **A:** "Mayoritas skripsi POS hanya mencakup CRUD transaksi dan laporan. Skripsi ini menambahkan: (1) batch management dengan FEFO/FIFO yang bisa dipilih per bahan, (2) deduksi stok otomatis berdasarkan resep menu, (3) immutable stock ledger untuk audit trail, (4) integrasi dengan modul data mining. Ini yang membedakannya dari skripsi POS pada umumnya."

### Q31: Apa novelty / kebaruan dari sistem ini?

> **A:** "Kebaruan sistem ini bukan pada teknologi yang digunakan, melainkan pada integrasi batch management FEFO/FIFO yang dapat dipilih per bahan baku dengan resep menu dan deduksi otomatis dalam satu kesatuan sistem POS. Tidak ada POS skala UKM yang menyediakan kombinasi ini secara gratis dan open source."

---

## KATEGORI H: HAL-HAL KRITIS LAINNYA

### Q32: Bagaimana rencana backup data?

> **A:** "PostgreSQL menyediakan `pg_dump` untuk backup. Bisa dijadwalkan via cron job harian. Docker volume bisa di-copy secara manual. Namun fitur backup otomatis belum diimplementasikan — ini masuk dalam saran pengembangan."

### Q33: Bagaimana jika listrik padam saat transaksi?

> **A:** "Transaksi dibungkus dalam database transaction. Jika listrik padam di tengah proses, transaksi tidak pernah commit — data tetap konsisten. Pesanan yang belum selesai hilang, tapi tidak ada data parsial yang korup."

### Q34: Apakah sistem bisa digunakan tanpa internet?

> **A:** "Saat ini belum mendukung offline mode. Semua operasi — POS, admin panel, dan customer menu — membutuhkan koneksi ke server. Offline mode ada dalam saran pengembangan."

### Q35: Bagaimana cara install sistem ini?

> **A:** "Step-by-step: install Docker, clone repository, `docker compose --profile prod up -d`, `php artisan migrate --seed`. Total 10 menit. Dokumentasi ada di README."

### Q36: Apakah bisa menerima pembayaran QRIS/online?

> **A:** "Saat ini pembayaran QRIS menggunakan metode manual — pelanggan scan gambar QRIS yang disediakan, upload bukti transfer, kasir konfirmasi. Integrasi payment gateway seperti Midtrans ada dalam saran pengembangan."

### Q37: Berapa persen kontribusi kode yang benar-benar kamu tulis?

> **A:** "Saya menulis 100% kode di modul inventori: 2 service, 4 model, 6 migration, 6 halaman Filament, 15 test files. Total sekitar 60% dari kode inventori. Modul transaksi ditulis Ivan, data mining oleh Ruben."

---

## RINGKASAN — Pertanyaan Paling Berbahaya (Top 5)

| # | Pertanyaan | Risiko |
|:---:|-----------|:---:|
| 1 | "Ini cuma CRUD biasa — mana kontribusi teknik komputer-nya?" | 🔴 Tinggi |
| 2 | "FEFO/FIFO kan cuma sorting — terlalu sederhana untuk skripsi" | 🔴 Tinggi |
| 3 | "Kenapa buat POS sendiri kalau sudah banyak yang gratis?" | 🔴 Tinggi |
| 4 | "Kenapa test ada 42 yang gagal?" | 🟡 Sedang |
| 5 | "Apa bedanya project ini dengan skripsi POS yang sudah ada?" | 🟡 Sedang |
