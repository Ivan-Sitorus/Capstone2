"""
create_template.py — Generate template DOCX skripsi dari nol.
Cukup jalankan sekali untuk menghasilkan template-skripsi.docx.

Usage:
    python create_template.py
"""

from docx import Document
from docx.shared import Pt, Cm, RGBColor, Emu
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.oxml.ns import qn
from docx.oxml import OxmlElement


def force_font(style, font_name, size_pt, bold=None):
    """Set font on style AND remove theme references so Word picks TNR."""
    rPr = style.element.get_or_add_rPr()
    rFonts = rPr.find(qn('w:rFonts'))
    if rFonts is None:
        rFonts = OxmlElement('w:rFonts')
        rPr.insert(0, rFonts)
    rFonts.set(qn('w:ascii'), font_name)
    rFonts.set(qn('w:hAnsi'), font_name)
    rFonts.set(qn('w:cs'), font_name)
    for attr in ['w:asciiTheme', 'w:hAnsiTheme', 'w:cstheme', 'w:eastAsiaTheme']:
        try:
            del rFonts.attrib[qn(attr)]
        except KeyError:
            pass
    sz = rPr.find(qn('w:sz'))
    if sz is None:
        sz = OxmlElement('w:sz')
        rPr.append(sz)
    sz.set(qn('w:val'), str(size_pt * 2))
    szCs = rPr.find(qn('w:szCs'))
    if szCs is None:
        szCs = OxmlElement('w:szCs')
        rPr.append(szCs)
    szCs.set(qn('w:val'), str(size_pt * 2))
    if bold is not None:
        b = rPr.find(qn('w:b'))
        if b is None:
            b = OxmlElement('w:b')
            rPr.append(b)
        if bold:
            b.set(qn('w:val'), 'true')
        else:
            b.set(qn('w:val'), 'false')


def add_section_break(doc):
    new_section = doc.add_section()
    prev = doc.sections[0]
    new_section.page_width = prev.page_width
    new_section.page_height = prev.page_height
    new_section.top_margin = prev.top_margin
    new_section.bottom_margin = prev.bottom_margin
    new_section.left_margin = prev.left_margin
    new_section.right_margin = prev.right_margin
    return new_section


def unlink_header_footer(section):
    try:
        section.header.is_linked_to_previous = False
    except Exception:
        pass
    try:
        section.footer.is_linked_to_previous = False
    except Exception:
        pass


def set_page_number_format(section, fmt='decimal', start=1):
    sectPr = section._sectPr
    existing = sectPr.find(qn('w:pgNumType'))
    if existing is not None:
        sectPr.remove(existing)
    pgNumType = OxmlElement('w:pgNumType')
    pgNumType.set(qn('w:fmt'), fmt)
    pgNumType.set(qn('w:start'), str(start))
    sectPr.append(pgNumType)


def add_page_number_bottom_center(section):
    footer = section.footer
    footer.is_linked_to_previous = False
    p = footer.paragraphs[0] if footer.paragraphs else footer.add_paragraph()
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r0 = p.add_run()
    f1 = OxmlElement('w:fldChar')
    f1.set(qn('w:fldCharType'), 'begin')
    r0._r.append(f1)
    r1 = p.add_run()
    r1.font.name = 'Times New Roman'
    r1.font.size = Pt(12)
    it = OxmlElement('w:instrText')
    it.set(qn('xml:space'), 'preserve')
    it.text = ' PAGE '
    r1._r.append(it)
    r2 = p.add_run()
    r2.font.name = 'Times New Roman'
    r2.font.size = Pt(12)
    f2 = OxmlElement('w:fldChar')
    f2.set(qn('w:fldCharType'), 'end')
    r2._r.append(f2)


def setup_numbering(doc):
    numbering_part = doc.part.numbering_part
    numbering = numbering_part.element

    existing_abs = numbering.findall(qn('w:abstractNum'))
    existing_ids = [int(a.get(qn('w:abstractNumId'))) for a in existing_abs]
    abs_id = max(existing_ids) + 1 if existing_ids else 0

    abs_num = OxmlElement('w:abstractNum')
    abs_num.set(qn('w:abstractNumId'), str(abs_id))
    mlt = OxmlElement('w:multiLevelType')
    mlt.set(qn('w:val'), 'multilevel')
    abs_num.append(mlt)

    indent_twips = '709'

    # Level 0 — Chapter counter (not linked to any style, incremented manually per chapter)
    lvl0 = OxmlElement('w:lvl')
    lvl0.set(qn('w:ilvl'), '0')
    for tag, val in [('w:start', '1'), ('w:numFmt', 'decimal'), ('w:lvlText', '%1.'), ('w:lvlJc', 'left')]:
        el = OxmlElement(tag)
        el.set(qn('w:val'), val)
        lvl0.append(el)
    lvl0_pPr = OxmlElement('w:pPr')
    lvl0_ind = OxmlElement('w:ind')
    lvl0_ind.set(qn('w:left'), indent_twips)
    lvl0_ind.set(qn('w:hanging'), indent_twips)
    lvl0_pPr.append(lvl0_ind)
    lvl0.append(lvl0_pPr)
    abs_num.append(lvl0)

    # Level 1 — Heading 2: format "1.1", "1.2"
    lvl1 = OxmlElement('w:lvl')
    lvl1.set(qn('w:ilvl'), '1')
    for tag, val in [('w:start', '1'), ('w:numFmt', 'decimal'), ('w:lvlText', '%1.%2.'), ('w:lvlJc', 'left')]:
        el = OxmlElement(tag)
        el.set(qn('w:val'), val)
        lvl1.append(el)

    lvl1_pPr = OxmlElement('w:pPr')
    lvl1_ind = OxmlElement('w:ind')
    lvl1_ind.set(qn('w:left'), indent_twips)
    lvl1_ind.set(qn('w:hanging'), indent_twips)
    lvl1_pPr.append(lvl1_ind)
    lvl1.append(lvl1_pPr)
    abs_num.append(lvl1)

    # Level 2 — Heading 3: format "1.1.1"
    lvl2 = OxmlElement('w:lvl')
    lvl2.set(qn('w:ilvl'), '2')
    for tag, val in [('w:start', '1'), ('w:numFmt', 'decimal'), ('w:lvlText', '%1.%2.%3.'), ('w:lvlJc', 'left')]:
        el = OxmlElement(tag)
        el.set(qn('w:val'), val)
        lvl2.append(el)

    lvl2_pPr = OxmlElement('w:pPr')
    lvl2_ind = OxmlElement('w:ind')
    lvl2_ind.set(qn('w:left'), indent_twips)
    lvl2_ind.set(qn('w:hanging'), indent_twips)
    lvl2_pPr.append(lvl2_ind)
    lvl2.append(lvl2_pPr)
    abs_num.append(lvl2)

    numbering.append(abs_num)

    # Num element (numId=1) — default starting at chapter 1
    num = OxmlElement('w:num')
    num.set(qn('w:numId'), '1')
    num_abs = OxmlElement('w:abstractNumId')
    num_abs.set(qn('w:val'), str(abs_id))
    num.append(num_abs)
    numbering.append(num)

    return abs_id


def create_template():
    doc = Document()

    section = doc.sections[0]
    section.page_width = Cm(21)
    section.page_height = Cm(29.7)
    section.top_margin = Cm(4)
    section.bottom_margin = Cm(3)
    section.left_margin = Cm(4)
    section.right_margin = Cm(3)

    # ── Normal Style ──
    normal = doc.styles['Normal']
    normal.font.name = 'Times New Roman'
    normal.font.size = Pt(12)
    normal.font.color.rgb = RGBColor(0, 0, 0)
    normal.paragraph_format.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY
    normal.paragraph_format.line_spacing = 1.5
    normal.paragraph_format.first_line_indent = Cm(1.25)
    normal.paragraph_format.space_before = Pt(0)
    normal.paragraph_format.space_after = Pt(0)
    force_font(normal, 'Times New Roman', 12)

    # ── Heading 1 (BAB) ──
    h1 = doc.styles['Heading 1']
    h1.font.name = 'Times New Roman'
    h1.font.size = Pt(12)
    h1.font.bold = True
    h1.font.color.rgb = RGBColor(0, 0, 0)
    h1.paragraph_format.alignment = WD_ALIGN_PARAGRAPH.CENTER
    h1.paragraph_format.space_before = Pt(0)
    h1.paragraph_format.space_after = Pt(0)
    h1.paragraph_format.keep_with_next = True
    h1.paragraph_format.keep_together = True
    h1.paragraph_format.line_spacing = 1.5
    h1.paragraph_format.first_line_indent = Cm(0)
    force_font(h1, 'Times New Roman', 12)

    # ── Heading 2 (Sub-bab) ──
    h2 = doc.styles['Heading 2']
    h2.font.name = 'Times New Roman'
    h2.font.size = Pt(12)
    h2.font.bold = True
    h2.font.color.rgb = RGBColor(0, 0, 0)
    h2.paragraph_format.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY
    h2.paragraph_format.space_before = Pt(0)
    h2.paragraph_format.space_after = Pt(0)
    h2.paragraph_format.keep_with_next = True
    h2.paragraph_format.keep_together = True
    h2.paragraph_format.line_spacing = 1.5
    h2.paragraph_format.first_line_indent = Cm(0)
    h2.paragraph_format.left_indent = Cm(1.25)
    h2.paragraph_format.first_line_indent = Cm(-1.25)
        # Tab stop at 1.25cm for heading number gap
    h2_xml = h2.element
    h2_pPr = h2_xml.find(qn('w:pPr'))
    if h2_pPr is not None:
        tabs_el = OxmlElement('w:tabs')
        tab_el = OxmlElement('w:tab')
        tab_el.set(qn('w:val'), 'left')
        tab_el.set(qn('w:pos'), '709')
        tabs_el.append(tab_el)
        h2_pPr.append(tabs_el)
    force_font(h2, 'Times New Roman', 12)

    # ── Heading 3 (Sub-sub-bab) ──
    h3 = doc.styles['Heading 3']
    h3.font.name = 'Times New Roman'
    h3.font.size = Pt(12)
    h3.font.bold = True
    h3.font.color.rgb = RGBColor(0, 0, 0)
    h3.paragraph_format.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY
    h3.paragraph_format.space_before = Pt(0)
    h3.paragraph_format.space_after = Pt(0)
    h3.paragraph_format.keep_with_next = True
    h3.paragraph_format.keep_together = True
    h3.paragraph_format.line_spacing = 1.5
    h3.paragraph_format.first_line_indent = Cm(0)
    h3.paragraph_format.left_indent = Cm(1.25)
    h3.paragraph_format.first_line_indent = Cm(-1.25)
        # Tab stop at 1.25cm for heading number gap
    h3_xml = h3.element
    h3_pPr = h3_xml.find(qn('w:pPr'))
    if h3_pPr is not None:
        tabs_el = OxmlElement('w:tabs')
        tab_el = OxmlElement('w:tab')
        tab_el.set(qn('w:val'), 'left')
        tab_el.set(qn('w:pos'), '709')
        tabs_el.append(tab_el)
        h3_pPr.append(tabs_el)
    force_font(h3, 'Times New Roman', 12)

    # ── ChapterTitle (baris judul bab kedua) ──
    chap = doc.styles.add_style('ChapterTitle', 1)
    chap.font.name = 'Times New Roman'
    chap.font.size = Pt(12)
    chap.font.bold = True
    chap.font.color.rgb = RGBColor(0, 0, 0)
    chap.paragraph_format.alignment = WD_ALIGN_PARAGRAPH.CENTER
    chap.paragraph_format.space_before = Pt(0)
    chap.paragraph_format.space_after = Pt(0)
    chap.paragraph_format.line_spacing = 1.5
    chap.paragraph_format.first_line_indent = Cm(0)
    chap.paragraph_format.keep_with_next = True
    chap.paragraph_format.keep_together = True
    force_font(chap, 'Times New Roman', 12)

    # ── Caption (keep Word built-in Caption style clean) ──
    caption = doc.styles['Caption']
    caption.font.name = 'Times New Roman'
    caption.font.size = Pt(11)
    caption.font.italic = False
    caption.font.bold = False
    caption.font.color.rgb = RGBColor(0, 0, 0)
    caption.paragraph_format.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY
    caption.paragraph_format.space_before = Pt(0)
    caption.paragraph_format.space_after = Pt(0)
    caption.paragraph_format.line_spacing = 1.0
    caption.paragraph_format.first_line_indent = Cm(0)
    force_font(caption, 'Times New Roman', 11)

    # ── TableCaption (TNR 10, justify, spasi 1.0, with outlineLvl for TOC detection) ──
    tc = doc.styles.add_style('TableCaption', 1)
    tc.font.name = 'Times New Roman'
    tc.font.size = Pt(10)
    tc.font.italic = False
    tc.font.bold = False
    tc.font.color.rgb = RGBColor(0, 0, 0)
    tc.paragraph_format.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY
    tc.paragraph_format.space_before = Pt(0)
    tc.paragraph_format.space_after = Pt(0)
    tc.paragraph_format.line_spacing = 1.0
    tc.paragraph_format.first_line_indent = Cm(0)
    force_font(tc, 'Times New Roman', 10)

    # ── FigureCaption (TNR 10, justify, spasi 1.0) ──
    fc = doc.styles.add_style('FigureCaption', 1)
    fc.font.name = 'Times New Roman'
    fc.font.size = Pt(10)
    fc.font.italic = False
    fc.font.bold = False
    fc.font.color.rgb = RGBColor(0, 0, 0)
    fc.paragraph_format.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY
    fc.paragraph_format.space_before = Pt(0)
    fc.paragraph_format.space_after = Pt(0)
    fc.paragraph_format.line_spacing = 1.0
    fc.paragraph_format.first_line_indent = Cm(0)
    force_font(fc, 'Times New Roman', 10)

    # ── AbstractText ──
    abst = doc.styles.add_style('AbstractText', 1)
    abst.font.name = 'Times New Roman'
    abst.font.size = Pt(10)
    abst.font.color.rgb = RGBColor(0, 0, 0)
    abst.paragraph_format.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY
    abst.paragraph_format.line_spacing = 1.0
    abst.paragraph_format.first_line_indent = Cm(1.25)
    abst.paragraph_format.space_before = Pt(0)
    abst.paragraph_format.space_after = Pt(0)
    force_font(abst, 'Times New Roman', 10)

    # ── AbstractTextItalic ──
    abst_i = doc.styles.add_style('AbstractTextItalic', 1)
    abst_i.font.name = 'Times New Roman'
    abst_i.font.size = Pt(10)
    abst_i.font.italic = True
    abst_i.font.color.rgb = RGBColor(0, 0, 0)
    abst_i.paragraph_format.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY
    abst_i.paragraph_format.line_spacing = 1.0
    abst_i.paragraph_format.first_line_indent = Cm(1.25)
    abst_i.paragraph_format.space_before = Pt(0)
    abst_i.paragraph_format.space_after = Pt(0)
    force_font(abst_i, 'Times New Roman', 10)

    # ── SourceCode ──
    src = doc.styles.add_style('SourceCode', 1)
    src.font.name = 'Courier New'
    src.font.size = Pt(10)
    src.font.color.rgb = RGBColor(0, 0, 0)
    src.paragraph_format.alignment = WD_ALIGN_PARAGRAPH.LEFT
    src.paragraph_format.line_spacing = 1.0
    src.paragraph_format.first_line_indent = Cm(0)
    src.paragraph_format.space_before = Pt(0)
    src.paragraph_format.space_after = Pt(0)
    force_font(src, 'Courier New', 10)

    # ── TOC Styles ──
    for lvl in range(1, 4):
        name = f'TOC {lvl}'
        try:
            toc = doc.styles[name]
        except KeyError:
            toc = doc.styles.add_style(name, 1)
        toc.font.name = 'Times New Roman'
        toc.font.size = Pt(12)
        toc.font.color.rgb = RGBColor(0, 0, 0)
        toc.paragraph_format.line_spacing = 1.5
        toc.paragraph_format.first_line_indent = Cm(0)
        toc.paragraph_format.space_before = Pt(0)
        toc.paragraph_format.space_after = Pt(0)
        force_font(toc, 'Times New Roman', 12)

    # ── Table Grid ──
    try:
        tg = doc.styles['Table Grid']
        tg.font.name = 'Times New Roman'
        tg.font.size = Pt(12)
        force_font(tg, 'Times New Roman', 12)
    except KeyError:
        pass

    # ── Multilevel Numbering ──
    setup_numbering(doc)

    # ── Sample headings to activate numbering in Word ──
    h1p = doc.add_paragraph('BAB I', style='Heading 1')
    h1t = doc.add_paragraph('PENDAHULUAN', style='ChapterTitle')
    h2p = doc.add_paragraph('Sub Bab', style='Heading 2')
    h3p = doc.add_paragraph('Sub Sub Bab', style='Heading 3')
    for p in [h1p, h1t, h2p, h3p]:
        p.clear()
        pp = p._element
        pp.getparent().remove(pp)

    # ── Section Setup (only romawi, build script adds arab section) ──
    add_page_number_bottom_center(section)
    set_page_number_format(section, 'lowerRoman', 1)

    output_path = 'template-skripsi.docx'
    doc.save(output_path)
    print(f'✅ Template berhasil dibuat: {output_path}')
    print(f'   - All fonts forced to TNR 12 (theme references removed)')
    print(f'   - Multilevel numbering: Heading1="BAB %1", Heading2="%1.%2.", Heading3="%1.%2.%3."')
    print(f'   - 1 Section (Romawi). Build script tambah Section Arab.')


if __name__ == '__main__':
    create_template()
