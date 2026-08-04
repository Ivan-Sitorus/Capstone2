<?php

namespace App\Filament\Forms\Components;

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
     * numeric() (number keyboard + validation) + type text (so mask & maxLength work)
     * + thousands/decimal mask + strip dots + character limit + comma-to-dot normalization.
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
            ->maxLength(static::maxLength($maxDigits, $precision))
            ->mutateStateForValidationUsing(static::validationNormalizer())
            ->dehydrateStateUsing(fn ($state) => static::normalizeState($state));
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
     * Maximum input length (including thousands dots & decimal comma).
     * Example: 6 digits + 0 decimals → "999.999" = 7 chars; 6 digits + 3 decimals → "999.999,999" = 11 chars.
     */
    public static function maxLength(int $maxDigits = 6, int $precision = 0): int
    {
        $thousandDots = (int) floor(($maxDigits - 1) / 3);
        $decimalPart = $precision > 0 ? 1 + $precision : 0;

        return $maxDigits + $thousandDots + $decimalPart;
    }

    /**
     * Convert decimal comma (Indonesian format) to dot for numeric validation & DB.
     */
    public static function normalizeState(mixed $state): mixed
    {
        return is_string($state) ? str_replace(',', '.', $state) : $state;
    }

    /**
     * Closure to normalize comma-to-dot BEFORE validation (mutateStateForValidationUsing).
     * Without this, input "8,8" fails the numeric rule because the comma is not yet normalized.
     */
    public static function validationNormalizer(): \Closure
    {
        return fn (mixed $state): mixed => static::normalizeState($state);
    }
}
