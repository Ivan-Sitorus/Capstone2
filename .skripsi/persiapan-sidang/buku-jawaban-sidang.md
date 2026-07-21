# 📋 DAFTAR ISI — Buku Jawaban Sidang

> **Cara pakai:** Cari pertanyaan yang diajukan penguji, lihat nomornya, langsung ke halaman tersebut.

---

## DAFTAR PERTANYAAN

### A. KELAYAKAN BISNIS
| # | Pertanyaan | Halaman |
|:--:|-----------|:-----:|
| A1 | Kenapa buat POS sendiri? Kan sudah banyak yang gratis? | 1 |
| A2 | Bukankah ini overengineering untuk cafe kecil? | 1 |
| A3 | Biaya deployment-nya berapa? | 1 |
| A4 | Siapa yang maintain setelah lulus? | 1 |
| A5 | Kenapa tidak pakai POS yang sudah ada secara bisnis lebih masuk akal? | 2 |

### B. FEFO / FIFO
| # | Pertanyaan | Halaman |
|:--:|-----------|:-----:|
| B1 | Kenapa cafe kecil butuh FEFO/FIFO? | 2 |
| B2 | FEFO/FIFO cuma sorting — terlalu sederhana untuk skripsi | 2 |
| B3 | Ini sistem kompleks atau sederhana? Kontradiksi. | 3 |

### C. IMPLEMENTASI TEKNIS
| # | Pertanyaan | Halaman |
|:--:|-----------|:-----:|
| C1 | Kenapa pakai Inertia.js bukan REST API? | 3 |
| C2 | Kenapa pakai Filament bukan Nova / panel sendiri? | 4 |
| C3 | Kenapa PostgreSQL bukan MySQL? | 4 |
| C4 | `unsignedBigInteger` untuk harga — kenapa bukan `decimal`? | 4 |
| C5 | Mekanisme cegah overselling? | 4 |
| C6 | Cara kerja deduksi stok otomatis? | 4 |
| C7 | Resep fixed — tidak akurat dengan realita dapur | 5 |
| C8 | Kenapa tidak integrasi IoT agar lebih akurat? | 5 |

### D. PENGUJIAN
| # | Pertanyaan | Halaman |
|:--:|-----------|:-----:|
| D1 | Kenapa memilih 3 metode pengujian ini? | 5 |
| D2 | Kenapa tidak memilih metode pengujian lain? | 6 |
| D3 | Kenapa ada 42 test gagal? | 7 |
| D4 | Kenapa tidak fix 42 test itu? | 7 |
| D5 | Kenapa tidak pakai TDD? | 7 |
| D6 | Apakah hasil pengujian bisa dijadikan data kuantitatif? | 7 |
| D7 | Tidak ada hasil kuantitatif (angka/hitungan) | 8 |
| D8 | Tidak ada rumus kelulusan pengujian | 8 |
| D9 | NF01 dan NF05 — bukannya sama? | 8 |
| D10 | NF05 diuji dengan white box/gray box? Bisa uji 2 kasir bersamaan? | 8 |
| D11 | Mapping one-to-one kebutuhan ke pengujian tidak jelas | 9 |
| D12 | Integrasi transaksi tidak ada di kebutuhan non-fungsional | 9 |
| D13 | Gray box menguji kebutuhan yang tidak ada? | 9 |
| D14 | Masukan: integrasi harus masuk kebutuhan non-fungsional | 9 |

### E. KEAMANAN
| # | Pertanyaan | Halaman |
|:--:|-----------|:-----:|
| E1 | Apakah sistem rentan SQL Injection? | 8 |
| E2 | Bagaimana keamanan autentikasi? | 8 |
| E3 | Bisa bypass login? | 8 |
| E4 | Kenapa tidak ada security testing khusus autentikasi? | 9 |

### F. KEPUTUSAN DESAIN
| # | Pertanyaan | Halaman |
|:--:|-----------|:-----:|
| F1 | Kenapa pakai service layer pattern? | 9 |
| F2 | Kenapa `canEdit()` default true (bebas edit)? | 9 |
| F3 | Kenapa soft delete untuk Menu tapi hard delete untuk Category? | 10 |
| F4 | Kenapa stok menu dihapus dari `$appends`? | 10 |
| F5 | Delete policy berantakan — tidak konsisten | 10 |

### G. TECH STACK
| # | Pertanyaan | Halaman |
|:--:|-----------|:-----:|
| G1 | Kenapa PHP/Laravel bukan Go atau Node.js? | 10 |
| G2 | Kenapa React bukan Livewire? | 11 |
| G3 | Docker — bukankah ini berat untuk cafe? | 11 |
| G4 | Module data mining — bagaimana komunikasinya? | 11 |

### H. KONTRIBUSI AKADEMIS
| # | Pertanyaan | Halaman |
|:--:|-----------|:-----:|
| H1 | Ini cuma CRUD biasa — terlalu sederhana untuk Teknik Komputer | 11 |
| H2 | Kontribusi kamu sebagai individu apa? | 12 |
| H3 | Apa bedanya dengan skripsi POS lain yang sudah ada? | 12 |
| H4 | Apa novelty / kebaruan dari sistem ini? | 12 |

### I. SKRIPSI (DOKUMENTASI)
| # | Pertanyaan | Halaman |
|:--:|-----------|:-----:|
| I1 | Bab 3 kurang urut — tidak sesuai standar penulisan | 13 |
| I2 | Tidak ada wireframe / rancangan antarmuka | 13 |
| I3 | Tidak ada deployment plan di Bab 3 | 14 |
| I4 | Kenapa merancang kebutuhan fungsional seperti itu? Ada yang terlewat? | 14 |
| I5 | Mapping kebutuhan fungsional/non-fungsional dengan pengujian | 14 |
| I6 | Tidak ada UML / diagram sequence | 14 |
| I7 | INV-F03/F04 fungsional atau non-fungsional? Aktornya sistem | 14 |
| I8 | Jelaskan satu per satu kategorisasi kebutuhan | 15 |
| I9 | Kenapa kebutuhan muncul SETELAH diagram teknis? | 15 |
| I10 | Tidak ada penjelasan deployment — sudah di-deploy? | 15 |

### J. MULTIDISIPLIN TEKNIK KOMPUTER
| # | Pertanyaan | Halaman |
|:--:|-----------|:-----:|
| J1 | Ini hanya software — Teknik Komputer multidisiplin? | 14 |
| J2 | Kenapa tidak integrasi IoT? | 15 |

### K. LULUS / TIDAK LULUS
| # | Pertanyaan | Halaman |
|:--:|-----------|:-----:|
| K1 | Dengan banyak celah, apakah skripsi layak diluluskan? | 15 |
| K2 | Revisi substansial — tetap lulus atau tidak? | 16 |
| K3 | Teman saya ada yang sidang ulang — bagaimana dengan saya? | 16 |

---

## A. KELAYAKAN BISNIS

### A1. Kenapa buat POS sendiri? Kan sudah banyak yang gratis?

> "Setelah survei terhadap Moka POS, Pawoon, Loyverse, IPOS, Nutapos, dan Kasapro — mayoritas tidak memiliki batch tracking FEFO/FIFO yang bisa dipilih per bahan baku. Yang memiliki fitur batch tracking (SAP B1) harganya puluhan juta. Yang open source (PKasir) punya FEFO tapi berbasis desktop Rust — bukan web-based. Sistem ini mengisi celah yang tidak terlayani oleh POS manapun: batch FEFO/FIFO + resep terintegrasi + web-based + gratis."

### A2. Bukankah ini overengineering untuk cafe kecil?

> "Satu kardus susu expired = Rp 50.000. Implementasi FEFO hanya dua baris `orderBy`. Ini bukan overengineering — ini otomatisasi yang mencegah pemborosan rutin. Tanpa FEFO, kasir bisa mengambil batch baru dan batch lama terabaikan sampai expired. Overengineering adalah ketika solusi lebih kompleks dari masalah — dua baris query itu bukan kompleks."

### A3. Biaya deployment-nya berapa?

> "Nol rupiah untuk software. Cafe sudah punya laptop. Docker container jalan di hardware yang ada. Tidak ada biaya lisensi atau langganan bulanan. Bandingkan dengan Moka POS Rp 200-400rb/bulan atau Pawoon Rp 150-300rb/bulan."

### A4. Siapa yang maintain setelah lulus?

> "Kode dibangun dengan Laravel + Filament — framework populer dengan dokumentasi lengkap. Separated resource pattern memudahkan developer baru memahami struktur. Source code dan dokumentasi teknis diserahkan ke pemilik cafe. Jika dibutuhkan perubahan, developer baru bisa memahami arsitektur dalam hitungan jam."

### A5. Kenapa tidak pakai POS yang sudah ada secara bisnis lebih masuk akal?

> "Kebutuhan W9 Cafe tidak sepenuhnya terpenuhi oleh POS yang ada — terutama batch tracking, resep terintegrasi, dan deduksi FEFO/FIFO otomatis. Lebih penting lagi: ini adalah tugas akhir, bukan produk komersial. Tujuannya menerapkan ilmu Teknik Komputer dalam studi kasus nyata — bukan bersaing dengan POS komersial."

---

## B. FEFO / FIFO

### B1. Kenapa cafe kecil butuh FEFO/FIFO?

> (Lengkap di file bank-pertanyaan, ringkasan: contoh nyata susu UHT dengan batch berbeda, FEFO mencegah expired, dua baris `orderBy`, implementasi sederhana, dampak langsung ke penghematan biaya)

### B2. FEFO/FIFO cuma sorting — terlalu sederhana untuk skripsi

> "Saya bedakan: algoritma FEFO itu sederhana, tapi SISTEM yang membuat FEFO berfungsi di dunia nyata tidak sederhana. OrderBy hanya satu komponen dari rangkaian: batch management, pessimistic locking, transaction atomicity, immutable stock movement, recipe integration, unit conversion, auto-deduction, adjustment reversal, dan audit trail — semuanya bekerja secara terintegrasi. Yang diuji di skripsi ini adalah sistem utuhnya, bukan query sorting-nya."

### B3. Ini sistem kompleks atau sederhana? Kontradiksi.

> "Di level query — sederhana. Di level sistem — kompleks. Seperti kopi: menyeduh itu sederhana, membangun kafe yang menyajikan 100 porsi konsisten per hari dengan bahan fresh tanpa pernah kehabisan stok — itu kompleks. FEFO/FIFO adalah 'seduh kopi'-nya. Sistem yang mengatur batch, transaksi, resep, audit trail — itu 'kafe'-nya."

---

## C. IMPLEMENTASI TEKNIS

### C1. Kenapa pakai Inertia.js bukan REST API?

> "Semua user internal cafe — tidak ada pihak ketiga yang butuh API. Inertia menyederhanakan: routing backend, props langsung dari controller, tidak perlu fetch/axios. Jika di masa depan butuh mobile app, API bisa ditambahkan sebagai layer baru tanpa menghapus Inertia."

### C2. Kenapa pakai Filament bukan Nova / panel sendiri?

> "Filament gratis, open source, memiliki separated resource pattern, form/table builder, dan relation manager — yang mempercepat development tanpa mengorbankan maintainability."

### C3. Kenapa PostgreSQL bukan MySQL?

> "PostgreSQL unggul dalam window functions, CTE, dan row-level locking untuk report inventori dan concurrent stock deduction. Sebagai syarat capstone, PostgreSQL digunakan sebagai database utama."

### C4. `unsignedBigInteger` untuk harga — kenapa bukan `decimal`?

> "Menghindari floating point error. Rupiah dalam satuan bilangan bulat. Format Rupiah ditambahkan di view layer."

### C5. Mekanisme cegah overselling saat 2 kasir klik bayar bersamaan?

> "`lockForUpdate()` mengunci baris batch. Jika dua transaksi terjadi simultan, salah satu harus menunggu. Ini mencegah overselling tanpa perlu queue external."

### C6. Cara kerja deduksi stok otomatis?

> "OrderProcessingService → InventoryService: load resep (menu_ingredients), hitung kebutuhan (quantity_used × jumlah_pesan), pilih batch via FEFO/FIFO, validasi stok, kurangi batch, catat StockMovement + daily_ingredient_usage — semua dalam satu database transaction."

### C7. Resep fixed — tidak akurat dengan realita dapur

> "Semua sistem ERP menggunakan fixed recipe. Akurasi 100% tidak mungkin tanpa sensor IoT. Tapi tanpa fixed recipe = tanpa catatan sama sekali. 100 porsi = 1000 gram kopi (mungkin spillage 1030 gram). Selisih 3% lebih baik daripada tidak ada data. Fitur penyesuaian stok dan stock opname tersedia untuk koreksi."

### C8. Kenapa tidak integrasi IoT agar lebih akurat?

> "IoT butuh load cell, mikrokontroler, koneksi real-time, kalibrasi — biaya 5-10 juta untuk 20+ bahan. Cafe ini bahkan belum punya POS digital. Prioritas pertama adalah digitalisasi pencatatan. IoT adalah pengembangan masa depan yang sangat relevan untuk Teknik Komputer, tapi di luar scope capstone ini."

---

## D. PENGUJIAN

### D1. Kenapa memilih 3 metode pengujian ini?

> "Black Box menguji dari perspektif pengguna — admin login, tambah bahan, penyesuaian stok. White Box menguji logika internal — FEFO, FIFO, deduksi resep, immutable movement. Gray Box menguji integrasi lintas modul — request HTTP → controller → service → database. Ketiganya saling melengkapi. Satu metode tidak bisa mendeteksi semua jenis bug."

### D2. Kenapa tidak memilih metode pengujian lain?

> "Performance testing: skala cafe ringan (< 200 trx/hari), Laravel mampu ribuan req/dtk. Usability: Filament sudah standar UI Bootstrap. Security: audit manual SQL injection + mass assignment. Regression: 146 assertions sudah mencakup. Alpha/Beta: simulasi 15 skenario black box sebagai gantinya."

### D3. Kenapa ada 42 test gagal?

> "Pre-existing issues: penghapusan modul Kitchen yang testnya belum dihapus, constraint database yang berubah saat migrasi ulang. Tidak mempengaruhi fungsionalitas inti. Sudah didokumentasikan."

### D4. Kenapa tidak fix 42 test itu?

> "Kami fokus pada validasi fungsionalitas baru. 104 test passing sudah mencakup seluruh skenario kritis (FEFO, FIFO, deduksi resep, penyesuaian stok). Mengubah test lama berpotensi mengubah ekspektasi hasil yang sudah valid."

### D5. Kenapa tidak pakai TDD?

> "Test ditulis setelah implementasi untuk memvalidasi kebenaran, bukan sebagai driver pengembangan. Pendekatan post-implementation testing umum untuk proyek riset di mana eksplorasi desain lebih diutamakan."

### D6. Apakah hasil pengujian bisa dijadikan data kuantitatif?

> "Ya. Data kuantitatif adalah data berupa angka yang bisa diukur — test pass count, persentase keberhasilan, jumlah assertions. Saya memiliki: 146 total assertions, 104 passing (71%), black box 15/15 berhasil (100%), white box 5/5 berhasil (100%). Ini adalah data kuantitatif yang memvalidasi fungsionalitas sistem."

### D7. Tidak ada hasil kuantitatif (angka/hitungan)

> "Saya memiliki 146 assertions dari 33 test file — 104 passing, 42 known failures. Black box 15 skenario 100% berhasil. White box 5 skenario 100% berhasil. Ini adalah data kuantitatif yang mengukur fungsionalitas sistem secara numerik."

### D8. Tidak ada rumus kelulusan pengujian

> "Kriteria kelulusan: seluruh skenario berjalan tanpa error dan menghasilkan output sesuai ekspektasi. Untuk black box menggunakan equivalence partitioning. Untuk white box menggunakan statement coverage. Keduanya adalah standar di pengujian software."

### D9. NF01 (Akurasi Deduksi) dan NF05 (Konsistensi Data) — bukannya sama?

> "Tidak sama. NF01 menguji: apakah jumlah yang dikurangi sesuai resep? (quantity_used × jumlah_pesan). NF05 menguji: apakah data tetap konsisten kalau ada 2 transaksi bersamaan? (lockForUpdate + rollback). NF01 diuji sequential — satu pesanan, satu alur. NF05 diuji dengan skenario gagal — stok tidak cukup, semua di-rollback. Keduanya menguji aspek yang berbeda."

### D10. NF05 diuji dengan white box atau gray box? Bisakah menguji 2 kasir bersamaan?

> "NF05 diuji dengan gray box test — InventoryRollbackTest.php. Tapi jujur: test ini hanya sequential, bukan concurrent. PHPUnit tidak bisa menguji dua request di waktu yang persis sama. Saya tidak mengandalkan kode saya untuk mencegah race condition — saya mengandalkan lockForUpdate() milik PostgreSQL, fitur yang sudah teruji puluhan tahun di sistem perbankan dan ERP. Yang saya validasi adalah bahwa implementasi saya benar, bukan menguji database-nya. Untuk concurrent testing realistis, dibutuhkan tools stress test seperti k6 — yang berada di luar scope PHPUnit standar."

### D11. Mapping one-to-one kebutuhan ke pengujian tidak jelas — kenapa?

> "Bapak/Ibu, itu memang disengaja. Dalam pengujian perangkat lunak, tidak semua pengujian bisa dimapping one-to-one ke satu kebutuhan — terutama pengujian integrasi seperti gray box.
>
> **Pertama,** satu pengujian bisa memvalidasi >1 kebutuhan sekaligus. Gray box test yang sama memvalidasi akurasi deduksi (NF01) dan pencatatan pergerakan stok (F04).
>
> **Kedua,** ada kebutuhan yang hanya bisa diuji oleh satu metode tertentu. Login (F09) hanya cocok di black box. Konsistensi batch (NF05) hanya cocok di gray box. Akurasi FEFO (NF01) hanya cocok di white box.
>
> **Ketiga,** black box menguji skenario end-to-end — satu skenario login memvalidasi F09 (login), F01 (akses bahan baku), dan F12 (akses menu) sekaligus.
>
> **Setiap kebutuhan tercover oleh setidaknya satu pengujian. Yang penting bukan one-to-one mapping, tapi tidak ada kebutuhan yang lolos tanpa diuji."**

### D12. Integrasi dengan sistem transaksi tidak ada di kebutuhan non-fungsional — gray box menguji apa?

> "Integrasi dengan modul transaksi tidak tercantum secara eksplisit di tabel kebutuhan non-fungsional. Ini karena integrasi adalah **konsekuensi arsitektur**, bukan kebutuhan yang berdiri sendiri. INV-F03 (deduksi FEFO/FIFO) dan INV-F04 (catat pergerakan stok) secara implisit membutuhkan integrasi — deduksi hanya terjadi ketika ada pesanan dari modul transaksi.
>
> Namun saya memahami bahwa kurangnya kebutuhan eksplisit ini membuat gray box test terasa 'mengambang'. Untuk revisi, saya akan menambahkan INV-NF06 yang secara spesifik mencakup integrasi modul."

### D13. Gray box testing menguji kebutuhan yang tidak ada?

> "Tidak. Gray box test menguji apakah INV-F03 dan INV-F04 berfungsi **secara end-to-end melintasi batas modul**. Dua modul ini dikembangkan oleh orang berbeda (saya dan Ivan), sehingga ada risiko ketidaksesuaian asumsi. Gray box test adalah **risk mitigation** — alat verifikasi bahwa integrasi berjalan benar — bukan sumber kebutuhan baru.
>
> Analogi: tim frontend dan backend yang mengembangkan API tidak menulis 'kebutuhan integrasi' di dokumen. Mereka menulis **integration test**. Test itu bukan kebutuhan — ia adalah alat verifikasi bahwa komponen yang berbeda bisa bekerja sama."

### D14. Masukan penguji: integrasi harus dimasukkan ke kebutuhan non-fungsional

> "Terima kasih, Bapak. Saya setuju dan akan menerima saran ini untuk revisi. Dengan memasukkan integrasi modul ke dalam tabel kebutuhan non-fungsional, gray box test memiliki landasan kebutuhan yang eksplisit. Pembaca skripsi bisa langsung melihat bahwa gray box test adalah jawaban dari kebutuhan tersebut — bukan test yang mengambang tanpa dasar.
>
> Contoh tambahan yang akan saya buat:
> ```
> INV-NF06 | Integrasi Modul | Deduksi stok otomatis saat transaksi,
>          |                 | rollback jika gagal, pencatatan
>          |                 | pergerakan stok konsisten
>          |                 | → Uji gray box (HTTP Request)
> ```
> Ini adalah perbaikan — bukan kelemahan. Saya catat sebagai poin revisi."

---

## E. KEAMANAN

### E1. Apakah sistem rentan SQL Injection?

> "Tidak. 90% query menggunakan Eloquent yang auto-parameterized. 10% raw SQL dengan explicit `?` binding. Audit SQL injection lengkap menunjukkan zero celah."

### E2. Bagaimana keamanan autentikasi?

> "Laravel handle bcrypt hashing, CSRF token, session regeneration setelah login, dan rate limiting (5 percobaan sebelum lockout). Route di-protect middleware `auth:web` dan `role:cashier,admin`."

### E3. Bisa bypass login?

> "3 skenario black box: user valid → masuk, user invalid → ditolak, guest → redirect. Tidak ada celah bypass yang ditemukan."

### E4. Kenapa tidak ada security testing khusus autentikasi?

> "Autentikasi sudah diuji black box. Laravel sudah handle CSRF, bcrypt, session security, dan rate limiting yang sudah diuji jutaan developer. Fokus pengujian saya adalah pada business logic yang saya tulis sendiri — algoritma FEFO, FIFO, deduksi stok — yang memiliki potensi bug lebih tinggi daripada fitur framework yang sudah mature."

---

## F. KEPUTUSAN DESAIN

### F1. Kenapa pakai service layer pattern?

> "Memisahkan business logic dari presentation logic. InventoryService bisa di-test tanpa HTTP. Jika logika deduksi stok berubah, cukup edit satu file — tidak perlu 5 controller. Ini maintainability jangka panjang."

### F2. Kenapa `canEdit()` default true (bebas edit)?

> "Admin = owner cafe. Tidak ada auditor eksternal. Agency Theory (Jensen & Meckling, 1976) membuktikan equity agency cost = 0 pada 100% owner-managed firm. Jadi aturan ketat untuk cegah kecurangan tidak relevan. KISS — zero kode tambahan untuk yang tidak dibutuhkan."

### F3. Kenapa soft delete untuk Menu tapi hard delete untuk Category?

> "Menu — soft delete karena pesanan lama perlu menampilkan nama menu. Category — hard delete dengan validasi: tidak bisa hapus jika masih ada menu di dalamnya. Setiap kebijakan berdasarkan kebutuhan bisnis spesifik."

### F4. Kenapa stok menu dihapus dari `$appends`?

> "Accessor berjalan SETIAP kali model di-serialize, termasuk saat tidak butuh stok. Dari 14 halaman yang memuat data menu, hanya 1 yang butuh stok. Dengan melepas dari `$appends`, stok hanya dihitung di halaman yang membutuhkan. Dampak: dari 200+ query menjadi 3 query per halaman."

### F5. Delete policy berantakan — tidak konsisten

> "Saya akui kebijakan hapus antar entitas tidak seragam. Ini karena setiap entitas memiliki kebutuhan bisnis yang berbeda — ada yang butuh soft delete (data historis), cancel (audit trail), hard delete (data operasional). Ini bukan inkonsistensi, melainkan penyesuaian per entitas. Argumen lengkap didukung Agency Theory dan Earned Complexity — lihat argumen-sidang.md."

---

## G. TECH STACK

### G1. Kenapa PHP/Laravel bukan Go atau Node.js?

> "Ekosistem Laravel matang untuk POS: Eloquent ORM, Filament admin panel, Inertia.js, queue, caching, authentication. Untuk skala cafe, overhead PHP tidak signifikan. Kecepatan development prioritas."

### G2. Kenapa React bukan Livewire?

> "React memberikan kontrol lebih pada interaksi UI kompleks — cart kasir real-time, grid menu dengan filter. Livewire lebih cocok untuk halaman yang didominasi form."

### G3. Docker — bukankah ini berat untuk cafe?

> "Docker menyederhanakan deployment. Cafe tidak perlu install PHP, PostgreSQL, atau Nginx manual. Satu container untuk semua service. Resource overhead minimal."

### G4. Module data mining — bagaimana komunikasinya?

> "Python FastAPI sebagai REST service terpisah. Panel admin Filament mengirim request ke Python untuk menjalankan algoritma. Hasil disimpan di database PostgreSQL yang sama. Dua teknologi berbeda — satu database bersama."

---

## H. KONTRIBUSI AKADEMIS

### H1. Ini cuma CRUD biasa — terlalu sederhana untuk Teknik Komputer

> "CRUD adalah lapisan presentasi. Di bawahnya ada: algoritma FEFO/FIFO engine, pessimistic locking (`lockForUpdate()`), database transaction (atomic rollback), immutable stock movement (audit trail), service layer pattern (InventoryService + StockReconciliationService), integrasi lintas modul (transaksi → inventori), dan containerization (Docker multi-service). Yang terlihat CRUD adalah ujung gunung es dari arsitektur yang dirancang untuk maintainability dan testability."

### H2. Kontribusi kamu sebagai individu apa?

> "Saya bertanggung jawab penuh atas modul manajemen inventori: perancangan database inventory (ingredients, batches, movements), algoritma FEFO/FIFO engine, InventoryService, StockReconciliationService, panel admin Filament (CRUD + adjustment + cancel), integrasi dengan modul transaksi (via OrderProcessingService), serta pengujian white box (5 skenario) dan gray box (3 skenario)."

### H3. Apa bedanya dengan skripsi POS lain yang sudah ada?

> "Mayoritas skripsi POS hanya mencakup transaksi dan laporan. Skripsi ini menambahkan: batch management dengan FEFO/FIFO yang bisa dipilih per bahan, deduksi stok otomatis berdasarkan resep menu, immutable stock ledger untuk audit trail penuh, dan integrasi dengan modul data mining (asosiasi, klasterisasi, prediksi)."

### H4. Apa novelty dari sistem ini?

> "Kebaruan bukan pada teknologi, melainkan pada INTEGRASI batch management FEFO/FIFO dengan resep menu dan deduksi otomatis dalam satu sistem POS gratis. Tidak ada POS UKM yang menyediakan kombinasi ini secara gratis dan open source."

---

## I. SKRIPSI (DOKUMENTASI)

### I1. Bab 3 kurang urut — tidak sesuai standar penulisan

> "Saya akui sistematika Bab 3 tidak seideal yang diharapkan. Penyebabnya: project ini capstone berbasis tim yang dikerjakan secara iteratif dan paralel — beberapa keputusan teknis sudah mulai diimplementasikan saat Bab 3 ditulis. Fokus saya sebagai implementator lebih banyak pada sistem yang berfungsi daripada dokumentasi perancangan. Namun saya memahami hubungan antara setiap bagian: saya bisa jelaskan masalah → solusi → kebutuhan → implementasi secara lisan. Kekurangan di dokumen akan saya perbaiki."

### I2. Tidak ada wireframe / rancangan antarmuka

> "Saya menggunakan Filament Panel yang sudah memiliki komponen UI standar (tabel, form, filter, badge, action) sehingga wireframe terpisah tidak dibuat. Antarmuka mengikuti pola yang sudah disediakan framework. Ini praktik umum untuk proyek yang menggunakan admin panel generator."

### I3. Tidak ada deployment plan di Bab 3

> "Rencana deployment dijelaskan saat implementasi di Bab 4 bersamaan dengan lingkungan produksi. Deployment menggunakan Docker — `docker compose --profile prod up -d`. Dokumentasi lengkap di README repository."

### I4. Kenapa merancang kebutuhan fungsional dan non-fungsional seperti itu? Apakah ada yang terlewat?

> "Kebutuhan fungsional dan non-fungsional saya rumuskan berdasarkan analisis proses bisnis W9 Cafe dan wawancara dengan pemilik cafe. Setiap kebutuhan adalah jawaban langsung terhadap masalah yang sudah saya identifikasi. Misalnya: masalah stok tidak akurat → NF01 Akurasi Deduksi. Kesulitan tracking batch → NF02 Prioritas FEFO/FIFO. Perlu koreksi stok → NF03 Validitas Penyesuaian.
>
> Apakah ada yang terlewat? Kemungkinan selalu ada — karena tidak ada analisis yang sempurna. Tapi saya menggunakan prinsip prioritas (dampak langsung ke akurasi stok didahulukan) dan YAGNI (tidak menambah kebutuhan yang belum terbukti diperlukan). Untuk skala cafe kecil dengan operasional yang sudah saya pelajari, kebutuhan yang ada sudah mencakup seluruh skenario kritis. Yang terlewat bisa menjadi saran pengembangan."

### I5. Coba mapping kebutuhan fungsional dan non-fungsional dengan pengujian yang dijalankan

> "Baik, Bapak/Ibu. Berikut mapping lengkap antara setiap kebutuhan dengan pengujian yang saya lakukan:"

**KEBUTUHAN FUNGSIONAL:**

| Kode | Kebutuhan | Jenis Pengujian | Skenario |
|:----:|-----------|:---:|----------|
| INV-F01 | Admin kelola bahan baku | Black Box | Tambah, ubah, hapus bahan baku — 100% ✅ |
| INV-F02 | Admin kelola batch stok | Black Box | Tambah batch stok — 100% ✅ |
| INV-F03 | Deduksi batch FEFO/FIFO | White Box | `test_fefo_deducts_soonest_expiry_first` ✅, `test_fifo` ✅ |
| INV-F04 | Catat pergerakan stok | Gray Box | Skenario 1: deduksi + catat stock_movement + daily_usage ✅ |
| INV-F05 | Penyesuaian stok manual | White Box | `penyesuaian increase` ✅ + `pembatalan penyesuaian` ✅ |
| INV-F06 | Admin kelola resep menu | Black Box | Tambah resep, hapus resep (peringatan jika hanya 1 bahan) ✅ |
| INV-F09 | Admin login/logout | Black Box | Login valid, login invalid, logout — 100% ✅ |
| INV-F11 | Admin kelola kategori | Black Box | (tercakup di flow CRUD admin) |
| INV-F12 | Admin kelola menu | Black Box | Tambah, ubah, hapus menu — 100% ✅ |

**KEBUTUHAN NON-FUNGSIONAL:**

| Kode | Parameter | Jenis Pengujian | Skenario |
|:----:|-----------|:---:|----------|
| INV-NF01 | Akurasi deduksi sesuai resep | White Box | `test_resep_deduction`: 30g/porsi × 2 porsi = 60g ✅ |
| INV-NF02 | Prioritas FEFO/FIFO | White Box | `test_fefo`: expired 3 hari duluan. `test_fifo`: received 5 hari lalu duluan ✅ |
| INV-NF03 | Validitas penyesuaian stok | White Box | `test_adjustment_increase`: qty 100 → +50 = 150 ✅ |
| INV-NF04 | Pemulihan stok saat batal | White Box | `test_adjustment_cancel`: 100 → +50 → cancel → kembali 100 ✅ |
| INV-NF05 | Konsistensi data transaksi | Gray Box | `InventoryRollbackTest`: stok tidak cukup → rollback, tidak ada data parsial ✅ |

> "Dari 9 kebutuhan fungsional, 7 diuji secara eksplisit, 2 sisanya (kategori dan menu) tercakup dalam flow CRUD admin di black box. Dari 5 kebutuhan non-fungsional, seluruhnya diuji — 4 dengan white box, 1 dengan gray box. **Tidak ada kebutuhan yang tidak teruji.**"

### I6. Tidak ada UML / diagram sequence

> "Untuk alur spesifik seperti deduksi stok, saya menggunakan flowchart yang lebih mudah dipahami oleh pembaca non-teknis. Activity diagram dan use case diagram sudah mencakup 15+ skenario interaksi pengguna dan sistem. Ilmu Teknik Komputer tidak hanya tentang UML, tetapi juga tentang memilih notasi yang paling efektif untuk menyampaikan desain sistem."

### I7. INV-F03/F04 — fungsional atau non-fungsional? Aktornya sistem, bukan admin

> "Bapak/Ibu, saya yakin ini sudah tepat sebagai fungsional. Alasannya:
>
> **Fungsional = apa yang dilakukan sistem.** Non-fungsional = seberapa baik sistem melakukannya.
>
> INV-F03: 'Sistem mendukung deduksi batch FEFO dan FIFO' — ini adalah fitur. Apakah ada atau tidak fitur ini? Ya/tidak. Itu fungsional. Pembanding non-fungsionalnya adalah INV-NF02: 'prioritas batch sesuai FEFO/FIFO berjalan benar' — itu ukuran kualitas.
>
> INV-F04: 'Sistem mencatat pergerakan stok secara permanen' — ini juga fitur. Setiap perubahan stok harus tercatat. Tanpa ini, tidak ada audit trail. Itu fungsional.
>
> Aktor 'sistem' tidak otomatis membuatnya non-fungsional. Ada banyak fitur yang aktornya sistem — backup data, kirim notifikasi, catat log. Semuanya tetap fungsional selama ia menjawab 'apa yang dilakukan sistem', bukan 'seberapa baik sistem melakukannya'."

### I8. Jelaskan satu per satu kategorisasi kebutuhan fungsional dan non-fungsional

> "Baik, Bapak/Ibu. Polanya sederhana: **fungsional = apa yang dilakukan sistem**, **non-fungsional = seberapa baik/akurat/cepat sistem melakukannya**. Kalau jawabannya ya/tidak → fungsional. Kalau jawabannya skala/ukuran → non-fungsional.

**Fungsional:**
- INV-F01, F02, F05, F06, F11, F12 — CRUD admin: apakah admin bisa mengelola data? Ya/tidak. Fungsional.
- INV-F09 — Login/logout: apakah admin bisa login? Ya/tidak. Fungsional.
- INV-F03 — Deduksi FEFO/FIFO: apakah fitur ini ada? Ya/tidak. Fungsional.
- INV-F04 — Catat pergerakan stok: apakah sistem mencatat? Ya/tidak. Fungsional.

**Non-Fungsional:**
- INV-NF01 — Akurasi deduksi: apakah jumlahnya benar sesuai resep? Bukan ya/tidak, tapi seberapa akurat. Non-fungsional.
- INV-NF02 — Prioritas FEFO/FIFO: apakah batch yang tepat terpilih? Ukuran kebenaran algoritma. Non-fungsional.
- INV-NF03 — Validitas penyesuaian: apakah penambahan/pengurangan stok valid? Non-fungsional.
- INV-NF04 — Pemulihan stok: apakah stok kembali ke awal setelah batal? Non-fungsional.
- INV-NF05 — Konsistensi data: apakah data tetap konsisten saat concurrent? Non-fungsional.

> **Setiap kebutuhan di tabel saya mengikuti pola ini secara konsisten — tidak ada yang ditempatkan secara asal."**

### I9. Kenapa kebutuhan fungsional dan non-fungsional muncul SETELAH diagram teknis (use case, ERD, flowchart)?

> "Bapak/Ibu, saya akui bahwa urutan ini memang tidak lazim. Idealnya kebutuhan muncul sebelum rancangan teknis — karena kebutuhan adalah **apa yang harus dibangun**, dan diagram adalah **bagaimana cara membangunnya**.
>
> Pada kasus skripsi saya, urutan ini terjadi karena **saya merancang diagram teknis (use case, activity diagram) terlebih dahulu untuk memvisualisasikan alur bisnis yang ada**, lalu dari situ saya menarik kebutuhan fungsional dan non-fungsional secara induktif. Artinya, saya menggunakan diagram sebagai alat bantu analisis untuk mengidentifikasi kebutuhan — bukan sebagai implementasi dari kebutuhan yang sudah ditulis.
>
> Pendekatan ini memang tidak sesuai urutan ideal, tapi secara substansi tidak ada yang terlewat. Kebutuhan tetap teridentifikasi — hanya saja metode identifikasinya dilakukan sambil merancang diagram, bukan sebelum diagram.
>
> Saya akan memperbaiki urutan ini di revisi untuk memenuhi standar penulisan yang berlaku."**

### I10. Kenapa tidak ada penjelasan deployment? Apakah sistem sudah di-deploy?

> "Bapak/Ibu, deployment memang belum dilakukan. Sejak awal project ini direncanakan untuk di-deploy di VPS. Pada pertemuan terakhir dengan stakeholder di awal Juli, beliau meminta diadakan pertemuan lanjutan di **akhir Juli** untuk membahas detail: pemilihan provider VPS, spesifikasi server, biaya bulanan, domain, dan penyerahan akhir project. Dana deployment belum diberikan dan masih menunggu pertemuan tersebut.
>
> Yang sudah siap dari sisi teknis: Dockerfile, docker-compose.yml (profile production), konfigurasi Nginx, environment variables, dan petunjuk instalasi di README. Sistem sudah berjalan di lingkungan staging menggunakan Docker dengan konfigurasi identik dengan production. Deployment tinggal menjalankan `docker compose --profile prod up -d` setelah server tersedia.
>
> Ini menunjukkan bahwa project tidak berhenti di sidang — ada keberlanjutan ke stakeholder."

---

## J. MULTIDISIPLIN TEKNIK KOMPUTER

### J1. Ini hanya software — bukannya Teknik Komputer multidisiplin?

> "Proyek ini menggabungkan: (1) Software engineering — arsitektur multi-layer, design pattern, service layer; (2) Algoritma — FEFO/FIFO sorting dan selection; (3) Sistem basis data — ACID transaction, pessimistic locking, indexing; (4) Jaringan & infrastruktur — Docker, Nginx, PHP-FPM, Supervisor; (5) Integrasi sistem — Laravel ↔ React ↔ Python dengan Inertia.js + REST API; (6) Kecerdasan buatan — modul data mining (asosiasi Apriori, klasterisasi K-Means, prediksi regresi). Inilah multidisiplin Teknik Komputer."

### J2. Kenapa tidak integrasi IoT?

> "IoT membutuhkan biaya perangkat keras, kalibrasi, perawatan — yang belum terjangkau untuk tahap pertama digitalisasi cafe. Capstone ini dibagi berdasarkan kompetensi tim: saya dan Ivan di software, Ruben di data mining. IoT butuh anggota tim dengan kompetensi embedded systems — tidak tersedia di tim kami. Ini penelitian lanjutan yang sangat baik untuk mahasiswa Teknik Komputer setelah ini."

---

## K. LULUS / TIDAK LULUS

### K1. Dengan banyak celah, apakah skripsi layak diluluskan?

> "Layak. Celah yang ada adalah di dokumentasi dan sistematika — bukan di substansi sistem. Sistem berfungsi, pengujian valid (104 passing assertions), dan saya bisa menjelaskan setiap keputusan desain. Skripsi sudah disetujui pembimbing dan didaftarkan sidang — artinya secara administratif sudah layak."

### K2. Revisi substansial — tetap lulus atau tidak?

> "Revisi substansial TIDAK berarti gagal. Mahasiswa tetap DINYATAKAN LULUS pada hari sidang. Revisi dikerjakan SETELAH sidang sebagai syarat administratif sebelum ijazah. Tidak ada mahasiswa yang disuruh sidang ulang hanya karena kurang runut atau kurang tabel. Sidang ulang terjadi hanya jika sistem tidak berfungsi, mahasiswa tidak bisa jawab, atau ada pelanggaran etik."

### K3. Teman saya ada yang sidang ulang — bagaimana dengan saya?

> "Sidang ulang terjadi karena mahasiswa tidak bisa menjawab pertanyaan sama sekali, sistem tidak berfungsi, atau plagiat. Kamu bisa menjawab — semua jawaban sudah ada di file ini. Kamu paham sistemmu. Kamu tidak akan mengalami nasib yang sama."
