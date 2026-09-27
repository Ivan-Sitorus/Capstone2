<?php

namespace Tests\Unit;

use App\Enums\Unit;
use App\Services\UnitConversionService;
use Tests\TestCase;

class UnitEnumTest extends TestCase
{
    public function test_pcs_and_buah_are_distinct_cases(): void
    {
        $this->assertNotSame(Unit::Piece, Unit::Buah);
        $this->assertSame('pcs', Unit::Piece->value);
        $this->assertSame('buah', Unit::Buah->value);
        $this->assertSame(Unit::Piece, Unit::from('pcs'));
        $this->assertSame(Unit::Buah, Unit::from('buah'));
    }

    public function test_pcs_and_buah_have_expected_labels(): void
    {
        $this->assertSame('Pcs', Unit::Piece->label());
        $this->assertSame('Buah', Unit::Buah->label());
        $this->assertNotSame(Unit::Piece->label(), Unit::Buah->label());
    }

    public function test_pcs_and_buah_are_count_units_with_factor_one(): void
    {
        $this->assertSame('count', Unit::Piece->unitType());
        $this->assertSame('count', Unit::Buah->unitType());
        $this->assertSame(1.0, Unit::Piece->conversionFactor());
        $this->assertSame(1.0, Unit::Buah->conversionFactor());
    }

    public function test_options_expose_both_units(): void
    {
        $options = Unit::options();

        $this->assertArrayHasKey('pcs', $options);
        $this->assertArrayHasKey('buah', $options);
        $this->assertSame('Pcs', $options['pcs']);
        $this->assertSame('Buah', $options['buah']);
    }

    public function test_conversion_service_treats_both_as_compatible_count_units(): void
    {
        $service = new UnitConversionService();

        $fromPcs = $service->getCompatibleUnits(Unit::Piece);
        $this->assertTrue($fromPcs->contains(fn (Unit $u): bool => $u === Unit::Buah));

        $fromBuah = $service->getCompatibleUnits(Unit::Buah);
        $this->assertTrue($fromBuah->contains(fn (Unit $u): bool => $u === Unit::Piece));

        $this->assertSame(3.0, $service->convert(3, Unit::Piece, Unit::Buah));
    }
}
