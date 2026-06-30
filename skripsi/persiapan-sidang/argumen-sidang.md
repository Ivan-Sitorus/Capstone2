# Argumen Sidang — Sistem Manajemen Inventori W9 Cafe

## Prinsip Dasar: Fleksibilitas vs Kontrol

Setiap keputusan desain dalam sistem ini didasarkan pada **satu prinsip utama**:

> **"Selama tindakan itu mengganggu operasional kafe, maka harus dicegah. Jika tidak, biarkan fleksibel."**

Perbandingan dengan enterprise:

| Skala | Prinsip | Alasan |
|---|---|---|
| **Cafe (1 admin)** | Operasional terganggu? → Cegah | Tidak ada pihak lain yang dirugikan |
| **Enterprise (50+ karyawan)** | Operasional terganggu **ATAU** risiko keagenan? → Cegah | Banyak pihak bisa bertindak tidak sesuai kepentingan owner |

---

## Landasan Teori 1: Agency Theory (Jensen & Meckling, 1976)

### Konsep
Konflik kepentingan muncul ketika **pemilik (principal)** dan **pengelola (agent)** adalah orang yang berbeda.

### Riset Kunci: Ang, Cole & Lin (2000)

> *"Equity agency costs are **zero** in a 100% **owner-managed firm**."*

Artinya: Ketika owner = manager = operator (satu orang), **tidak ada biaya keagenan**.

### Implikasi ke Sistem

| Aspek | Cafe (owner = admin) | Enterprise (owner ≠ manager) |
|---|---|---|
| **Agency cost** | Nol | Positif |
| **Need monitoring** | Minimal | Tinggi |
| **Risk of leakage** | Hanya ke diri sendiri | Bisa ke karyawan mana pun |
| **Approval workflow** | Tidak perlu | Perlu |
| **Audit trail** | Cukup untuk core transaksi | Wajib untuk semua tindakan |

### Sumber
- Jensen, M.C. & Meckling, W.H. (1976). "Theory of the Firm: Managerial Behavior, Agency Costs and Ownership Structure." *Journal of Financial Economics*, 3(4), 305-360.
- Ang, J.S., Cole, R.A., & Lin, J.W. (2000). "Agency Costs and Ownership Structure." *The Journal of Finance*, 55(1), 81-106.

---

## Landasan Teori 2: Earned Complexity (Grant Watson, 2024)

### Prinsip

> *"Complexity must be **earned** by measurable pain, and paid for with controls."*

Setiap kompleksitas harus melewati **3 gates**:

### Gate 1: Apakah ada masalah konkret yang terjadi SAAT INI?

| Fitur | Gate 1: Ada masalah? | Keputusan |
|---|---|---|
| StockMovement immutable | ✅ Stok perlu akurat dan terlacak | **WAJIB** |
| Adjustment reversal | ✅ Stok harus kembali saat adjustment dihapus | **WAJIB** |
| Full audit trail edit menu | ❌ Owner tidak pernah komplain | **TIDAK PERLU** |
| Approval workflow | ❌ 1 admin, approval ke siapa? | **TIDAK PERLU** |

### Gate 2: Apakah solusi sederhana sudah dicoba?
Untuk masalah "owner lupa apa yang dia edit" — solusi paling sederhana: **ingatan owner**, **browser history**, **reversal otomatis untuk stok**. Belum ada bukti ini tidak cukup.

### Gate 3: Benefit vs Cost
| Fitur | Benefit | Cost | Layak? |
|---|---|---|---|
| Audit trail edit nama menu | Owner lihat riwayat (1x/bulan) | 20+ tabel, kompleksitas query | ❌ |
| Reversal adjustment | Stok kembali akurat | ~40 baris kode | ✅ |

### Sumber
- Watson, G. (2024). "Earned Complexity: A Disciplined, Evidence-Based Framework." *DEV Community*.
- Brooks, F. (1987). "No Silver Bullet: Essence and Accidents of Software Engineering." *IEEE Computer*.

---

## Landasan Teori 3: YAGNI (Martin Fowler)

> *"Yagni says: since you won't need this feature for six months, you shouldn't build it until it's necessary."*

> *"The common reason why people build presumptive features is because they think it will be cheaper now. But that cost comparison has to be made against the **cost of delay** — factoring in the probability that you're building an **unnecessary feature**, for which your odds are at least **⅔**."*

**Terjemahan:** ⅔ fitur yang dibangun untuk "siapa tahu nanti dibutuhkan" ternyata **tidak pernah dipakai**.

### Sumber
- Fowler, M. (2000). "Yagni." *martinfowler.com*.

---

## Perbedaan Enterprise vs Small Business

| Aspek | Cafe (Proyek Saya) | Enterprise (SAP/Oracle) |
|---|---|---|
| **Volume transaksi** | ~50/hari | ~50.000/hari |
| **Jumlah karyawan** | 1-3 | 50+ |
| **Stakeholder** | Owner = satu orang | CFO, auditor, regulator, investor |
| **Akibat error** | Bisa diverifikasi manual | Bisa denda miliaran |
| **IT team** | 0 orang | 5-50 orang |
| **Budget IT/tahun** | ~Rp 0 | ~Rp 5-10M+ |
| **Compliance** | Tidak ada | SOX, GAAP, IFRS, Pajak |

### Sumber
- Celerant (2026). "Enterprise vs. Small Business POS Systems: Key Differences."
- GoPosly (2026). "POS vs ERP: Which One Does Your Business Really Need?"

---

## Aturan Praktis per Entitas

| Entitas | Kategori | Perlakuan | Alasan |
|---|---|---|---|
| **StockMovement** | Audit Trail | **Immutable** — tidak bisa diubah/dihapus | Core transaksi, wajib akurat |
| **Penyesuaian Stok** | Transaksional | **Void + reversal otomatis** | Stok harus kembali, tapi audit trail tetap |
| **Order** | Transaksional | **Cancel status** — tidak bisa hard delete | Ada implikasi stok dan finansial |
| **Menu** | Master Data | **Soft delete (arsip)** | Tidak punya implikasi keuangan |
| **Kategori** | Master Data | **RESTRICT** jika masih dipakai | Relasi ke menu |
| **Bahan Baku** | Master Data | **RESTRICT** jika dipakai resep | Relasi ke menu_ingredients |
| **Batch Stok** | Transaksional | **SET NULL** — boleh hapus | History tetap, batch berikutnya dipakai |

---

## Trade-off yang Dipilih

### Untuk Cafe: Fleksibilitas > Kontrol

**Keuntungan:**
1. **Kecepatan adaptasi** — perubahan 1 hari, tanpa approval
2. **Biaya operasional rendah** — 0 IT staff
3. **Owner bisa langsung bertindak** — satu pengambil keputusan
4. **Tidak ada compliance cost** — tidak perlu audit trail level GL

**Konsekuensi:**
- Risiko error lebih tinggi — tapi bisa diverifikasi manual (50 transaksi/hari)
- Tidak ada accountability untuk multiple user — belum diperlukan

### Untuk Enterprise: Kontrol > Fleksibilitas

**Keuntungan:**
1. **Auditable** — compliance terjamin
2. **Role-based access** — tiap peran terbatas
3. **Scalable** — handle ribuan cabang

**Konsekuensi:**
- Implementasi berbulan-bulan
- Biaya IT puluhan juta/bulan
- Perubahan perlu approval tim

---

## Kapan Harus Bertransisi ke Enterprise Workflow?

| Trigger | Sekarang? | Action |
|---|---|---|
| Karyawan > 5 orang | ❌ (1-3) | Tambah role-based access |
| Volume > 500 transaksi/hari | ❌ (~50) | Tambah audit trail otomatis |
| Multi-cabang | ❌ (1 cabang) | Tambah konsolidasi data |
| Ada auditor/investor | ❌ | Tambah compliance report |
| Ada requirement legal | ❌ | Tambah data retention policy |

**Prinsip: Build for today, refactor for tomorrow.**

---

## Ringkasan Argumen untuk Sidang

> *"Saya memprioritaskan fleksibilitas karena W9 Cafe adalah bisnis kecil dengan 50 transaksi/hari dan 1-3 karyawan. Memaksakan workflow enterprise (approval chain, full audit trail, role-based access ketat) pada skala ini adalah **premature optimization** — biayanya lebih besar dari manfaatnya, dan justru akan memperlambat adaptasi yang diperlukan bisnis kecil untuk bertahan.*
>
> *Ini didukung oleh **Agency Theory (Jensen & Meckling, 1976)** yang membuktikan bahwa pada firm dengan 100% owner-manager, equity agency cost = 0. Tidak ada konflik kepentingan yang memerlukan mekanisme monitoring kompleks.*
>
> *Saya juga menggunakan prinsip **Earned Complexity**: setiap kompleksitas harus di-justify oleh **measurable pain**. Saya telah mencakup semua yang berdampak pada stok dan keuangan melalui StockMovement immutable dan reversal adjustment otomatis. Menambahkan full audit trail untuk master data tidak memiliki pain yang meng-justifikasinya.*
>
> *Ketika bisnis berkembang dan trigger seperti multi-karyawan, multi-cabang, atau compliance requirement muncul, sistem akan di-refactor. Tapi membangunnya sekarang adalah **premature optimization** yang hanya akan memperlambat operasional tanpa manfaat signifikan."*

---

## Daftar Pustaka

1. Ang, J.S., Cole, R.A., & Lin, J.W. (2000). "Agency Costs and Ownership Structure." *The Journal of Finance*, 55(1), 81-106.
2. Brooks, F. (1987). "No Silver Bullet: Essence and Accidents of Software Engineering." *IEEE Computer*.
3. Fowler, M. (2000). "Yagni." *martinfowler.com*.
4. Jensen, M.C. & Meckling, W.H. (1976). "Theory of the Firm: Managerial Behavior, Agency Costs and Ownership Structure." *Journal of Financial Economics*, 3(4), 305-360.
5. Watson, G. (2024). "Earned Complexity: A Disciplined, Evidence-Based Framework." *DEV Community*.
6. Brown, S. (2011). "Software Architecture for Developers." *Leanpub*.
7. Boehm, B. (1987). "Improving Software Productivity." *IEEE Computer*.
