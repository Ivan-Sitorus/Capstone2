<?php

namespace Tests\Unit;

use App\Models\Unit;
use App\Services\UnitConversionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitConversionServiceTest extends TestCase
{
    use RefreshDatabase;
    private UnitConversionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UnitConversionService();
    }

    public function test_kg_to_gram(): void
    {
        $this->assertEquals(1000, $this->service->convert(1, Unit::where('name','kg')->first(), Unit::where('name','gram')->first()));
    }

    public function test_gram_to_kg(): void
    {
        $this->assertEquals(0.5, $this->service->convert(500, Unit::where('name','gram')->first(), Unit::where('name','kg')->first()));
    }

    public function test_liter_to_ml(): void
    {
        $this->assertEquals(1000, $this->service->convert(1, Unit::where('name','liter')->first(), Unit::where('name','ml')->first()));
    }

    public function test_cross_type_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->convert(1, Unit::where('name','gram')->first(), Unit::where('name','ml')->first());
    }

    public function test_same_unit(): void
    {
        $this->assertEquals(5.0, $this->service->convert(5, Unit::where('name','kg')->first(), Unit::where('name','kg')->first()));
    }

    public function test_get_compatible_units(): void
    {
        $units = $this->service->getCompatibleUnits(Unit::where('name','kg')->first());
        $this->assertTrue($units->contains('name','gram'));
        $this->assertFalse($units->contains('name','ml'));
    }
}
