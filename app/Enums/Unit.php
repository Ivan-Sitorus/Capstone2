<?php

namespace App\Enums;

/**
 * App-level vocabulary for `ingredients.unit` and `menu_ingredients.unit`.
 *
 * `Piece` ('pcs') and `Buah` ('buah') are intentionally distinct count units
 * (both unitType 'count', conversion factor 1), so the UnitConversionService
 * treats them as compatible. Use 'buah' for discrete fruit/produce and 'pcs'
 * for packaged/processed countable items. The columns store free strings;
 * this enum only constrains the vocabulary, so no migration is required.
 */
enum Unit: string
{
    case Gram = 'gram';
    case Kilogram = 'kg';
    case Milliliter = 'ml';
    case Liter = 'liter';
    case Piece = 'pcs';
    case Buah = 'buah';
    case Sachet = 'sachet';

    public function label(): string
    {
        return match ($this) {
            self::Gram => 'Gram (g)',
            self::Kilogram => 'Kilogram (kg)',
            self::Milliliter => 'Mililiter (ml)',
            self::Liter => 'Liter (L)',
            self::Piece => 'Pcs',
            self::Buah => 'Buah',
            self::Sachet => 'Sachet',
        };
    }

    public function unitType(): string
    {
        return match ($this) {
            self::Gram, self::Kilogram => 'weight',
            self::Milliliter, self::Liter => 'volume',
            self::Piece, self::Buah, self::Sachet => 'count',
        };
    }

    public function conversionFactor(): float
    {
        return match ($this) {
            self::Gram => 0.001,
            self::Milliliter => 0.001,
            self::Kilogram, self::Liter, self::Piece, self::Buah, self::Sachet => 1,
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $unit) => [$unit->value => $unit->label()])
            ->all();
    }
}
