
## 2026-05-08: Task 17-19 — CafeTableResource with QR Code

### QR Library
- `chillerlan/php-qrcode` v5.0.5 was already installed as a dependency of `pragmarx/google2fa-qrcode`
- Used directly instead of installing `linkxtr/laravel-qrcode`
- SVG generation works: `(new QRCode($options))->render($url)`
- Options: `OUTPUT_MARKUP_SVG`, `ECC_L`, `scale=5`, `imageBase64=false`

### CafeTableResource
- Navigation group: 'Data Master', sort: 3 (after Menu=1, Category=2)
- Form: table_number (numeric, required, unique), is_available (toggle, default true)
- Table: table_number, is_available (boolean icon), qr_code_svg (custom HTML)
- Record actions: EditAction, custom 'view_qr' modal
- Pages: List, Create, Edit (+ DeleteAction)

### QR in Filament
- List column: renders 80x80 img via data URI (`data:image/svg+xml;base64,...`)
- Modal (via "Lihat QR" action): 260x260 img with download button
- Download: uses `<a href="dataUri" download="...">` — works in modern browsers
- qr_code field auto-generated on create/update from `route('customer.identitas', ['table' => table_number])`

### Model Accessors
- `getQrCodeUrlAttribute()`: returns the route URL
- `getQrCodeSvgAttribute()`: generates QR SVG string
- `getQrCodeSvgDataUriAttribute()`: returns base64 data URI for inline display
- Boot events (`creating`, `updating`) auto-manage `qr_code` field

### Note
- The `customer.identitas` route `/order?table={table_number}` passes table_number to `CafeTable::find()` which expects primary key ID, not table_number. This is a pre-existing controller bug — downstream fix needed.
