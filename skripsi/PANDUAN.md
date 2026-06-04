# PANDUAN PENGGUNAAN BUILD SKRIPSI

## Alur Lengkap

```
Baca konteks-skripsi.md
  ↓
Baca codebase (app/Services/, app/Models/, app/Filament/)
  ↓  
Tulis konten di file markdown per bab
  ↓
python3 create_template.py      (cukup sekali)
  ↓
python3 build_skripsi.py         (setiap habis nulis)
  ↓
Buka skripsial.docx di Word → Ctrl+A → F9
```

---

## 1. Persiapan Awal (Sekali)

```bash
cd skripsi
python3 create_template.py
```

File yang dihasilkan: `template-skripsi.docx`

Jalankan ulang hanya jika ingin mengubah formatting (margin, font, style, dll.).

---

## 2. Menulis Konten

Tulis setiap bab di file markdown terpisah:

| File | Isi |
|------|-----|
| `abstrak.md` | Abstrak Bahasa Indonesia |
| `abstract.md` | Abstract Bahasa Inggris |
| `kata-pengantar.md` | Kata Pengantar |
| `bab1-pendahuluan.md` | BAB I Pendahuluan |
| `bab2-tinjauan-pustaka.md` | BAB II Tinjauan Pustaka |
| `bab3-perancangan-sistem.md` | BAB III Perancangan Sistem |
| `bab4-implementasi-sistem.md` | BAB IV Implementasi Sistem |
| `bab5-pengujian-dan-evaluasi.md` | BAB V Pengujian dan Evaluasi |
| `bab6-penutup.md` | BAB VI Penutup |
| `daftar-pustaka.md` | Daftar Pustaka (format IEEE [1], [2], ...) |

### Format Heading

```markdown
# BAB I PENDAHULUAN
## 1.1 Latar Belakang
### 1.1.1 Sub Pokok Bahasan
```

**Aturan:**
- `#` untuk judul bab → WAJIB format `BAB [ROMawi] [Judul]`
- `##` untuk sub-bab → WAJIB format `[angka].[angka] [Judul]`
- `###` untuk sub-sub-bab → WAJIB format `[angka].[angka].[angka] [Judul]`
- Nomor heading ditulis manual, script hanya membaca teks apa adanya

### Format Tabel

Gunakan pipe table:

```markdown
| Kolom 1 | Kolom 2 | Kolom 3 |
|---------|---------|---------|
| data 1  | data 2  | data 3  |
```

### Format Source Code

Gunakan fenced code block:

```markdown
```php
function example() {
    return true;
}
```
```

### Format Caption Gambar

```markdown
![Deskripsi Gambar](path/to/gambar.png)
```

Caption akan otomatis diberi nomor `{bab}.{counter}`.

### Format Kata Asing

Gunakan `*italic*` untuk memastikan kata di-italic:

```markdown
Sistem menggunakan *database* dan *framework* Laravel.
```

Kata dalam daftar FOREIGN_TERMS akan otomatis di-italic tanpa `*`.

### Format Daftar Pustaka

```markdown
[1] A. B. Setiawan, "Judul artikel," *Nama Jurnal*, vol. 1, no. 2, pp. 10-20, 2024.
[2] doi:10.1234/example
```

Baris yang diawali `doi:` akan otomatis di-fetch dari CrossRef API.

---

## 3. Build DOCX

```bash
python3 build_skripsi.py
```

File yang dihasilkan: `skripsial.docx`

### Yang Dilakukan Script

| Fitur | Cara |
|-------|------|
| Margin 4-4-3-3, A4 | Dari template |
| Font TNR 12, spasi 1.5 | Dari template |
| Heading 1: "BAB I" + judul (2 baris, center, bold) | Auto dari `# BAB I ...` |
| Heading 2: "1.1\tJudul" (tab, indent 1.25cm, justify) | Auto dari `## 1.1 ...` |
| Heading 3: "1.1.1\tJudul" (tab, indent 1.25cm, justify) | Auto dari `### 1.1.1 ...` |
| Page number prelim: romawi (i, ii, iii) | Dari template section 0 |
| Page number body: arab (1, 2, 3), different first page | Dari template section 0 |
| Daftar Isi | Field TOC, perlu di-Update di Word |
| Daftar Gambar | Field TOC \c "Figure", perlu di-Update |
| Daftar Tabel | Field TOC \c "Table", perlu di-Update |
| Caption gambar: "{bab}.{counter} teks" (center, TNR 10) | SEQ Figure |
| Caption tabel: "{bab}.{counter} teks" (justify, TNR 10) | SEQ Table |
| Source code: Courier New 10, tabel 1x1, left | Auto dari ``` |
| Tabel data: TNR 12, header center, data justify | Auto dari pipe table |
| Kata asing: auto-italic | FOREIGN_TERMS + *italic* |
| Referensi IEEE dari DOI | habanero (opsional) |

---

## 4. Finalisasi di Word

1. Buka `skripsial.docx` di Microsoft Word
2. **Ctrl+A → F9** (Update all fields)
3. Klik kanan Daftar Isi → **Update Field** → **Update entire table**
4. Klik kanan Daftar Gambar → **Update Field**
5. Klik kanan Daftar Tabel → **Update Field**
6. Periksa:
   - Nomor halaman romawi (i, ii, iii) di bagian awal
   - Nomor halaman arab (1, 2, 3) di bagian isi
   - Halaman pertama setiap bab: nomor di bawah tengah
   - Halaman lanjutan: nomor di kanan atas
   - Heading 2: "1.1\tJudul" dengan indent
   - Heading 3: "1.1.1\tJudul" dengan indent
   - Daftar Isi sesuai dengan isi

---

## 5. Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Heading 2/3 tidak indent | Pastikan `create_template.py` sudah dijalankan |
| Nomor halaman tidak muncul | Ctrl+A → F9 di Word |
| Daftar Isi kosong | Klik kanan → Update Field |
| BAB tidak romawi | Pastikan format `# BAB I`, bukan `# BAB 1` |
| Caption tidak bernomor | SEQ field perlu di-refresh (Ctrl+A → F9) |
| Font masih Calibri | Hapus `template-skripsi.docx`, jalankan ulang `create_template.py` |
| `habanero` error | `pip install habanero` untuk auto-fetch DOI |
| Template error | `python3 create_template.py` untuk regenerate |

---

## 6. File Output

| File | Keterangan |
|------|-----------|
| `skripsial.docx` | File DOCX final siap sidang |
| `skripsial.md` | Gabungan semua bab dalam satu file markdown |
| `template-skripsi.docx` | Template formatting (auto-generated) |
| `create_template.py` | Script generate template |
| `build_skripsi.py` | Script build DOCX dari markdown |
| `konteks-skripsi.md` | Dokumentasi lengkap arsitektur dan konteks |
| `contoh-skripsi.md` | Template referensi skripsi UNDIP |
