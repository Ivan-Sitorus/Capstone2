<?php

namespace Tests\Unit\Support;

use App\Support\Formatter;
use PHPUnit\Framework\TestCase;

class FormatterTest extends TestCase
{
    public function test_rupiah_formats_zero(): void
    {
        $this->assertSame('Rp 0', Formatter::rupiah(0));
    }

    public function test_rupiah_formats_small_amounts(): void
    {
        $this->assertSame('Rp 500', Formatter::rupiah(500));
    }

    public function test_rupiah_formats_thousands(): void
    {
        $this->assertSame('Rp 1.500', Formatter::rupiah(1500));
        $this->assertSame('Rp 45.000', Formatter::rupiah(45000));
    }

    public function test_rupiah_formats_millions(): void
    {
        $this->assertSame('Rp 1.000.000', Formatter::rupiah(1000000));
        $this->assertSame('Rp 2.450.000', Formatter::rupiah(2450000));
    }

    public function test_rupiah_formats_negative_amounts(): void
    {
        $this->assertSame('Rp -45.000', Formatter::rupiah(-45000));
    }

    public function test_rupiah_accepts_floats_and_rounds_to_zero_decimals(): void
    {
        $this->assertSame('Rp 45.000', Formatter::rupiah(45000.0));
        $this->assertSame('Rp 45.001', Formatter::rupiah(45000.75));
    }

    public function test_number_formats_plain_id_grouping(): void
    {
        $this->assertSame('45.000', Formatter::number(45000));
        $this->assertSame('1.000.000', Formatter::number(1000000));
        $this->assertSame('-45.000', Formatter::number(-45000));
    }

    public function test_number_supports_decimals(): void
    {
        $this->assertSame('1.500,50', Formatter::number(1500.5, 2));
    }
}
