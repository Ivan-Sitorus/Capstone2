<?php

namespace App\Services;

use App\Enums\Unit;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class UnitConversionService
{
    public function convert(float $amount, Unit $from, Unit $to): float
    {
        if ($from === $to) {
            return $amount;
        }

        if ($from->unitType() !== $to->unitType()) {
            throw new InvalidArgumentException("Cannot convert {$from->unitType()} to {$to->unitType()}");
        }

        return round($amount * $from->conversionFactor() / $to->conversionFactor(), 6);
    }

    public function getCompatibleUnits(Unit $unit): Collection
    {
        return collect(Unit::cases())
            ->filter(fn (Unit $u) => $u->unitType() === $unit->unitType())
            ->values();
    }
}
