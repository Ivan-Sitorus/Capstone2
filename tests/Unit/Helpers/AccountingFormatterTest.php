<?php

namespace Tests\Unit\Helpers;

use App\Helpers\AccountingFormatter;
use PHPUnit\Framework\TestCase;

class AccountingFormatterTest extends TestCase
{
    public function test_rupiah_formats_positive_amount(): void
    {
        $this->assertEquals('Rp 1.000.000', AccountingFormatter::rupiah(1000000));
    }

    public function test_rupiah_formats_zero(): void
    {
        $this->assertEquals('Rp 0', AccountingFormatter::rupiah(0));
    }

    public function test_rupiah_formats_large_amount_with_millions(): void
    {
        $this->assertEquals('Rp 2.500.000.000', AccountingFormatter::rupiah(2500000000));
    }

    public function test_rupiah_accounting_formats_negative(): void
    {
        $this->assertEquals('(Rp 50.000)', AccountingFormatter::rupiahAccounting(-50000));
    }

    public function test_rupiah_accounting_formats_positive(): void
    {
        $this->assertEquals('Rp 75.000', AccountingFormatter::rupiahAccounting(75000));
    }

    public function test_excel_format_rupiah_returns_string(): void
    {
        $format = AccountingFormatter::excelFormatRupiah();
        $this->assertIsString($format);
        $this->assertNotEmpty($format);
        $this->assertStringContainsString('#,##0', $format);
    }

    public function test_excel_percent_format_returns_string(): void
    {
        $this->assertSame('0.00%', AccountingFormatter::excelPercentFormat());
    }

    public function test_date_indo_formats_correctly(): void
    {
        $this->assertEquals('22 Feb 2026', AccountingFormatter::dateIndo('2026-02-22'));
    }

    public function test_date_indo_full_formats_correctly(): void
    {
        $this->assertEquals('22 Februari 2026', AccountingFormatter::dateIndoFull('2026-02-22'));
    }

    public function test_source_label_maps_all_known_sources(): void
    {
        $sources = [
            'cash' => 'Tunai',
            'qris' => 'QRIS',
            'transfer' => 'Transfer',
            'card' => 'Kartu',
            'bayar_nanti' => 'Bayar Nanti',
            'unexpected_income' => 'Pemasukan Tidak Terduga',
            'ingredient_purchase' => 'Pembelian Bahan Baku',
            'unexpected_expense' => 'Pengeluaran Tidak Terduga',
        ];

        foreach ($sources as $code => $expected) {
            $this->assertEquals($expected, AccountingFormatter::sourceLabel($code), "Failed for source: {$code}");
        }
    }

    public function test_source_label_falls_back_to_unknown(): void
    {
        $result = AccountingFormatter::sourceLabel('unknown_source_code');
        $this->assertEquals('Unknown source code', $result);
    }

    public function test_source_label_falls_back_for_empty_string(): void
    {
        $result = AccountingFormatter::sourceLabel('');
        $this->assertEquals('', $result);
    }
}
