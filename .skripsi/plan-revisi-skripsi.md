# Plan Revisi Skripsi — v7
## Muhammad Nio Hastungkoro — 21120122140155

---

## 📋 Daftar Revisi (18 Poin)

| # | Kategori | Poin Revisi | Prioritas |
|:--:|:--------|-------------|:--------:|
| 1 | Administratif | Print lembar pengesahan, orisinalitas, dll | Tinggi |
| 2 | Bab 1 | Latar belakang — catatan khusus dari penguji (fisik) | Tinggi |
| 3 | Bab 1 | Sebutkan metode pengujian (black box, white box, gray box) di latar belakang | Sedang |
| 4 | Bab 1 | Hapus kata "akurat" dari latar belakang | Rendah |
| 5 | Bab 1 | Tujuan penelitian disesuaikan dengan rumusan masalah | Tinggi |
| 6 | Bab 2 | Tambah penjelasan Filament di landasan teori | Sedang |
| 7 | Bab 2 | Detailkan teori Point of Sale | Sedang |
| 8 | Bab 2 | Detailkan teori ketiga pengujian (black, white, gray box) + perbedaannya | Tinggi |
| 9 | Bab 2 | Tambah penjelasan batch stok, FEFO, FIFO | Sedang |
| 10 | Bab 3 | Metode penelitian dan sistematika penulisan — sebut gray box | Sedang |
| 11 | Bab 3 | Jelaskan perbedaan "metode penelitian" vs "metodologi penelitian" | Sedang |
| 12 | Bab 3 | Use case diagram — sederhanakan (hapus CRUD), tambah skenario | Tinggi |
| 13 | Bab 3 | Flowchart + activity diagram — tambah error handling | Rendah |
| 14 | Bab 4 | Iterasi / agile — buktikan bahwa benar pakai agile dalam pengembangan | Tinggi |
| 15 | Bab 4 | Halaman penyesuaian stok — jelaskan otomatis atau manual oleh admin | Rendah |
| 16 | Bab 4 | Jelaskan tools untuk setiap pengujian | Sedang |
| 17 | Bab 4 | Detail white box testing — coverage line by line | Tinggi |
| 18 | Bab 4 | Tabel perbandingan ketiga pengujian | Sedang |

---

## 📁 File yang Akan Dibuat / Dimodifikasi

| File | Aksi |
|------|------|
| `.skripsi/skripsi_v7.md` | File skripsi revisi (copy dari v6 + semua perubahan) |
| `.skripsi/skripsi_v7.docx` | Build ulang dari v7.md |

---

## 🗺️ Execution Plan (Sequential)

### FASE 1: Bab 1 — Pendahuluan (3 poin)

| Step | Poin | Perubahan |
|:----:|:----:|-----------|
| 1.1 | #3 | Tambah 1-2 kalimat di akhir latar belakang: "Sistem diuji menggunakan tiga metode pengujian, yaitu black box, white box, dan gray box testing." |
| 1.2 | #4 | Ganti kata "akurat" dengan "konsisten" atau "tercatat secara sistematis" di latar belakang. Argumen: resep fixed tidak 100% akurat. |
| 1.3 | #5 | Cocokkan setiap rumusan masalah dengan tujuan penelitian. Pastikan 1:1 mapping. |

### FASE 2: Bab 2 — Landasan Teori (3 poin)

| Step | Poin | Perubahan |
|:----:|:----:|-----------|
| 2.1 | #6 | Tambah sub-bab: "Filament Admin Panel" — jelaskan apa itu Filament, kelebihannya (CRUD generator, Form Builder, Table Builder, RelationManager), kenapa dipilih. |
| 2.2 | #7 | Perdalam sub-bab "Point of Sale" — definisi, komponen POS, POS untuk cafe vs retail, contoh POS populer. |
| 2.3 | #8 | Detailkan ketiga pengujian: Black Box (definisi, equivalence partitioning), White Box (statement coverage, branch coverage), Gray Box (definisi + contoh integrasi antar modul). Tambah tabel perbandingan. |
| 2.4 | #9 | Tambah sub-bab baru: "Batch Stok, FEFO, dan FIFO" — jelaskan kenapa perlu batch tracking, perbedaan FEFO dan FIFO, kapan digunakan. |

### FASE 3: Bab 3 — Perancangan (3 poin)

| Step | Poin | Perubahan |
|:----:|:----:|-----------|
| 3.1 | #11 | Tambah paragraf: "Metode penelitian adalah cara pengumpulan data dan pengujian sistem (black box, white box, gray box). Metodologi penelitian adalah pendekatan pengembangan yang digunakan (waterfall/agile)." |
| 3.2 | #10 | Sebut gray box di sub-bab metode penelitian dan sistematika penulisan. |
| 3.3 | #12 | Sederhanakan use case: hapus "Tambah/Edit/Hapus" — cukup "Kelola Bahan Baku", "Kelola Batch", dll. Tambah tabel skenario use case. |
| 3.4 | #13 | Update flowchart dan activity diagrams: tambah cabang error (koneksi gagal, input tidak valid). |

### FASE 4: Bab 4 — Implementasi & Pengujian (6 poin)

| Step | Poin | Perubahan |
|:----:|:----:|-----------|
| 4.1 | #14 | Tambah sub-bab "Proses Pengembangan": ceritakan timeline, berapa iterasi, fitur apa yang berubah tiap iterasi, bagaimana agile diterapkan (sprint planning, review, retrospective). |
| 4.2 | #15 | Tambah penjelasan: "Penyesuaian stok dilakukan secara manual oleh admin melalui form penyesuaian di panel Filament." |
| 4.3 | #18 | Tambah tabel perbandingan ketiga pengujian (aspek, tujuan, tools, contoh skenario) |
| 4.4 | #16 | Tambah tools untuk setiap pengujian: Black Box → uji manual via UI Filament, White Box → PHPUnit (tests/Unit/), Gray Box → PHPUnit Feature Test (tests/Feature/) |
| 4.5 | #17 | Detail white box: tambah penjelasan statement coverage dan branch coverage, serta daftar method/baris yang diuji. |
| 4.6 | #3 | Verifikasi bahwa metode pengujian sudah disebut di latar belakang (crosscheck Fase 1) |

### FASE 5: Bab 5 — Penutup (0 poin)

Tidak ada revisi spesifik untuk Bab 5.

### FASE 6: Administratif (1 poin)

| Step | Poin | Perubahan |
|:----:|:----:|-----------|
| 6.1 | #1 | Siapkan lembar pengesahan dan pernyataan orisinalitas untuk print dan tanda tangan. |
| 6.2 | #2 | Cek catatan fisik dari penguji untuk poin spesifik di latar belakang. |

---

## ⏱️ Estimasi Waktu per Fase

| Fase | Isi | Estimasi |
|:----:|-----|:--------:|
| 1 | Bab 1 — Pendahuluan | 30 menit |
| 2 | Bab 2 — Landasan Teori | 1 jam |
| 3 | Bab 3 — Perancangan | 1 jam |
| 4 | Bab 4 — Implementasi & Pengujian | 2 jam |
| 6 | Administratif | 15 menit |
| | **Total** | **~5 jam** |

---

## ✅ Verifikasi

Setelah semua perubahan, pastikan:
- [ ] Setiap rumusan masalah dijawab oleh tujuan penelitian
- [ ] Gray box disebut di Bab 2, Bab 3, dan Bab 4
- [ ] Tabel perbandingan pengujian ada
- [ ] White box test dijelaskan dengan coverage
- [ ] Agile dibuktikan dengan cerita iterasi
- [ ] Use case tanpa CRUD
- [ ] Flowchart & activity diagram ada error handling
