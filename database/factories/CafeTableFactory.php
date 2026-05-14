<?php

namespace Database\Factories;

use App\Models\CafeTable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CafeTable>
 */
class CafeTableFactory extends Factory
{
    private static int $tableCounter = 1;

    public function definition(): array
    {
        $number = self::$tableCounter++;

        return [
            'table_number' => $number,
            'qr_code' => fake()->url().'/table/'.$number,
            'is_available' => fake()->boolean(80),
        ];
    }
}
