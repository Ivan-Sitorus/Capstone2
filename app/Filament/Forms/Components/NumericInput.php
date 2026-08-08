<?php

namespace App\Filament\Forms\Components;

use App\Enums\Unit;
use Filament\Forms\Components\TextInput;
use Filament\Support\RawJs;

/**
 * Helper for all numeric inputs in Filament forms.
 * Uses the Alpine $money mask for automatic thousands (dot) and decimal (comma) formatting.
 * Thousands dots are stripped before validation/saving; decimal commas are normalized to dots.
 */
class NumericInput
{
    /**
     * Apply full numeric configuration to a TextInput:
     * numeric() (number keyboard + validation) + type text (so mask works)
     * + thousands/decimal mask + strip dots + dynamic rules (max + decimal/integer)
     * + comma-to-dot normalization.
     *
     * @param  int  $maxDigits  number of integer digits (e.g. 6 = max 999.999, 9 = max 999.999.999)
     * @param  int  $precision  decimal digits (0 = integer, 3 = max 3 decimals)
     */
    public static function apply(TextInput $input, int $maxDigits = 6, int $precision = 0): TextInput
    {
        return $input
            ->numeric()
            ->type('text')
            ->mask(static::mask($precision))
            ->stripCharacters('.')
            ->rules(static::rulesFor($maxDigits, $precision))
            ->mutateStateForValidationUsing(fn ($state) => static::normalizeState($state))
            ->dehydrateStateUsing(fn ($state) => static::normalizeState($state));
    }

    /**
     * Build validation rules from maxDigits & precision.
     * Uses native Laravel rules (max + decimal/integer) instead of max_digits,
     * which is broken for decimal values (Laravel counts string length and
     * rejects any non-digit character, e.g. the decimal dot).
     *
     * @return array<int, string>
     */
    public static function rulesFor(int $maxDigits = 6, int $precision = 0): array
    {
        $maxValue = (10 ** $maxDigits) - ($precision > 0 ? 10 ** -$precision : 1);

        $rules = ['numeric', "max:{$maxValue}"];

        if ($precision > 0) {
            $rules[] = "decimal:0,{$precision}";
        } else {
            $rules[] = 'integer';
        }

        return $rules;
    }

    /**
     * Map a unit value to decimal precision:
     * gram/ml → 0 (integer only), others (kg/liter/pcs/sachet) → 3.
     */
    public static function precisionForUnit(?string $unit): int
    {
        return in_array($unit, [Unit::Gram->value, Unit::Milliliter->value], true) ? 0 : 3;
    }

    /**
     * Alpine $money mask with Indonesian formatting:
     * - thousands separator: '.' (automatic, e.g. 15000 → 15.000)
     * - decimal separator: ',' (comma)
     * - precision: number of decimal digits (0 = integer, 3 = max 3 decimals)
     */
    public static function mask(int $precision = 0): RawJs
    {
        return RawJs::make("\$money(\$input, ',', '.', {$precision})");
    }

    /**
     * Convert decimal comma (Indonesian format) to dot for numeric validation & DB.
     */
    public static function normalizeState(mixed $state): mixed
    {
        return is_string($state) ? str_replace(',', '.', $state) : $state;
    }
}
