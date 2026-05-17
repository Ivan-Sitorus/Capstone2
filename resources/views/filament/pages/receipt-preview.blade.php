<div class="receipt-preview">
    <div class="mx-auto" style="max-width: 320px; font-family: 'Courier New', Courier, monospace; background: #fff; padding: 24px; border: 1px dashed #d1d5db;">
        {{-- Header: Logo & Nama Cafe --}}
        <div class="text-center">
            @if (!empty($data['receipt_logo']))
                <img src="{{ \Illuminate\Support\Facades\Storage::url($data['receipt_logo']) }}" alt="Logo"
                     style="max-height: 60px; margin: 0 auto 8px;">
            @endif
            <div style="font-size: 18px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #1f2937;">
                {{ $data['cafe_name'] ?? 'W9 Cafe' }}
            </div>
            @if (!empty($data['cafe_address']))
                <div style="font-size: 11px; color: #6b7280; margin-top: 2px;">
                    {{ $data['cafe_address'] }}
                </div>
            @endif
            @if (!empty($data['cafe_phone']))
                <div style="font-size: 11px; color: #6b7280;">
                    Telp: {{ $data['cafe_phone'] }}
                </div>
            @endif
        </div>

        {{-- Divider --}}
        <div style="border-top: 1px dashed #9ca3af; margin: 12px 0;"></div>

        {{-- Title --}}
        <div style="text-align: center; font-size: 13px; font-weight: 700; letter-spacing: 2px; color: #374151; margin-bottom: 10px;">
            BUKTI PEMBELIAN
        </div>

        <div style="border-top: 1px dashed #9ca3af; margin-bottom: 8px;"></div>

        {{-- Items --}}
        <table style="width: 100%; font-size: 11px; color: #374151; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="text-align: left; padding-bottom: 4px; border-bottom: 1px solid #d1d5db;">Item</th>
                    <th style="text-align: center; padding-bottom: 4px; border-bottom: 1px solid #d1d5db;">Qty</th>
                    <th style="text-align: right; padding-bottom: 4px; border-bottom: 1px solid #d1d5db;">Harga</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding: 3px 0;">Kopi Robusta</td>
                    <td style="text-align: center; padding: 3px 0;">2</td>
                    <td style="text-align: right; padding: 3px 0;">24.000</td>
                </tr>
                <tr>
                    <td style="padding: 3px 0;">Roti Bakar</td>
                    <td style="text-align: center; padding: 3px 0;">1</td>
                    <td style="text-align: right; padding: 3px 0;">15.000</td>
                </tr>
                <tr>
                    <td style="padding: 3px 0;">Teh Manis</td>
                    <td style="text-align: center; padding: 3px 0;">1</td>
                    <td style="text-align: right; padding: 3px 0;">5.000</td>
                </tr>
            </tbody>
        </table>

        <div style="border-top: 1px dashed #9ca3af; margin: 6px 0;"></div>

        {{-- Total --}}
        <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 700; color: #1f2937; padding: 4px 0;">
            <span>TOTAL</span>
            <span>Rp 44.000</span>
        </div>

        {{-- NPWP --}}
        @if (!empty($data['receipt_show_npwp']) && !empty($data['receipt_npwp']))
            <div style="border-top: 1px dashed #9ca3af; margin: 6px 0;"></div>
            <div style="font-size: 10px; color: #6b7280;">
                NPWP: {{ $data['receipt_npwp'] }}
            </div>
        @endif

        {{-- QRIS --}}
        @if (!empty($data['qris_image']))
            <div style="text-align: center; margin-top: 10px;">
                <img src="{{ \Illuminate\Support\Facades\Storage::url($data['qris_image']) }}" alt="QRIS"
                     style="max-height: 80px;">
                <div style="font-size: 10px; color: #6b7280; margin-top: 4px;">Scan untuk pembayaran</div>
            </div>
        @endif

        {{-- Footer --}}
        <div style="border-top: 1px dashed #9ca3af; margin: 10px 0 8px;"></div>
        <div style="text-align: center; font-size: 11px; color: #6b7280; white-space: pre-line;">
            {{ $data['receipt_footer'] ?? '' }}
        </div>
    </div>
</div>
