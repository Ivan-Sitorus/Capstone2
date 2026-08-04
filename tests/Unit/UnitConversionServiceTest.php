<?php

namespace Tests\Unit;

use App\Enums\Unit;
use App\Services\UnitConversionService;
use Tests\TestCase;

class UnitConversionServiceTest extends TestCase
{
    private UnitConversionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UnitConversionService();
    }

    public function test_kg_to_gram(): void
    {
        $this->assertEquals(1000, $this->service->convert(1, Unit::Kilogram, Unit::Gram));
    }

    public function test_gram_to_kg(): void
    {
        $this->assertEquals(0.5, $this->service->convert(500, Unit::Gram, Unit::Kilogram));
    }

    public function test_liter_to_ml(): void
    {
        $this->assertEquals(1000, $this->service->convert(1, Unit::Liter, Unit::Milliliter));
    }

    public function test_cross_type_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->convert(1, Unit::Gram, Unit::Milliliter);
    }

    public function test_same_unit(): void
    {
        $this->assertEquals(5.0, $this->service->convert(5, Unit::Kilogram, Unit::Kilogram));
    }

    public function test_get_compatible_units(): void
    {
        $units = $this->service->getCompatibleUnits(Unit::Kilogram);
        $this->assertTrue($units->contains(fn (Unit $u) => $u === Unit::Gram));
        $this->assertFalse($units->contains(fn (Unit $u) => $u === Unit::Milliliter));
    }
}
