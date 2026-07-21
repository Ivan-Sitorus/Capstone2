"""
build_skripsi.py — Baca markdown per bab, generate DOCX skripsi lengkap.

Workflow:
    1. Load template-skripsi.docx (hasilkan dulu via create_template.py)
    2. Baca file markdown setiap bab
    3. Parse heading, paragraf, tabel, kode, gambar
    4. Generate DOCX final dengan formatting lengkap
    5. Simpan sebagai skripsi-lengkap.docx

Usage:
    python build_skripsi.py
"""

import re
import os
from pathlib import Path

# CrossRef DOI → IEEE citation (opsional)
try:
    from habanero import cn as crossref_cn
    HAS_HABANERO = True
except ImportError:
    HAS_HABANERO = False

from docx import Document
from docx.shared import Pt, Cm, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT
from docx.oxml.ns import qn
from docx.oxml import OxmlElement
from lxml import etree


# ── Konfigurasi ─────────────────────────────────────────────
SKRIPSI_DIR = Path(__file__).parent
TEMPLATE_PATH = SKRIPSI_DIR / 'template-skripsi.docx'
OUTPUT_PATH = SKRIPSI_DIR / 'skripsial.docx'

CHAPTERS = [
    ('halaman-pengesahan', 'pengesahan.md'),
    ('halaman-pernyataan', 'pernyataan-orisinalitas.md'),
    ('halaman-persetujuan-publikasi', 'pernyataan-publikasi.md'),
    ('kata-pengantar', 'kata-pengantar.md'),
    ('daftar-isi',   None),
    ('daftar-gambar', None),
    ('daftar-tabel', None),
    ('daftar-rumus',  None),
    ('abstrak',      'abstrak.md'),
    ('abstract',     'abstract.md'),
    ('bab1',         'bab1-pendahuluan.md'),
    ('bab2',         'bab2-kajian-pustaka.md'),
    ('bab3',         'bab3-perancangan-sistem.md'),
    ('bab4',         'bab4-implementasi-pengujian.md'),
    ('bab5',         'bab5-penutup.md'),
    ('daftar-pustaka', 'daftar-pustaka.md'),
]

ROMAN_MAP = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X']

# Daftar istilah teknis asing yang umum, untuk auto-italic
FOREIGN_TERMS = {
    'backend', 'frontend', 'database', 'framework', 'software', 'hardware',
    'middleware', 'endpoint', 'server', 'client', 'cache', 'query',
    'batch', 'fifo', 'fefo', 'algorithm', 'repository', 'service',
    'controller', 'view', 'component', 'module', 'dependency',
    'interface', 'implementation', 'abstract', 'extends', 'implements',
    'override', 'overload', 'constructor', 'destructor', 'namespace',
    'package', 'library', 'token', 'session', 'request', 'response',
    'payload', 'header', 'footer', 'sidebar', 'dashboard', 'default',
    'boolean', 'string', 'integer', 'float', 'array', 'object',
    'pointer', 'reference', 'variable', 'constant', 'iteration',
    'recursion', 'inheritance', 'polymorphism', 'encapsulation',
    'deadlock', 'race condition', 'thread', 'process', 'handler',
    'listener', 'event', 'callback', 'promise', 'async', 'await',
    'input', 'output', 'stream', 'buffer', 'offset', 'wrapper',
    'production', 'development', 'staging', 'deployment', 'pipeline',
    'commit', 'branch', 'merge', 'push', 'pull', 'clone', 'fork',
    'markdown', 'metadata', 'schema', 'migration', 'seeder', 'factory',
    'update', 'source', 'code', 'compile', 'runtime', 'argument',
    'property', 'attribute', 'method', 'function', 'statement',
    'cafe', 'spreadsheet', 'agile', 'pessimistic',
    'rollback', 'immutable', 'audit', 'multitasking', 'upsert',
    'browser', 'booting', 'loading', 'mouse', 'stock', 'adjustment',
}


# ── Helper Functions ─────────────────────────────────────────

def parse_markdown(filepath):
    """Parse markdown file into structured blocks."""
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    blocks = []
    lines = content.split('\n')
    i = 0
    code_buffer = []
    in_code = False
    in_table = False
    table_lines = []

    while i < len(lines):
        line = lines[i]

        # Code block
        if line.startswith('```'):
            if in_code:
                code_buffer.append(line)
                blocks.append({'type': 'code', 'content': '\n'.join(code_buffer)})
                code_buffer = []
                in_code = False
            else:
                if code_buffer:
                    blocks.append({'type': 'text', 'content': '\n'.join(code_buffer)})
                    code_buffer = []
                in_code = True
                code_buffer.append(line)
            i += 1
            continue

        if in_code:
            code_buffer.append(line)
            i += 1
            continue

        # Table (simple pipe table)
        if '|' in line and line.strip().startswith('|'):
            table_lines.append(line)
            in_table = True
            i += 1
            continue
        elif in_table:
            if table_lines:
                blocks.append({'type': 'table', 'content': table_lines[:]})
                table_lines = []
            in_table = False
            # Don't skip this line, process it normally

        # Heading
        h_match = re.match(r'^(#{1,6})\s+(.+)$', line)
        if h_match:
            level = len(h_match.group(1))
            text = h_match.group(2).strip()
            # Detect chapter heading (BAB I, BAB II, etc)
            bab_match = re.match(r'^BAB\s+([IVXLCDM]+)\s+(.+)$', text, re.IGNORECASE)
            if bab_match and level == 1:
                blocks.append({
                    'type': 'chapter',
                    'bab_num': bab_match.group(1),
                    'bab_roman': bab_match.group(1),
                    'title': bab_match.group(2),
                })
            else:
                blocks.append({'type': 'heading', 'level': level, 'text': text})
            i += 1
            continue

        # Empty line = paragraph break
        if line.strip() == '':
            if code_buffer:
                blocks.append({'type': 'text', 'content': '\n'.join(code_buffer)})
                code_buffer = []
            i += 1
            continue

        # Regular text
        code_buffer.append(line)
        i += 1

    if code_buffer:
        blocks.append({'type': 'text', 'content': '\n'.join(code_buffer)})

    if table_lines:
        blocks.append({'type': 'table', 'content': table_lines[:]})

    return blocks


def add_field_code(paragraph, field_code):
    """Add a Word field code (e.g., PAGE, TOC) to a paragraph."""
    run = paragraph.add_run()
    fld_begin = OxmlElement('w:fldChar')
    fld_begin.set(qn('w:fldCharType'), 'begin')
    run._r.append(fld_begin)

    run2 = paragraph.add_run()
    instr = OxmlElement('w:instrText')
    instr.set(qn('xml:space'), 'preserve')
    instr.text = field_code
    run2._r.append(instr)

    run3 = paragraph.add_run()
    fld_separate = OxmlElement('w:fldChar')
    fld_separate.set(qn('w:fldCharType'), 'separate')
    run3._r.append(fld_separate)

    run4 = paragraph.add_run()
    fld_end = OxmlElement('w:fldChar')
    fld_end.set(qn('w:fldCharType'), 'end')
    run4._r.append(fld_end)


def make_seq_run(p, field_text):
    """Insert a hidden SEQ field (invisible but detectable by TOC \\c)."""
    r0 = p.add_run()
    f0 = OxmlElement('w:fldChar'); f0.set(qn('w:fldCharType'), 'begin'); r0._r.append(f0)
    r1 = p.add_run()
    it = OxmlElement('w:instrText'); it.set(qn('xml:space'), 'preserve'); it.text = field_text; r1._r.append(it)
    r2 = p.add_run()
    f2 = OxmlElement('w:fldChar'); f2.set(qn('w:fldCharType'), 'separate'); r2._r.append(f2)
    # Hidden display text (vanish + noProof) so SEQ number invisible but field still works
    r3 = p.add_run()
    rPr3 = OxmlElement('w:rPr')
    for tag in ('w:vanish', 'w:noProof'):
        el = OxmlElement(tag)
        rPr3.append(el)
    r3._r.append(rPr3)
    t3 = OxmlElement('w:t'); t3.set(qn('xml:space'), 'preserve'); t3.text = ' '; r3._r.append(t3)
    r4 = p.add_run()
    f4 = OxmlElement('w:fldChar'); f4.set(qn('w:fldCharType'), 'end'); r4._r.append(f4)


def add_caption_with_seq(doc, text, seq_identifier, style_name):
    """Add caption paragraph with SEQ field (for TOC c-switch detection) + formatted text."""
    p = doc.add_paragraph('')
    p.style = doc.styles[style_name]
    p.paragraph_format.first_line_indent = Cm(0)
    # Insert SEQ field: { SEQ {seq_identifier} \* ARABIC }
    make_seq_run(p, f' SEQ {seq_identifier} \\* ARABIC')
    # Add space + caption text
    rs = p.add_run(' ')
    add_formatted_runs(p, text)
    return p


def add_heading_numbered(doc, text, level):
    if level == 1:
        p = doc.add_paragraph(text, style='Heading 1')
    elif level == 2:
        p = doc.add_paragraph(text, style='Heading 2')
    elif level == 3:
        p = doc.add_paragraph(text, style='Heading 3')
    else:
        p = doc.add_paragraph(text, style='Heading 1')
    return p


def extract_chapter_title(heading_text):
    m = re.match(r'^BAB\s+[IVXLCDM]+\s+(.+)', heading_text, re.IGNORECASE)
    if m:
        return m.group(1).strip()
    return heading_text


def add_formatted_runs(p, text):
    """Add runs to paragraph handling `code` (Courier New 10) and *italic* markers."""
    code_parts = re.split(r'(`[^`]+`)', text)
    for part in code_parts:
        if part.startswith('`') and part.endswith('`') and len(part) > 2:
            run = p.add_run(part[1:-1])
            run.font.name = 'Courier New'
            run.font.size = Pt(10)
        else:
            italic_parts = re.split(r'(\*[^*]+\*)', part)
            for ip in italic_parts:
                if ip.startswith('*') and ip.endswith('*') and len(ip) > 2:
                    run = p.add_run(ip[1:-1])
                    run.font.italic = True
                else:
                    words = re.split(r'(\s+)', ip)
                    for w in words:
                        clean = w.strip('.,;:!?()[]{}"\'')
                        if clean.isupper() and len(clean) > 1:
                            p.add_run(w)
                        elif clean.lower() in FOREIGN_TERMS:
                            run = p.add_run(w)
                            run.font.italic = True
                        else:
                            p.add_run(w)


def add_paragraph_with_italic(doc, text):
    return add_formatted_paragraph(doc, text, 'Normal')

def add_formatted_paragraph(doc, text, style='Normal'):
    """Add paragraph with italic (*...*) and inline code (`...`) formatting."""
    p = doc.add_paragraph(style=style)
    add_formatted_runs(p, text)
    return p


def add_code_block(doc, code_text):
    lines = code_text.split('\n')
    if lines and lines[0].startswith('```'):
        lines = lines[1:]
    if lines and lines[-1].strip() == '```':
        lines = lines[:-1]

    table = doc.add_table(rows=1, cols=1)
    table.alignment = WD_TABLE_ALIGNMENT.CENTER
    cell = table.cell(0, 0)
    tc_pr = cell._tc.get_or_add_tcPr()
    tc_borders = OxmlElement('w:tcBorders')
    for side in ('top', 'left', 'bottom', 'right'):
        border = OxmlElement(f'w:{side}')
        border.set(qn('w:val'), 'single')
        border.set(qn('w:sz'), '4')
        border.set(qn('w:color'), '999999')
        tc_borders.append(border)
    tc_pr.append(tc_borders)

    # Clear default empty paragraph, reuse first paragraph for first line
    for para in cell.paragraphs:
        para.clear()
    for idx, code_line in enumerate(lines):
        if idx == 0:
            p = cell.paragraphs[0]
        else:
            p = cell.add_paragraph()
        p.text = code_line
        p.style = doc.styles['SourceCode']


def add_table_from_lines(doc, lines):
    rows_data = []
    for line in lines:
        if line.strip().startswith('|') and line.strip().endswith('|'):
            cells = [c.strip() for c in line.strip().split('|')[1:-1]]
            if cells:
                rows_data.append(cells)
            if re.match(r'^[\s|:\-]+$', line):
                continue

    if not rows_data:
        return

    headers = rows_data[0]
    data_rows = rows_data[1:] if len(rows_data) > 1 else []
    if len(rows_data) > 1 and all('-' in c for c in rows_data[1]):
        data_rows = rows_data[2:] if len(rows_data) > 2 else []
    if not data_rows:
        return

    table = doc.add_table(rows=1 + len(data_rows), cols=len(headers))
    table.style = 'Table Grid'
    table.alignment = WD_TABLE_ALIGNMENT.CENTER

    for j, header in enumerate(headers):
        cell = table.cell(0, j)
        cell.text = ''
        p = cell.paragraphs[0]
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        p.paragraph_format.first_line_indent = Cm(0)
        p.paragraph_format.space_before = Pt(0)
        p.paragraph_format.space_after = Pt(0)
        run = p.add_run(header)
        run.bold = True
        run.font.name = 'Times New Roman'
        run.font.size = Pt(12)

    for i, row in enumerate(data_rows):
        for j, cell_text in enumerate(row):
            cell = table.cell(i + 1, j)
            cell.text = ''
            p = cell.paragraphs[0]
            p.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY
            p.paragraph_format.first_line_indent = Cm(0)
            p.paragraph_format.space_before = Pt(0)
            p.paragraph_format.space_after = Pt(0)
            add_italic_runs(p, cell_text)
            for run in p.runs:
                if not run.font.name:
                    run.font.name = 'Times New Roman'
                    run.font.size = Pt(12)


def add_image(doc, image_path, caption='', width_cm=12):
    """Add image with caption."""
    if not os.path.exists(image_path):
        doc.add_paragraph(f'[Gambar: {os.path.basename(image_path)}]', style='Normal')
        return

    paragraph = doc.add_paragraph()
    paragraph.alignment = WD_ALIGN_PARAGRAPH.CENTER
    run = paragraph.add_run()
    run.add_picture(image_path, width=Cm(width_cm))

    if caption:
        add_caption_with_seq(doc, caption, 'Figure', 'FigureCaption')


def fetch_ieee_from_doi(doi):
    """Fetch IEEE citation from CrossRef using habanero."""
    if not HAS_HABANERO:
        return None
    try:
        clean_doi = doi.strip()
        return crossref_cn.content_negotiation(
            ids=clean_doi,
            format="text",
            style="ieee"
        )
    except Exception:
        return None


def process_daftar_pustaka(doc, filepath):
    """Process daftar-pustaka.md, resolving DOI references automatically."""
    with open(filepath, 'r', encoding='utf-8') as f:
        lines = f.readlines()

    for line in lines:
        line = line.strip()
        if not line:
            doc.add_paragraph('')
            continue

        # Detect DOI reference: [n] doi:10.xxxxx/xxxxx
        doi_match = re.match(r'^\[(\d+)\]\s*doi:\s*(10\.[^\s]+)', line, re.IGNORECASE)
        if doi_match:
            num = doi_match.group(1)
            doi = doi_match.group(2)
            citation = fetch_ieee_from_doi(doi)
            if citation:
                p = doc.add_paragraph(f'[{num}] {citation}', style='Normal')
                p.paragraph_format.first_line_indent = Cm(0)
                p.paragraph_format.left_indent = Cm(1.25)
                p.paragraph_format.hanging_indent = Cm(1.25)
            else:
                doc.add_paragraph(f'[{num}] [Gagal fetch DOI: {doi}]', style='Normal')
            continue

        # Regular reference line: [n] Teks IEEE ...
        ref_match = re.match(r'^\[(\d+)\]\s+(.+)', line)
        if ref_match:
            p = doc.add_paragraph(line, style='Normal')
            p.paragraph_format.first_line_indent = Cm(0)
            p.paragraph_format.left_indent = Cm(1.25)
            p.paragraph_format.hanging_indent = Cm(1.25)
            continue

        # Regular text
        doc.add_paragraph(line, style='Normal')


def copy_margins(target, source):
    for a in ['page_width','page_height','top_margin','bottom_margin','left_margin','right_margin']:
        setattr(target, a, getattr(source, a))

def add_body_sec(doc):
    sec = doc.add_section()
    copy_margins(sec, doc.sections[0])
    sp = sec._sectPr
    ex = sp.find(qn('w:pgNumType'))
    if ex is not None: sp.remove(ex)
    pn = OxmlElement('w:pgNumType'); pn.set(qn('w:fmt'), 'decimal'); sp.append(pn)
    sec.different_first_page_header_footer = True

    fp = sec.first_page_footer
    try: fp.is_linked_to_previous = False
    except: pass
    pp = fp.paragraphs[0] if fp.paragraphs else fp.add_paragraph()
    pp.alignment = WD_ALIGN_PARAGRAPH.CENTER; pp.paragraph_format.first_line_indent = Cm(0)
    r0 = pp.add_run(); f1 = OxmlElement('w:fldChar'); f1.set(qn('w:fldCharType'), 'begin'); r0._r.append(f1)
    r1 = pp.add_run(); r1.font.name='Times New Roman'; r1.font.size=Pt(12)
    it = OxmlElement('w:instrText'); it.set(qn('xml:space'), 'preserve'); it.text=' PAGE '; r1._r.append(it)
    r2 = pp.add_run(); r2.font.name='Times New Roman'; r2.font.size=Pt(12)
    f2 = OxmlElement('w:fldChar'); f2.set(qn('w:fldCharType'), 'end'); r2._r.append(f2)

    df = sec.footer
    try: df.is_linked_to_previous = False
    except: pass
    for p in df.paragraphs: p.clear()

    dh = sec.header
    try: dh.is_linked_to_previous = False
    except: pass
    hp = dh.paragraphs[0] if dh.paragraphs else dh.add_paragraph()
    hp.alignment = WD_ALIGN_PARAGRAPH.RIGHT; hp.paragraph_format.first_line_indent = Cm(0)
    r0 = hp.add_run(); f1 = OxmlElement('w:fldChar'); f1.set(qn('w:fldCharType'), 'begin'); r0._r.append(f1)
    r1 = hp.add_run(); r1.font.name='Times New Roman'; r1.font.size=Pt(10)
    it = OxmlElement('w:instrText'); it.set(qn('xml:space'), 'preserve'); it.text=' PAGE '; r1._r.append(it)
    r2 = hp.add_run(); r2.font.name='Times New Roman'; r2.font.size=Pt(10)
    f2 = OxmlElement('w:fldChar'); f2.set(qn('w:fldCharType'), 'end'); r2._r.append(f2)

    fh = sec.first_page_header
    try: fh.is_linked_to_previous = False
    except: pass
    for p in fh.paragraphs: p.clear()


def add_italic_runs(p, text):
    add_formatted_runs(p, text)

def make_cover_run(p, text, bold=True, italic=False):
    r = p.add_run(text)
    r.bold = bold
    r.italic = italic
    r.font.name = 'Times New Roman'
    r.font.size = Pt(12)
    return r

def handle_cafe_line(p, line):
    idx = line.index('W9 Cafe')
    if idx > 0:
        make_cover_run(p, line[:idx])
    make_cover_run(p, 'W9 ')
    make_cover_run(p, 'Cafe', italic=True)
    after = idx + 7
    if after < len(line):
        make_cover_run(p, line[after:])

def generate_cover_page(doc):
    lines = [
        'IMPLEMENTASI SISTEM MANAJEMEN INVENTORI',
        'PADA POINT OF SALE W9 CAFE',
        'MENGGUNAKAN LARAVEL DAN FILAMENT',
        'TUGAS AKHIR',
        '',
        'Diajukan sebagai salah satu syarat untuk memperoleh gelar',
        'Sarjana Teknik',
        '',
        'MUHAMMAD NIO HASTUNGKORO',
        '21120122140155',
        '',
        'DEPARTEMEN TEKNIK KOMPUTER',
        'FAKULTAS TEKNIK',
        'UNIVERSITAS DIPONEGORO',
        'SEMARANG',
        '2026',
    ]
    for line in lines:
        p = doc.add_paragraph('')
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        p.paragraph_format.first_line_indent = Cm(0)
        p.paragraph_format.line_spacing = 1.5
        if line:
            if 'W9 Cafe' in line:
                handle_cafe_line(p, line)
            else:
                make_cover_run(p, line)
    doc.add_page_break()

def render_prelim_page(doc, title, lines, center_title=True, subtitle=None):
    h = doc.add_paragraph('')
    h.style = doc.styles['Heading 1']
    h.alignment = WD_ALIGN_PARAGRAPH.CENTER
    h.paragraph_format.first_line_indent = Cm(0)
    h.paragraph_format.line_spacing = 1.5
    run = h.add_run(title)
    run.bold = True
    if subtitle:
        p = doc.add_paragraph('')
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        p.paragraph_format.first_line_indent = Cm(0)
        p.paragraph_format.line_spacing = 1.5
        r2 = p.add_run(subtitle)
        r2.bold = True
        r2.font.name = 'Times New Roman'
        r2.font.size = Pt(12)
    for line_data in lines:
        if isinstance(line_data, str):
            txt, bld, itl = line_data, False, False
        else:
            txt, bld, itl = line_data
        if not txt.strip():
            doc.add_paragraph('')
        else:
            p = doc.add_paragraph('')
            p.alignment = WD_ALIGN_PARAGRAPH.CENTER if center_title else WD_ALIGN_PARAGRAPH.JUSTIFY
            p.paragraph_format.first_line_indent = Cm(0)
            p.paragraph_format.line_spacing = 1.5
            run = p.add_run(txt)
            if bld: run.bold = True
            if itl: run.italic = True
            run.font.name = 'Times New Roman'
            run.font.size = Pt(12)
    doc.add_page_break()

def generate_pengesahan(doc):
    render_prelim_page(doc, 'HALAMAN PENGESAHAN', [
        ('Tugas Akhir', True, False), '',
        ('IMPLEMENTASI SISTEM MANAJEMEN INVENTORI PADA', True, False),
        ('POINT OF SALE W9 CAFE MENGGUNAKAN', True, False),
        ('LARAVEL DAN FILAMENT', True, False), '',
        'Tugas Akhir ini diajukan oleh:',
        ('Muhammad Nio Hastungkoro', False, False),
        ('21120122140155', False, False), '',
        'Kepada',
        'Departemen Teknik Komputer',
        'Universitas Diponegoro', '',
        'Telah disetujui Oleh:', '', '',
        ('Pembimbing I', False, False),
        ('Yudi Eko Windarto, S.T., M.Kom.', False, False), '',
        ('Pembimbing II', False, False),
        ('Rinta Kridalukmana, S.Kom., M.T., Ph.D.', False, False),
    ], center_title=True)

def generate_pernyataan_pages(doc):
    render_prelim_page(doc, 'HALAMAN PERNYATAAN ORISINALITAS', [
        'Tugas Akhir ini adalah hasil karya saya sendiri, dan semua sumber baik yang dikutip maupun yang dirujuk telah saya nyatakan dengan benar.', '',
        ('Nama : Muhammad Nio Hastungkoro', False, False),
        ('NIM : 21120122140155', False, False),
        'Tanda Tangan :',
        'Tanggal : 23 Mei 2026',
    ], center_title=False)
    render_prelim_page(doc, 'HALAMAN PERNYATAAN PERSETUJUAN PUBLIKASI', [
        'Sebagai sivitas akademika Universitas Diponegoro, saya yang bertanda tangan di bawah ini:', '',
        ('Nama : MUHAMMAD NIO HASTUNGKORO', False, False),
        ('NIM : 21120122140155', False, False),
        ('Departemen : TEKNIK KOMPUTER', False, False),
        ('Fakultas : TEKNIK', False, False),
        ('Jenis Karya : TUGAS AKHIR', False, False), '',
        'Demi pengembangan ilmu pengetahuan, menyetujui untuk memberikan kepada Universitas Diponegoro Hak Bebas Royalti Non Eksklusif (Non-exclusive Royalty Free Right) atas karya ilmiah saya berjudul:', '',
        ('IMPLEMENTASI SISTEM MANAJEMEN INVENTORI PADA POINT OF SALE W9 CAFE MENGGUNAKAN LARAVEL DAN FILAMENT', False, False), '',
        'beserta perangkat yang ada (jika diperlukan). Dengan Hak Bebas Royalti/Non Eksklusif ini Universitas Diponegoro berhak menyimpan, mengalih media/formatkan, mengelola dalam bentuk pangkalan data (database), merawat dan memublikasikan Tugas Akhir saya selama tetap mencantumkan nama saya sebagai penulis/pencipta dan sebagai pemilik Hak Cipta. Demikian pernyataan ini saya buat dengan sebenarnya.', '',
        'Dibuat di: Semarang',
        'Pada tanggal: 23 Mei 2026',
        'Yang menyatakan,', '',
        '(Muhammad Nio Hastungkoro)',
    ], center_title=False, subtitle='TUGAS AKHIR UNTUK KEPENTINGAN AKADEMIS')

def suppress_footer(sec):
    try: sec.footer.is_linked_to_previous = False
    except: pass
    for p in sec.footer.paragraphs:
        p.clear()

def set_pgnum(section, fmt, start):
    sp = section._sectPr
    ex = sp.find(qn('w:pgNumType'))
    if ex is not None: sp.remove(ex)
    pn = OxmlElement('w:pgNumType')
    pn.set(qn('w:fmt'), fmt)
    pn.set(qn('w:start'), str(start))
    sp.append(pn)

def add_pgnum_footer(section):
    footer = section.footer
    try: footer.is_linked_to_previous = False
    except: pass
    pp = footer.paragraphs[0] if footer.paragraphs else footer.add_paragraph()
    pp.alignment = WD_ALIGN_PARAGRAPH.CENTER
    pp.paragraph_format.first_line_indent = Cm(0)
    r0 = pp.add_run()
    f1 = OxmlElement('w:fldChar'); f1.set(qn('w:fldCharType'), 'begin'); r0._r.append(f1)
    r1 = pp.add_run(); r1.font.name='Times New Roman'; r1.font.size=Pt(12)
    it = OxmlElement('w:instrText'); it.set(qn('xml:space'), 'preserve'); it.text=' PAGE '; r1._r.append(it)
    r2 = pp.add_run(); r2.font.name='Times New Roman'; r2.font.size=Pt(12)
    f2 = OxmlElement('w:fldChar'); f2.set(qn('w:fldCharType'), 'end'); r2._r.append(f2)

def build_skripsi():
    if not TEMPLATE_PATH.exists():
        print(f'❌ Template tidak ditemukan: {TEMPLATE_PATH}')
        print('   Jalankan dulu: python create_template.py')
        return

    doc = Document(str(TEMPLATE_PATH))

    # ===== Section 0: Cover (NO page number) =====
    generate_cover_page(doc)
    suppress_footer(doc.sections[0])

    # ===== Section 1: Pengesahan + Pernyataan + Publikasi (Roman i, ii, iii...) =====
    sec1 = doc.add_section()
    copy_margins(sec1, doc.sections[0])
    set_pgnum(sec1, 'lowerRoman', 1)
    add_pgnum_footer(sec1)
    generate_pengesahan(doc)
    generate_pernyataan_pages(doc)

    # ===== Body chapters start with add_body_sec() =====
    chapter_index = 0

    for chap_id, filename in CHAPTERS:
        if chap_id in ('halaman-pengesahan', 'halaman-pernyataan', 'halaman-persetujuan-publikasi'):
            continue

        if chap_id == 'daftar-isi':
            doc.add_page_break()
            doc.add_paragraph('DAFTAR ISI', style='Heading 1').alignment = WD_ALIGN_PARAGRAPH.CENTER
            p = doc.add_paragraph()
            p.paragraph_format.first_line_indent = Cm(0)
            add_field_code(p, 'TOC \\o "1-3" \\h \\z \\u')
            p.add_run('\n(Klik kanan → Update Field untuk menampilkan Daftar Isi)').font.size = Pt(10)
            continue

        if chap_id == 'daftar-gambar':
            doc.add_page_break()
            doc.add_paragraph('DAFTAR GAMBAR', style='Heading 1').alignment = WD_ALIGN_PARAGRAPH.CENTER
            p = doc.add_paragraph()
            p.paragraph_format.first_line_indent = Cm(0)
            add_field_code(p, 'TOC \\c "Figure" \\h \\z')
            p.add_run('\n(Klik kanan → Update Field untuk menampilkan Daftar Gambar)').font.size = Pt(10)
            continue

        if chap_id == 'daftar-tabel':
            doc.add_page_break()
            doc.add_paragraph('DAFTAR TABEL', style='Heading 1').alignment = WD_ALIGN_PARAGRAPH.CENTER
            p = doc.add_paragraph()
            p.paragraph_format.first_line_indent = Cm(0)
            add_field_code(p, 'TOC \\c "Table" \\h \\z')
            p.add_run('\n(Klik kanan → Update Field untuk menampilkan Daftar Tabel)').font.size = Pt(10)
            continue

        if chap_id == 'daftar-rumus':
            doc.add_page_break()
            doc.add_paragraph('DAFTAR RUMUS', style='Heading 1').alignment = WD_ALIGN_PARAGRAPH.CENTER
            p = doc.add_paragraph()
            p.paragraph_format.first_line_indent = Cm(0)
            add_field_code(p, 'TOC \\c "Rumus" \\h \\z')
            p.add_run('\n(Klik kanan → Update Field untuk menampilkan Daftar Rumus)').font.size = Pt(10)
            continue

        if chap_id == 'daftar-pustaka':
            filepath = SKRIPSI_DIR / filename
            if filepath.exists():
                doc.add_page_break()
                doc.add_heading('DAFTAR PUSTAKA', level=1).alignment = WD_ALIGN_PARAGRAPH.CENTER
                process_daftar_pustaka(doc, filepath)
            continue

        if filename is None:
            continue

        filepath = SKRIPSI_DIR / filename
        if not filepath.exists():
            print(f'⚠️  File tidak ditemukan: {filepath}, dilewati.')
            continue

        blocks = parse_markdown(filepath)
        print(f'📖 Memproses: {filename} ({len(blocks)} blok)')

        if chap_id == 'abstrak':
            para_style = 'AbstractText'
        elif chap_id == 'abstract':
            para_style = 'AbstractTextItalic'
        else:
            para_style = 'Normal'

        for block in blocks:
            t = block['type']

            if t == 'chapter':
                chapter_index += 1
                add_body_sec(doc)
                title = block['title']
                roman = {1:'I',2:'II',3:'III',4:'IV',5:'V',6:'VI',7:'VII',8:'VIII',9:'IX',10:'X'}.get(chapter_index, str(chapter_index))
                h1 = doc.add_paragraph(f'BAB {roman}', style='Heading 1')
                h1.alignment = WD_ALIGN_PARAGRAPH.CENTER
                ct = doc.add_paragraph(title, style='ChapterTitle')
                ct.alignment = WD_ALIGN_PARAGRAPH.CENTER
                ep = doc.add_paragraph('')
                ep.paragraph_format.first_line_indent = Cm(0)

            elif t == 'heading':
                level = block['level']
                text = block['text']
                if level == 1:
                    doc.add_page_break()
                    p = doc.add_paragraph(text, style='Heading 1')
                    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
                elif level == 2:
                    sp = text.split(' ', 1)
                    h2_text = sp[0] + '	' + sp[1] if len(sp) > 1 else text
                    ep2 = doc.add_paragraph('')
                    ep2.paragraph_format.first_line_indent = Cm(0)
                    doc.add_paragraph(h2_text, style='Heading 2')
                elif level == 3:
                    sp = text.split(' ', 1)
                    h3_text = sp[0] + '	' + sp[1] if len(sp) > 1 else text
                    ep2 = doc.add_paragraph('')
                    ep2.paragraph_format.first_line_indent = Cm(0)
                    doc.add_paragraph(h3_text, style='Heading 3')
                else:
                    doc.add_paragraph(text, style='Heading 1')

            elif t == 'text':
                text = block['content'].strip()
                text = text.replace('**', '')
                if text:
                    # Split multi-line text blocks into individual lines for processing
                    for line in text.split('\n'):
                        line = line.strip()
                        if not line:
                            continue
                        img_match = re.match(r'^!\[(.*)\]\((.+)\)$', line)
                        if img_match:
                            caption = img_match.group(1)
                            img_path = str(SKRIPSI_DIR / img_match.group(2))
                            add_image(doc, img_path, caption)
                        elif re.match(r'^Tabel\s+\d+\.\d+\s+', line):
                            add_caption_with_seq(doc, line, 'Table', 'TableCaption')
                        elif re.match(r'^Gambar\s+\d+\.\d+\s+', line):
                            add_caption_with_seq(doc, line, 'Figure', 'FigureCaption')
                        elif re.match(r'^Rumus\s+\d+\.\d+\s+', line):
                            add_caption_with_seq(doc, line, 'Rumus', 'FigureCaption')
                        elif re.match(r'^BAB\s+[IVXLCDM]+\s+', line):
                            p = doc.add_paragraph('')
                            p.alignment = WD_ALIGN_PARAGRAPH.LEFT
                            p.paragraph_format.first_line_indent = Cm(0)
                            p.paragraph_format.left_indent = Cm(0)
                            run = p.add_run(line)
                            run.bold = True
                            run.font.name = 'Times New Roman'
                            run.font.size = Pt(12)
                        elif re.match(r'^\d+[\.\)]\s', line):
                            lines_list = line.split('\n')
                            for item in lines_list:
                                item = item.strip()
                                if not item:
                                    continue
                                sp = item.split(' ', 1)
                                num_text = sp[0] + '\t' + sp[1] if len(sp) > 1 else item
                                p = add_formatted_paragraph(doc, num_text, para_style)
                                p.paragraph_format.left_indent = Cm(1.25)
                                p.paragraph_format.first_line_indent = Cm(-1.25)
                        else:
                            add_formatted_paragraph(doc, line, para_style)

            elif t == 'code':
                code_text = block['content']
                if code_text.strip():
                    add_code_block(doc, code_text)

            elif t == 'table':
                add_table_from_lines(doc, block['content'])

    doc.save(str(OUTPUT_PATH))
    print(f'\n✅ Skripsi berhasil digenerate: {OUTPUT_PATH}')
    print(f'   Buka di Word, lalu:')
    print(f'   1. Klik kanan Daftar Isi → Update Field')
    print(f'   2. Klik kanan Daftar Gambar → Update Field')
    print(f'   3. Klik kanan Daftar Tabel → Update Field')
    print(f'   4. Periksa nomor halaman dan formatting')


if __name__ == '__main__':
    build_skripsi()
