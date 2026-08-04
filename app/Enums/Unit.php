<?php

namespace App\Enums;

enum Unit: string
{
    case Gram = 'gram';
    case Kilogram = 'kg';
    case Milliliter = 'ml';
    case Liter = 'liter';
    case Piece = 'pcs';
    case Sachet = 'sachet';

    public function label(): string
    {
        return match ($this) {
            self::Gram => 'Gram (g)',
            self::Kilogram => 'Kilogram (kg)',
            self::Milliliter => 'Mililiter (ml)',
            self::Liter => 'Liter (L)',
            self::Piece => 'Buah / Pcs',
            self::Sachet => 'Sachet',
        };
    }

    public function unitType(): string
    {
        return match ($this) {
            self::Gram, self::Kilogram => 'weight',
            self::Milliliter, self::Liter => 'volume',
            self::Piece, self::Sachet => 'count',
        };
    }

    public function conversionFactor(): float
    {
        return match ($this) {
            self::Gram => 0.001,
            self::Milliliter => 0.001,
            self::Kilogram, self::Liter, self::Piece, self::Sachet => 1,
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $unit) => [$unit->value => $unit->label()])
            ->all();
    }
}
