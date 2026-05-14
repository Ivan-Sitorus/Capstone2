# Laporan Keuangan (Financial Reports)

> **Modul:** Admin & Finance  
> **Fase:** Fitur Admin dan Keuangan  
> **Penulis:** Tim Capstone W9 Cafe POS

---

## Daftar Isi

1. [Arsitektur Layanan](#arsitektur-layanan)
2. [3 Jenis Laporan](#3-jenis-laporan)
3. [Tampilan Excel-like dengan AG Grid](#tampilan-excel-like-dengan-ag-grid)
4. [Header Template (ReportHeaderTemplate)](#header-template-reportheadertemplate)
5. [SAK Standar untuk Referensi](#sak-standar-untuk-referensi)
6. [Teknis Implementasi](#teknis-implementasi)
7. [PDF Export](#pdf-export)
8. [Edge Cases](#edge-cases)

---

## Arsitektur Layanan

Sistem laporan keuangan menggunakan arsitektur _service layer_ dengan satu service utama yang menjadi _facade_ untuk semua jenis laporan.

```
FinancialReportService (facade)
├── generate('simple', $params)  → SimpleReportService (SAK EMKM)
├── generate('rigid', $params)   → RigidReportService (SAK EP)
└── generate('custom', $params)  → CustomReportService (Bebas)
```

**File-file inti:**

| File | Deskripsi |
|------|-----------|
| `app/Services/FinancialReportService.php` | Facade utama, dispatch ke sub-service berdasarkan `$type` |
| `app/Services/RigidReportService.php` | Laporan SAK EP: Neraca, Laba Rugi, Perubahan Ekuitas, Arus Kas, CALK |
| `app/Services/SimpleReportService.php` | Laporan SAK EMKM: Neraca, Laba Rugi, CALK |
| `app/Services/CustomReportService.php` | Laporan custom dengan filter bebas (kategori, agregasi, periode) |
| `app/Models/ReportTemplate.php` | Template laporan tersimpan per user |
| `app/Models/GeneratedReport.php` | Hasil generate laporan yang sudah disimpan |
| `app/DTO/ReportData.php` | Data Transfer Object untuk hasil laporan |

**Route Filament:**

- `app/Filament/Clusters/Financial/FinancialCluster.php` — Cluster modul keuangan
- `app/Filament/Clusters/Financial/Pages/SavedTemplates.php` — CRUD template laporan
- `app/Filament/Clusters/Financial/Pages/GeneratedReports.php` — Riwayat laporan yang sudah di-generate
- `app/Filament/Pages/ViewReport.php` — Halaman preview laporan

---

## 3 Jenis Laporan

### 1. Rigid (SAK EP — Standar Akuntansi Keuangan Entitas Privat)

Mengikuti PSAK (Pernyataan Standar Akuntansi Keuangan) untuk entitas privat. Menghasilkan **5 laporan**:

| No | Laporan | Deskripsi |
|----|---------|-----------|
| 1 | **Neraca** (Balance Sheet) | Posisi keuangan: Aset, Liabilitas, Ekuitas per tanggal tertentu |
| 2 | **Laporan Laba Rugi** (Income Statement) | Pendapatan, Beban, Laba/Rugi bersih untuk periode |
| 3 | **Laporan Perubahan Ekuitas** | Mutasi ekuitas pemilik selama periode |
| 4 | **Laporan Arus Kas** (Cash Flow Statement) | Arus kas masuk/keluar: Operasi, Investasi, Pendanaan |
| 5 | **CaLK** (Catatan atas Laporan Keuangan) | Penjelasan rinci setiap pos laporan |

**Karakteristik:**
- Basis akrual (accrual basis) — pendapatan diakui saat terjadi, bukan saat kas diterima
- Format baku sesuai SAK EP
- Menggunakan `RigidReportService` (saat ini di-*deprecate*, migrasi ke `FinancialReportService`)

### 2. Simpel (SAK EMKM — Entitas Mikro, Kecil, Menengah)

Dirancang untuk usaha kecil seperti W9 Cafe. Menghasilkan **3 laporan**:

| No | Laporan | Deskripsi |
|----|---------|-----------|
| 1 | **Neraca** | Posisi keuangan sederhana (Aset - Liabilitas = Ekuitas) |
| 2 | **Laporan Laba Rugi** | Pendapatan dikurangi Beban |
| 3 | **CaLK** | Catatan atas Laporan Keuangan (penjelasan singkat) |

**Karakteristik:**
- Basis kas (cash basis) — pendapatan diakui saat kas diterima
- Format lebih sederhana dari SAK EP
- Cocok untuk operasional harian W9 Cafe
- Menggunakan `SimpleReportService` (saat ini di-*deprecate*, migrasi ke `FinancialReportService.generate('simple', ...)`)

**Perbandingan Rigid vs Simpel:**

| Aspek | Rigid (SAK EP) | Simpel (SAK EMKM) |
|-------|---------------|-------------------|
| Jumlah laporan | 5 | 3 |
| Basis akuntansi | Akrual | Kas |
| Perubahan Ekuitas | ✓ Wajib | ✗ Tidak ada |
| Arus Kas | ✓ Wajib | ✗ Tidak ada (digabung Laba Rugi) |
| Kompleksitas CALK | Tinggi | Rendah |
| Target pengguna | Akuntan profesional | Pemilik usaha |

### 3. Custom

Admin bebas memilih parameter untuk menghasilkan laporan sesuai kebutuhan.

**Parameter yang bisa dipilih:**

| Parameter | Opsi | Deskripsi |
|-----------|------|-----------|
| `date_start` | Tanggal | Awal periode (inclusive) |
| `date_end` | Tanggal | Akhir periode (inclusive) |
| `categories` | Array | Filter kategori: `menu:{id}`, `unexpected_income`, `bahan_baku`, `unexpected_expense` |
| `aggregation` | `daily` / `monthly` | Agregasi per hari atau per bulan |
| `type` | `income` / `expense` / `all` | Hanya pemasukan, hanya pengeluaran, atau semua |

**Contoh konfigurasi custom report:**

```php
$config = [
    'date_start'  => '2026-01-01',
    'date_end'    => '2026-03-31',
    'categories'  => ['menu:1', 'menu:2', 'unexpected_income'],
    'aggregation' => 'monthly',
];

$report = app(FinancialReportService::class)->generate('custom', $config);
```

---

## Tampilan Excel-like dengan AG Grid

### Library yang Digunakan

```json
{
  "dependencies": {
    "ag-grid-react": "^33.x",
    "ag-grid-community": "^33.x"
  }
}
```

- **`ag-grid-react`**: Komponen React untuk AG Grid
- **`ag-grid-community`**: Library inti AG Grid (lisensi MIT, gratis)
- **`clickbar/ag-grid-laravel`**: Paket Laravel untuk server-side processing AG Grid

### Fitur AG Grid

| Fitur | Implementasi |
|-------|-------------|
| **Frozen Headers** | `headerHeight={48}` dengan sticky header |
| **Pinned Columns** | `pinned: 'left'` pada kolom pertama (nama akun) |
| **Format Rupiah** | `valueFormatter: (params) => formatRupiah(params.value)` |
| **Export Excel** | Built-in AG Grid: `gridApi.exportDataAsExcel()` |
| **Export PDF** | Via `pdfMake`, dipanggil dari AG Grid callback |
| **Dark Theme** | `ag-theme-quartz-dark` class pada container |
| **Server-side** | `clickbar/ag-grid-laravel` untuk sorting, filtering, pagination di server |

### Komponen AgGridReport

Komponen wrapper React yang menangani konfigurasi AG Grid untuk semua jenis laporan:

**File:** `resources/js/Components/Common/AgGridReport.jsx`

```jsx
// Struktur komponen (konseptual)
import { AgGridReact } from 'ag-grid-react';
import { formatRupiah } from '@/helpers';

const AgGridReport = ({ rowData, columnDefs, onExportExcel, onExportPdf }) => {
  // Column definitions dengan valueFormatter Rupiah
  const defaultColDef = {
    sortable: true,
    resizable: true,
    filter: 'agTextColumnFilter',
  };

  return (
    <div className="ag-theme-quartz-dark" style={{ height: 600 }}>
      <AgGridReact
        rowData={rowData}
        columnDefs={columnDefs}
        defaultColDef={defaultColDef}
        domLayout="normal"
        suppressRowClickSelection={true}
      />
    </div>
  );
};
```

### Format Rupiah di AG Grid

```jsx
// Value formatter untuk kolom angka
{
  field: 'amount',
  headerName: 'Jumlah (Rp)',
  valueFormatter: (params) => {
    if (params.value == null) return '-';
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(params.value);
  },
  cellClass: 'text-right',
}
```

### Tombol Export

```jsx
// Toolbar actions
<div className="flex gap-2 mb-3">
  <button onClick={() => gridApi.exportDataAsExcel({
    fileName: `Laporan_Keuangan_${dateStart}_${dateEnd}.xlsx`
  })}>
    📥 Export Excel
  </button>
  <button onClick={() => exportToPdf(gridApi)}>
    📄 Export PDF
  </button>
</div>
```

---

## Header Template (ReportHeaderTemplate)

> **Catatan implementasi:** Model aktual bernama `ReportTemplate` (bukan `ReportHeaderTemplate`).  
> File: `app/Models/ReportTemplate.php`  
> Migration: `database/migrations/2026_05_08_000001_create_report_templates_table.php`

### Schema Tabel `report_templates`

```sql
CREATE TABLE report_templates (
    id         BIGSERIAL PRIMARY KEY,
    user_id    BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name       VARCHAR(255) NOT NULL,
    type       VARCHAR(50) NOT NULL,
    config     JSONB NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Field `config` (JSON)

Template header disimpan sebagai JSON dalam field `config`. Struktur yang akan ditambahkan:

```json
{
  "header": {
    "entity_name": "W9 Cafe STIE Totalwin",
    "address": "Jl. Majapahit No. 605, Semarang",
    "phone": "0812-3456-7890",
    "periode": "Januari - Maret 2026",
    "mata_uang": "Rp",
    "pembulatan": "ribuan"
  },
  "additional_info": {
    "npwp": "xx.xxx.xxx.x-xxx.xxx",
    "note": "Laporan ini disusun berdasarkan SAK EMKM"
  },
  "is_default": true
}
```

### Header sesuai Standar SAK

Setiap laporan SAK wajib mencantumkan minimal:

1. **Nama entitas** (`entity_name`): "W9 Cafe STIE Totalwin"
2. **Jenis laporan**: "Laporan Posisi Keuangan" / "Laporan Laba Rugi" / dll.
3. **Periode laporan** (`periode`): "31 Maret 2026" atau "Untuk periode yang berakhir 31 Maret 2026"
4. **Mata uang pelaporan** (`mata_uang`): "Rupiah (Rp)"
5. **Tingkat pembulatan** (`pembulatan`): "dalam ribuan Rupiah"

### CRUD Template via Filament

**File yang akan dibuat:** `app/Filament/Resources/ReportHeaderTemplateResource.php`

```php
// Konseptual - implementasi via Filament Resource
class ReportTemplateResource extends Resource
{
    protected static ?string $model = ReportTemplate::class;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')->required(),
            Select::make('type')->options([
                'simple' => 'SAK EMKM (Simpel)',
                'rigid'  => 'SAK EP (Rigid)',
                'custom' => 'Custom',
            ]),
            KeyValue::make('config.header')
                ->label('Header Laporan'),
            Toggle::make('config.is_default')
                ->label('Jadikan Default'),
        ]);
    }
}
```

---

## SAK Standar untuk Referensi

### PSAK 201 — Penyajian Laporan Keuangan

Referensi utama untuk format dan penyajian laporan keuangan:

| Prinsip | Implementasi |
|---------|-------------|
| **Going concern** | Laporan disusun dengan asumsi usaha berlanjut. Tidak ada indikasi penghentian. |
| **Accrual basis** | Digunakan untuk Rigid (SAK EP). Pendapatan diakui saat transaksi, bukan saat kas diterima. |
| **Materiality** | Selisih pembulatan < Rp 3.000 dianggap tidak material. Tidak perlu koreksi. |
| **Offsetting** | Aset dan liabilitas disajikan terpisah. Tidak saling hapus (offset). |
| **Comparative** | Setiap laporan menyajikan periode komparatif (tahun lalu). |

### PSAK 234 — Pelaporan Keuangan Interim

Untuk laporan tengah tahun (mid-year):

| Ketentuan | Implementasi |
|-----------|-------------|
| **YTD Cumulative** | Laporan Laba Rugi dan Arus Kas dihitung kumulatif dari 1 Januari hingga tanggal laporan. |
| **Komparatif** | Menyajikan periode yang sama tahun sebelumnya sebagai pembanding. |
| **Estimasi** | Beban yang diestimasi tahunan (misal: bonus) dibagi proporsional. |

**Contoh Mid-year YTD:**

```
Laporan Laba Rugi
Untuk periode 1 Januari - 31 Maret 2026 (YTD)
(Dengan perbandingan 1 Januari - 31 Maret 2025)

                    YTD Mar 2026    YTD Mar 2025
Pendapatan          Rp 45.000.000   Rp 38.000.000
Beban Pokok         Rp 18.000.000   Rp 15.000.000
Laba Kotor          Rp 27.000.000   Rp 23.000.000
```

### OJK SEOJK — Penyajian Nilai Negatif

| Ketentuan | Implementasi |
|-----------|-------------|
| **Negative values** | Format: `-Rp 5.000.000` dengan tanda minus di depan, BUKAN dalam kurung `(Rp 5.000.000)`. |
| **Konsistensi** | Format sama untuk seluruh laporan. |

### Materiality Principle

```
Pembulatan dalam ribuan → selisih akibat pembulatan < Rp 3.000 → immaterial
Disclosure di header: "Disajikan dalam Rupiah, dibulatkan dalam ribuan"
Rounding dilakukan hanya pada TOTAL AKHIR (total = ROUND(true_total / 1000) * 1000)
```

---

## Teknis Implementasi

### File yang Diubah/Dibuat

```
app/
├── Models/
│   └── ReportHeaderTemplate.php          # NEW — model untuk header template (extend ReportTemplate)
├── Filament/
│   ├── Clusters/Financial/
│   │   ├── FinancialCluster.php          # EXISTING — cluster keuangan
│   │   ├── Pages/
│   │   │   ├── SavedTemplates.php        # EXISTING — CRUD templates
│   │   │   └── GeneratedReports.php      # EXISTING — riwayat laporan
│   └── Resources/
│       └── ReportHeaderTemplateResource.php  # NEW — Filament resource untuk header
├── Services/
│   ├── FinancialReportService.php        # EXISTING — facade utama
│   ├── RigidReportService.php            # EXISTING — extend untuk SAK EP
│   ├── SimpleReportService.php           # EXISTING — extend untuk SAK EMKM
│   └── CustomReportService.php           # EXISTING — extend untuk custom
└── Renderers/
    └── DomPdfRenderer.php                # NEW — PDF renderer custom (margin, footer, A4)

resources/
└── js/
    └── Components/
        └── Common/
            └── AgGridReport.jsx           # NEW — komponen AG Grid wrapper
```

### FinancialReportService sebagai Facade

```php
// app/Services/FinancialReportService.php
class FinancialReportService
{
    public function generate(string $type, array $params = []): ReportData
    {
        return match ($type) {
            'simple' => $this->generateSimple($params),  // SAK EMKM
            'rigid'  => $this->generateRigid($params),   // SAK EP
            'custom' => $this->generateCustom($params),  // Custom
            default  => throw new \InvalidArgumentException("Unknown type: {$type}"),
        };
    }
}
```

### Integrasi Server-Side AG Grid Laravel

```bash
composer require clickbar/ag-grid-laravel
```

```php
// Controller untuk data AG Grid (server-side)
use Clickbar\AgGridLaravel\AgGrid;

public function getReportData(Request $request): JsonResponse
{
    $query = Order::query()->with('items.menu', 'payment');

    return AgGrid::forQuery($query)
        ->withColumn('order_code', 'ID Pesanan')
        ->withColumn('total_amount', 'Total', formatter: 'currency')
        ->withColumn('created_at', 'Tanggal', formatter: 'date')
        ->handle($request);
}
```

---

## PDF Export

### DomPdfRenderer

**File:** `app/Renderers/DomPdfRenderer.php`

Spesifikasi render:

| Pengaturan | Nilai |
|-----------|-------|
| Ukuran kertas | A4 (210mm × 297mm) |
| Orientasi default | Portrait |
| Orientasi lebar | Landscape (untuk laporan dengan banyak kolom) |
| Margin atas | 2 cm |
| Margin bawah | 2 cm |
| Margin kiri | 2.5 cm |
| Margin kanan | 2.5 cm |
| Footer | "Halaman {PAGE} dari {PAGES}" |
| Font | Inter / DejaVu Sans (Unicode support) |

```php
// app/Renderers/DomPdfRenderer.php (konseptual)
class DomPdfRenderer
{
    public function render(string $html, array $options = []): string
    {
        $orientation = $options['orientation'] ?? 'portrait';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
            ->setPaper('a4', $orientation)
            ->setOptions([
                'margin_top'    => 20,  // mm
                'margin_bottom' => 20,
                'margin_left'   => 25,
                'margin_right'  => 25,
                'defaultFont'   => 'dejavu sans',
            ]);

        // Footer: nomor halaman
        $pdf->getDomPDF()->setCallbacks([
            'page_script' => '
                if ($PAGE_COUNT > 1) {
                    $font = $fontMetrics->getFont("DejaVu Sans");
                    $canvas->text(270, 810, "Halaman $PAGE_NUM dari $PAGE_COUNT", $font, 9);
                }
            ',
        ]);

        return $pdf->output();
    }
}
```

### Pencegahan Tabel Terpotong

```css
/* CSS directive di template PDF */
tr {
    page-break-inside: avoid;  /* Jangan potong row di tengah */
}
thead {
    display: table-header-group;  /* Ulangi header di setiap halaman */
}
.section-heading {
    page-break-before: always;  /* Section baru = halaman baru */
}
```

### PDF via pdfMake (dari AG Grid)

```javascript
// Export PDF dari AG Grid menggunakan pdfMake
import pdfMake from 'pdfmake/build/pdfmake';
import pdfFonts from 'pdfmake/build/vfs_fonts';

pdfMake.vfs = pdfFonts.pdfMake.vfs;

const exportToPdf = (gridApi) => {
  const rowData = [];
  gridApi.forEachNodeAfterFilterAndSort((node) => {
    rowData.push(node.data);
  });

  const docDefinition = {
    pageSize: 'A4',
    pageMargins: [25, 20, 25, 20], // left, top, right, bottom (mm)
    footer: (currentPage, pageCount) => ({
      text: `Halaman ${currentPage} dari ${pageCount}`,
      alignment: 'center',
      fontSize: 9,
    }),
    content: [
      { text: 'W9 Cafe STIE Totalwin', style: 'header' },
      { text: 'Laporan Keuangan', style: 'subheader' },
      // ... table dari rowData
    ],
  };

  pdfMake.createPdf(docDefinition).download('Laporan_Keuangan.pdf');
};
```

---

## Edge Cases

### 1. Zero-Transaction Period

**Kasus:** Periode laporan di mana tidak ada transaksi sama sekali (misal: hari libur, cafe tutup).

**Penanganan:**
- ✅ **Tampilkan semua section** laporan dengan nilai `Rp 0`
- ❌ **JANGAN sembunyikan** section yang bernilai nol
- ❌ **JANGAN tampilkan** pesan "tidak ada data" atau "no data"

Sesuai PSAK 201: laporan keuangan harus lengkap, meskipun saldonya nol.

```
Laporan Laba Rugi
Untuk periode 25 Desember 2026 (Cafe Tutup)

Pendapatan          Rp 0
Beban Pokok         Rp 0
Laba Kotor          Rp 0
Beban Operasional   Rp 0
Laba/Rugi Bersih    Rp 0
```

### 2. Mid-Year YTD (PSAK 234)

**Kasus:** Laporan dibuat pada bulan Juni untuk periode 1 Januari - 30 Juni.

**Penanganan:**

```php
// Laporan Laba Rugi dan Arus Kas: kumulatif dari 1 Januari
$dateStart = Carbon::create($year, 1, 1); // 1 Januari
$dateEnd = Carbon::parse($request->date_end); // 30 Juni

// Bandingkan dengan periode yang sama tahun lalu
$prevYearStart = Carbon::create($year - 1, 1, 1);
$prevYearEnd = Carbon::create($year - 1, $dateEnd->month, $dateEnd->day);
```

### 3. Negative Balance

**Kasus:** Nilai negatif (rugi, arus kas keluar lebih besar, saldo minus).

**Penanganan:**
- Format: `-Rp 5.000.000` (tanda minus di depan)
- ❌ BUKAN parentheses: `(Rp 5.000.000)`
- ❌ BUKAN warna merah (PDF hitam putih)

```php
// Format helper untuk nilai negatif
function formatNegativeCurrency(float $amount): string {
    if ($amount < 0) {
        return '-Rp ' . number_format(abs($amount), 0, ',', '.');
    }
    return 'Rp ' . number_format($amount, 0, ',', '.');
}
```

### 4. Currency Rounding

**Kasus:** Pembulatan nilai dalam ribuan Rupiah.

**Penanganan:**

```php
// Round hanya total akhir, bukan setiap baris
$trueTotal = $items->sum('amount');           // 12.345.678
$roundedTotal = round($trueTotal / 1000) * 1000; // 12.346.000

// Disclosure di header
// "Disajikan dalam Rupiah, dibulatkan dalam ribuan"
```

**Prinsip materialitas:** selisih pembulatan < Rp 3.000 tidak material.

### 5. Report Template Dihapus

**Kasus:** Admin menghapus template yang sedang dipakai oleh laporan aktif.

**Penanganan:**

```php
// Soft reference — jangan hard delete template
// Gunakan soft delete atau simpan snapshot config di generated_reports

// Saat generate laporan, simpan SNAPSHOT template config:
GeneratedReport::create([
    'template_id' => $template->id,
    'template_snapshot' => $template->config,  // JSON snapshot
    'report_data' => $reportData,
    'generated_at' => now(),
]);

// Jika template dihapus, laporan tetap bisa dirender dari snapshot
```

### 6. Laporan dengan Banyak Kolom (Landscape)

**Kasus:** Laporan custom dengan banyak kolom tidak muat di A4 portrait.

**Penanganan:**
- Deteksi otomatis: jika jumlah kolom > 6 → switch ke landscape
- Admin bisa override orientasi manual di parameter laporan
- AG Grid auto-resize kolom agar muat

---

## Diagram Alur Generate Laporan

```
Admin pilih jenis laporan
│
├─ Simpel (SAK EMKM)
│   ├─ Pilih periode
│   ├─ Pilih template header
│   └─ [GENERATE] → FinancialReportService::generate('simple')
│       ├─ SimpleReportService
│       ├─ Tampilkan di AG Grid
│       └─ Export options: Excel / PDF
│
├─ Rigid (SAK EP)
│   ├─ Pilih periode
│   ├─ Pilih template header
│   └─ [GENERATE] → FinancialReportService::generate('rigid')
│       ├─ RigidReportService
│       ├─ Tampilkan 5 section (Neraca, Laba Rugi, Perubahan Ekuitas, Arus Kas, CALK)
│       └─ Export options
│
└─ Custom
    ├─ Pilih periode
    ├─ Pilih kategori (pemasukan saja / pengeluaran saja / semua)
    ├─ Pilih agregasi (harian / bulanan)
    └─ [GENERATE] → FinancialReportService::generate('custom')
        ├─ CustomReportService
        ├─ Tampilkan tabel dengan grouping
        └─ Export options
```
