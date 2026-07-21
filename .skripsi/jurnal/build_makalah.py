"""build_makalah.py — Makalah TA 2 kolom, format mengikuti Fatih."""

from docx import Document
from docx.shared import Pt, Cm, Emu
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.oxml.ns import qn
from docx.oxml import OxmlElement
from pathlib import Path

HERE = Path(__file__).parent

def add_para(doc, text, size=Pt(10), bold=None, italic=None, alignment=None,
             fi=None, ls=None, sb=None, sa=None, fn='Times New Roman'):
    p = doc.add_paragraph('')
    if alignment is not None: p.alignment = alignment
    pf = p.paragraph_format
    if fi is not None: pf.first_line_indent = fi
    if ls is not None: pf.line_spacing = ls
    if sb is not None: pf.space_before = sb
    if sa is not None: pf.space_after = sa
    run = p.add_run(text)
    run.font.name = fn
    if size: run.font.size = size
    if bold is not None: run.bold = bold
    if italic is not None: run.italic = italic
    return p

def set_two_columns(sec):
    """Convert section to 2-column layout."""
    sectPr = sec._sectPr
    cols = OxmlElement('w:cols')
    cols.set(qn('w:num'), '2')
    cols.set(qn('w:space'), '720')  # 0.5cm gap
    sectPr.append(cols)

def add_section(doc, num_cols=2):
    """Add new section (continuous), set columns. Returns section."""
    sec = doc.add_section()
    # Change section break to continuous (no page break)
    sectPr = sec._sectPr
    for old in sectPr.findall(qn('w:type')):
        sectPr.remove(old)
    tp = OxmlElement('w:type')
    tp.set(qn('w:val'), 'continuous')
    sectPr.append(tp)
    src = doc.sections[0]
    for attr in ['page_width','page_height','top_margin','bottom_margin','left_margin','right_margin']:
        setattr(sec, attr, getattr(src, attr))
    sectPr = sec._sectPr
    # Remove existing cols
    for old in sectPr.findall(qn('w:cols')):
        sectPr.remove(old)
    if num_cols > 1:
        cols = OxmlElement('w:cols')
        cols.set(qn('w:num'), str(num_cols))
        cols.set(qn('w:space'), '720')
        sectPr.append(cols)
    return sec

def add_drop_cap(p, lines=3, size=Pt(32)):
    """Add Word drop cap to paragraph. First char becomes large, drops `lines` lines."""
    pPr = p._p.find(qn('w:pPr'))
    if pPr is None:
        pPr = OxmlElement('w:pPr')
        p._p.insert(0, pPr)
    for old in pPr.findall(qn('w:dropCap')):
        pPr.remove(old)
    drop = OxmlElement('w:dropCap')
    drop.set(qn('w:val'), 'drop')
    drop.set(qn('w:lines'), str(lines))
    pPr.append(drop)
    # Make first run large
    runs = p.runs
    if runs:
        runs[0].font.size = size

def add_drop_cap_after_heading(doc, heading_text, lines=3, drop_size=Pt(32)):
    """Find the first body paragraph after heading_text, apply drop cap to it."""
    found_heading = False
    for p in doc.paragraphs:
        txt = p.text.strip()
        if txt == heading_text:
            found_heading = True
            continue
        if found_heading and txt and not txt.startswith('['):
            # First body paragraph after heading
            # Remove first char from text, add as first run
            full = p.text
            first = full[0]
            rest = full[1:]
            p.clear()
            r1 = p.add_run(first)
            r1.font.name = 'Times New Roman'
            r1.font.size = drop_size
            r2 = p.add_run(rest)
            r2.font.name = 'Times New Roman'
            r2.font.size = Pt(10)
            add_drop_cap(p, lines=lines)
            return
    print(f'  [Warning] Could not find paragraph after "{heading_text}"')

def section_heading(doc, text):
    return add_para(doc, text, size=Pt(10), bold=True, alignment=WD_ALIGN_PARAGRAPH.CENTER,
                    ls=1.0, fi=Cm(0), sb=Pt(6), sa=Pt(0))

def subsection_heading(doc, text):
    return add_para(doc, text, size=Pt(10), bold=True, italic=True,
                    alignment=WD_ALIGN_PARAGRAPH.JUSTIFY,
                    ls=1.0, fi=Cm(-0.506), sb=Pt(3))

def body_text(doc, text):
    return add_para(doc, text, size=Pt(10), alignment=WD_ALIGN_PARAGRAPH.JUSTIFY,
                    sb=Pt(1))

def body_first(doc, text):
    """First body paragraph after heading: has first_line_indent"""
    return add_para(doc, text, size=Pt(10), alignment=WD_ALIGN_PARAGRAPH.JUSTIFY,
                    fi=Cm(0.381), sb=Pt(1))

def build_makalah():
    doc = Document()

    # ===== SECTION 0: COVER (single column) =====
    sec0 = doc.sections[0]
    sec0.page_width = Cm(21); sec0.page_height = Cm(29.7)
    sec0.top_margin = Cm(2.01); sec0.bottom_margin = Cm(0.49)
    sec0.left_margin = Cm(1.5); sec0.right_margin = Cm(1.5)

    ns = doc.styles['Normal']
    ns.font.name = 'Times New Roman'

    # Title
    add_para(doc, 'Makalah Tugas Akhir', size=Pt(11), alignment=WD_ALIGN_PARAGRAPH.CENTER,
             fi=Cm(0), sb=Pt(6))
    add_para(doc, 'Implementasi Sistem Manajemen Inventori pada Point of Sale W9 Cafe Menggunakan Laravel dan Filament',
             size=Pt(24), alignment=WD_ALIGN_PARAGRAPH.CENTER, fi=Cm(0), ls=1.05)
    add_para(doc, 'Muhammad Nio Hastungkoro\u00b9*), Yudi Eko Windarto\u00b9, Rinta Kridalukmana\u00b9',
             size=Pt(11), alignment=WD_ALIGN_PARAGRAPH.CENTER, fi=Cm(0), ls=1.05)
    add_para(doc, '\u00b9)Departemen Teknik Komputer, Fakultas Teknik, Universitas Diponegoro',
             size=Pt(11), italic=True, alignment=WD_ALIGN_PARAGRAPH.CENTER, fi=Cm(0))
    add_para(doc, 'Jl. Prof. Soedarto, SH, Kampus Undip Tembalang, Semarang, Indonesia 50275',
             size=Pt(11), italic=True, alignment=WD_ALIGN_PARAGRAPH.CENTER, fi=Cm(0))
    add_para(doc, 'E-mail: niohastungkoro@students.undip.ac.id',
             size=Pt(11), italic=True, alignment=WD_ALIGN_PARAGRAPH.CENTER, fi=Cm(0), sb=Pt(10))

    # ===== SECTION 1: Abstract + ALL CONTENT (2 columns) =====
    sec1 = add_section(doc, num_cols=2)

    # Abstract
    add_para(doc, 'Abstract - This research aims to design and implement an inventory management system for the W9 Cafe Point of Sale using Laravel and Filament. The system manages two parallel stock tracks: recipe-based raw materials and finished product menu stock. Two batch deduction modes are implemented: FEFO (First-Expiry-First-Out) and FIFO (First-In-First-Out), with pessimistic locking to prevent race conditions in concurrent transactions. The system consists of 11 database tables, 4 services, 2 observers, and a Filament administration panel.',
             size=Pt(9), bold=True, italic=True, alignment=WD_ALIGN_PARAGRAPH.JUSTIFY,
             fi=Cm(0), ls=1.0, sb=Pt(8))
    add_para(doc, 'Key Terms: inventory management system; Point of Sale; Laravel; Filament; FEFO; FIFO; batch tracking',
             size=Pt(11), italic=True, alignment=WD_ALIGN_PARAGRAPH.JUSTIFY, fi=Cm(0), sb=Pt(18))

    # Abstrak
    add_para(doc, 'Abstrak - Penelitian ini bertujuan merancang dan mengimplementasikan sistem manajemen inventori pada Point of Sale W9 Cafe menggunakan Laravel dan Filament. Sistem mengelola dua jalur stok paralel: bahan baku berbasis resep dan stok menu produk jadi. Mode deduksi batch FEFO dan FIFO diimplementasikan dengan pessimistic locking untuk mencegah race condition pada transaksi konkuren. Sistem terdiri dari 11 tabel basis data, 4 service, 2 observer, dan panel administrasi Filament.',
             size=Pt(9), bold=True, italic=True, alignment=WD_ALIGN_PARAGRAPH.JUSTIFY,
             fi=Cm(0), ls=1.0, sb=Pt(8))
    add_para(doc, 'Kata kunci: sistem manajemen inventori; Point of Sale; Laravel; Filament; FEFO; FIFO; batch tracking',
             size=Pt(11), italic=True, alignment=WD_ALIGN_PARAGRAPH.JUSTIFY, fi=Cm(0), sb=Pt(20))

    # I. PENDAHULUAN
    section_heading(doc, 'Pendahuluan')
    body_text(doc, 'Manajemen inventori merupakan aspek kritis dalam operasional kafe yang mempengaruhi ketersediaan bahan baku, kelancaran produksi, dan kepuasan pelanggan. Pada praktiknya, banyak kafe masih mencatat stok secara manual menggunakan kertas atau spreadsheet yang rentan terhadap kesalahan pencatatan, keterlambatan informasi stok, serta sultinya melacak riwayat pergerakan stok secara akurat [1].')
    body_text(doc, 'Sistem Point of Sale (POS) modern umumnya menyediakan fitur pencatatan transaksi penjualan, namun belum tentu dilengkapi dengan modul manajemen inventori yang komprehensif. Penelitian oleh Susila [2] mengembangkan sistem POS berbasis web yang mencakup otomatisasi laporan stok, namun belum membahas manajemen batch dengan mode deduksi FEFO dan FIFO secara terintegrasi dengan alur pemrosesan pesanan.')
    body_text(doc, 'Penelitian ini bertujuan merancang dan mengimplementasikan sistem manajemen inventori pada POS W9 Cafe menggunakan Laravel dan Filament. Sistem mampu mengelola dua jalur stok paralel (bahan baku berbasis resep dan stok menu produk jadi), mendukung mode deduksi batch FEFO dan FIFO dengan pessimistic locking, menyediakan immutable audit trail, serta terintegrasi dengan modul transaksi kasir melalui Service Layer.')
    add_drop_cap_after_heading(doc, 'Pendahuluan')

    # II. TINJAUAN PUSTAKA
    section_heading(doc, 'Tinjauan Pustaka')
    subsection_heading(doc, 'Penelitian Terdahulu')
    body_text(doc, 'Susila [2] mengembangkan aplikasi POS berbasis website dengan Laravel yang mencakup transaksi penjualan dan manajemen stok. Pratama dan Wijaya [3] menganalisis perbandingan algoritma FEFO dan FIFO pada sistem inventory bahan baku makanan. Nugroho [4] membahas implementasi admin panel Filament pada aplikasi Laravel, menunjukkan bahwa Filament menyediakan komponen antarmuka siap pakai yang terintegrasi dengan Eloquent ORM.')
    subsection_heading(doc, 'Sistem Manajemen Inventori')
    body_text(doc, 'Sistem manajemen inventori adalah serangkaian proses untuk mengelola, memantau, dan mengendalikan persediaan barang dalam suatu organisasi [5]. Dalam industri makanan dan minuman, inventori mencakup bahan baku dan barang jadi.')
    subsection_heading(doc, 'Algoritma Deduksi Batch dan Pessimistic Locking')
    body_text(doc, 'Metode deduksi batch yang umum digunakan adalah FEFO (First-Expiry-First-Out) untuk bahan dengan masa kedaluwarsa dan FIFO (First-In-First-Out) untuk bahan tanpa masa kedaluwarsa signifikan [3]. FEFO mengurutkan batch berdasarkan expiry_date ASC, sedangkan FIFO berdasarkan received_at ASC. Pessimistic locking dengan SELECT FOR UPDATE pada PostgreSQL digunakan untuk mencegah race condition pada transaksi konkuren [6].')
    subsection_heading(doc, 'Laravel dan Filament')
    body_text(doc, 'Laravel adalah kerangka kerja aplikasi web berbasis PHP dengan pola arsitektur MVC yang menyediakan Eloquent ORM, migration, dan event system [7]. Filament adalah pustaka antarmuka berbasis Tailwind CSS, Alpine.js, dan Livewire yang menyediakan komponen Resources untuk CRUD, Relation Managers, dan Tabbed Pages [4].')

    # III. METODE PENELITIAN
    section_heading(doc, 'Metode Penelitian')
    subsection_heading(doc, 'Arsitektur Sistem')
    body_text(doc, 'Sistem dibangun menggunakan Laravel 13 dengan arsitektur MVC yang diperluas dengan Service Layer. Lapisan presentasi menggunakan Filament untuk panel administrasi. Lapisan Service berisi InventoryService sebagai mesin utama deduksi stok bahan baku dan MenuStockService untuk deduksi stok menu. Basis data menggunakan PostgreSQL 18 dengan Eloquent ORM.')
    subsection_heading(doc, 'Perancangan Basis Data')
    body_text(doc, 'Sistem memiliki 11 tabel yang terbagi dalam dua sub-sistem. Sub-sistem bahan baku: ingredients, ingredient_batches, menu_ingredients, stock_movements (immutable), stock_adjustments, daily_ingredient_usages, dan stock_reports. Sub-sistem stok menu: menu_stocks, menu_stock_batches, menu_stock_adjustments, dan menu_stock_movements (immutable).')
    subsection_heading(doc, 'Algoritma Deduksi')
    body_text(doc, 'Deduksi stok dimulai ketika pesanan masuk. Sistem memeriksa apakah menu memiliki resep (menu_ingredients). Jika memiliki resep, sistem mendekomposisi pemakaian bahan baku dan mendebet IngredientBatch. Jika tidak, sistem mendebet MenuStockBatch melalui MenuStockService. Seluruh proses menggunakan pessimistic locking (lockForUpdate()) dengan ORDER BY id ASC dalam satu transaksi.')
    subsection_heading(doc, 'Immutable Audit Trail')
    body_text(doc, 'Model StockMovement dan MenuStockMovement mengimplementasikan immutable audit trail dengan mencegah operasi update dan delete melalui method booted() yang melempar LogicException.')

    # IV. HASIL DAN PEMBAHASAN
    section_heading(doc, 'Hasil dan Pembahasan')
    subsection_heading(doc, 'Implementasi Service Layer')
    body_text(doc, 'InventoryService (~453 baris) merupakan mesin utama deduksi stok. Method processSaleForOrder() memeriksa idempotensi, melakukan pre-validasi via canFulfillOrder(), dan mendelegasikan deduksi. Method deductIngredientStock() mengurutkan batch berdasarkan mode (FEFO/FIFO), mengunci dengan lockForUpdate(), mendebet stok, dan mencatat StockMovement.')
    body_text(doc, 'Pada menu dengan resep, sistem mendekomposisi pemakaian berdasarkan komposisi bahan baku melalui menu_ingredients. Setiap bahan baku dikalikan jumlah pesanan, lalu didebet dari IngredientBatch. Untuk menu tanpa resep, MenuStockBatch didebet melalui MenuStockService.')
    subsection_heading(doc, 'Implementasi Panel Filament')
    body_text(doc, 'Panel administrasi Filament terdiri dari 6 resource dan 2 halaman tabbed dalam grup Inventori. StokPage menampilkan tab Bahan Baku dan tab Menu. AdjustmentsPage menampilkan riwayat penyesuaian stok. StockResource menyediakan CRUD bahan baku dengan halaman manajemen batch.')
    subsection_heading(doc, 'Pengujian Black Box')
    body_text(doc, 'Pengujian black box memvalidasi fungsionalitas sistem dari sisi antarmuka. Skenario meliputi autentikasi admin, manajemen bahan baku (CRUD ingredient dan batch stok), penyesuaian stok (increase, decrease), manajemen resep menu, manajemen stok menu, dan dashboard. Seluruh skenario menunjukkan hasil sesuai yang diharapkan.')
    subsection_heading(doc, 'Pengujian White Box')
    body_text(doc, 'Pengujian white box memverifikasi algoritma deduksi. FIFO: batch dengan received_at terlama dikonsumsi pertama. FEFO: batch dengan expiry_date terdekat menjadi prioritas. Immutable: StockMovement tidak dapat diupdate/dihapus. Idempotensi: pemrosesan kedua pada order sama dilewati. Rollback: stok tidak berubah saat deduksi gagal.')

    # ⚠️ PENGUJIAN PERFORMA — Belum dijalankan, hanya skema
    subsection_heading(doc, 'Pengujian Performa')
    body_text(doc, '[Skema] Pengujian performa dirancang dengan skenario 5 proses deduksi simultan dari menu yang sama dan 3 proses penambahan stok simultan pada batch yang sama. Metrik: tidak terjadi deadlock, konsistensi stok akhir, dan seluruh StockMovement tercatat. (Pengujian belum dilaksanakan.)')

    # V. KESIMPULAN
    section_heading(doc, 'Kesimpulan')
    body_text(doc, 'Penelitian ini berhasil merancang dan mengimplementasikan sistem manajemen inventori pada POS W9 Cafe menggunakan Laravel dan Filament. Sistem mampu mengelola dua jalur stok paralel (bahan baku berbasis resep dan stok menu produk jadi), mendukung mode deduksi batch FEFO dan FIFO dengan pessimistic locking, serta menyediakan immutable audit trail. Panel administrasi Filament berhasil diimplementasikan dengan 6 resource dan 2 halaman tabbed. Pengujian black box memvalidasi fungsionalitas, white box memverifikasi algoritma deduksi.')
    body_text(doc, '[Pengujian performa belum dapat disimpulkan karena belum dilaksanakan.]')

    # ===== DAFTAR PUSTAKA (2 columns) =====
    section_heading(doc, 'Daftar Pustaka')
    refs = [
        '[1] A. B. Saputra, D. Pratama, dan R. Wijaya, "Sistem informasi manajemen stok bahan baku berbasis web menggunakan metode FIFO," J. Tek. Inform., vol. 14, no. 2, pp. 115\u2013124, 2023.',
        '[2] A. Susila, "Aplikasi point of sales (POS) berbasis website dengan menggunakan Laravel (Studi Kasus: Bakmi Djowo)," Skripsi, Univ. Amikom Yogyakarta, 2023.',
        '[3] C. D. Pratama dan E. Wijaya, "Analisis perbandingan algoritma FEFO dan FIFO pada sistem inventory," J. Sist. Inf., vol. 8, no. 1, pp. 22\u201335, 2023.',
        '[4] S. Nugroho, "Implementasi Filament admin panel pada aplikasi berbasis Laravel," J. Rekayasa Perangkat Lunak, vol. 6, no. 2, pp. 55\u201363, 2025.',
        '[5] R. H. Ballou, Business Logistics/Supply Chain Management, 5th ed. Upper Saddle River, NJ: Pearson Prentice Hall, 2004.',
        '[6] M. R. Firdaus dan A. S. Budi, "Implementasi transaksi basis data konkuren dengan pessimistic locking pada aplikasi berbasis web," J. Tek. Inform. dan Sist. Inf., vol. 7, no. 2, pp. 210\u2013222, 2023.',
        '[7] P. Garbarz dan M. Plechawska-W\u00f3jcik, "Comparative analysis of PHP frameworks on the example of Laravel and Symfony," J. Comput. Sci. Inst., vol. 23, pp. 150\u2013157, 2022.',
        '[8] I. Sommerville, Software Engineering, 10th ed. Boston, MA: Pearson, 2015.',
    ]
    for ref in refs:
        p = doc.add_paragraph('')
        p.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY
        pf = p.paragraph_format
        pf.left_indent = Cm(0.5)
        pf.first_line_indent = Cm(-0.5)
        run = p.add_run(ref)
        run.font.name = 'Times New Roman'; run.font.size = Pt(10)

    output_path = HERE / 'makalah.docx'
    if output_path.exists(): output_path.unlink()
    doc.save(str(output_path))
    print(f'\u2705 Makalah berhasil digenerate: {output_path}')
    print(f'   [Catatan] Pengujian performa: SKEMA SAJA, belum dijalankan.')
    print(f'   [Catatan] Judul gambar masih placeholder.')

if __name__ == '__main__':
    build_makalah()
